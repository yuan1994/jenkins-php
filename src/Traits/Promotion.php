<?php

namespace Yuan1994\Jenkins\Traits;

use Yuan1994\Jenkins\Consts\URL;
use Yuan1994\Jenkins\Exceptions\JenkinsException;

/**
 * Trait Promotion
 *
 * @package Yuan1994\Jenkins\Traits
 */
trait Promotion
{
    /**
     * Get promotion information dictionary of a job
     *
     * @param string $jobName job_name
     * @param int    $depth JSON depth
     * @return array|false promotion info
     */
    public function getPromotionInfo($jobName, $depth = 0)
    {
        $paths = $this->getJobFolder($jobName);
        $paths['depth'] = $depth;

        $response = $this->jenkinsRequest([
            'GET', $this->buildUrl(URL::PROMOTION_INFO, $paths),
        ]);

        return $this->getResponseFalseOrContents($response);
    }

    /**
     * Get list of promotions running.
     * Each promotion is a dictionary with 'name' and 'url' keys.
     *
     * @param string $jobName Job name
     * @return false|array list of promotions or false
     */
    public function getPromotions($jobName)
    {
        $promotion = $this->getPromotionInfo($jobName);
        if (empty($promotion)) {
            return false;
        }

        return $promotion['processes'];
    }

    /**
     * Delete Jenkins promotion permanently.
     *
     * @param string $name Name of Jenkins promotion
     * @param string $jobName Job name
     * @return bool|int
     */
    public function deletePromotion($name, $jobName)
    {
        $paths = $this->getJobFolder($jobName);
        $paths['name'] = $name;

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::DELETE_PROMOTION, $paths),
        ]);

        return $this->getResponseTrueOrStatusCode($response);
    }

    /**
     * Create a new Jenkins promotion
     *
     * @param string $name Name of Jenkins promotion
     * @param string $jobName Job name
     * @param string $configXml config file text
     * @return bool|int
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function createPromotion($name, $jobName, $configXml)
    {
        $paths = $this->getJobFolder($jobName);
        $paths['name'] = $name;

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::CREATE_PROMOTION, $paths), [
                'body' => $configXml,
                'headers' => ['Content-Type' => URL::DEFAULT_CONTENT_TYPE],
            ]
        ]);

        if ($response->getStatusCode() == 400) {
            throw new JenkinsException("promotion[{$name}] already exists at job[{$jobName}]");
        }

        return $this->getResponseTrueOrStatusCode($response);
    }

    /**
     * Change configuration of existing Jenkins promotion.
     * To create a new promotion, see Jenkins::createPromotion.
     *
     * @param string $name Name of Jenkins promotion
     * @param string $jobName Job name
     * @param string $configXml  New XML configuration
     * @return string
     */
    public function reconfigPromotion($name, $jobName, $configXml)
    {
        $paths = $this->getJobFolder($jobName);
        $paths['name'] = $name;

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::CONFIG_PROMOTION, $paths), [
                'body' => $configXml,
                'headers' => ['Content-Type' => URL::DEFAULT_CONTENT_TYPE],
            ]
        ]);

        return $this->getResponseTrueOrStatusCode($response);
    }

    /**
     * Get configuration of existing Jenkins promotion.
     *
     * @param string $name Name of Jenkins promotion
     * @param string $jobName Job name
     * @return string promotion configuration (XML format)
     */
    public function getPromotionConfig($name, $jobName)
    {
        $paths = $this->getJobFolder($jobName);
        $paths['name'] = $name;

        $response = $this->jenkinsRequest([
            'GET', $this->buildUrl(URL::CONFIG_PROMOTION, $paths),
        ]);

        if ($response->getStatusCode() != 200) {
            return false;
        }

        return $response->getBody()->getContents();
    }
}
