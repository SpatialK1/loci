<?php
class I18n {

    private static array $strings = [];
    private static string $activeLanguage = 'en';

    private static array $supportedLanguages = [
        'en' => 'English',
        'fr' => 'Français',
        'de' => 'Deutsch',
        'pt' => 'Português',
        'it' => 'Italiano',
        'ja' => '日本語',
        'zh' => '中文',
        'fa' => 'فارسی',
        'ru' => 'Русский',
    ];

    // RTL languages
    private static array $rtlLanguages = ['fa'];

    public static function init(string $overrideLanguage = null): void {
        $language = $overrideLanguage ?? self::detectLanguage();
        $language = self::sanitizeLanguage($language);
        self::$activeLanguage = $language;
        self::loadLanguage($language);
    }

    public static function detectLanguage(): string {
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en';
        // Parse Accept-Language header
        $parts = explode(',', $header);
        foreach ($parts as $part) {
            $lang = trim(explode(';', $part)[0]);
            $lang = strtolower(substr($lang, 0, 2));
            if (isset(self::$supportedLanguages[$lang])) {
                return $lang;
            }
        }
        return 'en';
    }

    private static function sanitizeLanguage(string $lang): string {
        $lang = preg_replace('/[^a-z]/', '', strtolower($lang));
        return isset(self::$supportedLanguages[$lang]) ? $lang : 'en';
    }

    private static function loadLanguage(string $lang): void {
        $file = __DIR__ . '/../../lang/' . $lang . '.php';
        if (file_exists($file)) {
            self::$strings = require $file;
        }
        // Always fall back to English for missing keys
        $englishFile = __DIR__ . '/../../lang/en.php';
        if ($lang !== 'en' && file_exists($englishFile)) {
            $english = require $englishFile;
            self::$strings = array_merge($english, self::$strings);
        }
    }

    public static function get(string $key): string {
        return self::$strings[$key] ?? $key;
    }

    public static function getActiveLanguage(): string {
        return self::$activeLanguage;
    }

    public static function getSupportedLanguages(): array {
        return self::$supportedLanguages;
    }

    public static function isRTL(): bool {
        return in_array(self::$activeLanguage, self::$rtlLanguages);
    }

    public static function getAllStrings(): array {
        return self::$strings;
    }
}

// Global helper function
function t(string $key): string {
    return I18n::get($key);
}