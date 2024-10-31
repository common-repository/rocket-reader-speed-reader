<?php
$rr_version      = '1.6.2';
$rr_release_date = '01/28/2017';
$rr_use_popup    = 'N';
/**
 * @package Rocket Reader
 * @version 1.6.2
 */
/*
Plugin Name: Rocket Reader
Plugin URI: http://cagewebdev.com/rocket-reader/
Description: Adds a control to read the text of posts and pages using a speed reading technique
Author: CAGE Web Design | Rolf van Gelder, Eindhoven, The Netherlands
Version: 1.6.2
Author URI: http://cagewebdev.com
*/
?>
<?php
/***********************************************************************************
 *
 * 	ROCKET READER - MAIN CLASS
 *
 ***********************************************************************************/
 
// CREATE INSTANCE
global $rr_class;
$rr_class = new RocketReader; 
 
class RocketReader {
	var $rr_version      = '1.6.2';
	var $rr_release_date = '01/28/2017';
	
	/*******************************************************************************
	 * 	CONSTRUCTOR
	 *******************************************************************************/
	function __construct() {
		// INITIALIZE PLUGIN
		add_action('init', array(&$this, 'rr_init'));

		// USE THE NON-MINIFIED VERSION OF JS AND CSS WHILE DEBUGGING
		$this->script_minified = (defined('WP_DEBUG') && WP_DEBUG) ? '' : '.min';
		
		// GET OPTIONS FROM DB (JSON FORMAT)
		$this->rr_options = get_option('rr_options');

		// FIRST RUN: SET DEFAULT SETTINGS
		$this->rr_init_settings();
	
		// BASE NAME OF THE PLUGIN
		$this->plugin_basename = plugin_basename(__FILE__);
		$this->plugin_basename = substr($this->plugin_basename, 0, strpos( $this->plugin_basename, '/'));
		
		// LOCALIZATION
		add_action('init', array(&$this, 'rr_i18n'));						
	} // __construct()


