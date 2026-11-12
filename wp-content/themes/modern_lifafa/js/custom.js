jQuery(document).ready(function ($) {

    // Initially add class
    $('.image-wrap').addClass('dokan-open-hide');

    // Watch featured image input value change continuously
    setInterval(function () {

        $('.dokan-feat-image-upload').each(function () {

            let imageId = $(this).find('.dokan-feat-image-id').val();
            let imageWrap = $(this).find('.image-wrap');

            // Image selected/uploaded
            if (imageId && imageId != '0') {
                imageWrap.removeClass('dokan-open-hide');
            } else {
                imageWrap.addClass('dokan-open-hide');
            }

        });

    }, 200);

});


// cross sell product carousel in product details page 
jQuery(document).ready(function ($) {
    $('.cross-sell-carousel').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        arrows: true,
        dots: false,
// 	   	autoplay: true,          // ✅ enable auto slide
        autoplaySpeed: 1200,     // ✅ time (2 seconds)
        speed: 800,              // ✅ transition speed
        pauseOnHover: true,      // optional (pause on hover)

        responsive: [
            {
                breakpoint: 1024,
                settings: { slidesToShow: 3 }
            },
            {
                breakpoint: 768,
                settings: { slidesToShow: 2 }
            },
            {
                breakpoint: 480,
                settings: { slidesToShow: 1 }
            }
        ]
    });
});

// document.addEventListener('DOMContentLoaded', function () {

//     document.body.addEventListener('click', function(e) {
//         if (e.target.closest('.wc-block-cart-item__remove-link')) {

//             // Wait for WooCommerce to update cart via AJAX
//             setTimeout(function() {
//                 window.location.reload();
//             }, 800); // small delay required
//         }
//     });

// });

// when item remove from cart than refresh the page

document.addEventListener('DOMContentLoaded', function () {

    // ✅ Fix back button cache issue
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    // ✅ Handle remove from Cart Block + Mini Cart
    document.body.addEventListener('click', function(e) {

        // Cart Block remove button
        if (e.target.closest('.wc-block-cart-item__remove-link')) {
            setTimeout(function() {
                window.location.reload();
            }, 800);
        }

        // Elementor Mini Cart remove button
        if (
            e.target.closest('.elementor_remove_from_cart_button') ||
            e.target.closest('.remove_from_cart_button')
        ) {
            setTimeout(function() {
                window.location.reload();
            }, 800);
        }

    });

});

jQuery(document).ready(function ($) {

    window.showNotification = function(message, callback) {
    let notification = document.createElement("div");
    notification.className = "custom-notification new-custom-cls-add";
    notification.innerHTML = `
        <div class="notification-content newall-notification-custom-type">
            <div class="message"><p>${message}</p></div>
            <button class="notification-btn">OK</button>
        </div>`;
    document.body.appendChild(notification);
    document.body.classList.add("open-modal");

    jQuery(document).on("click", ".notification-btn", function () {
        jQuery("body").removeClass("open-modal");
        jQuery(".custom-notification").remove(); 
        if (typeof callback === "function") {
            callback();
        }
    });
};


    // alert('hello');
    // jQuery('.user_search').on('input', function () {
   let searchTimeout;
    $(document).on('input', '.user_search', function () {
        let query = $(this).val().trim();

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            $('.search-results-list').html('');
            return;
        }

        searchTimeout = setTimeout(function () {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'user_search',
                    query: query,
                },
                success: function (response) {
                    $('.search-results-list').html(response);
                }
            });
        }, 300); // delay (300ms)
    });

    $('.model-close-btn').on('click', function () {
        $('.user_search').val('').trigger('input');
    });
});

jQuery(document).ready(function($) {
    $('.find-couple-popup').on('click', function(e) {
        e.preventDefault();
        elementorProFrontend.modules.popup.showPopup({id: 1989});
    });

    $(document).on('click', '#find-couple-cart', function(e) {
   // $('#find-couple-cart').on('click', function(e) {
        e.preventDefault();
        elementorProFrontend.modules.popup.showPopup({id: 1989});
    });

    // document.addEventListener('wpcf7mailsent', function(event) {
    //     elementorProFrontend.modules.popup.showPopup({id: 7212});
    // }, false);

});

jQuery(document).ready(function($) {
    $('.banner-images-popup').on('click', function(e) {
        e.preventDefault();
        elementorProFrontend.modules.popup.showPopup({id: 4718});
    });

    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'shipping_address_filled',
            // query: query,
        },
        success: function (response) {
            if (response.success) {
                // elementorProFrontend.modules.popup.showPopup({id: 2382});
            } else {
                $('.share-wishlist').addClass('disabled');
            }
        }
    });
});


jQuery(document).ready(function($) {
    $('.share-wishlist').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'shipping_address_filled',
                // query: query,
            },
            success: function (response) {
                if (response.success) {
                    elementorProFrontend.modules.popup.showPopup({id: 2382});
                } else {
                    $('.share-wishlist').addClass('disabled');
                }
            }
        });
    });
});
jQuery(document).ready(function($) {
    $('.delete_account').on('click', function(e) {
        // alert('hello');
        e.preventDefault();
        elementorProFrontend.modules.popup.showPopup({id: 3630});
    });

    // $(document).on('click', '#cancel-delete-btn', function(e) {
    //     e.preventDefault();
    //     elementorProFrontend.modules.popup.closePopup({id: 3630});
    // });
});

document.getElementById('copyButton').addEventListener('click', function() {
    var copyText = document.querySelector('.page_link_share');
    copyText.select();
    document.execCommand("copy");
    // alert("Copied: " + copyText.value);
});

/* Registration validation js start */

jQuery.validator.addMethod("customname", function(value, element) {
    return this.optional(element) || /^[a-zA-Z\s]+$/.test(value.trim());
}, "Please enter a valid name");
jQuery.validator.addMethod("customphone", function(value, element) {
    return this.optional(element) || /^\+?\d{10,14}$/.test(value);
}, "Please enter a valid phone number");
jQuery.validator.addMethod("strongPassword", function(value, element) {
    return this.optional(element) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/.test(value);
}, "Password must be at least 8 characters long and contain at least one number, one lowercase and one uppercase letter.");
jQuery.validator.addMethod("imageType", function(value, element) {
    return this.optional(element) || /\.(jpe?g|png|webp)$/i.test(value);
}, "Please upload a valid image (JPEG, JPG, PNG, WEBP).");
jQuery.validator.addMethod("futureDate", function(value, element) {
    var selectedDate = new Date(value);
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    return this.optional(element) || selectedDate >= today;
}, "Please select a future date as Wedding date.");
jQuery('#woocommerce-form-register').validate({
    rules: {
        billing_first_name: {
            required: true,
            customname: true,
            minlength: 2
        },
        billing_last_name: {
            required: true,
            customname: true,
            minlength: 2
        },
        email: {
            required: true,
            email: true,
        },
        // phone: {
        //     customphone: true,
        // },
        password_1: {
            required: true,
            strongPassword : true,
        },
        password_2: {
            equalTo: "#password_1",
        },
        partner_fname: {
            required: true,
            customname: true,
            minlength: 2
        },
        partner_lname: {
            required: true,
            customname: true,
            minlength: 2
        },
        wedding_date : {
            futureDate: true
        }
    },
    messages: {
        billing_first_name:{
            required:  "Please Enter Your First Name.",
            customphone:  "Please Enter valid Name",
            minlength : "Please Enter valid Name",
        },
        billing_last_name: {
            required:  "Please Enter Your Last Name.",
            customphone:  "Please Enter Valid Name",
            minlength : "Please Enter valid Name",
        },
        email: {
            required: "Please Enter Your Email.",
            email: "Please Enter Valid Email",
        },
        // phone:{
        //     customphone: "Please Enter Valid Phone Number",
        // },
        password_1: {
            required:  "Please Enter Your Password.",
            strongPassword: "Must be 8+ chars with a number, lowercase and uppercase."
        },
        password_2: {
            equalTo: "New Password and Confirm Password are not same.",
        },
        partner_fname: {
            required: "Please Enter Your Partner's First Name.",
            // customphone:  "Please Enter valid Name",
            minlength : "Please Enter valid Name",
        },
        partner_lname: {
            required: "Please Enter Your Partner's Last Name.",
            // customphone:  "Please Enter valid Name",
            minlength : "Please Enter valid Name",
        },
        wedding_date : {
            futureDate: "Please select a future date as Wedding date."
        }
    },
    errorElement: "div",
    errorPlacement: function(error, element) {
        element.after(error);
    }
});
/* Registration validation js End */

// Header search
jQuery(document).ready(function ($) {
    jQuery('.header_main_search').on('input', function(e) {
        e.preventDefault();
        var searchQuery = $('#s').val();
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'search_results',
                search: searchQuery
            },
            success: function(response) {
                $('.load-search-result').html(response);
            }
        });
    });
});

/* Header search js Start */
jQuery(document).ready(function ($) {
    $(".search-open").click(function () {
        $(".search-box-wrapper").slideToggle();
    });

    $(".close-search-bar").click(function () {
        $('.header_main_search').text('');
        $('.header_main_search').val('');
        $('.load-search-result').html('');
        $(".search-box-wrapper").slideUp();
    });

    // Hide search-box-wrapper slideUp clicking outside
    $(document).click(function (e) {
        if (!$(e.target).closest(".search-open, .search-box-wrapper").length) {
        $(".search-box-wrapper").slideUp();
        }
    });
});
/* Header search js End */

jQuery(document).ready(function ($) {
    $(window).scroll(function () {
      if ($(this).scrollTop() > 50) { 
        $("header").addClass("scrolled");
      } else {
        $("header").removeClass("scrolled");
      }
    });

    jQuery('.password_hide_show').on('click', function() {
        var input = jQuery('#' + jQuery(this).data('id'));
        var showIcon = jQuery('#show_icon').val();
        var hideIcon = jQuery('#hide_icon').val();

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            jQuery(this).find('img').attr('src', showIcon);
        } else {
            input.attr('type', 'password');
            jQuery(this).find('img').attr('src', hideIcon);
        }
    });

    $(document).on('click', '.add-to-personalized', function(e) {
        // alert('hello');
        e.preventDefault();
        var product_id = $(this).data('product-id');
        var button_action = $(this).data('action');
        var count = $('.product_count').text();
        var qty = $('.qty').val();
        var product_type = $(this).data('product-type');
        var variation_id = $('input[name="variation_id"]').val();
        // console.log(variation_id);
        var missingAttrs = [];
        $('.variations select').each(function () {
            if (!$(this).val()) {
                var label = $(this).closest('tr').find('label').text().trim().replace(':', '');
                missingAttrs.push(label);
            }
        });
        if (missingAttrs.length > 0 && $('.variations select').length >= 2) {
            toastr.error('Please select: ' + missingAttrs.join(', '));
            return;
        }
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'add_to_personalized',
                product_id: product_id,
                click_action : button_action,
                quantity : qty,
                variation_id : variation_id,
            },
            success: function(response) {
                if (button_action == 'remove' && response.success) {
                    $('#box'+product_id).remove();
                    count = count-1;
                    toastr.warning('Product Removed from your wishlist.');
                    // alert('Product Removed from your wishlist.');
                    $('.product_count').text(count);
                }else{
                    // toastr.success(response.data.message);
                }
            }
        });

        $('#share-wishlist-modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var link = button.attr('data-href');
            var encodedUrl = encodeURIComponent(link);
            var modal = $(this);
            modal.find('.page_link_share').val(link);
            $('.wtsp_share').attr('href', 'https://api.whatsapp.com/send?text='+encodedUrl);
            $('.fcbk_share').attr('href', 'https://www.facebook.com/sharer.php?u='+encodedUrl);
            $('.twtr_share').attr('href', 'https://twitter.com/intent/tweet?url='+encodedUrl+'&text=Personalised Page');
            $('.gmail_share').attr('href', 'mailto:?subject='+encodedUrl+'&body=Check%20this%20out:%20'+encodedUrl);
        });
    });

