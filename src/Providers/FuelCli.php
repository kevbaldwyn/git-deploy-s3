<?php namespace KevBaldwyn\GitDeploy\Providers;

use KevBaldwyn\GitDeploy\Cli as GitDeployCli;
use KevBaldwyn\GitDeploy\Interfaces\CliInterface;

class FuelCli extends GitDeployCli implements CliInterface {

	public function getRevisions() 
	{
		return array(\Cli::option(0), \Cli::option(1));
	}

	public function info($text) {
		echo \CLi::write($text, 'green');
	}

	public function warn($text) {
		echo \CLi::write($text, 'yellow');
	}

	public function error($text) {
		\Cli::error($text);
	}

}

