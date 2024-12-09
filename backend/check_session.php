<?php
session_start();
$_SESSION['test'] = "Session is working!";
echo "Session ID: " . session_id() . "<br>";
echo "Session Data: " . $_SESSION['test'];
?>