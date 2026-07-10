jQuery(document).ready(function () {

    jQuery('#category').change(function () {

        var cat_id = jQuery('#category').val();
        let token = jQuery('#csrf').val();
        jQuery.ajax({
            url: '/getSubcat',
            type: 'post',
            data: {
                '_token': token,
                'catid': cat_id
            },
            success: function (result) {
                jQuery('#subcategory').html(result);
            },
            error: function () {
                alert('error');
            }
        })
    });
    // getting product data
    jQuery('#subcategory').change(function () {
        var sub_id = jQuery('#subcategory').val();
        let token = jQuery('#csrf').val();

        jQuery.ajax({
            url: '/getProduct',
            type: 'post',
            data: {
                '_token': token,
                'sub_id': sub_id
            },
            success: function (result) {
                jQuery('#product').html(result);
            },
            error: function () {
                alert('error');
            }
        });
    });

    jQuery('#product').change(function () {
        var product = jQuery(this).val();
        window.location = product;
    });

    $('#tax').change(function () {
        if (this.checked) {
            $('#taxinput').show();
        }
    })
    $('#notax').change(function () {
        if (this.checked) {
            $('#taxinput').hide();
        }
    })
});

