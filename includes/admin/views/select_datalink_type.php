<?php
/**
 * @var $possible_link_types
 */

$test = 1;
?>

<select id="dialog_add_datalink_type_selector">
    <?php foreach($possible_link_types as $link) :?>
        <option value='<?php echo json_encode($link) ?>'><?php echo $link['link_name']; ?></option>
    <?php endforeach; ?>
</select>