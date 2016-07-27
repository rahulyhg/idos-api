<?php

/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types=1);

namespace App\Handler;

use App\Command\ResponseDispatch;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Slim\HttpCache\CacheProvider;

/**
 * Handles HTTP Responses.
 */
class Response implements HandlerInterface {
    private $httpCache;
    private $validator;

    private function jsonResponse(
        ResponseInterface $response,
        array $body,
        $statusCode = 200
    ) {
        unset($body['list'][0]['private_key']);
        $body     = json_encode($body);
        $response = $this->httpCache->withEtag($response, sha1($body), 'weak');

        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->write($body);
    }

    private function javascriptResponse(
        ResponseInterface $response,
        array $body,
        $statusCode = 200,
        $callback = 'jsonp'
    ) {
        $body     = sprintf('/**/%s(%s)', $callback, json_encode($body));
        $response = $this->httpCache->withEtag($response, sha1($body), 'weak');

        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/javascript')
            ->write($body);
    }

    private function xmlResponse(
        ResponseInterface $response,
        array $body,
        $statusCode = 200
    ) {
        $xml = new \SimpleXMLElement('<veridu/>');
        array_walk_recursive(
            $body,
            function ($value, $key) use ($xml) {
                if (is_bool($value))
                    $xml->addChild($key, ($value ? 'true' : 'false'));
                else
                    $xml->addChild($key, $value);
            }
        );
        $body     = $xml->asXML();
        $response = $this->httpCache->withEtag($response, sha1($body), 'weak');

        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/xml; charset=utf-8')
            ->write($body);
    }

    private function textResponse(
        ResponseInterface $response,
        array $body,
        $statusCode = 200
    ) {
        $body     = http_build_query($body);
        $response = $this->httpCache->withEtag($response, sha1($body), 'weak');

        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'text/plain')
            ->write($body);
    }

    public static function register(ContainerInterface $container) {
        $container[self::class] = function (ContainerInterface $container) {
            return new \App\Handler\Response(
                $container->get('httpCache'),
                $container->get('validator')
            );
        };
    }

    public function __construct(CacheProvider $httpCache, Validator $validator) {
        $this->httpCache = $httpCache;
        $this->validator = $validator;
    }

    public function handleResponseDispatch(ResponseDispatch $command) {
        $request    = $command->request;
        $response   = $command->response;
        $body       = $command->body;
        $statusCode = $command->statusCode;

        if (! isset($body['status']))
            $body = array_merge(['status' => true], $body);

        $queryParams = $request->getQueryParams();

        // Forces HTTP errors (4xx and 5xx) to be suppressed
        if (($statusCode >= 400)
            && (isset($queryParams['failSilently']))
            && ($this->validator->trueVal()->validate($queryParams['failSilently']))
        )
            $statusCode = 200;

        // Suppresses links field on response body
        if ((isset($body['links'], $queryParams['hideLinks']))
            && ($this->validator->trueVal()->validate($queryParams['hideLinks']))
        )
            unset($body['links']);

        // Overrides HTTP's Accept header
        if (! empty($queryParams['forceOutput'])) {
            switch (strtolower($queryParams['forceOutput'])) {
                // case 'plain':
                // 	$accept = ['text/plain'];
                // 	break;
                case 'xml':
                    $accept = ['application/xml'];
                    break;
                case 'javascript':
                    $accept = ['application/javascript'];
                    break;
                case 'json':
                default:
                    $accept = ['application/json'];
            }
        } else {
            // Extracts HTTP's Accept header
            $accept = $request->getHeaderLine('Accept');

            if (preg_match_all('/([^\/]+\/[^;,]+)[^,]*,?/', $accept, $matches)) {
                $accept = $matches[1];
            } else
                $accept = ['application/json'];
        }

        // Last Modified Cache Header
        if (isset($body['updated']))
            $response = $this
                ->httpCache
                ->withLastModified($response, $body['updated']);
        elseif (isset($body['data']['updated']))
            $response = $this
                ->httpCache
                ->withLastModified($response, $body['data']['updated']);

        // Force Content-Type to be used
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');

        // if ((in_array('text/html', $accept)) || (in_array('text/plain', $accept)))
        // 	return $this->textResponse($response, $body, $statusCode);

        // if (in_array('application/xml', $accept))
        // 	return $this->xmlResponse($response, $body, $statusCode);

        if (in_array('application/javascript', $accept)) {
            if (empty($queryParams['callback']))
                $callback = 'jsonp';
            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $callback))
                $callback = 'jsonp';

            return $this->javascriptResponse($response, $body, $statusCode, $callback);
        }

        return $this->jsonResponse($response, $body, $statusCode);
    }
}