// because of this two time product added issue disable this code 

    //  $(document).on('click', '.add-to-amazon-personalized', function(e) {
    //     // alert('hello');
    //     e.preventDefault();
    //     var product_id = $(this).data('product-id');
    //     var button_action = $(this).data('action');
    //     var count = $('.product_count').text();
    //     // var qty = $('.qty').val();
    //     // var product_type = $(this).data('product-type');
    //     // var variation_id = $('input[name="variation_id"]').val();
    //     // console.log(variation_id);
    //     // var missingAttrs = [];
    //     // $('.variations select').each(function () {
    //     //     if (!$(this).val()) {
    //     //         var label = $(this).closest('tr').find('label').text().trim().replace(':', '');
    //     //         missingAttrs.push(label);
    //     //     }
    //     // });
    //     // if (missingAttrs.length > 0 && $('.variations select').length >= 2) {
    //     //     toastr.error('Please select: ' + missingAttrs.join(', '));
    //     //     return;
    //     // }
    //     $.ajax({
    //         url: ajaxurl,
    //         type: 'POST',
    //         data: {
    //             action: 'add_to_amazon_personalized',
    //             product_id: product_id,
    //             click_action : button_action,
    //             quantity : 1,
    //             // variation_id : variation_id,
    //         },
    //         success: function(response) {
    //             if (button_action == 'remove' && response.success) {
    //                 $('#box'+product_id).remove();
    //                 count = count-1;
    //                 toastr.warning('Product Removed from your wishlist.');
    //                 // alert('Product Removed from your wishlist.');
    //                 $('.product_count').text(count);
    //             }else{
    //                 toastr.success(response.data.message);
    //             }
    //         }
    //     });

    //     $('#share-wishlist-modal').on('show.bs.modal', function (event) {
    //         var button = $(event.relatedTarget);
    //         var link = button.attr('data-href');
    //         var encodedUrl = encodeURIComponent(link);
    //         var modal = $(this);
    //         modal.find('.page_link_share').val(link);
    //         $('.wtsp_share').attr('href', 'https://api.whatsapp.com/send?text='+encodedUrl);
    //         $('.fcbk_share').attr('href', 'https://www.facebook.com/sharer.php?u='+encodedUrl);
    //         $('.twtr_share').attr('href', 'https://twitter.com/intent/tweet?url='+encodedUrl+'&text=Personalised Page');
    //         $('.gmail_share').attr('href', 'mailto:?subject='+encodedUrl+'&body=Check%20this%20out:%20'+encodedUrl);
    //     });
    // });
    
    // $(document).on('change', '#sort-options', function() {
    //     var sortValue = $(this).val();
    //     var user_id = $('.user_id').val();
    //     var received_gift = $('.received_gift').val();
    //     $.ajax({
    //         url: ajaxurl,
    //         type: 'POST',
    //         data: {
    //             action: 'sort_wishlist',
    //             sort_order: sortValue,
    //             user_id:user_id,
    //             received_gift:received_gift,
    //         },
    //         success: function(response) {
    //             $('.product-boxes').html(response);
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('AJAX Error:', error);
    //         }
    //     });
    // });

    // $('#copyButton').click(function() {
    $(document).on('click', '#copyButton', function(e) {
        var copyText = $('.page_link_share');
        copyText.select();
        copyText[0].setSelectionRange(0, 99999);
        document.execCommand('copy');
        // $('.copied_msgs').html('Copied');
    });

  });


jQuery(document).ready($ => {
    // Toggle scroll-hide on popup show/hide
    jQuery(document).on('elementor/popup/show elementor/popup/hide', (e) => {
        jQuery('body').toggleClass('scroll-hide', e.type === 'elementor/popup/show');
    });
	jQuery('.elementor-menu-cart__toggle_button, .search--icon, .close-search-bar, .elementor-menu-cart__close-button,.header-nav .elementor-menu-toggle').on('click', () => {
        jQuery('body').toggleClass('scroll-hide');
    });
	
	// Remove scroll-hide when clicking anywhere outside the menu/cart
    jQuery(document).on('click', (e) => {
        if (!$(e.target).closest('.header-nav .elementor-menu-toggle, .elementor-menu-cart__toggle_button, .elementor-menu-cart__close-button').length) {
            jQuery('body').removeClass('scroll-hide');
        }
    });
    jQuery(document).on('click', '.elementor-menu-cart__toggle', () => {
        jQuery('body').addClass('scroll-hide');
    });
    
});

// smooth scrolling
jQuery(document).ready($ => {
    jQuery(".custom_button.secondary_button").click(function() {
        var banner = jQuery("#banner-section");

        if (banner.length) {  // ✅ Run only if #banner-section exists
            jQuery('html, body').animate({
                scrollTop: banner.offset().top - 150
            }, 800);
        }
    });
});



// jQuery(document).ready(function($) {
//     var logoutUrl = '<?php echo wp_logout_url( home_url() ); ?>';
//     $('.logout_site a').attr('href', logoutUrl);
// });

jQuery(document).ready(function($) {
    if (typeof myData !== 'undefined') {
        $('.logout_site a').attr('href', myData.logoutUrl);
    }
    // jQuery(document).on('#confirm-delete-btn', function(e) {
    jQuery(document).on('click', '#confirm-delete-btn', function(e) {
        const password = $('#account-password').val();
        if (!password) {
            alert('Please enter your password.');
            return;
        }
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_account',
                password: password,
            },
            success: function(response) {
                if (response.success) {
                    alert('Your account has been deleted.');
                    window.location.href = myData.logoutUrl;
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
$('#cancel-delete-btn').click(function() {
    $('.close-modal').trigger('click');
});
});
jQuery(document).ready(function ($) {
    $("#load-more").on("click", function () {
        let button = $(this);
        let offset = parseInt(button.data("offset"));
        let user_id = button.data("user");

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: "load_more_wishlist_products",
                offset: offset,
                user_id: user_id
            },
            beforeSend: function () {
                button.text("Loading...");
            },
            success: function (response) {
                if (response.success) {
                    $("#product-list").append(response.data.html);
                    button.data("offset", response.data.new_offset);
                    button.text("Load More");

                    // Hide button if no more products
                    if (response.data.html.trim() === "") {
                        button.hide();
                    }
                } else {
                    button.text("No more products");
                }
            },
            error: function () {
                button.text("Error! Try again.");
            }
        });
    });


    // $(document).on('change', '.product-filter', function(e) {
    $(document).on('change', '.product-filter_cat, .product-filter', function(e) {
    // $('.product-filter').change(function () {
       // alert('hello');
        var category = $('.filter-category').find(":selected").val();
        var price = $('.filter-price').find(":selected").val();
        var brand = $('.filter-brand').find(":selected").val();
        
        var url = new URL(window.location.href);
        var params = new URLSearchParams(url.search);
    
        if(category) {
            params.set('category', category);
        } else {
            params.delete('category');
        }   
    
        if(price) {
            params.set('price', price);
        }else{
            params.delete('price');
        }
    
        if(brand) {
            params.set('filter_brand', brand);
        }else {
            params.delete('filter_brand');
        }
    
        var baseUrl = url.origin + url.pathname.split("page/")[0];
        var queryString = params.toString();
        var newUrl = baseUrl + (queryString ? '?' + queryString : '');
        window.location.href = newUrl.toString();
    });
});

var swiper = new Swiper(".popular-product-slider", {
    slidesPerView: 4,
    spaceBetween: 16,
    navigation: {
        nextEl: ".product-next",
        prevEl: ".product-prev",
    },
    breakpoints: {
        1281: {
            slidesPerView: 4,
        },
        1025: {
            slidesPerView: 3.5,
        },
        992: {
            slidesPerView: 3,
        },
        768:{
            slidesPerView: 3,
        },
        576:{
            slidesPerView: 2,
        },
        481:{
            slidesPerView: 1,
        },
        376:{
            slidesPerView: 1,
        },
        0:{
            slidesPerView: 1,
        },
    },
});

//===============  Product Details slide Js Start  ========================================================//

var sliderThumbnail = new Swiper('.products-thumbnail', {
    slidesPerView: 4,
    spaceBetween: 10,
    freeMode: true,
    watchSlidesVisibility: true,
    watchSlidesProgress: true,

    pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },

    breakpoints: {
        // when window width is >= 768px
        320: {
            slidesPerView: 4,
            spaceBetween: 10
        },
        768: {
            slidesPerView: 3,
            spaceBetween: 5
        },
        1280: {
            slidesPerView: 4,
            spaceBetween: 10
        }
    }

});

var slider = new Swiper('.products-slider', {
    slidesPerView: 1,
    navigation: {
    nextEl: '.product-button-next',
    prevEl: '.product-button-prev',
    },
    thumbs: {
    swiper: sliderThumbnail
    }
});

jQuery(document).ready(function($) {
    $('.product-content table').each(function() {
        $(this).wrap('<div class="content-table-section"></div>');
    });

    //$(".clear_filter").on("click", function() {
        jQuery(document).on('click', '.clear_filter', function (e) {
            e.preventDefault(); // Prevent default behavior
            $('#custom-preloader').show();
            var url = new URL(window.location.href);
            var baseUrl = url.origin + url.pathname.split("page/")[0];
            var coupleName = url.searchParams.get("couple_name");
            var originalUserId = url.searchParams.get("original_user_id");
            var queryParams = [];
            if (coupleName) {
                queryParams.push('couple_name=' + encodeURIComponent(coupleName));
            }
            if (originalUserId) {
                queryParams.push('original_user_id=' + encodeURIComponent(originalUserId));
            }
            if (queryParams.length > 0) {
                baseUrl += '?' + queryParams.join('&');
            }
            window.location.href = baseUrl;
        });
        
});


// Banner image js

// jQuery(document).ready(function ($) {
//     $('.upload-banner-image').click(function (e) {
//             e.preventDefault();
//             var button = $(this);
//             var inputField = button.prev('input');
//             var imgPreview = button.next('img');
            
