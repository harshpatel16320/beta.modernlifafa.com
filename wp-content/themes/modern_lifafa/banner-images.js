// This code for display choose banne image option in wordpress backend  
jQuery(document).ready(function ($) {
    $('.upload-banner-image').on('click', function (e) {
        e.preventDefault();

        let button = $(this);
        let targetInput = $('#' + button.attr('data-target'));
        let previewImg = $('#preview_' + button.attr('data-target'));
        let removeBtn = button.siblings('.remove-banner-image');

        // Ensure media uploader is properly initialized
        let mediaUploader = wp.media({
            title: 'Select a Banner Image',
            library: { type: 'image' },
            button: { text: 'Use this image' },
            multiple: false
        });

        mediaUploader.on('select', function () {
            let attachment = mediaUploader.state().get('selection').first().toJSON();
            targetInput.val(attachment.id);
            previewImg.attr('src', attachment.url).show();
            removeBtn.show();
        });

        mediaUploader.open();
    });

    $('.remove-banner-image').on('click', function (e) {
        e.preventDefault();

        let button = $(this);
        let targetInput = button.siblings('input[type="hidden"]');
        let previewImg = button.siblings('img');

        targetInput.val('');
        previewImg.hide().attr('src', '');
        button.hide();
    });
});