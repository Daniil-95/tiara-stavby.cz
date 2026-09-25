(() => {
  const button = document.querySelector('.admin-menu-toggle');
  const sidebar = document.querySelector('.admin-sidebar');
  if (!button || !sidebar) return;
  button.addEventListener('click', () => {
    const opened = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', String(!opened));
    sidebar.classList.toggle('is-open', !opened);
  });
})();
