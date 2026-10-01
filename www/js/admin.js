(() => {
  document.querySelectorAll('[data-preview-target]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files?.[0];
      const preview = document.getElementById(input.dataset.previewTarget);
      if (!file || !preview) return;
      if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
      preview.dataset.objectUrl = URL.createObjectURL(file);
      preview.src = preview.dataset.objectUrl;
    });
  });

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
    let touchPointerId = null;
    const cards = () => Array.from(sortable.querySelectorAll('[data-sort-item]'));
    const syncOrder = () => { orderField.value = cards().map((card) => card.dataset.sortItem).join(','); };
    const moveCard = (target, clientY) => {
      if (!dragged || !target || dragged === target) return;
      const box = target.getBoundingClientRect();
      const after = clientY > box.top + box.height / 2;
      sortable.insertBefore(dragged, after ? target.nextSibling : target);
    };

    sortable.addEventListener('pointerdown', (event) => {
      if (event.pointerType !== 'touch' || !event.target.closest('.gallery-sort-card__handle')) return;
      dragged = event.target.closest('[data-sort-item]');
      if (!dragged) return;
      touchPointerId = event.pointerId;
      dragged.classList.add('is-dragging');
      event.preventDefault();
    });
    sortable.addEventListener('pointermove', (event) => {
      if (event.pointerId !== touchPointerId || !dragged) return;
      moveCard(document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-sort-item]'), event.clientY);
    });
    const finishTouchDrag = (event) => {
      if (event.pointerId !== touchPointerId) return;
      dragged?.classList.remove('is-dragging');
      dragged = null;
      touchPointerId = null;
      syncOrder();
    };
    sortable.addEventListener('pointerup', finishTouchDrag);
    sortable.addEventListener('pointercancel', finishTouchDrag);

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
      moveCard(event.target.closest('[data-sort-item]'), event.clientY);
    });
    sortable.addEventListener('drop', (event) => { event.preventDefault(); syncOrder(); });
  }
})();
