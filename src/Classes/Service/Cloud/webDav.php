<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Service\Cloud;

use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;

class webDav implements CloudInterface
{
    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;

    public function __construct(array $settings, string $basePath)
    {
        $this->filesystem = new Filesystem(new WebDAVAdapter(new Client($settings)));
    }

    public function upload(string $content, string $targetPath, $overwriteExistingFile = false)
    {
        $this->filesystem->createDir(dirname($targetPath));
        if ($overwriteExistingFile || !$this->filesystem->has($targetPath)) {
            $this->filesystem->put($targetPath, $content);
        }
    }
}