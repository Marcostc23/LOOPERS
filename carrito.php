<?php
session_start();
require "conexion.php";
 
// LÓGICA DEL CARRITO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $id = $_POST['id'];
 
    if ($_POST['accion'] === 'agregar') {
        // Consultamos la DB para tener los datos reales
        $res = $conexion->query("SELECT * FROM vinilos WHERE id = $id");
        $v = $res->fetch_assoc();
 
        if ($v) {
            // Si ya existe en el carrito, sumamos cantidad
            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id]['cantidad']++;
            } else {
                // Si es nuevo, lo creamos
                $_SESSION['carrito'][$id] = [
                    'nombre' => $v['nombre'],
                    'precio' => $v['precio'],
                    'foto' => $v['foto'],
                    'cantidad' => 1
                ];
            }
        }
    }
 
    if ($_POST['accion'] === 'eliminar') {
        unset($_SESSION['carrito'][$id]);
    }
 
    header("Location: carrito.php");
    exit();
}
 
$total_final = 0;
?>
 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito – RetroGroove Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Inter:wght@400;600&display=swap%22 rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #080808; color: white; font-family: 'Inter', sans-serif; }
        .neon-title { font-family: 'Orbitron'; color: #00eaff; text-shadow: 0 0 10px #00eaff; }
       
        .cart-card { background: rgba(255,255,255,0.02); border: 1px solid #222; border-radius: 15px; padding: 25px; }
        .product-img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; border: 1px solid #00eaff; }
       
        .summary-box { background: #111; border-radius: 15px; padding: 25px; border: 1px solid #333; position: sticky; top: 20px; }
       
        .btn-checkout { background: #00eaff; color: black; font-weight: bold; border-radius: 10px; padding: 15px; transition: 0.3s; border: none; }
        .btn-checkout:hover { background: white; box-shadow: 0 0 25px #00eaff; transform: scale(1.02); }
       
        .table { --bs-table-bg: transparent; color: white; }
    </style>
</head>
<body class="py-5">
 
<div class="container">
    <h1 class="neon-title mb-5 text-center">TU CESTA DE VINILOS</h1>
 
    <div class="row g-5">
        <div class="col-lg-8">
            <div class="cart-card">
                <?php if (empty($_SESSION['carrito'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-compact-disc fa-4x mb-3 text-secondary"></i>
                        <p class="h5">Tu carrito está vacío</p>
                        <a href="catalogo.php" class="btn btn-outline-info mt-3">Explorar Catálogo</a>
                    </div>
                <?php else: ?>
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-secondary small">
                                <th>DISCO</th>
                                <th>PRECIO</th>
                                <th>CANT.</th>
                                <th>TOTAL</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['carrito'] as $id => $item):
                                $subtotal = $item['precio'] * $item['cantidad'];
                                $total_final += $subtotal;
                            ?>
                            <tr style="border-bottom: 1px solid #1a1a1a;">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $item['foto']; ?>" class="product-img me-3">
                                        <span class="fw-bold"><?php echo htmlspecialchars($item['nombre']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo $item['precio']; ?>€</td>
                                <td>x<?php echo $item['cantidad']; ?></td>
                                <td class="text-info"><?php echo number_format($subtotal, 2); ?>€</td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
 
        <div class="col-lg-4">
            <div class="summary-box">
                <h4 class="mb-4">Resumen de pedido</h4>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Subtotal</span>
                    <span><?php echo number_format($total_final, 2); ?>€</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-secondary">Envío</span>
                    <span class="text-success">GRATIS</span>
                </div>
                <hr style="border-color: #444;">
                <div class="d-flex justify-content-between mb-4">
                    <span class="h4">Total</span>
                    <span class="h4 text-info"><?php echo number_format($total_final, 2); ?>€</span>
                </div>
               
<a href="finalizar_compra.php" class="btn btn-checkout w-100 text-center text-decoration-none">
    FINALIZAR COMPRA
</a>                <a href="catalogo.php" class="btn btn-link w-100 text-secondary text-decoration-none">Seguir comprando</a>
               
                <div class="mt-4 text-center opacity-50">
                    <i class="fab fa-cc-visa fa-2x mx-1"></i>
                    <i class="fab fa-cc-mastercard fa-2x mx-1"></i>
                    <i class="fab fa-cc-paypal fa-2x mx-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>
 
</body>
</html>