(() => {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 顶部阅读进度条
  const progress = document.createElement('div');
  progress.className = 'ref-progress';
  progress.setAttribute('aria-hidden', 'true');
  document.body.appendChild(progress);

  // 标题逐行浮现（与原版一致：标题内文字合并为一行动画）
  document.querySelectorAll('.hero__title, .section__title, .home-hero__title').forEach((heading) => {
    if (heading.dataset.motionSplit === 'true') return;
    heading.dataset.motionSplit = 'true';
    const text = heading.textContent.replace(/\s+/g, ' ').trim();
    heading.textContent = '';
    const line = document.createElement('span');
    line.className = 'motion-line fade-in';
    const inner = document.createElement('span');
    inner.textContent = text;
    line.appendChild(inner);
    heading.appendChild(line);
  });

  const revealItems = document.querySelectorAll('.fade-in, .motion-line');
  if (!('IntersectionObserver' in window) || reducedMotion) {
    revealItems.forEach((item) => item.classList.add('is-visible'));
  } else if (revealItems.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    revealItems.forEach((item, index) => {
      item.style.setProperty('--reveal-delay', `${Math.min(index % 5, 4) * 70}ms`);
      observer.observe(item);
    });
  }

  // 视差：只处理当前在视口内的元素，并用 requestAnimationFrame 合并每帧的滚动事件
  const parallaxItems = reducedMotion ? [] : [...document.querySelectorAll('.ph-img, .project-stage img')];
  const visibleParallax = new Set();
  if (parallaxItems.length && 'IntersectionObserver' in window) {
    const parallaxObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) visibleParallax.add(entry.target);
        else visibleParallax.delete(entry.target);
      });
      requestFrame();
    });
    parallaxItems.forEach((item) => {
      item.style.willChange = 'transform';
      parallaxObserver.observe(item);
    });
  }

  let ticking = false;
  let viewportHeight = window.innerHeight;
  let scrollable = 1;

  const measure = () => {
    viewportHeight = window.innerHeight;
    scrollable = Math.max(1, document.documentElement.scrollHeight - viewportHeight);
  };

  const update = () => {
    ticking = false;
    const ratio = Math.min(1, Math.max(0, window.scrollY / scrollable));
    progress.style.transform = `scaleX(${ratio})`;
    visibleParallax.forEach((item) => {
      const rect = item.getBoundingClientRect();
      const offset = ((rect.top + rect.height / 2) - viewportHeight / 2) / viewportHeight;
      item.style.transform = `translate3d(0, ${(offset * -18).toFixed(2)}px, 0)`;
    });
  };

  function requestFrame() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(update);
  }

  window.addEventListener('scroll', requestFrame, { passive: true });
  window.addEventListener('resize', () => {
    measure();
    requestFrame();
  }, { passive: true });
  window.addEventListener('load', () => {
    measure();
    requestFrame();
  });
  measure();
  requestFrame();
})();
