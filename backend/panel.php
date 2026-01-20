<?php
session_start();
require "conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// --- ACCIONES POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    if ($_POST['accion'] == 'añadir') {
        $autor = $_POST['autor'];
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $anio = $_POST['anio'];

        // --- RUTA MODIFICADA SEGÚN TU ESTRUCTURA ---
        // Sube un nivel para salir de 'backend' y entra en 'frontend/imgs'
        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $nombreArchivo = time() . '_' . basename($_FILES['foto']['name']);
            $rutaDestino = '../frontend/imgs/' . $nombreArchivo;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                // Guardamos la ruta relativa que usará el HTML para mostrarla
                $foto = '../frontend/imgs/' . $nombreArchivo;
            }
        }

        $sql = "INSERT INTO vinilos (autor, nombre, descripcion, precio, anio, foto) 
                VALUES ('$autor', '$nombre', '$descripcion', '$precio', '$anio', '$foto')";
        $conexion->query($sql);
    }

    if ($_POST['accion'] == 'borrar' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $conexion->query("DELETE FROM vinilos WHERE id = $id");
    }

    if ($_POST['accion'] == 'toggle' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $resultado = $conexion->query("SELECT visible FROM vinilos WHERE id = $id");
        $row = $resultado->fetch_assoc();
        $nuevoEstado = $row['visible'] ? 0 : 1;
        $conexion->query("UPDATE vinilos SET visible = $nuevoEstado WHERE id = $id");
    }

    // Redirección para evitar duplicados
    header("Location: panel.php");
    exit();
}

// --- BÚSQUEDA ---
$buscar = '';
if (isset($_GET['buscar'])) {
    $buscar = $_GET['buscar'];
    $sql_vinilos = "SELECT * FROM vinilos WHERE nombre LIKE '%$buscar%' OR autor LIKE '%$buscar%'";
} else {
    $sql_vinilos = "SELECT * FROM vinilos";
}
$result_vinilos = $conexion->query($sql_vinilos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Vinilos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    color: #e0e0e0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
h1, h2 { color: #ffcc00; text-shadow: 1px 1px 4px rgba(0,0,0,0.7); }

.card-vinilo {
    background: #1e2a38;
    border-radius: 15px;
    padding: 15px;
    margin-bottom: 25px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    transition: transform 0.3s, box-shadow 0.3s;
}
.card-vinilo:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 10px 25px rgba(255, 204, 0, 0.5);
}
.card-vinilo img {
    max-width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 12px;
    transition: transform 0.3s, filter 0.3s;
}
.card-vinilo img:hover { transform: scale(1.08); filter: brightness(1.1); }

.btn-action { margin: 3px; transition: all 0.3s ease; }
.btn-action:hover { transform: scale(1.1); }

.btn-primary {
    background: #ffcc00; border: none; color: #1e2a38; font-weight: bold; transition: background 0.3s;
}
.btn-primary:hover { background: #e6b800; }

.form-control, .btn { border-radius: 10px; }

.badge-visible { font-size: 0.85rem; padding: 0.5em 0.7em; font-weight: 500; border-radius: 12px; }

.container { max-width: 1200px; }

input[type=file] { padding: 3px; }
</style>
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bienvenido, <?php echo $_SESSION['usuario']; ?></h1>
        <a href="logout.php" class="btn btn-danger btn-lg">Cerrar sesión</a>
    </div>

    <hr class="border-warning">

    <form method="get" class="mb-4 row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="buscar" placeholder="Buscar vinilo o autor" class="form-control form-control-lg" value="<?php echo htmlspecialchars($buscar); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-lg">Buscar</button>
        </div>
    </form>

    <h2 class="mb-3">Añadir Vinilo</h2>
    <form method="post" enctype="multipart/form-data" class="mb-5 row g-3 align-items-center">
        <input type="hidden" name="accion" value="añadir">
        <div class="col-md-3"><input type="text" name="autor" placeholder="Autor" required class="form-control form-control-lg"></div>
        <div class="col-md-3"><input type="text" name="nombre" placeholder="Nombre del vinilo" required class="form-control form-control-lg"></div>
        <div class="col-md-3"><input type="text" name="descripcion" placeholder="Descripción" class="form-control form-control-lg"></div>
        <div class="col-md-1"><input type="number" step="0.01" name="precio" placeholder="Precio" required class="form-control form-control-lg"></div>
        <div class="col-md-1"><input type="number" name="anio" placeholder="Año" required class="form-control form-control-lg"></div>
        <div class="col-md-1"><input type="file" name="foto" accept="image/*" class="form-control form-control-lg"></div>
        <div class="col-md-1"><button type="submit" class="btn btn-success btn-lg w-100">Añadir</button></div>
    </form>

    <h2 class="mb-3">Vinilos</h2>
    <div class="row">
        <?php while ($vinilo = $result_vinilos->fetch_assoc()): ?>
        <div class="col-md-3">
            <div class="card-vinilo">
                <?php if($vinilo['foto']): ?>
                    <img src="<?php echo $vinilo['foto']; ?>" alt="<?php echo htmlspecialchars($vinilo['nombre']); ?>">
                <?php else: ?>
                    <div style="height:180px; background:#2c3e50; display:flex; align-items:center; justify-content:center; border-radius:12px;">Sin Imagen</div>
                <?php endif; ?>
                <h5 class="mt-3"><?php echo htmlspecialchars($vinilo['nombre']); ?></h5>
                <p class="mb-1">Autor: <?php echo htmlspecialchars($vinilo['autor']); ?></p>
                <p class="mb-1"><?php echo htmlspecialchars($vinilo['descripcion']); ?></p>
                <p class="mb-1"><strong><?php echo $vinilo['precio']; ?> €</strong></p>
                <p class="mb-1">Año: <?php echo $vinilo['anio']; ?></p>
                <span class="badge <?php echo $vinilo['visible'] ? 'bg-success' : 'bg-secondary'; ?> badge-visible">
                    <?php echo $vinilo['visible'] ? 'Visible' : 'Oculto'; ?>
                </span>
                <div class="mt-3">
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="accion" value="borrar">
                        <input type="hidden" name="id" value="<?php echo $vinilo['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm btn-action">Borrar</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="accion" value="toggle">
                        <input type="hidden" name="id" value="<?php echo $vinilo['id']; ?>">
                        <button type="submit" class="btn btn-warning btn-sm btn-action">
                            <?php echo $vinilo['visible'] ? 'Ocultar' : 'Mostrar'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

</div>
</body>
</html>