jQuery(document).ready(function ($) {
    console.log('Amazon admin JS loaded'); // debug

    $(document).on('click', '.fetch_amazon_url_admin', function (e) {
        e.preventDefault();

        let url = $('.amazon_product_url_admin').val();
        if (!url) {
            alert('Please enter a URL');
            return;
        }

        console.log('Fetching product from URL:', url);

        $.ajax({
            url: amazonAdmin.ajaxurl, // ✅ use localized ajaxurl
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'admin_fetch_amazon_product',
                amazon_url: url,
                add_amazon_product_nonce: amazonAdmin.nonce // ✅ localized nonce
            },
            success: function (response) {
                console.log('AJAX Response:', response);
                if (response.success) {
                    alert('Product created!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('AJAX error occurred: ' + error);
            }
        });
    });
});
