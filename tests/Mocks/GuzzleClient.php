<?php

namespace Ringierimu\Recommend\Tests\Mocks;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Http\Message\StreamFactory\GuzzleStreamFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleClient extends Client implements ClientInterface
{
    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @throws \Exception
     *
     * @return ResponseInterface
     */
    public function request(
        $method,
        $uri = '',
        array $options = []
    ): ResponseInterface {
        $path = str_replace(
            '?' . array_get(parse_url($uri), 'query'),
            '',
            $uri
        );

        cache()->put(
            md5($path . ':' . mb_strtolower($method)),
            $options,
            1 * 60
        );

        return $this->getGuzzleResponse($path);
    }

    /**
     * {@inheritdoc}
     */
    public function post(
        $uri,
        array $options = []
    ): ResponseInterface {
        return $this
            ->request(
                'post',
                $uri,
                $options
            );
    }

    /**
     * @param string $uri
     *
     * @throws \Exception
     *
     * @return ResponseInterface
     */
    protected function getGuzzleResponse(
        string $uri
    ): ResponseInterface {
        $response = resolve(Response::class);

        $responseArray = cache()->get(md5('response:' . $uri));
        if (! $responseArray) {
            return $response;
        }

        $json = file_get_contents(base_path($responseArray['fixturePath']));

        return $response
            ->withStatus($responseArray['code'])
            ->withBody(
                resolve(GuzzleStreamFactory::class)
                    ->createStream($json)
            );
    }

    /**
     * {@inheritdoc}
     */
    public function send(
        RequestInterface $request,
        array $options = []
    ): ResponseInterface {
        return $this->request(
            $request->getMethod(),
            (string) $request->getUri(),
            $options
        );
    }
}
