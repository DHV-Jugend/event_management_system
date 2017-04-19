<?php

namespace BIT\EMS\Utility;

/**
 * @author Christoph Bessei
 * @version
 */
class GeneralUtility
{
    /**
     * Generate a url safe uid. Can be used for unique/secure file names.
     * @param string|null $salt
     * @return string
     */
    public static function getUrlSafeUid(string $salt = null)
    {
        if (is_null($salt)) {
            $salt = bin2hex(random_bytes(50));
        }
        return urlencode(wp_create_nonce($salt));
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return 0 === stripos($haystack, $needle);
    }
}