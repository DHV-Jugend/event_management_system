<?php

namespace BIT\EMS\Utility;

/**
 * @author Christoph Bessei
 * @version
 */
class General
{
    /**
     * Generate a url safe uid. Can be used for unique/secure file names.
     * @param string|null $salt
     * @return string
     */
    public static function getUrlSafeUid($salt = null)
    {
        if (is_null($salt)) {
            $salt = uniqid(strval(microtime(true)), true);
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