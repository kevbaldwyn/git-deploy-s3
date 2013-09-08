<?php

use \Mockery as m;
use KevBaldwyn\GitDeploy\Providers\S3Storage;

class S3StorageTest extends \PHPUnit_Framework_TestCase {

	public function test_parseBucket() {
		$path = S3Storage::parseBucket('bucket-name/file/path/here.css');
		$this->assertSame('bucket-name', $path[0]);
	}


	public function tearDown() 
	{
        m::close();
    }

}