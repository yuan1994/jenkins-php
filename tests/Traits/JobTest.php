<?php

namespace Yuan1994\Jenkins\Tests\Traits;

use Yuan1994\Jenkins\Exceptions\JenkinsException;
use Yuan1994\Jenkins\Tests\TestCase;

class JobTest extends TestCase
{
    public function testGetJobInfo()
    {
        $name = 'test-job-name';
        $data = $this->getArrayFromFile('Job/getJobInfo.json');
        $data['name'] = $name;
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getJobInfo($name);

        $this->assertEquals($name, $response['name']);
    }

    public function testGetJobName()
    {
        $jobName = 'job-name';
        $data = [
            ['status' => 200, 'body' => ['name' => $jobName]],
            ['status' => 200, 'body' => null],
        ];
        $jenkins = $this->getJenkins($data);

        $responseWithName = $jenkins->getJobName($jobName);
        $responseWithNull = $jenkins->getJobName($jobName);

        $this->assertEquals($jobName, $responseWithName);
        $this->assertEquals(null, $responseWithNull);
    }

    public function testGetAllJobs()
    {
        $array = $this->getArrayFromFile('Job/getInfoWithJobs.json');
        $data = [$array];
        $jobs = $array['jobs'];
        // remove empty folder
        unset($jobs[3]);
        $expect = array_values($jobs);
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getAllJobs();

        $this->assertEquals($expect, $response);
    }

    public function testJobsCount()
    {
        $array = $this->getArrayFromFile('Job/getInfoWithJobs.json');
        $data = [$array];
        $jobs = $array['jobs'];
        // remove empty folder
        unset($jobs[3]);
        $expect = count(array_values($jobs));
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->jobsCount();

        $this->assertEquals($expect, $response);
    }

    public function testCopyJob()
    {
        $data = [
            ['status' => 200],
            ['status' => 404],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->copyJob('from-job-name', 'to-job-name');
        $responseError = $jenkins->copyJob('from-job-name', 'to-job-name');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testJobExist()
    {
        $jobName = 'exist-job-name';
        $data = $this->getArrayFromFile('Job/getJobInfo.json');
        $data['name'] = $jobName;

        $jenkins = $this->getJenkins($data);

        $response = $jenkins->jobExists($jobName);

        $this->assertEquals(true, $response);
    }

    public function testJobExistNotExist()
    {
        $jobName = 'exist-job-name';
        // The real job name is job-name
        $data = $this->getArrayFromFile('Job/getJobInfo.json');

        $jenkins = $this->getJenkins($data);

        $response = $jenkins->jobExists($jobName);

        $this->assertEquals(false, $response);
    }

    public function testRenameJob()
    {
        $data = [
            ['status' => 200],
            ['status' => 404],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->renameJob('from-job-name', 'to-job-name');
        $responseError = $jenkins->renameJob('from-job-name', 'to-job-name');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);

        $this->expectException(JenkinsException::class);
        $expectJenkinsExpect = $jenkins->renameJob('folder1/job', 'folder2/job2');
    }

    public function testDeleteJob()
    {
        $data = [
            ['status' => 200],
            ['status' => 404],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->deleteJob('job-name');
        $responseError = $jenkins->deleteJob('job-name');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testEnableJob()
    {
        $data = [
            ['status' => 200],
            ['status' => 404],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->enableJob('job-name');
        $responseError = $jenkins->enableJob('job-name');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testDisableJob()
    {
        $data = [
            ['status' => 200],
            ['status' => 404],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->disableJob('job-name');
        $responseError = $jenkins->disableJob('job-name');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testCreateJob()
    {
        $data = [
            // the mockery data is used Jenkins::jobExists
            ['name' => 'exist-job-name'],
            ['status' => 200],
            // the mockery data is used Jenkins::jobExists
            ['name' => 'exist-job-name'],
            ['status' => 404],
            // the mockery data is used Jenkins::jobExists
            ['name' => 'exist-job-name'],
            ['status' => 200],
        ];
        $jenkins = $this->getJenkins($data);

        $configXml = file_get_contents(__DIR__.'/../data/Job/config.xml');

        $responseSuccess = $jenkins->createJob('job-name', $configXml);
        $responseError = $jenkins->createJob('job-name', $configXml);

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);

        $this->expectException(JenkinsException::class);
        $jenkins->createJob('exist-job-name', $configXml);
    }

    public function testGetJobConfig()
    {
        $configXml = file_get_contents(__DIR__.'/../data/Job/config.xml');
        $data = [
            ['status' => 200, 'body' => $configXml],
            ['status' => 404, 'body' => 'not xml format, like 404 not found.']
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getJobConfig('exist-job-name');
        $responseError = $jenkins->getJobConfig('not-exist-job-name');

        $this->assertEquals($configXml, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }

    public function testReconfigJob()
    {
        $configXml = file_get_contents(__DIR__.'/../data/Job/config.xml');
        $data = [
            ['status' => 200],
            ['status' => 404],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->reconfigJob('job-name', $configXml);
        $responseError = $jenkins->reconfigJob('job-name', $configXml);

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testPollJob()
    {
        $data = [
            ['status' => 200],
            ['status' => 404],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->pollJob('job-name');
        $responseError = $jenkins->pollJob('job-name');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }
}
