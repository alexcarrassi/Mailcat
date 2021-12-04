
<?php

function mailcat_error_admin_notices() {
    //mailcat_id_errors
    //mailcat_render_errors

    $current_screen = get_current_screen();
    if($current_screen->post_type == "ark_mail") {
//    if($current_screen->post_type == MAILCAT_CPT_TYPE) {

            return;
    }

    $logged_errors = get_option('mailcat_errors');

    if(!empty($logged_errors)) {

        foreach($logged_errors as $mail_id => $errors) {
            $mail = get_post($mail_id);
            $error_count = 0;
            $error_count = isset($errors['render']) ? $error_count + count($errors['render']) : $error_count + 0;
            $error_count = isset($errors['id']) ? $error_count + count($errors['id']) : $error_count + 0;

            ?>
                <div class="notice notice-error">
                    <p>
                        <strong> <?php echo $error_count; ?></strong> <?php _e('unresolved errors have occured for your e-mail: ', 'mailcat'); ?> <a href="<?php echo get_edit_post_link($mail_id); ?>"> <?php echo $mail->post_title; ?></a>
                    </p>
                </div>
            <?php
        }
    }
}
add_action( 'admin_notices', 'mailcat_error_admin_notices' );