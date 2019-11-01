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
     * AssetManager constructor.
     * @param string $path
     * @param string $prefix
     */
    public function __construct(string $path, string $prefix = 'default')
    {
        $this->register($path, $prefix);
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
        $type = $type ?? 'default';
        if (!isset($this->paths[$type])) {
            throw new \Exception(sprintf('Package %s has not been registered', $type));
        }

        return $type
            ? sprintf('/%s/%s', ltrim($this->paths[$type], '/'), ltrim($asset, '/'))
            : sprintf('/%s', ltrim($asset, '/'));
    }
}
