<?php
/**
 * The Template for displaying a DHL pacel shop finder result on the map.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/dhl/parcel-finder-result.php.
 *
 * @version 3.4.0
 */
defined( 'ABSPATH' ) || exit;
?>

<div id="parcel-content">
	<div id="site-notice"></div>
	<h4 class="parcel-title"><?php echo $result->gzd_name; ?></h4>
	<div id="bodyContent">
		<address>
			<?php echo $result->address->street; ?> <?php echo $result->address->streetNo; ?><br/>
			<?php echo $result->address->zip; ?> <?php echo $result->address->city; ?><br/>
		</address>

		<?php if ( 'packstation' !== $result->gzd_type ) : ?>
			<div class="parcel-opening-hours">
				<h5 class="parcel-subtitle"><?php _ex( 'Opening Times', 'dhl', 'woocommerce-germanized-dhl' ); ?></h5>

				<?php foreach( $result->gzd_opening_hours as $time ) : ?>
					<?php echo $time['weekday']; ?>: <?php echo $time['time_html']; ?><br/>
				<?php endforeach; ?>
			</div>

			<div class="parcel-services">
				<h5 class="parcel-subtitle"><?php _ex( 'Services', 'dhl', 'woocommerce-germanized-dhl' ); ?></h5>

				<?php _ex( 'Handicap Accessible', 'dhl', 'woocommerce-germanized-dhl' ); ?>: <?php echo ( $result->hasHandicappedAccess ? _x( 'Yes', 'dhl', 'woocommerce-germanized-dhl' ) : _x( 'No', 'dhl', 'woocommerce-germanized-dhl' ) ); ?><br/>
				<?php _ex( 'Parking', 'dhl', 'woocommerce-germanized-dhl' ); ?>: <?php echo ( $result->hasParkingArea ? _x( 'Yes', 'dhl', 'woocommerce-germanized-dhl' ) : _x( 'No', 'dhl', 'woocommerce-germanized-dhl' ) ); ?><br/>
			</div>
		
		<?php endif; ?>

		<button type="button" class="dhl-parcelshop-select-btn" id="<?php echo esc_attr( $result->gzd_result_id ); ?>"><?php _ex( 'Select ', 'dhl', 'woocommerce-germanized-dhl' ); ?></button>
	</div>
</div>
