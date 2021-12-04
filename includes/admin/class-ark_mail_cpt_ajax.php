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




    function direct_mail() {

        /**
         * TODO: Get Root Ids and To address from UI ( $_REQUEST['..'] )
         **/
        require_once ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/class-mailcat_sender.php";
        $sender = new MailCat_Sender($_REQUEST['mail_id']);

        //We do not have IDs for the root datalinks yet
        $root_ids = array();
        foreach($this->datalinks->links as $link_id => $datalink) {
            $root_id = $datalink->get_example_id();
            $root_ids[$link_id] = $root_id;
        }
//        $root_ids[array_key_last($root_ids)]= "";

        $sender->setToAddress("aazoutewelle@gmail.com");
        $sender->setRootIds($root_ids);
        $sender->send_mail();
    }

}

return new Mail_CPT_AJAX();