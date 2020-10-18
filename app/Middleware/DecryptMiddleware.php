<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Response;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DecryptMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(ContainerInterface $container, RequestInterface $request)
    {
        $this->container = $container;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestData = $this->request->input('requestData');

        if ($requestData) {
            $requestData = decrypt($requestData);
            if ($requestData) {
                $parms = array_merge($this->request->all(), $requestData);
                $request = $request->withQueryParams($parms);

                /*
                 * 由于Hyperf\HttpServer\Request的getInputData是放协程中并且是自命名，需要指定更新改变对象才能传递上下文
                 */
                Context::override('http.request.parsedData', function () use ($request) {
                    if (is_array($request->getParsedBody())) {
                        $data = $request->getParsedBody();
                    } else {
                        $data = [];
                    }

                    return array_merge($data, $request->getQueryParams());
                });
                $request = Context::set(ServerRequestInterface::class, $request);
            }
        }

        return $handler->handle($request);
    }
}
