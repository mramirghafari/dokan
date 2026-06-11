<?php

namespace App\Services;

use Exception;

class ReceiptAiService
{
    /* ================= DEPENDENCY ================= */

    public function __construct(
        private ProductMatcherService $matcher
    ) {}

    /* ================= CONFIG ================= */

    private string $API_KEY = 'aa-yCUIXgFYpkxIq3gaEc8hSVrEEDhrriTBMtR80yEAU1OmP9fg';

    private string $OCR_ENDPOINT  = 'https://api.avalai.ir/v1/ocr';
    private string $CHAT_ENDPOINT = 'https://api.avalai.ir/v1/chat/completions';

    /* ================= HELPERS ================= */

    private function normalizePersianNumbers(string $text): string
    {
        return str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
            ['0','1','2','3','4','5','6','7','8','9'],
            $text
        );
    }

    private function extractJsonBlock(string $text): ?string
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

    private function curl_json(string $url, array $payload): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->API_KEY
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 400 || isset($data['error'])) {
            throw new Exception($data['error']['message'] ?? 'API Error');
        }

        if (!$data) {
            throw new Exception('Invalid API JSON');
        }

        return $data;
    }

    /* ================= MAIN ================= */

    public function processImage(string $imageUrl): array
    {
        /* ---------- OCR ---------- */

        $ocr = $this->curl_json($this->OCR_ENDPOINT, [
            'model' => 'mistral-ocr-latest',
            'document' => [
                'type' => 'image_url',
                'image_url' => $imageUrl
            ]
        ]);

        $page = $ocr['pages'][0]
            ?? throw new Exception('OCR empty');

        $ocrText = $page['html']
            ?? $page['markdown']
            ?? $page['text']
            ?? null;

        if (!$ocrText) {
            throw new Exception('OCR text missing');
        }

        $ocrText = $this->normalizePersianNumbers($ocrText);

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

        $chat = $this->curl_json($this->CHAT_ENDPOINT, [
            'model' => 'gpt-4.1-mini',
            'temperature' => 0,
            'messages' => [
                ['role' => 'system', 'content' => 'Return STRICT JSON only'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $raw = $chat['choices'][0]['message']['content'] ?? '';
        file_put_contents(storage_path('logs/llm_raw_output.txt'), $raw);

        $json = $this->extractJsonBlock($raw)
            ?? throw new Exception('JSON not found');

        $aiData = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

        /* ---------- VALIDATION ---------- */

        foreach ($aiData as $i => &$row) {
            if (!is_array($row)) {
                throw new Exception("Invalid row $i");
            }

            // ضد شماره ردیفی که AI قاطی می‌کنه
            if (
                isset($row['pr_code']) &&
                is_numeric($row['pr_code']) &&
                (int) $row['pr_code'] === $i + 1
            ) {
                $row['pr_code'] = null;
            }

            foreach ($row as $k => $v) {
                if (str_contains($k, 'price') || in_array($k, ['pr_item','pr_pack'])) {
                    if ($v !== null && !is_numeric($v)) {
                        throw new Exception("Invalid number at row $i ($k)");
                    }
                }
            }
        }
        unset($row);

        /* ---------- MATCHING (NEW ✅) ---------- */

        $matched = $this->matcher->match($aiData);

        return [
            'success' => true,
            'rows' => count($aiData),
            'ai_data' => $aiData,
            'matched_items' => $matched,
        ];
    }
}
