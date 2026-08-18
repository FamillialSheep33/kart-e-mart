<?php
require_once 'config.env.php';
require_once 'conexion.php';
$c = themeColors();

if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$id = mysqli_real_escape_string($conn, trim($_GET['id']));
$producto = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM productos WHERE id='$id' AND disponible=1"));
if (!$producto) { header("Location: index.php"); exit(); }

//took the images from the server
$imagenes = [];
$dir = 'uploads/'.$producto['id'].'/';
for ($i = 1; $i <= 5; $i++) {
    if (file_exists($dir.$i.'.jpg')) $imagenes[] = $dir.$i.'.jpg';
}
if (empty($imagenes)) {
    for ($i = 1; $i <= 5; $i++) {
        $f = 'uploads/'.$producto['id'].'_'.$i.'.jpg';
        if (file_exists($f)) $imagenes[] = $f;
    }
}
if (empty($imagenes)) {
    $leg = 'uploads/'.$producto['id'].'.jpg';
    $imagenes[] = file_exists($leg) ? $leg : PLACEHOLDER_IMG;
}
$idPad = $producto['id'];
//load sizes
$medidas = [];
$res_m = mysqli_query($conn, "SELECT * FROM producto_medidas WHERE producto_id='$id' ORDER BY id ASC");
if ($res_m) while ($m = mysqli_fetch_assoc($res_m)) $medidas[] = $m;

$medidas = [];
$res_med = mysqli_query($conn, "SELECT * FROM producto_medidas WHERE producto_id='$id' ORDER BY id ASC");
if ($res_med) while ($m = mysqli_fetch_assoc($res_med)) $medidas[] = $m;
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>" data-theme="<?= $c['name'] ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($producto['nombre']) ?> | <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="<?= FONT_IMPORT ?>" rel="stylesheet">
  <style>
