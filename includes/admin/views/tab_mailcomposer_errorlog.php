<div id="tab_errorlog" class="mail_composer_tab">
    <div class="errorlog_main">

    <?php if($this->errors == null) : ?>
        <h3>
            <?php _e("There are no unresolved errors for this mail!", "mailcat"); ?>
        </h3>
    <?php else: ?>
        <?php
            $id_errors = isset($this->errors['id']) ? $this->errors['id'] : null;
            $render_errors = isset($this->errors['render']) ? $this->errors['render'] : null;
        ?>


        <div class="errorlog_header">
            <h3>
                <?php _e("Unfortunately, MailCat has encountered some errors", "mailcat"); ?>
            </h3>
        </div>

        <div class="errorlog_divider">
            <?php if($id_errors != null) : ?>
                <?php $count_id_errors = count($id_errors); ?>

                <div id="errorlog_id_errors">
                    <div class="errorlog_errors_header">
                        <h3>ID errors <span id="id_errors_count"> : <?php echo count($id_errors); ?> </span></h3>
                    </div>
                    <div class="errorlog_errors_expl">
                        <?php _e("These errors pertain to the fetching of datasets for your e-mails. The Ids of these datasets were either invalid or completely missing.", "mailcat"); ?>
                        <br>
                    </div>

                    <div class="errorlog_errors_container">


                        <?php foreach($id_errors as $index => $id_error):?>
                        <?php $index = $index + 1; ?>
                        <div class="single_error_report <?php echo $index == 1 ? "active_errorlog" : "";?>" id="errorlog_iderror_<?php echo $index; ?>" >
                            <div class="errorlog_row">
                                <div class="errorlog_report_label">
                                    <?php _e("Originated from", "mailcat"); ?>
                                </div>
                                <div class="errorlog_report_result">
                                    <?php echo errorlog_construct_origin_string( $id_error['origin'] ) ;?>
                                </div>
                            </div>

                            <div class="errorlog_row">
                                <div class="errorlog_report_label">
                                    <?php _e("Last occured at"); ?>
                                </div>
                                <div class="errorlog_report_result">
                                    <?php echo $id_error['last_occured']; ?>
                                </div>
                            </div>


                            <?php if(isset($id_error['data']['missing_ids'])) : ?>
                            <div class="errorlog_row">
                                <div class="errorlog_report_label">
                                    <?php _e("Missing ids"); ?>
                                </div>
                                <div class="errorlog_report_result">
                                    <?php echo implode(", ", $id_error['missing_ids']); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if(isset($id_error['data']['invalid_ids'])) : ?>
                            <div class="errorlog_row">
                                <div class="errorlog_report_label">
                                    <?php _e("Invalid ids", "mailcat"); ?>
                                </div>
                                <div class="errorlog_report_result">
                                    <?php foreach($id_error['data']['invalid_ids'] as $id_name => $invalid_id): ?>
                                    <div>
                                        <strong><?php echo $id_name; ?> </strong> =>
                                        <i><?php echo $invalid_id == "" ? "Empty string!" : $invalid_id; ?> </i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                            </div>
                            <?php endif; ?>
                            <div class="rectification_form">
                                <header>
                                    <h3><?php _e("Rectification form", "mailcat"); ?></h3>
                                    <div>
                                        <i><?php _e('You can use this form to rectify cancelled mails caused by faulty ids. Fill in the ids and send the email to specified recipients', "mailcat"); ?> </i>
                                    </div>
                                </header>
                                <div class="rectification_form_fields">
                                    <div class="rectification_form_id_fields">
                                        <div class="rectification_form_fields_header">
                                            <div class="rectification_form_fields_header_title"><?php _e("Ids", "mailcat"); ?></div> <br>
                                            <i><?php _e("Fill in the the Ids that should've been used for this email", "mailcat"); ?></i>

                                        </div>

                                        <?php if(isset($id_error['data']['valid_ids'])):
                                            foreach($id_error['data']['valid_ids'] as $key => $value) : ?>
                                                <div class="errorlog_row">
                                                    <label class="errorlog_report_label"><?php echo $key; ?></label>
                                                    <input type="number" name="<?php echo $key; ?>" value="<?php echo $value; ?>" placeholder="<?php echo __("Enter id value for ") . $key?>" class="errorlog_report_result" required/>
                                                </div>

                                            <?php endforeach;
                                        endif; ?>

                                        <?php if(isset($id_error['data']['invalid_ids'])):
                                            foreach($id_error['data']['invalid_ids'] as $key => $value) : ?>
                                                <div class="errorlog_row">
                                                    <label class="errorlog_report_label"><?php echo $key; ?></label>
                                                    <input type="number" name="<?php echo $key; ?>" placeholder="<?php echo __("Enter id value for ") . $key?>" class="errorlog_report_result" required/>
                                                </div>
                                            <?php endforeach;
                                        endif; ?>

                                        <?php if(isset($id_error['data']['missing_ids'])):
                                            foreach($id_error['data']['missing_ids'] as $key => $value) : ?>
                                                <div class="errorlog_row">
                                                    <label class="errorlog_report_label"><?php echo $value; ?></label>
                                                    <input type="number" name="<?php echo $value; ?>" placeholder="<?php echo __("Enter id value for ") . $value?>" class="errorlog_report_result" required/>
                                                </div>
                                            <?php endforeach;
                                        endif; ?>
                                    </div>

                                    <div class="rectification_form_mailing_list" >
                                        <div class="rectification_form_fields_header">
                                            <div class="rectification_form_fields_header_title"><?php _e("Recipients","mailcat"); ?></div>
                                            <i><?php _e("Listed here are all the mail addresses that did not receive your mail due to above errors. All checked recipients will receive this rectification mail"); ?></i>
                                        </div>


                                        <div class="errorlog_row">
                                            <input type="checkbox" class="checkbox_check_all">
                                            <label> <strong><?php _e("Select all", "mailcat"); ?> </strong></label>
                                        </div>
                                        <div class="rectification_recipient_list">
                                            <?php foreach($id_error['recipient_list'] as $index => $recipient) : ?>
                                                <div class="errorlog_row">
                                                    <input type="checkbox" name="recipient_list[]" value="<?php echo $recipient?>">
                                                    <label> <strong><?php echo $recipient; ?> </strong></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="rectification_form_button">
                                    <button type="button" class="btn_send_rectification">Send</button>
                                </div>

                            </div>

                        </div>
                        <?php endforeach; ?>

                        <div class="paginator">
                            <button type="button" class="btn_errorlog_back" disabled><</button>

                            <?php foreach($id_errors as $index => $id_error) : ?>
                                <?php $index = $index + 1; ?>
                                <button type="button" class="btn_errorlog_numbered <?php echo $index == 1 ? "btn_id_errorlog_pag_active" : ""; ?>" data-target="errorlog_iderror_<?php echo $index ?>"> <?php echo $index; ?> </button>
                            <?php endforeach; ?>

                            <button type="button" class="btn_errorlog_next" <?php echo count($id_errors) == 1 ? "disabled" : ""; ?> >></button>
                        </div>
                    </div>

                    <?php
                        /**  Get the first error
                             Deduce all; the different ids
                         *   Make inputs for them
                         *   fill in what was filled in
                         */
                    $error = $id_errors[0];
    //                $ids = array();
    //                if(isset($error['invalid_ids']))

    $test = 1;
                    ?>

                    <div id="id_inputs" class="id_inputs">

                    </div>




                    <strong> <?php echo count($id_errors); ?> </strong>
                </div>
            <?php endif; ?>

            <?php if($render_errors != null) : ?>
                <div id="errorlog_render_errors">
                    <div class="errorlog_errors_header">
                        Rendering errors

                    </div>
                    <div class="errorlog_errors_expl">
                        <?php _e("These errors occur during rendering of your mail templates.", "mailcat"); ?>
                        <br>
                    </div>
                    <strong> <?php echo count($render_errors); ?> </strong>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>
</div>

