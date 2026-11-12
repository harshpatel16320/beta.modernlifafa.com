jQuery(document).ready(function ($) {
  
    /* Checkout form validation */
    $("#custom-checkout-form").validate({
        rules: {
            billing_first_name: {
                required: true,
                minlength: 2
            },
            billing_last_name: {
                required: true,
                minlength: 2
            },
            billing_email: {
                required: true,
                email: true
            },
            billing_phone: {
               // required: true,
                digits: true,
                minlength: 10,
                maxlength: 15
            },
            billing_address_1: {
                required: true
            },
            billing_city: {
                required: true
            },
            billing_postcode: {
                required: true,
                digits: true
            },
            terms: {
                required: true
            }            
        },
        messages: {
            billing_first_name:{
                required:  "Please enter your first name.",
                minlength : "Name must be at least 2 characters."
            },
            billing_last_name: {
                required:  "Please enter your last name.",
                minlength : "Name must be at least 2 characters."
            },
            billing_email: {
                required: "Please enter your email.",
                email: "Please enter a valid email address."
            },
            billing_phone: {
               // required: "Please enter your phone number.",
                digits: "Only numbers are allowed.",
                minlength: "Phone number must be at least 10 digits.",
                maxlength: "Phone number cannot exceed 15 digits."
            },
            billing_address_1: {
                required: "Please enter your billing address."
            },
            billing_city: {
                required: "Please enter your city."
            },
            billing_postcode: {
                required: "Please enter your postal code.",
                digits: "Please enter a valid postal code."
            },
            terms: {
                required: "You must accept the terms and conditions."
            } 
        },
        errorElement: "div",
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        },
        submitHandler: function (form) {
            form.submit(); // Allow form submission if valid
        }
    });

    /* Prevent checkout if form is invalid */
    // $(document).on("click", "#place_order", function (e) {
    //     e.preventDefault();
        
    // });

});

jQuery(document).ready(function ($) {
    // alert("HEllo");
    function addTooltipToFees() {
        $('.wc-block-components-totals-item__label').each(function () {
            let labelText = $(this).text().trim();

            if (labelText.includes("Platform Fees") && !$(this).find(".platform-fee-tooltip").length) {
                $(this).append(`
                    <span class="platform-fee-tooltip">
                        <i class="dashicons dashicons-info" title="This fee covers platform maintenance and services."></i>
                    </span>
                `);
            }
        });
    }

    // Run on page load
    addTooltipToFees();

    // Run when cart updates (WooCommerce Blocks trigger `wc-blocks` events)
    $(document.body).on('wc-blocks-order-review-update wc-cart-totals-updated', function () {
        addTooltipToFees();
    });
    
});


document.addEventListener("DOMContentLoaded", function () {
    const relatedSlider = document.querySelector(".related-product-slider-cls");
    if (relatedSlider) {
      const slides = relatedSlider.querySelectorAll(".swiper-slide");
      if (slides.length === 1) {
        slides[0].classList.add("one_related");
      }
    }
  });
  
  jQuery(document).ready(function($){
    if ($(window).width() <= 768) {
        var maxLength = 200;
        $(".read-more-text").each(function(){
            var $p = $(this);
            var fullText = $p.clone().children().remove().end().text().trim();

            if (fullText.length > maxLength) {
                var shortText = fullText.substring(0, maxLength) + '...';
                $p.data("full-text", fullText);
                $p.data("short-text", shortText);
                $p.html('<span class="text-part"></span><span class="toggle-read-more">more</span>');
                $p.find('.text-part').text(shortText);
            }
        });

        $(document).on("click", ".toggle-read-more", function(){
            var $toggle = $(this);
            var $p = $toggle.closest(".read-more-text");
            var isExpanded = $toggle.text().trim() === "less"; // <- FIXED HERE
            var fullText = $p.data("full-text");
            var shortText = $p.data("short-text");

            if (!isExpanded) {
                $p.find(".text-part").text(fullText);
                $toggle.text("less");
            } else {
                $p.find(".text-part").text(shortText);
                $toggle.text("more");
            }
        });
    }
});

