
Self-host Gridstack (v12.x) — exact filenames

Recommended (single file):
  Put BOTH files in /assets/vendor/

  - gridstack.min.css
  - gridstack-all.min.js   <-- easiest (includes dd plugins)

Alternative (split core + plugins) — if you don't have gridstack-all*:
  Upload ALL of these to /assets/vendor/ (v12.3.3 names shown):

  - gridstack.min.js
  - dd-base-impl.min.js
  - dd-draggable.min.js
  - dd-droppable.min.js
  - dd-element.min.js
  - dd-manager.min.js
  - dd-resizable-handle.min.js
  - dd-resizable.min.js
  - dd-touch.min.js
  - dd-gridstack.min.js

Test directly in browser (should show JS/CSS, not HTML error page):
  https://<your-domain>/newtab/assets/vendor/gridstack-all.min.js
  https://<your-domain>/newtab/assets/vendor/gridstack.min.css

Make sure your server serves correct MIME types:
  .js  ->  application/javascript
  .css ->  text/css
