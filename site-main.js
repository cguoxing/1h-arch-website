(() => {
const body = document.body;
const navToggle = document.querySelector('[data-nav-toggle]');
const navLinks = document.querySelector('[data-nav-links]');
const yearTargets = document.querySelectorAll('span[data-year]');

for (const node of yearTargets) {
  node.textContent = new Date().getFullYear();
}

const adminConfig = window.oneHConfig || { projectsPage: {} };
if (navToggle && navLinks) {
  const closeNav = () => {
    body.classList.remove('nav-open');
    navToggle.setAttribute('aria-expanded', 'false');
  };

  navToggle.addEventListener('click', () => {
    const isOpen = body.classList.toggle('nav-open');
    navToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navLinks.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeNav);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeNav();
    }
  });
}

// 滚动渐显统一由 motion-reference.js 处理（避免两个 IntersectionObserver 重复监听）。

const heroCarousel = document.querySelector('[data-hero-carousel]');

if (heroCarousel) {
  const slides = [...heroCarousel.querySelectorAll('[data-hero-slide]')];
  const titleNode = heroCarousel.querySelector('[data-hero-title]');
  const metaNode = heroCarousel.querySelector('[data-hero-meta]');
  const counterNode = heroCarousel.querySelector('[data-hero-counter]');
  const thumbButtons = [...heroCarousel.querySelectorAll('[data-hero-thumb]')];
  const prevButton = heroCarousel.querySelector('[data-hero-prev]');
  const nextButton = heroCarousel.querySelector('[data-hero-next]');
  const pauseButton = heroCarousel.querySelector('[data-hero-pause]');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let index = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
  let timer = null;
  let paused = reducedMotion;

  const sync = (nextIndex) => {
    if (!slides.length) {
      return;
    }

    index = (nextIndex + slides.length) % slides.length;

    slides.forEach((slide, slideIndex) => {
      const isActive = slideIndex === index;
      slide.classList.toggle('is-active', isActive);
      slide.setAttribute('aria-hidden', String(!isActive));
    });

    thumbButtons.forEach((button, buttonIndex) => {
      const isActive = buttonIndex === index;
      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-pressed', String(isActive));
    });

    const activeSlide = slides[index];
    if (titleNode) {
      titleNode.textContent = activeSlide?.dataset.title || '';
    }
    if (metaNode) {
      metaNode.textContent = activeSlide?.dataset.meta || '';
    }
    if (counterNode) {
      counterNode.textContent = activeSlide?.dataset.label || '';
    }
  };

  const stop = () => {
    if (timer) {
      window.clearInterval(timer);
      timer = null;
    }
  };

  const start = () => {
    if (paused || slides.length < 2 || timer) {
      return;
    }

    timer = window.setInterval(() => {
      sync(index + 1);
    }, 6200);
  };

  const setPaused = (nextPaused) => {
    paused = nextPaused;
    if (pauseButton) {
      pauseButton.textContent = paused ? 'Play carousel' : 'Pause carousel';
      pauseButton.setAttribute('aria-label', paused ? 'Play carousel' : 'Pause carousel');
    }

    if (paused) {
      stop();
    } else {
      start();
    }
  };

  sync(index);
  setPaused(paused);

  thumbButtons.forEach((button) => {
    button.addEventListener('click', () => {
      sync(Number(button.dataset.heroThumb || 0));
      if (!paused) {
        stop();
        start();
      }
    });
  });

  prevButton?.addEventListener('click', () => {
    sync(index - 1);
    if (!paused) {
      stop();
      start();
    }
  });

  nextButton?.addEventListener('click', () => {
    sync(index + 1);
    if (!paused) {
      stop();
      start();
    }
  });

  pauseButton?.addEventListener('click', () => {
    setPaused(!paused);
  });

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stop();
    } else if (!paused) {
      start();
    }
  });
}

const projectBoard = document.querySelector('[data-board-item]');

