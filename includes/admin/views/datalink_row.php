<?php
/**
 * @var $datalink_node - The datalink node to render ;
 * @var $depth - The current recursion node
 * @var $id - ID of the datalink node
 */
?>


<li>

    <div class="datalink_row <?php echo $depth == 0 ? "root" : ""; ?> <?php echo  $datalink_node->many ? "to_many" : ""; ?>"
         data-link_name="<?php echo $datalink_node->name; ?>"
         data-link_type="<?php echo $datalink_node->type; ?>"
         data-depth="<?php echo $depth; ?>"
         data-id="<?php echo $id; ?>">

    <div class="datalink_name">
        <span>
            <?php if( $datalink_node->many ) : ?>
                <strong>[..]</strong>
            <?php endif; ?>

            <strong>
                <?php echo $datalink_node->name; ?>
            </strong>

            <?php if(isset($datalink_node->desc)): ?>
                <i><?php echo $datalink_node->desc; ?></i>
            <?php endif; ?>
        </span>
    </div>

    <a class="datalink_btn_vars datalink_btn">

        <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/icons8-file.svg'); ?>

    </a>
    <a class="datalink_btn_delete datalink_btn">
        <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/icons8-trash.svg'); ?>

    </a>
    <a class="datalink_btn_add datalink_btn thickbox" href="#TB_inline?&width=800&height=900&inlineId=dialog_add_datalink_">
            <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/icons8-plus-+.svg'); ?>
    </a>
</div>
    <?php
        if(count($datalink_node->links) > 0) {
            $i = 1;
            ?> <ul> <?php
                foreach($datalink_node->links as $id =>  $datalink_node) {
                    include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/datalink_row.php");
                    $i += 1;
                }
            ?> </ul> <?php
        }
    ?>
</li>
