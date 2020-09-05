<?php

namespace Ringierimu\Recommend\Tests\Traits;

use Exception;

trait Guzzle
{
    /**
     * @param string $method
     * @param string $url
     * @param array $options
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function assertRequestSent(
        string $method,
        string $url,
        array $options = []
    ) {
        $request = cache()->get(md5($url . ':' . mb_strtolower($method)));

        if (!$request) {
            throw new Exception('No mocked guzzle request found, for ' . mb_strtolower($method) . ' url: ' . $url);
        }

        $this->assertEquals($options, $request);

        return $this;
    }

    /**
     * @param string $method
     * @param string $url
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function assertRequestNotSent(
        string $method,
        string $url
    ) {
        $request = cache()->get(md5($url . ':' . mb_strtolower($method)));

        $this->assertNull($request);

        return $this;
    }

    /**
     * @param string $url
     * @param string $fixturePath
     * @param int $code
     */
    public function setGuzzleResponse(
        string $url,
        string $fixturePath,
        int $code = 200
    ) {
        cache()->put(
            md5('response:' . $url),
            compact('fixturePath', 'code')
        );
    }
}
