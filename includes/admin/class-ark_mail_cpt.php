<?php

class Ark_Mail_CPT {
    public $mail_id = null;
    private $datalinks = null;

    public function __construct() {

        if(is_admin()) {
            add_action('init', array($this, 'register_Ark_Mail_CPT'));
            add_action('init', array($this, 'includes'));

            if (isset($_REQUEST['mail_id'])) {
                $this->mail_id = $_REQUEST['mail_id'];
            }
            else if(isset($_GET['post'])) {
                $this->mail_id = $_GET['post'];
            }

            if($this->mail_id != null) {

                add_action('add_meta_boxes', array($this, 'add_DataLink_metabox'));
                add_action('add_meta_boxes', array($this, 'add_direct_mail_metabox'));
                add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

                add_filter('get_grapes_blocks', array($this, 'default_grapes_blocks'));
            }
        }

    }

    public function enqueue_scripts() {
        wp_enqueue_script('ark_mail_cpt_js', ARK_MAIL_COMPOSER_ROOT_URI . "/includes/admin/ark_mail_cpt.js", array(), '0.1', false);
        wp_enqueue_style( 'ark_mail_cpt_css', ARK_MAIL_COMPOSER_ROOT_URI . "/includes/admin/ark_mail_cpt.css" );


        $vars = $this->datalinks->gather_variables();
        wp_localize_script('ark_mail_cpt_js', 'ark_mail_cpt_config',
            array(
                    'ajax_url' => admin_url( 'admin-ajax.php'),
                    'media_endpoint' => admin_url('async-upload.php'),
                    'nonce_media_form' => wp_create_nonce( 'media-form' ),
                    'media_assets' => self::get_image_assets(),
                    'mail_id' => $this->mail_id,
                    'format_functions' => DataLink_Utils::formatting_functions_array(),
                    'datalinks' => $this->datalinks,
                    'variables' => $vars,
                    'msg' => array(
                            'format_func_argument_missing' => __('Format failed. You are missing an argument for: ', 'ark_mail_composer')
                    ),
                    'editor' => self::get_editor_config($this->mail_id)

            )
        );
    }

    public static function get_image_assets($mail_id = "") {
        $images = array();

        $attimages = get_attached_media('image', $mail_id);
        foreach ($attimages as $image) {
            $src = wp_get_attachment_image_src($image->ID, 'full');

            array_push($images, array(
                'type'  => 'image',
                'src'   =>  $src[0],
                'width' =>  $src[1],
                'height' => $src[2]
            ));

        }

        return $images;
    }

    public static function get_editor_config($mail_id = "") {
        /** Icons **/
        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/assets/icons/colorbucket.svg");
        $icon_color_bucket = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/assets/icons/img.svg");
        $icon_image = ob_get_clean();


        /** Special category **/
        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/special_category.php");
        $category = ob_get_clean();

        $html = empty(get_post_meta($mail_id, 'gjs-html', true)) ? "<mjml><mj-body></mj-body></mjml>"  : get_post_meta($mail_id, 'gjs-html', true);
        $components = empty(get_post_meta($mail_id, 'gjs-components', true)) ? "[]"  : get_post_meta($mail_id, 'gjs-components', true);

        return array(
            'icons' => array(
                    'icon-bg_color' => $icon_color_bucket,
                    'icon-image' => $icon_image
            ),
            'special_category' => $category,
            'blocks' => apply_filters('get_grapes_blocks', array()),
            'components' => get_post_meta($mail_id, 'gjs-components', true),
            'html' => $html,
            'css' => get_post_meta($mail_id, 'gjs-css', true),
            'styles' => get_post_meta($mail_id, 'gjs-styles', true),
            'assets' => get_post_meta($mail_id, 'gjs-assets', true),

        );

    }

    public function includes() {
        if(is_admin()) {
            include_once __DIR__ . "/class-ark_datalink.php";
            include_once __DIR__ . "/class-ark_mail_cpt_ajax.php";


//            $root = new Ark_DataLink( array( ) );
//
//            $booking = $root->add_child(
//                new Ark_DataLink(
//                    array(
//                        'type' => 'post',
//                        'name' => 'wc_booking',
//                        'desc' => 'Mijn Boeking',
//                        'many' => false
//                    )                )
//            );
//
//            $booking->add_child(
//                new Ark_DataLink(
//                    array(
//                        'type' => 'user',
//                        'name' => 'wp_users',
//                        'desc' => 'Boeker',
//                        'many' => false
//                    )
//                )
//            );
//
//            $booking->add_child(
//                new Ark_DataLink(
//                    array(
//                        'type' => 'comment',
//                        'name' => 'wp_comments',
//                        'desc' => 'Notities',
//                        'many' => true
//                    )
//                )
//            );
//
//            $root->add_child(
//                new Ark_DataLink(
//                    array(
//                        'type' => 'post',
//                        'name' => 'product',
//                        'desc' => 'Boeking product',
//                        'many' => false
//                    )
//                )
//            );
//
//            update_post_meta($this->mail_id, 'datalink', $root);

            /** Get the DataLinks for this post id **/
            $this->datalinks = get_post_meta($this->mail_id, 'datalink', true);
        }
    }

