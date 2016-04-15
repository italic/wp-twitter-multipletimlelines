<?php
/*
Plugin Name: Twitter Multiple Timelines
Description: Allow you to show one or more Twitter timelines, and filter them with some hashtags.
Version: 0.1
Author: Italic
License: MIT
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once plugin_dir_path( __FILE__ ).'classes/timelines.php';
require_once plugin_dir_path( __FILE__ ).'nojimage/twitter-text-php/lib/Twitter/Autolink.php';

function tmt_launch_widget() {
	register_widget('TwitterMultipleTimelines_Widget');
}
add_action( 'widgets_init', 'tmt_launch_widget' );


register_activation_hook( __FILE__, array( 'TwitterMultipleTimelines_Widget', 'install' ) );
?>