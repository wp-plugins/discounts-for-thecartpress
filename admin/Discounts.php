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

$currency = tcp_get_the_currency(); ?>

<div class="wrap">
<h2><?php _e( 'Discounts', 'tcp-discount' ); ?></h2>
<ul class="subsubsub">
</ul>
<div class="clear"></div>

<h3><?php _e( 'By Order', 'tcp-discount' ); ?></h3>
<?php
$discounts		= get_option( 'tcp_discounts_by_order', array() );
$id				= isset( $_REQUEST['id'] ) ? (int)$_REQUEST['id'] : 0;
$active			= isset( $_REQUEST['active'] );
$greather_than	= isset( $_REQUEST['greather_than'] ) && (int)$_REQUEST['greather_than'] > 0 ? (float)$_REQUEST['greather_than'] : 0;
$discount		= isset( $_REQUEST['discount'] ) && (int)$_REQUEST['discount'] > 0 ? (float)$_REQUEST['discount'] : 0;
$max			= isset( $_REQUEST['max'] ) && (int)$_REQUEST['max'] > 0 ? (float)$_REQUEST['max'] : 0;
$apply_by_product = isset( $_REQUEST['apply_by_product'] );

if ( isset( $_REQUEST['add_discount_by_order'] ) || isset( $_REQUEST['delete_discount_by_order'] ) || isset( $_REQUEST['modify_discount_by_order'] ) ) {
	if ( $greather_than == 0 ) {
		echo '<div class="error"><p>', __( '"Greather than" field cannot be zero value', 'tcp-discount' ), '</p></div>';
	} elseif ( $discount == 0 && $max == 0 ) {
		echo '<div class="error"><p>', __( 'One of the fields "Discount" or "Maximum" cannot be zero.', 'tcp-discount' ), '</p></div>';
	} elseif ( isset( $_REQUEST['add_discount_by_order'] ) ) {
		$discounts[] = array (
			'active'			=> $active,
			'greather_than'		=> $greather_than,
			'discount'			=> $discount,
			'max'				=> $max,
			'apply_by_product'	=> $apply_by_product,
		);
		rsort( $discounts );
		update_option( 'tcp_discounts_by_order', $discounts ); ?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount added', 'tcp-discount' ); ?>
		</p></div><?php
	} elseif ( isset( $_REQUEST['delete_discount_by_order'] ) ) {
		unset( $discounts[$id] );
		update_option( 'tcp_discounts_by_order', $discounts ); ?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount deleted', 'tcp-discount' ); ?>
		</p></div><?php
	} else { //if ( isset( $_REQUEST['modify_discount_by_order'] ) ) {
		$discounts[$id] = array (
			'active'			=> $active,
			'greather_than'		=> $greather_than,
			'discount'			=> $discount,
			'max'				=> $max,
			'apply_by_product'	=> $apply_by_product,
		);
		rsort( $discounts );
		update_option( 'tcp_discounts_by_order', $discounts ); ?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount modified', 'tcp-discount' ); ?>
		</p></div><?php
	}
}
?>
<table class="widefat fixed" cellspacing="0">
<thead>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Greater than', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Maximum', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column" style="width: 20%;">&nbsp;</th>
</tr>
</thead>