    /** Register the Mail post type **/
    public function register_Ark_Mail_CPT() {
        register_post_type(
            'ark_mail',
            array(
                'labels' => array(
                    'name'                => __('Mail', 'mail_composer'),
                    'singular_name'       => __('Mail', 'mail_composer'),
                    'menu_name'           => __( 'Mails', 'mail_composer' ),
                    'parent_item_colon'   => __( 'Parent Mail', 'mail_composer' ),
                    'all_items'           => __( 'Alle Mails', 'mail_composer' ),
                    'view_item'           => __( 'Check Mail', 'mail_composer' ),
                    'add_new_item'        => __( 'Add New Mail', 'mail_composer' ),
                    'add_new'             => __( 'Add New', 'mail_composer' ),
                    'edit_item'           => __( 'Edit Mail', 'mail_composer' ),
                    'update_item'         => __( 'Update Mail', 'mail_composer' ),
                    'search_items'        => __( 'Search Mail', 'mail_composer' ),
                    'not_found'           => __( 'Not Found', 'mail_composer' ),
                    'not_found_in_trash'  => __( 'Not found in Trash', 'mail_composer' ),

                ),
                'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', ),
                'hierarchical'        => false,
                'public'              => false,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => true,
                'menu_position'       => 99999,
                'show_in_admin_bar'   => true,
                'can_export'          => true,
                'has_archive'         => false,
                'exclude_from_search' => true,
                'publicly_queryable'  => true,
            )
        );

