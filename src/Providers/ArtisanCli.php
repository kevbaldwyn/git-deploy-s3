<?php namespace KevBaldwyn\GitDeploy\Providers;

use KevBaldwyn\GitDeploy\Cli as GitDeployCli;
use KevBaldwyn\GitDeploy\Interfaces\CliInterface;

class ArtisanCli extends GitDeployCli implements CliInterface {

	public function getRevisions() 
	{
		
	}

	public function info() {}

	public function warn() {}

	public function error() {}

}

