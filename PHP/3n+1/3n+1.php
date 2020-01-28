<?php
    set_time_limit(86400);
    $done = false;
?>

<!DOCTYPE html>
    <head>
        <title>3n+1 algorithm</title>
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }
            td, th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }
        </style>
    </head>
    <body>
        <form action="3n+1.php" method="POST">
            <label for="value1">Type here the first number of the range: </label>
            <input type='number' name='value1' />
            </br></br>
            <label for="value2">Type here the last number of the range: </label>
            <input type='number' name='value2' />
            </br></br>
            <input type="submit"/>
            </br></br>
        </form>
            <?php
                if (!isset($_POST["value1"]) && !isset($_POST["value2"]))
                {
                    echo("<b>No data in both ranges was specified</b>");
                }
                else
                {
                    $first = $_POST["value1"];
                    $last = $_POST["value2"];
                    if (!($first > 0 && $first < 10000) && ($last > 0 && $last < 10000))
                    {
                        echo("<b>The range is not between 1 and 9999");
                    }
                    else if ($first > $last)
                    {
                        echo("<b>The first number of the range can't be higher than the last one</b>");
                    }
                    else
                    {   
                        $counter=0;
                        for ($j = $first; $j < ($last+1); $j++)
                        {
                            $k=$j;
                            $loopcounter=1;
                            while($k != 1)
                            {
                                if ($k%2 != 0)
                                {
                                    $k = (3*$k)+1;
                                }
                                else
                                {
                                    $k = ($k/2);
                                }
                                $loopcounter++;
                            }
                            if($loopcounter > $counter)
                            {
                                $counter=$loopcounter;
                            }
                        }
                        $done = true;
                    }
                }
            ?>
            <?php
                if($done)
                {
                    echo("<table>\n");
                }
                else
                {
                    echo("<table style='display: none'>\n");
                }
            ?>
                <tr>
                    <?php
                        if ($done)
                        {
                            echo("<th>FIRST NUMBER IN THE RANGE</th>\n");
                            echo("<th>LAST NUMBER IN THE RANGE</th>\n");
                            echo("<th>HIGHEST NUMBER OF ITERATIONS</th>\n");
                        }
                    ?>
                </tr>
                <tr>
                    <td>
                        <?php
                            if ($done)
                            {
                                echo($first);
                            }
                        ?>
                    </td>
                    <td>
                        <?php
                            if ($done)
                            {
                                echo($last);
                            }
                        ?>
                    </td>
                    <td>
                        <?php
                            if ($done)
                            {
                                echo($counter);
                            }
                        ?>
                    </td>
                </tr>
            </table>
    </body>
</html>