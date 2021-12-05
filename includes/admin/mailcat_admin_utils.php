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