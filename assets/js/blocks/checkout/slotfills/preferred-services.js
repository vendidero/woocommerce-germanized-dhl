import { ExperimentalOrderShippingPackages } from '@woocommerce/blocks-checkout';
import { registerPlugin } from '@wordpress/plugins';
import { useEffect, useCallback } from "@wordpress/element";
import { useSelect, useDispatch, select } from '@wordpress/data';
import { extensionCartUpdate } from '@woocommerce/blocks-checkout';
import classnames from 'classnames';
import { getSetting } from '@woocommerce/settings';
import { __, sprintf } from '@wordpress/i18n';
import _ from 'lodash';
import { CART_STORE_KEY, CHECKOUT_STORE_KEY } from '@woocommerce/block-data';
import { getCurrencyFromPriceResponse } from '@woocommerce/price-format';

import {
    __experimentalRadio as Radio,
    __experimentalRadioGroup as RadioGroup,
} from 'wordpress-components';

import {
    ValidatedTextInput,
    ValidatedTextInputHandle,
} from '@woocommerce/blocks-checkout';

import RadioControl from '@wooshipments/base-components/radio-control';
import './style.scss';
import RadioControlOption from "@wooshipments/base-components/radio-control";
import RadioControlAccordion from "@wooshipments/base-components/radio-control-accordion";
import FormattedMonetaryAmount from "@wooshipments/base-components/formatted-monetary-amount";

const getSelectedShippingProviders = (
    shippingRates
) => {
    return Object.fromEntries( shippingRates.map( ( { package_id: packageId, shipping_rates: packageRates } ) => {
        const selected = packageRates.find( ( rate ) => rate.selected );
        let provider = '';

        if ( selected ) {
            provider = selected.meta_data.reduce( ( { key: metaKey, value: metaValue } ) => {
                if ( 'shipping_provider' === metaKey ) {
                    return metaValue;
                }

                return null;
            } );
        }

        return [
            packageId,
            provider
        ];
    } ) );
};

const hasShippingProvider = ( shippingProviders, shippingProvider ) => {
    return Object.values( shippingProviders ).includes( shippingProvider );
};

const DhlPreferredDaySelect = ({
    preferredDays,
    setPreferredOption,
    preferredOptions,
    preferredDayCost,
    currency
}) => {
    const preferredDay = preferredOptions.hasOwnProperty( 'preferred_day' ) ? preferredOptions['preferred_day'] : '';
    const costValue = parseInt( preferredDayCost, 10 );

    return (
        <div className="wc-gzd-dhl-preferred-days">
            <p className="wc-block-components-checkout-step__description">
                { __( 'Choose a delivery day', 'woocommerce-germanized' ) }

                { costValue > 0 &&
                    <span className="dhl-cost"> (+ <FormattedMonetaryAmount
                            currency={ currency }
                            value={ costValue }
                        />)
                    </span>
                }
            </p>
            <div className="wc-gzd-dhl-preferred-day-select">
                { preferredDays.map( ( preferred ) => {
                    const checked = preferredDay === preferred.date;

                    return (
                        <Radio
                            value={ preferred.date }
                            key={ preferred.date }
                            onClick={ ( event ) => {
                                setPreferredOption( 'preferred_day', preferred.date );
                            } }
                            checked={ checked }
                            className={ classnames(
                                `wc-gzd-dhl-preferred-day`,
                                {
                                    active: checked
                                }
                            ) }
                        >
                        <span className="inner">
                            <span className="day">
                            { preferred.day }
                            </span>
                            <span className="week-day">
                                { preferred.week_day }
                            </span>
                        </span>
                        </Radio>
                    );
                } ) }
            </div>
        </div>
    );
};

