<?php

use \Mockery as m;
use \KevBaldwyn\GitDeploy\Deploy;

class GitDeployTest extends \PHPUnit_Framework_TestCase {

	private $diffCmdResult = "M       assets/js/general.js
A       assets/js/dom.js
D       some/path/config.json
D       some/class.php
D       assets/css/file.css
D       assets/css/general.css
D       assets/css/lib/new.css";
	
	private $paths = array('assets/css' => 'assets-bucket/css',
						   'assets/js' => 'assets-bucket/js');

	private $cli;


	public function setUp() {
		// mock the Cli
		$this->cli = m::mock('KevBaldwyn\GitDeploy\CliInterface');
		$this->cli->shouldReceive('diff')
					->with('hash1', 'hash2')
					->andReturn($this->diffCmdResult);
		$this->cli->shouldReceive('getRevisions')
					->withAnyArgs()
					->andReturn(array('hash1', 'hash2'))
					->once();
	}


	public function test_getDiffReturnsCorrectArrays() {
		$deploy = new Deploy($this->cli, $this->paths);
		$diff = $deploy->getDiff();
		$this->assertEquals(3, count($diff['delete']));
		$this->assertEquals(2, count($diff['upload']));
	}


	public function tearDown() {
        m::close();
    }


}