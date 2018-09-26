<?php

namespace Yuan1994\Jenkins\Tests\Traits;

use Yuan1994\Jenkins\Exceptions\JenkinsException;
use Yuan1994\Jenkins\Tests\TestCase;

class BuildTest extends TestCase
{
    public function testGitBuildInfo()
    {
        $data = $this->getArrayFromFile('Build/getBuildInfo.json');
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getBuildInfo('job-name', 4);

        $this->assertEquals($data, $response);
    }

    public function testGetBuildEnvVars()
    {
        $data = ['Build/getBuildInfo.json', 'Build/getBuildInfoNoVars.json'];
        $jenkins = $this->getJenkins($data);

        $vars = ['hosts' => '10.1.1.1', 'version' => 'master'];
        $responseWithVars = $jenkins->getBuildEnvVars('with-vars', 4);
        $responseWithoutVars = $jenkins->getBuildEnvVars('without-vars', 4);

        $this->assertEquals($vars, $responseWithVars);
        $this->assertEquals([], $responseWithoutVars);
    }

    public function testGetBuildGitInfo()
    {
        $data = ['Build/getBuildInfo.json', 'Build/getBuildInfoNotGit.json'];
        $jenkins = $this->getJenkins($data);

        $lastRemoteUrl = "https://git.xxx.com/tianpian/xxx";
        $responseGit = $jenkins->getBuildGitInfo('with-vars', 4);
        $responseNotGit = $jenkins->getBuildGitInfo('without-vars', 4);

        $this->assertEquals($lastRemoteUrl, $responseGit['last']['remote_url']);
        $this->assertEquals(false, $responseNotGit);
    }

    public function testGetBuildTestReport()
    {
        $testReport = 'test report';
        $data = ['body' => $testReport];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getBuildTestReport('job-name', 4);

        $this->assertEquals($testReport, $response);
    }

    public function testGetBuildTestReportEmpty()
    {
        $data = ['status' => 404];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getBuildTestReport('job-name', 4);

        $this->assertEquals(false, $response);
    }

    public function testBuildJob()
    {
        $data = [
            // queue itemId equal 109
            $this->getArrayFromFile('Build/BuildJob.php'),
            // header location does contain queue itemId
            ['headers' => ['Location: http://github.com/yuan1994/jenkins-php']],
        ];
        $jenkins = $this->getJenkins($data);

        $responseWithQueueId = $jenkins->buildJob('job-name');
        $responseWithoutQueueId = $jenkins->buildJob('job-name');

        $this->assertEquals(109, $responseWithQueueId);
        $this->assertEquals(0, $responseWithoutQueueId);
    }

    public function testStopBuild()
    {
        $data = [
            ['status' => 200],
            ['status' => 404],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->stopBuild('job-name', 4);
        $responseNotFound = $jenkins->stopBuild('job-name', 4);

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseNotFound);
    }

    public function testDeleteBuild()
    {
        $data = [
            ['status' => 200],
            ['status' => 404],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->deleteBuild('job-name', 4);
        $responseNotFound = $jenkins->deleteBuild('job-name', 4);

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseNotFound);
    }

    public function testGetRunningBuilds()
    {
        $nodes = [
            ['displayName' => 'master', 'offline' => false],
            ['displayName' => 'slave', 'offline' => true],
        ];
        $nodeInfo = $this->getArrayFromFile('Node/getNodeInfo.json');
        $expect = [
            [
                "name" => "Test4",
                "number" => 4,
                "queue" => 4,
                "url" => "http://localhost:8080/job/Test4/job/copy-copy/4/",
                "node" => "master",
                "executor" => 1,
            ]
        ];
        $data = [
            ['status' => 200, 'body' => ['computer' => $nodes]],
            ['status' => 200, 'body' => $nodeInfo],
        ];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getRunningBuilds();
        $this->assertEquals($expect, $response);
    }

    public function testGetBuildConsoleOutput()
    {
        $output = $this->getArrayFromFile('Build/getBuildConsoleOutput.php');
        $data = [
            $output,
            ['status' => 200, 'body' => null],
        ];
        $jenkins = $this->getJenkins($data);

        $responseWithOutput = $jenkins->getBuildConsoleOutput('job-name', 4);

        $this->assertEquals($output['body'], $responseWithOutput);

        $this->expectException(JenkinsException::class);

        $responseExpectJenkinsException = $jenkins->getBuildConsoleOutput('job-name', 4);
    }
}
