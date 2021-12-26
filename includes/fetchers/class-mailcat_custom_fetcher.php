<?php
/**
 * Handles all undefined functionalities
 * Meaning: If the objects aren't featured in standard WordPress or any of the known plugins, we handle it here
 */
class Custom_DataFetcher {
    public function __construct() {
        add_filter("mc-get_primary_datalink_selection-posts", array($this, 'primary_posts_selection'), 50, 1);
        add_filter("mc-get_primary_datalink_selection-tax", array($this, 'primary_tax_selection'), 50, 1);
        add_filter("mc-get_primary_datalink_selection-users", array($this, 'primary_users_selection'), 50, 1);
    }

    /** Possible DataLinks primary **/
    public function primary_posts_selection($primary_selection) {
        /** foreach post type, get the ones that are relevant and organize **/
        if(!isset($primary_selection['posts_custom'])) {
            $primary_selection['posts_custom'] = array(
                'display_name' => __("Custom posts", "mailcat"),
                'selection' => array_reduce(
                    $primary_selection['post_types'],
                    function($acc, $item) {
                        $selection = array(
                            'data' => array('type' => 'post', 'post_type' => $item->name),
                            'display_name' => $item->label != false ? $item->label :  ucfirst(implode(" ",explode("_", $item->name)))
                        );

                        $acc[] = $selection;

                        return $acc;
                    },
                    []
                )
            );
        }

        unset($primary_selection['post_types']);

        return $primary_selection;
    }
    public function primary_tax_selection($primary_selection) {
        if(!isset($primary_selection['tax_custom'])) {
            $primary_selection['tax_custom'] = array(
                'display_name' => __("Custom taxonomies", "mailcat"),
                'selection' => array_reduce(
                    $primary_selection['taxonomies'],
                    function($acc, $item) {

                        $selection = array(
                            'data' => array('type'=> 'taxonomy', 'taxonomy' => $item->name),
                            'display_name' => $item->label != false ? $item->label :  ucfirst(implode(" ",explode("_", $item->name)))
                        );

                        $acc[] = $selection;

                    },
                    []
                )
            );
        }

        unset($primary_selection['taxonomies']);

        return $primary_selection;

    }
    public function primary_users_selection($primary_selection) {

        if(!isset($primary_selection['users_custom'])) {
            $primary_selection['users_custom'] = array(
                'display_name' => __("Custom users", "mailcat"),
                'selection' => array_reduce(
                    $primary_selection['user_types'],
                    function($acc, $item) {

                        $selection = array(
                            'data' => array('type'=> 'user', 'role' => $item->name),
                            'display_name' => ucfirst($item->name)
                        );

                        $acc[] = $selection;

                        return $acc;
                    },
                    []
                )
            );
        }


        unset($primary_selection['user_types']);


        return $primary_selection;
    }




}
new Custom_DataFetcher();
