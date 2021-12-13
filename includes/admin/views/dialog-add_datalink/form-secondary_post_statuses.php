<?php
/**
 * @var $post_statuses
 */
?>

<div class="secondary_form">
    <h2>Post status</h2>
    <div id="dialog_add_datalink_inputs">
        <?php foreach($post_statuses as $post_status): ?>
            <?php $test = 1; ?>
            <label><?php echo $post_status; ?></label>
            <input type="checkbox"  name="post_status" value="<?php echo $post_status; ?>"/>
        <?php endforeach; ?>
    </div>
</div>
