<?php
/** Ark Mail CPT ajax functions **/
include_once(ARK_MAIL_COMPOSER_ROOT_DIR ."/includes/admin/class-ark_datalinks.php");

class Mail_CPT_AJAX {
    private Ark_DataLink $datalinks;

    public function __construct() {
        add_action("wp_ajax_render_example_variable_set", array($this, "render_variable_set"));
        add_action("wp_ajax_nopriv_render_example_variable_set", array($this,"render_variable_set"));

        add_action("wp_ajax_get_possible_datalinks", array($this,"get_possible_datalinks"));
        add_action("wp_ajax_nopriv_get_possible_datalinks", array($this,"get_possible_datalinks"));

        add_action("wp_ajax_delete_datalink", array($this,"delete_datalink"));
        add_action("wp_ajax_nopriv_delete_datalink", array($this,"delete_datalink"));

        add_action('wp_ajax_add_datalink', array($this,'add_DataLink'));
        add_action('wp_ajax_nopriv_add_datalink', array($this,'add_DataLink'));

        add_action('wp_ajax_save_format_list', array($this,'save_format_list'));
        add_action('wp_ajax_nopriv_save_format_list', array($this,'save_format_list'));

        add_action('wp_ajax_save_mail_template', array($this,'save_mail_template'));
        add_action('wp_ajax_nopriv_save_mail_template', array($this,'save_mail_template'));

        add_action('wp_ajax_direct_mail', array($this,'direct_mail'));
        add_action('wp_ajax_nopriv_direct_mail', array($this,'direct_mail'));

        add_action("wp_ajax_get_example_variable_set", array($this,"get_variable_set"));
        add_action("wp_ajax_nopriv_get_example_variable_set", array($this,"get_variable_set"));


        add_action("wp_ajax_get_loop_iterations", array($this,"get_loop_iterations"));
        add_action("wp_ajax_nopriv_get_loop_iterations", array($this,"get_loop_iterations"));

        if(isset($_REQUEST['mail_id'])) {
            $this->mail_id = $_REQUEST['mail_id'];
            $this->datalinks = get_post_meta($this->mail_id , 'datalink', true);


        }
    }


    function render_variable_set() {

        $link_name = $_REQUEST['link_name'];

        /** Get configured formats **/
        $datalink = isset($_REQUEST['hierarchy_path']) ? $this->datalinks->get_child_by_path($_REQUEST['hierarchy_path']) : null;

        $object_id = $datalink->get_example_id();
        $datalink->get_variable_sets();

        ob_start();
        Ark_Mail_CPT::render_Variable_sets($datalink->data, $datalink, $_REQUEST['formatting'] === "true", $datalink->many);
        wp_send_json_success(array('html' => ob_get_clean()));
    }


    function get_possible_datalinks() {
        $datalink_object = array(
            "link_name"=> $_REQUEST['link_name'],
            "link_type" => $_REQUEST['link_type']
        );
        $possible_link_types = DataLink_Utils::get_all_possible_children($datalink_object);

        ob_start();
        include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/select_datalink_type.php");
        wp_send_json_success(array(
            'html' => ob_get_clean()
        ));
    }



    function delete_datalink() {
        /** Delete the datalink by hierarchy **/
        $this->datalinks->delete_child_by_path($_REQUEST['hierarchy_path']);

        update_post_meta($this->mail_id, 'datalink', $this->datalinks);

        /** Render the datalinks UI  **/
        ob_start();

        $depth = 0;
        foreach ($this->datalinks->links as $id => $datalink_node) {
            include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/datalink_row.php");
        }

        wp_send_json_success(array('html'=> ob_get_clean(), 'datalinks' => $this->datalinks));

    }


    /** Add a datalink to an Ark_Mail, and return the new DataLink UI Element **/
    function add_DataLink() {
        /** Get the DataLinks for the current post **/
        $hierarchy_path = isset($_REQUEST['hierarchy_path']) ? $_REQUEST['hierarchy_path'] : ["root"];
        $new_child = new Ark_DataLink(
            array (
                'type'=> $_REQUEST['link_type'],
                'name'=> $_REQUEST['link_name'],
                'desc' => $_REQUEST['desc'],
            )
        );

        $this->datalinks->add_child_by_path($hierarchy_path, $new_child);

        update_post_meta($this->mail_id, 'datalink', $this->datalinks);

        /** Render the datalinks UI  **/
        ob_start();
        $depth = 0;
        foreach ($this->datalinks->links as $id => $datalink_node) {
            include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/datalink_row.php");
        }

        wp_send_json_success(array('html'=> ob_get_clean(), 'datalinks' => $this->datalinks));
    }


    function save_format_list() {
        $datalink = $this->datalinks->get_child_by_path($_REQUEST['hierarchy_path']);
        $datalink->set_var_format($_REQUEST['set_name'], $_REQUEST['var_name'], $_REQUEST['function_data']);

        update_post_meta($this->mail_id, 'datalink', $this->datalinks);
    }


    function save_mail_template() {

        update_post_meta($_REQUEST['mail_id'], 'gjs-html', $_REQUEST['gjs-html']);
        update_post_meta($_REQUEST['mail_id'], 'gjs-components', $_REQUEST['gjs-components']);
        update_post_meta($_REQUEST['mail_id'], 'gjs-assets', $_REQUEST['gjs-assets']);
        update_post_meta($_REQUEST['mail_id'], 'gjs-css', $_REQUEST['gjs-css']);
        update_post_meta($_REQUEST['mail_id'], 'gjs-styles', $_REQUEST['gjs-styles']);


        wp_send_json_success(
            array(
                'msg' => 'success!'
            )
        );
    }

