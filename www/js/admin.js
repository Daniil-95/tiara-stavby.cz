(() => {
  const bindPreview = (input) => {
    input.addEventListener('change', () => {
      const file = input.files?.[0];
      if (!file) return;
      const field = input.closest('.editor-field');
      let preview = input.dataset.previewTarget ? document.getElementById(input.dataset.previewTarget) : field?.querySelector('img');
      if (!preview && field) {
        preview = document.createElement('img');
        preview.className = 'admin-current-image';
        preview.alt = 'Náhled nové fotografie';
        input.after(preview);
      }
      if (!preview) return;
      if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
      preview.dataset.objectUrl = URL.createObjectURL(file);
      preview.src = preview.dataset.objectUrl;
    });
  };
  document.querySelectorAll('input[type=file]').forEach(bindPreview);

  const seoSelect = document.querySelector('select[name=seo_path]');
  if (seoSelect) {
    const form = seoSelect.form;
    let dirty = false;
    form.addEventListener('input', () => { dirty = true; });
    seoSelect.addEventListener('change', () => {
      if (dirty && !window.confirm('Neuložené změny budou ztraceny. Pokračovat?')) {
        seoSelect.value = new URLSearchParams(location.search).get('path') || '/';
        return;
      }
      location.href = '/admin/editor/seo?path=' + encodeURIComponent(seoSelect.value);
    });
  }

  const button = document.querySelector('.admin-menu-toggle');
  const sidebar = document.querySelector('.admin-sidebar');
  const backdrop = document.querySelector('.admin-backdrop');
  if (!button || !sidebar) return;
  const setOpen = (open) => {
    button.setAttribute('aria-expanded', String(open));
    sidebar.classList.toggle('is-open', open);
    document.body.classList.toggle('admin-menu-open', open);
    if (backdrop) backdrop.hidden = !open;
  };
  button.addEventListener('click', () => setOpen(button.getAttribute('aria-expanded') !== 'true'));
  backdrop?.addEventListener('click', () => setOpen(false));
  sidebar.addEventListener('click', (event) => { if (event.target.closest('a')) setOpen(false); });
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') setOpen(false); });
})();
