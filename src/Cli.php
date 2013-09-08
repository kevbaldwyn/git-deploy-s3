<?php namespace KevBaldwyn\GitDeploy;

class Cli {

	public function diff($oldRevision, $newRevision) {
		$cmd = "git diff --name-status " . $oldRevision . " " . $newRevision;
		$cmdOutput = shell_exec($cmd);
	}

}