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

/**
 * Displays or returns the calculated discount
 * @since 1.0.9
 */
function tcp_the_discount( $post_id = 0, $echo = true ) {
	$label = tcp_get_the_discount( $post_id );
	if ( $echo )
		echo $label;
	else
		return $label;
}

function tcp_get_the_discount( $post_id = 0, $price = 0 ) {
	if ( $post_id == 0 ) $post_id = get_the_ID();
	$post_id = tcp_get_default_id( $post_id, get_post_type( $post_id ) );
	global $tcp_discount;
	$discounts = $tcp_discount->getDiscountsByProduct();
	$discounts = $tcp_discount->getDiscountByProduct( $discounts, $post_id );
	if ( is_array( $discounts ) && count( $discounts ) > 0 ) {
		if ( $price == 0 ) $price = tcp_get_the_price( $post_id );
		$amount = 0;
		$percents = array();
		foreach( $discounts as $discount ) {
			if ( $discount['type'] == 'amount' ) {
				$amount += $discount['value'];
			} elseif ( $discount['type'] == 'percent' ) {
				$percents[] = $discount['value'];
			}
		}
		if ( count( $percents ) > 0 ) {
			//accumulates the percentages
			foreach( $percents as $percent ) {
				$amount += $price * $percent / 100;
			}
		}
		return $amount;
	} else {
		return 0;
	}
}

/**
 * Displays or returns the value of the discount (amount or percentage)
 * @since 1.0.9
 */
function tcp_the_discount_value( $post_id = 0, $echo = true ) {
	$label = tcp_get_the_discount_value( $post_id );
	if ( $echo ) echo $label;
	else return $label;
}

/**
 * Returns the value of the discount (amount or percentage)
 * @since 1.0.9
 */
function tcp_get_the_discount_value( $post_id = 0 ) {
	if ( $post_id == 0 ) $post_id = get_the_ID();
	$post_id = tcp_get_default_id( $post_id, get_post_type( $post_id ) );
	global $tcp_discount;
	$discounts = $tcp_discount->getDiscountsByProduct();
	$discounts = $tcp_discount->getDiscountByProduct( $discounts, $post_id );
	if ( is_array( $discounts ) && count( $discounts ) > 0 ) {
		foreach( $discounts as $discount ) {
			if ( $discount['type'] == 'amount' ) {
				return tcp_format_the_price( $discount['value'] );
			} elseif ( $discount['type'] == 'percent' ) {
				return $discount['value'] . '%';
			}
		}
	} else {
		return false;
	}
}

/**
 * Display the price without discount, with currency
 * @since 1.0.9
 */
function tcp_the_price_label_without_discount( $before = '', $after = '', $echo = true ) {
	$label = tcp_get_the_price_label_without_discount();
	$label = $before . $label . $after;
	if ( $echo ) echo $label;
	else return $label;
}

/**
 * Returns the price without discount, with currency
 * @since 1.0.9
 */
function tcp_get_the_price_label_without_discount( $post_id = 0, $price = false ) {
	if ( $post_id == 0 ) $post_id = get_the_ID();
	$post_id = tcp_get_default_id( $post_id, get_post_type( $post_id ) );
	$price = tcp_get_the_price_to_show( $post_id, $price );
	$label = tcp_format_the_price( $price );
	return $label;
}

function tcp_has_discounts( $post_id = 0, $option_id_1 = 0, $option_id_2 = 0 ) {
	global $tcp_discount;
	if ( $tcp_discount ) {
		if ( $post_id == 0 ) $post_id = get_the_ID();
		$post_id = tcp_get_default_id( $post_id, get_post_type( $post_id ) );
		return $tcp_discount->hasDiscountsByProduct( $post_id, $option_id_1, $option_id_2 );
	}
	return false;
}

function tcp_get_coupons() {
	return get_option( 'tcp_coupons', array() );
}

function tcp_add_coupon( $active, $coupon_code, $coupon_type, $coupon_value, $from_date, $to_date = '', $uses_per_coupon = 1, $uses_per_user = 1) {
	$coupons = tcp_get_coupons();
	$coupons[] = array (
			'active'			=> $active,
			'coupon_code'		=> $coupon_code,
			'coupon_type'		=> $coupon_type,
			'coupon_value'		=> $coupon_value,
			'from_date'			=> $from_date,
			'to_date'			=> $to_date,
			'uses_per_coupon'	=> $uses_per_coupon,
			'uses_per_user'		=> $uses_per_user
		);
	rsort( $coupons );
	update_option( 'tcp_coupons', $coupons );
}

function tcp_modify_coupon( $id, $active, $coupon_type, $coupon_value, $from_date, $to_date = '', $uses_per_coupon = 1, $uses_per_user = 1 ) {
	$coupons = tcp_get_coupons();
	$coupons[$id] = array(
		'active'			=> $active,
		'coupon_code'		=> $coupons[$id]['coupon_code'],
		'coupon_type'		=> $coupon_type,
		'coupon_value'		=> $coupon_value,
		'from_date'			=> $from_date,
		'to_date'			=> $to_date,
		'uses_per_coupon'	=> $uses_per_coupon,
		'uses_per_user'		=> $uses_per_user
	);
	update_option( 'tcp_coupons', $coupons );
}

function tcp_delete_coupon( $id ) {
	$coupons = tcp_get_coupons();
	unset( $coupons[$id] );
	update_option( 'tcp_coupons', $coupons );
}

function tcp_get_discount_types() {
	$discount_types = array(
		'amount'		=> __( 'Amount', 'tcp-discount' ),
		'percent'		=> __( 'Percent', 'tcp-discount' ),
		'freeshipping'	=> __( 'Free Shipping', 'tcp-discount' )
	);
	return apply_filters( 'tcp_discount_types', $discount_types );
}

function tcp_exclude_from_order_discount( $post_id = 0 ) {
	if ( $post_id == 0 ) $post_id = get_the_ID();
	$discount_exclude = get_post_meta( $post_id, 'tcp_discount_exclude', true );
	return apply_filters( 'tcp_exclude_from_order_discount', $discount_exclude, $post_id );
}
?>