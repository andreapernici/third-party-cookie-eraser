<?php 
/*
	Plugin Name: Third Party Cookie Eraser
	Plugin URI: http://andreapernici.com/wordpress/third-party-cookie-eraser/
	Description: The Cookie Law is one of the most stupid law in the world. Maybe made by someone, who doesn't really understand how the web works. This plugin is a drastic solution to lock all the third party contents inside posts and pages not possible using the editor or for website with lot's of authors. You can use the plugin in conjunction with any kind of plugin you prefer for the Cookie Consent. You only need to setup your cookie values.
	Version: 1.0.2
	Author: Andrea Pernici
	Author URI: http://www.andreapernici.com/
	
	Copyright 2013 Andrea Pernici (andreapernici@gmail.com)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	*/

define( 'THIRDPARTYCOOKIEERASER_VERSION', '1.0.2' );

$pluginurl = plugin_dir_url(__FILE__);
if ( preg_match( '/^https/', $pluginurl ) && !preg_match( '/^https/', get_bloginfo('url') ) )
	$pluginurl = preg_replace( '/^https/', 'http', $pluginurl );
define( 'THIRDPARTYCOOKIEERASER_FRONT_URL', $pluginurl );

define( 'THIRDPARTYCOOKIEERASER_URL', plugin_dir_url(__FILE__) );
define( 'THIRDPARTYCOOKIEERASER_PATH', plugin_dir_path(__FILE__) );
define( 'THIRDPARTYCOOKIEERASER_BASENAME', plugin_basename( __FILE__ ) );

