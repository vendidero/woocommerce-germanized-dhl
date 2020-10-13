window.germanized = window.germanized || {};
window.germanized.admin = window.germanized.admin || {};

( function( $, admin ) {

    /**
     * Core
     */
    admin.dhl_internetmarke = {

        params: {},

        init: function () {
            var self = admin.dhl_internetmarke;

            $( document ).on( 'click', '#woocommerce_gzd_dhl_im_portokasse_charge', self.onCharge );
        },

        onCharge: function() {
            var $button = $( this ),
                data    = $button.data(),
                amount  = $( '#woocommerce_gzd_dhl_im_portokasse_charge_amount' ).val();

            $form = $( '<form target="_blank" action="' + $button.data( 'url' ) + '" id="wc-gzd-dhl-im-portokasse-form" method="POST" style=""></form>' ).appendTo( 'body' );

            $.each( data, function( index, value ) {
                $form.append( '<input type="hidden" name="' + index.toUpperCase() + '" value="' + value + '" />' );
            } );

            var balance = parseInt( ( parseFloat( amount.replace( ',', '.') ).toFixed( 2 ) * 100 ).toFixed() );
            var wallet  = parseInt( data['wallet'] );

            /**
             * Set min amount.
             */
            if ( balance < 1000 || Number.isNaN( balance ) ) {
                balance = 1000;
            }

            var date    = new Date();
            var timestamp =
                ('0' + date.getDate()).slice(-2) +
                ('0' + (date.getMonth() + 1)).slice(-2) +
                date.getFullYear().toString() +
                '-' +
                ('0' + date.getHours()).slice(-2) +
                ('0' + date.getMinutes()).slice(-2) +
                ('0' + date.getSeconds()).slice(-2);

            var concat = [
                data['partner_id'],
                timestamp,
                data['success_url'],
                data['cancel_url'],
                data['user_token'],
                wallet + balance,
                data['schluessel_dpwn_partner']
            ].join( '::' );

            $form.append( '<input type="hidden" name="BALANCE" value="' + ( wallet + balance ) + '" />' );
            $form.append( '<input type="hidden" name="PARTNER_SIGNATURE" value="' + ( md5( concat ).substring( 0,8 ) ) + '" />' );
            $form.append( '<input type="hidden" name="REQUEST_TIMESTAMP" value="' + timestamp + '" />' );

            $form.submit();

            return false;
        }
    };

    $( document ).ready( function() {
        germanized.admin.dhl_internetmarke.init();
    });

})( jQuery, window.germanized.admin );