<?php
/**
 * This file is part of TheCartPress-discount.
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

/*$coupons = get_option( 'tcp_coupons', array() );
foreach( $coupons as $id => $coupon ) {
	//$coupons[$id]['coupon_code'] = str_replace( 'MAKIL-LIFE', 'MAKIL', $coupon['coupon_code'] );
	$coupons[$id]['uses_per_coupon'] = 0;
}
update_option( 'tcp_coupons', $coupons );*/

/*update_option( 'tcp_coupons', array() );*/

/*
$coupons = get_option( 'tcp_coupons', array() );
foreach( $coupons as $id => $coupon ) {
	if ( $coupon['coupon_value'] == 60 ) $coupons[$id]['coupon_value'] = 30;
}
update_option( 'tcp_coupons', $coupons );
*/


$currency = tcp_get_the_currency(); ?>

<div class="wrap">
<?php screen_icon( 'tcp-default' ); ?><h2><?php _e( 'Coupons', 'tcp-discount' ); ?></h2>
<div class="clear"></div>

<?php
$id					= isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;
$active				= isset( $_REQUEST['active'] );
$coupon_code		= isset( $_REQUEST['coupon_code'] ) ? $_REQUEST['coupon_code'] : '';
$coupon_value		= isset( $_REQUEST['coupon_value'] ) ? (float)$_REQUEST['coupon_value'] : 0;
$coupon_type		= isset( $_REQUEST['coupon_type'] ) ? $_REQUEST['coupon_type'] : 'amount';
$from_date			= isset( $_REQUEST['from_date'] ) ? $_REQUEST['from_date'] : '';
$to_date			= isset( $_REQUEST['to_date'] ) ? $_REQUEST['to_date'] : '';
$by_product			= isset( $_REQUEST['by_product'] );
if ( $by_product ) $product_id = $_REQUEST['product_id'];
else $product_id = false;
$uses_per_coupon	= isset( $_REQUEST['uses_per_coupon'] ) ? $_REQUEST['uses_per_coupon'] : -1;
$uses_per_user		= isset( $_REQUEST['uses_per_user'] ) ? $_REQUEST['uses_per_user'] : -1;
$uses_per_coupon	= $uses_per_coupon == '' ? -1 : $uses_per_coupon;
$uses_per_user		= $uses_per_user == '' ? -1 : $uses_per_user;

$discount_types = tcp_get_discount_types();

if ( isset( $_REQUEST['add_coupon'] ) ) {
	$errors = array();
	if ( strlen( $coupon_code ) == 0 ) {
		$errors[] = __( 'Coupon Code field must contain a text', 'tcp-discount' );
	} else {
		$coupons = get_option( 'tcp_coupons', array() );
		if ( is_array( $coupons ) && count( $coupons ) > 0 ) {
			foreach( $coupons as $coupon ) {
				if ( $coupon['coupon_code'] == $coupon_code ) {
					$errors[] = __( 'Coupon Code exists', 'tcp-discount' );
					break;
				}
			}
		}
	}
	if ( ( $from_date = strtotime( $from_date ) ) === false )
		$errors[] = __( 'From Date field must contain a valid date', 'tcp-discount' );
	if ( strlen( $to_date ) > 0 )
		if ( ( $to_date = strtotime( $to_date ) ) === false )
			$errors[] = __( 'To Date field must contain a valid date', 'tcp-discount' );
	if ( count( $errors ) > 0 ) : ?>
		<div id="message" class="error">
		<?php foreach( $errors as $error ) : ?>
			<p><?php echo $error; ?></p>
		<?php endforeach; ?>
		</div>
	<?php else :
		tcp_add_coupon( $active, $coupon_code, $coupon_type, $coupon_value, $from_date, $to_date, $uses_per_coupon, $uses_per_user, $by_product, $product_id );
		?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount added', 'tcp-discount' ); ?>
		</p></div>
	<?php endif;
} elseif ( isset( $_REQUEST['modify_coupon'] ) ) {
	$errors = array();
	if ( ( $from_date = strtotime( $from_date ) ) === false )
		$errors[] = __( 'From Date field must contain a valid date', 'tcp-discount' );
	if ( strlen( $to_date ) > 0 )
		if ( ( $to_date = strtotime( $to_date ) ) === false)
			$errors[] = __( 'To Date field must contain a valid date', 'tcp-discount' );
	if ( count( $errors ) > 0 ) : ?>
		<div id="message" class="error">
		<?php foreach( $errors as $error ) : ?>
			<p><?php echo $error; ?></p>
		<?php endforeach; ?>
		</div>
	<?php else :
		tcp_modify_coupon( $id, $active, $coupon_type, $coupon_value, $from_date, $to_date, $uses_per_coupon, $uses_per_user, $by_product, $product_id ); ?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount modified', 'tcp-discount' ); ?>
		</p></div>
	<?php endif;
} elseif ( isset( $_REQUEST['delete_coupon'] ) ) {
	tcp_delete_coupon( $id ); ?>
	<div id="message" class="updated"><p>
		<?php _e( 'Coupon deleted', 'tcp-discount' ); ?>
	</p></div><?php
}
$post_types = tcp_get_product_post_types();
$args = array(
	'post_type'			=> $post_types,
	'orderby'			=> 'title',
	'order'				=> 'ASC',
	'posts_per_page'	=> -1,
	'fields'			=> 'ids',
);
$products = get_posts( $args );

