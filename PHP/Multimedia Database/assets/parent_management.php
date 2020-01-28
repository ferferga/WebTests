<?php
    session_start();
    $db_connection = new mysqli("localhost", "root", "", "multimedia_library");
    if (isset($_COOKIE['ferferga_media_db_ticket']))
    {
        $resultado = $db_connection->query("SELECT profile_pic, ticket_id FROM user_info_tickets 
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
                $loggedIn = True;
            }
        }
        else
        {
            setcookie("ferferga_media_db_ticket", "", time() - 3600);
        }
        if (isset($_GET['parent_id']))
        {
            $parent_id = $db_connection->real_escape_string($_GET['parent_id']);
            $child_id = $db_connection->real_escape_string($_SESSION['childId']);
            $db_connection->query("UPDATE media SET parent_id = {$parent_id} WHERE _id = {$child_id};");
            if ($_SESSION['runtimemode'] == 0)
            {
                header('Location: people_management.php');
                die();
            }
            else 
            {
                header("Location: edititem.php?item_id=".$_SESSION['childId']."&is_person=0");
                die();
            }            
        }
    }    
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="src/styles.css" rel="stylesheet">
        <title>Manage parent item</title>
        <style>
        </style>
        <?php            
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
        <div>
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
                    echo("</div>");
                    echo("<div class='search_bar'>
                            <form action='' method='POST' class='centered-content'>
                                <input id='search-textbox' type='text' height='50px' name='search-text' placeholder='Search...'>
                                <button type='submit' style='border: none;background-color: #202224;' name='search-submit'>
                                    <img width='25px' height='25px' src='src/magnifyer.svg' style='filter: invert(100%);'/>
                                </button>
                            </form>
                        </div>");
                    if(isset($_SESSION['childMediaType']))
                    {
                        $type_id_to_match = $_SESSION['childMediaType']-1;
                        if ($_SESSION['childMediaType'] == 3)
                        {
                            echo("<i>As you're editing a <b>TV Show Episode</b>, only TV Seasons are shown. 
                                Choose the Season where this TV Episode belongs to</i>");
                        }
                        elseif ($_SESSION['childMediaType'] == 2)
                        {
                            echo("<i>As you're editing a <b>TV Show Season</b>, only TV Shows are shown. 
                                Choose the TV Show where the season belongs to</i>");
                        }
                        elseif ($_SESSION['childMediaType'] == 5)
                        {
                            echo("<i>As you're editing a <b>Song</b>, only music albums are shown. 
                                Choose the music album where this song belongs to</i>");
                        }
                        echo("<br><i>If what you're looking for doesn't appear here, go back and add it first and edit this content later.</i>");
                        if ($_SESSION['childMediaType'] == 3)
                        {
                            echo("<h4>Available Seasons: </h4>");
                        }
                        elseif ($_SESSION['childMediaType'] == 2)
                        {
                            echo("<h4>Available TV Shows: </h4>");
                        }
                        elseif ($_SESSION['childMediaType'] == 5)
                        {
                            echo("<h4>Available Music Albums: </h4>");
                        }
                        echo("<div class='carousel'>");
                        if (!isset($_POST['search-submit']))
                        {
                            $query = "SELECT _id, title, poster, release_date FROM media WHERE media_type_id = {$type_id_to_match} ORDER BY title";
                        }
                        else
                        {
                            $search = $db_connection->real_escape_string($_POST['search-text']);
                            $query = "SELECT _id, title, poster, release_date FROM media WHERE media_type_id = {$type_id_to_match} AND 
                            (description LIKE '%{$search}%' OR title LIKE '%{$search}%') ORDER BY title";
                        }
                        $results = $db_connection->query($query);                 
                        if ($results->num_rows > 0)
                        {
                            while($row=$results->fetch_assoc())
                            {
                                if ($row['poster'] != null)
                                {
                                    $cardpic = "data:image/*;base64,".base64_encode($row['poster']);
                                    $picstyle = "";
                                }
                                else
                                {
                                    $cardpic = "src/broken_image.svg";
                                    $picstyle = "filter:invert(100%);width:500px";
                                }
                                echo("<div class='card centered-content-vertical'>");                            
                                echo("<a href='parent_management.php?parent_id=".strval($row['_id'])."'>");
                                echo("<img id='poster' src='".$cardpic."' style='".$picstyle."'/>");
                                echo("</a>");
                                echo("<div>");
                                echo("<b>".$row['title']."</b>");
                                echo("<br>");
                                echo("<i>".date('Y', strtotime($row['release_date']))."</i>");
                                echo("</div>");
                                echo("</div>");
                            }
                        }
                        else
                        {
                            echo("<h4 style='color:red'>There are no results to show</h4>");
                        }
                    }
                }
            ?>
        </div>
    </body>
</html>