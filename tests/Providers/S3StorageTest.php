<?php

use \Mockery as m;
use KevBaldwyn\GitDeploy\Providers\S3Storage;

class S3StorageTest extends \PHPUnit_Framework_TestCase {

	private $s3Key      = '';
	private $s3Secret   = '';
	private $bucketName = '';

	public function test_parseBucket() {
		$path = S3Storage::parseBucket('bucket-name/file/path/here.css');
		$this->assertSame('bucket-name', $path[0]);
	}

	/**
	 * These 2 tests require a connection to a real s3 account to test properly.
	 * This can be configured above.
	 */
	/*
	public function test_upload() {

		$s3 = new S3Storage(array('key'    => $this->s3Key,
								  'secret' => $this->s3Secret));

		$path = __DIR__ . '/../../tests/';

		$s3->setBaseDir($path);
		$s3->createObject($path . 'test-assets/css/1.css', $this->bucketName . '/css/1.css');
		$s3->createObject($path . 'test-assets/css/2.css', $this->bucketName . '/css/2.css');
		$s3->createObject($path . 'test-assets/js/1.js', $this->bucketName . '/js/1.js');
		$s3->createObject($path . 'test-assets/js/2.js', $this->bucketName . '/js/2.js');

		$s3->batchCreate();
	}	
	*/
	
	

	/*
	public function test_delete() {

		$s3 = new S3Storage(array('key'    => $this->s3Key,
								  'secret' => $this->s3Secret));

		$path = __DIR__ . '/../../tests/';

		$s3->setBaseDir($path);
		$s3->deleteObject('test-assets/css/1.css', $this->bucketName . '/css/1.css');
		$s3->deleteObject('test-assets/css/2.css', $this->bucketName . '/css/2.css');
		$s3->deleteObject('test-assets/js/1.js', $this->bucketName . '/js/1.js');
		$s3->deleteObject('test-assets/js/2.js', $this->bucketName . '/js/2.js');

		$s3->batchDelete();
	}
	*/
	

	public function test_responseReturnsFalse() {

		$s3 = new S3Storage(array('key'    => $this->s3Key,
								  'secret' => $this->s3Secret));

		$path = __DIR__ . '/../../tests/';

		$s3->setBaseDir($path);
		$s3->deleteObject('test-assets/css/1.css', $this->bucketName . '/css/1.css');
		$s3->deleteObject('test-assets/css/2.css', $this->bucketName . '/css/2.css');
		$s3->createObject('test-assets/js/1.js', $this->bucketName . '/js/1.js');
		$s3->createObject('test-assets/js/2.js', $this->bucketName . '/js/2.js');

		// set the repsonses so some fail
		$s3->responses = array(200, 501, 200, 200);
		$this->assertFalse($s3->successful());
		$this->assertSame('Some files weren\'t synced: (3 / 4 ok).', $s3->getResponseMessage());
	}


	public function tearDown() 
	{
        m::close();
    }

}