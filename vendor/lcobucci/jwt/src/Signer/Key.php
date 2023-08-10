<?php
/**
 * This file is part of Lcobucci\JWT, a simple library to handle JWT and JWS
 *
 * @license http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace Lcobucci\JWT\Signer;

use Exception;
use InvalidArgumentException;
use SplFileObject;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 * @since 3.0.4
 */
final class Key
{
    /**
     * @var string
     */
    private $content;

    /**
     * @param string $content
     * @param string $passphrase
     */
    public function __construct($content, private $passphrase = null)
    {
        $this->setContent($content);
    }

    /**
     * @param string $content
     *
     * @throws InvalidArgumentException
     */
    private function setContent($content)
    {
        if (str_starts_with($content, 'file://')) {
            $content = $this->readFile($content);
        }

        $this->content = $content;
    }

    /**
     * @param string $content
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function readFile($content)
    {
        try {
            $file    = new SplFileObject(substr($content, 7));
            $content = '';

            while (! $file->eof()) {
                $content .= $file->fgets();
            }

            return $content;
        } catch (Exception $exception) {
            throw new InvalidArgumentException('You must inform a valid key file', 0, $exception);
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }
}
