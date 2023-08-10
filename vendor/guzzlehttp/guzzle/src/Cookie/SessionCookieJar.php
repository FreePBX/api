<?php
namespace GuzzleHttp\Cookie;

/**
 * Persists cookies in the client session
 */
class SessionCookieJar extends CookieJar
{
    /**
     * Create a new SessionCookieJar object
     *
     * @param string $sessionKey        Session key name to store the cookie
     *                                  data in session
     * @param bool $storeSessionCookies Set to true to store session cookies
     *                                  in the cookie jar.
     */
    public function __construct(private $sessionKey, private $storeSessionCookies = false)
    {
        $this->load();
    }

    /**
     * Saves cookies to session when shutting down
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Save cookies to the client session
     */
    public function save()
    {
        $json = [];
        foreach ($this as $cookie) {
            /** @var SetCookie $cookie */
            if (CookieJar::shouldPersist($cookie, $this->storeSessionCookies)) {
                $json[] = $cookie->toArray();
            }
        }

        $_SESSION[$this->sessionKey] = json_encode($json, JSON_THROW_ON_ERROR);
    }

    /**
     * Load the contents of the client session into the data array
     */
    protected function load()
    {
        if (!isset($_SESSION[$this->sessionKey])) {
            return;
        }
        $data = json_decode((string) $_SESSION[$this->sessionKey], true, 512, JSON_THROW_ON_ERROR);
        if (is_array($data)) {
            foreach ($data as $cookie) {
                $this->setCookie(new SetCookie($cookie));
            }
        } elseif (strlen((string) $data)) {
            throw new \RuntimeException("Invalid cookie data");
        }
    }
}
