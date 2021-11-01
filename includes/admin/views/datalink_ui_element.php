<?php
/** Renders the DataLink UI
 * @var $post_id - The od of the current Mail CPT
 * @var $datalinks - The datalinks of the current Mail CPT
 * @var $DataLinks
 **/

?>
    <style>
        #datalink_list {
            border: 1px solid #c3c4c7;
        }

        #datalink_list .datalink_ui_single_link {
            border-bottom: 1px solid #c3c4c7;
        }

        .datalink_title {
            display:flex;
            border-bottom:1px solid #c3c4c7;
            cursor: pointer;
            transition: 0.3s background-color;
        }

        .datalink_possible_set {
            display:flex;
            border-bottom:1px solid #c3c4c7;
        }

        .datalink_title:hover{
            background-color: #f3f3f3;
        }

        .datalink_content_holder {
            padding: 5px 5px 5px 10px;
            transition:height 0.5s;
            flex:1
        }

        button.mailstrom_display_toggle {
            width:25px;
            height:25px;
            cursor:pointer;
            padding:0px;
            text-align: center;
            margin-left:auto;

            border:0px;
            background-color:transparent;
        }
        button.mailstrom_display_toggle .mailstrom_toggle-indicator::before{
            content: "\f142";
            font: normal 25px/1 dashicons;
            display:inline-block;

            position: relative;
            top: .05rem;
            text-indent: -2px;
        }

        button.mailstrom_display_toggle .mailstrom_toggle-indicator.expanded::before{
            content: "\f140";
            font: normal 20px/1 dashicons;
            display:inline-block;

            position: relative;
            top: .05rem;
            text-indent: -2px;
        }

        .collapsible-wrapper {
            display: flex;
            overflow: hidden;
        }

        .collapsible {
            transition: margin-bottom 0.3s;
            margin-bottom: 0;
            max-height: 1000000px;
            max-width:100%
        }

        .collapsible-wrapper.collapsed > .collapsible {
            margin-bottom: -2000px;
            transition: margin-bottom 0.3s, visibility 0.3s, max-height 0.3s;
            visibility: hidden;
            max-height: 0;
        }

        .datalink_available_variable {
            display:inline-block;
            font-size: 10px;
        }


        .datalink_variable_container {
            border: 1px solid #c3c4c7;
        }
        .datalink_variable_container_title {
            display:flex;
            padding:10px;
            border-bottom: 1px solid #c3c4c7;
            background-color: #f3f3f3;

        }
        .datalink_variable_container_header {
            border-bottom: 1px solid #c3c4c7;
        }

        .datalink_variable_container > div {
        }

        .datalink_variable_container_body {
            max-height:300px;
            overflow-y: overlay;
        }

        .datalink_variable_container .row {
            width: 100%;
            overflow:auto;
            display:inline-flex;
        }
        .datalink_variable_container .col {
            padding:10px;
            overflow:auto;
            flex: 1;        /* distributes space on the line equally among items */
            border-right: 1px solid #c3c4c7
        }

        .col-content {
            background-color:transparent;
        }

        .datalink_variable_container .col:last-child {
            flex: 1;        /* distributes space on the line equally among items */
            border-right: 0px
        }

        .datalink_active {
            background-color: #f3f3f3;
        }

        .row.uneven {
            background-color: #f3f3f3;
        }

        button.save_datalink_settings{
            margin-left: auto;
            cursor: pointer;
            background-color: #2271b1;
            color: #fff;
            text-shadow: none;
            border: none;
            text-decoration: none;
            padding: 5px;
            border-radius: 3px;
            transition:background-color 0.4s;
        }

        button#save_datalink_settings:hover {
            background-color: #205ba0;
        }

        button.add_datalink_variable_set {
            margin-left: auto;
            margin-right:5px;
            cursor: pointer;
            background-color: #2271b1;
            color: #fff;
            text-shadow: none;
            border: none;
            text-decoration: none;
            padding: 5px;
            border-radius: 3px;
            transition:background-color 0.4s;
        }

        button.add_datalink_variable_set:hover {
            background-color: #205ba0;
        }


        .datalink_row {
            padding:10px;
            display:flex;
            flex-flow: row nowrap;
        }

        .datalink_name {
            flex: 4 ;
        }
        .datalink_desc {
            flex: 4 ;
        }
        .datalink_btn_vars {
            flex: 1 ;
        }
        .datalink_btn_delete {
            flex: 1 ;
        }
        .datalink_btn_add{
            flex: 1 ;
        }

    </style>
