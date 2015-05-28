<?php
/**
 * Plugin Name: Third Party Cookie Eraser
 * Plugin URI: http://andreapernici.com/wordpress/third-party-cookie-eraser/
 * Description: The Cookie Law is one of the most stupid law in the world. Maybe made by someone, who doesn't really understand how the web works. This plugin is a drastic solution to lock all the third party contents inside posts and pages not possible using the editor or for website with lot's of authors. You can use the plugin in conjunction with any kind of plugin you prefer for the Cookie Consent. You only need to setup your cookie values.
 * Version: 1.1.0
 * Author: Andrea Pernici
 * Author URI: http://www.andreapernici.com/
 * Text Domain: third-party-cookie-eraser
 * License: GPLv2 or later
 *
 * @package Third Party Cookie Eraser
 * @since 1.0.0
 *
 * Copyright 2013 Andrea Pernici (andreapernici@gmail.com)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * This will make shure the plugin files can't be accessed within the web browser directly.
 */
if ( !defined( 'WPINC' ) )
    die;

/**
 * Define some costant for internal use
 */

if ( !defined( 'THIRDPARTYCOOKIEERASER_VERSION' ) )
	define( 'THIRDPARTYCOOKIEERASER_VERSION', '1.0.2' );

if ( !defined( 'THIRDPARTYCOOKIEERASER_PLUGIN' ) )
    define('THIRDPARTYCOOKIEERASER_PLUGIN', true);

/**
 * Example = F:\xampp\htdocs\italystrap\wp-content\plugins\third-party-cookie-eraser\third-party-cookie-eraser.php
 */
if ( !defined( 'THIRDPARTYCOOKIEERASER_FILE' ) )
    define('THIRDPARTYCOOKIEERASER_FILE', __FILE__ );

/**
 * Example = F:\xampp\htdocs\italystrap\wp-content\plugins\third-party-cookie-eraser/
 */
if ( !defined( 'THIRDPARTYCOOKIEERASER_PLUGIN_PATH' ) )
    define('THIRDPARTYCOOKIEERASER_PLUGIN_PATH', plugin_dir_path( THIRDPARTYCOOKIEERASER_FILE ));
/**
 * Example = third-party-cookie-eraser/third-party-cookie-eraser.php
 */
if ( !defined( 'THIRDPARTYCOOKIEERASER_BASENAME' ) )
    define('THIRDPARTYCOOKIEERASER_BASENAME', plugin_basename( THIRDPARTYCOOKIEERASER_FILE ));


/**
 * 
 */
