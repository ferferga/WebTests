<?php
    set_time_limit(86400);
    function remove_accents( $string ) {
        $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
        $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
        $returnable = str_replace($search, $replace, $string);
        return $returnable;
    }
    if (isset($_POST["sentence"]))
    {
        $chars = range('a', 'z');
        $input = $_POST["sentence"];
        $split_input = str_replace(" ", "", $input);
        $split_input = str_replace(".", "", $split_input);
        $split_input = str_replace(",", "", $split_input);
        $split_input = remove_accents($split_input);
        $split_input = strtolower($split_input);
        $split_input = str_split($split_input);
        $split_input = array_unique($split_input);
        $result = array_intersect($split_input, $chars);
        if (count($result) == count($chars))
        {
            $output = "The phrase <b>is</b> a Pangram!</br></br>";
        }
        else
        {
            $output = "The phrase <b>is not</b> a Pangram!</br></br>";
        }
        $output = $output."Here is the checked sentence: ".$input."</br></br>";
        echo $output;
    }
?>

<!DOCTYPE html>
    <head>

    </head>
    <body>
        <form action="Pangram.php" method="POST">
            <b>This is a pangram checker app. A pangram is a phrase that contains all the lleters of the dictionary.</b></br>
            <label for="phrase">Please, input the phrase that we are going to check: </label>            
            <input type="text" name="sentence"/>
            <label for="phrase">
                <input type="submit"/>
            </label>
        </form>
    </body>
</html>