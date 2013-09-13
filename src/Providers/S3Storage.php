<?php namespace KevBaldwyn\GitDeploy\Providers;

use KevBaldwyn\GitDeploy\Interfaces\BatchStorageInterface;
use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl as AWSPerm;
use Aws\S3\Sync\UploadSync;
use Aws\S3\Sync\UploadSyncBuilder;
use Aws\S3\Model\DeleteObjectsBatch;
use Guzzle\Common\Event;
use Guzzle\Plugin\History\HistoryPlugin;
use ArrayIterator;

class S3Storage implements BatchStorageInterface {

	private $client;

	private $upload = array();
	private $delete = array();

	private $paths;
	private $baseDir = '';

	private $expectedTransfers = 0;
	public $responses = array();
	private $responseMsg = '';

	public function __construct(array $credentials) 
	{
		$this->setUp($credentials);
		$this->addResponseListener();
	}

	public function setUp(array $credentials) 
	{
		$client = S3Client::factory(array(
		    'key'    => $credentials['key'],
		    'secret' => $credentials['secret']
		));
		$this->client = $client;
	}


	private function addResponseListener() {
		$storage = $this;
		$history = new HistoryPlugin();

		$this->client->addSubscriber($history);
		
		$dispatcher = $this->client->getEventDispatcher();
		$dispatcher->addListener('command.after_send', function(Event $e) use ($storage, $history) {
			$request = $history->getLastRequest();
			$storage->responses[] = $request->getResponse()->getStatusCode();
		});
	}


	public function setBaseDir($baseDir) {
		$this->baseDir = $baseDir;
	}


	public function deleteObject($local, $remote)
	{
		$path = static::parseBucket($remote);
		$this->delete[$path[0]][] = $local;
		$this->expectedTransfers ++;
	}

	public function createObject($local, $remote) 
	{
		//store the data for the sync builder
		$path = static::parseBucket($remote);
		$this->upload[$path[0]][] = new \SplFileInfo($local);
		$this->expectedTransfers ++;
	}


	public function batchCreate() 
	{
		if(count($this->upload) > 0) {
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
		return true;
	}


	public function batchDelete()
	{
		if(count($this->delete) > 0) {
			foreach($this->delete as $bucket => $files) {
				$delete = DeleteObjectsBatch::factory($this->client, $bucket);
				foreach($files as $file) {
					$delete->addKey($file);
				}
				$delete->flush();
			}
		}
		return true;
	}


	public function send() 
	{
		$this->batchCreate();
		$this->batchDelete();
	}


	public function successful() 
	{
		$ok = 0;
		foreach($this->responses as $code) {
			var_dump($code);
			if($code == 200) {
				$ok ++;
			}
		}
		if($this->expectedTransfers > $ok) {
			$this->responseMsg = 'Some files weren\'t synced: (' . $ok . ' / ' . $this->expectedTransfers . ' ok).';
			return false;
		}
		return true;
	}


	public function getResponse()
	{
		return $this->responses;
	}


	public function getResponseMessage()
	{
		return $this->responseMsg;
	}


	public static function parseBucket($path) 
	{
		$first_slash = strpos($path, '/');
		$bucket = substr($path, 0, $first_slash);
		$object = substr($path, $first_slash+1);

		return array($bucket, $object);
	}

}