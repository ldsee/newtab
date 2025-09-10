<?php
header('Content-Type: application/json; charset=utf-8');
$raw = file_get_contents('php://input');
if ($raw === false) { http_response_code(400); echo json_encode(['ok'=>false,'err'=>'no body']); exit; }
$file = __DIR__ . '/cache/layout.json';
if ($raw === '[]') { @unlink($file); echo json_encode(['ok'=>true,'reset'=>true]); exit; }
$res = @file_put_contents($file, $raw);
echo json_encode(['ok'=> (bool)$res]);
