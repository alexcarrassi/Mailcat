<?php

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





//UTILS
class DataLink_Utils {


    public static function get_datalink_variables() {
        return ["var"];
    }

    private static function get_post_types() {
        global $wpdb;

        $sql = "SELECT DISTINCT post_type as link_name, 'post' as link_type FROM " . $wpdb->prefix . "posts";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    private static function get_taxonomies() {
        global $wpdb;

        $sql = "SELECT DISTINCT taxonomy AS link_name, 'taxonomy' AS link_type FROM " . $wpdb->prefix . "term_taxonomy";

        return $wpdb->get_results($sql, ARRAY_A);
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
        if(empty($datalink->type) || empty($datalink->name)) {
            /** Get all possible datalinks */
            $possible_links = array(
                array (
                    'link_name' => 'wp_users',
                    'link_type' => 'user'
                ),
                array(
                    'link_name' => 'wp_comments',
                    'link_type' => 'comment',
                ),
            );

            $possible_links = array_merge($possible_links, self::get_post_types(), self::get_taxonomies());

            return $possible_links;
        }


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

        return $possible_links;
    }

    public static function is_many($child, $parent) {
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
                            'vars' => get_object_vars(get_term($object_id))
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
}


/**
 * In the data fetcher, you MUST supply the datanode with a db_id
 * Class Basic_DataFetcher
 */
class Basic_DataFetcher {
    public function __construct() {

            /** Data Singular **/
        add_filter('mc-get_data-post', array($this, 'get_post_data'), 10, 2);
        add_filter('mc-get_data-comment', array($this, 'get_comment_data'), 10, 2);
        add_filter('mc-get_data-user', array($this, 'get_user_data'), 10, 2);
        add_filter('mc-get_data-term', array($this, 'get_term_data'), 10, 2);

            /** Data Relational */
        add_filter('mc-get_data-user-post', array($this, 'user_post_data'), 10, 2);
        add_filter('mc-get_data-comment-post', array($this, 'post_comment_data'), 10, 2);

            /** Example Ids */
        add_filter('mc-get_example_id-post', array($this, 'get_post_example_id'), 10, 1);
        add_filter('mc-get_example_id-user', array($this, 'get_user_example_id'), 10, 1);
        add_filter('mc-get_example_id-comment', array($this, 'get_comment_example_id'), 10, 1);
        add_filter('mc-get_example_id-taxonomy', array($this, 'get_taxonomy_example_id'), 10, 1);

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
        $post_data = get_object_vars(get_post($datalink_node->db_id));
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
        $comment_data = get_object_vars(get_comment($datalink_node->db_id));
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
        $comment_data = get_object_vars(get_userdata($datalink_node->db_id));
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
        $comment_data = get_object_vars(get_term($datalink_node->db_id));
        $datalink_node->data['term_data']['data'] = array_merge($datalink_node->data['term_data']['data'], $comment_data);


        if(!isset($datalink_node->data['term_meta'])) {
            $datalink_node->data['term_meta'] = array(
                'display_name' => 'Term meta',
                'data' => array()
            );
        }

        $comment_meta = array_map(function($item) {return array_values($item)[0]; }, get_term_meta($datalink_node->db_id));
        $datalink_node->data['term_meta']['data'] = array_merge($datalink_node->data['term_meta']['data'], $comment_meta);
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

/** For Woocommerce */
class WC_DataFetcher {
    public function __construct() {
        /** Data **/
        add_filter('mc-get_data-product', array($this, 'get_product_data'), 10, 2);

        /** Example Ids */
        add_filter('mc-get_example_id-product', array($this, 'get_product_example_id'), 10, 1 );
        add_filter('mc-get_example_id-shop_order', array($this, 'get_shop_order_example_id'), 10, 1 );
        add_filter('mc-get_example_id-shop_order', array($this, 'get_review_example_id'), 10, 1 );

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

}

new WC_Bookings_DataFetcher();