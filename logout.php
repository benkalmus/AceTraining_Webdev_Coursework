<?php
/**
 * Created by PhpStorm.
 * User: kalmu
 * Date: 20/02/2019
 * Time: 21:45
 */
include_once 'includes/connect.php';
openConnection();

$_SESSION = [];
session_destroy();

closeConnection();
header("Location: login.php?log=0");


?>
