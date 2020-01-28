<?php
    set_time_limit(86400);
    $months = array();
    for ($i = 1; $i < 14; $i++) 
    {
        $timestamp = mktime(0, 0, 0, $i, 1);
        $months[date('n', $timestamp)] = date('F', $timestamp);
    }
?>

<!DOCTYPE html>
    <head>

    </head>
    <body>
        <form action="Months.php" method="POST">        
            <b>This program will check how many days a month has, taking in account leap years.</b></br></br>
            <label for="months">Please, choose the month that do you want to check: </label>
            <select name="months">
                <?php
                    foreach($months as $index=>$month){
                        echo("<option value='".$index."'>".$month."</option>");
                      }
                ?>
            </select>
            <label for="months">
                <input type="submit"/>
            </label>
        </form>
        </br>
        </br>
        <?php
            if (isset($_POST["months"]))
            {
                $leapyear = date("L");
                $number = cal_days_in_month(CAL_GREGORIAN, $_POST["months"], date('Y'));
                echo("The month of ".$months[$_POST["months"]]." has ".$number." days.</br></br>");
                if ($leapyear == 1)
                {
                    echo("Also, ".date('Y')." <b>is</b> a leap year");
                }
                else
                {
                    echo("Also, ".date('Y')." <b>is not</b> a leap year");
                }
            }
        ?>
    </body>
</html>