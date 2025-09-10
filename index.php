<?php
// index.php — clean dark theme + Gridstack (self-hosted)

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone'] ?? 'Europe/Oslo');

require __DIR__ . '/framework.php';
$widgets = $config['widgets'] ?? [];

/** Default layout (24 cols). Used only when a widget has no x/y/w/h in config.php */
$defaults = [
  'search'        => ['x'=>0,  'y'=>0,  'w'=>24, 'h'=>6],
  'quote'         => ['x'=>16, 'y'=>6,  'w'=>8,  'h'=>6],
  'weather'       => ['x'=>16, 'y'=>12, 'w'=>8,  'h'=>8],
  'general-news'  => ['x'=>0,  'y'=>6,  'w'=>16, 'h'=>14],
  'tech-news'     => ['x'=>0,  'y'=>22, 'w'=>16, 'h'=>14],
  'reddit'        => ['x'=>16, 'y'=>22, 'w'=>8,  'h'=>14],
];
function pick($w, $defaults) {
  $name = $w['id'] ?? $w['name'] ?? '';
  $d = $defaults[$name] ?? ['x'=>0,'y'=>0,'w'=>6,'h'=>6];
  return [
    'x' => (int)($w['x'] ?? $d['x']),
    'y' => (int)($w['y'] ?? $d['y']),
    'w' => (int)($w['w'] ?? $d['w']),
    'h' => (int)($w['h'] ?? $d['h']),
  ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>New Tab</title>

  <!-- Gridstack (self-hosted) -->
  <link rel="stylesheet" href="assets/vendor/gridstack.min.css">

  <!-- Theme -->
  <link rel="stylesheet" href="assets/style.css">

  <!-- Gridstack engine (must exist on server) -->
  <script src="assets/vendor/gridstack-all.min.js"></script>

  <!-- App -->
  <script defer src="assets/app.js"></script>
</head>
<body>
  <div class="shell">
    <header class="mast">
      <div class="time"><?php echo date("H:i"); ?></div>
      <div class="date"><?php echo date("l, F j, Y"); ?></div>
      <div class="controls">
        <button id="editToggle" title="Toggle edit mode">✥ Edit</button>
        <button id="resetLayout" title="Reset to default">↺ Reset</button>
      </div>
    </header>

    <div class="grid-stack" id="grid">
      <?php foreach ($widgets as $w):
        $id = $w['id'] ?? $w['name'];
        $pos = pick($w, $defaults);
      ?>
        <div class="grid-stack-item"
             gs-id="<?= htmlspecialchars($id) ?>"
             gs-x="<?= $pos['x'] ?>" gs-y="<?= $pos['y'] ?>"
             gs-w="<?= $pos['w'] ?>" gs-h="<?= $pos['h'] ?>">
          <div class="grid-stack-item-content card">
            <!-- invisible drag overlay (only active in Edit mode) -->
            <div class="drag-handle" aria-hidden="true"></div>
            <?php renderWidget($w['name'], $w); ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
