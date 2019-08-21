
window.germanized = window.germanized || {};
window.germanized.dhl_parcel_locator = window.germanized.dhl_parcel_locator || {};

( function( $, germanized ) {

    /**
     * Core
     */
    germanized.dhl_parcel_locator = {

        params: {},
        parcelShops: [],
        wrapper: '',

        init: function () {
            var self     = germanized.dhl_parcel_locator;
            self.params  = wc_gzd_dhl_parcel_locator_params;
            self.wrapper = self.params.wrapper;

            $( document )
                .on( 'change.dhl', self.wrapper + ' #shipping_address_type', self.refreshAddressType )
                .on( 'change.dhl', self.wrapper + ' #shipping_address_1', self.onChangeAddress )
                .on( 'change.dhl', self.wrapper + ' #ship-to-different-address-checkbox', self.onChangeShipping )
                .on( 'change.dhl', self.wrapper + ' #shipping_country', self.refreshAvailability );

            self.refreshAvailability();
            self.refreshAddressType();
        },

        refreshAvailability: function() {
            var self = germanized.dhl_parcel_locator;

            if ( ! self.isAvailable() ) {
                $( self.wrapper + ' #shipping_address_type' ).val( 'regular' ).trigger( 'change' );
                $( self.wrapper + ' #shipping_address_type_field' ).hide();
            } else {
                $( self.wrapper + ' #shipping_address_type_field' ).show();
            }
         },

        onChangeShipping: function() {
            var self      = germanized.dhl_parcel_locator,
                $checkbox = $( this );

            if ( $checkbox.is( ':checked' ) ) {
                self.refreshAvailability();

                if ( self.isEnabled() ) {
                    self.refreshAddressType();
                }
            }
        },

        onChangeAddress: function() {
            var self = germanized.dhl_parcel_locator;

            if ( self.isEnabled() ) {
                self.formatAddress();
            }
        },

        formatAddress: function() {
            var needsValidation = false,
                self            = germanized.dhl_parcel_locator,
                $addressField   = $( self.wrapper + ' #shipping_address_1' ),
                address         = $addressField.val();

            if ( address.length > 0 ) {
                if ( $.isNumeric( address ) ) {
                    needsValidation = true;
                } else if ( self.addressIsPackstation() || self.addressIsPostOffice() || self.addressIsParcelShop() ) {

                } else {
                    $addressField.val( '' );
                }
            }

            if ( needsValidation ) {
                self.validateAddress( address );
            }

            self.refreshCustomerNumberStatus();
        },

        addressIsPackstation: function() {
            var self       = germanized.dhl_parcel_locator,
                addressVal = $( self.wrapper + ' #shipping_address_1' ).val().toLowerCase();

            if ( addressVal.indexOf( self.params.i18n.packstation.toLowerCase() ) >= 0 ) {
                return true;
            }

            return false;
        },

        addressIsPostOffice: function() {
            var self       = germanized.dhl_parcel_locator,
                addressVal = $( self.wrapper + ' #shipping_address_1' ).val().toLowerCase();

            if ( addressVal.indexOf( self.params.i18n.postoffice.toLowerCase() ) >= 0 ) {
                return true;
            }

            return false;
        },

        addressIsParcelShop: function() {
            var self       = germanized.dhl_parcel_locator,
                addressVal = $( self.wrapper + ' #shipping_address_1' ).val().toLowerCase();

            if ( addressVal.indexOf( self.params.i18n.parcelshop.toLowerCase() ) >= 0 ) {
                return true;
            }

            return false;
        },

        customerNumberIsMandatory: function() {
            var self = germanized.dhl_parcel_locator;

            if ( ! self.isEnabled() ) {
                return false;
            }

            if ( self.addressIsPackstation() ) {
                return true;
            } else if ( self.addressIsParcelShop() ) {
                return false;
            } else if ( self.addressIsPostOffice() ) {
                return false;
            }

            return true;
        },

        refreshCustomerNumberStatus: function() {
            var self = germanized.dhl_parcel_locator,
                $field = $( self.wrapper + ' #shipping_dhl_postnumber_field' );

            if ( self.customerNumberIsMandatory() ) {
                if ( ! $field.find( 'label span' ).length || ( ! $field.find( 'label span' ).hasClass( 'required' ) ) ) {
                    $field.find( 'label' ).append( ' <span class="required">*</span>' );
                }

                $field.find( 'label span.optional' ).hide();
                $field.addClass( 'validate-required' );
            } else {
                $field.find( 'label span.required' ).remove();
                $field.find( 'label span.optional' ).show();

                $field.removeClass( 'validate-required woocommerce-invalid woocommerce-invalid-required-field' );
            }
         },

        validateAddress: function( addressData ) {
            var self   = germanized.dhl_parcel_locator,
                params = {
                    'action'  : 'woocommerce_gzd_dhl_parcel_locator_validate_address',
                    'address' : addressData,
                    'security': self.params.parcel_locator_nonce
                };

            $.ajax({
                type: "POST",
                url:  self.params.ajax_url,
                data: params,
                success: function( data ) {
                    if ( data.valid ) {
                        $( self.wrapper + ' #shipping_address_1' ).val( data.address );
                        self.refreshCustomerNumberStatus();
                    } else {
                        $( self.wrapper + ' #shipping_address_1' ).val( '' );
                    }
                },
                error: function( data ) {},
                dataType: 'json'
            });
        },

        refreshAddressType: function() {
            var self           = germanized.dhl_parcel_locator,
                $addressField  = $( self.wrapper + ' #shipping_address_1_field' ),
                $addressInput  = $( self.wrapper + ' #shipping_address_1' ),
                address        = $addressInput.val(),
                $spans;

            if ( self.isEnabled() ) {
                $( self.wrapper + ' #shipping_dhl_postnumber_field' ).show();

                if ( $addressInput.data( 'label-dhl' ) ) {
                    $spans = $addressField.find( 'label span, label abbr' );

                    $addressField.find( 'label' ).html( $addressInput.data( 'label-dhl' ) + ' ' );
                    $addressField.find( 'label' ).append( $spans );
                }

                if ( $addressInput.data( 'placeholder-dhl' ) ) {
                    $addressInput.attr( 'placeholder', $addressInput.data( 'placeholder-dhl' ) );
                }

                if ( $addressInput.data( 'desc-dhl' ) ) {
                    if ( $addressField.find( '.dhl-desc' ).length === 0 ) {
                        $addressField.find( '.woocommerce-input-wrapper' ).after( '<p class="desc dhl-desc">' + $addressInput.data( 'desc-dhl' ) + '</p>' );
                    }
                }

                if ( address.length > 0 ) {
                    self.formatAddress();
                }
            } else {
                $( self.wrapper + ' #shipping_dhl_postnumber_field' ).hide();

                if ( $addressInput.data( 'label-regular' ) ) {
                    $spans = $addressField.find( 'label span, label abbr' );

                    $addressField.find( 'label' ).html( $addressInput.data( 'label-regular' ) + ' ' );
                    $addressField.find( 'label' ).append( $spans );
                }

                if ( $addressInput.data( 'placeholder-regular' ) ) {
                    $addressInput.attr( 'placeholder', $addressInput.data( 'placeholder-regular' ) );
                }

                $addressField.find( '.dhl-desc' ).remove();
            }
        },

        isEnabled: function() {
            var self = germanized.dhl_parcel_locator;

            return self.isAvailable() && $( self.wrapper + ' #shipping_address_type' ).val() === 'dhl';
        },

        isAvailable: function() {
            var self            = germanized.dhl_parcel_locator,
                shippingCountry = $( self.wrapper + ' #shipping_country' ).val();

            if ( $.inArray( shippingCountry, self.params.supported_countries ) !== -1 ) {
                return true;
            } else {
                return false;
            }
        }
    };

    $( document ).ready( function() {
        germanized.dhl_parcel_locator.init();
    });

})( jQuery, window.germanized );
