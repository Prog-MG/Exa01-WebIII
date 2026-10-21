<!doctype html>
<html lang="es">
<head>
    <?php include 'views/layout/head.php'; ?>
    <title>Registrarse al Sitio</title>
</head>
<body class="container-fluid">
    <header class="row">
        <div class="row"><?php include 'views/layout/header.php'; ?></div>
    </header>
    <main class="row">
        <div class="panel panel-primary datos3">
            <div class="panel-heading">
                <strong>Datos del nuevo usuario</strong>
            </div>
            <div class="panel-body">
                <?php if ($error): ?>
                    <p style="color:red"><?= h($error) ?></p>
                <?php endif; ?>
                <form action="index.php?c=Usuario&a=registro" method="post">
                    <fieldset>
                        <label>Usuario:</label><input type="text" name="txtUsua" size="22" maxlength="15" required /><br>
                        <label>Contraseña:</label><input type="password" name="txtContra" size="22" maxlength="15" required /><br>
                        <label>Nombre Completo:</label><input type="text" name="txtNomb" size="40" maxlength="30" required /><br>
                        <label>Correo Electrónico:</label><input type="email" name="txtEmail" size="50" maxlength="50" required /><br>
                    </fieldset>
                    <input type="submit" value="Aceptar" />
                </form>
            </div>
        </div>
    </main>
    <footer class="row"><?php include 'views/layout/foot.php'; ?></footer>
</body>
</html>
