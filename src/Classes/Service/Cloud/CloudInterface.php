<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Service\Cloud;


interface CloudInterface
{
    /**
     * CloudInterface constructor.
     * @param array $settings
     */
    public function __construct(array $settings);

    /**
     * @param string $content
     * @param string $targetPath
     * @param bool $overwriteExistingFile
     * @return bool
     */
    public function upload(string $content, string $targetPath, $overwriteExistingFile = false);
}