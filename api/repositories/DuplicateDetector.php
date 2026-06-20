<?php
class DuplicateDetector {

    private static array $abbreviations = [];
    private static array $nicknames = [];

    public static function init(string $language = 'en'): void {
        // Try to load language-specific abbreviations
        $file = __DIR__ . '/../../lang/abbreviations/' . $language . '.php';
        if (!file_exists($file)) {
            $file = __DIR__ . '/../../lang/abbreviations/en.php';
        }
        if (file_exists($file)) {
            $data = require $file;
            self::$abbreviations = $data['abbreviations'] ?? [];
            self::$nicknames     = $data['nicknames'] ?? [];
        }
    }

    public static function normalize(string $str): string {
        $str = mb_strtolower($str);
        $str = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $str);
        $str = preg_replace('/\s+/', ' ', trim($str));
        $words = explode(' ', $str);
        $words = array_map(function($word) {
            return self::$abbreviations[$word] ?? $word;
        }, $words);
        $words = array_map(function($word) {
            return self::$nicknames[$word] ?? $word;
        }, $words);
        return implode(' ', $words);
    }

    public static function trigramSimilarity(string $a, string $b): float {
        if ($a === $b) return 1.0;
        
        $lenA = mb_strlen($a);
        $lenB = mb_strlen($b);
        
        // For very short strings use a different approach
        if ($lenA < 10 || $lenB < 10) {
            // Check if one contains the other
            if (mb_strpos($b, $a) !== false || mb_strpos($a, $b) !== false) {
                return 0.9;
            }
            // Use Levenshtein for short strings
            $lev = levenshtein($a, $b);
            $maxLen = max($lenA, $lenB);
            return $maxLen > 0 ? 1.0 - ($lev / $maxLen) : 0.0;
        }
    
        if ($lenA < 3 || $lenB < 3) {
            return $a === $b ? 1.0 : 0.0;
        }
    
        $trigramsA = self::getTrigrams($a);
        $trigramsB = self::getTrigrams($b);
        $intersection = count(array_intersect($trigramsA, $trigramsB));
        $union = count(array_unique(array_merge($trigramsA, $trigramsB)));
        return $union > 0 ? $intersection / $union : 0.0;
    }

    private static function getTrigrams(string $str): array {
        $trigrams = [];
        $padded = '  ' . $str . '  ';
        for ($i = 0; $i < mb_strlen($padded) - 2; $i++) {
            $trigrams[] = mb_substr($padded, $i, 3);
        }
        return $trigrams;
    }

    public static function compare(string $a, string $b): float {
        $normA = self::normalize($a);
        $normB = self::normalize($b);
        return self::trigramSimilarity($normA, $normB);
    }

    public static function getConfidence(float $score): string {
        if ($score >= 0.99) return 'definitive';
        if ($score >= 0.85) return 'likely';
        if ($score >= 0.70) return 'possible';
        return 'none';
    }

    public static function findDuplicates(array $incoming, array $existingItems): array {
        $duplicates = [];

        if (!empty($incoming['url'])) {
            foreach ($existingItems as $existing) {
                if (!empty($existing['url']) && $existing['url'] === $incoming['url']) {
                    $duplicates[] = [
                        'existing'   => $existing,
                        'confidence' => 'definitive',
                        'reason'     => 'Exact URL match',
                        'score'      => 1.0,
                    ];
                    return $duplicates;
                }
            }
        }

        if (!empty($incoming['isbn'])) {
            foreach ($existingItems as $existing) {
                if (!empty($existing['isbn']) && $existing['isbn'] === $incoming['isbn']) {
                    $duplicates[] = [
                        'existing'   => $existing,
                        'confidence' => 'definitive',
                        'reason'     => 'Exact ISBN match',
                        'score'      => 1.0,
                    ];
                    return $duplicates;
                }
            }
        }

        foreach ($existingItems as $existing) {
            $titleScore = self::compare(
                $incoming['title'] ?? '',
                $existing['title'] ?? ''
            );

            $authorScore = 0.0;
            if (!empty($incoming['author']) && !empty($existing['author'])) {
                $authorScore = self::compare($incoming['author'], $existing['author']);
            }

            $combinedScore = !empty($incoming['author']) && !empty($existing['author'])
                ? ($titleScore * 0.6) + ($authorScore * 0.4)
                : $titleScore;

            $confidence = self::getConfidence($combinedScore);

            if ($confidence !== 'none') {
                $duplicates[] = [
                    'existing'   => $existing,
                    'confidence' => $confidence,
                    'reason'     => 'Similar title' . ($authorScore > 0 ? ' and author' : ''),
                    'score'      => round($combinedScore, 3),
                ];
            }
        }

        usort($duplicates, fn($a, $b) => $b['score'] <=> $a['score']);

        return $duplicates;
    }
}