<?php
/**
 * @var $header
 * @var $name
 * @var $values
 */
?>

<div class="secondary_form">
    <h2><?php echo $header; ?></h2>
    <div class="form_inputs_checkboxes">
        <?php foreach($values as $value): ?>
            <div>
                <input type="radio"  name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
                <label><?php echo $value; ?></label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
