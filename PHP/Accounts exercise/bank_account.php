<?php
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Alata&display=swap" rel="stylesheet">
        <style>
            * {
                font-family: 'Alata', sans-serif;
            }
            #main {
                display: flex;
                flex-direction: column;
                width: 100%;
            }
            #message {
                padding: 10px;
                color: grey;
            }
            #notification { 
                float: right;
                padding: 25px;
                border: 1px;
                color: #ff884d;
            }
        </style>
        <title>The easy bank</title>
        <?php
            if (!isset($_COOKIE['accounts']) && !isset($_COOKIE['passwords']) && !isset($_COOKIE['names']) && !isset($_COOKIE['quantities']))
            {
                $accounts = array("11111111A", "22222222B", "33333333C");
                $passwords = array("abcd", "abcd", "abcd");
                $names = array("Agustín Martínez Bravo", "Sergio Salgado Delgado", "María Laguillo del Moral");
                $quantities = array(25000, 15000, 20000);
                setcookie('accounts', serialize($accounts), time() + 365 * 24 * 60 * 60);
                setcookie('passwords', serialize($passwords), time() + 365 * 24 * 60 * 60);
                setcookie('names', serialize($names), time() + 365 * 24 * 60 * 60);
                setcookie('quantities', serialize($quantities), time() + 365 * 24 * 60 * 60);
            }
            else
            {
                $accounts = unserialize($_COOKIE['accounts']);
                $passwords = unserialize($_COOKIE['passwords']);
                $names = unserialize($_COOKIE['names']);
                $quantities = unserialize($_COOKIE['quantities']);
            }
            class Accounting
            {
                function update_account_info()
                {
                    global $accounts;
                    global $passwords;
                    global $names;
                    global $quantities;
                    setcookie('accounts', serialize($accounts), time() + 365 * 24 * 60 * 60);
                    setcookie('passwords', serialize($passwords), time() + 365 * 24 * 60 * 60);
                    setcookie('names', serialize($names), time() + 365 * 24 * 60 * 60);
                    setcookie('quantities', serialize($quantities), time() + 365 * 24 * 60 * 60);
                }
                function deposit(string $account, int $quantity)
                {
                    global $accounts;
                    global $quantities;
                    $quantities[array_search($account, $accounts)] = $quantities[array_search($account, $accounts)]+$quantity;
                    $this->update_account_info();
                    return true;              
                }
                function withdraw(string $account, int $quantity)
                {
                    global $accounts;
                    global $quantities;
                    if (!($quantities[array_search($account, $accounts)] <= 0))
                    {
                        $quantities[array_search($account, $accounts)] = $quantities[array_search($account, $accounts)]-$quantity;
                        $this->update_account_info();
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }                    
                function transfer(string $origin_account, string $destination_account, int $quantity)
                {
                    global $accounts;
                    global $quantities;
                    if (!($quantities[array_search($origin_account, $accounts)] <= 0))
                    {
                        $quantities[array_search($origin_account, $accounts)] = $quantities[array_search($origin_account, $accounts)]-$quantity;
                        $quantities[array_search($destination_account, $accounts)] = $quantities[array_search($destination_account, $accounts)]+$quantity;
                        $this->update_account_info();
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                function get_balance(string $account)
                {
                    global $accounts;
                    global $quantities;
                    return $quantities[array_search($account, $accounts)];
                }
            }
            $customer_account = new Accounting();            
            if(isset($_POST['log_out']))
            {
                session_unset();
            }
            if(isset($_POST['go_back']))
            {
                $_SESSION["operation_progress"] = null;
            }
            if(isset($_POST['log_in']))
            {
                if (isset($_POST['account_id']) && isset($_POST['account_password']))
                {
                    $session_account = strtoupper(str_replace(" ", "", $_POST['account_id']));
                    $session_password = str_replace(" ", "", $_POST['account_password']);
                    $user_key = array_search($session_account, $accounts);
                    if(!is_bool($user_key))
                    {
                        if($session_password == $passwords[$user_key])
                        {
                            $_SESSION["logged_account"] = $session_account;
                        }
                        else
                        {
                            $error_message = "Your credentials are invalid";
                        }
                    }
                    else
                    {
                        $error_message = "Your credentials are invalid";
                    }                                         
                }
                else
                {
                    $error_message = "Your credentials are invalid";
                }
            }
            if (isset($_POST['deposit_button']))
            {
                $_SESSION["operation_progress"] = "deposit";
            }
            if (isset($_POST['withdraw_button']))
            {
                $_SESSION["operation_progress"] = "withdraw";
            }
            if (isset($_POST['transfer_button']))
            {
                $_SESSION["operation_progress"] = "transfer";
            }
            if (isset($_POST['confirm_transfer_button']))
            {
                if(isset($_POST['transfer_account']))
                {
                    $destination_account = strtoupper(str_replace(" ", "", $_POST['transfer_account']));
                    if ($destination_account != "" && $_POST['transfer_account'] < 1)
                    {
                        $error_message = "The provided values are invalid";
                    }
                    else
                    {
                        if($customer_account->transfer($_SESSION["logged_account"], $destination_account, $_POST['transfer_quantity']))
                        {
                            $notification_message = "Your transfer to ".$destination_account." was completed succesfully!";
                            $_SESSION["operation_progress"] = null;
                        }
                        else
                        {
                            $notification_message = "Your transfer to ".$destination_account." failed. Do you have enough funds?";
                        }
                    }
                }
                else
                {
                    $error_message = "The values or account ID are invalid";
                }
            }
            if (isset($_POST['confirm_withdraw_button']))
            {
                if($_POST['withdraw_quantity'] > 1)
                {
                    if($customer_account->withdraw($_SESSION["logged_account"], $_POST['withdraw_quantity']))
                    {
                        $notification_message = "Money withdrawn correctly";
                        $_SESSION["operation_progress"] = null;
                    }
                    else
                    {
                        $notification_message = "Money withdrawn failed. Do you have enough funds?";
                    }
                }
                else
                {
                    $error_message = "The provided values are invalid";
                }
            }
            if (isset($_POST['confirm_deposit_button']))
            {
                if($_POST['deposit_quantity'] > 1)
                {
                    $customer_account->deposit($_SESSION["logged_account"], $_POST['deposit_quantity']);
                    $notification_message = "Money deposited correctly";
                    $_SESSION["operation_progress"] = null;
                }
                else
                {
                    $error_message = "The provided values are invalid";
                }
            }
        ?>
    </head>
    <body>
        ferferga's Bank
        </br></br>
        <div id="main">
            <div id="header">
                <?php
                    if (!isset($_SESSION["logged_account"]))
                    {
                        echo("You are not logged in. Please, enter your credentials to operate inside your account</br></br>");
                    }
                    elseif (!isset($_SESSION["operation_progress"]))
                    {
                        echo("<b>Hello, ".$names[array_search($_SESSION["logged_account"], $accounts)]."</b>");
                        echo("</br></br>What can we do for you today?</br></br>");
                    }
                ?>
            </div>
            <?php
                if (!isset($_SESSION["logged_account"]))
                {
                    echo("<form action='' method='POST'>");
                    echo("<label for='account_id'>DNI: </label>");
                    echo("<input type='text' name='account_id'>");
                    echo("</br></br>");
                    echo("<label for='account_password'>Password: </label>");
                    echo("<input type='password' name='account_password'>");
                    echo("</br></br>");
                    echo("<input type='submit' value='Log in' name='log_in'>");
                }
                elseif (!isset($_SESSION["operation_progress"]) || $_SESSION["operation_progress"] == null)
                {
                    echo("<form action='' method='POST' style='display:flex'>");
                    echo("<input type='submit' value='Deposit' name='deposit_button'>");
                    echo("<input type='submit' value='Withdraw' name='withdraw_button'>");
                    echo("<input type='submit' value='Transfer' name='transfer_button'>");
                }
                elseif ($_SESSION["operation_progress"] == "transfer")
                {
                    echo("<form action='' method='POST'>");
                    echo("<label for='transfer_account'>DNI associated with the destination account: </label>");
                    echo("<input type='text' name='transfer_account'>");
                    echo("</br></br>");
                    echo("<label for='transfer_quantity'>Quantity to transfer: </label>");
                    echo("<input type='number' name='transfer_quantity'>");
                    echo("</br></br>");
                    echo("<input type='submit' value='<- Go back' name='go_back'>");
                    echo("<input type='submit' value='Transfer' name='confirm_transfer_button'>");
                }
                elseif ($_SESSION["operation_progress"] == "withdraw")
                {
                    echo("<form action='' method='POST'>");
                    echo("<label for='withdraw_quantity'>Quantity to withdraw: </label>");
                    echo("<input type='number' name='withdraw_quantity'>");
                    echo("</br></br>");
                    echo("<input type='submit' value='<- Go back' name='go_back'>");
                    echo("<input type='submit' value='Withdraw' name='confirm_withdraw_button'>");
                }
                elseif ($_SESSION["operation_progress"] == "deposit")
                {
                    echo("<form action='' method='POST'>");
                    echo("<label for='deposit_quantity'>Quantity to deposit: </label>");
                    echo("<input type='number' name='deposit_quantity'>");
                    echo("</br></br>");
                    echo("<input type='submit' value='<- Go back' name='go_back'>");
                    echo("<input type='submit' value='Deposit' name='confirm_deposit_button'>");                        
                }
            ?>
            </form>
            <div id="message">
                <hr/>
                <?php
                    if(isset($_SESSION["logged_account"]))
                    {
                        echo("<a>Your current balance: ".$customer_account->get_balance($_SESSION["logged_account"])." €</a></br></br>");
                        echo("<form action='' method='POST'>");
                        echo("<input type='submit' value='Log out' name='log_out'>");                        
                    }
                    elseif (isset($error_message))
                    {
                        echo("<a>".$error_message."</a>");
                    }                  
                ?>
            </div>
            <div id="notification">
                <?php
                    if (isset($notification_message))
                    {
                        echo("<a>".$notification_message."</a>");
                    }
                ?>
            </div>          
        </div>
    </body>
</html>