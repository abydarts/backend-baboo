<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
|| Android Firebase Push Notification Configurations
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
 */

/*
|--------------------------------------------------------------------------
| Firebase API Key
|--------------------------------------------------------------------------
|
| The secret key for Firebase API
|
 */
$config['key'] = getenv('FIREBASE_KEY');

/*
|--------------------------------------------------------------------------
| Firebase Cloud Messaging API URL
|--------------------------------------------------------------------------
|
| The URL for Firebase Cloud Messafing
|
 */

$config['fcm_url'] = 'https://fcm.googleapis.com/fcm/send';
