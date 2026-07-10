$(document).ready(function () {
    $('#package').change(function () {
        $package = $(this).val();

        if ($package == '1') {
            $('#amount').val(2005);
        }
        else if ($package == '2') {
            $('#amount').val(5899);
        }
        else if ($package == '3') {
            $('#amount').val(58999);
        }

    });



});