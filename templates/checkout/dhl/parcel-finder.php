<?php
/**
 * The Template for displaying DHL pacel shop finder.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/dhl/parcel-finder.php.
 *
 * @version 3.4.0
 */
defined( 'ABSPATH' ) || exit;
?>

<div id="dhl-parcel-finder-wrapper">
    <div id="dhl-parcel-finder-bg-overlay"></div>
    <div id="dhl-parcel-finder-inner">
        <div id="dhl-parcel-finder-inner-wrapper">
            <div id="dhl-parcel-finder">
                <form id="dhl-parcel-finder-form" method="post">
                    <p class="form-row form-field small">
                        <input type="text" name="dhl_parcelfinder_postcode" class="input-text" placeholder="<?php esc_attr_e( 'Postcode', 'woocommerce-germanized-dhl' ); ?>" id="dhl-parcelfinder-postcode" />
                    </p>
                    <p class="form-row form-field small">
                        <input type="text" name="dhl_parcelfinder_city" class="input-text" placeholder="<?php esc_attr_e( 'City', 'woocommerce-germanized-dhl' ); ?>" id="dhl-parcelfinder-city" />
                    </p>
                    <p class="form-row form-field large">
                        <input type="text" name="dhl_parcelfinder_address" class="input-text" placeholder="<?php esc_attr_e( 'Address', 'woocommerce-germanized-dhl' ); ?>" id="dhl-parcelfinder-address" />
                    </p>

		            <?php if ( $is_packstation_enabled ) : ?>
                        <p class="form-row form-field packstation">
                            <input type="checkbox" name="dhl_parcelfinder_packstation_filter" class="input-checkbox" id="dhl-packstation-filter" value="yes" checked />
                            <label for="dhl-packstation-filter"><?php esc_attr_e( 'Packstation', 'woocommerce-germanized-dhl' ); ?></label>
                            <span class="icon" style="background-image: url('<?php echo $img_packstation; ?>');"></span>
                        </p>
		            <?php endif; ?>

		            <?php if( $is_parcelshop_enabled || $is_postoffice_enabled ) : ?>
                        <p class="form-row form-field parcelshop">
                            <input type="checkbox" name="dhl_parcelfinder_branch_filter" class="input-checkbox" placeholder="" id="dhl-branch-filter" value="yes" checked />
                            <label for="dhl-branch-filter"><?php esc_attr_e( 'Branch', 'woocommerce-germanized-dhl' ); ?></label>
                            <span class="parcel-wrap">
                                <?php if( $is_parcelshop_enabled ) : ?>
                                    <span class="icon" style="background-image: url('<?php echo $img_parcelshop; ?>');"></span>
                                <?php endif; ?>
                                <?php if( $is_postoffice_enabled ) : ?>
                                    <span class="icon" style="background-image: url('<?php echo $img_postoffice; ?>');"></span>
                                <?php endif; ?>
                            </span>
                        </p>
		            <?php endif; ?>

                    <p id="dhl-search-button" class="form-row form-field small">
                        <input type="submit" class="button" name="apply_parcel_finder" value="<?php esc_attr_e( 'Search', 'woocommerce-germanized-dhl' ); ?>" />
                    </p>

                    <input type="hidden" name="dhl_parcelfinder_country" id="dhl-parcelfinder-country" />
                    <div class="clear"></div>

                    <button class="dhl-parcel-finder-close" title="close"><svg viewBox="0 0 32 32"><path d="M10,10 L22,22 M22,10 L10,22"></path></svg></button>
                </form>

                <div class="notice-wrapper"></div>
                <div id="dhl-parcel-finder-map"></div>
            </div>
        </div>
    </div>
</div>