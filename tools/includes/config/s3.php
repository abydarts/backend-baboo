<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| Use SSL
|--------------------------------------------------------------------------
|
| Run this over HTTP or HTTPS. HTTPS (SSL) is more secure but can cause problems
| on incorrectly configured servers.
|
*/
$config['use_ssl'] = TRUE;
/*
|--------------------------------------------------------------------------
| Verify Peer
|--------------------------------------------------------------------------
|
| Enable verification of the HTTPS (SSL) certificate against the local CA
| certificate store.
|
*/
$config['verify_peer'] = TRUE;
/*
|--------------------------------------------------------------------------
| Access Key
|--------------------------------------------------------------------------
|
| Your Amazon S3 access key.
|
*/
$config['access_key'] = 'AKIAIUXVSHDIJ6JAEX3Q';
/*
|--------------------------------------------------------------------------
| Secret Key
|--------------------------------------------------------------------------
|
| Your Amazon S3 Secret Key.
|
*/
$config['secret_key'] = 'AD/yihQc0EOkU7dgL1GFGH92mGuvUjbR8wn4X0Jc';
/*
|--------------------------------------------------------------------------
| Use Enviroment?
|--------------------------------------------------------------------------
|
| Get Settings from enviroment instead of this file?
| Used as best-practice on Heroku
|
*/
$config['get_from_enviroment'] = FALSE;
/*
|--------------------------------------------------------------------------
| Access Key Name
|--------------------------------------------------------------------------
|
| Name for access key in enviroment
|
*/
$config['access_key_envname'] = 'S3_KEY';
/*
|--------------------------------------------------------------------------
| Access Key Name
|--------------------------------------------------------------------------
|
| Name for access key in enviroment
|
*/
$config['secret_key_envname'] = 'S3_SECRET';
/*
|--------------------------------------------------------------------------
| If get from enviroment, do so and overwrite fixed vars above
|--------------------------------------------------------------------------
|
*/
if ($config['get_from_enviroment']){
    $config['access_key'] = getenv($config['access_key_envname']);
    $config['secret_key'] = getenv($config['secret_key_envname']);
}

$config['s3key'] = 'AKIAIUXVSHDIJ6JAEX3Q';
$config['s3secret'] = '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';
$config['s3bucket'] = 's3.baboo.id';