if (!class_exists("AndreaThirdPartyCookieEraser")) {

	class AndreaThirdPartyCookieEraser {
		/**
		 * Class Constructor
		 */
		function AndreaThirdPartyCookieEraser(){
		
		}
		
		/**
		 * Enabled the AndreaThirdPartyCookieEraser plugin with registering all required hooks
		 */
		public static function Enable() {
			add_action('admin_menu', array("AndreaThirdPartyCookieEraser",'ThirdPartyCookieEraserMenu'));
			//add_action("wp_insert_post",array("AndreaFacebookSend","SetFacebookSendCode"));
			$options_cookie_name = get_option( 'third_party_cookie_eraser_cookie_name' );
			$options_cookie_value = str_replace("'","",get_option( 'third_party_cookie_eraser_cookie_value' ));
			$options_lang = get_option( 'third_party_cookie_eraser_lang' );
			
			
			if ($_COOKIE[$options_cookie_name] != $options_cookie_value) {
				add_filter("the_content", array("AndreaThirdPartyCookieEraser","AutoErase"));
				
				add_filter('widget_display_callback', function($instance, $widget, $args){
					    $fnFixArray = function($v) use (&$fnFixArray){
						if(is_array($v) or is_object($v)){
						    foreach($v as $k1=>&$v1){
							$v1 = $fnFixArray($v1);
						    }
						    return $v;
						}

						if(!is_string($v) or empty($v)) return $v;
						
						$valore = '<div style="padding:10px;margin-bottom: 18px;color: #b94a48;background-color: #f2dede;border: 1px solid #eed3d7; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;">'.stripslashes(get_option( 'third_party_cookie_eraser_lang' )).'</div>';
						
						return preg_replace('#<iframe.*?\/iframe>|<embed.*?>|<script.*?\/script>#is', $valore , $v);  
					    };
					    return $fnFixArray($instance);
					}, 11, 3); 
			}
			
			
		}
		
		public static function AutoErase($content) {
			$options_lang = stripslashes(get_option( 'third_party_cookie_eraser_lang' ));
			$valore = '<div style="padding:10px;margin-bottom: 18px;color: #b94a48;background-color: #f2dede;border: 1px solid #eed3d7; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;">'.$options_lang.'</div>';
			
			return preg_replace('#<iframe.*?\/iframe>|<embed.*?>|<script.*?\/script>#is', $valore , $content);
			
			
		}
		
		public static function WidgetErase(){
			// Hook into 'widget_display_callback' filter
			// It allows altering a Widget properties right before it outputs in sidebar
			add_filter('widget_display_callback', function($instance, $widget, $args){
			    // Recursive functions that applies replacements in all string elements of $instance
			    $fnFixArray = function($v) use (&$fnFixArray){
				// Dig deeper if this is an array or object
				if(is_array($v) or is_object($v)){
				    // Use pointer here for property to satisfy both array/object in one
				    // Otherwise for arrays we need $v[$k1] and $v->{$k1} for objects
				    foreach($v as $k1=>&$v1){
					// Go recursive on elements / properties
					$v1 = $fnFixArray(v1);
				    }
				    return $v;
				}
				// Don't alter non-strings or empty ones
				if(!is_string($v) or empty($v)) return $v;
				// We found a string, replace stuff in it and return the altered value
				preg_replace('#<iframe.*?\/iframe>|<embed.*?>|<script.*?\/script>#is', $valore , $v);  
			    };
			    return $fnFixArray($instance);
			}, 11, 3); // We need 3 arguments and a below normal priority
		}
		
		
		function SetEraseAdminConfiguration() {
			add_action('admin_menu', array("AndreaThirdPartyCookieEraser",'ThirdPartyCookieEraserMenu'));
			return true;
		}
		
		public static function ThirdPartyCookieEraserMenu() {
			add_options_page('Third Party Cookie Eraser Options', 'Third Party Cookie Eraser', 'manage_options', 'third-party-cookie-eraser', array("AndreaThirdPartyCookieEraser",'ThirdPartyCookieEraserOptions'));
		}
		
		public static function ThirdPartyCookieEraserOptions() {
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
		    // variables for the field and option names 
		    $third_party_cookie_eraser_cookie_name = 'third_party_cookie_eraser_cookie_name';
		     $third_party_cookie_eraser_cookie_value = 'third_party_cookie_eraser_cookie_value';
		    $third_party_cookie_eraser_lang = 'third_party_cookie_eraser_lang';
		    
		    $hidden_field_name = 'mt_submit_hidden';
		
		    // Read in existing option value from database
		    $opt_val_eraser_cookie_name = get_option( $third_party_cookie_eraser_cookie_name );
		    $opt_val_eraser_cookie_value = get_option( $third_party_cookie_eraser_cookie_value );
		    $opt_val_eraser_lang = get_option( $third_party_cookie_eraser_lang );
		    
		    // See if the user has posted us some information
		    // If they did, this hidden field will be set to 'Y'
		    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
		        // Read their posted value
		    	$opt_val_eraser_cookie_name = $_POST[ $third_party_cookie_eraser_cookie_name ];
			$opt_val_eraser_cookie_value = $_POST[ $third_party_cookie_eraser_cookie_value ];
		    	$opt_val_eraser_lang = $_POST[ $third_party_cookie_eraser_lang ];
		
		        // Save the posted value in the database
		        update_option( $third_party_cookie_eraser_cookie_name, $opt_val_eraser_cookie_name );
			update_option( $third_party_cookie_eraser_cookie_value, $opt_val_eraser_cookie_value );
		        update_option( $third_party_cookie_eraser_lang, $opt_val_eraser_lang );
		
		        // Put an settings updated message on the screen
		
		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'menu-third-party-cookie-eraser' ); ?></strong></p></div>
		<?php
		
		    }
		    // Now display the settings editing screen
		    echo '<div class="wrap">';
		    // header
		    echo "<h2>" . __( 'Third Party Cookie Eraser Options', 'menu-third-party-cookie-eraser' ) . "</h2>";
		    // settings form
		    
		    ?>
		<table class="form-table">
		<tbody>
			<form name="form1" method="post" action="">
			<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
				<tr>
				<?php $options_cookie_name = get_option( 'third_party_cookie_eraser_cookie_name' ); ?>
					<th scope="row"><label for="third_party_cookie_eraser_cookie_name"><?php _e("Cookie Name:", 'menu-third-party-cookie-eraser' ); ?> </label></th>
					<td><input type="text" name="third_party_cookie_eraser_cookie_name" value="<?php echo $options_cookie_name; ?>"  class="regular-text" /> </p>
					<p class="description" id="tagline-third_party_cookie_eraser_cookie_name">(put the cookie name - IE: viewed_cookie_policy)</p></td>
				</tr>
				<tr>
					<?php $options_cookie_value = get_option( 'third_party_cookie_eraser_cookie_value' ); ?>
					<th scope="row"><label for="third_party_cookie_eraser_cookie_value"><?php _e("Cookie Consent Value:", 'menu-third-party-cookie-eraser' ); ?> </label></th>
					<td><input type="text" name="third_party_cookie_eraser_cookie_value" value="<?php echo $options_cookie_value; ?>"  class="regular-text" />
					<p class="description" id="tagline-third_party_cookie_eraser_cookie_value">(put the cookie value - IE: yes)</p></td>
				</tr>
				<tr>
					<?php $options_lang = get_option( 'third_party_cookie_eraser_lang' ); ?>
					<th scope="row"><label for="third_party_cookie_eraser_lang"><?php _e("Your message to show:", 'menu-third-party-cookie-eraser' ); ?> </label></th>
					<td><input type="text" name="third_party_cookie_eraser_lang" value="<?php echo $options_lang; ?>" class="regular-text" /></td>
				</tr>
				<p class="submit">
					<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
				</p>
			</form>
		</tbody>
		</table>
		<?php echo "<h2>" . __( 'Magari essendo oltre 1000 ci confronteremo anche sulla Cookie Law ;)', 'menu-third-party-cookie-eraser' ) . "</h2>"; ?>
		<p><a href="https://www.webmarketingfestival.it/?utm_source=CookieLawPlugin&utm_medium=Banner300x200&utm_campaign=CookieEraser" target="_blank"><img src="https://www.webmarketingfestival.it/images/kit-stampa/300x250.gif" alt="Web Marketing Festival"></p>
		
		<?php

		}
		
	}
}


/*
 * Plugin activation
 */
 
if (class_exists("AndreaThirdPartyCookieEraser")) {
	$anfs = new AndreaThirdPartyCookieEraser();
}


if (isset($anfs)) {
	add_action("init",array("AndreaThirdPartyCookieEraser","Enable"),1000,0);
}

if (!function_exists('andrea_third_party_cookie_eraser')) {
	function andrea_third_party_cookie_eraser() {
		$third_party_cookie_eraser = new AndreaThirdPartyCookieEraser();
		return $third_party_cookie_eraser->AutoErase();
	}	
}

?>