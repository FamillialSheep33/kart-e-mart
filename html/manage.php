<?php
ob_start();
ini_set('session.cookie_path', '/');
session_start();
require_once 'config.env.php';
require_once 'conexion.php';
if (empty($_SESSION['kkmart_user'])) { header('Location: login.php'); exit(); }
$c = themeColors();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion  = $_POST['accion'] ?? '';
    $pid     = trim($_POST['id'] ?? '');
    $pidSafe = mysqli_real_escape_string($conn, $pid);

    if ($pid !== '') {
        if ($accion === 'toggle') {
            mysqli_query($conn, "UPDATE productos SET disponible = 1 - disponible WHERE id = '$pidSafe'");

        } elseif ($accion === 'delete') {
            $dir = "uploads/$pid/";
            if (is_dir($dir)) { foreach (glob($dir.'*') ?: [] as $f) unlink($f); rmdir($dir); }
            for ($i = 1; $i <= 5; $i++) { $f = "uploads/{$pid}_{$i}.jpg"; if (file_exists($f)) unlink($f); }
            $leg = "uploads/{$pid}.jpg"; if (file_exists($leg)) unlink($leg);
            mysqli_query($conn, "DELETE FROM productos WHERE id = '$pidSafe'");

        } elseif ($accion === 'editar') {
            $nombre      = mysqli_real_escape_string($conn, trim($_POST['nombre']      ?? ''));
            $precio      = floatval($_POST['precio'] ?? 0);
            $categoria   = mysqli_real_escape_string($conn, trim($_POST['categoria']   ?? ''));
            $descripcion = mysqli_real_escape_string($conn, trim($_POST['descripcion'] ?? ''));
            $tags_cache  = mysqli_real_escape_string($conn, trim($_POST['tags_cache']  ?? ''));
            mysqli_query($conn, "UPDATE productos SET nombre='$nombre', precio=$precio, categoria='$categoria', descripcion='$descripcion', tags_cache='$tags_cache' WHERE id='$pidSafe'");
            // Actualizar medidas: borrar las existentes y reinsertar
            mysqli_query($conn, "DELETE FROM producto_medidas WHERE producto_id='$pidSafe'");
            $med_nombres = $_POST['medida_nombre'] ?? [];
            $med_precios = $_POST['medida_precio'] ?? [];
            $med_codigos = $_POST['medida_codigo'] ?? [];
            for ($mi = 0; $mi < count($med_nombres); $mi++) {
                $mn = mysqli_real_escape_string($conn, trim($med_nombres[$mi] ?? ''));
                $mp = floatval($med_precios[$mi] ?? 0);
                $mc = mysqli_real_escape_string($conn, trim($med_codigos[$mi] ?? ''));
                if ($mn !== '') mysqli_query($conn, "INSERT INTO producto_medidas (producto_id, nombre, precio, codigo) VALUES ('$pidSafe', '$mn', $mp, '$mc')");
            }
        }
    }
    $redir = (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
    header("Location: $redir");
    header("Connection: close");
    ob_end_flush();
    exit();
}

$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';
$where  = $search ? "WHERE nombre LIKE '%$search%'" : '';
$prods  = mysqli_query($conn, "SELECT * FROM productos $where ORDER BY id DESC");
$total  = mysqli_num_rows($prods);

$categorias_bd = [];
$res = mysqli_query($conn, "SELECT nombre FROM categorias ORDER BY nombre");
if ($res) while ($r = mysqli_fetch_assoc($res)) $categorias_bd[] = $r['nombre'];
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>" data-theme="<?= $c['name'] ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión | <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="<?= FONT_IMPORT ?>" rel="stylesheet">
  <style>
