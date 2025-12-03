<?php
$host = 'localhost';
$usuario = 'root'; // Padrão do XAMPP
$senha = ''; // Padrão do XAMPP (vazia)
$banco = 'befit_system';

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>