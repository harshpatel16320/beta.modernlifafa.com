jQuery.validator.addMethod("checklower", function(value) {
    return /[a-z]/.test(value);
});
jQuery.validator.addMethod("checkupper", function(value) {
    return /[A-Z]/.test(value);
});
jQuery.validator.addMethod("checkdigit", function(value) {
    return /[0-9]/.test(value);
});
jQuery.validator.addMethod("notEqualToCurrent", function(value, element) {
    return value !== $("#password_current").val();
}, "New password must be different from the current password.");
jQuery.validator.addMethod("strongPassword", function(value, element) {
    return this.optional(element) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/.test(value);
}, "Password must be at least 8 characters long and contain at least one number, one lowercase and one uppercase letter.");
 jQuery('#woocommerce-EditAccountForm').validate({
    rules: {
        password_current: {
            required: true,
        },
        password_1: {
            required: true,
            strongPassword : true,
            notEqualToCurrent: true
        },
        password_2: {
            equalTo: "#password_1",
        },
    },
    messages: {
        password_current: "Please Enter Old Password.",
        password_1: {
            required:  "Please Enter New Password.",
            strongPassword: "Must be 8+ chars with a number, lowercase and uppercase.",
            notEqualToCurrent: "New password must be different from the current password.",
        },
        password_2: {
            equalTo: "New Password and Confirm Password are not same.",
        }
    },
    errorElement: "div",
    errorPlacement: function(error, element) {
        element.after(error);
    }
});

jQuery(document).ready(function($) {
    $(document).on('elementor/popup/show', function(event, popup) {
        if ($('.couple_share_link').length) {
            var link = $('.couple_share_link').val();
            console.log('Link:', link);
     
            var emailSubject = 'Check out our Modern Lifafa Registry!';
            var emailBody = 'Check out our Modern Lifafa Registry at: ' + link;
            console.log('Email Body:', emailBody);
     
            var mailtoLink = 'mailto:?subject=' + encodeURIComponent(emailSubject) + '&body=' + encodeURIComponent(emailBody);
            console.log('Mailto Link:', mailtoLink);
     
            $('.page_link_share').val(link);
            $('.wtsp_share').attr('href', 'https://api.whatsapp.com/send?text=' + encodeURIComponent(link));
            $('.fcbk_share').attr('href', 'https://www.facebook.com/sharer.php?u=' + encodeURIComponent(link));
            $('.twtr_share').attr('href', 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(link) + '&text=' + encodeURIComponent('Personalised Page'));
            $('.gmail_share').attr('href', mailtoLink);
        }
    });
    $('#file').change(function() {
        var file = this.files[0];
        if (file) {
            var fileType = file.type;
            var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (validTypes.includes(fileType)) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var fileUrl = e.target.result;
                    $('.user_profile').attr('src', fileUrl);
                    $('.upload-file').addClass('full_image');
                    $('.add_image').attr('src', fileUrl);
                }
                reader.readAsDataURL(file);
            }else{
                alert('Invalid file type. Please upload an image file (jpg, jpeg, png, webp).');
            }
        } else {
            $('#profile-image').attr('src', '<?php echo get_template_directory_uri(); ?>/assets/images/user-image.webp');
        }
    });
});
