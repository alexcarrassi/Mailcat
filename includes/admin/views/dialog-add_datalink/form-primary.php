<?php
/**
 * @var $possible_link_types
 */

?>
<h2>Primary</h2>
<div id="dialog_add_datalink_inputs">
    <div>
        <label for="dialog_add_datalink_type_selector">Link type </label>
        <select id="dialog_add_datalink_type_selector" name="primary_selection">
            <?php foreach($possible_link_types as $parent_type => $optgroup_parenttype) :?>
                <optgroup label="<?php echo $parent_type; ?>" class="parenttype">

                    <?php if($parent_type == "Miscellaneous"): ?>   <?php //The miscellaneous group works slightly different ?>
                        <?php foreach($optgroup_parenttype as $option):
                            $type = $option['data']['type']; unset($option['data']['type']);
                            ?>

                            <option class="option_misc" value='<?php echo $type; ?>'
                                <?php foreach($option['data'] as $key => $value):  //Rendering the dataset
                                    $value = json_encode($value) ;
                                    echo "data-$key =  $value ";
                                endforeach; ?>
                            ><?php echo $option['display_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach($optgroup_parenttype as $optgroup) :?>
                            <optgroup label="<?php echo $optgroup['display_name']; ?>" class="subtype">
                                <?php foreach($optgroup['selection'] as $option):
                                    $type = $option['data']['type']; unset($option['data']['type']);
                                    ?>

                                    <option value='<?php echo $type; ?>'
                                        <?php foreach($option['data'] as $key => $value):  //Rendering the dataset
                                            $value = json_encode($value) ;
                                            echo "data-$key =  $value ";
                                        endforeach; ?>
                                    ><?php echo $option['display_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </optgroup>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="dialog_add_datalink_description">
            Description
        </label>
        <input type="text" id="dialog_add_datalink_description" placeholder="My new link"/>
    </div>
</div>



