<?php

declare(strict_types=1);

namespace App\Traits;

use DateTime;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use NumberFormatter;

/**
 * Trait for handling localization operations
 */
trait HandlesLocalization
{
    /**
     * Get the current locale.
     *
     * @return string The current locale
     */
    protected function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Set the application locale.
     *
     * @param  string  $locale  The locale to set
     * @param  bool  $persistInSession  Whether to persist the locale in the session
     * @return bool Whether the locale was set successfully
     */
    protected function setLocale(string $locale, bool $persistInSession = true): bool
    {
        // Check if the locale is valid
        if (! $this->isValidLocale($locale)) {
            return false;
        }

        // Set the application locale
        App::setLocale($locale);

        // Persist in session if requested
        if ($persistInSession) {
            Session::put('locale', $locale);
        }

        return true;
    }

    /**
     * Check if a locale is valid (supported by the application).
     *
     * @param  string  $locale  The locale to check
     * @return bool Whether the locale is valid
     */
    protected function isValidLocale(string $locale): bool
    {
        $supportedLocales = $this->getSupportedLocales();

        return in_array($locale, $supportedLocales);
    }

    /**
     * Get all supported locales.
     *
     * @return array The supported locales
     */
    protected function getSupportedLocales(): array
    {
        return Cache::remember('supported_locales', 86400, function () {
            return Config::get('app.supported_locales', [Config::get('app.locale')]);
        });
    }

    /**
     * Get the locale display name.
     *
     * @param  string|null  $locale  The locale (or current locale if null)
     * @param  string|null  $inLocale  The locale to display the name in (or current locale if null)
     * @return string The locale display name
     */
    protected function getLocaleDisplayName(?string $locale = null, ?string $inLocale = null): string
    {
        $locale = $locale ?? $this->getCurrentLocale();
        $inLocale = $inLocale ?? $this->getCurrentLocale();

        $localeNames = $this->getLocaleDisplayNames($inLocale);

        return $localeNames[$locale] ?? $locale;
    }

    /**
     * Get all locale display names in a specific locale.
     *
     * @param  string|null  $inLocale  The locale to display the names in (or current locale if null)
     * @return array The locale display names
     */
    protected function getLocaleDisplayNames(?string $inLocale = null): array
    {
        $inLocale = $inLocale ?? $this->getCurrentLocale();

        return Cache::remember("locale_names_{$inLocale}", 86400, function () use ($inLocale) {
            // Default locale names in English
            $defaultNames = [
                'en' => 'English',
                'es' => 'Spanish',
                'fr' => 'French',
                'de' => 'German',
                'it' => 'Italian',
                'pt' => 'Portuguese',
                'ru' => 'Russian',
                'zh' => 'Chinese',
                'ja' => 'Japanese',
                'ar' => 'Arabic',
                'hi' => 'Hindi',
                'bn' => 'Bengali',
                'pa' => 'Punjabi',
                'jv' => 'Javanese',
                'ko' => 'Korean',
                'vi' => 'Vietnamese',
                'ta' => 'Tamil',
                'ur' => 'Urdu',
                'fa' => 'Persian',
                'tr' => 'Turkish',
                'pl' => 'Polish',
                'uk' => 'Ukrainian',
                'ro' => 'Romanian',
                'nl' => 'Dutch',
                'hu' => 'Hungarian',
                'el' => 'Greek',
                'cs' => 'Czech',
                'sv' => 'Swedish',
                'da' => 'Danish',
                'fi' => 'Finnish',
                'no' => 'Norwegian',
                'he' => 'Hebrew',
                'id' => 'Indonesian',
                'ms' => 'Malay',
                'th' => 'Thai',
            ];

            // Try to get localized names from config
            $configNames = Config::get("locale_names.{$inLocale}", []);

            return array_merge($defaultNames, $configNames);
        });
    }

    /**
     * Get the locale from various sources (URL, session, browser).
     *
     * @param  Request  $request  The HTTP request
     * @return string The determined locale
     */
    protected function determineLocale(Request $request): string
    {
        // Check URL parameter
        $locale = $request->query('locale');
        if ($locale && $this->isValidLocale($locale)) {
            return $locale;
        }

        // Check session
        $locale = Session::get('locale');
        if ($locale && $this->isValidLocale($locale)) {
            return $locale;
        }

        // Check browser preferences
        $locale = $this->getLocaleFromBrowser($request);
        if ($locale && $this->isValidLocale($locale)) {
            return $locale;
        }

        // Fall back to default locale
        return Config::get('app.locale');
    }

