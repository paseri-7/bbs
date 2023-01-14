<?php
session_start();

unset($_SESSION['id']);
unset($_SESSION['name']);
unset($_SESSION['form']);
unset($_SESSION['user_form']);
unset($_SESSION['image']);


header('Location: login.php');
exit();
