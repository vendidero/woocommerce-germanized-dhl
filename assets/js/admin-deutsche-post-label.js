window.germanized = window.germanized || {};
window.germanized.admin = window.germanized.admin || {};

( function( $, admin ) {

    /**
     * Core
     */
    admin.dhl_post_label = {

        params: {},

        init: function () {
            var self    = admin.dhl_post_label;
            self.params = wc_gzd_admin_deutsche_post_label_params;

            $( document ).on( 'change', '#deutsche_post_label_dhl_product', self.onChangeProductId );
            $( document.body ).on( 'wc_gzd_shipment_label_after_init', self.onInit );
        },

        onInit: function() {
            var self = admin.dhl_post_label;

            if ( $( '#deutsche_post_label_dhl_product' ).length > 0 ) {
                self.refreshProductData();
            }
        },

        refreshProductData: function() {
            var self = admin.dhl_post_label;

            self.replaceProductData( self.getProductData( self.getProductId() ) );
            self.refreshPreview();
        },

        refreshPreview: function() {
            var self     = admin.dhl_post_label,
                backbone = germanized.admin.shipment_label_backbone.backbone,
                params   = {},
                $wrapper = $( '.wc-gzd-shipment-create-label .col-preview' );

            params['security']    = self.params.preview_nonce;
            params['product_id']  = self.getProductId();
            params['action']      = 'woocommerce_gzd_dhl_preview_stamp';

            backbone.doAjax( params, $wrapper, self.onPreviewSuccess );
        },

        onPreviewSuccess: function( data ) {
            var self         = admin.dhl_post_label,
                $wrapper     = $( '.wc-gzd-dhl-im-product-data .col-preview' ),
                $img_wrapper = $( '.wc-gzd-dhl-im-product-data' ).find( '.image-preview' );

            if ( data.preview_url ) {
                $wrapper.block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                if ( $img_wrapper.find( '.stamp-preview' ).length <= 0 ) {
                    $img_wrapper.append( '<img class="stamp-preview" style="display: none;" />' );
                }

                $img_wrapper.find( '.stamp-preview' ).attr('src', data.preview_url ).load( function() {
                    $wrapper.unblock();
                    $( this ).show();
                });
            } else {
                $img_wrapper.html( '' );
            }
        },

        getProductId: function() {
            return $( '#deutsche_post_label_dhl_product' ).val();
        },

        getProductData: function( productId ) {
            var self        = admin.dhl_post_label,
                productData = {},
                available   = $( 'form.wc-gzd-create-shipment-label-form' ).data( 'products' );

            if ( available.hasOwnProperty( productId ) ) {
                productData = available[ productId ];
            }

            return productData;
        },

        onChangeProductId: function() {
            var self = admin.dhl_post_label;

            self.refreshProductData();
        },

        replaceProductData: function( productData ) {
            var self = admin.dhl_post_label,
                $wrapper = $( '.wc-gzd-shipment-create-label' ).find( '.wc-gzd-dhl-im-product-data' );

            $wrapper.find( '.data-placeholder' ).html( '' );

            $wrapper.find( '.data-placeholder' ).each( function() {
                var replaceKey = $( this ).data( 'replace' );

                if ( productData.hasOwnProperty( replaceKey ) ) {
                    $( this ).html( productData[ replaceKey ] );
                    $( this ).show();
                } else {
                    $( this ).hide();
                }
            } );
        }
    };

    $( document ).ready( function() {
        germanized.admin.dhl_post_label.init();
    });

})( jQuery, window.germanized.admin );