    /**
     * Get the preferred locale from browser settings.
     *
     * @param  Request  $request  The HTTP request
     * @return string|null The preferred locale or null if none found
     */
    protected function getLocaleFromBrowser(Request $request): ?string
    {
        $supportedLocales = $this->getSupportedLocales();
        $browserLocales = $this->parseBrowserLocales($request);

        foreach ($browserLocales as $browserLocale) {
            // Check for exact match
            if (in_array($browserLocale, $supportedLocales)) {
                return $browserLocale;
            }

            // Check for language match (e.g., 'en-US' matches 'en')
            $language = explode('-', $browserLocale)[0];
            if (in_array($language, $supportedLocales)) {
                return $language;
            }
        }

        return null;
    }

    /**
     * Parse browser locales from the Accept-Language header.
     *
     * @param  Request  $request  The HTTP request
     * @return array The browser locales in order of preference
     */
    protected function parseBrowserLocales(Request $request): array
    {
        $locales = [];

        $acceptLanguage = $request->header('Accept-Language');
        if (! $acceptLanguage) {
            return $locales;
        }

        // Parse the Accept-Language header
        $languageParts = explode(',', $acceptLanguage);

        foreach ($languageParts as $part) {
            $part = mb_trim($part);

            // Extract locale and quality
            if (preg_match('/(.*?)(;q=([0-9.]+))?$/', $part, $matches)) {
                $locale = $matches[1];
                $quality = isset($matches[3]) ? (float) $matches[3] : 1.0;

                $locales[$locale] = $quality;
            }
        }

        // Sort by quality (highest first)
        arsort($locales);

        return array_keys($locales);
    }

    /**
     * Get the text direction for a locale.
     *
     * @param  string|null  $locale  The locale (or current locale if null)
     * @return string The text direction ('ltr' or 'rtl')
     */
    protected function getTextDirection(?string $locale = null): string
    {
        $locale = $locale ?? $this->getCurrentLocale();

        // List of RTL languages
        $rtlLocales = ['ar', 'fa', 'he', 'ur'];

        // Check if the locale or its primary language is RTL
        $primaryLanguage = explode('-', $locale)[0];

        return in_array($primaryLanguage, $rtlLocales) ? 'rtl' : 'ltr';
    }

    /**
     * Get the locale-specific date format.
     *
     * @param  string|null  $locale  The locale (or current locale if null)
     * @param  string  $format  The format type (short, medium, long, full)
     * @return string The date format
     */
    protected function getDateFormat(?string $locale = null, string $format = 'medium'): string
    {
        $locale = $locale ?? $this->getCurrentLocale();

        // Default formats by locale
        $formats = [
            'en' => [
                'short' => 'm/d/Y',
                'medium' => 'M j, Y',
                'long' => 'F j, Y',
                'full' => 'l, F j, Y',
            ],
            'fr' => [
                'short' => 'd/m/Y',
                'medium' => 'j M Y',
                'long' => 'j F Y',
                'full' => 'l j F Y',
            ],
            'de' => [
                'short' => 'd.m.Y',
                'medium' => 'j. M Y',
                'long' => 'j. F Y',
                'full' => 'l, j. F Y',
            ],
            'es' => [
                'short' => 'd/m/Y',
                'medium' => 'j M Y',
                'long' => 'j \d\e F \d\e Y',
                'full' => 'l, j \d\e F \d\e Y',
            ],
            // Add more locales as needed
        ];

        // Get the format for the locale or fall back to English
        $localeFormats = $formats[$locale] ?? $formats['en'];

        return $localeFormats[$format] ?? $localeFormats['medium'];
    }

    /**
     * Get the locale-specific time format.
     *
     * @param  string|null  $locale  The locale (or current locale if null)
     * @param  string  $format  The format type (short, medium, long)
     * @return string The time format
     */
    protected function getTimeFormat(?string $locale = null, string $format = 'medium'): string
    {
        $locale = $locale ?? $this->getCurrentLocale();

        // Default formats by locale
        $formats = [
            'en' => [
                'short' => 'g:i a',
                'medium' => 'g:i:s a',
                'long' => 'g:i:s a T',
            ],
            'fr' => [
                'short' => 'H:i',
                'medium' => 'H:i:s',
                'long' => 'H:i:s T',
            ],
            'de' => [
                'short' => 'H:i',
                'medium' => 'H:i:s',
                'long' => 'H:i:s T',
            ],
            'es' => [
                'short' => 'H:i',
                'medium' => 'H:i:s',
                'long' => 'H:i:s T',
            ],
            // Add more locales as needed
        ];

        // Get the format for the locale or fall back to English
        $localeFormats = $formats[$locale] ?? $formats['en'];

        return $localeFormats[$format] ?? $localeFormats['medium'];
    }

