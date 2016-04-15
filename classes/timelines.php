<?php

class TwitterMultipleTimelines_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'class_name' => 'twitter_multiple_timelines',
			'description' => 'Show one or more timeline(s) from Twitter',
		);
		parent::__construct( 'twitter_multiple_timelines', 'Twitter Multiple Timelines', $widget_ops );
	}

	public function install() {
		global $wpdb;
		$query = "CREATE TABLE `".$wpdb->prefix."twitter_multiple_timelines` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `tweet_id` bigint(20) DEFAULT NULL,
				  `text` varchar(255) DEFAULT NULL,
				  `user` varchar(255) DEFAULT NULL,
				  `created_at` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;";
		$wpdb->query($query);

	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $wpdb;
		// outputs the content of the widget
		$query = "SELECT * FROM ".$wpdb->prefix."twitter_multiple_timelines";
		$tweets = $wpdb->get_results($query);
		if (sizeof($tweets)) :
			?>
			<ol class="tweets">
				<?php
				foreach ($tweets as $tweet) :
					$tweet_text = Twitter_Autolink::create()
								  ->setNoFollow(false)
								  ->autoLink($tweet->text);
					?>
					<li>
						<div class="msg">
							<span class="twit">
								<a href="http://twitter.com/<?php echo $tweet->user; ?>" target="_blank"><?php echo $tweet->user; ?></a> :
							</span>
							<span class="msgtxt">
								<?php echo $tweet_text; ?>
							</span>
						</div>
						<div class="info">
							<a href="https://twitter.com/<?php echo $tweet->user; ?>/status/<?php echo $tweet->tweet_id; ?>" class="tweet-link"><?php echo $this->getRelativeTime($tweet->created_at); ?></a>
						</div>
						<p class="clearleft"></p>
					</li>
					<?php

					// echo $this->c( $do_more = 0 );
				endforeach;
				?>
			</ol>
			<?php
		endif;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

        $account = isset( $instance['account'] ) ? $instance['account'] : '';
        $hashTag = isset( $instance['hashTag'] ) ? $instance['hashTag'] : '';
        $retweets = $instance['retweets'];
        $CONSUMER_KEY = isset( $instance['CONSUMER_KEY'] ) ? $instance['CONSUMER_KEY'] : '';
        $CONSUMER_SECRET = isset( $instance['CONSUMER_SECRET'] ) ? $instance['CONSUMER_SECRET'] : '';
		?>
		<p>
			<label>Account
				<input class="account widefat" id="account" name="account" type="text" value="<?php echo esc_attr( $account ); ?>" />
			</label>
			<br /><small>Séparer les différents comptes par une virgule</small>
		</p>
		<p>
			<label>HashTag
				<input class="hashTag widefat" id="hashTag" name="hashTag" type="text" value="<?php echo esc_attr( $hashTag ); ?>" />
			</label>
			<br /><small>Séparer les différents hashtags par une virgule</small>
		</p>

		<p>
			<label>
				<input type="checkbox" id="retweets" name="retweets"<?php if ($retweets) { echo ' checked'; } ?>> Inclure les retweets
			</label>
		</p>

		<p>
			<label for="">CONSUMER KEY
				<input class="CONSUMER_KEY widefat" id="CONSUMER_KEY" name="CONSUMER_KEY" type="text" value="<?php echo esc_attr( $CONSUMER_KEY ); ?>" />
			</label>
			<br /><small>Ces informations se trouvent dans l'onglet "Keys And Access Tokens" de votre application twitter</small>
		</p>

		<p>
			<label for="">CONSUMER SECRET
				<input class="CONSUMER_SECRET widefat" id="CONSUMER_SECRET" name="CONSUMER_SECRET" type="text" value="<?php echo esc_attr( $CONSUMER_SECRET ); ?>" />
			</label>
			<br /><small>Ces informations se trouvent dans l'onglet "Keys And Access Tokens" de votre application twitter</small>
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$new_instance[ 'account' ] = isset($_POST['account']) ? $_POST['account'] : '';
		$new_instance[ 'hashTag' ] = isset($_POST['hashTag']) ? $_POST['hashTag'] : '';
		$new_instance[ 'retweets' ] = $_POST['retweets'];
		$new_instance[ 'CONSUMER_KEY' ] = isset($_POST['CONSUMER_KEY']) ? $_POST['CONSUMER_KEY'] : '';
		$new_instance[ 'CONSUMER_SECRET' ] = isset($_POST['CONSUMER_SECRET']) ? $_POST['CONSUMER_SECRET'] : '';
        return $new_instance;
	}


	//+ Maigret Aurélien
	//@ http://www.dewep.net
	function getRelativeTime($date)
	{
		$date_a_comparer = new DateTime($date);
		$date_actuelle = new DateTime("now");

		$intervalle = $date_a_comparer->diff($date_actuelle);

		if ($date_a_comparer > $date_actuelle)
		{
			$prefixe = 'dans ';
		}
		else
		{
			$prefixe = 'il y a ';
		}

		$ans = $intervalle->format('%y');
		$mois = $intervalle->format('%m');
		$jours = $intervalle->format('%d');
		$heures = $intervalle->format('%h');
		$minutes = $intervalle->format('%i');
		$secondes = $intervalle->format('%s');

		if ($ans != 0)
		{
			$relative_date = $prefixe . $ans . ' an' . (($ans > 1) ? 's' : '');
			if ($mois >= 6) $relative_date .= ' et demi';
		}
		elseif ($mois != 0)
		{
			$relative_date = $prefixe . $mois . ' mois';
			if ($jours >= 15) $relative_date .= ' et demi';
		}
		elseif ($jours != 0)
		{
			$relative_date = $prefixe . $jours . ' jour' . (($jours > 1) ? 's' : '');
		}
		elseif ($heures != 0)
		{
			$relative_date = $prefixe . $heures . ' heure' . (($heures > 1) ? 's' : '');
		}
		elseif ($minutes != 0)
		{
			$relative_date = $prefixe . $minutes . ' minute' . (($minutes > 1) ? 's' : '');
		}
		else
		{
			$relative_date = $prefixe . ' quelques secondes';
		}

		return $relative_date;
	}
}