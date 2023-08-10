<?php
namespace GuzzleHttp\Exception;

use Psr\Http\Message\StreamInterface;

/**
 * Exception thrown when a seek fails on a stream.
 */
class SeekException extends \RuntimeException implements GuzzleException
{
    public function __construct(private readonly StreamInterface $stream, $pos = 0, $msg = '')
    {
        $msg = $msg ?: 'Could not seek the stream to position ' . $pos;
        parent::__construct($msg);
    }

    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }
}
