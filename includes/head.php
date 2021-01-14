<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 21/02/2019
 * Time: 15:46
 */

include_once "connect.php";

openConnection();

?>

<!DOCTYPE html>
<html lang="">
<head>
    <link href='style.css' rel='stylesheet' type='text/css'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport'>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>

<body>
<div class="header">
    <h1>Ace Training</h1>
    <p>
        <?php
        if (isset($_GET['log']) && $_GET['log']==0)
        {
            echo "You have been successfully logged out.";
        }
        closeConnection();
        ?>

    </p>
</div>