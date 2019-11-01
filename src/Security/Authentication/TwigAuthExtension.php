<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03/02/2019
 * Time: 06:46
 */

namespace Keiryo\Security\Authentication;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigAuthExtension extends AbstractExtension
{

    /**
     * @var AuthenticationManager
     */
    private $auth;

    /**
     * TwigAuthExtension constructor.
     * @param AuthenticationManager $auth
     */
    public function __construct(AuthenticationManager $auth)
    {

        $this->auth = $auth;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('current_user', [$this->auth, 'getUser'])
        ];
    }
}
