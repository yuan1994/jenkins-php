<?php

namespace Yuan1994\Jenkins\Tests\Traits;

use Yuan1994\Jenkins\Exceptions\JenkinsException;
use Yuan1994\Jenkins\Tests\TestCase;

class PromotionTest extends TestCase
{
    public function testGetPromotionInfo()
    {
        $promotion = ['var1' => 'val1'];
        $data = [
            ['status' => 200, 'body' => $promotion],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getPromotionInfo('job-name');
        $responseError = $jenkins->getPromotionInfo('job-not-exist');

        $this->assertEquals($promotion, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }

    public function testGetPromotions()
    {
        $processes = ['var1' => 'val1'];
        $data = [
            ['status' => 200, 'body' => ['processes' => $processes]],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getPromotions('job-name');
        $responseError = $jenkins->getPromotions('job-not-exist');

        $this->assertEquals($processes, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }

    public function testDeletePromotion()
    {
        $data = [
            ['status' => 200, 'body' => 'success'],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->deletePromotion('promtoion', 'job-name');
        $responseError = $jenkins->deletePromotion('promotion-not-exist', 'job-name');

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testCreatePromotion()
    {
        $configXml = file_get_contents(__DIR__.'/../data/Promotion/getPromotionConfig.xml');
        $data = [
            ['status' => 200, 'body' => 'success'],
            ['status' => 400, 'body' => 'promotion already exist'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->createPromotion('promotion-another', 'job-name', $configXml);
        $this->assertEquals(true, $responseSuccess);

        $this->expectException(JenkinsException::class);
        $jenkins->createPromotion('promotion-exist', 'job-name', $configXml);
    }

    public function testReconfigPromotion()
    {
        $configXml = file_get_contents(__DIR__.'/../data/Promotion/getPromotionConfig.xml');
        $data = [
            ['status' => 200, 'body' => 'success'],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->reconfigPromotion('promotion', 'job-name', $configXml);
        $responseError = $jenkins->reconfigPromotion('promotion-not-exist', 'job-name', $configXml);

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }

    public function testGetPromotionConfig()
    {
        $configXml = file_get_contents(__DIR__.'/../data/Promotion/getPromotionConfig.xml');
        $data = [
            ['status' => 200, 'body' => $configXml],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getPromotionConfig('promotion', 'job-name');
        $responseError = $jenkins->getPromotionConfig('promotion-not-exist', 'job-name');

        $this->assertEquals($configXml, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }
}
