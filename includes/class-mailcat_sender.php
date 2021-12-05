<?php
require_once ARK_MAIL_COMPOSER_ROOT_DIR . '/lib/vendor/autoload.php';

/** Handles the Sending process */

/**
 * Class MailCat_Sender
 *
 * Datalinks are fetched from the Mail_CPT meta.
 */
class MailCat_Sender {
    private $datalinks;
    private $to_address;
    private $root_ids;
    private $renderer;
    private $mail_id;


    public function __construct($mail_id, $to_address = null, $root_ids = null) {
        $datalinks = get_post_meta($mail_id, 'datalink', true);
        $this->setMailId($mail_id);
        $this->setDatalinks($datalinks);
        $this->setToAddress($datalinks);
        $this->setRootIds($root_ids);
        $this->renderer = new \Qferrer\Mjml\Renderer\BinaryRenderer(ARK_MAIL_COMPOSER_ROOT_DIR  . '/lib/node_modules/.bin/mjml');
    }


    /** Getters and setters **/

    public function getMailId()
    {
        return $this->mail_id;
    }

    public function setMailId($mail_id): void
    {
        $this->mail_id = $mail_id;
    }

    public function getDatalinks()
    {
        return $this->datalinks;
    }

    public function setDatalinks($datalinks): void
    {
        $this->datalinks = $datalinks;
    }

    public function getToAddress()
    {
        return $this->to_address;
    }

    public function setToAddress($to_address): void
    {
        $this->to_address = $to_address;
    }

    public function getRootIds()
    {
        return $this->root_ids;
    }

    public function setRootIds($root_ids): void
    {
        $this->root_ids = $root_ids;
    }

    public function getRootId($key) {
        if(!isset($this->root_ids[$key])) {
            throw new Exception("missing id", 0);
        }

        $intval = intval($this->root_ids[$key]);
        if($intval == 0 ) {
            throw new Exception("invalid id", 1);
        }

        else {
            return $intval;
        }
    }

    /**
     *
     * We differentiate between 2 different kinds of errors:
     * 1. ID errors:
     *          Errors that occur when accessing data through a given ID. Missing/Invalid/Misc.
     * 2. Render errors:
     *          Errors that occur during the rendering of the HTML of a mail template
     */
    public function send_mail() {
        $errors = array();
        $valid_ids = array();

        foreach($this->datalinks->links as $link_id => $datalink) {
            try {
                $root_id = $this->getRootId($link_id);
                $datalink->db_id = $root_id;
                $datalink->populate_data($root_id, $link_id);

                $valid_ids[$link_id] = $root_id;
            }
            catch(Exception $e) {
                $errorcode = $e->getCode();
                if($errorcode == 0) {
                    /** ID was missing */
                    !isset($errors['missing_ids']) ? $errors['missing_ids'] = array() : "";
                    array_push($errors['missing_ids'], $link_id);
                }

                elseif($errorcode == 1) {
                    /** ID was invalid **/
                    !isset($errors['invalid_ids']) ? $errors['invalid_ids'] = array() : "";
                    $errors['invalid_ids'][$link_id] = $this->getRootIds()[$link_id];
                }

                else {
                    /** Different error **/
                    !isset($errors['other_errors']) ? $errors['other_errors'] = array() : "";
                    array_push($errors['other_errors'], array($link_id => array('msg' => $e->getMessage(), 'id' => $root_id)));
                }
            }
        }

        if(!empty($errors)) {
            $errors['valid_ids'] = $valid_ids;
            /** Abort, log errors **/
            $this->log_error($errors, 'id');
            return;
        }



        try {
            $gjs_html = get_post_meta($_REQUEST['mail_id'] , 'gjs-html', true);
//    $gjs_css = get_post_meta($_REQUEST['mail_id'] , 'gjs-css', true);

            $doc = new DOMWrap\Document();
            $doc->html($gjs_html);
            $doc = $this->resolve_html($doc);

            /** Before we parse for variables, we need to ready the HTML **/
            $html = $this->renderer->render($doc->find('body')->html());

            $handle = fopen(ARK_MAIL_COMPOSER_ROOT_DIR . '/testhtml.html','w+');
            fwrite($handle,$html); fclose($handle);

            throw new Exception("test!");
        }

        catch(Exception $e) {
            $this->log_error(array('msg' => $e->getMessage() ), 'render');
            return;
        }


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


    private function resolve_html($node) {

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


    /** Log errors to the database, so we can provide the user with opportunities to rectify any errors **/
    private function log_error($error_data, $error_kind) {
        $logged_errors = get_option("mailcat_errors");

        //Create the error array for this Mail Id, if it does not exist yet
        !isset($logged_errors[$this->mail_id]) ? $logged_errors[$this->mail_id] = array() : "";

        //Create the error kind array for this error array, if it does not exist yet
        !isset($logged_errors[$this->mail_id][$error_kind]) ? $logged_errors[$this->mail_id][$error_kind] = array() : "";


        $new_error = array();
        $new_error['data'] = $error_data;

                    /** Assembling context on the origin of the error **/
        $backtrace = debug_backtrace()[2];
        $origin = array();
        $origin['function'] = $backtrace['function'];
        $origin['line'] = $backtrace['line'];
        if(isset($backtrace['class'])) {
            $origin['class'] = $backtrace['class'];
        }
        $file = explode('\\', debug_backtrace()[1]['file']);
        $origin['file'] = $file[count($file) - 1];

        $new_error['origin'] = $origin;



        /** Check if the error has already occurred:
         *
         * Things that must be the same:
         *      function name
         *      class (if exists)
         *      missing_ids (if exists)
         *      invalid_ids (if exists)
         *      valid_ids   (if exists)
         *
         *      These must be in their own array, so we can basically say:
         *          error == error
         *
         *      Then, all we need is a separate array for the recipients.
         *      And, if the error is the same, we just add the to_address to the recipient list.
         *
         *
         *      if we want to add dates and times, we can add one more key value, last_datetime_occured.
         *      This is merely just an update with now()
         *
         **/


    /**
     * array(
     *  'data' => array (
     *      'missing_ids' => array(),
     *      'invalid_ids' => array(),
     *      'valid_ids'   => array()
     *  ),
     *  'origin' => array(
     *      'function' => 'name',
     *      'class'    =>   'name'
     *      'file'    =>    'name',
     *      'line'    =>    1222
     *  ),
     *  'last_occured => "datetime",
     *  'recipients' => array(
     *      '1@gmail.com',
     *      '2@gmail.com'
     *  )
     *
     * )
     *
     *
     */

        foreach($logged_errors[$this->mail_id][$error_kind] as $index => $error) {

            if($new_error['data'] == $error_data) {
                /** We have a match for the error data. Is the origin the same? */
                if($new_error['origin'] == $origin) {
                    /** The origin is the same. So really, this isn't something new, we just need to update the recipient list, and last_occured **/
                    array_push($error['recipient_list'], $this->getToAddress());
                    $error['last_occured'] = date("D M Y H:m");

                    $logged_errors[$this->mail_id][$error_kind][$index] = $error;
                    update_option("mailcat_errors", $logged_errors);

                    return;
                }
            }
        }

        /** If we made it here, then this is a completely new error **/


        $new_error['last_occured'] = date("D M Y H:m");
        $new_error['recipient_list'] = [$this->getToAddress()];
        array_push($logged_errors[$this->mail_id][$error_kind], $new_error);
        update_option("mailcat_errors", $logged_errors);
    }
}