<?php
/**
 * @var $set_name
 * @var $variables
 * @var $display_name
 * @var $formatting
 * @var $many
 * @var $var_formats
 */
?>

<!-- header-->
<div class="variable_set_title" data-target="variable_set_<?php echo strtolower(str_replace(" ", "_", $set_name));?>" data-set_name="<?php echo $set_name; ?>">
    <?php if ($many) {echo "MANY";} ?>
    <strong style="margin-top: 5px; font-size:1.1em; font-weight: bold"><?php echo $display_name; ?></strong>
    <!--                    <i style="margin-top: 5px; font-size:15px" class="changes_count">0 changes</i>-->
    <!--                    <button type="button" class="save_variables_settings" disabled>Save settings</button>-->
    <button type="button" class="mail_composer_display_toggle">
        <span class="mail_composer_toggle-indicator" ></span>
    </button>
</div>

<div class="collapsible-wrapper collapsed" id="variable_set_<?php echo strtolower(str_replace(" ", "_", $set_name)); ?>">
    <div class="collapsible">
        <div class="variables_container">

            <div class="variables_container_header datalink_variable">
                <div class="col header_var_name">
                    Variable
                </div>
                <?php if($formatting) : ?>
                    <div class="col header_formatting">
                        Formatting
                    </div>
                <?php endif; ?>

                <div class="col header_var_value">
                    Value
                </div>
            </div>

            <div class="variables_container_body">
                <?php $key_iterator = 0;?>
                <?php foreach($variables as $var_name => $var_value): ?>
                    <div class="datalink_variable <?php echo $key_iterator % 2 == 0 ? "uneven" : "";?>">
                        <div class="col var_name">
                            <div class="value">
                                <strong> <?php echo "{$var_name}"; ?></strong>
                            </div>
                        </div>
                        <?php if($formatting): ?>
                            <div class="col formatting">
                                <div class="list">
                                    <?php if(isset($var_formats[$var_name])) :?>
                                        <?php foreach($var_formats[$var_name] as $format_function) :?>
                                            <?php $test = 1; ?>
                                            <div class="format_func_container">
                                                <div class="format_func"
                                                     data-function_name="<?php echo $format_function['func_name']; ?>"
                                                     data-arguments='<?php echo $format_function['args']; ?>' draggable="true" >
                                                    <div class="remove_func">
                                                        X
                                                    </div>
                                                    <div class="func_name">
                                                        <?php echo $format_function['func_name'] . "(" . implode("," ,array_values(json_decode($format_function["args"], ARRAY_A))) . ")"; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="col var_value">
                            <div class="value" >
                                <?php print_r($var_value); ?>
                            </div>

                            <div class="original_value" style="display:none;">
                                <?php print_r($var_value); ?>
                            </div>
                        </div>
                    </div>

                    <?php if($formatting) : ?>
                        <span class="format_option_adder">
                            <div> </div>
                            <div>

                                <input type="text" class="format_searcher_input" placeholder="Enter a formatting function" />
                                <div class="format_options">

                                </div>

                            </div>
                            <div></div>

                        </span>
                    <?php endif; ?>

                    <?php $key_iterator += 1; ?>

                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>