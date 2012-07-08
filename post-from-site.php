<?php
/**
 * Plugin Name: Post From Site 2
 * Plugin URI: http://redradar.net/category/plugins/post-from-site/
 * Description: Add a new post/page/{your custom post type} directly from your website.
 * Author: Kelly Dwan
 * Version: 0.1
 * Date: 07.07.12
 * Author URI: http://redradar.net/
 */
 
/*
TODO

*/

class PostFromSite2 {

    public function __construct($var = '') {
    	register_activation_hook( __FILE__, array ($this, 'install' ) );
    	
    	// TODO deal with translations.
    	load_plugin_textdomain('pfs_domain');
    	
    	// add js & css
    	add_action( 'get_header', array($this,'includes') );
    	
    	// add shortcode support
    	add_shortcode( 'post-from-site', array($this, 'shortcode') );
    	
    	// add admin page & options
    	add_filter( 'admin_init' , array( $this , 'register_fields' ) );
    	
    	// add wp ajax hook for handling submission

    }
    
    public function install(){
    	//nothing here yet, as there's really nothing to 'install' that isn't covered by __construct
    	// Can I error here if PFS 1 is currently active? They'll collide otherwise. (same shortcode)
    }
    
    /**
	 * Add javascript and css to header files.
	 */
	public function includes(){
	    // chosen for taxonomy choosing
	    wp_enqueue_script( 'pfs-script', plugins_url("includes/pfs-script.js",__FILE__) );
	    wp_enqueue_style( 'pfs-min-style',  plugins_url("includes/minimal.css",__FILE__) );
	}

	/**
	 * Add shortcode support.
	 * @param $atts shortcode attributes, cat, link, and popup
	 * cat is the category to post to, link is the display text of the link,
	 * and popup decides whether it's an inline form (false) or a popup box (true).
	 */
	function shortcode($atts, $content=null, $code="") {
	    global $pfs;
	    return $pfs->get_form();
	}
	
	function register_fields() {
		register_setting( 'general', 'pfs_2_options', array($this, 'validate') );
		
		add_settings_section( 'pfs2', 'Post From Site Settings', array($this, 'setting_section_pfs'), 'general' );
		
		// Status
		add_settings_field('pfs_status', '<label for="pfs_status">'.__('Post Status:' , 'pfs_domain' ).'</label>' , array(&$this, 'setting_pfs_status') , 'general', 'pfs2' );
		// Type
		// Comments?
		// Taxonomies
		
	}
	function setting_section_pfs(){
		echo "<p>".__("Some description about PFS",'pfs_domain')."</p>";
	}

	function setting_pfs_status() {
		$options = get_option('pfs_2_options');
		echo "choose status";
	}
	
	/**
	 * Sanitize and validate input. 
	 * @param array $input an array to sanitize
	 * @return array a valid array.
	 */
	public function validate($input) {
	    $output = array();
	    
	    // validate each option, filling out $output with correct vals.
	    if ( isset( $input['pfs_status'] ) )
	    	$output['pfs_status'] = $input['pfs_status'];
	    
	    return $output;
	}
	
	function get_form() {
	    // print out form, so much simpler with wp_editor!
	    // add chosen-enabled taxonomy boxes, note somewhere that you can disable chosen via code by removing from enqueue
	}
}
$GLOBALS['pfs'] = new PostFromSite2();