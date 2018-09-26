<?php

namespace Yuan1994\Jenkins\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Yuan1994\Jenkins\Jenkins;
use Yuan1994\Jenkins\Supports\Http;
use GuzzleHttp\Psr7\Response;

/**
 * Class TestCase
 *
 * @package Yuan1994\Jenkins\Tests
 */
class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    protected $url = 'http://localhost:8080';

    /**
     * @var string
     */
    protected $username = 'jenkins';

    /**
     * @var string
     */
    protected $password = 'password';

    /**
     * @param null|array|string $mockData
     * @return \Yuan1994\Jenkins\Jenkins
     */
    public function getJenkins($mockData = null)
    {
        $data = [];
        if (is_array($mockData)) {
            if (isset($mockData[0])) {
                $basePath = __DIR__ .'/data/';
                foreach ($mockData as $mockItem) {
                    if (is_array($mockItem)) {
                        // if the data source is array
                        $response = $this->buildResponseFromArray($mockItem);
                    } else {
                        if (is_file($basePath . $mockItem)) {
                            // if the data source is a file
                            $array = $this->getArrayFromFile($mockItem);
                            $response = $this->buildResponseFromArray($array);
                        } else {
                            // the data source is string
                            $response = new Response(200, [], $mockItem);
                        }
                    }
                    $data[] = $response;
                }
            } else {
                $response = $this->buildResponseFromArray($mockData);
                $data = [$response];
            }
        } else {
            $response = new Response(200, [], $mockData);
            $data = [$response];
        }
        $mockHttp = \Mockery::mock(Http::class . "[request]");
        $mockHttp->shouldReceive('request')->andReturnValues($data);

        $config = [
            'username' => $this->username,
            'password' => $this->password,
            'maybe_add_crumb' => false,
        ];
        $jenkins = new Jenkins($this->url, $config, $mockHttp);

        return $jenkins;
    }

    /**
     * @param array $array
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function buildResponseFromRawArray(array $array)
    {
        $headers = ['Content-Type' => 'application/json'];
        $body = json_encode($array);

        return new Response(200, $headers, $body);
    }

    /**
     * @param array $array
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function buildResponseFromArray(array $array)
    {
        if (!(isset($array['body']) || isset($array['headers']) || isset($array['status']))) {
            return $this->buildResponseFromRawArray($array);
        }

        $status = empty($array['status']) ? 200 : $array['status'];
        $headers = empty($array['headers']) ? [] : $array['headers'];
        $body = empty($array['body']) ? null : $array['body'];
        if (is_array($body)) {
            $body = json_encode($body);
            $headers[] = ['Content-Type' => 'application/json'];
        }

        return new Response($status, $headers, $body);
    }

    /**
     * @param string $filename
     * @return array|string
     */
    protected function getArrayFromFile($filename)
    {
        $basePath = __DIR__ .'/data/';
        if (is_file(($file = $basePath . $filename))) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'json':
                    $array = json_decode(file_get_contents($file), true);
                    break;
                case 'php':
                    // php data source
                    $array = include $file;
                    break;
                case 'xml':
                    // pass
                default:
                    $array = file_get_contents($file);
            }

            return $array;
        }

        return $file;
    }

    /**
     * Tear down the test case.
     */
    public function tearDown()
    {
        $this->finish();
        parent::tearDown();
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->Mockery_getExpectationCount());
        }
        \Mockery::close();
    }

    /**
     * Run extra tear down code.
     */
    protected function finish()
    {
        // call more tear down methods
    }
}
