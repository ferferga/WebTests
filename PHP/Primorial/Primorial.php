<?php
    set_time_limit(86400);
    function is_prime($number)
    {
        // 1 is not prime
        if ($number == 1) 
        {
            return false;
        }
        // 2 is the only even prime number
        if ($number == 2) {
            return true;
        }
        // square root algorithm speeds up testing of bigger prime numbers
        $x = sqrt($number);
        $x = floor($x);
        for ($i = 2; $i <= $x; ++$i) 
        {
            if ($number%$i == 0) 
            {
                break;
            }
        }    
        if($x==$i-1) 
        {
            return true;
        } 
        else 
        {
            return false;
        }
    }
    if (isset($_POST["value"]))
    {
        $input = $_POST["value"];
        $output = "</br><b>".$input."#</b> = ";
        $first = true;
        $factorial = 1;
        for ($i = $input; $i >= 1; $i--)
        {
            if(is_prime($i) && $first)
            {
                $output = $output.$i;
                $factorial=$factorial*$i;
                $first = false;
            }
            else if (is_prime($i) && !$first)
            {
                $output = $output." * ".$i;
                $factorial=$factorial*$i;
            }
        }
        $output = $output." = ".$factorial;        
    }
?>

<!DOCTYPE html>
    <head>

    </head>
    <body>
        <form action="Primorial.php" method="POST">
            <b>This is a primorial checker app. A primorial is a factorial that only multiplies using prime numbers.</b></br>
            <label for="phrase">Please, input the number that we are going to calculate: </label>            
            <input type="number" name="value"/>
            <label for="phrase">
                <input type="submit"/>
            </label>
        </form>
        <?php
            echo($output);
        ?>
    </body>
</html>