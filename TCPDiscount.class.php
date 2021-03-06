<?php
/*
Plugin Name: TheCartPress Discounts
Plugin URI: http://thecartpress.com
Description: Discounts for TheCartPress
Version: 1.3.4
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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TCPDiscount' ) ) :

define( 'TCP_DISCOUNT_FOLDER'			, dirname( __FILE__ ) . '/' );
define( 'TCP_DISCOUNT_ADMIN_FOLDER'		, TCP_DISCOUNT_FOLDER . 'admin/' );
define( 'TCP_DISCOUNT_METABOXES_FOLDER'	, TCP_DISCOUNT_FOLDER . 'metaboxes/' );
define( 'TCP_DISCOUNT_TEMPLATES_FOLDER'	, TCP_DISCOUNT_FOLDER . 'templates/' );

class TCPDiscount {

	function __construct() {
		//includes
		require_once( TCP_DISCOUNT_TEMPLATES_FOLDER . 'templates.php' );
		require_once( TCP_DISCOUNT_METABOXES_FOLDER . 'DiscountMetabox.class.php' );

		//activate action
		register_activation_hook( __FILE__	, array( $this, 'activate_plugin' ) );

		//setup actions
		add_action( 'init'				, array( $this, 'init' ) );
		add_action( 'tcp_init'			, array( $this, 'tcp_init' ) );
		add_action( 'tcp_admin_init'	, array( $this, 'admin_init' ) );
		add_action( 'tcp_admin_menu'	, array( $this, 'tcp_admin_menu' ), 99 );
	}

	function activate_plugin() {
		tcp_create_coupons_table();
	}

	function init() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( ! is_plugin_active( 'thecartpress/TheCartPress.class.php' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	function tcp_init() {
		if ( function_exists( 'load_plugin_textdomain' ) ) {
			load_plugin_textdomain( 'tcp-discount', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
		$this->check_for_shopping_cart_actions();

		add_action( 'tcp_add_shopping_cart'				, array( $this, 'shoppingcart_modify' ) );
		add_action( 'tcp_shopping_cart_modify_units'	, array( $this, 'shoppingcart_modify' ) );
		add_action( 'tcp_delete_item_shopping_cart'		, array( $this, 'shoppingcart_modify' ) );
		add_action( 'tcp_modify_shopping_cart'			, array( $this, 'shoppingcart_modify' ) );
		//add_action( 'tcp_copy_wish_list_to_shopping_cart', array( $this, 'shoppingcart_modify' ) );

		//Last filter for all labels
		add_filter( 'tcp_get_the_price_label'			, array( $this, 'tcp_get_the_price_label' ), 90, 3 );

		add_filter( 'tcp_get_the_product_price'			, array( $this, 'tcp_get_the_product_price' ), 10, 2 );
		add_filter( 'tcp_buy_button_get_product_classes', array( $this, 'tcp_buy_button_get_product_classes' ), 10, 2 );
		add_filter( 'tcp_options_title'					, array( $this, 'tcp_options_title'), 10, 4 );
		add_action( 'tcp_before_cart_box'				, array( $this, 'tcp_before_cart_box' ) );//Coupons
		add_action( 'tcp_checkout_after_order_cart'		, array( $this, 'tcp_checkout_after_order_cart' ) );
		add_action( 'tcp_shopping_cart_after_cart'		, array( $this, 'tcp_checkout_cart_after_cart' ) );
		//add_action( 'tcp_shopping_cart_footer'		, array( $this, 'tcp_checkout_cart_after_cart' ) );
		add_action( 'tcp_checkout_ok'					, array( $this, 'tcp_checkout_ok' ) );
		//API
		add_action( 'tcp_api_update_product'			, array( $this, 'tcp_api_update_product' ), 10, 2 );
	}

	function tcp_api_update_product( $post_id, $params ) {
		$type = isset( $params['discount_type'] ) ? $params['discount_type'] : false;//amount/percent/freeshipping
		$value = isset( $params['discount_value'] ) ? (float)$params['discount_value'] : false;
		if ( $type === false || $value === false ) return;
		$discounts	= get_option( 'tcp_discounts_by_product', array() );
		if ( $value <= 0 && $type != 'freeshipping' ) {
			foreach( $discounts as $id => $discount )
				if ( $discount['product_id'] == $post_id )
					unset( $discounts[$id] );
			update_option( 'tcp_discounts_by_product', $discounts );
		} else {
			foreach( $discounts as $id => $discount ) {
				if ( $discount['product_id'] == $post_id ) {
					unset( $discounts[$id] );
				}
			}
			$discounts[] = array (
				'active'		=> true,
				'product_id'	=> $post_id,
				'option_id_1'	=> 0,
				'option_id_2'	=> 0,
				'type'			=> $type,
				'value'			=> $value,
			);
			rsort( $discounts );
			update_option( 'tcp_discounts_by_product', $discounts );
		}
	}

	function admin_init() {
		add_action( 'tcp_admin_order_after_editor'	, array( $this, 'tcp_admin_order_after_editor' ) );//Coupons
		add_action( 'personal_options'				, array( $this, 'personal_options' ) );
		add_action( 'tcp_localize_settings_page'	, array( $this, 'tcp_localize_settings_page' ) );
		add_filter( 'tcp_localize_settings_action'	, array( $this, 'tcp_localize_settings_action' ) );
	}

	function admin_notices() { ?>
		<div class="error">
			<p><?php _e( '<strong>Discount for TheCartPress</strong> requires TheCartPress plugin is activated.', 'tcp-discount' ); ?></p>
		</div><?php
	}

	function tcp_admin_menu( $thecartpress ) {
		$base = $thecartpress->get_base();
		add_submenu_page( $base, __( 'Discounts', 'tcp-discount' ), __( 'Discounts', 'tcp-discount' ), 'tcp_edit_settings', TCP_DISCOUNT_ADMIN_FOLDER . 'Discounts.php' );
		add_submenu_page( $base, __( 'Coupons', 'tcp-discount' ), __( 'Coupons', 'tcp-discount' ), 'tcp_edit_settings', TCP_DISCOUNT_ADMIN_FOLDER . 'Coupons.php' );
	}

	function tcp_localize_settings_page() {
		global $thecartpress;
		if ( ! isset( $thecartpress ) ) return;
		$discount_layout = $thecartpress->get_setting( 'discount_layout', '' ); ?>

<h3><?php _e( 'Discount Settings', 'tcp'); ?></h3>

<div class="postbox">

<table class="form-table">
<tbody>
<tr valign="top">
	<th scope="row">
	<label for="discount_layout"><?php _e( 'Discount layouts', 'tcp-discount' ); ?></label>
	</th>
	<td>
		<p>
			<label><?php _e( 'Default layouts', 'tcp' ); ?>: <select id="tcp_discount_layout" onchange="jQuery('#discount_layout').val(jQuery('#tcp_discount_layout').val());">>
			<option value="%2$s <strike>%1$s</strike>" <?php selected( $discount_layout, '%2$s <strike>%1$s</strike>' ); ?>>%2$s &lt;strike&gt;%1$s&lt;/strike&gt;</option>
			<option value="<strike>%1$s</strike> %2$s (-%3$s)" <?php selected( $discount_layout, '<strike>%1$s</strike> %2$s (-%3$s)' ); ?>>&lt;strike&gt;%1$s&lt;/strike&gt; %2$s (-%3$s)</option>
			<option value="%2$s (-%3$s)" <?php selected( $discount_layout, '%2$s (-%3$s)' ); ?>>%2$s (-%3$s)</option>
		</select></label>
		</p>
		<label><?php _e( 'Custom layout', 'tcp' ); ?>: <input type="text" name="discount_layout" id="discount_layout" value="<?php echo $discount_layout; ?>" size="35" maxlength="255"/></label>
		<p class="description"><?php _e( '%1$s -> Price before discount; %2$s -> Price after discounr; %3$s -> discount.', 'tcp' ); ?></p>
		<p class="description"><?php _e( 'If this value is left to blank, then TheCartPress will take this layout from the languages configuration files (mo files).', 'tcp' ); ?></p>
	</td>
</tr>
</tbody>
</table>

</div>
	<?php }

	function tcp_localize_settings_action( $settings ) {
		$settings['discount_layout'] = isset( $_POST['discount_layout'] ) ? $_POST['discount_layout'] : '';
		return $settings;
	}

	function shoppingcart_modify() {
		$shoppingCart = TheCartPress::getShoppingCart();
		$shoppingCart->deleteAllDiscounts();
		$this->apply_discount_by_product( $shoppingCart );
		$this->apply_discount_by_order( $shoppingCart );
		$this->apply_coupon_discount( $shoppingCart );
	}

	function apply_discount_by_product( $shoppingCart ) {
		$shoppingCart->deleteAllDiscounts();
		$discounts = $this->getDiscountsByProduct();
		if ( is_array( $discounts ) || count( $discounts ) > 0 ) { //by product
			$items = $shoppingCart->getItems();
			if ( is_array( $items ) && count( $items ) > 0 ) {
				foreach( $items as $item ) {
					$discounts_by_product = $this->getDiscountByProduct( $discounts, $item->getPostId(), $item->getOption1Id(), $item->getOption2Id() );
					$discounts_by_product = apply_filters( 'tcp_apply_discount_by_product', $discounts_by_product, $item->getPostId(), $shoppingCart );
					if ( is_array( $discounts_by_product ) && count( $discounts_by_product ) > 0 ) {
						foreach( $discounts_by_product as $discount ) {
							if ( $discount['type'] == 'freeshipping' ) {
								$item->setFreeShipping();
							} elseif ( $discount['type'] == 'amount' ) {
								$amount = $discount['value'] * $item->getUnits();
								$item->addDiscount( $amount );//Applaying more than one discount
							} elseif ( $discount['type'] == 'percent' ) {
								//$amount = $item->getUnitPrice() * ( $discount['value'] / 100 );
								$amount = $item->getPriceToShow() * ( $discount['value'] / 100 );
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
		if ( is_array( $discounts ) || count( $discounts ) > 0 ) {
			$total = $shoppingCart->getTotalToShow();
			$total_to_calculate = apply_filters( 'tcp_discount_get_total_for_discount', $total );
			$total_for_ranges = apply_filters( 'tcp_discount_get_total_for_discount_for_ranges', $total );
			foreach( $discounts as $discount_item ) {
				$active = isset( $discount_item['active'] ) ? $discount_item['active'] : false;
				if ( $active ) {
					$apply_by_product = isset( $discount_item['apply_by_product'] ) ? $discount_item['apply_by_product'] : false;
					if ( $apply_by_product ) {
						$greather_than = isset( $discount_item['greather_than'] ) ? $discount_item['greather_than'] : 0;
						if ( $total_for_ranges > $greather_than ) {
							$discount = isset( $discount_item['discount'] ) ? $discount_item['discount'] : 0;
							if ( $discount > 0 ) {
								$items = $shoppingCart->getItems();
								if ( is_array( $items ) && count( $items ) > 0 ) {
									foreach( $items as $item ) {
										if ( ! tcp_exclude_from_order_discount( $item->getPostId() ) ) {
											$price = $item->getPriceToShow() - $item->getDiscount() / $item->getUnits();
											$amount = $price * ( $discount / 100 );
											$amount = $amount * $item->getUnits();
											$item->addDiscount( $amount );//Appling more than one discount
										}
									}
									break;
								}
							}
						}
					}
				}
			}
		}
	}

	function apply_discount_by_order( $shoppingCart ) {
		$discounts = get_option( 'tcp_discounts_by_order', array() );
		$discounts = apply_filters( 'tcp_discount_by_order_get_discounts', $discounts );
		if ( is_array( $discounts ) && count( $discounts ) > 0 ) { //by order
			//$total = $shoppingCart->getTotal();
			$total = $shoppingCart->getTotalToShow();
			$total_to_calculate = apply_filters( 'tcp_discount_get_total_for_discount', $total );
			$total_for_ranges = apply_filters( 'tcp_discount_get_total_for_discount_for_ranges', $total );
			foreach( $discounts as $discount_item ) {
				$active = isset( $discount_item['active'] ) ? $discount_item['active'] : false;
				if ( $active ) {
					$apply_by_product = isset( $discount_item['apply_by_product'] ) ? $discount_item['apply_by_product'] : false;
					if ( ! $apply_by_product ) {
						$greather_than = isset( $discount_item['greather_than'] ) ? $discount_item['greather_than'] : 0;
						if ( $total_for_ranges > $greather_than ) {
							$discount = isset( $discount_item['discount'] ) ? $discount_item['discount'] : 0;
							$max = isset( $discount_item['max'] ) ? $discount_item['max'] : 0;
							if ( $discount > 0 ) {
								$discount_amount = $total_to_calculate * $discount / 100;
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
	}

	function tcp_checkout_create_order_cart( $args ) {
		$shoppingCart = TheCartPress::getShoppingCart();
		$shoppingCart->deleteAllDiscounts();
		$this->apply_discount_by_order( $shoppingCart );
	}

	function tcp_get_the_price_label( $label, $post_id, $price ) {
		$discounts = $this->getDiscountsByProduct();
		$discounts = $this->getDiscountByProduct( $discounts, $post_id );
		$discounts = $this->getDiscountByCouponByProduct( $discounts, $post_id );
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
			global $thecartpress;
			$discount_layout = $thecartpress->get_setting( 'discount_layout', '' );
			$decimals = tcp_get_decimal_currency();
			if ( $amount > 0 ) {
				$price -= $amount;
				if ( strlen( $discount_layout ) == 0 ) {
					$label = '<strike class="tcp_strike_price">' . $label . '</strike><span class="tcp_item_discount">';
					$label .= '<span class="tcp_item_current_price">' . tcp_format_the_price( $price ) . '</span>';
					$label .= ' <span class="tcp_item_discount_detail">' . sprintf( __( '(Discount -%s)', 'tcp-discount' ), tcp_format_the_price( $amount ) );
					$label .= '</span>';
				} else {
					$label = sprintf( $discount_layout, $label, tcp_format_the_price( $price ), tcp_format_the_price( $amount ) );
				}
			}
			//TODO else?, acumulative?
			if ( count( $percents ) > 0 ) {//accumulates the percentages?
				foreach( $percents as $percent ) {
					$amount = tcp_number_format( $percent, 0 );
					//$price_amount = $price; //tcp_get_the_price( $post_id );
					$price_amount = $price * ( 1 - $percent / 100 );
					$price_amount = round( $price_amount, $decimals );//TODO new TCP 1.2.9
					if ( strlen( $discount_layout ) == 0 ) {
						$label = '<strike class="tcp_strike_price">' . $label . '</strike><span class="tcp_item_discount">';
						$label .= '<span class="tcp_item_current_price">' . tcp_format_the_price( $price_amount ) . '</span>';
						$label .= ' <span class="tcp_item_discount_detail">' . sprintf( __( '(%s&#37; Off)', 'tcp-discount' ), $amount );
						$label .= '</span>';
					} else {
						$label = sprintf( $discount_layout, $label, tcp_format_the_price( $price_amount ), $amount . '%' );
					}
					break;
				}
			}
			if ( $freeshipping ) {
				$label .= '<span class="tcp_free_shipping">' . __( 'Free shipping!', 'tcp-discount' ) . '</span>';
			}
			if ( strlen( $label ) > 0 ) return $label;
			else return $price;
		} else {
			return $label;
		}
	}

	function tcp_get_the_product_price( $price, $post_id ) {
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
				}
			}
			$price -= $amount;
			foreach( $percents as $percent ) {
				$price -= $price * $percent / 100;
			}
		}
		return $price;
	}

	function tcp_buy_button_get_product_classes( $classes, $product_id ) {
		if ( $this->hasDiscountsByProduct( $product_id ) )
			$classes[] = 'tcp_has_discount';
		return $classes;
	}

	function hasDiscountsByProduct( $product_id, $option_id_1 = 0, $option_id_2 = 0 ) {
		$discounts = $this->getDiscountsByProduct();
		$discounts = $this->getDiscountByProduct( $discounts, $product_id, $option_id_1, $option_id_1 );
		$discounts = $this->getDiscountByCouponByProduct( $discounts, $product_id );
		return count( $discounts ) > 0;
	}

	function getDiscountsByProduct() {
		$discounts = get_option( 'tcp_discounts_by_product', array() );
		$discounts = apply_filters( 'tcp_discount_by_product_get_discounts', $discounts );
		return $discounts;
	}

	function getDiscountByProduct( $discounts, $product_id, $option_id_1 = 0, $option_id_2 = 0 ) {
		$discounts_by_product = array();
		$discounts_to_all_products = false;
		if ( is_array( $discounts ) && count( $discounts ) > 0 )
			foreach( $discounts as $discount_item ) {
				$active = isset( $discount_item['active'] ) ? $discount_item['active'] : false;
				if ( $active ) {
					if ( $discount_item['product_id'] == $product_id ) {
						$discount_option_id_1 = isset( $discount_item['option_id_1'] ) ? $discount_item['option_id_1'] : 0;//-1
						if ( $discount_option_id_1 == $option_id_1 ) {
							$discount_option_id_2 = isset( $discount_item['option_id_2'] ) ? $discount_item['option_id_2'] : 0;//-1
							if ( $discount_option_id_2 == $option_id_2 ) {
								$discounts_by_product[] = $discount_item;
							}
						}
					} elseif ( $discount_item['product_id'] == 0 ) {
						$discounts_to_all_products = $discount_item;
					}
				}
			}
		if ( count( $discounts_by_product ) == 0 && $discounts_to_all_products !== false ) $discounts_by_product[] = $discounts_to_all_products;
		return apply_filters( 'tcp_get_discount_by_product', $discounts_by_product, $discounts, $product_id );
	}

	function tcp_options_title( $option_title, $product_id, $option_id_1 = 0, $option_id_2 = 0 ) {
		$discounts = $this->getDiscountsByProduct();
		$discounts = $this->getDiscountByProduct( $discounts, $product_id, $option_id_1, $option_id_2 );
		if ( count( $discounts ) > 0 ) {
			return $option_title . ' ' . sprintf( __( '(%s off)', 'tcp-discount' ), tcp_format_the_price( $discounts[0]['value'] ) );
		}
		return $option_title;
	}

	//Coupons
	public function tcp_checkout_after_order_cart() {
		if ( exists_active_coupons() ) {
			if ( isset( $_SESSION['tcp_checkout']['coupon_code'] ) ) {
				$coupon_code = $_SESSION['tcp_checkout']['coupon_code'];
				if ( strlen( trim( $coupon_code ) ) == 0 || tcp_is_coupon_valid( $coupon_code ) ) {
					$coupon_invalid = false;
				} else {
					$coupon_invalid = true;
				}
			} else {
				$coupon_code = ''; 
			}
			global $thecartpress;
			$buy_button_color = $thecartpress->get_setting( 'buy_button_color' );
			$buy_button_size = $thecartpress->get_setting( 'buy_button_size' ); ?>
			<div class="form-inline tcp_chk_coupons tcp-coupon tcp-tcpf">
				<div class="form-group">
					<label class="sr-only" for="tcp_coupon_code"><?php _e( 'Coupon code', 'tcp-discount' ); ?></label>
					<input type="text" name="tcp_coupon_code" id="tcp_coupon_code" placeholder="<?php _e( 'Coupon code', 'tcp-discount' ); ?>" value="<?php echo $coupon_code; ?>" class="form-control"/>
				</div>
				<button type="submit" name="tcp_add_coupon" id="tcp_add_coupon" class="tcp-btn tcp_checkout_button tcp_add_coupon <?php echo $buy_button_color; ?> <?php echo $buy_button_size; ?>"><?php _e( 'Add coupon', 'tcp-discount' ); ?></button>
				<button type="submit" name="tcp_remove_coupon" id="tcp_remove_coupon" class="tcp-btn tcp_checkout_button tcp_remove_coupon <?php echo $buy_button_size; ?>"><?php _e( 'Remove coupon', 'tcp-discount' ); ?></button>
				<?php if ( strlen( $coupon_code ) > 0 && $coupon_invalid ) echo '<span class="tcp-alert error">', __( 'The Coupon is invalid or it is out of date. Remember that there are coupons for registered users only.', 'tcp-discount' ), '</span>'; ?>
			</div><?php
		}
	}

	public function tcp_checkout_cart_after_cart() {
		ob_start();
		$this->tcp_checkout_after_order_cart();
		$out = ob_get_clean();
		if ( strlen( $out ) > 0 ) : ?>
<form method="post">
<?php echo $out; ?>
</form>
		<?php endif;
	}

	public function tcp_before_cart_box( $action ) {
		/*if ( isset( $_REQUEST['tcp_coupon_code'] ) ) {
			$_SESSION['tcp_checkout']['coupon_code'] = $_REQUEST['tcp_coupon_code'];
		}*/
		return $action;
	}

	private function create_coupon_id( $coupon_code ) {
		return 'coupon-' . $coupon_code;
	}

	public function check_for_shopping_cart_actions() {
		if ( isset( $_REQUEST['tcp_add_coupon'] ) && isset( $_REQUEST['tcp_coupon_code'] ) && strlen( trim( $_REQUEST['tcp_coupon_code'] ) ) > 0 ) {
			$_SESSION['tcp_checkout']['coupon_code'] = trim( $_REQUEST['tcp_coupon_code'] );
			//if ( ! tcp_is_coupon_valid() ) unset( $_SESSION['tcp_checkout']['coupon_code'] );
			$this->shoppingcart_modify();
		} elseif ( isset( $_REQUEST['tcp_remove_coupon'] ) ) {
			//$shoppingCart = TheCartPress::getShoppingCart();
			//$shoppingCart->setFreeShipping( false );
			unset( $_SESSION['tcp_checkout']['coupon_code'] );
			$this->shoppingcart_modify();
		}
	}

	function apply_coupon_discount( $shoppingCart = false ) {
		if ( ! isset( $_SESSION['tcp_checkout']['coupon_code'] ) ) {
			return;
		}
		$coupon_code = $_SESSION['tcp_checkout']['coupon_code'];
		$coupons = tcp_get_coupons();
		if ( $shoppingCart === false ) {
			$shoppingCart = TheCartPress::getShoppingCart();
		}
		foreach( $coupons as $coupon ) {
			if ( $coupon['active'] && $coupon['coupon_code'] == $coupon_code ) {
				if ( tcp_is_coupon_valid( $coupon['coupon_code'] ) ) {
					if ( $coupon['by_product'] ) {
						$items = $shoppingCart->getItem( $coupon['product_id'] );
						//for dynamic options, search for the options of this post id and set the discount to them
						if ( $items === false ) {
							$items = array();
							$options = $this->get_dynamic_options_items( $coupon['product_id'] );
							foreach( $options as $post_id ) {
								$item = $shoppingCart->getItem( $post_id );
								if ( $item !== false ) $items[] = $item;
							}
						} else {
							$items = array( $items );
						}
						foreach( $items as $item ) {
							if ( $item && $item->getCount() <= $coupon['uses_per_coupon'] ) {
								if ( 'freeshipping' == $coupon['coupon_type'] ) {
									$item->setFreeShipping();
								} elseif ( 'amount' == $coupon['coupon_type'] ) {
									$amount = $coupon['coupon_value'] * $item->getUnits();
									$item->addDiscount( $amount );
								} elseif ( 'percent' == $coupon['coupon_type'] ) {
									//$amount = $item->getUnitPrice() * ( $discount['value'] / 100 );
									$amount = $item->getPriceToShow() * ( $coupon['coupon_value'] / 100 );
									$amount = $amount * $item->getUnits();
									$item->addDiscount( $amount );
								}
							}
						}
					} else {
						if ( 'amount' == $coupon['coupon_type'] ) {
							$discount = $coupon['coupon_value'];
						} elseif ( 'percent' == $coupon['coupon_type'] ) {
							$total = $shoppingCart->getTotal();
							$discount = $total * $coupon['coupon_value'] / 100;
						} elseif ( 'freeshipping' == $coupon['coupon_type'] ) {
							$shoppingCart = TheCartPress::getShoppingCart();
							$shoppingCart->setFreeShipping();
							$discount = 0;
						} else {
							$discount = apply_filters( 'tcp_apply_discount_type', $discount, $coupon['coupon_type'], $coupon );
						}
						$shoppingCart->addDiscount( $this->create_coupon_id( $coupon_code ), $discount );
					}
				}
				break;
			}
		}
	}

	private function get_dynamic_options_items( $parent_id ) {
		$items = get_posts( array(
			'posts_per_page'	=> -1,
			'post_type'			=> 'tcp_dynamic_options',
			'post_parent'		=> $parent_id,
			'post_status'		=> 'publish',
			'fields'			=> 'ids',
		) );
		return $items;
	}

	function getDiscountByCouponByProduct( $discounts, $post_id ) {
		$coupon = tcp_get_coupon();
		if ( tcp_is_coupon_valid( $coupon['coupon_code'] ) ) {
			if ( $coupon['product_id'] == $post_id ) {
				$discounts[] = array(
					'type'	=> $coupon['coupon_type'],
					'value'	=> $coupon['coupon_value'],
				);
			}
		} 
		return apply_filters( 'tcp_get_discount_by_coupon_by_product', $discounts, $coupon, $post_id );
	}

	//When the order is created
	public function tcp_checkout_ok( $order_id ) {
		if ( isset( $_SESSION['tcp_checkout']['coupon_code'] ) ) {
			// $coupon_code = $_SESSION['tcp_checkout']['coupon_code'];
			// $coupons = tcp_get_coupons()
			// foreach( $coupons as $id => $coupon ) {
			// 	if ( $coupon['coupon_code'] == $coupon_code ) {
			// 		$uses_to_remove = 1;
			// 		if ( isset( $coupon['by_product'] ) && $coupon['by_product'] ) {
			// 			require_once( TCP_DAOS_FOLDER . 'OrdersDetails.class.php' );
			// 			$ordersDetails = OrdersDetails::getDetails( $order_id );
			// 			foreach( $ordersDetails as $detail ) {
			// 				if ( $detail->post_id == $coupon['product_id'] ) {
			// 					$uses_to_remove = $detail->qty_ordered;
			// 				}
			// 			}
			// 		}
			// 		if ( $coupon['uses_per_coupon'] > 0 ) $coupons[$id]['uses_per_coupon'] -= $uses_to_remove;
			// 		if ( $coupon['uses_per_user'] > 0 ) {
			// 			$current_user = wp_get_current_user();
			// 			if ( $current_user->ID > 0 ) {
			// 				$user_coupons = get_user_meta( $current_user->ID, 'tcp_coupons' );
			// 				if ( is_array( $user_coupons ) && isset( $user_coupons[$coupon_code]['quantity'] ) ) {
			// 					$user_coupons[$coupon_code]['quantity'] += $uses_to_remove;
			// 				} else {
			// 					$user_coupons = array( $coupon_code => array( 'quantity' => 1, 'coupon_type' => $coupon['coupon_type'], 'coupon_value' => $coupon['coupon_value'] ) );
			// 				}
			// 				update_user_meta( $current_user->ID, 'tcp_coupons', $user_coupons );
			// 			}
			// 		}
			// 		tcp_add_order_meta( $order_id, 'tcp_coupon', $coupon );
			// 		tcp_set_coupons( $coupons )
			// 	}
			// }
		}
	}

	public function tcp_admin_order_after_editor( $order_id ) {
		$coupon = tcp_get_order_meta( $order_id, 'tcp_coupon' );
		if ( is_array( $coupon ) ) : ?>
		<tr>
			<th scope="row">
				<?php _e( 'Coupon', 'tcp-discount' ); ?>
			</th>
			<td>
				<?php $to_date = isset( $coupon['to_date'] ) && $coupon['to_date'] != '' ? strftime( '%Y/%m/%d', $coupon['to_date'] ) : __( 'no limit', 'tcp-discount' );
				if ( $coupon['coupon_type'] == 'percent' ) printf( __( 'Code: %s, Type: %d&#37;, from: %s to %s', 'tcp-discount' ), $coupon['coupon_code'], $coupon['coupon_value'], strftime( '%Y/%m/%d', $coupon['from_date'] ), $to_date );
				else printf( __( 'Code: %s, Type: %s, Amount: %d, from: %s to %s', 'tcp-discount' ), $coupon['coupon_code'], $coupon['coupon_type'], tcp_format_the_price( $coupon['coupon_value'] ), strftime( '%Y/%m/%d', $coupon['from_date'] ), $to_date );?>
			</td>
		</tr>
		<?php endif;
	}

	public function personal_options( $profileuser ) {
		$user_coupons = get_user_meta ( $profileuser->ID, 'tcp_coupons', true );
		if ( is_array( $user_coupons ) && count( $user_coupons ) > 0 ) : ?>
		<tr class="tcp_coupons">
			<th scope="row"><?php _e( 'Coupons used', 'tcp-discount' ); ?></th>
			<td>
			<?php foreach( $user_coupons as $coupon_code => $coupon ) : ?>
				<p><?php if ( $coupon['coupon_type'] == 'percent' ) printf( __( 'Code: %s, Type: %d&#37;', 'tcp-discount' ), $coupon_code, $coupon['coupon_value'] );
			else printf( __( 'Code: %s, Type: %s, Amount: %d', 'tcp-discount' ), $coupon_code, $coupon['coupon_type'], tcp_format_the_price( $coupon['coupon_value'] ) );?></p>
			<?php endforeach; ?>
			</td>
		</tr>
		<?php endif;
	}
}

$tcp_discount = new TCPDiscount();
endif; // class_exists check