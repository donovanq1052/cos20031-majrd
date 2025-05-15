<?php

function sanitise_input($input_str) {
    $input_str = trim($input_str);
    $input_str = stripslashes($input_str);
    $input_str = htmlspecialchars($input_str);
    return $input_str;
}