    function resolve_html($node) {

        /**  Resolve the variables that are not inside a loop **/
        $node->find('.mc_ineditor_variable')->each(function($variable) use ($node){
            $wrapper_node = $variable->closest('mj-wrapper[data_ref]');
            if($wrapper_node == null) {
                /** No wrapper node, so these are static variables **/

                $data_ref = json_decode($variable->attr('data-data_ref'));
                $table_name = $variable->attr('data-table_name');
                $datalink = $this->datalinks->get_child_by_path($data_ref);
                $var_name = trim(trim($variable->textContent, '{'), '}');


                $value = $datalink->get_value($table_name, $var_name);

                $variable->substituteWith($node->create("<span>$value</span>"));
            }

            elseif($wrapper_node == $node ) {

                /** Wrapper node is not empty, it is a loop, but it is the current Node.
                 * Meaning, this loop has been resolved, and we can resolve it's variables
                 */

                $data_ref = json_decode($variable->attr('data-data_ref'));
                $table_name = $variable->attr('data-table_name');
                $datalink = $this->datalinks->get_child_by_path($data_ref, true);
                $var_name = trim(trim($variable->textContent, '{'), '}');


                $value = $datalink->get_value($table_name, $var_name);

                $variable->substituteWith($node->create("<span> $value!</span>"));
            }
        });

        /** Resolve Loops **/
        $node->find('mj-wrapper:first-of-type[data_ref]')->each(function($loop) {
            $data_ref = $loop->attr('data_ref');
            if(!empty($data_ref)) {
                /** This wrapper has a data_ref, so it is a loop **/
                $data_ref = json_decode(urldecode($data_ref));
                $datalinks = $this->datalinks->get_child_by_path($data_ref);

                /** We need to copy the InnerHTML of the current Wrapper Node $loop_count amount of times **/
                $true_loop = $loop->create("<div></div");
                foreach($datalinks as $link_id => $datalink) {
                    $clone = $loop->clone();

                    /** We also mark the wrapper element with it's datalink data **/
                    $clone->attr('data_ref_db_id', $datalink->db_id);
                    $clone->attr('data_ref_link_id', $link_id);

                    $data_ref = urldecode($clone->attr('data_ref'));

                    /** With this new data_ref, we must resolve the datarefs of our variables as well */
                    $clone->find(".mc_ineditor_variable[data-data_ref|='$data_ref']")->each(function($variable) use($clone) {

                       $loop_data_ref = json_decode(urldecode($clone->attr('data_ref')));
                       $original_link_id = array_pop($loop_data_ref);
                       $new_link_id = $clone->attr('data_ref_link_id');

                       $var_data_ref = json_decode($variable->attr('data-data_ref'));
                       $index = array_search($original_link_id, $var_data_ref);
                       if($index != false) {
                           $var_data_ref[$index] = $new_link_id;
                       }

                        $variable->attr('data-data_ref', json_encode($var_data_ref));
                    });


                    $data_ref = json_decode($data_ref);
                    array_pop($data_ref);
                    array_push($data_ref, $link_id);

                    $clone->attr('data_ref' , urlencode(json_encode($data_ref)));


                    /** Resursively resolve this new node **/
                    $clone = $this->resolve_html($clone);

                    $true_loop->appendWith($clone);
                }

                $loop->html("");
                $loop->prependWith($true_loop->children());
            }
        });


//
//        foreach($loops as $loop) {
//            $node->detach($loop);
//        }
//


        return $node;

    }


    /**
     * Mail
     *
     * The steps are as follows:
     *  1. Construct the Datalinks object for the mail
     *  2. If not set, get the (example) IDs for the root datalinks
     *  3.
     */
    function direct_mail() {

        require_once ARK_MAIL_COMPOSER_ROOT_DIR . '/lib/vendor/autoload.php';

        $renderer = new \Qferrer\Mjml\Renderer\BinaryRenderer(ARK_MAIL_COMPOSER_ROOT_DIR  . '/lib/node_modules/.bin/mjml');

        //We do not have IDs for the root datalinks yet
        $root_ids = array();
        foreach($this->datalinks->links as $link_id => $datalink) {
            $root_id = $datalink->get_example_id();
            $datalink->db_id = $root_id;
            $datalink->populate_data($root_id, $link_id);

        }


        $gjs_html = get_post_meta($_REQUEST['mail_id'] , 'gjs-html', true);
//    $gjs_css = get_post_meta($_REQUEST['mail_id'] , 'gjs-css', true);

        $doc = new DOMWrap\Document();
        $doc->html($gjs_html);
        $doc = $this->resolve_html($doc);
        /** Before we parse for variables, we need to ready the HTML **/
        $test = 1;



        $html = $renderer->render($doc->find('body')->html());

        $handle = fopen(ARK_MAIL_COMPOSER_ROOT_DIR . '/testhtml.html','w+');
        fwrite($handle,$html); fclose($handle);


//    $html = stripcslashes($_REQUEST['html']);
//    $recipient = $_REQUEST['recipient'];
//    $subject = 'SUBJECT';
//    $headers = array("Content-Type: text/html; charset=UTF-8 \r\n");
//    $success = wp_mail( $recipient, $subject, $html, $headers );

        $success = true;
        if($success) {
            wp_send_json_success(array(
                'msg' => 'success'
            ));
        }
        else {
            wp_send_json_error(array(
                'msg' => 'error'
            ));
        }



    }

}

return new Mail_CPT_AJAX();