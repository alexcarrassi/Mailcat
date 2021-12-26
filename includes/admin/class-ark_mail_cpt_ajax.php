<?php
/** Ark Mail CPT ajax functions **/
class Mail_CPT_AJAX {
    private Ark_DataLink $datalinks;

    public function __construct() {
        add_action("wp_ajax_render_example_variable_set", array($this, "render_variable_set"));
        add_action("wp_ajax_nopriv_render_example_variable_set", array($this,"render_variable_set"));

        add_action("wp_ajax_render_primary_datalink_form", array($this,"render_primary_datalink_form"));
        add_action("wp_ajax_nopriv_render_primary_datalink_form", array($this,"render_primary_datalink_form"));

        add_action("wp_ajax_render_secondary_selection_form", array($this,"render_secondary_selection_form"));
        add_action("wp_ajax_nopriv_render_secondary_selection_form", array($this,"render_secondary_selection_form"));


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


        add_action("wp_ajax_mailcat_rectify_id_error", array($this,"mailcat_rectify_id_error"));
        add_action("wp_ajax_nopriv_mailcat_rectify_id_error", array($this,"mailcat_rectify_id_error"));

        if(isset($_REQUEST['mail_id'])) {
            $this->mail_id = $_REQUEST['mail_id'];
            $this->datalinks = get_post_meta($this->mail_id , 'datalink', true);
        }
    }

    function render_variable_set() {

        $link_name = $_REQUEST['link_name'];

        /** Get configured formats **/
        $datalink = isset($_REQUEST['hierarchy_path']) ?
            $this->datalinks->get_child_by_path($_REQUEST['hierarchy_path'])
            :
            new Ark_DataLink(array('name' => $_REQUEST['link_name'], 'type' => $_REQUEST['link_type']));

        $datalink->get_example_id();
        $datalink->get_variable_sets();

        ob_start();
        Ark_Mail_CPT::render_Variable_sets($datalink->data, $datalink, $_REQUEST['formatting'] === "true", $datalink->many);
        wp_send_json_success(array('html' => ob_get_clean()));
    }

    function render_primary_datalink_form() {

        $datalink = new Ark_DataLink(array('name' => $_REQUEST['link_name'], 'type' => $_REQUEST['link_type']));

        /** Get the possible links types and render the primary selection form **/
        $possible_link_types = DataLink_Utils::get_all_possible_children($datalink);
        ob_start();
        include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-primary.php");
        $form_primary = ob_get_clean();

        /** Get the variable sets and render them */
        $datalink->get_variable_sets();

        wp_send_json_success(array(
            'html_form_primary' => $form_primary
        ));
    }

    function render_secondary_selection_form() {
        /** Preprocess  **/
        $link_spec = array(
            'link_type' => $_REQUEST['link_type'],
            'link_spec' => $_REQUEST['link_spec']
        );
        /** Get the variable sets and render them */

        $datalink = new Ark_DataLink(array('type' => $_REQUEST['link_type'], 'link_spec' => $_REQUEST['link_spec']));
        $datalink->get_example_id();
        $datalink->get_variable_sets();

        ob_start();
        DataLink_Utils::get_secondary_children_forms($link_spec);

        wp_send_json_success(array(
            'html' => ob_get_clean()
        ));
        $test = 1;
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



        $sender->setToAddress($_REQUEST['recipient']);
        $sender->setRootIds($_REQUEST['root_ids']);
        if($sender->send_mail()) {
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

    /** User wants to rectify ID errors by manually sending some emails **/
    function mailcat_rectify_id_error() {
        require_once ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/class-mailcat_sender.php";
        $sender = new MailCat_Sender($_REQUEST['mail_id']);

        $sender->setToAddress($_REQUEST['recipients']);
        $sender->setRootIds($_REQUEST['ids']);
        $sender->setIsRectifier(true);
        $sender->setErrorToRectify(array(
            'error_kind' => $_REQUEST['error_kind'],
            'error_index' => $_REQUEST['error_index'],
        ));


        if($sender->send_mail()) {

            /** Rerender the Error tab **/
            $this->errors = $sender->getErrors();
            $this->errors = $this->errors[$this->mail_id] ?? null;


            ob_start();
            include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/tab_mailcomposer_errorlog.php");
            $errortab_html = ob_get_clean();

            ob_start();
            render_errortab_badge($this->errors);
            $errortab_badge = ob_get_clean();


            wp_send_json_success(array(
                'errortab_html' => $errortab_html,
                'errortab_badge' => $errortab_badge,
                'msg' => 'Rectification succeeded. The mail has been sent to the recipient(s)'
            ));
        }
        else {
            $errors = $sender->getErrors();
            $message = "There were errors in your mail: \n\n";
            foreach($errors as $kind => $error) {
                switch($kind) {
                    case "invalid_ids":
                        $message .= "Invalid ids: \n";
                        foreach($error as $key => $invalid_id) {
                            $message .= "$key - $invalid_id";
                        }
                        break;
                }
            }


            $message .= "\n\nDon't worry, we did not send the mail.";

            wp_send_json_error(array(

                'msg' => $message
            ));
        }

    }

}

return new Mail_CPT_AJAX();