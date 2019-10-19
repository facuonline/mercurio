<?php

namespace Mercurio\Utils;

/**
 * Provides various random-related utilities
 */
class Random {

/**
     * Returns a very random sha256 hash
     * @param mixed $entropy Optional additional entropy
     * @return string SHA256 hash
     */
    public static function hash($entropy = 'EUreka') {
        $hash[] = microtime();
        $hash[] = mt_rand(1111, 9999);
        $hash[] = openssl_random_pseudo_bytes(16);
        $hash[] = $entropy;
        $glue = base64_encode(random_bytes(4));
        shuffle($hash);
        return hash('sha256', implode($glue, $hash));
    }

}
