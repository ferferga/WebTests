<?php
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
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="src/styles.css" rel="stylesheet">
        <?php
            if (isset($_GET['type_movies']))
            {
                echo("<title>Movies</title>");
            }
            elseif (isset($_GET['type_shows']))
            {
                echo("<title>TV Shows</title>");
            }
            elseif (isset($_GET['type_music']))
            {
                echo("<title>Music Albums</title>");
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
        <div>
            <div class="search_bar">
                <form action="" method="POST" class="centered-content-horizontal">
                    <?php
                        if (isset($_POST['search-submit']))
                        {
                            echo("<input id='search-textbox' type='text' 
                                name='search-text' value='".$_POST['search-text']."'>");
                        }
                        else
                        {
                            echo("<input id='search-textbox' type='text' 
                                name='search-text' placeholder='Search at a glance your favorite music, movies, TV Shows...'>");
                        }
                    ?>
                    <button type="submit" style="border: none;background-color: #202224;" name="search-submit">
                        <img width="25px" height="25px" src="src/magnifyer.svg" style="filter: invert(100%);"/>
                    </button>
                </form>
            </div>
        </div>
        <?php
            if (isset($_GET['type_movies']))
            {
                echo("<h3>All Movies</h3>");
            }
            elseif (isset($_GET['type_shows']))
            {
                echo("<h3>All TV Shows</h3>");
            }
            elseif (isset($_GET['type_music']))
            {
                echo("<h3>All Music</h3>");
            }
        ?>
        <div class="carousel">
            <?php
                if (isset($_POST['search-submit']))
                {
                    $search = $db_connection->real_escape_string($_POST['search-text']);
                    if (isset($_GET['type_movies']))
                    {
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE media_type_id = 0 AND (title LIKE '%{$search}%' 
                        OR description LIKE '%{$search}%') ORDER BY title";
                    }
                    elseif (isset($_GET['type_shows']))
                    {
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE media_type_id = 1
                            media_type_id = 3) AND (title LIKE '%{$search}%' OR description LIKE '%{$search}%') ORDER BY title";
                    }
                    elseif (isset($_GET['type_music']))
                    {
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE media_type_id = 4
                            AND (title LIKE '%{$search}%' OR description LIKE '%{$search}%') ORDER BY title";
                    }
                }
                else
                {
                    if (isset($_GET['type_movies']))
                    {
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE media_type_id = 0 ORDER BY title";
                    }
                    elseif (isset($_GET['type_shows']))
                    {
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE media_type_id = 1 ORDER BY title";
                    }
                    elseif (isset($_GET['type_music']))
                    {
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE media_type_id = 4 ORDER BY title";
                    }
                    
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
                        echo("<a href='itemdetails.php?item_id=".strval($row['_id'])."&is_person=0'>");
                        echo("<img id='poster' src='".$cardpic."' style='".$picstyle."'/>");
                        echo("</a>");
                        echo("<div>");
                        echo("<b>".$row['title']."</b>");
                        echo("<br>");
                        echo("<i>".date('Y', strtotime($row['release_date']))."</i>");
                        echo("<br>");
                        if($row['media_type_id'] == 1)
                        {
                            echo("<i class='media-type-footer'>TV Show</i>");
                        }
                        elseif($row['media_type_id'] == 2)
                        {
                            echo("<i class='media-type-footer'>TV Season</i>");
                        }
                        elseif($row['media_type_id'] == 3)
                        {
                            echo("<i class='media-type-footer'>TV Episode</i>");
                        }
                        elseif($row['media_type_id'] == 4)
                        {
                            echo("<i class='media-type-footer'>Album</i>");
                        }
                        elseif($row['media_type_id'] == 5)
                        {
                            echo("<i class='media-type-footer'>Song</i>");
                        }
                        echo("</div>");
                        echo("</div>");
                    }
                }
                else
                {
                    if (isset($_GET['type_movies']))
                    {
                        echo("<a id='informativeText'>There aren't any movies to display</a>");
                    }
                    elseif (isset($_GET['type_shows']))
                    {
                        echo("<a id='informativeText'>There aren't any TV Shows to display</a>");
                    }
                    elseif (isset($_GET['type_music']))
                    {
                        echo("<a id='informativeText'>There aren't any music album or songs to display</a>");
                    }
                }
            ?>
        </div>
    </body>
</html>