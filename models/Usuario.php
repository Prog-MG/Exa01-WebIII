<?php
class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Contra usuario
    public function validar($usuario, $contra)
    {
        $st = $this->db->prepare('SELECT usuario, nombre, contra FROM usuarios WHERE usuario = ?');
        $st->execute([$usuario]);
        $u = $st->fetch();

        if (!$u) {
            return false;
        }
        if (password_verify($contra, $u['contra'])) {
            return $u;
        }
        // Usuarios de la versión anterior (md5): se aceptan y se actualiza su clave
        if ($u['contra'] === md5($contra)) {
            $st = $this->db->prepare('UPDATE usuarios SET contra = ? WHERE usuario = ?');
            $st->execute([password_hash($contra, PASSWORD_DEFAULT), $usuario]);
            return $u;
        }
        return false;
    }

    // Registra un usuario nuevo
    public function registrar($usuario, $contra, $nombre, $email)
    {
        try {
            $st = $this->db->prepare(
                'INSERT INTO usuarios (usuario, contra, nombre, email) VALUES (?, ?, ?, ?)'
            );
            $st->execute([$usuario, password_hash($contra, PASSWORD_DEFAULT), $nombre, $email]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
