<?php
/**
 * Shipment label HTML for meta box.
 *
 * @package WooCommerce_Germanized/DHL/Admin
 */
defined( 'ABSPATH' ) || exit;

use Vendidero\Germanized\DHL\Package;

$default_args        = wc_gzd_dhl_get_deutsche_post_label_default_args( $dhl_order, $shipment );
$im_products         = wc_gzd_dhl_get_deutsche_post_products( $shipment->get_country() );
$selected_product    = isset( $default_args['dhl_product'] ) ? $default_args['dhl_product'] : array_key_first( $im_products );
$selected_product_id = 0;

if ( ! empty( $selected_product ) ) {
    $selected_product_id = Package::get_internetmarke_api()->get_product_id( $selected_product );
}
?>
<form action="" method="post" class="wc-gzd-create-shipment-label-form">
    <?php woocommerce_wp_select( array(
        'id'          		=> 'deutsche_post_label_dhl_product',
        'label'       		=> _x( 'Product', 'dhl', 'woocommerce-germanized-dhl' ),
        'description'		=> '',
        'options'			=> $im_products,
        'value'             => isset( $default_args['dhl_product'] ) ? $default_args['dhl_product'] : '',
    ) ); ?>

    <div class="wc-gzd-shipment-im-additional-services">
        <?php
            $product_id        = $selected_product_id;
            $selected_services = isset( $default_args['additional_services'] ) ? $default_args['additional_services'] : array();

            include( Package::get_path() . '/includes/admin/views/html-deutsche-post-additional-services.php' );
        ?>
    </div>

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