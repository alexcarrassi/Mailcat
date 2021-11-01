<?php

/**
 * Class that handles Datalinks
 */
class Ark_Datalinks {
    public function __construct($mail_id) {
        $this->datalinks = get_post_meta($mail_id, 'datalinks', true);
        $this->mail_id = $mail_id;
    }

    public function update_variable_formats($hierarchy_path, $set_name, $function_data, $var_name) {

        $this->rec_update_variable_formats($this->datalinks, $hierarchy_path, $set_name, $function_data, $var_name);
    }

    function rec_update_variable_formats(&$datalinks, $link_names, $set_name, $function_data, $var_name) {
        $current_link_name = array_shift($link_names);
        if(count($link_names) == 0) {

            $test = 1;
            if(!isset($datalinks[$current_link_name]['var_forms'][$set_name])) {
                $datalinks[$current_link_name]['var_forms'][$set_name] = array();
            }
            if(!isset($datalinks[$current_link_name]['var_forms'][$set_name][$var_name])) {
                $datalinks[$current_link_name]['var_forms'][$set_name][$var_name] = array();
            }
            $datalinks[$current_link_name]['var_forms'][$set_name][$var_name] = $function_data;

//        $datalink['var_forms'][$set_name]
            update_post_meta($this->mail_id, 'datalinks', $this->datalinks);

        } else {
            $this->rec_update_variable_formats($datalinks[$current_link_name]['links'], $link_names, $set_name, $function_data, $var_name );
        }
        return $datalinks;
    }



    public function &get_datalink_by_path($path) {

       return $this->get_datalink($this->datalinks, 0, $path, "dunno");

    }

    public function &get_datalink(&$datalinks, $depth, $link_names, $target_link_name) {
        $current_link_name = array_shift($link_names);
        if(count($link_names) == 0) {
            //end
            return $datalinks[$current_link_name];
        }

        return $this->get_datalink($datalinks[$current_link_name]['links'], $depth +1, $link_names, $target_link_name );
    }

    public function add_datalink($datalink, $hierarchy_path) {
        $this->rec_add_datalink($this->datalinks, 0, $hierarchy_path, $hierarchy_path[count($hierarchy_path) - 1], $datalink);

    }

    private function rec_add_datalink(&$datalinks, $depth, $link_names, $target_link_name, $datalink_to_add) {
        $current_link_name = array_shift($link_names);
        if(count($link_names) == 0) {

            if($current_link_name === "root") {
                //Edge case: This datalink is meant to be added to the root
                $datalinks[$datalink_to_add['link_name']] = $datalink_to_add;
            }
            else {
                $datalink_to_add['many'] = $this->relationship_is_to_many($datalink_to_add['link_name'], $current_link_name );
                $datalinks[$current_link_name]['links'][$datalink_to_add['link_name']] = $datalink_to_add;
            }

            update_post_meta($this->mail_id, 'datalinks', $this->datalinks);

        } else {
            $this->rec_add_datalink($datalinks[$current_link_name]['links'], $depth +1, $link_names, $target_link_name, $datalink_to_add );
        }
        return $datalinks;
    }

    public function remove_datalink($hierarchy_path) {
        $this->rec_remove_datalink($this->datalinks, 0, $hierarchy_path, $hierarchy_path[count($hierarchy_path) - 1]);
    }

    private function rec_remove_datalink(&$datalinks, $depth, $link_names, $target_link_name) {
        $current_link_name = array_shift($link_names);
        if(count($link_names) == 0) {
            unset($datalinks[$current_link_name]);

            update_post_meta($this->mail_id, 'datalinks', $this->datalinks);

        } else {
            $this->rec_remove_datalink($datalinks[$current_link_name]['links'], $depth +1, $link_names, $target_link_name );
        }
        return $datalinks;
    }

    /**
     * Gets the external table names for a link
     *
     * These include tables that are not included in standard Wordpress Custom Post/Taxonomy functionalities.
     * @param $link_name
     */
    function get_external_table_names($link_name) {
        global $wpdb;

        /** Getting all tables associated by name **/
        $sql = "SELECT DISTINCT TABLE_NAME FROM information_schema.tables  WHERE TABLE_NAME REGEXP '" . $link_name . "[^s]'";
        return $wpdb->get_col($sql);
    }

