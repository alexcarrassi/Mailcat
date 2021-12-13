<?php
include_once (ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/class-datalink_utils.php");

class Ark_DataLink {
    public int $ID = 0;
    public int $db_id = 0;
    public string $type;
    public string $name;
    public string $desc;
    public bool $many = false;
    public $parent    = null;
    public $links     = array();
    public $var_forms = array();
    public $data      = array();

    public function __construct($data) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function &get_child($id) {
        if(isset($this->links[$id])) {
            return $this->links[$id];
        }
        $null = null;
        return $null;
    }
    public function &add_child($datalink, $link_id = null) {
        $datalink->many = DataLink_Utils::is_many($datalink, $this);

        if(isset($link_id)) {
            $this->links[$link_id] = $datalink;

        }
        elseif(isset($datalink->db_id) && $datalink->db_id != 0) {
            $this->links[$datalink->name . '_' . $datalink->db_id] = $datalink;
        }
        else {
            $num = 1;
            foreach($this->links as $child) {
                if($child->name == $datalink->name) {
                    $num += 1;
                }
            }

            $this->links[$datalink->name . '_' . $num] = $datalink;
        }

        return $datalink;
    }

    public function set_db_id($new_id) {
        $this->db_id = $new_id;


    }

    public function &get_child_by_name($name) {
        foreach($this->links as $child) {
            if($child->name == $name) {
                return $child;
            }
        }
        $null = null;
        return $null;
    }

    public function delete_child_by_name($name) {
        if(isset($this->links[$name])) {
            unset($this->links[$name]);
        }
    }

    public function &get_child_by_path($hierarchy_path, $single = false) {
        if(count($hierarchy_path) < 1) {

            return $this;
        }

        $next = array_shift($hierarchy_path);

        if($next == "root") {
            return $this;
        }
        if(isset($this->links[$next])) {
            if(count($hierarchy_path) == 0 && $this->links[$next]->many && !$single) {
                /** We need to do a forward check. The next link is our target.
                 * If it is a Many, there is a chance that there are duplicate siblings we want as well
                 **/
                $ids = array_keys($this->links);
                $sibling_ids = array_filter($ids, function($key) use($next) {

                    return strpos($key, $next) !== false;
                });
                if(count($sibling_ids) > 1) {
                    $siblings = array();
                    foreach($sibling_ids as $sibling_id) {
                        $siblings[$sibling_id] = $this->links[$sibling_id];
                    }
                    return $siblings;
                }
            }
            return $this->links[$next]->get_child_by_path($hierarchy_path, $single);
        }

        $null = null;
        return $null;
    }

    public function delete_child_by_path($hierarchy_path) {
        $target = array_pop($hierarchy_path);
        $parent = $this->get_child_by_path($hierarchy_path);
        $parent->delete_child_by_name($target);
    }

    public function delete_child($link_id) {
        unset($this->links[$link_id]);
    }

    public function add_child_by_path($hierarchy_path,  $new_child) {
        $parent = $this->get_child_by_path($hierarchy_path);
        $parent->add_child( $new_child );

    }

    public function set_var_format($set_name, $var_name, $function_data) {

        if(!isset($this->var_forms[$set_name][$var_name])) {
           $this->var_forms[$set_name][$var_name] = array();
        }
        $this->var_forms[$set_name][$var_name] = $function_data;

    }



    /**
     * Gathers all the data belonging to the Datalink.
     * With this, the DataLink object will contain the actual values of its variables
     *
     * @param $root_ids - The id's of the root datalinks
     *
     * @param $link_id - The ID of the node in the Links array of the parent
     */
    public function populate_data($db_id, $link_id, $parent = null ) {

        //Wat moeten we hiervoor doen?
        //We moeten iedere datalink nagaan. Dan krijgen we dus een relationship. Die moeten we gebruiken!

        // Voorlopig hoeven we alleen IDs te pakken.
        // Dit kan alleen als je al bij de child bent. We moeten hiervoor een speciale mapping voor hebben.
        //      wp_post ID      ->   wp_comment  comment_post_ID
        //
        // Cool zou zijn als we een aparte file hebben. Gewoon een assoc array met strings:
        //      wp_post
        //

        $child_name = $this->name; $child_type = $this->type;
        $parent_name = $parent != null ? "-" . $parent->name : "";
        $parent_type = $parent != null ? "-" . $parent->type : "";


        $id_to_use = $parent != null ? $parent->db_id : $this->db_id;

        apply_filters("mc-get_data-$child_name$parent_name", $this, $id_to_use);
        apply_filters("mc-get_data-$child_type$parent_type", $this, $id_to_use);


        if($this->many) {
            /** This is a datalink flagged as Many. Meaning that we need to create Duplicate siblings for each retrieved dataset**/
            $i = 0;
            $parent->delete_child($link_id);

            foreach($this->data as $id => $dataset) {

                $sibling = new Ark_DataLink(array(
                    'type' => $this->type,
                    'name' => $this->name,
                    'many' => $this->many,
                    'desc' => $this->desc
                ));
                $sibling->links = $this->links;
                $sibling->db_id = $id;
                $sibling->data = $dataset;
                $sibling->var_forms = $this->var_forms;

                $sibling_link_id = $i == 0 ? $link_id : $link_id . "_$i"; // Make sure the original link_id is reserved, and we build subsequent link_ids upon it
                $parent->add_child($sibling, $sibling_link_id);
                $i++;
            }
        }
        foreach($this->links as $link_id => $child){
            $child->populate_data($this->db_id, $link_id, $this);
        }
    }

    public function gather_variables($hierarchy_path_raw = array(), $hierarchy_path_usr = "", $variables = array(), $link_id = null ) {

        if($link_id != null) {
            $usr_path_node = isset($this->desc) ? $this->desc : $link_id;
            $hierarchy_path_usr .= " > $usr_path_node";
            array_push($hierarchy_path_raw, $link_id);

            if($this->db_id == 0) {
                /** To gather variables, we must have an example id **/
                $this->get_example_id();
            }

            $this->get_variable_sets();

            $vars = array();
            foreach($this->data as $table_name => $data_container) {
                $vars[$table_name] = array_keys($data_container['data']);
            }
            $variables[$hierarchy_path_usr] = array(
                'data_ref' => $hierarchy_path_raw,
                'vars' => $vars
            );
        }

        foreach($this->links as $link_id => $link) {
            $variables = $link->gather_variables($hierarchy_path_raw, $hierarchy_path_usr, $variables, $link_id);
        }
        return $variables;
    }

    public function get_example_id() {
        $example_id = 0;
        $example_id = apply_filters("mc-get_example_id-" . $this->name, $example_id);
        if($example_id == 0) {
            $example_id = apply_filters("mc-get_example_id-" . $this->type, $example_id);
        }

        $this->db_id = $example_id;
        return $example_id;
    }

    public function get_variable_sets() {
        apply_filters("mc-get_data-" . $this->type, $this, $this->db_id);
        apply_filters("mc-get_data-" . $this->name, $this, $this->db_id);

    }

    public function get_value($varset_name, $var_name) {
        if(isset($this->data[$varset_name])) {
            if(isset($this->data[$varset_name]['data'][$var_name])) {
                return $this->data[$varset_name]['data'][$var_name];
            }
        }

        return " ";
    }
}






/**
 * In the data fetcher, you MUST supply the datanode with a db_id
 * Class Basic_DataFetcher
 *
 * Handles Basic wordpress functionalities
 */
class Basic_DataFetcher {
    private $standard_taxonomies = ["category", "post_tag", "post_format"];

    public function __construct() {

            /** Data Singular **/
        add_filter('mc-get_data-post', array($this, 'get_post_data'), 10, 2);
        add_filter('mc-get_data-comment', array($this, 'get_comment_data'), 10, 2);
        add_filter('mc-get_data-user', array($this, 'get_user_data'), 10, 2);
        add_filter('mc-get_data-term', array($this, 'get_term_data'), 10, 2);
        add_filter('mc-get_data-taxonomy', array($this, 'get_term_data'), 10, 2);


            /** Data Relational */
        add_filter('mc-get_data-user-post', array($this, 'user_post_data'), 10, 2);
        add_filter('mc-get_data-comment-post', array($this, 'post_comment_data'), 10, 2);

            /** Example Ids */
        add_filter('mc-get_example_id-post', array($this, 'get_post_example_id'), 10, 1);
        add_filter('mc-get_example_id-user', array($this, 'get_user_example_id'), 10, 1);
        add_filter('mc-get_example_id-comment', array($this, 'get_comment_example_id'), 10, 1);
        add_filter('mc-get_example_id-taxonomy', array($this, 'get_taxonomy_example_id'), 10, 1);


        /** Possible DataLinks primary **/

        add_filter("mc-get_primary_datalink_selection-posts", array($this, 'primary_posts_selection'), 10, 1);
        add_filter("mc-get_primary_datalink_selection-tax", array($this, 'primary_tax_selection'), 10, 1);
        add_filter("mc-get_primary_datalink_selection-users", array($this, 'primary_users_selection'), 10, 1);

        /** Possible Datalinks secondary **/

        add_filter("mc-get_secondary_datalink_selection-posts", array($this, 'secondary_posts_selection'), 10, 1);


    }



                /** Possible DataLinks primary
                 *
                 *  The first part of the Add Datalink dialog
                 *  Renders the necessary inputs to select a certain type of datalink, such as a post type
                 **/
    public function primary_posts_selection($primary_selection) {
        /** foreach post type, get the ones that are relevant and organize **/
        if(!isset($primary_selection['posts_standard'])) {
            $primary_selection['posts_standard'] = array(
                'display_name' => __("Standard WordPress posts", "mailcat"),
                'selection' => array_reduce(
                    $primary_selection['post_types'],
                    function($acc, $item) {

                        if( in_array($item->name, ["post", "attachment", "revisions", "nav_menu_item"])) {
                            $selection = array(
                                'data' => array('type' => 'post', 'post_type' => $item->name),
                                'display_name' => $item->label != false ? $item->label :  ucfirst(implode(" ",explode("_", $item->name)))
                            );

                            $acc[] = $selection;
                        }
                        return $acc;
                    },
                    []
                )
            );
        }


        /** Remove post types if necessary **/
        $primary_selection['post_types'] = array_reduce(
            $primary_selection['post_types'],
            function($acc, $item) {
                if( !in_array($item->name, ["post", "attachment", "revisions", "nav_menu_item"])) {
                    $acc[] = $item;
                }

                return $acc;
            },
            []
        );

        return $primary_selection;
    }
    public function primary_tax_selection($primary_selection) {
        if(!isset($primary_selection['tax_standard'])) {
            $primary_selection['tax_standard'] = array(
                'display_name' => __("Standard WordPress taxonomies", "mailcat"),
                'selection' => array_reduce(
                    $primary_selection['taxonomies'],
                    function($acc, $item) {

                        if(in_array($item->name, ["post_tag", "category", "post_format"])) {

                            $selection = array(
                                'data' => array('type'=> 'taxonomy', 'taxonomy' => $item->name),
                                'display_name' => $item->label != false ? $item->label :  ucfirst(implode(" ",explode("_", $item->name)))
                            );

                            $acc[] = $selection;

                        }
                        return $acc;
                    },
                    []
                )
            );
        }

        /** Remove post types if necessary **/
        $primary_selection['taxonomies'] = array_reduce(
            $primary_selection['taxonomies'],
            function($acc, $item) {
                if( !in_array($item->name, ["post_tag", "category", "post_format"])) {
                    $acc[] = $item;
                }

                return $acc;
            },
            []
        );
        return $primary_selection;

    }
    public function primary_users_selection($primary_selection) {

        if(!isset($primary_selection['general_users'])) {
            $primary_selection['general_users'] = array(
                'display_name' => __("Standard WordPress users", "mailcat"),
                'selection' => array_reduce(
                    $primary_selection['user_types'],
                    function($acc, $item) {
                        if(in_array($item->name, ["administrator", "editor", "author", "contributor", "subscriber"])) {

                            $selection = array(
                                'data' => array('type'=> 'user', 'role' => $item->name),
                                'display_name' => ucfirst($item->name)
                            );

                            $acc[] = $selection;
                        }
                        return $acc;
                    },
                    []
                )
            );
        }


        /** Remove post types if necessary **/
        $primary_selection['user_types'] = array_reduce(
            $primary_selection['user_types'],
            function($acc, $item) {
                if( !in_array($item->name, ["administrator", "editor", "author", "contributor", "subscriber"])) {
                    $acc[] = $item;
                }

                return $acc;
            },
            []
        );
        return $primary_selection;

        return $primary_selection;
    }

                /** Possible Datalinks secondary
                 *
                 *  The second part of the Add Datalink dialog.
                 *  Renders optional, secondary information about a datalink, such as taxonomies, post_status, etc
                 **/

    public function secondary_posts_selection($link_spec) {
        /**
         * category, post_tag, format,
         */

        /** Create forms for the taxonomies **/
        $taxonomies = $link_spec['taxonomies'];

        foreach($taxonomies as $name => $taxonomy_spec) {
            if(in_array($name, $this->standard_taxonomies)) {
                /** Creating the form **/
//                ob_start();
                include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_taxonomy_checkboxes.php");
//                $html = ob_get_clean();
//                $link_spec['forms'][] = $html;
                /** Remove the taxonomy from the spec **/
                unset($taxonomies[$name]);
            }
        }

        $link_spec['taxonomies'] = $taxonomies;

        /** Create form for the post statuses */
        $post_statuses = $link_spec['post_statuses'];
//        ob_start();
        include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_taxonomy_checkboxes.php");
//        $html = ob_get_clean();
//        $link_spec['forms'][] = $html;




        return $link_spec;

    }

                /** Data **/

    public function get_post_data($datalink_node, $id) {
        global $wpdb;

        if(!isset($datalink_node->data['post_data'])) {
            $datalink_node->data['post_data'] = array(
                'display_name' => 'Post data',
                'data' => array()
            );
        }

        $post_data =  DataLink_Utils::get_object_vars(get_post($datalink_node->db_id));
        $datalink_node->data['post_data']['data'] = array_merge($datalink_node->data['post_data']['data'], $post_data);

        if(!isset($datalink_node->data['post_meta'])) {
            $datalink_node->data['post_meta'] = array(
                'display_name' => 'Post meta',
                'data' => array()
            );
        }
        $post_meta = array_map(function($item) {return array_values($item)[0]; }, get_post_meta($datalink_node->db_id));
        $datalink_node->data['post_meta']['data'] = array_merge($datalink_node->data['post_meta']['data'], $post_meta);

        $datalink_node->db_id = $datalink_node->data['post_data']['data']['ID'];

        /** REFORMAT :
         * table_name => array('display_name' => 'Post data', 'data' => array())
         **/
        return $datalink_node;
    }
    public function get_comment_data($datalink_node, $id) {
        if(!isset($datalink_node->data['comment_data'])) {
            $datalink_node->data['comment_data'] = array(
                'display_name' => 'Comment data',
                'data' => array()
            );
        }

        $comment_data =  DataLink_Utils::get_object_vars(get_comment($datalink_node->db_id));
        $datalink_node->data['comment_data']['data'] = array_merge($datalink_node->data['comment_data']['data'], $comment_data);


        if(!isset($datalink_node->data['comment_meta'])) {
            $datalink_node->data['comment_meta'] = array(
                'display_name' => 'Comment meta',
                'data' => array()
            );
        }

        $comment_meta = array_map(function($item) {return array_values($item)[0]; }, get_comment_meta($datalink_node->db_id));
        $datalink_node->data['comment_meta']['data'] = array_merge($datalink_node->data['comment_meta']['data'], $comment_meta);
    }
    public function get_user_data($datalink_node, $id) {
        if(!isset($datalink_node->data['user_data'])) {
            $datalink_node->data['user_data'] = array(
                'display_name' => 'User data',
                'data' => array()
            );
        }

        $comment_data =  DataLink_Utils::get_object_vars(get_userdata($datalink_node->db_id));
        $datalink_node->data['user_data']['data'] = array_merge($datalink_node->data['user_data']['data'], $comment_data);


        if(!isset($datalink_node->data['user_meta'])) {
            $datalink_node->data['user_meta'] = array(
                'display_name' => 'User meta',
                'data' => array()
            );
        }

        $comment_meta = array_map(function($item) {return array_values($item)[0]; }, get_user_meta($datalink_node->db_id));
        $datalink_node->data['user_meta']['data'] = array_merge($datalink_node->data['user_meta']['data'], $comment_meta);
    }
    public function get_term_data($datalink_node, $id) {
        if(!isset($datalink_node->data['term_data'])) {
            $datalink_node->data['term_data'] = array(
                'display_name' => 'Term data',
                'data' => array()
            );
        }


        $term_data =  DataLink_Utils::get_object_vars(get_term($datalink_node->db_id));
        $datalink_node->data['term_data']['data'] = array_merge($datalink_node->data['term_data']['data'], $term_data);


        if(!isset($datalink_node->data['term_meta'])) {
            $datalink_node->data['term_meta'] = array(
                'display_name' => 'Term meta',
                'data' => array()
            );
        }

        $term_meta = array_map(function($item) {return array_values($item)[0]; }, get_term_meta($datalink_node->db_id));
        $datalink_node->data['term_meta']['data'] = array_merge($datalink_node->data['term_meta']['data'], $term_meta);
    }


    public function post_comment_data($datalink_node, $post_id) {
        global $wpdb;

        $sql = "SELECT " . $wpdb->prefix . "comments.*  FROM " . $wpdb->prefix . "comments, " . $wpdb->prefix . "posts
                WHERE " . $wpdb->prefix ."comments.comment_post_ID = " . $wpdb->prefix . "posts.ID 
                 AND " . $wpdb->prefix . "posts.ID = '172441'";


        $comments = $wpdb->get_results($sql,ARRAY_A);
        $data = array();
        foreach($comments as $comment) {
            /** Copy the datalink node, add the specific node's comment's data **/
            $id = $comment['comment_ID'];
            $datalink_node->data[$id]['comment_data']['data'] = $comment;

            $sql = "SELECT meta_key, meta_value  FROM " . $wpdb->prefix . "commentmeta WHERE " . $wpdb->prefix . "commentmeta.comment_id  = %s";
            $meta_data = $wpdb->get_results($wpdb->prepare($sql, array($id)), ARRAY_A);
            foreach($meta_data as $md) {
                $datalink_node->data[$id]['comment_meta']['data'][$md['meta_key']] = $md['meta_value'];
            }
        }
        return $datalink_node;
    }
    public function user_post_data($datalink_node, $post_id) {
        global $wpdb;
        $sql = "SELECT " . $wpdb->prefix . "users.*  FROM " . $wpdb->prefix . "users, " . $wpdb->prefix . "posts
                WHERE " . $wpdb->prefix ."users.Id = " . $wpdb->prefix . "posts.post_author 
                 AND " . $wpdb->prefix . "posts.ID = %s";
        $result = $wpdb->get_results($wpdb->prepare($sql, array($post_id)), ARRAY_A);

        if(!empty($result)) {
            $datalink_node->data['user_data'] = $result[0];
            $datalink_node->db_id = $datalink_node->data['user_data']['ID'];
        }

        $sql = "SELECT " . $wpdb->prefix . "usermeta.*  FROM " . $wpdb->prefix . "usermeta, " . $wpdb->prefix . "posts
                WHERE " . $wpdb->prefix ."usermeta.user_id = " . $wpdb->prefix . "posts.post_author 
                 AND " . $wpdb->prefix . "posts.ID = %s";
        $meta_data = $wpdb->get_results($wpdb->prepare($sql, array($post_id)), ARRAY_A);
        foreach($meta_data as $md) {
            $datalink_node->data['user_meta'][$md['meta_key']] = $md['meta_value'];
        }

        return $datalink_node;
    }

    /** Example Ids */

    public function get_post_example_id($example_id) {
        global $wpdb;

        $sql = "SELECT ID FROM " . $wpdb->prefix . "posts ORDER BY RAND() LIMIT 1";
        $example_id = $wpdb->get_var($sql);

        return $example_id;
    }
    public function get_user_example_id($example_id) {
        global $wpdb;

        $sql = "SELECT ID FROM " . $wpdb->prefix . "users ORDER BY RAND() LIMIT 1";
        $example_id = $wpdb->get_var($sql);

        return $example_id;

    }
    public function get_comment_example_id($example_id) {
        global $wpdb;

        $sql = "SELECT comment_ID FROM wp_comments ORDER BY RAND() LIMIT 1";
        $example_id = $wpdb->get_var($sql);

        return $example_id;

    }
    public function get_taxonomy_example_id($example_id) {
        global $wpdb;

        $sql = "SELECT term_taxonomy_id FROM wp_term_taxonomy ORDER BY RAND() LIMIT 1";
        $example_id = $wpdb->get_var($sql);

        return $example_id;

    }

}

new Basic_DataFetcher();

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

/** For ACF **/
/**
 * ACF field groups are fetched with 'type' => 'type_name'
 *                               ex:   'post_type' => 'product'
 *                               ex2:  'taxonomy'  => 'product_cat'
 *
 **/


class ACF_DataFetcher {
    public function __construct() {

    }

    public static function get_data(&$datalink_node, $field_groups, $id) {
        $test = 1;
        foreach($field_groups as $field_group) {
            $datalink_node->data[$field_group['key']] = array(
                'display_name' => $field_group['title'],
                'data' => array()
            );

            $fields = acf_get_fields_by_id($field_group['ID']);
            foreach($fields as $field) {

//                $value = get_field('bevestiging_nodig_van_champagnehuis', $nl_product_id);


                $trst = 1;
            }
            $test = 1;
        }
    }


}
//new ACF_DataFetcher();

/** For Woocommerce */
class WC_DataFetcher {
    public function __construct() {
        /** Data **/
        add_filter('mc-get_data-product', array($this, 'get_product_data'), 10, 2);

        /** Example Ids */
        add_filter('mc-get_example_id-product', array($this, 'get_product_example_id'), 10, 1 );
        add_filter('mc-get_example_id-shop_order', array($this, 'get_shop_order_example_id'), 10, 1 );
        add_filter('mc-get_example_id-shop_order', array($this, 'get_review_example_id'), 10, 1 );


        /** Possible DataLinks primary **/
        add_filter("mc-get_primary_datalink_selection-posts", array($this, 'primary_posts_selection'), 10, 1);
        add_filter("mc-get_primary_datalink_selection-tax", array($this, 'primary_tax_selection'), 10, 1);
        add_filter("mc-get_primary_datalink_selection-users", array($this, 'primary_users_selection'), 10, 1);


        /** Possible Datalinks secondary **/
        add_filter("mc-get_secondary_datalink_selection-posts", array($this, 'secondary_posts_selection'), 10, 1);

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

            get_terms(array('taxonomy' => 'product_type', 'hide_empty' => false)),

            function($acc, $item) {

                $selection = array(
                    'data' => array('type' => 'post', 'post_type' => 'product', 'taxonomies' => ['product_type' => [$item->name]]),
                    'display_name' => "Product (" . $item->name . ")"
                );

                $acc[] = $selection;

                return $acc;
            },

            [array('display_name' => 'Shop order', 'data' => array('type' => 'post', 'post_type' => 'shop_order')),
                array('display_name' => "Product (general)", 'data' => array('type' => 'post', 'post_type' => 'product'))                ]
        );

        $primary_selection['woo_posts']['selection'] = array_merge($primary_selection['woo_posts']['selection'], $selection);



        /** Remove post types if necessary **/
        $primary_selection['post_types'] = array_reduce(
            $primary_selection['post_types'],
            function($acc, $item) {
                if( !in_array($item->name, array('shop_order', 'product') ) ) {
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

        return $link_spec;

    }

        /** Data **/
    public function get_product_data($datalink_node, $id) {
        $datalink_node->data['wc_product_data'] = array(
            'display_name' => 'Product data',
            'data' => array('test' => 1)
        );
    }

        /** Example Ids **/
    public function get_product_example_id($example_id) {
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
                if( in_array( $item->name, array("bookable_resource", "wc_booking") ) )
                $acc[] = $item->label;
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
//new WC_Bookings_DataFetcher();









