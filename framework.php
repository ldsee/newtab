<?php
define('BASE_DIR', __DIR__);
define('CACHE_DIR', BASE_DIR . '/cache/');
define('WIDGETS_DIR', BASE_DIR . '/widgets/');
define('STATE_DIR', CACHE_DIR);
if (!file_exists(CACHE_DIR)) { mkdir(CACHE_DIR, 0777, true); }

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }

function http_get($url, $opts = []){
  $timeout = $opts['timeout'] ?? 8;
  $ua = $opts['user_agent'] ?? 'NewTab/2.0 (+newtab)';
  if (function_exists('curl_init')){
    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_CONNECTTIMEOUT => $timeout, CURLOPT_TIMEOUT => $timeout, CURLOPT_USERAGENT => $ua,
      CURLOPT_SSL_VERIFYPEER => true, CURLOPT_HTTPHEADER => $opts['headers'] ?? [],
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [$status ?: 0, $body];
  } else {
    $ctx = stream_context_create(['http'=>['method'=>'GET','timeout'=>$timeout,'header'=>"User-Agent: $ua\r\n"]]);
    $body = @file_get_contents($url,false,$ctx);
    return [200,$body];
  }
}

function jitter_ttl($ttl,$pct=0.1){ $d=(int)round($ttl*$pct); if($d<=0) return $ttl; $j=random_int(-$d,$d); return max(1,$ttl+$j); }
function cache_path($name){ return CACHE_DIR.$name; }
function state_path($name){ return STATE_DIR.$name; }
function read_json($p){ if(!file_exists($p)) return null; $r=@file_get_contents($p); if($r===false) return null; return json_decode($r,true); }
function write_json($p,$d){ @file_put_contents($p,json_encode($d,JSON_UNESCAPED_SLASHES)); }

function with_lock($key,$fn,$timeout_ms=150){
  $lock=cache_path($key.'.lock'); $fh=fopen($lock,'c'); if(!$fh){ return $fn(false); }
  $start=microtime(true); $acq=false;
  do{ $acq=flock($fh,LOCK_EX|LOCK_NB); if($acq) break; usleep(10000); } while((microtime(true)-$start)*1000<$timeout_ms);
  try{ return $fn($acq); } finally { if($acq) flock($fh,LOCK_UN); fclose($fh); }
}

function fetch_with_cache($key,$ttl,$fetcher,$opts=[]){
  $ttl=jitter_ttl($ttl,$opts['jitter_pct']??0.1);
  $file=cache_path($key); $stale=$opts['stale_if_error']??true;
  $fresh=file_exists($file) && (time()-filemtime($file) < $ttl);
  if($fresh){ $d=read_json($file); if($d!==null) return $d; }
  return with_lock($key, function($locked) use ($file,$fetcher,$stale){
    if($locked){
      $d=$fetcher(); if($d!==null){ write_json($file,$d); return $d; }
      if($stale && file_exists($file)){ $f=read_json($file); if($f!==null) return $f; } return null;
    } else {
      if(file_exists($file)){ $f=read_json($file); if($f!==null) return $f; }
      $d=$fetcher(); if($d!==null) write_json($file,$d); return $d;
    }
  });
}

function rotate_slice_from_pool($pool,$widget,$count){
  $n=count($pool); if($n===0) return [];
  $stateFile=state_path('state_'.$widget.'.json');
  $state=read_json($stateFile);
  if(!is_array($state)){ $state=['offset'=>0,'last_ids'=>[]]; }
  foreach($pool as $i=>$it){ if(!isset($it['id'])){ $pool[$i]['id']=md5(json_encode($it)); } }
  $out=[]; $seen=[]; $i=$state['offset']%$n; $attempts=0;
  while(count($out)<min($count,$n) && $attempts<$n*2){
    $item=$pool[$i]; $id=$item['id'];
    if(!isset($seen[$id]) && !in_array($id,$state['last_ids'], true)){
      $out[]=$item; $seen[$id]=1;
    }
    $i=($i+1)%$n; $attempts++;
  }
  $i=$state['offset']%$n;
  while(count($out)<min($count,$n) && count($out)<$n){
    $item=$pool[$i]; $id=$item['id'];
    if(!isset($seen[$id])){ $out[]=$item; $seen[$id]=1; }
    $i=($i+1)%$n;
  }
  $state['offset']=$i%$n;
  $state['last_ids']=array_map(function($x){ return $x['id']; }, $out);
  write_json($stateFile,$state);
  return $out;
}

function renderWidget($name,$ctx=[]){
  $file=WIDGETS_DIR.$name.'.php';
  if(!file_exists($file)){ echo "<!-- widget '$name' not found -->"; return; }
  include_once $file;
  $fn='render_'.$name;
  if(function_exists($fn)){
    try { $fn($ctx); }
    catch (Throwable $e){
      echo '<div class="error"><h3>Widget error</h3><pre>'.h($e->getMessage()).'</pre></div>';
    }
  } else { echo "<!-- widget '$name' missing render function -->"; }
}
