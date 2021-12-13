<?php
class DataLink_Utils {


    public static function get_datalink_variables() {
        return ["var"];
    }

    private static function get_post_types() {
        global $wp_post_types;

        $post_types = get_post_types(array('show_ui' => 1), 'objects');
        $to_return = [];
        $to_exclude = ["wp_block", "acf-field-group", "page"];

        foreach($post_types as $name => $post_type) {
            if( in_array($name, $to_exclude) ) continue;

            $to_return[] = $post_type;
        }

        return $to_return;
    }

    private static function get_taxonomies() {
        return get_taxonomies(array(), 'objects');
    }

    private static function get_user_types() {
        global $wp_roles;


        return wp_roles()->role_objects;
    }

    private static function get_post_term_relationships($link_name) {
        global $wpdb;

        $sql_taxonomies = "
                            SELECT DISTINCT tt.taxonomy as 'link_name', 'taxonomy' as 'link_type' 
                            FROM wp_posts post
			                    LEFT JOIN
				                    wp_term_relationships tt_r 
				                ON tt_r.object_id = post.ID
			                    LEFT JOIN 
			        	            wp_term_taxonomy tt
				                ON tt.term_taxonomy_id = tt_r.term_taxonomy_id
                            WHERE post.post_type = %s
                            AND tt.taxonomy IS NOT NULL";

        return $wpdb->get_results($wpdb->prepare($sql_taxonomies, array($link_name)), ARRAY_A);
    }

    private static function get_term_post_relationships($link_name) {
        global $wpdb;

        $sql_taxonomies = "
                            SELECT DISTINCT post.post_type as 'link_name', 'post' as 'link_type', true as 'many' 
                            FROM wp_posts post
			                    LEFT JOIN
				                    wp_term_relationships tt_r 
				                ON tt_r.object_id = post.ID
			                    LEFT JOIN 
			        	            wp_term_taxonomy tt
				                ON tt.term_taxonomy_id = tt_r.term_taxonomy_id
                            WHERE tt.taxonomy = %s
                            AND tt.taxonomy IS NOT NULL";

        return $wpdb->get_results($wpdb->prepare($sql_taxonomies, array($link_name)), ARRAY_A);
    }

    private static function get_user_term_relationships() {
        global $wpdb;

        $sql_taxonomies = "SELECT DISTINCT tt.taxonomy as 'link_name', 'taxonomy' as 'link_type' 
                            FROM wp_users as user
			                    LEFT JOIN
				                    wp_term_relationships tt_r 
				                ON tt_r.object_id = user.ID
			                    LEFT JOIN 
			        	            wp_term_taxonomy tt
				                ON tt.term_taxonomy_id = tt_r.term_taxonomy_id
                            WHERE tt.taxonomy IS NOT NULL";

        return $wpdb->get_results($sql_taxonomies, ARRAY_A);
    }

    /** Gets all the possible children types for a datalink**/
    public static function get_all_possible_children($datalink) {
        $possible_links = array();
        if(empty($datalink->type) || empty($datalink->name)) {

            /** Get all possible datalinks */

            $primary_posts_selection = apply_filters("mc-get_primary_datalink_selection-posts", array('post_types' => self::get_post_types()));
            $primary_tax_selection = apply_filters("mc-get_primary_datalink_selection-tax",     array('taxonomies' => self::get_taxonomies()));
            $primary_user_selection = apply_filters("mc-get_primary_datalink_selection-users",  array('user_types' => self::get_user_types()));

            $possible_links = array_merge($primary_posts_selection, $primary_tax_selection, $primary_user_selection);

        }
        else {
            switch($datalink->type) {
                case "post":

                    $possible_links = array(
                        array (
                            'link_name' => 'wp_users',
                            'link_type' => 'user'
                        ),
                        array(
                            'link_name' => 'wp_comments',
                            'link_type' => 'comment',
                            'many' => true
                        ),

                    );

                    /** Get terms  through term relationships **/

                    $possible_links = array_merge($possible_links, self::get_post_term_relationships($datalink->name));


                    break;
                case "user":
                    $possible_links = array (
                        array(
                            'link_name' => 'wp_comments',
                            'link_type' => 'comment',
                            'many' => true
                        )
                    );

                    $possible_links = array_merge($possible_links, self::get_user_term_relationships(), self::get_post_types());

                    break;
                case "comment":
                    $possible_links = array(
                        array (
                            'link_name' => 'wp_users',
                            'link_type' => 'user'
                        )
                    );

                    break;
                case "taxonomy":
                    $possible_links = self::get_term_post_relationships($datalink->name);
                    break;
            }

        }

        return $possible_links;
    }