<?php include 'theme_styles.php'; ?>


    .product-layout {
      max-width: 1100px; margin: 0 auto;
      padding: 32px clamp(16px,4vw,48px) 80px;
      display: grid; grid-template-columns: 1fr 420px; gap: 48px; align-items: start;
    }

    /*images and code for not getting out of line  ── */
    .img-section { position: sticky; top: 80px; }
    .main-img-wrap {
      background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
      width: 100%; aspect-ratio: 1 / 1;
      display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    .main-img-wrap img {
      width: 100%; height: 100%; object-fit: contain; object-position: center;
      max-width: 100%; max-height: 100%; padding: clamp(12px,3vw,32px); display: block;
      transition: transform .4s ease, opacity .2s ease;
    }
    .main-img-wrap:hover img { transform: scale(1.04); }

    /*thumbnails*/
    .thumb-strip { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
    .thumb-btn {
      width: 64px; height: 64px; flex-shrink: 0;
      border-radius: var(--radius-sm); border: 2px solid var(--border);
      background: var(--surface2); overflow: hidden; cursor: pointer; padding: 0;
      transition: border-color var(--transition), transform var(--transition);
    }
    .thumb-btn img { width: 100%; height: 100%; object-fit: contain; padding: 4px; display: block; }
    .thumb-btn:hover { border-color: var(--muted); transform: translateY(-2px); }
    .thumb-btn.active { border-color: var(--primary); }


    /* sizes  */
    .medidas-block { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px 20px; }
    .medidas-label { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
    .medidas-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .medida-chip {
      border: 2px solid var(--border); border-radius: var(--radius-sm);
      background: var(--bg); padding: 8px 16px; cursor: pointer;
      font-family: var(--font-display); font-size: .85rem; font-weight: 700;
      transition: all var(--transition); user-select: none;
      display: flex; flex-direction: column; align-items: center; gap: 2px;
    }
    .medida-chip:hover { border-color: var(--muted); }
    .medida-chip.active { border-color: var(--primary); background: <?= rgba($c['primary'], 0.08) ?>; color: var(--primary); }
    .medida-chip .chip-precio { font-size: .72rem; font-weight: 500; color: var(--muted); font-family: var(--font-body); }
    .medida-chip.active .chip-precio { color: var(--primary); }
    /* ── INFO ── */
    .product-info { display: flex; flex-direction: column; gap: 20px; }
    .info-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .meta-badge { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 4px 10px; border-radius: 50px; }
    .meta-badge.avail { background: <?= rgba($c['primary'], 0.12) ?>; color: var(--primary); border: 1px solid <?= rgba($c['primary'], 0.25) ?>; }
    .meta-badge.cat { background: var(--surface); color: var(--muted); border: 1px solid var(--border); }

    .product-title { font-family: var(--font-display); font-size: clamp(1.4rem,3vw,2.1rem); font-weight: 700; letter-spacing: -.03em; line-height: 1.15; color: var(--text); }

    .price-block { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px 24px; }
    .price-main { font-family: var(--font-display); font-size: 2.6rem; font-weight: 800; color: var(--text); letter-spacing: -.04em; display: flex; align-items: flex-start; gap: 4px; }
    .price-currency { font-family: var(--font-body); font-size: 1.6rem; font-weight: 600; margin-top: 4px; }

    .id-block { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px 20px; cursor: pointer; transition: all var(--transition); user-select: none; }
    .id-block:hover { border-color: var(--primary); background: <?= rgba($c['primary'], 0.04) ?>; }
    .id-block-inner { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: nowrap; }
    .id-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); font-weight: 600; margin-bottom: 4px; }
    .id-number { font-family: var(--font-display); font-size: clamp(.9rem, 3.5vw, 2.2rem); font-weight: 800; color: var(--primary); letter-spacing: .04em; word-break: break-all; line-height: 1.2; }
    .id-copy-hint { font-size: .7rem; color: var(--muted); white-space: nowrap; flex-shrink: 0; transition: color var(--transition); }
    .id-block:hover .id-copy-hint { color: var(--primary); }
    .id-block.copied { border-color: var(--primary); background: <?= rgba($c['primary'], 0.08) ?>; }
    .id-block.copied .id-copy-hint { color: var(--primary); }


    .spec-table { width: 100%; border-collapse: collapse; }
    .spec-table tr { border-bottom: 1px solid var(--border); }
    .spec-table td { padding: 11px 0; font-size: .875rem; }
    .spec-table td:first-child { color: var(--muted); width: 140px; font-weight: 500; }
    .spec-block { margin-top: 16px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px 24px; }
    .spec-block-title { font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
    /* Desktop: specs below images. Mobile: specs after id-block in the right panel */
    .spec-below-img { display: block; }
    .spec-in-panel  { display: none; }
    @media (max-width: 860px) {
      .spec-below-img { display: none; }
      .spec-in-panel  { display: block; }
    }

    .desc-block { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px 24px; }
    .desc-block-title { font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
    .desc-block-body { color: var(--text); line-height: 1.8; font-size: .92rem; }


    @media (max-width: 860px) {
      .product-layout { grid-template-columns: 1fr; gap: 28px; }
      .img-section { position: static; }
      .main-img-wrap { max-height: min(360px, 75vw); }
    }

    /* ── sizes ── */
    .medidas-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px 24px; }
    .medidas-label { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; display: block; }
    .medidas-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .medida-chip {
      padding: 8px 16px; border-radius: var(--radius-sm);
      border: 2px solid var(--border); background: var(--surface2);
      cursor: pointer; font-size: .875rem; font-weight: 600;
      transition: all var(--transition); user-select: none;
    }
    .medida-chip:hover { border-color: var(--muted); }
    .medida-chip.selected { border-color: var(--primary); background: <?= rgba($c['primary'], 0.1) ?>; color: var(--primary); }
    .medida-chip .chip-price { font-size: .75rem; color: var(--muted); display: block; font-weight: 400; margin-top: 2px; }
    .medida-chip.selected .chip-price { color: var(--primary); opacity: .8; }

    @media (max-width: 480px) {

      .price-main { font-size: 1.8rem; }
      .id-number { font-size: clamp(.8rem, 3vw, 1.3rem); }
      .thumb-btn { width: 48px; height: 48px; }
      .product-title { font-size: clamp(1.1rem, 5vw, 1.6rem); }
    }
  </style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="nav-logo"><?= SITE_NAME ?></a>
  <div class="nav-links">
    <a href="index.php" class="nav-link">← Catálogo</a>
    <?php if (FEATURE_THEME_TOGGLE): ?>
    <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
      <div class="toggle-track"><div class="toggle-thumb"></div></div>
      <span class="toggle-label"><?= $c['name']==='dark'?'🌙':'☀️' ?></span>
    </button>
    <?php endif; ?>
  </div>
  <button class="nav-menu-btn" onclick="toggleMobileNav()">☰</button>
</nav>

<div class="mobile-nav" id="mobileNav">
  <button class="mobile-nav-close" onclick="toggleMobileNav()">✕</button>
  <a href="index.php">← Volver al catálogo</a>
  <?php if (FEATURE_THEME_TOGGLE): ?>
  <a href="#" onclick="toggleTheme();return false;"><?= $c['name']==='dark'?'☀️ Modo claro':'🌙 Modo oscuro' ?></a>
  <?php endif; ?>
</div>

<div class="product-layout">
  <div class="img-section">
    <div class="main-img-wrap">
      <img id="mainImg" src="<?= $imagenes[0] ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
    </div>
    <?php if (count($imagenes) > 1): ?>
    <div class="thumb-strip">
      <?php foreach ($imagenes as $i => $r): ?>
      <button class="thumb-btn <?= $i==0?'active':'' ?>" onclick="switchImg('<?= $r ?>',this)">
        <img src="<?= $r ?>" alt="Foto <?= $i+1 ?>">
      </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  <!-- specs under the images, only seen on desktop -->
    <div class="spec-below-img spec-block" style="margin-top:16px;">
      <div class="spec-block-title">Especificaciones</div>
      <table class="spec-table">
        <?php if ($producto['categoria']): ?><tr><td>Categoría</td><td><?= htmlspecialchars($producto['categoria']) ?></td></tr><?php endif; ?>
        <tr><td>ID Producto</td><td>#<?= $idPad ?></td></tr>
        <?php if (count($imagenes) > 1): ?><tr><td>Fotos</td><td><?= count($imagenes) ?></td></tr><?php endif; ?>
      </table>
    </div>
  </div>

  <div class="product-info">
    <div class="info-meta">
      <span class="meta-badge avail">Disponible</span>
      <?php if ($producto['categoria']): ?><span class="meta-badge cat"><?= htmlspecialchars($producto['categoria']) ?></span><?php endif; ?>
      <?php if (!empty($producto['ventas'])): ?><span style="font-size:.8rem;color:var(--muted)">+<?= $producto['ventas'] ?> vendidos</span><?php endif; ?>
    </div>

    <h1 class="product-title"><?= htmlspecialchars($producto['nombre']) ?></h1>

    <div class="price-block">
      <div class="price-main" id="precioDisplay"><span class="price-currency">&#36;</span><span class="price-value"><?= formatPrice($producto['precio']) ?></span></div>
    </div>


    <?php if (!empty($medidas)): ?>
    <div class="medidas-block">
      <div class="medidas-label">Medidas disponibles</div>
      <div class="medidas-chips">
        <?php foreach ($medidas as $mi => $md): ?>
        <div class="medida-chip <?= $mi===0?'active':'' ?>" 
             onclick='selectMedida(this, <?= htmlspecialchars(json_encode($md, JSON_HEX_QUOT), ENT_QUOTES) ?>)'>
          <span><?= htmlspecialchars($md['nombre']) ?></span>
          <span class="chip-precio">&#36;<?= formatPrice($md['precio']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="id-block" id="idBlock" onclick="copyId('<?= $idPad ?>')">
      <div class="id-block-inner">
        <div>
          <div class="id-label">ID de referencia — clic para copiar</div>
          <div class="id-number">#<?= $idPad ?></div>
        </div>
        <span class="id-copy-hint" id="copyHint">📋 Copiar</span>
      </div>
    </div>


    <!-- specs for mobile -->
    <div class="spec-in-panel spec-block">
      <div class="spec-block-title">Especificaciones</div>
      <table class="spec-table">
        <?php if ($producto['categoria']): ?><tr><td>Categoría</td><td><?= htmlspecialchars($producto['categoria']) ?></td></tr><?php endif; ?>
        <tr><td>ID Producto</td><td>#<?= $idPad ?></td></tr>
        <?php if (count($imagenes) > 1): ?><tr><td>Imágenes</td><td><?= count($imagenes) ?></td></tr><?php endif; ?>
      </table>
    </div>

    <?php if ($producto['descripcion']): ?>
    <div class="desc-block">
      <div class="desc-block-title">Descripción</div>
      <div class="desc-block-body"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></div>
    </div>
    <?php endif; ?>

  </div>
</div>

<footer><p><?= FOOTER_TEXT ?></p></footer>

<?php include 'theme_js.php'; ?>
<script>
window._currentId = "<?= $idPad ?>";

// ── sizes  ──
<?php if (!empty($medidas)):
  $m0 = $medidas[0]; ?>
// Init con primera medida
window.addEventListener('DOMContentLoaded', function() {
  selectMedida(document.querySelector('.medida-chip'), <?= htmlspecialchars(json_encode(['nombre'=>$m0['nombre'],'precio'=>$m0['precio'],'codigo'=>$m0['codigo']], JSON_HEX_QUOT), ENT_QUOTES) ?>);
});
<?php endif; ?>

function selectMedida(el, data) {
  // Deselect all
  document.querySelectorAll('.medida-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  // Update price
  const priceEl = document.querySelector('.price-value');
  if (priceEl) priceEl.textContent = parseFloat(data.precio).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});
  // Update ID to medida's codigo
  const idNum = document.querySelector('.id-number');
  if (idNum && data.codigo) {
    idNum.textContent = '#' + data.codigo;
    window._currentId = data.codigo;
    // update copy function
    idNum.closest('.id-block') && (idNum.closest('.id-block').onclick = function(){ copyId(data.codigo); });
  }
}

function selectMedida(el, nombre, precio, codigo) {
  document.querySelectorAll('.medida-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  // Update price
  const fmt = precio.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
  document.getElementById('precioDisplay').innerHTML = '<span style="font-family:var(--font-body);font-size:1.6rem;font-weight:600;margin-top:4px">&#36;</span>' + fmt;
  // Update ID block if medida has its own code
  if (codigo) {
    document.getElementById('idNumber').textContent = '#' + codigo;
    window._currentId = codigo;
  }
}

function switchImg(src, btn) {
  const img = document.getElementById('mainImg');
  img.style.opacity = '0';
  setTimeout(() => { img.src = src; img.style.opacity = '1'; }, 180);
  document.querySelectorAll('.thumb-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
window._currentId = '<?= $idPad ?>';
function copyId(id) {
  id = window._currentId || id;
  navigator.clipboard.writeText(id).then(() => {
    const block = document.getElementById('idBlock');
    const hint  = document.getElementById('copyHint');
    hint.textContent = '✓ Copiado';
    block.classList.add('copied');
    setTimeout(() => {
      hint.textContent = '📋 Copiar';
      block.classList.remove('copied');
    }, 2000);
  });
}
</script>
</body>
</html>
