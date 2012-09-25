<?php
/**
 * This file is part of TheCartPress-Discount.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class TCPDiscountMetabox {

	function __construct() {
		add_action( 'admin_init', array( $this, 'register_metabox' ) );
	}

	function register_metabox() {
		if ( function_exists( 'tcp_get_saleable_post_types' ) ) {
			$saleable_post_types = tcp_get_saleable_post_types();
			if ( is_array( $saleable_post_types ) && count( $saleable_post_types ) > 0 ) {
				foreach( $saleable_post_types as $post_type ) {
					add_meta_box( 'tcp-discount-custom-fields', __( 'Discount setup', 'tcp' ), array( &$this, 'show' ), $post_type, 'normal', 'core' );
				}
			}
			add_action( 'save_post', array( &$this, 'save' ), 10, 2 );
			add_action( 'delete_post', array( &$this, 'delete' ) );
		}
	}

	function show() {
		global $post;
		if ( ! tcp_is_saleable_post_type( $post->post_type ) ) return;
		$post_id = tcp_get_default_id( $post->ID, $post->post_type );
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		$lang			= isset( $_REQUEST['lang'] ) ? $_REQUEST['lang'] : '';
		$source_lang	= isset( $_REQUEST['source_lang'] ) ? $_REQUEST['source_lang'] : '';//isset( $_REQUEST['lang'] ) ? $_REQUEST['lang'] : '';
		$is_translation	= $lang != $source_lang;
		if ( $is_translation && $post_id == $post->ID ) {
			_e( 'After saving the title and content, you will be able to edit the specific fields of the product.', 'tcp' );
			return;
		}
		$discount = $this->getDiscount( $post_id );
		$exclude = tcp_exclude_from_order_discount( $post_id ); ?>
		<div class="form-wrap">

			<?php wp_nonce_field( 'tcp_discount_noncename', 'tcp_discount_noncename', false, true ); ?>

			<table class="form-table">
			<tbody>

			<tr valign="top">
				<td><label for="tcp_discount_active"><?php _e( 'Active', 'tcp-discount' ); ?></label> <input type="checkbox" name="tcp_discount_active" id="tcp_discount_active" <?php checked( $discount['active'], true ); ?> /></td>

				<td><label for="tcp_discount_type"><?php _e( 'Type', 'tcp-discount' ); ?></label> <select name="tcp_discount_type" id="tcp_discount_type">
				<?php $discount_types = tcp_get_discount_types();
				foreach( $discount_types as $t => $discount_type ) : ?>
					<option value="<?php echo $t; ?>" <?php selected( $t, $discount['type'] ); ?>><?php echo $discount_type; ?></option>
				<?php endforeach; ?>
				</select></td>

				<td><label for="tcp_discount_value"><?php _e( 'Value', 'tcp-discount' ); ?></label> <input type="text" min="0" step="any" placeholder="<?php tcp_get_number_format_example(); ?>" name="tcp_discount_value" id="tcp_discount_value" value="<?php echo tcp_number_format( $discount['value'] ); ?>" class="regular-text" style="width:12em !important" />&nbsp;<?php tcp_the_currency(); ?>/%
				<p class="description"><?php printf( __( 'Current number format is %s', 'tcp'), tcp_get_number_format_example( 9999.99, false ) ); ?></p></td>
			</tr>

			<tr valign="top">
				<td colspan="3"><label for="tcp_discount_exclude"><input type="checkbox" name="tcp_discount_exclude" id="tcp_discount_exclude" <?php checked( $exclude ); ?> value="yes"/> <?php _e( 'Exclude the product from Discount by Order (only "Apply to each product" option)', 'tcp-discount' ); ?></label></td>
			</tr>

			</tbody>
			</table>

		</div> <!-- form-wrap -->
		<?php
	}

	function save( $post_id, $post ) {
		if ( ! isset( $_POST[ 'tcp_discount_noncename' ] ) || ! wp_verify_nonce( $_POST[ 'tcp_discount_noncename' ], 'tcp_discount_noncename' ) ) return array( $post_id, $post );
		if ( ! tcp_is_saleable_post_type( $post->post_type ) ) return array( $post_id, $post );
		if ( ! current_user_can( 'edit_post', $post_id ) ) return array( $post_id, $post );
		$post_id	= tcp_get_default_id( $post_id, $post->post_type );
		$value		= isset( $_POST['tcp_discount_value'] ) ? tcp_input_number( $_POST['tcp_discount_value'] ) : '0';
		$type		= isset( $_POST['tcp_discount_type'] ) ? $_POST['tcp_discount_type'] : 'amount';
		if ( $value > 0 || $type == 'freeshipping' ) {
			$active		= isset( $_POST['tcp_discount_active'] );
			$value		= isset( $_POST['tcp_discount_value'] ) ? tcp_input_number( $_POST['tcp_discount_value'] ) : '0';
			$exclude	= isset( $_POST['tcp_discount_exclude'] );
			$this->saveDiscount( $post_id, $active, $type, $value );
			update_post_meta( $post_id, 'tcp_discount_exclude', $exclude );
		}
	}

	function delete( $post_id ) {
		$post = get_post( $post_id );
		if ( ! tcp_is_saleable_post_type( $post->post_type ) ) return $post_id;
		if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
		$post_id = tcp_get_default_id( $post_id, $post->post_type );

		$this->deleteDiscount( $post_id );
		delete_post_meta( $post_id, 'tcp_discount_exclude' );
	}

	private function getDiscount( $post_id ) {
		$discounts = get_option( 'tcp_discounts_by_product', array() );
		foreach( $discounts as $discount ) {
			if ( $discount['product_id'] == $post_id ) return $discount;
		}
		return false;
	}

	private function saveDiscount( $post_id, $active, $type, $value ) {
		$discounts = get_option( 'tcp_discounts_by_product', array() );
		foreach( $discounts as $id => $discount )
			if ( $discount['product_id'] == $post_id ) {
				$discounts[$id] = array (
					'product_id'	=> $post_id,
					'active'		=> $active,
					'type'			=> $type,
					'value'			=> $value,
				);
				rsort( $discounts );
				update_option( 'tcp_discounts_by_product', $discounts );
				return;
			}
		$discounts[] = array (
			'product_id'	=> $post_id,
			'active'		=> $active,
			'type'			=> $type,
			'value'			=> $value,
		);
		rsort( $discounts );
		update_option( 'tcp_discounts_by_product', $discounts );
	}

	private function deleteDiscount( $post_id ) {
		$discounts = get_option( 'tcp_discounts_by_product', array() );
		foreach( $discounts as $id => $discount )
			if ( $discount['product_id'] == $post_id ) {
				unset( $discounts[$id] );
				update_option( 'tcp_discounts_by_product', $discounts );
				return;
			}
	}
}

new TCPDiscountMetabox();
?>