window.germanized = window.germanized || {};
window.germanized.dhl_parcel_finder = window.germanized.dhl_parcel_finder || {};

( function( $, germanized ) {

    /**
     * Core
     */
    germanized.dhl_parcel_finder = {

        params: {},
        parcelShops: [],
        wrapper: '',

        init: function () {
            var self    = germanized.dhl_parcel_finder;
            self.params = wc_gzd_dhl_parcel_finder_params;
            self.wrapper = self.params.wrapper;

            $( document )
                .on( 'click', '.gzd-dhl-parcel-shop-modal', self.openModal )
                .on( 'click', '#dhl-parcel-finder-wrapper .dhl-parcel-finder-close', self.closeModal )
                .on( 'submit', '#dhl-parcel-finder-wrapper #dhl-parcel-finder-form', self.onSubmit )
                .on( 'click', '#dhl-parcel-finder-wrapper .dhl-retry-search', self.onSubmit )
                .on( 'click', '#dhl-parcel-finder-wrapper .dhl-parcelshop-select-btn', self.onSelectShop );

            $( document.body ).bind( 'woocommerce_gzd_dhl_location_available_pickup_types_changed', self.onChangeAvailablePickupTypes );
        },

        onChangeAvailablePickupTypes: function() {
            var self       = germanized.dhl_parcel_finder,
                loc        = germanized.dhl_parcel_locator,
                $modal     = self.getModal(),
                method     = loc.getShippingMethod(),
                methodData = loc.getShippingMethodData( method );

            if ( methodData ) {

                $modal.find( '.finder-pickup-type' ).addClass( 'hidden' );

                $.each( methodData.supports, function( i, pickupType ) {
                    var $type = $modal.find( '.finder-pickup-type[data-pickup_type="' + pickupType + '"]' );

                    $type.find( 'input[type=checkbox]' ).prop( 'checked', true );
                    $type.removeClass( 'hidden' );
                });
            }
        },

        openModal: function() {
            var self   = germanized.dhl_parcel_finder,
                $modal = self.getModal();

            var country = $( self.wrapper + ' #shipping_country' ).val().length > 0 ? $( self.wrapper + ' #shipping_country' ).val() : $( self.wrapper + ' #billing_country' ).val();
            $modal.find( '#dhl-parcelfinder-country').val( country );

            var postcode = $( self.wrapper + ' #shipping_postcode' ).val().length > 0 ? $( self.wrapper + ' #shipping_postcode' ).val() : $( self.wrapper + ' #billing_postcode' ).val();
            $modal.find( '#dhl-parcelfinder-postcode' ).val( postcode );

            var city = $( self.wrapper + ' #shipping_city' ).val().length > 0 ? $( self.wrapper + ' #shipping_city' ).val() : $( self.wrapper + ' #billing_city' ).val();
            $modal.find( '#dhl-parcelfinder-city' ).val( city );

            $modal.addClass( 'open' );
            $modal.find( '#dhl-parcel-finder-form' ).submit();

            return false;
        },

        closeModal: function() {
            var self = germanized.dhl_parcel_finder;
            self.getModal().removeClass( 'open' );

            return false;
        },

        getModal: function() {
            return $( '#dhl-parcel-finder-wrapper' );
        },

        doAjax: function( params, $wrapper, cSuccess, cError  ) {
            var self = germanized.dhl_parcel_finder;

            cSuccess = cSuccess || self.onAjaxSuccess;
            cError   = cError || self.onAjaxError;

            if ( ! params.hasOwnProperty( 'security' ) ) {
                params['security'] = self.params.parcel_finder_nonce;
            }

            $wrapper.find( '#dhl-parcel-finder-map' ).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            $wrapper.find( '.notice-wrapper' ).empty();

            $.ajax({
                type: "POST",
                url:  self.params.ajax_url,
                data: params,
                success: function( data ) {
                    if ( data.success ) {
                        $wrapper.find( '#dhl-parcel-finder-map' ).unblock();
                        cSuccess.apply( $wrapper, [ data ] );
                    } else {
                        cError.apply( $wrapper, [ data ] );
                        $wrapper.find( '#dhl-parcel-finder-map' ).unblock();

                        if ( data.hasOwnProperty( 'message' ) ) {
                            self.addNotice( data.message, 'error', $wrapper );
                        } else if( data.hasOwnProperty( 'messages' ) ) {
                            $.each( data.messages, function( i, message ) {
                                self.addNotice( message, 'error', $wrapper );
                            });
                        }
                    }
                },
                error: function( data ) {},
                dataType: 'json'
            });
        },

        onAjaxSuccess: function( data ) {},

        onAjaxError: function( data ) {},

        getFormData: function( $form ) {
            var data = {};

            $.each( $form.serializeArray(), function( index, item ) {
                if ( item.name.indexOf( '[]' ) !== -1 ) {
                    item.name = item.name.replace( '[]', '' );
                    data[ item.name ] = $.makeArray( data[ item.name ] );
                    data[ item.name ].push( item.value );
                } else {
                    data[ item.name ] = item.value;
                }
            });

            return data;
        },

        onSubmit: function( e ) {
            var self       = germanized.dhl_parcel_finder,
                loc        = germanized.dhl_parcel_locator,
                $modal     = self.getModal(),
                $content   = $modal.find( '#dhl-parcel-finder' ),
                $form      = $content.find( 'form' ),
                params     = self.getFormData( $form );

            params['action']      = 'woocommerce_gzd_dhl_parcelfinder_search';
            params['is_checkout'] = loc.isCheckout() ? 'yes' : 'no';

            self.doAjax( params, $content, self.onSubmitSuccess );

            return false;
        },

        onSubmitSuccess: function( data ) {
            var self = germanized.dhl_parcel_finder;

            if ( data.parcel_shops ) {
                self.parcelShops = data.parcel_shops;

                if ( typeof google === 'object' && typeof google.maps === 'object' ) {
                    self.updateMap();
                } else {
                    self.loadMapsAPI();
                }
            }
        },

        loadMapsAPI: function() {
            var self = germanized.dhl_parcel_finder;

            self.addScript( 'https://maps.googleapis.com/maps/api/js?key=' + self.params.api_key, self.updateMap );
        },

        addScript: function( url, callback ) {
            var script = document.createElement( 'script' );

            if ( callback ) {
                script.onload = callback;
            }

            script.type = 'text/javascript';
            script.src = url;
            document.body.appendChild( script );
        },

        updateMap: function() {
            var self        = germanized.dhl_parcel_finder,
                parcelShops = self.parcelShops;

            var uluru = {
                lat: parcelShops[0].location.latitude,
                lng: parcelShops[0].location.longitude
            };

            var map = new google.maps.Map( document.getElementById('dhl-parcel-finder-map' ), {
                zoom: 13,
                center: uluru
            });

            var infoWinArray = [];

            $.each( parcelShops, function( key,value ) {
                var uluru = {lat: value.location.latitude, lng: value.location.longitude};

                // Get opening times
                var openingTimes = '<h5 class="parcel-subtitle">' + self.params.i18n.opening_times + '</h5>',
                    prev_day     = 0,
                    day_of_week;

                $.each( value.psfTimeinfos, function( key_times, value_times ) {
                    if ( value_times.type === 'openinghour' ) {
                        switch ( value_times.weekday ) {
                            case 1:
                                day_of_week = self.params.i18n.monday;
                                break;
                            case 2:
                                day_of_week = self.params.i18n.tueday;
                                break;
                            case 3:
                                day_of_week = self.params.i18n.wednesday;
                                break;
                            case 4:
                                day_of_week = self.params.i18n.thrusday;
                                break;
                            case 5:
                                day_of_week = self.params.i18n.friday;
                                break;
                            case 6:
                                day_of_week = self.params.i18n.satuday;
                                break;
                            case 7:
                                day_of_week = self.params.i18n.sunday;
                                break;
                        }

                        if ( prev_day ) {
                            if ( prev_day === value_times.weekday ) {
                                openingTimes += ', ';
                            } else {
                                openingTimes += '<br/>' + day_of_week + ': ';
                            }
                        } else {
                            openingTimes += day_of_week + ': ';
                        }

                        prev_day = value_times.weekday;
                        openingTimes += value_times.timefrom + ' - ' + value_times.timeto;
                    }
                });

                // Get services
                var shopServices         = '<h5 class="parcel-subtitle">' + self.params.i18n.services + '</h5>',
                    shopServicesParking  = ': ' + self.params.i18n.no,
                    shopServicesHandicap = ': ' + self.params.i18n.no;

                $.each( value.psfServicetypes, function( key_services, value_services ) {
                    switch ( value_services ) {
                        case 'parking':
                            shopServicesParking = ': ' + self.params.i18n.yes;
                            break;
                        case 'handicappedAccess':
                            shopServicesHandicap = ': ' + self.params.i18n.yes;
                            break;
                    }
                });

                shopServices += self.params.i18n.parking + shopServicesParking + '<br/>';
                shopServices += self.params.i18n.handicap + shopServicesHandicap + '<br/>';

                var markerIcon = self.params.packstation_icon,
                    shopName   = self.params.i18n.packstation,
                    shopLabel  = self.params.i18n.packstation;

                switch ( value.shopType ) {
                    case 'parcelShop':
                        markerIcon = self.params.parcelshop_icon;
                        shopName   = self.params.i18n.parcelshop;
                        shopLabel  = self.params.i18n.branch;
                        break;
                    case 'postOffice':
                        markerIcon = self.params.postoffice_icon;
                        shopName   = self.params.i18n.postoffice;
                        shopLabel  = self.params.i18n.branch;
                        break;
                }

                shopName += ' ' + value.primaryKeyZipRegion;

                var contentString = '<div id="parcel-content">'+
                    '<div id="site-notice">'+
                    '</div>'+
                    '<h4 class="parcel-title">' + shopName + '</h4>'+
                    '<div id="bodyContent">'+
                    '<div>' + value.street + ' ' + value.houseNo + '</br>' + value.city + ' ' + value.zipCode + '</div>'+
                    openingTimes +
                    shopServices +
                    '<button type="button" class="dhl-parcelshop-select-btn" id="' + value.id + '">' + self.params.i18n.select + '</button>'+
                    '</div>'+
                    '</div>';

                var infowindow = new google.maps.InfoWindow({
                    content: contentString,
                    maxWidth: 300
                });

                infoWinArray.push( infowindow );

                var marker = new google.maps.Marker({
                    position: uluru,
                    map: map,
                    title: shopLabel,
                    animation: google.maps.Animation.DROP,
                    icon: markerIcon
                });

                marker.addListener('click', function() {
                    clearOverlays();
                    infowindow.open( map, marker );
                });
            });

            // Clear all info windows
            function clearOverlays() {
                for ( var i = 0; i < infoWinArray.length; i++ ) {
                    infoWinArray[i].close();
                }
            }
        },

        onSelectShop: function() {
            var self         = germanized.dhl_parcel_finder,
                parcelShopId = parseInt( $( this ).attr( 'id' ) ),
                shopName     = self.params.i18n.packstation,
                $addressType = $( self.wrapper + ' #shipping_address_type' );

            $.each( self.parcelShops, function( key,value ) {
                if ( parseInt( value.id ) === parcelShopId ) {
                    var isPackstation = false;

                    switch ( value.shopType ) {
                        case 'packStation':
                            shopName      = self.params.i18n.packstation;
                            isPackstation = true;
                            break;
                        case 'parcelShop':
                            shopName = self.params.i18n.parcelshop;
                            break;
                        case 'postOffice':
                            shopName = self.params.i18n.postoffice;
                            break;
                    }

                    $( self.wrapper + ' #shipping_first_name' ).val( $( self.wrapper + ' #shipping_first_name' ).val().length > 0 ? $( self.wrapper + ' #shipping_first_name' ).val() : $( self.wrapper + ' #billing_first_name' ).val() );
                    $( self.wrapper + ' #shipping_last_name' ).val( $( self.wrapper + ' #shipping_last_name' ).val().length > 0 ? $( self.wrapper + ' #shipping_last_name' ).val() : $( self.wrapper + ' #billing_last_name' ).val() );

                    $( self.wrapper + ' #shipping_address_1' ).val( shopName + ' ' + value.primaryKeyZipRegion );
                    $( self.wrapper + ' #shipping_address_2' ).val( '' );
                    $( self.wrapper + ' #shipping_postcode' ).val( value.zipCode );
                    $( self.wrapper + ' #shipping_city' ).val( value.city );

                    $addressType.val( 'dhl' ).trigger( 'change' );

                    self.closeModal();

                    if ( isPackstation && $( self.wrapper + ' #shipping_dhl_postnumber' ).val() === '' ) {
                        $( self.wrapper + ' #shipping_dhl_postnumber' ).focus();
                    }

                    return true;
                }
            });
        },

        addNotice: function( message, noticeType, $wrapper ) {
            $wrapper.find( '.notice-wrapper' ).append( '<div class="notice notice-' + noticeType +'"><p>' + message + '</p></div>' );
        }
    };

    $( document ).ready( function() {
        germanized.dhl_parcel_finder.init();
    });

})( jQuery, window.germanized );
