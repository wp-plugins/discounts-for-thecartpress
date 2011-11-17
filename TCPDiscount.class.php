<?php
/*
Plugin Name: TheCartPress Discounts
Plugin URI: http://thecartpress.com
Description: Discounts for TheCartPress
Version: 1.0.3
Author: TheCartPress team
Author URI: http://thecartpress.com
License: GPL
Parent: thecartpress
*/

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

if ( ! class_exists( 'TCPDiscount' ) ) {
class TCPDiscount {

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
					$discounts_by_product = $this->getDiscountByProduct( $discounts, $item->getPostId(), $item->getOption1Id(), $item->getOption2Id() );
					if ( is_array( $discounts_by_product ) && count( $discounts_by_product ) > 0 ) {
						foreach( $discounts_by_product as $discount ) {
							if ( $discount['type'] == 'freeshipping' ) {
								$item->setFreeShipping();
							} elseif ( $discount['type'] == 'amount' ) {
								$amount = $discount['value'] * $item->getUnits();
								$item->addDiscount( $amount );//Applaying more than one discount
							} elseif ( $discount['type'] == 'percent' ) {
								$amount = $item->getUnitPrice() * ( $discount['value'] / 100 );
								$amount = $amount * $item->getUnits();
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
			$total = apply_filters( 'tcp_discount_get_total_for_discount', $total );
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
						$shoppingCart->addDiscount( 'discount_by_order', $discount_amount );//Only one discount
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
				$label .= '</span>';
			}
			if ( count( $percents ) > 0 ) {//accumulates the percentages
				foreach( $percents as $percent ) {
					$amount = tcp_number_format( $percent, 0 );
					$label .= $price . '<span class="tcp_item_discount tcp_item_discount_' . $amount . '">';
					$label .= sprintf( __( '%s&#37; Off', 'tcp_discount' ), $amount );
					$label .= '</span>';
				}
			}
			/*if ( count( $percents ) > 0 ) {//accumulates the percentages
				foreach( $percents as $percent ) {
					$by_unit = $item->getUnitPrice() * ( $percent / 100 );
					$amount += $by_unit * $item->getUnits();
				}
			}
			$label = '<span class="tcp_item_before_discount">' . $price . '</span><span class="tcp_item_discount">';
			$label .= sprintf( __( 'Discount -%s', 'tcp_discount' ), tcp_format_the_price( $amount ) );
			$label .= '</span>';*/
			//TODO sumar todo... + tachado + clase de descuento en td...
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

	private function hasDiscountsByProduct( $product_id, $option_id_1 = 0, $option_id_1 = 0 ) {
		$discounts = $this->getDiscountsByProduct();
		$discounts = $this->getDiscountByProduct( $discounts, $product_id, $option_id_1, $option_id_1 );
		return count( $discounts ) > 0;
	}

	private function getDiscountsByProduct() {
		$discounts = get_option( 'tcp_discounts_by_product', array() );
		$discounts = apply_filters( 'tcp_discount_by_product_get_discounts', $discounts );
		return $discounts;
	}

	private function getDiscountByProduct( $discounts, $product_id, $option_id_1 = 0, $option_id_2 = 0 ) {
		$discounts_by_product = array();
		if ( is_array( $discounts ) && count( $discounts ) > 0 )
			foreach( $discounts as $discount_item ) {
				$active = isset( $discount_item['active'] ) ? $discount_item['active'] : false;
				if ( $active ) {
					if ( $discount_item['product_id'] == $product_id ) {
						$discount_option_id_1 = isset( $discount_item['option_id_1'] ) ? $discount_item['option_id_1'] : -1;
						if ( $discount_option_id_1 == $option_id_1 ) {
							$discount_option_id_2 = isset( $discount_item['option_id_2'] ) ? $discount_item['option_id_2'] : -1;
							if ( $discount_option_id_2 == $option_id_2 ) {
								$discounts_by_product[] = $discount_item;
							}
						}
					}
				}
			}
		return $discounts_by_product;
	}

	function tcp_options_title( $option_title, $product_id, $option_id_1 = 0, $option_id_2 = 0 ) {
		$discounts = $this->getDiscountsByProduct();
		$discounts = $this->getDiscountByProduct( $discounts, $product_id, $option_id_1, $option_id_2 );
		if ( count( $discounts ) > 0 ) {
			return $option_title . ' ' . sprintf( __( '(%s off)', 'tcp_discount' ), tcp_format_the_price( $discounts[0]['value'] ) );
		}
		return $option_title;
	}

	//Coupons
	public function tcp_checkout_after_order_cart() { 
		if ( isset( $_SESSION['tcp_checkout']['coupon_code'] ) ) {
			$coupon_code = $_SESSION['tcp_checkout']['coupon_code']; 
			if ( strlen( $coupon_code ) == 0 || $this->is_coupon_valid( $coupon_code ) ) {
				$coupon_invalid = false;
			} else {
				$coupon_invalid = true;
			}
		} else {
			$coupon_code = ''; 
		} ?>
		<p>
			<label for="tcp_coupon"><?php _e( 'Coupon code', 'tcp_discount' ); ?>:&nbsp;<input type="text" name="tcp_coupon_code" id="tcp_coupon_code" value="<?php echo $coupon_code; ?>" /></label>
			<input type="submit" name="tcp_add_coupon" id="tcp_add_coupon" value="<?php _e( 'Add coupon', 'tcp_discount' ); ?>" class="tcp_checkout_button tcp_add_coupon" />
			<input type="submit" name="tcp_remove_coupon" id="tcp_remove_coupon" value="<?php _e( 'Remove coupon', 'tcp_discount' ); ?>" class="tcp_checkout_button tcp_remove_coupon" />
			<?php if ( strlen( $coupon_code ) > 0 && $coupon_invalid ) echo '<span class="error">', __( 'The Coupon is invalid or it is out of date. Remember that there are coupons for registered users only.', 'tcp_dicount' ), '</span>'; ?>
		</p><?php
	}

	public function tcp_before_cart_box( $action ) {
		/*if ( isset( $_REQUEST['tcp_coupon_code'] ) ) {
			$_SESSION['tcp_checkout']['coupon_code'] = $_REQUEST['tcp_coupon_code'];
		}*/
		return $action;
	}

	private function is_coupon_valid( $coupon_code ) {
		$coupons = get_option( 'tcp_coupons', array() );
		foreach( $coupons as $coupon ) {
			if ( $coupon['active'] && $coupon['coupon_code'] == $coupon_code ) {
				if ( $coupon['from_date'] <= time() ) {
					if ( $coupon['to_date'] == '' ) $valid = true;
					elseif ( $coupon['to_date'] > time() ) $valid = true;
					else $valid = false;
					if ( $valid && $coupon['uses_per_coupon'] == 0 ) $valid = false;
					if ( $valid && $coupon['uses_per_user'] > 0 ) {
						$current_user = wp_get_current_user();
						if ( $current_user->ID == 0 ) {
							$valid = false; //Only for registered user
						} else {
							$user_coupons = get_user_meta( $current_user->ID, 'tcp_coupons', true );
							if ( isset( $user_coupons[$coupon_code]['quantity'] ) ) {
								if ( $user_coupons[$coupon_code]['quantity'] >= $coupon['uses_per_user'] ) $valid = false;
							} else {
								$valid = true;
							}
						}
					}		
					if ( $valid ) return true;
				}
			}
		}
		return false;
	}

	private function create_coupon_id( $coupon_code ) {
		return 'coupon-' . $coupon_code;
	}

	public function wp_head() {
		if ( isset( $_REQUEST['tcp_add_coupon'] ) && isset( $_REQUEST['tcp_coupon_code'] ) ) {
			$_SESSION['tcp_checkout']['coupon_code'] = $_REQUEST['tcp_coupon_code'];
			$coupon_code = $_SESSION['tcp_checkout']['coupon_code'];
			$shoppingCart = TheCartPress::getShoppingCart();
			//$shoppingCart->deleteDiscount( $this->create_coupon_id( $coupon_code ) );
			$shoppingCart->deleteAllCartDiscounts();
			$coupons = get_option( 'tcp_coupons', array() );
			foreach( $coupons as $coupon ) {
				if ( $coupon['active'] && $coupon['coupon_code'] == $coupon_code ) {
					if ( $coupon['from_date'] <= time() ) {
						if ( $coupon['to_date'] == '' ) $ok = true;
						elseif ( $coupon['to_date'] > time() ) $ok = true;
						else $ok = false;
						if ( $ok && $coupon['uses_per_coupon'] == 0 ) $ok = false;
						if ( $ok && $coupon['uses_per_user'] > 0 ) {
							$current_user = wp_get_current_user();
							if ( $current_user->ID == 0 ) {
								$ok = false; //Only for registered user
							} else {
								$user_coupons = get_user_meta( $current_user->ID, 'tcp_coupons', true );
								if ( is_array( $user_coupons ) ) {
									foreach( $user_coupons as $user_coupon_code => $user_coupon_uses ) {
										if ( $user_coupon_code == $coupon_code && $user_coupon_uses['quantity'] >= $coupon['uses_per_user'] ) {
											$ok = false;
											break;
										}
									}
									$ok = true;
								} else {
									$ok = true; //never used
								}
							}
						}
						if ( $ok ) {
							if ( $coupon['coupon_type'] == 'amount' ) {
								$discount = $coupon['coupon_value'];
							} elseif ( $coupon['coupon_type'] == 'percent' ) {
								$total = $shoppingCart->getTotal();
								$discount = $total * $coupon['coupon_value'] / 100;
							//} elseif ( $coupon['coupon_type'] == 'freeshipping' ) {
							//	$shoppingCart = TheCartPress::getShoppingCart();
							//	$shoppingCart->setFreeShipping( true );
							} else {
								$discount = apply_filters( 'tcp_apply_discount_type', $discount, $coupon['coupon_type'], $coupon );
							}
							$shoppingCart->addDiscount( $this->create_coupon_id( $coupon_code ), $discount );
						}
					}
					break;
				}
			}
		} elseif ( isset( $_REQUEST['tcp_remove_coupon'] ) ) {
			$shoppingCart = TheCartPress::getShoppingCart();
			if ( isset( $_REQUEST['tcp_coupon_code'] ) && strlen( $_REQUEST['tcp_coupon_code'] ) > 0 ) {
				$coupon_code = $_REQUEST['tcp_coupon_code'];
				$shoppingCart->deleteDiscount( $this->create_coupon_id( $coupon_code ) );
			} else {
				$shoppingCart->deleteAllCartDiscounts();
			}
			unset( $_SESSION['tcp_checkout']['coupon_code'] );
		}
	}

	//When the order is created
	public function tcp_checkout_ok( $order_id ) {
		if ( isset( $_SESSION['tcp_checkout']['coupon_code'] ) ) {
			$coupon_code = $_SESSION['tcp_checkout']['coupon_code'];
			$coupons = get_option( 'tcp_coupons', array() );
			foreach( $coupons as $id => $coupon ) {
				if ( $coupon['coupon_code'] == $coupon_code ) {
					if ( $coupon['uses_per_coupon'] > 0 ) $coupons[$id]['uses_per_coupon']--;
					if ( $coupon['uses_per_user'] > 0 ) {
						$current_user = wp_get_current_user();
						if ( $current_user->ID > 0 ) {
							$user_coupons = get_user_meta( $current_user->ID, 'tcp_coupons' );
							if ( is_array( $user_coupons ) && isset( $user_coupons[$coupon_code]['quantity'] ) ) {
								$user_coupons[$coupon_code]['quantity']++;
							} else {
								$user_coupons = array( $coupon_code => array( 'quantity' => 1, 'coupon_type' => $coupon['coupon_type'], 'coupon_value' => $coupon['coupon_value'] ) );
							}
							update_user_meta( $current_user->ID, 'tcp_coupons', $user_coupons );
						}
					}
					add_order_meta( $order_id, 'tcp_coupon', $coupon );
					update_option( 'tcp_coupons', $coupons );
				}
			}
			//unset( $_SESSION['tcp_checkout']['coupon_code'] );
		}
	}

	public function tcp_admin_order_after_editor( $order_id ) {
		$coupon = get_order_meta( $order_id, 'tcp_coupon' );
		if ( is_array( $coupon ) ) : ?>
			<tr>
			<th scope="row"><?php _e( 'Coupon', 'tcp_discount' ); ?></th>
			<td>
			<?php $to_date = isset( $coupon['to_date'] ) && $coupon['to_date'] != '' ? strftime( '%Y/%m/%d', $coupon['to_date'] ) : __( 'no limit', 'tcp_discount' );
			if ( $coupon['coupon_type'] == 'percent' ) printf( __( 'Code: %s, Type: %d&#37;, from: %s to %s', 'tcp_discount' ), $coupon['coupon_code'], $coupon['coupon_value'], strftime( '%Y/%m/%d', $coupon['from_date'] ), $to_date );
			else printf( __( 'Code: %s, Type: %s, Amount: %d, from: %s to %s', 'tcp_discount' ), $coupon['coupon_code'], $coupon['coupon_type'], tcp_format_the_price( $coupon['coupon_value'] ), strftime( '%Y/%m/%d', $coupon['from_date'] ), $to_date );?>
			</td>
			</tr>
		<?php endif;
	}

	public function personal_options( $profileuser ) {
		$user_coupons = get_user_meta ( $profileuser->ID, 'tcp_coupons', true );
		if ( is_array( $user_coupons ) && count( $user_coupons ) > 0 ) : ?>
		<tr class="tcp_coupons">
			<th scope="row"><?php _e( 'Coupons used', 'tcp_discount' ); ?></th>
			<td>
			<?php foreach( $user_coupons as $coupon_code => $coupon ) : ?>
				<p><?php if ( $coupon['coupon_type'] == 'percent' ) printf( __( 'Code: %s, Type: %d&#37;', 'tcp_discount' ), $coupon_code, $coupon['coupon_value'] );
			else printf( __( 'Code: %s, Type: %s, Amount: %d', 'tcp_discount' ), $coupon_code, $coupon['coupon_type'], tcp_format_the_price( $coupon['coupon_value'] ) );?></p>
			<?php endforeach; ?>
			</td>
		</tr>
		<?php endif;
	}

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
			//Coupons
			add_action( 'tcp_admin_order_after_editor', array( $this, 'tcp_admin_order_after_editor' ) );
			add_Action( 'personal_options', array( $this, 'personal_options' ) );
		} else {
			add_action( 'tcp_add_shopping_cart', array( $this, 'shoppingcart_modify' ) );
			add_action( 'tcp_shopping_cart_modify_units', array( $this, 'shoppingcart_modify' ) );
			add_action( 'tcp_delete_item_shopping_cart', array( $this, 'shoppingcart_modify' ) );
			add_action( 'tcp_modify_shopping_cart', array( $this, 'shoppingcart_modify' ) );
			add_filter( 'tcp_get_the_price_label', array( $this, 'tcp_get_the_price_label' ), 10, 2 );
			add_filter( 'tcp_buy_button_get_product_classes', array( $this, 'tcp_buy_button_get_product_classes' ), 10, 2 );
			add_filter( 'tcp_options_title', array( $this, 'tcp_options_title'), 10, 4 );
			//Coupons
			add_action( 'wp_head', array( $this, 'wp_head' ) );
			add_action( 'tcp_before_cart_box', array( $this, 'tcp_before_cart_box' ) );
			add_Action( 'tcp_checkout_after_order_cart', array( $this, 'tcp_checkout_after_order_cart' ) );
			add_action( 'tcp_checkout_ok', array( $this, 'tcp_checkout_ok' ) );
		}
	}
}

new TCPDiscount();
}
?>
