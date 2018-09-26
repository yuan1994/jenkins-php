<?php

namespace Yuan1994\Jenkins\Traits;

use Yuan1994\Jenkins\Consts\URL;
use Yuan1994\Jenkins\Exceptions\JenkinsException;

/**
 * Trait Base
 *
 * @package Yuan1994\Jenkins\Traits
 */
trait Base
{
    /**
     * Get information on this Master or item on Master.
     * This information includes job list and view information and can be
     * used to retreive information on items such as job folders.
     *
     * @param string $item item to get information about on this Master
     * @return array|mixed information about Master or item
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function getInfo($item = '', $query = '', $folderUrl = '')
    {
        $response = $this->jenkinsOpen([
            'GET', $this->buildUrl(URL::INFO, ['folder_url' => $folderUrl]) . $query
        ]);

        if ($item) {
            if (isset($response[$item])) {
                return $response[$item];
            }

            throw new JenkinsException("Item[{$item}] does not exists.");
        }

        return $response;
    }

    /**
     * Get information about the user account that authenticated to
     * Jenkins. This is a simple way to verify that your credentials are
     * correct.
     *
     * @param int $depth
     * @return array Information about the current user
     */
    public function getWhoAmi($depth = 0)
    {
        $response = $this->jenkinsOpen([
            'GET', $this->buildUrl(URL::WHOAMI_URL, compact('depth')),
        ]);

        return $response;
    }

    /**
     * Get the version of this Master.
     *
     * @return string This master's version number
     */
    public function getVersion()
    {
        $response = $this->JenkinsRequest([
            'GET', '',
        ]);

        return $response->getHeaderLine('X-Jenkins');
    }

    /**
     * Return plugins info using helper class for version comparison
     * This method retrieves information about all the installed plugins and
     * uses a Plugin helper class to simplify version comparison. Also uses
     * a multi key dict to allow retrieval via either short or long names.
     *
     * @param int $depth JSON depth
     * @return array info on all plugins
     */
    public function getPlugins($depth = 2)
    {
        $response = $this->jenkinsOpen([
            'GET', $this->buildUrl(URL::PLUGIN_INFO, compact('depth')),
        ]);

        $plugins = [];
        foreach ($response['plugins'] as $item) {
            $plugins[$item['shortName']] = $item;
        }

        return $plugins;
    }

    /**
     * Get an installed plugin information on this Master.
     *  This method retrieves information about a specific plugin and returns
     * the raw plugin data in a JSON format.
     * The passed in plugin name (short or long) must be an exact match.
     *
     * @param string $name Name (short or long) of plugin
     * @param int    $depth JSON depth
     * @return false|array a specific plugin
     */
    public function getPluginInfo($name, $depth = 2)
    {
        $plugins = $this->getPlugins($depth);

        foreach ($plugins as $plugin) {
            if ($plugin['longName'] == $name || $plugin['shortName'] == $name) {
                return $plugin;
            }
        }

        return false;
    }

    /**
     * Execute a groovy script on the jenkins master or on a node if
     * specified..
     *
     * @param string $script The groovy script
     * @param string $node Node to run the script on, defaults to null (master).
     * @return bool|string The result of the script run. False if run failed.
     */
    public function runScript($script, $node = null)
    {
        $magicStr = ')]}.';
        $printMagicStr = 'print("'. $magicStr .'")';
        $data = [
            'script' => $script . "\n" . $printMagicStr,
        ];

        if ($node) {
            $uri = $this->buildUrl(URL::NODE_SCRIPT_TEXT, compact('node'));
        } else {
            $uri = $this->buildUrl(URL::SCRIPT_TEXT);
        }

        $response = $this->jenkinsOpen([
            'POST', $uri, ['form_params' => $data],
        ]);

        if ($magicStr != substr($response, -strlen($magicStr))) {
            return false;
        }

        return substr($response, 0, -strlen($magicStr));
    }

    /**
     * Install a plugin and its dependencies from the Jenkins public repository.
     *
     * @param string $name The plugin short name
     * @param bool   $includeDependencies Install the plugin's dependencies
     * @return bool Whether a Jenkins restart is required
     */
    public function installPlugin($name, $includeDependencies = true)
    {
        $install = 'Jenkins.instance.updateCenter.getPlugin("' . $name . '").deploy();';
        if ($includeDependencies) {
            $install = 'Jenkins.instance.updateCenter.getPlugin("' . $name . '")'
                       . '.getNeededDependencies().each{it.deploy()};' . $install;
        }

        $res = $this->runScript($install);

        sleep(2);

        $isRestartRequired = 'Jenkins.instance.updateCenter.isRestartRequiredForCompletion();';

        $response = $this->runScript($isRestartRequired);

        return $response == '';
    }

    /**
     * Wipe out workspace for given Jenkins job.
     *
     * @param string $name Name of Jenkins job
     * @return bool|int
     */
    public function wipeoutJobWorkspace($name)
    {
        $paths = $this->getJobFolder($name);

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::WIPEOUT_JOB_WORKSPACE, $paths),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Prepare Jenkins for shutdown.
     * No new builds will be started allowing running builds to complete
     * prior to shutdown of the server.
     *
     * @return bool
     */
    public function quietDown()
    {
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::QUIET_DOWN),
        ]);

        $info = $this->getInfo();
        if (!$info['quietingDown']) {
            return false;
        }

        return true;
    }

    /**
     * Cancels the Quiet Down (Prepare for shutdown) message
     *
     * @return bool
     */
    public function cancelQuietDown()
    {
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::CANCEL_QUIET_DOWN),
        ]);

        $info = $this->getInfo();
        if ($info['quietingDown']) {
            return false;
        }

        return true;
    }

    /**
     * Safe Restart Jenkins.
     * Puts Jenkins into the quiet mode, wait for existing builds
     * to be completed, and then restart Jenkins.
     *
     * @return bool
     */
    public function safeRestart()
    {
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::SAFE_RESTART),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 503);
    }

    /**
     * Restart Jenkins without waiting for any existing build to complete.
     *
     * @return bool
     */
    public function restart()
    {
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::RESTART),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 503);
    }

    /**
     * Puts Jenkins into the quiet mode, wait for existing builds to
     * be completed, and then shut down Jenkins.
     *
     * @return bool
     */
    public function safeExit()
    {
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::SAFE_EXIT),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Shutdown Jenkins without waiting for any existing build to complete.
     *
     * @return bool
     */
    public function jenkinsExit()
    {
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::JENKINS_EXIT),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }
}
