<?php namespace KevBaldwyn\GitDeploy;

/**
 * the commands needed to carry out the actions
 */
class Deploy {

	private $oldRevision;
	private $newRevision;
	private $cli;
	private $storage;

	private $diffRaw;
	private $diff;

	private $paths = array();
	private $baseDir = '';


	public function __construct(Interfaces\CliInterface $cli, array $paths, Interfaces\BatchStorageInterface $storage) 
	{
		$this->cli     = $cli;
		$this->storage = $storage;
		$this->paths   = $paths;

		$this->getRevisions();
		$this->diff = $this->getDiff();

	}

	
	public function setBaseDir($baseDir) 
	{
		$this->baseDir = $baseDir;
		$this->storage->setBaseDir($baseDir);
	}
	


	// git-s3-deploy post <oldrev> <newrev>
	public function post() 
	{
		if(count($this->diff['upload']) > 0) {
			foreach($this->diff['upload'] as $localPath => $uploadPath) {
				$this->storage->createObject($this->baseDir . $localPath, $uploadPath);
			}
			return true;
		}else{
			return false;
		}
	}


	// git-s3-deploy delete <oldrev> <newrev>
	public function delete() 
	{
		if(count($this->diff['delete']) > 0) {
			foreach($this->diff['delete'] as $localPath => $uploadPath) {
				$this->storage->deleteObject($this->baseDir . $localPath, $uploadPath);
			}
			return true;
		}else{
			return false;
		}
	}


	// git-s3-deploy sync <oldrev> <newrev>
	public function snyc() 
	{
		if($this->delete() || $this->post()) {
			return $this->commit();
		}
		return false;
	}


	public function commit() 
	{
		$success = false;
		try {
			//$this->storage->setLocalPaths($this->paths, $this->baseDir);
			$this->storage->send();

			$success = $this->storage->successful();
			$msg     = $this->storage->getResponseMessage();
		}catch(\Exception $e) {
			$msg = $e->getMessage();
		}

		if(!$success) {
			throw new \Exception($msg);
		}

		return true;
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
	
		if (is_null($cmdOutput)) {
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




