<?php
/**
 * The file that defines the custom post type and taxonomy we'll need for this plugin.
 *
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC Salud America
 * @subpackage CC Salud America/includes
 */

/**
 * Define the custom post type and taxonomy we'll need for this plugin.
 *
 *
 * @since      1.0.0
 * @package    CC Salud America
 * @subpackage CC Salud America/includes
 * @author     Your Name <email@example.com>
 */
class CC_SA_Take_Action_CPT_Tax extends CC_Salud_America {

	private $nonce_value = 'sa_take_action_meta_box_nonce';
	private $nonce_name = 'sa_take_action_meta_box';
	public $post_type = 'sa_take_action';

	/**
	 * Initialize the extension class
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		// Register Policy custom post type
		add_action( 'init', array( $this, 'register_cpt' ), 17 );

		// Register related taxonomies
		// add_action( 'init', array( $this, 'register_resource_types_taxonomy' ) );
		// add_action( 'init', array( $this, 'register_resource_cats_taxonomy' ) );

		// Add submenus to handle the edit screens for our custom taxonomies
		// add_action( 'admin_menu', array( $this, 'create_taxonomy_management_menu_items' ) );
		// add_action( 'parent_file', array( $this, 'sa_tax_menu_highlighting' ) );

		// Handle saving policies
		add_action( 'save_post', array( $this, 'save' ) );

		// Add our templates to BuddyPress' template stack.
		// add_filter( 'manage_edit-sapolicies_columns', array( $this, 'edit_admin_columns') );
		// add_filter( 'manage_sapolicies_posts_custom_column', array( $this, 'manage_admin_columns') );
		// add_filter( 'manage_edit-sapolicies_sortable_columns', array( $this, 'register_sortable_columns' ) );
		// add_action( 'pre_get_posts', array( $this, 'sortable_columns_orderby' ) );
		add_action( 'admin_init', array( $this, 'add_meta_box' ) );

		// add_action( 'bp_init', array( $this, 'capture_vote_submission'), 78 );
		// add_action( 'bp_init', array( $this, 'capture_join_group_submission'), 78 );

	}

	/**
	 * Define the "sa_policies" custom post type and related taxonomies:
	 * "sa_advocacy_targets", "sa_policy_tags" and "sa_geographies".
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	public function register_cpt() {

	    $labels = array(
	        'name' => _x( 'SA Take Action', 'sa_take_action' ),
	        'singular_name' => _x( 'SA Take Action', 'sa_take_action' ),
	        'add_new' => _x( 'Add New', 'sa_take_action' ),
	        'add_new_item' => _x( 'Add New Petition', 'sa_take_action' ),
	        'edit_item' => _x( 'Edit Petition', 'sa_take_action' ),
	        'new_item' => _x( 'New Petitions', 'sa_take_action' ),
	        'view_item' => _x( 'View Petitions', 'sa_take_action' ),
	        'search_items' => _x( 'Search SA Take Action', 'sa_take_action' ),
	        'not_found' => _x( 'No petitions found', 'sa_take_action' ),
	        'not_found_in_trash' => _x( 'No petitions found in Trash', 'sa_take_action' ),
	        'parent_item_colon' => _x( 'Parent Petition:', 'sa_take_action' ),
	        'menu_name' => _x( 'SA Take Action', 'sa_take_action' ),
	    );

	    $args = array(
	        'labels' => $labels,
	        'hierarchical' => false,
	        'description' => 'Petition campaigns run by Salud America',
	        'supports' => array( 'title', 'editor', 'thumbnail' ),
	        'public' => true,
	        'show_ui' => true,
	        'show_in_menu' => 'salud_america',
	        'show_in_nav_menus' => true,
	        'publicly_queryable' => true,
	        'exclude_from_search' => false,
	        'has_archive' => true,
	        'query_var' => true,
	        'can_export' => true,
	        'rewrite' => true,
	        'capability_type' => $this->post_type,
	        'map_meta_cap' => true
	    );

	    register_post_type( $this->post_type, $args );

	}

	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Add Type and Stage columns (populated from post meta).
	 *
	 * @since    1.0.0
	 *
	 * @return   array of columns to display
	 */
	public function edit_admin_columns( $columns ) {
		// Last two columns are always Comments and Date.
		// We want to insert our new columns just before those.
		$entries = count( $columns );
		$opening_set = array_slice( $columns, 0, $entries - 2 );
		$closing_set = array_slice( $columns, - 2 );

		$insert_set = array(
			'type' => __( 'Type' ),
			'stage' => __( 'Stage' )
			);

		$columns = array_merge( $opening_set, $insert_set, $closing_set );

		return $columns;
	}

	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Handle Output for Type and Stage columns (populated from post meta).
	 *
	 * @since    1.0.0
	 *
	 * @return   string content of custom columns
	 */
	public function manage_admin_columns( $column, $post_id ) {
			switch( $column ) {
				case 'type' :
					// These are all title case.
					$type = get_post_meta( $post_id, 'sa_policytype', true );
					echo $type;
				break;
				case 'stage' :
					// These are all lowercase.
					$stage = get_post_meta( $post_id, 'sa_policystage', true );
					echo ucfirst( $stage );
				break;
			}
	}

	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Add sortability to Type and Stage columns.
	 *
	 * @since    1.0.0
	 *
	 * @return   array of columns to display
	 */
	public function register_sortable_columns( $columns ) {
					$columns["type"] = "type";
					$columns["stage"] = "stage";
					//Note: Advo targets can't be sortable, because the value is a string.
					return $columns;
	}
	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Define sorting query for Type and Stage columns.
	 *
	 * @since    1.0.0
	 *
	 * @return   alters $query variable by reference
	 */
	function sortable_columns_orderby( $query ) {
			if ( ! is_admin() ) {
				return;
			}

			$orderby = $query->get( 'orderby');

			switch ( $orderby ) {
				case 'stage':
						$query->set( 'meta_key','sa_policystage' );
						$query->set( 'orderby','meta_value' );
					break;
				case 'type':
						$query->set( 'meta_key','sa_policytype' );
						$query->set( 'orderby','meta_value' );
					break;
			}
	}

