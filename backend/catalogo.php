<?php
session_start();
require "conexion.php";

/* ---------- CARRITO ---------- */
$conteo_carrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $conteo_carrito += $item['cantidad'];
    }
}

/* ---------- VINILOS ---------- */
$sql = "SELECT * FROM vinilos WHERE visible = 1 ORDER BY id DESC";
$resultado = $conexion->query($sql);

/* ---------- OPINIONES ---------- */
$sqlOpiniones = "
    SELECT o.nombre, o.ciudad, o.comentario, o.created_at,
           v.nombre AS vinilo_nombre
    FROM opiniones o
    INNER JOIN vinilos v ON v.id = o.vinilo_id
    ORDER BY o.created_at DESC, o.id DESC
";
$resOpiniones = $conexion->query($sqlOpiniones);
$opiniones = [];
if ($resOpiniones) {
    while ($row = $resOpiniones->fetch_assoc()) {
        $opiniones[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Catálogo – RetroGroove Records</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Inter&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
body {
    background:#050505;
    color:white;
    font-family:'Inter',sans-serif;
}
.neon-title {
    font-family:'Orbitron';
    font-weight:800;
    letter-spacing:6px;
    color:#00eaff;
}
.card-vinilo {
    background:rgba(255,255,255,0.05);
    border-radius:20px;
    padding:20px;
    height:100%;
}
.btn-opinar {
    border:1px solid #00eaff;
    color:#00eaff;
}
.btn-opinar:hover {
    background:#00eaff;
    color:black;
}
.precio {
    font-family:'Orbitron';
    font-size:1.5rem;
    color:#00eaff;
}
</style>
</head>

<body>

<div class="container pb-5">
    <div class="header-section text-center">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="https://loopers-ten.vercel.app/" class="text-secondary text-decoration-none small fw-bold" style="letter-spacing:2px">
                <i class="fas fa-long-arrow-alt-left me-2"></i> VOLVER
            </a>
            
            <a href="carrito.php" class="cart-icon-container">
                <i class="fas fa-shopping-cart fa-lg"></i>
                <?php if($conteo_carrito > 0): ?>
                    <span class="badge rounded-pill bg-info ms-2 text-dark"><?php echo $conteo_carrito; ?></span>
                <?php endif; ?>
            </a>
        </div>
        <h1 class="neon-title">Catálogo</h1>
    </div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="../frontend/index.html" class="text-secondary fw-bold text-decoration-none">← VOLVER</a>
    <a href="carrito.php" class="btn btn-outline-info position-relative">
        <i class="fas fa-shopping-cart"></i>
        <?php if($conteo_carrito>0): ?>
            <span class="badge bg-info text-dark position-absolute top-0 start-100 translate-middle">
                <?= $conteo_carrito ?>
            </span>
        <?php endif; ?>
    </a>
</div>

<h1 class="text-center neon-title mb-5">CATÁLOGO</h1>

<div class="row g-4">
<?php while($v = $resultado->fetch_assoc()): ?>
    <div class="col-md-4 col-xl-3">
        <div class="card-vinilo text-center d-flex flex-column">

            <?php if($v['foto']): ?>
                <img src="<?= $v['foto'] ?>" class="img-fluid rounded mb-3">
            <?php endif; ?>

            <h5><?= htmlspecialchars($v['nombre']) ?></h5>
            <p class="text-secondary mb-2"><?= htmlspecialchars($v['autor']) ?></p>
            <div class="precio mb-3"><?= number_format($v['precio'],2) ?> €</div>

            <form action="carrito.php" method="POST">
                <input type="hidden" name="id" value="<?= $v['id'] ?>">
                <input type="hidden" name="accion" value="agregar">
                <button class="btn btn-outline-light w-100 mb-2">Añadir al carrito</button>
            </form>

            <a href="opinar.php?vinilo_id=<?= $v['id'] ?>"
               class="btn btn-opinar w-100 mt-auto">
               <i class="fa-regular fa-comment"></i> Dejar opinión
            </a>
        </div>
    </div>
<?php endwhile; ?>
</div>

<hr class="my-5">

<!-- ===== CARRUSEL OPINIONES ===== -->
<section id="opiniones">
<h2 class="text-center neon-title mb-4">OPINIONES</h2>

<?php if(empty($opiniones)): ?>
    <p class="text-center text-secondary">Aún no hay opiniones.</p>
<?php else: ?>
<?php $chunks = array_chunk($opiniones, 3); ?>

<div id="opinionesCarousel" class="carousel slide">
<div class="carousel-inner">

<?php foreach($chunks as $i=>$grupo): ?>
<div class="carousel-item <?= $i===0?'active':'' ?>">
<div class="row g-4 justify-content-center">

<?php foreach($grupo as $op): ?>
<div class="col-md-4">
<div class="card-vinilo">
<strong><?= htmlspecialchars($op['nombre']) ?></strong>
<div class="text-secondary small"><?= htmlspecialchars($op['ciudad']) ?></div>

<p class="mt-3">“<?= nl2br(htmlspecialchars($op['comentario'])) ?>”</p>

<div class="mt-3 text-info small">
Vinilo: <?= htmlspecialchars($op['vinilo_nombre']) ?>
</div>
</div>
</div>
<?php endforeach; ?>

</div>
</div>
<?php endforeach; ?>

</div>

<button class="carousel-control-prev" type="button" data-bs-target="#opinionesCarousel" data-bs-slide="prev">
<span class="carousel-control-prev-icon"></span>
</button>
<button class="carousel-control-next" type="button" data-bs-target="#opinionesCarousel" data-bs-slide="next">
<span class="carousel-control-next-icon"></span>
</button>

</div>
<?php endif; ?>
</section>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