const DhlPreferredLocation = ( props ) => {
    const {
        setPreferredOption,
        preferredOptions,
    } = props;

    const location = preferredOptions.hasOwnProperty( 'preferred_location' ) ? preferredOptions['preferred_location'] : '';

    return (
        <>
            <p>{ __( 'Choose a weather-protected and non-visible place on your property, where we can deposit the parcel in your absence.', 'woocommerce-germanized' ) }</p>
            <ValidatedTextInput
                key="dhl-location"
                value={ location }
                id="dhl-location"
                label={ __( "e.g. Garage, Terrace", "woocommerce-germanized" ) }
                name="dhl_location"
                required={ true }
                maxLength="80"
                onChange={ ( newValue ) => {
                    setPreferredOption( 'preferred_location', newValue );
                } }
            />
        </>
    )
}

const DhlPreferredNeighbor = ( props ) => {
    const {
        setPreferredOption,
        preferredOptions,
    } = props;

    const neighborName = preferredOptions.hasOwnProperty( 'preferred_neighbor_name' ) ? preferredOptions['preferred_neighbor_name'] : '';
    const neighborAddress = preferredOptions.hasOwnProperty( 'preferred_neighbor_address' ) ? preferredOptions['preferred_neighbor_address'] : '';

    return (
        <>
            <p>{ __( 'Determine a person in your immediate neighborhood whom we can hand out your parcel in your absence. This person should live in the same building, directly opposite or next door.', 'woocommerce-germanized' ) }</p>

            <ValidatedTextInput
                key="dhl-preferred-neighbor-name"
                value={ neighborName }
                id="dhl-preferred-neighbor-name"
                label={ __( "First name, last name of neighbor", "woocommerce-germanized" ) }
                required={ true }
                maxLength="25"
                onChange={ ( newValue ) => {
                    setPreferredOption( 'preferred_neighbor_name', newValue );
                } }
            />
            <ValidatedTextInput
                key="dhl-preferred-neighbor-address"
                value={ neighborAddress }
                id="dhl-preferred-neighbor-address"
                required={ true }
                maxLength="55"
                label={ __( "Street, number, postal code, city", "woocommerce-germanized" ) }
                onChange={ ( newValue ) => {
                    setPreferredOption( 'preferred_neighbor_address', newValue );
                } }
            />
        </>

    )
}

const DhlPreferredLocationSelect = ( props ) => {
    const {
        setPreferredOption,
        preferredOptions,
        preferredNeighborEnabled,
        preferredLocationEnabled
    } = props;

    const preferredLocationType = preferredOptions.hasOwnProperty( 'preferred_location_type' ) ? preferredOptions['preferred_location_type'] : '';

    const options = [
        {
            value: '',
            label: __( 'None', 'woocommerce-germanized' ),
            content: '',
        },
        preferredLocationEnabled ?
        {
            value: 'place',
            label: __( 'Drop-off location', 'woocommerce-germanized' ),
            content: (
                <DhlPreferredLocation { ...props } />
            ),
        } : {},
        preferredNeighborEnabled ?
        {
            value: 'neighbor',
            label: __( 'Neighbor', 'woocommerce-germanized' ),
            content: (
                <DhlPreferredNeighbor { ...props } />
            ),
        } : {},
    ].filter( value => Object.keys( value ).length !== 0 );

    return (
        <div className="wc-gzd-dhl-preferred-location">
            <p className="wc-block-components-checkout-step__description">{ __( 'Choose a preferred location', 'woocommerce-germanized' ) }</p>
            <RadioControlAccordion
                id={ 'wc-gzd-dhl-preferred-location-options' }
                selected={ preferredLocationType }
                onChange={ ( value ) => {
                    setPreferredOption( 'preferred_location_type', value );
                } }
                options={ options }
            />
        </div>
    );
};

