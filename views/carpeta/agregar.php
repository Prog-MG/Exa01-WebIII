<!doctype html>
<html lang="es">
<head>
    <?php include 'views/layout/head.php'; ?>
    <title>Agregar archivos</title>
</head>
<body class="container-fluid">
    <header class="row">
        <div class="row"><?php include 'views/layout/header.php'; ?></div>
    </header>
    <main class="row">
        <div class="panel panel-primary datos1">
            <div class="panel-heading">
                <strong>Agregar archivo</strong>
            </div>
            <div class="panel-body">
                <?php if ($error): ?>
                    <p style="color:red"><?= h($error) ?></p>
                <?php endif; ?>
                <form action="index.php?c=Carpeta&a=guardar" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="padre" value="<?= (int)$padre ?>">
                    <fieldset>
                        <label><strong>Archivo</strong></label>
                        <input name="txtArchi" type="file" required /><br>
                        <label><strong>Comentario</strong></label>
                        <input name="txtComentario" type="text" size="40" maxlength="50" placeholder="De qué trata el archivo" /><br>
                    </fieldset>
                    <input type="submit" value="Cargar" />
                    <a href="index.php?c=Carpeta&a=listar<?= $padre ? '&id=' . (int)$padre : '' ?>">Cancelar</a>
                </form>
            </div>
        </div>
    </main>
    <footer class="row"><?php include 'views/layout/foot.php'; ?></footer>
</body>
</html>
