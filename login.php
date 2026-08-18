<?php
ob_start();
ini_set('session.cookie_path', '/');
session_start();
require_once 'config.env.php';
require_once 'conexion.php';
$c = themeColors();

// //if theres already a session, redirect to the panel
if (!empty($_SESSION['kkmart_user'])) {
    header("Location: admin.php"); exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['usuario'] ?? '');
    $p = trim($_POST['password'] ?? '');

    if ($u && $p) {
        $us = mysqli_real_escape_string($conn, $u);
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM usuarios WHERE usuario='$us' AND activo=1"));
        if ($row && password_verify($p, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['kkmart_user']   = $row['usuario'];
            $_SESSION['kkmart_nombre'] = $row['nombre'];
            $_SESSION['kkmart_id']     = $row['id'];
            header("Location: admin.php"); exit();
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>" data-theme="<?= $c['name'] ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="<?= FONT_IMPORT ?>" rel="stylesheet">
  <style>
<?php include 'theme_styles.php'; ?>

    .login-wrap {
      min-height: 100vh; display: flex; align-items: center; justify-content: center;
      padding: 24px; background: var(--bg);
    }
    .login-card {
      background: var(--surface); border: 1px solid var(--border);
      border-radius: var(--radius); padding: clamp(28px,5vw,48px);
      width: 100%; max-width: 420px;
      box-shadow: 0 24px 64px <?= rgba($c['bg'], 0.5) ?>;
    }
    .login-logo {
      font-family: var(--font-display); font-size: 1.4rem; font-weight: 800;
      color: var(--primary); text-decoration: none; display: block; margin-bottom: 8px;
    }
    .login-title {
      font-family: var(--font-display); font-size: 1.6rem; font-weight: 800;
      letter-spacing: -.03em; margin-bottom: 6px;
    }
    .login-sub { font-size: .85rem; color: var(--muted); margin-bottom: 32px; }

    .field { margin-bottom: 18px; }
    .field label { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
    .field input {
      width: 100%; background: var(--bg); border: 1px solid var(--border);
      color: var(--text); border-radius: var(--radius-sm); padding: 12px 14px;
      font-family: var(--font-body); font-size: .95rem; outline: none;
      transition: border-color var(--transition), box-shadow var(--transition);
      box-sizing: border-box;
    }
    .field input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px <?= rgba($c['primary'], 0.15) ?>;
    }
    .field input::placeholder { color: var(--muted); }

    .btn-login {
      width: 100%; padding: 14px; border: none;
      background: var(--primary); color: #000;
      font-family: var(--font-display); font-size: 1rem; font-weight: 700;
      border-radius: var(--radius-sm); cursor: pointer;
      transition: all var(--transition); margin-top: 8px;
    }
    .btn-login:hover { background: var(--primary-dark); transform: translateY(-1px); }

    .error-msg {
      background: <?= rgba($c['accent'], 0.1) ?>; border: 1px solid <?= rgba($c['accent'], 0.25) ?>;
      color: var(--accent); border-radius: var(--radius-sm);
      padding: 10px 14px; font-size: .85rem; margin-bottom: 20px;
    }

    .back-link { display: block; text-align: center; margin-top: 20px; font-size: .8rem; color: var(--muted); text-decoration: none; }
    .back-link:hover { color: var(--primary); }

    .theme-btn {
      position: fixed; top: 16px; right: 16px;
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 50px; padding: 6px 14px 6px 8px;
      display: flex; align-items: center; gap: 8px; cursor: pointer;
      transition: all var(--transition);
    }
    .theme-btn:hover { border-color: var(--primary); }
  </style>
</head>
<body>

<button class="theme-btn" onclick="toggleTheme()">
  <div class="toggle-track"><div class="toggle-thumb"></div></div>
  <span class="toggle-label" style="font-size:.78rem;color:var(--muted)"><?= $c['name']==='dark'?'🌙':'☀️' ?></span>
</button>

<div class="login-wrap">
  <div class="login-card">
    <a href="index.php" class="login-logo"><?= SITE_NAME ?></a>
    <h1 class="login-title">Panel de administración</h1>
    <p class="login-sub">Ingresa tus credenciales para continuar.</p>

    <?php if ($error): ?>
    <div class="error-msg">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="field">
        <label for="usuario">Usuario</label>
        <input type="text" name="usuario" id="usuario" placeholder="tu_usuario" autocomplete="username" required>
      </div>
      <div class="field">
        <label for="password">Contraseña</label>
        <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn-login">Entrar →</button>
    </form>

    <a href="index.php" class="back-link">← Volver al catálogo</a>
  </div>
</div>

<?php include 'theme_js.php'; ?>
</body>
</html>
