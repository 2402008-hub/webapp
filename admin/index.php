<?php
session_start();

// ── CAMBIA ESTA CONTRASEÑA ──
define('ADMIN_PASS', 'iglesia2025');
// ────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['password']) && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin_logged'] = true;
        header('Location: panel.php');
        exit;
    } else {
        $error = 'Contraseña incorrecta. Intenta de nuevo.';
    }
}

if (!empty($_SESSION['admin_logged'])) {
    header('Location: panel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — 1ra Iglesia Apostólica</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #0d1f3c 0%, #1a3a6e 100%);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Lato', sans-serif;
    }
    .card {
      background: #fff;
      border-radius: 12px;
      padding: 3rem 2.5rem;
      width: 100%; max-width: 380px;
      box-shadow: 0 20px 60px rgba(0,0,0,.3);
      text-align: center;
    }
    .cross { font-size: 2.5rem; color: #c9a84c; margin-bottom: .5rem; display: block; }
    h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem; color: #0d1f3c;
      margin-bottom: .3rem;
    }
    .sub { font-size: .85rem; color: #6b7280; margin-bottom: 2rem; }
    label { display: block; text-align: left; font-size: .8rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em; color: #374151; margin-bottom: .4rem; }
    input[type=password] {
      width: 100%; padding: .75rem 1rem;
      border: 1.5px solid #d1d5db; border-radius: 6px;
      font-size: .95rem; margin-bottom: 1.25rem;
      transition: border-color .2s;
      outline: none;
    }
    input[type=password]:focus { border-color: #1a3a6e; }
    button {
      width: 100%; padding: .85rem;
      background: #0d1f3c; color: #fff;
      border: none; border-radius: 6px;
      font-size: .95rem; font-weight: 700;
      cursor: pointer; transition: background .2s;
    }
    button:hover { background: #1a3a6e; }
    .error {
      background: #fef2f2; color: #dc2626;
      border: 1px solid #fecaca;
      border-radius: 6px; padding: .7rem 1rem;
      font-size: .85rem; margin-bottom: 1rem;
    }
    .back { display: block; margin-top: 1.5rem; font-size: .82rem; color: #6b7280; text-decoration: none; }
    .back:hover { color: #1a3a6e; }
  </style>
</head>
<body>
  <div class="card">
    <span class="cross">✝</span>
    <h1>Panel Admin</h1>
    <p class="sub">1ra Iglesia Apostólica</p>

    <?php if (!empty($error)): ?>
      <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" placeholder="••••••••" autofocus>
      <button type="submit">Entrar</button>
    </form>
    <a href="../index.html" class="back">← Volver al sitio</a>
  </div>
</body>
</html>
