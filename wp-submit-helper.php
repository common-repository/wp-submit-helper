<?php
/*
Plugin Name: WP Submit Helper
Plugin URI: http://jlafuentec.ivore.com/wordpress-submit-helper-plugin/
Description: WP Submit Helper
Version: 0.0.1
Author: Jonatan Lafuente Castillo
Author URI: http://jlafuentec.ivore.com/

== Installation ==
1. Upload the 'wp-submit-helper.php' file to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Under Settings, click 'Submit Helper' and modify your settings accordingly.
4. That's it!


Copyright 2009. UOC - Jonatan Lafuente Castillo (email: jlafuentec@uoc.edu) http://www.uoc.edu/

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define (NAME, "WP Submit Helper");
define (VERSION, "v0.0.1");

function wpsh_post($postID) {
/*
	if (!class_exists ("Curl"))
	{
		require_once ('Curl.class.php');
	}
*/	

	$pligg_url = 'http://pligg.29tian.com/';
	$pligg_username = 'jlc';
	$pligg_password = 'pligg000';

	$pligg_url = get_option('wpsh_pligg_url');
	$pligg_username = get_option('wpsh_pligg_username');
	$pligg_password = get_option('wpsh_pligg_password');
	$pligg_cat_id = get_option('wpsh_cat_id');

	$simpy_url = 'http://www.simpy.com/simpy/api/rest/SaveLink.do';
	$simpy_username = get_option('wpsh_simpy_username');
	$simpy_password = get_option('wpsh_simpy_password');

	query_posts("p=".$postID);
	if(have_posts()) {
		the_post();
		$permalink=apply_filters('the_permalink', get_permalink());
		//$title=get_the_title();
		$tags = '';
		
		$post = wp_get_single_post ($postID);
		$text = strip_tags ($post->post_content);
		$cat = strip_tags ($post->post_category);
		$title = strip_tags ($post->post_title);
		$excerpt = strip_tags ($post->post_excerpt);
		$posttags = get_the_tags();
		if ($posttags) {
			foreach($posttags as $tag) {
				if ($tags <> '') { //no es el primer tag
					$tags.=',';
				}
				$tags.=trim($tag->name);
			}
		}
//		if (($excerpt == '') || strlen($excerpt) < 50)
		if (strlen($excerpt) < 50) {
			$excerpt.="\n\n";
			$excerpt.= neat_trim($text, 50); //anyadimos unas cuantas lineas del content si el excerpt no existe o es demasiado corto
		}
		
		if (get_option('wpsh_pligg')) {
			$url1 = $pligg_url."/3rdparty/API/api.php?fn=post";
			$url1.= "&username=$pligg_username";
			$url1.= "&password=$pligg_password";
			$url1.= "&category=$pligg_cat_id";
			$url1.= "&url=".urlencode($permalink);
			$url1.= "&title=".urlencode($title);
			$url1.= "&content=".urlencode($excerpt);
			$url1.= "&tags=".urlencode($tags);
			
			$result = file_get_contents($url1);
/*			
			if ($fp = fopen(dirname(__FILE__)."/logs/test0.log.".date("YmdHis", time())."a.txt", "w"))
			{
				fwrite($fp, "$url1 \n\n $result");
				fclose($fp);
			}
*/			
		}
		
		if (get_option('wpsh_simpy')) {
//		  $permalink ="http://www.lalalaoeoe.com/asdf.html";    //testing
			$url2 = "$simpy_url";
			$url2.= "?title=".urlencode($title);
			$url2.= "&href=".urlencode($permalink);
			$url2.= "&accessType=1";
			$url2.= "&tags=".urlencode($tags);
//	    $url = "http://www.simpy.com/simpy/api/rest/HTTPLogin.do?_doneURI=%2Fsimpy%2Fapi%2Frest%2FSaveLink.do%3Ftitle%3DExample%26href%3Dhttp%3A%2F%2Ffoo.com%26accessType%3D1%26tags%3Dfoo%2Csite";
			$cookiefile = tempnam("tmp","wpsh");
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)');
			curl_setopt($ch, CURLOPT_USERPWD, "$simpy_username:$simpy_password");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_URL, $url2);
			$result = curl_exec($ch); //login y redireccion
			
			$curl_error = curl_error($ch);
			if ($curl_error <> '') {
				//TODO: tratar el error
			}
			curl_close($ch);
/*			
			if ($fp = fopen(dirname(__FILE__)."/logs/test1.log.".date("YmdHis", time())."a.txt", "w"))
			{
				fwrite($fp, "$url1 \n\n $result");
				fclose($fp);
			}
*/			
		}
		
/*
		if ($fp = fopen(dirname(__FILE__)."/logs/test2.log.".date("YmdHis", time())."a.txt", "w"))
		{
			fwrite($fp, "Title: $title \nTags: $tags \nEx: $excerpt \nPerm: $permalink \n\nURL: $url1   \n\nURL: $url2  ");
			fclose($fp);
		}
*/		
	//http://www.simpy.com/simpy/api/rest/SaveLink.do?title=hola+prueba+delete&href=http%3A%2F%2Flocalhost%2Fblogtest%2F%3Fp%3D44&accessType=1&tags=
	}
}



