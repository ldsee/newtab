<?php
function render_weather($ctx){
  $lat = $ctx['lat'] ?? 59.9139;
  $lon = $ctx['lon'] ?? 10.7522;
  $ttl = $ctx['ttl'] ?? 3600;

  $data = fetch_with_cache('weather_' . md5("$lat,$lon") . '.json', $ttl, function() use ($lat,$lon){
    $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current_weather=true";
    list($status, $body) = http_get($url);
    if ($status !== 200 || !$body) return null;
    $json = json_decode($body, true);
    if (!isset($json['current_weather'])) return null;
    return $json['current_weather'];
  });

  echo '<div class="widget weather">';
  echo '<h3>🌤 Weather</h3>';
  if ($data) {
    $t = round($data['temperature']);
    $w = round($data['windspeed']);
    echo '<div class="big">'.h($t).'°C</div>';
    echo '<div class="muted">Wind '.h($w).' km/h</div>';
  } else {
    echo '<p>Weather unavailable.</p>';
  }
  echo '</div>';
}
?>