<?php include 'theme_styles.php'; ?>
    .page { max-width: 1100px; margin: 0 auto; padding: 40px clamp(16px,4vw,48px) 80px; }
    .page-title { font-family: var(--font-display); font-size: clamp(1.4rem,3vw,2rem); font-weight: 800; letter-spacing: -.03em; margin-bottom: 28px; }
    .admin-badge { font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; background: <?= rgba($c['accent'], 0.12) ?>; color: var(--accent); border: 1px solid <?= rgba($c['accent'], 0.25) ?>; border-radius: 50px; padding: 4px 12px; vertical-align: middle; margin-left: 8px; }
    .nav-admin-tag { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; background: <?= rgba($c['accent'], 0.12) ?>; color: var(--accent); border: 1px solid <?= rgba($c['accent'], 0.25) ?>; border-radius: 50px; padding: 3px 10px; }
    .toolbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
    .toolbar-search { position: relative; }
    .toolbar-search input { background: var(--surface); border: 1px solid var(--border); color: var(--text); border-radius: var(--radius-sm); padding: 9px 14px 9px 36px; font-family: var(--font-body); font-size: .875rem; outline: none; width: 240px; transition: border-color var(--transition); }
    .toolbar-search input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px <?= rgba($c['primary'], 0.15) ?>; }
    .toolbar-search input::placeholder { color: var(--muted); }
    .toolbar-search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 13px; pointer-events: none; }
    .toolbar-count { font-size: .8rem; color: var(--muted); background: var(--surface); padding: 4px 12px; border-radius: 50px; border: 1px solid var(--border); }
    .btn-new { background: var(--primary); color: #000; font-family: var(--font-display); font-size: .85rem; font-weight: 700; border: none; border-radius: var(--radius-sm); padding: 9px 18px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all var(--transition); }
    .btn-new:hover { background: var(--primary-dark); transform: translateY(-1px); }
    .table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    thead { background: var(--surface2); }
    thead th { padding: 12px 16px; text-align: left; font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
    tbody tr { border-top: 1px solid var(--border); transition: background var(--transition); }
    tbody tr:hover { background: var(--surface2); }
    td { padding: 12px 16px; font-size: .875rem; vertical-align: middle; }
    .prod-img { width: 48px; height: 48px; object-fit: contain; border-radius: var(--radius-sm); background: var(--surface2); border: 1px solid var(--border); padding: 4px; }
    .prod-name { font-weight: 600; color: var(--text); }
    .prod-id { font-size: .72rem; color: var(--muted); margin-top: 2px; font-family: monospace; }
    .prod-cat { font-size: .72rem; color: var(--muted); background: var(--surface2); border: 1px solid var(--border); border-radius: 50px; padding: 2px 8px; white-space: nowrap; }
    .prod-price { font-family: var(--font-display); font-weight: 700; color: var(--text); white-space: nowrap; }
    .avail-pill { width: 42px; height: 22px; border-radius: 50px; position: relative; transition: background .25s; border: none; cursor: pointer; padding: 0; flex-shrink: 0; }
    .avail-pill.on  { background: var(--primary); }
    .avail-pill.off { background: var(--border); }
    .avail-pill::after { content: ''; position: absolute; top: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: left .25s; }
    .avail-pill.on::after  { left: calc(100% - 19px); }
    .avail-pill.off::after { left: 3px; }
    .avail-label { font-size: .78rem; font-weight: 600; }
    .avail-label.on  { color: var(--primary); }
    .avail-label.off { color: var(--muted); }
    .actions { display: flex; gap: 6px; }
    .btn-sm { font-size: .75rem; font-weight: 600; border-radius: var(--radius-sm); padding: 6px 12px; border: 1px solid; cursor: pointer; background: transparent; white-space: nowrap; transition: all var(--transition); }
    .btn-edit   { color: var(--primary); border-color: <?= rgba($c['primary'], 0.35) ?>; }
    .btn-edit:hover { background: var(--primary); color: #000; border-color: var(--primary); }
    .btn-delete { color: var(--accent); border-color: <?= rgba($c['accent'], 0.35) ?>; }
    .btn-delete:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
    .empty { text-align: center; padding: 60px 20px; color: var(--muted); }
    .modal-overlay { display: none; position: fixed; inset: 0; background: <?= rgba($c['bg'], 0.85) ?>; backdrop-filter: blur(6px); z-index: 300; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box; }
    .modal-overlay.open { display: flex; }
    .modal { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 28px; width: 100%; max-height: 90vh; overflow-y: auto; }
    #editModal .modal { max-width: 540px; }
    #deleteModal .modal { max-width: 380px; }
    .modal-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
    .modal-body { font-size: .875rem; color: var(--muted); line-height: 1.6; margin-bottom: 20px; }
    .modal-name { color: var(--text); font-weight: 600; }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
    .field { margin-bottom: 14px; }
    .field label { display: block; font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; }
    .field input, .field select, .field textarea { width: 100%; background: var(--bg); border: 1px solid var(--border); color: var(--text); border-radius: var(--radius-sm); padding: 10px 12px; font-family: var(--font-body); font-size: .875rem; outline: none; transition: border-color var(--transition); box-sizing: border-box; }
    .field input:focus, .field select:focus, .field textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px <?= rgba($c['primary'], 0.15) ?>; }
    .field textarea { min-height: 80px; resize: vertical; }
    .field-hint { font-size: .7rem; color: var(--muted); margin-top: 4px; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .btn-cancel { background: var(--surface2); color: var(--muted); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 9px 18px; font-size: .875rem; font-weight: 600; cursor: pointer; }
    .btn-save { background: var(--primary); color: #000; border: none; border-radius: var(--radius-sm); padding: 9px 24px; font-family: var(--font-display); font-size: .9rem; font-weight: 700; cursor: pointer; }
    .btn-save:hover { background: var(--primary-dark); }

    .medidas-edit-header { display: grid; grid-template-columns: 1fr 110px 130px 36px; gap: 8px; margin-bottom: 4px; }
    .medidas-edit-header span { font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
    .medida-edit-row { display: grid; grid-template-columns: 1fr 110px 130px 36px; gap: 8px; align-items: center; margin-bottom: 6px; }
    .medida-edit-row input { background: var(--bg); border: 1px solid var(--border); color: var(--text); border-radius: var(--radius-sm); padding: 8px 10px; font-size: .82rem; font-family: var(--font-body); outline: none; width: 100%; box-sizing: border-box; }
    .medida-edit-row input:focus { border-color: var(--primary); }
    .btn-rm-med { background: <?= rgba($c['accent'], 0.1) ?>; border: 1px solid <?= rgba($c['accent'], 0.25) ?>; color: var(--accent); border-radius: var(--radius-sm); width: 34px; height: 34px; cursor: pointer; font-size: .9rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .btn-rm-med:hover { background: var(--accent); color: #fff; }
    .btn-add-med { background: var(--surface2); border: 1px solid var(--border); color: var(--muted); border-radius: var(--radius-sm); padding: 7px 14px; font-size: .78rem; font-weight: 600; cursor: pointer; margin-top: 4px; }
    .btn-add-med:hover { border-color: var(--primary); color: var(--primary); }
    .btn-confirm-delete { background: var(--accent); color: #fff; border: none; border-radius: var(--radius-sm); padding: 9px 18px; font-size: .875rem; font-weight: 700; cursor: pointer; }
    @media (max-width: 700px) { .col-cat, .col-price { display: none; } .two-col { grid-template-columns: 1fr; } }
  </style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="nav-logo"><?= SITE_NAME ?></a>
  <span class="nav-admin-tag">Admin</span>
  <div class="nav-links">
    <a href="admin.php"    class="nav-link">+ Nuevo</a>
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
  <a href="admin.php">+ Nuevo producto</a>
  <a href="usuarios.php">Usuarios</a>
  <a href="index.php">Ver catálogo</a>
  <a href="logout.php" style="color:var(--accent)">Cerrar sesión</a>
  <?php if (FEATURE_THEME_TOGGLE): ?>
  <a href="#" onclick="toggleTheme();return false;"><?= $c['name']==='dark'?'☀️ Modo claro':'🌙 Modo oscuro' ?></a>
  <?php endif; ?>
</div>

<div class="page">
  <h1 class="page-title">Gestión de productos <span class="admin-badge">Admin</span></h1>

  <div class="toolbar">
    <div class="toolbar-search">
      <span class="toolbar-search-icon">⌕</span>
      <input type="text" id="searchInput" placeholder="Buscar producto..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <span class="toolbar-count"><?= $total ?> producto<?= $total!==1?'s':'' ?></span>
    <a href="admin.php" class="btn-new">+ Nuevo</a>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:56px"></th>
          <th>Producto</th>
          <th class="col-cat">Categoría</th>
          <th class="col-price">Precio</th>
          <th style="width:130px">Disponible</th>
          <th style="width:190px"></th>
        </tr>
      </thead>
      <tbody>
        <?php if ($total === 0): ?>
        <tr><td colspan="6"><div class="empty"><p>No hay productos<?= $search?' con ese nombre':'' ?>.</p></div></td></tr>
        <?php endif; ?>

        <?php while ($row = mysqli_fetch_assoc($prods)):
          $base = __DIR__.'/';
          $img  = UPLOADS_DIR.$row['id'].'/1.jpg';
          if (!file_exists($base.$img)) $img = UPLOADS_DIR.$row['id'].'_1.jpg';
          if (!file_exists($base.$img)) $img = UPLOADS_DIR.$row['id'].'.jpg';
          if (!file_exists($base.$img)) $img = PLACEHOLDER_IMG;
          $disp = (int)($row['disponible'] ?? 1);
          $meds_arr = [];
          $pidSafe2 = mysqli_real_escape_string($conn, $row['id']);
          $meds_res = mysqli_query($conn, "SELECT * FROM producto_medidas WHERE producto_id='".$pidSafe2."' ORDER BY id");
          while ($mr = mysqli_fetch_assoc($meds_res)) $meds_arr[] = $mr;
          $data = json_encode([
            'id'          => $row['id'],
            'nombre'      => $row['nombre'],
            'precio'      => $row['precio'],
            'categoria'   => $row['categoria'],
            'descripcion' => $row['descripcion'] ?? '',
            'tags_cache'  => $row['tags_cache']  ?? '',
            'medidas'     => $meds_arr,
          ], JSON_HEX_QUOT | JSON_HEX_APOS);
        ?>
        <tr>
          <td><img src="<?= $img ?>" class="prod-img" alt=""></td>
          <td>
            <div class="prod-name"><?= htmlspecialchars($row['nombre']) ?></div>
            <div class="prod-id"><?= htmlspecialchars($row['id']) ?></div>
          </td>
          <td class="col-cat"><span class="prod-cat"><?= htmlspecialchars($row['categoria']) ?></span></td>
          <td class="col-price"><span class="prod-price">&#36;<?= number_format($row['precio'],2,'.',',') ?></span></td>
          <td>
            <form method="POST" style="display:inline-flex;align-items:center;gap:8px;">
              <input type="hidden" name="accion" value="toggle">
              <input type="hidden" name="id"     value="<?= htmlspecialchars($row['id']) ?>">
              <button type="submit" class="avail-pill <?= $disp?'on':'off' ?>"></button>
            </form>
            <span class="avail-label <?= $disp?'on':'off' ?>"><?= $disp?'Disponible':'Oculto' ?></span>
          </td>
          <td>
            <div class="actions">
              <button class="btn-sm btn-edit"   onclick='openEdit(<?= $data ?>)'>Modificar</button>
              <button class="btn-sm btn-delete" onclick="openDelete('<?= htmlspecialchars(addslashes($row['id'])) ?>', '<?= htmlspecialchars(addslashes($row['nombre'])) ?>')">Eliminar</button>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<footer><p><?= FOOTER_TEXT ?></p></footer>

<!-- MODAL MODIFICAR -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-title">✏️ Modificar producto</div>
    <form method="POST">
      <input type="hidden" name="accion" value="editar">
      <input type="hidden" name="id"     id="editId">
      <div class="field">
        <label>Título</label>
        <input type="text" name="nombre" id="editNombre" required>
      </div>
      <div class="two-col">
        <div class="field">
          <label>Precio (&#36;)</label>
          <input type="number" name="precio" id="editPrecio" step="0.01" min="0" required>
        </div>
        <div class="field">
          <label>Categoría</label>
          <select name="categoria" id="editCategoria">
            <option value="">— Sin categoría —</option>
            <?php foreach ($categorias_bd as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="field">
        <label>Tags</label>
        <input type="text" name="tags_cache" id="editTags" placeholder="ej: gaming, inalámbrico, oferta">
        <div class="field-hint">Separados por coma</div>
      </div>
      <div class="field">
        <label>Descripción</label>
        <textarea name="descripcion" id="editDescripcion"></textarea>
      </div>
      <div class="field" style="border-top:1px solid var(--border);padding-top:16px;margin-top:4px;">
        <label>Medidas / Variantes</label>
        <div id="editMedidasHeader" style="display:grid;grid-template-columns:1fr 120px 150px 36px;gap:8px;margin-bottom:6px;">
          <span style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)">Nombre</span>
          <span style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)">Precio</span>
          <span style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)">Código</span>
          <span></span>
        </div>
        <div id="editMedidasList"></div>
        <button type="button" onclick="addEditMedida()" style="background:none;border:1px dashed var(--border);border-radius:var(--radius-sm);color:var(--muted);padding:8px 16px;font-size:.8rem;font-weight:600;cursor:pointer;width:100%;margin-top:4px;transition:all var(--transition)">+ Agregar medida</button>
      </div>
      <div class="field">
        <label>Medidas / Variantes</label>
        <div class="medidas-edit-header"><span>Medida</span><span>Precio (&#36;)</span><span>Código</span><span></span></div>
        <div id="editMedidasList"></div>
        <button type="button" class="btn-add-med" onclick="addEditMedida()">+ Agregar medida</button>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeModals()">Cancelar</button>
        <button type="submit" class="btn-save">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<!--delete -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal-title">⚠ Eliminar producto</div>
    <div class="modal-body">¿Seguro que quieres eliminar <span class="modal-name" id="deleteName"></span>? Se borrarán sus imágenes y <strong>no se puede deshacer</strong>.</div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModals()">Cancelar</button>
      <form method="POST" style="display:inline">
        <input type="hidden" name="accion" value="delete">
        <input type="hidden" name="id"     id="deleteId">
        <button type="submit" class="btn-confirm-delete">Sí, eliminar</button>
      </form>
    </div>
  </div>
</div>

<?php include 'theme_js.php'; ?>
<script>
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    const q = this.value.trim();
    window.location.href = q ? 'manage.php?q=' + encodeURIComponent(q) : 'manage.php';
  }, 400);
});

function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModals(); });
});


