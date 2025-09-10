(function () {
  function ready(cb){ if (document.readyState !== 'loading') cb(); else document.addEventListener('DOMContentLoaded', cb); }
  const KEY = 'nt_layout_v1';

  const debounce = (fn, ms=160) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function saveLayout(grid){
    const layout = grid.engine.nodes.map(n => ({
      id: n.id || n.el.getAttribute('gs-id'),
      x: n.x, y: n.y, w: n.w, h: n.h
    }));
    try { localStorage.setItem(KEY, JSON.stringify(layout)); } catch {}
    fetch('save_layout.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(layout) }).catch(()=>{});
  }

  ready(() => {
    if (!window.GridStack) {
      alert('Grid engine missing. Ensure assets/vendor/gridstack-all.min.js is on the server and included.');
      return;
    }

    const grid = GridStack.init({
      column: 24,
      cellHeight: 12,
      margin: 20,              // visual gap similar to your old grid
      float: true,
      animate: true,
      staticGrid: true,        // locked until Edit
      draggable: { handle: '.drag-handle' },
      resizable: { handles: 'e, se, s' }
    }, '#grid');

    // restore: server file first, then localStorage
    (async () => {
      let saved = null;
      try {
        const r = await fetch('cache/layout.json?b=' + Date.now(), {cache:'no-store'});
        if (r.ok) saved = await r.json();
        if (!Array.isArray(saved)) {
          const s = localStorage.getItem(KEY);
          if (s) saved = JSON.parse(s);
        }
      } catch {}
      if (Array.isArray(saved)) {
        saved.forEach(l => {
          const el = [...document.querySelectorAll('.grid-stack-item')]
            .find(e => (e.getAttribute('gs-id')||'') === l.id);
          if (el) grid.update(el, {x:l.x, y:l.y, w:l.w, h:l.h});
        });
      }
    })();

    // persist on change / stop
    const saveDebounced = debounce(() => saveLayout(grid), 180);
    grid.on('change', saveDebounced);
    grid.on('dragstop', () => saveLayout(grid));
    grid.on('resizestop', () => saveLayout(grid));

    // controls
    const edit  = document.getElementById('editToggle');
    const reset = document.getElementById('resetLayout');

    const setEditable = on => {
      grid.setStatic(!on);
      document.body.classList.toggle('editing', on);
    };
    setEditable(false);

    edit?.addEventListener('click', () => setEditable(!document.body.classList.contains('editing')));

    reset?.addEventListener('click', () => {
      try { localStorage.removeItem(KEY); } catch {}
      fetch('save_layout.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:'[]'}).catch(()=>{});
      // reset to gs-* defaults in DOM (we set those in index.php)
      grid.engine.nodes.forEach(n => {
        const el = n.el;
        const x = parseInt(el.getAttribute('gs-x') || '0', 10);
        const y = parseInt(el.getAttribute('gs-y') || '0', 10);
        const w = parseInt(el.getAttribute('gs-w') || '6', 10);
        const h = parseInt(el.getAttribute('gs-h') || '6', 10);
        grid.update(el, {x,y,w,h});
      });
      saveLayout(grid);
    });
  });
})();
