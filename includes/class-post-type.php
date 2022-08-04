<?php
/**
 * Custom post type class.
 *
 * Handles registering the post type and procesing for metabox data display and updates.
 *
 * @package Scrap_Enquiry
 */

namespace MJE\ScrapEnquiry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class
 */
class Post_Type {

	/**
	 * Add hook
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'gutenberg_can_edit_post_type' ), 10, 2 );
		add_filter( 'manage_scrap_inquiry_posts_columns', array( __CLASS__, 'set_columns' ) );
		add_action( 'manage_scrap_inquiry_posts_custom_column', array( __CLASS__, 'get_column_data' ), 10, 2 );
		add_action( 'manage_edit-scrap_inquiry_sortable_columns', array( __CLASS__, 'sortable_column' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'do_meta_boxes', array( __CLASS__, 'hide_publish_box' ) );
		add_action( 'transition_post_status', array( __CLASS__, 'transition_post_status' ), 5, 3 );
		add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 3 );
	}

	/**
	 * Register post type
	 *
	 * @return void
	 */
	public static function register_post_type() {

		register_post_type(
			'scrap_inquiry',
			array(
				'labels'              => array(
					'name'                  => __( 'Scrap enquiries', 'scrap-enquiry' ),
					'singular_name'         => __( 'Scrap enquiry', 'scrap-enquiry' ),
					'all_items'             => __( 'All Scrap Enquiries', 'scrap-enquiry' ),
					'menu_name'             => _x( 'Scrap enquiries', 'Admin menu name', 'scrap-enquiry' ),
					'add_new'               => __( 'Add New', 'scrap-enquiry' ),
					'add_new_item'          => __( 'Add new scrap enquiry', 'scrap-enquiry' ),
					'edit'                  => __( 'Edit', 'scrap-enquiry' ),
					'edit_item'             => __( 'Edit scrap enquiry', 'scrap-enquiry' ),
					'new_item'              => __( 'New scrap enquiry', 'scrap-enquiry' ),
					'view_item'             => __( 'View scrap enquiry', 'scrap-enquiry' ),
					'view_items'            => __( 'View scrap enquiries', 'scrap-enquiry' ),
					'search_items'          => __( 'Search Scrap enquiries', 'scrap-enquiry' ),
					'not_found'             => __( 'No scrap enquiries found', 'scrap-enquiry' ),
					'not_found_in_trash'    => __( 'No scrap enquirie found in trash', 'scrap-enquiry' ),
					'parent'                => __( 'Parent scrap enquiry', 'scrap-enquiry' ),
					'featured_image'        => __( 'Scrap enquiry image', 'scrap-enquiry' ),
					'set_featured_image'    => __( 'Set scrap enquiry image', 'scrap-enquiry' ),
					'remove_featured_image' => __( 'Remove scrap enquiry image', 'scrap-enquiry' ),
					'use_featured_image'    => __( 'Use as scrap enquiry image', 'scrap-enquiry' ),
					'insert_into_item'      => __( 'Insert into scrap enquiry', 'scrap-enquiry' ),
					'uploaded_to_this_item' => __( 'Uploaded to this scrap enquiry', 'scrap-enquiry' ),
					'filter_items_list'     => __( 'Filter scrap enquiries', 'scrap-enquiry' ),
					'items_list_navigation' => __( 'Scrap enquiry navigation', 'scrap-enquiry' ),
					'items_list'            => __( 'Scrap enquiry list', 'scrap-enquiry' ),
				),
				'description'         => __( 'This is where you can add new scrap enquiries.', 'scrap-enquiry' ),
				'public'              => false,
				'show_ui'             => true,
				'capability_type'     => 'product',
				'capabilities'        => array(
					'create_posts' => false, // Removes support for the "Add New" menu and related function.
				),
				'map_meta_cap'        => true,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'hierarchical'        => false,
				'query_var'           => true,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
			)
		);
	}

	/**
	 * Disable Gutenberg editor for scrap enquiries.
	 *
	 * @param bool   $can_edit Whether the post type can be edited.
	 * @param string $post_type The post type being checked.
	 * @return bool
	 */
	public static function gutenberg_can_edit_post_type( $can_edit, $post_type ) {
		return 'scrap_inquiry' === $post_type ? false : $can_edit;
	}