$select = array();
if ( is_array( $products ) && count( $products ) > 0 ) {
foreach( $products as $id ) {
	$product = get_post( $id );
	$select[] = '<option value="' . $id . '" >' . esc_attr( $product->post_title ) . '</option>';
}
$select = implode( "\n", $select );
}
?>

<table class="widefat fixed" cellspacing="0">
<thead>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?> - <?php _e( 'Coupon code', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'By Product', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'From date', 'tcp-discount' ); ?> - <?php _e( 'To date', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Uses per coupon', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Uses per user', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column">&nbsp;</th>
</tr>
</thead>
<tfoot>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?> - <?php _e( 'Coupon code', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'By Product', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'From date', 'tcp-discount' ); ?> - <?php _e( 'To date', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Uses per coupon', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Uses per user', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column">&nbsp;</th>
</tr>
</tfoot>
<tbody>
<?php $coupons = get_option( 'tcp_coupons', array() );
if ( is_array( $coupons ) && count( $coupons ) > 0 ) :
	$row = 1;
	foreach( $coupons as $id_coupon => $coupon ) : ?>
		<form method="post">
		<tr>
			<td>
				<input type="hidden" name="id" id="id" value="<?php echo $id_coupon; ?>" />
				<?php echo $row++; ?>&nbsp;
				<input type="checkbox" name="active" value="yes" <?php checked( $coupon['active'] ); ?>/>
				&nbsp;
				<?php echo $coupon['coupon_code']; ?>
			</td>
			<td>
				<input type="text" min="0" name="coupon_value" value="<?php echo $coupon['coupon_value']; ?>" size="4" maxlength="4" />
				<select name="coupon_type" id="coupon_type">
					<?php //unset( $discount_types['freeshipping'] );
					foreach( $discount_types as $t => $discount_type ) : ?>
						<option value="<?php echo $t; ?>" <?php selected( $t, $coupon['coupon_type'] ); ?>><?php echo $discount_type; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input type="checkbox" name="by_product" value="yes" <?php checked( $coupon['by_product'] ); ?> />
				<select name="product_id" id="product_id_<?php echo $id_coupon; ?>">';
				<?php echo $select; ?>
				</select>
				<script>
				jQuery( '#product_id_<?php echo $id_coupon; ?> option[value="<?php echo $coupon['product_id']; ?>"]' ).attr( "selected", "selected" );
				</script>
			</td>
			<td>
				<input type="text" name="from_date" size="10" maxlength="10" value="<?php echo strftime( '%Y/%m/%d', $coupon['from_date'] ); ?>" />
				-
				<input type="text" name="to_date" size="10" maxlength="10" value="<?php echo strlen( $coupon['to_date'] ) > 0 ? strftime( '%Y/%m/%d', $coupon['to_date'] ) : ''; ?>" />
			</td>
			<td>
				<input type="numeric" name="uses_per_coupon" size="4" maxlength="4" value="<?php echo $coupon['uses_per_coupon']; ?>" />
			</td>
			<td>
				<input type="numeric" name="uses_per_user" size="4" maxlength="4" value="<?php echo $coupon['uses_per_user']; ?>" />
			</td>
			<td>
				<input type="submit" name="modify_coupon" value="<?php _e( 'modify', 'tcp-discount' ); ?>" class="button-secondary"/>
				<a href="javascript:return;" onclick="jQuery('.delete_coupon').hide();jQuery('#delete_coupon_<?php echo $id_coupon; ?>').show();" class="delete"><?php _e( 'delete', 'tcp-discount' ); ?></a>
				<div id="delete_coupon_<?php echo $id_coupon; ?>" class="delete_coupon" style="display:none; border: 1px dotted orange; padding: 2px">
					<p><?php _e( 'Do you really want to delete this coupon?', 'tcp-discount' ); ?></p>
					<input type="submit" name="delete_coupon" id="delete_coupon" value="<?php _e( 'Yes', 'tcp-discount' ); ?>"  class="button-secondary"/>
					<a href="javascript:return;" onclick="jQuery('#delete_coupon_<?php echo $id_coupon; ?>').hide();"><?php _e( 'No, I don\'t' , 'tcp-discount' ); ?></a>
				</div>
			</td>
		</tr>
		</form>
	<?php endforeach;
endif; ?>
</tbody>
</table>

<h3><?php _e( 'Add New Coupon', 'tcp_coupon' ); ?></h3>
<form method="post">
	<table class="widefat fixed" cellspacing="0">
	<thead>
	<tr>
		<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?> - <?php _e( 'Coupon code', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'By Product', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'From date', 'tcp-discount' ); ?> - <?php _e( 'To date', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'Uses per coupon', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'Uses per user', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column">&nbsp;</th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?> - <?php _e( 'Coupon code', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'By Product', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'From date', 'tcp-discount' ); ?> - <?php _e( 'To date', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'Uses per coupon', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column"><?php _e( 'Uses per user', 'tcp-discount' ); ?></th>
		<th scope="col" class="manage-column">&nbsp;</th>
	</tr>
	</tfoot>
	<tbody>
	<tr>
		<td>
			<input type="checkbox" name="active" id="active" value="yes" checked="true"/>
			&nbsp;
			<input type="text" name="coupon_code" id="coupon_code" size="10" maxlength="10"/>
		</td>
		<td>
			<label><?php _e( 'type', 'tcp-discount' ); ?>:
			<select name="coupon_type" id="coupon_type">
				<?php foreach( $discount_types as $t => $discount_type ) : ?>
					<option value="<?php echo $t; ?>"><?php echo $discount_type; ?></option>
				<?php endforeach; ?>
			</select></label>
			<label><input type="text" min="0" name="coupon_value" id="coupon_value" value="" size="4" maxlength="4" /><?php echo $currency; ?>/%</label>
		</td>
		<td>
			<label><input type="checkbox" value="yes" name="by_product"/>&nbsp;<?php _e( 'By Product', 'tcp-discount' ); ?></label>
			<?php if ( is_array( $products ) && count( $products ) > 0 ) : ?>
				<select name="product_id" id="product_id">
				<?php foreach( $products as $id ) : $product = get_post( $id ); ?>
					<option value="<?php echo $id; ?>" <?php selected( $product->ID, $product_id ); ?>><?php echo esc_attr( $product->post_title ); ?></option>
				<?php endforeach; ?>
				</select>
			<?php endif; ?>
		</td>
		<td>
			<input type="text" name="from_date" id="from_date" size="10" maxlength="10"/> - <input type="text" name="to_date" id="to_date" size="10" maxlength="10"/>
			<p class="description"><?php _e( 'Format YYYY/MM/DD. Leave \'To Date\' blank to have no end.', 'tcp-discount' ); ?></p>
		</td>
		<td>
			<input type="text" min="-1"  name="uses_per_coupon" id="uses_per_coupon" size="4" maxlength="4" />
			<p class="description"><?php _e( 'To set no limit leave this field to -1 or blank.', 'tcp-discount' ); ?></p>
		</td>
		<td>
			<input type="text" min="-1" name="uses_per_user" id="uses_per_user" size="4" maxlength="4" />
			<p class="description"><?php _e( 'This value will be used only for registered users.', 'tcp-discount' ); ?></p>
			<p class="description"><?php _e( 'To set no limit leave this field to -1 or blank.', 'tcp-discount' ); ?></p>
			<p class="description"><?php _e( 'Zero value has no sense.', 'tcp-discount' ); ?></p>
		</td>
		<td>
			<input type="submit" name="add_coupon" id="add_coupon" value="<?php _e( 'add', 'tcp-discount' ); ?>" class="button-secondary"/>
		</td>
	</tr>
	</tbody>
	</table>
</form><!-- add new coupon-->
</div><!-- .wrap -->