function initAutocomplete() {
    /**
     * Get country code from selected country dropdown
     * This allows dynamic country selection instead of hardcoded country
     */
    function getSelectedCountryCode(fieldType) {
        let countrySelect;
        if (fieldType === 'billing') {
            countrySelect = document.querySelector('select[name="billing_country"]');
        } else if (fieldType === 'shipping') {
            countrySelect = document.querySelector('select[name="shipping_country"]');
        }
        
        if (countrySelect && countrySelect.value) {
            return countrySelect.value.toLowerCase();
        }
        return ''; // No country restriction if not selected
    }

    /**
     * Helper function to create autocomplete with dynamic country
     */
    function createAutocompleteInstance(inputElement, countryFieldType) {
        if (!inputElement) return null;

        const options = {
            types: ["geocode"]
        };

        // Only add country restriction if a country is selected
        const countryCode = getSelectedCountryCode(countryFieldType);
        if (countryCode) {
            options.componentRestrictions = { country: countryCode };
        }

        return new google.maps.places.Autocomplete(inputElement, options);
    }


    // ==========================================
    // BILLING ADDRESS AUTOCOMPLETE
    // ==========================================
    const billingInput = document.getElementById("billing_address_1");
    if (billingInput) {
        let billingAutocomplete = createAutocompleteInstance(billingInput, 'billing');

        if (billingAutocomplete) {
            billingAutocomplete.addListener("place_changed", function () {
                const place = billingAutocomplete.getPlace();
                if (!place.geometry) return;

                let street = '', city = '', postal_code = '', address2 = '', stateName = '', countryCode = '';

                for (const component of place.address_components) {
                    const types = component.types;

                    if (types.includes("street_number")) {
                        street = component.long_name + " " + street;
                    }
                    if (types.includes("route")) {
                        street += component.long_name;
                    }
                    if (types.includes("sublocality") || types.includes("sublocality_level_1")) {
                        address2 = component.long_name;
                    }
                    if (types.includes("locality")) {
                        city = component.long_name;
                    }
                    if (types.includes("postal_code")) {
                        postal_code = component.long_name;
                    }
                    if (types.includes("administrative_area_level_1")) {
                        stateName = component.long_name;
                    }
                    if (types.includes("country")) {
                        countryCode = component.short_name.toUpperCase();
                    }
                }

                document.getElementById("billing_address_1").value = street;
                document.getElementById("billing_address_2").value = address2;
                document.getElementById("billing_city").value = city;
                document.getElementById("billing_postcode").value = postal_code;

                // Set country if found
                const countrySelect = document.querySelector('select[name="billing_country"]');
                if (countrySelect && countryCode) {
                    countrySelect.value = countryCode;
                    const countryChangeEvent = new Event('change', { bubbles: true });
                    countrySelect.dispatchEvent(countryChangeEvent);
                }

                // Set state after country is set (with a small delay for state dropdown to populate)
                if (stateName) {
                    setTimeout(function() {
                        const stateSelect = document.getElementById("billing_state");
                        if (stateSelect) {
                            const options = stateSelect.options;
                            for (let i = 0; i < options.length; i++) {
                                if (options[i].text.toLowerCase() === stateName.toLowerCase() || 
                                    options[i].value.toLowerCase() === stateName.toLowerCase()) {
                                    stateSelect.value = options[i].value;
                                    const stateChangeEvent = new Event('change', { bubbles: true });
                                    stateSelect.dispatchEvent(stateChangeEvent);
                                    break;
                                }
                            }
                        }
                    }, 200);
                }

                billingInput.blur();
            });

            // Update autocomplete when billing country changes
            const billingCountrySelect = document.querySelector('select[name="billing_country"]');
            if (billingCountrySelect) {
                billingCountrySelect.addEventListener('change', function () {
                    billingAutocomplete.setComponentRestrictions({
                        country: this.value.toLowerCase()
                    });

                    console.log("Billing country changed:", this.value);
                });
            }
        }
    }

    // ==========================================
    // SHIPPING ADDRESS AUTOCOMPLETE
    // ==========================================
    const shippingInput = document.getElementById("shipping_address_1");
    if (shippingInput) {
        let shippingAutocomplete = createAutocompleteInstance(shippingInput, 'shipping');

        if (shippingAutocomplete) {
            shippingAutocomplete.addListener("place_changed", function () {
                const place = shippingAutocomplete.getPlace();
                if (!place.geometry) return;

                let street = '', city = '', postal_code = '', address2 = '', stateName = '', countryCode = '';

                for (const component of place.address_components) {
                    const types = component.types;

                    if (types.includes("street_number")) {
                        street = component.long_name + " " + street;
                    }
                    if (types.includes("route")) {
                        street += component.long_name;
                    }
                    if (types.includes("sublocality") || types.includes("sublocality_level_1")) {
                        address2 = component.long_name;
                    }
                    if (
                        types.includes("locality") ||
                        types.includes("postal_town") ||
                        types.includes("administrative_area_level_2")
                    ) {
                        city = component.long_name;
                    }
                    if (types.includes("postal_code")) {
                        postal_code = component.long_name;
                    }
                    if (types.includes("administrative_area_level_1")) {
                        stateName = component.long_name;
                    }
                    if (types.includes("country")) {
                        countryCode = component.short_name.toUpperCase();
                    }
                }

                document.getElementById("shipping_address_1").value = street;
                document.getElementById("shipping_address_2").value = address2;
                document.getElementById("shipping_city").value = city;
                document.getElementById("shipping_postcode").value = postal_code;

                // Set country if found
                const countrySelect = document.querySelector('select[name="shipping_country"]');
                if (countrySelect && countryCode) {
                    countrySelect.value = countryCode;
                    const countryChangeEvent = new Event('change', { bubbles: true });
                    countrySelect.dispatchEvent(countryChangeEvent);
                }

                // Set state after country is set (with a small delay for state dropdown to populate)
                if (stateName) {
                    setTimeout(function() {
                        const stateSelect = document.getElementById("shipping_state");
                        if (stateSelect) {
                            const options = stateSelect.options;
                            for (let i = 0; i < options.length; i++) {
                                if (options[i].text.toLowerCase() === stateName.toLowerCase() || 
                                    options[i].value.toLowerCase() === stateName.toLowerCase()) {
                                    stateSelect.value = options[i].value;
                                    const stateChangeEvent = new Event('change', { bubbles: true });
                                    stateSelect.dispatchEvent(stateChangeEvent);
                                    break;
                                }
                            }
                        }
                    }, 200);
                }

                shippingInput.blur();
            });

            // Update autocomplete when shipping country changes
            const shippingCountrySelect = document.querySelector('select[name="shipping_country"]');
            if (shippingCountrySelect) {
                shippingCountrySelect.addEventListener('change', function () {
                    shippingAutocomplete.setComponentRestrictions({
                        country: this.value.toLowerCase()
                    });

                    console.log("Shipping country changed:", this.value);
                });
            }
        }
    }
}
window.onload = initAutocomplete;