if (projectBoard) {
  const boardItems = [...document.querySelectorAll('[data-board-item]')];
  const boardTag = document.querySelector('[data-board-tag]');
  const boardTitle = document.querySelector('[data-board-title]');
  const boardCopy = document.querySelector('[data-board-copy]');
  const boardFacts = document.querySelector('.project-board__facts');
  const boardScale = document.querySelector('[data-board-fact-scale]');
  const boardRole = document.querySelector('[data-board-fact-role]');
  const boardStage = document.querySelector('.project-board__stage');
  const boardPoint = document.querySelector('[data-board-point]');
  const boardImage = document.querySelector('[data-board-image]');
  let activeBoardIndex = Math.max(0, boardItems.findIndex((button) => button.classList.contains('is-active')));

  const selectBoardItem = (item) => {
    if (!item || !boardTag || !boardTitle || !boardCopy || !boardFacts) {
      return;
    }

    activeBoardIndex = boardItems.indexOf(item);

    boardItems.forEach((button) => {
      const active = button === item;
      button.classList.toggle('is-active', active);
      if (active) {
        button.setAttribute('aria-current', 'true');
      } else {
        button.removeAttribute('aria-current');
      }
    });

    boardTag.textContent = item.dataset.boardTag || '';
    boardTitle.textContent = item.dataset.boardTitle || '';
    boardCopy.textContent = item.dataset.boardCopy || '';
    if (boardPoint) {
      boardPoint.setAttribute('aria-label', `${item.dataset.boardTitle || 'Project'} point`);
    }

    if (boardImage && item.dataset.boardImage) {
      // 主图带 srcset 时只改 src 不生效，先移除响应式属性再切换
      boardImage.removeAttribute('srcset');
      boardImage.removeAttribute('sizes');
      boardImage.src = item.dataset.boardImage;
      boardImage.alt = item.dataset.boardAlt || `${item.dataset.boardTitle || 'Project'} visual`;
    }

    if (boardStage) {
      const phase = Number(item.dataset.boardPhase || 0);
      boardStage.style.setProperty('--board-orbit-delay', `${phase * -3.5}s`);
    }

    if (boardScale) boardScale.textContent = item.dataset.boardScale || '—';
    if (boardRole) boardRole.textContent = item.dataset.boardRole || '—';
  };

  selectBoardItem(boardItems[0]);

  boardPoint?.addEventListener('click', () => {
    if (!boardItems.length) {
      return;
    }

    const activeItem = boardItems[activeBoardIndex] || boardItems[0];
    if (activeItem?.href) {
      window.location.assign(activeItem.href);
    }
  });

  boardItems.forEach((button) => {
    button.addEventListener('mouseenter', () => selectBoardItem(button));
    button.addEventListener('focus', () => selectBoardItem(button));
  });
}

const filterButtons = document.querySelectorAll('[data-filter-button]');
const projectCards = document.querySelectorAll('[data-project-card]');
const projectGrid = document.querySelector('.project-grid');
const periodButtons = document.querySelectorAll('[data-period-button]');
const detailTitle = document.querySelector('[data-project-detail-title]');
const detailText = document.querySelector('[data-project-detail-text]');
const detailFacts = document.querySelector('[data-project-detail-facts]');
const detailBadge = document.querySelector('[data-project-detail-badge]');
const detailImage = document.querySelector('[data-project-detail-image]');
const filterSummary = document.querySelector('[data-project-filter-summary]');
const filterCount = document.querySelector('[data-project-filter-count]');
const projectSearchInput = document.querySelector('[data-project-search]');
const projectSearchButton = document.querySelector('[data-project-search-button]');

const projectPeriodLabels = {
  'all-timeframes': 'All timeframes',
  '2009-2015': '2009-2015',
  '2016-2020': '2016-2020',
  '2021-2025': '2021-2025',
  '2026-present': '2026 - Present'
};

let activeProjectFilter = 'all';
let activeProjectPeriod = adminConfig.projectsPage?.defaultYearFilter || 'all-timeframes';
let activeProjectSearch = '';

const projectPeriodRange = (period) => {
  if (period === '2009-2015') return [2009, 2015];
  if (period === '2016-2020') return [2016, 2020];
  if (period === '2021-2025') return [2021, 2025];
  if (period === '2026-present') return [2026, Infinity];
  return null;
};