//                 var mediaUploader = wp.media({
//                 title: 'Choose Image',
//                 library: { type: 'image' },
//                 button: { text: 'Select' },
//                 multiple: false
//                 }).on('select', function () {
//             var attachment = mediaUploader.state().get('selection').first().toJSON();
//             inputField.val(attachment.url);
//             imgPreview.attr('src', attachment.url).show();
//             }).open();
//         });
//     });

jQuery(document).ready(function ($) {
    $(document).on('change', '.filter-dropdown', function() {
    let selectedCategory = $('#category-filter').val();
    let selectedBrand = $('#brand-filter').val();
    
    let baseUrl = window.location.pathname; // Get clean URL
    let params = new URLSearchParams(window.location.search);

    // Remove filter when it's "All" to prevent unnecessary URL parameters
    if (selectedCategory && selectedCategory !== 'All') {
        params.set('category', selectedCategory);
    } else {
        params.delete('category');
    }

    if (selectedBrand && selectedBrand !== 'All') {
        params.set('brand', selectedBrand);
    } else {
        params.delete('brand');
    }

    // Construct final URL
    let newUrl = baseUrl + '?' + params.toString();
    window.location.href = newUrl;
});


    $('.clear_filter').click(function () {
        window.location.href = window.location.pathname; // Reset filters
    });
    
    $('.reset_variations').click(function () {
        window.location.href = window.location.pathname; // Reset filters
    });
});


// Accept reject received gift

jQuery(document).ready(function ($) {
    const EXPIRY_TIME = 24 * 60 * 60 * 1000; 

    function checkGiftExpiry() {
        $('.received-box').each(function () {
            const giftBox = $(this);
            const orderId = giftBox.data('order-id');
            const itemId = giftBox.data('item-id');
            const orderTimeRaw = giftBox.data('order-time');
            const orderTime = parseInt(orderTimeRaw, 10) * 1000 || 0;
            let currentStatus = giftBox.data('status'); 

            // console.log(`Checking Order ID: ${orderId}, Item ID: ${itemId}`);
            // console.log(`Raw Order Time: ${orderTimeRaw}`);
            // console.log(`Parsed Order Time: ${orderTime}`);
            // console.log(`Current Status: ${currentStatus}`);

            // Ensure valid order time
            if (!orderTime || isNaN(orderTime)) {
                // console.warn(`Invalid order time for order ${orderId}, item ${itemId}`);
                return;
            }

            // If already accepted or rejected, do NOT expire
            if (currentStatus === 'accepted' || currentStatus === 'rejected') {
                return;
            }

            const timeElapsed = Date.now() - orderTime;
            // console.log(`Time Elapsed: ${timeElapsed}ms`);

            // Expire only if the gift has no status set (pending) and time exceeds limit
            if (!currentStatus || currentStatus === 'pending') {
                if (timeElapsed > EXPIRY_TIME) {
                    updateGiftStatus(itemId, orderId, 'expired', giftBox);
                }
            }
        });
    }

    // Run expiry check every 1 seconds
    setInterval(checkGiftExpiry, 60 * 60 * 1000);

    $('.accept-gift').on('click', function () {
        const button = $(this);
        const action = button.data('action');
        const giftBox = button.closest('.received-box');
        const itemId = giftBox.data('item-id');
        const orderId = giftBox.data('order-id');
        $('#custom-preloader').show();
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'handle_gift_status',
                item_id: itemId,
                order_id: orderId,
                status: action
            },
            success: function (response) {
                $('#custom-preloader').hide();
                if (response.success) {
                    giftBox.data('status', action);
                    giftBox.find('.accept-gift, .reject-gift').remove();
                    // giftBox.append(`<button type="button" class="custom_button secondary_button" disabled>${response.data.message}</button>`);
                    giftBox.find('.received-actions').html(
                        `<button type="button" class="custom_button secondary_button" disabled>${response.data.message}</button>`
                    );
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    });

    // Function to update gift status when expired
    function updateGiftStatus(itemId, orderId, status, giftBox) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'handle_gift_status',
                item_id: itemId,
                order_id: orderId,
                status: status
            },
            success: function (response) {
                if (response.success) {
                    // Update status locally
                    giftBox.data('status', 'expired');

                    // Ensure "Expired" button is only shown if it was pending before
                    giftBox.find('.accept-gift, .reject-gift').remove();
                    giftBox.append('<button type="button" class="custom_button secondary_button" disabled>Expired</button>');

                    // console.log(`Gift Expired for Order ID: ${orderId}, Item ID: ${itemId}`);
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    }
});

jQuery(document).ready(function($) {
    let homeUrl = window.location.origin; // Get home URL dynamically
    $("style").each(function() {
        $(this).html($(this).html().replace(/{{HOME_URL}}/g, homeUrl));
    });
});


// Wishlist custom profile image auto upload code
jQuery(document).ready(function ($) {
    
    $('#profile_picture_input').on('change', function (e) {
        e.preventDefault();
        $('#custom-preloader').show();

        let fileInput = this;
        let file = fileInput.files[0];
        let errorDiv = $('#profile_picture_error');

        // Remove previous error message
        errorDiv.text('');

        // ✅ Validation: check if file exists
        if (!file) {
            errorDiv.text('Please select a file.');
            $('#custom-preloader').hide();
            return;
        }

        // ✅ Validation: allowed file types
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type.toLowerCase())) {
            errorDiv.text('Only JPG, JPEG, PNG, or WEBP images are allowed.');
            $(fileInput).val(''); // clear invalid file
            $('#custom-preloader').hide();
            return;
        }

        // ✅ Validation: optional size check (example: 2MB max)
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            errorDiv.text('File size must be less than 2MB.');
            $(fileInput).val('');
            $('#custom-preloader').hide();
            return;
        }

        // ✅ Proceed with AJAX if validation passes
        let formData = new FormData();
        formData.append('action', 'update_user_profile_picture');
        formData.append('profile_picture', file);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#custom-preloader').hide();
                if (response.success) {
                    let newImageUrl = response.data.image_url + '?t=' + new Date().getTime();
                    $('.user_profile').attr('src', newImageUrl);
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                } else {
                    alert('Error: ' + (response.data.message || 'Unexpected error.'));
                }
            },
            error: function (xhr) {
                $('#custom-preloader').hide();
                console.log("AJAX Error:", xhr.responseText);
                alert('An error occurred while uploading. Check console for details.');
            }
        });
    });

   
});

// Quantity Add

(function () {
    const quantityContainer = document.querySelector(".quantity");
    const minusBtn = quantityContainer.querySelector(".minus");
    const plusBtn = quantityContainer.querySelector(".plus");
    const inputBox = quantityContainer.querySelector(".input-box");
  
    updateButtonStates();
  
    quantityContainer.addEventListener("click", handleButtonClick);
    inputBox.addEventListener("input", handleQuantityChange);
  
    function updateButtonStates() {
      const value = parseInt(inputBox.value);
      minusBtn.disabled = value <= 1;
      plusBtn.disabled = value >= parseInt(inputBox.max);
    }
  
    function handleButtonClick(event) {
      if (event.target.classList.contains("minus")) {
        decreaseValue();
      } else if (event.target.classList.contains("plus")) {
        increaseValue();
      }
    }
  
    function decreaseValue() {
      let value = parseInt(inputBox.value);
      value = isNaN(value) ? 1 : Math.max(value - 1, 1);
      inputBox.value = value;
      updateButtonStates();
      handleQuantityChange();
    }
  
    function increaseValue() {
      let value = parseInt(inputBox.value);
      value = isNaN(value) ? 1 : Math.min(value + 1, parseInt(inputBox.max));
      inputBox.value = value;
      updateButtonStates();
      handleQuantityChange();
    }
  
    function handleQuantityChange() {
      let value = parseInt(inputBox.value);
      value = isNaN(value) ? 1 : value;
  
      // Execute your code here based on the updated quantity value
      console.log("Quantity changed:", value);
    }
});

jQuery(function ($) {
    function applyCheckboxLogic() {
        var $checkbox = $('#contact-namespace-toggle-shipping-address');
        if ($checkbox.length) {
            console.log('Checkbox found, applying logic.');

            if (!$checkbox.is(':checked')) {
                console.log('Checkbox is not checked on load. Forcing check.');
                $checkbox.prop('checked', true); 
            } else {
                console.log('Checkbox is already checked on load.');
            }

            $checkbox.off('change').on('change', function () {
                console.log('Checkbox state changed. Is checked?', $(this).is(':checked'));
                if ($(this).is(':checked')) {
                    console.log('Checkbox is checked.');
                    $('.wp-block-woocommerce-checkout-shipping-address-block').hide();
                } else {
                    console.log('Checkbox is unchecked.');
                    $('.wp-block-woocommerce-checkout-shipping-address-block').show();
                }
            });
        } else {
            // console.log('Checkbox not found.');
        }
    }

    setTimeout(function () {
        // console.log('Retrying after delay.');
        applyCheckboxLogic();
    }, 1000);

    jQuery(document).on('input', '#custom-price', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    jQuery(document).on('click', '#custom-price', function(e) {
        $(this).removeClass('disable_selection');
        // $(this).prop("disabled", false)
        $('#lifafa').addClass('disable_selection');
        // $("#lifafa").attr("disabled", "disabled");
        this.value = this.value.replace(/[^0-9]/g, ''); 
    });

    // $("#custom-price").on("click", function () {
    // jQuery(document).on('click', '#custom-price', function(e) {
    //     $("#attribute-lifafa").attr("disabled", "disabled");
    // });

    // $("#lifafa").on("click", function () {
    jQuery(document).on('click', '#lifafa', function(e) {
        $(this).removeClass('disable_selection');
        // $(this).prop("disabled", false)
        // $("#custom-price").attr("disabled", "disabled");
        $('#custom-price').addClass('disable_selection');
    });
    // $('.woocommerce-shipping-fields').css('display', 'none');
    setTimeout(() => {
        // $('#ship-to-different-address-checkbox').prop("checked", true).trigger("change"); 
        // $('#ship-to-different-address').css('display', 'none');
        $('.shipping_address').css('display','none');
    }, 210);
});

jQuery(document).ready(function($) {
    // Enable "Place Order" button immediately
    $('#place_order').prop('disabled', false);

    // Hide WooCommerce loader (spinner)
    $(document).on('ajaxStart', function() {
        $('.blockUI, .woocommerce-checkout-review-order .blockUI').hide(); 
    });

    // Ensure "Place Order" remains enabled after AJAX calls
    $(document).on('ajaxStop', function() {
        $('#place_order').prop('disabled', false);
    });
});

jQuery(document).on('elementor/popup/show', function() {
    jQuery('body').addClass('open-modal');
});

// Remove class when popup is closed
jQuery(document).on('elementor/popup/hide', function() {
    jQuery('body').removeClass('open-modal');
});

// Received gift: UPI ID check before transfer
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".accept-gift-transfer").forEach(function (button) {

        button.addEventListener("click", function (event) {

            // Check if user has saved a UPI ID
            let hasUpiId = this.getAttribute("data-user-has-upi") === "true";

            // If UPI ID is missing, show notification
            if (!hasUpiId) {

                event.preventDefault();

                showNotification(
                    "<div class='share-wishlist-address transfer_cash_received'>" +
                    "<strong>Almost there!</strong><br>" +
                    "We need your UPI ID to transfer your funds.<br><br>" +
                    "Please add your UPI ID in My Account " +
                    "<a href='" + myData.homeUrlJs +
                    "my-account' class='save_address_link'>here</a>." +
                    "</div>"
                );

                return;
            }

            // UPI ID exists: continue with transfer AJAX
            const button = jQuery(this);
            const action = button.data('action');
            const giftBox = button.closest('.received-box');
            const itemId = giftBox.data('item-id');
            const orderId = giftBox.data('order-id');

            jQuery('#custom-preloader').show();

            jQuery.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'handle_gift_status',
                    item_id: itemId,
                    order_id: orderId,
                    status: action
                },

                success: function (response) {

                    jQuery('#custom-preloader').hide();

                    if (response.success) {

                        giftBox.data('status', action);

                        giftBox.find('.accept-gift, .reject-gift').remove();

                        giftBox.find('.received-actions').html(
                            `<button type="button" class="custom_button secondary_button" disabled>${response.data.message}</button>`
                        );

                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },

                error: function () {
                    jQuery('#custom-preloader').hide();
                    alert('Something went wrong. Please try again.');
                }
            });

        });

    });

    jQuery(document).on('click', '.share-wishlist.disabled', function(e) {
        ok_button = 1;
		showNotification("<div class='share-wishlist-address'><div class='all-cls-typed-cls-there'>Almost there! Please add your shipping address before sharing your registry link—so your gifts can arrive right at your doorstep! You can update your address <a href='"+myData.homeUrlJs+"my-account' class='save_address_link'>here</a></div></div>");
	});

    // Show notification message

    function showNotification(message, callback) {
        let notification = document.createElement("div");
        notification.className = "custom-notification notificationnew-custom-cls-add";
        // if(ok_button == 1){
        //      notification.innerHTML = `
        //     <div class="notification-content">
        //         <div class="message"><p>${message}</p></div>
        //         <button class="notification-btn">OK</button>
        //     </div>`;
        // }else{
            notification.innerHTML = `
            <div class="notification-content newall-notification-custom-message">
                <button class="close-notification">&times;</button>
                <div class="message"><p>${message}</p></div>
                <button class="notification-btn">OK</button>
            </div>`;
        // }
        document.body.appendChild(notification);
        document.body.classList.add("open-modal");

        jQuery(document).on("click",".notification-btn", function ($) {
            jQuery("body").removeClass("open-modal");
            jQuery(".notification").remove(); 
            jQuery(".custom-notification").hide(); // Close the pop up
            if (typeof callback === "function") {
                callback();
            }
        });

         jQuery(document).on("click", ".close-notification", function () {
        jQuery("body").removeClass("open-modal");
        jQuery(".custom-notification").remove();
    });
    }
    
});