if ( !class_exists( 'AndreaThirdPartyCookieEraser' ) ){

    class AndreaThirdPartyCookieEraser{

        /**
         * Definition of variables containing the configuration
         * to be applied to the various function calls wordpress
         */
        protected $capability = 'manage_options';

        /**
         * Global variables and default values
         * @var array
         */
        protected $default_options = array();

        /**
         * Option
         * @var array
         */
        private $options = array();

        private $options_cookie_name = '';

        private $options_cookie_value = '';

        private $options_lang = '';

        private $pattern = '#<iframe.*?\/iframe>|<embed.*?>|<script.*?\/script>#is';

        private $valore = '';


        /**
         * [__construct description]
         */
        public function __construct(){

            /**
             * Add Admin menù page
             */
            add_action( 'admin_menu', array( $this, 'addMenuPage') );

            /**
             * 
             */
            add_action( 'admin_init', array( $this, 'andrea_settings_init') );

            /**
             * Only for debug
             */
            // var_dump($_COOKIE);
            // var_dump($_COOKIE[ $this->options_cookie_name ]);
            // var_dump(headers_list());
            // if ( !isset( $_COOKIE[ $this->options_cookie_name ] ) && $_COOKIE[ $this->options_cookie_name ] !== $this->options_cookie_value ){
            // 
            $this->options = get_option( 'third-party-cookie-eraser' );

            if ( !isset( $_COOKIE[ $this->options['cookie_name'] ] ) ){


                /**
                 * Replacement for regex
                 * @var string
                 */
                $this->valore = '<div class="el"><div style="padding:10px;margin-bottom: 18px;color: #b94a48;background-color: #f2dede;border: 1px solid #eed3d7; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;">' . esc_textarea( $this->options['text'] ) . '<button onclick="myFunction()">Try it</button></div><!-- $0 --></div>';

            	add_filter( 'the_content', array( $this, 'AutoErase' ), 11);

            	add_filter('widget_display_callback', function($instance, $widget, $args){
            		$fnFixArray = function($v) use (&$fnFixArray){
            			if(is_array($v) or is_object($v)){
            				foreach($v as $k1=>&$v1){
            					$v1 = $fnFixArray($v1);
            				}
            				return $v;
            			}

            			if(!is_string($v) or empty($v)) return $v;

            			// $valore = '<div class="el" id="prova"><div class="alert" style="padding:10px;margin-bottom: 18px;color: #b94a48;background-color: #f2dede;border: 1px solid #eed3d7; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;">' . esc_textarea( $this->options['text'] ) . '</div><!-- $0 --></div>';

            			return preg_replace( $this->pattern, $this->valore , $v);

            		};

            		return $fnFixArray($instance);

            	}, 11, 3);

                add_action( 'wp_footer', array( $this, 'printJS' ), 999 );

            }
        }

        /**
         * Add page for third-party-cookie-eraser admin page
         */
        public function addMenuPage(){

            add_options_page(
                __('Third Party Cookie Eraser Options', 'third-party-cookie-eraser'),
                'Third Party Cookie Eraser',
                $this->capability,
                'third-party-cookie-eraser',
                array( $this, 'dashboard')
                );

        }

        /**
         *  The dashboard callback
         */
        public function dashboard(){

            if ( !current_user_can( $this->capability ) )
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

                ?>
                <div class="wrap">

                        <?php settings_errors(); ?>

                    <form action='options.php' method='post'>
                        
                        <?php
                        settings_fields( 'andrea_options_group' );
                        do_settings_sections( 'andrea_options_group' );
                        submit_button();
                        ?>
                        
                    </form>
                    <?php echo "<h2>" . __( 'Magari essendo oltre 1000 ci confronteremo anche sulla Cookie Law ;)', 'menu-third-party-cookie-eraser' ) . "</h2>"; ?>
					<p><a href="https://www.webmarketingfestival.it/?utm_source=CookieLawPlugin&utm_medium=Banner300x200&utm_campaign=CookieEraser" target="_blank"><img src="https://www.webmarketingfestival.it/images/kit-stampa/300x250.gif" alt="Web Marketing Festival"></p>
                </div>
                <?php

        }

        /**
         * [andrea_settings_init description]
         * @return [type] [description]
         */
        public function andrea_settings_init() {

			$this->options_cookie_name = get_option( 'third_party_cookie_eraser_cookie_name' );
			$this->options_cookie_value = str_replace("'","",get_option( 'third_party_cookie_eraser_cookie_value' ));
			$this->options_lang = get_option( 'third_party_cookie_eraser_lang' );


            /**
            * Load Plugin Textdomain
            */
            // load_plugin_textdomain('third-party-cookie-eraser', false, THIRDPARTYCOOKIEERASER_PLUGIN_PATH . 'lang/' );
            load_plugin_textdomain('third-party-cookie-eraser', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

            /**
             * Create default options
             * Migrazione dalle vecchie options al nuovo array per mantenere il codice
             * più pulito e performante
             * Quando tutti avranno aggiornato alla nuova versione si può togliere
             * Eventualmente creare la funzione per eliminare le vecchie opzioni
             *
             * Dopo l'aggiornamento basta navigari in almeno una pagina dell'admin per
             * migrare le vecchie opzioni nelle nuove altrimenti i campi rimangono vuoti
             *
             * Questo hook upgrader_process_complete non so se funziona, credo sia per
             * l'autoupdate
             * 
             * @var array
             */
            if ( $this->options_cookie_name ) {

            	$this->default_options = array(

                'cookie_name'	=> $this->options_cookie_name,
                'cookie_value'	=> $this->options_cookie_value,
                'text'			=> $this->options_lang

                );

            } else {

				$this->default_options = array(

                'cookie_name'	=> '',
                'cookie_value'	=> '',
                'text'			=> ''

                );
            }
            
            // $this->default_options = array(

            //     'cookie_name'	=> '',
            //     'cookie_value'	=> '',
            //     'text'			=> ''

            //     );

            /**
             * [$this->options description]
             * @var [type]
             */
            $this->options = get_option( 'third-party-cookie-eraser' );

            // var_dump($this->options);

            /**
             * If the theme options don't exist, create them.
             */
            if( false === $this->options )
                add_option( 'third-party-cookie-eraser', $this->default_options );

            /**
             * 
             */
            add_settings_section(
                'setting_section', 
                __( 'Third Party Cookie Eraser Options', 'third-party-cookie-eraser' ), 
                array( $this, 'andrea_settings_section_callback'), 
                'andrea_options_group'
            );

            /**
             * Checkbox for activation
             */
            add_settings_field( 
                'cookie_name', 
                __( 'Cookie name:', 'third-party-cookie-eraser' ), 
                array( $this, 'andrea_option_cookie_name'), 
                'andrea_options_group', 
                'setting_section'
                );

            /**
             * How to display cookie_value
             * Default Bar
             */
            add_settings_field( 
                'cookie_value', 
                __( 'Cookie Consent Value:', 'third-party-cookie-eraser' ), 
                array( $this, 'andrea_option_cookie_value'), 
                'andrea_options_group', 
                'setting_section'
                );

            /**
             * 
             */
            add_settings_field( 
                'text', 
                __( 'Your message to show:', 'third-party-cookie-eraser' ), 
                array( $this, 'andrea_option_text'), 
                'andrea_options_group', 
                'setting_section'
                );

            /**
             * 
             */
            register_setting(
                'andrea_options_group',
                'third-party-cookie-eraser',
                array( $this, 'sanitize_callback')
                );


        }


        /**
         * [andrea_settings_section_callback description]
         * @return [type] [description]
         */
        public function andrea_settings_section_callback() { 

            _e( 'Insert your settings', 'third-party-cookie-eraser' );

        }

        /**
         * Snippet for checkbox
         * @return strimg       Activate cookie_value in front-end Default doesn't display
         */
        public function andrea_option_cookie_name($args) {

            $cookie_name = ( isset( $this->options['cookie_name'] ) ) ? $this->options['cookie_name'] : '' ;

        ?>

            <input type="text" id="third-party-cookie-eraser[cookie_name]" name="third-party-cookie-eraser[cookie_name]" value="<?php echo esc_attr( $this->options['cookie_name'] ); ?>" placeholder="<?php _e( 'Your cookie name', 'third-party-cookie-eraser' ) ?>" size="70" />
			<br>
            <label for="third-party-cookie-eraser[cookie_name]">
                <?php _e( '(put the cookie name - IE: viewed_cookie_policy)', 'third-party-cookie-eraser' ); ?>
            </label>

        <?php

        }

        /**
         * Choose how to display cookie_value in page
         * @return string       Display input and labels in plugin options page
         */
        public function andrea_option_cookie_value($args) {

        ?>

			<input type="text" id="third-party-cookie-eraser[cookie_value]" name="third-party-cookie-eraser[cookie_value]" value="<?php echo esc_attr( $this->options['cookie_value'] ); ?>" placeholder="<?php _e( 'Your cookie name', 'third-party-cookie-eraser' ) ?>" size="70" />
			<br>
            <label for="third-party-cookie-eraser[cookie_value]">
                <?php _e( '(put the cookie value - IE: yes)', 'third-party-cookie-eraser' ); ?>
            </label>

        <?php

        }

        /**
         * Textarea for the message to display
         * @return string
         */
        public function andrea_option_text($args) {

        ?>

            <textarea rows="5" cols="70" name="third-party-cookie-eraser[text]" id="third-party-cookie-eraser[text]" placeholder="<?php _e( 'Your short cookie policy', 'third-party-cookie-eraser' ) ?>" ><?php echo esc_textarea( $this->options['text'] ); ?></textarea>
            <br>
            <label for="third-party-cookie-eraser[text]">
                <?php echo __( 'People will see this notice if they don\'t accept Cookie Notice', 'third-party-cookie-eraser' ); ?>
            </label>

        <?php

        }

        /**
         * Sanitize data
         * @param  array $input Data to sanitize
         * @return array        Data sanitized
         */
        public function sanitize_callback( $input ){

            $new_input = array();

            if( isset( $input['cookie_name'] ) )
                $new_input['cookie_name'] =  sanitize_text_field( $input['cookie_name'] );

            if( isset( $input['cookie_value'] ) )
                $new_input['cookie_value'] =  sanitize_text_field( $input['cookie_value'] );

            if( isset( $input['text'] ) )
                $new_input['text'] = sanitize_text_field( $input['text'] );

            return $new_input;

        }

        /**
         * Erase third part embed
         * @param string $content Article content
         */
		public function AutoErase( $content ) {

			// $valore = '<div class="el" id="prova"><div class="alert" style="padding:10px;margin-bottom: 18px;color: #b94a48;background-color: #f2dede;border: 1px solid #eed3d7; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;">' . esc_textarea( $this->options['text'] ) . '</div><!-- $0 --></div>';

            // preg_match_all( $this->pattern, $content, $matches );

            // var_dump($matches[0]);

            // $i = 0;
            // foreach ( $matches[0] as $value ){

            //     $commento .= '<!--' . $value . '-->';

            // }

            // $nuovo_contenuto = preg_replace( $this->pattern, $this->valore , $content, -1 , $count);

            // var_dump($count);

            // $nuovo_contenuto = $nuovo_contenuto . $commento;

/*                      return preg_replace('#<iframe.*?\/iframe>|<embed.*?>|<script.*?\/script>#is', $valore , $content);
*/
            // return $nuovo_contenuto;


			$content = preg_replace( $this->pattern, $this->valore , $content);

			
			return $content;
		}

		public function WidgetErase(){


		}

        public function printJS(){

            $js = '<script>
                function myFunction() {
                    var x=document.getElementsByClassName("el");

                    var i;
                    for (i = 0; i < x.length; i++) {

                        x[i].removeChild(x[i].childNodes[0]);

                        var str = x[i].innerHTML;
                        var res = str.replace(/<!--(.*?)-->/g, "$1");
                        x[i].innerHTML = res;

                        cookieName="displayCookieConsent";var expiryDate=new Date();expiryDate.setFullYear(expiryDate.getFullYear()+1);document.cookie=cookieName+"=y; expires="+expiryDate.toGMTString()+"; path=/";

                    }
                }
            </script>';

            echo $js;

        }

    }// class
}//endif

new AndreaThirdPartyCookieEraser;