const currentProjectCards = () => projectGrid
  ? [...projectGrid.querySelectorAll('[data-project-card]')]
  : [...projectCards];

const sortProjectCardList = () => {
  if (!projectGrid || !projectCards.length) return;
  [...projectCards]
    .sort((a, b) => {
      const yearDiff = Number(b.dataset.year || 0) - Number(a.dataset.year || 0);
      if (yearDiff !== 0) return yearDiff;
      return (a.dataset.title || '').localeCompare(b.dataset.title || '', 'zh-Hans-CN');
    })
    .forEach((card) => projectGrid.appendChild(card));
};

const syncProjectCardMeta = () => {
  projectCards.forEach((card) => {
    const meta = card.querySelector('.project-card__meta');
    if (!meta) return;
    const parts = [card.dataset.year || '', card.dataset.title || ''].filter(Boolean);
    meta.textContent = parts.join(' · ');
  });
};

const setProjectDetail = (project, card) => {
  if (!project || !detailTitle || !detailText || !detailFacts || !detailBadge || !detailImage) {
    return;
  }

  projectCards.forEach((projectCard) => projectCard.classList.toggle('is-active', projectCard === card));

  detailBadge.textContent = project.categoryLabel || project.category || '';
  detailTitle.textContent = project.title || '';
  detailText.textContent = project.summary || '';
  if (project.image && detailImage.getAttribute('src') !== project.image) {
    detailImage.src = project.image;
  }
  detailImage.alt = `${project.title || 'Project'} project visual`;

  detailFacts.innerHTML = '';
  [
    ['Location', project.city || '—'],
    ['Year', project.year || '—'],
    ['Area', project.area || '—'],
    ['Role', project.role || '—']
  ].forEach(([key, value]) => {
    const row = document.createElement('div');
    row.className = 'fact';
    const keyNode = document.createElement('span');
    keyNode.className = 'fact__key';
    keyNode.textContent = key;
    const valueNode = document.createElement('span');
    valueNode.className = 'fact__value';
    valueNode.textContent = value;
    row.append(keyNode, valueNode);
    detailFacts.appendChild(row);
  });
};

const clearProjectDetailSelection = () => {
  projectCards.forEach((projectCard) => projectCard.classList.remove('is-active'));
  if (detailBadge) detailBadge.textContent = 'No matching project';
  if (detailTitle) detailTitle.textContent = 'No projects found';
  if (detailText) detailText.textContent = 'Adjust the type, timeframe, or search term to refresh the project list.';
  if (detailImage) {
    detailImage.removeAttribute('src');
    detailImage.alt = '';
  }
  if (detailFacts) detailFacts.innerHTML = '';
};

const selectProject = (card) => {
  if (!card) {
    return;
  }

  setProjectDetail({
    categoryLabel: card.dataset.categoryLabel,
    title: card.dataset.title,
    summary: card.dataset.summary,
    city: card.dataset.city,
    year: card.dataset.year,
    area: card.dataset.area,
    role: card.dataset.role,
    image: card.dataset.image
  }, card);
};

const updateProjectArchiveStatus = () => {
  const activeButton = [...filterButtons].find((button) => button.dataset.filterButton === activeProjectFilter);
  const visibleCards = currentProjectCards().filter((card) => card.dataset.match !== '0');
  const selectedLabel = activeButton?.textContent.trim() || 'All Projects';

  if (filterSummary) {
    const searchLabel = activeProjectSearch ? ` · Search: ${activeProjectSearch}` : '';
    filterSummary.textContent = `${selectedLabel} · ${projectPeriodLabels[activeProjectPeriod]}${searchLabel}`;
  }
  if (filterCount) {
    filterCount.textContent = `${visibleCards.length} ${visibleCards.length === 1 ? 'project' : 'projects'}`;
  }
};

