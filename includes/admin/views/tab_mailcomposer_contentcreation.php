<?php

/**
 * Content creation tab on the Mail CPT
 *
 * This tab contains the table with each datalink, recursively rendered
 * Right next to it, is the Variables dropdown, in which the variable set and formatting configuration is displayed
 *
 * This tab contains the GrapesJS template builder
 * Right next to it, is a simplified Variables dropdown, with clear names for the variable names.
 *
 * @var $possible_link_types - a list of distinct post types
 * @var $DataLinks - The datalinks of the current Mail CPT
 */


?>
<!--<link rel="stylesheet" href="//unpkg.com/grapesjs/dist/css/grapes.min.css">-->
<!--<script src="//unpkg.com/grapesjs"></script>-->



<style>
    #gjs {
        border: 3px solid #444;
    }

    /* Reset some default styling */
    .gjs-cv-canvas {
        top: 0;
        width: 100%;
        height: 100%;
    }

    .gjs-block {
        width: auto;
        height: auto;
        min-height: auto;
    }

    #tab_contentcreation {
        display: flex;
        flex-direction: row;
        height:1200px;
    }

    #tab_contentcreation .content_editor {
        flex:4;
        display:flex;
        flex-direction: column;
    }

    #tab_contentcreation .right_panel {
        flex:1;
        display: flex;
        flex-direction: column;
    }

    .right_panel .top {
        position: inherit;
        display: flex;
        min-height: 50px;
    }
    #tab_contentcreation .bot {
        position: inherit;
        flex:30
    }

    .commands_bar.top {
        position: inherit;
        flex:1;
        display: flex;
        flex-direction: row;
        min-height: 50px;
        justify-content: space-between;
        align-items: center;
    }

    .commands_bar button {
        cursor: pointer;
        transition: background-color 0.5s, box-shadow 0.2s;
        border: 1px solid #c3c4c7;
        border-radius: 2px;
    }

    .commands_bar button:hover {
        box-shadow: 0px 4px 4px -1px #c5c6c6;
    }

    .commands_bar button.btn_active {
        background-color: #2271B1;
        fill: #eaeaea;
    }

    .commands_bar svg {
        width: 22px;
        height: 22px;
    }

    .commands_bar > div {
        padding: 0px 10px 0px 15px;
    }

    .mail_composer_body {
        background: #f6f7f7;
        border: 1px solid #c3c4c7;
    }

    /* We can remove the border we've set at the beginnig */
    #gjs {
        border: none;
    }

    .gjs-pn-btn{
        transition: 0.3s;
    }

    /* Theming */

    /* Primary color for the background */
    .gjs-one-bg {
        background-color: #f6f7f7;
    }

    /* Secondary color for the text color */
    .gjs-two-color {
        color: #3c434a;
    }

    /* Tertiary color for the background */
    .gjs-three-bg {
        background-color: #2271B1;
        color: #3c434a;
    }

    /* Quaternary color for the text color */
    .gjs-four-color,
    .gjs-four-color-h:hover {
        background-color: #2271B1;
        color: #eaeaea;

    }

    .gjs-pn-btn.gjs-pn-active{
        background-color: #2271B1;
        color: #eaeaea;
    }

    .blocks svg {
        width: 50px;
        height: 50px;
    }

    .sp-preview {
        /*display: none;*/
    }


    .ghost_cp .sp-dd {
        padding-top: 3px;
    }

    .ghost_cp .sp-replacer {
        background-color: #ffffff8c;
        display:flex;
    }

    .sp-dd svg {

        fill: white;
    }


    #tab_contentcreation .gjs-toolbar {
        background-color: transparent;
        pointer-events: none;
        top:-30px;
    }

    .gjs-toolbar-item {
        background-color: #2271B1;
        pointer-events: all;
        width:auto !important;
    }

    .gjs-toolbar-item > .sp-dd {
        position: relative;
    }

    gjs-toolbar-item > sp-replacer {
        overflow: inherit;
    }

    .gjs-toolbar-item > div {
        position: relative;
    }

    .gjs-toolbar-item > div svg {
        height: 17px;
        width: 16px;
    }

    /** CUSTOM TOOLBAR **/

    .items_container {
        cursor: default;
    }

    .custom_tb_l {
        /*transform: translate(520px, 0px);*/
        display: flex;
        flex-direction: column;
        position: absolute;
        background-color: #2271B1;
    }

    .custom_tb_l .gjs-toolbar-item {
        padding:0px;
    }


    .custom_tb_l .category_container > div {
        transition: padding 0.3s;

        padding: 5px;
    }

    .custom_tb_l .category_container .collapsed {
        padding:0px 5px 0px 5px !important;
    }

    .custom_tb_r > div {
        width:auto;
    }


    .tb_bgcolorpicker {
    }

    .gjs-toolbar > div > div {
        box-shadow: 8px 4px 8px 0px #19202682;

    }
    .custom_tb_t {
        /*transform: translate(120px, 0px);*/
        bottom:10px;
        display: flex;
        flex-direction: column;
        position: absolute;
        background-color: #2271B1;
    }

    .tb_hrefbar {
        width: auto;
    }

    .hrefbar_container .cke_top {
        display: none !important;
    }

    .hrefbar_container body{
        padding: 0px;
        overflow: hidden;

    }

    .hrefbar_container .cke_editable {
        font-size: 13px;
        line-height: 1 !important;
        word-wrap: break-word;
    }

    .custom_tb_l .category_title {
        display:flex;
        align-items: center;
        background-color: #0000004a;
        font-weight: bold;
        border-bottom: 1px inset white;

    }

    .tb_l_section_label {
        text-align: center;
        padding: 5px 0px 5px 0px;
        background-color: #00000029;
        margin-bottom: 4px;
    }

    .tb_l_item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tb_l_item .item_name {
        margin-right:10px;
        flex:1;
    }


    /** Slider CSS **/
    .tb_l_item .slider_value {
        padding: 10px;
        width: 40px;
    }


    .tb_l_item .slider {
        -webkit-appearance: none;  /* Override default CSS styles */
        appearance: none;
        width: 75px; /* Full-width */
        height: 2px; /* Specified height */
        background: #d3d3d3; /* Grey background */
        outline: none; /* Remove outline */
        opacity: 0.7; /* Set transparency (for mouse-over effects on hover) */
        -webkit-transition: .2s; /* 0.2 seconds transition on hover */
        transition: opacity .2s;
        flex:1;
    }

    .tb_l_item .slider:hover {
        opacity: 1; /* Fully shown on mouse-over */
    }

    .tb_l_item .slider::-webkit-slider-thumb {
        -webkit-appearance: none; /* Override default look */
        appearance: none;
        width: 15px; /* Set a specific slider handle width */
        height: 15px; /* Slider handle height */
        background: #04AA6D; /* Green background */
        cursor: pointer; /* Cursor on hover */
    }

    .tb_l_item .slider::-moz-range-thumb {
        width: 15px; /* Set a specific slider handle width */
        height: 15px; /* Slider handle height */
        background: #04AA6D; /* Green background */
        cursor: pointer; /* Cursor on hover */
    }

    .tb_l_item select {
        flex : 1.2;
    }


    .border_radius_preset_container {
        display:flex;
        flex-direction:row;
        justify-content: space-evenly;
    }
    .border_preset {
        border: 1px solid #ffffff78;
        padding:5px;
        width:30px;
        height:30px;
        background-color: transparent;
        cursor: pointer;
    }

    .border_preset > div {
        height:100%;
        width:100%;
        border: 2px solid white;
    }

    .border_preset:hover {
        background-color: darkblue;
    }

    .square_div {
    }

    .oval_div {
        border-radius: 6px;
    }

    .circle_div {
        border-radius: 50%;
    }

    .image_input_container {
        flex:3;
        height: 50px;

    }

    .image_input_container .img{
        height: 50px;
        background-size: 100% 50px;
        cursor:pointer;
        border: 1px solid black;
        background-color: #6262627d;
    }


    .image_input_container .img_delete {
        width: 17px;
        height: 17px;
        cursor:pointer;
        float:right;
        color:red;
        text-align: center;
        transition:background-color 0.3s;
    }

    .image_input_container .img_delete:hover {
        background-color: rgba(33, 55, 92, 0.44)

    }


