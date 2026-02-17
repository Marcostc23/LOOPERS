<?php
session_start();
require "conexion.php";

// Verificación de sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../frontend/index.html");
    exit();
}

// --- SECCIÓN DE ACCIONES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    // 1. Añadir un nuevo vinilo
    if ($_POST['accion'] == 'añadir') {
        $autor        = $conexion->real_escape_string($_POST['autor']);
        $nombre       = $conexion->real_escape_string($_POST['nombre']);
        $descripcion  = $conexion->real_escape_string($_POST['descripcion']);
        $precio       = floatval($_POST['precio']);
        $anio         = intval($_POST['anio']);

        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $nombreArchivo     = time() . '_' . basename($_FILES['foto']['name']);
            $directorioDestino = __DIR__ . '/../frontend/imgs/';

            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0777, true);
            }

            $rutaSubida = $directorioDestino . $nombreArchivo;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaSubida)) {
                // ✅ Guardamos SOLO el nombre del archivo en la BD
                $foto = $nombreArchivo;
            }
        }

        $foto_escaped = $conexion->real_escape_string($foto);
        $sql = "INSERT INTO vinilos (autor, nombre, descripcion, precio, anio, foto, visible) 
                VALUES ('$autor', '$nombre', '$descripcion', $precio, $anio, '$foto_escaped', 1)";
        $conexion->query($sql);
    }

    // 2. Borrar un vinilo
    if ($_POST['accion'] == 'borrar' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $conexion->query("DELETE FROM vinilos WHERE id = $id");
    }

    // 3. Cambiar visibilidad (Toggle)
    if ($_POST['accion'] == 'toggle' && isset($_POST['id'])) {
        $id        = intval($_POST['id']);
        $resultado = $conexion->query("SELECT visible FROM vinilos WHERE id = $id");
        $row       = $resultado->fetch_assoc();
        $nuevoEstado = $row['visible'] ? 0 : 1;
        $conexion->query("UPDATE vinilos SET visible = $nuevoEstado WHERE id = $id");
    }

    // 4. Borrar una opinión de cliente
    if ($_POST['accion'] == 'borrar_opinion' && isset($_POST['id_opinion'])) {
        $id_op = intval($_POST['id_opinion']);
        $conexion->query("DELETE FROM opiniones WHERE id = $id_op");
        header("Location: panel.php#seccion-opiniones");
        exit();
    }

    header("Location: panel.php");
    exit();
}

// --- SECCIÓN DE CONSULTAS (GET) ---

// Consulta de Vinilos con buscador
$buscar     = $_GET['buscar'] ?? '';
$sql_vinilos = "SELECT * FROM vinilos";
if ($buscar != '') {
    $b = $conexion->real_escape_string($buscar);
    $sql_vinilos .= " WHERE nombre LIKE '%$b%' OR autor LIKE '%$b%'";
}
$result_vinilos = $conexion->query($sql_vinilos);

// Consulta de Opiniones con filtros
$f_ciudad = $_GET['f_ciudad'] ?? '';
$f_vinilo = $_GET['f_vinilo'] ?? '';

$sql_opiniones = "SELECT o.*, v.nombre AS vinilo_nombre 
                  FROM opiniones o 
                  JOIN vinilos v ON o.vinilo_id = v.id 
                  WHERE 1=1";

if ($f_ciudad != '') {
    $c = $conexion->real_escape_string($f_ciudad);
    $sql_opiniones .= " AND o.ciudad LIKE '%$c%'";
}
if ($f_vinilo != '') {
    $v = $conexion->real_escape_string($f_vinilo);
    $sql_opiniones .= " AND v.nombre LIKE '%$v%'";
}
$sql_opiniones .= " ORDER BY o.created_at DESC";
$res_opiniones  = $conexion->query($sql_opiniones);

