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
		register_setting( 'writing', 'pfs_2_options', array($this, 'validate') );
		
		add_settings_section( 'pfs2', 'Post From Site Settings', array($this, 'setting_section_pfs'), 'writing' );
		
		// Status
		add_settings_field('pfs_status', '<label for="pfs_status">'.__('Post Status:' , 'pfs_domain' ).'</label>' , array(&$this, 'setting_pfs_status') , 'writing', 'pfs2' );
		// Type
		add_settings_field('pfs_type', '<label for="pfs_type">'.__('Post Type:' , 'pfs_domain' ).'</label>' , array(&$this, 'setting_pfs_type') , 'writing', 'pfs2' );
		// Comments?
		add_settings_field('pfs_comments', '<label for="pfs_comments">'.__('Comment Status:' , 'pfs_domain' ).'</label>' , array(&$this, 'setting_pfs_comments') , 'writing', 'pfs2' );
		// Taxonomies
		add_settings_field('pfs_taxonomies', '<label for="pfs_taxonomies">'.__('Taxonomies to use:' , 'pfs_domain' ).'</label>' , array(&$this, 'setting_pfs_taxonomies') , 'writing', 'pfs2' );
		
	}
	/**
	 * Returns the options array for PFS, with defaults if needed.
	 */
	function get_options() {
		$saved = (array) get_option( 'pfs_2_options' );
		$defaults = array(
			'pfs_status' => 'publish',
			'pfs_type' => 'post',
			'pfs_comments' => 'open',
			'pfs_taxonomies' => array()
		);
		$defaults = apply_filters( 'pfs_2_default_options', $defaults );
		
		$options = wp_parse_args( $saved, $defaults );
		$options = array_intersect_key( $options, $defaults );
		
		return $options;
	}
	
	function setting_section_pfs(){
		echo "<p>".__("Some description about PFS",'pfs_domain')."</p>";
	}

	function setting_pfs_status() {
		$options = $this->get_options();
		?>
		<select id='pfs_status' name='pfs_2_options[pfs_status]'>
            <option<?php selected( $options['pfs_status'], 'publish' ); ?> value='publish'><?php _e('Published') ?></option>
            <option<?php selected( $options['pfs_status'], 'private' ); ?> value='private'><?php _e('Privately Published') ?></option>
            <option<?php selected( $options['pfs_status'], 'pending' ); ?> value='pending'><?php _e('Pending Review') ?></option>
            <option<?php selected( $options['pfs_status'], 'draft' ); ?> value='draft'><?php _e('Draft') ?></option>
        </select>
        <?php 
	}
	
	function setting_pfs_type() {
		$options = $this->get_options();
		$post_types = get_post_types(array('public'=>true, 'show_ui'=>true), 'object'); 
		?>
		<select id='pfs_type' name='pfs_2_options[pfs_type]'>
            <?php foreach ($post_types as $post_type ) { ?>
                <option<?php selected( $options['pfs_type'], $post_type->name ); ?> value="<?php echo $post_type->name; ?>"><?php echo $post_type->labels->singular_name; ?></option>
            <?php } ?>
	    </select>
	    <?php
	}
	
	function setting_pfs_comments() {
		$options = $this->get_options();
		?>
		<select id='pfs_comments' name='pfs_2_options[pfs_comments]'>
	        <option<?php selected( $options['pfs_comments'], 'open' ); ?> value='open'><?php _e('Open') ?></option>
	        <option<?php selected( $options['pfs_comments'], 'closed' ); ?> value='closed'><?php _e('Closed') ?></option>
	        <option<?php selected( $options['pfs_comments'], 'perpost' ); ?> value='perpost'><?php _e('Per Post') ?></option>
	    </select>
	    <label class="description" for="pfs_comments"><?php _e( 'Per Post will let you decide per-post', 'pfs_domain' ); ?></label>
	    <?php 
	}
	
	function setting_pfs_taxonomies() {
		$options = $this->get_options();
		$taxonomies = get_taxonomies( array( 'public'=>true, 'show_ui'=>true ), 'object' ); 
		foreach ($taxonomies as $taxonomy ) { ?>
    		<label for"pfs_taxonomies">
    			<input type="checkbox" name="pfs_2_options[pfs_taxonomies][]" id="pfs_taxonomies" value="<?php echo $taxonomy->name; ?>" <?php if( in_array($taxonomy->name, $options['pfs_taxonomies'])) echo "checked"; ?> />
    			<?php echo $taxonomy->label; ?>
    		</label>
	    <?php }
	}
	
	/**
	 * Sanitize and validate input. 
	 * @param array $input an array to sanitize
	 * @return array a valid array.
	 */
	public function validate($input) {
	    $output = array();
	    $valid_statuses = array( 'publish', 'private', 'pending', 'draft' );
	    $valid_types = get_post_types( array( 'public'=>true, 'show_ui'=>true ), 'names' );
	    $valid_comment = array( 'open', 'closed', 'perpage' );
	    $valid_taxonomies = get_taxonomies( array( 'public'=>true, 'show_ui'=>true ), 'names' );

	    // validate each option, filling out $output with correct vals.
	    if ( isset( $input['pfs_status'] ) && in_array( $input['pfs_status'], $valid_statuses ) )
	    	$output['pfs_status'] = $input['pfs_status']; 
	    if ( isset( $input['pfs_type'] ) && in_array( $input['pfs_type'], $valid_types ) )
	        $output['pfs_type'] = $input['pfs_type'];
	    if ( isset( $input['pfs_comment'] ) && in_array( $input['pfs_comment'], $valid_comment ) )
	        $output['pfs_comment'] = $input['pfs_comment'];
	    if ( isset( $input['pfs_taxonomies'] ) ){
	        $output['pfs_taxonomies'] = array();
	        foreach ( $input['pfs_taxonomies'] as $t ) {
	            if ( in_array( $t, $valid_taxonomies ) )
        	        $output['pfs_taxonomies'][] = $t;
        	}
        }
	    
	    
	    return $output;
	}
	
	function get_form() {
	    // print out form, so much simpler with wp_editor!
	    // add chosen-enabled taxonomy boxes, note somewhere that you can disable chosen via code by removing from enqueue
	}
}
$GLOBALS['pfs'] = new PostFromSite2();