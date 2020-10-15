<?php
/**
 * Shipment label HTML for meta box.
 *
 * @package WooCommerce_Germanized/DHL/Admin
 */
defined( 'ABSPATH' ) || exit;

use Vendidero\Germanized\DHL\Package;

$default_args    = wc_gzd_dhl_get_label_default_args( $dhl_order, $shipment );
$variations_json = wp_json_encode( Package::get_internetmarke_api()->get_available_products_printable() );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
?>
<form action="" method="post" class="wc-gzd-create-shipment-label-form" data-products="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
    <?php woocommerce_wp_select( array(
        'id'          		=> 'deutsche_post_label_dhl_product',
        'label'       		=> _x( 'Product', 'dhl', 'woocommerce-germanized-dhl' ),
        'description'		=> '',
        'options'			=> wc_gzd_dhl_get_post_products( $shipment->get_country() ),
        'value'             => isset( $default_args['post_product'] ) ? $default_args['post_product'] : '',
    ) ); ?>

    <?php woocommerce_wp_select( array(
        'id'          		=> 'deutsche_post_label_page_format',
        'label'       		=> _x( 'Page Format', 'dhl', 'woocommerce-germanized-dhl' ),
        'description'		=> '',
        'options'			=> Package::get_internetmarke_api()->get_page_format_list(),
        'value'             => isset( $default_args['page_format'] ) ? $default_args['page_format'] : '',
    ) ); ?>
</form>

<div class="columns preview-columns wc-gzd-dhl-im-product-data">
    <div class="column col-4">
        <p class="wc-gzd-dhl-im-product-price wc-price data-placeholder hide-default" data-replace="price_formatted"></p>
    </div>
    <div class="column col-3 col-dimensions">
        <p class="wc-gzd-dhl-im-product-dimensions data-placeholder hide-default" data-replace="dimensions_formatted"></p>
    </div>
    <div class="column col-5 col-preview">
        <div class="image-preview"></div>
    </div>
    <div class="column col-12">
        <p class="wc-gzd-dhl-im-product-description data-placeholder hide-default" data-replace="description_formatted"></p>
        <p class="wc-gzd-dhl-im-product-information-text data-placeholder hide-default" data-replace="information_text_formatted"></p>
    </div>
</div>