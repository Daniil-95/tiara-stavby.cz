(() => {
  const button = document.querySelector('.admin-menu-toggle');
  const sidebar = document.querySelector('.admin-sidebar');
  if (!button || !sidebar) return;
  button.addEventListener('click', () => {
    const opened = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', String(!opened));
    sidebar.classList.toggle('is-open', !opened);
  });

  const sortable = document.querySelector('[data-sortable-gallery]');
  const orderField = document.querySelector('input[name="order"]');
  if (sortable && orderField) {
    let dragged = null;
    const cards = () => Array.from(sortable.querySelectorAll('[data-sort-item]'));
    const syncOrder = () => { orderField.value = cards().map((card) => card.dataset.sortItem).join(','); };
    sortable.addEventListener('dragstart', (event) => {
      dragged = event.target.closest('[data-sort-item]');
      if (!dragged) return;
      dragged.classList.add('is-dragging');
      event.dataTransfer.effectAllowed = 'move';
    });
    sortable.addEventListener('dragend', () => {
      dragged?.classList.remove('is-dragging');
      dragged = null;
      syncOrder();
    });
    sortable.addEventListener('dragover', (event) => {
      event.preventDefault();
      const target = event.target.closest('[data-sort-item]');
      if (!dragged || !target || dragged === target) return;
      const box = target.getBoundingClientRect();
      const after = event.clientY > box.top + box.height / 2;
      sortable.insertBefore(dragged, after ? target.nextSibling : target);
    });
    sortable.addEventListener('drop', (event) => { event.preventDefault(); syncOrder(); });
  }
})();