    /**
     * Format a date according to the locale.
     *
     * @param  DateTime|string  $date  The date to format
     * @param  string|null  $locale  The locale (or current locale if null)
     * @param  string  $format  The format type (short, medium, long, full)
     * @return string The formatted date
     */
    protected function formatLocalizedDate($date, ?string $locale = null, string $format = 'medium'): string
    {
        $locale = $locale ?? $this->getCurrentLocale();

        // Convert to DateTime if string
        if (is_string($date)) {
            $date = new DateTimeImmutable($date);
        }

        // Get the date format for the locale
        $dateFormat = $this->getDateFormat($locale, $format);

        // Format the date
        return $date->format($dateFormat);
    }

    /**
     * Format a time according to the locale.
     *
     * @param  DateTime|string  $time  The time to format
     * @param  string|null  $locale  The locale (or current locale if null)
     * @param  string  $format  The format type (short, medium, long)
     * @return string The formatted time
     */
    protected function formatLocalizedTime($time, ?string $locale = null, string $format = 'medium'): string
    {
        $locale = $locale ?? $this->getCurrentLocale();

        // Convert to DateTime if string
        if (is_string($time)) {
            $time = new DateTimeImmutable($time);
        }

        // Get the time format for the locale
        $timeFormat = $this->getTimeFormat($locale, $format);

        // Format the time
        return $time->format($timeFormat);
    }

    /**
     * Format a date and time according to the locale.
     *
     * @param  DateTime|string  $dateTime  The date and time to format
     * @param  string|null  $locale  The locale (or current locale if null)
     * @param  string  $format  The format type (short, medium, long, full)
     * @return string The formatted date and time
     */
    protected function formatLocalizedDateTime($dateTime, ?string $locale = null, string $format = 'medium'): string
    {
        $locale = $locale ?? $this->getCurrentLocale();

        // Convert to DateTime if string
        if (is_string($dateTime)) {
            $dateTime = new DateTimeImmutable($dateTime);
        }

        // Get the date and time formats for the locale
        $dateFormat = $this->getDateFormat($locale, $format);
        $timeFormat = $this->getTimeFormat($locale, $format);

        // Format the date and time
        return $dateTime->format("{$dateFormat} {$timeFormat}");
    }

    /**
     * Format a number according to the locale.
     *
     * @param  float  $number  The number to format
     * @param  int  $decimals  The number of decimal places
     * @param  string|null  $locale  The locale (or current locale if null)
     * @return string The formatted number
     */
    protected function formatLocalizedNumber(float $number, int $decimals = 2, ?string $locale = null): string
    {
        $locale = $locale ?? $this->getCurrentLocale();

        // Set the locale for number formatting
        $oldLocale = setlocale(LC_NUMERIC, 0);
        setlocale(LC_NUMERIC, $locale);

        // Format the number
        $formatted = number_format($number, $decimals, '.', ',');

        // Restore the original locale
        setlocale(LC_NUMERIC, $oldLocale);

        return $formatted;
    }

    /**
     * Format a currency amount according to the locale.
     *
     * @param  float  $amount  The amount to format
     * @param  string  $currency  The currency code (e.g., 'USD', 'EUR')
     * @param  string|null  $locale  The locale (or current locale if null)
     * @return string The formatted currency amount
     */
    protected function formatLocalizedCurrency(float $amount, string $currency = 'USD', ?string $locale = null): string
    {
        $locale = $locale ?? $this->getCurrentLocale();

        // Set the locale for currency formatting
        $oldLocale = setlocale(LC_MONETARY, 0);
        setlocale(LC_MONETARY, $locale);

        // Format the currency amount
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $formatted = $formatter->formatCurrency($amount, $currency);

        // Restore the original locale
        setlocale(LC_MONETARY, $oldLocale);

        return $formatted;
    }

    /**
     * Translate a key based on the current locale.
     *
     * @param  string  $key  The translation key
     * @param  array  $replace  The replacement parameters
     * @param  string|null  $locale  The locale (or current locale if null)
     * @return string The translated string
     */
    protected function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        return trans($key, $replace, $locale);
    }

    /**
     * Translate a choice based on the current locale and count.
     *
     * @param  string  $key  The translation key
     * @param  int  $count  The count for pluralization
     * @param  array  $replace  The replacement parameters
     * @param  string|null  $locale  The locale (or current locale if null)
     * @return string The translated string
     */
    protected function translateChoice(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        return trans_choice($key, $count, $replace, $locale);
    }
}
