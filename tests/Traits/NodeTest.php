<?php

namespace Yuan1994\Jenkins\Tests\Traits;

use Yuan1994\Jenkins\Consts\URL;
use Yuan1994\Jenkins\Exceptions\JenkinsException;
use Yuan1994\Jenkins\Tests\TestCase;

class NodeTest extends TestCase
{
    public function testGetNodes()
    {
        $nodes = [
            ['displayName' => 'master', 'offline' => false],
            ['displayName' => 'slave', 'offline' => true],
        ];
        $expect = [
            ['name' => 'master', 'offline' => false],
            ['name' => 'slave', 'offline' => true],
        ];
        $data = ['computer' => $nodes];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getNodes();

        $this->assertEquals($expect, $response);
    }

    public function testGetNodeInfo()
    {
        $node = $this->getArrayFromFile('Node/getNodeInfo.json');
        $data = [
            ['status' => 200, 'body' => $node],
            ['status' => 404, 'body' => 'not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getNodeInfo('master');
        $responseError = $jenkins->getNodeInfo('not-exist-node');

        $this->assertEquals($node, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }

    public function testNodeExists()
    {
        $node = $this->getArrayFromFile('Node/getNodeInfo.json');
        $data = [
            ['status' => 200, 'body' => $node],
            ['status' => 404, 'body' => 'not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->nodeExists('master');
        $responseError = $jenkins->nodeExists('not-exist-node');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }

    public function testDeleteNode()
    {
        $data = [
            ['status' => 200, 'body' => 'success'],
            ['status' => 404, 'body' => 'not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->deleteNode('slave');
        $responseError = $jenkins->deleteNode('not-exist-node');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testDisableNode()
    {
        $node = $this->getArrayFromFile('Node/getNodeInfo.json');
        $data = [
            // mockery data is for Jenkins::getNodeInfo
            ['status' => 200, 'body' => $node],
            ['status' => 200, 'body' => 'success'],
            // mockery data is for Jenkins::getNodeInfo
            ['status' => 200, 'body' => $node],
            ['status' => 404, 'body' => 'not found'],
            // mockery data is for Jenkins::getNodeInfo
            ['status' => 404, 'body' => 'not found'],
            ['status' => 404, 'body' => 'not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->disableNode('slave');
        $responseError = $jenkins->disableNode('slave');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);

        $this->expectException(JenkinsException::class);

        $expectJenkinsException = $jenkins->disableNode('not-exist-node');
    }

    public function testEnableNode()
    {
        $node = $this->getArrayFromFile('Node/getNodeInfo.json');
        $node['offline'] = true;
        $data = [
            // mockery data is for Jenkins::getNodeInfo
            ['status' => 200, 'body' => $node],
            ['status' => 200, 'body' => 'success'],
            // mockery data is for Jenkins::getNodeInfo
            ['status' => 200, 'body' => $node],
            ['status' => 404, 'body' => 'not found'],
            // mockery data is for Jenkins::getNodeInfo
            ['status' => 404, 'body' => 'not found'],
            ['status' => 404, 'body' => 'not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->enableNode('slave');
        $responseError = $jenkins->enableNode('slave');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);

        $this->expectException(JenkinsException::class);

        $expectJenkinsException = $jenkins->enableNode('not-exist-node');
    }

    public function testCreateNode()
    {
        $node = $this->getArrayFromFile('Node/getNodeInfo.json');
        $data = [
            // mockery data is for Jenkins::getNodeInfo
            ['status' => 404, 'body' => 'not found'],
            ['status' => 200, 'body' => 'success'],
            // mockery data is for Jenkins::getNodeInfo
            ['status' => 404, 'body' => 'not found'],
            ['status' => 404, 'body' => 'not found'],
            // mockery data is for Jenkins::getNodeInfo
            ['status' => 200, 'body' => $node],
            ['status' => 404, 'body' => 'not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->createNode(
            'not-exist-slave', 2, 'slave node', '/var/lib/jenkins', null, false,
            URL::LAUNCHER_COMMAND, ['command' => 'launcher command']
        );
        $responseError = $jenkins->createNode(
            'not-exist-slave', 2, 'slave node', '/var/lib/jenkins', null, false,
            URL::LAUNCHER_COMMAND, ['command' => 'launcher command']
        );;

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);

        $this->expectException(JenkinsException::class);

        $expectJenkinsException = $jenkins->createNode(
            'exist-slave', 2, 'slave node', '/var/lib/jenkins', null, false,
            URL::LAUNCHER_COMMAND, ['command' => 'launcher command']
        );;
    }

    public function testGetNodeConfig()
    {
        $configXml = file_get_contents(__DIR__.'/../data/Node/config.xml');
        $data = [
            ['status' => 200, 'body' => $configXml],
            ['status' => 404, 'body' => 'not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getNodeConfig('master');
        $responseError = $jenkins->getNodeConfig('not-exist-node');

        $this->assertEquals($configXml, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }

    public function testReconfigNode()
    {
        $configXml = file_get_contents(__DIR__.'/../data/Node/config.xml');
        $data = [
            ['status' => 200, 'body' => 'success'],
            ['status' => 404, 'body' => 'not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->reconfigNode('slave', $configXml);
        $responseError = $jenkins->reconfigNode('not-exist-node', $configXml);

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

}
