<script>
// ── Theme toggle ──
function toggleTheme() {
  const html  = document.documentElement;
  const isDark = html.getAttribute('data-theme') === 'dark';
  const next   = isDark ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  document.cookie = 'theme=' + next + ';path=/;max-age=31536000';
  // Update toggle label if present
  document.querySelectorAll('.toggle-label').forEach(el => {
    el.textContent = next === 'dark' ? '🌙' : '☀️';
  });
  location.reload();
}

// ── Mobile nav ──
function toggleMobileNav() {
  const nav = document.getElementById('mobileNav');
  if (!nav) return;
  nav.classList.toggle('open');
  document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
}

// Close mobile nav on outside click
document.addEventListener('click', function(e) {
  const nav = document.getElementById('mobileNav');
  const btn = document.querySelector('.nav-menu-btn');
  if (nav && nav.classList.contains('open') && !nav.contains(e.target) && e.target !== btn) {
    nav.classList.remove('open');
    document.body.style.overflow = '';
  }
});
</script>