if (projectCards.length && detailTitle) {
  sortProjectCardList();
  syncProjectCardMeta();
  selectProject(currentProjectCards().find((card) => !card.hidden) || currentProjectCards()[0]);
  updateProjectArchiveStatus();
  projectCards.forEach((card) => {
    const openProject = () => {
      const projectUrl = card.dataset.projectUrl;
      if (projectUrl) {
        const separator = projectUrl.includes('?') ? '&' : '?';
        window.location.assign(`${projectUrl}${separator}filter=${encodeURIComponent(activeProjectFilter)}#projects-filter`);
      } else {
        selectProject(card);
      }
    };

    card.addEventListener('click', (event) => {
      if (event.target.closest('a')) return;
      openProject();
    });
    // 悬停时预取详情图，切换右侧详情更顺滑
    card.addEventListener('mouseenter', () => {
      if (card.dataset.image && !card.dataset.prefetched) {
        card.dataset.prefetched = '1';
        const img = new Image();
        img.decoding = 'async';
        img.src = card.dataset.image;
      }
      selectProject(card);
    });
    card.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openProject();
      }
    });
  });
}

const PROJECT_PAGE_SIZE = 12;
let projectVisibleLimit = PROJECT_PAGE_SIZE;
const projectMore = document.querySelector('[data-project-more]');
const projectMoreButton = projectMore?.querySelector('button');
const projectArchiveStatus = document.querySelector('.project-archive-status');

