<?php

class Elemento
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Elementos propios dentro de una carpeta (null = raíz)
    public function listar($usuario, $idPadre = null)
    {
        $sql = 'SELECT e.id, e.nombre, e.comentario, e.extension, e.tamano,
                       e.fecha_creacion, e.es_carpeta, e.usuario AS propietario,
                       EXISTS(SELECT 1 FROM comparticiones c WHERE c.id_elemento = e.id) AS compartido,
                       0 AS conmigo
                FROM elementos e
                WHERE e.usuario = :usuario AND e.id_padre <=> :padre
                ORDER BY e.es_carpeta DESC, e.nombre';
        $st = $this->db->prepare($sql);
        $st->execute([':usuario' => $usuario, ':padre' => $idPadre]);
        return $st->fetchAll();
    }

    // Elementos que otros usuarios compartieron conmigo
    public function listarCompartidosConmigo($usuario)
    {
        $sql = 'SELECT e.id, e.nombre, e.comentario, e.extension, e.tamano,
                       e.fecha_creacion, e.es_carpeta, e.usuario AS propietario,
                       0 AS compartido,
                       1 AS conmigo
                FROM comparticiones c
                JOIN elementos e ON e.id = c.id_elemento
                WHERE c.usuario_destino = ?
                ORDER BY e.es_carpeta DESC, e.nombre';
        $st = $this->db->prepare($sql);
        $st->execute([$usuario]);
        return $st->fetchAll();
    }

    // Un elemento sin su contenido
    public function obtener($id)
    {
        $st = $this->db->prepare('SELECT id, usuario, id_padre, nombre, es_carpeta, extension FROM elementos WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch();
    }

    // Contenido (BLOB) de un archivo
    public function contenido($id)
    {
        $st = $this->db->prepare('SELECT contenido FROM elementos WHERE id = ?');
        $st->execute([$id]);
        return $st->fetchColumn();
    }

    // Contenido de una carpeta (propia o compartida)
    public function listarHijos($idPadre, $usuario)
    {
        $sql = 'SELECT e.id, e.nombre, e.comentario, e.extension, e.tamano,
                       e.fecha_creacion, e.es_carpeta, e.usuario AS propietario,
                       EXISTS(SELECT 1 FROM comparticiones c WHERE c.id_elemento = e.id) AS compartido,
                       (e.usuario <> :usuario) AS conmigo
                FROM elementos e
                WHERE e.id_padre = :padre
                ORDER BY e.es_carpeta DESC, e.nombre';
        $st = $this->db->prepare($sql);
        $st->execute([':usuario' => $usuario, ':padre' => $idPadre]);
        return $st->fetchAll();
    }

    // Puede ver el elemento si es suyo, o si el elemento o alguna carpeta superior se le compartió
    public function puedeVer($id, $usuario)
    {
        while ($id) {
            $e = $this->obtener($id);
            if (!$e) {
                return false;
            }
            if ($e['usuario'] === $usuario) {
                return true;
            }
            $st = $this->db->prepare('SELECT 1 FROM comparticiones WHERE id_elemento = ? AND usuario_destino = ?');
            $st->execute([$id, $usuario]);
            if ($st->fetch()) {
                return true;
            }
            $id = $e['id_padre'];
        }
        return false;
    }

    // Crea una carpeta en DB
    public function crearCarpeta($usuario, $idPadre, $nombre)
    {
        $st = $this->db->prepare('INSERT INTO elementos (usuario, id_padre, nombre, es_carpeta) VALUES (?, ?, ?, 1)');
        return $st->execute([$usuario, $idPadre, $nombre]);
    }

    // Comparte una carpeta o archivo propio con otro usuario
    public function compartir($id, $dueno, $destino)
    {
        $e = $this->obtener($id);
        if (!$e || $e['usuario'] !== $dueno || $destino === $dueno) {
            return false;
        }
        $st = $this->db->prepare('SELECT 1 FROM usuarios WHERE usuario = ?');
        $st->execute([$destino]);
        if (!$st->fetch()) {
            return false;
        }
        $st = $this->db->prepare('INSERT IGNORE INTO comparticiones (id_elemento, usuario_destino) VALUES (?, ?)');
        return $st->execute([$id, $destino]);
    }

    // Guarda un archivo en DB
    public function guardarArchivo($usuario, $idPadre, $nombre, $comentario, $extension, $mime, $tamano, $contenido)
    {
        $sql = 'INSERT INTO elementos
                    (usuario, id_padre, nombre, es_carpeta, comentario, extension, tipo_mime, tamano, contenido)
                VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?)';
        $st = $this->db->prepare($sql);
        return $st->execute([$usuario, $idPadre, $nombre, $comentario, $extension, $mime, $tamano, $contenido]);
    }

    // Solo el dueño puede borrar sus cositas
    public function borrar($id, $usuario)
    {
        $st = $this->db->prepare('DELETE FROM elementos WHERE id = ? AND usuario = ?');
        $st->execute([$id, $usuario]);
        return $st->rowCount() > 0;
    }

    // Tipo de documento segun extensión
    public static function tipoDocumento($extension)
    {
        $tipos = [
            'pdf'  => 'PDF',
            'doc'  => 'Word',       'docx' => 'Word',
            'xls'  => 'Excel',      'xlsx' => 'Excel',
            'ppt'  => 'PowerPoint', 'pptx' => 'PowerPoint',
            'txt'  => 'Texto',
            'jpg'  => 'Imagen',     'jpeg' => 'Imagen', 'png' => 'Imagen',
            'gif'  => 'Imagen',     'bmp'  => 'Imagen', 'webp' => 'Imagen',
            'zip'  => 'Comprimido', 'rar'  => 'Comprimido',
        ];
        return $tipos[strtolower((string)$extension)] ?? 'Otro';
    }
}
