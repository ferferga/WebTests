<?php
    $db_connection = new mysqli("localhost", "root", "", "multimedia_library");
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="src/styles.css" rel="stylesheet">
        <title>Log-in to FMD</title>
        <style>
            .content{
                display: flex;
                justify-content: center;
                align-items: center;
                flex-wrap: wrap;
            }
            form {
                text-align: center;
                width: 500px;
            }
            form > input {
                margin-bottom: 15px;
            }
            .content > div {
                padding: 2%;
            }
            #placeholder-for-social > img:hover {
                cursor: pointer;
            }
        </style>
        <?php
            if (isset($_POST['register-button']))
            {

                $query = "INSERT INTO users VALUES(NULL,NULL,?,?,?,?)";
                $results = $db_connection->prepare($query);
                $results->bind_param('ssss', $_POST['user_name'], $_POST['user_username'], $_POST['user_email'], $_POST['user_password']);                
                if ($results->execute())
                {                    
                    $user_ticket = rand();
                    $user_id = $db_connection->insert_id;
                    $db_connection->query("INSERT INTO user_tickets VALUES(".$user_id.",".$user_ticket.");");
                    setcookie('ferferga_media_db_ticket', $user_ticket, time() + 86400);
                    header("Location: index.php");
                    die();
                }
                else
                {
                    $user_exists = TRUE;
                }
            }
            elseif (isset($_POST['login-button']))
            {
                $query = "SELECT _id FROM users WHERE username = ? AND password = ?";
                $results = $db_connection->prepare($query);
                $results->bind_param('ss', $_POST['login_username'], $_POST['login_password']);
                $results->execute();
                $rows = $results->get_result();
                if ($rows->num_rows > 0)
                {
                    $user_ticket = rand();
                    while($row=$rows->fetch_assoc())
                    {
                        $user_id = $row['_id'];
                    }
                    $ticket_assignment = $db_connection->query("INSERT INTO user_tickets VALUES(".$user_id.",".$user_ticket.");");
                    while (!$ticket_assignment)
                    {
                        $user_ticket = rand();
                        $ticket_assignment = $db_connection->query("INSERT INTO user_tickets VALUES(".$user_id.",".$user_ticket.");");
                    }
                    setcookie('ferferga_media_db_ticket', $user_ticket, time() + 86400);
                    $results->close();
                    header("Location: index.php");
                    die();
                }
                else
                {
                    $incorrect_login = FALSE;
                }
            }
        ?>
    </head>
    <body>
        <form style='width:initial;text-align:initial' action='index.php'>
            <button style="border:none;background-color:#202224;color:white">
                <h1 id="header">Fernando's Multimedia DB</h1>
            </button>
        </form>
        <br>
        <h3>Two ways to join the community, for a simple authentication</h3>
        <br>
        <h5 style="text-align:center;">Using a Fernando's Multimedia Database (FMD) passport:</h5>
        <div class="content">
            <div id="sign-up-form">
                <h3>Not an user? Sign-up here!</h3>
                <?php
                    if (isset($user_exists))
                    {
                        echo("<h3 style='color:red'>The user already exists</h3>");
                    }
                ?>
                <form action="" method="POST">
                    <input type="text" id="textbox" placeholder="Your name" name="user_name" required/>
                    <br>
                    <input type="text" id="textbox" placeholder="Your e-mail address" name="user_email" required/>
                    <br>
                    <input type="text" id="textbox" placeholder="Your FMD's username" name="user_username" required/>
                    <br>
                    <input type="password" id="textbox" placeholder="Your password" name="user_password" required/>
                    <br>
                    <input type="submit" id="colorful-button" value="Sign Up" name="register-button"/>
                </form>
            </div>
            <div id="login-form">
            <h3>Already an user? Enter your credentials:</h3>
                <?php
                    if (isset($incorrect_login))
                    {
                        echo("<h3 style='color:red'>Incorrect login details</h3>");
                    }
                ?>
                <form action="" method="POST">
                    <input type="text" id="textbox" placeholder="Your FMD's username" name="login_username" required/>
                    <br>
                    <input type="password" id="textbox" placeholder="Your password" name="login_password" required/>
                    <br>
                    <input type="submit" id="colorful-button" value="Log In" name="login-button"/>
                </form>
            </div>
        </div>
        <h5 style="text-align:center">Or using social media:</h5>
        <div id="placeholder-for-social" style="text-align:center;">
            <img src="src/google_logo.svg" width="50px" height="50px" />
            <img src="src/twitter_logo.svg" width="50px" height="50px" />
        </div>
    </body>
</html>