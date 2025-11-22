<?php
session_start();
error_reporting(0);

if ($_SESSION["user"])
{
	unset($_SESSION["user"]);
	header("Location: ../login.php");
}

else
{
	header("Location: ../login.php");
}

?>