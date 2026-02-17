<?php
session_start();
require "conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../frontend/index.html");
    exit();
}

$error_msg   = '';
$success_msg = '';

// ✅ RUTAS DEFINITIVAS confirmadas por debug:
// - Disco:  /app/imgs/
// - URL:    /imgs/nombre.jpg
define('IMGS_DIR',     '/app/imgs/');
define('IMGS_URL',     '/imgs/');
define('IMGS_DEFAULT', '/frontend/imgs/vinilo1.png');

function urlImagen($foto) {
    if (empty($foto)) return IMGS_DEFAULT;
    $nombre = basename(str_replace('\\', '/', $foto));
    if (empty($nombre)) return IMGS_DEFAULT;
    return IMGS_URL . $nombre;
}

// --- ACCIONES POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    if ($_POST['accion'] == 'añadir') {
        $autor       = $conexion->real_escape_string($_POST['autor']);
        $nombre      = $conexion->real_escape_string($_POST['nombre']);
        $descripcion = $conexion->real_escape_string($_POST['descripcion']);
        $precio      = floatval($_POST['precio']);
        $anio        = intval($_POST['anio']);

        if ($anio < 1901 || $anio > 2155) {
            $error_msg = "El año '$anio' no es válido. Debe estar entre 1901 y 2155.";
        } else {
            $foto = '';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $nombreArchivo = time() . '_' . basename($_FILES['foto']['name']);
                if (!is_dir(IMGS_DIR)) mkdir(IMGS_DIR, 0777, true);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], IMGS_DIR . $nombreArchivo)) {
                    $foto = $nombreArchivo;
                } else {
                    $error_msg = "No se pudo mover la imagen.";
                }
            }
            if (empty($error_msg)) {
                $foto_escaped = $conexion->real_escape_string($foto);
                $sql = "INSERT INTO vinilos (autor, nombre, descripcion, precio, anio, foto, visible) 
                        VALUES ('$autor', '$nombre', '$descripcion', $precio, $anio, '$foto_escaped', 1)";
                if (!$conexion->query($sql)) {
                    $error_msg = "Error BD: " . $conexion->error;
                } else {
                    $success_msg = "Vinilo '$nombre' añadido correctamente.";
                }
            }
        }
    }

    if ($_POST['accion'] == 'borrar' && isset($_POST['id'])) {
        $conexion->query("DELETE FROM vinilos WHERE id = " . intval($_POST['id']));
    }

    if ($_POST['accion'] == 'toggle' && isset($_POST['id'])) {
        $id  = intval($_POST['id']);
        $res = $conexion->query("SELECT visible FROM vinilos WHERE id = $id");
        $row = $res->fetch_assoc();
        $conexion->query("UPDATE vinilos SET visible = " . ($row['visible'] ? 0 : 1) . " WHERE id = $id");
    }

    if ($_POST['accion'] == 'borrar_opinion' && isset($_POST['id_opinion'])) {
        $conexion->query("DELETE FROM opiniones WHERE id = " . intval($_POST['id_opinion']));
        header("Location: panel.php?seccion=opiniones");
        exit();
    }

    if (empty($error_msg)) {
        header("Location: panel.php");
        exit();
    }
}

// --- CONSULTAS ---
$buscar = $_GET['buscar'] ?? '';
$sql_v  = "SELECT * FROM vinilos";
if ($buscar != '') {
    $b     = $conexion->real_escape_string($buscar);
    $sql_v .= " WHERE nombre LIKE '%$b%' OR autor LIKE '%$b%'";
}
$result_vinilos = $conexion->query($sql_v);

$f_ciudad      = $_GET['f_ciudad'] ?? '';
$f_vinilo      = $_GET['f_vinilo'] ?? '';
$sql_op        = "SELECT o.*, COALESCE(v.nombre, '[Vinilo eliminado]') AS vinilo_nombre FROM opiniones o 
                  LEFT JOIN vinilos v ON o.vinilo_id = v.id WHERE 1=1";
if ($f_ciudad != '') $sql_op .= " AND o.ciudad LIKE '%" . $conexion->real_escape_string($f_ciudad) . "%'";
if ($f_vinilo != '') $sql_op .= " AND v.nombre LIKE '%" . $conexion->real_escape_string($f_vinilo) . "%'";
$sql_op       .= " ORDER BY o.created_at DESC";
$res_opiniones = $conexion->query($sql_op);

