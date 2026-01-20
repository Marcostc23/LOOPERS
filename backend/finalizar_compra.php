<?php
session_start();
require "conexion.php";
 
// Si el carrito está vacío, redirigir al catálogo
if (empty($_SESSION['carrito'])) {
    header("Location: catalogo.php");
    exit();
}
 
$total_final = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total_final += $item['precio'] * $item['cantidad'];
}
 
// Lógica para simular el éxito de la compra
$compra_finalizada = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pago'])) {
    // Aquí normalmente guardarías el pedido en la base de datos (tabla pedidos)
    $compra_finalizada = true;
    unset($_SESSION['carrito']); // Limpiamos el carrito
}
?>
 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout – RetroGroove Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Inter:wght@400;600&display=swap%22 rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --neon-blue: #00eaff; --neon-purple: #bc13fe; --dark-bg: #050505; }
        body { background: var(--dark-bg); color: white; font-family: 'Inter', sans-serif; min-height: 100vh; }
       
        .checkout-container { max-width: 600px; margin: 50px auto; }
       
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
 
        .neon-title { font-family: 'Orbitron'; color: var(--neon-blue); text-shadow: 0 0 15px var(--neon-blue); margin-bottom: 30px; text-align: center; }
       
        .form-control {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 12px;
            padding: 12px;
        }
        .form-control:focus { background: rgba(255,255,255,0.1); color: white; border-color: var(--neon-blue); box-shadow: 0 0 10px var(--neon-blue); }
 
        .btn-pay {
            background: var(--neon-blue);
            color: black;
            font-weight: 800;
            border: none;
            border-radius: 14px;
            padding: 18px;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: 0.4s;
            margin-top: 20px;
        }
        .btn-pay:hover { background: white; box-shadow: 0 0 30px white; transform: scale(1.02); }
 
        /* Estilo Ticket de Éxito */
        .success-box { text-align: center; }
        .success-icon { font-size: 5rem; color: #00ff88; text-shadow: 0 0 20px #00ff88; margin-bottom: 20px; }
       
        .ticket {
            background: white;
            color: black;
            padding: 20px;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
            text-align: left;
            position: relative;
            margin-top: 20px;
        }
        .ticket::after {
            content: '';
            position: absolute;
            bottom: -10px; left: 0; width: 100%; height: 10px;
            background: linear-gradient(-45deg, transparent 5px, white 5px), linear-gradient(45deg, transparent 5px, white 5px);
            background-size: 10px 10px;
        }
    </style>
</head>
<body>
 
<div class="container checkout-container">
    <?php if (!$compra_finalizada): ?>
        <div class="glass-card">
            <h2 class="neon-title">CHECKOUT</h2>
           
            <div class="mb-4">
                <p class="text-secondary small mb-1">Total a pagar:</p>
                <h3 class="display-5 fw-bold" style="color: var(--neon-blue);"><?php echo number_format($total_final, 2); ?> €</h3>
            </div>
 
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small text-secondary">Nombre en la tarjeta</label>
                    <input type="text" class="form-control" placeholder="EJ. ENOL MARCOS" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-secondary">Número de tarjeta</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-credit-card"></i></span>
                        <input type="text" class="form-control" placeholder="0000 0000 0000 0000" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small text-secondary">Expiración</label>
                        <input type="text" class="form-control" placeholder="MM/YY" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small text-secondary">CVV</label>
                        <input type="text" class="form-control" placeholder="123" required>
                    </div>
                </div>
               
                <input type="hidden" name="confirmar_pago" value="1">
                <button type="submit" class="btn btn-pay">
                    <i class="fas fa-lock me-2"></i> Procesar Pago Seguro
                </button>
            </form>
           
            <div class="mt-4 text-center">
                <a href="carrito.php" class="text-secondary text-decoration-none small"><i class="fas fa-arrow-left"></i> Modificar pedido</a>
            </div>
        </div>
 
    <?php else: ?>
        <div class="glass-card success-box animate__animated animate__zoomIn">
            <i class="fas fa-check-circle success-icon"></i>
            <h2 class="fw-bold mb-2">¡PAGO COMPLETADO!</h2>
            <p class="text-secondary">Tu música está en camino hacia el tocadiscos.</p>
           
            <div class="ticket">
                <div class="text-center fw-bold border-bottom mb-2 pb-2">RETROGROOVE RECORDS</div>
                <p class="small mb-1">FECHA: <?php echo date('d/m/Y H:i'); ?></p>
                <p class="small mb-3">PEDIDO: #RG-<?php echo rand(1000, 9999); ?></p>
                <div class="border-bottom mb-2"></div>
                <div class="d-flex justify-content-between fw-bold">
                    <span>TOTAL</span>
                    <span><?php echo number_format($total_final, 2); ?> €</span>
                </div>
                <div class="mt-3 text-center small text-uppercase">Gracias por mantener vivo el vinilo</div>
            </div>
 
            <a href="index.php" class="btn btn-pay mt-5">VOLVER AL INICIO</a>
        </div>
    <?php endif; ?>
</div>
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</body>
</html>