<?php

/*
Plugin Name: RINF News Widget
Plugin URI: http://rinf.com
Description: News for your site. 
Author: RINF News
Version: 1.8
Author URI: http://rinf.com
*/

class Rinf_RSS_Widget extends WP_Widget {

	function Rinf_RSS_Widget() {
		$widget_ops = array( 'description' => __('Latest news from http://rinf.com') );
		$control_ops = array( 'width' => 400, 'height' => 200 );
		$this->WP_Widget( 'rinfnews', __('Rinf.com Latest News Widget'), $widget_ops, $control_ops );
		
		
		$this->options = array(
			array("name" => "Title:",
						"id" => "title",
						"type" => "text"
			),
			array("name" => "News items to display:",
						"id" => "count",
						"type" => "select",
						"options" => array("5", "10")
			)
		);
		$this->description = "Displays latest news from <a href='http://rinf.com'>http://rinf.com</a>";
	}

	function widget($args, $instance) {

		if ( isset($instance['error']) && $instance['error'] )
			return;

		extract($args, EXTR_SKIP);

		$url = "http://www.rinf.com/alt-news/feed";
		while ( stristr($url, 'http') != $url )
			$url = substr($url, 1);

		if ( empty($url) )
			return;

		$rss = fetch_feed($url);
		$title = $instance['title'];
		$desc = '';
		$link = '';

		if ( ! is_wp_error($rss) ) {
			$desc = esc_attr(strip_tags(@html_entity_decode($rss->get_description(), ENT_QUOTES, get_option('blog_charset'))));
			if ( empty($title) )
				$title = esc_html(strip_tags($rss->get_title()));
			$link = esc_url(strip_tags($rss->get_permalink()));
			while ( stristr($link, 'http') != $link )
				$link = substr($link, 1);
		}

		if ( empty($title) )
			$title = empty($desc) ? __('Unknown Feed') : $desc;

		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		$url = esc_url(strip_tags($url));
		if($instance["title"] != "")
			$title = "<h3>".$title."</h3>";
		else
			$title = "<a class='rsswidget' href='$url' title='" . esc_attr__( 'Syndicate this content' ) ."'></a> <a class='rsswidget' href='$link' title='$desc'>$title</a>";

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
			if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
					$count = 5;
					if($instance["count"] != "")
						$count = (int)$instance["count"];
					$maxitems = $rss->get_item_quantity($count); 

					// Build an array of all the items, starting with element 0 (first element).
					$rss_items = $rss->get_items(0, $maxitems); 
			endif;
			?>

			<ul>
					<?php if ($maxitems == 0) echo '<li>No recent news or feed error. Please visit <a href="http://rinf.com">http://rinf.com</a> for more information.</li>';
					else
					// Loop through each feed item and display each item as a hyperlink.
					foreach ( $rss_items as $item ) : ?>
					<li>
							<a href='<?php echo $item->get_permalink(); ?>'
							title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
							<?php echo $item->get_title(); ?></a>
					</li>
					<?php endforeach; ?>
			</ul>
			<?php
			echo $after_widget;

		if ( ! is_wp_error($rss) )
			$rss->__destruct();
		unset($rss);
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		return $new_instance;
	}

	function form($instance) {
		$options = $this->options;
?>
		<div><?php echo $this->description;?></div>
		<div style="clear: both; height: 15px;" ></div>
			<?php foreach($options as $option){ ?>
			<?php switch($option["type"]){ 
					case "text":?>
					<p><label for="<?php echo $this->get_field_id($option["id"]); ?>"><?php echo $option["name"];?>
					<input class="widefat" id="<?php echo $this->get_field_id($option["id"]); ?>" 
						name="<?php echo $this->get_field_name($option["id"]); ?>" type="text" 
						value="<?php echo attribute_escape($instance[$option["id"]]); ?>" />
					</label></p>
					<?php break; ?>
				<?php case "textarea":?>
					<p><label for="<?php echo $this->get_field_id($option["id"]); ?>"><?php echo $option["name"];?><textarea rows="6" class="widefat" id="<?php echo $this->get_field_id($option["id"]); ?>" name="<?php echo $this->get_field_name($option["id"]); ?>"><?php echo attribute_escape($instance[$option["id"]]); ?></textarea></label></p>
					<?php break; ?>
				<?php case "checkbox":?>
					<p><label for="<?php echo $this->get_field_id($option["id"]); ?>"><?php echo $option["name"];?><input type="checkbox" class="widefat" id="<?php echo $this->get_field_id($option["id"]); ?>" name="<?php echo $this->get_field_name($option["id"]); ?>" <?php if($instance[$option["id"]]) echo "checked='checked';"; ?>/></label></p>
					<?php break; ?>
				<?php case "select":?>
					<p><label for="<?php echo $this->get_field_id($option["id"]); ?>"><?php echo $option["name"];?>
					<select class="widefat" id="<?php echo $this->get_field_id($option["id"]); ?>" name="<?php echo $this->get_field_name($option["id"]); ?>" ><?php foreach($option["options"] as $opt):?>
							<option <?php if(attribute_escape($instance[$option["id"]]) == $opt){echo "selected='selected'";}; ?> value="<?php echo $opt;?>"><?php echo $opt;?></option>
						<?php endforeach;?>
					</select></label></p>
					<?php break; ?>
				<?php } ?>
			<?php }; ?>
<?php
	}
}

function rinfnews_widget_init(){
	register_widget('Rinf_RSS_Widget');
}

add_action( 'widgets_init', 'rinfnews_widget_init' );
?>