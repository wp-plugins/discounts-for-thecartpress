<?php
/**
 * This file is part of TheCartPress-discount.
 * 
 * TheCartPress-discount is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TheCartPress-discount is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TheCartPress-discount.  If not, see <http://www.gnu.org/licenses/>.
 */
$currency = tcp_get_the_currency();
?>

<div class="wrap">
<h2><?php _e( 'Discounts', 'tcp_discount' );?></h2>
<ul class="subsubsub">
</ul>
<div class="clear"></div>

<h3><?php _e( 'By Order', 'tcp_discount' );?></h3>
<?php
$discounts		= get_option( 'tcp_discounts_by_order', array() );
$id				= isset( $_REQUEST['id'] ) ? (int)$_REQUEST['id'] : 0;
$active			= isset( $_REQUEST['active'] );
$greather_than	= isset( $_REQUEST['greather_than'] ) && (int)$_REQUEST['greather_than'] > 0 ? (float)$_REQUEST['greather_than'] : 0;
$discount		= isset( $_REQUEST['discount'] ) && (int)$_REQUEST['discount'] > 0 ? (float)$_REQUEST['discount'] : 0;
$max			= isset( $_REQUEST['max'] ) && (int)$_REQUEST['max'] > 0 ? (float)$_REQUEST['max'] : 0;
if ( isset( $_REQUEST['add_discount_by_order'] ) || isset( $_REQUEST['delete_discount_by_order'] ) || isset( $_REQUEST['modify_discount_by_order'] ) ) {
	if ( $greather_than == 0 ) {
		echo '<div class="error"><p>', __( '"Greather than" field cannot be zero value', 'tcp_discount' ), '</p></div>';
	} elseif ( $discount == 0 && $max == 0 ) {
		echo '<div class="error"><p>', __( 'One of the fields "Discount" or "Maximum" cannot be zero.', 'tcp_discount' ), '</p></div>';
	} elseif ( isset( $_REQUEST['add_discount_by_order'] ) ) {
		$discounts[] = array (
			'active'		=> $active,
			'greather_than'	=> $greather_than,
			'discount'		=> $discount,
			'max'			=> $max,
		);
		rsort( $discounts );
		update_option( 'tcp_discounts_by_order', $discounts );?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount added', 'tcp_discount' );?>
		</p></div><?php
	} elseif ( isset( $_REQUEST['delete_discount_by_order'] ) ) {
		unset( $discounts[$id] );
		update_option( 'tcp_discounts_by_order', $discounts );?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount deleted', 'tcp_discount' );?>
		</p></div><?php
	} else { //if ( isset( $_REQUEST['modify_discount_by_order'] ) ) {
		$discounts[$id] = array (
			'active'		=> $active,
			'greather_than'	=> $greather_than,
			'discount'		=> $discount,
			'max'			=> $max,
		);
		rsort( $discounts );
		update_option( 'tcp_discounts_by_order', $discounts );?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount modified', 'tcp_discount' );?>
		</p></div><?php
	}
}
?>
<table class="widefat fixed" cellspacing="0">
<thead>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Greather than', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Maximum', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column" style="width: 20%;">&nbsp;</th>
</tr>
</thead>

