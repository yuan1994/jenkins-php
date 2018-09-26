<?php

namespace Yuan1994\Jenkins\Traits;

use Yuan1994\Jenkins\Consts\URL;
use Yuan1994\Jenkins\Exceptions\JenkinsException;

/**
 * Trait Job
 *
 * @package Yuan1994\Jenkins\Traits
 */
trait Job
{
    /**
     * Get job information dictionary.
     *
     * @param string $name Job name
     * @param int    $depth JSON depth
     * @param bool $fetchAllBuilds If true, all builds will be retrieved
     *                             from Jenkins. Otherwise, Jenkins will
     *                             only return the most recent 100
     *                             builds. This comes at the expense of
     *                             an additional API call which may
     *                             return significant amounts of data.
     * @return array job information
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function getJobInfo($name, $depth = 0, $fetchAllBuilds = false)
    {
        $paths = $this->getJobFolder($name);
        $paths['depth'] = $depth;
        $response = $this->jenkinsOpen([
            'GET', $this->buildUrl(URL::JOB_INFO, $paths)
        ]);
        if ($fetchAllBuilds) {
            return $this->addMissingBuilds($response);
        } else {
            return $response;
        }
    }

    /**
     * Query Jenkins to get all builds of a job.
     * The Jenkins API only fetches the first 100 builds, with no
     * indicator that there are more to be fetched. This fetches more
     * builds where necessary to get all builds of a given job.
     *
     * @param array $data
     * @return array
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    protected function addMissingBuilds($data)
    {
        if (empty($data['builds'])) {
            return $data;
        }

        $oldestLoadedBuildNumber = end($data['builds'])['number'];
        if (empty($data['firstBuild'])) {
            $firstBuildNumber = $oldestLoadedBuildNumber;
        } else {
            $firstBuildNumber = $data['firstBuild']['number'];
        }
        $allBuildsLoaded = ($oldestLoadedBuildNumber == $firstBuildNumber);
        if ($allBuildsLoaded) {
            return $data;
        }

        $paths = $this->getJobFolder($data['name']);
        $response = $this->jenkinsOpen([
            'GET', $this->buildUrl(URL::ALL_BUILDS, $paths)
        ]);

        if ($response) {
            $data['builds'] = $response['allBuilds'];
        } else {
            throw new JenkinsException("Could not fetch all builds from job[{$data['name']}]");
        }

        return $data;
    }

    /**
     * Return the name of a job using the API.
     * That is roughly an identity method which can be used to quickly verify
     * a job exists or is accessible without causing too much stress on the
     * server side.
     *
     * @param string $name Job name
     * @return string|null Null if the job does exist
     */
    public function getJobName($name)
    {
        $paths = $this->getJobFolder($name);
        $response = $this->jenkinsOpen([
            'GET', $this->buildUrl(URL::JOB_NAME, $paths)
        ]);
        if (empty($response) || empty($response['name'])) {
            return null;
        }

        return $response['name'];
    }

    /**
     * Get list of all jobs recursively to the given folder depth.
     *
     * @param string $baseFolder Base folder url.
     * @param int $folderDepth Number of levels to search. By default null, which
     *                         will search all levels. 1 limits to top level.
     * @return array
     */
    public function getAllJobs($baseFolder = '', $folderDepth = null)
    {
        $jobsList = [];

        $this->getJobsByFolder($baseFolder, 1, $folderDepth, $jobsList);

        return $jobsList;
    }

    /**
     * @param string $folder
     * @param int    $level
     * @param null   $depth
     * @param array  $jobsList
     */
    protected function getJobsByFolder(
        $folder = '',
        $level = 1,
        $depth = null,
        &$jobsList = []
    ) {
        if ($folder) {
            $folderUrl = ltrim(str_replace('/', '/job/', '/' . $folder), '/') . '/';
        } else {
            $folderUrl = '';
        }
        $jobs = $this->getInfo('jobs', URL::JOBS_QUERY, $folderUrl);
        foreach ($jobs as $job) {
            if ($job['_class'] == URL::FOLDER_CLASS) {
                if ((!$depth || $level < $depth) && !empty($job['jobs'])) {
                    $this->getJobsByFolder($job['fullName'], $level + 1, $depth, $jobsList);
                }
            } else {
                $jobsList[] = $job;
            }
        }
    }

    /**
     * Get the number of jobs on the Jenkins server
     *
     * @return int Total number of jobs
     */
    public function jobsCount()
    {
        return count($this->getAllJobs());
    }

