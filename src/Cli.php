<?php namespace KevBaldwyn\GitDeploy;

class Cli {

	protected $currentBranch;

	protected $lastCommand = '';


	public function diff($oldRevision, $newRevision) 
	{	
		return $this->run("git diff --name-status " . $newRevision . " " . $oldRevision);
	}


	/** 
	 * $ git ls-remote
	 *
	 * Outputs:
	 * From git@github.com:kevbaldwyn/git-deploy-s3.git
	 * b9d4be453fadf401ac5c26862a078903c8b27629	HEAD
	 * b9d4be453fadf401ac5c26862a078903c8b27629	refs/heads/master
	 * 
	 * $ git rev-parse HEAD
	 * Outputs an sha hash: e813f58c785ad9530818d52d2982b3ca3d52ae82
	 * which can be used to pass the arguments to the deployer
	 */
	public function currentState() 
	{
		$fetch = $this->run("git fetch");
		$lsRemote = $this->parseOutput($this->run("git ls-remote"));
		if(!is_null($lsRemote)) {
			$branch = $this->currentBranch();
			if(!is_null($branch)) {

				foreach($lsRemote as $branchDetail) {
					if(preg_match("/([A-Za-z0-9]*)\s*(.*)/", $branchDetail, $matches)) {
						$commitId = $matches[1];
						$branchRef = $matches[2];
						if($branchRef == 'refs/heads/' . $branch) {
							$commits['remote'] = $commitId;
						}
					}
				}

				$localHead = $this->run("git rev-parse HEAD");
				$commits['local'] = $localHead;
				if(!$localHead) {
					return false;
				}

			}

			if(count($commits) == 2) {
				return $commits;
			}else{
				if(!array_key_exists('local', $commits)) {
					throw new \Exception('No local HEAD found');
				}
				if(!array_key_exists('remote', $commits)) {
					throw new \Exception('No remote branch ' . $branch . ' found');
				}
			}
		}else{
			return false;
		}

	}


	public function currentBranch() 
	{
		if($this->currentBranch) {
			return $this->currentBranch;
		}

		$status = $this->parseOutput($this->run("git status"));
		if(!is_null($status)) {
			if(preg_match("/^#\sOn\sbranch\s(.*)/", $status[0], $matches)) {
				$this->currentBranch = trim($matches[1]);
				return $this->currentBranch;
			}else{
				throw new \Exception('No branch information found.');
			}
		}else{
			return false;
		}
	}


	public function getLastCommand() 
	{
		return $this->lastCommand;
	}


	private function run($cmd)
	{
		$this->lastCommand = $cmd;
		return shell_exec($cmd);
	}


	private function parseOutput($output)
	{
		if(!is_null($output)) {
			return explode("\n", $output);
		}
		return null;
	}


}