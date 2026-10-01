(() => {
  const menuButton = document.querySelector('.menu-toggle');
  const navigation = document.querySelector('.main-nav');
  const header = document.querySelector('[data-header]');
  let menuScrollPosition = 0;

  if (menuButton && navigation) {
    const closeMenu = () => {
      menuButton.setAttribute('aria-expanded', 'false');
      navigation.classList.remove('is-open');
      document.body.classList.remove('menu-open');
      header?.style.removeProperty('--menu-header-top');
      window.requestAnimationFrame(() => window.scrollTo({ top: menuScrollPosition, behavior: 'instant' }));
    };

    menuButton.addEventListener('click', () => {
      const expanded = menuButton.getAttribute('aria-expanded') === 'true';
      if (expanded) {
        closeMenu();
        return;
      }
      menuScrollPosition = window.scrollY;
      header?.style.setProperty('--menu-header-top', `${header.getBoundingClientRect().top}px`);
      menuButton.setAttribute('aria-expanded', 'true');
      navigation.classList.add('is-open');
      document.body.classList.add('menu-open');
    });
    navigation.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
      if (menuButton.getAttribute('aria-expanded') === 'true') closeMenu();
    }));
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true') {
        closeMenu();
        menuButton.focus();
      }
    });
  }

  const updateHeader = () => header?.classList.toggle('is-scrolled', window.scrollY > 30);
  window.addEventListener('scroll', updateHeader, { passive: true });
  updateHeader();

  const revealItems = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    }), { threshold: 0.14 });
    revealItems.forEach((item) => observer.observe(item));
  } else revealItems.forEach((item) => item.classList.add('is-visible'));

  document.querySelectorAll('[data-lightbox]').forEach((anchor) => anchor.addEventListener('click', (event) => {
    event.preventDefault();
    const dialog = document.createElement('dialog');
    dialog.className = 'image-dialog';
    const image = document.createElement('img');
    image.src = anchor.href;
    image.alt = anchor.querySelector('img')?.alt || '';
    const close = document.createElement('button');
    close.type = 'button'; close.setAttribute('aria-label', 'Close image'); close.innerHTML = '&times;';
    close.addEventListener('click', () => dialog.close());
    dialog.append(close, image);
    dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
    dialog.addEventListener('close', () => dialog.remove());
    document.body.append(dialog); dialog.showModal();
  }));

  document.querySelectorAll('form').forEach((form) => form.addEventListener('submit', (event) => {
    if (form.classList.contains('contact-form') && !form.reportValidity()) event.preventDefault();
  }));
})();
