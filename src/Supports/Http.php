<?php

namespace Yuan1994\Jenkins\Supports;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Http
 *
 * @package Yuan994\Jenkins\Supports
 */
class Http
{
    /**
     * GuzzleHttp Client
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * GuzzleHttp Middleware
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * @var \GuzzleHttp\HandlerStack
     */
    protected $handlerStack = null;

    /**
     * Http constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!empty($config['middleware'])) {
            $this->middleware = array_merge($this->middleware, $config['middleware']);
        }
        unset($config['middleware']);
        // 注册GuzzleHttp中间件
        $config['handler'] = $this->getHandlerStack();
        // 实例化Client
        $this->client = new Client($config);
    }

    /**
     * Make a get request.
     *
     * @param string $uri
     * @param array  $query
     * @param array  $headers
     *
     * @return array
     */
    public function get($uri, $query = [], $headers = [])
    {
        return $this->request('get', $uri, [
            'headers' => $headers,
            'query' => $query,
        ]);
    }

    /**
     * Make a post request.
     *
     * @param string $uri
     * @param array  $params
     * @param array  $headers
     *
     * @return array
     */
    public function post($uri, $params = [], $headers = [])
    {
        return $this->request('post', $uri, [
            'headers' => $headers,
            'form_params' => $params,
        ]);
    }

    /**
     * Make a post request with json params.
     *
     * @param string $uri
     * @param array  $params
     * @param array  $headers
     *
     * @return array
     */
    public function postJson($uri, $params = [], $headers = [])
    {
        return $this->request('post', $uri, [
            'headers' => $headers,
            'json' => $params,
        ]);
    }

    /**
     * Make a http request.
     *
     * @param string $method
     * @param string $uri
     * @param array  $options  http://docs.guzzlephp.org/en/latest/request-options.html
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function request($method, $uri, $options = [])
    {
        $response = $this->client->{$method}($uri, $options);

        return $response;
    }

    /**
     * Get the GuzzleHttp Client
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Convert response contents to array.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array|string
     */
    public function unwrapResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $contents = trim($response->getBody()->getContents());

        if (false !== stripos($contentType, 'json') || stripos($contentType, 'javascript')) {
            return json_decode($contents, true);
        } elseif (false !== stripos($contentType, 'xml')) {
            return json_decode(json_encode(simplexml_load_string($contents)), true);
        }
        // 尝试强制json_decode
        $json = json_decode($contents, true);
        if (json_last_error() === 0) {
            return $json;
        }

        return $contents;
    }

    /**
     * 解析XML
     *
     * @param string $result
     * @return array|string
     */
    public function parseXml($content)
    {
        $xmlParse = xml_parser_create();
        if(!xml_parse($xmlParse, $content, true)){
            xml_parser_free($xmlParse);
            return $content;
        }else {
            return json_decode(json_encode(simplexml_load_string($content)), true);
        }
    }

    /**
     * @return \GuzzleHttp\HandlerStack
     */
    protected function getHandlerStack()
    {
        if (is_null($this->handlerStack)) {
            $this->handlerStack = HandlerStack::create();

            foreach ($this->middleware as $name => $middleware) {
                if (is_array($middleware)) {
                    // Library提供的中间件
                    if (in_array($middleware[0], [
                        'logMiddleware', 'retryMiddleware'
                    ])) {
                        $middleware = call_user_func([$this, $middleware[0]]);
                    }
                    // TODO 其他情况
                }
                $this->handlerStack->push($middleware, $name);
            }
        }

        return $this->handlerStack;
    }

    /**
     * 记录请求日志
     * 可用于Debug调试
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        // TODO $format中{response}调用$response->getBody()会导致没有结果输出
        $format = ">>>>>>>>\n{request}\n<<<<<<<<\n{res_headers}\n--------\n{error}";
        $formatter = new MessageFormatter($format);

        return Middleware::log(Log::getLogger($this->middleware['log']['config']), $formatter);
    }

    /**
     * 重试中间件
     *
     * @return \Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            if ($retries < $this->middleware['retry']['config']['times']) {
                if ($response->getStatusCode() >= 500) {
                    return true;
                }
            }

            return false;
        }, function () {
            if (empty($this->middleware['retry']['config']['delay'])) {
                return 0;
            }
            return abs($this->middleware['retry']['config']['delay']);
        });
    }
}