	/**
	 * Modify the SA Policies edit screen.
	 * - Add meta boxes for policy meta and geography.
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	//Building the input form in the WordPress admin area
	function add_meta_box() {
		add_meta_box( 'sa_take_action_meta_box', 'Petition Details', array( $this, 'sa_take_action_meta_box' ), 'sa_take_action', 'normal', 'high' );   ;
	}
		function sa_take_action_meta_box() {
			$custom = get_post_custom( $post->ID );

			// Add a nonce field so we can check for it later.
			wp_nonce_field( $this->nonce_name, $this->nonce_value );
			?>
			<div>
				<p>
					<label for='sa_take_action_url'>Petition URL</label>
					<input type='text' name='sa_take_action_url' value='<?php
						if ( ! empty( $custom[ 'sa_take_action_url' ][0] ) ) {
							echo $custom[ 'sa_take_action_url' ][0];
						}
						?>' size="90"/>
				</p>
				<p class="info">Note: Petition URLs should take the form <em>http://www.thepetitionsite.com/takeaction/702/787/135/?z00m=21258369</em></p>
				<p>
					<input type="checkbox" id="sa_take_action_highlight" name="sa_take_action_highlight" value='1' <?php checked( $custom[ 'sa_take_action_highlight' ][0] ); ?> > <label for="sa_take_action_highlight">Highlight this campaign at the top of the hub home page.</label>
				</p>
			</div>
			<div>


			</div>
			<?php

			}

	/**
	 * Save resources extra meta.
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	public function save( $post_id ) {

 		if ( get_post_type( $post_id ) != $this->post_type ) {
			return;
		}

		if ( ! $this->user_can_save( $post_id, $this->nonce_value, $this->nonce_name  ) ) {
			return false;
		}
		// Create array of fields to save
		$meta_fields_to_save = array( 'sa_take_action_highlight', 'sa_take_action_url' );

		// Save meta
		$meta_success = $this->save_meta_fields( $post_id, $meta_fields_to_save );

	}

} //End class CC_SA_Resources_CPT_Tax
$sa_take_action_cpt_tax = new CC_SA_Take_Action_CPT_Tax();