
// gs-loader.js - tries local vendor files in this priority:
// 1) gridstack-all.min.js
// 2) gridstack-all.js
// 3) core + dd plugins (min names from v12.x)
(function(){
  function loadScript(src){ return new Promise((res,rej)=>{ const s=document.createElement('script'); s.src=src; s.onload=res; s.onerror=()=>rej(new Error('fail '+src)); document.head.appendChild(s); }); }
  async function tryAll(){
    const base = 'assets/vendor/';
    const tries = [
      [base+'gridstack-all.min.js'],
      [base+'gridstack-all.js'],
      // split set (order matters: core first, then plugins)
      [base+'gridstack.min.js',
       base+'dd-base-impl.min.js',
       base+'dd-touch.min.js',
       base+'dd-draggable.min.js',
       base+'dd-droppable.min.js',
       base+'dd-resizable-handle.min.js',
       base+'dd-resizable.min.js',
       base+'dd-element.min.js',
       base+'dd-manager.min.js',
       base+'dd-gridstack.min.js']
    ];
    for (const files of tries){
      try{
        for (const f of files) { await loadScript(f); }
        if (window.GridStack) return true;
      }catch(e){ /* try next */ }
    }
    return false;
  }
  (async () => {
    const ok = await tryAll();
    if (!ok){
      console.error('Could not load Gridstack from local vendor files. See README_VENDOR.txt');
      alert('Grid engine missing. Upload matching Gridstack files into /assets/vendor. See README_VENDOR.txt.');
    }
  })();
})();
