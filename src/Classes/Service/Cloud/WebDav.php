<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Service\Cloud;

use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;

class WebDav implements CloudInterface
{
    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;

    public function __construct(array $settings)
    {
        $this->filesystem = new Filesystem(new WebDAVAdapter(new Client($settings)));
    }

    public function upload(string $content, string $targetPath, $overwriteExistingFile = false)
    {
        $this->filesystem->createDir(dirname($targetPath));
        $doesExist = $this->filesystem->has($targetPath);

        if ($overwriteExistingFile && $doesExist) {
            return $this->filesystem->update($targetPath, $content);
        } elseif (!$doesExist) {
            return $this->filesystem->put($targetPath, $content);
        }

        return true;
    }
}