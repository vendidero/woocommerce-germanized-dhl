<?php
/**
 * Shipment label HTML for meta box.
 *
 * @package WooCommerce_Germanized/DHL/Admin
 */
defined( 'ABSPATH' ) || exit;

use Vendidero\Germanized\DHL\Package;

// @TODO Parse defaults to show them in the options
$default_args = wc_gzd_dhl_get_label_default_args( $dhl_order, $shipment );
?>

<form action="" method="post" class="wc-gzd-dhl-create-label-form">

<?php woocommerce_wp_select( array(
	'id'          		=> 'dhl_label_dhl_product',
	'label'       		=> __( 'DHL Product', 'woocommerce-germanized-dhl' ),
	'description'		=> '',
	'options'			=> wc_gzd_dhl_get_products( $shipment->get_country() ),
	'value'             => wc_gzd_dhl_get_default_product( $shipment->get_country() )
) ); ?>

<?php if ( $dhl_order->has_cod_payment() ) : ?>
	<?php woocommerce_wp_text_input( array(
		'id'          		=> 'dhl_label_cod_total',
		'class'          	=> 'wc_input_decimal',
		'label'       		=> __( 'COD Amount', 'woocommerce-germanized-dhl' ),
		'placeholder' 		=> '',
		'description'		=> '',
		'value'       		=> isset( $default_args['cod_total'] ) ? $default_args['cod_total'] : '',
	) ); ?>
<?php endif; ?>

<?php if ( Package::is_crossborder_shipment( $shipment->get_country() ) ) : ?>
	<?php woocommerce_wp_select( array(
		'id'          		=> 'dhl_label_duties',
		'label'       		=> __( 'Duties', 'woocommerce-germanized-dhl' ),
		'description'		=> '',
		'value'       		=> isset( $default_args['duties'] ) ? $default_args['duties'] : '',
		'options'			=> wc_gzd_dhl_get_duties(),
	) ); ?>
<?php endif; ?>