        remove_post_type_support( 'ark_mail', 'editor');
    }

    /** Adds the DataLink metabox **/
    public function add_DataLink_metabox() {
        add_meta_box(
            'DataLink_metabox',
            'DataLink',
            array($this, 'output_DataLink_metabox'),
            'ark_mail',
            'normal',
            'high'
        );
    }

    /** Adds the Direct Mail metabox **/
    public function add_direct_mail_metabox() {
        add_meta_box(
            'direct_mail_metabox',
            'Mail Directly',
            array($this, 'output_direct_mail_metabox'),
            'ark_mail',
            'normal',
            'high'
        );
    }

    public function output_DataLink_metabox() {
        $link_types = DataLink_Utils::get_available_link_types();
        $possible_link_types = DataLink_Utils::get_all_possible_children(null);

        $datalink = $this->datalinks;

        include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/mail_composer_main_view.php");
    }

    public function output_direct_mail_metabox() {
        ?>
        <div>
            <h3>Mail directly to: </h3>
            <input id="input_direct_mail" type="text"/>
            <button id="btn_direct_mail" type="button" >Mail</button>
        </div>
        <?php
    }




    /**
     * Function used to render each currently configured Datalink Rows
     *
     */
    public static function render_DataLink_rows($depth, $link_name, $datalink_config) {

        ?> <li><?php
        //Render the singular row for the current datalink
        include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/datalink_row.php");

        //If the current datalink has links nested within itself..
        if(count($datalink_config['links']) > 0) {
            $i = 1;
            ?> <ul> <?php

            foreach($datalink_config['links'] as $link_name =>  $nested_config) {

                self::render_DataLink_rows($depth + 1, $link_name, $nested_config);
                $i += 1;

            }
            ?> </ul> <?php

        }

        ?> </li><?php
    }

    public static function render_Variable_sets($variable_sets, $datalink, $formatting = true, $many = false) {
        foreach($variable_sets as $set_name => $set) {
                $variables = $set['data'];
                $display_name = $set['display_name'];

                $var_formats = array();
                if(isset($datalink->var_forms[$set_name])) {
                    $var_formats = $datalink->var_forms[$set_name];
                }
                $format_functions_array = DataLink_Utils::formatting_functions_array();
                include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/variable_set.php");
        }
    }



    public function default_grapes_blocks($blocks) {

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-1.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-1.php");
        $label = ob_get_clean();

        $blocks['1_column'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'resizable' => true,
            'category' => '1_column',
            'attributes' => array (
                'title' => __('1 Column', 'ark_mail_composer'),
                'class' => 'block_1_column default_block'
            )

        );



        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-2.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-2.php");
        $label = ob_get_clean();

        $blocks['2_column'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '2_column',
            'attributes' => array (
                'title' => __('2 Columns', 'ark_mail_composer'),
                'class' => 'block_2_column default_block'
            )
        );


        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-2_1x3.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-2_1x3.php");
        $label = ob_get_clean();

        $blocks['2_column_1x3'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '2_column',
            'attributes' => array (
                'title' => __('2 Column 1x3', 'ark_mail_composer'),
                'class' => 'gjs-block-section'
            )

        );


        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-2_1x2.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-2_1x2.php");
        $label = ob_get_clean();

        $blocks['2_column_1x2'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '2_column',
            'attributes' => array (
                'title' => __('2 Column 1x2', 'ark_mail_composer'),
                'class' => 'gjs-block-section'
            )
        );


        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-2_2x1.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-2_2x1.php");
        $label = ob_get_clean();

        $blocks['2_column_2x1'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '2_column',
            'attributes' => array (
                'title' => __('2 Column 2x1', 'ark_mail_composer'),
                'class' => 'gjs-block-section'
            )

        );

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-2_3x1.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-2_3x1.php");
        $label = ob_get_clean();

        $blocks['2_column_3x1'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '2_column',
            'attributes' => array (
                'title' => __('2 Column 3x1', 'ark_mail_composer'),
                'class' => 'gjs-block-section'
            )

        );





        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-3.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-3.php");
        $label = ob_get_clean();

        $blocks['3_column'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '3_column',
            'attributes' => array (
                'title' => __('3 Columns', 'ark_mail_composer'),
                'class' => 'block_3_column default_block'
            )
        );

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-3_1x1x2.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-3_1x1x2.php");
        $label = ob_get_clean();

        $blocks['3_column_1x1x2'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '3_column',
            'attributes' => array (
                'title' => __('3 Columns 1x1x2', 'ark_mail_composer'),
                'class' => 'gjs-block-section'
            )

        );

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-3_1x2x1.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-3_1x2x1.php");
        $label = ob_get_clean();

        $blocks['3_column_1x2x1'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '3_column',
            'attributes' => array (
                'title' => __('3 Columns 1x2x1', 'ark_mail_composer'),
                'class' => 'gjs-block-section'
            )

        );



        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-3_2x1x1.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-3_2x1x1.php");
        $label = ob_get_clean();

        $blocks['3_column_2x1x1'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '3_column',
            'attributes' => array (
                'title' => __('3 Columns 2x1x1', 'ark_mail_composer'),
                'class' => 'gjs-block-section'
            )
        );

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/column-4.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/column-4.php");
        $label = ob_get_clean();

        $blocks['4_column'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => '4_column',
            'attributes' => array (
                'title' => __('4 Columns', 'ark_mail_composer'),
                'class' => 'block_4_column default_block'
            )
        );




        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/divider.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/divider.php");
        $label = ob_get_clean();

        $blocks['divider'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => __('Basic Content', 'ark_mail_composer'),
            'attributes' => array (
                'title' => __('Divider Line', 'ark_mail_composer'),
                'class' => 'gjs-block-section'
            )
        );


        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/spacer.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/spacer.php");
        $label = ob_get_clean();

        $blocks['spacer'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => __('Basic Content', 'ark_mail_composer'),
            'attributes' => array (
                'title' => __('Empty space', 'ark_mail_composer'),
                'class' => 'gjs-block-section',
                'resizable'=> true

            )
        );

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/button.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/button.php");
        $label = ob_get_clean();

        $blocks['button'] = array(
            'select' => true,
            'activate' => true,
            'label' => $label,
            'content' => $content,
            'category' => __('Basic Content', 'ark_mail_composer'),
            'attributes' => array (
                'title' => __('Button', 'ark_mail_composer'),
                'class' => 'gjs-block-section',
                'resizable'=> true

            )
        );

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/image.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/image.php");
        $label = ob_get_clean();

        $blocks['image'] = array(
            'select' => false,
            'activate' => false,
            'label' => $label,
            'content' => $content,
            'category' => __('Basic Content', 'ark_mail_composer'),
            'attributes' => array (
                'title' => __('Image', 'ark_mail_composer'),
                'class' => 'gjs-block-section',
                'resizable'=> true

            )
        );


        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/text.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/text.php");
        $label = ob_get_clean();

        $blocks['text'] = array(
            'select' => false,
            'activate' => false,
            'label' => $label,
            'content' => $content,
            'category' => __('Basic Content', 'ark_mail_composer'),
            'attributes' => array (
                'title' => __('Text', 'ark_mail_composer'),
                'class' => 'gjs-block-section',
                'resizable'=> true
            )
        );

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/loop.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/loop.php");
        $label = ob_get_clean();

        $blocks['loop'] = array(
            'select' => false,
            'activate' => false,
            'label' => $label,
            'content' => $content,
            'category' => __('Basic Content', 'ark_mail_composer'),
            'attributes' => array (
                'title' => __('Loop', 'ark_mail_composer'),
                'class' => 'gjs-block-section',
                'resizable'=> true
            )
        );

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_content/navbar.php");
        $content = ob_get_clean();

        ob_start();
        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/grapesjs-blocks/grapesjs-blocks_label/navbar.php");
        $label = ob_get_clean();

        $blocks['navbar'] = array(
            'select' => false,
            'activate' => false,
            'label' => $label,
            'content' => $content,
            'category' => __('Basic Content', 'ark_mail_composer'),
            'attributes' => array (
                'title' => __('Navbar', 'ark_mail_composer'),
                'class' => 'gjs-block-section',
                'resizable'=> true
            )
        );




        return $blocks;
    }
}

return new Ark_Mail_CPT();