<?php
function render_quote($ctx){
  $ttl = $ctx['ttl'] ?? 21600;
  $poolSize = $ctx['pool_size'] ?? 25;

  $pool = fetch_with_cache('quotes_pool.json', $ttl, function() use ($poolSize){
    list($status, $body) = http_get('https://zenquotes.io/api/quotes');
    if ($status !== 200 || !$body) return null;
    $json = json_decode($body, true);
    if (!is_array($json)) return null;
    $quotes = [];
    foreach ($json as $q) {
      if (isset($q['q'], $q['a'])) {
        $quotes[] = ['id'=>md5($q['q'].$q['a']), 'text'=>$q['q'].' — '.$q['a']];
      }
      if (count($quotes) >= $poolSize) break;
    }
    if (empty($quotes)) return null;
    shuffle($quotes);
    return $quotes;
  });

  echo '<div class="widget quote">';
  echo '<h3>💡 Quote</h3>';
  if ($pool && count($pool)) {
    $chosen = rotate_slice_from_pool($pool, 'quote', 1);
    $q = $chosen ? $chosen[0] : null;
    echo $q ? '<p>'.h($q['text']).'</p>' : '<p>No quotes available.</p>';
  } else {
    echo '<p>No quotes available.</p>';
  }
  echo '</div>';
}
?>