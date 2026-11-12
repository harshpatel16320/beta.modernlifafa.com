jQuery(document).ready(function ($) {
    jQuery(document).on('click', '.wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input', function() {
        console.log('Checkbox clicked!');
    });
    $('#email').one('focusout', function () {
        setTimeout(function () {
            $('#contact-namespace-toggle-shipping-address').trigger('click');
        }, 500 );
    });
    // Check if gift user data is available
    // Ensure the script runs only when WooCommerce's checkout is initialized
    // $('#contact-namespace-toggle-shipping-address').trigger('click');
    setTimeout(function () {
        jQuery('#checkbox-control-1').click();
        $('.wc-block-components-shipping-address').hide();
         $('.wp-block-woocommerce-checkout-shipping-address-block').hide();
        // $('#contact-namespace-toggle-shipping-address').trigger('click');
        if (typeof giftUserData !== 'undefined' && Object.keys(giftUserData).length > 0) {
            // console.log('hello2');
            // $('#shipping-first_name').removeAttr('required');
            // $('#shipping-first_name').parent().addClass('is-active');
            // $('#shipping-first_name').val(giftUserData.first_name).trigger('input').trigger('change');
            // $('#shipping-first_name').attr('value',giftUserData.first_name);
            // $('#shipping-last_name').val(giftUserData.last_name).trigger('input').trigger('change');
            // $('#shipping-last_name').parent().addClass('is-active');
            // $('#shipping-last_name').val(giftUserData.last_name).trigger('input').trigger('change');
            // $('#shipping-last_name').attr('value',giftUserData.last_name);
            // $('#shipping-address_1').removeAttr('required');
            // $('#shipping-city').removeAttr('required');
            // $('#shipping-postcode').removeAttr('required');
            // $('#shipping-country').removeAttr('required');
            // $('#shipping-state').removeAttr('required');
            // $('#shipping-address_1').parent().addClass('is-active');
            // $('#shipping-address_1').val(giftUserData.shipping_address_1).trigger('input');
            // $('#shipping-address_1').attr('value',giftUserData.shipping_city).trigger('change');
            // $('#shipping-city').parent().addClass('is-active');
            // $('#shipping-city').val(giftUserData.shipping_city).trigger('input');
            // $('#shipping-city').attr('value',giftUserData.shipping_city);
            // $('#shipping-postcode').parent().addClass('is-active');
            // $('#shipping-postcode').val(giftUserData.shipping_postcode).trigger('input').trigger('change');
            // $('#shipping-postcode').attr('value', giftUserData.shipping_postcode);
            // $('#shipping-country').val(giftUserData.shipping_country).change();
            // $('#shipping-state').val(giftUserData.shipping_state);
            // $('.wc-block-components-address-card__address-section').html(
            //     '<span>'+giftUserData.first_name + ' ' +giftUserData.last_name + '</span><br>' +
            //     '<span>'+giftUserData.shipping_address_1+'</span>'+
            //     '<span>'+giftUserData.shipping_city+' '+giftUserData.shipping_postcode+'</span>'+
            //     '<span>'+giftUserData.shipping_state+'</span>'+
            //     '<span>'+giftUserData.shipping_address_1+'</span>'
            // );
        }
        // $('#')
    }, 2500);
// });

// Testing - 2
    // setTimeout(function () {
    //     if (typeof giftUserData !== 'undefined' && Object.keys(giftUserData).length > 0) {

    //         function updateField(selector, value) {
    //             const field = $(selector);
                
    //             // Set the value explicitly without clearing the field or placeholder
    //             field.val(value);
    
    //             // Trigger the input and change events for WooCommerce recalculations
    //             field[0].dispatchEvent(new Event('input', { bubbles: true }));
    //             field[0].dispatchEvent(new Event('change', { bubbles: true }));
    //         }
    
    //         // Sequentially update fields without clearing the placeholder
    //         updateField('#shipping-address_1', giftUserData.shipping_address_1);
    //         updateField('#shipping-city', giftUserData.shipping_city);
    //         updateField('#shipping-postcode', giftUserData.shipping_postcode);
    
    //         // Handle dropdown fields (if applicable)
    //         $('#shipping-country').val(giftUserData.shipping_country).change();
    //         $('#shipping-state').val(giftUserData.shipping_state).change();
    
    //         // Trigger WooCommerce checkout update after filling all fields
    //         $('body').trigger('updated_checkout');
    
    //         console.log("All shipping fields populated successfully:", giftUserData);
    //     } else {
    //         console.log("No gift user data available.");
    //     }
    // }, 2000);    

    // 20-12-2024

    // setTimeout(function () {
    //     if (typeof giftUserData !== 'undefined' && Object.keys(giftUserData).length > 0) {
    //         console.log("Populating shipping fields with giftUserData:", giftUserData);
    
    //         function updateField(selector, value) {
    //             const field = document.querySelector(selector);
    
    //             if (field) {
    //                 // Set the value programmatically
    //                 field.value = value;
    
    //                 // Trigger input and change events for WooCommerce updates
    //                 field.dispatchEvent(new Event('input', { bubbles: true }));
    //                 field.dispatchEvent(new Event('change', { bubbles: true }));
    
    //                 console.log(`Updated field ${selector} with value: ${value}`);
    //             } else {
    //                 console.warn(`Field not found for selector: ${selector}`);
    //             }
    //         }
    
    //         // Update each shipping field
    //         updateField('#shipping_address_1', giftUserData.shipping_address_1);
    //         updateField('#shipping_city', giftUserData.shipping_city);
    //         updateField('#shipping_postcode', giftUserData.shipping_postcode);
    
    //         // Update country and state dropdowns
    //         const countryField = document.querySelector('#shipping_country');
    //         const stateField = document.querySelector('#shipping_state');
    
    //         if (countryField) {
    //             countryField.value = giftUserData.shipping_country;
    //             countryField.dispatchEvent(new Event('change', { bubbles: true }));
    //             console.log(`Updated country to: ${giftUserData.shipping_country}`);
    //         }
    
    //         if (stateField) {
    //             stateField.value = giftUserData.shipping_state;
    //             stateField.dispatchEvent(new Event('change', { bubbles: true }));
    //             console.log(`Updated state to: ${giftUserData.shipping_state}`);
    //         }
    
    //         // Trigger WooCommerce checkout recalculations
    //         document.body.dispatchEvent(new Event('update_checkout', { bubbles: true }));
    
    //         console.log("Shipping fields updated successfully.");
    //     } else {
    //         console.log("No data available in giftUserData.");
    //     }
    // }, 2000);
    
});