<tfoot>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Greather than', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Maximum', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column" style="width: 20%;">&nbsp;</th></tr>
</tfoot>
<tbody>
<?php
if ( is_array( $discounts ) || count( $discounts ) > 0 )
	foreach( $discounts as $id => $discount_item ) : 
		$active			= isset( $discount_item['active'] ) ? $discount_item['active'] : false;
		$greather_than	= isset( $discount_item['greather_than'] ) ? $discount_item['greather_than'] : 0;
		$discount		= isset( $discount_item['discount'] ) ? $discount_item['discount'] : 0;
		$max			= isset( $discount_item['max'] ) ? $discount_item['max'] : 0;?>
	<tr>
		<form method="post">
			<input type="hidden" name="id" id="id" value="<?php echo $id;?>" />
			<td>
				<input type="checkbox" name="active" id="active" value="yes" <?php checked( $active );?>/>
			</td>
			<td>
				<input type="text" name="greather_than" id="greather_than" value="<?php echo $greather_than;?>" size="10" maxlength="10" /><?php echo $currency;?>
			</td>
			<td>
				<input type="text" name="discount" id="discount" value="<?php echo $discount;?>" size="10" maxlength="10" />%
			</td>
			<td>
				<input type="text" name="max" id="max" value="<?php echo $max;?>" size="10" maxlength="10" /><?php echo $currency;?>
			</td>
			<td>
				<input type="submit" name="modify_discount_by_order" id="modify_discount_by_order" value="<?php _e( 'modify', 'tcp_discount' );?>" class="button-secondary" />
				<a href="#" onclick="jQuery('.delete_discount').hide();jQuery('#delete_by_order_<?php echo $id;?>').show();" class="delete"><?php _e( 'delete', 'tcp_discount' );?></a>
				<div id="delete_by_order_<?php echo $id;?>" class="delete_discount" style="display:none; border: 1px dotted orange; padding: 2px">
					<p><?php _e( 'Do you really want to delete this discount?', 'tcp_discount' );?></p>
					<input type="submit" name="delete_discount_by_order" id="delete_discount_by_order" value="<?php _e( 'Yes', 'tcp_discount' );?>"  class="button-secondary"/>
					<a href="#" onclick="jQuery('#delete_by_order_<?php echo $id;?>').hide();"><?php _e( 'No, I don\'t' , 'tcp_discount' );?></a>
					</form>
				</div>
			</td>
		</form>
	</tr>
	<?php endforeach;?>
	<tr>
		<th scope="col" class="manage-column" colspan="5"><?php _e( 'Add new discount', 'tcp_discount' );?></th>
	</tr>
	<tr>
		<form method="post">
			<td>
				<input type="checkbox" name="active" id="active" value="yes" checked="true"/>
			</td>
			<td>
				<input type="text" name="greather_than" id="greather_than" value="" size="10" maxlength="10" /><?php echo $currency;?>
			</td>
			<td>
				<input type="text" name="discount" id="discount" value="" size="10" maxlength="10" />%
			</td>
			<td>
				<input type="text" name="max" id="max" value="" size="10" maxlength="10" /><?php echo $currency;?>
			</td>
			<td>
				<input type="submit" name="add_discount_by_order" id="add_discount_by_order" value="<?php _e( 'add', 'tcp_discount' );?>" class="button-secondary"/>
			</td>
		</form>
	</tr>
</tbody>
</table>

