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
     * @return string
     */
    public static function getUrlSafeUid()
    {
        return urlencode(wp_create_nonce(bin2hex(random_bytes(50))));
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