	/**
	 * Add column for status display in the list of enquiries
	 *
	 * @param array $columns Array of columns.
	 * @return array
	 */
	public static function set_columns( $columns ) {
		$saved_columns     = $columns;
		$columns           = array();
		$columns['cb']     = '<input type="checkbox" />';
		$columns['id']     = __( 'ID', 'scrap-enquiry' );
		$columns           = array_merge( $columns, $saved_columns );
		$columns['status'] = __( 'Status', 'scrap-enquiry' );
		return $columns;
	}

	/**
	 * Set status column text
	 *
	 * @param string  $column Column to get data for.
	 * @param boolean $post_id Post ID number.
	 * @return void
	 */
	public static function get_column_data( $column, $post_id ) {
		global $post;

		switch ( $column ) {
			case 'id':
				$id_url = '<a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '">' . $post_id . '</a>';
				echo $id_url; // phpcs:ignore
				break;
			case 'status':
				echo esc_html( Post_Statuses::$statuses[ $post->post_status ]['label'] );
				break;
			default:
				break;
		}
	}

	/**
	 * Make ID column sortable
	 *
	 * @param array $columns Array of columns that are sortable.
	 * @return array
	 */
	public static function sortable_column( $columns ) {
		$columns['id'] = 'id';
		return $columns;
	}

	/**
	 * Remove the Publish meta box from our custom post type edit page
	 *
	 * @return void
	 */
	public static function hide_publish_box() {
		remove_meta_box( 'submitdiv', 'scrap_inquiry', 'side' );
	}

	/**
	 * Add metaboxes to our custom post type
	 *
	 * @return void
	 */
	public static function add_meta_boxes() {
		add_meta_box( 'enquiry-data', 'Data', array( __CLASS__, 'metabox_display_items' ), 'scrap_inquiry', 'normal', 'high' );
		add_meta_box( 'enquiry-status', 'Status', array( __CLASS__, 'metabox_display_status' ), 'scrap_inquiry', 'side', 'high' );
	}

	/**
	 * Render "status update" meta box data
	 *
	 * @return void
	 */
	public static function metabox_display_status() {
		global $post;

		$statuses = Post_Statuses::$statuses;

		?>
		<p>
			<select id="post_status" name="post_status" style="width:100%">
				<?php foreach ( Post_Statuses::$statuses as $slug => $values ) { ?>
					<option value="<?php echo esc_html( $slug ); ?>" <?php selected( $slug, $post->post_status ); ?>>
						<?php echo esc_html( $values['label'] ); ?>
					</option>
				<?php } ?>
			</select>
		</p>
		<div style="text-align:right">
			<input type="submit" name="save" id="update" class="button button-primary button-large" value="<?php esc_html_e( 'Update', 'scrap-enquiry' ); ?>">
		</div>
		<?php
	}

