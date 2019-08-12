window.germanized = window.germanized || {};
window.germanized.admin = window.germanized.admin || {};

( function( $, admin ) {

    /**
     * Core
     */
    admin.dhl = {

        params: {},

        init: function () {
            var self    = germanized.admin.dhl;
            self.params = wc_gzd_admin_dhl_params;

            $( document )
                .on( 'click', '#panel-order-shipments .create-shipment-label', self.onCreateLabel )
                .on( 'click', '#panel-order-shipments .remove-shipment-label', self.onRemoveLabel )
                .on( 'click', '.germanized-create-label .show-further-services', self.onExpandServices )
                .on( 'click', '.germanized-create-label .show-fewer-services', self.onHideServices )
                .on( 'change', '.germanized-create-label input.show-if-trigger', self.onShowIf )
                .on( 'click', '.germanized-create-label .notice .notice-dismiss', self.onRemoveNotice );

            $( document.body )
                .on( 'wc_backbone_modal_loaded', self.backbone.init )
                .on( 'wc_backbone_modal_response', self.backbone.response );
        },

        onRemoveNotice: function() {
            $( this ).parents( '.notice' ).slideUp( 150, function() {
                $( this ).remove();
            });
        },

        getShipmentWrapperByLabel: function( labelId ) {
            var self       = germanized.admin.dhl,
                $wrapper   = $( '.wc-gzd-shipment-dhl-label[data-label="' + labelId + '"]' );

            if ( $wrapper.length > 0 ) {
                return $wrapper.parents( '.order-shipment' );
            }

            return false;
        },

        getShipmentIdByLabel: function( labelId ) {
            var self       = germanized.admin.dhl,
                $wrapper   = $( '.wc-gzd-shipment-dhl-label[data-label="' + labelId + '"]' );

            if ( $wrapper.length > 0 ) {
                return $wrapper.parents( '.order-shipment' ).data( 'shipment' );
            }

            return false;
        },

        removeLabel: function( labelId ) {
            var self       = germanized.admin.dhl,
                $wrapper   = self.getShipmentWrapperByLabel( labelId );

            var params = {
                'action'  : 'woocommerce_gzd_remove_dhl_label',
                'label_id': labelId,
                'security': self.params.remove_label_nonce
            };

            if ( $wrapper ) {
                self.doAjax( params, $wrapper );
            }
        },

        onRemoveLabel: function() {
            var self       = germanized.admin.dhl,
                labelId    = $( this ).data( 'label' );

            var answer = window.confirm( self.params.i18n_remove_label_notice );

            if ( answer ) {
                self.removeLabel( labelId );
            }

            return false;
        },

        doAjax: function( params, $wrapper, cSuccess, cError  ) {
            var self       = germanized.admin.dhl,
                shipments  = germanized.admin.shipments,
                $shipment  = $wrapper.hasClass( 'order-shipment' ) ? $wrapper : $wrapper.parents( '.order-shipment' ),
                shipmentId = $shipment.data( 'shipment' );

            cSuccess = cSuccess || self.onAjaxSuccess;
            cError   = cError || self.onAjaxError;

            if ( ! params.hasOwnProperty( 'security' ) ) {
                params['security'] = self.params.edit_label_nonce;
            }

            if ( ! params.hasOwnProperty( 'shipment_id' ) ) {
                params['shipment_id'] = shipmentId;
            }

            $shipment.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            $shipment.find( '.notice-wrapper' ).empty();

            $.ajax({
                type: "POST",
                url:  self.params.ajax_url,
                data: params,
                success: function( data ) {
                    if ( data.success ) {
                        $shipment.unblock();

                        if ( data.fragments ) {
                            $.each( data.fragments, function ( key, value ) {
                                $( key ).replaceWith( value );
                            });
                        }

                        cSuccess.apply( $shipment, [ data ] );
                    } else {
                        cError.apply( $shipment, [ data ] );

                        $shipment.unblock();

                        if ( data.hasOwnProperty( 'message' ) ) {
                           shipments.addNotice( data.message, 'error' );
                        } else if( data.hasOwnProperty( 'messages' ) ) {
                            $.each( data.messages, function( i, message ) {
                                shipments.addNotice( message, 'error' );
                            });
                        }
                    }
                },
                error: function( data ) {},
                dataType: 'json'
            });
        },

        onAjaxSuccess: function( data ) {

        },

        onAjaxError: function( data ) {

        },

        onShowIf: function() {
            var $wrapper  = $( this ).parents( '.germanized-create-label' ),
                $show     = $wrapper.find( $( this ).data( 'show-if' ) ),
                $checkbox = $( this );

            if ( $show.length > 0 ) {
                if ( $checkbox.is( ':checked' ) ) {
                    $show.show();
                } else {
                    $show.hide();
                }
            }
        },

        onExpandServices: function() {
            var $wrapper  = $( this ).parents( '.germanized-create-label' ).find( '.show-if-further-services' ),
                $trigger  = $( this ).parents( '.show-services-trigger' );

            $wrapper.show();

            $trigger.find( '.show-further-services' ).hide();
            $trigger.find( '.show-fewer-services' ).show();

            return false;
        },

        onHideServices: function() {
            var $wrapper  = $( this ).parents( '.germanized-create-label' ).find( '.show-if-further-services' ),
                $trigger  = $( this ).parents( '.show-services-trigger' );

            $wrapper.hide();

            $trigger.find( '.show-further-services' ).show();
            $trigger.find( '.show-fewer-services' ).hide();

            return false;
        },

        getShipment: function( id ) {
            return $( '#panel-order-shipments' ).find( '#shipment-' + id );
        },

        onCreateLabel: function() {
            var self       = germanized.admin.dhl,
                shipmentId = $( this ).parents( '.order-shipment' ).data( 'shipment' );

            self.getShipment( shipmentId ).WCBackboneModal({
                template: 'wc-gzd-modal-create-shipment-label-' + shipmentId
            });

            return false;
        },

        backbone: {
            getShipmentId: function( target ) {
                return target.replace( /^\D+/g, '' );
            },

            init: function( e, target ) {
                if ( target.indexOf( 'wc-gzd-modal-create-shipment-label' ) !== -1 ) {
                    var self       = germanized.admin.dhl.backbone,
                        shipmentId = self.getShipmentId( target );

                    $( document.body ).trigger( 'wc-enhanced-select-init' );

                    $( '.germanized-create-label' ).find( 'input.show-if-trigger' ).trigger( 'change' );
                    $( '.germanized-create-label' ).parents( '.wc-backbone-modal' ).on( 'click', '#btn-ok', { 'shipmentId': shipmentId }, self.onSubmit );
                }
            },

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
                var self       = germanized.admin.dhl.backbone,
                    labels     = germanized.admin.dhl,
                    $modal     = $( this ).parents( '.wc-backbone-modal-content' ),
                    $content   = $modal.find( '.germanized-create-label' ),
                    $form      = $content.find( 'form' ),
                    params     = self.getFormData( $form );

                params['security']    = labels.params.create_label_nonce;
                params['shipment_id'] = e.data.shipmentId;
                params['action']      = 'woocommerce_gzd_create_dhl_label';

                $modal.block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                $content.find( '.notice-wrapper' ).empty();

                $.ajax({
                    type: "POST",
                    url:  labels.params.ajax_url,
                    data: params,
                    success: function( data ) {
                        if ( data.success ) {
                            $modal.unblock();

                            if ( data.fragments ) {
                                $.each( data.fragments, function ( key, value ) {
                                    $( key ).replaceWith( value );
                                });
                            }

                            $modal.find( '.modal-close' ).trigger( 'click' );
                        } else {
                            $modal.unblock();
                            if ( data.hasOwnProperty( 'messages' ) ) {

                                $.each( data.messages, function( i, message ) {
                                    self.addNotice( message, 'error', $content );
                                });

                                // ScrollTo top of modal
                                $content.animate({
                                    scrollTop: 0
                                }, 500 );
                            }
                        }
                    },
                    error: function( data ) {},
                    dataType: 'json'
                });

                e.preventDefault();
                e.stopPropagation();
            },

            addNotice: function( message, noticeType, $wrapper ) {
                $wrapper.find( '.notice-wrapper' ).append( '<div class="notice is-dismissible notice-' + noticeType +'"><p>' + message + '</p><button type="button" class="notice-dismiss"></button></div>' );
            },

            response: function( e, target, data ) {
                if ( target.indexOf( 'wc-gzd-modal-create-shipment-label' ) !== -1 ) {

                }
            }
        }
    };

    $( document ).ready( function() {
        germanized.admin.dhl.init();
    });

})( jQuery, window.germanized.admin );
