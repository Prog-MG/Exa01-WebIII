<?php
class CarpetaController
{
    private $modelo;

    //autenticación de usuarios
    public function __construct()
    {
        if (($_SESSION['autenticado'] ?? '') !== 'SI') {
            header('Location: index.php');
            exit();
        }
        $this->modelo = new Elemento();
    }

    // Redirige al listado de una carpeta (0 = raíz)
    private function volver($padre, $extra = '')
    {
        header('Location: index.php?c=Carpeta&a=listar' . ($padre ? '&id=' . $padre : '') . $extra);
        exit();
    }

    // carpetas.php propios y compartidos, raíz o dentro de una carpeta
    public function listar()
    {
        $usuario = $_SESSION['usuario'];
        $id      = (int)($_GET['id'] ?? 0);
        $actual  = null;
        $error   = isset($_GET['error']) ? 'No se pudo compartir (verifique que el usuario exista).' : '';

        if ($id) {
            $actual = $this->modelo->obtener($id);
            if (!$actual || !$actual['es_carpeta'] || !$this->modelo->puedeVer($id, $usuario)) {
                $this->volver(0);
            }
            $elementos = $this->modelo->listarHijos($id, $usuario);
        } else {
            $propios   = $this->modelo->listar($usuario, null);
            $conmigo   = $this->modelo->listarCompartidosConmigo($usuario);
            $elementos = array_merge($propios, $conmigo);
        }
        // Solo se puede crear/subir en la raíz o en carpetas propias
        $propia = !$actual || $actual['usuario'] === $usuario;
        require 'views/carpeta/lista.php';
    }

    // Muestra el formulario para subir un archivo
    public function agregar()
    {
        $error = '';
        $padre = (int)($_GET['padre'] ?? 0);
        require 'views/carpeta/agregar.php';
    }

    // Recibe el archivo y lo guarda en la base de datos
    public function guardar()
    {
        $error = '';
        $padre = (int)($_POST['padre'] ?? 0);
        $f = $_FILES['txtArchi'] ?? null;

        if ($padre) {
            $carpeta = $this->modelo->obtener($padre);
            if (!$carpeta || !$carpeta['es_carpeta'] || $carpeta['usuario'] !== $_SESSION['usuario']) {
                $this->volver(0);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$f || $f['error'] !== UPLOAD_ERR_OK) {
            $error = 'No se pudo cargar el archivo. Verifique que seleccionó uno y que no sea demasiado grande.';
        } else {
            $nombre     = str_replace(' ', '_', basename($f['name']));
            $extension  = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            $comentario = mb_substr(trim($_POST['txtComentario'] ?? ''), 0, 50);
            $contenido  = file_get_contents($f['tmp_name']);

            $this->modelo->guardarArchivo(
                $_SESSION['usuario'], $padre ?: null, $nombre, $comentario,
                $extension, $f['type'], $f['size'], $contenido
            );
            $this->volver($padre);
        }
        require 'views/carpeta/agregar.php';
    }

    // Crea una carpeta en la raíz o dentro de una carpeta propia
    public function crearCarpeta()
    {
        $padre  = (int)($_POST['padre'] ?? 0);
        $nombre = mb_substr(trim($_POST['txtCarpeta'] ?? ''), 0, 100);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $nombre !== '') {
            $carpeta = $padre ? $this->modelo->obtener($padre) : null;
            if (!$padre || ($carpeta && $carpeta['es_carpeta'] && $carpeta['usuario'] === $_SESSION['usuario'])) {
                $this->modelo->crearCarpeta($_SESSION['usuario'], $padre ?: null, $nombre);
            }
        }
        $this->volver($padre);
    }

    // Comparte una carpeta (y su contenido) o un archivo con otro usuario
    public function compartir()
    {
        $padre = (int)($_POST['padre'] ?? 0);
        $extra = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->modelo->compartir((int)($_POST['id'] ?? 0), $_SESSION['usuario'], trim($_POST['txtDestino'] ?? ''));
            $extra = $ok ? '' : '&error=1';
        }
        $this->volver($padre, $extra);
    }

    // pdf e imágenes se muestran en pantalla, lo demás se descarga
    public function ver()
    {
        $id = (int)($_GET['id'] ?? 0);
        $e  = $this->modelo->obtener($id);

        if (!$e || $e['es_carpeta'] || !$this->modelo->puedeVer($id, $_SESSION['usuario'])) {
            http_response_code(404);
            exit('Archivo no encontrado');
        }

        $mostrar = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif'  => 'image/gif',  'bmp'  => 'image/bmp',  'webp' => 'image/webp',
        ];
        $ext    = strtolower((string)$e['extension']);
        $nombre = str_replace('"', '', $e['nombre']);

        if (isset($mostrar[$ext])) {
            header('Content-Type: ' . $mostrar[$ext]);
            header('Content-Disposition: inline; filename="' . $nombre . '"');
        } else {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $nombre . '"');
        }
        echo $this->modelo->contenido($id);
        exit();
    }

    public function borrar()
    {
        $padre = (int)($_POST['padre'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->modelo->borrar((int)($_POST['id'] ?? 0), $_SESSION['usuario']);
        }
        $this->volver($padre);
    }
}
