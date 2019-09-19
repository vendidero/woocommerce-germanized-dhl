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

                    <p class="form-row form-field finder-pickup-type packstation <?php echo ( ! $is_packstation_enabled ? 'hidden' : '' ); ?>" data-pickup_type="packstation">
                        <input type="checkbox" name="dhl_parcelfinder_packstation_filter" class="input-checkbox" id="dhl-packstation-filter" value="yes" <?php echo ( $is_packstation_enabled ? 'checked="checked"' : '' ); ?> />
                        <label for="dhl-packstation-filter"><?php esc_attr_e( 'Packstation', 'woocommerce-germanized-dhl' ); ?></label>
                        <span class="icon" style="background-image: url('<?php echo $img_packstation; ?>');"></span>
                    </p>

                    <p class="form-row form-field finder-pickup-type parcelshop <?php echo ( ! $is_parcelshop_enabled ? 'hidden' : '' ); ?>" data-pickup_type="parcelshop">
                        <input type="checkbox" name="dhl_parcelfinder_parcelshop_filter" class="input-checkbox" id="dhl-parcelshop-filter" value="yes" <?php echo ( $is_parcelshop_enabled ? 'checked="checked"' : '' ); ?> />
                        <label for="dhl-parcelshop-filter"><?php esc_attr_e( 'Parcelshop', 'woocommerce-germanized-dhl' ); ?></label>
                        <span class="icon" style="background-image: url('<?php echo $img_parcelshop; ?>');"></span>
                    </p>

                    <p class="form-row form-field finder-pickup-type postoffice <?php echo ( ! $is_postoffice_enabled ? 'hidden' : '' ); ?>" data-pickup_type="postoffice">
                        <input type="checkbox" name="dhl_parcelfinder_postoffice_filter" class="input-checkbox" id="dhl-postoffice-filter" value="yes" <?php echo ( $is_postoffice_enabled ? 'checked="checked"' : '' ); ?> />
                        <label for="dhl-postoffice-filter"><?php esc_attr_e( 'Postoffice', 'woocommerce-germanized-dhl' ); ?></label>
                        <span class="icon" style="background-image: url('<?php echo $img_postoffice; ?>');"></span>
                    </p>

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