    private function get_post_term_relationships($link_name) {
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

    private function get_term_post_relationships($link_name) {
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

    private function get_user_term_relationships() {
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

    private function get_post_types() {
        global $wpdb;

        $sql = "SELECT DISTINCT post_type as link_name, 'post' as link_type FROM " . $wpdb->prefix . "posts";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    private function get_taxonomies() {
        global $wpdb;

        $sql = "SELECT DISTINCT taxonomy AS link_name, 'taxonomy' AS link_type FROM " . $wpdb->prefix . "term_taxonomy";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get all the possible datalinks for the current object, regardless of the actual saved datalinks
     *
     * @param $datalink_object - array with link_type and link_name. If null, get ALL possible datalinks
     */
    function get_all_possible_datalinks($datalink_object = null) {
        if(empty($datalink_object['link_type']) || empty($datalink_object['link_name'])) {
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

            $possible_links = array_merge($possible_links, $this->get_post_types(), $this->get_taxonomies());

            return $possible_links;
        }


        switch($datalink_object['link_type']) {
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

               $possible_links = array_merge($possible_links, $this->get_post_term_relationships($datalink_object['link_name']));


                break;
            case "user":
                $possible_links = array (
                    array(
                        'link_name' => 'wp_comments',
                        'link_type' => 'comment',
                        'many' => true
                    )
                );

                $possible_links = array_merge($possible_links, $this->get_user_term_relationships(), $this->get_post_types());

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
                $possible_links = $this->get_term_post_relationships($datalink_object['link_name']);
                break;
        }

        return $possible_links;
    }




    /**
     * In order to get variables, we must have an ID of an object. Here, we get a random one, if not previously configured.
     *
     * TODO: Make it so that the user can configure a standard id
     * TODO: Currently only works for post_types. Make it work for terms as well
     */
    function get_datalink_example_object_id($link_name, $link_type) {
        global $wpdb;

        switch($link_type) {
            case "post":
                $sql = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = %s ORDER BY RAND() LIMIT 1";
                return $wpdb->get_var($wpdb->prepare($sql, array($link_name)));

            case "user":
                $sql = "SELECT ID FROM " . $wpdb->prefix . "users ORDER BY RAND() LIMIT 1";
                return $wpdb->get_var($sql);
            case "taxonomy":
                $sql = "SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE taxonomy = %s ORDER BY RAND() LIMIT 1";
                return $wpdb->get_var($wpdb->prepare($sql, array($link_name)));

            case "comment":
                $sql = "SELECT comment_ID FROM wp_comments ORDER BY RAND() LIMIT 1";
                return $wpdb->get_var($sql);
        }


    }

    /** Get all the variable sets belonging to a link name
     *
     * @var $link_name - name of the object linked to the Mail. example: wc_booking
     * @var $example_id - A randomly selected id of an object of type: $link_name
     *
     * TODO: Currently only works for post_types. Make it work for terms as well
     **/
    function get_datalink_variable_sets($link_type, $link_name, $object_id) {
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
        $table_names = $this->get_external_table_names($link_name);

        $possible_links = array_merge($possible_links, $table_names);


        $variable_sets = array();

        /** Get all the saved datalinks **/
        if(!empty($possible_links)) {
            foreach($possible_links as $link) {
                switch($link){
                    case $wpdb->prefix . "posts":

                        /** post data **/
                        $postdata = get_object_vars(get_post($object_id));

                        $variable_sets['post_data'] = array(
                            'display_name' => 'Post Data',
                            'vars' => $postdata
                        );
                        break;

                    case $wpdb->prefix . "postmeta":
                        $sql = "SELECT DISTINCT meta_key,meta_value FROM ". $wpdb->prefix . "postmeta WHERE post_id = %s";
                        $post_metas = $wpdb->get_results($wpdb->prepare($sql, array($object_id)), ARRAY_A);
                        $obj = array();
                        foreach($post_metas as $post_meta) {
                            $obj[$post_meta['meta_key']] = $post_meta['meta_value'];
                        }

                        $variable_sets['post_meta'] = array(
                            'display_name' => "Post Meta",
                            'vars' => $obj
                        );

                        break;

                    case $wpdb->prefix . "users":

                        $userdata = get_object_vars(get_user_by("ID", $object_id));
                        $variable_sets['user_data'] = array(
                            'display_name' => 'User Data',
                            'vars' => $userdata
                        );
                        break;

                    case $wpdb->prefix . "usermeta":
                        $sql = "SELECT DISTINCT meta_key,meta_value FROM ". $wpdb->prefix . "usermeta WHERE user_id = %s";
                        $user_metas = $wpdb->get_results($wpdb->prepare($sql, array($object_id)), ARRAY_A);
                        $obj = array();
                        foreach($user_metas as $user_meta) {
                            $obj[$user_meta['meta_key']] = $user_meta['meta_value'];
                        }

                        $variable_sets['user_meta'] = array(
                          'display_name' => 'User Meta',
                          'vars' => $obj
                        );
                        break;

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

                    case $wpdb->prefix . "comments":
                        $commentdata = get_object_vars(get_comment($object_id));

                        $variable_sets['comment_data'] = array(
                            'display_name' => 'Comment Data',
                            'vars' => $commentdata
                        );
                        break;

                    case $wpdb->prefix . "commentmeta":
                        $sql = "SELECT DISTINCT meta_key,meta_value FROM ". $wpdb->prefix . "commentmeta WHERE comment_id  = %s";
                        $comment_metas = $wpdb->get_results($wpdb->prepare($sql, array($object_id)), ARRAY_A);
                        $obj = array();
                        foreach($comment_metas as $comment_meta) {
                            $obj[$comment_meta['meta_key']] = $comment_meta['meta_value'];
                        }

                        $variable_sets['comment_meta'] = array(
                            'display_name' => 'Comment Meta',
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
     * Define the type of the relation between child and parent
     * We define the default as single, returning false
     *
     * @param $child
     * @param $parent
     */
    public function relationship_is_to_many($child, $parent) {
        switch($parent) {
            case 'wc_booking':
                switch($child) {
                    case 'wp_comments':
                        return true;
                }
            case 'post' :
                switch($child) {
                    case 'comment':
                        return true;
                    default:
                        return false;
                }
            case 'user' : {
                switch($child) {
                    case 'comment':
                        return true;
                    default:
                        return false;
                }
            }
            case 'taxonomy' : {
                switch($child) {
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

    /**
     * Gathers all the data belonging to the Datalink.
     * With this, the DataLink object will contain the actual values of its variables
     *
     * @param $root_ids - The id's of the root datalinks
     */
    public function Gather_mail_data($root_ids) {

        //Wat moeten we hiervoor doen?
        //We moeten iedere datalink nagaan. Dan krijgen we dus een relationship. Die moeten we gebruiken!

        // Voorlopig hoeven we alleen IDs te pakken.
        // Dit kan alleen als je al bij de child bent. We moeten hiervoor een speciale mapping voor hebben.
        //      wp_post ID      ->   wp_comment  comment_post_ID
        //
        // Cool zou zijn als we een aparte file hebben. Gewoon een assoc array met strings:
        //      wp_post
        //
        foreach($root_ids as $link_name => $id) {

            $this->datalinks[$link_name]['id'] = $id;
            $link_type = $this->datalinks[$link_name]['type'];

            //Get the data
            $dataset = array();
            $dataset = apply_filters("mc-get_data-$link_type", $dataset, $id);
            $dataset = apply_filters("mc-get_data-$link_name", $dataset, $id);
            $this->datalinks[$link_name]['data'] = $dataset;

            $this->datalinks[$link_name] = $this->get_datalink_dataset($this->datalinks[$link_name], null );

        }
    }


    public function &get_datalink_dataset(&$datalink, $parent) {

        if(!isset($datalink['data'])) {
            $dataset = array();
            $child_name = $datalink['link_name']; $child_type = $datalink['type'];
            $parent_name = $parent['link_name'];  $parent_type = $parent['type'];
            $dataset = apply_filters("mc-get_data-$child_name-$parent_name", $dataset, $parent['id']);
            $dataset = apply_filters("mc-get_data-$child_type-$parent_type", $dataset, $parent['id']);
            $datalink['data'] = $dataset;

//            $dataset = apply_filters("mc-get_data-$link_type", $dataset, $id);
//            $dataset = apply_filters("mc-get_data-$link_name", $dataset, $id);
//            $datalink['data'] = $dataset;
        }

        foreach($datalink['links'] as $link_name => $link) {
            $datalink['links'][$link_name] = $this->get_datalink_dataset($link, $datalink );

        }

        return $datalink;
    }

    /** Gets all the meta keys belonging to $object_id, of $object_type **/
    function get_datalink_meta_keys($object_id, $object_type) {
        global $wpdb;

        $sql = "SELECT DISTINCT meta_key FROM ". $wpdb->prefix . $object_type . "meta WHERE " . $object_type . "_id = %s";
        $keys = $wpdb->get_results($wpdb->prepare($sql, array($object_id)));

        return $keys;
    }

}
