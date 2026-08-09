<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class PasswordGenerator
{
    /**
     * Generate a cryptographically secure random password that meets all requirements:
     * - Minimum 12 characters
     * - At least 1 lowercase letter
     * - At least 1 uppercase letter
     * - At least 1 digit
     * - At least 1 symbol
     */
    public static function generate(int $length = 14): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits    = '0123456789';
        $symbols   = '!@#$%^&*()-_=+';

        // Guarantee at least one of each category
        $password = [
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        // Fill the rest with random characters from all categories
        $all = $lowercase . $uppercase . $digits . $symbols;
        for ($i = count($password); $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle to avoid predictable order
        for ($i = count($password) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$password[$i], $password[$j]] = [$password[$j], $password[$i]];
        }

        return implode('', $password);
    }

    /**
     * Generate a unique email based on first_name.last_name with numeric suffix if needed.
     */
    public static function generateEmail(string $firstName, string $lastName, string $domain = 'timetable-app.com'): string
    {
        $base = strtolower(Str::slug($firstName) . '.' . Str::slug($lastName));
        $email = $base . '@' . $domain;
        $counter = 1;

        while (\App\Models\User::where('email', $email)->exists()) {
            $counter++;
            $email = $base . $counter . '@' . $domain;
        }

        return $email;
    }
}
