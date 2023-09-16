<?php
/**
 * Plugin Name: RSS Feed Importer
 * Description: This plugin imports RSS feed content as posts.
 * Version: 1.0
 * Author: Your Name
 */

// Hook into WordPress to add a menu item for the plugin
function rss_feed_importer_menu() {
    add_menu_page('RSS Feed Importer', 'RSS Importer', 'manage_options', 'rss-feed-importer', 'rss_feed_importer_page');
}
add_action('admin_menu', 'rss_feed_importer_menu');

// Function to display the plugin's admin page
function rss_feed_importer_page() {
    ?>
    <div class="wrap">
        <h2>RSS Feed Importer</h2>
        <form method="post" action="">
            <label for="rss_feed_url">RSS Feed URL:</label>
            <input type="text" id="rss_feed_url" name="rss_feed_url" size="50"><br><br>
            <input type="submit" name="import_feed" value="Import Feed">
        </form>
    </div>
    <?php
}

// Function to handle the RSS feed import
function import_rss_feed() {
    if (isset($_POST['import_feed'])) {
       $feed_url = sanitize_text_field($_POST['rss_feed_url']);
       // $rss_url =  fetch_rss_feed($rss_feed_url);
       $ch = curl_init($feed_url);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       $rss_data = curl_exec($ch);
       curl_close($ch);
   
       if ($rss_data) {
           $rss = simplexml_load_string($rss_data, 'SimpleXMLElement', LIBXML_NOCDATA);
           if ($rss) {
               foreach ($rss->channel->item as $item) {
                   $post_data = array(
                       'post_title' => (string)$item->title,
                       'post_content' => (string)$item->description,
                       //'post_date'=> (string)$item->pubDate,
                       'post_status' => 'draft',
                       'post_author'   => 1,
                       'post_category' => array(8,39)
                   );
   
                   // Insert the post into the WordPress database
                   wp_insert_post($post_data);
                   //echo json_encode($post_data);
                   add_action('admin_notices', 'rss_publish_success_notice');
               }
               //echo '<p>Feed items published as posts.</p>';
           } else {
               echo '<div class="notice notice-success is-dismissible"><p>Failed to parse the RSS feed.</p>';
           }
       } else {
           echo '<div class="notice notice-success is-dismissible"><p>Failed to fetch the RSS feed using cURL.</p>';
       }
}
}

function rss_publish_success_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>Article published successfully!</p>
    </div>
    <?php
}

// Hook into WordPress to handle the RSS feed import
add_action('admin_init', 'import_rss_feed');
?>
