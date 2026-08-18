<?php
require_once 'config.env.php';
require_once 'conexion.php';
$c = themeColors();

$search    = isset($_GET['q'])   ? mysqli_real_escape_string($conn, trim($_GET['q']))   : '';
$categoria = isset($_GET['cat']) ? mysqli_real_escape_string($conn, trim($_GET['cat'])) : '';
$tag_fil   = isset($_GET['tag']) ? mysqli_real_escape_string($conn, trim($_GET['tag'])) : '';

// load categories from db
$categorias_bd = [];
$res = mysqli_query($conn, "SELECT * FROM categorias ORDER BY nombre");
while ($row = mysqli_fetch_assoc($res)) $categorias_bd[] = $row;

//load tags from db
$tags_bd = [];
$res = mysqli_query($conn, "SELECT DISTINCT tags_cache FROM productos WHERE tags_cache != '' ORDER BY tags_cache");
$all_tags = [];
while ($row = mysqli_fetch_assoc($res)) {
    foreach (explode(',', $row['tags_cache']) as $t) {
        $t = trim($t);
        if ($t && !in_array($t, $all_tags)) $all_tags[] = $t;
    }
}
sort($all_tags);
$tags_bd = $all_tags;

$where = ['p.disponible = 1'];
if ($search)    $where[] = "(p.nombre LIKE '%$search%' OR p.descripcion LIKE '%$search%' OR p.tags_cache LIKE '%$search%')";
if ($categoria) $where[] = "p.categoria = '$categoria'";
if ($tag_fil)   $where[] = "(FIND_IN_SET('$tag_fil', REPLACE(p.tags_cache, ', ', ',')) > 0 OR p.tags_cache LIKE '%$tag_fil%')";
$whereSQL = 'WHERE '.implode(' AND ', $where);

// paginate
$por_pagina = 50;
$pagina     = max(1, intval($_GET['pag'] ?? 1));
$offset     = ($pagina - 1) * $por_pagina;

$total_res  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM productos p $whereSQL"));
$total_prod = (int)$total_res['n'];
$total_pags = max(1, ceil($total_prod / $por_pagina));
$pagina     = min($pagina, $total_pags);
$offset     = ($pagina - 1) * $por_pagina;

$resultado = mysqli_query($conn, "SELECT p.id, p.nombre, p.precio, p.categoria FROM productos p $whereSQL ORDER BY p.id DESC LIMIT $por_pagina OFFSET $offset");
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>" data-theme="<?= $c['name'] ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= SITE_NAME ?> | Catálogo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="<?= FONT_IMPORT ?>" rel="stylesheet">
  <style>