<?php if ( Package::base_country_supports( 'services' ) && Package::is_shipping_domestic( $shipment->get_country() ) ) :

	$preferred_days     = array();
	$preferred_times    = array();

	try {
		$preferred_day_time = Package::get_api()->get_preferred_day_time( $shipment->get_postcode() );

		if ( $preferred_day_time ) {
			$preferred_days  = $preferred_day_time['preferred_day'];
			$preferred_times = $preferred_day_time['preferred_time'];
		}
	} catch( Exception $e ) {}
	?>

	<div class="columns">
		<div class="column col-6">
			<?php woocommerce_wp_select( array(
				'id'          		=> 'dhl_label_preferred_day',
				'label'       		=> __( 'Preferred Day', 'woocommerce-germanized-dhl' ),
				'description'		=> '',
				'value'       		=> $dhl_order->get_preferred_day() ? $dhl_order->get_preferred_day()->date( 'Y-m-d' ) : '',
				'options'			=> wc_gzd_dhl_get_preferred_days_select_options( $preferred_days ),
			) ); ?>
		</div>
		<div class="column col-6">
			<?php woocommerce_wp_select( array(
				'id'          		=> 'dhl_label_preferred_time',
				'label'       		=> __( 'Preferred Time', 'woocommerce-germanized-dhl' ),
				'description'		=> '',
				'value'       		=> $dhl_order->get_preferred_time() ? $dhl_order->get_preferred_time() : '',
				'options'			=> wc_gzd_dhl_get_preferred_times_select_options( $preferred_times ),
			) ); ?>
		</div>
	</div>

	<?php if ( $dhl_order->has_preferred_location() ) : ?>
		<?php woocommerce_wp_text_input( array(
			'id'          		=> 'dhl_label_preferred_location',
			'label'       		=> __( 'Preferred Location', 'woocommerce-germanized-dhl' ),
			'placeholder' 		=> '',
			'description'		=> '',
			'value'       		=> $dhl_order->get_preferred_location(),
			'custom_attributes'	=> array( 'maxlength' => '80' )
		) ); ?>
	<?php endif; ?>

	<?php if ( $dhl_order->has_preferred_neighbor() ) : ?>
		<?php woocommerce_wp_text_input( array(
			'id'          		=> 'dhl_label_preferred_neighbor',
			'label'       		=> __( 'Preferred Neighbor', 'woocommerce-germanized-dhl' ),
			'placeholder' 		=> '',
			'description'		=> '',
			'value'       		=> $dhl_order->get_preferred_neighbor_formatted_address(),
			'custom_attributes'	=> array( 'maxlength' => '80' )
		) ); ?>
	<?php endif; ?>

	<?php woocommerce_wp_checkbox( array(
		'id'          		=> 'dhl_label_has_return',
		'label'       		=> __( 'Create return label', 'woocommerce-germanized-dhl' ),
		'class'             => 'checkbox show-if-trigger',
		'custom_attributes' => array( 'data-show-if' => '.show-if-has-return' ),
		'desc_tip'          => true,
		'value'             => isset( $default_args['has_return'] ) ? wc_bool_to_string( $default_args['has_return'] ) : 'no',
		'wrapper_class'     => 'form-field-checkbox'
	) ); ?>

	<div class="show-if show-if-has-return">
		<div class="columns">
			<div class="column col-12">
				<?php woocommerce_wp_text_input( array(
					'id'          		=> 'dhl_label_return_address[name]',
					'label'       		=> __( 'Name', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'             => Package::get_setting( 'return_address_name' )
				) ); ?>
			</div>
		</div>
		<?php woocommerce_wp_text_input( array(
			'id'          		=> 'dhl_label_return_address[company]',
			'label'       		=> __( 'Company', 'woocommerce-germanized-dhl' ),
			'placeholder' 		=> '',
			'description'		=> '',
			'value'             => Package::get_setting( 'return_address_company' )
		) ); ?>
		<div class="columns">
			<div class="column col-9">
				<?php woocommerce_wp_text_input( array(
					'id'          		=> 'dhl_label_return_address[street]',
					'label'       		=> __( 'Street', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'             => Package::get_setting( 'return_address_street' )
				) ); ?>
			</div>
			<div class="column col-3">
				<?php woocommerce_wp_text_input( array(
					'id'          		=> 'dhl_label_return_address[street_number]',
					'label'       		=> __( 'Street No', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'             => Package::get_setting( 'return_address_street_no' )
				) ); ?>
			</div>
		</div>
		<div class="columns">
			<div class="column col-6">
				<?php woocommerce_wp_text_input( array(
					'id'          		=> 'dhl_label_return_address[postcode]',
					'label'       		=> __( 'Postcode', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'             => Package::get_setting( 'return_address_postcode' )
				) ); ?>
			</div>
			<div class="column col-6">
				<?php woocommerce_wp_text_input( array(
					'id'          		=> 'dhl_label_return_address[city]',
					'label'       		=> __( 'City', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'             => Package::get_setting( 'return_address_city' )
				) ); ?>
			</div>
		</div>
		<div class="columns">
			<div class="column col-6">
				<?php woocommerce_wp_text_input( array(
					'id'          		=> 'dhl_label_return_address[phone]',
					'label'       		=> __( 'Phone', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'             => Package::get_setting( 'return_address_phone' )
				) ); ?>
			</div>
			<div class="column col-6">
				<?php woocommerce_wp_text_input( array(
					'id'          		=> 'dhl_label_return_address[email]',
					'label'       		=> __( 'Email', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'             => Package::get_setting( 'return_address_email' )
				) ); ?>
			</div>
		</div>
	</div>

	<?php woocommerce_wp_checkbox( array(
		'id'          		=> 'dhl_label_codeable_address_only',
		'label'       		=> __( 'Valid address only', 'woocommerce-germanized-dhl' ),
		'placeholder' 		=> '',
		'description'		=> '',
		'value'       		=> isset( $default_args['codeable_address_only'] ) ? wc_bool_to_string( $default_args['codeable_address_only'] ) : 'no',
		'wrapper_class'     => 'form-field-checkbox'
	) ); ?>

	<p class="show-services-trigger">
		<a href="#" class="show-further-services">
			<span class="dashicons dashicons-plus"></span> <?php _e( 'More services', 'woocommerce-germanized-dhl' ); ?>
		</a>
		<a class="show-fewer-services hide-default" href="#">
			<span class="dashicons dashicons-minus"></span> <?php _e( 'Fewer services', 'woocommerce-germanized-dhl' ); ?>
		</a>
	</p>

	<div class="hide-default show-if-further-services">
		<?php woocommerce_wp_select( array(
			'id'          		=> 'dhl_label_visual_min_age',
			'label'       		=> __( 'Age check', 'woocommerce-germanized-dhl' ),
			'description'		=> '',
			'value'       		=> isset( $default_args['visual_min_age'] ) ? $default_args['visual_min_age'] : '',
			'options'			=> wc_gzd_dhl_get_visual_min_ages(),
		) ); ?>

		<?php woocommerce_wp_checkbox( array(
			'id'          		=> 'dhl_label_email_notification',
			'label'       		=> __( 'E-Mail notification', 'woocommerce-germanized-dhl' ),
			'description'       => '',
			'value'		        => isset( $default_args['email_notification'] ) ? $default_args['email_notification'] : 'no',
			'wrapper_class'     => 'form-field-checkbox'
		) ); ?>

		<?php woocommerce_wp_checkbox( array(
			'id'          		=> 'dhl_label_service_AdditionalInsurance',
			'label'       		=> __( 'Additional insurance', 'woocommerce-germanized-dhl' ),
			'description'       => '',
			'value'		        => isset( $default_args['services']['AdditionalInsurance'] ) ? 'yes' : 'no',
			'wrapper_class'     => 'form-field-checkbox'
		) ); ?>

		<?php woocommerce_wp_checkbox( array(
			'id'          		=> 'dhl_label_service_NoNeighbourDelivery',
			'label'       		=> __( 'No neighbor', 'woocommerce-germanized-dhl' ),
			'description'       => '',
			'value'		        => isset( $default_args['services']['NoNeighbourDelivery'] ) ? 'yes' : 'no',
			'wrapper_class'     => 'form-field-checkbox'
		) ); ?>

		<?php woocommerce_wp_checkbox( array(
			'id'          		=> 'dhl_label_service_NamedPersonOnly',
			'label'       		=> __( 'Named person only', 'woocommerce-germanized-dhl' ),
			'description'		=> '',
			'value'		        => isset( $default_args['services']['NamedPersonOnly'] ) ? 'yes' : 'no',
			'wrapper_class'     => 'form-field-checkbox'
		) ); ?>

		<?php woocommerce_wp_checkbox( array(
			'id'          		=> 'dhl_label_service_Premium',
			'label'       		=> __( 'Premium', 'woocommerce-germanized-dhl' ),
			'description'		=> '',
			'value'		        => isset( $default_args['services']['Premium'] ) ? 'yes' : 'no',
			'wrapper_class'     => 'form-field-checkbox'
		) ); ?>

		<?php woocommerce_wp_checkbox( array(
			'id'          		=> 'dhl_label_service_BulkyGoods',
			'label'       		=> __( 'Bulky goods', 'woocommerce-germanized-dhl' ),
			'description'		=> '',
			'value'		        => isset( $default_args['services']['BulkyGoods'] ) ? 'yes' : 'no',
			'wrapper_class'     => 'form-field-checkbox'
		) ); ?>

		<?php woocommerce_wp_checkbox( array(
			'id'          		=> 'dhl_label_service_IdentCheck',
			'label'       		=> __( 'Identity check', 'woocommerce-germanized-dhl' ),
			'description'		=> '',
			'class'             => 'checkbox show-if-trigger',
			'value'		        => isset( $default_args['services']['IdentCheck'] ) ? 'yes' : 'no',
			'custom_attributes' => array( 'data-show-if' => '.show-if-ident-check' ),
			'wrapper_class'     => 'form-field-checkbox'
		) ); ?>

		<div class="show-if show-if-ident-check">
			<?php woocommerce_wp_text_input( array(
				'id'          		=> 'dhl_label_ident_date_of_birth',
				'label'       		=> __( 'Date of Birth', 'woocommerce-germanized-dhl' ),
				'placeholder' 		=> '',
				'description'		=> '',
				'value'       		=> isset( $default_args['ident_date_of_birth'] ) ? $default_args['ident_date_of_birth'] : '',
				'custom_attributes' => array( 'pattern' => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])', 'maxlength' => 10 ),
				'class'				=> 'short date-picker'
			) ); ?>

			<?php woocommerce_wp_select( array(
				'id'          		=> 'dhl_label_ident_min_age',
				'label'       		=> __( 'Minimum age', 'woocommerce-germanized-dhl' ),
				'description'		=> '',
				'value'       		=> isset( $default_args['ident_min_age'] ) ? $default_args['ident_min_age'] : '',
				'options'			=> wc_gzd_dhl_get_ident_min_ages(),
			) ); ?>
		</div>
	</div>
<?php endif; ?>

</form>