function neat_trim($str, $n, $delim='...') {
   $len = strlen($str);
   if ($len > $n) {
       preg_match('/(.{' . $n . '}.*?)\b/', $str, $matches);
       return rtrim($matches[1]) . $delim;
   }
   else {
       return $str;
   }
}



//////////

function wpsh_admin_menu() {  
	add_options_page('WP Submit Helper Options', 'Submit Helper', 8, __FILE__, 'wpsh_admin');
}

function wpsh_admin() {
	if($_POST['wpsh_update'] == 'Y') {
		//Form data sent
		$wpsh_pligg = $_POST['wpsh_pligg'];
		update_option('wpsh_pligg', $wpsh_pligg);
		update_option('wpsh_pligg_url', $_POST['wpsh_pligg_url']);
		update_option('wpsh_pligg_username', $_POST['wpsh_pligg_username']);
		update_option('wpsh_pligg_password', $_POST['wpsh_pligg_password']);
		update_option('wpsh_cat_id', $_POST['wpsh_cat_id']);
		
		$wpsh_simpy = $_POST['wpsh_simpy'];
		update_option('wpsh_simpy', $wpsh_simpy);
		update_option('wpsh_simpy_username', $_POST['wpsh_simpy_username']);
		update_option('wpsh_simpy_password', $_POST['wpsh_simpy_password']);
		
		
	?>  
    	<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
	<?php  
	} else {
		//Normal page display
		$nofollow_login = get_option('wpsh_nofollow_login');
		
		if (empty($nofollow_login)) 	$nofollow_login = "unchecked";
	}
?>
	
	<div class="wrap">
		<h2><?php _e('WP Submit Helper') ?></h2>
			
		<form name="wpsh_admin_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<input type="hidden" name="wpsh_update" value="Y">
			<table>
					<tr>
						<td colspan="2">
							<h3>Pligg</h3>
						</td>
					</tr>
					<tr>
						<td><input name="wpsh_pligg" type="checkbox" value="yes" <?php checked('yes', get_option('wpsh_pligg')); ?> /></td>
						<td><?php _e("Submit to Pligg?" ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>Pligg URL</td>
						<td><input type="text" name="wpsh_pligg_url" id="wpsh_pligg_url" size="20" value="<?php echo get_option(wpsh_pligg_url); ?>" class="regular-text" /></td>		
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>Pligg username</td>
						<td><input type="text" name="wpsh_pligg_username" id="wpsh_pligg_username" size="20" value="<?php echo get_option(wpsh_pligg_username); ?>" class="regular-text" /></td>		
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>Pligg password</td>
						<td><input type="text" name="wpsh_pligg_password" id="wpsh_pligg_password" size="20" value="<?php echo get_option(wpsh_pligg_password); ?>" class="regular-text" /></td>		
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>Pligg category ID</td>
						<td><input type="text" name="wpsh_cat_id" id="wpsh_cat_id" size="5" value="<?php echo get_option(wpsh_cat_id); ?>" class="regular-text" /></td>		
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2">
							<h3>Simpy</h3>
						</td>
					</tr>
					<tr>
						<td><input name="wpsh_simpy" type="checkbox" value="yes" <?php checked('yes', get_option('wpsh_simpy')); ?> /></td>
						<td><?php _e("Submit to Simpy?" ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>Simpy username</td>
						<td><input type="text" name="wpsh_simpy_username" id="wpsh_simpy_username" size="20" value="<?php echo get_option(wpsh_simpy_username); ?>" class="regular-text" /></td>		
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
            <td>Simpy password</td>
						<td><input type="text" name="wpsh_simpy_password" id="wpsh_simpy_password" size="20" value="<?php echo get_option(wpsh_simpy_password); ?>" class="regular-text" /></td>		
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /></p></td>
					</tr>
			</table>
		</form>
	</div>
<?php
}

///////////////

function wpsh_install() {
	if(get_option('wpsh_installed') <> true){
		update_option('wpsh_installed', true);
	}
	//valores iniciales
	add_option('wpsh_pligg', 0);
	add_option('wpsh_pligg_url', '');
	add_option('wpsh_pligg_username', '');
	add_option('wpsh_pligg_password', '');
	add_option('wpsh_cat_id', 1);

	add_option('wpsh_simpy', 0);
	add_option('wpsh_simpy_username', '');
	add_option('wpsh_simpy_password', '');
}


if (isset($_GET['activate']) && $_GET['activate'] == 'true'){
	add_action('init', 'wpsh_install');
}

if (isset($_GET['deactivate']) && $_GET['deactivate'] == 'true'){
//	set_option('wpsh_installed', false);
}

//anyadimos las acciones
add_action('publish_post', 'wpsh_post');
add_action('admin_menu', 'wpsh_admin_menu');
?>