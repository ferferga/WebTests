<?php
    session_start();
    $db_connection = new mysqli("localhost", "root", "", "multimedia_library");
    if (isset($_COOKIE['ferferga_media_db_ticket']))
    {
        $resultado = $db_connection->query("SELECT profile_pic, ticket_id, _id FROM user_info_tickets 
            WHERE ticket_id = ".$_COOKIE['ferferga_media_db_ticket']." LIMIT 1");
        setcookie('ferferga_media_db_ticket', $_COOKIE['ferferga_media_db_ticket'], time() + 86400);
        if ($resultado->num_rows > 0)
        {
            while($row=mysqli_fetch_row($resultado))
            {
                if ($row[0] != null)
                {
                    $profilepic = "data:image/*;base64,".base64_encode($row[0]);
                    $ppicstyling = "";
                }
                else
                {
                    $profilepic = "src/unknown_profile.svg";
                    $ppicstyling = "filter:invert(100%);";
                }
                $user_id = $row[2];
                $loggedIn = True;
            }
        }
        else
        {
            setcookie("ferferga_media_db_ticket", "", time() - 3600);
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="src/styles.css" rel="stylesheet">
        <style>
            .form-content > form > input[type='text'] {
                width: 115%;
            }
        </style>
        <?php
            // This function will help us later for turning empty strings into NULL values.
            function drop_empty($var)
            {
              return ($var === '') ? NULL : $var;
            }
            if(isset($_GET['add_movie']))
            {
                echo("<title>Adding a movie</title>");
                $movie = true;
                unset($_GET);
                $_SESSION['media_group'] = 0;
            }
            elseif(isset($_GET['add_tvshow']))
            {
                echo("<title>Adding a TV Show</title>");
                $tv_show = true;
                unset($_GET);
                $_SESSION['media_group'] = 1;
            }
            elseif(isset($_GET['add_music']))
            {
                echo("<title>Adding music</title>");
                $music = true;
                unset($_GET);
                $_SESSION['media_group'] = 2;
            }
            if(isset($_POST['content_submit']))
            {
                // We convert empty strings to null values
                $_POST = array_map('drop_empty', $_POST);
                $info = getimagesize($_FILES['fileToUpload']['tmp_name']);
                if($info)
                {
                    $data = $db_connection->real_escape_string(file_get_contents($_FILES['fileToUpload']['tmp_name']));
                }
                else
                {
                    $data = null;
                }
                if (!isset($_POST['media_type']))
                {
                    $media_type = 0;
                }
                else
                {
                    $media_type = $db_connection->real_escape_string($_POST['media_type']);
                }
                $added_date = date('Y-m-d', time());
                $release_date = $db_connection->real_escape_string($_POST['release_date']);
                $recorded_on = $db_connection->real_escape_string($_POST['recorded_on']);
                $title = $db_connection->real_escape_string($_POST['title']);
                $aired_on = $db_connection->real_escape_string($_POST['aired_on']);                
                $description = $db_connection->real_escape_string($_POST['description']);
                $query = "INSERT INTO media VALUES(NULL,'{$media_type}', '{$release_date}', '{$added_date}',
                    '{$description}', '{$title}', '{$data}', '{$aired_on}', '{$recorded_on}', NULL, {$user_id})";
                $result = $db_connection->query($query);
                if($result)
                {
                    $_SESSION['childId'] = $db_connection->insert_id;
                    $_SESSION['childMediaType'] = $media_type;
                    $_SESSION['runtimemode'] = 0;
                    if($media_type == 0 || $media_type == 4 || $media_type == 1)
                    {                        
                        header('Location: people_management.php');
                    }
                    else
                    {
                        header('Location: parent_management.php');
                    }
                    die();                  
                }
                else
                {
                    $query_error = true;
                }
            }        
        ?>
    </head>
    <body>
        <div class="header">
            <form action='index.php'>
                <button style="border:none;background-color:#202224;color:white;display:flex">
                    <h1 id="header">Fernando's Multimedia DB</h1>
                </button>
            </form>
                <?php
                    if(!isset($loggedIn))
                    {
                        echo("<form action='login.php' method='POST'>");
                        echo("<input id='colorful-button' type='submit' name='log_in' value='Log In'/>");
                    }
                    else
                    {
                        echo("<a href='controlpanel.php'>");
                        echo("<div id='profile-pic' style='background-image:url(".$profilepic.");background-position:center center; 
                            background-repeat:no-repeat;background-size:cover;border-radius:100%;width:45px;height:45px;".$ppicstyling."'>");
                        echo("</div>");
                        echo("</a>");
                    }
                ?>                
            </form>
        </div>
        <div class="centered-content-vertical">
            <?php
                if(!isset($loggedIn))
                {
                    echo("<h4 style='color:red'>You can't perform this action if you're not logged in</h4>");
                }
                elseif(isset($query_error))
                {
                    echo("<h4 style='color:red'>There was an error in the query</h4>");
                }
                else
                {
                    if(isset($movie))
                    {
                        echo("<h1>Adding a movie: </h1>");
                    }
                    elseif(isset($tv_show))
                    {
                        echo("<h1>Adding a TV Show, TV Show season or TV Show episode: </h1>");
                    }
                    elseif(isset($music))
                    {
                        echo("<h1>Adding a album or song: </h1>");
                    }
                }
            ?>
        </div>
        <div class="centered-content-horizontal">
            <div style='justify-content: flex-start;'>
                <?php
                if(isset($loggedIn))
                {
                    echo("<div style='background-image:url(src/add_photo.svg);background-position:center center; 
                        background-repeat:no-repeat;background-size:cover;border-radius:100%;
                        width:200px;height:200px;filter:invert(100%)'>");
                }
                ?>                
                </div>
            </div>
            <div class="centered-content-vertical form-content">
                <?php
                    if(isset($loggedIn))
                    {
                        echo("<form action='' method='POST' enctype='multipart/form-data' style='text-align:center'>");
                        echo("<label for='fileToUpload'>Poster picture:</label>");
                        echo("<br><br>");
                        echo("<input type='file' accept='image/*' name='fileToUpload' style='color:white'>");
                        echo("<br><br>");
                        echo("<label for='title'>Title: </label>");
                        echo("<input type='text' id='textbox' name='title' required />");
                        echo("<br><br>");
                        if(isset($tv_show))
                        {
                            echo("<label for='media_type'>What are you adding?: </label>");
                            echo("
                                <select name='media_type' required>
                                   <option value='1'>TV Show</option> 
                                   <option value='2'>Season</option> 
                                   <option value='3'>Episode</option>
                                </select>");
                            echo("<br><br>");
                            echo("<label for='aired_on'>Where it was aired?: </label>");
                            echo("<input type='text' id='textbox' name='aired_on' placeholder='HBO, CBS, Netflix, Hulu...'/>");
                            echo("<br><br>");
                        }
                        elseif(isset($music))
                        {
                            echo("<label for='media_type'>What are you adding?: </label>");
                            echo("
                                <select name='media_type' required>
                                   <option value='5'>Song</option> 
                                   <option value='4'>Album</option>
                                </select>");
                            echo("<br><br>");
                        }
                        echo("<label for='release_date'>Release Date: </label>");
                        echo("<input type='date' id='textbox' name='release_date' />");
                        echo("<br><br>");
                        echo("<label for='recorded_on'>Recorded on: </label>");
                        echo("<input type='text' id='textbox' name='recorded_on' placeholder='Location, Studio...' />");
                        echo("<br><br>");
                        echo("<label for='description'>Description: </label>");
                        echo("<textarea id='textbox' name='description' style='height:50%;' required></textarea>");                            
                        echo("<br><br>");
                        echo("<div>");
                        echo("<input type='submit' id='colorful-button' value='Submit' name='content_submit'/>");
                        echo("</div>");
                    }
                    echo("</form>");
                ?>
            </div>
        </div>
    </body>
</html>