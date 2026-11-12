(() => {
  'use strict';
  const grid = document.getElementById('tool-catalog');
  if (!grid) return;
  const search = document.getElementById('tool-search');
  const filters = document.getElementById('tool-categories');
  const more = document.getElementById('catalog-more');
  const count = document.getElementById('catalog-count');
  let category = '', page = 1, lastPage = 1, query = '', timer = null;
  const symbols = ['✦','▤','▧','∑','⌘','◈'];
  const node = (tag, cls, value) => {
    const el = document.createElement(tag);
    if (cls) el.className = cls;
    if (value !== undefined) el.textContent = String(value ?? '');
    return el;
  };
  const message = (value) => grid.replaceChildren(node('p','catalog-message',value));
  const render = (tool, index) => {
    const card = node('article','tool-card catalog-tool-card');
    card.append(
      node('div','card-icon',symbols[index % symbols.length]),
      node('h3','',tool.name_bn || tool.name_en || tool.slug),
      node('p','catalog-tool-en',tool.name_en || tool.slug),
      node('p','',tool.description_bn || tool.description_en || 'এই টুলের বিস্তারিত শিগগিরই যুক্ত হবে।'),
      node('span','card-status',tool.category_bn || tool.category_en || 'ডিজিটাল টুল')
    );
    return card;
  };
  async function loadTools(append) {
    if (!append) message('টুল খুঁজে দেখা হচ্ছে...');
    const params = new URLSearchParams({page:String(page),per_page:'24'});
    if (category) params.set('category',category);
    if (query) params.set('q',query);
    try {
      const response = await fetch('/api/v1/tools?' + params.toString(),{headers:{Accept:'application/json'}});
      if (!response.ok) throw new Error('catalog unavailable');
      const payload = await response.json();
      const tools = Array.isArray(payload.data) ? payload.data : [];
      lastPage = Number(payload.meta?.last_page || 1);
      if (!append) grid.replaceChildren();
      if (!tools.length && !append) message('এখনো কোনো প্রকাশিত টুল নেই। নতুন টুল যুক্ত হলে এখানে দেখা যাবে।');
      tools.forEach((tool,index) => grid.append(render(tool,index + (page - 1) * 24)));
      count.textContent = 'মোট প্রকাশিত টুল: ' + (payload.meta?.total ?? tools.length);
      more.hidden = page >= lastPage;
    } catch (_) {
      if (!append) message('টুল ক্যাটালগ লোড করা যায়নি। ডেটাবেস সংযোগ ও প্রকাশিত টুল যাচাই করুন।');
      count.textContent = 'ক্যাটালগ সাময়িকভাবে পাওয়া যাচ্ছে না';
      more.hidden = true;
    }
  }
  async function loadCategories() {
    try {
      const response = await fetch('/api/v1/categories',{headers:{Accept:'application/json'}});
      if (!response.ok) throw new Error('categories unavailable');
      const payload = await response.json();
      const categoryGrid = document.getElementById('category-grid');
      if (categoryGrid) categoryGrid.replaceChildren();
      (payload.data || []).forEach((item,index) => {
        const button = node('button','filter-chip',item.name_bn || item.name_en);
        button.type = 'button';
        button.dataset.category = item.slug;
        button.addEventListener('click',() => selectCategory(item.slug));
        filters.append(button);
        if (categoryGrid) {
          const tile = node('button','category-tile');
          tile.type = 'button';
          tile.setAttribute('aria-label', (item.name_bn || item.name_en) + ' ক্যাটাগরির টুল দেখুন');
          const icon = node('span','category-tile-icon',['✧','▤','▧','◉','♫','Aa','∑','⇄','⌘','↗','✓','◈'][index % 12]);
          const copy = node('span','category-tile-copy');
          copy.append(node('strong','',item.name_bn || item.name_en),node('small','',String(item.tool_count || 0) + ' টি প্রকাশিত টুল'));
          tile.append(icon,copy);
          tile.addEventListener('click',() => { selectCategory(item.slug); document.getElementById('tools')?.scrollIntoView({behavior:'smooth'}); });
          categoryGrid.append(tile);
        }
      });
    } catch (_) { /* The all-categories option remains available. */ }
  }
  search?.addEventListener('input',() => {
    clearTimeout(timer);
    timer = setTimeout(() => { query = search.value.trim(); page = 1; loadTools(false); },250);
  });
  search?.addEventListener('keydown',(event) => {
    if (event.key === 'Escape') { search.value = ''; query = ''; page = 1; loadTools(false); }
  });
  more?.addEventListener('click',() => { if (page < lastPage) { page += 1; loadTools(true); } });
  filters?.querySelector('[data-category=""]')?.addEventListener('click',() => {
    category = ''; page = 1;
    filters.querySelectorAll('button').forEach((b) => b.classList.toggle('active',b.dataset.category === ''));
    loadTools(false);
  });
  document.addEventListener('keydown',(event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      event.preventDefault(); search?.focus();
    }
  });
  loadCategories();
  loadTools(false);

  function selectCategory(slug) {
    category = slug;
    page = 1;
    filters.querySelectorAll('button').forEach((b) => b.classList.toggle('active',b.dataset.category === category));
    loadTools(false);
  }
  const heroSearch = document.getElementById('hero-search');
  const heroSearchButton = document.getElementById('hero-search-button');
  const runHeroSearch = () => {
    if (!heroSearch || !search) return;
    search.value = heroSearch.value;
    query = heroSearch.value.trim();
    category = '';
    page = 1;
    filters.querySelectorAll('button').forEach((b) => b.classList.toggle('active',b.dataset.category === ''));
    loadTools(false);
    document.getElementById('tools')?.scrollIntoView({behavior:'smooth'});
  };
  heroSearchButton?.addEventListener('click',runHeroSearch);
  heroSearch?.addEventListener('keydown',(event) => { if (event.key === 'Enter') runHeroSearch(); });


  const themeButton = document.getElementById('theme-toggle');
  const applyTheme = (theme) => {
    document.body.dataset.theme = theme;
    if (themeButton) {
      const dark = theme === 'dark';
      themeButton.textContent = dark ? '☀' : '☾';
      themeButton.setAttribute('aria-label', dark ? 'লাইট মোড চালু করুন' : 'ডার্ক মোড চালু করুন');
      themeButton.setAttribute('aria-pressed', String(dark));
    }
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'dark' ? '#101828' : '#ffffff');
  };
  let savedTheme = 'light';
  try { savedTheme = localStorage.getItem('toolkitpro-theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); } catch (_) {}
  applyTheme(savedTheme);
  themeButton?.addEventListener('click', () => {
    const next = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    try { localStorage.setItem('toolkitpro-theme', next); } catch (_) {}
  });
})();