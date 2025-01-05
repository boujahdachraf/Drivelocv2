<?php
require_once 'config/Session.php';

$session = Session::getInstance();
$session->destroy();

header('Location: login.php');
exit;
?>
