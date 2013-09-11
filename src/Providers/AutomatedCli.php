<?php namespace KevBaldwyn\GitDeploy\Providers;

use KevBaldwyn\GitDeploy\Cli as GitDeployCli;
use KevBaldwyn\GitDeploy\Interfaces\CliInterface;

class AutomatedCli extends GitDeployCli implements CliInterface {

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

