<?php
/**
 * Main view of the Mail composer UI.
 */

?>

<link href="<?php echo ARK_MAIL_COMPOSER_ROOT_URI . "/lib/node_modules/grapesjs/dist/css/grapes.min.css"; ?>" rel="stylesheet"/>
<script src="<?php echo ARK_MAIL_COMPOSER_ROOT_URI . "/lib/node_modules/grapesjs/dist/grapes.min.js";  ?>"> </script>
<script src="<?php echo ARK_MAIL_COMPOSER_ROOT_URI . "/lib/node_modules/grapesjs-mjml/dist/grapesjs-mjml.min.js";  ?>"> </script>
<script src="<?php echo ARK_MAIL_COMPOSER_ROOT_URI . "/lib/ckeditor/ckeditor.js" ?>"></script>
<script src="<?php echo ARK_MAIL_COMPOSER_ROOT_URI . "/lib/node_modules/grapesjs-plugin-ckeditor/dist/grapesjs-plugin-ckeditor.min.js";  ?>"></script>
<script src="<?php echo ARK_MAIL_COMPOSER_ROOT_URI . "/lib/node_modules/spectrum-colorpicker/spectrum.js"; ?>"></script>


<div id="mail_composer_main">
    <div class="mail_composer_navbar nav-bar">
        <div class="nav-item nav-item_active" data-target="tab_datalinks">
            Datalinks
        </div>

        <div class="nav-item" data-target="tab_contentcreation">
            Content creation
        </div>

        <div class="nav-item" data-target="tab_scheduling">
            Scheduling
        </div>

        <div class="nav-item <?php echo $this->errors != null ? "errors_unresolved" : ""; ?>" data-target="tab_errorlog">
            Error log
        </div>
    </div>

    <div class="mail_composer_body">
        <?php include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/tab_mailcomposer_datalinks.php"); ?>
        <?php include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/tab_mailcomposer_contentcreation.php"); ?>
        <?php include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/tab_mailcomposer_scheduling.php"); ?>
        <?php include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/tab_mailcomposer_errorlog.php"); ?>


    </div>

    <div class="ghost">
        <div class="ghost_cp original_ghost">
            <input type="text" id="ghost_color_picker" />

        </div>
    </div>
</div>