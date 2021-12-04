<div id="tab_errorlog" class="mail_composer_tab">
    <div class="errorlog_main">

    <h3>Error Log</h3>


    <?php if($this->errors == null) : ?>
        <p>
            <?php _e("There are no unresolved errors for this mail!", "mailcat"); ?>
        </p>
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
            <div class="errorlog_id_errors">
                <div class="errorlog_errors_header">
                    ID errors
                </div>
                <div class="errorlog_errors_expl">
                    <?php _e("These errors pertain to the fetching of datasets for your e-mails. The Ids of these datasets were either invalid or completely missing.", "mailcat"); ?>
                    <br>
                </div>

                <div class="errorlog_errors_container">
                    <?php foreach($id_errors as $id_error):?>
                    <div class="single_error_report">
                        <div class="errorlog_row">
                            <div class="errorlog_report_label">
                                <?php _e("Originated from", "mailcat"); ?>
                            </div>
                            <div class="errorlog_report_result">
                                <?php echo $id_error['origin'] ;?>
                            </div>
                        </div>

                        <div class="errorlog_row">
                            <div class="errorlog_report_label">
                                <?php _e("Occured at"); ?>
                            </div>
                            <div class="errorlog_report_result">
                                <?php echo $id_error['date_and_time']; ?>
                            </div>
                        </div>


                        <?php if(isset($id_error['missing_ids'])) : ?>
                        <div class="errorlog_row">
                            <div class="errorlog_report_label">
                                <?php _e("Missing ids"); ?>
                            </div>
                            <div class="errorlog_report_result">
                                <?php echo implode(", ", $id_error['missing_ids']); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(isset($id_error['invalid_ids'])) : ?>
                        <div class="errorlog_row">
                            <div class="errorlog_report_label">
                                <?php _e("Invalid ids", "mailcat"); ?>
                            </div>
                            <div class="errorlog_report_result">
                                <?php foreach($id_error['invalid_ids'] as $invalid_id): ?>
                                <div>
                                    <strong><?php echo key($invalid_id); ?> </strong> =>
                                    <i><?php echo $invalid_id[key($invalid_id)] == "" ? "Empty string!" : $invalid_id[key($invalid_id)]; ?> </i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>



                    </div>
                    <?php endforeach; ?>
                </div>

                <?php
                    /**  Get the first eerror
                         Deduce all; the different ids
                     *   Make inputs for them
                     *   fill in what was filled in
                     */
                ?>

                <div id="id_inputs" class="id_inputs">

                </div>




                <strong> <?php echo count($id_errors); ?> </strong>
            </div>

            <div class="errorlog_render_errors">
                <div class="errorlog_errors_header">
                    Rendering errors

                </div>
                <div class="errorlog_errors_expl">
                    <?php _e("These errors occur during rendering of your mail templates.", "mailcat"); ?>
                    <br>
                </div>
                <strong> <?php echo count($render_errors); ?> </strong>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>

