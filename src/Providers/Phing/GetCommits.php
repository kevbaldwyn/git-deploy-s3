<?php

require_once 'phing/Task.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use \KevBaldwyn\GitDeploy\Cli;


class GetCommits extends Task {

	private $outputPropertyLocal;
	private $outputPropertyRemote;

	
	public function setOutputPropertyLocal($v) {
		$this->outputPropertyLocal = $v;
	}


	public function setOutputPropertyRemote($v) {
		$this->outputPropertyRemote = $v;
	}


	public function main() 
	{
		try {
			$cli = new Cli();
			$commits = $cli->currentState();
			$this->project->setProperty($this->outputPropertyLocal, $commits['local']);
			$this->project->setProperty($this->outputPropertyRemote, $commits['remote']);
		}catch(\Exception $e) {
			// convert the exception
			throw new BuildException($e->getMessage(), $this->getLocation());
		}
	}

}