// function showNotificationWithTwoButton(message, onConfirm, onCancel) { 
//     let notification = document.createElement("div");
//     notification.className = "custom-notification";
//     notification.innerHTML = `
//         <div class="notification-content">
//            ${message}
//             <div class="notification-buttons">
//                 <button class="notification-btn confirm-btn">Yes I’m sure</button>
//                 <button class="notification-btn cancel-btn">Go Back</button>
//             </div>
//         </div>
//     `;

//     document.body.appendChild(notification);
//     document.body.classList.add("open-modal");

//     document.querySelector(".confirm-btn").addEventListener("click", function () {
//         document.body.removeChild(notification);
//         document.body.classList.remove("open-modal");
//         if (onConfirm) onConfirm();
//     });

//     document.querySelector(".cancel-btn").addEventListener("click", function () {
//         document.body.removeChild(notification);
//         document.body.classList.remove("open-modal");
//         if (onCancel) onCancel();
//     });
// }

// same as billing

function showNotificationWithTwoButton(message, onConfirm, onCancel) { 
    let notification = document.createElement("div");
    notification.className = "newcustom-notification-modal-behind-cls-adding";
    notification.innerHTML = `
        <div class="notification-content newall-notification-custom-back">
            <p>${message}</p>
            <div class="notification-buttons">
                <button class="notification-btn confirm-btn">Yes I’m sure</button>
                <button class="notification-btn cancel-btn">Go Back</button>
            </div>
        </div>
    `;

    document.body.appendChild(notification);
    document.body.classList.add("open-modal");

    document.querySelector(".confirm-btn").addEventListener("click", function () {
        document.body.removeChild(notification);
        document.body.classList.remove("open-modal");
        if (onConfirm) onConfirm();
    });

    document.querySelector(".cancel-btn").addEventListener("click", function () {
        document.body.removeChild(notification);
        document.body.classList.remove("open-modal");
        if (onCancel) onCancel();
    });
}
 
 
window.addEventListener("load", function() {
    const sameAsBillingCheckbox = document.querySelector("input[name='same_as_billing']");
    if (sameAsBillingCheckbox && sameAsBillingCheckbox.checked) {
        autoFillShippingDetails();
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const sameAsBillingCheckbox = document.querySelector("input[name='same_as_billing']");
    if (sameAsBillingCheckbox) {
        sameAsBillingCheckbox.addEventListener('change', function () {
            if (sameAsBillingCheckbox.checked) {
                autofillShippingAddress();
            } else {
    
                clearShippingAddress();
            }
        });
    }
 
    function autofillShippingAddress() {
        const billingFirstName = document.querySelector("input[name='billing_first_name']").value;
        const billingLastName = document.querySelector("input[name='billing_last_name']").value;
        const billingAddress1 = document.querySelector("input[name='billing_address_1']").value;
        const billingAddress2 = document.querySelector("input[name='billing_address_2']").value;
        const billingCity = document.querySelector("input[name='billing_city']").value;
        const billingPostcode = document.querySelector("input[name='billing_postcode']").value;
        const billingState = document.querySelector("select[name='billing_state']").value;
 
        document.querySelector("input[name='shipping_first_name']").value = billingFirstName;
        document.querySelector("input[name='shipping_last_name']").value = billingLastName;
        document.querySelector("input[name='shipping_address_1']").value = billingAddress1;
        document.querySelector("input[name='shipping_address_2']").value = billingAddress2;
        document.querySelector("input[name='shipping_city']").value = billingCity;
        document.querySelector("input[name='shipping_postcode']").value = billingPostcode;
        document.querySelector("select[name='shipping_state']").value = billingState;
    }
 
    function clearShippingAddress() {
        document.querySelector("input[name='shipping_first_name']").value = '';
        document.querySelector("input[name='shipping_last_name']").value = '';
        document.querySelector("input[name='shipping_address_1']").value = '';
        document.querySelector("input[name='shipping_address_2']").value = '';
        document.querySelector("input[name='shipping_city']").value = '';
        document.querySelector("input[name='shipping_postcode']").value = '';
        document.querySelector("select[name='shipping_state']").value = '';
 
        console.log("Shipping details cleared.");
    }
});

jQuery(document).ready(function($) {
    $('#deliver_to_couple_hidden').val(1);
   // Make sure this is only run once
    $('#deliver_to_couple_hidden').on('change', function () {
        $(this).val(this.checked ? '1' : '0');
    });

    $('#custom-checkout-form').on('submit', function(e) {
        e.preventDefault(); // Always prevent by default
        let error = false;

        // Reset preloader on every submit attempt
        $('#custom-preloader').hide();

        const requiredFields = [
            { id: '#billing_first_name', message: 'Billing first name is required.' },
            { id: '#billing_last_name', message: 'Billing last name is required.' },
            { id: '#billing_email', message: 'Billing email is required.' },
            { id: '#billing_address_1', message: 'Billing address is required.' },
            { id: '#billing_city', message: 'Billing city is required.' },
            { id: '#billing_postcode', message: 'Billing postcode is required.' },
            { id: '#billing_country', message: 'Billing country is required.' },
            { id: '#shipping_address_1', message: 'Shipping address is required.' },
            { id: '#shipping_city', message: 'Shipping city is required.' },
            { id: '#shipping_postcode', message: 'Shipping postcode is required.' },
            // { id: '#shipping_country', message: 'Shipping country is required.' }
        ];

        requiredFields.forEach(function(field) {
            const input = $(field.id);
            if (input.length && !input.val().trim()) {
                error = true;
                toastr.warning(field.message);
            }
        });

        if (!$('#terms').is(':checked')) {
            error = true;
            toastr.warning("You must accept the terms and conditions.");
        }

        if (!error) {
            $('#custom-preloader').show();
            this.submit(); // Only trigger native form submission if no error
        }
    });

});

// jQuery(document).ready(function($) {
//     $('#custom-checkout-form').submit(function(e) {
//         e.preventDefault();
//         if (!$("#custom-checkout-form").valid()) {
//             return;
//         } else {
//             $('#custom-preloader').show();
//             $.ajax({
//                 type: 'POST',
//                 url: ajaxurl,
//                 data: $(this).serialize() + '&action=process_custom_checkout',
//                 success: function(response) {
//                     if (response.success) {
//                         window.location.href = response.data.redirect;
//                     } else {
//                         alert(response.data.message || 'Error processing checkout.');
//                         window.location.href = '/checkout/order-received/';
//                     }
//                 },
//                 error: function() {
//                     alert('An unexpected error occurred.');
//                     window.location.href = '/checkout/order-received/';
//                 }
//             });
//         }
//     });
// });



// jQuery(document).ready(function($) {
//     $('#custom-checkout-form').submit(function(e) {
//         e.preventDefault();

//         $.ajax({
//             type: 'POST',
//             url: wc_checkout_params.ajax_url,
//             data: $(this).serialize() + '&action=process_checkout',
//             dataType: 'json',
//             beforeSend: function() {
//                 $('#checkout-response').html('<p>Processing...</p>');
//             },
//             success: function(response) {
//                 if (response.success) {
//                     window.location.href = response.redirect;
//                 } else {
//                     $('#checkout-response').html('<p style="color: red;">' + response.error + '</p>');
//                 }
//             }
//         });
//     });
// });


// Search functionality in my account page in address (1)

// jQuery(document).ready(function($) {
//     var autocompleteService = new google.maps.places.AutocompleteService();
//     var inputField = $('#address-input');
//     var suggestionsList = $('#address-suggestions');
//     inputField.on('input', function () {
//         var query = $(this).val();
//         if (!query) {
//             suggestionsList.empty().hide();
//             return;
//         }
//         // Fetch autocomplete suggestions
//         autocompleteService.getPlacePredictions({ input: query }, function (predictions, status) {
//             if (status === google.maps.places.PlacesServiceStatus.OK) {
//                 suggestionsList.empty().show();
//                 predictions.forEach(function (prediction) {
//                     suggestionsList.append('<li style="padding: 5px; cursor: pointer;" data-place-id="' + prediction.place_id + '">' + prediction.description + '</li>');
//                 });
//             }
//         });
//     });

//     // Handle suggestion click
//     suggestionsList.on('click', 'li', function () {
//         var selectedAddress = $(this).text();
//         inputField.val(selectedAddress);
//         suggestionsList.empty().hide();
//         var data = {
//             action: 'save_selected_address',
//             address: selectedAddress
//         };

//         $.post(ajax_obj.ajaxurl, data, function (response) {
//             console.log('Address saved successfully:', response);
//         });
//     });
// });

jQuery(document).ready(function ($) {
    jQuery(document).on('click', '.remove_product', function () {
    var product_id = jQuery(this).data('product_id');
    let productBox = jQuery(this).closest('.presonalised-item');

    showNotificationWithTwoButton1(
        "Are you sure you want to remove this item?",
        function () {
            // ✅ User clicked OK - Proceed with removal
            jQuery('#custom-preloader').show();

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    product_id1: product_id,
                    action: 'remove_from_personalized',
                },
                success: function (response) {
                    jQuery('#custom-preloader').hide();
                    console.log(response);

                    // ✅ Show message in popup (same as Amazon version)
                    const popup = document.getElementById('add-to-cart-success-popup');
                    if (popup) {
                        const messageEl = popup.querySelector('.popup-message');
                        messageEl.textContent = response.data?.message || 'Product removed successfully.';
                        popup.style.display = 'flex';
                    }
                    // ✅ Instantly remove product box (no refresh)
                    productBox.slideUp(700, function () {
                        jQuery(this).remove();
						if (jQuery('.presonalised-item').length === 0) {
                                // You can show "Your list is empty" message here if needed
                                console.log('All products removed');
                            }
                    });
                },
                error: function (error) {
                    jQuery('#custom-preloader').hide();
                    console.log(error);
                    toastr.warning('Something went wrong. Please try again.');
                }
            });
        },
        function () {
            // ❌ User clicked Cancel
            console.log("Product removal cancelled.");
        }
    );
});


