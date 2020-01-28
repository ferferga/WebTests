<?php
    set_time_limit(86400);
    $output = "· Original array: ";
    $array = array(6,4,9,10,25,89,75,102,36,45,21,78);
    foreach($array as $a)
    {
        $output = $output.$a.", ";
    }
    $output = substr_replace($output,"", -2);
    sort($array);
    $output = $output."</br></br>· Sorted array: ";
    foreach($array as $a)
    {
        $output = $output.$a.", ";
    }
    $output = substr_replace($output,"", -2);
    echo($output);
?>