<?php namespace KevBaldwyn\GitDeploy\Interfaces;

interface BatchStorageInterface {

	public function setUp(array $credentials);

	public function deleteObject($local, $remote);

	public function createObject($local, $remote);

	public function send();

	public function successful();

	public function getResponse();

	public function getResponseMessage();

}