// Testing - 3
// jQuery(document).ready(function ($) {
//     function populateShippingFields(giftUserData) {
//         if (giftUserData && Object.keys(giftUserData).length > 0) {
//             $('#shipping_address_1').val(giftUserData.shipping_address_1);
//             $('#shipping_city').val(giftUserData.shipping_city);
//             $('#shipping_postcode').val(giftUserData.shipping_postcode);
//             $('#shipping_country').val(giftUserData.shipping_country).trigger('change');
//             $('#shipping_state').val(giftUserData.shipping_state).trigger('change');
//             $('body').trigger('updated_checkout');
//             console.log("Shipping fields populated with gift user data:", giftUserData);
//         } else {
//             console.log("No gift user data available.");
//         }
//     }
//     const giftUserData = {
//         shipping_address_1: '123 Test St',
//         shipping_city: 'Test City',
//         shipping_postcode: '12345',
//         shipping_country: 'US',
//         shipping_state: 'CA'
//     };
//     populateShippingFields(giftUserData);
//     $(document.body).on('updated_checkout', function () {
//         console.log('WooCommerce checkout updated');
//     });
//     $(document).on('checkout_place_order', function () {
//         $('form.checkout').find('#shipping_address_1, #shipping_city, #shipping_postcode, #shipping_country, #shipping_state').each(function () {
//             $(this).removeAttr('required');
//         });
//     });
//     $(document).on('checkout_place_order', function () {
//         $('form.checkout').find('#shipping_address_1, #shipping_city, #shipping_postcode, #shipping_country, #shipping_state').each(function () {
//             $(this).attr('required', 'required');
//         });
//     });
// });

