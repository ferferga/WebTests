<?php
    set_time_limit(86400);
    if (isset($_POST["rows"]))
    {
        $row=$_POST["rows"];
        $count=$_POST["rows"];
        $spaces = '&nbsp;&nbsp;&nbsp;';
        $loopspaces = "";
        function str_replace_first($from, $to, $content)
        {
            $from = '/'.preg_quote($from, '/').'/';
            return preg_replace($from, $to, $content, 1);
        }
        for ($i=0; $i < $row; $i++) 
        {
            $loopspaces = ""; 
            for ($x=0; $x < $count ; $x++) 
            {
                $loopspaces = $loopspaces.$spaces;
            }
            if ($i==0) 
            {
                echo(str_replace_first("&nbsp;", "", $loopspaces));
                echo("*");
            }
            else
            {
                echo $loopspaces;
            }
            for ($j=0; $j < $i*3; $j++) 
            { 
                echo("*");
            }
            $count--;
            echo("<br>");
        };
    }
?>

<!DOCTYPE html>
    <head>

    </head>
    <body>
        <form action="asterisk_pyramid.php" method="POST">
            <label for="rows">How many rows do you want to generate?: </label>            
            <input type="float" name="rows"/>
            <label for="rows">
                <input type="submit"/>
            </label>
        </form>
    </body>
</html>