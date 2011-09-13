<?php
/*
Plugin Name: TheCartPress Discounts
Plugin URI: http://thecartpress.com
Description: Discounts for TheCartPress
Version: 1.0.1
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
			add_filter( 'tcp_get_the_price_label', array( $this, 'tcp_get_the_price_label' ), 10, 2 );
			add_filter( 'tcp_buy_button_get_product_classes', array( $this, 'tcp_buy_button_get_product_classes' ), 10, 2 );
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
		global $thecartpress;
		if ( $thecartpress ) {
			$base = $thecartpress->get_base();
			add_submenu_page( $base, __( 'Discounts', 'tcp_discount' ), __( 'Discounts', 'tcp_discount' ), 'tcp_edit_settings', dirname( __FILE__ ) . '/admin/Discounts.php' );
		}
	}

	function shoppingcart_modify() {
		$shoppingCart = TheCartPress::getShoppingCart();
		$shoppingCart->deleteAllDiscounts();

		$discounts = $this->getDiscountsByProduct();
		$discounts = apply_filters( 'tcp_discount_by_products', $discounts );
		if ( is_array( $discounts ) || count( $discounts ) > 0 ) { //by product
			$items = $shoppingCart->getItems();
			if ( is_array( $items ) && count( $items ) > 0 ) {
				foreach( $items as $item ) {
					$item->setDiscount( 0 );
					$discounts_by_product = $this->getDiscountByProduct( $discounts, $item->getPostId() );
					if ( is_array( $discounts_by_product ) && count( $discounts_by_product ) > 0 ) {
						foreach( $discounts_by_product as $discount ) {
							if ( $discount['type'] == 'freeshipping' ) {
								$item->setFreeShipping();
							} elseif ( $discount['type'] == 'amount' ) {
								$item->addDiscount( $discount['value'] );//Applaying more than one discount
							} elseif ( $discount['type'] == 'percent' ) {
								$amount = $item->getUnitPrice() * ( $discount['value'] / 100 );
								$item->addDiscount( $amount );//Appling more than one discount
							}
						}
					}
				}
			}
		}
		$discounts = get_option( 'tcp_discounts_by_order', array() );
		$discounts = apply_filters( 'tcp_discount_by_order_get_discounts', $discounts );
		if ( is_array( $discounts ) || count( $discounts ) > 0 ) { //by order
			$total = $shoppingCart->getTotal();
			foreach( $discounts as $discount_item ) {
				$active = isset( $discount_item['active'] ) ? $discount_item['active'] : false;
				if ( $active ) {
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
						break;
					}
				}
			}
		}
	}

	function tcp_get_the_price_label( $price, $post_id ) {
		$discounts = $this->getDiscountsByProduct();
		$discounts = $this->getDiscountByProduct( $discounts, $post_id );
		if ( is_array( $discounts ) && count( $discounts ) > 0 ) {
			$amount = 0;
			$percents = array();
			$freeshipping = false;
			foreach( $discounts as $discount ) {
				if ( $discount['type'] == 'amount' ) {
					$amount += $discount['value'];
				} elseif ( $discount['type'] == 'percent' ) {
					$percents[] = $discount['value'];
				} elseif ( $discount['type'] == 'freeshipping' ) {
					$freeshipping = true;
				}
			}
			$label = '';
			if ( $amount > 0 ) {
				$label = $price . '<span class="tcp_item_discount">';
				$label .= sprintf( __( 'Discount -%s', 'tcp_discount' ), tcp_format_the_price( $amount ) );
			}
			if ( count( $percents ) > 0 ) {//accumulates the percentages
				foreach( $percents as $percent ) {
					$amount = tcp_number_format( $percent, 0 );
					$label .= $price . '<span class="tcp_item_discount tcp_item_discount_' . $amount . '">';
					$label .= sprintf( __( 'Discount %s&#37;', 'tcp_discount' ), $amount );
					$label .= '</span>';
				}
			}
			if ( $freeshipping ) {
				$label .= $price . '<span class="tcp_item_discount">';
				$label .= __( 'Free shipping!', 'tcp_discount' );
			}
			if ( strlen( $label ) > 0 ) {
				return $label;
			} else {
				return $price;
			}
		} else {
			return $price;
		}
	}

	function tcp_buy_button_get_product_classes( $classes, $product_id ) {
		if ( $this->hasDiscountsByProduct( $product_id ) )
			$classes[] = 'tcp_has_discount';
		return $classes;
	}

	private function hasDiscountsByProduct( $product_id ) {
		$discounts = $this->getDiscountsByProduct();
		$discounts = $this->getDiscountByProduct( $discounts, $product_id );
		return count( $discounts ) > 0;
	}

	private function getDiscountsByProduct() {
		$discounts = get_option( 'tcp_discounts_by_product', array() );
		$discounts = apply_filters( 'tcp_discount_by_product_get_discounts', $discounts );
		return $discounts;
	}

	private function getDiscountByProduct( $discounts, $product_id ) {
		$discounts_by_product = array();
		if ( is_array( $discounts ) && count( $discounts ) > 0 )
			foreach( $discounts as $discount_item ) {
				$active = isset( $discount_item['active'] ) ? $discount_item['active'] : false;
				if ( $active ) {
					if ( $discount_item['product_id'] == $product_id ) {
						$discounts_by_product[] = $discount_item;
					}
				}
			}
		return $discounts_by_product;
	}
}

new TCPDiscount();
?>
