<?php

namespace Defuse\Crypto;

/**
 * Class DerivedKeys
 * @package Defuse\Crypto
 */
final class DerivedKeys
{
    /**
     * Returns the authentication key.
     * @return string
     */
    public function getAuthenticationKey()
    {
        return $this->akey;
    }

    /**
     * Returns the encryption key.
     * @return string
     */
    public function getEncryptionKey()
    {
        return $this->ekey;
    }

    /**
     * Constructor for DerivedKeys.
     *
     * @param string $akey
     * @param string $ekey
     */
    public function __construct(private $akey, private $ekey)
    {
    }
}
