<?php

namespace Yuan1994\Jenkins\Tests\Traits;

use Yuan1994\Jenkins\Exceptions\JenkinsException;
use Yuan1994\Jenkins\Tests\TestCase;

class BaseTest extends TestCase
{
    public function testGetInfoAll()
    {
        $data = $this->getArrayFromFile('Base/getInfo.json');
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getInfo();

        $this->assertEquals($data, $response);
    }

    public function testGetInfoItem()
    {
        $data = $this->getArrayFromFile('Base/getInfo.json');
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getInfo('_class');

        $this->assertEquals($data['_class'], $response);
    }

    public function testGetInfoException()
    {
        $data = $this->getArrayFromFile('Base/getInfo.json');
        $jenkins = $this->getJenkins($data);

        $this->expectException(JenkinsException::class);

        $response = $jenkins->getInfo('no_exist_item');
    }

    public function testGetWhoAmi()
    {
        $data = $this->getArrayFromFile('Base/getWhoAmi.json');
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getWhoAmi();

        $this->assertEquals($data, $response);
    }

    public function testGetVersion()
    {
        $version = '2.121.1';
        $data = [
            'headers' => ['X-Jenkins' => $version]
        ];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getVersion();

        $this->assertEquals($version, $response);
    }

    public function testGetPlugins()
    {
        $data = $this->getArrayFromFile('Base/getPlugins.json');
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getPlugins();

        $this->assertEquals($data['plugins'], array_values($response));
    }

    public function testGetPluginInfoByShortName()
    {
        $data = $this->getArrayFromFile('Base/getPlugins.json');
        $jenkins = $this->getJenkins($data);

        $name = 'jsch';
        $response = $jenkins->getPluginInfo($name);

        $this->assertEquals($name, $response['shortName']);
    }

    public function testGetPluginInfoByLongName()
    {
        $data = $this->getArrayFromFile('Base/getPlugins.json');
        $jenkins = $this->getJenkins($data);

        $name = 'Jenkins JSch dependency plugin';
        $response = $jenkins->getPluginInfo($name);

        $this->assertEquals($name, $response['longName']);
    }

    public function testGetPluginInfoNotExists()
    {
        $data = $this->getArrayFromFile('Base/getPlugins.json');
        $jenkins = $this->getJenkins($data);

        $name = 'not exist plugin';
        $response = $jenkins->getPluginInfo($name);

        $this->assertEquals(null, $response);
    }

    public function testRunScriptSuccess()
    {
        $str = 'test string';
        $magicStr = ')]}.';
        $data = "{$str}{$magicStr}";
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->runScript("print('{$str}')");

        $this->assertEquals($str, $response);
    }

    public function testRunScriptFailed()
    {
        $str = 'test string';
        $notMagicStr = 'xxx';
        $data = "{$str}{$notMagicStr}";
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->runScript("print('{$str}')");

        $this->assertNotEquals($str, $response);
    }

    public function testInstallPlugin()
    {
        $magicStr = ')]}.';
        $data = $magicStr;
        $jenkins = $this->getJenkins($data);

        $name = 'test plugin';
        $response = $jenkins->installPlugin($name);

        $this->assertEquals(true, $response);
    }

    public function testWipeoutJobWorkspaceSuccess()
    {
        $data = ['status' => 200];
        $jenkins = $this->getJenkins($data);

        $name = 'test-job-name';
        $response = $jenkins->wipeoutJobWorkspace($name);

        $this->assertEquals(true, $response);
    }

    public function testWipeoutJobWorkspaceFailed()
    {
        $data = ['status' => 404];
        $jenkins = $this->getJenkins($data);

        $name = 'test-job-name';
        $response = $jenkins->wipeoutJobWorkspace($name);

        $this->assertEquals(404, $response);
    }

    public function testQuietDownSuccess()
    {
        $data = ['quietingDown' => true];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->quietDown();

        $this->assertEquals(true, $response);
    }

    public function testQuietDownFailed()
    {
        $data = ['quietingDown' => false];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->quietDown();

        $this->assertEquals(false, $response);
    }

    public function testCancelQuietDownSuccess()
    {
        $data = ['quietingDown' => false];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->cancelQuietDown();

        $this->assertEquals(true, $response);
    }

    public function testCancelQuietDownFailed()
    {
        $data = ['quietingDown' => true];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->cancelQuietDown();

        $this->assertEquals(false, $response);
    }

    public function testSafeRestartSuccess()
    {
        $data = ['status' => 503];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->safeRestart();

        $this->assertEquals(true, $response);
    }

    public function testSafeRestartFailed()
    {
        $data = ['status' => 403];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->safeRestart();

        $this->assertEquals(403, $response);
    }

    public function testRestartSuccess()
    {
        $data = ['status' => 503];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->restart();

        $this->assertEquals(true, $response);
    }

    public function testRestartFailed()
    {
        $data = ['status' => 403];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->restart();

        $this->assertEquals(403, $response);
    }

    public function testSafeExitSuccess()
    {
        $data = ['status' => 200];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->safeExit();

        $this->assertEquals(true, $response);
    }

    public function testSafeExitFailed()
    {
        $data = ['status' => 403];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->safeExit();

        $this->assertEquals(403, $response);
    }

    public function testJenkinsExitSuccess()
    {
        $data = ['status' => 200];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->jenkinsExit();

        $this->assertEquals(true, $response);
    }

    public function testJenkinsExitFailed()
    {
        $data = ['status' => 403];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->jenkinsExit();

        $this->assertEquals(403, $response);
    }
}
