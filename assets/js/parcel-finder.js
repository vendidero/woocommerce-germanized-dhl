window.germanized = window.germanized || {};
window.germanized.dhl_parcel_finder = window.germanized.dhl_parcel_finder || {};

( function( $, germanized ) {

    /**
     * Core
     */
    germanized.dhl_parcel_finder = {

        params: {},

        init: function () {
            var self    = germanized.dhl_parcel_finder;
            self.params = wc_gzd_dhl_parcel_finder_params;

            $( document )
                .on( 'click', '.gzd-dhl-parcel-shop-modal', self.openModal )
                .on( 'click', '#dhl-parcel-finder-wrapper .dhl-parcel-finder-close', self.closeModal )
                .on( 'submit', '#dhl-parcel-finder-wrapper #dhl-parcel-finder-form', self.onSubmit );
        },

        openModal: function() {
            var self   = germanized.dhl_parcel_finder,
                $modal = self.getModal();

            var country = $( '.woocommerce-checkout #shipping_country' ).val().length > 0 ? $( '.woocommerce-checkout #shipping_country' ).val() : $( '.woocommerce-checkout #billing_country' ).val();
            $modal.find( '#dhl-parcelfinder-country').val( country );

            var postcode = $( '.woocommerce-checkout #shipping_postcode' ).val().length > 0 ? $( '.woocommerce-checkout #shipping_postcode' ).val() : $( '.woocommerce-checkout #billing_postcode' ).val();
            $modal.find( '#dhl-parcelfinder-postcode' ).val( postcode );

            var city = $( '.woocommerce-checkout #shipping_city' ).val().length > 0 ? $( '.woocommerce-checkout #shipping_city' ).val() : $( '.woocommerce-checkout #billing_city' ).val();
            $modal.find( '#dhl-parcelfinder-city' ).val( city );

            var address_1 = $( '.woocommerce-checkout #shipping_address_1' ).val().length > 0 ? $( '.woocommerce-checkout #shipping_address_1' ).val() : $( '.woocommerce-checkout #billing_address_1' ).val();
            var address_2 = $( '.woocommerce-checkout #shipping_address_2' ).val().length > 0 ? $( '.woocommerce-checkout #shipping_address_2' ).val() : $( '.woocommerce-checkout #billing_address_2' ).val();
            var address   = address_1 + ' ' + address_2;

            $modal.find( '#dhl-parcelfinder-address').val( address );

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

            $wrapper.block({
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
                        $wrapper.unblock();
                        cSuccess.apply( $wrapper, [ data ] );
                    } else {
                        cError.apply( $wrapper, [ data ] );
                        $wrapper.unblock();

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
                $modal     = self.getModal(),
                $content   = $modal.find( '#dhl-parcel-finder' ),
                $form      = $content.find( 'form' ),
                params     = self.getFormData( $form );

            params['action'] = 'woocommerce_gzd_dhl_parcelfinder_search';

            self.doAjax( params, $content, self.onSubmitSuccess );

            return false;
        },

        onSubmitSuccess: function( data ) {
            var self = germanized.dhl_parcel_finder;

            if ( data.parcel_shops ) {
                self.updateMap( data.parcel_shops );
            }
        },

        updateMap: function( parcelShops ) {
            var self = germanized.dhl_parcel_finder;

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
                var openingTimes = '<h5 class="parcel_subtitle">' + self.params.opening_times + '</h5>',
                    prev_day     = 0,
                    day_of_week;

                $.each( value.psfTimeinfos, function( key_times, value_times ) {
                    if( value_times.type === 'openinghour' ) {
                        switch (value_times.weekday) {
                            case 1:
                                day_of_week = pr_dhl_checkout_frontend.monday;
                                break;
                            case 2:
                                day_of_week = pr_dhl_checkout_frontend.tueday;
                                break;
                            case 3:
                                day_of_week = pr_dhl_checkout_frontend.wednesday;
                                break;
                            case 4:
                                day_of_week = pr_dhl_checkout_frontend.thrusday;
                                break;
                            case 5:
                                day_of_week = pr_dhl_checkout_frontend.friday;
                                break;
                            case 6:
                                day_of_week = pr_dhl_checkout_frontend.satuday;
                                break;
                            case 7:
                                day_of_week = pr_dhl_checkout_frontend.sunday;
                                break;

                        }

                        if( prev_day ) {
                            if( prev_day == value_times.weekday ) {
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
                var shopServices = '<h5 class="parcel_subtitle">' + pr_dhl_checkout_frontend.services + '</h5>';
                var shopServicesParking = ': ' + pr_dhl_checkout_frontend.no;
                var shopServicesHandicap = ': ' + pr_dhl_checkout_frontend.no;
                $.each(value.psfServicetypes, function(key_services,value_services) {
                    switch (value_services) {
                        case 'parking':
                            shopServicesParking = ': ' + pr_dhl_checkout_frontend.yes;
                            break;
                        case 'handicappedAccess':
                            shopServicesHandicap = ': ' + pr_dhl_checkout_frontend.yes;
                            break;
                    }

                });

                shopServices += pr_dhl_checkout_frontend.parking + shopServicesParking + '<br/>';
                shopServices += pr_dhl_checkout_frontend.handicap + shopServicesHandicap + '<br/>';


                switch (value.shopType) {
                    case 'packStation':
                        var gmap_marker_icon = pr_dhl_checkout_frontend.packstation_icon;
                        var shop_name = pr_dhl_checkout_frontend.packstation;
                        var shop_label = pr_dhl_checkout_frontend.packstation;
                        break;
                    case 'parcelShop':
                        var gmap_marker_icon = pr_dhl_checkout_frontend.parcelshop_icon;
                        var shop_name = pr_dhl_checkout_frontend.parcelShop;
                        var shop_label = pr_dhl_checkout_frontend.branch;
                        break;
                    case 'postOffice':
                        var gmap_marker_icon = pr_dhl_checkout_frontend.post_office_icon;
                        var shop_name = pr_dhl_checkout_frontend.postoffice;
                        var shop_label = pr_dhl_checkout_frontend.branch;
                        break;
                    default:
                        var gmap_marker_icon = pr_dhl_checkout_frontend.packstation_icon;
                        var shop_name = pr_dhl_checkout_frontend.packstation;
                        var shop_label = pr_dhl_checkout_frontend.packstation;
                        break;
                }

                shop_name += ' ' + value.primaryKeyZipRegion;

                var contentString = '<div id="parcel-content">'+
                    '<div id="site-notice">'+
                    '</div>'+
                    '<h4 class="parcel-title">' + shop_name + '</h4>'+
                    '<div id="bodyContent">'+
                    '<div>' + value.street + ' ' + value.houseNo + '</br>' + value.city + ' ' + value.zipCode + '</div>'+
                    openingTimes +
                    shopServices +
                    '<button type="button" class="parcelshop-select-btn" id="' + value.id + '">' + pr_dhl_checkout_frontend.select + '</button>'+
                    '</div>'+
                    '</div>';

                var infowindow = new google.maps.InfoWindow({
                    content: contentString,
                    maxWidth: 300
                });

                infoWinArray.push(infowindow);


                var marker = new google.maps.Marker({
                    position: uluru,
                    map: map,
                    title: shop_label,
                    animation: google.maps.Animation.DROP,
                    icon: gmap_marker_icon
                });

                marker.addListener('click', function() {
                    clearOverlays();
                    infowindow.open(map, marker);
                });

            });

            // Clear all info windows
            function clearOverlays() {
                for (var i = 0; i < infoWinArray.length; i++ ) {
                    infoWinArray[i].close();
                }
                // infoWinArray.length = 0;
            }

            // marker.addListener('click', toggleBounce);
        },

        onSelectShop: function() {
            var parcelShopId = $(this).attr('id');

            $.each(wc_checkout_dhl_parcelfinder.parcelShops, function(key,value) {

                if( value.id == parcelShopId ) {
                    switch (value.shopType) {
                        case 'packStation':
                            var shop_name = pr_dhl_checkout_frontend.packstation;
                            $('.woocommerce-checkout #shipping_dhl_address_type').val('dhl_packstation').trigger('change');
                            break;
                        case 'parcelShop':
                            var shop_name = pr_dhl_checkout_frontend.parcelShop;
                            $('.woocommerce-checkout #shipping_dhl_address_type').val('dhl_branch').trigger('change');
                            break;
                        case 'postOffice':
                            var shop_name = pr_dhl_checkout_frontend.postoffice;
                            $('.woocommerce-checkout #shipping_dhl_address_type').val('dhl_branch').trigger('change');
                            break;
                        default:
                            var shop_name = pr_dhl_checkout_frontend.packstation;
                            $('.woocommerce-checkout #shipping_dhl_address_type').val('dhl_packstation').trigger('change');
                            break;
                    }

                    $('.woocommerce-checkout #shipping_first_name').val( $('.woocommerce-checkout #billing_first_name').val() );
                    $('.woocommerce-checkout #shipping_last_name').val( $('.woocommerce-checkout #billing_last_name').val() );
                    // $('.woocommerce-checkout #shipping_company').val( '' );
                    $('.woocommerce-checkout #shipping_address_1').val( shop_name + ' ' + value.primaryKeyZipRegion );
                    $('.woocommerce-checkout #shipping_address_2').val( '' );
                    $('.woocommerce-checkout #shipping_postcode').val( value.zipCode );
                    $('.woocommerce-checkout #shipping_city').val( value.city );

                    $.fancybox.close();
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
