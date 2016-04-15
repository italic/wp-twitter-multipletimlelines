<?php
ini_set('display_errors', 1);
require '../../../wp-config.php';
require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

/*
CREATE TABLE `wp_twitter_multiple_timelines` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tweet_id` bigint(20) DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `created_at` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;
*/

$options = get_option('widget_twitter_multiple_timelines');

$hashTags = isset($options[2]['hashTag']) && $options[2]['hashTag'] ? explode(',', $options[2]['hashTag']) : array();
$screen_names = isset($options[2]['account']) && $options[2]['account'] ? explode(',', $options[2]['account']) : array();
$retweets = isset($options[2]['retweets']) && $options[2]['retweets'] ? $options[2]['retweets'] : $options[2]['retweets'];
$CONSUMER_KEY = isset($options[2]['CONSUMER_KEY']) && $options[2]['CONSUMER_KEY'] ? $options[2]['CONSUMER_KEY'] : '';
$CONSUMER_SECRET = isset($options[2]['CONSUMER_SECRET']) && $options[2]['CONSUMER_SECRET'] ? $options[2]['CONSUMER_SECRET'] : '';

if (!$CONSUMER_KEY)
	exit('CONSUMER_KEY non définie');

if (!$CONSUMER_SECRET)
	exit('CONSUMER_SECRET non définie');


//$screen_names = array('CAREfrance', 'CoalitionEau', 'ACF_France', 'AMREFFRANCE', 'GHAFrance');
$tmp_tweets = array();

//tweet url
$all_max_id = 0;
$count = count($screen_names);

foreach($screen_names as $screen_name) :
	$tmp_tweets[$screen_name] = array();

	// On obtient le token d'accès
	$oauth = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET);
	$accessToken = $oauth->oauth2('oauth2/token', ['grant_type' => 'client_credentials']);
	$access_token = $accessToken->access_token;
	$max_id = 0;
	//on parse les 800 derniers tweets de l'utilisateur
	for ($i = 1; $i <= 4; $i++) :

		// on appel l'API
		$twitter = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, null, $access_token);

		$params = array(
		    'id' => $screen_name,
		    'count' => 6 // On est obligé de filtrer après coup (cf doc)
		);

		if ($max_id) :
			$params['max_id'] = $max_id;
		endif;

		$tweets = $twitter->get('collections/show.json', $params);

		// echo "<pre>";
		// print_r($tweets);
		// echo "</pre>";
		$last_tweet = end($tweets);
		$max_id = $last_tweet->id;

		if (!$all_max_id || $max_id < $all_max_id)
			$all_max_id = $max_id;

		if (sizeof($tweets)) : foreach ($tweets as $tweet) :
			if (isset($tweet->entities->hashtags) && sizeof($tweet->entities->hashtags)) :
				foreach ($tweet->entities->hashtags as $hash) :
					if (in_array($hash->text, $hashTags) || !sizeof($hashTags)) :
						$tmp_tweets[$screen_name][] = $tweet;
					endif;
				endforeach;
			endif;
		endforeach; endif;

	endfor;
endforeach;

$query = "DELETE FROM wp_twitter_multiple_timelines WHERE tweet_id >=  ".esc_sql($all_max_id);
$wpdb->query($query);

if (sizeof($tmp_tweets)) :
	foreach ($tmp_tweets as $user => $tweets) :
		foreach($tweets as $tweet) :
			// echo "<pre>";
			// print_r($tweet);
			// echo "</pre>";

			$wpdb->insert($wpdb->prefix.'twitter_multiple_timelines', array(
							'tweet_id' => $tweet->id,
							'text' => $tweet->text,
							'user' => $user,
							'created_at' => $tweet->created_at
							));
		endforeach;
	endforeach;
endif;


