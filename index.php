<?php
// Controlador frontal: todas las páginas entran por aquí
//   index.php?c=Controlador&a=accion
session_start();

require_once 'config/Conexion.php';
require_once 'models/Usuario.php';
require_once 'models/Elemento.php';

// Función corta para escapar texto en las vistas
function h($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

$c = $_GET['c'] ?? 'Usuario';
$a = $_GET['a'] ?? 'login';

$clase   = $c . 'Controller';
$archivo = 'controllers/' . $clase . '.php';

if (!preg_match('/^[A-Za-z]+$/', $c) || !preg_match('/^[A-Za-z]+$/', $a) || !is_file($archivo)) {
    http_response_code(404);
    exit('Página no encontrada');
}

require_once $archivo;
$controlador = new $clase();

if (!method_exists($controlador, $a)) {
    http_response_code(404);
    exit('Página no encontrada');
}

$controlador->$a();
