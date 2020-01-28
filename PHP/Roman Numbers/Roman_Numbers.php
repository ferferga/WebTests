<?php
    set_time_limit(86400);
    error_reporting(E_ERROR | E_PARSE);
    require_once 'Numbers/Roman.php';
    if (isset($_POST["value"]))
    {
        $input = $_POST["value"];
        if (is_numeric($input))
        {
            $input = round($input);
            if($input <= 0)
            {
                echo("<b>The input is invalid. 0 or negative numbers are not valid</b>");
            }
            else
            {
                $roman = Numbers_Roman::toNumeral($input);
                echo("Performed <b>Arabic->Roman</b> conversion: ".$roman."</br></br>");
            }            
        }
        else
        {
            $roman = Numbers_Roman::toNumber($input);
            if ($roman == 0 || $roman == "")
            {
                echo("<b>The input is invalid. This isn't recognised as a Roman or arabic number</b>");
            }
            else
            {
                echo("Performed <b>Roman->Arabic</b> conversion: ".$roman."</br></br>");
            }
        }
    }
?>

<!DOCTYPE html>
    <head>
        <title>Roman-Arabic conversor</title>
    </head>
    <body>
        <form action="Roman_Numbers.php" method="POST">
            <label for="value">Please, input the number that we are going to convert: </label>
            <input type='text' name='value' />
            <label for="value">
                <input type="submit"/>
            </label>            
        </form>
    </body>
</html>