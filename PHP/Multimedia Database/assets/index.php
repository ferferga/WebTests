<?php
    $db_connection = new mysqli("localhost", "root", "", "multimedia_library");
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="src/styles.css" rel="stylesheet">
        <?php
            // When starting, we should check that the database is created. We check it by running the first 'create table' 
            // that must be created (one that doesn't has any foreign keys to other tables). That's the case of the 'users' table,
            // where we will store all the data of our users.
            $integrity = "CREATE TABLE users (_id INTEGER PRIMARY KEY AUTO_INCREMENT, profile_pic LONGBLOB,
                    name TEXT NOT NULL, username TEXT UNIQUE NOT NULL, email TEXT UNIQUE NOT NULL, password TEXT NOT NULL);";
            // This if statement will be true if the table was created successfully (meaning that the database is not created yet).
            if($db_connection->query($integrity) === TRUE)
            {                
                // We are going to use IDs instead of text for identifying media types, as it should be faster for queries
                // 0 - Movie
                // 1 - TV Show
                // 2 - TV Show Season
                // 3 - TV Show Episode
                // 4 - Album
                // 5 - Song
                // Storing files in a database is a bad practice, however, it's easier in our test case
                // as we don't want to deal woth permissions and our database is going to be small. That's why
                // i'm using BLOB for images
                $db_connection->query("CREATE TABLE media (_id INTEGER PRIMARY KEY AUTO_INCREMENT, media_type_id INTEGER, 
                    release_date DATE, added_date DATE, description TEXT, title TEXT, poster LONGBLOB, aired_on TEXT,
                    recorded_on TEXT, parent_id INTEGER, added_by INTEGER, 
                    FOREIGN KEY (parent_id) REFERENCES media(_id) ON DELETE CASCADE,
                    FOREIGN KEY (added_by) REFERENCES users(_id) ON DELETE SET NULL);");
                // For giving more flexibility to the users' authentication, each user will be able to log in in multiple devices, were we will store
                // a random number (which we will call 'ticket') in the DB and in a cookie. If they coincide at runtime, the user
                // will be logged in automatically.
                $db_connection->query("CREATE TABLE user_tickets (user_id INTEGER, ticket_id INTEGER UNIQUE, 
                    FOREIGN KEY (user_id) REFERENCES users(_id) ON DELETE CASCADE);");          
                // For giving much more flexibility, I'm going to use a 'people' table to store all the details of the people involved
                // in the media items (directors, producers, artists, actors...)
                $db_connection->query("CREATE TABLE people (_id INTEGER PRIMARY KEY AUTO_INCREMENT, person_name TEXT, biography TEXT, 
                    poster LONGBLOB, added_date DATE, added_by INTEGER, FOREIGN KEY (added_by) REFERENCES users(_id) ON DELETE SET NULL);");
                $db_connection->query("CREATE TABLE roles (person_id INTEGER, role_name TEXT, media_id INTEGER, 
                    FOREIGN KEY (person_id) REFERENCES people(_id) ON DELETE CASCADE, 
                    FOREIGN KEY (media_id) REFERENCES media(_id) ON DELETE CASCADE);");                
                // We also create views for simplifying our tasks:
                $db_connection->query("CREATE VIEW user_info_tickets AS SELECT ut.ticket_id ticket_id, u._id _id, 
                    u.profile_pic profile_pic, u.username username, u.email email, 
                    u.password password, u.name name FROM users u JOIN user_tickets ut ON u._id=ut.user_id");
                $db_connection->query("CREATE VIEW people_roles AS SELECT p._id person_id, p.person_name person_name, p.biography biography, p.poster poster, 
                    r.media_id media_id, r.role_name role_name FROM people p LEFT JOIN roles r ON p._id=r.person_id");
                // This trigger will automatically add the roles records so all the 'child' items inherit the 'parent' items roles
                $db_connection->query("CREATE TRIGGER inherit_people_roles AFTER UPDATE ON media FOR EACH ROW 
                INSERT INTO roles SELECT person_id, role_name, OLD._id FROM roles WHERE media_id=NEW.parent_id AND (person_id,role_name,OLD._id) 
                NOT IN (SELECT * FROM roles)");
                // An index will be also helpful when the roles table grows a lot.
                $db_connection->query("CREATE INDEX role_index ON roles(person_id, media_id);");
            }
            else
            {
                // The database is already created, so we continue by checking if there is a logged user into our system by using the
                // 'ticket system'
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
            }
        ?>
        <title>Fernando's Multimedia Database</title>
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
            <div class="carousel-header">
                <h3>Recently added Movies</h3>
                <div class="carousel-actions carousel-header">
                    <form action="add_content.php" method="GET">
                        <button type="submit" style="border: none;background-color: #202224;" name="add_movie">
                            <img width="35px" height="35px" src="src/add_circle.svg" style="filter: invert(100%);"/>
                        </button>
                    </form>    
                    <form action="content.php" method="GET">
                        <input type="submit" value="See All" id="colorful-button" name="type_movies">
                    </form>                    
                </div>
            </div>
            <div class="carousel">
                <?php
                    if (isset($_POST['search-submit']))
                    {
                        $search = $db_connection->real_escape_string($_POST['search-text']);
                        $query = "SELECT _id, title, poster, release_date FROM media WHERE media_type_id = 0 AND (title LIKE '%{$search}%' 
                            OR description LIKE '%{$search}%') ORDER BY _id DESC LIMIT 7";
                    }
                    else
                    {
                        $query = "SELECT _id, title, poster, release_date FROM media WHERE media_type_id = 0 ORDER BY _id DESC LIMIT 7";
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
                            echo("</div>");
                            echo("</div>");
                        }
                    }
                    else
                    {
                        echo("<a id='informativeText'>There aren't any TV Shows to display</a>");
                    }
                ?>
            </div>
            <div class="carousel-header">
                <h3>Recently added to TV Shows</h3>
                <div class="carousel-actions carousel-header">                    
                    <form action="add_content.php" method="GET">
                        <button type="submit" style="border: none;background-color: #202224;" name="add_tvshow">
                            <img width="35px" height="35px" src="src/add_circle.svg" style="filter: invert(100%);"/>
                        </button>
                    </form>
                    <form action="content.php" method="GET">
                        <input type="submit" value="See All" id="colorful-button" name="type_shows">
                    </form>
                </div>
            </div>
            <div class="carousel">
                <?php
                    if (isset($_POST['search-submit']))
                    {
                        $search = $db_connection->real_escape_string($_POST['search-text']);
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE (media_type_id = 1 OR media_type_id = 2 OR
                            media_type_id = 3) AND (title LIKE '%{$search}%' OR description LIKE '%{$search}%') ORDER BY _id DESC LIMIT 7";
                    }
                    else
                    {
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE (media_type_id = 1 OR media_type_id = 2) ORDER BY _id DESC LIMIT 7";
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
                            echo("<br>");
                            echo("<i>".date('Y', strtotime($row['release_date']))."</i>");                            
                            echo("</div>");
                            echo("</div>");
                        }
                    }
                    else
                    {
                        echo("<a id='informativeText'>There aren't any TV Shows to display</a>");
                    }
                ?>
            </div>
            <div class="carousel-header">
                <h3>Recently added to Music</h3>
                <div class="carousel-actions carousel-header">                    
                    <form action="add_content.php" method="GET">
                        <button type="submit" style="border: none;background-color: #202224;" name="add_music">
                            <img width="35px" height="35px" src="src/add_circle.svg" style="filter: invert(100%);"/>
                        </button>
                    </form>
                    <form action="content.php" method="GET">
                        <input type="submit" value="See All" id="colorful-button" name="type_music">
                    </form>
                </div>
            </div>
            <div class="carousel">
                <?php
                    if (isset($_POST['search-submit']))
                    {
                        $search = $db_connection->real_escape_string($_POST['search-text']);
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE (media_type_id = 4 OR media_type_id = 5) 
                            AND (title LIKE '%{$search}%' OR description LIKE '%{$search}%') ORDER BY _id DESC LIMIT 7";
                    }
                    else
                    {
                        $query = "SELECT _id, title, poster, release_date, media_type_id FROM media WHERE (media_type_id = 4 
                        OR media_type_id = 5) ORDER BY _id DESC LIMIT 7";
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
                            if($row['media_type_id'] == 4)
                            {
                                echo("<i class='media-type-footer'>Album</i>");
                            }
                            elseif($row['media_type_id'] == 5)
                            {
                                echo("<i class='media-type-footer'>Song</i>");
                            }
                            echo("<br>");
                            echo("<i>".date('Y', strtotime($row['release_date']))."</i>");
                            echo("</div>");
                            echo("</div>");
                        }
                    }
                    else
                    {
                        echo("<a id='informativeText'>There aren't any music album or song to display</a>");
                    }
                ?>
            </div>
        </div>
    </body>
</html>