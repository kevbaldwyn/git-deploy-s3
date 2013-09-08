<?php namespace KevBaldwyn\GitDeploy\Providers;

/**
 * 1) git ls-remote
 *
 * Outputs:
 * From git@github.com:kevbaldwyn/git-deploy-s3.git
 * b9d4be453fadf401ac5c26862a078903c8b27629	HEAD
 * b9d4be453fadf401ac5c26862a078903c8b27629	refs/heads/master
 *
 * 2) git rev-parse HEAD
 * Outputs an sha hash: e813f58c785ad9530818d52d2982b3ca3d52ae82
 * which can be used to pass the arguments to the deployer
 * 
 * # git diff --name-status e813f58c785ad9530818d52d2982b3ca3d52ae82 b9d4be453fadf401ac5c26862a078903c8b27629
 */
class Phng {
	
}

