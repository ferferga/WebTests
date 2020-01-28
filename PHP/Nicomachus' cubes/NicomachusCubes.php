<?php
    set_time_limit(86400);
    if (isset($_POST["number"]))
    {
        $output = "";
        $input = $_POST["number"];
        $last_odd = 0;
        for ($i = 1; $i < ($input+1); $i++)
        {
            $sum = "";
            $cube = pow($i, 3);
            $raw_sum = 0;
            $counter = 0;
            $j = $last_odd;          
            while(TRUE)
            {
                if ($counter == $i)
                {
                    break;
                }             
                if ($j%2 != 0)
                {
                    if ($sum == "")
                    {
                        $sum = $j;
                    }
                    else
                    {
                        $sum = $sum." + ".$j;
                    }
                    $counter++;
                }
                $j++;                
            }
            $last_odd = $j;
            $output = $output."<b>".$i."^3</b> = ".$sum." = ".$cube."</br>";
        }
        echo($output);
    }
?>

<!DOCTYPE html>
    <head>

    </head>
    <body>
        <form action="NicomachusCubes.php" method="POST">
            <label for="rows">How many Nicomachu's cubes do you want to generate?: </label>            
            <input type="number" name="number"/>
            <label for="rows">
                <input type="submit"/>
            </label>
        </form>
    </body>
</html>