<?php
session_start();
error_reporting(0);

if($_SESSION["admin"])
{
    unset($_SESSION["admin"]);
    header("Location: login.php");
}


elseif ($_SESSION["user"])
{
    unset($_SESSION["user"]);
    header("Location: login.php");
}

else
{
    header("Location: login.php");
}
?>