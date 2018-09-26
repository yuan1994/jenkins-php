<?php

namespace Yuan1994\Jenkins\Traits;

use Yuan1994\Jenkins\Consts\URL;
use Yuan1994\Jenkins\Exceptions\JenkinsException;

/**
 * Trait Node
 *
 * @package Yuan1994\Jenkins\Traits
 */
trait Node
{
    /**
     * Get a list of nodes connected to the Master
     *
     * @param int $depth JSON depth
     * @return array Array of nodes, [ { name: str, offline: bool} ]
     */
    public function getNodes($depth = 0)
    {
        $response = $this->jenkinsOpen([
            'GET', $this->buildUrl(URL::NODE_LIST, compact('depth')),
        ]);

        return array_map(function($node) {
            return [
                'name' => $node['displayName'],
                'offline' => $node['offline'],
            ];
        }, $response['computer']);
    }

    /**
     * Get node information dictionary
     *
     * @param string $name Node name
     * @param int    $depth JSON depth
     * @return array|bool
     */
    public function getNodeInfo($name, $depth = 0)
    {
        if ($name == 'master') {
            $name = '(master)';
        }
        $response = $this->jenkinsRequest([
            'GET', $this->buildUrl(URL::NODE_INFO, compact('name', 'depth')),
        ]);

        return $this->getResponseFalseOrContents($response, 200);
    }

    /**
     * Check whether a node exists
     *
     * @param string $name Name of Jenkins node
     * @return bool
     */
    public function nodeExists($name)
    {
        return !!$this->getNodeInfo($name);
    }

    /**
     * Delete Jenkins node permanently.
     *
     * @param string $name Name of Jenkins node
     * @return bool|int
     */
    public function deleteNode($name)
    {
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::DELETE_NODE, compact('name')),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Disable a node
     *
     * @param string $name Name of Jenkins node
     * @param string $msg Offline message
     * @return bool|int
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function disableNode($name, $msg = '')
    {
        $node = $this->getNodeInfo($name);
        if (!$node) {
            throw new JenkinsException("node[{$name}] does not exist");
        }
        if ($node['offline']) {
            return true;
        }
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::TOGGLE_OFFLINE, compact('name', 'msg')),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Enable a node
     *
     * @param string $name Name of Jenkins node
     * @return bool|int
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function enableNode($name)
    {
        $node = $this->getNodeInfo($name);
        if (!$node) {
            throw new JenkinsException("node[{$name}] does not exist");
        }
        if (!$node['offline']) {
            return true;
        }
        $msg = '';
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::TOGGLE_OFFLINE, compact('name', 'msg')),
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Create a node
     *
     * @param string $name Name of Jenkins node
     * @param int    $numExecutors number of executors for node
     * @param string $nodeDescription Description of node
     * @param string $remoteFS Remote filesystem location to use
     * @param null   $labels Labels to associate with node
     * @param bool   $exclusive Use this node for tied jobs only
     * @param string $launcher The launch method for the slave, optional value
     *                         is: URL::LAUNCHER_COMMAND, URL::LAUNCHER_SSH,
     *                         URL::LAUNCHER_JNLP, URL::LAUNCHER_WINDOWS_SERVICE
     * @param array  $launcherParams Additional parameters for the launcher
     * @return bool
     * @throws \Yuan1994\Jenkins\Exceptions\JenkinsException
     */
    public function createNode(
        $name, $numExecutors = 2, $nodeDescription = null,
        $remoteFS = '/var/lib/jenkins', $labels = null, $exclusive = false,
        $launcher = URL::LAUNCHER_COMMAND, $launcherParams = []
    ) {
        if ($this->nodeExists($name)) {
            throw new JenkinsException("node[{$name}] already exists");
        }

        $mode = 'NORMAL';
        if ($exclusive) {
            $mode = 'EXCLUSIVE';
        }

        $launcherParams['stapler-class'] = $launcher;

        $innerParams = [
            'nodeDescription' => $nodeDescription,
            'numExecutors' => $numExecutors,
            'remoteFS' => $remoteFS,
            'labelString' => $labels,
            'mode' => $mode,
            'retentionStrategy' => [
                'stapler-class' => 'hudson.slaves.RetentionStrategy$Always'
            ],
            'nodeProperties' => ['stapler-class-bag' => 'true'],
            'launcher' => $launcherParams
        ];

        $params = [
            'name' => $name,
            'type' => URL::NODE_TYPE,
            'json' => \GuzzleHttp\json_encode($innerParams),
        ];

        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::CREATE_NODE), ['form_params' => $params],
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

    /**
     * Get the configuration for a node.
     *
     * @param string $name Name of Jenkins node
     * @return string|bool
     */
    public function getNodeConfig($name)
    {
        $response = $this->jenkinsRequest([
            'GET', $this->buildUrl(URL::CONFIG_NODE, compact('name')),
        ]);

        if ($response->getStatusCode() != 200) {
            return false;
        }

        return $response->getBody()->getContents();
    }

    /**
     * Change the configuration for an existing node.
     *
     * @param string $name Name of Jenkins node
     * @param string $configXml New XML configuration
     * @return bool|int
     */
    public function reconfigNode($name, $configXml)
    {
        $response = $this->jenkinsRequest([
            'POST', $this->buildUrl(URL::CONFIG_NODE, compact('name')), [
                'body' => $configXml,
                'headers' => ['Content-Type' => URL::DEFAULT_CONTENT_TYPE],
            ]
        ]);

        return $this->getResponseTrueOrStatusCode($response, 200);
    }

}
