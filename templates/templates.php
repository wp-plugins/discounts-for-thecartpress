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
 *
 * @since 1.0.9
 */
function tcp_the_discount( $post_id = 0 ) {
	echo tcp_get_the_discount( $post_id );
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
 *
 * @since 1.0.9
 */
function tcp_the_discount_value( $post_id = 0 ) {
	echo tcp_get_the_discount_value( $post_id );
}

/**
 * Returns the value of the discount (amount or percentage)
 *
 * @since 1.0.9
 */
function tcp_get_the_discount_value( $post_id = 0 ) {
	if ( $post_id == 0 ) $post_id = get_the_ID();
	$post_id = tcp_get_default_id( $post_id, get_post_type( $post_id ) );
	global $tcp_discount;
	$discounts = $tcp_discount->getDiscountsByProduct();
	$discounts = $tcp_discount->getDiscountByProduct( $discounts, $post_id );
	$discounts = $tcp_discount->getDiscountByCouponByProduct( $discounts, $post_id );
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
 *
 * @since 1.0.9
 */
function tcp_the_price_label_without_discount( $before = '', $after = '' ) {
	echo $before, tcp_get_the_price_label_without_discount(), $after;
}

/**
 * Returns the price without discount, with currency
 *
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

//
//Coupons
//
function tcp_create_coupons_table() {
	// global $wpdb;
	// $sql = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'tcp_coupons` (
	// 	`coupon_id`			bigint(20) 		unsigned NOT NULL auto_increment,
	// 	`active`			bool			NOT NULL ,
	// 	`coupon_code`		varchar(100) 	NOT NULL,
	// 	`coupon_type`		varchar(50) 	NOT NULL,
	// 	`coupon_value`		decimal(13, 2)	NOT NULL ,
	// 	`from_date`			datetime		NOT NULL,
	// 	`to_date`			datetime		NOT NULL,
	// 	`uses_per_coupon`	varchar(100)	NOT NULL,
	// 	`uses_per_user`		varchar(50)		NOT NULL,
	// 	`by_product`		bool			NOT NULL,
	// 	`product_id`		bigint(20)		unsigned NOT NULL,
	// 	PRIMARY KEY  (`coupon_id`)
	// ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=\'Coupons\';';
	// $wpdb->query( $sql );
}

function tcp_get_coupons() {
	return get_option( 'tcp_coupons', array() );
	// global $wpdb;
	// $sql = 'select * from `' . $wpdb->prefix . 'tcp_coupons`';
	// return $wpdb->get_results( $sql, ARRAY_A );
}

function tcp_add_coupon( $active, $coupon_code, $coupon_type, $coupon_value, $from_date, $to_date = '', $uses_per_coupon = 1, $uses_per_user = 1, $by_product = false, $product_id = false ) {
	$coupons = tcp_get_coupons();
	$coupons[] = array (
		'active'			=> $active,
		'coupon_code'		=> $coupon_code,
		'coupon_type'		=> $coupon_type,
		'coupon_value'		=> $coupon_value,
		'from_date'			=> $from_date,
		'to_date'			=> $to_date,
		'uses_per_coupon'	=> $uses_per_coupon,
		'uses_per_user'		=> $uses_per_user,
	 	'by_product'		=> $by_product,
		'product_id'		=> $product_id,
	);
	update_option( 'tcp_coupons', $coupons );

	// global $wpdb
	// $wpdb->insert( $wpdb->prefix . 'tcp_coupons', array(
	// 	'active'				=> $active,
	// 	'coupon_code'			=> $coupon_code,
	// 	'coupon_type'			=> $coupon_type,
	// 	'coupon_value'			=> $coupon_value,
	// 	'from_date'				=> $from_date,
	// 	'to_date'				=> $to_date,
	// 	'uses_per_coupon'		=> $uses_per_coupon,
	// 	'uses_per_user'			=> $uses_per_user,
	// 	'by_product'			=> $by_product,
	// 	'product_id'			=> $product_id,
	// ), array( '%f', '%s', '%s', '%d', '%s', '%s', '%f', '%d' ) );
	// return $wpdb->insert_id;
}

/**
 * Adds coupons to the current list of coupons
 *
 * @param Array $coupons_to_added
 */
function tcp_add_coupons( $coupons_to_added ) {
	$coupons = tcp_get_coupons();
	$coupons = array_merge( $coupons, $coupons_to_added );
	unset( $coupons );
	update_option( 'tcp_coupons', $coupons );
}

/**
 * Set coupons to the current list of coupons
 *
 * @param Array $coupons_to_added
 */
function tcp_set_coupons( $coupons ) {
	update_option( 'tcp_coupons', $coupons );
	unset( $coupons );
}

/**
 * Check if a given coupon exists
 *
 * @param $coupon_code
 * @since 1.3.2
 * @uses tcp_get_coupon
 */
function tcp_exists_coupon( $coupon_code = false ) {
	$coupon = tcp_get_coupon( $coupon_code );
	return $coupon !== false;

	// global $wpdb;
	// $sql = 'select count(*) from `' . $wpdb->prefix . 'tcp_coupons` ' . $wpdb->prepare( 'where coupon_code = %d', $order_id );
	// return $wpdb->get_row( $sql ) > 1;
}

/**
 * Checks if exists one or more active coupons
 *
 * @since 1.3.2
 * @uses tcp_get_coupon
 */
function exists_active_coupons() {
	$coupons = get_option( 'tcp_coupons', array() );
	foreach( $coupons as $coupon )
		if ( $coupon['active'] ) {
			unset( $coupons );
			return true;
		}
	unset( $coupons );
	return false;

	// global $wpdb;
	// $sql = 'select count(*) from `' . $wpdb->prefix . 'tcp_coupons` where active = true';
	// return $wpdb->get_row( $sql );
}

/**
 * Returns a coupon of a given coupon. If not given coupon it uses the session coupon
 *
 * @param $coupon_code, if false, the funtions gets the coupon from the session
 * @return Array(Coupon)/Bool False if the coupon code doesn't exist
 * @since 1.3.2
 */
function tcp_get_coupon( $coupon_code = false ) {
	if ( $coupon_code == false ) {
		if ( ! isset( $_SESSION['tcp_checkout']['coupon_code'] ) ) return false;
		$coupon_code = $_SESSION['tcp_checkout']['coupon_code'];
	};

	$coupons = tcp_get_coupons();
	foreach( $coupons as $coupon ) {
		if ( $coupon['coupon_code'] == $coupon_code ) {
			unset( $coupons );
			return $coupon;
		}
	}
	unset( $coupons );
	return false;

	// global $wpdb;
	// $sql = 'select count(*) from `' . $wpdb->prefix . 'tcp_coupons` ' . $wpdb->prepare( 'where coupon_code = %s', $coupon_code );
	// return $wpdb->get_row( $sql, ARRAY_A );
}

function tcp_modify_coupon( $id, $active, $coupon_type, $coupon_value, $from_date, $to_date = '', $uses_per_coupon = 1, $uses_per_user = 1, $by_product = false, $product_id = false ) {
	$coupons = tcp_get_coupons();
	$coupons[$id] = array(
		'active'			=> $active,
		'coupon_code'		=> $coupons[$id]['coupon_code'],
		'coupon_type'		=> $coupon_type,
		'coupon_value'		=> $coupon_value,
		'from_date'			=> $from_date,
		'to_date'			=> $to_date,
		'uses_per_coupon'	=> $uses_per_coupon,
		'uses_per_user'		=> $uses_per_user,
		'by_product'		=> $by_product,
		'product_id'		=> $product_id,
	);
	update_option( 'tcp_coupons', $coupons );
	unset( $coupons );
}

function tcp_delete_coupon( $id ) {
	$coupons = tcp_get_coupons();
	unset( $coupons[$id] );
	update_option( 'tcp_coupons', $coupons );
	unset( $coupons );
}

function tcp_delete_all_coupons() {
	delete_option( 'tcp_coupons' );
}

/**
 * Returns true if the given coupon is valid.
 *
 * @param String $coupon_code, if false, the funtions gets the coupon from the session
 * @return Bool False if the coupon code doesn't exist
 * @since 1.3.2
 */
function tcp_is_coupon_valid( $coupon_code = false ) {
	$coupon = tcp_get_coupon( $coupon_code );
	if ( $coupon === false ) return false;
	if ( !$coupon['active'] ) return false;//&& $coupon['coupon_code'] == $coupon_code ) {
	if ( $coupon['uses_per_coupon'] == 0 ) return false;
	if ( $coupon['from_date'] > time() ) return false;
	if ( $coupon['to_date'] != '' && $coupon['to_date'] + (24 * 60 * 60) < time() ) return false;
	if ( $coupon['uses_per_user'] > 0 ) {
		$current_user = wp_get_current_user();
		if ( $current_user->ID == 0 ) {
			return false; //Only for registered users
		} else {
			$user_coupons = get_user_meta( $current_user->ID, 'tcp_coupons', true );
			if ( isset( $user_coupons[$coupon_code]['quantity'] ) ) {
				return $user_coupons[$coupon_code]['quantity'] < $coupon['uses_per_user'];
			}
		}
	}
	return true;
}

function tcp_is_coupon_code_added() {
	return isset( $_SESSION['tcp_checkout']['coupon_code'] );
}

function tcp_set_coupon_code_added( $coupon_code ) {
	$_SESSION['tcp_checkout']['coupon_code'] = $coupon_code;
}

function tcp_remove_coupon_code_added() {
	unset( $_SESSION['tcp_checkout']['coupon_code'] );
}

//
// Common
//
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