<?php
session_start();
require "conexion.php";

$conteo_carrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) { $conteo_carrito += $item['cantidad']; }
}

$sql = "SELECT * FROM vinilos WHERE visible = 1 ORDER BY id DESC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo – RetroGroove Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --neon-blue: #00eaff;
            --neon-purple: #bc13fe;
            --dark-bg: #050505;
            --glass: rgba(255, 255, 255, 0.03);
        }

        body { 
            background: var(--dark-bg); 
            color: white; 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(0, 234, 255, 0.07) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(188, 19, 254, 0.07) 0%, transparent 40%);
            overflow-x: hidden;
        }

        /* --- ANIMACIÓN FLOTANTE --- */
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .header-section { padding: 60px 0; }
        .neon-title { 
            font-family: 'Orbitron', sans-serif; font-size: 3.5rem; font-weight: 800;
            background: linear-gradient(45deg, #fff 30%, var(--neon-blue));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            text-transform: uppercase; letter-spacing: 8px;
        }

        /* --- TARJETA ULTRA PROFESIONAL --- */
        .card-vinilo {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 25px;
            transition: all 0.5s cubic-bezier(0.2, 1, 0.3, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
            animation: floating 6s ease-in-out infinite;
        }

        /* Diferentes tiempos de flotación para que no todos suban a la vez */
        .col-md-4:nth-child(2n) .card-vinilo { animation-delay: 1s; }
        .col-md-4:nth-child(3n) .card-vinilo { animation-delay: 2s; }

        .card-vinilo:hover {
            border-color: var(--neon-blue);
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 20px 50px rgba(0, 234, 255, 0.2);
            transform: scale(1.02);
            animation-play-state: paused;
        }

        /* Brillo que recorre la tarjeta en hover */
        .card-vinilo::after {
            content: '';
            position: absolute;
            top: -50%; left: -60%; width: 20%; height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
            transition: all 0.7s;
            pointer-events: none;
        }
        .card-vinilo:hover::after { left: 120%; }

        /* --- IMAGEN Y VINILO --- */
        .img-container {
            position: relative; margin-bottom: 25px; perspective: 1000px;
        }
        .img-vinilo { 
            width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 12px;
            z-index: 2; position: relative; transition: 0.6s cubic-bezier(0.2, 1, 0.3, 1);
            box-shadow: 15px 15px 30px rgba(0,0,0,0.6);
        }
        .disco-inner {
            position: absolute; top: 5%; left: 5%; width: 90%; height: 90%;
            background: repeating-radial-gradient(circle, #111, #111 2px, #000 4px, #111 5px);
            border-radius: 50%; z-index: 1; transition: 0.7s cubic-bezier(0.2, 1, 0.3, 1);
            display: flex; align-items: center; justify-content: center;
        }
        .disco-inner::after {
            content: ''; width: 30%; height: 30%; background: var(--neon-blue);
            border-radius: 50%; border: 4px solid #000; box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
        }

        .card-vinilo:hover .img-vinilo { transform: translateX(-20%) rotateY(-10deg); }
        .card-vinilo:hover .disco-inner { transform: translateX(25%) rotate(360deg); }

        /* --- BOTÓN PREMIUM --- */
        .btn-comprar {
            background: transparent; color: white; border: 1px solid rgba(255,255,255,0.2);
            font-weight: 700; width: 100%; padding: 15px; border-radius: 14px; 
            text-transform: uppercase; letter-spacing: 1px; transition: all 0.4s;
            position: relative; overflow: hidden;
        }

        .btn-comprar:hover {
            border-color: var(--neon-blue);
            color: var(--neon-blue);
            box-shadow: 0 0 20px rgba(0, 234, 255, 0.3), inset 0 0 10px rgba(0, 234, 255, 0.1);
            transform: translateY(-2px);
        }

        .btn-comprar::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: 0.5s;
        }
        .btn-comprar:hover::before { left: 100%; }

        /* --- PRECIO --- */
        .precio { 
            font-family: 'Orbitron'; color: var(--neon-blue); font-weight: 800; font-size: 1.8rem; 
            margin: 20px 0; text-shadow: 0 0 10px rgba(0, 234, 255, 0.3);
        }

        .cart-icon-container {
            background: var(--glass); padding: 15px; border-radius: 18px; color: white;
            border: 1px solid rgba(255,255,255,0.1); transition: 0.4s; text-decoration: none;
        }
        .cart-icon-container:hover {
            background: var(--neon-blue); color: black; box-shadow: 0 0 30px var(--neon-blue);
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<div class="container pb-5">
    <div class="header-section text-center">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="index.php" class="text-secondary text-decoration-none small fw-bold" style="letter-spacing:2px">
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

    <div class="row g-4 justify-content-center">
        <?php while($v = $resultado->fetch_assoc()): ?>
            <div class="col-sm-6 col-md-4 col-xl-3">
                <div class="card-vinilo d-flex flex-column text-center">
                    
                    <div class="img-container">
                        <div class="disco-inner"></div>
                        <?php if($v['foto']): ?>
                            <img src="<?php echo $v['foto']; ?>" class="img-vinilo" alt="Disco">
                        <?php else: ?>
                            <div class="img-vinilo bg-dark d-flex align-items-center justify-content-center">
                                <i class="fas fa-compact-disc fa-4x opacity-20"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h4 class="vinilo-nombre mb-1"><?php echo htmlspecialchars($v['nombre']); ?></h4>
                    <p class="text-secondary small mb-0 fw-bold"><?php echo htmlspecialchars($v['autor']); ?></p>
                    
                    <div class="precio"><?php echo number_format($v['precio'], 2); ?> €</div>

                    <form action="carrito.php" method="POST" class="mt-auto">
                        <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                        <input type="hidden" name="accion" value="agregar">
                        <button type="submit" class="btn btn-comprar">
                            <span>Añadir al Carrito</span>
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>