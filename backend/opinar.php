<?php
require "conexion.php";

$vinilo_id = (int)($_GET['vinilo_id'] ?? $_POST['vinilo_id'] ?? 0);
$errores = [];

$stmt = $conexion->prepare("SELECT nombre FROM vinilos WHERE id=?");
$stmt->bind_param("i",$vinilo_id);
$stmt->execute();
$vinilo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$vinilo){ die("Vinilo no válido"); }

if($_SERVER['REQUEST_METHOD']=="POST"){
    $nombre = trim($_POST['nombre']);
    $ciudad = trim($_POST['ciudad']);
    $comentario = trim($_POST['comentario']);

    if($nombre==''||$ciudad==''||$comentario==''){
        $errores[]="Todos los campos son obligatorios";
    }

    if(!$errores){
        $stmt = $conexion->prepare(
            "INSERT INTO opiniones (vinilo_id,nombre,ciudad,comentario)
             VALUES (?,?,?,?)"
        );
        $stmt->bind_param("isss",$vinilo_id,$nombre,$ciudad,$comentario);
        $stmt->execute();
        $stmt->close();

        header("Location: catalogo.php#opiniones");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Opinar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-white">
<div class="container py-5" style="max-width:600px">

<h3>Opinión sobre: <?= htmlspecialchars($vinilo['nombre']) ?></h3>

<?php if($errores): ?>
<div class="alert alert-danger">
<?= implode("<br>",$errores) ?>
</div>
<?php endif; ?>

<form method="POST">
<input type="hidden" name="vinilo_id" value="<?= $vinilo_id ?>">

<div class="mb-3">
<label>Nombre</label>
<input name="nombre" class="form-control" required>
</div>

<div class="mb-3">
<label>Ciudad</label>
<input name="ciudad" class="form-control" required>
</div>

<div class="mb-3">
<label>Comentario</label>
<textarea name="comentario" class="form-control" rows="4" required></textarea>
</div>

<button class="btn btn-info text-dark fw-bold">Enviar opinión</button>
<a href="catalogo.php" class="btn btn-outline-light ms-2">Cancelar</a>
</form>

</div>
</body>
</html>
