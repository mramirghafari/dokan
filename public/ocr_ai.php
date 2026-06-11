<?php
header('Content-Type: application/json; charset=utf-8');

/* ================= CONFIG ================= */

$API_KEY = 'aa-yCUIXgFYpkxIq3gaEc8hSVrEEDhrriTBMtR80yEAU1OmP9fg';

$OCR_ENDPOINT  = 'https://api.avalai.ir/v1/ocr';
$CHAT_ENDPOINT = 'https://api.avalai.ir/v1/chat/completions';

$IMAGE_URL = 'https://newdokan.daramino.ir/vorod3.jpg';

/* ================= HELPERS ================= */

function normalizePersianNumbers($text)
{
    return str_replace(
        ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
        ['0','1','2','3','4','5','6','7','8','9'],
        $text
    );
}

function extractJsonBlock($text)
{
    $text = preg_replace('/
```json|
```/i', '', $text);

    $start = strpos($text, '[');
    $end   = strrpos($text, ']');

    if ($start === false || $end === false || $end <= $start) {
        return null;
    }

    return trim(substr($text, $start, $end - $start + 1));
}

function curl_json($url, $payload, $apiKey)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE)
    ]);

    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        throw new Exception(curl_error($ch));
    }

    curl_close($ch);

    $data = json_decode($response, true);
    if ($http >= 400 || isset($data['error'])) {
        throw new Exception($data['error']['message'] ?? 'API Error');
    }

    if (!$data) {
        throw new Exception('Invalid API JSON');
    }

    return $data;
}

/* ================= MAIN ================= */

try {

    /* ---------- OCR ---------- */

    $ocr = curl_json($OCR_ENDPOINT, [
        'model' => 'mistral-ocr-latest',
        'document' => [
            'type' => 'image_url',
            'image_url' => $IMAGE_URL
        ]
    ], $API_KEY);

    $page = $ocr['pages'][0]
        ?? throw new Exception('OCR empty');

    $ocrText = $page['html']
        ?? $page['markdown']
        ?? $page['text']
        ?? null;

    if (!$ocrText) {
        throw new Exception('OCR text missing');
    }

    $ocrText = normalizePersianNumbers($ocrText);

    /* ---------- PROMPT (LOCKED) ---------- */

    $prompt = <<<PROMPT
You are a STRICT Persian invoice table parser.

VERY IMPORTANT RULES:
- Row numbers (1,2,3...) are NOT product codes
- Product code MUST come only from column "کد کالا"
- Product title MUST come only from column "شرح کالا یا خدمت"
- If value does not exist, use null
- Output ONLY valid JSON array
- No text outside JSON
- No markdown
- Numbers must be JSON numbers

Output schema (EXACT keys):
[
  {
    "pr_code": string|null,
    "pr_title": string|null,
    "pr_item": number|null,
    "pr_pack": number|null,
    "pr_item_price": number|null,
    "pr_all_packs_price": number|null,
    "pr_discount_price": number|null,
    "pr_pat_price": number|null,
    "pr_with_tax": number|null,
    "pr_full_price": number|null
  }
]

Table content (HTML):
$ocrText
PROMPT;

    /* ---------- CHAT ---------- */

    $chat = curl_json($CHAT_ENDPOINT, [
        'model' => 'gpt-4.1-mini',
        'temperature' => 0,
        'messages' => [
            ['role' => 'system', 'content' => 'Return STRICT JSON only'],
            ['role' => 'user', 'content' => $prompt]
        ]
    ], $API_KEY);

    $raw = $chat['choices'][0]['message']['content'] ?? '';
    file_put_contents('llm_raw_output.txt', $raw);

    $json = extractJsonBlock($raw)
        ?? throw new Exception('JSON not found');

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception(json_last_error_msg());
    }

    /* ---------- VALIDATION ---------- */

    foreach ($data as $i => $row) {
        if (!is_array($row)) {
            throw new Exception("Invalid row $i");
        }

        // pr_code must NEVER be a row index
        if (is_numeric($row['pr_code']) && intval($row['pr_code']) === $i + 1) {
            $row['pr_code'] = null;
            $data[$i] = $row;
        }

        foreach ($row as $k => $v) {
            if (str_contains($k, 'price') || in_array($k, ['pr_item','pr_pack'])) {
                if ($v !== null && !is_numeric($v)) {
                    throw new Exception("Invalid number at row $i ($k)");
                }
            }
        }
    }

    /* ---------- OUTPUT ---------- */

    echo json_encode([
        'success' => true,
        'rows' => count($data),
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
