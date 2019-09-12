<?php
/**
 * Shipment label HTML for meta box.
 *
 * @package WooCommerce_Germanized/DHL/Admin
 */
defined( 'ABSPATH' ) || exit;

use Vendidero\Germanized\DHL\Package;
?>

<div class="wc-gzd-shipment-dhl-label" data-label="<?php echo ( $dhl_label ? esc_attr( $dhl_label->get_id() ) : '' ); ?>">
    <h4><?php _e( 'DHL Label', 'woocommerce-germanized-dhl' ); ?></h4>

    <?php if ( $dhl_label ) : ?>
        <div class="wc-gzd-shipment-dhl-label-content">
            <div class="shipment-dhl-label-actions">
                <div class="shipment-dhl-label-actions-left">
                    <a class="button button-secondary download-shipment-label" href="<?php echo $dhl_label->get_download_url(); ?>" target="_blank"><?php _e( 'Download', 'woocommerce-germanized-dhl' ); ?></a>
                </div>
                <div class="shipment-dhl-label-actions-right">
                    <a class="remove-shipment-label delete" data-label="<?php echo esc_attr( $dhl_label->get_id() ); ?>" href="#"><?php _e( 'Delete label', 'woocommerce-germanized-dhl' ); ?></a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <a class="button button-secondary create-shipment-label" href="#" title="<?php _e( 'Create new DHL label', 'woocommerce-germanized-dhl' ); ?>"><?php _e( 'Create label', 'woocommerce-germanized-dhl' ); ?></a>
        <?php include_once( 'html-shipment-label-backbone.php' ); ?>
    <?php endif; ?>
</div>