// Woocommerce product remove code

     jQuery(document).on('click', '.remove_amazon_product', function (e) {
    e.preventDefault();
    e.stopPropagation();
        var product_id = jQuery(this).data('product_id');
    let productBox = jQuery(this).closest('.presonalised-item');

        showNotificationWithTwoButton1(
        "Are you sure you want to remove this item?",
        function () {
            // User clicked OK - Proceed with removal
            jQuery('#custom-preloader').show();

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    product_id1: product_id,
                    action: 'remove_from_amazon_personalized',
                },
                success: function (response) {
                    jQuery('#custom-preloader').hide();
                    console.log(response);

                    const popup = document.getElementById('add-to-cart-success-popup');

                    if (popup) {
                        const messageEl = popup.querySelector('.popup-message');
                        messageEl.textContent = response.data.message;

                        // Show popup
						setTimeout(() => {
							popup.style.display = 'flex';
						}, 50);
                    }

                    // ✅ Immediately remove product box from page (no refresh needed)
                  productBox.slideUp(700, function () {
                        jQuery(this).remove();
                    });
                },
                error: function (error) {
                    jQuery('#custom-preloader').hide();
                    console.log(error);
                    // toastr.warning('Something went wrong. Please try again.');
                }
            });
        },
        function () {
            // User clicked Cancel
            console.log("Product removal cancelled.");
        }
    );
});

jQuery(document).on('click', '#add-to-cart-success-popup .close-popup', function (e) {
    console.log('close-popup clicked in add-to-cart-success-popup');
    e.preventDefault();
    e.stopPropagation();
    setTimeout(function() {
        location.reload();
    }, 50);
    return false;
});
jQuery(document).on('click', '#add-to-cart-success-popup', function (e) {
    if (e.target === this) {
        location.reload();
    }
});
jQuery(document).on('click', '#add-to-cart-success-popup .popup-content', function (e) {
    e.stopPropagation();
});
    // Custom notification popup function
    function showNotificationWithTwoButton1(message, onConfirm, onCancel) { 
        let notification = document.createElement("div");
        notification.className = "custom-notification";
        notification.innerHTML = `
            <div class="notification-content newall-notification-custom-cancel">
                <p>${message}</p>
                <div class="notification-buttons">
                    <button type="button" class="notification-btn confirm-btn">Remove</button>
                    <button class="notification-btn cancel-btn">Cancel</button>
                </div>
            </div>
        `;

        document.body.appendChild(notification);
        document.body.classList.add("open-modal");

        document.querySelector(".confirm-btn").addEventListener("click", function (e) {
            document.body.removeChild(notification);
            document.body.classList.remove("open-modal");
            if (onConfirm) onConfirm(); // Execute confirm function
        });

        document.querySelector(".cancel-btn").addEventListener("click", function () {
            document.body.removeChild(notification);
            document.body.classList.remove("open-modal");
            if (onCancel) onCancel(); // Execute cancel function
        });
    }
});

// jQuery(document).ready(function ($) {
//     $('.remove_product').on('click', function () {
//         var product_id = $(this).data('product_id');
//         let productBox = $(this).closest('.presonalised-item');
//         $('#custom-preloader').show();
//         $.ajax({
//             url: ajaxurl,
//             type: 'POST',
//             data: {
//                 product_id1: product_id,
//                 action: 'remove_from_personalized',
//             },
//             beforeSend: function () {
//                 console.log('Removing product...');
//             },
//             success: function (response) {
//                 $('#custom-preloader').hide();
//                 if (response.success) {
//                     toastr.success('Product removed from your Wishlist.');
//                     productBox.fadeOut(300, function () {
//                         $(this).remove();
//                     });
//                 } else {
//                     alert(response.message);
//                 }
//             },
//             error: function () {
//                 toastr.warning('Something went wrong. Please try again.');
//             }
//         });
//     });
// });


// jQuery(document).ready(function ($) {
//     $('#category-filter').change(function () {
//         let category = $(this).val();
//         let user_id = $('.user_id').val(); 

//         $.ajax({
//             url: ajaxurl,
//             type: "POST",
//             data: {
//                 action: "filter_wishlist_by_category",
//                 user_id: user_id,
//                 category: category
//             },
//             beforeSend: function () {
//                 $('#wishlist-products').html('<p>Loading...</p>'); // Show loading text
//             },
//             success: function (response) {
//                 $('#wishlist-products').html(response); // Update wishlist
//             }
//         });
//     });
// });

jQuery(document).ready(function ($) {
    function fetchFilteredAndSortedProducts() {
        var sortValue = $('#sort-options').val();
        var selectedCategory = $('#category-filter').val(); // Assuming a category dropdown exists
        var user_id = $('.user_id').val();
        var received_gift = $('.received_gift').val();
        var urlParams = new URLSearchParams(window.location.search);
        var gift_for = urlParams.get('original_user_id');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'filter_and_sort_wishlist',
                sort_order: sortValue,
                category: selectedCategory, // Pass category in AJAX
                user_id: user_id,
                received_gift: received_gift,
                gift_for: gift_for
            },
            success: function (response) {
                $('.product-boxes').html(response);
                $('#custom-preloader').hide();
            }
        });
    }
    const currentPage = window.location.pathname;
    if (localStorage.getItem('filter-page') !== currentPage) {
        localStorage.removeItem('category-filter');
        localStorage.removeItem('sort-options');
        localStorage.setItem('filter-page', currentPage);
    }
    if (localStorage.getItem('category-filter')) {
        $('#category-filter').val(localStorage.getItem('category-filter'));
    }

    if (localStorage.getItem('sort-options')) {
        $('#sort-options').val(localStorage.getItem('sort-options'));
    }
    if (localStorage.getItem('category-filter') || localStorage.getItem('sort-options')) {
        fetchFilteredAndSortedProducts();
    }
    $(document).on('change', '#category-filter', function () {
        $('#custom-preloader').show();
        var selectedCategory = $(this).val();
        localStorage.setItem('category-filter', selectedCategory);
        localStorage.setItem('filter-page', currentPage);
        fetchFilteredAndSortedProducts();
    });
    $(document).on('change', '#sort-options', function () {
        $('#custom-preloader').show();
        var sortValue = $(this).val();
        localStorage.setItem('sort-options', sortValue);
        localStorage.setItem('filter-page', currentPage);
        fetchFilteredAndSortedProducts();
    });
    $(document).on('click', '.clear_filter', function () {
        $('#category-filter').val('');
        $('#sort-options').val('');
        localStorage.removeItem('category-filter');
        localStorage.removeItem('sort-options');
        fetchFilteredAndSortedProducts();
    });

   const observer = new MutationObserver(() => {
    const selectors = document.querySelectorAll('.wc-block-components-quantity-selector');

    selectors.forEach(selector => {
        const minus = selector.querySelector('.wc-block-components-quantity-selector__button--minus');
        const plus = selector.querySelector('.wc-block-components-quantity-selector__button--plus');
        const qty_input = selector.querySelector('.wc-block-components-quantity-selector__input');

        if (minus && plus && qty_input) {
            minus.setAttribute('disabled', true);
            plus.setAttribute('disabled', true);
            qty_input.setAttribute('disabled', true);
        }
    });

    // Optionally disconnect if you're sure all elements are loaded
    if (selectors.length > 0) {
        observer.disconnect();
    }
});

// Start observing the entire document or a specific container
observer.observe(document.body, {
    childList: true,
    subtree: true
});

    
    observer.observe(document.body, { childList: true, subtree: true });
    
});

// jQuery(document).ready(function ($) {
    
// });


jQuery(document).on('input', '.custom_amount', function($) {
    if (parseFloat(jQuery(this).val()) < 1) {
        jQuery(this).val(1);
    }
})

// add to cart in wishlist page
jQuery(document).ready(function($) {
    jQuery(document).on('click', '.add_to_cart_from_wishlist', function(e) {
        $(this).attr('disabled', 'disabled');
        e.preventDefault();
        var unique_key = $(this).data('key');
        var custom_amount = $('.custom_amount_'+ unique_key).val();
        // alert(custom_amount);
       const popup = document.getElementById('add-to-cart-success-popup');
        const messageEl = popup ? popup.querySelector('.popup-message') : null;

        // ✅ Show popup message for missing amount
        if ($(this).hasClass('is_cash_product') && custom_amount == '') {
            if (popup && messageEl) {
                messageEl.textContent = 'Please enter the Lifafa amount.';
                $(popup).fadeIn(200).css('display', 'flex');
            }
            $(this).removeAttr('disabled');
            return false;
        }
        $('#custom-preloader').show();
        var product_id = $(this).val();
        var attributes = $('#hidden_attributes_' + unique_key).val();
        var quantity = $('#hidden_quantity_' + unique_key).val();
        var gift_for = new URLSearchParams(window.location.search).get('original_user_id');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'add_to_cart_with_variations',
                product_id: product_id,
                attributes: attributes,
                quantity: quantity,
                gift_for: gift_for,
                custom_amount:custom_amount
            },
            // success: function(response) {
            //     if (response.success) {
            //         toastr.success('Product Added to Cart.');
            //         location.reload();
            //     } else {
            //        toastr.warning(response.data.message);
            //     }
            // }
           success: function(response) {
                $('#custom-preloader').hide();

                if (response.success) {

                    // ✅ Refresh WooCommerce cart fragments
                    if (typeof wc_cart_fragments_params !== 'undefined') {
                        $(document.body).trigger('wc_fragment_refresh');
                    }

                    // ✅ Open Elementor side cart
                    setTimeout(function () {
                        const cartToggle = document.getElementById('elementor-menu-cart__toggle_button');
                        if (cartToggle) {
                            cartToggle.click();
                        }
                    }, 300);

                } else {
                    // Optional: show error popup or alert
                    const popup = document.getElementById('add-to-cart-success-popup');
                    if (popup) {
                        const messageEl = popup.querySelector('.popup-message');
                        if (messageEl) {
                            messageEl.textContent = 'You can gift to only one couple at a time.';
                        }
                        $(popup).fadeIn(200).css('display', 'flex');
                    }
                }
            }


        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('#add-to-cart-success-popup .close-popup')) {
            console.log('close-popup clicked via document listener');
            e.preventDefault();
            e.stopPropagation();
            setTimeout(function() {
                location.reload();
            }, 50);
        }
            
    });

});

