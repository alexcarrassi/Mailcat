<?php
/**
 * @var $taxonomy_spec
 */


$test = 1;
 ?>

<div class="secondary_form">
    <h2><?php echo $taxonomy_spec['display_name']; ?></h2>
    <div class="form_inputs_checkboxes">
        <?php foreach($taxonomy_spec['terms'] as $term): ?>
            <div>
                <input id="checkbox_tax<?php echo $term->name; ?>" type="checkbox"  name="taxonomy:<?php echo $taxonomy_spec['taxonomy']; ?>[]" value="<?php echo $term->slug; ?>"/>
                <label for="checkbox_tax<?php echo $term->name; ?>" ><?php echo $term->name; ?></label>
            </div>
        <?php endforeach; ?>
    </div>
</div>


