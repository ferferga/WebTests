<?php
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
        <title>Amazing Web Services</title>
        <style>
            * {
                font-family: 'Roboto', sans-serif;
            }
            #image {
                border-radius: 100%;
                min-height: 150px;
                min-width: 150px;
                width: 150px;
                height: 150px;
                background-position: center;
                background-size: cover;
                background-repeat: no-repeat;
            }
            #header {
                display: flex;
                align-content: center;
                align-items: center;
            }
            #namemsg {
                padding: 50px;
            }
            #footer {
                display: flex;
                align-content: center;
                align-items: center;
            }
        </style>
        <?php
            if(isset($_COOKIE['contador']))
            {
                setcookie('contador', $_COOKIE['contador'] + 1, time() + 365 * 24 * 60 * 60);
                $visitors = 'You visited this page '.$_COOKIE['contador'].' times.';
            }  
        ?>
    </head>
    <body>
        <b>Amazing Web Services - Data Verification</b></br></br>
        <div id="header">
        <?php
            if(isset($_SESSION['image']))
            {
                echo("<div id=\"image\" style=\"background-image:url('".$_SESSION['image']."')\"></div>");
            }
            if(isset($_COOKIE['username']))
            {
                echo("<div id='namemsg'>Hello <b>".$_COOKIE['username']."</b>!</br>Glad to see that you are interested in our services!</div>");
            }
        ?>
        </div>
        <?php
            
            if(isset($_SESSION['province']))
            {
                echo("</br></br>");
                echo("Please, confirm us some information before you proceed: <i>Is <b>".$_SESSION['province']."</b> your province of residence?</i>");
                echo("</br></br>If it's correct, continue. Otherwise, go back and modify your information.");
            }
            echo("</br>");
            echo("<i>".$visitors."</i>");
        ?>
        </br></br>
        <div id="footer">
            <form action="cookies_page1.php">
                <input type="submit" value="<< Go back to registration form" name="submit">
            </form>
            <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
            <form action="cookies_page2.php">
                <input type="submit" value="< Go back" name="submit">
            </form>
            <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
            <form action="">
                <input type="submit" value="Continue to nowhere! >" name="submit">
            </form>
        </div>
    </body>
</html>