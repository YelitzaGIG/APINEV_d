/* =============================================================
   public/js/dashboard.js — Lógica del sidebar (sección activa)
   ============================================================= */

document.addEventListener('DOMContentLoaded', () => {
  // Marcar como activo el enlace que corresponde a la página actual
  const current = window.location.pathname.split('/').pop();

  document.querySelectorAll('.sidebar nav a').forEach(link => {
    const href = link.getAttribute('href').split('/').pop();
    if (href === current) link.classList.add('active');
  });
});
