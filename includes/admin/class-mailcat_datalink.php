<?php
include_once (ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/class-datalink_utils.php");
include_once (ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/fetchers/class-mailcat_standard_fetcher.php");
include_once (ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/fetchers/class-mailcat_custom_fetcher.php");
include_once (ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/fetchers/class-mailcat_acf_fetcher.php");
include_once (ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/fetchers/class-mailcat_wc_fetcher.php");
include_once (ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/fetchers/class-mailcat_wc_bookings_fetcher.php");

class Ark_DataLink {
    public int $ID = 0;
    public int $db_id = 0;          //ID of associated object in Database / Datastore
    public string $type;            //Link type. Ex: 'post', 'taxonomy', 'user', 'custom'
    public string $name;            //Name of the link. Ex: 'wc_booking_2',
    public string $desc;            //User submitted description
    public bool $many = false;      //Does parent hold Many of this datalink?
    public $parent    = null;       //Parent DataLink
    public $links     = array();    //Children Datalinks
    public $var_forms = array();    //Variable Formatting
    public $data      = array();    //Data container (contains actual values)
    public $link_spec = array();    //Link specification, such as Taxonomies, terms, etc

    public function __construct($data) {
        if(!isset($data['type'])) {
            throw new Exception("Missing link type in new DataLink");
        }
        if(!isset($data['name'])) {
            $data['name']  = DataLink_Utils::get_name_from_spec($data['type'], $data['link_spec']);
        }
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
        $example_id = apply_filters("mc-get_example_id-" . $this->name, $this, $example_id);
        if(!is_numeric($example_id) || $example_id == 0) {
            $example_id = apply_filters("mc-get_example_id-" . $this->type, $this, $example_id);
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


















