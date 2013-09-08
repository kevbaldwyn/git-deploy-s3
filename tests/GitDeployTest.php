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


	public function test_getDiffReturnsCorrectArrays() 
	{
		$deploy = new Deploy(static::mockCli($this->diffCmdResult), $this->paths, static::mockStorage());
		$diff = $deploy->getDiff();
		$this->assertEquals(3, count($diff['delete']));
		$this->assertEquals(2, count($diff['upload']));
	}


	public function test_postReturnsTrueWithFiles() 
	{
		$storage = static::mockStorage();
		$storage->shouldReceive('createObject')->atLeast()->once();

		$deploy = new Deploy(static::mockCli($this->diffCmdResult), $this->paths, $storage);
		$this->assertTrue($deploy->post());
	}


	public function test_postReturnsFalseWithoutFiles() 
	{
		$deploy = new Deploy(static::mockCli(), array('upload' => array(), 'delete' => array()), static::mockStorage());
		$this->assertFalse($deploy->post());
	}


	public function test_deleteReturnsTrueWithFiles() 
	{
		$storage = static::mockStorage();
		$storage->shouldReceive('deleteObject')->atLeast()->once();

		$deploy = new Deploy(static::mockCli($this->diffCmdResult), $this->paths, $storage);
		$this->assertTrue($deploy->delete());
	}


	public function test_deleteReturnsFalseWithoutFiles() 
	{
		$deploy = new Deploy(static::mockCli(), array('upload' => array(), 'delete' => array()), static::mockStorage());
		$this->assertFalse($deploy->delete());
	}


	/**
	 * @expectedException Exception
	 */
	public function test_commitThrowsExceptionOnError() {
		$storage = static::mockStorageWithSendError();

		$deploy = new Deploy(static::mockCli($this->diffCmdResult), $this->paths, $storage);
		$deploy->commit();
	}


	/**
	 * @expectedException Exception
	 */
	public function test_commitThrowsExceptionOnStorageException() {
		$storage = static::mockStorage();
		$storage->shouldReceive('send')->once()->andThrow('Exception', 'Some exception');

		$deploy = new Deploy(static::mockCli($this->diffCmdResult), $this->paths, $storage);
		$deploy->commit();
	}


	public function test_commitReturnsTrueOnSuccess() {
		$storage = static::mockStorageWithSendSuccess();

		$deploy = new Deploy(static::mockCli($this->diffCmdResult), $this->paths, $storage);
		$this->assertTrue($deploy->commit());
	}





	private static function mockCli($result = '') {
		// mock the Cli
		$cli = m::mock('KevBaldwyn\GitDeploy\CliInterface');
		$cli->shouldReceive('diff')
					->with('hash1', 'hash2')
					->andReturn($result);
		$cli->shouldReceive('getRevisions')
					->withAnyArgs()
					->andReturn(array('hash1', 'hash2'))
					->once();
		return $cli;
	}


	private static function mockStorage() {
		return m::mock('KevBaldwyn\GitDeploy\BatchStorageInterface');
	}


	private static function mockStorageWithSendError() {
		$storage = static::mockStorage();
		$storage->shouldReceive('send')->once();
		$storage->shouldReceive('successful')->once()->andReturn(false);
		$storage->shouldReceive('getResponseMessage')->once()->andReturn('Some error');
		return $storage;
	}


	private static function mockStorageWithSendSuccess() {
		$storage = static::mockStorage();
		$storage->shouldReceive('send')->once();
		$storage->shouldReceive('successful')->once()->andReturn(true);
		$storage->shouldReceive('getResponseMessage');
		return $storage;
	}


	public function tearDown() 
	{
        m::close();
    }


}