jQuery(document).ready(function ($) {
    $(document).on('submit', '.wpcf7-form', function () {
        $('#custom-preloader').show();
    });

    document.addEventListener('wpcf7mailsent', function () {
        $('#custom-preloader').hide();
    });

    document.addEventListener('wpcf7invalid', function () {
        $('#custom-preloader').hide();
    });

    document.addEventListener('wpcf7spam', function () {
        $('#custom-preloader').hide();
    });

    document.addEventListener('wpcf7failed', function () {
        $('#custom-preloader').hide();
    });
});


jQuery(document).on('click', '.icon-minus-circle, .icon-plus-circle', function($) {
    var $qty = jQuery('.qty');
    var currentVal = parseFloat($qty.val());
    // alert(currentVal);
    var max = parseFloat($qty.attr('max'));
    var min = parseFloat($qty.attr('min'));
    var step = parseFloat($qty.attr('step'));
    if (jQuery(this).is('.icon-plus-circle')) {
        if (max && (max <= currentVal)) {
            $qty.val(max);
        } else {
            $qty.val(currentVal + step);
        }
    } else {
        if (min && (min >= currentVal)) {
            $qty.val(min);
        } else if (currentVal > 1) {
            $qty.val(currentVal - step);
        }
    }
    $qty.trigger('change');
});

jQuery(function($) {
	// When a variation is selected
	$('form.variations_form').on('show_variation', function(event, variation) {
		if (variation.image && variation.image.full_src) {
			// Replace main image
			const newImage = variation.image.full_src;
			$('.products-slider .swiper-slide.variation-main-slide img').attr('src', newImage);

			// Optional: Replace thumbnail too (if needed)
			$('.products-thumbnail .swiper-slide.variation-thumb-slide img').attr('src', newImage);
		}
	});
});

jQuery(document).ready(function($) {

    $('.elementor-menu-cart__close-button').on('click', function(){
        setTimeout(() => {
            $('body').removeClass('scroll-hide').css('overflow', '');
        }, 500);
    });

    $('body').on('click', '.qty-plus', function() {
        var $input = $(this).siblings('.personalized-product-qty');
        var currentVal = parseInt($input.val()) || 1;
        $input.val(currentVal + 1).trigger('change');
    });

    $('body').on('click', '.qty-minus', function() {
        var $input = $(this).siblings('.personalized-product-qty');
        var currentVal = parseInt($input.val()) || 1;
        if (currentVal > 1) {
            $input.val(currentVal - 1).trigger('change');
        }
    });

    // AJAX update when quantity changes
    // $('body').on('change', '.personalized-product-qty', function() {
    //     var $input = $(this);
    //     var product_id = $input.data('product_id');
    //     var variation_id = $input.data('variation_id');
    //     var qty = $input.val();
    //     var attributes = $input.data('attributes'); // JSON object

    //     var data = {
    //         action: 'update_personalized_product_quantity',
    //         product_id: product_id,
    //         variation_id: variation_id,
    //         qty: qty,
    //         attributes: attributes,
    //         // security: personalized_product_nonce.security
    //     };

    //     $.ajax({
    //         url: ajaxurl,
    //         method: 'POST',
    //         data: data,
    //         success: function(response) {
    //             if (response.success) {
    //                 console.log(response.data.message);
    //             } else {
    //                 console.error(response.data.message);
    //             }
    //         },
    //         error: function() {
    //             console.error('AJAX error');
    //         }
    //     });
    // });


    // $('body').on('click', '.update_wishlist_qty', function () {
    //     var $btn = $(this);
    //     var $input = $btn.closest('.wishlist-item').find('.personalized-product-qty');
    
    //     var product_id = $input.data('product_id');
    //     var variation_id = $input.data('variation_id');
    //     var qty = $input.val();
    //     var attributes = $input.data('attributes'); // JSON object
    
    //     var data = {
    //         action: 'update_personalized_product_quantity',
    //         product_id: product_id,
    //         variation_id: variation_id,
    //         qty: qty,
    //         attributes: attributes
    //     };
    
    //     $.ajax({
    //         url: ajaxurl,
    //         method: 'POST',
    //         data: data,
    //         success: function(response) {
    //             if (response.success) {
    //                 console.log(response.data.message);
    //             } else {
    //                 console.error(response.data.message);
    //             }
    //         },
    //         error: function() {
    //             console.error('AJAX error');
    //         }
    //     });
    // });

    $('body').on('click', '.update_wishlist_qty', function () {
    var $btn = $(this);
    var qty_attr = $(this).attr('id');
    var $input = $btn.closest('.product-quantity').find('.personalized-product-qty');

    var product_id = $input.data('product_id');
    var variation_id = $input.data('variation_id');
    var qty = $input.val();
    var attributes = $input.data('attributes'); // likely a JS object

    var data = {
        action: 'update_personalized_product_quantity',
        product_id: product_id,
        variation_id: variation_id,
        qty: qty,
        attributes: JSON.stringify(attributes) // important!
    };
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: data,
        success: function (response) {
            if (response.success) {
                console.log(qty_attr);
                $('.'+qty_attr).text(qty);
                console.log(response.data.message);
            } else {
                console.error(response.data.message);
            }
        },
        error: function () {
            console.error('AJAX error');
        }
    });
});

    
});

document.addEventListener('DOMContentLoaded', function() {
   
    const tabs = document.querySelectorAll('.tab-link');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {

            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));

            this.classList.add('active');
            const target = document.getElementById(this.getAttribute('data-tab'));
            target.classList.add('active');

            console.log("Active tab:", this.getAttribute('data-tab'));
        });
    });
});


function toggleRupeeSymbol(input) {
  const rupeeSymbol = input.parentElement.querySelector('.rupee-symbol');
  if (input.value.trim() !== '') {
    rupeeSymbol.style.opacity = 1;
  } else {
    rupeeSymbol.style.opacity = 0;
  }
}

// Add gift button popup open

jQuery(document).ready(function ($) {
    // On click of add-product-registry
    $(document).on('click', '.add-product-registry', function (e) {
        e.preventDefault();
        
      

        // Open Elementor popup with ID 9979
        if (typeof elementorProFrontend !== 'undefined' && elementorProFrontend.modules.popup) {
            elementorProFrontend.modules.popup.showPopup({ id: 9979 });
        } else {
            console.warn('Elementor Pro popup module not loaded.');
        }
    });
});



// Pop up for success message of amazon product add

jQuery(function($){
  // make global so other scripts / console can call it
  window.amazonSuccessPopup = function(message, callback) {
    // set message
    $('.popup-message').text(message);

    // show popup
    $('.success-popup').fadeIn();

    // remove prior handlers so repeated calls don't attach duplicates
    $('.close-popup, .success-popup').off('click.amazonPopup').on('click.amazonPopup', function(e) {
        // if ($(e.target).is('.close-popup') || $(e.target).is('.success-popup')) {
          // ✅ CHANGED — use .closest() so clicks on SVG/path inside button also count
        if ($(e.target).closest('.close-popup').length || $(e.target).is('.success-popup')) {

        $('.success-popup').fadeOut();
        if (typeof callback === 'function') callback();
        // unbind to avoid memory leak
        $('.close-popup, .success-popup').off('click.amazonPopup');
      }
    });

        // view registry button
      $('.popup-btn').off('click.amazonPopupBtn').on('click.amazonPopupBtn', function () {
          $('.success-popup').fadeOut();  
          $('#custom-preloader').show();
              window.location.href = MyAjax.wishlistUrl;
        });
        // Hide loader on page load (after refresh)
        $(window).on('load', function() {
            $('#custom-preloader').hide();

        });
      
  };
});

// Pop up for Error messasge when failed to add product

jQuery(function($){
  // make global so other scripts / console can call it
  window.amazonFailPopup = function(message, callback) {
    // set message
     $('.amazon-fail-popup .popup-message').text(message);

    // show popup
    $('.amazon-fail-popup').fadeIn();

    // remove prior handlers so repeated calls don't attach duplicates
    $('.amazon-fail-popup .close-popup, .amazon-fail-popup').off('click.amazonFailPopup').on('click.amazonFailPopup', function(e) {
      if ($(e.target).closest('.close-popup').length || $(e.target).is('.amazon-fail-popup')) {
        $('.amazon-fail-popup').fadeOut();
        if (typeof callback === 'function') callback();
        // unbind to avoid memory leak
        $('.amazon-fail-popup .close-popup, .amazon-fail-popup').off('click.amazonFailPopup');
      }
    });

    // "Try Again" button behavior
    // $('.amazon-fail-popup .popup-btn').off('click.amazonFailPopupBtn').on('click.amazonFailPopupBtn', function () {
    //     $('.amazon-fail-popup').fadeOut();
    //     $('#custom-preloader').show();
    //     if (typeof callback === 'function') callback();
    // });
      
      
      // "Try Again" button behavior
$('.amazon-fail-popup .popup-btn').off('click.amazonFailPopupBtn').on('click.amazonFailPopupBtn', function () {
    // Hide fail popup
    $('.amazon-fail-popup').fadeOut();

    // Show loader
    $('#custom-preloader').show();

    // Reopen the Add Amazon Product popup after small delay
    setTimeout(function () {
        $('#custom-preloader').hide();

        // 👉 Trigger your Add Amazon Product popup (Elementor or custom)
        // If using Elementor popup:
        // if (typeof elementorPro !== 'undefined' && elementorProFrontend.modules.popup) {
            if (typeof elementorProFrontend !== 'undefined' && elementorProFrontend.modules && elementorProFrontend.modules.popup) {
            elementorProFrontend.modules.popup.showPopup({ id: 9979 }); // numeric ID — safer
        } else {
            // fallback if Elementor popup not available
            $('.add_amazon_product').fadeIn();
        }
        // }
        // If using a custom popup:
        // else {
            $('.add_amazon_product').fadeIn();
        // }

    }, 600); // delay for smoother transition
});


    // Hide loader on page load (after refresh)
    $(window).on('load', function() {
        $('#custom-preloader').hide();
    });
  };
});


