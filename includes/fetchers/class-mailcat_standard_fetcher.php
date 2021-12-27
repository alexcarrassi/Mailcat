<?php
/**
 * In the data fetcher, you MUST supply the datanode with a db_id
 * Class Basic_DataFetcher
 *
 * Handles Basic wordpress functionalities
 */
class Standard_DataFetcher {
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
        add_filter('mc-get_example_id-post', array($this, 'get_post_example_id'), 10, 2);
        add_filter('mc-get_example_id-user', array($this, 'get_user_example_id'), 10, 2);
        add_filter('mc-get_example_id-comment', array($this, 'get_comment_example_id'), 10, 2);
        add_filter('mc-get_example_id-taxonomy', array($this, 'get_taxonomy_example_id'), 10, 2);


        /** Possible DataLinks primary **/

        add_filter("mc-get_primary_datalink_selection-posts", array($this, 'primary_posts_selection'), 10, 1);
        add_filter("mc-get_primary_datalink_selection-tax", array($this, 'primary_tax_selection'), 10, 1);
        add_filter("mc-get_primary_datalink_selection-users", array($this, 'primary_users_selection'), 10, 1);

        /** Possible Datalinks secondary **/

        add_filter("mc-get_secondary_datalink_selection-posts", array($this, 'secondary_posts_selection'), 10, 1);
        add_filter("mc-get_secondary_datalink_selection-tax", array($this, 'secondary_taxonomy_selection'), 10, 1);



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

    }

    /** Possible Datalinks secondary
     *
     *  The second part of the Add Datalink dialog.
     *  Renders optional, secondary information about a datalink, such as taxonomies, post_status, etc
     **/

    public function secondary_posts_selection($link_spec) {

        /** if we're dealing with a page:  */

        if($link_spec['post_type'] == 'page') {
            $page_templates = array_keys(get_page_templates());
            $pages = get_pages(array('post_status' => 'publish'));
            include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_page_radios.php");
        }

        else {
            /**
             * category, post_tag, format,
             */

            /** Create forms for the taxonomies **/
            $taxonomies = $link_spec['taxonomies'];

            foreach($taxonomies as $name => $taxonomy_spec) {
                if(in_array($name, $this->standard_taxonomies) && !empty($taxonomy_spec['terms'])) {
                    /** Creating the form **/
                    $name = "[taxonomies][$name]";
                    include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_taxonomy_checkboxes.php");
                    /** Remove the taxonomy from the spec **/
                    unset($taxonomies[$name]);
                }
            }

            $link_spec['taxonomies'] = $taxonomies;

            /** Create form for the post statuses */
            $values = $link_spec['post_statuses'];
            $name = 'post_status';
            $header =  "Post Status";
            include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_radios.php");
            unset($link_spec['post_statuses']);

        }

        return $link_spec;
    }

    public function secondary_taxonomy_selection($taxonomy_spec) {

        if(in_array($taxonomy_spec['taxonomy'], $this->standard_taxonomies) && !empty($taxonomy_spec['terms'])) {

            include(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/views/dialog-add_datalink/form-secondary_taxonomy_checkboxes.php");

        }
        return $taxonomy_spec;
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

    public function get_post_example_id($datalink_node, $example_id) {
        $args = array('fields' => 'ids'); //We only want ids.

        $link_spec = $datalink_node->link_spec;
        /** First, we dissect the taxonomies **/

        if(isset($link_spec['taxonomies'])) {
            $tax_query = array();
            foreach($link_spec['taxonomies'] as $tax_name => $tax_terms) {
                array_push($tax_query, array (
                    'taxonomy' => $tax_name,
                    'field' => 'slug',
                    'terms' => $tax_terms
                ));
            }
            $args['tax_query'] = $tax_query;

            unset($link_spec['taxonomies']);
        }

        /** Now we do the rest **/
        foreach($link_spec as $key => $value) {
            $args[$key] = $value;
        }
        $ids = get_posts($args);

        if(count($ids) > 0) {
            $random_index = array_rand($ids);
            return $ids[$random_index];
        }
        return 0;
    }

    public function get_user_example_id($datalink_node, $example_id) {
        global $wpdb;

        $sql = "SELECT ID FROM " . $wpdb->prefix . "users ORDER BY RAND() LIMIT 1";
        $example_id = $wpdb->get_var($sql);

        return $example_id;

    }
    public function get_comment_example_id($datalink_node, $example_id) {
        global $wpdb;

        $sql = "SELECT comment_ID FROM wp_comments ORDER BY RAND() LIMIT 1";
        $example_id = $wpdb->get_var($sql);

        return $example_id;

    }
    public function get_taxonomy_example_id($datalink_node, $example_id) {
        global $wpdb;

        $sql = "SELECT term_taxonomy_id FROM wp_term_taxonomy ORDER BY RAND() LIMIT 1";
        $example_id = $wpdb->get_var($sql);

        return $example_id;

    }

}

new Standard_DataFetcher();