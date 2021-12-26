<?php

/** For Woocommerce */
class WC_DataFetcher {
    private $standard_taxonomies = ["product_cat", "product_type", "product_visibility", "product_tag"];
    private $standard_post_types = ["product", "shop_order"];

    public function __construct() {
        /** Data **/
        add_filter('mc-get_data-product', array($this, 'get_product_data'), 10, 2);

        /** Example Ids */
        add_filter('mc-get_example_id-product', array($this, 'get_product_example_id'), 10, 2 );
        add_filter('mc-get_example_id-shop_order', array($this, 'get_shop_order_example_id'), 10, 2 );
        add_filter('mc-get_example_id-shop_order', array($this, 'get_review_example_id'), 10, 2 );


        /** Possible DataLinks primary **/
        add_filter("mc-get_primary_datalink_selection-posts", array($this, 'primary_posts_selection'), 10, 1);
        add_filter("mc-get_primary_datalink_selection-tax", array($this, 'primary_tax_selection'), 10, 1);
        add_filter("mc-get_primary_datalink_selection-users", array($this, 'primary_users_selection'), 10, 1);


        /** Possible Datalinks secondary **/
        add_filter("mc-get_secondary_datalink_selection-posts", array($this, 'secondary_posts_selection'), 10, 1);
        add_filter("mc-get_secondary_datalink_selection-tax", array($this, 'secondary_taxonomy_selection'), 10, 1);

    }



    /** Possible DataLinks **/
    public function primary_users_selection($primary_selection) {


        /** Find the 'Product' and 'Shop Order' post types, add them to the WooCommerce selection remove the post_type from consideration **/
        if(!isset($primary_selection['woo_users'])) {
            $primary_selection['woo_users'] = array (
                'display_name' => __('Woocommerce users', 'mailcat'),
                'selection' => array()

            );
        }

        $selection = array_reduce(
            $primary_selection['user_types'],

            function($acc, $item) {

                if( in_array($item->name, array("shop_manager", "customer") ) ) {
                    $selection = array(
                        'data' => array('type' => 'user', 'role' => $item->name),
                        'display_name' => ucfirst(implode(" ", explode("_", $item->name)))
                    );

                    $acc[] = $selection;
                }

                return $acc;
            },

            []
        );

        $primary_selection['woo_users']['selection'] = array_merge($primary_selection['woo_users']['selection'], $selection);



        /** Remove post types if necessary **/
        $primary_selection['user_types'] = array_reduce(
            $primary_selection['user_types'],
            function($acc, $item) {
                if( !in_array($item->name, array("shop_manager", "customer") ) ) {
                    $acc[] = $item;
                }

                return $acc;
            },
            []
        );






        return $primary_selection;
    }
    public function primary_posts_selection($primary_selection ) {

        /** Find the 'Product' and 'Shop Order' post types, add them to the WooCommerce selection remove the post_type from consideration **/
        if(!isset($primary_selection['woo_posts'])) {
            $primary_selection['woo_posts'] = array (
                'display_name' => __('Woocommerce posts', 'mailcat'),
                'selection' => array()

            );
        }

        $selection = array_reduce(
            $primary_selection['post_types'],

            function($acc, $item) {

                if( in_array($item->name, $this->standard_post_types )) {
                    $selection = array(
                        'data' => array('type' => 'post', 'post_type' => $item->name),
                        'display_name' => $item->label != false ? $item->label :  ucfirst(implode(" ",explode("_", $item->name)))

                    );

                    $acc[] = $selection;
                }

                return $acc;
            },
            []
        );



        $primary_selection['woo_posts']['selection'] = array_merge($primary_selection['woo_posts']['selection'], $selection);



        /** Remove post types if necessary **/
        $primary_selection['post_types'] = array_reduce(
            $primary_selection['post_types'],
            function($acc, $item) {
                if( !in_array($item->name, $this->standard_post_types ) ) {
                    $acc[] = $item;
                }

                return $acc;
            },
            []
        );



        return $primary_selection;
    }
    public function primary_tax_selection($primary_selection) {

        if(!isset($primary_selection['woo_tax'])) {
            $primary_selection['woo_tax'] = array(
                'display_name' => __("Woocommerce taxonomies", "mailcat"),

                'selection' => array()
            );
        }

        $selection = array_reduce(
            $primary_selection['taxonomies'],
            function($acc, $item) {
                if(in_array( $item->name, ["product_type", "product_visibility", "product_cat", "product_tag", "product_shipping_class"])) {

                    $selection = array(
                        'data' => array('type'=> 'taxonomy', 'taxonomy' => $item->name),
                        'display_name' => $item->label != false ? $item->label :  ucfirst(implode(" ",explode("_", $item->name)))
                    );

                    $acc[] = $selection;
                }
                return $acc;

            },
            []
        );
        $primary_selection['woo_tax']['selection'] = array_merge($primary_selection['woo_tax']['selection'], $selection);

        /** Remove taxonomies if necessary **/
        $primary_selection['taxonomies'] = array_reduce(
            $primary_selection['taxonomies'],
            function($acc, $item) {
                if( !in_array($item->name, ["product_type", "product_visibility", "product_cat", "product_tag", "product_shipping_class"] ) ) {
                    $acc[] = $item;
                }

                return $acc;
            },
            []
        );



        return $primary_selection;
    }