let editMedCount = 0;
function addEditMedida(nombre='', precio='', codigo='') {
  const row = document.createElement('div');
  row.className = 'medida-edit-row';
  row.innerHTML = `
    <input type="text"   name="medida_nombre[]" value="${nombre}" placeholder="ej: 195/65 R15">
    <input type="number" name="medida_precio[]" value="${precio}" placeholder="0.00" step="0.01" min="0">
    <input type="text"   name="medida_codigo[]" value="${codigo}" placeholder="SKU">
    <button type="button" class="btn-rm-med" onclick="this.parentElement.remove()">✕</button>
  `;
  document.getElementById('editMedidasList').appendChild(row);
}

function openEdit(data) {
  document.getElementById('editId').value          = data.id;
  document.getElementById('editNombre').value      = data.nombre;
  document.getElementById('editPrecio').value      = data.precio;
  document.getElementById('editDescripcion').value = data.descripcion;
  document.getElementById('editTags').value        = data.tags_cache;
  const sel = document.getElementById('editCategoria');
  for (let o of sel.options) o.selected = (o.value === data.categoria);
  // Load medidas
  const mlist = document.getElementById('editMedidasList');
  mlist.innerHTML = '';
  if (data.medidas && data.medidas.length > 0) {
    data.medidas.forEach(m => addEditMedida(m.nombre, m.precio, m.codigo));
  }
  document.getElementById('editModal').classList.add('open');
}

function openDelete(id, nombre) {
  document.getElementById('deleteId').value         = id;
  document.getElementById('deleteName').textContent = '"' + nombre + '"';
  document.getElementById('deleteModal').classList.add('open');
}
</script>
</body>
</html>
