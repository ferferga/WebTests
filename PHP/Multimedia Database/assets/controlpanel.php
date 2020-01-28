<?php
    $db_connection = new mysqli("localhost", "root", "", "multimedia_library");
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="src/styles.css" rel="stylesheet">
        <title>FMD's Control Panel</title>
        <?php
            if (isset($_POST['delete-account']))
            {
                $resultado = $db_connection->query("DELETE FROM users WHERE _id IN (SELECT user_id FROM user_tickets
                    WHERE ticket_id = ".$_COOKIE['ferferga_media_db_ticket'].");");
                    setcookie("ferferga_media_db_ticket", "", time() - 3600);
                    header('Location: login.php');
                    die();
            }
            if (isset($_POST['log-out_button']))
            {
                $resultado = $db_connection->query("SELECT user_id FROM user_tickets
                    WHERE ticket_id = ".$_COOKIE['ferferga_media_db_ticket']." LIMIT 1");
                if ($resultado->num_rows > 0)
                {
                    while($row=mysqli_fetch_row($resultado))
                    {
                        $user_id = $row[0];
                    }
                }
                $db_connection->query("DELETE FROM user_tickets WHERE user_id = ".$user_id);
                setcookie("ferferga_media_db_ticket", "", time() - 3600);
                header('Location: index.php');
                die();
            }
            else
            {
                if (isset($_POST['submit_profile']) || isset($_POST["submit_changes"]))
                {
                    if (isset($_POST['submit_profile']))
                    {
                        $info = getimagesize($_FILES['fileToUpload']['tmp_name']);
                        if ($info)
                        {
                            $data = $db_connection->real_escape_string(file_get_contents($_FILES['fileToUpload']['tmp_name']));
                            $db_connection->query("UPDATE user_info_tickets SET profile_pic = '{$data}' WHERE 
                                ticket_id = ".$_COOKIE['ferferga_media_db_ticket']);
                            unset($_POST['submit_profile']);
                        }
                        else
                        {
                            $profile_pic_invalid = True;
                        }
                    }
                    else
                    {
                        $resultado = $db_connection->query("SELECT password FROM user_info_tickets
                            WHERE ticket_id = ".$_COOKIE['ferferga_media_db_ticket']." LIMIT 1");
                        if ($resultado->num_rows > 0)
                        {
                            while($row=mysqli_fetch_row($resultado))
                            {
                                $stored_password = $row[0];
                            }
                        }
                        if($stored_password != $_POST['confirm_password'])
                        {
                            $invalid_password = True;
                        }
                        else
                        {
                            $resultado = $db_connection->query("SELECT _id FROM user_info_tickets
                                WHERE ticket_id = ".$_COOKIE['ferferga_media_db_ticket']." LIMIT 1");
                            if ($resultado->num_rows > 0)
                            {
                                while($row=mysqli_fetch_row($resultado))
                                {
                                    $user_id = $row[0];
                                }
                            }
                            if (isset($_POST['new_name']) && !empty($_POST['new_name']))
                            {
                                $query = "UPDATE users SET name = ? WHERE _id = ?;";
                                $results = $db_connection->prepare($query);
                                $results->bind_param('si', $_POST['new_name'], $user_id);
                                $results->execute();
                                $results->close();
                            }
                            if (isset($_POST['new_username']) && !empty($_POST['new_username']))
                            {
                                $query = "UPDATE users SET username = ? WHERE _id = ?;";
                                $results = $db_connection->prepare($query);
                                $results->bind_param('si', $_POST['new_username'], $user_id);
                                $results->execute();
                                $results->close();
                            }
                            if (isset($_POST['new_email']) && !empty($_POST['new_email']))
                            {
                                $query = "UPDATE users SET email = ? WHERE _id = ?;";
                                $results = $db_connection->prepare($query);
                                $results->bind_param('si', $_POST['new_email'], $user_id);
                                $results->execute();
                                $results->close();
                            }
                            if (isset($_POST['new_password']) && !empty($_POST['new_password']))
                            {
                                $query = "UPDATE users SET password = ? WHERE _id = ?;";
                                $results = $db_connection->prepare($query);
                                $results->bind_param('si', $_POST['new_password'], $user_id);
                                $results->execute();
                                $results->close();
                            }
                        }
                    }
                }              
                $resultado = $db_connection->query("SELECT profile_pic, email, username, name FROM user_info_tickets
                    WHERE ticket_id = ".$_COOKIE['ferferga_media_db_ticket']." LIMIT 1");
                if ($resultado->num_rows > 0)
                {
                    while($row=mysqli_fetch_row($resultado))
                    {
                        if ($row[0] != null)
                        {
                            $profilepic = "data:image/jpeg;base64,".base64_encode($row[0]);
                            $ppicstyling = "";
                        }
                        else
                        {
                            $profilepic = "src/unknown_profile.svg";
                            $ppicstyling = "filter:invert(100%);";
                        }
                        $email = $row[1];
                        $username = $row[2];
                        $name = $row[3];                   
                    }
                }
                else
                {
                    $display = False;
                }
            }                                    
        ?>
    </head>
    <body>
    </body>
        <div class="header">
            <form action='index.php'>
                <button style="border:none;background-color:#202224;color:white">
                    <h1 id="header">Fernando's Multimedia DB</h1>
                </button>
            </form>
            <div class="header-sidebar">
                <form action='' method='POST'>
                    <input type="submit" value="Delete my account" id="colorful-button" style='background-color:red' name='delete-account'/>
                </form>
                <form action='' method='POST'>
                    <input type="submit" value="Close all sessions" id="colorful-button" name="log-out_button" />
                </form>
            </div>
        </div>
        <div class="centered-content-horizontal">
            <div>
                <h3>Your current profile pic:</h3>
                <?php
                    if (isset($profile_pic_invalid))
                    {
                        echo("<h4 style='color:red'>Invalid profile photo submitted</h4>");
                    }
                    if (!isset($display))
                    {
                        echo("<div id='profile-pic' style='background-image:url(".$profilepic.");background-position:center center; 
                            background-repeat:no-repeat;background-size:cover;border-radius:100%;
                            width:200px;height:200px;".$ppicstyling."'>");
                        echo("</div>");
                    }                             
                ?>
                <br>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="file" accept="image/*" name="fileToUpload" style="color:white">
                    <br><br>
                    <input type="submit" id="colorful-button" value="Submit" name="submit_profile"/>
                </form>
                <div style="padding: 1%">
                    <?php
                        if (!isset($display))
                        {
                            echo("<h5>Your current name: ".$name."</h5>");
                            echo("<h5>Your current username: ".$username."</h5>");
                            echo("<h5>Your current username: ".$email."</h5>");
                        }
                    ?>
                </div>
            </div>
            <div style="padding: 5%; width:50%">
                <form action="" method="POST" style="text-align:center">
                    <h3>Change your details</h3>
                    <?php
                        if (isset($invalid_password))
                        {
                            echo("<h4 style='color:red'>Your current password doesn't match</h4>");
                        }
                    ?>
                    <input type="text" id="textbox" placeholder="Your new name here..." name="new_name" style="width:90%"/>
                    <br>
                    <input type="text" id="textbox" placeholder="Your new username here..." name="new_username" style="width:90%"/>
                    <br>
                    <input type="text" id="textbox" placeholder="Your new email here..." name="new_email" style="width:90%"/>
                    <br>
                    <input type="password" id="textbox" placeholder="Type your new password..." name="new_password" style="width:90%"/>
                    <br>
                    <input type="password" id="textbox" placeholder="Type your current password to confirm the changes" name="confirm_password" style="width:90%" required/>
                    <br><br>
                    <input type="submit" id="colorful-button" value="Confirm" style="display:inline-block" name="submit_changes"/>
                </form>
            </div>
                       
        </div>
    </body>
</html>