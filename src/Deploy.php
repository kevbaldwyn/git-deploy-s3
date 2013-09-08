<?php namespace KevBaldwyn\GitDeploy;

/**
 * the commands needed to carry out the actions
 */
class Deploy {

	private $oldRevision;
	private $newRevision;
	private $cli;

	private $diffRaw;
	private $diff;

	private $paths = array();


	public function __construct(CliInterface $cli, array $paths) 
	{
		$this->cli = $cli;
		$this->getRevisions();
		$this->paths = $paths;
		$this->diff = $this->getDiff();

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


	public function getDiff() 
	{
		$cmdOutput = $this->cli->diff($this->oldRevision, $this->newRevision);
	
		if (!$cmdOutput) {
			throw new \Exception('There was a problem executing the command.');
		}

		$this->diffRaw = explode("\n", trim($cmdOutput));
		return $this->parseDiff();
	}


	private function parseDiff() 
	{
		$diff = array();
		$diff['delete'] = array();
		$diff['upload'] = array();
		foreach($this->diffRaw as $line) {
			if(preg_match("/([A-Z]){1}\s*(.*)/", $line, $matches)) {
				$flag = $matches[1];
				$file = $matches[2];
				foreach($this->paths as $path => $remote_path) {
					$regex = '^' . preg_quote($path, '@');
					//$is_dir = is_dir($path);
					//if(!$is_dir) {
						//$regex .= '$'; 
					//}
					//var_dump($regex);
					if(!preg_match('@' . $regex . '@', $file )) continue; 

					//if($is_dir) {
						$_path = preg_replace('@^' . $path . '@', '', $file);
						$remote_path = $remote_path . '/' . ltrim( $_path, '/');
					//}

					if('D' == $flag) {
						$diff['delete'][$file] = $remote_path;
					}else {
						$diff['upload'][$file] = $remote_path;
					}
				} 
			}	
		}
		return $diff;
	}

}




