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
<?php screen_icon( 'tcp-default' ); ?><h2><?php _e( 'Discounts', 'tcp-discount' ); ?></h2>
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
			<td><?php $post_types = tcp_get_product_post_types();
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

</div>