<?php
session_start();
session_unset();
session_destroy();
ob_start();
header("location:login.html");
ob_end_flush(); 
include 'login.html';
exit();
?>