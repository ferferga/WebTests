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
    if ((isset($_GET['item_id']) && isset($_GET['is_person'])) && ($_GET['is_person'] >= 0 && $_GET['is_person'] <= 1))
    {
        $item_id = $db_connection->real_escape_string($_GET['item_id']);
        if ($_GET['is_person'] == 0)
        {            
            $query = "SELECT media_type_id, title, description, release_date, added_date, poster, aired_on, recorded_on, parent_id, added_by 
                FROM media WHERE _id = {$item_id}";
        }
        else
        {
            $query = "SELECT person_name, biography, poster, added_date, added_by FROM people WHERE _id = {$item_id}";            
        }
        $results = $db_connection->query($query);
        if($results)
        {
            if ($results->num_rows > 0)
            {
                while($row=$results->fetch_assoc())
                {
                    $added_date = $row['added_date'];
                    if ($row['added_by'] != null)
                    {
                        $added_by_id = $row['added_by'];
                        $parent_results = $db_connection->query("SELECT username FROM users WHERE _id = {$added_by_id}");
                        if ($parent_results->num_rows > 0)
                        {
                            while($row1=$parent_results->fetch_assoc())
                            {
                                $added_by = $row1['username'];
                            }
                        }
                    }
                    if ($row['poster'] != null)
                    {
                        $cardpic = "data:image/*;base64,".base64_encode($row['poster']);
                        $picstyle = "";
                    }
                    else
                    {
                        if($_GET['is_person'] == 0)
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
                    if ($_GET['is_person'] == 0)
                    {
                        $title = $row['title'];
                        $description = $row['description'];
                        $aired_on = $row['aired_on'];
                        $recorded_on = $row['recorded_on'];
                        $media_type_id = $row['media_type_id'];
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
    else
    {
        $incorrect_data = True;
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="src/styles.css" rel="stylesheet">
        <?php
            if (!isset($incorrect_data))
            {
                echo("<title>".$title."</title>");
            }
            else
            {
                echo("<title>Invalid Data</title>");
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
                    }
                    else
                    {
                        if(!isset($incorrect_data))
                        {
                            if ($_GET['is_person'] == 0)
                            {
                                echo("<a href='edititem.php?item_id=".$item_id."&is_person=0'>");
                            }
                            else
                            {
                                echo("<a href='edititem.php?item_id=".$item_id."&is_person=1'>");
                            }                                
                            echo("<input id='colorful-button' type='submit' name='edit_item' value='Edit Item'/>");
                            echo("</a>");
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
        <div style='display:flex;padding:2%'>
            <?php
                if (isset($incorrect_data))
                {
                    echo("<h3 style='color:red'>The data hasn't been provided correctly</h3>");
                }
                elseif(isset($query_error))
                {
                    echo("<h3 style='color:red'>There was an error in the query</h3>");
                }
                else
                {
                    echo("<div class='card'>                         
                        <img id='poster' src='".$cardpic."' style='".$picstyle."'/>
                    </div>
                    <div style='margin-top:0;margin-left:1%;flex-grow:1;flex:1'>");
                    if (!isset($media_type_id))
                    {
                        echo("<i>Person</i>");
                    }
                    elseif($media_type_id == 0)
                    {
                        echo("<i>Movie</i>");
                    }
                    elseif($media_type_id == 1)
                    {
                        echo("<i>TV Show</i>");
                    }
                    elseif($media_type_id == 2)
                    {
                        if (isset($parent_name))
                        {
                            echo("<i>A TV Show season of ".$parent_name."</i>");
                        }
                        else
                        {
                            echo("<i>A TV Show season</i>");
                        }                        
                    }
                    elseif($media_type_id == 3)
                    {
                        if (isset($parent_name))
                        {
                            echo("<i>A TV Show episode of the season ".$parent_name."</i>");
                        }
                        else
                        {
                            echo("<i>A TV Show episode</i>");
                        }                        
                    }
                    elseif($media_type_id == 4)
                    {
                        echo("<i>Music Album</i>");
                    }
                    elseif($media_type_id == 5)
                    {
                        if (isset($parent_name))
                        {
                            echo("<i>A song of '".$parent_name."' music album</i>");
                        }
                        else
                        {
                            echo("<i>Song</i>");
                        }                        
                    }
                    echo("<h1 style='margin-top:0;margin-bottom:0'>".$title."</h1>");
                    if (isset($added_by))
                    {
                        echo("<h4>Added on ".$added_date." by <i>".$added_by."</i></h4>");
                    }
                    else
                    {
                        echo("<h4>Added on ".$added_date." by a <i>Deleted User</i></h4>");
                    }
                    if (isset($release_date) && ($release_date != null || !empty($release_date)))
                    {
                        echo("<h4>Released on: ".$release_date."</h4>");
                    }
                    if (isset($aired_on) && ($aired_on != null || !empty($aired_on)))
                    {
                        echo("<h4>Aired on: ".$aired_on."</h4>");
                    }
                    if (isset($recorded_on) && ($recorded_on != null || !empty($aired_on)))
                    {
                        echo("<h4>Recorded on: ".$recorded_on."</h4>");
                    }
                    echo($description);
                    echo("</div></div><br>");
                    if (isset($media_type_id))
                    {
                        if ($media_type_id == 1 || $media_type_id == 2 || $media_type_id == 4)
                        {
                            $query = "SELECT DISTINCT _id, title, poster, release_date FROM media WHERE parent_id = {$item_id}";
                            $child_results = $db_connection->query($query);
                            if ($child_results->num_rows > 0)
                            {
                                echo("<div class='carousel-header'>");
                                if($media_type_id == 1)
                                {
                                    echo("<h3>Seasons</h3>");
                                }
                                elseif($media_type_id == 2)
                                {
                                    echo("<h3>Episodes</h3>");
                                }
                                else
                                {
                                    echo("<h3>Songs</h3>");
                                }
                                echo("</div>");
                                echo("<div class='carousel'>");
                                while($row=$child_results->fetch_assoc())
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
                                    echo("</div>");
                                    echo("</div>");
                                }
                                echo("</div>");
                            }
                        }
                        $query = "SELECT DISTINCT person_id, person_name, poster, role_name FROM people_roles WHERE media_id = {$item_id}";
                        $results = $db_connection->query($query);
                        if ($results->num_rows > 0)
                        {
                            echo("<div class='carousel-header'>");
                            if($media_type_id == 4 || $media_type_id == 5)
                            {
                                echo("<h3>Artists & Team</h3>");
                            }
                            else
                            {
                                echo("<h3>Cast & Team</h3>");
                            }
                            echo("</div>");
                            echo("<div class='carousel'>");
                            while($row=$results->fetch_assoc())
                            {
                                if ($row['poster'] != null)
                                {
                                    $cardpic = "data:image/*;base64,".base64_encode($row['poster']);
                                    $picstyle = "";
                                }
                                else
                                {
                                    $cardpic = "src/unknown_profile.svg";
                                    $picstyle = "filter:invert(100%);width:500px";
                                }
                                echo("<div class='card centered-content-vertical'>");                            
                                echo("<a href='itemdetails.php?item_id=".strval($row['person_id'])."&is_person=1'>");
                                echo("<img id='poster' src='".$cardpic."' style='".$picstyle."'/>");
                                echo("</a>");
                                echo("<div>");
                                echo("<b>".$row['person_name']."</b>");
                                echo("<br>");
                                echo("<i class='media-type-footer'>as '".$row['role_name']."'</i>");
                                echo("</div>");
                                echo("</div>");
                            }
                            echo("</div>");
                        }
                    }
                    else
                    {
                        $query = "SELECT DISTINCT m._id _id, m.title title, m.poster poster, pr.role_name role_name 
                        FROM media m JOIN people_roles pr ON m._id=pr.media_id WHERE pr.person_id = {$item_id};";
                        $results = $db_connection->query($query);
                        if ($results->num_rows > 0)
                        {
                            echo("<div class='carousel-header'>
                                <h3>Participated In</h3>
                                </div>");
                            echo("<div class='carousel'>");
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
                                echo("<i class='media-type-footer'>as '".$row['role_name']."'</i>");
                                echo("</div>");
                                echo("</div>");
                            }
                            echo("</div>");
                        }
                    }
                }
            ?>
    </body>
</html>