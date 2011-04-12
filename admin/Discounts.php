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
$discounts		= get_option( 'tcp_discounts', array() );
$id				= isset( $_REQUEST['id'] ) ? (int)$_REQUEST['id'] : 0;
$greather_than	= isset( $_REQUEST['greather_than'] ) && (int)$_REQUEST['greather_than'] > 0 ? (float)$_REQUEST['greather_than'] : 0;
$discount		= isset( $_REQUEST['discount'] ) && (int)$_REQUEST['discount'] > 0 ? (float)$_REQUEST['discount'] : 0;
$max			= isset( $_REQUEST['max'] ) && (int)$_REQUEST['max'] > 0 ? (float)$_REQUEST['max'] : 0;

if ( isset( $_REQUEST['add_discount'] ) || isset( $_REQUEST['delete_discount'] ) || isset( $_REQUEST['modify_discount'] ) ) {
	if ( $greather_than == 0 ) {
		echo '<div class="error"><p>', __( '"Greather than" field cannot be zero value', 'tcp_discount' ), '</p></div>';
	} elseif ( $discount == 0 && $max == 0 ) {
		echo '<div class="error"><p>', __( 'One of the fields "Discount" or "Maximum" cannot be zero.', 'tcp_discount' ), '</p></div>';
	} elseif ( isset( $_REQUEST['add_discount'] ) ) {
		$discounts[] = array (
			'greather_than'	=> $greather_than,
			'discount'		=> $discount,
			'max'			=> $max,
		);
		rsort( $discounts );
		update_option( 'tcp_discounts', $discounts );?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount added', 'tcp_discount' );?>
		</p></div><?php
	} elseif ( isset( $_REQUEST['delete_discount'] ) ) {
		unset( $discounts[$id] );
		update_option( 'tcp_discounts', $discounts );?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount deleted', 'tcp_discount' );?>
		</p></div><?php
	} else { //if ( isset( $_REQUEST['modify_discount'] ) ) {
		$discounts[$id] = array (
			'greather_than'	=> $greather_than,
			'discount'		=> $discount,
			'max'			=> $max,
		);
		rsort( $discounts );
		update_option( 'tcp_discounts', $discounts );?>
		<div id="message" class="updated"><p>
			<?php _e( 'Discount modified', 'tcp_discount' );?>
		</p></div><?php
	}
}
?>
<div class="wrap">
<h2><?php _e( 'Discounts', 'tcp_discount' );?></h2>
<ul class="subsubsub">
</ul>
<div class="clear"></div>

<table class="widefat fixed" cellspacing="0">
<thead>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Greather than', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Maximum', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column" style="width: 20%;">&nbsp;</th>
</tr>
</thead>

<tfoot>
<tr>
	<th scope="col" class="manage-column"><?php _e( 'Greather than', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Discount', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column"><?php _e( 'Maximum', 'tcp_discount' );?></th>
	<th scope="col" class="manage-column" style="width: 20%;">&nbsp;</th></tr>
</tfoot>
<tbody>
<?php

$currency = tcp_get_the_currency();
if ( is_array( $discounts ) || count( $discounts ) > 0 )
	foreach( $discounts as $id => $discount_item ) : 
		$greather_than	= isset( $discount_item['greather_than'] ) ? $discount_item['greather_than'] : 0;
		$discount		= isset( $discount_item['discount'] ) ? $discount_item['discount'] : 0;
		$max			= isset( $discount_item['max'] ) ? $discount_item['max'] : 0;?>
	<tr>
		<form method="post">
			<input type="hidden" name="id" id="id" value="<?php echo $id;?>" />
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
				<input type="submit" name="modify_discount" id="modify_discount" value="<?php _e( 'modify', 'tcp_discount' );?>" class="button-secondary" />
				<a href="#" onclick="jQuery('.delete_address').hide();jQuery('#delete_<?php echo $id;?>').show();" class="delete"><?php _e( 'delete', 'tcp_discount' );?></a>
				<div id="delete_<?php echo $id;?>" class="delete_address" style="display:none; border: 1px dotted orange; padding: 2px">
					<p><?php _e( 'Do you really want to delete this discount?', 'tcp_discount' );?></p>
					<input type="submit" name="delete_discount" id="delete_discount" value="<?php _e( 'Yes', 'tcp_discount' );?>"  class="button-secondary"/>
					<a href="#" onclick="jQuery('#delete_<?php echo $id;?>').hide();"><?php _e( 'No, I don\'t' , 'tcp_discount' );?></a>
					</form>
				</div>
			</td>
		</form>
	</tr>
	<?php endforeach;?>
	<tr>
		<th scope="col" class="manage-column" colspan="4"><?php _e( 'Add new discount', 'tcp_discount' );?></th>
	</tr>
	<tr>
		<form method="post">
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
				<input type="submit" name="add_discount" id="add_discount" value="<?php _e( 'add', 'tcp_discount' );?>" class="button-secondary"/>
			</td>
		</form>
	</tr>
</tbody>
</table>
</div>
