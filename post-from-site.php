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
		
		// add settings link to plugin action links
		add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), array($this, 'add_plugin_action_links') );
		
		// add js & css
		add_action( 'get_header', array($this,'includes') );
		
		// add shortcode support
		add_shortcode( 'post-from-site', array($this, 'shortcode') );
		
		// add admin page & options
		add_filter( 'admin_init' , array( $this, 'register_fields' ) );
		
		// add wp ajax hook for handling submission

	}
	
	public function install(){
		//nothing here yet, as there's really nothing to 'install' that isn't covered by __construct
		// Can I error here if PFS 1 is currently active? They'll collide otherwise. (same shortcode)
	}
	
	function add_plugin_action_links( $links ) {
		return array_merge(
			array( 'settings' => sprintf('<a href="%1$s">%2$s</a>', admin_url('options-writing.php#pfs'), __('Settings','pfs_domain') ) ),
			$links
		);
	}
	
	/**
	 * Add javascript and css to header files.
	 */
	public function includes(){
		// chosen for taxonomy choosing
		wp_enqueue_script( 'chosen', 'https://raw.github.com/harvesthq/chosen/master/chosen/chosen.jquery.min.js', array('jquery') );
		wp_enqueue_script( 'pfs-script', plugins_url("includes/script.js",__FILE__), array('jquery') );
		//wp_enqueue_style( 'chosen',  'https://raw.github.com/harvesthq/chosen/master/chosen/chosen.css' );
		wp_enqueue_style( 'pfs-min-style',  plugins_url("includes/minimal.css",__FILE__) );
		
		//wp_enqueue_script( 'chosen', plugins_url("includes/chosen/chosen.jquery.min.js",__FILE__), array('jquery') );
		wp_enqueue_style( 'chosen',  plugins_url("includes/chosen/chosen.css",__FILE__) );
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
		
		// Type
		add_settings_field('pfs_type', '<label for="pfs_type">'.__('Post Type:' , 'pfs_domain' ).'</label>' , array(&$this, 'setting_pfs_type') , 'writing', 'pfs2' );
		// Status
		add_settings_field('pfs_status', '<label for="pfs_status">'.__('Post Status:' , 'pfs_domain' ).'</label>' , array(&$this, 'setting_pfs_status') , 'writing', 'pfs2' );
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
		echo "<p id='pfs'>".__("Some description about PFS",'pfs_domain')."</p>";
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
	
	function setting_pfs_status() {
		$options = $this->get_options();
		$post_statuses = get_post_stati( array( 'show_in_admin_all_list'=>true ), 'object' );
		unset($post_statuses['future']);
		$post_statuses['perpost'] = (object) array('name'=>'perpost','label'=>'Per Post');
		?>
		<select id='pfs_status' name='pfs_2_options[pfs_status]'>
			<?php foreach ($post_statuses as $status ) { ?>
				<option<?php selected( $options['pfs_status'], $status->name ); ?> value="<?php echo $status->name; ?>"><?php echo $status->label; ?></option>
			<?php } ?>
		</select>
		<label class="description" for="pfs_status"><?php _e( 'Per Post will let you decide per-post', 'pfs_domain' ); ?></label>
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
	 * TODO: 
	 */
	public function validate($input) {
		$options = $this->get_options();
		$output = array();
		$valid_statuses = get_post_stati( array( 'show_in_admin_all_list'=>true ) );
		unset($valid_statuses['future']); // we don't have a way of setting scheduled posts, so don't allow it.
		$valid_statuses['perpost'] = 'perpost';
		$valid_types = get_post_types( array( 'public'=>true, 'show_ui'=>true ), 'names' );
		$valid_comment = array( 'open', 'closed', 'perpost' );
		
		if ( isset( $input['pfs_type'] ) && in_array( $input['pfs_type'], $valid_types ) ) {
			$output['pfs_type'] = $input['pfs_type'];
		}

		// status is not affected by changing post type.
		if ( isset( $input['pfs_status'] ) && in_array( $input['pfs_status'], $valid_statuses ) ) {
			$output['pfs_status'] = $input['pfs_status']; 
		}

		// grab the current post type, either just set & is in output, or grab from $options.
		$post_type = (isset($output['pfs_type']))? $output['pfs_type']: $options['pfs_type'];

		// comments 
		if ( isset( $input['pfs_comments'] ) ){
			if ( !post_type_supports( $post_type, 'comments' ) ){
				add_settings_error('pfs_comments', 'pfs_comments', __('Comments are not supported on the chosen post type.', 'pfs_domain') );
			} else if ( in_array( $input['pfs_comments'], $valid_comment ) ) {
				$output['pfs_comments'] = $input['pfs_comments'];
			}
		}
		
		if ( isset( $input['pfs_taxonomies'] ) ){
			$output['pfs_taxonomies'] = array();
			$valid_taxonomies = get_taxonomies( array( 'public'=>true, 'show_ui'=>true, 'object_type'=>array($post_type) ), 'names' );
			foreach ( $input['pfs_taxonomies'] as $t ) {
				if ( in_array( $t, $valid_taxonomies ) )
					$output['pfs_taxonomies'][] = $t;
			}
			$diff = array_diff($input['pfs_taxonomies'],$output['pfs_taxonomies']);
			if ( count($diff) ){
				add_settings_error('pfs_taxonomies', 'pfs_taxonomies', sprintf( _n('The taxonomy %s is not supported on the chosen post type.', 'The taxonomies %s are not supported on the chosen post type.', count($diff), 'pfs_domain'), implode(', ', $diff) ) );
			}
		}
		return $output;
	}
	
	function get_form() {
		//if ( no capability ) return;
		
		$options = $this->get_options();

		$style = '<style>
			input.title {
				width:100%;
				box-sizing:border-box;
			}
			select.chzn-select {
				display:block;
				width:100%;
			}
		</style>';
		
		
		// nonce
		$nonce = wp_nonce_field( 'create_post', '_pfs_create_nonce', true, false );
		
		// title
		$title = '<input class="title" type="text" placeholder="Enter title here" />';
		
		// editor
		$editor_settings = array();
		$editor_settings = apply_filters('pfs_editor_settings', $editor_settings);
		ob_start();
		wp_editor('', 'post_content', $editor_settings);
		$editor = ob_get_contents();
		ob_clean();
		
		// chosen-enabled taxonomy boxes
		if ( apply_filters('pfs_use_chosen', true) )
			$chosen = '<script type="text/javascript"> jQuery(document).ready(function($){  $( ".chzn-select" ).chosen();  }); </script>';
		else
			$chosen = ''; 
		$taxonomies = '';
		foreach ( $options['pfs_taxonomies'] as $tax) {
			$tax = get_taxonomy($tax); // grab the object, so we have labels etc.
			//var_export($tax);
			$taxonomies .= sprintf(
				'<select data-placeholder="%1$s" name="%2$s[]" id="%2$s" class="chzn-select widefat" multiple>',
				$tax->label,
				$tax->name
			);
			$terms = get_terms( $tax->name, 'hide_empty=0' );
			foreach ( $terms as $term ) { 
				$taxonomies .= sprintf( '<option value="%1$s">%2$s</option>', $term->slug, $term->name );
			}
			$taxonomies .= '</select>';	
		}
		
		// comment status, if set as perpost
		
		// publish status, if set as perpost
		
		$form = '<form id="post-from-site">' .
			$style .
			$nonce .
			$title .
			$editor .
			$chosen . 
			$taxonomies .
		'</form>';
		return $form;
	}
	

}
$GLOBALS['pfs'] = new PostFromSite2();