<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – RetroGroove</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

  <style>
    body {
      background: radial-gradient(circle at center, #0a0a0a, #000000);
      color: white;
      font-family: 'Inter', sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-box {
      background: rgba(0, 0, 0, 0.7);
      padding: 40px;
      border-radius: 15px;
      width: 350px;
      box-shadow: 0 0 20px #00eaff;
      text-align: center;
    }

    .login-box h2 {
      font-family: 'Orbitron', sans-serif;
      margin-bottom: 20px;
      color: #00eaff;
    }

    .btn-login {
      background: #00eaff;
      border: none;
      font-weight: bold;
      color: #000;
    }

    .btn-login:hover {
      background: #00c6d8;
    }

    .input-glow {
      border: 2px solid #00eaff;
      background: black;
      color: white;
    }
  </style>
</head>
<body>

<div class="login-box">
  <h2>Login</h2>

  <!-- FORMULARIO -->
  <form action="validar_login.php" method="post">
    
    <div class="mb-3">
      <label class="form-label">Usuario</label>
      <input type="text" name="usuario" class="form-control input-glow" placeholder="Tu usuario" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Contraseña</label>
      <input type="password" name="password" class="form-control input-glow" placeholder="Tu contraseña" required>
    </div>

    <button type="submit" class="btn btn-login w-100 mt-3">Entrar</button>
  </form>

  <p class="mt-3">
    ¿No tienes cuenta? <a href="#" style="color:#00eaff;">Registrarse</a>
  </p>
</div>

</body>
</html>
