<?php
    session_start();
    error_reporting(E_ERROR | E_PARSE);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db_connection = new mysqli("localhost", "root", "", "multimedia_library");
    $db_connection->query("SET GLOBAL max_allowed_packet=524288000;");
    $child_id = $db_connection->real_escape_string($_SESSION['childId']);
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
                $loggedIn = True;
                $user_id = $row[2];
            }
        }
        else
        {
            setcookie("ferferga_media_db_ticket", "", time() - 3600);
        }
        if (isset($_POST['save_changes']))
        {
            $runtime_mode = $_SESSION['runtimemode'];            
            if ($runtime_mode == 0)
            {
                session_destroy();
                header('Location: index.php');
            }
            else 
            {                
                header("Location: edititem.php?item_id=".$_SESSION['childId']."&is_person=0");
            }
            die();
        }
        if (isset($_POST['role-submit']))
        {
            $person_id = $db_connection->real_escape_string($_SESSION['editing_person_id']);
            if($_POST['role_name'] != null || !empty($_POST['role_name']))
            {
                $role_name = $db_connection->real_escape_string($_POST['role_name']);                
                $results = $db_connection->query("SELECT COUNT(person_id) c FROM roles WHERE media_id = {$child_id} AND person_id = {$person_id}");                
                if ($results->num_rows > 0)
                {
                    while($row=$results->fetch_assoc())
                    {
                        if ($row['c'] == 0)
                        {
                            $result = $db_connection->query("INSERT INTO roles(person_id, role_name, media_id) VALUES({$person_id}, '{$role_name}', {$child_id})");
                        }
                        else
                        {
                            $result = $db_connection->query("UPDATE roles SET role_name = '{$role_name}' WHERE person_id = {$person_id} AND media_id = {$child_id}");
                        }
                        if (!$result)
                        {
                            $query_error = true;
                        }
                    }
                }
            }
            else
            {
                $query = "DELETE FROM roles WHERE media_id = {$child_id} AND person_id = {$person_id}";
                $result = $db_connection->query($query);
                if (!$result)
                {
                    $query_error = True;
                }
            }            
            unset($_SESSION['editing_person_id']);
        }
        if (isset($_POST['person-delete']))
        {
            $person_id = $db_connection->real_escape_string($_SESSION['editing_person_id']);
            $query = "DELETE FROM people WHERE _id = {$person_id}";
            $result = $db_connection->query($query);
            if(!$result)
            {
                $query_error = True;
            }
            unset($_SESSION['editing_person_id']);
        }
        if (isset($_POST['submit-person']))
        {
            $name = $db_connection->real_escape_string($_POST['person_name']);
            $biography = $db_connection->real_escape_string($_POST['person_biography']);
            $info = getimagesize($_FILES['fileToUpload']['tmp_name']);
            if($info)
            {
                $data = $db_connection->real_escape_string(file_get_contents($_FILES['fileToUpload']['tmp_name']));
            }
            else
            {
                $data = null;
            }
            $current_date = date('Y-m-d', time());
            $result = $db_connection->query("INSERT INTO people(person_name,biography,poster,added_date,added_by) 
                VALUES('{$name}', '{$biography}', '{$data}', '{$current_date}', {$user_id});");
            if(!$result)
            {
                $query_error = true;
            }
            header('Location: people_management.php');
            die();
        }
    }    
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="src/styles.css" rel="stylesheet">
        <title>People Management</title>
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
                        if (!isset($_GET['person_id']) && !isset($_GET['add_person']))
                        {
                            echo("<form action='' method='POST'>");
                            echo("<input id='colorful-button' type='submit' name='save_changes' value='Save Changes'/>");
                            echo("</form>");
                            echo("<form action='' method='GET'>");
                            echo("<input id='colorful-button' type='submit' name='add_person' value='Add Person'/>");
                            echo("</form>");
                        }
                        echo("<a href='controlpanel.php'>");
                        echo("<div id='profile-pic' style='background-image:url(".$profilepic.");background-position:center center; 
                            background-repeat:no-repeat;background-size:cover;border-radius:100%;width:45px;height:45px;".$ppicstyling."'>");
                        echo("</div>");
                        echo("</a>");
                        echo("</div");
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
                elseif (!isset($_SESSION['childId']))
                {
                    echo("<h4 style='color:red'>You can't perform this action. Invalid data was passed in the session</h4>");
                }
                elseif(isset($query_error))
                {
                    echo("<h4 style='color:red'>There was an error in the query</h4>");
                }
                elseif(!isset($_GET['person_id']) && !isset($_GET['add_person']) && isset($_SESSION['childId']))
                {
                    echo("</div>");
                    echo("<div class='search_bar'>
                            <form action='' method='POST' class='centered-content'>
                                <input id='search-textbox' type='text' height='50px' name='search-text' placeholder=\"Search by people's names...\">
                                <button type='submit' style='border: none;background-color: #202224;' name='search-submit'>
                                    <img width='25px' height='25px' src='src/magnifyer.svg' style='filter: invert(100%);'/>
                                </button>
                            </form>
                        </div>");
                }
            ?>
            <?php
                if (isset($loggedIn) && isset($_GET['person_id']) && isset($_SESSION['childId']))
                {
                    $_SESSION['editing_person_id'] = $_GET['person_id'];
                    echo("<div style='margin: 0 auto;width:60%;margin-top:10%;text-align:center'>
                            <h3 style='text-align:center'>Editing this person's role in this item. Leave blank to remove</h3>
                            <br><br> 
                            <form action='people_management.php' method='POST' style='width:100%'>
                                <input type='text' id='textbox' style='width:100%' name='role_name' placeholder='Role of this person' />
                                <br><br>
                                <div>
                                <input type='submit' style='text-align:center;background-color:red' id='colorful-button' name='person-delete' value='Delete this person' />
                                <input type='submit' style='text-align:center' id='colorful-button' name='role-submit' value='Submit Role' />
                                </div>
                            </form>
                        </div>");
                }
                elseif(isset($loggedIn) && isset($_GET['add_person']) && isset($_SESSION['childId']))
                {
                    echo("<div class='centered-content-horizontal'>
                            <div style='justify-content: flex-start;'>
                            <div style='background-image:url(src/add_photo.svg);background-position:center center; 
                            background-repeat:no-repeat;background-size:cover;border-radius:100%;
                            width:200px;height:200px;filter:invert(100%)'>
                            </div>
                            </div>");
                    echo("<div class='centered-content-vertical form-content'>
                            <h2>Adding a Person</h2>
                            <br><br>
                            <form action='' method='POST' enctype='multipart/form-data' style='text-align:center'>
                                <input type='file' accept='image/*' name='fileToUpload' style='color:white'>
                                <br><br>
                                <label for='title'>Person Name: </label>
                                <br>
                                <input type='text' id='textbox' name='person_name' required />
                                <label for='title'>Biography: </label>
                                <br>
                                <textarea id='textbox' style='height:50%;' name='person_biography' required></textarea>
                                <br><br>
                                <input type='submit' id='colorful-button' name='submit-person' value='Submit'/>
                                <br><br>");
                }
                elseif(isset($loggedIn) && !isset($_GET['person_id']) && !isset($_GET['add_person']) && isset($_SESSION['childId']))
                {
                    echo("<div class='carousel-header'>");
                    echo("<h2>Available People</h2>");
                    $query = "SELECT title FROM media WHERE _id = {$child_id} LIMIT 1";
                    $results = $db_connection->query($query);                 
                    if ($results->num_rows > 0)
                    {
                        while($row=$results->fetch_assoc())
                        {
                            echo("<i>Currently editing item: ".$row['title']."</i>");
                        }
                    }
                    echo("</div>");
                    echo("<div class='carousel'>");
                    if (!isset($_POST['search-submit']))
                    {
                        $query = "SELECT person_id, person_name, poster, role_name, media_id FROM people_roles WHERE media_id = {$child_id} 
                        UNION SELECT _id, person_name, poster, NULL, NULL FROM people WHERE _id NOT IN 
                        (SELECT person_id FROM people_roles WHERE media_id = {$child_id})";
                    }
                    else
                    {
                        $search = $db_connection->real_escape_string($_POST['search-text']);
                        $query = "SELECT * FROM ((SELECT person_id, person_name, poster, role_name, media_id FROM people_roles WHERE media_id = {$child_id}) 
                        UNION (SELECT _id, person_name, poster, NULL, NULL FROM people WHERE _id NOT IN 
                        (SELECT person_id FROM people_roles WHERE media_id = {$child_id}))) AS results WHERE person_name LIKE '%{$search}%'";
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
                                $cardpic = "src/unknown_profile.svg";
                                $picstyle = "filter:invert(100%);width:500px";
                            }
                            echo("<div class='card centered-content-vertical'>");                            
                            echo("<a href='people_management.php?person_id=".strval($row['person_id'])."'>");
                            echo("<img id='poster' src='".$cardpic."' style='".$picstyle."'/>");
                            echo("</a>");
                            echo("<div>");
                            echo("<b>".$row['person_name']."</b>");
                            echo("<br>");
                            if($row['role_name'] != null && $row['media_id'] == $child_id)
                            {
                                echo("<i class='media-type-footer'>assigned as '".$row['role_name']."'</i>");
                            }                            
                            echo("</div>");
                            echo("</div>");
                        }
                        echo("</div>");
                    }
                    else
                    {
                        echo("<i>There are no results to show</i>");
                    }
                }
            ?>
            </div>
        </div>
    </body>
</html>