<?php include 'theme_styles.php'; ?>

    /* ── HERO ── */
    .hero {
      position: relative; overflow: hidden;
      padding: clamp(48px,8vw,96px) clamp(16px,4vw,48px);
      text-align: center;
      background:
      linear-gradient(
        <?= ($c['name'] === 'dark')
        ? 'rgba(0, 0, 0, 0.7)'     /* Capa negra al 70% para modo oscuro */
        : 'rgba(255, 255, 255, 0.85)' /* Capa blanca al 85% para modo claro */
      ?>,
      <?= ($c['name'] === 'dark')
      ? 'rgba(0, 0, 0, 0.8)'
      : 'rgba(255, 255, 255, 0.5)'
      ?>
      ),

      url('banner.png') center/cover no-repeat;

      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 350px; /* Altura mínima para que luzca la foto */
    }
    .hero::before {
      content: ''; position: absolute; inset: 0; pointer-events: none;
      background:
        radial-gradient(ellipse 70% 50% at 50% -10%, <?= rgba($c['primary'], 0.18) ?> 0%, transparent 70%),
        radial-gradient(ellipse 40% 40% at 80% 120%, <?= rgba($c['accent'],  0.07) ?> 0%, transparent 60%);
    }
    .hero-eyebrow {
      display: inline-block; font-size: .75rem; font-weight: 600;
      letter-spacing: .12em; text-transform: uppercase; color: var(--primary);
      border: 1px solid <?= rgba($c['primary'], 0.35) ?>;
      border-radius: 50px; padding: 4px 14px; margin-bottom: 20px;
    }

    .hero h1 {
      font-family: var(--font-display);
      font-size: clamp(2rem, 8vw, 6rem); font-weight: 800;
      letter-spacing: -.04em; line-height: .95;
      max-width: 100%; word-break: break-word;
      <?php if ($c['name'] === 'dark'): ?>
      background: linear-gradient(135deg, var(--text) 0%, var(--muted) 100%);
      -webkit-background-clip: text; background-clip: text;
      -webkit-text-fill-color: transparent;
      <?php else: ?>
      color: var(--text);
      <?php endif; ?>
      margin-bottom: 16px;
    }
    .hero p { font-size: 1rem; color: var(--muted); font-weight: 300; max-width: 400px; margin: 0 auto 32px; line-height: 1.6; }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 50px; padding: 8px 18px; font-size: .82rem; color: var(--muted);
    }
    .hero-badge span { color: var(--primary); font-weight: 600; }

    /* ── SEARCH BANNER ── */
    .search-banner {
      background: var(--surface); border-bottom: 1px solid var(--border);
      padding: 12px clamp(16px,4vw,48px); font-size: .85rem; color: var(--muted);
      display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .search-banner strong { color: var(--text); }
    .search-banner a { color: var(--primary); text-decoration: none; font-weight: 500; margin-left: auto; }

    /* ── NAV SEARCH ── */
    .nav-search { flex: 1; max-width: 520px; position: relative; }
    .nav-search input {
      width: 100%; background: var(--surface); border: 1px solid var(--border);
      border-radius: 50px; padding: 9px 16px 9px 40px; color: var(--text);
      font-family: var(--font-body); font-size: .875rem; outline: none;
      transition: border-color var(--transition), box-shadow var(--transition);
    }
    .nav-search input::placeholder { color: var(--muted); }
    .nav-search input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px <?= rgba($c['primary'], 0.18) ?>;
    }
    .nav-search-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; pointer-events: none; }

    /* ── LAYOUT ── */
    .main-layout { display: flex; max-width: 1440px; margin: 0 auto; padding: 0 clamp(16px,4vw,48px) 80px; width: 100%; box-sizing: border-box; }

    /* ── SIDEBAR ── */
    .sidebar { width: 210px; flex-shrink: 0; padding-top: 40px; }
    .sidebar-label { font-size: .7rem; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; font-weight: 600; }
    .filter-list { list-style: none; display: flex; flex-direction: column; gap: 4px; }
    .filter-item a {
      display: block; padding: 8px 12px; border-radius: var(--radius-sm);
      color: var(--muted); text-decoration: none; font-size: .875rem;
      transition: all var(--transition); border: 1px solid transparent;
    }
    .filter-item a:hover, .filter-item a.active { color: var(--text); background: var(--surface); border-color: var(--border); }
    .filter-item a.active { color: var(--primary); }

    /* ── CATALOG ── */
    .catalog-area { flex: 1; min-width: 0; padding-top: 40px; padding-left: 32px; }
    .catalog-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; gap: 16px; flex-wrap: wrap; }
    .catalog-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; }
    .catalog-count { font-size: .8rem; color: var(--muted); background: var(--surface); padding: 4px 10px; border-radius: 50px; border: 1px solid var(--border); }

    /* ── PRODUCT GRID ── */
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(195px, 1fr)); gap: 16px; }

    /* ── PRODUCT CARD ── */
    .product-card {
      background: var(--surface); border: 1px solid var(--border);
      border-radius: var(--radius); overflow: hidden;
      display: flex; flex-direction: column;
      transition: transform var(--transition), border-color var(--transition), box-shadow var(--transition);
      animation: fadeUp .4s ease both;
    }
    .product-card:hover {
      transform: translateY(-4px);
      border-color: <?= rgba($c['primary'], 0.4) ?>;
      box-shadow: 0 16px 40px <?= rgba($c['bg'], 0.6) ?>, 0 0 0 1px <?= rgba($c['primary'], 0.1) ?>;
    }
    /* Contenedor de imagen con altura fija — evita desbordamiento con cualquier tamaño */
    .card-img-wrap {
      background: var(--surface2);
      width: 100%; height: 180px;
      display: flex; align-items: center; justify-content: center;
      overflow: hidden; position: relative;
      flex-shrink: 0; text-decoration: none;
    }
    .card-img-wrap img {
      width: 100%; height: 100%;
      object-fit: contain; object-position: center;
      padding: 12px; max-width: 100%; display: block;
      transition: transform var(--transition);
    }
    .product-card:hover .card-img-wrap img { transform: scale(1.05); }

    .card-body { padding: 14px; display: flex; flex-direction: column; flex: 1; gap: 5px; }
    .card-category { font-size: .68rem; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; font-weight: 600; }
    .card-name { font-size: .875rem; font-weight: 500; color: var(--text); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4em; }
    .card-price { font-family: var(--font-display); font-size: 1.2rem; font-weight: 700; color: var(--text); margin-top: auto; padding-top: 6px; }
    .card-price-sym { font-family: var(--font-body); font-size: .85rem; font-weight: 600; }
    .card-id-badge {
      display: block; text-align: center;
      background: var(--surface2); color: var(--muted);
      font-size: .75rem; font-weight: 600; font-family: var(--font-display);
      text-decoration: none; padding: 8px; border-radius: var(--radius-sm);
      margin-top: 8px; border: 1px solid var(--border);
      letter-spacing: .04em; transition: all var(--transition);
    }
    .card-id-badge:hover { background: <?= rgba($c['primary'], 0.1) ?>; color: var(--primary); border-color: <?= rgba($c['primary'], 0.3) ?>; }

    /* ── EMPTY ── */
    .empty-state { grid-column: 1/-1; text-align: center; padding: 80px 20px; color: var(--muted); }
    .empty-state-icon { font-size: 3rem; margin-bottom: 16px; opacity: .4; }
    .empty-state h3 { font-size: 1.1rem; font-weight: 600; margin-bottom: 8px; color: var(--text); }
    .empty-state a { color: var(--primary); }

    @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    <?php for ($i=1; $i<=12; $i++) echo ".product-card:nth-child($i){animation-delay:".($i*.04)."s}\n"; ?>

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) { .sidebar { display: none; } .catalog-area { padding-left: 0; } }
    @media (max-width: 640px) {
      .nav-search { max-width: none; flex: 1; }
      .product-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
      .card-img-wrap { height: 140px; }
      .card-body { padding: 10px; }
      .card-name { font-size: .8rem; }
      .card-price { font-size: 1rem; }
    }
    @media (max-width: 380px) {
      .card-img-wrap { height: 120px; }
    }

    /* ── paginate
     * ── */
    .pagination { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 40px 0 0; flex-wrap: wrap; }
    .page-btn {
      min-width: 38px; height: 38px; border-radius: var(--radius-sm);
      border: 1px solid var(--border); background: var(--surface);
      color: var(--muted); font-family: var(--font-display); font-size: .85rem; font-weight: 600;
      cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;
      padding: 0 10px; transition: all var(--transition);
    }
    .page-btn:hover { border-color: var(--primary); color: var(--primary); }
    .page-btn.active { background: var(--primary); border-color: var(--primary); color: #000; cursor: default; }
    .page-btn.disabled { opacity: .35; pointer-events: none; }
  </style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="nav-logo"><?= SITE_NAME ?></a>
  <?php if (FEATURE_SEARCH): ?>
  <div class="nav-search">
    <span class="nav-search-icon">⌕</span>
    <input type="text" id="searchInput" placeholder="Buscar productos..."
           value="<?= htmlspecialchars($search) ?>" autocomplete="off">
  </div>
  <?php endif; ?>
  <div class="nav-links">
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
  <a href="index.php">Todos los productos</a>
  <?php foreach ($categorias_bd as $cat): ?>
  <a href="index.php?cat=<?= urlencode($cat['nombre']) ?>"><?= htmlspecialchars($cat['nombre']) ?></a>
  <?php endforeach; ?>
  <?php foreach ($tags_bd as $tag): ?>
  <a href="index.php?tag=<?= urlencode($tag) ?>"># <?= htmlspecialchars($tag) ?></a>
  <?php endforeach; ?>
  <?php if (FEATURE_THEME_TOGGLE): ?>
  <a href="#" onclick="toggleTheme();return false;"><?= $c['name']==='dark'?'☀️ Modo claro':'🌙 Modo oscuro' ?></a>
  <?php endif; ?>
</div>

<?php if ($search || $categoria || $tag_fil): ?>
<div class="search-banner">
  <?php if ($search): ?>Resultados para: <strong>"<?= htmlspecialchars($search) ?>"</strong>
  <?php elseif ($categoria): ?>Categoría: <strong><?= htmlspecialchars($categoria) ?></strong>
  <?php elseif ($tag_fil): ?>Tag: <strong>#<?= htmlspecialchars($tag_fil) ?></strong><?php endif; ?>
  <a href="index.php">✕ Limpiar</a>
</div>
<?php else: ?>
<section class="hero">
  <h1><?= BANNER_TITLE ?></h1>
  <p><?= SITE_DESCRIPTION ?></p>
  <div class="hero-badge"><span>✦</span> <?= BANNER_SUBTITLE ?></div>
</section>
<?php endif; ?>

<div class="main-layout">
  <aside class="sidebar">
    <p class="sidebar-label">Categorías</p>
    <ul class="filter-list">
      <li class="filter-item"><a href="index.php" class="<?= (!$categoria&&!$tag_fil)?'active':'' ?>">Todos</a></li>
      <?php foreach ($categorias_bd as $cat): ?>
      <li class="filter-item">
        <a href="index.php?cat=<?= urlencode($cat['nombre']) ?>" class="<?= $categoria===$cat['nombre']?'active':'' ?>">
          <?= htmlspecialchars($cat['nombre']) ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <!--
    <?php if (!empty($tags_bd)): ?>
    <p class="sidebar-label" style="margin-top:20px">Tags</p>
    <ul class="filter-list">
      <?php foreach ($tags_bd as $tag): ?>
      <li class="filter-item">
        <a href="index.php?tag=<?= urlencode($tag) ?>" class="<?= $tag_fil===$tag?'active':'' ?>">
          # <?= htmlspecialchars($tag) ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    -->
  </aside>

  <main class="catalog-area">
    <div class="catalog-header">
      <h2 class="catalog-title"><?= CATALOG_SECTION ?></h2>
      <span class="catalog-count"><?= $total_prod ?> productos</span>
    </div>
    <div class="product-grid">
      <?php
      $count = 0;
      while ($row = mysqli_fetch_assoc($resultado)):
        $count++;
        $base = __DIR__.'/';
        $img = UPLOADS_DIR.$row['id'].'/1.jpg';
        if (!file_exists($base.$img)) $img = UPLOADS_DIR.$row['id'].'_1.jpg';
        if (!file_exists($base.$img)) $img = UPLOADS_DIR.$row['id'].'.jpg';
        if (!file_exists($base.$img)) $img = PLACEHOLDER_IMG;
      ?>
      <article class="product-card">
        <a href="article.php?id=<?= $row['id'] ?>" class="card-img-wrap">
          <img src="<?= $img ?>" alt="<?= htmlspecialchars($row['nombre']) ?>" loading="lazy" decoding="async">
        </a>
        <div class="card-body">
          <?php if ($row['categoria']): ?>
          <span class="card-category"><?= htmlspecialchars($row['categoria']) ?></span>
          <?php endif; ?>
          <h3 class="card-name"><?= htmlspecialchars($row['nombre']) ?></h3>
          <div class="card-price"><span class="card-price-sym">&#36;</span><?= formatPrice($row['precio']) ?></div>
          <a href="article.php?id=<?= $row['id'] ?>" class="card-id-badge">
            ID <?= htmlspecialchars($row['id']) ?>
          </a>
        </div>
      </article>
      <?php endwhile; ?>
      <?php if ($count === 0): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🔍</div>
        <h3>Sin resultados</h3>
        <p>Intenta con otro término o <a href="index.php">ve todos los productos</a>.</p>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($total_pags > 1):
      // Build base URL preserving filters
      $qp = [];
      if ($search)    $qp[] = 'q='   . urlencode($search);
      if ($categoria) $qp[] = 'cat=' . urlencode($categoria);
      if ($tag_fil)   $qp[] = 'tag=' . urlencode($tag_fil);
      $base_url = 'index.php' . ($qp ? '?' . implode('&', $qp) . '&' : '?');
    ?>
    <div class="pagination">
      <a href="<?= $base_url ?>pag=<?= max(1,$pagina-1) ?>" class="page-btn <?= $pagina<=1?'disabled':'' ?>">‹</a>
      <?php
        $start = max(1, $pagina - 2);
        $end   = min($total_pags, $pagina + 2);
        if ($start > 1) { echo '<a href="'.$base_url.'pag=1" class="page-btn">1</a>'; if ($start > 2) echo '<span class="page-btn disabled">…</span>'; }
        for ($p = $start; $p <= $end; $p++):
      ?>
      <a href="<?= $base_url ?>pag=<?= $p ?>" class="page-btn <?= $p===$pagina?'active':'' ?>"><?= $p ?></a>
      <?php endfor;
        if ($end < $total_pags) { if ($end < $total_pags-1) echo '<span class="page-btn disabled">…</span>'; echo '<a href="'.$base_url.'pag='.$total_pags.'" class="page-btn">'.$total_pags.'</a>'; }
      ?>
      <a href="<?= $base_url ?>pag=<?= min($total_pags,$pagina+1) ?>" class="page-btn <?= $pagina>=$total_pags?'disabled':'' ?>">›</a>
    </div>
    <?php endif; ?>

  </main>
</div>

<footer><p><?= FOOTER_TEXT ?></p></footer>

<?php include 'theme_js.php'; ?>
<script>
  let debounceTimer;
  const si = document.getElementById('searchInput');
  if (si) {
    si.addEventListener('input', function() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        const q = this.value.trim();
        window.location.href = q ? `index.php?q=${encodeURIComponent(q)}` : 'index.php';
      }, 450);
    });
  }
</script>
</body>
</html>
