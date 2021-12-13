<?php
/**
 * @var $possible_link_types
 */

?>
<h2>Primary</h2>
<div id="dialog_add_datalink_inputs">
    <div>
        <label for="dialog_add_datalink_type_selector">Link type </label>
        <select id="dialog_add_datalink_type_selector">
            <?php foreach($possible_link_types as $optgroup) :?>
                <optgroup label="<?php echo $optgroup['display_name']; ?>">
                    <?php foreach($optgroup['selection'] as $option):
                        $type = $option['data']['type']; unset($option['data']['type']);
                        ?>

                        <option value='<?php echo $type; ?>'
                            <?php foreach($option['data'] as $key => $value):  //Rendering the dataset
                                $value = json_encode($value) ;
                                echo "data-$key =  $value ";
                            endforeach; ?>
                        ><?php echo $option['display_name']; ?></option>
                    <?php endforeach; ?>
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