// document.addEventListener('DOMContentLoaded', function () {
//     setTimeout(function () {
//         if (typeof giftUserData !== 'undefined' && Object.keys(giftUserData).length > 0) {
//             console.log("Populating shipping fields with giftUserData:", giftUserData);
//             function updateField(selector, value) {
//                 const field = document.querySelector(selector);
//                 if (field) {
//                     field.value = value;
//                     field.dispatchEvent(new Event('input', { bubbles: true }));
//                     field.dispatchEvent(new Event('change', { bubbles: true }));
//                     console.log(`Updated field ${selector} with value: ${value}`);
//                 } else {
//                     console.warn(`Field not found for selector: ${selector}`);
//                 }
//             }
//             console.log("Gift User Data contains:", giftUserData);
//             updateField('#shipping-address_1', giftUserData.shipping_address_1);
//             updateField('#shipping-city', giftUserData.shipping_city);
//             updateField('#shipping-postcode', giftUserData.shipping_postcode);
//             if (giftUserData.shipping_country) {
//                 updateField('#shipping-country', giftUserData.shipping_country);
//             }
//             if (giftUserData.shipping_state) {
//                 updateField('#shipping-state', giftUserData.shipping_state);
//             }
//             document.body.dispatchEvent(new Event('update_checkout', { bubbles: true }));
//             console.log("Shipping fields updated successfully.");
//         } else {
//             console.log("No data available in giftUserData.");
//         }
//     }, 2000);
// });

// document.addEventListener('DOMContentLoaded', function () {
//     // Simulated giftUser  Data for demonstration
//     const giftUserData = {
//         shipping_address_1: "123 Main St",
//         shipping_address_2: "Apt 4B",
//         shipping_city: "New York",
//         shipping_postcode: "10001",
//         shipping_country: "US",
//         shipping_state: "NY"
//     };

//     // Function to set shipping address
//     function setShippingAddress() {
//         if (typeof giftUserData !== 'undefined' && Object.keys(giftUserData).length > 0) {
//             console.log('Setting shipping address:', giftUserData); // Debugging log
//             wp.data.dispatch('core/block-editor').updateBlockAttributes(
//                 'woocommerce/checkout', // The block name
//                 {
//                     shipping: {
//                         address_1: giftUserData.shipping_address_1,
//                         address_2: giftUserData.shipping_address_2,
//                         city: giftUserData.shipping_city,
//                         postcode: giftUserData.shipping_postcode,
//                         country: giftUserData.shipping_country,
//                         state: giftUserData.shipping_state,
//                     }
//                 }
//             );

//             // Optionally update the address display
//             $('.wc-block-components-address-card__address-section').html(
//                 '<span>' + giftUserData.shipping_address_1 + '</span>' +
//                 '<span>' + giftUserData.shipping_city + ' ' + giftUserData.shipping_postcode + '</span>' +
//                 '<span>' + giftUserData.shipping_state + '</span>' +
//                 '<span>' + giftUserData.shipping_country + '</span>'
//             );
//         } else {
//             console.log('No giftUser  Data available'); // Debugging log
//         }
//     }

//     // Call the function immediately or after a delay
//     setShippingAddress(); // Call immediately if data is ready
//     // Alternatively, use setTimeout if data is not ready immediately
//     // setTimeout(setShippingAddress, 5000);
// });