<tfoot>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Greater than', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Maximum', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column" style="width: 20%;">&nbsp;</th>
</tr>
</tfoot>
<tbody>
<?php
if ( is_array( $discounts ) || count( $discounts ) > 0 )
	foreach( $discounts as $id => $discount_item ) : 
		$active			= isset( $discount_item['active'] ) ? $discount_item['active'] : false;
		$greather_than	= isset( $discount_item['greather_than'] ) ? $discount_item['greather_than'] : 0;
		$discount		= isset( $discount_item['discount'] ) ? $discount_item['discount'] : 0;
		$max			= isset( $discount_item['max'] ) ? $discount_item['max'] : 0;
		$apply_by_product = isset( $discount_item['apply_by_product'] ) ? $discount_item['apply_by_product'] : false; ?>
	<tr>
		<form method="post">
			<input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
			<td>
				<input type="checkbox" name="active" id="active" value="yes" <?php checked( $active ); ?>/>
			</td>
			<td>
				<input type="numeric" min="0"  name="greather_than" id="greather_than" value="<?php echo $greather_than; ?>" size="4" maxlength="4" /><?php echo $currency; ?>
			</td>
			<td>
				<input type="numeric" min="0"  name="discount" id="discount" value="<?php echo $discount; ?>" size="4" maxlength="4" />%
				<br>
				<label><?php _e( 'Apply to each product', 'tcp-discount' ); ?> <input type="checkbox" name="apply_by_product" value="yes" <?php checked( $apply_by_product ); ?> /></label>
			</td>
			<td>
				<input type="numeric" min="0"  name="max" id="max" value="<?php echo $max; ?>" size="4" maxlength="4" /><?php echo $currency; ?>
			</td>
			<td>
				<input type="submit" name="modify_discount_by_order" id="modify_discount_by_order" value="<?php _e( 'modify', 'tcp-discount' ); ?>" class="button-secondary" />
				<a href="javascript:return;" onclick="jQuery('.delete_discount').hide();jQuery('#delete_by_order_<?php echo $id; ?>').show();" class="delete"><?php _e( 'delete', 'tcp-discount' ); ?></a>
				<div id="delete_by_order_<?php echo $id; ?>" class="delete_discount" style="display:none; border: 1px dotted orange; padding: 2px">
					<p><?php _e( 'Do you really want to delete this discount?', 'tcp-discount' ); ?></p>
					<input type="submit" name="delete_discount_by_order" id="delete_discount_by_order" value="<?php _e( 'Yes', 'tcp-discount' ); ?>"  class="button-secondary"/>
					<a href="javascript:return;" onclick="jQuery('#delete_by_order_<?php echo $id; ?>').hide();"><?php _e( 'No, I don\'t' , 'tcp-discount' ); ?></a>
				</div>
			</td>
		</form>
	</tr>
	<?php endforeach; ?>
	<tr>
		<th scope="col" class="manage-column" colspan="5"><?php _e( 'Add new discount', 'tcp-discount' ); ?></th>
	</tr>
	<tr>
		<form method="post">
			<td>
				<input type="checkbox" name="active" id="active" value="yes" checked="true"/>
			</td>
			<td>
				<input type="numeric" min="0" name="greather_than" id="greather_than" value="" size="4" maxlength="4" /><?php echo $currency; ?>
			</td>
			<td>
				<input type="numeric" min="0" name="discount" id="discount" value="" size="4" maxlength="4" />%
				<br>
				<label><?php _e( 'Apply to each product', 'tcp-discount' ); ?> <input type="checkbox" name="apply_by_product" value="yes" /></label>
			</td>
			<td>
				<input type="numeric" min="0" name="max" id="max" value="" size="4" maxlength="4" /><?php echo $currency; ?>
				<p class="description"><?php _e( 'This value will not be used if "Apply to each product" is checked.', 'tcp-discount' ); ?></p>
			</td>
			<td>
				<input type="submit" name="add_discount_by_order" id="add_discount_by_order" value="<?php _e( 'add', 'tcp-discount' ); ?>" class="button-secondary"/>
			</td>
		</form>
	</tr>
</tbody>
</table>

<h3><?php _e( 'By Product', 'tcp-discount' ); ?></h3>
<?php
$id			= isset( $_REQUEST['id'] ) ? (int)$_REQUEST['id'] : 0;
$active		= isset( $_REQUEST['active'] );
$product_id	= isset( $_REQUEST['product_id'] ) ? (int)$_REQUEST['product_id'] : 0;
$option_ids	= isset( $_REQUEST['option_ids'] ) ? $_REQUEST['option_ids'] : 0;
$option_ids = explode( '-', $option_ids );
$option_id_2 = 0;
if ( count( $option_ids ) == 2 ) {
	$option_id_1 = $option_ids[0];
	$option_id_2 = $option_ids[1];
} else {
	$option_id_1 = $option_ids[0];
}
$type		= isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : 'amount';
$value		= isset( $_REQUEST['value'] ) ? tcp_input_number( $_REQUEST['value'] ) : 0;
$discounts	= get_option( 'tcp_discounts_by_product', array() );
if ( isset( $_REQUEST['add_discount_by_product'] ) ) {
	if ( $value <= 0 && $type != 'freeshipping' ) { ?>
		<div id="message" class="updated">
			<p><?php _e( 'The value must be a number greater than zero', 'tcp-discount' ); ?></p>
		</div><?php
	} else {
		$discounts[] = array (
			'active'		=> $active,
			'product_id'	=> $product_id,
			'option_id_1'	=> $option_id_1,
			'option_id_2'	=> $option_id_2,
			'type'			=> $type,
			'value'			=> $value,
		);
		rsort( $discounts );
		update_option( 'tcp_discounts_by_product', $discounts ); ?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount added', 'tcp-discount' ); ?>
		</p></div><?php
	}
} elseif ( isset( $_REQUEST['delete_discount_by_product'] ) ) {
	unset( $discounts[$id] );
	update_option( 'tcp_discounts_by_product', $discounts ); ?>
	<div id="message" class="updated"><p>
		<?php _e( 'Discount deleted', 'tcp-discount' ); ?>
	</p></div><?php
} elseif ( isset( $_REQUEST['modify_discount_by_product'] ) ) {
	$discounts[$id]['active'] = $active;
	$discounts[$id]['type'] = $type;
	$discounts[$id]['value'] = $value;
	rsort( $discounts );
	update_option( 'tcp_discounts_by_product', $discounts ); ?>
	<div id="message" class="updated"><p>
		<?php _e( 'Discount modified', 'tcp-discount' ); ?>
	</p></div><?php
}

