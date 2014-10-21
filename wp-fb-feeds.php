<?php
session_start();
/*
Plugin Name: WP Facebook Feeds
Plugin URI: http://wordpress.org/extend/plugins/wp-fb-feeds/
Description: it will show the public posts from your Facebook page. 
Author: Mritunjay Datt Tiwari
Author URI: http://hopeadjustor.com/
Version: 0.1
Text Domain: wp-fb-feeds
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
$dir = plugin_dir_path( __FILE__ );
class wp_fb_feeds_widget extends WP_Widget {
	
	// constructor
	function wp_fb_feeds_widget() {
	        parent::WP_Widget(false, $name = __('WP Facebook Feeds', 'wp_fb_feeds_widget') );
			// Register style sheet.
			add_action( 'wp_enqueue_scripts', 'register_plugin_styles' );

			/**
			 * Register style sheet.
			 */
			function register_plugin_styles() {
				wp_register_style( 'wp-fb-feeds-style', plugins_url( 'wp-fb-feeds/css/wp-fb-feeds-style.css' ) );
				wp_enqueue_style( 'wp-fb-feeds-style' );
			}
	}

// widget form creation
function form($instance) {

//assign default values
$instance = wp_parse_args( (array) $instance, array(
'title' => __("Facebook Feeds",'wp_fb_feeds_widget'),
'appid' => __("Enter Your App ID",'wp_fb_feeds_widget'),
'appskey' => __("Enter Your App Secret Key",'wp_fb_feeds_widget'),
'pid' => __("Enter Your Page ID",'wp_fb_feeds_widget'),
'nooffeeds' => __("5",'wp_fb_feeds_widget'),
'noofchar' => __("80",'wp_fb_feeds_widget'),
) 
);

// Check values
if( $instance) {
     $title = esc_attr($instance['title']);
     $appid = $instance['appid'];
	 $appskey = $instance['appskey'];
	 $pid = $instance['pid'];
	 $nooffeeds = $instance['nooffeeds'];
	 $noofchar = $instance['noofchar'];
} else {
     $title = '';
     $appid = '';
	 $appskey ='';
	 $pid = '';
	 $nooffeeds ='';
	 $noofchar='';

}
?>
<div class="input_fields_wrap">
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'home_wp_fb_feeds_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id('appid'); ?>"><?php _e('Facebook App ID', 'home_wp_fb_feeds_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('appid'); ?>" name="<?php echo $this->get_field_name('appid'); ?>" type="text" value="<?php echo $appid; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id('appskey'); ?>"><?php _e('Facebook App Secret Key', 'home_wp_fb_feeds_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('appskey'); ?>" name="<?php echo $this->get_field_name('appskey'); ?>" type="password" value="<?php echo $appskey; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id('pid'); ?>"><?php _e('Facebook Page ID', 'home_wp_fb_feeds_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('pid'); ?>" name="<?php echo $this->get_field_name('pid'); ?>" type="text" value="<?php echo $pid; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id('nooffeeds'); ?>"><?php _e('Number of Feeds To Show', 'home_wp_fb_feeds_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('nooffeeds'); ?>" name="<?php echo $this->get_field_name('nooffeeds'); ?>" type="text" value="<?php echo $nooffeeds; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id('noofchar'); ?>"><?php _e('Number of Characters To Show in post', 'home_wp_fb_feeds_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('noofchar'); ?>" name="<?php echo $this->get_field_name('noofchar'); ?>" type="text" value="<?php echo $noofchar; ?>" />
</p>

<p>
	To get App id and Secret Key<a href="https://developers.facebook.com/apps/" target="_blank"> Click here</a> <br>
</p>
</div>
<?php
}

	
function update($new_instance, $old_instance) {
      $instance = $old_instance;
      // Fields
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['appid'] = strip_tags($new_instance['appid']);
	  $instance['appskey'] = strip_tags($new_instance['appskey']);
	  $instance['pid'] = strip_tags($new_instance['pid']);
	  $instance['nooffeeds'] = strip_tags($new_instance['nooffeeds']);
	  $instance['noofchar'] = strip_tags($new_instance['noofchar']);
     return $instance;
	 
}
	

   // display widget
function widget($args, $instance) {
   extract( $args );
   // these are the widget options
   $title = apply_filters('widget_title', $instance['title']);
   $appid = $instance['appid'];
   $appskey = $instance['appskey'];
   $pid = $instance['pid'];
   $nooffeeds = $instance['nooffeeds'];
   $noofchar = $instance['noofchar'];

   // include the facebook sdk
	require_once($dir.'src/facebook.php');
	$config = array();
	// connect to app
	if(!empty($appid)){
		$config['appId'] = $appid;
	}
	else{
		$config['appId'] = '344617158898614';
	}
	if(!empty($appskey)){
		$config['secret'] = $appskey;
	}
	else{
		$config['secret'] = '6dc8ac871858b34798bc2488200e503d';
	}
	
    $config['fileUpload'] = true; // optional

    // instantiate
    $facebook = new Facebook($config);

    // set page id
	if(!empty($pid)){
		$pageid = $pid;
	}
	else{
		$pageid = "PAGE_ID";
	}
	if(!empty($noofchar))
	{
		$length = $noofchar; //modify for desired width
	}
	else{
		$length = 80;
	}
    // now we can access various parts of the graph, starting with the feed
    $pagefeed = $facebook->api("/" . $pageid . "/feed");
	
    echo "<div class=\"fb-feed\">";
    // set counter to 0, because we only want to display 10 posts
	echo '<h2>'. $title . '</h2>';
     $i = 0;
    foreach($pagefeed['data'] as $post) {
		if ($post['type'] == 'status' || $post['type'] == 'link' || $post['type'] == 'photo') 
		{
        // open up an fb-update div
        echo "<div class=\"fb-update\">";
        // post the time
         // check if post type is a status
        if ($post['type'] == 'status') {
             echo "<h2>Status updated: " . date("jS M, Y", (strtotime($post['created_time']))) . "</h2>";
			if (empty($post['story']) === false) {
				echo "<p>" . $post['story'] . "</p>";
			} elseif (empty($post['message']) === false) {
				
				echo "<p>" .preg_replace('/\s+?(\S+)?$/', '', substr($post['message'], 0, $length)). "</p>";
				echo "<p><a href=\"https://www.facebook.com/".$pageid ."?hc_location=timeline\" target=\"_blank\">View More &rarr;</a></p>";
			}
        }
                        
         // check if post type is a link
         if ($post['type'] == 'link') {
             echo "<h2>Link posted on: " . date("jS M, Y", (strtotime($post['created_time']))) . "</h2>";
            echo "<p>" . $post['name'] . "</p>";
             echo "<p><a href=\"" . $post['link'] . "\" target=\"_blank\">" . $post['link'] . "</a></p>";
        }
                        
         // check if post type is a photo
        if ($post['type'] == 'photo') {
        echo "<h2>Photo posted on: " . date("jS M, Y", (strtotime($post['created_time']))) . "</h2>";
			if (empty($post['story']) === false) {
				echo "<p>" . $post['story'] . "</p>";
			} elseif (empty($post['message']) === false) {
				 echo "<p>" . preg_replace('/\s+?(\S+)?$/', '', substr($post['message'], 0, $length)) . "</p>";
			}
			 echo "<p><a href=\"" . $post['link'] . "\" target=\"_blank\">View photo &rarr;</a></p>";
			 echo '<iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;width&amp;layout=standard&amp;action=like&amp;show_faces=false&amp;share=true&amp;height=35&amp;appId='.$appid.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:35px;" allowTransparency="true"></iframe>';
       }
        echo "</div>"; // close fb-update div
                    
        $i++; // add 1 to the counter if our condition for $post['type'] is met
		}
		//  break out of the loop if counter has reached 10
		if ($i == $nooffeeds) {
			break;
		}
    } 	// end the foreach statement
         echo "</div>";

}

}
// register widget
add_action('widgets_init', create_function('', 'return register_widget("wp_fb_feeds_widget");')); 

?>