<?php
// theme_styles.php — estilos base compartidos
// Requiere: $c = themeColors() ya definido, y la función rgba() de config.env.php
?>
    :root {
      --primary:      <?= $c['primary'] ?>;
      --primary-dark: <?= $c['primary_dark'] ?>;
      --bg:           <?= $c['bg'] ?>;
      --surface:      <?= $c['surface'] ?>;
      --surface2:     <?= $c['surface2'] ?>;
      --text:         <?= $c['text'] ?>;
      --muted:        <?= $c['muted'] ?>;
      --border:       <?= $c['border'] ?>;
      --accent:       <?= $c['accent'] ?>;
      --font-display: '<?= FONT_DISPLAY ?>', sans-serif;
      --font-body:    '<?= FONT_BODY ?>', sans-serif;
      --radius:       14px;
      --radius-sm:    8px;
      --transition:   0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--font-body);
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      -webkit-font-smoothing: antialiased;
      transition: background 0.3s ease, color 0.3s ease;
    }

    /* ── NAVBAR ── */
    .navbar {
      position: sticky; top: 0; z-index: 100;
      background: <?= rgba($c['bg'], 0.88) ?>;
      backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border);
      padding: 0 clamp(16px,4vw,48px); height: 64px;
      display: flex; align-items: center; gap: 20px;
    }
    .nav-logo {
      font-family: var(--font-display); font-size: 1.35rem; font-weight: 800;
      color: var(--primary); text-decoration: none; letter-spacing: -0.02em;
    }
    .nav-link {
      color: var(--muted); text-decoration: none; font-size: 0.85rem; font-weight: 500;
      padding: 7px 14px; border-radius: 50px; border: 1px solid transparent;
      transition: all var(--transition); white-space: nowrap;
    }
    .nav-link:hover { color: var(--text); background: var(--surface); border-color: var(--border); }
    .nav-links { display: flex; align-items: center; gap: 8px; margin-left: auto; }
    .nav-spacer { flex: 1; }

    /* ── THEME TOGGLE ── */
    .theme-toggle {
      display: flex; align-items: center; gap: 8px;
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 50px; padding: 5px 12px 5px 6px;
      cursor: pointer; transition: all var(--transition); flex-shrink: 0;
    }
    .theme-toggle:hover { border-color: var(--primary); }
    .toggle-track {
      width: 34px; height: 18px; border-radius: 50px;
      background: var(--border); position: relative;
      transition: background 0.3s ease; flex-shrink: 0;
    }
    .toggle-thumb {
      position: absolute; top: 2px; left: 2px;
      width: 14px; height: 14px; border-radius: 50%;
      background: var(--muted);
      transition: transform 0.3s ease, background 0.3s ease;
    }
    <?php if ($c['name'] === 'light'): ?>
    .toggle-track { background: var(--primary); }
    .toggle-thumb { background: #fff; transform: translateX(16px); }
    <?php endif; ?>
    .toggle-label { font-size: 0.78rem; color: var(--muted); font-weight: 500; user-select: none; }

    /* ── FOOTER ── */
    footer {
      border-top: 1px solid var(--border);
      padding: 24px clamp(16px,4vw,48px);
      text-align: center; color: var(--muted); font-size: 0.8rem;
    }

    /* ── MOBILE NAV ── */
    .nav-menu-btn {
      display: none; background: var(--surface); border: 1px solid var(--border);
      border-radius: var(--radius-sm); color: var(--text);
      padding: 8px 12px; cursor: pointer; font-size: 1.1rem;
      margin-left: auto;
    }
    .mobile-nav {
      display: flex; flex-direction: column;
      position: fixed; inset: 0;
      background: <?= rgba($c['bg'], 0.97) ?>;
      backdrop-filter: blur(20px);
      z-index: 200; padding: 80px 32px 40px; gap: 12px;
      transform: translateX(100%);
      transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .mobile-nav.open { transform: translateX(0); }
    .mobile-nav a {
      color: var(--text); text-decoration: none;
      font-size: 1.1rem; font-weight: 500;
      padding: 12px 0; border-bottom: 1px solid var(--border);
    }
    .mobile-nav-close {
      position: absolute; top: 20px; right: 20px;
      background: var(--surface); border: 1px solid var(--border);
      border-radius: var(--radius-sm); color: var(--text);
      padding: 8px 14px; cursor: pointer; font-size: 1rem;
    }

    @media (max-width: 640px) {
      .nav-links { display: none; }
      .nav-menu-btn { display: block; }
    }
