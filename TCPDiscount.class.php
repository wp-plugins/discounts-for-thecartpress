<?php
/*
Plugin Name: TheCartPress Discounts
Plugin URI: http://thecartpress.com
Description: Discounts for TheCartPress
Version: 1.0
Author: TheCartPress team
Author URI: http://thecartpress.com
License: GPL
Parent: thecartpress
*/

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

class TCPDiscount {

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
		} else {
			add_action( 'tcp_add_shopping_cart', array( $this, 'shoppingcart_modify' ) );
			add_action( 'tcp_shopping_cart_modify_units', array( $this, 'shoppingcart_modify' ) );
			add_action( 'tcp_delete_item_shopping_cart', array( $this, 'shoppingcart_modify' ) );
			add_action( 'tcp_modify_shopping_cart', array( $this, 'shoppingcart_modify' ) );
		}
	}

	public function init() {
		if ( ! function_exists( 'is_plugin_active' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'thecartpress/TheCartPress.class.php' ) )  {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
		if ( function_exists( 'load_plugin_textdomain' ) )
			load_plugin_textdomain( 'tcp_discount', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	function admin_notices() {
		echo '<div class="error">
			<p>', __( '<strong>Discount for TheCartPress</strong> requires TheCartPress plugin is activated.', 'tcp_discount' ), '</p>
		</div>';
	}

	function admin_menu() {
		$base = dirname( dirname( __FILE__ ) ) . '/thecartpress/admin/OrdersList.php';
		add_submenu_page( $base, __( 'Discounts', 'tcp_discount' ), __( 'Discounts', 'tcp_discount' ), 'tcp_edit_settings', dirname( __FILE__ ) . '/admin/Discounts.php' );
	}

	function shoppingcart_modify() {
		$discounts = get_option( 'tcp_discounts', array() );
		if ( is_array( $discounts ) || count( $discounts ) > 0 ) {
			$shoppingCart = TheCartPress::getShoppingCart();
			$shoppingCart->setDiscount( 0 );
			$total = $shoppingCart->getTotal();
			foreach( $discounts as $discount_item ) {
				$greather_than	= isset( $discount_item['greather_than'] ) ? $discount_item['greather_than'] : 0;
				if ( $total > $greather_than ) {
					$discount = isset( $discount_item['discount'] ) ? $discount_item['discount'] : 0;
					$max = isset( $discount_item['max'] ) ? $discount_item['max'] : 0;
					if ( $discount > 0 ) {
						$discount_amount = $total * $discount / 100;
						if ( $max > 0 && $discount_amount > $max ) $discount_amount = $max;
					} else {
						$discount_amount = $max;
					}
					$shoppingCart->setDiscount( $discount_amount );
					return;
				}
			}
		}
	}
}

new TCPDiscount();
?>
