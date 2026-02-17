<?php
session_start();
require "conexion.php";

function rutaImagenVinilo($rutaGuardada) {
    $fallback = "imgs/vinilo1.png";
    if (empty($rutaGuardada)) {
        return $fallback;
    }

    $rutaNormalizada = str_replace("\\", "/", trim($rutaGuardada));
    $nombreArchivo = basename($rutaNormalizada);
    $rutaLocal = __DIR__ . "/imgs/" . $nombreArchivo;

    if ($nombreArchivo !== "" && file_exists($rutaLocal)) {
        return "imgs/" . rawurlencode($nombreArchivo);
    }

    return $fallback;
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../frontend/index.html");
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

        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $nombreArchivo = time() . '_' . basename($_FILES['foto']['name']);
            
            // CORRECCIÓN: Añadida la barra '/' después de imgs para que la ruta sea válida
            $rutaDestino = 'imgs/' . $nombreArchivo; 
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                // CORRECCIÓN: Guardamos la ruta con la barra '/' para que el HTML la encuentre
                $foto = 'imgs/' . $nombreArchivo;
            }
        }

        // Usamos sentencias preparadas o escapamos datos para evitar errores de SQL
        $autor = $conexion->real_escape_string($autor);
        $nombre = $conexion->real_escape_string($nombre);
        $descripcion = $conexion->real_escape_string($descripcion);

        $sql = "INSERT INTO vinilos (autor, nombre, descripcion, precio, anio, foto) 
                VALUES ('$autor', '$nombre', '$descripcion', '$precio', '$anio', '$foto')";
        $conexion->query($sql);
    }

    if ($_POST['accion'] == 'borrar' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $conexion->query("DELETE FROM vinilos WHERE id = $id");
    }

    if ($_POST['accion'] == 'toggle' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $resultado = $conexion->query("SELECT visible FROM vinilos WHERE id = $id");
        $row = $resultado->fetch_assoc();
        $nuevoEstado = $row['visible'] ? 0 : 1;
        $conexion->query("UPDATE vinilos SET visible = $nuevoEstado WHERE id = $id");
    }

    header("Location: panel.php");
    exit();
}

// --- BÚSQUEDA ---
$buscar = '';
if (isset($_GET['buscar'])) {
    $buscar = $conexion->real_escape_string($_GET['buscar']);
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
    <title>Panel de Vinilos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
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
            height: 100%;
        }
        .card-vinilo:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(255, 204, 0, 0.3);
        }
        .card-vinilo img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }
        .btn-primary { background: #ffcc00; border: none; color: #1e2a38; font-weight: bold; }
        .btn-primary:hover { background: #e6b800; color: #1e2a38; }
        .form-control { border-radius: 8px; }
        .badge-visible { border-radius: 12px; padding: 5px 10px; }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Panel de Control</h1>
        <div class="text-end">
            <span class="me-3">Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
            <a href="logout.php" class="btn btn-outline-danger">Cerrar Sesión</a>
        </div>
    </div>

    <hr class="border-warning mb-5">

    <div class="card bg-dark text-white p-4 mb-5 border-warning">
        <h2 class="h4 mb-4">Añadir Nuevo Vinilo</h2>
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="accion" value="añadir">
            <div class="col-md-4">
                <label class="form-label">Autor</label>
                <input type="text" name="autor" required class="form-control bg-secondary text-white border-0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Título del Álbum</label>
                <input type="text" name="nombre" required class="form-control bg-secondary text-white border-0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Imagen (Portada)</label>
                <input type="file" name="foto" accept="image/*" class="form-control bg-secondary text-white border-0">
            </div>
            <div class="col-md-6">
                <label class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control bg-secondary text-white border-0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Precio (€)</label>
                <input type="number" step="0.01" name="precio" required class="form-control bg-secondary text-white border-0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Año</label>
                <input type="number" name="anio" required class="form-control bg-secondary text-white border-0">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </div>
        </form>
    </div>

    <form method="get" class="mb-5">
        <div class="input-group">
            <input type="text" name="buscar" class="form-control form-control-lg" placeholder="Buscar por nombre o artista..." value="<?php echo htmlspecialchars($buscar); ?>">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>

    <div class="row">
        <?php if ($result_vinilos->num_rows > 0): ?>
            <?php while ($vinilo = $result_vinilos->fetch_assoc()): ?>
            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="card-vinilo">
                    <?php $rutaImagen = rutaImagenVinilo($vinilo['foto']); ?>
                    <img src="<?php echo htmlspecialchars($rutaImagen); ?>" alt="Portada">
                    
                    <h5 class="text-warning mb-1"><?php echo htmlspecialchars($vinilo['nombre']); ?></h5>
                    <p class="small text-secondary mb-2"><?php echo htmlspecialchars($vinilo['autor']); ?></p>
                    <p class="fw-bold fs-5 mb-2"><?php echo number_format($vinilo['precio'], 2); ?> €</p>
                    
                    <div class="mb-3">
                        <span class="badge badge-visible <?php echo $vinilo['visible'] ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $vinilo['visible'] ? 'Visible' : 'Oculto'; ?>
                        </span>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <form method="post">
                            <input type="hidden" name="accion" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $vinilo['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-light">
                                <?php echo $vinilo['visible'] ? 'Ocultar' : 'Mostrar'; ?>
                            </button>
                        </form>
                        <form method="post" onsubmit="return confirm('¿Seguro que quieres borrar este vinilo?');">
                            <input type="hidden" name="accion" value="borrar">
                            <input type="hidden" name="id" value="<?php echo $vinilo['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Borrar</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-secondary">No se encontraron vinilos en la colección.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
