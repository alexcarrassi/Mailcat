<?php
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