document.addEventListener('DOMContentLoaded', function () {
    // Simulated giftUser  Data for demonstration
    // const giftUserData2 = {
    //     shipping_address_1: "123 Main St",
    //     shipping_address_2: "Apt 4B",
    //     shipping_city: "New York",
    //     shipping_postcode: "10001",
    //     shipping_country: "US",
    //     shipping_state: "NY"
    // };

    // Function to set shipping address
    // function setShippingAddress() {
    //     if (typeof giftUserData2 !== 'undefined' && Object.keys(giftUserData2).length > 0) {
    //         console.log('Setting shipping address:', giftUserData2); // Debugging log

    //         // Use wp.data to set the shipping address
    //         const checkoutBlock = wp.data.select('core/block-editor').getBlocks().find(block => block.name === 'woocommerce/checkout');

    //         if (checkoutBlock) {
    //             // Update the shipping address attributes
    //             wp.data.dispatch('core/block-editor').updateBlockAttributes(checkoutBlock.clientId, {
    //                 attributes: {
    //                     shipping: {
    //                         address_1: giftUserData2.shipping_address_1,
    //                         address_2: giftUserData2.shipping_address_2,
    //                         city: giftUserData2.shipping_city,
    //                         postcode: giftUserData2.shipping_postcode,
    //                         country: giftUserData2.shipping_country,
    //                         state: giftUserData2.shipping_state,
    //                     }
    //                 }
    //             });

    //             // Trigger change events for country and state
    //             const countryField = document.querySelector('[name="shipping_country"]');
    //             const stateField = document.querySelector('[name="shipping_state"]');

    //             if (countryField) {
    //                 countryField.value = giftUserData2.shipping_country;
    //                 countryField.dispatchEvent(new Event('change', { bubbles: true })); // Trigger change event
    //             }

    //             if (stateField) {
    //                 stateField.value = giftUserData2.shipping_state;
    //                 stateField.dispatchEvent(new Event('change', { bubbles: true })); // Trigger change event
    //             }

    //             // Optionally update the address display
    //             const addressDisplay = document.querySelector('.wc-block-components-address-card__address-section');
    //             if (addressDisplay) {
    //                 addressDisplay.innerHTML = `
    //                     <span>${giftUserData2.shipping_address_1}</span>
    //                     <span>${giftUserData2.shipping_city} ${giftUserData2.shipping_postcode}</span>
    //                     <span>${giftUserData2.shipping_state}</span>
    //                     <span>${giftUserData2.shipping_country}</span>
    //                 `;
    //             }
    //         } else {
    //             console.log('Checkout block not found'); // Debugging log
    //         }
    //     } else {
    //         console.log('No giftUser  Data available'); // Debugging log
    //     }
    // }

    // Check for the checkout block with a longer delay
    // const checkForCheckoutBlock = setInterval(() => {
    //     const blocks = wp.data.select('core/block-editor').getBlocks();
    //     console.log(blocks);
    //     const checkoutBlock = blocks.find(block => block.name === 'woocommerce/checkout');
    //     if (checkoutBlock) {
    //         console.log('Checkout Block Found:', checkoutBlock);
    //     } else {
    //         console.log('Checkout Block Not Found');
    //     }
    //          // const checkoutBlock = wp.data.select('core/block-editor').getBlocks().find(block => block.name === 'woocommerce/checkout');
    //     if (checkoutBlock) {
    //         setShippingAddress();
    //         clearInterval(checkForCheckoutBlock); // Stop checking once the block is found
    //     } else {
    //         console.log('Waiting for checkout block...'); // Debugging log
    //     }
    // }, 1000); // Check every second
});

// 24-03-2025

( function( wp, wc ) {
    const { addFilter } = wp.hooks;
    const { useState, useEffect } = wp.element;
    const { CheckboxControl } = wp.components;
    const { createElement, Fragment } = wp.element;
    const { __ } = wp.i18n;

    const CustomShippingComponent = ( OriginalComponent ) => ( props ) => {
        const [usePredefinedShipping, setUsePredefinedShipping] = useState(true);

        useEffect(() => {
            // Remove "Ship to a different address?" checkbox
            const shipToDifferentAddressCheckbox = document.querySelector('input[name="ship_to_different_address"]');
            if (shipToDifferentAddressCheckbox) {
                shipToDifferentAddressCheckbox.checked = true; // Force it checked
                shipToDifferentAddressCheckbox.closest('div').style.display = 'none'; // Hide the checkbox
            }

            // Fetch predefined shipping address if the checkbox is enabled
            if (usePredefinedShipping) {
                fetch('/wp-json/custom/v1/get-shipping-address/', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        props.setShippingAddress( data.shipping );
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error fetching shipping address:', error));
            }
        }, [usePredefinedShipping]);

        return createElement(
            Fragment,
            null,
            createElement( OriginalComponent, props ),
            createElement( 'div', { className: 'custom-shipping-checkbox' },
                createElement( CheckboxControl, {
                    label: __( 'Use predefined shipping address', 'your-text-domain' ),
                    checked: usePredefinedShipping,
                    onChange: () => setUsePredefinedShipping(!usePredefinedShipping)
                })
            )
        );
    };

    // Always show billing address
    const AlwaysShowBilling = ( OriginalComponent ) => ( props ) => {
        useEffect(() => {
            const billingSection = document.querySelector('.wc-block-components-billing-address');
            if (billingSection) {
                billingSection.style.display = 'block';
            }
        }, []);

        return createElement( OriginalComponent, props );
    };

    addFilter(
        'woocommerce.checkout.blocks.components.ShippingAddress',
        'custom-checkout-shipping',
        CustomShippingComponent
    );

    addFilter(
        'woocommerce.checkout.blocks.components.BillingAddress',
        'custom-checkout-billing',
        AlwaysShowBilling
    );
} )( window.wp, window.wcBlocksCheckout );










