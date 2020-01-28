<?php
    session_start();
    $_SESSION['runtimemode'] = 1;
    $db_connection = new mysqli("localhost", "root", "", "multimedia_library");
    if (isset($_GET['item_id']) && isset($_GET['is_person']) && ($_GET['is_person'] >= 0 && $_GET['is_person'] <= 1))
    {
        $_SESSION['childId'] = $_GET['item_id'];
        if ($_GET['is_person'] == 1)
        {
            $person = True;
        }
        else
        {
            $person = False;
        }
        $item_id = $db_connection->real_escape_string($_GET['item_id']);
    }
    else
    {
        $incorrect_data = True;
    }
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
        if (!isset($incorrect_data))
        {
            if(!$person)
            {
                $query = "SELECT media_type_id, title, description, release_date, added_date, poster, aired_on, recorded_on, parent_id, added_by 
                FROM media WHERE _id = {$item_id}";
            }
            else
            {
                $query = "SELECT person_name, biography, poster, added_date, added_by FROM people WHERE _id = {$item_id}";            
            }
            $results = $db_connection->query($query);
            if ($results)
            {
                if ($results->num_rows > 0)
                {
                    while($row=$results->fetch_assoc())
                    {
                        if ($row['poster'] != null)
                        {
                            $cardpic = "data:image/*;base64,".base64_encode($row['poster']);
                            $picstyle = "";
                            $havepic = true;
                        }
                        else
                        {
                            if(!$person)
                            {
                                $cardpic = "src/broken_image.svg";
                                $picstyle = "filter:invert(100%);";
                            }
                            else
                            {
                                $cardpic = "src/unknown_profile.svg";
                                $picstyle = "filter:invert(100%);";
                            }
                        }
                        if (!$person)
                        {
                            $title = $row['title'];
                            $description = $row['description'];
                            $aired_on = $row['aired_on'];
                            $recorded_on = $row['recorded_on'];
                            $media_type_id = $row['media_type_id'];
                            $_SESSION['childMediaType'] = $media_type_id;
                            $release_date = $row['release_date'];
                            if ($row['parent_id'] != null)
                            {
                                $parent_id = $row['parent_id'];
                                $parent_results = $db_connection->query("SELECT title FROM media WHERE _id = {$parent_id}");
                                if ($parent_results->num_rows > 0)
                                {
                                    while($row1=$parent_results->fetch_assoc())
                                    {
                                        $parent_name = $row1['title'];
                                    }
                                }
                            }
                        }
                        else
                        {
                            $title = $row['person_name'];
                            $description = $row['biography'];
                        }
                    }
                }
                else
                {
                    $incorrect_data = True;
                }
            }
            else
            {
                $query_error = True;
            }
        }
        if (isset($_POST['delete_item']))
        {
            if ($person)
            {
                $query = "DELETE FROM people WHERE _id = {$item_id}";
            }
            else
            {
                $query = "DELETE FROM media WHERE _id = {$item_id}";
            }
            $result = $db_connection->query($query);
            if($result)
            {
                session_destroy();
                header('Location: index.php');
                die();
            }
            else
            {
                $query_error = True;
            }
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
                if (isset($_POST['release_date']))
                {
                    $release_date = $db_connection->real_escape_string($_POST['release_date']);
                }
                else
                {
                    $release_date = null;
                }
                if (isset($_POST['recorded_on']))
                {
                    $recorded_on = $db_connection->real_escape_string($_POST['recorded_on']);
                }
                else
                {
                    $recorded_on = null;
                }
                $title = $db_connection->real_escape_string($_POST['title']);
                if (isset($_POST['aired_on']))
                {
                    $aired_on = $db_connection->real_escape_string($_POST['aired_on']);
                }
                else
                {
                    $aired_on = null;
                }               
                $description = $db_connection->real_escape_string($_POST['description']);
                if($person)
                {
                    if($havepic && $data == null)
                    {
                        $query = "UPDATE people SET person_name = '{$title}', biography = '{$description}'
                        WHERE _id = {$item_id}";
                    }
                    else
                    {
                        $query = "UPDATE people SET person_name = '{$title}', biography = '{$description}', poster = '{$data}'
                        WHERE _id = {$item_id}";
                    }                    
                }
                else
                {
                    if ($havepic && $data == null)
                    {
                        $query = "UPDATE media SET release_date = '{$release_date}', description = '{$description}', title = '{$title}',
                        aired_on = '{$aired_on}', recorded_on = '{$recorded_on}' WHERE _id = {$item_id}";
                    }
                    else
                    {
                        $query = "UPDATE media SET release_date = '{$release_date}', description = '{$description}', title = '{$title}',
                        poster = '{$data}', aired_on = '{$aired_on}', recorded_on = '{$recorded_on}' WHERE _id = {$item_id}";
                    }                    
                }  
                $result = $db_connection->query($query);
                if($result)
                {
                    session_destroy();
                    if($person)
                    {
                        header("Location: itemdetails.php?item_id=".$item_id."&is_person=1");
                    }
                    else
                    {
                        header("Location: itemdetails.php?item_id=".$item_id."&is_person=0");
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
            <div class='header-sidebar'>
            <?php
                if(!isset($loggedIn))
                {
                    echo("<form action='login.php' method='POST'>");
                    echo("<input id='colorful-button' type='submit' name='log_in' value='Log In'/>");
                    echo("</form>");
                }
                else
                {
                    if (!isset($incorrect_data))
                    {
                        echo("<form action='' method='POST'>");
                        echo("<input id='colorful-button' style='background-color:red' type='submit' name='delete_item' value='Delete item'/>");
                        echo("</form>");
                        if(!$person)
                        {
                            if ($media_type_id == 2)
                            {
                                echo("<form action='parent_management.php' method='POST'>");
                                echo("<input id='colorful-button' type='submit'  value='Manage parent TV Show'/>");
                                echo("</form>");
                            }
                            elseif ($media_type_id == 3)
                            {
                                echo("<form action='parent_management.php' method='POST'>");
                                echo("<input id='colorful-button' type='submit'  value='Manage parent TV Season'/>");
                                echo("</form>");
                            }
                            elseif ($media_type_id == 5)
                            {
                                echo("<form action='parent_management.php' method='POST'>");
                                echo("<input id='colorful-button' type='submit'  value='Manage parent album'/>");
                                echo("</form>");
                            }
                            echo("<form action='people_management.php' method='POST'>");
                            echo("<input id='colorful-button' type='submit' value='Manage people & cast'/>");
                            echo("</form>");
                        }                        
                    }
                    echo("<a href='controlpanel.php'>");
                    echo("<div id='profile-pic' style='background-image:url(".$profilepic.");background-position:center center; 
                        background-repeat:no-repeat;background-size:cover;border-radius:100%;width:45px;height:45px;".$ppicstyling."'>");
                    echo("</div>");
                    echo("</a>");
                }
            ?>
            </div>               
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
                elseif(isset($incorrect_data))
                {
                    echo("<h4 style='color:red'>Invalid data has been passed</h4>");
                }
                else
                {
                    echo("<h1>Editing <i>".$title."</i></h1>");
                }
            ?>
        </div>
        <div class="centered-content-horizontal">
                <?php
                if(isset($loggedIn))
                {
                    echo("<div class='card centered-content-vertical' style='padding:20px'>");
                    echo("<img id='poster' src='".$cardpic."' style='".$picstyle."'/>");
                    echo("</div>");
                }
                ?>
            <div class="centered-content-vertical form-content">
                <?php
                    if(isset($loggedIn))
                    {
                        echo("<form action='' method='POST' enctype='multipart/form-data' style='text-align:center'>");
                        echo("<label for='fileToUpload'>Poster picture (leave blank to keep the current one):</label>");
                        echo("<br><br>");
                        echo("<input type='file' accept='image/*' name='fileToUpload' style='color:white'>");
                        echo("<br><br>");
                        if($person)
                        {
                            echo("<label for='title'>Name: </label>");
                        }
                        else
                        {
                            echo("<label for='title'>Title: </label>");
                        }                            
                        echo("<input type='text' id='textbox' name='title' value='".$title."' required />");
                        echo("<br><br>");
                        if(!$person)
                        {
                            if($media_type_id == 1 || $media_type_id == 2 || $media_type_id == 3)
                            {
                                echo("<label for='aired_on'>Where it was aired?: </label>");
                                echo("<input type='text' id='textbox' name='aired_on' placeholder='HBO, CBS, Netflix, Hulu...' value='".$aired_on."'/>");
                                echo("<br><br>");
                            }
                        }
                        if (!$person)  
                        {
                            echo("<label for='release_date'>Release Date: </label>");
                            echo("<input type='date' id='textbox' name='release_date' value='".$release_date."'/>");
                            echo("<br><br>");
                            echo("<label for='recorded_on'>Recorded on: </label>");
                            echo("<input type='text' id='textbox' name='recorded_on' placeholder='Location, Studio...' value='".$recorded_on."' />");
                            echo("<br><br>");
                            echo("<label for='description'>Description: </label>");
                        }
                        else
                        {
                            echo("<label for='description'>Biography: </label>");
                        }                            
                        echo("<textarea id='textbox' name='description' style='height:50%;' required>".$description."</textarea>");                            
                        echo("<br><br>");
                        echo("<div>");
                        echo("<input type='submit' id='colorful-button' value='Save Changes' name='content_submit'/>");
                        echo("</div>");
                    }
                    echo("</form>")
                ?>
            </div>
        </div>
    </body>
</html>