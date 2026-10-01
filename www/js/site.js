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

  document.querySelectorAll('[data-project-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-project-track]');
    const controls = carousel.parentElement?.querySelector('[data-carousel-controls]');
    const previous = controls?.querySelector('[data-carousel-prev]');
    const next = controls?.querySelector('[data-carousel-next]');
    if (!track || !controls || !previous || !next) return;

    const step = () => {
      const card = track.querySelector('.project-card');
      if (!card) return 0;
      return card.getBoundingClientRect().width + Number.parseFloat(window.getComputedStyle(track).columnGap || '0');
    };
    const updateControls = () => {
      const maxScroll = track.scrollWidth - track.clientWidth;
      controls.hidden = maxScroll <= 1;
      previous.disabled = track.scrollLeft <= 4;
      next.disabled = track.scrollLeft >= maxScroll - 4;
    };
    const scroll = (direction) => track.scrollBy({
      left: step() * direction,
      behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
    });

    let scrollFrame = 0;
    previous.addEventListener('click', () => scroll(-1));
    next.addEventListener('click', () => scroll(1));
    track.addEventListener('scroll', () => {
      if (scrollFrame) return;
      scrollFrame = window.requestAnimationFrame(() => {
        scrollFrame = 0;
        updateControls();
      });
    }, { passive: true });
    window.addEventListener('resize', updateControls, { passive: true });
    updateControls();
  });

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
