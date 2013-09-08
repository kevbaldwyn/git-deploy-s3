<?php namespace KevBaldwyn\GitDeploy\Providers;

use KevBaldwyn\GitDeploy\Interfaces\BatchStorageInterface;
use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl as AWSPerm;
use Aws\S3\Sync\UploadSyncBuilder;

use ArrayIterator;

class S3Storage implements BatchStorageInterface {

	private $client;

	private $upload = array();
	private $delete = array();

	private $paths;
	private $baseDir = '';

	public function __construct(array $credentials) 
	{
		$this->setUp($credentials);
	}

	public function setUp(array $credentials) 
	{
		$client = S3Client::factory(array(
		    'key'    => $credentials['key'],
		    'secret' => $credentials['secret']
		));
		$this->client = $client;
	}


	public function setBaseDir($baseDir) {
		$this->baseDir = $baseDir;
	}


	public function deleteObject($local, $remote)
	{

	}

	public function createObject($local, $remote) 
	{
		//store the data for the sync builder
		$path = static::parseBucket($remote);
		$this->upload[$path[0]][] = $local;
	}


	public function batchCreate() 
	{
		// loop over the buckets
		foreach($this->upload as $bucket => $files) {
				
			UploadSyncBuilder::getInstance()
							    ->setClient($this->client)
							    ->setBucket($bucket)
							    ->setBaseDir($this->baseDir)
							    ->setAcl(AWSPerm::PUBLIC_READ)
								
							    ->setSourceIterator(new ArrayIterator($files)) 

							    ->build()
							    ->transfer();
				
		}
	}


	public function send() 
	{
		
		$this->batchCreate();
		
	}


	public function successful() 
	{
		return true;
	}

	public function getResponse()
	{
		
	}

	public function getResponseMessage()
	{
		
	}


	public static function parseBucket($path) 
	{
		$first_slash = strpos($path, '/');
		$bucket = substr($path, 0, $first_slash);
		$object = substr($path, $first_slash+1);

		return array($bucket, $object);
	}

}