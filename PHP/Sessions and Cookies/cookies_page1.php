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
            a {
                color: red;
            }
        </style>
        <?php
            if(isset($_COOKIE['contador']))
            {
                setcookie('contador', $_COOKIE['contador'] + 1, time() + 365 * 24 * 60 * 60);
                $visitors = 'Welcome back! You visited this page '.$_COOKIE['contador'].' times.';
            }
            else
            {
                setcookie('contador', 1, time() + 365 * 24 * 60 * 60); 
                $mensaje = "This is your first time here. Welcome to Amazing Web Services!";
            }            
            if (isset($_POST["username"]) && isset($_POST["colour"]) && isset($_POST["province"]))
            {
                $info = getimagesize($_FILES['fileToUpload']['tmp_name']);
                if (!is_array($info))
                {
                    $validData = FALSE;
                }
                else
                {           
                    $target_dir = "profiles/".$_POST["username"]."/";
                    if(!is_dir($target_dir))
                    {
                        mkdir($target_dir, 0777, true);
                    }                    
                    $target_file = $target_dir.basename($_FILES["fileToUpload"]["name"]);
                    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_dir."profile.".$imageFileType);       
                    setcookie("username", $_POST["username"], time() + 86400);
                    $_SESSION["colour"] = $_POST["colour"];
                    $_SESSION["province"] = $_POST["province"];
                    $_SESSION["image"] = $target_dir."profile.".$imageFileType;
                    $validData = TRUE;
                }
            }
            else
            {
                $validData = FALSE;
            }            
        ?>
    </head>
    <body>
        <b>Amazing Web Services - Registration</b></br></br>
        <?php
            echo("<i>".$visitors."</i>");
        ?>
        </br></br>
        Thank you for your interest in signing up in Amazing Web Services. The sign up process consists in three guided steps. At each of them the details you provide to us 
        will be validated and confirmed to ensure that they are right.
        </br>
        First, tell us the main stuff about yourself. Press "Send" once you're done
        </br></br>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="username">Choose your username: </label>
            <input type="text" name="username" id="username">
            </br></br>
            <label for="fileToUpload">Choose your profile picture: </label>
            <input type="file" accept="image/*" name="fileToUpload" id="fileToUpload">
            </br></br>
            <label for="colour">What's your favourite colour?: </label>
            <input type="text" name="colour" id="colour">
            </br></br>
            <label for="province">What's your province of residence?: </label>
            <input type="text" name="province" id="province">
            </br></br></br>
            <input type="submit" value="Send" name="submit">
            </br></br></br>            
        </form>
        <?php
            if (isset($_POST["submit"]))
            {
                if ($validData)
                {
                    echo("The file attached was a valid image. You can proceed to the next page</br></br>");
                    echo("<form action='cookies_page2.php'>");
                    echo("<input type='submit' value='Continue >' name='submit'>");
                }
                else
                {
                    echo("<a>Invalid information, you can't proceed to the next page. Make sure that the chosen file is an image and that all the fields are filled up properly</a>");
                }
            }            
        ?>
    </body>
</html>