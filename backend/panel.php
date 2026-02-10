<?php
session_start();
require "conexion.php";

// Verificación de sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../frontend/index.html");
    exit();
}

// --- ACCIONES POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    // Acciones de Vinilos
    if ($_POST['accion'] == 'añadir') {
        $autor = $conexion->real_escape_string($_POST['autor']);
        $nombre = $conexion->real_escape_string($_POST['nombre']);
        $descripcion = $conexion->real_escape_string($_POST['descripcion']);
        $precio = $_POST['precio'];
        $anio = $_POST['anio'];

        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $nombreArchivo = time() . '_' . basename($_FILES['foto']['name']);
            $rutaDestino = 'imgs/' . $nombreArchivo; 
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                $foto = 'imgs/' . $nombreArchivo;
            }
        }

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

    // NUEVA ACCIÓN: Borrar Opinión [cite: 36]
    if ($_POST['accion'] == 'borrar_opinion' && isset($_POST['id_opinion'])) {
        $id_op = intval($_POST['id_opinion']);
        $conexion->query("DELETE FROM opiniones WHERE id = $id_op");
        header("Location: panel.php#seccion-opiniones");
        exit();
    }

    header("Location: panel.php");
    exit();
}

// --- CONSULTA DE VINILOS (Búsqueda) ---
$buscar = '';
if (isset($_GET['buscar'])) {
    $buscar = $conexion->real_escape_string($_GET['buscar']);
    $sql_vinilos = "SELECT * FROM vinilos WHERE nombre LIKE '%$buscar%' OR autor LIKE '%$buscar%'";
} else {
    $sql_vinilos = "SELECT * FROM vinilos";
}
$result_vinilos = $conexion->query($sql_vinilos);

// --- CONSULTA DE OPINIONES (Filtros) [cite: 34, 35] ---
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
$res_opiniones = $conexion->query($sql_opiniones);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Vinilos y Opiniones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        h1, h2 { color: #ffcc00; text-shadow: 1px 1px 4px rgba(0,0,0,0.7); }
        .card-custom {
            background: #1e2a38;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,204,0,0.2);
        }
        .card-vinilo {
            background: #1e2a38;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s;
            height: 100%;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .card-vinilo:hover { transform: translateY(-5px); border-color: #ffcc00; }
        .card-vinilo img {
            width: 100%; height: 180px; object-fit: cover;
            border-radius: 10px; margin-bottom: 10px;
        }
        .btn-primary { background: #ffcc00; border: none; color: #1e2a38; fw-bold; }
        .btn-info { background: #17a2b8; border: none; color: white; }
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
            <div class="col-md-3"><label class="form-label small">Autor</label><input type="text" name="autor" required class="form-control bg-dark text-white border-secondary"></div>
            <div class="col-md-3"><label class="form-label small">Título</label><input type="text" name="nombre" required class="form-control bg-dark text-white border-secondary"></div>
            <div class="col-md-3"><label class="form-label small">Precio (€)</label><input type="number" step="0.01" name="precio" required class="form-control bg-dark text-white border-secondary"></div>
            <div class="col-md-3"><label class="form-label small">Año</label><input type="number" name="anio" required class="form-control bg-dark text-white border-secondary"></div>
            <div class="col-md-9"><label class="form-label small">Descripción</label><input type="text" name="descripcion" class="form-control bg-dark text-white border-secondary"></div>
            <div class="col-md-3"><label class="form-label small">Portada</label><input type="file" name="foto" accept="image/*" class="form-control bg-dark text-white border-secondary"></div>
            <div class="col-12 text-end"><button type="submit" class="btn btn-primary px-4">Guardar Vinilo</button></div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4">Inventario de Vinilos</h2>
        <form method="get" class="d-flex gap-2">
            <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar vinilo..." value="<?= htmlspecialchars($buscar) ?>">
            <button class="btn btn-primary btn-sm" type="submit">Buscar</button>
        </form>
    </div>

    <div class="row mb-5">
        <?php while ($vinilo = $result_vinilos->fetch_assoc()): ?>
            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="card-vinilo">
                    <img src="<?= $vinilo['foto'] ? $vinilo['foto'] : 'imgs/default.png'; ?>">
                    <h6 class="text-warning mb-1"><?= htmlspecialchars($vinilo['nombre']); ?></h6>
                    <p class="small text-secondary mb-2"><?= htmlspecialchars($vinilo['autor']); ?></p>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <form method="post">
                            <input type="hidden" name="accion" value="toggle">
                            <input type="hidden" name="id" value="<?= $vinilo['id']; ?>">
                            <button type="submit" class="btn btn-xs <?= $vinilo['visible'] ? 'btn-success' : 'btn-secondary'; ?> btn-sm">
                                <?= $vinilo['visible'] ? 'Visible' : 'Oculto'; ?>
                            </button>
                        </form>
                        <form method="post" onsubmit="return confirm('¿Borrar vinilo?');">
                            <input type="hidden" name="accion" value="borrar">
                            <input type="hidden" name="id" value="<?= $vinilo['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <hr class="border-warning my-5" id="seccion-opiniones">
    <div class="card-custom mb-5" style="border-color: #17a2b8;">
        <h2 class="h4 mb-4 text-info">Gestión de Opiniones de Clientes</h2>

        <form method="get" class="row g-2 mb-4">
            <div class="col-md-4">
                <input type="text" name="f_vinilo" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Filtrar por Vinilo..." value="<?= htmlspecialchars($f_vinilo) ?>">
            </div>
            <div class="col-md-4">
                <input type="text" name="f_ciudad" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Filtrar por Ciudad..." value="<?= htmlspecialchars($f_ciudad) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-info btn-sm w-100 fw-bold">Filtrar Opiniones</button>
            </div>
            <div class="col-md-2">
                <a href="panel.php#seccion-opiniones" class="btn btn-outline-secondary btn-sm w-100">Limpiar</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle small">
                <thead class="table-secondary text-dark">
                    <tr>
                        <th>Vinilo</th>
                        <th>Cliente</th>
                        <th>Ciudad</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_opiniones->num_rows > 0): ?>
                        <?php while ($op = $res_opiniones->fetch_assoc()): ?>
                            <tr>
                                <td class="text-warning fw-bold"><?= htmlspecialchars($op['vinilo_nombre']) ?></td>
                                <td><?= htmlspecialchars($op['nombre']) ?></td>
                                <td><?= htmlspecialchars($op['ciudad']) ?></td>
                                <td><?= htmlspecialchars($op['comentario']) ?></td>
                                <td class="text-secondary"><?= $op['created_at'] ?></td>
                                <td>
                                    <form method="post" onsubmit="return confirm('¿Eliminar esta opinión permanentemente?');">
                                        <input type="hidden" name="accion" value="borrar_opinion">
                                        <input type="hidden" name="id_opinion" value="<?= $op['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-secondary">No hay opiniones registradas con esos filtros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>