$discount_types = tcp_get_discount_types();
?>
<table class="widefat fixed" cellspacing="0">
<thead>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Product', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Type', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Value', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column" style="width: 20%;">&nbsp;</th>
</tr>
</thead>
<tfoot>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Product', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Type', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Value', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column">&nbsp;</th>
</tr>
</tfoot>
<tbody><?php
if ( is_array( $discounts ) || count( $discounts ) > 0 )
	foreach( $discounts as $id => $discount_item ) : 
		$active			= isset( $discount_item['active'] ) ? $discount_item['active'] : false;
		$product_id		= isset( $discount_item['product_id'] ) ? $discount_item['product_id'] : 0;
		$option_id_1	= isset( $discount_item['option_id_1'] ) ? $discount_item['option_id_1'] : 0;
		$option_id_2	= isset( $discount_item['option_id_2'] ) ? $discount_item['option_id_2'] : 0;
		$type			= isset( $discount_item['type'] ) ? $discount_item['type'] : 'amount';
		$value			= isset( $discount_item['value'] ) ? $discount_item['value'] : 0; ?>
	<tr>
		<form method="post">
			<input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
			<td>
				<input type="checkbox" name="active" id="active" value="yes" <?php checked( $active ); ?>/>
			</td>
			<td>
				<?php echo $product_id == 0 ? __( 'All', 'tcp-discount' ) : edit_post_link( get_the_title( $product_id ), '', '', $product_id ); ?>
				<?php if ( $option_id_1 > 0 ) echo ' - ', get_the_title( $option_id_1 ); ?>
				<?php if ( $option_id_2 > 0 ) echo ' - ', get_the_title( $option_id_2 ); ?>
			</td>
			<td>
				<select name="type" id="type">
				<?php foreach( $discount_types as $t => $discount_type ) : ?>
					<option value="<?php echo $t; ?>" <?php selected( $t, $type ); ?>><?php echo $discount_type; ?></option>
				<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input type="numeric" min="0" name="value" id="value" value="<?php echo $value; ?>" size="4" maxlength="4" /><?php echo $currency; ?>/%
			</td>
			<td>
				<input type="submit" name="modify_discount_by_product" id="modify_discount_by_product" value="<?php _e( 'modify', 'tcp-discount' ); ?>" class="button-secondary" />
				<a href="javascript:return;" onclick="jQuery('.delete_discount').hide();jQuery('#delete_by_product_<?php echo $id; ?>').show();return;" class="delete"><?php _e( 'delete', 'tcp-discount' ); ?></a>
				<div id="delete_by_product_<?php echo $id; ?>" class="delete_discount" style="display:none; border: 1px dotted orange; padding: 2px">
					<p><?php _e( 'Do you really want to delete this discount?', 'tcp-discount' ); ?></p>
					<input type="submit" name="delete_discount_by_product" id="delete_discount_by_product" value="<?php _e( 'Yes', 'tcp-discount' ); ?>"  class="button-secondary"/>
					<a href="javascript:return;" onclick="jQuery('#delete_by_product_<?php echo $id; ?>').hide();"><?php _e( 'No, I don\'t' , 'tcp-discount' ); ?></a>
				</div>
			</td>
		</form>
	</tr>
	<?php endforeach; ?>
	<tr>
		<th scope="col" class="manage-column" colspan="5"><?php _e( 'Add new discount', 'tcp-discount' ); ?></th>
	</tr>
	<tr>
		<form method="post">
			<td>
				<input type="checkbox" name="active" id="active" value="yes" checked="true"/>
			</td>
			<td><?php $post_types = tcp_get_saleable_post_types();
				$i = array_search( 'tcp_dynamic_options', $post_types );
				if ( $i !== false ) unset( $post_types[$i] );
				$args = array(
					'post_type'			=> $post_types,
					'orderby'			=> 'title',
					'order'				=> 'ASC',
					'posts_per_page'	=> -1,
					'fields'			=> 'ids',
				);
				$products = get_posts( $args );
				if ( is_array( $products ) && count( $products ) > 0 ) : ?>
					<select name="product_id" id="product_id">
					<?php $product_id = isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : 0; ?>
						<option value="0"><?php _e( 'All', 'tcp-discount' ); ?></option>
						<?php foreach( $products as $id ) : $product = get_post( $id ); ?>
						<option value="<?php echo $product->ID; ?>" <?php selected( $product->ID, $product_id ); ?>><?php echo $product->post_title; ?></option>
						<?php endforeach; ?>
					</select><input type="submit" name="tcp_load_options" id="tcp_load_options" value="<?php _e( 'Options', 'tcp-discount' ); ?>" class="button-secondary"/>
						<?php if ( isset( $_REQUEST['tcp_load_options'] ) ) :
							require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/thecartpress/daos/RelEntities.class.php' );
							$options_1 = RelEntities::select( $product_id, 'OPTIONS' );
							if ( is_array( $options_1 ) && count ( $options_1 ) > 0 ) :	?>
								<select name="option_ids">
									<option><?php _e( 'All', 'tcp-discount' ); ?></option>
								<?php foreach( $options_1 as $option_1 ) :
									$option_1_title = get_the_title( $option_1->id_to );
									$options_2 = RelEntities::select( $option_1->id_to, 'OPTIONS' );
									if ( is_array( $options_2 ) && count ( $options_2 ) > 0 ) :
										foreach( $options_2 as $option_2 ) :
											$option_2_level = get_post( $option_2->id_to ); ?>
										<option value="<?php echo $option_1->id_to, '-', $option_2->id_to; ?>"><?php echo $option_1_title . ' - ' . get_the_title( $option_2->id_to ); ?></option><?php
										endforeach;
									else : ?>
										<option value="<?php echo $option_1->id_to; ?>"><?php echo $option_1_title; ?></option><?php
									endif;
								endforeach; ?>
								</select>
							<?php endif; ?> 
						<?php endif; ?>
					<?php endif; ?>
			</td>
			<td>
				<select name="type" id="type">
					<?php foreach( $discount_types as $t => $discount_type ) : ?>
						<option value="<?php echo $t; ?>" ><?php echo $discount_type; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input type="numeric" min="0" name="value" id="value" value="" size="4" maxlength="4" /><?php echo $currency; ?>/%
			</td>
			<td>
			<input type="submit" name="add_discount_by_product" id="add_discount_by_product" value="<?php _e( 'add', 'tcp-discount' ); ?>" class="button-secondary"/>
			</td>
		</form>
	</tr>