jQuery(document).ready(function ($) {

	// --- Dedicated popup for product exists errors ---
	window.amazonExistsPopup = function (message, callback) {
		const $popup = $('.amazon-exists-popup');

		// Insert error message
		$popup.find('.popup-message').html(message);

		// Show popup
		$popup.fadeIn(200).addClass('active');

		// Close actions
		$popup.find('.close-popup, .popup-btn, .popup-note').off('click').on('click', function (e) {
			e.preventDefault();
			$popup.fadeOut(200).removeClass('active');
			if (typeof callback === 'function') callback();
		});
	};

});



jQuery(document).ready(function ($) {

  // When Elementor popup opens
  $(document).on('elementor/popup/show', (event, id, instance) => {
    if (id === 9979) { // your popup ID
      const $input = $('#amazon_product_url');
      const $button = $('#fetch_amazon_url');

      // Reset button state
      $button.prop('disabled', true).removeClass('active');

      // Listen for input changes
      $input.on('input', function () {
        const value = $.trim($input.val());

        if (value.length > 0) {
          $button.prop('disabled', false).addClass('active');
        } else {
          $button.prop('disabled', true).removeClass('active');
        }
      });
    }
  });

});


jQuery(document).ready(function ($) {
        
    // Add url to fetch  auto Amazon product details
    
        // $('.add_amazon_product').on('click', function () {
        //     // Open Elementor Popup by ID or slug
        //     elementorProFrontend.modules.popup.showPopup({ id: 8346 });
        // });

        $(document).on('click', '#fetch_amazon_url', function () {
            let url = $('#amazon_product_url').val();
           
            let $input = $('#amazon_product_url');
            $input.next('.validation-error').remove();

                if (!url) {
                    // Url validation
                    $input.after('<div class="validation-error" style="color:red; font-size:12px; margin-top:5px;">Please enter Amazon URL</div>');
                    return;
                }
                // Close the Elementor popup
                $('#elementor-popup-modal-9979 .eicon-close').trigger('click');
                // Show loader
                $('#custom-preloader').show();

                
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fetch_amazon_product',
                    amazon_url: url,
                    amazon_category: $('#amazon_category').val(), // assuming you have a select or input for category,
                  
                },
                // success: function (response) {

                //       // Hide loader
                //     $('#custom-preloader').hide();

                //      if (response.success) {
                
                //         // Show custom notification
                //         amazonSuccessPopup(response.data, function () {
                //             // Reload page after clicking OK
                //             $('#custom-preloader').show();
                //             location.reload();
                //         });
                //     } else {
                //         // Show error message in notification
                //         amazonFailPopup(response.data, function () {
                //             // Reload page after clicking OK
                //             $('#custom-preloader').show();
                //             location.reload();
                //         });
                //     }
                // },
                success: function (response) {
                    $('#custom-preloader').hide();

                    if (response.success) {
                        amazonSuccessPopup(response.data, function () {
                            $('#custom-preloader').show();
                            location.reload();
                        });
                    } else {
                        // Handle specific “already exists” type errors separately
                        if (
                            response.data.includes('already exists') ||
                            response.data.includes('available in the product listing')
                        ) {
                            amazonExistsPopup(response.data);
                        } else {
                            // All other generic errors
                            amazonFailPopup(response.data, function () {
                                $('#custom-preloader').show();
                                location.reload();
                            });
                        }
                    }
                },

                // error: function () {
                //     showNotification('AJAX error occurred.');
                // }
               error: function (xhr, status, error) {
                    amazonFailPopup('We were unable to link to the Amazon URL provided. Make sure to copy the entire url from the product detail page and try again or you can also add all the details manually', function () {
                        // This runs after the popup is closed (✕ or outside click)
                        $('#custom-preloader').show();
                        location.reload();
                    });
                }


            });
        });

        //  Ensure loader hides only after page is fully reloaded
        $(window).on('load', function () {
            $('#custom-preloader').hide();
        });
     
     
    // Manual Amazon product Save
     
        $(document).on('click', '#manual_amazon_create', function () {
            // $('#manual_fields').slideDown();
           // Close popup with ID 8346 (if it's open)
            $('#elementor-popup-modal-9979 .eicon-close').trigger('click');
            // Open the manual product popup (ID 8877)
            elementorProFrontend.modules.popup.showPopup({ id: '8877'  });
        });

        // Remove any existing validation messages when typing
        $(document).on('input', '#manual_product_name, #manual_product_price, #manual_product_share_url', function () {
            $(this).next('.validation-error').remove();
        });
    
        let imageIsValid = true; // global flag

        // Image validation - run when image changes
    $(document).on('change', '#manual_product_image', function () {
            
            let file = this.files[0];
            let errorContainer = $(this).closest('.form-group');
            let uploadBox = $(this).closest('.right-upload').find('.upload-box');
            
            errorContainer.find('.validation-error').remove(); // remove old error
            imageIsValid = true; // reset

            if (file) {
                let fileType = file.type.toLowerCase();
                let validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']; // allowed

                if (!validTypes.includes(fileType)) {
                    errorContainer.append('<div class="validation-error" style="color:red; font-size:12px; margin-top:5px;">Only JPG, JPEG, PNG, or WEBP images are allowed. GIF and PDF are not supported.</div>');
                    $(this).val(''); // reset file input
                    $('#file_name_display').text('');
                    uploadBox.show(); // Show upload box again
                    $('#grid-top-all-cls-adding_container').remove(); // Remove preview if exists
                    imageIsValid = false;
                } else {
                    // Create image preview
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        // Remove old preview if exists
                        $('#grid-top-all-cls-adding_container').remove();
                        
                        // Create preview HTML
                        let previewHTML = `
                            <div id="grid-top-all-cls-adding_container" class="image-display-manual" style="position: relative;">
                                <div class="upload-icon">
                                    <img src="${e.target.result}" alt="Preview">
                                    <button type="button" class="remove-image-btn" style="position: absolute; top: 7px; right: 5px; background: #f779ad; color: white; border: none; width: 28px; height: 31px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;">×</button>
                                </div>
                            </div>
                        `;
                        
                        // Insert preview after upload box
                        uploadBox.after(previewHTML);
                        
                        // Hide upload box
                        uploadBox.hide();
                    };
                    reader.readAsDataURL(file);
                    
                    $('#file_name_display').text(file.name);
                    imageIsValid = true;
                }
            } else {
                // No file selected (optional reset)
                $('#file_name_display').text('');
                uploadBox.show();
                $('#grid-top-all-cls-adding_container').remove();
            }
        });
        
        // Handle remove image button
        $(document).on('click', '.remove-image-btn', function (e) {
            e.preventDefault();
            $('#manual_product_image').val(''); // Clear file input
            $('#grid-top-all-cls-adding_container').remove(); // Remove preview
            $('.upload-box').show(); // Show upload box again
            $('#file_name_display').text('');
            imageIsValid = false;
        });
     
        $(document).on('click', '#save_manual_product', function (e) {
            e.preventDefault();

            let isValid = true;

            // Clear previous errors
            $('#manual_product_name, #manual_product_price, #manual_product_desc, #manual_product_share_url').next('.validation-error').remove();

            // Validate each field
            if (!$('#manual_product_name').val()) {
                $('#manual_product_name').after('<div class="validation-error" style="color:red; font-size:12px; margin-top:5px;">Please enter product name</div>');
                isValid = false;
            }
        
            let price = $('#manual_product_price').val().trim();
            if (!price) {
                $('#manual_product_price').after('<div class="validation-error" style="color:red; font-size:12px; margin-top:5px;">Please enter product price</div>');
                isValid = false;
            } else if (!/^\d+(\.\d{1,2})?$/.test(price)) {
                $('#manual_product_price').after('<div class="validation-error" style="color:red; font-size:12px; margin-top:5px;">Price must be a valid number</div>');
                isValid = false;
            }

            if (!$('#manual_product_share_url').val()) {
                $('#manual_product_share_url').after('<div class="validation-error" style="color:red; font-size:12px; margin-top:5px;">Please enter Amazon URL</div>');
                isValid = false;
            }

            //  Stop if image invalid
            if (!imageIsValid) {
                // Remove previous error if any
                $('.custom_file_label').next('.validation-error').remove();

                // Add the error once
                $('.custom_file_label').after('<div class="validation-error" style="color:red; font-size:12px; margin-top:5px;">Please upload a valid image before submitting.</div>');
                isValid = false;
            }


            // Stop if validation fails
            if (!isValid) return;


            // Prepare form data
            let formData = new FormData();
            formData.append('action', 'manual_amazon_product');
            formData.append('name', $('#manual_product_name').val());
            formData.append('amazon_price', $('#manual_product_price').val());
            // formData.append('desc', $('#manual_product_desc').val());
            formData.append('amazon_url', $('#manual_product_share_url').val());

             let image = $('#manual_product_image')[0].files[0];
                if (image) {
                    formData.append('amazon_image', image);
                } else {
                    // Append default image URL if no file is chosen
                    formData.append('amazon_image_url', homeUrl + '/wp-content/uploads/2025/04/Main-Logo@4x.png');
                }

                // Close the Elementor popup
                $('#elementor-popup-modal-8877 .eicon-close').trigger('click');
                // Show loader
                $('#custom-preloader').show();

            // AJAX request
            $.ajax({
                url: ajaxurl, // or my_ajax.ajaxurl if frontend
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {

                    // hide loader
                $('#custom-preloader').hide();

                    // let message = '';

                    // if (typeof response.data === 'string') {
                    //     message = response.data;
                    // } else if (typeof response.data === 'object' && response.data.message) {
                    //     message = response.data.message;
                    // } else {
                    //     message = 'Action completed successfully!';
                    // }

                    // if (response.success) {
                    //     // Show notification
                    //     amazonSuccessPopup(message, function () {
                    //         $('#custom-preloader').show();
                    //         location.reload();
                    //     });
                    // } else {
                    //      // Show error message in notification
                    //     amazonFailPopup(response.data, function () {
                    //         // Reload page after clicking OK
                    //         $('#custom-preloader').show();
                    //         location.reload();
                    //     });
                    //     // Show error message
                    //     // showNotification(message);
                    // }

                     if (response.success) {

                amazonSuccessPopup(response.data.message, function () {
                    location.reload();
                });

            } else {

                // 🔥 IMPORTANT: DO NOT RELOAD PAGE ON ERROR (this was causing duplicate issues)
                amazonFailPopup(response.data, function () {
                    // Do NOT reload here
                });
            }
                }
            });
        });
     
         //  Ensure loader hides only after page is fully reloaded
        $(window).on('load', function () {
            $('#custom-preloader').hide();
        });
    });

   

    // Mark as purchased 
   
    jQuery(document).ready(function ($) {

        // Open Elementor Popup
        $('.mark-purchased-btn').on('click', function () {
            let productId = $(this).data('product-id'); 
            elementorProFrontend.modules.popup.showPopup({ id: 8370 });
            $('#purchased-popup').find('input[name="product_id"]').val(productId);
        });


        jQuery(document).on('submit', '#purchasedForm', function (e) {
            // alert('helllo');
        e.preventDefault();

            let $form = $(this);
            let isValid = true;

            // Remove previous validation errors
            $form.find('.validation-error').remove();

            // Validate required fields
            $form.find('input[name="first_name"], input[name="last_name"]').each(function () {
                if (!$(this).val().trim()) {
                    $(this).after('<div class="validation-error" style="color:red; font-size:12px; margin-top:5px;">This field is required</div>');
                    isValid = false;
                }
            });

            // Stop if validation fails
            if (!isValid) return;
            
             // Get original_user_id from URL
            const urlParams = new URLSearchParams(window.location.search);
            const originalUserId = urlParams.get('original_user_id');



              // Close the Elementor popup
            $('#elementor-popup-modal-8370 .eicon-close').trigger('click');
            // Show preloader before AJAX
            $('#custom-preloader').show();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_purchased_form',
                    original_user_id: originalUserId,
                    form_data: $(this).serialize()
                },
                success: function (response) {
                    // Hide preloader after AJAX completes
                    $('#custom-preloader').hide();
                                
                    // Ensure message is a string
                        let message = '';
                    
                        if (typeof response.data === 'string') {
                            message = response.data;
                        } else if (typeof response.data === 'object' && response.data.message) {
                            message = response.data.message;
                        } else {
                            message = 'Thank you for sharing the details';
                        }

                        if (response.success) {
                            // Close Elementor popup
                            $('#elementor-popup-modal-8370 .eicon-close').trigger('click');

                            // Wait a bit for closing animation
                            setTimeout(function () {
                                // Show new custom popup
                                $('#purchasedSuccessPopup').addClass('active');
                            }, 400);

                        } else {
                            showNotification(message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log('Status:', status);
                        console.log('Error:', error);
                        console.log('Response:', xhr.responseText); // raw server response
                        showNotification('An error occurred while processing your request.');
                    }
                
            });
        });


        const $popup = $('.purchased-success-popup');
        
        // Close actions after reload 
        $popup.find('.close-popup').off('click').on('click', function (e) {
            e.preventDefault();

            $popup.fadeOut(200).removeClass('active');

            // 🔄 Reload page after closing
            setTimeout(() => {
                location.reload();
            }, 200);

            if (typeof callback === 'function') callback();
        });


         // close popup
        $(document).on('click', '.purchased-success-popup .popup-btn, .purchased-success-popup .close-popup, .purchased-success-popup .popup-overlay', function () {
            $('#purchasedSuccessPopup').removeClass('active');
            // Show your custom preloader
            $('#custom-preloader').show();
            setTimeout(() => location.reload(), 300);
        });

        
    // Buy for amazon and open popup

    // Remove validation message when user starts typing
        $(document).on('input', '#purchasedForm input, #purchasedForm textarea', function () {
            $(this).next('.validation-error').remove();
        });

        $(document).on('change', '#manual_product_image', function () {
        let fileName = this.files.length ? this.files[0].name : '';
        $('#file_name_display').text(fileName);
        });

       // Buy from Amazon button click
        $(document).on('click', '.buy-amazon', function () {
            window.selectedAmazonUrl = $(this).data('amazon-url');
            elementorProFrontend.modules.popup.showPopup({ id: 8588 });
        });
         $(document).on('click', '.details-buy-amazon', function () {
            window.selectedAmazonUrl = $(this).data('details-amazon-url');
            elementorProFrontend.modules.popup.showPopup({ id: 8588 });
        });


        // When user clicks OK → redirect
        $(document).on('click', '.ok-buy-now', function () {
            if (window.selectedAmazonUrl) {
                    $('#elementor-popup-modal-8588 .eicon-close').trigger('click');

                setTimeout(function () {
                    window.open(window.selectedAmazonUrl, '_blank'); // open in new tab
                }, 300);
            }
        });
        
         $(document).on('click', '.amazon-redirect-popup-close', function () {
                    $('#elementor-popup-modal-8588 .eicon-close').trigger('click');
        });



    });

    // load more in wishlist
    jQuery(document).ready(function($) {
        setTimeout(() => {
            let itemsPerPage = 24; // how many products to show initially
            let $items = $('#wishlist-products .box.presonalised-item');
            let totalItems = $items.length;
            let visibleCount = itemsPerPage;

            // hide all first
            $items.hide();

            // show initial
            $items.slice(0, itemsPerPage).show();

            // if total products <= itemsPerPage, hide button
            if (totalItems <= itemsPerPage) {
                $('#load-more1').hide();
            }

            // Load More button
            $('#load-more1').on('click', function() {
                visibleCount += itemsPerPage;
                $items.slice(0, visibleCount).fadeIn();

                if (visibleCount >= totalItems) {
                    $(this).hide(); // hide button if no more items
                }
            });
        }, 1500);
    });

    // see more js for product description in details page
    jQuery(document).ready(function ($) {
        const desc = $('.product-description-content');
        const btn = $('.see-more-btn');

        desc.each(function (i, el) {
            const contentHeight = $(el).prop('scrollHeight');
            const visibleHeight = $(el).height();

            if (contentHeight > visibleHeight + 50) {
            $(el).next('.see-more-btn').show();
            }
        });

        btn.on('click', function () {
            const content = $(this).prev('.product-description-content');
            content.toggleClass('expanded');
            $(this).text(content.hasClass('expanded') ? 'See Less' : 'See More');
        });



     
    });


