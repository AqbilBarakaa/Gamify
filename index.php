<?php
session_start();

if (isset($_SESSION["admin"]))
{
	header("Location: admin/index.php");
}

elseif (isset($_SESSION["user"]))
{
	header("Location: user/index.php");
}

else
{
	header("Location: login.php");
}

?>