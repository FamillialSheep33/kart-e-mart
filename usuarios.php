<?php
ob_start();
ini_set('session.cookie_path', '/');
session_start();
require_once 'config.env.php';
require_once 'conexion.php';

// protect with session
if (empty($_SESSION['kkmart_user'])) {
    header("Location: login.php"); exit();
}

$c = themeColors();
$error = '';
$ok    = '';

// post actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $u = trim($_POST['usuario']  ?? '');
        $n = trim($_POST['nombre']   ?? '');
        $p = trim($_POST['password'] ?? '');

        if (!$u || !$p) {
            $error = 'Usuario y contraseña son obligatorios.';
        } else {
            $us = mysqli_real_escape_string($conn, $u);
            $ns = mysqli_real_escape_string($conn, $n);
            $h  = password_hash($p, PASSWORD_DEFAULT);
            if (mysqli_query($conn, "INSERT INTO usuarios (usuario, password, nombre) VALUES ('$us', '$h', '$ns')")) {
                $ok = "Usuario \"$u\" creado correctamente.";
            } else {
                $error = 'Ese nombre de usuario ya existe.';
            }
        }
    }

    elseif ($accion === 'toggle') {
        $id = intval($_POST['id']);
        // No desactivar al usuario actual
        if ($id !== (int)$_SESSION['kkmart_id']) {
            mysqli_query($conn, "UPDATE usuarios SET activo = 1 - activo WHERE id = $id");
        }
        header("Location: usuarios.php"); exit();
    }

    elseif ($accion === 'delete') {
        $id = intval($_POST['id']);
        if ($id !== (int)$_SESSION['kkmart_id']) {
            mysqli_query($conn, "DELETE FROM usuarios WHERE id = $id");
        }
        header("Location: usuarios.php"); exit();
    }

    elseif ($accion === 'cambiar_pass') {
        $id = intval($_POST['id']);
        $p  = trim($_POST['nueva_pass'] ?? '');
        if ($p) {
            $h = password_hash($p, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE usuarios SET password='$h' WHERE id=$id");
            $ok = 'Contraseña actualizada.';
        }
    }
}

$usuarios = mysqli_query($conn, "SELECT * FROM usuarios ORDER BY creado DESC");
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>" data-theme="<?= $c['name'] ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios | <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="<?= FONT_IMPORT ?>" rel="stylesheet">
  <style>
