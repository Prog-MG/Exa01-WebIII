<?php
class UsuarioController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Usuario();
    }

    public function login()
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $u = $this->modelo->validar(trim($_POST['txtUsua'] ?? ''), $_POST['txtContra'] ?? '');
            if ($u) {
                $this->iniciarSesion($u['usuario'], $u['nombre']);
            }
            $error = 'Usuario o contraseña incorrectos';
        }
        require 'views/usuario/login.php';
    }

    public function registro()
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim($_POST['txtUsua'] ?? '');
            $contra  = trim($_POST['txtContra'] ?? '');
            $nombre  = trim($_POST['txtNomb'] ?? '');
            $email   = trim($_POST['txtEmail'] ?? '');

            if ($usuario === '' || $contra === '' || $nombre === '' || $email === '') {
                $error = 'Complete todos los campos';
            } elseif ($this->modelo->registrar($usuario, $contra, $nombre, $email)) {
                $this->iniciarSesion($usuario, $nombre);
            } else {
                $error = 'El usuario ya existe';
            }
        }
        require 'views/usuario/registro.php';
    }

    public function salir()
    {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    }

    private function iniciarSesion($usuario, $nombre)
    {
        session_regenerate_id(true);
        $_SESSION['autenticado'] = 'SI';
        $_SESSION['usuario']     = $usuario;
        $_SESSION['nombre']      = $nombre;
        header('Location: index.php?c=Carpeta&a=listar');
        exit();
    }
}
