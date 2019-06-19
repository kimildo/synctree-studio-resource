<?php
namespace middleware;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Container\ContainerInterface;

use libraries\log\LogMessage;
use libraries\util\CommonUtil;

class Common
{
    private $ci;
    private $isLoggingResponse;

    public function __construct(ContainerInterface $ci, $isLoggingResponse = false)
    {
        $this->ci = $ci;
        $this->isLoggingResponse = $isLoggingResponse;
    }

    /**
     *
     * middleware invokable class
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        /*
         * get params in http body
         */
        try {
            $params = CommonUtil::getParams($request);
        } catch (\Exception $ex) {
            LogMessage::error($ex->getMessage());
            throw $ex;
        }

        $uri = $request->getUri()->getPath();
        LogMessage::info('[middleware] uri:: ' . $uri);

        /*
         * logging route name
         */
        $route = $request->getAttribute('route');
        $routeName = $route->getName();

        LogMessage::info('[middleware] route:: ' . $routeName);

        /*
         * logging http request body contents
         */
        LogMessage::info('[middleware] headers:: ' . json_encode($request->getHeaders(), JSON_UNESCAPED_UNICODE));

        /*
         * logging http request body contents
         */
        LogMessage::info('[middleware] request:: ' . json_encode($params, JSON_UNESCAPED_UNICODE));

        $ref = $request->getHeader('HTTP_REFERER');
        LogMessage::info('[middleware] refferer::' . json_encode($ref, JSON_UNESCAPED_UNICODE));

        /*
         * set http params to requeset attribute
         */
        $request = $request->withAttribute('params', $params)
                 ->withAttribute('route_name', $routeName)
                 ->withAttribute('method_name', $uri)
                 ;




        /*
         * call next middleware or application
         */
        $response = $next($request, $response);

        /*
         * if logging response is true;;
         */
        if (true === $this->isLoggingResponse) {
            /*
             * logging http response body contents
             */
            LogMessage::info('[middleware]response::' . $response->getBody());
        }

        return $response;
    }
}
