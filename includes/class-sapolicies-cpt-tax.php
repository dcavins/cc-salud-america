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
class CC_SA_Policies_CPT_Tax extends CC_Salud_America {

	private $nonce_value = 'sa_policy_meta_box_nonce';
	private $nonce_name = 'sa_policy_meta_box';
	private $post_type = 'sapolicies';

	/**
	 * Initialize the extension class
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		// Register Policy custom post type
		add_action( 'init', array( $this, 'register_policies_cpt' ) );

		// Register related taxonomies
		add_action( 'init', array( $this, 'register_sa_advocacy_targets' ) );
		add_action( 'init', array( $this, 'register_sa_policy_tags' ) );
		add_action( 'init', array( $this, 'register_sa_geographies' ) );

		// Add submenus to handle the edit screens for our custom taxonomies
		add_action( 'admin_menu', array( $this, 'create_taxonomy_management_menu_items' ) );
		add_filter( 'parent_file', array( $this, 'sa_tax_menu_highlighting' ) );

		// Handle saving policies
		add_action( 'save_post', array( $this, 'save' ) );

		// Add our templates to BuddyPress' template stack.
		add_filter( 'manage_edit-sapolicies_columns', array( $this, 'edit_admin_columns') );
		add_filter( 'manage_sapolicies_posts_custom_column', array( $this, 'manage_admin_columns'), 12, 2 );
		add_filter( 'manage_edit-sapolicies_sortable_columns', array( $this, 'register_sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'sortable_columns_orderby' ) );
		add_action( 'admin_init', array( $this, 'add_meta_box' ) );

		// Use Chosen.js on the policies edit screen.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_chosen_js' ), 10, 1 );

		// Let BP know that policies have activity items
		// add_action( 'bp_register_activity_actions', array( $this, 'sapolicies_register_activity_actions' ) );

		// Add the autosuggest handler on this post_type's edit screen.
		add_action( 'save_post', array( $this, 'sa_related_leaders_meta_box_save' ) );

		// Change the REST API response so that it includes important info for these items.
		add_action( 'rest_api_init', array( $this, 'rest_read_meta' ) );
	}

	/**
	 * Define the "sa_policies" custom post type and related taxonomies:
	 * "sa_advocacy_targets", "sa_policy_tags" and "sa_geographies".
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	public function register_policies_cpt() {
			$policy_labels = array(
					'name' => __( 'Changes', $this->plugin_slug ),
					'singular_name' => __( 'Change', $this->plugin_slug ),
					// 'all_items' => __( 'All Changes', $this->plugin_slug),
					'add_new' => __( 'Add Change', $this->plugin_slug ),
					'add_new_item' => __( 'Add New Change', $this->plugin_slug ),
					'edit_item' => __( 'Edit Change', $this->plugin_slug ),
					'new_item' => __( 'New Change', $this->plugin_slug ),
					'view_item' => __( 'View Change', $this->plugin_slug ),
					'search_items' => __( 'Search in Changes', $this->plugin_slug ),
					'not_found' =>  __( 'No Changes found', $this->plugin_slug ),
					'not_found_in_trash' => __( 'No changes found in trash', $this->plugin_slug ),
					'parent_item_colon' => __( 'Parent Change:', $this->plugin_slug ),
			        'menu_name' => __( 'Salud Changes', $this->plugin_slug ),

			);
			$args = array(
					'labels' => $policy_labels,
					'public' => true,
					'publicly_queryable' => true,
					'query_var' => true,
					'rewrite' => true,
					'hierarchical' => false,
					'show_ui' => true,
					'show_in_menu' => true, //'salud_america',
					'menu_position' => 55,
					'taxonomies' => array('sa_advocacy_targets', 'sa_policy_tags'),
					// 'supports' => array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes',),
					'has_archive' => true,
					'supports' => array('title','editor','comments', 'thumbnail', 'author'),
					'show_in_rest' => true,
					'capability_type' => $this->post_type,
					'map_meta_cap' => true
			);

			register_post_type( $this->post_type, $args );
	}

	public function register_sa_advocacy_targets() {

		$labels = array(
				'name' => __( 'Advocacy Targets', $this->plugin_slug ),
				'singular_name' => __( 'Advocacy Target', $this->plugin_slug  ),
				'search_items' => __( 'Search Advocacy Targets', $this->plugin_slug ),
				'popular_items' => __( 'Popular Advocacy Targets', $this->plugin_slug ),
				'all_items' => __( 'All Advocacy Targets', $this->plugin_slug ),
				'parent_item' => __( 'Parent Advocacy Target', $this->plugin_slug ),
				'parent_item_colon' => __( 'Parent Advocacy Target:', $this->plugin_slug ),
				'edit_item' => __( 'Edit Advocacy Target', $this->plugin_slug ),
				'update_item' => __( 'Update Advocacy Target', $this->plugin_slug ),
				'add_new_item' => __( 'Add New Advocacy Target', $this->plugin_slug ),
				'new_item_name' => __( 'New Advocacy Target', $this->plugin_slug ),
				'separate_items_with_commas' => __( 'Separate advocacy targets with commas', $this->plugin_slug ),
				'add_or_remove_items' => __( 'Add or remove Advocacy Targets', $this->plugin_slug ),
				'choose_from_most_used' => __( 'Choose from most used Advocacy Targets', $this->plugin_slug ),
				'menu_name' => __( 'Advocacy Targets', $this->plugin_slug ),
		);

		$args = array(
				'labels' => $labels,
				'public' => true,
				'show_in_nav_menus' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'capabilities' => array(
												'manage_terms' => 'edit_sapoliciess',
												'delete_terms' => 'edit_sapoliciess',
												'edit_terms' => 'edit_sapoliciess',
												'assign_terms' => 'edit_sapoliciess'
										),
				'show_tagcloud' => true,
				'show_admin_column' => true,
				'hierarchical' => true,
				'rewrite' => true,
				// 'rewrite' => array( 'slug' => 'salud/sa_advocacy_targets', 'with_front' => false),
				'query_var' => true,
				// WP REST API
				'show_in_rest' => true,
				'rest_base' => 'sa_advocacy_targets',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
		);

		register_taxonomy( 'sa_advocacy_targets', array( $this->post_type ), $args );
	}

	public function register_sa_policy_tags() {

		$labels = array(
				'name' => __( 'SA Tags', $this->plugin_slug ),
				'singular_name' => __( 'SA Tag', $this->plugin_slug ),
				'search_items' => __( 'Search SA Tags', $this->plugin_slug ),
				'popular_items' => __( 'Popular SA Tags', $this->plugin_slug ),
				'all_items' => __( 'All SA Tags', $this->plugin_slug ),
				'parent_item' => __( 'Parent SA Tag', $this->plugin_slug ),
				'parent_item_colon' => __( 'Parent SA Tag:', $this->plugin_slug ),
				'edit_item' => __( 'Edit SA Tag', $this->plugin_slug ),
				'update_item' => __( 'Update SA Tag', $this->plugin_slug ),
				'add_new_item' => __( 'Add New SA Tag', $this->plugin_slug ),
				'new_item_name' => __( 'New SA Tag', $this->plugin_slug ),
				'separate_items_with_commas' => __( 'Separate SA tags with commas', $this->plugin_slug ),
				'add_or_remove_items' => __( 'Add or remove SA Tags', $this->plugin_slug ),
				'choose_from_most_used' => __( 'Choose from most used SA Tags', $this->plugin_slug ),
				'menu_name' => __( 'SA Tags', $this->plugin_slug ),
		);

		$args = array(
				'labels' => $labels,
				'public' => true,
				'show_in_nav_menus' => true,
				'show_ui' => true,
				'capabilities' => array(
												'manage_terms' => 'edit_sapoliciess',
												'delete_terms' => 'edit_sapoliciess',
												'edit_terms' => 'edit_sapoliciess',
												'assign_terms' => 'edit_sapoliciess'
												),
				'show_tagcloud' => true,
				'show_admin_column' => true,
				'hierarchical' => false,
				'rewrite' => true,
				'query_var' => true
		);

		register_taxonomy( 'sa_policy_tags', array( $this->post_type, 'saresources' ), $args );
	}

	public function register_sa_geographies() {
		// Add new "Geographies" taxonomy to Salud America Policies

		$labels = array(
					'name' => __( 'Geographies', $this->plugin_slug ),
					'singular_name' => __( 'Geography', $this->plugin_slug ),
					'search_items' =>  __( 'Search Geographies', $this->plugin_slug ),
					'all_items' => __( 'All Geographies', $this->plugin_slug ),
					'parent_item' => __( 'Parent Geographies', $this->plugin_slug ),
					'parent_item_colon' => __( 'Parent Geography:', $this->plugin_slug ),
					'edit_item' => __( 'Edit Geography', $this->plugin_slug ),
					'update_item' => __( 'Update Geography', $this->plugin_slug ),
					'add_new_item' => __( 'Add New Geography', $this->plugin_slug ),
					'new_item_name' => __( 'New Geography Name', $this->plugin_slug ),
					'menu_name' => __( 'Geographies', $this->plugin_slug ),
				);

		$args = array(
						'labels' => $labels,
						'public' => false,
						// 'show_in_nav_menus' => false,
						// 'show_ui' => false,
						'show_tagcloud' => false,
						'show_admin_column' => true,
						'hierarchical' => true,
				'rewrite' => array(
							'slug' => 'geographies', // This controls the base slug that will display before each term
							'with_front' => false, // Don't display the category base before "/locations/"
							'hierarchical' => true // This will allow URLs like "/locations/boston/cambridge/"
						),
					 // 'query_var' => true
				);

		register_taxonomy( 'geographies', $this->post_type, $args );
	}

	/**
	 * Manage wp-admin behavior/appearance of CPT and taxonomy menus.
	 * - Add submenus to handle the edit screens for out custom taxonomies.
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	public function create_taxonomy_management_menu_items() {
		add_submenu_page( 'salud_america', 'Advocacy Targets', 'Advocacy Targets', 'edit_others_posts', 'edit-tags.php?taxonomy=sa_advocacy_targets');
		add_submenu_page( 'salud_america', 'SA Tags', 'SA Tags', 'edit_others_posts', 'edit-tags.php?taxonomy=sa_policy_tags');
	}

	/**
	 * Manage wp-admin behavior/appearance of CPT and taxonomy menus.
	 * - Make sure that the "Salud America" menu item is highlighted when viewing a taxonomy.
	 * - Default behavior highlights the "Posts" menu item.
	 *
	 * @since    1.0.0
	 *
	 * @return   string   id of menu item that should be highlighted
	 */
	public function sa_tax_menu_highlighting( $parent_file ) {
		global $current_screen;
		$sa_taxes = array( 'sa_advocacy_targets', 'sa_policy_tags' );
		if ( in_array( $current_screen->taxonomy, $sa_taxes ) ) {
			$parent_file = 'salud_america';
		}
		return $parent_file;
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
	 * Register and enqueue scripts and styles for chosen js select list helper.
	 *
	 * @since    1.8.0
	 */
	public function enqueue_chosen_js( $hook ) {
	    global $post;

	    if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
	        if ( $this->post_type === $post->post_type ) {
				wp_enqueue_style( 'chosen-js-styles', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.min.css', array(), '1.4.2' );
				wp_enqueue_script( 'chosen-js-script', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.jquery.min.js', array( 'jquery' ), '1.4.2' );
			}
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
			 add_meta_box( 'sa_policy_meta_box', 'Policy Information', array( $this, 'sa_policy_meta_box' ), $this->post_type, 'normal', 'high');
			 add_meta_box( 'sa_geog_meta_box', 'Geography',  array( $this, 'sa_geog_meta_box' ), $this->post_type, 'normal', 'high' );
			 add_meta_box( 'sa_related_leaders_meta_box', 'Associated Leaders',  array( $this, 'sa_related_leaders_meta_box' ), $this->post_type, 'side', 'default' );
	}

	/**
	 * Build the geographies meta box.
	 *
	 * @since    1.0.0
	 *
	 * @return   html
	 */
	function sa_geog_meta_box( $post ) {

		//Walk up the geographies taxonomy from the selected geography
		//Get the Geography term for this post
		$geo_tax = get_the_terms( $post->ID, 'geographies' );
		$geo_tax_id = isset( $geo_tax[0] ) ? $geo_tax[0]->term_id : 0;

		//Helper function returns the type of geography we're working with.
		$geo_type = cc_get_the_geo_tax_type( $post->ID );

		//Get the state name in human-readable format
		$geo_tax_state = cc_get_the_geo_tax_state( $post->ID );

	?>
	<style type="text/css">
			#leftcolumn, #rightcolumn, #leftcolumn2, #rightcolumn2  { width: 44%; margin-right: 3%; float: left; }
	</style>

	<div id="leftcolumn">
			<!-- <h4>Geography</h4> -->
			<ul id="sa_geog_select">
				<li><input type="radio" name="sa_geog" id="sa_geog_national" value="National" <?php checked( $geo_type, 'National' ); ?>> <label for="sa_geog_national">National</label></li>
				<li><input type="radio" name="sa_geog" id="sa_geog_state" value="State" <?php checked( $geo_type, 'State' ); ?>> <label for="sa_geog_state">State</label></li>
				<li><input type="radio" name="sa_geog" id="sa_geog_county" value="County" <?php checked( $geo_type, 'County' ); ?>> <label for="sa_geog_county">County</label></li>
				<li><input type="radio" name="sa_geog" id="sa_geog_city" value="City" <?php checked( $geo_type, 'City' ); ?>> <label for="sa_geog_city">City</label></li>
				<li><input type="radio" name="sa_geog" id="sa_geog_school_district" value="School District" <?php checked( $geo_type, 'School District' ); ?>> <label for="sa_geog_school_district">School District</label></li>
				<li><input type="radio" name="sa_geog" id="sa_geog_us_congress" value="US Congressional District" <?php checked( $geo_type, 'US Congressional District' ); ?>> <label for="sa_geog_us_congress">US Congressional District</label></li>
				<li><input type="radio" name="sa_geog" id="sa_geog_state_house" value="State House District" <?php checked( $geo_type, 'State House District' ); ?>> <label for="sa_geog_state_house">State House District</label></li>
				<li><input type="radio" name="sa_geog" id="sa_geog_state_senate" value="State Senate District" <?php checked( $geo_type, 'State Senate District' ); ?>> <label for="sa_geog_state_senate">State Senate District</label></li>
			</ul>
	</div>

	<div id="rightcolumn">
		<div id="states">
		<?php
		// Set up geographies
		// Get the terms, starting by finding the term_id of the very top level term.
		$geo_tax_top_level_term_id = get_geo_tax_top_level_term_id();

		// Populate States selectbox, states are direct descendants of the top level geography term.
		$state_args = array(
			'parent'     => $geo_tax_top_level_term_id,
			'hide_empty' => 0,
			'orderby'    => 'slug',
		);
		$state_terms = get_terms( 'geographies', $state_args );
		if ( $state_terms && ! is_wp_error( $state_terms ) ) {
			?>
			<p><select name="sa_state" id="sa_state" class="sa_state chosen-select" data-placeholder="Select your state." >
				<!-- Include an empty option for chosen.js support-->
				<option></option>
				<?php
				foreach ( $state_terms as $state_term ) {
					?>
					<option value="<?php echo $state_term->term_id; ?>" <?php selected( $geo_tax_state, $state_term->name ); ?>> <?php echo $state_term->name; ?></option>
					<?php
				}
				?>
			</select></p>
			<?php
		} else {
			print('no terms');
		}
		?>

		</div>
		<div id="moregeog">
			<div id="selgeog">
				<!-- If a selection exists (editing an existing policy), set up the option list on page load. We also need to be able to generate it on the fly in the case of a new policy. -->
				<p><select name="sa_selectedgeog" id="sa_selectedgeog" class="sa_selectedgeog chosen-select" data-placeholder="Select your geography.">
					<?php
					// Don't bother to try to load options if the geog value is empty or national or state.
					if ( ! empty( $geo_type ) && ! in_array( $geo_type, array( 'National', 'State' ) ) ) {
						$geog_str_prefix = sa_get_geography_prefix( $geo_type );
						$geo_search_slug = $geog_str_prefix . $geo_tax_state;
						$geoterm = get_term_by( 'slug', $geo_search_slug, 'geographies' );
						$args = array(
								'parent'     => $geoterm->term_id,
								'hide_empty' => 0,
								'orderby'    => 'slug',
						);
						$terms = get_terms( 'geographies', $args );

						if ( $terms && ! is_wp_error( $terms ) ) {
							?>
							<!-- Include an empty option for chosen.js support-->
							<option></option>
							<?php
							foreach ( $terms as $term ) {
								?>
								<option value="<?php echo $term->term_id; ?>" <?php selected( $geo_tax_id, $term->term_id ); ?>><?php echo $term->name; ?></option>
								<?php
							}
						}
					}
					?>
				</select></p>
			</div>
		</div>
	</div>
	<div style="clear:both;"></div>
	<?php
	}

	/**
	 * Build the policies meta box.
	 *
	 * @since    1.0.0
	 *
	 * @return   html
	 */
	public function sa_policy_meta_box( $post ) {
			$custom = get_post_custom($post->ID);
			$sapolicy_type = isset( $custom["sa_policytype"][0] ) ? $custom["sa_policytype"][0] : '';
			$sapolicy_stage = isset( $custom["sa_policystage"][0] ) ? $custom["sa_policystage"][0] : '';
			$pre1 = isset( $custom["sa_pre1"][0] ) ? $custom["sa_pre1"][0] : '';
			$pre2 = isset( $custom["sa_pre2"][0] ) ? $custom["sa_pre2"][0] : '';
			$pre3 = isset( $custom["sa_pre3"][0] ) ? $custom["sa_pre3"][0] : '';
			$dev1 = isset( $custom["sa_dev1"][0] ) ? $custom["sa_dev1"][0] : '';
			$dev2 = isset( $custom["sa_dev2"][0] ) ? $custom["sa_dev2"][0] : '';
			$dev3 = isset( $custom["sa_dev3"][0] ) ? $custom["sa_dev3"][0] : '';
			$enact1 = isset( $custom["sa_enact1"][0] ) ? $custom["sa_enact1"][0] : '';
			$enact2 = isset( $custom["sa_enact2"][0] ) ? $custom["sa_enact2"][0] : '';
			$enact3 = isset( $custom["sa_enact3"][0] ) ? $custom["sa_enact3"][0] : '';
			$post1 = isset( $custom["sa_post1"][0] ) ? $custom["sa_post1"][0] : '';
			$post2 = isset( $custom["sa_post2"][0] ) ? $custom["sa_post2"][0] : '';
			$post3 = isset( $custom["sa_post3"][0] ) ? $custom["sa_post3"][0] : '';
			$dateenacted = isset( $custom["sa_dateenacted"][0] ) ? $custom["sa_dateenacted"][0] : '';
			$dateimplemented = isset( $custom["sa_dateimplemented"][0] ) ? $custom["sa_dateimplemented"][0] : '';
			$emergencedatestg = isset( $custom["sa_emergencedate_stg"][0] ) ? $custom["sa_emergencedate_stg"][0] : '';
			$developmentdatestg = isset( $custom["sa_developmentdate_stg"][0] ) ? $custom["sa_developmentdate_stg"][0] : '';
			$enactmentdatestg = isset( $custom["sa_enactmentdate_stg"][0] ) ? $custom["sa_enactmentdate_stg"][0] : '';
			$implementationdatestg = isset( $custom["sa_implementationdate_stg"][0] ) ? $custom["sa_implementationdate_stg"][0] : '';
			$featured_video_url = isset( $custom["sa_featured_video_url"][0] ) ? $custom["sa_featured_video_url"][0] : '';

			if ( ! $sapolicy_type ){
					$ptdef = "---Select a Policy Type---";
			} else {
					$ptdef = $sapolicy_type;
			}

		// Add a nonce field so we can check for it later.
		wp_nonce_field( $this->nonce_name, $this->nonce_value );
	?>
	<!-- @TODO: switch types to a taxonomy
				Also use sensible select-->
			<h4>Type:</h4>
			<select name="sa_policytype">
				<option <?php selected( $sapolicy_type, "Legislation/Ordinance" ); ?> value="Legislation/Ordinance">Legislation/Ordinance</option>
				<option <?php selected( $sapolicy_type, "Resolution" ); ?> value="Resolution">Resolution</option>
				<option <?php selected( $sapolicy_type, "Tax Ordinance" ); ?> value="Tax Ordinance">Tax Ordinance</option>
				<option <?php selected( $sapolicy_type, "Internal Policy" ); ?> value="Internal Policy">Internal Policy</option>
				<option <?php selected( $sapolicy_type, "Executive Order" ); ?> value="Executive Order">Executive Order</option>
				<option <?php selected( $sapolicy_type, "Design Manual" ); ?> value="Design Manual">Plan</option>
				<option <?php selected( $sapolicy_type, "Design Manual" ); ?> value="Design Manual">Design Manual</option>
				<option <?php selected( $sapolicy_type, "Other" ); ?> value="Other">Other</option>
			</select>

		<label for="sa_featured_video_url" class="description"><h4>Featured video URL</h4>
			</label>
		<input type="text" id="sa_featured_video_url" name="sa_featured_video_url" value="<?php echo esc_attr( $featured_video_url ); ?>" size="75" /><br />
		<em>e.g.: http://www.youtube.com/watch?v=UueU0-EFido</em>
		<hr />

	<div id="leftcolumn2">
			<h4>Stage:</h4>
			<ul id="policy_stage_select">
				<li><input type="radio" name="sa_policystage" id="sa_policystage_pre_policy" value="emergence" <?php checked( $sapolicy_stage, 'emergence' ); ?> > <label for="sa_policystage_pre_policy">Emergence</label><br />
					<input type="text" id="sa_emergencedate_stg" name="sa_emergencedate_stg" placeholder="Emergence start date" value="<?php
									if ($emergencedatestg != "") {
											echo $emergencedatestg;
									}
						 ?>"/></li>

				<li><input type="radio" name="sa_policystage" id="sa_policystage_develop_policy" value="development" <?php checked( $sapolicy_stage, 'development' ); ?>> <label for="sa_policystage_develop_policy">Development</label><br />
					<input type="text" id="sa_developmentdate_stg" name="sa_developmentdate_stg" placeholder="Development start date" value="<?php
									if ($developmentdatestg != "") {
											echo $developmentdatestg;
									}
						 ?>"/></li>

				<li><input type="radio" name="sa_policystage" id="sa_policystage_enact_policy" value="enactment" <?php checked( $sapolicy_stage, 'enactment' ); ?>> <label for="sa_policystage_enact_policy">Enactment</label><br />
					<input type="text" id="sa_enactmentdate_stg" name="sa_enactmentdate_stg" placeholder="Enactment date" value="<?php
									if ($enactmentdatestg != "") {
											echo $enactmentdatestg;
									}
						 ?>"/></li>

				<li><input type="radio" name="sa_policystage" id="sa_policystage_post_policy" value="implementation" <?php checked( $sapolicy_stage, 'implementation' ); ?>> <label for="sa_policystage_post_policy">Implementation</label><br />
					<!-- <input type="text" id="sa_implementationdate_stg" placeholder="Enter date here" value="<?php
									if ($implementationdatestg != "") {
											echo $implementationdatestg;
									}
						 ?>"/> -->
						 <input id="sa_dateimplemented" name="sa_dateimplemented" placeholder="Implementation start date" value="<?php
									if ($dateimplemented != "") {
											echo $dateimplemented;
									}
						 ?>"></li>
			</ul>

	</div>
	<div id="rightcolumn2">
			<div id="morestage">

					<div id="prestage" class="policy_stage_details">
							<h4>Emergence</h4>
							<input type="checkbox" id="sa_pre1" name="sa_pre1" value='Describe Problem' <?php checked( $pre1, 'Describe Problem' ); ?> > <label for="sa_pre1">Describe Problem</label><br />

							<input type="checkbox" id="sa_pre2" name="sa_pre2" value='Study Causes and Consequences' <?php checked( $pre2, 'Study Causes and Consequences' ); ?>
										 > <label for="sa_pre2">Study Causes and Consequences</label><br />

							<input type="checkbox" id="sa_pre3" name="sa_pre3" value='Describe Trend and Spread of Issues' <?php checked( $pre3, 'Describe Trend and Spread of Issues' ); ?>> <label for="sa_pre1">Describe Trend and Spread of Issues</label><br />
					</div>

					<div id="developstage" class="policy_stage_details">
							<h4>Development</h4>
							<input type="checkbox" id="sa_dev1" name="sa_dev1" value='Promote Awareness' <?php checked( $dev1, 'Promote Awareness' ); ?>
										 > <label for="sa_dev1">Promote Awareness</label><br />

							<input type="checkbox" id="sa_dev2" name="sa_dev2" value='Re-frame Issues' <?php checked( $dev2, 'Re-frame Issues' ); ?>> <label for="sa_dev2">Re-frame Issues</label><br />

							<input type="checkbox" id="sa_dev3" name="sa_dev3" value='Mobilize Publics' <?php checked( $dev3, 'Mobilize Publics' ); ?>> <label for="sa_dev3">Mobilize Publics</label><br />
					</div>

					<div id="enactstage" class="policy_stage_details">
							<h4>Enactment</h4>
							<input type="checkbox" id="sa_enact1" name="sa_enact1" value='Create Advocacy' <?php checked( $enact1, 'Create Advocacy' ); ?>
										 > <label for="sa_enact1">Create Advocacy</label><br />
							<input type="checkbox" id="sa_enact2" name="sa_enact2" value='Frame Policy' <?php checked( $enact2, 'Frame Policy' ); ?>
										 > <label for="sa_enact2">Frame Policy</label><br />
							<input type="checkbox" id="sa_enact3" name="sa_enact3" value='Pass Policy or Legislation' <?php checked( $enact3, 'Pass Policy or Legislation' ); ?>
										 > <label for="sa_enact3">Pass Policy or Legislation</label><br />
							<label for="sa_dateenacted">Date Enacted <em>legal date</em><label><br />
							<input id="sa_dateenacted" name="sa_dateenacted" value="<?php
									if ($dateenacted != "") {
											echo $dateenacted;
									}
						 ?>"><br />
					</div>

					<div id="poststage" class="policy_stage_details">
							<h4>Implementation</h4>
							<input type="checkbox" id="sa_post1" name="sa_post1" value='Implement Policy' <?php checked( $post1, 'Implement Policy' ); ?>> <label for="sa_post1">Implement Policy</label><br />
							<input type="checkbox" id="sa_post2" name="sa_post2" value='Ensure Access and Equity' <?php checked( $post2, 'Ensure Access and Equity' ); ?>> <label for="sa_post2">Ensure Access and Equity</label><br />
							<input type="checkbox" id="sa_post3" name="sa_post3" value='Sustain, Change, Abandon'<?php checked( $post2, 'Sustain, Change, Abandon' ); ?>> <label for="sa_post3">Sustain, Change, Abandon</label><br />
							<!-- <label for="sa_dateimplemented">Date Implemented</label><br /> -->
							<!-- <input id="sa_dateimplemented" name="sa_dateimplemented" value="<?php
									if ($dateimplemented != "") {
											echo $dateimplemented;
									}
						 ?>"> --><br />
					</div>
			</div>
	</div>
	<div style="clear:both"></div>


	<script type="text/javascript">
			//Show and hide appropriate stage divs
	jQuery(document).ready(function(){

				refresh_sa_policy_stage_vis_setting();

				//On click, refresh the visibility. Hide them all, then show the selected one
				jQuery('#policy_stage_select input').on( 'change', function() {
					refresh_sa_policy_stage_vis_setting();
					} );
	});

	function refresh_sa_policy_stage_vis_setting() {
		//First, hide them all, then show the one that is selected
		jQuery('.policy_stage_details').hide();
				var visible_stage_div = jQuery('#policy_stage_select').find('input:checked').val();
				// console.log(visible_stage_div);
				switch (visible_stage_div){
					case "emergence":
								jQuery('#prestage').toggle();
						break;
					case "development":
								jQuery('#developstage').toggle();
						break;
					case "enactment":
								jQuery('#enactstage').toggle();
						break;
					case "implementation":
								jQuery('#poststage').toggle();
						break;
					}
	 }

	</script>

	<script type="text/javascript">
	//Handle the geography input form
	jQuery(document).ready(function(){
		// On page load, update the inputs that are enabled
		refresh_sa_policy_enable_geog_inputs();
		// Bind the chosen.js library to the inputs.
		jQuery( '.chosen-select' ).chosen({});

		// On change, refresh the option list and option list visibility
		// The page load setup is handled via php, so the js only has to handle the updates
		jQuery('#sa_geog_select').on( 'change', function() {
			refresh_sa_policy_enable_geog_inputs();
			refresh_sa_policy_geographies();
		});

		jQuery('#sa_state').chosen().change( function() {
			refresh_sa_policy_geographies();
		});
	});

	function refresh_sa_policy_enable_geog_inputs() {
		//First, disable the inputs, then enable the needed inputs
		jQuery('#sa_state,#sa_selectedgeog').chosen().prop('disabled', true);

		var sa_major_geography = jQuery('#sa_geog_select').find('input:checked').val();
			switch ( sa_major_geography ) {
				case ( undefined ):
				case ('National'):
					//Leave inputs disabled
					break;
				case ('State'):
					jQuery('#sa_state').chosen().prop('disabled', false);
					break;
				default:
					jQuery('#sa_state,#sa_selectedgeog').chosen().prop('disabled', false);
			}

	}

	function refresh_sa_policy_geographies() {
		//First, hide them all, then show the one that is selected
		// jQuery('.policy_stage_details').hide();
				var sa_major_geography = jQuery('#sa_geog_select').find('input:checked').val();
				var sa_state_geography = jQuery("#sa_state").chosen().val();
				// console.log(sa_major_geography);
				// console.log(sa_state_geography);

					switch (sa_major_geography) {
						case ( undefined ):
							//Nothing selected, hold tight
							break;
						case ('National'):
						case ('State'):
							// jQuery("#sa_finalgeog,#sa_latitude,#sa_longitude").val('');
							break;
						default:
						//Fetch the subdivisions if they're needed.
							if ( sa_state_geography !== "" ) {

									 //Getting the geographies list via WP AJAX request
										var data = {
											action: 'get_geographies_list',
											// post_attachment_to_delete: <?php echo $post->ID; ?>,
											selstate: sa_state_geography,
											geog: sa_major_geography,
											security: "<?php echo wp_create_nonce( 'get_geographies_list' ); ?>"
										};
									 // since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
										jQuery.post(
													ajaxurl,
													data,
													function(response) {
														// console.log(response)
														if ( response != -1 ) {
															//If we got a response, update the geography select.
														 jQuery("#sa_selectedgeog").html(response);
														 jQuery('#sa_selectedgeog').trigger("chosen:updated");
														}
													}
												);
							 }
							 break;
						 }
	 }

	</script>

	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#sa_dateenacted").datepicker();
			jQuery("#sa_dateimplemented").datepicker();
			jQuery("#sa_emergencedate_stg").datepicker();
			jQuery("#sa_developmentdate_stg").datepicker();
			jQuery("#sa_enactmentdate_stg").datepicker();
			// jQuery("#sa_implementationdate_stg").datepicker();
		});
	</script>

	<?php }

	/**
	 * Save policies extra meta.
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
			return;
		}

		// Save policy meta
		$meta_fields = array( 'sa_policytype', 'sa_policystage', 'sa_pre1', 'sa_pre2', 'sa_pre3', 'sa_dev1', 'sa_dev2', 'sa_dev3', 'sa_enact1', 'sa_enact2', 'sa_enact3', 'sa_post1', 'sa_post2', 'sa_post3', 'sa_dateenacted', 'sa_dateimplemented', 'sa_emergencedate_stg', 'sa_developmentdate_stg', 'sa_enactmentdate_stg', 'sa_featured_video_url' );
		$meta_success = $this->save_meta_fields( $post_id, $meta_fields );

		// Save Geography terms
		//Try to save the more specific option first
		if ( ! empty( $_POST["sa_selectedgeog"] ) ) {
			$this->save_taxonomy_field( $post_id, 'sa_selectedgeog', 'geographies' );
		} elseif ( ! empty( $_POST["sa_state"] ) ) {
			//Save the state term if a more specific term isn't set
			$this->save_taxonomy_field( $post_id, 'sa_state', 'geographies' );
		} else {
			//if that fails, set the terms as 'national'
			$term_ids = array( intval( get_geo_tax_top_level_term_id() ) );
			wp_set_object_terms( $post_id, $term_ids, 'geographies' );
		}

		do_action( 'sapolicies_after_save', $post_id );
	}

	/**
	 * Register SA Policies activity actions.
	 *
	 * @since 1.0.0
	 */
	function sapolicies_register_activity_actions() {
		bp_activity_set_action(
			$this->post_type,
			'sapolicy_created',
			__( 'Published a Change', $this->post_type ),
			array( $this, 'format_activity_action_sapolicy_created' )
		);
	}
	/**
	 * Format 'sapolicies_created' activity actions.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Activity action.
	 * @param object $activity Activity object.
	 * @return string
	 */
	function format_activity_action_sapolicy_created( $action, $activity ) {
		$user_link = bp_core_get_userlink( $activity->user_id );
		$post_id = $activity->secondary_item_id;

		$group = groups_get_group( array( 'group_id' => sa_get_group_id() ) );
		$group_url  = bp_get_group_permalink( $group );
		$group_link = '<a href="' . $group_url . '">' . $group->name . '</a>';

		$post_url = get_permalink( $post_id );
		$post_link = sprintf( '<a href="%s">%s</a>', $post_url, get_the_title( $post_id ) );

		$action = sprintf( __( '%1$s published the change %2$s in the Hub %3$s', $this->plugin_slug ), $user_link, $post_link, $group_link );

		return $action;
	}

 	/**
	 * Change the REST API response so that it includes important meta for policies.
	 *
	 * @since    1.8.2
	 *
	 * @return   void
	 */
	public function rest_read_meta() {
	    register_rest_field( $this->post_type,
	        'geoid',
	        array(
	            'get_callback'    => array( $this, 'rest_get_policy_info' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	    register_rest_field( $this->post_type,
	        'stage',
	        array(
	            'get_callback'    => array( $this, 'rest_get_policy_info' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	    register_rest_field( $this->post_type,
	        'type',
	        array(
	            'get_callback'    => array( $this, 'rest_get_policy_info' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

 	/**
	 * Get extra info to include in the response.
	 *
	 * @param array $object Details of current post.
	 * @param string $field_name Name of field.
	 * @param WP_REST_Request $request Current request
	 *
	 * @return mixed
	 */
	public function rest_get_policy_info( $object, $field_name, $request ) {
		switch ( $field_name ) {
			case 'geoid':
				$terms = get_the_terms( $object[ 'id' ], 'geographies' );
		        $value = ( ! empty( $terms ) ) ? current( $terms )->description : '01000US';
				break;
			case 'type' :
				// These are all title case.
				$value = get_post_meta( $object[ 'id' ], 'sa_policytype', true );
			break;
			case 'stage' :
				// These are all lowercase.
				$stage = get_post_meta( $object[ 'id' ], 'sa_policystage', true );
				$value = ucfirst( $stage );
			break;
			default:
				$value = '';
				break;
		}

	    return $value;
	}

} //End class CC_SA_Policies_CPT_Tax
$sa_policies_cpt_tax = new CC_SA_Policies_CPT_Tax();

add_action( 'wp_ajax_get_geographies_list', 'ajax_get_geographies_list' );
function ajax_get_geographies_list() {
		// global $wpdb;

		if( wp_verify_nonce( $_REQUEST['security'], 'get_geographies_list' ) ) {

			$selstate = (int) $_POST['selstate'];
			$result = '';

			if ( $selstate ) {
				//get the selected state slug
				$state_term = get_term_by('id', $selstate, 'geographies');
				//Trim the "-state" from the end of the state slug
				$state_clean = substr( $state_term->slug, 0, -6);

				if ( ! empty( $_POST['geog'] ) ) {
					$thisid = sa_get_geography_prefix( $_POST['geog'] ) . $state_clean;
					$geoterm = get_term_by('slug', $thisid, 'geographies');
					$args = array(
						'parent'     => $geoterm->term_id,
						'hide_empty' => 0,
						'orderby'    => 'slug',
					);
					$terms = get_terms( 'geographies', $args );
					if ( $terms ) {
						$result .= '<option value="' . $term->term_id . '">' . $term->name . '</option> ';
						foreach ( $terms as $term ) {
							$result .= '<option value="' . $term->term_id . '">' . $term->name . '</option> ';
						}
					}
				}
			}
			//return the result
			die( $result );

		} else {
			die('-1');
		}
}

function sa_get_geography_prefix($geog){
	 switch ($geog) {
		 case 'County':
		 $geog_str_prefix="counties-";
			 break;
		case 'City':
		 $geog_str_prefix="cities-";
			 break;
		 case 'School District':
		$geog_str_prefix="schooldistricts-";
			 break;
		 case 'US Congressional District':
		$geog_str_prefix="uscongressionaldistricts-";
			 break;
		 case  'State House District':
		$geog_str_prefix="statehousedistricts-";
			 break;
		 case 'State Senate District':
		$geog_str_prefix="statesenatedistricts-";
			 break;
	 }
	 return $geog_str_prefix;
}

function get_geo_tax_top_level_term_id() {
	//The top level term is the only term in geographies with a parent of 0
	$geo_starting_point = array(
									'parent' => 0,
									'hide_empty' => 0
									);
	$top_level_geo = get_terms( 'geographies', $geo_starting_point );

	return (int) $top_level_geo[0]->term_id;
}


function cc_get_the_geo_tax_type( $post_id = 0 ) {
    if ( empty( $post_id ) ) {
        $post_id = get_the_ID();
    }
    // Set default return value.
    $geo_type = 'National';

	// Get the Geography term for this post
	$geo_tax = wp_get_object_terms( $post_id, 'geographies' );

	//Figure out which level of geography we're dealing with here. Get the term's parent, which will give us the type of geography.
	if ( ! empty( $geo_tax ) ) {
		$geo_term_parent = get_term_by( 'id', $geo_tax[0]->parent, 'geographies' );
		// Possible Values of $geo_term_parent->name:
		// United States (parent term of all states)
		// Counties
		// Cities
		// School Districts
		// US Congressional Districts
		// State House Districts
		// State Senate Districts
		if ( ! empty( $geo_term_parent->name ) ) {
			switch ( $geo_term_parent->name ) {
				case 'United States':
					$geo_type = 'State';
					break;
				case 'Counties':
					$geo_type = 'County';
					break;
				case 'Cities':
					$geo_type = 'City';
					break;
				case 'School Districts':
					$geo_type = 'School District';
					break;
				case 'US Congressional Districts':
					$geo_type = 'US Congressional District';
					break;
				case 'State House Districts':
					$geo_type = 'State House District';
					break;
				case 'State Senate Districts':
					$geo_type = 'State Senate District';
					break;
				default:
					$geo_type = 'National';
					break;
			}
		}
	}

	return $geo_type;
}

/**
 * Get the human-readable name for a geography.
 *
 * @since   1.0.0
 *
 * @return  string
 */
function cc_get_the_geo_tax_name( $post_id = 0 ){
	if ( empty( $post_id ) ) {
        $post_id = get_the_ID();
    }
	$geo_tax = wp_get_object_terms( $post_id, 'geographies' );

	return $geo_tax[0]->name;
}

/**
 * Get the human-readable name of the state that contains a geography.
 *
 * @since   1.0.0
 *
 * @return  string
 */
function cc_get_the_geo_tax_state( $post_id = 0 ){
	if ( empty( $post_id ) ) {
        $post_id = get_the_ID();
    }
	$geo_tax = wp_get_object_terms( $post_id, 'geographies' );
	$geo_tax_type = cc_get_the_geo_tax_type( $post_id );

	switch ( $geo_tax_type ) {
		case 'State':
			$geo_tax_state = $geo_tax[0]->name;
			break;
		case 'County':
		case 'City':
		case 'School District':
		case 'US Congressional District':
		case 'State House District':
		case 'State Senate District':
			$geo_parent_term = get_term_by( 'id', $geo_tax[0]->parent, 'geographies' );
			$geo_grandparent_term = get_term_by( 'id', $geo_parent_term->parent, 'geographies' );
			$geo_tax_state = $geo_grandparent_term->name;
			break;
		default:
			$geo_tax_state = '';
			break;
	}

	return $geo_tax_state;

}

/**
 * Output policy search form and build search results.
 *
 * @since   1.0.0
 *
 * @return  html
 */
function sa_searchpolicies() {
	?>
	<div id="cc-adv-search" class="clear">
		<form action="<?php echo sa_get_section_permalink( $section = 'policies' ) . 'search'; ?>" method="GET" enctype="multipart/form-data" name="sa_ps">
			<div class="row">
				<input type="text" id="saps" name="keyword" Placeholder="Enter search terms here" value="<?php
					if ( isset( $_REQUEST['keyword'] ) ) {
						echo $_REQUEST['keyword'];
					} elseif ( isset($_GET['qs'] ) ) {
						echo $_GET['qs'];
					}
				?>" />
				<input id="searchsubmit" type="submit" alt="Search" value="Search" />
			</div>

			<a role="button" id="cc_advanced_search_toggle" class="clear" >+ Advanced Search</a>
				<div id="cc-adv-search-pane-container" class="row clear">
					<div class="cc-adv-search-option-pane third-block">
						<h4>Topic Area</h4>
						<ul>
							<?php
							$ATterms = get_terms('sa_advocacy_targets');
							foreach ($ATterms as $ATterm) {
								echo '<li><input type="checkbox" name="sa_advocacy_targets[]" id="sa_advocacy_targets_' . $ATterm->term_id . '" value="' . $ATterm->term_id . '" /> <label for="sa_advocacy_targets_' . $ATterm->term_id . '">' . $ATterm->name . '</label></li>';
							}
							?>
						</ul>
					</div> <!-- End .cc-adv-search-option-pane -->

					<div class="cc-adv-search-option-pane third-block">
						<h4>Stage of Change</h4>
						<ul>
							<li><input type="checkbox" name="sa_policystage[]" id="policy-stage-emergence" value="emergence" /> <label for="policy-stage-emergence">Emergence</label></li>
							<li><input type="checkbox" name="sa_policystage[]" id="policy-stage-develop" value="development" /> <label for="policy-stage-develop">Development</label></li>
							<li><input type="checkbox" name="sa_policystage[]" id="policy-stage-enact" value="enactment" /> <label for="policy-stage-enact">Enactment</label></li>
							<li><input type="checkbox" name="sa_policystage[]" id="policy-stage-implement" value="implementation" /> <label for="policy-stage-implement">Implementation</label></li>
						</ul>
					</div> <!-- End .cc-adv-search-option-pane -->

					<div class="cc-adv-search-option-pane third-block">
						<h4>Tags</h4>
						<?php $sat_args = array('orderby' => 'count', 'order' => 'DESC');
						$sapolicytags = get_terms('sa_policy_tags', $sat_args);
						?>
						<div class="cc-adv-search-scroll-container">
						<ul>
							<?php
							foreach ( $sapolicytags as $sapolicytag ) {
								echo '<li><input type="checkbox" name="sa_policy_tags[]" id="sa_policy_tag_' .  $sapolicytag->term_id . '" value="' . $sapolicytag->term_id . '" /> <label for="sa_policy_tag_' . $sapolicytag->term_id . '">' . $sapolicytag->name . ' (' . $sapolicytag->count . ')</label></li>';
							}
							?>
						</ul>
						</div> <!-- End scroll container -->
					</div> <!-- End .cc-adv-search-option-pane -->
				</div> <!-- End .cc-adv-search-pane-container -->
			</div> <!-- End .row -->
		</form>
	</div> <!-- End #cc-adv-search -->

	<script type="text/javascript">
			var $j = jQuery.noConflict();

			$j(document).ready(function(){

				 $j('#cc-adv-search-pane-container').hide();
				 $j('#cc_advanced_search_toggle').click(function(){
							$j('#cc-adv-search-pane-container').slideToggle('fast');
							if ($j("#cc_advanced_search_toggle").text() == "+ Advanced Search") {
									$j("#cc_advanced_search_toggle").text("- Advanced Search");
							}
							else {
									$j("#cc_advanced_search_toggle").text("+ Advanced Search");
							}
				 });

			});

	</script>

	<?php
	if ( sa_is_archive_search() ) {
		$post_type = 'sapolicies';
		$taxonomies = array( 'sa_advocacy_targets', 'sa_policy_tags' );
		$metas = array( 'sa_policystage' );
		$filter_args = sa_build_search_query( $post_type, $taxonomies, $metas );
		// echo '<pre>';
		// print_r($filter_args);
		// echo '</pre>';

		$policy_search = new WP_Query( $filter_args );

		if ( $policy_search->have_posts() ) {
			echo '<div class="row">';
			echo '<h3 class="screamer sapurple">Search Results</h3>';
			while( $policy_search->have_posts() ) :
				$policy_search->the_post();
				bp_get_template_part( 'groups/single/sapolicies/policy-short' );
			endwhile;
			echo '</div>';
			sa_section_content_nav( 'nav-below', $policy_search->max_num_pages );
		} else {
			echo "No Results - Search criteria too specific";
		}
	}
}

/**
 * Output location search form.
 *
 * @since   1.0.0
 *
 * @return  html
 */
function sa_location_search() {
	?>
	<h3 class="screamer sapurple">Search for Changes in Progress by Location</h3>
	<div>
		<form method="GET" action="http://maps.communitycommons.org/policymap/" name="sa_ls" enctype="multipart/form-data">
				<input type="text" id="address" placeholder="Mosinee, Wisconsin" name="address" />
				<input type="submit" name="submit" value="Search"/>
		</form>
		<a href="http://maps.communitycommons.org/policymap/"><img src="<?php echo sa_get_plugin_base_uri(); ?>public/images/policy-map-2015x960.png" class="alignnone" alt="Use the maproom to find changes in your area." style="margin:1.4em 0;"></a>
	</div>
<?php
}
/*
//Make the menus reflect where we are
//Apply current-menu-item class to nav items when child pages, related tax or CPT is active
add_filter('nav_menu_css_class' , 'cc_filter_nav_class' , 10 , 2);
function cc_filter_nav_class($classes, $item){

		 if ( ( is_child(150) || is_singular( 'sapolicies' ) ) && $item->title == "Changes in Progress" )
				$classes[] = "current-menu-item";

		 return $classes;
}

// Some query filters for archive pages
// add_action('pre_get_posts', 'sa_taxonomy_filter_queries', 9999);
function sa_taxonomy_filter_queries( $query ) {

		// Show only policies for the main query
		if( is_tax( 'sa_advocacy_targets' ) && ( !is_admin() ) && ( $query->is_main_query() )  ) {

				$query->set('post_type', 'sapolicies');
		}

}
*/
/**
 * Print the correct color (css class) based on the policy taxonomy term .
 *
 * @since   1.0.0
 *
 * @return  string
 */
function sa_the_topic_color ( $tax_term ) {
	echo sa_get_topic_color( $tax_term );
}
	function sa_get_topic_color( $tax_term ) {
		switch ( $tax_term ) {
					case 'sa-active-play':
					case 'sa-healthier-schools':
							$topic_color = 'sayellow';
							break;
					case 'sa-active-spaces':
							$topic_color = 'sablue';
							break;
					case 'sa-better-food-in-neighborhoods':
							$topic_color = 'saorange';
							break;
					case 'sa-healthier-marketing':
					case 'sa-healthy-weight':
							$topic_color = 'sapink';
							break;
					case 'sa-healthier-school-snacks':
					case 'sa-health-equity':
							$topic_color = 'sagreen';
							break;
					case 'sa-sugary-drinks':
							$topic_color = 'sapurple';
							break;
					default:
							$topic_color = 'saorange';
							break;
										}
		return $topic_color;
	}

//Utility/helper functions

/**
 * Helps identify when we're working on a salud page.
 *
 * @since   1.0.0
 *
 * @return  bool
 */
//
// @TODO: This will be out of date.
function cc_is_salud_page() {

	$return = false;

	if ( is_page_template( 'page-templates/salud-america.php' )
			|| is_page_template( 'page-templates/salud-america-eloi.php' )
			|| is_singular('sapolicies')
			|| is_singular('saresources')
			|| is_singular('sa_success_story')
			|| is_tax('sa_advocacy_targets')
			|| is_tax('sa_resource_cat')
			|| is_tax('sa_policy_tags')
			|| is_post_type_archive('sa_success_story')
			|| is_post_type_archive('saresources')
			|| is_post_type_archive('sapolicies')
			) {
		$return = true;
	}

	return apply_filters( 'cc_is_salud_page', $return );

}

// Add "salud-america" as an interest if the registration originates from an SA page
// Filters array provided by registration_form_interest_query_string
// @returns array with new element (or not)
add_filter( 'registration_form_interest_query_string', 'salud_add_registration_interest_parameter', 11, 1 );
function salud_add_registration_interest_parameter( $interests ) {

		if ( cc_is_salud_page() )
			$interests[] = 'salud-america';

		return $interests;
}

/**
 * Extends the default WordPress body class
 * Filter classes added to body tag to add "salud" if on a Salud America page.
 *
 * @param array Existing class values.
 * @return array Filtered class values.
 */
/*
***************/
function cc_sa_custom_body_class( $classes ) {

		if ( cc_is_salud_page() ) {
				$classes[] = 'salud-america';
				if ( ($key = array_search('full-width', $classes) ) !== false ) {
					unset( $classes[$key] );
				}
			}

	return $classes;
}
add_filter( 'body_class', 'cc_sa_custom_body_class', 99 );

/**
 * Template tag that outputs the structure for the policy tracker progress bar
 *
 * @param string Progress value.
 * @return html
 */
function cc_the_policy_progress_tracker( $progress ) {

	switch ( $progress ) {
		case "emergence":
			$percentage = 25;
			$progress_label = 'in emergence';
			break;
		case "development":
			$percentage = 50;
			$progress_label = 'in development';
			break;
		case "enactment":
			$percentage = 75;
			$progress_label = 'enacted';
			break;
		case "implementation":
			$percentage = 100;
			$progress_label = 'in implementation';
			break;
		default:
			$percentage = 0;
			$progress_label = 'in emergence';
		 break;
		}
	?>
	<div class="meter-box clear">
		<p class="visible-mini">This change is <a href="/groups/salud-america/pages/the-science-behind-healthy-change/" title="More information about policy development"><?php echo $progress_label; ?></a>.</p>
		<div class="visible-maxi">
			<h5 style="margin-bottom:0;">Where does this issue stand?</h5>
			<p><a href="/groups/salud-america/what-is-change/the-science-behind-healthy-change/">Learn more</a> about the steps behind healthy policy changes!</p>
			<ol class="progtrckr" data-progtrckr-steps="4">
				<li class="<?php echo ( in_array( $progress, array('emergence', 'development', 'enactment', 'implementation') ) ) ? "progtrckr-done" : "progtrckr-todo"; ?>"><a href="/groups/salud-america/what-is-change/the-science-behind-healthy-change/">Emergence</a></li><!--
				--><li class="<?php echo ( in_array( $progress, array('development', 'enactment', 'implementation') ) ) ? "progtrckr-done" : "progtrckr-todo"; ?>"><a href="/groups/salud-america/what-is-change/the-science-behind-healthy-change/">Development</a></li><!--
				--><li class="<?php echo ( in_array( $progress, array('enactment', 'implementation') ) ) ? "progtrckr-done" : "progtrckr-todo"; ?>"><a href="/groups/salud-america/what-is-change/the-science-behind-healthy-change/">Enactment</a></li><!--
				--><li class="<?php echo ( in_array( $progress, array('implementation') ) ) ? "progtrckr-done" : "progtrckr-todo"; ?>"><a href="/groups/salud-america/what-is-change/the-science-behind-healthy-change/">Implementation</a></li>
			</ol>
		</div>
	</div> <!-- end .meter-box -->
	<?php
}
/* Filter the page title for certain Salud America page.
*  filters value in wp_title
*/
// add_filter( 'wp_title', 'cc_salud_title_filter', 20, 2 );
function cc_salud_title_filter( $title, $sep ) {

	if ( is_feed() )
		return $title;

	if ( is_page( 'take-action' ) )
		$title = get_the_title() . ' ' . $sep . ' Salud America' ;

	return $title;
}