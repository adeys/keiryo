<?php

namespace Keiryo\Http\Session;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SessionFlash
{

    /**
     * @var FlashBagInterface
     */
    private $bag;

    /**
     * Constructor
     *
     * @param FlashBagInterface $bag
     */
    public function __construct(FlashBagInterface $bag)
    {
        $this->bag = $bag;
    }

    /**
     * Flash a success message
     *
     * @param string $message
     * @return void
     */
    public function success(string $message)
    {
        $this->bag->add('success', $message);
    }

    /**
     * Flash an error message
     *
     * @param string $message
     * @return void
     */
    public function error(string $message)
    {
        $this->bag->add('error', $message);
    }

    /**
     * Flash an info message
     *
     * @param string $message
     * @return void
     */
    public function info(string $message)
    {
        $this->bag->add('info', $message);
    }

    /**
     * Add a flash
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    public function add(string $type, string $message)
    {
        $this->bag->add($type, $message);
    }
}
