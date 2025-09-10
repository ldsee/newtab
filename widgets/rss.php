<?php
function render_rss($ctx){
  $feeds = $ctx['feeds'] ?? [];
  $ttl   = $ctx['ttl'] ?? 7200;
  $show  = $ctx['show'] ?? 6;
  $poolN = $ctx['pool'] ?? 60;
  $title = $ctx['title'] ?? 'News';

  $cacheKey = 'rss_pool_' . md5(json_encode($feeds)) . '.json';
  $pool = fetch_with_cache($cacheKey, $ttl, function() use ($feeds, $poolN){
    $all = [];
    foreach ($feeds as $url) {
      list($status, $body) = http_get($url, ['user_agent'=>'NewTabRSS/2.0']);
      if ($status !== 200 || !$body) continue;
      $rss = @simplexml_load_string($body);
      if (!$rss) continue;

      if (isset($rss->channel)) {
        foreach ($rss->channel->item as $it) {
          $link = (string)$it->link;
          $title = (string)$it->title;
          $ts = strtotime((string)$it->pubDate) ?: time();
          if (!$link || !$title) continue;
          $all[] = ['id'=>md5($link), 'title'=>$title, 'link'=>$link, 'ts'=>$ts];
        }
      } else {
        foreach ($rss->entry as $it) {
          $link=''; foreach ($it->link as $l) { $a=$l->attributes(); if(isset($a['href'])) {$link=(string)$a['href']; break;} }
          $title=(string)$it->title; $ts=strtotime((string)$it->updated) ?: time();
          if (!$link || !$title) continue;
          $all[] = ['id'=>md5($link), 'title'=>$title, 'link'=>$link, 'ts'=>$ts];
        }
      }
    }
    $seen=[]; $uniq=[];
    foreach ($all as $it) { if(isset($seen[$it['id']])) continue; $seen[$it['id']]=1; $uniq[]=$it; }
    usort($uniq, function($a,$b){ return $b['ts'] <=> $a['ts']; });
    $pool = array_slice($uniq, 0, max(10,$poolN));
    shuffle($pool);
    return $pool;
  });

  echo '<div class="news">';
  echo '<h3>'.h($title).'</h3>';
  if ($pool && count($pool)) {
    $slice = rotate_slice_from_pool($pool, md5($title), $show);
    echo '<ul>';
    foreach ($slice as $it) {
      $mins = max(1, (int)round((time()-($it['ts']??time()))/60));
      echo '<li><a target="_top" href="'.h($it['link']).'">'.h($it['title']).'</a> <span class="meta">· '.$mins.'m ago</span></li>';
    }
    echo '</ul>';
  } else {
    echo '<p>No stories.</p>';
  }
  echo '</div>';
}
?>