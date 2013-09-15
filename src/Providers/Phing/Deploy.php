<?php

require_once 'phing/Task.php';
require_once __DIR__ . '/../../../../../autoload.php';

use \KevBaldwyn\GitDeploy\Deploy as Deployer;
use \KevBaldwyn\GitDeploy\Providers\AutomatedCli;
use \KevBaldwyn\GitDeploy\Providers\S3Storage;


class Deploy extends Task {
	
	private $oldRevision;
	private $newRevision;
	private $ensureBranch;
	private $baseDir;
	private $paths;

	private $credentials;

	private $additionalFiles;


	public function setOldRevision($v) 
	{
		$this->oldRevision = $v;
	}


	public function setNewRevision($v) 
	{
		$this->newRevision = $v;
	}


	public function setBaseDir($v) 
	{
		$this->baseDir = $v;
	}


	/**
	 * <taskname credntials="key=value,secret=value" />
	 */
	public function setCredentials($v) 
	{
		$this->credentials = $this->propertyToArray($v);
	}


	/**
	 * <taskname paths="local/path=bucket/path,local/otherpath=bucket/otherpath" />
	 */
	public function setPaths($v) 
	{
		$this->paths = $this->propertyToArray($v);
	}


	public function setEnsureBranch($v)
	{
		$this->ensureBranch = $v;
	}

	// withFiles="type.1=upload,local.1=public/assets/build/css/styles.css,remote.1=funeralzone-test/assets/build/css/styles.css,type.2=upload,local.2=public/assets/build/js/site.js,remote.2=funeralzone-test/assets/build/js/site.js"
	public function setWithFiles($v)
	{
		$tmp = $this->propertyToArray($v);
		$files = array();
		foreach($tmp as $key => $value) {
			$keys = explode('.', $key);
			$files[$keys[1]][$keys[0]] = $value;
		}
		$this->additionalFiles = $files;
	}


	public function main() 
	{
		if (!$this->oldRevision || !$this->newRevision) {
			throw new BuildException("You must specify the old revision and new revisions", $this->getLocation());
		}
		if (!$this->baseDir) {
			throw new BuildException("You must specify the base dir for the files", $this->getLocation());
		}
		if (!is_array($this->paths)) {
			throw new BuildException("You must specify the base path mappings", $this->getLocation());
		}
		if (!is_array($this->credentials)) {
			throw new BuildException("You must specify the login credentials for the storage interface", $this->getLocation());
		}


		$cli = new AutomatedCli($this->oldRevision, $this->newRevision);
		
		if(!is_null($this->ensureBranch)) {
			$currentBranch = $cli->currentBranch();
			if($currentBranch != $this->ensureBranch) {
				throw new BuildException('Current branch (' . $currentBranch . ') does not match specified branch (' . $this->ensureBranch . ')', $this->getLocation());
			}
		}

		try {

			$deploy = new Deployer($cli, 
								 $this->paths, 
								 new S3Storage($this->credentials));

			$deploy->setBaseDir($this->baseDir);
			
			if(is_array($this->additionalFiles)) {
				foreach($this->additionalFiles as $array) {
					$deploy->addFile($array['local'], $array['remote'], $array['type']);
				}
			}

			$deploy->snyc();
		}catch(\Exception $e) {
			// convert the exception
			throw new BuildException($e->getMessage(), $this->getLocation());
		}

	}


	private function propertyToArray($string) 
	{
		$return = array();
		$values = explode(',', $string);
		foreach($values as $value) {
			list($k, $v) = explode('=', trim($value));
			$return[trim($k)] = trim($v);
		}
		return $return;
	}

}

