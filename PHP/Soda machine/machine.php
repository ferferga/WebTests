<?php
    $choice = $_POST["sodas"];
    $money = $_POST["money"];

    switch($choice){
        case "coke":
            $money -= 0.8;
            $price = "0.80";
            break;
        case "h2o":
            $money -= 0.5;
            $price = "0.50";
            break;
        case "cruzcampo":
            $money -= 1;
            $price = "1";
            break;
    }
    // $return = $_POST["money"] - $money;
    echo '<script language="javascript">';
    echo 'alert("Inserted money: '.$_POST["money"].'€\nValue of the product chosen: '.$price.'€\nExchange: '.$money.'€\n\nEnjoy your drink!")';
    echo '</script>';
?>