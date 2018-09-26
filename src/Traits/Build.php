<?php

namespace Yuan1994\Jenkins\Traits;

use Yuan1994\Jenkins\Consts\URL;
use Yuan1994\Jenkins\Exceptions\JenkinsException;

/**
 * Trait Build
 *
 * @package Yuan1994\Jenkins\Traits
 */
trait Build
{
    /**
     * Get build information.
     *
     * @param string $name Job name
     * @param int    $number Build number
     * @param int    $depth JSON depth
     * @return array|bool build information
     */
    public function getBuildInfo($name, $number, $depth = 0)
    {
        $paths = $this->getJobFolder($name);
        $paths['number'] = $number;
        $paths['depth'] = $depth;
        $response = $this->jenkinsRequest([
            'GET', $this->buildUrl(URL::BUILD_INFO, $paths)
        ]);

        return $this->getResponseFalseOrContents($response);
    }

    /**
     * Get build environment variables.
     *
     * @param string $name Job name
     * @param int    $number Build number
     * @param int    $depth JSON depth
     * @return array build env vars array
     */
    public function getBuildEnvVars($name, $number, $depth = 0)
    {
        $info = $this->getBuildInfo($name, $number, $depth);

        if ($info === false) {
            return [];
        }

        $vars = [];
        foreach ($info['actions'] as $action) {
            if (!empty($action['_class']) && $action['_class'] == URL::PARAM_CLASS) {
                foreach ($action['parameters'] as $param) {
                    $vars[$param['name']] = $param['value'];
                }
                break;
            }
        }

        return $vars;
    }

    /**
     * Get build Git information.
     *
     * @param string $name Job name
     * @param int    $number Build number
     * @param int    $depth JSON depth
     * @return array|bool git information, false if not git
     */
    public function getBuildGitInfo($name, $number, $depth = 0)
    {
        $info = $this->getBuildInfo($name, $number, $depth);

        if ($info === false) {
            return [];
        }

        foreach ($info['actions'] as $action) {
            if (!empty($action['_class']) && $action['_class'] == URL::VCS_GIT) {
                $branches = [];
                foreach ($action['lastBuiltRevision']['branch'] as $branch) {
                    $branches[] = [
                        'sha1' => $branch['SHA1'],
                        'name' => $branch['name'] == 'detached' ? $branch['SHA1'] : $branch['name'],
                    ];
                }
                return [
                    'last' => [
                        'remote_url' => $action['remoteUrls'][0],
                        'branch' => $branches[0],
                    ],
                    'remote_urls' => $action['remoteUrls'],
                    'branches' => $branches,
                ];
            }
        }

        return false;
    }

    /**
     * Get test results report.
     *
     * @param string $name Job name
     * @param int    $number Build number
     * @param int    $depth JSON depth
     * @return array|false test report results, array or false if there is no Test Report
     */
    public function getBuildTestReport($name, $number, $depth = 0)
    {
        $paths = $this->getJobFolder($name);
        $paths['number'] = $number;
        $paths['depth'] = $depth;
        $response = $this->jenkinsRequest([
            'GET', $this->buildUrl(URL::BUILD_TEST_REPORT, $paths)
        ]);

        return $this->getResponseFalseOrContents($response, 200);
    }

    /**
     * Get URL to trigger build job.
     * Authenticated setups may require configuring a token on the server side.
     *
     * @param string $name Name of Jenkins job
     * @param array  $parameters parameters for job
     * @param string $token token for building job
     * @return string URL for building job
     */
    protected function buildJobUrl($name, $parameters = [], $token = null)
    {
        $paths = $this->getJobFolder($name);
        if ($parameters) {
            if ($token) {
                $parameters['token'] = $token;
            }

            return $this->buildUrl(URL::BUILD_WITH_PARAMS_JOB, $paths) . '?'
                . http_build_query($parameters);
        } else {
            if ($token) {
                return $this->buildUrl(URL::BUILD_JOB, $paths) . '?'
                    . http_build_query(['token' => $token]);
            }

            return $this->buildUrl(URL::BUILD_JOB, $paths);
        }
    }

    /**
     * Trigger build job.
     * This method returns a queue item number that you can pass to
     * `Jenkins::getQueueItem`. Note that this queue number is only
     * valid for about five minutes after the job completes, so you should
     * get/poll the queue information as soon as possible to determine the
     * job's URL.
     *
     * @param string $name name of job
     * @param array  $parameters parameters for job
     * @param string $token Jenkins API token
     * @return int queue item
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function buildJob($name, $parameters = [], $token = null)
    {
        $response = $this->jenkinsRequest([
            'POST', $this->buildJobUrl($name, $parameters, $token)
        ]);

        if (empty($response)) {
            throw new JenkinsException("[{$name}] Build failed. Check whether the build need parameters or token");
        }

        $location = $response->getHeaderLine('Location');
        $location = rtrim($location, '/');
        $parts = explode('/', $location);
        foreach ($parts as $part) {
            if (is_numeric($part)) {
                return intval($part);
            }
        }

        return 0;
    }

    /**
     * Stop a running Jenkins build.
     *
     * @param string $name Name of Jenkins job
     * @param int    $number Jenkins build number for the job
     * @return bool
     */
    public function stopBuild($name, $number)
    {
        $paths = $this->getJobFolder($name);
        $paths['number'] = $number;

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::STOP_BUILD, $paths),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Delete a Jenkins build.
     *
     * @param string $name Name of Jenkins job
     * @param int    $number Jenkins build number for the job
     * @return bool
     */
    public function deleteBuild($name, $number)
    {
        $paths = $this->getJobFolder($name);
        $paths['number'] = $number;

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::DELETE_BUILD, $paths),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Return list of running builds.
     * Each build is a dict with keys 'name', 'number', 'url', 'node', 'queueId',
     * and 'executor'.
     *
     * @return array
     */
    public function getRunningBuilds()
    {
        $builds = [];
        $nodes = $this->getNodes();
        foreach ($nodes as $node) {
            if ($node['offline']) {
                continue;
            }
            $info = $this->getNodeInfo($node['name'], 2);
            if (!$info) {
                continue;
            }

            foreach ($info['executors'] as $executor) {
                $executable = $executor['currentExecutable'];
                if ($executable && !strpos($executable['_class'], 'PlaceholderTask')) {
                    $url = $executable['url'];
                    if (preg_match('/\/job\/([^\/]+).*/', parse_url($url, PHP_URL_PATH), $matches)) {
                        $jobName = $matches[1];
                        $builds[] = [
                            'name' => $jobName,
                            'number' => $executable['number'],
                            'queue' => $executable['queueId'],
                            'url' => $url,
                            'node' => $node['name'],
                            'executor' => $executor['number']
                        ];
                    }
                }
            }
        }

        return $builds;
    }

    /**
     * Get build console text.
     *
     * @param string $name Job name
     * @param int    $number Build number
     * @return array Build console output
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function getBuildConsoleOutput($name, $number)
    {
        $paths = $this->getJobFolder($name);
        $paths['number'] = $number;

        $response = $this->jenkinsOpen([
            'GET', $this->buildUrl(URL::BUILD_CONSOLE_OUTPUT, $paths),
        ]);

        if ($response) {
            return $response;
        } else {
            throw new JenkinsException("job[{$name}] number[{$number}] does not exist");
        }
    }
}
