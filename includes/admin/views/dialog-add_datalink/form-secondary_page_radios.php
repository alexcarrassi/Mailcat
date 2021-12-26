<?php
/**
 * @var $page_templates
 * @var $pages
 */

$test = 1;
?>

<div class="secondary_form">
    <h2>Page</h2>
    <div class="form_inputs_checkboxes">
        <?php foreach($pages as $page): ?>
            <div>
                <input type="radio"  id="page<?php echo $page->ID; ?>" name="page_id" value="<?php echo $page->ID; ?>"/>
                <label for="page<?php echo $page->ID;?>"><?php echo $page->post_title; ?></label>
            </div>
        <?php endforeach; ?>
    </div>
</div>