<?php include 'theme_styles.php'; ?>

    .page { max-width: 900px; margin: 0 auto; padding: 40px clamp(16px,4vw,48px) 80px; }
    .page-title { font-family: var(--font-display); font-size: clamp(1.4rem,3vw,2rem); font-weight: 800; letter-spacing: -.03em; margin-bottom: 32px; }
    .admin-badge { font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; background: <?= rgba($c['accent'], 0.12) ?>; color: var(--accent); border: 1px solid <?= rgba($c['accent'], 0.25) ?>; border-radius: 50px; padding: 4px 12px; vertical-align: middle; margin-left: 8px; }
    .nav-admin-tag { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; background: <?= rgba($c['accent'], 0.12) ?>; color: var(--accent); border: 1px solid <?= rgba($c['accent'], 0.25) ?>; border-radius: 50px; padding: 3px 10px; }

    .grid { display: grid; grid-template-columns: 1fr 320px; gap: 28px; align-items: start; }

    /* ── TABLE ── */
    .table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    thead { background: var(--surface2); }
    thead th { padding: 11px 16px; text-align: left; font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
    tbody tr { border-top: 1px solid var(--border); transition: background var(--transition); }
    tbody tr:hover { background: var(--surface2); }
    td { padding: 12px 16px; font-size: .875rem; vertical-align: middle; }

    .user-name { font-weight: 600; color: var(--text); }
    .user-handle { font-size: .75rem; color: var(--muted); margin-top: 2px; font-family: monospace; }
    .you-badge { font-size: .62rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; background: <?= rgba($c['primary'], 0.12) ?>; color: var(--primary); border: 1px solid <?= rgba($c['primary'], 0.25) ?>; border-radius: 50px; padding: 2px 7px; margin-left: 6px; vertical-align: middle; }
    .date-cell { font-size: .78rem; color: var(--muted); white-space: nowrap; }

    /* toggle as  manage.php */
    .avail-pill { width: 42px; height: 22px; border-radius: 50px; position: relative; transition: background .25s ease; flex-shrink: 0; border: none; cursor: pointer; padding: 0; }
    .avail-pill.on  { background: var(--primary); }
    .avail-pill.off { background: var(--border); }
    .avail-pill::after { content: ''; position: absolute; top: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: left .25s ease; }
    .avail-pill.on::after  { left: calc(100% - 19px); }
    .avail-pill.off::after { left: 3px; }
    .avail-pill:disabled { opacity: .35; cursor: not-allowed; }

    .btn-sm { font-size: .75rem; font-weight: 600; border-radius: var(--radius-sm); padding: 5px 12px; border: 1px solid; cursor: pointer; transition: all var(--transition); background: transparent; }
    .btn-danger { color: var(--accent); border-color: <?= rgba($c['accent'], 0.3) ?>; }
    .btn-danger:hover:not(:disabled) { background: var(--accent); color: #fff; border-color: var(--accent); }
    .btn-danger:disabled { opacity: .3; cursor: not-allowed; }

    /* ── form card ── */
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; }
    .card-title { font-family: var(--font-display); font-size: 1rem; font-weight: 700; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
    .field { margin-bottom: 16px; }
    .field label { display: block; font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 7px; }
    .field input { width: 100%; background: var(--bg); border: 1px solid var(--border); color: var(--text); border-radius: var(--radius-sm); padding: 10px 12px; font-family: var(--font-body); font-size: .875rem; outline: none; transition: border-color var(--transition), box-shadow var(--transition); box-sizing: border-box; }
    .field input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px <?= rgba($c['primary'], 0.15) ?>; }
    .field input::placeholder { color: var(--muted); }
    .btn-submit { width: 100%; padding: 12px; border: none; background: var(--primary); color: #000; font-family: var(--font-display); font-size: .95rem; font-weight: 700; border-radius: var(--radius-sm); cursor: pointer; transition: all var(--transition); margin-top: 4px; }
    .btn-submit:hover { background: var(--primary-dark); }

    .alert { padding: 11px 16px; border-radius: var(--radius-sm); font-size: .85rem; margin-bottom: 20px; border: 1px solid; }
    .alert-ok    { background: rgba(34,197,94,.08); border-color: rgba(34,197,94,.25); color: #22c55e; }
    .alert-error { background: <?= rgba($c['accent'], 0.08) ?>; border-color: <?= rgba($c['accent'], 0.25) ?>; color: var(--accent); }

    /* password change */
    .modal-overlay { display: none; position: fixed; inset: 0; background: <?= rgba($c['bg'], 0.8) ?>; backdrop-filter: blur(6px); z-index: 300; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 28px; max-width: 360px; width: 90%; }
    .modal-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 16px; }
    .btn-cancel { background: var(--surface2); color: var(--muted); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 8px 16px; font-size: .875rem; font-weight: 600; cursor: pointer; }

    @media (max-width: 700px) { .grid { grid-template-columns: 1fr; } .col-date { display: none; } }
  </style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="nav-logo"><?= SITE_NAME ?></a>
  <span class="nav-admin-tag">Admin</span>
  <div class="nav-links">
    <a href="admin.php"   class="nav-link">Subir producto</a>
    <a href="manage.php"  class="nav-link">Gestión</a>
    <a href="logout.php"  class="nav-link">Cerrar sesión</a>
    <?php if (FEATURE_THEME_TOGGLE): ?>
    <button class="theme-toggle" onclick="toggleTheme()">
      <div class="toggle-track"><div class="toggle-thumb"></div></div>
      <span class="toggle-label"><?= $c['name']==='dark'?'🌙':'☀️' ?></span>
    </button>
    <?php endif; ?>
  </div>
</nav>

<div class="page">
  <h1 class="page-title">Usuarios <span class="admin-badge">Admin</span></h1>

  <?php if ($ok):    ?><div class="alert alert-ok">✓ <?= htmlspecialchars($ok) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="grid">
    <!-- Tabla de usuarios -->
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Activo</th>
            <th class="col-date">Creado</th>
            <th style="width:130px"></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($u = mysqli_fetch_assoc($usuarios)):
            $esYo = ($u['usuario'] === $_SESSION['kkmart_user']);
          ?>
          <tr>
            <td>
              <div class="user-name">
                <?= htmlspecialchars($u['nombre'] ?: $u['usuario']) ?>
                <?php if ($esYo): ?><span class="you-badge">tú</span><?php endif; ?>
              </div>
              <div class="user-handle">@<?= htmlspecialchars($u['usuario']) ?></div>
            </td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="accion" value="toggle">
                <input type="hidden" name="id"     value="<?= $u['id'] ?>">
                <button type="submit" class="avail-pill <?= $u['activo']?'on':'off' ?>"
                  <?= $esYo ? 'disabled title="No puedes desactivarte a ti mismo"' : '' ?>></button>
              </form>
            </td>
            <td class="col-date date-cell"><?= date('d/m/Y', strtotime($u['creado'])) ?></td>
            <td style="display:flex;gap:8px;flex-wrap:wrap;">
              <button class="btn-sm" style="color:var(--muted);border-color:var(--border);"
                onclick="openPassModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['usuario'])) ?>')">
                Contraseña
              </button>
              <form method="POST" style="display:inline">
                <input type="hidden" name="accion" value="delete">
                <input type="hidden" name="id"     value="<?= $u['id'] ?>">
                <button type="submit" class="btn-sm btn-danger"
                  <?= $esYo ? 'disabled title="No puedes eliminarte a ti mismo"' : '' ?>
                  <?= !$esYo ? 'onclick="return confirm(\'¿Eliminar usuario ' . htmlspecialchars(addslashes($u['usuario'])) . '?\')"' : '' ?>>
                  Eliminar
                </button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Formulario nuevo usuario -->
    <div class="card">
      <div class="card-title">➕ Nuevo usuario</div>
      <form method="POST">
        <input type="hidden" name="accion" value="crear">
        <div class="field">
          <label>Nombre completo</label>
          <input type="text" name="nombre" placeholder="Nombre Apellido">
        </div>
        <div class="field">
          <label>Usuario *</label>
          <input type="text" name="usuario" placeholder="usuario_unico" required>
        </div>
        <div class="field">
          <label>Contraseña *</label>
          <input type="password" name="password" placeholder="mínimo 6 caracteres" required>
        </div>
        <button type="submit" class="btn-submit">Crear usuario</button>
      </form>
    </div>
  </div>
</div>

<footer><p><?= FOOTER_TEXT ?></p></footer>

<!-- modal password change -->
<div class="modal-overlay" id="passModal">
  <div class="modal">
    <div class="modal-title">🔑 Cambiar contraseña</div>
    <p style="font-size:.85rem;color:var(--muted);margin-bottom:16px;">Usuario: <strong id="passUser"></strong></p>
    <form method="POST">
      <input type="hidden" name="accion" value="cambiar_pass">
      <input type="hidden" name="id"     id="passId">
      <div class="field">
        <label>Nueva contraseña</label>
        <input type="password" name="nueva_pass" id="passInput" placeholder="Nueva contraseña" required>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closePassModal()">Cancelar</button>
        <button type="submit" class="btn-submit" style="width:auto;padding:8px 20px;">Guardar</button>
      </div>
    </form>
  </div>
</div>

<?php include 'theme_js.php'; ?>
<script>
function openPassModal(id, user) {
  document.getElementById('passId').value   = id;
  document.getElementById('passUser').textContent = user;
  document.getElementById('passInput').value = '';
  document.getElementById('passModal').classList.add('open');
  setTimeout(() => document.getElementById('passInput').focus(), 100);
}
function closePassModal() {
  document.getElementById('passModal').classList.remove('open');
}
document.getElementById('passModal').addEventListener('click', function(e) {
  if (e.target === this) closePassModal();
});
</script>
</body>
</html>
