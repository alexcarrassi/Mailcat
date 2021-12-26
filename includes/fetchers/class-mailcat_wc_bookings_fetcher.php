<?php


/** For Woocommerce Bookings */
class WC_Bookings_DataFetcher {
    public function __construct() {
        /** Data **/
        add_filter('mc-get_data-wc_booking', array($this, 'get_wc_booking_data'), 10, 2);
        add_filter('mc-get_data-wp_users-wc_booking', array($this, 'user_booking_data'), 10, 2);


        /** Example Ids **/

        add_filter('mc-get_example_id-wc_booking', array($this, 'get_wc_booking_example_id'), 10, 1 );
        add_filter('mc-get_example_id-bookable_person', array($this, 'get_bookable_person_example_id'), 10, 1 );
        add_filter('mc-get_example_id-bookable_person', array($this, 'get_bookable_resource_example_id'), 10, 1 );


        /** ACF **/
//        add_filter( 'acf/location/rule_match', array( $this, 'rule_match' ), 50, 3 );


        /** Possible DataLinks **/
        add_filter("mc-get_primary_datalink_selection-posts", array($this, 'primary_posts_selection'), 10, 1);

        /** Possible Datalinks secondary **/
//        add_filter("mc-get_secondary_datalink_selection-posts", array($this, 'secondary_posts_selection'), 10, 1);
    }

    /** Data **/

    public function user_booking_data($datalink_node, $booking_id) {
        $datalink_node->data['customer_data'] = (array)get_wc_booking($booking_id)->get_customer();

        return $datalink_node;        //get_wc_booking($booking_id)->get_customer();


    }
    public function get_wc_booking_data($datalink_node, $id) {
        $datalink_node->data['wc_booking_data'] = array(
            'display_name' => 'Booking data',
            'data' => array('test' => 1)
        );

        ACF_DataFetcher::get_data($datalink_node, acf_get_field_groups(array('post_type' => 'wc_booking')), $id);

        $test2 = 1;
    }

    /** Example Ids **/
    public function get_wc_booking_example_id($example_id) {
        global $wpdb;

        $sql = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = %s ORDER BY RAND() LIMIT 1";
        $example_id =  $wpdb->get_var($wpdb->prepare($sql, array("wc_booking")));

        return $example_id;
    }
    public function get_bookable_person_example_id($example_id) {
        global $wpdb;

        $sql = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = %s ORDER BY RAND() LIMIT 1";
        $example_id =  $wpdb->get_var($wpdb->prepare($sql, array("bookable_person")));

        return $example_id;
    }
    public function get_bookable_resource_example_id($example_id) {
        global $wpdb;

        $sql = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = %s ORDER BY RAND() LIMIT 1";
        $example_id =  $wpdb->get_var($wpdb->prepare($sql, array("bookable_resource")));

        return $example_id;
    }


    /** Possible DataLinks **/

    public function primary_posts_selection($primary_selection ) {

        /** Find the 'Bookable Resource' and 'Booking' post types, add them to the WooCommerce selection remove the post_type from consideration **/
        if(!isset($primary_selection['woo_posts'])) {
            $primary_selection['woo_posts'] = array(
                'display_name' => __('Woocommerce posts', 'mailcat'),
                'selection' => array()

            );
        }


        $selection = array_reduce(
            $primary_selection['post_types'],

            function($acc, $item) {
                if( in_array( $item->name, array("bookable_resource", "wc_booking") ) ) {
                    $selection = array(
                        'data' => array('type' => 'post', 'post_type' => $item->name) ,
                        'display_name' => $item->label
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
                if( !in_array($item->name, array("bookable_resource", "wc_booking") ) ) {
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

        foreach($taxonomies as $name => $taxonomy_spec) {
            if(in_array($name, $this->standard_taxonomies) && !empty($taxonomy_spec['terms'])) {
                if($name != "product_type") { //Product type is a primary selection taxonomy
                    /** Creating the form **/
                    include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_taxonomy_checkboxes.php");
                }

                /** Remove the taxonomy from the spec **/
                unset($taxonomies[$name]);
            }
        }
        $link_spec['taxonomies'] = $taxonomies;


        return $link_spec;

    }



    /** ACF **/
    public function rule_match($match, $rule, $screen)
    {
        /** We only want ACF to reconsider it's prior falsy decision **/
        if ($match == false) {

            if (isset($screen['post_type'])) {
                if ($screen['post_type'] == 'wc_booking' && $rule['value'] == "product_type:booking") {
                    return true;
                }
            }
            return $match;
        }
    }

}
new WC_Bookings_DataFetcher();