    private static function get_tax_and_terms($post_type = null) {
        $result = array();
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        foreach($taxonomies as $key => $taxonomy) {
            if($key != "post_translations") {
                $result[$key] = array(
                    'display_name' => isset($taxonomy->label) ? $taxonomy->label : ucfirst($taxonomy->name),
                    'taxonomy' => $taxonomy->name,
                    'terms' => get_terms(array('taxonomy' => $key, 'hide_empty' => false))
                );
            }
        }

        return $result;
    }

    public static function get_secondary_children_forms($link_spec) {
        $forms = array();

        switch($link_spec['link_type']) {
            case 'post':
                $forms = apply_filters("mc-get_secondary_datalink_selection-posts", array(
                    'forms' => array(),
                    'post_statuses' => array_values( get_post_stati() ),
                    'taxonomies' => self::get_tax_and_terms($link_spec['link_spec']['post_type'])
                ));
                break;
            case 'taxonomy':
                apply_filters("mc-get_secondary_datalink_selection-tax", array());
                break;
            case 'user':
                apply_filters("mc-get_secondary_datalink_selection-users", array());
                break;
            case 'comment':
                apply_filters("mc-get_secondary_datalink_selection-comments", array());

                break;
            case 'table':
                apply_filters("mc-get_secondary_datalink_selection-tables", array());
                break;
        }

//        $primary_posts_selection = apply_filters("mc-get_primary_datalink_selection-posts", array('post_types' => self::get_post_types()));


        return $forms['forms'];
    }

    public static function is_many($child, $parent) {
        if(isset($parent->type) ) {
            switch($parent->type) {
                case 'post' :
                    switch($child->type) {
                        case 'comment':
                            return true;
                        default:
                            return false;
                    }
                case 'user' : {
                    switch($child->type) {
                        case 'comment':
                            return true;
                        default:
                            return false;
                    }
                }
                case 'taxonomy' : {
                    switch($child->type) {
                        case 'post':
                            return true;
                        default:
                            return false;
                    }
                }
                default:
                    return false;

            }
        }

        /** If we're here, it means the parent has no type: it must be the root node
         * TODO: determine whether it's better to use a is_root flag
         */

        return false;

    }