const applyProjectFilters = (resetLimit = true) => {
  const currentCards = currentProjectCards();
  if (!currentCards.length) {
    return;
  }
  if (resetLimit) projectVisibleLimit = PROJECT_PAGE_SIZE;

  sortProjectCardList();
  const range = projectPeriodRange(activeProjectPeriod);
  let matched = 0;
  currentProjectCards().forEach((card) => {
    const cardTypes = (card.dataset.category || '').split(/\s+/).filter(Boolean);
    const searchText = (card.dataset.searchText || card.textContent || '').toLowerCase();
    const year = Number(card.dataset.year || 0);
    const matchesType = activeProjectFilter === 'all' || cardTypes.includes(activeProjectFilter);
    const matchesSearch = !activeProjectSearch || searchText.includes(activeProjectSearch);
    const matchesPeriod = !range || (year >= range[0] && year <= range[1]);
    const isMatch = matchesType && matchesSearch && matchesPeriod;
    card.dataset.match = isMatch ? '1' : '0';
    // 不匹配的直接隐藏；匹配的先显示前 12 个，其余点「加载更多」
    card.hidden = !isMatch || matched >= projectVisibleLimit;
    if (isMatch) {
      matched += 1;
      if (!card.hidden) card.classList.add('is-visible');
    }
  });

  if (projectMore) {
    const remaining = matched - Math.min(matched, projectVisibleLimit);
    projectMore.hidden = remaining <= 0;
    if (projectMoreButton) projectMoreButton.textContent = `Load more 加载更多（${remaining}）`;
  }

  if (resetLimit) {
    const firstVisible = currentProjectCards().find((card) => !card.hidden);
    if (firstVisible) {
      selectProject(firstVisible);
    } else {
      clearProjectDetailSelection();
    }
    // 用户已滚到列表下方时，筛选后回到列表顶部
    if (projectArchiveStatus && projectArchiveStatus.getBoundingClientRect().top < 0) {
      projectArchiveStatus.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
  updateProjectArchiveStatus();
};

projectMoreButton?.addEventListener('click', () => {
  projectVisibleLimit += PROJECT_PAGE_SIZE;
  applyProjectFilters(false);
});

const applyProjectFilter = (button) => {
  if (!button || !filterButtons.length || !projectCards.length) {
    return;
  }

  activeProjectFilter = button.dataset.filterButton || 'all';
  filterButtons.forEach((current) => {
    const isActive = current === button;
    current.setAttribute('aria-pressed', String(isActive));
    current.classList.toggle('is-active', isActive);
  });
  applyProjectFilters();
};

if (filterButtons.length && projectCards.length) {
  filterButtons.forEach((button) => {
    button.addEventListener('click', () => applyProjectFilter(button));
  });
}

const applyProjectSearch = () => {
  activeProjectSearch = (projectSearchInput?.value || '').trim().toLowerCase();
  applyProjectFilters();
};

projectSearchInput?.addEventListener('keydown', (event) => {
  if (event.key !== 'Enter') return;
  event.preventDefault();
  applyProjectSearch();
});

projectSearchInput?.addEventListener('search', applyProjectSearch);
projectSearchInput?.addEventListener('input', applyProjectSearch);
projectSearchButton?.addEventListener('click', applyProjectSearch);

const requestedProjectFilter = new URLSearchParams(window.location.search).get('filter');
if (requestedProjectFilter && filterButtons.length) {
  const requestedFilterButton = [...filterButtons].find((button) => button.dataset.filterButton === requestedProjectFilter);
  if (requestedFilterButton) {
    applyProjectFilter(requestedFilterButton);
  }
}

if (periodButtons.length) {
  periodButtons.forEach((button) => {
    button.addEventListener('click', () => {
      activeProjectPeriod = button.dataset.periodButton || activeProjectPeriod;
      periodButtons.forEach((current) => {
        const isActive = current === button;
        current.setAttribute('aria-pressed', String(isActive));
        current.classList.toggle('is-active', isActive);
      });
      applyProjectFilters();
    });
  });
}

// 首次进入页面：按默认条件筛选并分页（若 URL 带 ?filter= 已在上方处理）
if (projectCards.length && !requestedProjectFilter) {
  applyProjectFilters();
}

window.applyProjectFilterByName = (filterName) => {
  const currentButtons = [...document.querySelectorAll('[data-filter-button]')];
  const currentCards = [...document.querySelectorAll('[data-project-card]')];
  const button = currentButtons.find((current) => current.dataset.filterButton === filterName);

  if (!button || !currentCards.length) {
    return;
  }

  applyProjectFilter(button);
};
window.siteProjectFilterReady = true;

const form = document.querySelector('[data-inquiry-form]');

if (form) {
  const status = form.querySelector('[data-form-status]');
  const submitButton = form.querySelector('[data-inquiry-submit]');
  const setStatus = (text, state) => {
    if (!status) return;
    status.textContent = text;
    status.className = `notice${state ? ` notice--${state}` : ''}`;
  };

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const requiredFields = [...form.querySelectorAll('[required]')];
    const invalidField = requiredFields.find((field) => !String(field.value || '').trim());
    if (invalidField) {
      invalidField.focus();
      setStatus('Please complete the required fields before sending the inquiry. 请填写必填项。', 'error');
      return;
    }
    const emailField = form.querySelector('[name="email"]');
    if (emailField && emailField.validity && !emailField.validity.valid) {
      emailField.focus();
      setStatus('Please enter a valid email address. 请填写正确的邮箱。', 'error');
      return;
    }

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.dataset.label = submitButton.textContent;
      submitButton.textContent = 'Sending…';
    }
    setStatus('Sending your inquiry… 正在提交…', '');

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json' },
        credentials: 'same-origin'
      });
      const result = await response.json();
      if (result.token) {
        const tokenField = form.querySelector('[name="_token"]');
        if (tokenField) tokenField.value = result.token;
      }
      if (result.ok) {
        form.reset();
        setStatus(result.message || 'Thank you. Your inquiry has been received.', 'success');
      } else {
        setStatus(result.message || 'Submission failed. Please email us directly.', 'error');
      }
    } catch (error) {
      setStatus('Network error. Please try again or email us directly. 网络异常，请重试或直接发邮件。', 'error');
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = submitButton.dataset.label || 'Send Inquiry';
      }
    }
  });
}

document.querySelectorAll('[data-copy-text]').forEach((button) => {
  button.addEventListener('click', async () => {
    const text = button.getAttribute('data-copy-text') || '';
    try {
      await navigator.clipboard.writeText(text);
      button.textContent = 'Copied';
      setTimeout(() => {
        button.textContent = 'Copy';
      }, 1400);
    } catch {
      button.textContent = 'Copy unavailable';
      setTimeout(() => {
        button.textContent = 'Copy';
      }, 1400);
    }
  });
});
})();
