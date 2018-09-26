<?php

namespace Yuan1994\Jenkins;

use Psr\Http\Message\ResponseInterface;
use Yuan1994\Jenkins\Consts\URL;
use Yuan1994\Jenkins\Supports\Http;
use Yuan1994\Jenkins\Traits\Base;
use Yuan1994\Jenkins\Traits\Build;
use Yuan1994\Jenkins\Traits\Job;
use Yuan1994\Jenkins\Traits\Node;
use Yuan1994\Jenkins\Traits\Promotion;
use Yuan1994\Jenkins\Traits\Queue;
use Yuan1994\Jenkins\Traits\View;
use GuzzleHttp\Exception\TransferException;

class Jenkins
{
    /**
     * SDK Version
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Jenkins Base URL
     *
     * @var string
     */
    protected $url;

    /**
     * Config
     *
     * @var array
     */
    protected $config = [];

    /**
     * Http Client
     *
     * @var \Yuan1994\Jenkins\Supports\Http
     */
    protected $http;

    /**
     * @var array
     */
    protected $crumb = null;

    /**
     * @var null|string
     */
    protected $authorization = null;

    use Base, Build, Job, Node, Promotion, Queue, View;

    /**
     * Jenkins constructor.
     *
     * @param string $url
     * @param array  $config
     * @param Http   $http
     */
    public function __construct($url, array $config = [], Http $http = null)
    {
        $this->url = rtrim($url, '/') . '/';

        $this->authorization = 'Basic ' .
            base64_encode("{$config['username']}:{$config['password']}");

        $config['guzzle']['base_uri'] = $this->url;

        if (empty($http)) {
            $this->http = new Http(empty($config['guzzle']) ? [] : $config['guzzle']);
        } else {
            $this->http = $http;
        }

        unset($config['guzzle']);

        $this->config = $config;
    }

    /**
     * @param string $url
     * @param array  $params
     * @return string
     */
    protected function buildUrl($url, $params = [])
    {
        return preg_replace_callback('/\{(.*?)\}/', function($m) use ($params){
            return $params[$m[1]];
        } , $url);
    }

    /**
     * @param array $request
     * @param bool  $addCrumb
     * @param bool  $resolveAuth
     * @return array|null|string
     */
    protected function jenkinsOpen($request, $addCrumb = true, $resolveAuth = true)
    {
        $response = $this->jenkinsRequest($request, $addCrumb, $resolveAuth);

        if (empty($response)) {
            return null;
        }

        return $this->http->unwrapResponse($response);
    }

    /**
     * @param array $request
     * @param bool  $addCrumb
     * @param bool  $resolveAuth
     * @return \GuzzleHttp\Psr7\Response
     * @throws \Exception
     */
    protected function jenkinsRequest($request, $addCrumb = true, $resolveAuth = true)
    {
        try {
            $httpHeaders = [];
            if ($resolveAuth) {
                $httpHeaders['Authorization'] = $this->authorization;
            }
            if ($addCrumb && (!empty($this->config['maybe_add_crumb']))) {
                $this->maybeAddCrumb($httpHeaders);
            }
            if (isset($request[2]) && isset($request[2]['headers'])) {
                $httpHeaders = array_merge($httpHeaders, $request[2]['headers']);
            }
            $request[2]['headers'] = $httpHeaders;
            $response = call_user_func_array([$this->http, 'request'], $request);
            return $response;
        } catch (TransferException $e) {
            return $e->getResponse();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Response return true if status code equal expect code not return status code.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return bool|int
     */
    protected function getResponseTrueOrStatusCode(ResponseInterface $response, $expect = 200)
    {
        if (($status = $response->getStatusCode()) == $expect) {
            return true;
        } else {
            return $status;
        }
    }

    /**
     * Response return unwrap data if status code equal expect code not return false.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int                                 $expect
     * @return array|bool|string
     */
    protected function getResponseFalseOrContents(ResponseInterface $response, $expect = 200)
    {
        if ($response->getStatusCode() == $expect) {
            return $this->http->unwrapResponse($response);
        }

        return false;
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getJobFolder($name)
    {
        $paths = explode('/', $name);
        $shortName = array_pop($paths);
        $folderUrl = $paths ? 'job/' . implode('/', $paths) . '/' : '';

        return ['folder_url' => $folderUrl, 'short_name' => $shortName];
    }

    /**
     * @param array $headers
     */
    protected function maybeAddCrumb(&$headers)
    {
        if (is_null($this->crumb)) {
            try {
                $response = $this->jenkinsOpen([
                    'GET', $this->buildUrl(URL::CRUMB_URL)
                ], false);
                if ($response) {
                    $this->crumb = $response;
                }
            } catch (\Exception $e) {

            }
        }

        if (!is_null($this->crumb)) {
            $headers[$this->crumb['crumbRequestField']] = $this->crumb['crumb'];
        }
    }
}
