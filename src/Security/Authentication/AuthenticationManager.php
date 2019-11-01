<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02/02/2019
 * Time: 22:39
 */

namespace Keiryo\Security\Authentication;

use Keiryo\Http\CookieStorage;
use Keiryo\Security\Authentication\Provider\UserProviderInterface;
use Keiryo\Security\Authentication\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthenticationManager
{
    /**
     * @var UserInterface
     */
    private $user;
    /**
     * @var UserProviderInterface
     */
    private $provider;
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var CookieStorage
     */
    private $cookies;

    /**
     * @var string
     */
    private $cookieKey;

    /**
     * @var string
     */
    private $loginPath = '/login';

    /**
     * AuthManager constructor.
     * @param UserProviderInterface $provider
     * @param SessionInterface $session
     * @param CookieStorage $cookies
     */
    public function __construct(UserProviderInterface $provider, SessionInterface $session, CookieStorage $cookies)
    {
        $this->provider = $provider;
        $this->session = $session;
        $this->cookies = $cookies;
        $this->cookieKey = 'Keiryo_remember';
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
        $token = $session->get('auth.user');

        return $token
            ? $this->provider->refreshUser($token)
            : null;
    }

    /**
     * Performs a login using $credentials
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function login(array $credentials, bool $remember = false): bool
    {
        $user = $this->provider->loadUserByUsername($credentials['login']);

        if ($user && (password_verify($credentials['password'], $user->getPassword()))) {
            $this->user = $user;
            $this->session->set('auth.user', $user->getToken());
            $this->session->migrate();

            if ($remember) {
                $cookie = $this->generateCookie($this->user);
                $expires = (new \DateTime())->add(new \DateInterval('P7D'));
                $this->cookies->setCookie($this->cookieKey, $cookie, $expires);
            }
            return true;
        }

        return false;
    }

    /**
     * Performs a logout
     *
     * @return bool
     */
    public function logout(): bool
    {
        $this->provider->forget($this->getUser());
        $this->user = null;
        $this->cookies->setCookie($this->cookieKey, null, -256000);
        $this->session->remove('auth.user');
        return $this->session->migrate();
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
    private function generateCookie(UserInterface $user): string
    {
        $id = base64_encode($user->getUsername());
        return base64_encode($id . ':' . $user->getToken());
    }

    /**
     * @param string $cookie
     * @return array|null
     */
    private function decodeCookie(string $cookie): ?array
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

    /**
     * @return string
     */
    public function getLoginPath(): string
    {
        return $this->loginPath;
    }

    /**
     * @param string $loginPath
     */
    public function setLoginPath(string $loginPath): void
    {
        $this->loginPath = $loginPath;
    }
}
