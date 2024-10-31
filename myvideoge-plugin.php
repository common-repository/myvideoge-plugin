<?php
/*
Plugin Name: Myvideo.Ge Plugin
Plugin URI: http://landish.ge/plugins/myvideo/
Description: This Plugin allows you to put videos from <a href="http://myvideo.ge/" title="Georgian Video Hosting Service">Myvideo.Ge</a> easily in your Wordpress Blog Posts and Pages.
Version: 1.5
Author: Lado Lomidze
Author URI: http://landish.ge/
*/

function myvideo($content) {

	/* important settings to define video dimensions */
	$options = get_option('myvideo_option');
		
	if(empty($options['width'])) $options['width'] = "452";
	if(empty($options['height']) || !is_numeric($options['height'])) $options['height'] = "380";
	
	/* variable for filtering myvideo.ge URL */
	$video_url = "/(<p>|<p style=\"text-align: center;\">|<p style=\"text-align: right;\">|<p style=\"text-align: left;\">)(http:\/\/)(www\.|)myvideo.ge\/\?video_id=(\S+)( |<\/p>)/";
	
	/* html output for embed video */
	$video_embed = "
	<!-- Myvideo.Ge Plugin for Wordpress by Landish - http://landish.ge/plugins/myvideo/ -->
	\${1}
	<object id=\"flowplayer\" width=\"".$options['width']."\" height=\"".$options['height']."\" data=\"http://embed.myvideo.ge/flv_player/external_player.swf\" type=\"application/x-shockwave-flash\">
		<param name=\"flashvars\" value=\"config=http://embed.myvideo.ge/flv_player/flowconfig.php?video_id=\${4}\" />
		<param name=\"movie\" value=\"http://embed.myvideo.ge/flv_player/external_player.swf\" />
		<param name=\"allowfullscreen\" value=\"true\" />
	</object>
	</p>
	<!-- Myvideo.Ge Plugin for Wordpress by Landish - http://landish.ge/plugins/myvideo/ -->";
	
	$content = preg_replace($video_url,$video_embed,$content);	
	return $content;
}



function pre_edit_myvideo($content)
{
	$video_url = "/((http:\/\/)(www\.|)myvideo.ge\/\?video_id=([0-9]+))(\D+\S+)/";
	$content = preg_replace($video_url,'${1}',$content);
	
	return $content;
}


function pre_save_myvideo($content)
{
	/* variable for filtering myvideo.ge URL */
	
	$video_url 	= "/((http:\/\/)(www\.|)myvideo.ge\/\?video_id=([0-9]+))/";
	$video_id	= preg_match_all($video_url,$content,$matches);
	
	for($i=0; $i<count($matches[4]); $i++){
		// get extension from myvideo
		$ext[$matches[4][$i]] = file_get_contents('http://webservice.myvideo.ge/ext/getExt.php?video_id='.$matches[4][$i]);
	}
	foreach((array) $ext as $key => $val){

		$reg		= "/video_id=".$key."/";
		$content	= preg_replace($reg,'video_id='.$key.'.'.$val,$content);	
	}
	return $content;
}



add_filter('the_content', 'myvideo');
add_filter('the_editor_content', 'pre_edit_myvideo');
add_filter('content_save_pre', 'pre_save_myvideo');
add_filter('plugin_action_links_'.plugin_basename(__FILE__).'', 'myvideo_options_links');

/* add "Myvideo Options" menu to wordpress adminpanel */
add_action('admin_init', 'myvideo_options_init' );
add_action('admin_menu', 'myvideo_options_add_page');


function myvideo_options_links($links) {
 
	$settings_link = '<a href="options-general.php?page=myvideo_options">' . __('Settings') . '</a>';
	array_unshift( $links, $settings_link );
 
	return $links;
}


function myvideo_options_init(){
	register_setting( 'myvideo_options', 'myvideo_option' );
}

function myvideo_options_add_page() {
	add_options_page('Myvideo.Ge Options', 'Myvideo.Ge Options', 'manage_options', 'myvideo_options', 'myvideo_options_do_page');
}

/* function to define embed video dimensions */
function myvideo_options_do_page() {
?>

<div class="wrap">
  <div id="icon-upload" class="icon32"></div>
  <h2>Myvideo.Ge Options</h2>
  <form method="post" action="options.php">
    <?php settings_fields('myvideo_options'); ?>
    <?php $options = get_option('myvideo_option'); ?>
    <table class="form-table">
      <tr>
        <th colspan="2"><strong>Recomended Dimensions: 452X380, 500X420, 100% width<br />
          Default: 452X380</strong></th>
      </tr>
      <tr valign="top">
        <th scope="row">Embed Width:</th>
        <td><input type="text" name="myvideo_option[width]" value="<?php if(empty($options['width'])) echo "452"; else echo $options['width']; ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">Embed Height:</th>
        <td><input type="text" name="myvideo_option[height]" value="<?php if(empty($options['height'])) echo "380"; else echo $options['height']; ?>" /></td>
      </tr>
       <tr valign="top">
        <th scope="row">Plugin Author URL:</th>
        <td> <a href="http://landish.ge/">http://landish.ge/</a></td>
      </tr>
    </table>
    <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
  </form>
</div>
<?php	
}

?>