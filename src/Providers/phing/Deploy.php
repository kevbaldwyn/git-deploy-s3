<?php 
require_once 'phing/Task.php';
require_once __DIR__ . '/../../vendor/autoload.php';

namespace KevBaldwyn\GitDeploy\Providers;

use \KevBaldwyn\GitDeploy\Deploy;
use \KevBaldwyn\GitDeploy\Providers\AutomatedCli;

/**
 * 1) git ls-remote
 *
 * Outputs:
 * From git@github.com:kevbaldwyn/git-deploy-s3.git
 * b9d4be453fadf401ac5c26862a078903c8b27629	HEAD
 * b9d4be453fadf401ac5c26862a078903c8b27629	refs/heads/master
 *
 * 2) git rev-parse HEAD
 * Outputs an sha hash: e813f58c785ad9530818d52d2982b3ca3d52ae82
 * which can be used to pass the arguments to the deployer
 * 
 * # git diff --name-status e813f58c785ad9530818d52d2982b3ca3d52ae82 b9d4be453fadf401ac5c26862a078903c8b27629
 */
class Phng {
	
	private $oldRevision;
	private $newRevision;
	private $baseDir;
	private $paths;

	private $credentials;


	public function setOldRevision($v) {
		$this->oldRevision = $v;
	}


	public function setNewRevision($v) {
		$this->newRevision = $v;
	}


	public function setBaseDir($v) {
		$this->baseDir = $v;
	}


	/**
	 * <property name="credntials[key]" value="12345" />
	 * <property name="credntials[secret]" value="123456789" />
	 * <taskname credntials="${credntials}" />
	 */
	public function setCredentials($v) {
		$this->credentials = $v;
	}


	/**
	 * <property name="paths[local]" value="remote" />
	 * <property name="paths[local]" value="remote" />
	 * <taskname paths="${paths}" />
	 */
	public function setPaths($v) {
		$this->paths;
	}


	public function main() {
		if (!$this->oldRevision || !$this->newRevision) {
			throw new BuildException("You must specify the old revision and new revisions", $this->getLocation());
		}
		if (!$this->baseDir) {
			throw new BuildException("You must specify the base dir for the files", $this->getLocation());
		}

		try {
			$deploy = new Deploy(new AutomatedCli($this->oldRevision, $this->newRevision), 
								 $this->paths, 
								 new KevBaldwyn\GitDeploy\Providers\S3Storage($this->credentials));

			$deploy->setBaseDir($this->baseDir);
			
			$deploy->snyc();
		}catch(\Exception $e) {
			// convert the exception
			throw new BuildException($e->getMessage(), $this->getLocation());
		}

	}

}

