<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02/02/2019
 * Time: 22:39
 */

namespace Keiryo\Security\Authentication;

use Keiryo\Security\Authentication\Provider\UserProviderInterface;
use Keiryo\Security\Authentication\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthenticationManager implements AuthenticationManagerInterface
{
    /**
     * @var UserInterface
     */
    protected $user;
    /**
     * @var UserProviderInterface
     */
    protected $provider;
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var string
     */
    protected $cookieKey;

    /**
     * AuthManager constructor.
     * @param UserProviderInterface $provider
     * @param SessionInterface $session
     */
    public function __construct(UserProviderInterface $provider, SessionInterface $session)
    {
        $this->provider = $provider;
        $this->session = $session;
        $this->cookieKey = '_remember_me';
    }

    /**
     * Checks for authenticated user within the request
     *
     * @param Request $request
     * @return bool
     */
    public function authenticate(Request $request): bool
    {
        $session = $request->getSession();
        $this->user = $this->check($session) ?? $this->checkCookies($request);

        return (bool)$this->user;
    }

    /**
     * Search for authenticated user within the session
     *
     * @param SessionInterface $session
     * @return UserInterface|null
     */
    protected function check(SessionInterface $session): ?UserInterface
    {
        $token = $session->get('auth.token');

        return $token
            ? $this->provider->refreshUser($token)
            : null;
    }

    /**
     * Gets authenticated user
     *
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        if (!$this->user) {
            $this->user = $this->check($this->session);
        }

        return $this->user;
    }

    /**
     * Generate a cookie for authenticated user
     *
     * @param UserInterface $user
     * @return string
     */
    protected function generateCookie(UserInterface $user): string
    {
        $id = base64_encode($user->getUsername());
        return base64_encode($id . ':' . $user->getToken());
    }

    /**
     * @param string $cookie
     * @return array|null
     */
    protected function decodeCookie(string $cookie): ?array
    {
        $parts = explode(':', base64_decode($cookie, true));

        if (2 !== count($parts)) {
            return null;
        }

        return [
            base64_decode($parts[0]),
            $parts[1]
        ];
    }

    /**
     * @param Request $request
     * @return UserInterface|null
     */
    private function checkCookies(Request $request): ?UserInterface
    {
        $cookie = $request->cookies->get($this->cookieKey, null);

        if ($cookie) {
            [$username, $token] = $this->decodeCookie($cookie);
            $user = $this->provider->loadUserByUsername($username);

            return $user->getToken() === $token
                ? $user
                : null;
        }

        return null;
    }

}