    /**
     * Copy a Jenkins job.
     *
     * @param string $fromName Name of Jenkins job to copy from
     * @param string $toName Name of Jenkins job to copy to
     * @return bool
     */
    public function copyJob($fromName, $toName)
    {
        $fromPaths = $this->getJobFolder($fromName);
        $toPaths = $this->getJobFolder($toName);
        $params = [
            'from_folder_url' => $fromPaths['folder_url'],
            'from_short_name' => $fromPaths['short_name'],
            'to_folder_url' => $toPaths['folder_url'],
            'to_short_name' => $toPaths['short_name'],
        ];

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::COPY_JOB, $params),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function jobExists($name)
    {
        $paths = $this->getJobFolder($name);

        return $paths['short_name'] == $this->getJobName($name);
    }

    /**
     * Rename an existing Jenkins job
     *
     * @param string $fromName Name of Jenkins job to rename
     * @param string $toName New Jenkins job name
     * @return bool
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function renameJob($fromName, $toName)
    {
        $fromPaths = $this->getJobFolder($fromName);
        $toPaths = $this->getJobFolder($toName);
        if ($fromPaths['folder_url'] != $toPaths['folder_url']) {
            throw new JenkinsException("rename[{$fromName} to {$toName}] failed, "
                . "source and destination folder must be the same");
        }
        $params = [
            'from_folder_url' => $fromPaths['folder_url'],
            'from_short_name' => $fromPaths['short_name'],
            'to_folder_url' => $toPaths['folder_url'],
            'to_short_name' => $toPaths['short_name'],
        ];

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::RENAME_JOB, $params),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Delete Jenkins job permanently.
     *
     * @param string $name Name of Jenkins job
     * @return bool
     */
    public function deleteJob($name)
    {
        $paths = $this->getJobFolder($name);
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::DELETE_JOB, $paths),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Enable Jenkins job.
     *
     * @param string $name Name of Jenkins job
     * @return mixed
     */
    public function enableJob($name)
    {
        $paths = $this->getJobFolder($name);
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::ENABLE_JOB, $paths),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Disable Jenkins job.
     * To re-enable, call `Jenkins::enableJob`.
     *
     * @param string $name
     * @return mixed
     */
    public function disableJob($name)
    {
        $paths = $this->getJobFolder($name);
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::DISABLE_JOB, $paths),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Create a new Jenkins job
     *
     * @param string $name Name of Jenkins job
     * @param string $configXml config file text
     * @return bool
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function createJob($name, $configXml)
    {
        $paths = $this->getJobFolder($name);
        if ($this->jobExists($name)) {
            throw new JenkinsException("job[{$name}] already exists");
        }

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::CREATE_JOB, $paths), [
                'body' => $configXml,
                'headers' => ['Content-Type' => URL::DEFAULT_CONTENT_TYPE],
            ]
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Create a new jenkins folder
     *
     * @param string $name
     * @return bool
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function createFolder($name)
    {
        $paths = $this->getJobFolder($name);
        if ($this->jobExists($name)) {
            throw new JenkinsException("folder[{$name}] already exists");
        }
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::CREATE_JOB, $paths), [
                'form_params' => [
                    'mode' => URL::FOLDER_CLASS,
                    'name' => $paths['short_name'],
                    'from' => '',
                    'Submit' => 'OK'
                ]
            ]
        ]);

        if ($response->getStatusCode() == 404) {
            $parentFolder = substr($paths['folder_url'], 4, -1);
            throw new JenkinsException("parent folder[{$parentFolder}] does "
                ."exist.");
        }

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Get configuration of existing Jenkins job.
     *
     * @param string $name Name of Jenkins job
     * @return string|bool job configuration (XML format) or false if job not exist
     */
    public function getJobConfig($name)
    {
        $paths = $this->getJobFolder($name);
        $response = $this->jenkinsRequest([
            'GET', $this->buildUrl(URL::CONFIG_JOB, $paths)
        ]);

        if ($response->getStatusCode() != 200) {
            return false;
        }

        return $response->getBody()->getContents();
    }

    /**
     * Change configuration of existing Jenkins job.
     *
     * @param string $name Name of Jenkins job
     * @param string $configXml New XML configuration
     * @return mixed
     */
    public function reconfigJob($name, $configXml)
    {
        $paths = $this->getJobFolder($name);
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::CONFIG_JOB, $paths), [
                'body' => $configXml,
                'headers' => ['Content-Type' => URL::DEFAULT_CONTENT_TYPE],
            ]
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Programatically schedule SCM polling for the specified job
     *
     * @param string $name Name of Jenkins job
     * @return mixed
     */
    public function pollJob($name)
    {
        $paths = $this->getJobFolder($name);
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::CONFIG_JOB, $paths),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }
}