</tbody>
</table>

<h3><?php _e( 'Coupons', 'tcp-discount' ); ?></h3>
<?php
$id					= isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;
$active				= isset( $_REQUEST['active'] );
$coupon_code		= isset( $_REQUEST['coupon_code'] ) ? $_REQUEST['coupon_code'] : '';
$coupon_value		= isset( $_REQUEST['coupon_value'] ) ? (float)$_REQUEST['coupon_value'] : 0;
$coupon_type		= isset( $_REQUEST['coupon_type'] ) ? $_REQUEST['coupon_type'] : 'amount';
$from_date			= isset( $_REQUEST['from_date'] ) ? $_REQUEST['from_date'] : '';
$to_date			= isset( $_REQUEST['to_date'] ) ? $_REQUEST['to_date'] : '';
$uses_per_coupon	= isset( $_REQUEST['uses_per_coupon'] ) ? $_REQUEST['uses_per_coupon'] : -1;
$uses_per_user		= isset( $_REQUEST['uses_per_user'] ) ? $_REQUEST['uses_per_user'] : -1;
$uses_per_coupon	= $uses_per_coupon == '' ? -1 : $uses_per_coupon;
$uses_per_user		= $uses_per_user == '' ? -1 : $uses_per_user;

if ( isset( $_REQUEST['add_coupon'] ) ) {
	$errors = array();
	if ( strlen( $coupon_code ) == 0 ) {
		$errors[] = __( 'Coupon Code field must contain a text', 'tcp-discount' );
	} else {
		$coupons = get_option( 'tcp_coupons', array() );
		foreach( $coupons as $coupon ) {
			if ( $coupon['coupon_code'] == $coupon_code ) {
				$errors[] = __( 'Coupon Code exists', 'tcp-discount' );
				break;
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
		tcp_add_coupon( $active, $coupon_code, $coupon_type, $coupon_value, $from_date, $to_date, $uses_per_coupon, $uses_per_user );
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
		tcp_modify_coupon( $id, $active, $coupon_type, $coupon_value, $from_date, $to_date, $uses_per_coupon, $uses_per_user ); ?>
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
?>
<table class="widefat fixed" cellspacing="0">
<thead>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Coupon code', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'From date', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'To date', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Uses per coupon', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Uses per user', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column">&nbsp;</th>
</tr>
</thead>
<tfoot>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Coupon code', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'From date', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'To date', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Uses per coupon', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column"><?php _e( 'Uses per user', 'tcp-discount' ); ?></th>
	<th scope="col" class="manage-column">&nbsp;</th>
</tr>
</tfoot>
<body>
<?php $coupons = get_option( 'tcp_coupons', array() );
if ( is_array( $coupons ) || count( $coupons ) > 0 ) :
	foreach( $coupons as $id => $coupon ) : ?>
		<tr>
		<form method="post">
			<input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
			<td>
				<input type="checkbox" name="active" id="active" value="yes" <?php checked( $coupon['active'] ); ?>/>
			</td>
			<td><?php echo $coupon['coupon_code']; ?></td>
			<td>
			<label><?php _e( 'type', 'tcp-discount' ); ?>:
				<select name="coupon_type" id="coupon_type">
					<?php //unset( $discount_types['freeshipping'] );
					foreach( $discount_types as $t => $discount_type ) : ?>
						<option value="<?php echo $t; ?>" <?php selected( $t, $coupon['coupon_type'] ); ?>><?php echo $discount_type; ?></option>
					<?php endforeach; ?>
				</select></label>
				<label><input type="text" min="0" name="coupon_value" id="coupon_value" value="<?php echo $coupon['coupon_value']; ?>" size="4" maxlength="4" /><?php echo $currency; ?>/%</label>
			</td>
			<td><input type="date" name="from_date" id="from_date" size="10" maxlength="10" value="<?php echo strftime( '%Y/%m/%d', $coupon['from_date'] ); ?>" /></td>
			<td><input type="date" name="to_date" id="to_date" size="10" maxlength="10" value="<?php echo strlen( $coupon['to_date'] ) > 0 ? strftime( '%Y/%m/%d', $coupon['to_date'] ) : ''; ?>" /></td>
			<td><input type="numeric" name="uses_per_coupon" id="uses_per_coupon" size="4" maxlength="4" value="<?php echo $coupon['uses_per_coupon']; ?>" /></td>
			<td><input type="numeric" name="uses_per_user" id="uses_per_user" size="4" maxlength="4" value="<?php echo $coupon['uses_per_user']; ?>" /></td>
			<td>
				<input type="submit" name="modify_coupon" id="modify_coupon" value="<?php _e( 'modify', 'tcp-discount' ); ?>" class="button-secondary"/>
				<a href="javascript:return;" onclick="jQuery('.delete_coupon').hide();jQuery('#delete_coupon_<?php echo $id; ?>').show();" class="delete"><?php _e( 'delete', 'tcp-discount' ); ?></a>
				<div id="delete_coupon_<?php echo $id; ?>" class="delete_coupon" style="display:none; border: 1px dotted orange; padding: 2px">
					<p><?php _e( 'Do you really want to delete this coupon?', 'tcp-discount' ); ?></p>
					<input type="submit" name="delete_coupon" id="delete_coupon" value="<?php _e( 'Yes', 'tcp-discount' ); ?>"  class="button-secondary"/>
					<a href="javascript:return;" onclick="jQuery('#delete_coupon_<?php echo $id; ?>').hide();"><?php _e( 'No, I don\'t' , 'tcp-discount' ); ?></a>
				</div>
			</td>
		</form>
		</tr>
	<?php endforeach;
endif; ?>
<tr>
	<td colspan="8"><?php _e( 'Add new discount', 'tcp_coupon' ); ?></td>
</tr>
<tr><form method="post">
	<td>
		<input type="checkbox" name="active" id="active" value="yes" checked="true"/>
	</td>
	<td>
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
		<input type="date" name="from_date" id="from_date" size="10" maxlength="10"/>
		<p class="description"><?php _e( 'Format YYYY/MM/DD', 'tcp-discount' ); ?></p>
	</td>
	<td>
		<input type="date" name="to_date" id="to_date" size="10" maxlength="10"/>
		<p class="description"><?php _e( 'Format YYYY/MM/DD, or leave to blank to have no end.', 'tcp-discount' ); ?></p>
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
</form></tr>
</tbody>
</table>

</div>