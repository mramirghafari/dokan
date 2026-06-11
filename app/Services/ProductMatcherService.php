<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductMatcherService
{
    private const MIN_TOKEN_LENGTH = 3;
    private const MAX_CANDIDATES   = 20;

    /* =====================================================
     | PUBLIC API
     ===================================================== */

    public function match(array $rows): array
    {
        return array_map(fn ($row) => $this->matchSingle($row), $rows);
    }

    private function matchSingle(array $row): array
    {
        $rawTitle = (string) ($row['pr_title'] ?? '');
        $rawCode  = (string) ($row['pr_code']  ?? '');

        $title = $this->normalizeText($rawTitle);
        $code  = $this->normalizeCode($rawCode);

        /* ======================================
         | STEP 1 — SKU FIRST (ABSOLUTE RULE)
         ====================================== */
        if ($code !== null) {
            $skuMatches = Product::query()
                ->whereNotNull('sku')
                ->where('sku', 'like', "%{$code}%")
                ->get();

            if ($skuMatches->count() === 1) {
                return $this->matched($skuMatches->first(), 'sku_exact');
            }

            if ($skuMatches->count() > 1) {
                // Tie‑break by title, but ONLY inside sku matches
                $best = $this->bestByTitle($title, $skuMatches);

                if ($best !== null) {
                    return $this->matched($best, 'sku_multi_title');
                }
            }
            // ❗ if no sku result → continue to title matching
        }

        /* ======================================
         | STEP 2 — TITLE MATCHING (FALLBACK)
         ====================================== */
        if ($title === '') {
            return $this->unmatched('empty_title');
        }

        $titleMatches = $this->titleCandidates($title);

        if ($titleMatches->isEmpty()) {
            return $this->unmatched('no_title_match');
        }

        $best = $this->bestByTitle($title, $titleMatches);

        if ($best === null) {
            return $this->unmatched('weak_title_match');
        }

        return $this->matched($best, 'title_only');
    }

    /* =====================================================
     | SKU HELPERS
     ===================================================== */

    private function normalizeCode(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $code);

        return strlen($digits) >= 3 ? $digits : null;
    }

    /* =====================================================
     | TITLE MATCHING (WORD‑BY‑WORD)
     ===================================================== */

    private function titleCandidates(string $title): Collection
    {
        $tokens = $this->tokens($title);

        return Product::query()
            ->where(function ($q) use ($tokens) {
                foreach ($tokens as $w) {
                    $q->orWhere('title', 'like', "%{$w}%")
                      ->orWhere('display_name', 'like', "%{$w}%");
                }
            })
            ->limit(self::MAX_CANDIDATES)
            ->get();
    }

    private function bestByTitle(string $inputTitle, Collection $products): ?Product
    {
        $inputTokens = $this->tokens($inputTitle);

        $bestScore = 0;
        $best      = null;

        foreach ($products as $product) {
            $score = $this->tokenOverlapScore(
                $inputTokens,
                $this->tokens(
                    $this->normalizeText($product->title . ' ' . $product->display_name)
                )
            );

            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = $product;
            }
        }

        // حداقل معنی‌دار بودن
        return $bestScore >= 2 ? $best : null;
    }

    private function tokenOverlapScore(array $a, array $b): int
    {
        return count(array_intersect($a, $b));
    }

    private function tokens(string $text): array
    {
        return array_values(
            array_filter(
                explode(' ', $text),
                fn ($w) => mb_strlen($w) >= self::MIN_TOKEN_LENGTH
            )
        );
    }

    private function normalizeText(?string $text): string
    {
        if (!$text) {
            return '';
        }

        $text = mb_strtolower($text);
        $text = str_replace(['ي', 'ك'], ['ی', 'ک'], $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /* =====================================================
     | RESPONSE
     ===================================================== */

    private function matched(Product $p, string $by): array
    {
        return [
            'status'       => 'matched',
            'matched_by'   => $by,
            'product_id'   => $p->id,
            'sku'          => $p->sku,
            'title'        => $p->title,
            'display_name' => $p->display_name,
        ];
    }

    private function unmatched(string $reason): array
    {
        return [
            'status' => 'unmatched',
            'reason' => $reason,
        ];
    }
}
