<!doctype html>
<html lang="es">
<head>
    <?php include 'views/layout/head.php'; ?>
    <title>Mi Cajón de Archivos</title>
</head>
<body class="container-fluid">
    <header class="row">
        <div class="row"><?php include 'views/layout/header.php'; ?></div>
    </header>
    <main class="row">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <strong>Mi Cajón de Archivos</strong>
            </div>
            <div class="panel-body">
                <?php if ($error): ?>
                    <p style="color:red"><?= h($error) ?></p>
                <?php endif; ?>

                <?php if ($actual): ?>
                    <p>
                        <a href="index.php?c=Carpeta&a=listar<?= $actual['id_padre'] ? '&id=' . (int)$actual['id_padre'] : '' ?>">&larr; Subir un nivel</a>
                        | Carpeta actual: <strong><?= h($actual['nombre']) ?></strong>
                    </p>
                <?php endif; ?>

                <?php if ($propia): ?>
                    <a href="index.php?c=Carpeta&a=agregar&padre=<?= (int)($actual['id'] ?? 0) ?>">Agregar archivo</a>
                    <form action="index.php?c=Carpeta&a=crearCarpeta" method="post" style="margin-top:10px">
                        <input type="hidden" name="padre" value="<?= (int)($actual['id'] ?? 0) ?>">
                        <input type="text" name="txtCarpeta" maxlength="100" placeholder="Nombre de la carpeta" required>
                        <button type="submit" class="btn btn-sm btn-primary">Crear carpeta</button>
                    </form>
                <?php endif; ?>
                <br>

                <?php if (count($elementos) === 0): ?>
                    <p>La carpeta del usuario se encuentra vacía</p>
                <?php else: ?>
                <table class="table table-striped">
                    <tr>
                        <th>Nombre</th>
                        <th>Comentario</th>
                        <th>Tipo de documento</th>
                        <th>Peso (MB)</th>
                        <th>Fecha</th>
                        <th>Archivo / Directorio</th>
                        <th>Compartido</th>
                        <th>Compartido conmigo</th>
                        <th>Acción</th>
                    </tr>
                    <?php foreach ($elementos as $e): ?>
                    <tr>
                        <td>
                            <?php if ($e['es_carpeta']): ?>
                                <a href="index.php?c=Carpeta&a=listar&id=<?= (int)$e['id'] ?>"><?= h($e['nombre']) ?></a>
                            <?php else: ?>
                                <a href="index.php?c=Carpeta&a=ver&id=<?= (int)$e['id'] ?>"><?= h($e['nombre']) ?></a>
                            <?php endif; ?>
                        </td>
                        <td><?= h($e['comentario']) ?></td>
                        <td><?= $e['es_carpeta'] ? 'Carpeta' : h(Elemento::tipoDocumento($e['extension'])) ?></td>
                        <td><?= $e['es_carpeta'] ? '-' : number_format($e['tamano'] / 1048576, 3) ?></td>
                        <td><?= h(date('d/m/Y H:i', strtotime($e['fecha_creacion']))) ?></td>
                        <td><?= $e['es_carpeta'] ? 'Directorio' : 'Archivo' ?></td>
                        <td><?= $e['compartido'] ? 'Sí' : 'No' ?></td>
                        <td><?= $e['conmigo'] ? 'Sí (de ' . h($e['propietario']) . ')' : 'No' ?></td>
                        <td>
                            <?php if (!$e['conmigo']): ?>
                            <form action="index.php?c=Carpeta&a=compartir" method="post">
                                <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                                <input type="hidden" name="padre" value="<?= (int)($actual['id'] ?? 0) ?>">
                                <input type="text" name="txtDestino" maxlength="15" placeholder="Usuario" required>
                                <button type="submit" class="btn btn-sm btn-success">Compartir</button>
                            </form>
                            <form action="index.php?c=Carpeta&a=borrar" method="post"
                                  onsubmit="return confirm('¿Borrar este elemento?');">
                                <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                                <input type="hidden" name="padre" value="<?= (int)($actual['id'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Borrar</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <footer class="row"><?php include 'views/layout/foot.php'; ?></footer>
</body>
</html>
