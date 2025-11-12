<?php
session_start();
if (!isset($_SESSION['usuario'])) header('Location: login.php');
if ($_SESSION['perfil'] === 'administrador') header('Location: admin/index.php');
else header('Location: fiscal/index.php');
exit;
?>