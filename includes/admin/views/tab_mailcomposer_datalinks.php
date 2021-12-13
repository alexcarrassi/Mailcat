<?php

/**
 * DataLinks tab on the Mail CPT
 *
 * This tab contains the table with each datalink, recursively rendered
 * Right next to it, is the Variables dropdown, in which the variable set and formatting configuration is displayed
 *
 * @var $possible_link_types - a list of distinct post types
 * @var $datalinks - The datalink root nodes of the current Mail CPT
 */

    add_thickbox();
?>

<div id="tab_datalinks" class="mail_composer_tab tab_active">
    <div class="table_datalinks">
        <div class="datalink_sticky_element">

        <div class="tab_header">
            Data Hierarchy
        </div>

            <div class="row_add_datalink_hierarchy">
                <a href="#TB_inline?&width=800&height=900&inlineId=dialog_add_datalink_" class="thickbox button button-primary button-large new_root_datalink">
                    + Add new hierarchy
                </a>

            </div>


            <div class="datalink_tree">
                <ul>
                    <?php
                        /** Recursively render each currently configured DataLinks **/
                        $depth = 0;
                        if(!empty($this->datalinks)) :
                            foreach($this->datalinks->links as  $id => $datalink_node) {
                                include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/datalink_row.php");
                            }
                        endif;
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="variable_set_display">
        <div class="tab_header">
            Variable sets
        </div>
        <div id="variable_sets">
            <?php /** Display variable sets of the first object **/

                /** IF IS ARRAY **/
                if(!empty($this->datalinks) && array_key_first($this->datalinks->links) != null) {
                    $first_key = array_key_first($this->datalinks->links);
                    $child = $this->datalinks->links[$first_key];

                    $object_id = $child->get_example_id();
                    $child->get_variable_sets();

                    Ark_Mail_CPT::render_Variable_sets($child->data, $child);
                }
            ?>
        </div>
    </div>


    <style>



    </style>

    <div id="dialog_add_datalink_" style="display:none">
        <input type="hidden" id="hidden_parent_link_type" />
        <div id="dialog_add_datalink">
            <div id="dialog_add_datalink_header">
                <div>
                    Add Datalink
                </div>
            </div>

            <div id="dialog_add_datalink_hierarchy_path">
                <div id="hierarchy_placeholder">
                    hierarchy path
                </div>
            </div>

            <div id="dialog_add_datalink_body">
                <div id="dialog_add_datalink_primary">
                    <?php include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-primary.php"); ?>
                </div>


                <div id="dialog_add_datalink_secondary">
                    <?php include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary.php"); ?>
                </div>

                <div id="variable_sets">
                    <h2>Variable sets</h2>
                    <div id="dialog_add_datalink_variable_sets">

                    </div>
                </div>
            </div>

            <div id="dialog_add_datalink_footer">
                <button type="button" class="submit_new_hierarchy button button-primary button-large">
                    Submit new hierarchy
                </button>
            </div>
        </div>
    </div>
</div>



