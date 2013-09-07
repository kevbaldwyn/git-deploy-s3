<?php namespace KevBaldwyn\Interfaces;

interface CliInterface {

	public function getRevisions();

	public function info($text);

	public function warn($text);

	public function error($text);

}