jQuery(document).ready(function($){

    // Open popup (ajax)
    $(document).on('click', '.add-to-personalized, .add-to-amazon-personalized', function(e){
        e.preventDefault();

        // ✅ Prevent double-click firing twice
        let button = $(this);
    // if (button.hasClass('processing')) return;
    //     button.addClass('processing');

        
        let product_id = button.data('product-id');
        let click_action = button.data('action');
          // get the amazon url for this product - change selector as needed
        let amazon_url = button.data('amazon-url');


        $.ajax({
            url: wishlist_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'add_to_amazon_personalized',
                product_id: product_id,
                amazon_url: amazon_url,
                click_action: click_action
            },
            success: function(response) {
                if (response.success) {
                    $('#wishlist-popup h3').text('Added to Your Registry!');
                } else {
                    $('#wishlist-popup h3').text(response.data.message);
                    if (response.data.status == 'not_logged_in') {
                        $('#wishlist-popup .wishlist-view-registry').remove();
                        $('#wishlist-popup .wishlist-keep-shopping').remove();
                        $('#wishlist-popup .wishlist-popup-content').append('<a href="' + myData.login_url + '"><button type="button" class="wishlist-view-registry" >Login</button></a>');
                    }else if(response.data.status == 'already_exists'){
                        // $('#wishlist-popup .wishlist-view-registry').remove();
                    }
                }

                // ensure the element's "show" state used by fadeIn is flex
                $('#wishlist-popup')
                    .addClass('active')        // sets display:flex via CSS .active
                    .css('display', 'flex')    // ensure jQuery remembers "flex" as the display value
                    .hide()                    // hide first so fadeIn animates
                    .fadeIn(300);
            },
            
            error: function() {
                $('#wishlist-popup h3').text('Something went wrong. Please try again.');

                $('#wishlist-popup')
                    .addClass('active')
                    .css('display', 'flex')
                    .hide()
                    .fadeIn(300);
            },
        //     complete: function() {
        //     // ✅ re-enable button after request
        //     button.removeClass('processing');
        // }
        
            
        });
    });

    // Close helper function (keeps logic DRY)
    function closeWishlistPopup() {
        $('#wishlist-popup').fadeOut(200, function() {
            // remove active class after fadeOut completes
            $(this).removeClass('active');
            // ensure inline display is removed so CSS (display:none) controls it
            $(this).css('display', '');
        });
    }

    // Close popup (buttons)
    $(document).on('click', '.wishlist-popup-close, .wishlist-keep-shopping', function(e){
        e.preventDefault();
        closeWishlistPopup();
    });

    // Close when clicking outside popup content (clicking the overlay)
    $(document).on('click', function(e){
        if ($(e.target).is('#wishlist-popup')) {
            closeWishlistPopup();
        }
    });

    // Close on Escape key
    $(document).on('keyup', function(e){
        if (e.key === "Escape" || e.keyCode === 27) {
            // only close if it's open
            if ($('#wishlist-popup').hasClass('active')) {
                closeWishlistPopup();
            }
        }
    });

});

// jQuery(document).on('click', '.close-popup', function () {
//   elementorProFrontend.modules.popup.closePopup();
// });

jQuery(document).on('click', '.close-popup', function () {
  if (typeof elementorProFrontend !== 'undefined' && elementorProFrontend.modules && elementorProFrontend.modules.popup) {
    elementorProFrontend.modules.popup.closePopup();
  }
});

 
        
// Try Manual Option button click handler
jQuery(document).ready(function ($) {
    $(document).on('click', '.manual-try-again', function (e) {
        e.preventDefault();

        // Close the fail popup
        $('.amazon-fail-popup').fadeOut(200);

        // Small delay to ensure smooth close animation
        setTimeout(function () {
            // Step 2: Open Elementor popup ID 9979
            if (typeof elementorProFrontend !== 'undefined' && elementorProFrontend.modules.popup) {
                elementorProFrontend.modules.popup.showPopup({ id: 8877 });
            }
        }, 300);
    });
});

// cart quantity update in cart page
jQuery(document).ready(function ($) {

    // Find WC cart block (wrapper)
    const cartWrapper = document.querySelector('.wc-block-cart, .wc-block-components-totals-wrapper, .wp-block-woocommerce-cart');

    if (cartWrapper) {
        console.log("Cart wrapper found — starting observer");

        const observer = new MutationObserver(function(mutations) {
            // This fires whenever a cart item is removed, changed, etc.
            console.log("Cart DOM changed — refreshing cart fragments...");

            setTimeout(function () {
                $(document.body).trigger('wc_fragment_refresh');
            }, 200);
        });

        observer.observe(cartWrapper, {
            childList: true,
            subtree: true
        });
    } else {
        console.log("No cart wrapper found");
    }
});


// Recaptcha
// document.querySelector('form').addEventListener('submit', function(e) {
//     var response = grecaptcha.getResponse();
//     if(response.length === 0) {
//         e.preventDefault();
//         alert("Please verify that you are not a robot.");
//     }
// });

jQuery(function($){
    if ($('#billing_country option').length <= 1) {
        $.each(wc_countries, function(code, name){
            $('#billing_country').append(
                $('<option>', { value: code, text: name })
            );
        });
    }
    $(document).on('change', '#billing_country', function(){
        console.log("Country changed:", $(this).val());
        var country = $(this).val();
        var $state = $('#billing_state');
        if ($state.is('input')) {
            $state.replaceWith('<select id="billing_state" name="billing_state"></select>');
            $state = $('#billing_state');
        }
        $state.html('<option value="">Select State</option>');
        if (wc_states[country] && Object.keys(wc_states[country]).length > 0) {
            $.each(wc_states[country], function(code, name){
                $state.append(
                    $('<option>', { value: code, text: name })
                );
            });
        } else {
            $state.replaceWith('<input type="text" class="input-text" name="billing_state" id="billing_state" placeholder="Enter State" />');
        }
    });
});