const DhlCdpOptions = (
    props
) => {
    const { preferredOptions, setPreferredOption, homeDeliveryCost, currency } = props;
    const preferredDeliveryType = preferredOptions.hasOwnProperty( 'preferred_delivery_type' ) ? preferredOptions['preferred_delivery_type'] : '';
    const costValue = parseInt( homeDeliveryCost, 10 );

    const options = [
        {
            value: 'cdp',
            label: __( 'Shop', 'woocommerce-germanized' ),
            content: (
                <>
                    { __( 'Delivery to nearby parcel store/locker or to the front door.', 'woocommerce-germanized' ) }
                </>
            ),
        },
        {
            value: 'home',
            label: __( 'Home Delivery', 'woocommerce-germanized' ),
            content: (
                <>
                    { __( 'Delivery usually to the front door.', 'woocommerce-germanized' ) }
                </>
            ),
            secondaryLabel: costValue > 0 ? (
                <FormattedMonetaryAmount
                    currency={ currency }
                    value={ costValue }
                />
            ) : ''
        }
    ];

    return (
        <div className="wc-gzd-dhl-preferred-delivery">
            <RadioControlAccordion
                id={ 'wc-gzd-dhl-preferred-delivery-types' }
                selected={ preferredDeliveryType }
                onChange={ ( value ) => {
                    setPreferredOption( 'preferred_delivery_type', value );
                } }
                options={ options }
            />
        </div>
    );
};

const DhlPreferredOptions = (
    props
) => {
    const { preferredDayEnabled, preferredLocationEnabled, preferredNeighborEnabled } = props;

    return (
        <div>
            { preferredDayEnabled ? (
                <DhlPreferredDaySelect { ...props } />
            ) : '' }
            { preferredLocationEnabled || preferredNeighborEnabled ? (
                <DhlPreferredLocationSelect { ...props } />
            ) : '' }
        </div>
    );
};

