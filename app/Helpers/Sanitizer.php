<?php
declare(strict_types=1);

namespace App\Helpers;

use DateTime;

/**
 * Data Sanitization & Formatting Helper
 */
class Sanitizer {
    public static function e(?string $value): string {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function slug(string $text): string {
        // Convert to lowercase and transliterate
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    public static function extractYouTubeVideoId(string $url): ?string {
        $url = trim($url);
        // Direct ID passed
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        // Standard YouTube URL formats
        $patterns = [
            '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public static function extractYouTubePlaylistId(string $url): ?string {
        $url = trim($url);
        // Direct playlist ID passed
        if (preg_match('/^(PL|UU|FL|RD|UL)[a-zA-Z0-9_-]+$/', $url)) {
            return $url;
        }

        if (preg_match('/[?&]list=([a-zA-Z0-9_-]+)/i', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function formatDate(?string $datetime, string $format = 'M j, Y'): string {
        if (empty($datetime)) {
            return 'N/A';
        }
        try {
            $dt = new DateTime($datetime);
            return $dt->format($format);
        } catch (\Throwable $e) {
            return $datetime;
        }
    }

    public static function timeAgo(?string $datetime): string {
        if (empty($datetime)) {
            return 'never';
        }
        try {
            $time = strtotime($datetime);
            $diff = time() - $time;
            if ($diff < 60) return 'just now';
            if ($diff < 3600) return floor($diff / 60) . 'm ago';
            if ($diff < 86400) return floor($diff / 3600) . 'h ago';
            if ($diff < 604800) return floor($diff / 86400) . 'd ago';
            return date('M j, Y', $time);
        } catch (\Throwable $e) {
            return $datetime;
        }
    }
}
