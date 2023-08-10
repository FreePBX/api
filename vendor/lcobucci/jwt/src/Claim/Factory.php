<?php
/**
 * This file is part of Lcobucci\JWT, a simple library to handle JWT and JWS
 *
 * @license http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace Lcobucci\JWT\Claim;

use Lcobucci\JWT\Claim;

/**
 * Class that create claims
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 * @since 2.0.0
 */
class Factory
{
    /**
     * The list of claim callbacks
     *
     * @var array
     */
    private $callbacks;

    /**
     * Initializes the factory, registering the default callbacks
     */
    public function __construct(array $callbacks = [])
    {
        $this->callbacks = array_merge(
            [
                'iat' => $this->createLesserOrEqualsTo(...),
                'nbf' => $this->createLesserOrEqualsTo(...),
                'exp' => $this->createGreaterOrEqualsTo(...),
                'iss' => $this->createEqualsTo(...),
                'aud' => $this->createEqualsTo(...),
                'sub' => $this->createEqualsTo(...),
                'jti' => $this->createEqualsTo(...)
            ],
            $callbacks
        );
    }

    /**
     * Create a new claim
     *
     * @param string $name
     *
     * @return Claim
     */
    public function create($name, mixed $value)
    {
        if (!empty($this->callbacks[$name])) {
            return call_user_func($this->callbacks[$name], $name, $value);
        }

        return $this->createBasic($name, $value);
    }

    /**
     * Creates a claim that can be compared (greator or equals)
     *
     * @param string $name
     *
     * @return GreaterOrEqualsTo
     */
    private function createGreaterOrEqualsTo($name, mixed $value)
    {
        return new GreaterOrEqualsTo($name, $value);
    }

    /**
     * Creates a claim that can be compared (greator or equals)
     *
     * @param string $name
     *
     * @return LesserOrEqualsTo
     */
    private function createLesserOrEqualsTo($name, mixed $value)
    {
        return new LesserOrEqualsTo($name, $value);
    }

    /**
     * Creates a claim that can be compared (equals)
     *
     * @param string $name
     *
     * @return EqualsTo
     */
    private function createEqualsTo($name, mixed $value)
    {
        return new EqualsTo($name, $value);
    }

    /**
     * Creates a basic claim
     *
     * @param string $name
     *
     * @return Basic
     */
    private function createBasic($name, mixed $value)
    {
        return new Basic($name, $value);
    }
}