const DhlPreferredDeliveryOptions = ({
    extensions,
    cart,
    components
}) => {
    const {
        shippingRates,
        needsShipping,
        isLoadingRates,
        isSelectingRate,
    } = useSelect( ( select ) => {
        const isEditor = !! select( 'core/editor' );
        const store = select( CART_STORE_KEY );
        const rates = isEditor
            ? []
            : store.getShippingRates();
        return {
            shippingRates: rates,
            needsShipping: store.getNeedsShipping(),
            isLoadingRates: store.isCustomerDataUpdating(),
            isSelectingRate: store.isShippingRateBeingSelected(),
        };
    } );

    const shippingProviders = getSelectedShippingProviders( shippingRates );
    const hasDhlProvider = hasShippingProvider( shippingProviders, 'dhl' );
    const { __internalSetExtensionData } = useDispatch( CHECKOUT_STORE_KEY );

    const checkoutExtensionData = useSelect( ( select ) =>
        select( CHECKOUT_STORE_KEY ).getExtensionData()
    );

    const dhlOptions = extensions['woocommerce-germanized-dhl'];
    const preferredOptions = checkoutExtensionData.hasOwnProperty( 'woocommerce-germanized-dhl' ) ? checkoutExtensionData['woocommerce-germanized-dhl'] : {};

    const preferredDayCost = parseInt( dhlOptions['preferred_day_cost'], 10 );
    const homeDeliveryCost = parseInt( dhlOptions['preferred_home_delivery_cost'], 10 );

    const setDhlOption = ( option, value, updateCart = true ) => {
        const checkoutOptions = { ...preferredOptions };
        checkoutOptions[ option ] = value;

        __internalSetExtensionData( 'woocommerce-germanized-dhl', checkoutOptions );

        if ( updateCart ) {
            if ( 'preferred_day' === option && preferredDayCost > 0 ) {
                extensionCartUpdate( {
                    namespace: 'woocommerce-germanized-dhl-checkout-fees',
                    data: checkoutOptions,
                } );
            } else if ( 'preferred_delivery_type' === option && homeDeliveryCost > 0 ) {
                extensionCartUpdate( {
                    namespace: 'woocommerce-germanized-dhl-checkout-fees',
                    data: checkoutOptions,
                } );
            }
        }
    };

    const setPreferredOption = useCallback(
        ( option, value ) => {
            setDhlOption( option, value );
        },
        [ setDhlOption, preferredOptions ]
    );

    const totalsCurrency = getCurrencyFromPriceResponse( cart.cartTotals );
    const preferredOptionsAvailable = 'DE' === cart.shippingAddress.country;
    const cdpCountries = getSetting( 'dhlCdpCountries', [] );
    const isCdpAvailable = _.includes( cdpCountries, cart.shippingAddress.country );

    const preferredDayEnabled = dhlOptions.preferred_day_enabled && dhlOptions.preferred_days.length > 0;
    const preferredLocationEnabled = dhlOptions.preferred_location_enabled;
    const preferredNeighborEnabled = dhlOptions.preferred_neighbor_enabled;

    useEffect(() => {
        if ( ! hasDhlProvider ) {
            const checkoutOptions = {
                'preferred_day': '',
                'preferred_location_type': '',
                'preferred_location': '',
                'preferred_neighbor_name': '',
                'preferred_neighbor_type': '',
                'preferred_delivery_type': '',
            }

            console.log('No dhl provider available..');

            __internalSetExtensionData( 'woocommerce-germanized-dhl', checkoutOptions );

            extensionCartUpdate( {
                namespace: 'woocommerce-germanized-dhl-checkout-fees',
                data: checkoutOptions,
            } );
        } else {
            const currentData = select( CHECKOUT_STORE_KEY ).getExtensionData()['woocommerce-germanized-dhl'];

            if ( ! preferredOptionsAvailable ) {
                const checkoutOptions = { ...currentData,
                    'preferred_day': '',
                    'preferred_location_type': '',
                    'preferred_location': '',
                    'preferred_neighbor_name': '',
                    'preferred_neighbor_type': '',
                    'preferred_delivery_type': isCdpAvailable ? dhlOptions['preferred_delivery_type'] : ''
                };

                __internalSetExtensionData( 'woocommerce-germanized-dhl', checkoutOptions );
            } else {
                const checkoutOptions = { ...currentData,
                    'preferred_day': dhlOptions['preferred_day'],
                    'preferred_delivery_type': '',
                };

                console.log('setting defaults');
                console.log(checkoutOptions);

                __internalSetExtensionData( 'woocommerce-germanized-dhl', checkoutOptions );
            }
        }
    }, [
        hasDhlProvider,
        preferredOptionsAvailable,
        isCdpAvailable,
        __internalSetExtensionData
    ] );

    useEffect(() => {
        if ( hasDhlProvider ) {
            const currentData = select( CHECKOUT_STORE_KEY ).getExtensionData()['woocommerce-germanized-dhl'];

            console.log('has dhl provider -> refresh');

            const checkoutOptions = { ...currentData };

            extensionCartUpdate( {
                namespace: 'woocommerce-germanized-dhl-checkout-fees',
                data: checkoutOptions,
            } );
        }
    }, [
        hasDhlProvider,
    ] );

    if ( ! hasDhlProvider ) {
        return null;
    }

    console.log(preferredOptions);

    return (
        <div className="wc-gzd-shipping-provider-options">
            { preferredOptionsAvailable &&
                <DhlPreferredOptions
                    preferredDayEnabled={ preferredDayEnabled }
                    preferredDays={ dhlOptions.preferred_days }
                    setPreferredOption={ setPreferredOption }
                    currency={ totalsCurrency }
                    preferredDayCost={ preferredDayCost }
                    preferredOptions={ preferredOptions }
                    preferredLocationEnabled={ preferredLocationEnabled }
                    preferredNeighborEnabled={ preferredNeighborEnabled }
                />
            }
            { isCdpAvailable &&
                <DhlCdpOptions
                    setPreferredOption={ setPreferredOption }
                    preferredOptions={ preferredOptions }
                    homeDeliveryCost={ homeDeliveryCost }
                    currency={ totalsCurrency }
                />
            }
        </div>
    );
};

const render = () => {
    return (
        <ExperimentalOrderShippingPackages>
            <DhlPreferredDeliveryOptions />
        </ExperimentalOrderShippingPackages>
    );
};

registerPlugin( 'woocommerce-germanized-dhl-preferred-services', {
    render,
    scope: 'woocommerce-checkout',
} );