	/**
	 * Render meta box data
	 *
	 * @return void
	 */
	public static function metabox_display_items() {
		global $post;
		wp_enqueue_script( 'scrap-enquiry-admin', SCRAP_INQUIRY_PLUGIN_URL . 'assets/js/scrap-enquiry-admin.js', 'jquery', '1.0', true );

		$meta = get_post_meta( $post->ID );

		unset( $meta['_edit_lock'] );

		?>
		<table style="margin-bottom: 2rem">
		<?php
		foreach ( $meta as $key => $value ) {
			if ( 'items' === $key || 'cheque_or_bacs' === $key ) {
				continue;
			}

			if ( ! in_array( $key, Scrap_Enquiry::$expected_fields, true ) ) {
				continue;
			}

			if ( 'image' === $key ) {
				$value[0] = maybe_unserialize( $value[0] );
				if ( ! empty( $value[0] ) ) {
					$url = str_replace( $_SERVER['DOCUMENT_ROOT'], site_url(), $value[0][0] ); // phpcs:ignore
					echo '<tr>
							<td style="font-weight:bold">' . esc_html( __( 'Image', 'scrap-enquiry' ) ) . '</td>
							<td><a href="' . esc_html( $url ) . '" target="_blank">' . esc_html( $url ) . '</a></td>
						</tr>';
				} else {
					echo '<tr>
							<td style="font-weight:bold">' . esc_html( __( 'Image', 'scrap-enquiry' ) ) . '</td>
							<td>' . esc_html( __( 'None', 'scrap-enquiry' ) ) . '</td>
						</tr>';
				}
			} else {
				echo '<tr>
						<td style="font-weight:bold">' . esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . '</td>
						<td>' . esc_html( $value[0] ) . '</td>
					</tr>';
			}
		}
		?>
		</table>

		<?php

		$meta['items'] = maybe_unserialize( $meta['items'][0] );

		if ( empty( $meta['items'] ) ) {
			esc_html_e( 'No items found in this enquiry', 'scrap-enquiry' );
			return;
		}

		?>
		<table class="widefat scrap_items_table">
			<thead>
				<th><?php esc_html_e( 'Name', 'scrap-enquiry' ); ?></th>
				<th><?php esc_html_e( 'Weight/Quanity', 'scrap-enquiry' ); ?></th>
				<th><?php esc_html_e( 'Value', 'scrap-enquiry' ); ?></th>
				<th><?php esc_html_e( 'Action', 'scrap-enquiry' ); ?></th>
			</thead>
			<tbody>
				<?php
				$sum = 0;
				foreach ( $meta['items'] as $key => $data ) {
					$sum         += floatval( $data['value'] );
					$is_arbitrary = empty( intval( scrap_calculator( $data['name'] ) ) ) && empty( intval( krugerrand_calculator( $data['name'] ) ) ) && empty( intval( sovereigns_calculator( $data['name'] ) ) ) && empty( intval( gold_bar_24ct( $data['name'] ) ) );
					?>
					<tr>
						<td>
							<?php if ( $is_arbitrary ) { ?>
								<input type="text" name="items[name][]" value="<?php echo esc_html( $data['name'] ); ?>">
							<?php } else { ?>
							<select name="items[name][]" data-id="1" class="scraps-dropdown scrap_item_name form-control">
								<optgroup label="Gold">
									<option data-value="<?php echo esc_attr( scrap_calculator( '9ct Scrap Gold' ) ); ?>" <?php selected( $data['name'], '9ct Scrap Gold', true ); ?> value="<?php esc_html_e( '9ct Scrap Gold', 'scrap-enquiry' ); ?>"><?php esc_html_e( '9ct Scrap Gold', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( '14ct Scrap Gold' ) ); ?>" <?php selected( $data['name'], '14ct Scrap Gold', true ); ?> value="<?php esc_html_e( '14ct Scrap Gold', 'scrap-enquiry' ); ?>"><?php esc_html_e( '14ct Scrap Gold', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( '18ct Scrap Gold' ) ); ?>" <?php selected( $data['name'], '18ct Scrap Gold', true ); ?> value="<?php esc_html_e( '18ct Scrap Gold', 'scrap-enquiry' ); ?>"><?php esc_html_e( '18ct Scrap Gold', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( '21ct Scrap Gold' ) ); ?>" <?php selected( $data['name'], '21ct Scrap Gold', true ); ?> value="<?php esc_html_e( '21ct Scrap Gold', 'scrap-enquiry' ); ?>"><?php esc_html_e( '21ct Scrap Gold', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( '22ct Scrap Gold' ) ); ?>" <?php selected( $data['name'], '22ct Scrap Gold', true ); ?> value="<?php esc_html_e( '22ct Scrap Gold', 'scrap-enquiry' ); ?>"><?php esc_html_e( '22ct Scrap Gold', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( '24ct Scrap Gold' ) ); ?>" <?php selected( $data['name'], '24ct Scrap Gold', true ); ?> value="<?php esc_html_e( '24ct Scrap Gold', 'scrap-enquiry' ); ?>"><?php esc_html_e( '24ct Scrap Gold', 'scrap-enquiry' ); ?></option>
								</optgroup>
								<optgroup label="Silver">
									<option data-value="<?php echo esc_attr( scrap_calculator( 'Silver .999' ) ); ?>" <?php selected( $data['name'], 'Silver .999', true ); ?> value="<?php esc_html_e( 'Silver .999', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Silver .999', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( 'Silver .925' ) ); ?>" <?php selected( $data['name'], 'Silver .925', true ); ?> value="<?php esc_html_e( 'Silver .925', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Silver .925', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( 'Silver .900' ) ); ?>" <?php selected( $data['name'], 'Silver .900', true ); ?> value="<?php esc_html_e( 'Silver .900', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Silver .900', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( 'Silver .800' ) ); ?>" <?php selected( $data['name'], 'Silver .800', true ); ?> value="<?php esc_html_e( 'Silver .800', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Silver .800', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( 'Silver .500' ) ); ?>" <?php selected( $data['name'], 'Silver .500', true ); ?> value="<?php esc_html_e( 'Silver .500', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Silver .500', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( silver_oz( 'Silver 1 Ounce' ) ); ?>" <?php selected( $data['name'], 'Silver 1 ounce', true ); ?> value="<?php esc_html_e( 'Silver 1 ounce', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Silver 1 ounce', 'scrap-enquiry' ); ?></option>
								</optgroup>
								<optgroup label="Platinum">
									<option data-value="<?php echo esc_attr( scrap_calculator( 'Platinum .999' ) ); ?>" <?php selected( $data['name'], 'Platinum .999', true ); ?> value="<?php esc_html_e( 'Platinum .999', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Platinum .999', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( 'Platinum .950' ) ); ?>" <?php selected( $data['name'], 'Platinum .950', true ); ?> value="<?php esc_html_e( 'Platinum .950', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Platinum .950', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( scrap_calculator( 'Platinum .900' ) ); ?>" <?php selected( $data['name'], 'Platinum .900', true ); ?> value="<?php esc_html_e( 'Platinum .900', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Platinum .900', 'scrap-enquiry' ); ?></option>
								</optgroup>
								<optgroup label="Palladium">
									<option data-value="<?php echo esc_attr( scrap_calculator( 'Palladium' ) ); ?>" <?php selected( $data['name'], 'Palladium .950', true ); ?> value="<?php esc_html_e( 'Palladium .950', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Palladium .950', 'scrap-enquiry' ); ?></option>
								</optgroup>
								<optgroup label="Krugerrands">
									<option data-value="<?php echo esc_attr( krugerrand_calculator( 'Full Krugerrand' ) ); ?>" <?php selected( $data['name'], 'Full Krugerrand', true ); ?> value="<?php esc_html_e( 'Full Krugerrand', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Full Krugerrand', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( krugerrand_calculator( 'Half Krugerrand' ) ); ?>" <?php selected( $data['name'], 'Half Krugerrand', true ); ?> value="<?php esc_html_e( 'Half Krugerrand', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Half Krugerrand', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( krugerrand_calculator( 'Quarter Krugerrand' ) ); ?>" <?php selected( $data['name'], 'Quarter Krugerrand', true ); ?> value="<?php esc_html_e( 'Quarter Krugerrand', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Quarter Krugerrand', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( krugerrand_calculator( '1/10 Krugerrand' ) ); ?>" <?php selected( $data['name'], '1/10 Krugerrand', true ); ?> value="<?php esc_html_e( '1/10 Krugerrand', 'scrap-enquiry' ); ?>"><?php esc_html_e( '1/10 Krugerrand', 'scrap-enquiry' ); ?></option>
								</optgroup>   
								<optgroup label="Sovereigns">
									<option data-value="<?php echo esc_attr( sovereigns_calculator( 'Double Sovereigns' ) ); ?>" <?php selected( $data['name'], 'Double Sovereigns', true ); ?> value="<?php esc_html_e( 'Double Sovereigns', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Double Sovereigns', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( sovereigns_calculator( 'Full Sovereigns' ) ); ?>" <?php selected( $data['name'], 'Full Sovereigns', true ); ?> value="<?php esc_html_e( 'Full Sovereigns', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Full Sovereigns', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( sovereigns_calculator( 'Half Sovereigns' ) ); ?>" <?php selected( $data['name'], 'Half Sovereigns', true ); ?> value="<?php esc_html_e( 'Half Sovereigns', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Half Sovereigns', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( sovereigns_calculator( 'Quarter Sovereigns' ) ); ?>" <?php selected( $data['name'], 'Quarter Sovereigns', true ); ?> value="<?php esc_html_e( 'Quarter Sovereigns', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Quarter Sovereigns', 'scrap-enquiry' ); ?></option>
								</optgroup>   
								<optgroup label="24ct Gold Bars">
									<option data-value="<?php echo esc_attr( gold_bar_24ct( 'Kilo Gold Bar' ) ); ?>" <?php selected( $data['name'], 'Kilo Gold Bar', true ); ?> value="<?php esc_html_e( 'Kilo Gold Bar', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Kilo Gold Bar', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( gold_bar_24ct( '100 Gram Gold Bar' ) ); ?>" <?php selected( $data['name'], '100 Gram Gold Bar', true ); ?> value="<?php esc_html_e( '100 Gram Gold Bar', 'scrap-enquiry' ); ?>"><?php esc_html_e( '100 Gram Gold Bar', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( gold_bar_24ct( '10 Gram Gold Bar' ) ); ?>" <?php selected( $data['name'], '10 Gram Gold Bar', true ); ?> value="<?php esc_html_e( '10 Gram Gold Bar', 'scrap-enquiry' ); ?>"><?php esc_html_e( '10 Gram Gold Bar', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( gold_bar_24ct( '1 Gram Gold Bar' ) ); ?>" <?php selected( $data['name'], '1 Gram Gold Bar', true ); ?> value="<?php esc_html_e( '1 Gram Gold Bar', 'scrap-enquiry' ); ?>"><?php esc_html_e( '1 Gram Gold Bar', 'scrap-enquiry' ); ?></option>
									<option data-value="<?php echo esc_attr( gold_oz( 'Gold 1 Ounce' ) ); ?>" <?php selected( $data['name'], 'Gold 1 ounce', true ); ?> value="<?php esc_html_e( 'Gold 1 ounce', 'scrap-enquiry' ); ?>"><?php esc_html_e( 'Gold 1 ounce', 'scrap-enquiry' ); ?></option>
								</optgroup>   
							</select>
							<?php } ?>
						</td>
						<td class="item_weight">
							<input type="text" name="items[weight][]" class="scrap_item_weight" value="<?php echo esc_html( $data['weight'] ); ?>">
						</td>
						<td class="item_value">
							<input type="text" name="items[value][]" class="scrap_item_value" style="text-align:right" value="<?php echo esc_html( $data['value'] ); ?>">
						</td>
						<td>
							<a href="#" class="remove_scrap_item" style="color:#bc0000"><span class="dashicons dashicons-no"></span></a>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
			<tfoot>
				<th>
					<a href="#" class="button add_selecteable_scrap_item"><?php echo esc_html( __( 'Add selectable item', 'scrap-enquiry' ) ); ?></a>
					<a href="#" class="button add_arbitrary_scrap_item"><?php echo esc_html( __( 'Add arbitrary item', 'scrap-enquiry' ) ); ?></a>
				</th>
				<th style="text-align:right"><?php echo esc_html( __( 'Total', 'scrap-enquiry' ) ); ?></th>
				<th><input type="text" name="scrap_value_total" class="scrap_value_total" style="text-align:right" value="<?php echo esc_html( $sum ); ?>"></th>
				<th></th>
			</tfoot>
		</table>
		<?php
	}

	/**
	 * Handle post status transition for the custom post type
	 *
	 * @param string $new_status New post status.
	 * @param string $old_status Former post status.
	 * @param object $post WP_Post.
	 * @return void
	 */
	public static function transition_post_status( $new_status, $old_status, $post ) {

		if ( 'scrap_inquiry' !== $post->post_type ) {
			return;
		}

		// If the new and old status are the same then do not send email, just return.
		if ( $new_status === $old_status ) {
			return;
		}

		if ( 'si-provided' === $new_status ) {
			// Email customer.
			$email_to = get_post_meta( $post->ID, 'email', true );
			do_action( 'scrap_enquiry_quote_provided', $post->ID, $email_to );
		} elseif ( 'si-paid' === $new_status ) {
			// Email customer.
			$email_to = get_post_meta( $post->ID, 'email', true );
			do_action( 'scrap_enquiry_quote_paid', $post->ID, $email_to );
		}
	}

	/**
	 * Save the post data
	 *
	 * @param string $post_id Post ID number.
	 * @param object $post Post object.
	 * @param bool   $update Whether this is an existing post being updated.
	 * @return void
	 */
	public static function save_post( $post_id, $post, $update ) {
		if ( 'scrap_inquiry' !== $post->post_type ) {
			return;
		}

		if ( empty( $_POST['items'] ) || ! is_array( $_POST['items'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$temp_items = $_POST['items']; //phpcs:ignore
		$items      = array();

		$item_count = count( $temp_items['name'] );

		for ( $i = 0; $i < $item_count; $i++ ) { // phpcs:ignores

			$temp_items['name'][ $i ] = trim( $temp_items['name'][ $i ] );

			// Skip items without a name.
			if ( empty( $temp_items['name'][ $i ] ) ) {
				continue;
			}

			$items[] = array(
				'name'   => sanitize_text_field( $temp_items['name'][ $i ] ),
				'weight' => sanitize_text_field( $temp_items['weight'][ $i ] ),
				'value'  => sanitize_text_field( $temp_items['value'][ $i ] ),
			);
		}

		update_post_meta( $post_id, 'items', $items );

		$scrap_values_total = wp_unslash( $_POST['scrap_value_total'] ); // phpcs:ignore

		update_post_meta( $post_id, 'scrap_value_total', $scrap_values_total );
	}
}

\MJE\ScrapEnquiry\Post_Type::init();