<h3><?php _e( 'By Product', 'tcp_discount' );?></h3>
<?php
$id			= isset( $_REQUEST['id'] ) ? (int)$_REQUEST['id'] : 0;
$active		= isset( $_REQUEST['active'] );
$product_id	= isset( $_REQUEST['product_id'] ) ? (int)$_REQUEST['product_id'] : 0;
$type		= isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : 'amount';
$value		= isset( $_REQUEST['value'] ) ? $_REQUEST['value'] : 0;
$discounts	= get_option( 'tcp_discounts_by_product', array() );
if ( isset( $_REQUEST['add_discount_by_product'] ) ) {
	if ( $value <= 0 ) { ?>
		<div id="message" class="updated"><p>
			<?php _e( 'The value must be a number greather than zero', 'tcp_discount' );?>
		</p></div><?php
	} else {
		$discounts[] = array (
			'active'		=> $active,
			'product_id'	=> $product_id,
			'type'			=> $type,
			'value'			=> $value,
		);
		rsort( $discounts );
		update_option( 'tcp_discounts_by_product', $discounts );?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount added', 'tcp_discount' );?>
		</p></div><?php
	}	
} elseif ( isset( $_REQUEST['delete_discount_by_product'] ) ) {
	unset( $discounts[$id] );
	update_option( 'tcp_discounts_by_product', $discounts );?>
	<div id="message" class="updated"><p>
		<?php _e( 'Discount deleted', 'tcp_discount' );?>
	</p></div><?php
} elseif ( isset( $_REQUEST['modify_discount_by_product'] ) ) {
	$discounts[$id]['active'] = $active;
	$discounts[$id]['type'] = $type;
	$discounts[$id]['value'] = $value;
	rsort( $discounts );
	update_option( 'tcp_discounts_by_product', $discounts );?>
	<div id="message" class="updated"><p>
		<?php _e( 'Discount modified', 'tcp_discount' );?>
	</p></div><?php
}
?>
<table class="widefat fixed" cellspacing="0">
<thead>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Product', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Type', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Value', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column" style="width: 20%;">&nbsp;</th>
</tr>
</thead>
<tfoot>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Active', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Product', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Type', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Value', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column" style="width: 20%;">&nbsp;</th></tr>
</tfoot>
<tbody><?php
if ( is_array( $discounts ) || count( $discounts ) > 0 )
	foreach( $discounts as $id => $discount_item ) : 
		$active		= isset( $discount_item['active'] ) ? $discount_item['active'] : false;
		$product_id	= isset( $discount_item['product_id'] ) ? $discount_item['product_id'] : 0;
		$type		= isset( $discount_item['type'] ) ? $discount_item['type'] : 'amount';
		$value		= isset( $discount_item['value'] ) ? $discount_item['value'] : 0;?>
	<tr>
		<form method="post">
			<input type="hidden" name="id" id="id" value="<?php echo $id;?>" />
			<td>
				<input type="checkbox" name="active" id="active" value="yes" <?php checked( $active );?>/>
			</td>
			<td>
				<?php echo get_the_title( $product_id );?>
			</td>
			<td>
				<select name="type" id="type">
					<option value='amount' <?php selected( $type, 'amount' );?>><?php _e( 'Amount', 'tcp_discount' );?></option/>
					<option value='percent' <?php selected( $type, 'percent' );?>><?php _e( 'Percent', 'tcp_discount' );?></option/>
					<option value='freeshipping' <?php selected( $type, 'freeshipping' );?>><?php _e( 'Free Shiping', 'tcp_discount' );?></option/>
				</select>
			</td>
			<td>
				<input type="text" name="value" id="value" value="<?php echo $value;?>" size="10" maxlength="10" /><?php echo $currency;?>/%
			</td>
			<td>
				<input type="submit" name="modify_discount_by_product" id="modify_discount_by_product" value="<?php _e( 'modify', 'tcp_discount' );?>" class="button-secondary" />
				<a href="#" onclick="jQuery('.delete_discount').hide();jQuery('#delete_by_product_<?php echo $id;?>').show();" class="delete"><?php _e( 'delete', 'tcp_discount' );?></a>
				<div id="delete_by_product_<?php echo $id;?>" class="delete_discount" style="display:none; border: 1px dotted orange; padding: 2px">
					<p><?php _e( 'Do you really want to delete this discount?', 'tcp_discount' );?></p>
					<input type="submit" name="delete_discount_by_product" id="delete_discount_by_product" value="<?php _e( 'Yes', 'tcp_discount' );?>"  class="button-secondary"/>
					<a href="#" onclick="jQuery('#delete_by_product_<?php echo $id;?>').hide();"><?php _e( 'No, I don\'t' , 'tcp_discount' );?></a>
					</form>
				</div>
			</td>
		</form>
	</tr>
	<?php endforeach;?>
	<tr>
		<th scope="col" class="manage-column" colspan="5"><?php _e( 'Add new discount', 'tcp_discount' );?></th>
	</tr>
	<tr>
		<form method="post">
			<td>
				<input type="checkbox" name="active" id="active" value="yes" checked="true"/>
			</td>
			<td><?php
					$args = array(
						'post_type'			=> 'tcp_product',
						'orderby'			=> 'title',
						'order'				=> 'ASC',
						'posts_per_page'	=> -1,
					);
					query_posts( $args );
					if ( have_posts() ) : ?>
						<select name="product_id" id="product_id">
						<?php while ( have_posts() ) : the_post();?>
							<option value="<?php the_ID();?>"><?php the_title();?></option>
						<?php endwhile;?>
						</select>
					<?php endif;
					wp_reset_postdata();
					wp_reset_query();?>
			</td>
			<td>
				<select name="type" id="type">
					<option value="amount"><?php _e( 'Amount', 'tcp_discount' );?></option>
					<option value="percent"><?php _e( 'Percent', 'tcp_discount' );?></option>
					<option value="freeshiping"><?php _e( 'Free Shiping', 'tcp_discount' );?></option>
				</select>
			</td>
			<td>
				<input type="text" name="value" id="value" value="" size="10" maxlength="10" /><?php echo $currency;?>/%
			</td>
			<td>
				<input type="submit" name="add_discount_by_product" id="add_discount_by_product" value="<?php _e( 'add', 'tcp_discount' );?>" class="button-secondary"/>
			</td>
		</form>
	</tr>
</tbody>
</table>

</div>