    public function secondary_posts_selection($link_spec) {
        /**
         * product_cat et all,
         */

        $taxonomies = $link_spec['taxonomies'];


        //
//        $selection = array_reduce(
//
//            get_terms(array('taxonomy' => 'product_type', 'hide_empty' => false)),
//
//            function($acc, $item) {
//
//                $selection = array(
//                    'data' => array('type' => 'post', 'post_type' => 'product', 'taxonomies' => ['product_type' => [$item->name]]),
//                    'display_name' => "Product (" . $item->name . ")"
//                );
//
//                $acc[] = $selection;
//
//                return $acc;
//            },
//
//            [array('display_name' => 'Shop order', 'data' => array('type' => 'post', 'post_type' => 'shop_order')),
//                array('display_name' => "Product (general)", 'data' => array('type' => 'post', 'post_type' => 'product'))                ]
//        );
//
//



        foreach($taxonomies as $name => $taxonomy_spec) {
            if(in_array($name, $this->standard_taxonomies) && !empty($taxonomy_spec['terms'])) {
                    /** Creating the form **/
                    if($name == 'product_type') {
                        /** Product types require radios */
                        $header = "Product type";
                        $values = array_map(function($item){return $item->slug; }, $taxonomy_spec['terms']);
                        include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_radios.php");
                    }
                    else {

                        include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_taxonomy_checkboxes.php");

                    }

                /** Remove the taxonomy from the spec **/
                unset($taxonomies[$name]);
            }
        }
        $link_spec['taxonomies'] = $taxonomies;


        return $link_spec;

    }
    public function secondary_taxonomy_selection($taxonomy_spec) {
        if(in_array($taxonomy_spec['taxonomy'], $this->standard_taxonomies) && !empty($taxonomy_spec['terms'])) {
            /** Creating the form **/
            include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_taxonomy_checkboxes.php");
        }
        return $taxonomy_spec;
    }


    /** Data **/
    public function get_product_data($datalink_node, $id) {
        $datalink_node->data['wc_product_data'] = array(
            'display_name' => 'Product data',
            'data' => array('test' => 1)
        );
    }

    /** Example Ids **/
    public function get_product_example_id($datalink_node, $example_id) {


//
//            $args = wp_parse_args( $args, array(
//                'status' => array( 'draft', 'pending', 'private', 'publish' ),
//                'type' => array_merge( array_keys( wc_get_product_types() ) ),
//                'parent' => null,
//                'sku' => '',
//                'category' => array(),
//                'tag' => array(),
//                'limit' => get_option( 'posts_per_page' ),
//                'offset' => null,
//                'page' => 1,
//                'include' => array(),
//                'exclude' => array(),
//                'orderby' => 'date',
//                'order' => 'DESC',
//                'return' => 'objects',
//                'paginate' => false,
//                'shipping_class' => array(),
//            ) );
//
        $product_type = isset($link_spec['product_type']) ? $link_spec['product_type'] : null;

//        if($product_type) {
//            switch($link)
//        }

        $test = new WC_Product();
        global $wpdb;

        $sql = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = %s ORDER BY RAND() LIMIT 1";
        $example_id =  $wpdb->get_var($wpdb->prepare($sql, array("product")));

        return $example_id;
    }
    public function get_shop_order_example_id($example_id) {
        global $wpdb;

        $sql = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = %s ORDER BY RAND() LIMIT 1";
        $example_id =  $wpdb->get_var($wpdb->prepare($sql, array("shop_order")));

        return $example_id;
    }
    public function get_review_example_id($example_id) {
        global $wpdb;

        $sql = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = %s ORDER BY RAND() LIMIT 1";
        $example_id =  $wpdb->get_var($wpdb->prepare($sql, array("review")));

        return $example_id;
    }



}
new WC_DataFetcher();