<?php

foreach($DataLinks->datalinks as $object_type => $object_config ) {//object_type: wc_booking?>
        <?php $tet = 1;?>
    <div class="datalink_row">
        <div class="datalink_name">
            <?php echo "name"; ?>
        </div>
        <div class="datalink_desc">
            <?php echo "description"; ?>
        </div>
        <div class="datalink_btn_vars">
            vars
        </div>
        <div class="datalink_btn_delete">
            del
        </div>
        <div class="datalink_btn_add">
            add
        </div>
    </div>
    <?php //Render nu de huidige item's datalinks. Recursie? ?>
<?php }
//
//foreach($DataLinks->datalinks as $link_type => $links) :
//    /** Link type is post_type or taxonomy name **/
//    foreach($links as $link_name => $link_config) : ?>
<!---->
<!--        --><?php //$object_id = $DataLinks->get_datalink_example_object_id($link_name); ?>
<!---->
<!--        --><?php //$possible_sets = $DataLinks->get_all_possible_datalinks($link_type, $link_name); ?>
<!--        --><?php //$variable_sets = $DataLinks->get_datalink_variable_sets($link_type, $link_name, $object_id);?>
<!---->
<!--        --><?php //$possible_sets = array_diff($possible_sets, array_keys($variable_sets)); ?>
<!---->
<!--        <div class="datalink_title datalink_content_holder">-->
<!--            <strong style="margin-top: 5px; font-size:15px">--><?php //echo ucfirst($link_type) . " : " . $link_name; ?><!--</strong>-->
<!---->
<!--            <button type="button" class="mailstrom_display_toggle" data-target="datalink_menu_--><?php //echo $link_name;?><!--">-->
<!--                <span class="mailstrom_toggle-indicator" ></span>-->
<!--            </button>-->
<!--        </div>-->
<!--        <div class="collapsible-wrapper collapsed" id="datalink_menu_--><?php //echo $link_name;?><!--">-->
<!--            <div  class="datalink_content_holder collapsible">-->
<!--                <p style="text-align: center;">-->
<!--                    <strong>Variables</strong> - example ID used: --><?php //echo $object_id; ?>
<!--                </p>-->
<!---->
<!--                <!-- Render UI for all datalinks that have been fully configured -->-->
<!--                --><?php //foreach($variable_sets as $set_name => $variable_set):?>
                    <div class="datalink_title datalink_content_holder" style="border: 1px solid #c3c4c7;">
                        <strong style="margin-top: 5px; font-size:15px"><?php echo str_replace(" ", "_", $set_name); ?></strong>

                        <button type="button" class="mailstrom_display_toggle" data-target="datalink_menu_<?php echo strtolower(str_replace(" ", "_", $set_name));?>">
                            <span class="mailstrom_toggle-indicator" ></span>
                        </button>
                    </div>

                    <div class="collapsible-wrapper collapsed" id="datalink_menu_<?php echo strtolower(str_replace(" ", "_", $set_name)); ?>">
                        <div class="collapsible">
                            <div class="datalink_variable_container">
                                <div class="datalink_variable_container_title">
                                    <div class="changes_count"></div>
                                    <button type="button" class="save_datalink_settings" disabled>Save settings</button>
                                    <br>
                                </div>
                                <div class="datalink_variable_container_header">
                                    <div class="row">
                                        <div class="col">
                                            Variable
                                        </div>
                                        <div class="col">
                                            Example value
                                        </div>
                                    </div>
                                </div>
                                <div class="datalink_variable_container_body">
                                    <?php $key_iterator = 0;

                                    foreach($variable_set as $key => $value) : ?>
                                        <div class="datalink_available_variable row <?php echo $key_iterator % 2 == 0 ? "uneven" : "";?>">
                                            <div class="col">
                                                <input name="<?php echo $key; ?>" type="checkbox"/>

                                                <label><strong> <?php echo "{ms_var_$key}"; ?></strong></label>
                                            </div>
                                            <div class="col">
                                                <div class="col-content">
                                                    <?php print_r( $value); ?>

                                                </div>
                                            </div>
                                        </div>
                                        <?php $key_iterator += 1; endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