$total_vinilos   = $conexion->query("SELECT COUNT(*) as c FROM vinilos")->fetch_assoc()['c'];
$total_visibles  = $conexion->query("SELECT COUNT(*) as c FROM vinilos WHERE visible=1")->fetch_assoc()['c'];
$total_opiniones = $conexion->query("SELECT COUNT(*) as c FROM opiniones")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel · Loopers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #0a0f1a;
            --surface:  #111827;
            --surface2: #1a2235;
            --border:   rgba(255,255,255,0.07);
            --gold:     #f5c518;
            --text:     #e2e8f0;
            --muted:    #64748b;
            --success:  #22c55e;
            --danger:   #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; }

        .sidebar {
            width: 220px; min-height: 100vh; background: var(--surface);
            border-right: 1px solid var(--border); display: flex; flex-direction: column;
            padding: 28px 14px; position: fixed; top: 0; left: 0; height: 100vh; z-index: 10;
        }
        .logo { font-size: 1.3rem; font-weight: 700; color: var(--gold); letter-spacing: 3px; padding: 0 8px; margin-bottom: 32px; }
        .nav-btn {
            display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px;
            color: var(--muted); background: none; border: none; width: 100%; text-align: left;
            font-size: .85rem; font-weight: 500; cursor: pointer; transition: all .2s; margin-bottom: 4px; font-family: inherit;
        }
        .nav-btn:hover, .nav-btn.active { background: rgba(245,197,24,.09); color: var(--gold); }
        .nav-btn svg { width: 17px; height: 17px; flex-shrink: 0; }
        .sidebar-footer { margin-top: auto; }
        .admin-pill { background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; font-size: .78rem; color: var(--muted); }
        .admin-pill strong { display: block; color: var(--text); font-size: .85rem; margin-bottom: 2px; }
        .logout-btn { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; color: var(--danger); text-decoration: none; font-size: .85rem; font-weight: 500; transition: background .2s; }
        .logout-btn:hover { background: rgba(239,68,68,.1); }
        .logout-btn svg { width: 17px; height: 17px; }

        .main { margin-left: 220px; padding: 32px; flex: 1; max-width: calc(100% - 220px); }

        .stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 28px; }
        .stat { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px 22px; }
        .stat-label { font-size: .72rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); margin-bottom: 8px; }
        .stat-val { font-size: 1.9rem; font-weight: 700; color: var(--gold); line-height: 1; }
        .stat-sub { font-size: .72rem; color: var(--muted); margin-top: 5px; }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 24px; margin-bottom: 24px; }
        .card-head { font-size: .95rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .card-head svg { color: var(--gold); }

        .form-label { font-size: .78rem; color: var(--muted); font-weight: 500; margin-bottom: 5px; display: block; }
        .form-control {
            background: var(--surface2) !important; border: 1px solid var(--border) !important;
            color: var(--text) !important; border-radius: 8px !important;
            font-size: .85rem; padding: 9px 12px; width: 100%; font-family: inherit;
        }
        .form-control:focus { border-color: var(--gold) !important; box-shadow: 0 0 0 3px rgba(245,197,24,.1) !important; outline: none !important; }
        .form-control::placeholder { color: var(--muted) !important; }

        .btn-gold { background: var(--gold); color: #0a0f1a; border: none; border-radius: 8px; padding: 9px 20px; font-weight: 600; font-size: .85rem; cursor: pointer; font-family: inherit; transition: background .2s; }
        .btn-gold:hover { background: #e6b800; }
        .btn-ghost { background: transparent; border: 1px solid var(--border); color: var(--muted); border-radius: 8px; padding: 9px 16px; font-size: .85rem; cursor: pointer; font-family: inherit; text-decoration: none; display: inline-block; transition: all .2s; }
        .btn-ghost:hover { border-color: var(--gold); color: var(--gold); }
        .btn-ok  { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.3); color: var(--success); border-radius: 6px; padding: 5px 12px; font-size: .78rem; cursor: pointer; font-family: inherit; width: 100%; transition: background .2s; }
        .btn-ok:hover { background: rgba(34,197,94,.22); }
        .btn-dim { background: rgba(100,116,139,.1); border: 1px solid rgba(100,116,139,.25); color: var(--muted); border-radius: 6px; padding: 5px 12px; font-size: .78rem; cursor: pointer; font-family: inherit; width: 100%; }
        .btn-del { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: var(--danger); border-radius: 6px; padding: 5px 12px; font-size: .78rem; cursor: pointer; font-family: inherit; width: 100%; transition: background .2s; }
        .btn-del:hover { background: rgba(239,68,68,.22); }
        .btn-del-sm { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: var(--danger); border-radius: 6px; padding: 5px 14px; font-size: .78rem; cursor: pointer; font-family: inherit; transition: background .2s; }
        .btn-del-sm:hover { background: rgba(239,68,68,.22); }

        .vinyl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(185px,1fr)); gap: 14px; }
        .vcard { background: var(--surface2); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: border-color .2s, transform .2s; }
        .vcard:hover { border-color: rgba(245,197,24,.35); transform: translateY(-2px); }
        .vimg { position: relative; aspect-ratio: 1; background: #050a12; overflow: hidden; }
        .vimg img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .no-img { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: .72rem; }
        .vdot { position: absolute; top: 8px; right: 8px; width: 9px; height: 9px; border-radius: 50%; border: 2px solid var(--surface2); }
        .dot-on { background: var(--success); }
        .dot-off { background: var(--danger); }
        .vbody { padding: 12px; }
        .vtitle { font-size: .85rem; font-weight: 600; color: var(--gold); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px; }
        .vauthor { font-size: .75rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 8px; }
        .vprice { font-size: .95rem; font-weight: 700; margin-bottom: 10px; }
        .vactions { display: flex; gap: 6px; }
        .vactions form { flex: 1; }

        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; }
        .search-bar .form-control { max-width: 280px; }

        .tbl { width: 100%; border-collapse: collapse; font-size: .82rem; }
        .tbl thead th { color: var(--muted); font-weight: 500; font-size: .72rem; text-transform: uppercase; letter-spacing: .5px; padding: 10px 14px; border-bottom: 1px solid var(--border); text-align: left; }
        .tbl tbody td { padding: 12px 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .tbl tbody tr:last-child td { border-bottom: none; }
        .tbl tbody tr:hover td { background: rgba(255,255,255,.02); }

        .alert { border-radius: 10px; padding: 12px 16px; font-size: .85rem; margin-bottom: 20px; }
        .alert-err { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; }
        .alert-ok  { background: rgba(34,197,94,.1);  border: 1px solid rgba(34,197,94,.3);  color: #86efac; }

        .section { display: none; }
        .section.active { display: block; }

        .filter-row { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; flex-wrap: wrap; }
        .filter-row .form-control { max-width: 220px; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo">LOOPERS</div>
    <button class="nav-btn active" onclick="showSec('vinilos',this)">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
        Vinilos
    </button>
    <button class="nav-btn" onclick="showSec('opiniones',this)">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Opiniones
    </button>
    <div class="sidebar-footer">
        <div class="admin-pill">
            <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
            Administrador
        </div>
        <a href="logout.php" class="logout-btn">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            Cerrar sesión
        </a>
    </div>
</aside>

<main class="main">

    <?php if ($error_msg): ?>
        <div class="alert alert-err">⚠️ <?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>
    <?php if ($success_msg): ?>
        <div class="alert alert-ok">✅ <?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat">
            <div class="stat-label">Total vinilos</div>
            <div class="stat-val"><?= $total_vinilos ?></div>
            <div class="stat-sub">en catálogo</div>
        </div>
        <div class="stat">
            <div class="stat-label">Visibles</div>
            <div class="stat-val"><?= $total_visibles ?></div>
            <div class="stat-sub">publicados</div>
        </div>
        <div class="stat">
            <div class="stat-label">Opiniones</div>
            <div class="stat-val"><?= $total_opiniones ?></div>
            <div class="stat-sub">de clientes</div>
        </div>
    </div>

    <!-- VINILOS -->
    <div id="sec-vinilos" class="section active">
        <div class="card">
            <div class="card-head">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Añadir nuevo vinilo
            </div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="añadir">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Autor</label>
                        <input type="text" name="autor" required class="form-control" placeholder="Artista">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="nombre" required class="form-control" placeholder="Álbum">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio (€)</label>
                        <input type="number" step="0.01" name="precio" required class="form-control" placeholder="12.00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Año</label>
                        <input type="number" name="anio" required min="1901" max="2155" class="form-control" placeholder="2000">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Portada</label>
                        <input type="file" name="foto" accept="image/*" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" class="form-control" placeholder="Descripción...">
                    </div>
                    <div class="col-12" style="text-align:right;">
                        <button type="submit" class="btn-gold">Guardar vinilo</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-head">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                Inventario
            </div>
            <form method="get" class="search-bar">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar..." value="<?= htmlspecialchars($buscar) ?>">
                <button type="submit" class="btn-gold">Buscar</button>
                <?php if ($buscar): ?><a href="panel.php" class="btn-ghost">Limpiar</a><?php endif; ?>
            </form>
            <div class="vinyl-grid">
                <?php while ($v = $result_vinilos->fetch_assoc()): ?>
                    <div class="vcard">
                        <div class="vimg">
                            <img src="<?= htmlspecialchars(urlImagen($v['foto'])) ?>"
                                 alt="<?= htmlspecialchars($v['nombre']) ?>"
                                 onerror="this.style.display='none'; this.parentElement.querySelector('.no-img').style.display='flex';">
                            <div class="no-img" style="display:none;">Sin imagen</div>
                            <div class="vdot <?= $v['visible'] ? 'dot-on' : 'dot-off' ?>"></div>
                        </div>
                        <div class="vbody">
                            <div class="vtitle" title="<?= htmlspecialchars($v['nombre']) ?>"><?= htmlspecialchars($v['nombre']) ?></div>
                            <div class="vauthor"><?= htmlspecialchars($v['autor']) ?></div>
                            <div class="vprice"><?= number_format($v['precio'], 2) ?> €</div>
                            <div class="vactions">
                                <form method="post">
                                    <input type="hidden" name="accion" value="toggle">
                                    <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                    <button class="<?= $v['visible'] ? 'btn-ok' : 'btn-dim' ?>">
                                        <?= $v['visible'] ? 'Visible' : 'Oculto' ?>
                                    </button>
                                </form>
                                <form method="post" onsubmit="return confirm('¿Borrar este vinilo?');">
                                    <input type="hidden" name="accion" value="borrar">
                                    <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                    <button class="btn-del">Borrar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- OPINIONES -->
    <div id="sec-opiniones" class="section">
        <div class="card">
            <div class="card-head">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Opiniones de clientes
            </div>
            <form method="get" class="filter-row">
                <input type="hidden" name="seccion" value="opiniones">
                <input type="text" name="f_vinilo" class="form-control" placeholder="Vinilo..." value="<?= htmlspecialchars($f_vinilo) ?>">
                <input type="text" name="f_ciudad" class="form-control" placeholder="Ciudad..." value="<?= htmlspecialchars($f_ciudad) ?>">
                <button type="submit" class="btn-gold">Filtrar</button>
                <a href="panel.php?seccion=opiniones" class="btn-ghost">Limpiar</a>
            </form>
            <div style="overflow-x:auto;">
                <table class="tbl">
                    <thead>
                        <tr><th>Vinilo</th><th>Cliente</th><th>Ciudad</th><th>Comentario</th><th>Acción</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($op = $res_opiniones->fetch_assoc()): ?>
                            <tr>
                                <td style="color:var(--gold);font-weight:500;"><?= htmlspecialchars($op['vinilo_nombre']) ?></td>
                                <td><?= htmlspecialchars($op['nombre']) ?></td>
                                <td style="color:var(--muted);"><?= htmlspecialchars($op['ciudad']) ?></td>
                                <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($op['comentario']) ?>">
                                    <?= htmlspecialchars($op['comentario']) ?>
                                </td>
                                <td>
                                    <form method="post" onsubmit="return confirm('¿Eliminar esta opinión?');">
                                        <input type="hidden" name="accion" value="borrar_opinion">
                                        <input type="hidden" name="id_opinion" value="<?= $op['id'] ?>">
                                        <button class="btn-del-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<script>
function showSec(name, btn) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('sec-' + name).classList.add('active');
    btn.classList.add('active');
}
const p = new URLSearchParams(window.location.search);
if (p.get('seccion') === 'opiniones' || p.get('f_ciudad') || p.get('f_vinilo')) {
    document.querySelectorAll('.nav-btn')[1].click();
}
</script>
</body>
</html>