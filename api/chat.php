<?php
// Simple API para chat con traducción automática bajo demanda.
// GET /api/chat.php[?lang=xx] -> devuelve mensajes (agrega text_translated cuando se solicita y se puede traducir)
// POST /api/chat.php -> añade mensaje { user, text, lang }

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$base = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
$dataDir = $base . '/data';
if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
$file = $dataDir . '/chat.json';
if (!file_exists($file)) file_put_contents($file, json_encode(['global'=>[]], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function read_all($file){
    $j = @file_get_contents($file);
    $a = json_decode($j, true);
    return is_array($a) ? $a : ['global'=>[]];
}
function write_all($file, $arr){
    $tmp = $file . '.tmp';
    file_put_contents($tmp, json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    @rename($tmp, $file);
}

function translate_text($text, $source, $target) {
    $text = trim($text);
    if ($text === '' || $source === $target) return $text;
    $endpoint = 'https://libretranslate.com/translate';
    $payload = ['q'=>$text,'source'=>($source?:'auto'),'target'=>$target,'format'=>'text'];
    if (function_exists('curl_init')) {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($res && $code >= 200 && $code < 300) {
            $j = json_decode($res, true);
            if (isset($j['translatedText'])) return $j['translatedText'];
        }
    } else {
        $opts = ['http'=>['method'=>'POST','header'=>"Content-Type: application/x-www-form-urlencoded\r\n",'content'=>http_build_query($payload),'timeout'=>8]];
        $ctx = stream_context_create($opts);
        $res = @file_get_contents($endpoint, false, $ctx);
        if ($res) {
            $j = json_decode($res, true);
            if (isset($j['translatedText'])) return $j['translatedText'];
        }
    }
    return false;
}

if ($method === 'GET') {
    $all = read_all($file);
    $room = trim((string)($_GET['room'] ?? 'global')) ?: 'global';
    if (!isset($all[$room])) $all[$room] = [];
    $msgs = $all[$room];
    $target = trim((string)($_GET['lang'] ?? ''));
    $modified = false;

    if ($target !== '') {
        foreach ($msgs as &$m) {
            if (!is_array($m)) continue;
            $src = trim((string)($m['lang'] ?? ''));
            if (!isset($m['translations']) || !is_array($m['translations'])) $m['translations'] = [];
            if ($src === $target) {
                $m['text_translated'] = $m['text'];
            } else {
                if (isset($m['translations'][$target])) {
                    $m['text_translated'] = $m['translations'][$target];
                } else {
                    $translated = translate_text($m['text'], $src ?: 'auto', $target);
                    if ($translated !== false) {
                        $m['translations'][$target] = $translated;
                        $m['text_translated'] = $translated;
                        $modified = true;
                    } else {
                        $m['text_translated'] = '';
                    }
                }
            }
        }
        unset($m);
        if ($modified) {
            // persist translations back into the full structure
            $all[$room] = $msgs;
            write_all($file, $all);
        }
    }

    echo json_encode(array_values($msgs), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'POST') {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if (!is_array($data) || empty(trim($data['text'] ?? ''))) {
        http_response_code(400);
        echo json_encode(['error'=>'Invalid payload']);
        exit;
    }
    $room = trim((string)($data['room'] ?? 'global')) ?: 'global';
    $all = read_all($file);
    if (!isset($all[$room])) $all[$room] = [];

    $msg = [
        'user' => substr(trim($data['user'] ?? 'Anon'), 0, 64),
        'text' => substr(trim($data['text']), 0, 1000),
        'lang' => substr(trim($data['lang'] ?? ''), 0, 8),
        'time' => date('H:i'),
        'translations' => []
    ];
    $all[$room][] = $msg;
    // mantener últimos 200 mensajes por sala
    if (count($all[$room]) > 200) $all[$room] = array_slice($all[$room], -200);

    write_all($file, $all);
    echo json_encode(['ok'=>true]);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
?>
