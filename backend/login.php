<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – RetroGroove</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <style>
    body {
      background: radial-gradient(circle at center, #0a0a0a, #000000);
      color: white;
      font-family: 'Inter', sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0;
      overflow: hidden;
    }

    /* Animación RGB para el resplandor de la caja */
    @keyframes rgbGlow {
      0% { box-shadow: 0 0 25px #ff0000; border-color: #ff0000; }
      20% { box-shadow: 0 0 25px #ffeb00; border-color: #ffeb00; }
      40% { box-shadow: 0 0 25px #00ff00; border-color: #00ff00; }
      60% { box-shadow: 0 0 25px #00eaff; border-color: #00eaff; }
      80% { box-shadow: 0 0 25px #ff00ff; border-color: #ff00ff; }
      100% { box-shadow: 0 0 25px #ff0000; border-color: #ff0000; }
    }

    .login-box {
      background: rgba(0, 0, 0, 0.9);
      padding: 40px;
      border-radius: 15px;
      width: 380px;
      text-align: center;
      border: 2px solid #00eaff;
      animation: rgbGlow 8s linear infinite;
      position: relative;
    }

    .login-box h2 {
      font-family: 'Orbitron', sans-serif;
      margin-bottom: 25px;
      color: white;
      text-transform: uppercase;
      letter-spacing: 2px;
      text-shadow: 0 0 10px rgba(0, 234, 255, 0.5);
    }

    /* BOTÓN ENTRAR MEJORADO */
    .btn-login {
      background: #00eaff;
      border: 1px solid #00eaff;
      font-weight: 800;
      color: #000;
      transition: 0.4s all ease;
      height: 50px;
      text-transform: uppercase;
      letter-spacing: 1px;
      border-radius: 8px;
    }

    .btn-login:hover {
      background: rgba(0, 234, 255, 0.1); /* Fondo oscuro al pasar el ratón */
      color: #ffffff; /* Texto blanco para contraste */
      box-shadow: 0 0 25px #00eaff, inset 0 0 10px #00eaff; /* Doble resplandor */
      border-color: #ffffff;
      transform: translateY(-2px);
    }

    /* BOTÓN VOLVER SEPARADO Y MEJORADO */
    .btn-atras {
      background: transparent;
      border: 2px solid rgba(0, 234, 255, 0.4);
      color: #00eaff;
      font-size: 13px;
      text-decoration: none !important;
      transition: 0.3s;
      display: inline-block;
      margin-top: 40px; /* Más separación */
      border-radius: 30px;
      padding: 10px 25px !important;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .btn-atras:hover {
      background: rgba(0, 234, 255, 0.1);
      color: white;
      border-color: #00eaff;
      box-shadow: 0 0 15px rgba(0, 234, 255, 0.3);
    }

    .input-glow {
      border: 1px solid rgba(0, 234, 255, 0.5);
      background: rgba(255, 255, 255, 0.05);
      color: white;
      height: 45px;
    }

    .input-glow:focus {
      background: black;
      color: white;
      border-color: #00eaff;
      box-shadow: 0 0 10px #00eaff;
    }

    .form-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #00eaff;
        display: block;
        text-align: left;
        margin-bottom: 8px;
    }
  </style>
</head>
<body>

<div class="login-box">
  <h2>Acceso</h2>

  <form action="validar_login.php" method="post">
    
    <div class="mb-3 text-start">
      <label class="form-label">Usuario</label>
      <input type="text" name="usuario" class="form-control input-glow" placeholder="Ingresa tu usuario" required>
    </div>

    <div class="mb-3 text-start">
      <label class="form-label">Contraseña</label>
      <input type="password" name="password" class="form-control input-glow" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-login w-100 mt-3">Entrar al Sistema</button>
    
    <div>
        <a href="../frontend/index.html" class="btn btn-atras">
          <i class="fas fa-arrow-left me-2"></i> Volver a RetroGroove
        </a>
    </div>
  </form>

  <p class="mt-4" style="font-size: 12px; color: #666;">
    ¿No tienes cuenta? <a href="#" style="color:#00eaff; text-decoration: none; font-weight: bold;">Registrarse</a>
  </p>
</div>

</body>
</html>
