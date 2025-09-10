<?php
function render_search($ctx){
  echo '<div class="widget search">';
  echo '<h3>🔎 Search</h3>';
  echo '<form method="get" action="https://www.google.com/search" target="_top">';
  echo '<input type="text" name="q" placeholder="Search the web..." autofocus style="width:100%; padding:14px 16px; border-radius:10px; border:none; background: rgba(255,255,255,0.08); color:var(--text); font-size:16px; outline: none;" />';
  echo '</form>';
  echo '</div>';
}
?>