<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 22/06/2019
 * Time: 20:28
 */

namespace Keiryo\Events;

use Symfony\Component\HttpFoundation\Response;

class KernelResponseEvent
{

    /**
     * @var Response
     */
    private $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
