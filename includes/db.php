<?php
$host='127.0.0.1'; 
$user='u152982153_ativacn'; 
$pass='11020407Diego'; 
$dbname='u152982153_ativa';
$conn = new mysqli($host,$user,$pass,$dbname);
if ($conn->connect_error) die('Erro BD: '.$conn->connect_error);
$conn->set_charset('utf8mb4');
?>