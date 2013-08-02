<?php

/* Load required lib files. */
require_once('twitteroauth/twitteroauth/twitteroauth.php');
require_once('library/Twitter.php');

$config = require_once 'config/local.php';


/* Create a TwitterOauth object with consumer/user tokens. */
$connection = new TwitterOAuth(
    $config['consumer_key'], $config['consumer_secret'],
    $config['oauth_token'], $config['oauth_token_secret']
);

$twitter = new Twitter($connection);

if (array_key_exists('type', $_GET)) {
    $type = $_GET['type'];
} else {
    $type = '';
}

if (array_key_exists('count', $_GET)) {
    $count = $_GET['count'];
} else {
    $count = 10;
}

if (array_key_exists('screen_name', $_GET)) {
    $screen_name = $_GET['screen_name'];
} else {
    $screen_name = '';
}

if (array_key_exists('trim_user', $_GET)) {
    $trim_user = (bool) $_GET['trim_user'];
} else {
    $trim_user = false;
}

switch ($type) {
    case 'user_timeline':
        $json = $twitter->getUserTimeline($count, $screen_name, $trim_user);
        break;
    case 'user':
    default:
        $json = $twitter->getUser();
        break;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
echo $json;
