<?php
/**
 * Shipment label HTML for meta box.
 *
 * @package WooCommerce_Germanized/DHL/Admin
 */
defined( 'ABSPATH' ) || exit;

use Vendidero\Germanized\DHL\Package;
?>

<div class="wc-gzd-shipment-dhl-label column column-spaced col-12" data-label="<?php echo ( $dhl_label ? esc_attr( $dhl_label->get_id() ) : '' ); ?>">
    <h4><?php _e( 'DHL Label', 'woocommerce-germanized-dhl' ); ?></h4>

    <div class="wc-gzd-shipment-dhl-label-content">
        <div class="shipment-dhl-label-actions">
	        <?php if ( $dhl_label ) : ?>
                <div class="shipment-dhl-label-actions-wrapper shipment-dhl-label-actions-download">
                    <a class="button button-secondary download-shipment-label" href="<?php echo $dhl_label->get_download_url(); ?>" target="_blank"><?php _e( 'Download', 'woocommerce-germanized-dhl' ); ?></a>

                    <?php if ( 'return' === $dhl_label->get_type() ) : ?>
                        <a class="send-shipment-label email" href="#" data-label="<?php echo esc_attr( $dhl_label->get_id() ); ?>"><?php _e( 'Send to customer', 'woocommerce-germanized-dhl' ); ?></a>
                    <?php endif; ?>

                    <a class="remove-shipment-label delete" data-label="<?php echo esc_attr( $dhl_label->get_id() ); ?>" href="#"><?php _e( 'Delete', 'woocommerce-germanized-dhl' ); ?></a>
                </div>
	        <?php else: ?>
                <div class="shipment-dhl-label-actions-wrapper shipment-dhl-label-actions-create">
                    <a class="button button-secondary create-shipment-label" href="#" title="<?php _e( 'Create new DHL label', 'woocommerce-germanized-dhl' ); ?>"><?php _e( 'Create label', 'woocommerce-germanized-dhl' ); ?></a>
                    <?php include( 'html-shipment-label-backbone.php' ); ?>
                </div>
	        <?php endif; ?>
        </div>
    </div>
</div>