    public static function get_available_link_types() {
        global $wpdb;

        $sql = "SELECT DISTINCT post_type as link_name, 'post' as link_type FROM " . $wpdb->prefix . "posts";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /** Get all the variable sets belonging to a link name
     *
     * @var $link_name - name of the object linked to the Mail. example: wc_booking
     * @var $example_id - A randomly selected id of an object of type: $link_name
     *
     * TODO: Currently only works for post_types. Make it work for terms as well
     **/
    public static function get_datalink_variable_sets($link_name, $link_type, $object_id) {
        global $wpdb;

        /** Get all possible variable sets */
        switch($link_type) {
            case "post":
                $possible_links = array(
                    $wpdb->prefix . 'posts',
                    $wpdb->prefix . 'postmeta'
                );
                break;
            case "user":
                $possible_links = array (
                    $wpdb->prefix . "users",
                    $wpdb->prefix . "usermeta"
                );
                break;
            case "taxonomy":
                $possible_links = array(
                    $wpdb->prefix . "termmeta",
                    $wpdb->prefix . "terms",
                );
                break;

            case "comment" :
                $possible_links = array(
                    $wpdb->prefix . "comments",
                    $wpdb->prefix . "commentmeta"
                );
                break;
        }

        /** Getting all tables associated by name **/
        $table_names = self::get_external_table_names($link_name);

        $possible_links = array_merge($possible_links, $table_names);


        $variable_sets = array();

        /** Get all the saved datalinks **/
        if(!empty($possible_links)) {
            foreach($possible_links as $link) {
                switch($link){

                    case $wpdb->prefix . "terms" :

                        $variable_sets['term_data'] = array(
                            'display_name' => 'Term Data',
                            'vars' => DataLink_Utils::get_object_vars(get_object_vars(get_term($object_id)))
                        );
                        break;

                    case $wpdb->prefix . "termmeta":
                        $sql = "SELECT DISTINCT meta_key,meta_value FROM ". $wpdb->prefix . "termmeta WHERE term_id  = %s";
                        $term_metas = $wpdb->get_results($wpdb->prepare($sql, array($object_id)), ARRAY_A);
                        $obj = array();
                        foreach($term_metas as $term_meta) {
                            $obj[$term_meta['meta_key']] = $term_meta['meta_value'];
                        }

                        $variable_sets['term_meta'] = array(
                            'display_name' => 'Term Meta',
                            'vars' => $obj
                        );

                        break;

                    default:
                        /** external table **/
                        /** TODO: The user must be able to define an identifying column **/

                        $identifying_column = isset($table_config['identifying_clumn']) ? $table_config['identifying_column'] : null;
                        if($identifying_column == null) {

                            /** Try and get the identifying column from the table **/

                            $sql = "SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = %s";
                            $table_columns = $wpdb->get_row($wpdb->prepare($sql, array($link)),ARRAY_A);
                            $identifying_column = $link_name . "_id";
                            if(!in_array($identifying_column, $table_columns)) {

                                break;
                            }
                        }

                        $sql = "SELECT * FROM $link WHERE $identifying_column = $object_id";
                        $variable_sets[$link] = $wpdb->get_row($sql);
                        break;
                }

            }
        }

        return $variable_sets;
    }

    /**
     * Gets the external table names for a link
     *
     * These include tables that are not included in standard Wordpress Custom Post/Taxonomy functionalities.
     * @param $link_name
     */
    private static function get_external_table_names($link_name) {
        global $wpdb;

        /** Getting all tables associated by name **/
        $sql = "SELECT DISTINCT TABLE_NAME FROM information_schema.tables  WHERE TABLE_NAME REGEXP '" . $link_name . "[^s]'";
        return $wpdb->get_col($sql);
    }

    /**
     * Returns an array of available formatting functions.
     * The structure is:
     *
     * array (
     *   user_friendly_name =>
     *      array (
     *          php_name => name1,
     *          args    => []
     *   )
     */
    public static function formatting_functions_array() {
        return array(
            __('Uppercase letters', 'ark_mail_composer') => array (
                'name' => 'strtoupper',
                'desc' => __('Grab a part of a string of text'),
                'args' => []
            ),
            __('Lowercase letters', 'ark_mail_composer') => array(
                'name' => 'strtolower',
                'desc' => __('Grab a part of a string of text'),
                'args' => []
            ),

            __('Substring', 'ark_mail_composer') => array (
                'name' => 'substr',
                'desc' => __('Grab a part of a string of text'),

                'args' => array(
                    'offset' => __('Start', 'mail_composer'),
                    'length' => __('Amount', 'mail_composer')
                )
            ),

            __('Remove whitespace', 'ark_mail_composer') => array(
                'name' => 'trim',
                'desc' => __('Grab a part of a string of text'),

                'args' => []
            ),

            __('Repeat', 'ark_mail_composer') => array (
                'name' => 'str_repeat',
                'desc' => __('Repeat a string a certain amount of times'),

                'args' => array(
                    'times' => __('Times', 'mail_composer')
                )
            )

        );
    }

    /**
     * For some reason, catching errors from a filter doesn't work without explicitly throwing an error
     */
    public static function get_object_vars($object) {
        if($object == null) {
            throw new Exception("Nothing found for ID", 1);
        }
        return get_object_vars($object);
    }
}
