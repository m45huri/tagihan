<?php /* ====================================================
PHP Logout
========================================================== */
session_start();
session_unset('username');
session_destroy();
header('Location: index.php');