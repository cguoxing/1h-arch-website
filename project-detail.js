(() => {
  const config = window.oneHConfig || {};
  const images = Array.isArray(config.projectImages) ? config.projectImages : [];
  const projectTitle = config.projectTitle || 'Project';
  const preloaded = new Set();
  const preload = (index) => {
    const image = images[(index + images.length) % images.length];
    if (!image || !image.src || preloaded.has(image.src)) return;
    preloaded.add(image.src);
    const img = new Image();
    img.decoding = 'async';
    img.src = image.src;
  };
  const projectImage = document.querySelector('[data-project-image]');
  const projectImageCount = document.querySelector('[data-project-image-count]');
  const projectImageCaption = document.querySelector('[data-project-image-caption]');
  let activeImageIndex = 0;
  let interacted = false;

  const renderProjectImage = () => {
    if (!projectImage || !images.length) return;
    const image = images[activeImageIndex] || images[0];
    const src = image.src || '';
    const caption = image.caption || projectTitle;
    if (projectImage.getAttribute('src') !== src) {
      projectImage.classList.add('is-switching');
      const swap = () => {
        projectImage.src = src;
        projectImage.classList.remove('is-switching');
      };
      const next = new Image();
      next.onload = swap;
      next.onerror = swap;
      next.src = src;
    }
    projectImage.alt = `${projectTitle}: ${caption}`;
    if (projectImageCount) {
      projectImageCount.textContent = `${String(activeImageIndex + 1).padStart(2, '0')} / ${String(images.length).padStart(2, '0')}`;
    }
    if (projectImageCaption) {
      projectImageCaption.textContent = caption;
    }
    // 预加载下一张（用户翻页过之后再预加载上一张），翻页无等待
    preload(activeImageIndex + 1);
    if (interacted) preload(activeImageIndex - 1);
  };

  document.querySelector('[data-project-previous]')?.addEventListener('click', () => {
    interacted = true;
    activeImageIndex = (activeImageIndex - 1 + images.length) % images.length;
    renderProjectImage();
  });

  document.querySelector('[data-project-next]')?.addEventListener('click', () => {
    interacted = true;
    activeImageIndex = (activeImageIndex + 1) % images.length;
    renderProjectImage();
  });

  // 键盘左右键 / 触屏左右滑动切换
  document.addEventListener('keydown', (event) => {
    if (images.length < 2 || event.target.closest('input, textarea, select')) return;
    if (event.key === 'ArrowLeft') document.querySelector('[data-project-previous]')?.click();
    if (event.key === 'ArrowRight') document.querySelector('[data-project-next]')?.click();
  });
  let touchX = null;
  projectImage?.addEventListener('touchstart', (event) => { touchX = event.touches[0].clientX; }, { passive: true });
  projectImage?.addEventListener('touchend', (event) => {
    if (touchX === null || images.length < 2) return;
    const dx = event.changedTouches[0].clientX - touchX;
    touchX = null;
    if (Math.abs(dx) < 40) return;
    document.querySelector(dx > 0 ? '[data-project-previous]' : '[data-project-next]')?.click();
  }, { passive: true });

  renderProjectImage();
})();