<!--                --><?php //endforeach; ?>
<!---->
<!--                <!-- Render UI for all possible datalinks that can be added -->-->
<!--                --><?php //foreach($possible_sets as $set_name) :?>
<!---->
<!--                    <div class="datalink_possible_set datalink_content_holder" style="border: 1px solid #c3c4c7;">-->
<!--                        <strong style="margin-top: 5px; font-size:15px">--><?php //echo str_replace(" ", "_", $set_name); ?><!--</strong>-->
<!---->
<!--                        <button type="button" class="add_datalink_variable_set">Add to datalink</button>-->
<!---->
<!--                    </div>-->
<!---->
<!--<!--                    <div class="collapsible-wrapper collapsed" id="datalink_menu_-->--><?php ////echo strtolower(str_replace(" ", "_", $set_name)); ?><!--<!--">-->-->
<!--<!--                        <div class="collapsible">-->-->
<!--<!--                            <div class="datalink_variable_container">-->-->
<!--<!--                                <div class="datalink_variable_container_title">-->-->
<!--<!--                                    <div class="changes_count"></div>-->-->
<!--<!--                                    <button type="button" class="save_datalink_settings" disabled>Save settings</button>-->-->
<!--<!--                                    <br>-->-->
<!--<!--                                </div>-->-->
<!--<!--                                <div class="datalink_variable_container_header">-->-->
<!--<!--                                    <div class="row">-->-->
<!--<!--                                        <div class="col">-->-->
<!--<!--                                            Variable-->-->
<!--<!--                                        </div>-->-->
<!--<!--                                        <div class="col">-->-->
<!--<!--                                            Example value-->-->
<!--<!--                                        </div>-->-->
<!--<!--                                    </div>-->-->
<!--<!--                                </div>-->-->
<!--<!--                                <div class="datalink_variable_container_body">-->-->
<!--<!--                                    -->--><?php ////$key_iterator = 0;
////
////                                    foreach($variable_set as $key => $value) : ?>
<!--<!--                                        <div class="datalink_available_variable row -->--><?php ////echo $key_iterator % 2 == 0 ? "uneven" : "";?><!--<!--">-->-->
<!--<!--                                            <div class="col">-->-->
<!--<!--                                                <input name="-->--><?php ////echo $key; ?><!--<!--" type="checkbox"/>-->-->
<!--<!---->-->
<!--<!--                                                <label><strong> -->--><?php ////echo "{ms_var_$key}"; ?><!--<!--</strong></label>-->-->
<!--<!--                                            </div>-->-->
<!--<!--                                            <div class="col">-->-->
<!--<!--                                                <div class="col-content">-->-->
<!--<!--                                                    -->--><?php ////print_r( $value); ?>
<!--<!---->-->
<!--<!--                                                </div>-->-->
<!--<!--                                            </div>-->-->
<!--<!--                                        </div>-->-->
<!--<!--                                        -->--><?php ////$key_iterator += 1; endforeach; ?>
<!--<!--                                </div>-->-->
<!--<!--                            </div>-->-->
<!--<!--                        </div>-->-->
<!--<!--                    </div>-->-->
<!--                --><?php //endforeach; ?>
<!--            </div>-->
<!--        </div>-->
<!---->
<!--    --><?php //endforeach;
//endforeach;

