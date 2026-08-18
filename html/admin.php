<?php
ob_start();
ini_set('session.cookie_path', '/');
session_start();
require_once 'config.env.php';
if (empty($_SESSION['kkmart_user'])) { header('Location: login.php'); exit(); }
require_once 'conexion.php';
$c = themeColors();
$status   = $_GET['status'] ?? '';
$maxFotos = 5;

$categorias = [];
$res = mysqli_query($conn, "SELECT * FROM categorias ORDER BY nombre");
while ($row = mysqli_fetch_assoc($res)) $categorias[] = $row;
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>" data-theme="<?= $c['name'] ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="<?= FONT_IMPORT ?>" rel="stylesheet">
  <style>
<?php include 'theme_styles.php'; ?>
    .page { max-width: 720px; margin: 0 auto; padding: 40px clamp(16px,4vw,48px) 80px; }
    .page-title { font-family: var(--font-display); font-size: clamp(1.6rem,4vw,2.4rem); font-weight: 800; letter-spacing: -.04em; margin-bottom: 8px; }
    .admin-badge { font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; background: <?= rgba($c['accent'], 0.12) ?>; color: var(--accent); border: 1px solid <?= rgba($c['accent'], 0.25) ?>; border-radius: 50px; padding: 4px 12px; }
    .nav-admin-tag { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; background: <?= rgba($c['accent'], 0.12) ?>; color: var(--accent); border: 1px solid <?= rgba($c['accent'], 0.25) ?>; border-radius: 50px; padding: 3px 10px; }
    .form-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: clamp(20px,4vw,36px); margin-top: 28px; }
    .form-card-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; margin-bottom: 24px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
    .field { margin-bottom: 20px; }
    .field label { display: block; font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
    .field input[type=text], .field input[type=number], .field textarea, .field select {
      width: 100%; background: var(--bg); border: 1px solid var(--border);
      color: var(--text); border-radius: var(--radius-sm); padding: 11px 14px;
      font-family: var(--font-body); font-size: .9rem; outline: none;
      transition: border-color var(--transition), box-shadow var(--transition); box-sizing: border-box;
    }
    .field input:focus, .field textarea:focus, .field select:focus {
      border-color: var(--primary); box-shadow: 0 0 0 3px <?= rgba($c['primary'], 0.15) ?>;
    }
    .field input.error { border-color: var(--accent); box-shadow: 0 0 0 3px <?= rgba($c['accent'], 0.15) ?>; }
    .field input::placeholder, .field textarea::placeholder { color: var(--muted); }
    .field textarea { min-height: 100px; resize: vertical; }
    .field-hint { font-size: .72rem; color: var(--muted); margin-top: 5px; }
    .field-error { font-size: .72rem; color: var(--accent); margin-top: 5px; display: none; }
    .field-error.show { display: block; }
    .id-check { display: inline-flex; align-items: center; gap: 6px; font-size: .72rem; margin-top: 5px; }
    .id-check.ok   { color: #22c55e; }
    .id-check.dup  { color: var(--accent); }
    .id-check.load { color: var(--muted); }

    /* Categoría */
    .cat-wrap { display: flex; gap: 8px; }
    .cat-wrap select, .cat-wrap input { flex: 1; }
    .cat-toggle { font-size: .75rem; color: var(--primary); cursor: pointer; text-decoration: underline; margin-top: 6px; display: inline-block; }

    /* Fotos */
    .fotos-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
    @media (max-width: 500px) { .fotos-grid { grid-template-columns: repeat(3, 1fr); } }
    .foto-slot { aspect-ratio: 1; border-radius: var(--radius-sm); border: 2px dashed var(--border); background: var(--bg); display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; position: relative; transition: border-color var(--transition); }
    .foto-slot:hover { border-color: var(--primary); }
    .foto-slot input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .foto-slot .plus { font-size: 1.4rem; color: var(--muted); pointer-events: none; }
    .foto-slot .label { font-size: .65rem; color: var(--muted); margin-top: 4px; pointer-events: none; }
    .foto-slot img { width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; }
    .foto-slot.has-img { border-style: solid; border-color: var(--primary); }

    /* Medidas */
    .medidas-toggle-row { display: flex; align-items: center; gap: 10px; padding: 16px 0 0; border-top: 1px solid var(--border); margin-top: 8px; cursor: pointer; }
    .medidas-toggle-row input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary); }
    .medidas-toggle-row span { font-size: .85rem; font-weight: 600; color: var(--muted); }
    .medidas-body { display: none; margin-top: 14px; }
    .medidas-body.open { display: block; }
    .medidas-col-header { display: grid; grid-template-columns: 1fr 120px 150px 36px; gap: 8px; margin-bottom: 6px; }
    .medidas-col-header span { font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
    .medida-row { display: grid; grid-template-columns: 1fr 120px 150px 36px; gap: 8px; margin-bottom: 8px; align-items: center; }
    .medida-row input { background: var(--bg); border: 1px solid var(--border); color: var(--text); border-radius: var(--radius-sm); padding: 9px 11px; font-size: .85rem; font-family: var(--font-body); outline: none; width: 100%; box-sizing: border-box; transition: border-color var(--transition); }
    .medida-row input:focus { border-color: var(--primary); }
    .medida-row input::placeholder { color: var(--muted); }
    .btn-remove { background: none; border: 1px solid <?= rgba($c['accent'], 0.35) ?>; border-radius: var(--radius-sm); color: var(--accent); width: 36px; height: 36px; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; transition: all var(--transition); }
    .btn-remove:hover { background: var(--accent); color: #fff; }
    .btn-add-medida { background: none; border: 1px dashed var(--border); border-radius: var(--radius-sm); color: var(--muted); padding: 9px 16px; font-size: .8rem; font-weight: 600; cursor: pointer; width: 100%; margin-top: 4px; transition: all var(--transition); }
    .btn-add-medida:hover { border-color: var(--primary); color: var(--primary); }
    @media (max-width: 540px) { .medida-row, .medidas-col-header { grid-template-columns: 1fr 100px 36px; } .medida-row input:nth-child(3), .medidas-col-header span:nth-child(3) { display: none; } }

    .btn-submit { width: 100%; padding: 15px; border: none; background: var(--primary); color: #000; font-family: var(--font-display); font-size: 1rem; font-weight: 800; border-radius: var(--radius-sm); cursor: pointer; transition: all var(--transition); margin-top: 20px; }
    .btn-submit:hover { background: var(--primary-dark); }
    .btn-submit:disabled { opacity: .5; cursor: not-allowed; }
    .alert { padding: 12px 18px; border-radius: var(--radius-sm); font-size: .875rem; margin-bottom: 20px; border: 1px solid; }
    .alert-ok    { background: rgba(34,197,94,.08); border-color: rgba(34,197,94,.25); color: #22c55e; }
    .alert-error { background: <?= rgba($c['accent'], 0.08) ?>; border-color: <?= rgba($c['accent'], 0.25) ?>; color: var(--accent); }
  </style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="nav-logo"><?= SITE_NAME ?></a>
  <span class="nav-admin-tag">Admin</span>
  <div class="nav-links">
    <a href="manage.php"   class="nav-link">Gestión</a>
    <a href="usuarios.php" class="nav-link">Usuarios</a>
    <a href="index.php"    class="nav-link">Catálogo</a>
    <a href="logout.php"   class="nav-link" style="color:var(--accent)">Salir</a>
    <?php if (FEATURE_THEME_TOGGLE): ?>
    <button class="theme-toggle" onclick="toggleTheme()">
      <div class="toggle-track"><div class="toggle-thumb"></div></div>
      <span class="toggle-label"><?= $c['name']==='dark'?'🌙':'☀️' ?></span>
    </button>
    <?php endif; ?>
  </div>
  <button class="nav-menu-btn" onclick="toggleMobileNav()">☰</button>
</nav>

<div class="mobile-nav" id="mobileNav">
  <button class="mobile-nav-close" onclick="toggleMobileNav()">✕</button>
  <a href="manage.php">Gestión de productos</a>
  <a href="usuarios.php">Usuarios</a>
  <a href="index.php">Ver catálogo</a>
  <a href="logout.php" style="color:var(--accent)">Cerrar sesión</a>
  <?php if (FEATURE_THEME_TOGGLE): ?>
  <a href="#" onclick="toggleTheme();return false;"><?= $c['name']==='dark'?'☀️ Modo claro':'🌙 Modo oscuro' ?></a>
  <?php endif; ?>
</div>

<div class="page">
  <h1 class="page-title">Panel de carga <span class="admin-badge">ADMIN</span></h1>

  <?php if ($status === 'success'): ?>
    <div class="alert alert-ok">✓ Producto subido correctamente.</div>
  <?php elseif ($status === 'error_dup'): ?>
    <div class="alert alert-error">⚠ Ese código ya existe. Usa uno diferente.</div>
  <?php elseif ($status === 'error_upload'): ?>
    <div class="alert alert-error">⚠ Producto guardado pero hubo un problema con las imágenes.</div>
  <?php elseif ($status === 'error_db'): ?>
    <div class="alert alert-error">⚠ Error al guardar. Revisa todos los campos.</div>
  <?php endif; ?>

  <div class="form-card">
    <div class="form-card-title">📦 Nuevo producto</div>
    <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">

      <!-- product code-->
      <div class="field">
        <label>Código / ID del producto *</label>
        <input type="text" name="codigo" id="codigoInput" placeholder="ej. RYZ-5700X" autocomplete="off" required>
        <div class="field-hint">Sin espacios ni diagonales. Ej: HW-MX7, LLN-244, GAB-01</div>
        <div class="field-error" id="codigoError">⚠ Ese código ya existe en el catálogo.</div>
        <div id="codigoCheck" style="display:none"></div>
      </div>

      <!-- name -->
      <div class="field">
        <label>Título del producto *</label>
        <input type="text" name="nombre" placeholder="Nombre descriptivo del artículo" required>
      </div>

      <!-- price -->
      <div class="field">
        <label>Precio base (&#36;)</label>
        <input type="number" name="precio" step="0.01" min="0" value="0.00" required>
      </div>

      <!-- categories -->
      <div class="field">
        <label>Categoría</label>
        <div id="catSelectWrap" class="cat-wrap">
          <select name="categoria" id="catSelect">
            <option value="">— Sin categoría —</option>
            <?php foreach ($categorias as $cat): ?>
            <option value="<?= htmlspecialchars($cat['nombre']) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="catNewWrap" class="cat-wrap" style="display:none;margin-top:8px;">
          <input type="text" name="categoria_nueva" id="catNuevaInput" placeholder="Nombre de la nueva categoría">
        </div>
        <?php if (empty($categorias)): ?>
        <span class="cat-toggle" onclick="toggleCatMode()">+ Crear primera categoría</span>
        <?php else: ?>
        <span class="cat-toggle" id="catToggleBtn" onclick="toggleCatMode()">+ Crear categoría nueva</span>
        <?php endif; ?>
      </div>

      <!-- tags -->
      <div class="field">
        <label>Tags</label>
        <input type="text" name="tags_cache" placeholder="ej: Auto, rines, neumáticos, llantas">
        <div class="field-hint">Separados por coma. Aparecen como filtros en el catálogo.</div>
      </div>

      <!-- description -->
      <div class="field">
        <label>Descripción</label>
        <textarea name="descripcion" placeholder="Descripción detallada del producto..."></textarea>
      </div>

      <!-- pictures -->
      <div class="field">
        <label>Imágenes del producto (hasta <?= $maxFotos ?>)</label>
        <div class="fotos-grid">
          <?php for ($i = 0; $i < $maxFotos; $i++): ?>
          <div class="foto-slot" id="slot<?= $i ?>">
            <input type="file" name="foto[]" accept="image/*" onchange="previewFoto(this, <?= $i ?>)">
            <span class="plus">+</span>
            <span class="label"><?= $i === 0 ? 'Principal' : "Foto ".($i+1) ?></span>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- sizes -->
      <div class="medidas-toggle-row" onclick="toggleMedidas()">
        <input type="checkbox" id="medidasCheck" name="tiene_medidas" value="1" onclick="event.stopPropagation();toggleMedidas()">
        <span>¿Este producto tiene medidas / variantes? (ej: tallas, tamaños)</span>
      </div>
      <div class="medidas-body" id="medidasBody">
        <div class="medidas-col-header">
          <span>Medida / Nombre</span><span>Precio (&#36;)</span><span>Código ref.</span><span></span>
        </div>
        <div id="medidasList"></div>
        <button type="button" class="btn-add-medida" onclick="addMedida()">+ Agregar medida</button>
      </div>

      <button type="submit" class="btn-submit" id="submitBtn">Subir producto →</button>
    </form>
  </div>
</div>

<footer><p><?= FOOTER_TEXT ?></p></footer>

<?php include 'theme_js.php'; ?>
<script>
// ──legacy code? ──
let checkTimer, medidaIdx = 0, codigoOk = true;

document.getElementById('codigoInput').addEventListener('input', function() {
  let val = this.value;
  // delete non valid characters such as /
  val = val.replace(/[\s\/\\]/g, '');
  this.value = val;

  const errDiv   = document.getElementById('codigoError');
  const checkDiv = document.getElementById('codigoCheck');

  if (!val) { errDiv.classList.remove('show'); checkDiv.style.display='none'; codigoOk=true; return; }

  clearTimeout(checkTimer);
  checkDiv.style.display = 'inline-flex';
  checkDiv.className = 'id-check load';
  checkDiv.textContent = '⏳ Verificando...';
  errDiv.classList.remove('show');

  checkTimer = setTimeout(() => {
    fetch('check_codigo.php?codigo=' + encodeURIComponent(val))
      .then(r => r.json())
      .then(data => {
        if (data.existe) {
          checkDiv.className = 'id-check dup';
          checkDiv.textContent = '✕ Ya existe';
          errDiv.classList.add('show');
          document.getElementById('codigoInput').classList.add('error');
          codigoOk = false;
        } else {
          checkDiv.className = 'id-check ok';
          checkDiv.textContent = '✓ Disponible';
          errDiv.classList.remove('show');
          document.getElementById('codigoInput').classList.remove('error');
          codigoOk = true;
        }
      });
  }, 500);
});

document.getElementById('uploadForm').addEventListener('submit', function(e) {
  if (!codigoOk) { e.preventDefault(); alert('El código ya existe, elige otro.'); }
});

// toggle category──
let catNueva = <?= empty($categorias) ? 'true' : 'false' ?>;
function toggleCatMode() {
  catNueva = !catNueva;
  document.getElementById('catSelectWrap').style.display = catNueva ? 'none' : 'flex';
  document.getElementById('catNewWrap').style.display    = catNueva ? 'flex' : 'none';
  const btn = document.getElementById('catToggleBtn');
  if (btn) btn.textContent = catNueva ? '← Usar categoría existente' : '+ Crear categoría nueva';
  if (!catNueva) document.getElementById('catNuevaInput').value = '';
}
if (catNueva) toggleCatMode();

// ── sizes ──
function toggleMedidas() {
  const cb   = document.getElementById('medidasCheck');
  const body = document.getElementById('medidasBody');
  //checkbox clicked on a row
  body.classList.toggle('open', cb.checked);
  if (cb.checked && document.getElementById('medidasList').children.length === 0) addMedida();
}

function addMedida() {
  const i = medidaIdx++;
  const row = document.createElement('div');
  row.className = 'medida-row';
  row.id = 'med-' + i;
  row.innerHTML = `
    <input type="text"   name="medida_nombre[]" placeholder="ej: 195/65 R15">
    <input type="number" name="medida_precio[]" placeholder="0.00" step="0.01" min="0">
    <input type="text"   name="medida_codigo[]" placeholder="ej: GY-195-R15">
    <button type="button" class="btn-remove" onclick="document.getElementById('med-${i}').remove()">✕</button>
  `;
  document.getElementById('medidasList').appendChild(row);
  row.querySelector('input').focus();
}

// ── pics preview ──
function previewFoto(input, idx) {
  if (!input.files || !input.files[0]) return;
  const slot = document.getElementById('slot' + idx);
  const reader = new FileReader();
  reader.onload = e => {
    let img = slot.querySelector('img');
    if (!img) { img = document.createElement('img'); slot.appendChild(img); }
    img.src = e.target.result;
    slot.classList.add('has-img');
    slot.querySelector('.plus').style.display  = 'none';
    slot.querySelector('.label').style.display = 'none';
  };
  reader.readAsDataURL(input.files[0]);
}
</script>
</body>
</html>
