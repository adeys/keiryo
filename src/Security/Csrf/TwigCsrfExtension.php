<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02/02/2019
 * Time: 23:26
 */

namespace Keiryo\Security\Csrf;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigCsrfExtension extends AbstractExtension
{

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $token;

    /**
     * TwigCsrfExtension constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('csrf_token', [$this, 'csrfToken']),
            new TwigFunction('csrf_field', [$this, 'csrfField'])
        ];
    }

    /**
     * Gets a form input field for CSRF token
     *
     * @param string $name
     */
    public function csrfField(string $name = '_token')
    {
        $token = $this->csrfToken();
        echo "<input type='hidden' value='$token' name='$name'>";
    }

    /**
     * Gets CSRF token
     *
     * @return string
     */
    public function csrfToken(): ?string
    {
        if (!$this->token) {
            $this->token = $this->session->get(CsrfTokenManager::TOKEN_NAME, null);
        }

        return $this->token;
    }
}
