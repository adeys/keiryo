<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 28/07/2019
 * Time: 19:05
 */

namespace Keiryo\Asset;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetExtension extends AbstractExtension
{

    /**
     * @var AssetManager
     */
    private $manager;

    /**
     * AssetExtension constructor.
     * @param AssetManager $manager
     */
    public function __construct(AssetManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('asset', function (string $asset, string $type = null) {
                $url = $this->manager->getUrl($asset, $type);
                return $url;
            })
        ];
    }
}
