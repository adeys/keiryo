<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 28/07/2019
 * Time: 07:21
 */

namespace Keiryo\Asset;

class AssetManager
{

    /**
     * @var string[]
     */
    private $paths = [];
    /**
     * @var string
     */
    private $basePath;

    /**
     * AssetManager constructor.
     * @param string $basePath
     */
    public function __construct(string $basePath = '/')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * @param string $path
     * @param string $prefix
     */
    public function register(string $path, string $prefix)
    {
        $this->paths[$prefix] = $path;
    }

    /**
     * @param string $asset
     * @param string|null $type
     * @return string
     * @throws \Exception
     */
    public function getUrl(string $asset, ?string $type)
    {
        if (!isset($this->paths[$type])) {
            throw new \Exception(sprintf('Package %s has not been registered', $type));
        }

        $url = $type
            ? sprintf('/%s/%s', ltrim($this->paths[$type], '/'), ltrim($asset, '/'))
            : sprintf('/%s', ltrim($asset, '/'));

        return $this->basePath . $url;
    }
}
