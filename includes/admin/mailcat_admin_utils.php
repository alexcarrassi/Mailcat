<?php

/**
 * Constructs a neat string from a MailCat error origin object
 * @param $origin
 */
function errorlog_construct_origin_string($origin) {

    $error_string = "";
    $error_string .=  isset($origin['class']) ? "<strong>Class: </strong> " . $origin['class'] . "<br>" : "";
    $error_string .= "<strong>Function: </strong> " . $origin['function'] . "<br>";
    $error_string .= "<strong>File: </strong> " . $origin['file'] . " :" . $origin['line'];

    return $error_string;
}


/**
 * Constructs a neat string from a MailCat render error ids object
 * @param $origin
 */
function errorlog_construct_ids_string($ids) {

    $error_string = "";
    foreach($ids as $key => $value) {
        $error_string .= "<strong>$key</strong> : $value <br>";
    }
    return $error_string;
}
function compare_id_errors($error1, $error2) {

    if(isset($error1['data']) && isset($error2['data'])) {
        $error1_data = $error1['data']; $error2_data = $error2['data'];

        $error1_data['invalid_ids'] = isset($error1_data['invalid_ids']) ? $error1_data['invalid_ids'] : null;
        $error1_data['valid_ids'] = isset($error1_data['valid_ids']) ? $error1_data['valid_ids'] : null;
        $error1_data['missing_ids'] = isset($error1_data['missing_ids']) ? $error1_data['missing_ids'] : null;

        $error2_data['invalid_ids'] = isset($error2_data['invalid_ids']) ? $error2_data['invalid_ids'] : null;
        $error2_data['valid_ids'] = isset($error2_data['valid_ids']) ? $error2_data['valid_ids'] : null;
        $error2_data['missing_ids'] = isset($error2_data['missing_ids']) ? $error2_data['missing_ids'] : null;

        if($error2_data['invalid_ids'] == $error1_data['invalid_ids'] &&
           $error2_data['valid_ids']   == $error1_data['valid_ids']   &&
           $error2_data['missing_ids'] == $error1_data['missing_ids']    ) {

           if($error1['origin'] == $error2['origin']) {
               return true;
           }
        }
    }

    return false;
}

function compare_render_errors($error1, $error2) {
    if($error1['data']['ids'] == $error2['data']['ids'] ) {
        if($error1['origin'] == $error2['origin']) {
            return true;
        }
    }

    return false;


}

function render_errortab_badge($errors) {
    $total_count = 0;

    if($errors != null) {
        $total_count += isset($errors['id']) ? count($errors['id']) : 0;
        $total_count += isset($errors['render']) ? count($errors['render']) : 0;
    }
    ?> <div id="badge_error_count" class="<?php echo $total_count == 0 ? "noerrors" : "haserrors"; ?>">
        <?php echo $total_count < 100 ? $total_count : "99+"; ?>
    </div> <?php
}