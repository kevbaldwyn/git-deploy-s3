<?php namespace KevBaldwyn\GitDeploy;

/**
 * the commands needed to carry out the actions
 */
class GitDeploy {

	private $oldRevision;
	private $newRevision;
	private $cli;

	private $diffRaw;

	public function __construct(CliInterface $cli) 
	{
		$this->cli = $cli;
		$this->getRevisions();
		$this->getDiff();
	}


	// git-s3-deploy post <oldrev> <newrev>
	public function post() 
	{
		if(count($this->diff['upload']) > 0) {
			
		}else{
			$this->cli->info('No files to upload.');
		}
	}


	// git-s3-deploy delete <oldrev> <newrev>
	public function delete() 
	{
		
	}


	// git-s3-deploy sync <oldrev> <newrev>
	public function snyc() 
	{
		$this->delete();
		$this->post();
	}



	private function getRevisions() 
	{
		$revs = $this->cli->getRevisions();
		$this->oldRevision = $revs[0];
		$this->newRevision = $revs[1];
	}


	private function getDiff() 
	{
		$cmd = "git diff --name-status " . $this->oldRevision . " " . $this->newRevision;
		$cmdOutput = shell_exec($cmd);

		if ( !$cmdOutput ) {
			throw new \Exception('There was a problem executing the command: [' . $cmd . ']');
		}

		$this->diffRaw = explode("\n", trim($cmdOutput));
		$this->parseDiff();
	}


	private function parseDiff() 
	{
		$deletes = $uploads = array();
		foreach($this->diffRaw as $line) {
			list($flag, $file) = explode("\t", $line);
			foreach($paths as $path => $remote_path) {
				$regex = '^' . preg_quote($path, '@');
				$is_dir = is_dir($path);
				if(!$is_dir) {
					$regex .= '$'; 
				}
				
				if(!preg_match('@' . $regex . '@', $file )) continue; 

				if($is_dir) {
					$_path = preg_replace('@^' . $path . '@', '', $file);
					$remote_path = $remote_path . '/' . ltrim( $_path, '/');
				}

				if('D' == $flag) {
					$this->diff['delete'][$file] = $remote_path;
				}else {
					$this->diff['delete'][$file] = $remote_path;
				}
			} 
		}
	}

}