	/*******************************************************************************
	 * 	INITIALIZE PLUGIN
	 *******************************************************************************/	
	function rr_init()
	{
		if($this->rr_is_frontend_page()) {
			// FRONTEND PAGE AND USER IS LOGGED IN
			add_action('wp_footer', array(&$this, 'rr_javascript_vars'));
			add_action('wp_footer', array(&$this, 'rr_fe_scripts'));
			// ADD FRONTEND STYLE SHEET
			add_action('wp_footer', array(&$this, 'rr_fe_styles'));			
			// THE METHOD IS LOCATED IN THIS INSTANCE
			add_filter('the_content', array($this, 'rr_add_control'));
		} else if (is_user_logged_in()) {
			// BACKEND PAGE
			// ADD BACKEND STYLE SHEET
			add_action('admin_init', array(&$this, 'rr_be_styles'));		
			add_action('admin_menu', array(&$this, 'rr_admin_menu'));
			add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(&$this, 'rr_settings_link'));
		} // if($this->rr_is_frontend_page())
	} // rr_init()


	/*******************************************************************************
	 * 	INITIALIZE SETTINGS (FIRST TIME)
	 *******************************************************************************/
	function rr_init_settings() {	
		if ($this->rr_options === false) {
			// NO SETTINGS YET: SET DEFAULTS
			$this->rr_options['rr_wpm']              = '300';
			$this->rr_options['rr_minimum_words']    = '0';				
			$this->rr_options['rr_use_popup']        = 'N';
			$this->rr_options['rr_cont_bgcolor']     = '#E2E2E2';				
			$this->rr_options['rr_cont_bordercolor'] = '#333';
			$this->rr_options['rr_textcolor']        = '#000000';
			$this->rr_options['rr_bgcolor']          = '#EFEFEF';
			$this->rr_options['rr_bordercolor']      = '#000000';
			$this->rr_options['rr_fpc']              = '#FF0000';					
			// SAVE OPTIONS ARRAY
			update_option('rr_options', $this->rr_options);
		} // if ( false === $this->rr_options )
	} // rr_init_settings()


	/*******************************************************************************
	 * 	LOAD SETTINGS PAGE
	 *******************************************************************************/
	function rr_settings() {
		// INITIALIZE SETTINGS (FIRST RUN)
		include_once(trailingslashit(dirname( __FILE__ )).'/admin/settings.php');
	} // fab_settings()
	

	/*******************************************************************************
	 * 	DEFINE TEXT DOMAIN (FOR LOCALIZATION)
	 *******************************************************************************/	
	function rr_i18n() {
		load_plugin_textdomain('rocket-reader-speed-reader', false, dirname(plugin_basename( __FILE__ )).'/languages/');
	} // rr_i18n()


	/*******************************************************************************
	 * 	LOAD FRONTEND STYLESHEET
	 *******************************************************************************/
	function rr_fe_styles() {	
		wp_register_style('rr-fe-style', plugins_url('css/rr_rocket_reader_fe'.$this->script_minified.'.css', __FILE__), array(), $this->rr_version);
		wp_enqueue_style('rr-fe-style');
	} // rr_fe_styles()	


	/*******************************************************************************
	 * 	LOAD BACKEND STYLESHEET
	 *******************************************************************************/
	function rr_be_styles() {	
		wp_register_style('rr-be-style', plugins_url('css/rr_rocket_reader_be'.$this->script_minified.'.css', __FILE__),array() ,$this->rr_version);
		wp_enqueue_style('rr-be-style');
	} // rr_be_styles()	


	/*******************************************************************************
	 * 	LOAD FRONTEND JAVASCRIPT
	 *******************************************************************************/
	function rr_fe_scripts() {
		// true: in footer
		wp_register_script('rr-frontend', plugins_url('js/rr_rocket_reader'.$this->script_minified.'.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'), $this->rr_version, true);
		wp_enqueue_script('rr-frontend');
	} // rr_fe_scripts()
	
	
	/*******************************************************************************
	 * 	PASS OPTIONS TO JAVASCRIPT
	 *******************************************************************************/
	function rr_javascript_vars() {
		echo '
<!-- START Rocket Reader v' . $this->rr_version . ' [' . $this->rr_release_date . '] | http://cagewebdev.com/rocket-reader | CAGE Web Design | Rolf van Gelder -->
<style>
.dlg-no-close .ui-dialog-titlebar-close {
	display: none;
}
.dlg-no-title .ui-dialog-titlebar {
	display: none;
}
</style>
<script type="text/javascript">
var rr_init_version          = "'.$this->rr_version.'";
var rr_init_WPM              = '.$this->rr_options['rr_wpm'].';
var rr_init_use_popup        = "'.$this->rr_options['rr_use_popup'].'";
// CONTAINER
var rr_init_cont_bgcolor     = "'.$this->rr_options['rr_cont_bgcolor'].'";
var rr_init_cont_bordercolor = "'.$this->rr_options['rr_cont_bordercolor'].'";
// READING PANEL
var rr_init_textcolor        = "'.$this->rr_options['rr_textcolor'].'";
var rr_init_bgcolor          = "'.$this->rr_options['rr_bgcolor'].'";
var rr_init_bordercolor      = "'.$this->rr_options['rr_bordercolor'].'";
var rr_init_fp_color         = "'.$this->rr_options['rr_fpc'].'";
// READER TRANSLATIONS
var rr_speed                 = "'.__('speed','rocket-reader-speed-reader').'";
var rr_words                 = "'.__('words','rocket-reader-speed-reader').'";
var rr_minute                = "'.__('minute','rocket-reader-speed-reader').'";
var rr_bold_on_off           = "'.__('bold on/off','rocket-reader-speed-reader').'";
var rr_decrease_speed        = "'.__('decrease speed','rocket-reader-speed-reader').'";
var rr_increase_speed        = "'.__('increase speed','rocket-reader-speed-reader').'";
var rr_pause                 = "'.__('pause','rocket-reader-speed-reader').'";
var rr_resume                = "'.__('resume','rocket-reader-speed-reader').'";
var rr_close                 = "'.__('close','rocket-reader-speed-reader').'";
var rr_read_with_reader      = "'.__('Read this article with the Rocket Reader!','rocket-reader-speed-reader').'";
</script>
<!-- END Rocket Reader -->
';		
	} // rr_javascript_vars()	
	

	/*******************************************************************************
	 * 	IS THIS A FRONTEND PAGE?
	 *******************************************************************************/
	function rr_is_frontend_page() {	
		if (isset($GLOBALS['pagenow']))
			return !is_admin() && !in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
		else
			return !is_admin();
	} // rr_is_frontend_page()


	/*******************************************************************************
	 * 	ADD PAGE TO THE SETTINGS MENU
	 *******************************************************************************/
	function rr_admin_menu() {	
		if (function_exists('add_options_page'))
			add_options_page(__('Rocket Reader', 'rocket-reader-speed-reader'), __( 'Rocket Reader', 'rocket-reader-speed-reader' ), 'manage_options', 'rr_settings', array( &$this, 'rr_settings'));		
	} // rr_admin_menu()
	
	
	/*******************************************************************************
	 * 	ADD 'SETTINGS' LINK TO THE MAIN PLUGIN PAGE
	 *******************************************************************************/
	function rr_settings_link($links) {	
		array_unshift($links, '<a href="options-general.php?page=rr_settings">Settings</a>');
		return $links;
	} // rr_settings_link()


	/*******************************************************************************
	 * 	ADD THE ROCKET READER CONTROL TO THE CONTENT
	 *******************************************************************************/	
	function rr_add_control($content) {
		global $wpdb;

		// IS THE ROCKET READER ENABLED FOR THIS PAGE / POST?
		$enable_rocket_reader = get_post_meta(get_the_ID(), 'enable_rocket_reader', true);
		if($enable_rocket_reader != "Y") return $content;
	
		// v1.1.4 - Index the 'clean' content from the post, straight from the database
		$sql = "
		SELECT	`post_content`, `post_type`
		FROM	$wpdb->posts
		WHERE	`ID` = ".get_the_ID()."
		";
		
		$results = $wpdb -> get_results($sql);
		
		$c = $results[0]->post_content;
		$t = $results[0]->post_type;
		
		// SINCE v1.1.4 - ONLY ADD THE CONTROL TO PAGES AND POSTS
		if(!$c || ($t != 'page' && $t != 'post')) return $content;
	
		// SINCE v1.5.1 - REMOVE VISUAL COMPOSER TAGS FROM READER [vc_*] [/vc_*]
		$c = preg_replace('/\[(.?)vc_(.*)\]/i', '', $c);
		
		$c = strip_tags($c);
		$c = str_replace("&nbsp;",  "",       $c);	// space
		$c = str_replace("&#8211;", "xyx123", $c);	// dash
		$c = str_replace("&#8216;", "",       $c);	// single quote
		$c = str_replace("&#8217;", "",       $c);	// single quote
		$c = str_replace("&#8220;", "",       $c);	// double quote
		$c = str_replace("&#8221;", "",       $c);	// double quote
		$c = str_replace("&#8230;", "yxy123", $c);	// ...
		$c = str_replace("&#8243;", "",       $c);	// double quote
	
		// SPLIT THE POST CONTENT INTO (UNICODE) WORDS AND FILL A JAVASCRIPT ARRAY WITH THEM
		// (HAVE TO DO THIS BECAUSE JAVASCRIPT DOESN'T SUPPORT UNICODE REGEX YET...)
		$ex = '';
		$return = $ex.'<script type="text/javascript"> var words'.get_the_ID().' = [';
		preg_match_all("/[\p{L}\p{M}\{0-9}\{-|?|!|%}]+/u", $c, $words, PREG_PATTERN_ORDER);
		
		// SINCE v1.5.0
		$rr_minimum_words = $this->rr_options['rr_minimum_words'];
		if ($rr_minimum_words)
			if(count($words[0]) < $rr_minimum_words) return $content;
		
		for ($i = 0; $i < count($words[0]); $i++) {
			$w = $words[0][$i];
			if($i) $return .= ',';
			// REPLACE PLACE HOLDERS
			$w = str_replace("xyx123", '-', $w);
			$w = str_replace("yxy123", "...", $w);
			// v1.5.1: ADDED BACK IN
			$w = str_replace("amp", "&", $w);
			# REMOVED IN v1.5.0
			# $w = str_replace("lt", "<", $w);
			# $w = str_replace("gt", ">", $w);
			$return .= '"'.$w.'"';
		}
		$return .= ']</script>';
	
		// v1.2.1
		if($this->rr_options['rr_use_popup'] == 'Y') {
			$return .= '
	<div align="right"><button type="button" id="rr_btn_play'.get_the_ID().'">ROCKET READER</button></div><br />
	<div id="rr_wrapper'.get_the_ID().'" class="rr_wrapper_popup" postid="'.get_the_ID().'"></div>';
		} else {
			$return .= '
	<div id="rr_wrapper'.get_the_ID().'" class="rr_wrapper" postid="'.get_the_ID().'"></div>';
		}
	
		$return .= '
	<div id="rr_content'.get_the_ID().'"> '.$content.' </div>
		';
		
		return $return;
	} // rr_add_control()
} // RocketReader
?>