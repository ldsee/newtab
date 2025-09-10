<?php
function render_reddit($ctx){
  $subs  = $ctx['subs'] ?? ['technology'];
  $ttl   = $ctx['ttl'] ?? 1800;
  $show  = $ctx['show'] ?? 8;
  $poolN = $ctx['pool'] ?? 60;
  $title = $ctx['title'] ?? 'Reddit · last hour';

  $cacheKey = 'reddit_pool_' . md5(json_encode([$subs])) . '.json';
  $pool = fetch_with_cache($cacheKey, $ttl, function() use ($subs, $poolN){
    $all = [];
    foreach ($subs as $s) {
      $url = "https://www.reddit.com/r/{$s}/top.json?t=hour&limit=50";
      list($status, $body) = http_get($url, ['headers'=>['Accept: application/json'], 'user_agent'=>'Mozilla/5.0 NewTab/2.0']);
      if ($status !== 200 || !$body) continue;
      $json = json_decode($body, true);
      if (!isset($json['data']['children'])) continue;
      foreach ($json['data']['children'] as $c) {
        $d = $c['data'];
        $link = 'https://www.reddit.com' . ($d['permalink'] ?? '');
        if (!$link) continue;
        $all[] = [
          'id'    => md5($link),
          'title' => $d['title'] ?? '',
          'link'  => $link,
          'score' => $d['score'] ?? 0,
          'sub'   => $d['subreddit'] ?? '',
          'ts'    => $d['created_utc'] ?? time()
        ];
      }
    }
    usort($all, function($a,$b){
      $s = ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
      return $s !== 0 ? $s : (($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
    });
    $seen=[]; $uniq=[];
    foreach ($all as $it) { if(isset($seen[$it['id']])) continue; $seen[$it['id']]=1; $uniq[]=$it; }
    $pool = array_slice($uniq, 0, max(10,$poolN));
    shuffle($pool);
    return $pool;
  });

  echo '<div class="news">';
  echo '<h3>'.h($title).'</h3>';
  if ($pool && count($pool)) {
    $slice = rotate_slice_from_pool($pool, 'reddit', $show);
    echo '<ul>';
    foreach ($slice as $it) {
      echo '<li><a target="_top" href="'.h($it['link']).'">'.h($it['title']).'</a> <span class="meta">r/'.h($it['sub']).' · ▲'.h($it['score']).'</span></li>';
    }
    echo '</ul>';
  } else {
    echo '<p>No Reddit items.</p>';
  }
  echo '</div>';
}
?>