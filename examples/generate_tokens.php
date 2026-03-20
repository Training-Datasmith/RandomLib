<?php

declare(strict_types=1);

/**
 * RandomLib — token and string generation example.
 *
 * Demonstrates: generating secure random strings, bytes, and integers.
 *
 * Run:
 *   php examples/generate_tokens.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Random_Lib\Factory;

$factory = new Factory();

// Medium-strength generator is suitable for session tokens, CSRF tokens, etc.
$medium = $factory->getMediumStrengthGenerator();

// Generate a 32-character alphanumeric token
$token = $medium->generateString(32, Generator::CHAR_ALNUM);
echo 'Random token (32 chars): ' . $token . PHP_EOL;

// Generate 16 raw bytes (useful for binary secrets)
$bytes = $medium->generateBytes(16);
echo 'Random bytes (hex):      ' . bin2hex($bytes) . PHP_EOL;

// Generate a random integer in a range (e.g., 6-digit OTP)
$otp = $medium->generateInt(100000, 999999);
echo 'Random OTP (6 digits):   ' . $otp . PHP_EOL;

// URL-safe base64 token (common for password reset links)
$urlSafe = rtrim(strtr(base64_encode($medium->generateBytes(24)), '+/', '-_'), '=');
echo 'URL-safe token:          ' . $urlSafe . PHP_EOL;

// High-strength generator for cryptographic keys
$high = $factory->getHighStrengthGenerator();
$key = bin2hex($high->generateBytes(32));
echo 'Crypto key (256-bit):    ' . $key . PHP_EOL;
