<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08/08/2019
 * Time: 02:01
 */

namespace App\BankuModule\Controller;

use Simplex\Renderer\TwigRenderer;

class HomeController
{

    /**
     * @var TwigRenderer
     */
    private $view;

    public function __construct(TwigRenderer $renderer)
    {

        $this->view = $renderer;
    }

    public function index()
    {
        $data = ['branches' => 5, 'accounts' => 30, 'transactions' => 1125];
        return $this->view->render('@banku/dashboard', $data);
    }
}