// ✅ Función auxiliar para construir la URL de la imagen en el panel
function urlImagenPanel($foto) {
    if (empty($foto)) {
        return '../frontend/imgs/vinilo1.png';
    }

    // Si ya es una ruta relativa larga (registros antiguos), extraemos solo el nombre
    $nombreArchivo = basename(str_replace('\\', '/', $foto));

    $rutaFisica = __DIR__ . '/../frontend/imgs/' . $nombreArchivo;
    if (file_exists($rutaFisica)) {
        return '../frontend/imgs/' . rawurlencode($nombreArchivo);
    }

    return '../frontend/imgs/vinilo1.png';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Loopers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); color: #e0e0e0; min-height: 100vh; }
        h1, h2 { color: #ffcc00; text-shadow: 1px 1px 4px rgba(0,0,0,0.7); }
        .card-custom { background: #1e2a38; border-radius: 15px; padding: 20px; border: 1px solid rgba(255,204,0,0.2); }
        .card-vinilo { background: #1e2a38; border-radius: 15px; padding: 15px; text-align: center; height: 100%; border: 1px solid rgba(255,255,255,0.1); }
        .card-vinilo img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; background: #000; margin-bottom: 10px; }
        .btn-primary { background: #ffcc00; color: #1e2a38; border: none; font-weight: bold; }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Panel de Control</h1>
        <div class="text-end">
            <span class="me-3">Admin: <strong><?= htmlspecialchars($_SESSION['usuario']); ?></strong></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
        </div>
    </div>

    <div class="card-custom mb-5">
        <h2 class="h4 mb-4">Añadir Nuevo Vinilo</h2>
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="accion" value="añadir">
            <div class="col-md-3"><label class="form-label">Autor</label><input type="text" name="autor" required class="form-control bg-dark text-white"></div>
            <div class="col-md-3"><label class="form-label">Título</label><input type="text" name="nombre" required class="form-control bg-dark text-white"></div>
            <div class="col-md-3"><label class="form-label">Precio (€)</label><input type="number" step="0.01" name="precio" required class="form-control bg-dark text-white"></div>
            <div class="col-md-3"><label class="form-label">Año</label><input type="number" name="anio" required class="form-control bg-dark text-white"></div>
            <div class="col-md-8"><label class="form-label">Descripción</label><input type="text" name="descripcion" class="form-control bg-dark text-white"></div>
            <div class="col-md-4"><label class="form-label">Portada</label><input type="file" name="foto" accept="image/*" class="form-control bg-dark text-white"></div>
            <div class="col-12 text-end"><button type="submit" class="btn btn-primary px-4">Guardar Vinilo</button></div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4">Inventario</h2>
        <form method="get" class="d-flex gap-2">
            <input type="text" name="buscar" class="form-control bg-dark text-white" placeholder="Buscar..." value="<?= htmlspecialchars($buscar) ?>">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </form>
    </div>

    <div class="row mb-5">
        <?php while ($vinilo = $result_vinilos->fetch_assoc()): ?>
            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="card-vinilo">
                    <!-- ✅ Usamos la función auxiliar para la URL correcta -->
                    <img src="<?= htmlspecialchars(urlImagenPanel($vinilo['foto'])) ?>" alt="Portada">

                    <h5 class="text-warning mb-1"><?= htmlspecialchars($vinilo['nombre']); ?></h5>
                    <p class="small text-secondary mb-2"><?= htmlspecialchars($vinilo['autor']); ?></p>
                    <p class="fw-bold fs-5 mb-2"><?= number_format($vinilo['precio'], 2); ?> €</p>

                    <div class="mb-3">
                        <span class="badge <?= $vinilo['visible'] ? 'bg-success' : 'bg-danger'; ?>">
                            <?= $vinilo['visible'] ? 'Visible' : 'Oculto'; ?>
                        </span>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <form method="post">
                            <input type="hidden" name="accion" value="toggle">
                            <input type="hidden" name="id" value="<?= $vinilo['id'] ?>">
                            <button class="btn btn-sm <?= $vinilo['visible'] ? 'btn-success' : 'btn-secondary' ?>">
                                <?= $vinilo['visible'] ? 'Visible' : 'Oculto' ?>
                            </button>
                        </form>
                        <form method="post" onsubmit="return confirm('¿Borrar?');">
                            <input type="hidden" name="accion" value="borrar">
                            <input type="hidden" name="id" value="<?= $vinilo['id'] ?>">
                            <button class="btn btn-danger btn-sm">Borrar</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <hr class="border-warning my-5" id="seccion-opiniones">
    <div class="card-custom mb-5">
        <h2 class="h4 mb-4" style="color: #17a2b8;">Opiniones de Clientes</h2>
        <form method="get" class="row g-2 mb-4">
            <div class="col-md-4"><input type="text" name="f_vinilo" class="form-control bg-dark text-white" placeholder="Vinilo..." value="<?= htmlspecialchars($f_vinilo) ?>"></div>
            <div class="col-md-4"><input type="text" name="f_ciudad" class="form-control bg-dark text-white" placeholder="Ciudad..." value="<?= htmlspecialchars($f_ciudad) ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-info btn-sm w-100 text-white">Filtrar</button></div>
            <div class="col-md-2"><a href="panel.php#seccion-opiniones" class="btn btn-outline-secondary btn-sm w-100">Limpiar</a></div>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover small">
                <thead>
                    <tr><th>Vinilo</th><th>Cliente</th><th>Ciudad</th><th>Comentario</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php while ($op = $res_opiniones->fetch_assoc()): ?>
                        <tr>
                            <td class="text-warning"><?= htmlspecialchars($op['vinilo_nombre']) ?></td>
                            <td><?= htmlspecialchars($op['nombre']) ?></td>
                            <td><?= htmlspecialchars($op['ciudad']) ?></td>
                            <td><?= htmlspecialchars($op['comentario']) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="accion" value="borrar_opinion">
                                    <input type="hidden" name="id_opinion" value="<?= $op['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>