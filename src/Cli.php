<?php namespace KevBaldwyn\GitDeploy;

class Cli {

	private $lastCommand = '';

	public function diff($oldRevision, $newRevision) 
	{
		$cmd = $this->lastCommand = "git diff --name-status " . $oldRevision . " " . $newRevision;
		$cmdOutput = shell_exec($cmd);
	}

	public function getLastCommand() {
		return $this->lastCommand;
	}

}