<?php

namespace Yuan1994\Jenkins\Tests\Traits;

use Yuan1994\Jenkins\Tests\TestCase;

class QueueTest extends TestCase
{
    public function testGetQueueItem()
    {
        $queueItem = $this->getArrayFromFile('Queue/getQueueItem');
        $data = [
            ['status' => 200, 'body' => $queueItem],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getQueueItem(12);
        $responseError = $jenkins->getQueueItem(122);

        $this->assertEquals($queueItem, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }

    public function testGetQueueInfo()
    {
        $queueInfo = $this->getArrayFromFile('Queue/getQueueInfo');
        $data = [
            ['status' => 200, 'body' => $queueInfo],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->getQueueInfo();
        $responseError = $jenkins->getQueueInfo();

        $this->assertEquals($queueInfo, $responseSuccess);
        $this->assertEquals(false, $responseError);
    }

    public function testCancelQueue()
    {
        $data = [
            ['status' => 200, 'body' => 'success'],
            ['status' => 404, 'body' => '404 not found'],
        ];
        $jenkins = $this->getJenkins($data);

        $responseSuccess = $jenkins->cancelQueue(12);
        $responseError = $jenkins->cancelQueue(122);

        $this->assertEquals(true, $responseSuccess);
        $this->assertEquals(404, $responseError);
    }
}