</style>

<div id="tab_contentcreation" class="mail_composer_tab">
    <div class="content_editor">
        <div class="commands_bar top">

            <div class="devices">
                <button type="button" id="desktop">
                    <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/desktop.svg'); ?>
                </button>

                <button type="button" id="tablet">
                    <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/tablet.svg'); ?>
                </button>

                <button type="button" id="mobilePortrait">
                    <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/mobile.svg'); ?>
                </button>
            </div>

            <div class="editing_tools">
                <button type="button" id="fullscreen">
                    <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/fullscreen.svg'); ?>
                </button>
                <button type="button" id="preview" data-stateful="true" >
                    <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/eye.svg'); ?>
                </button>
                <button type="button" id="open-code">
                    <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/code.svg'); ?>
                </button>
                <button type="button" id="undo">
                    <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/undo.svg'); ?>
                </button>
                <button type="button" id="redo">
                    <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/redo.svg'); ?>
                </button>
                <button type="button" id="canvas-clear">
                    <?php echo file_get_contents(ARK_MAIL_COMPOSER_ROOT_URI . '/assets/icons/erase.svg'); ?>
                </button>
                <button type="button" id="save_content">
                    Save
                </button>
            </div>


        </div>
        <div id="gjs" class="bot">

        </div>

    </div>

    <div class="right_panel">
        <div class="creation_tools_nav top">

        </div>

        <div class="creation_tools bot">
            <div class="blocks">
            </div>
            <div class="styles">
            </div>

            <div class="variable_overview">
            </div>

            <div class="traits">

            </div>
        </div>
    </div>




</div>
