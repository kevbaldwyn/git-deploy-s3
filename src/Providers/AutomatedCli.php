<?php namespace KevBaldwyn\GitDeploy\Providers;

use KevBaldwyn\GitDeploy\Cli as GitDeployCli;
use KevBaldwyn\GitDeploy\Interfaces\CliInterface as Cli;

class AutomatedCli extends GitDeployCli implements Cli {

	private $oldRev;
	private $newRev;


	public function __construct($oldRev, $newRev) {
		$this->oldRev = $oldRev;
		$this->newRev = $newRev;
	}

	public function getRevisions() 
	{
		return array($this->oldRev, $this->newRev);
	}

}

