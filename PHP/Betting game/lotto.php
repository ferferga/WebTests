<?php
    set_time_limit(86400);
    for($i = 1; $i < 16; ++$i) 
    {
        $randomizer = rand(0, 100);
        $random = 0;
        if ($randomizer >= 0 && $randomizer < 50)
        {
            $random = "1";
        }
        else if ($randomizer >= 50 && $randomizer < 70)
        {
            $random = "X";
        }
        else if ($randomizer >=70 && $randomizer <=100)
        {
            $random = "2";
        }
        echo("<b>".$i."</b>: &nbsp;".$random."<br>");
    }
?>