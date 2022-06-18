<?php

namespace Flow;

use Flow\FlowException;

class Runner
{
	/**
	 * Array of all root nodes
	 *
	 * @var NodeInterface[]
	 */
	private array $rootNodes = [];

    /**
     * Load a JSON definition
     *
     * @param string $filename
     * @throws FlowException if the file could not be loaded or processed
     */
    public function load(string $filename): void
    {

        if (!$f = file_get_contents($filename)) {
            throw new FlowException('Cannot open ' . basename($filename));
        }

        if (!$config = json_decode($f, true)) {
            throw new FlowException('Invalid JSON file');
        }

		$nodes = [];

		// first pass, create the nodes
		foreach ($config['nodes'] as $node) {
			$class = '\\Flow\\Node\\' . $node['type'] . 'Node';
			$obj = new $class($node['id']);
			$nodes[$node['id']] = $obj;

			if (!$obj->getInputs()) {
				$this->addNode($obj);
			}
		}
		// second pass, the connections
		foreach ($config['nodes'] as $node) {
			$connections = [];

			foreach($node['connections'] as $connection) {
				if (!isset($connections[$connection['node']])) {
					$connections[$connection['node']] = [];
				}
				list($from, $to) = explode(':', $connection['map']);
				$connections[$connection['node']][$from] = $to;
			}
			foreach ($connections as $subnodekey => $map) {
				if (isset($nodes[$subnodekey])) {
					$nodes[$node['id']]->addNode($nodes[$subnodekey], $map);
				}
			}
		}
    }

	/**
	 * Set the entry node
	 *
	 * @param NodeInterface $node
	 * @return NodeInterface
	 */
	public function addNode(NodeInterface $node): NodeInterface
	{
		if ($node->getInputs()) {
			throw new FlowException('Cannot add root node that requires input');
		}
		return $this->rootNodes[$node->getIdentifier()] = $node;
	}

	/**
	 * Run the flow, passing in optional array of inputs
	 *
	 * @return void
	 */
	public function run(): void
	{
		foreach ($this->rootNodes as $node) {
			$node->run();
		}
	}
}
