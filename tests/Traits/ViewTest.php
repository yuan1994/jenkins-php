<?php

namespace Yuan1994\Jenkins\Tests\Traits;

use Yuan1994\Jenkins\Exceptions\JenkinsException;
use Yuan1994\Jenkins\Tests\TestCase;

class ViewTest extends TestCase
{
    public function testGetViewJobs()
    {
        $jobs = $this->getArrayFromFile('View/getViewJobs.json');
        $data = [
            ['status' => 200, 'body' => ['jobs' => $jobs]],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getViewJobs('view1');
        $this->assertEquals($jobs, $responseSuccess);

        $this->expectException(JenkinsException::class);
        $jenkins->getViewJobs('not-exist-view');
    }

    public function testGetViews()
    {
        $views = $this->getArrayFromFile('View/getViews.json');
        $data = ['status' => 200, 'body' => ['views' => $views]];
        $jenkins = $this->getJenkins($data);

        $response = $jenkins->getViews();
        $this->assertEquals($views, $response);
    }

    public function testDeleteView()
    {
        $data = [
            ['status' => 200, 'body' => 'success'],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->deleteView('view1');
        $responseError = $jenkins->deleteView('not-exist-view');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testCreateView()
    {
        $configXml = file_get_contents(__DIR__.'/../data/View/getViewConfig.xml');
        $data = [
            ['status' => 200, 'body' => 'success'],
            ['status' => 400, 'body' => 'view already exist'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->createView('view-another', $configXml);
        $this->assertEquals(true, $responseSuccess);

        $this->expectException(JenkinsException::class);
        $jenkins->createView('view-exist', $configXml);
    }

    public function testReconfigView()
    {
        $configXml = file_get_contents(__DIR__.'/../data/View/getViewConfig.xml');
        $data = [
            ['status' => 200, 'body' => 'success'],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->reconfigView('view1', $configXml);
        $responseError = $jenkins->reconfigView('view-not-exist', $configXml);

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testGetViewConfig()
    {
        $configXml = file_get_contents(__DIR__.'/../data/View/getViewConfig.xml');
        $data = [
            ['status' => 200, 'body' => $configXml],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getViewConfig('view1');
        $responseError = $jenkins->getViewConfig('view-not-exist');

        $this->assertEquals($configXml, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }
}
