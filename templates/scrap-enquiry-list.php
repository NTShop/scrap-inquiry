<?php
/**
 * My account page - list scrap enquiries for user account.
 *
 * @package Scrap_Enquiry
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="woocommerce-orders-table table table-striped woocommerce-orders-table shop_table shop_table_responsive my_account_orders account-orders-table"> 
	<thead>
		<tr>
			<th><?php esc_html_e( 'Enquiry', 'scrap-enquiry' ); ?></th>
			<th><?php esc_html_e( 'Date', 'scrap-enquiry' ); ?></th>
			<th><?php esc_html_e( 'Status', 'scrap-enquiry' ); ?></th>
			<th><?php esc_html_e( 'Action', 'scrap-enquiry' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if ( empty( $enquiries ) ) {
			?>
			<tr>
				<td colspan="4">
					<?php echo esc_html_e( 'You do not have any scrap enquiries', 'scrap-enquiry' ); ?>
				</td>
			</tr>
			<?php
		} else {
			foreach ( $enquiries as $enquiry ) {
				$enquiry_status = \MJE\ScrapEnquiry\get_status_label( $enquiry->post_status );
				$enquiry_date   = gmdate( 'd F Y', strtotime( $enquiry->post_date ) );
				?>
				<tr>
					<td>
						<a href="<?php echo esc_attr( wc_get_endpoint_url( 'scrap-enquiries', $enquiry->ID ) ); ?>">
							<?php echo esc_html( __( '#', 'scrap-enquiry' ) . $enquiry->ID ); ?>
						</a>
					</td>
					<td>
						<?php echo esc_html( $enquiry_date ); ?>
					</td>
					<td>
						<?php echo esc_html( $enquiry_status ); ?>
					</td>
					<td>
					<a href="<?php echo esc_attr( wc_get_endpoint_url( 'scrap-enquiries', $enquiry->ID ) ); ?>" class="woocommerce-button button view">View</a>
					</td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
</table>
