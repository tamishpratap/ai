

$(document).ready(function () {
    $('#withAmount').on('input', function () {
        var amount = $(this).val();
        var netAmount = amount / 100 * 95;
        $('#netAmount').val(netAmount);
    });

    //withdrawal code
    $('#withAmount').on('input', function () {

        var netBalance = parseInt($('#netamount').val());
        var inputAmount = parseInt($(this).val());

        if (inputAmount > netBalance) {

            $('#withdrawBtn').attr('disabled', true);
            $('#wMesg').html('Enter amount can not be grater then Net Balance');
        }
        else if (inputAmount < 5) {
            $('#withdrawBtn').attr('disabled', true);
            $('#wMesg').html('Minimum withdrawal limit is 5 $');
        } else {
            $('#withdrawBtn').attr('disabled', false);
            $('#wMesg').html('');
        }
    });

    //Limit Buy input range code
    $('#buyrange').on('input', function () {
        var amount = parseFloat($(this).val());
        var price = parseFloat($('#limit_price').val());
        var coins = amount / price;
        if (!price) {
            $('#price-msg').html('Please First Enter Bid Price')
        } else {
            $('#buy_amount').val(amount.toFixed(4));
            $('#totbal').val(coins.toFixed(4));
            
        }
    });
    // Mannual Enter
    $('#buy_amount, #limit_price').on('input', function () {
        var amount = parseFloat($(this).val());
        var price = parseFloat($('#limit_price').val());
        var wallet = parseFloat($('#buyrange').val());
        var coins = amount / price;


        if (!amount || amount.length == 0 || amount == '') {
            $('#buy_amount').val('');
            $('#totbal').val('');
        } else {
            if (!price) {
                $('#price-msg').html('** Please First Enter Bid Price');
                $('#buyLimitBtn').prop('disabled', true);
            } else if (wallet < amount) {
                $('#price-msg').html('** No Enought Balance in wallet');
                $('#buyLimitBtn').prop('disabled', true);
            }
            else {
                $('#price-msg').html('');
                //$('#buybal').val(amount);
                $('#totbal').val(coins.toFixed(4));
            }
        }

    });

    //Market Buy input range code
    $('#market_buy_range').on('input', function () {
        var amount = parseFloat($(this).val());
        $('#market_buy_amount').val(amount.toFixed(4));
        $('#market_tot_bal').val(amount.toFixed(4));
        $('#buyTextAmount').html(amount.toFixed(4));
    });
    //market Mannual Enter
    $('#market_buy_amount').on('input', function () {
        var amount = parseFloat($(this).val());
        var wallet = parseFloat($('#market_buy_range').val());
        if (!amount || amount == 0 || amount == '') {
            $('#market_buy_amount').val('');
            $('#market_tot_bal').val('');
            $('#buyTextAmount').html('');
        } else if (amount < 0) {
            $('#price-msg-mar').html('** Negative value is not allowed. Please enter valid amount');
            $('#buyMarketBtn').prop('disabled', true);
        } else {
            if (wallet < amount) {
                $('#price-msg-mar').html('** No Enought Balance in wallet');
                $('#buyMarketBtn').prop('disabled', true);
            } else {
                $('#price-msg-mar').html('');
                $('#buyMarketBtn').prop('disabled', false);
                $('#market_tot_bal').val(amount.toFixed(4));
                $('#buyTextAmount').html(amount.toFixed(4));
            }

        }
    });
    //Limit sell enter function 
    $('#limit_price_sell, #limit_quant_sell').on('input', function () {

        var price = parseFloat($('#limit_price_sell').val());
        var quantity = parseFloat($('#limit_quant_sell').val());
        var amount = price * quantity;
        var total_quant = parseFloat($('#buyrange').val());
        var total_assets = parseFloat($('#total_assets').val());

        if (!quantity || quantity.length == 0 || quantity == '') {
            $('#limit_sell_total').val('');
            $('#limit_quant_sell').val('');
        } else {
            if (!price) {
                $('#sell-price-msg').html('** Please First Enter Bid Price');
                $('#sellLimitBtn').prop('disabled', true);
            } else if (quantity > total_assets) {
                $('#sell-price-msg').html('** Sell Quantity can not be grater than available quantity');
                $('#sellLimitBtn').prop('disabled', true);
            } else {
                $('#sell-price-msg').html('');
                $('#sellLimitBtn').prop('disabled', false);
                $('#limit_sell_total').val(amount.toFixed(4))
            }
        }

    });

    //Limit sell range function
    $('#sell_range_limit').on('input', function () {
        var quantity = parseFloat($(this).val());
        var price = parseFloat($('#limit_price_sell').val());
        var amount = quantity * price;
        var total_assets = parseFloat($('#total_assets').val());

        if (!price) {
            $('#sell-price-msg').html('** Please First Enter Bid Price');
            $('#sellLimitBtn').prop('disabled', true);
        } else if (quantity > total_assets) {
            $('#sell-price-msg').html('** Sell Quantity can not be grater than available quantity');
            $('#sellLimitBtn').prop('disabled', true);
        } else {
            $('#sell-price-msg').html('');
            $('#sellLimitBtn').prop('disabled', false);
            $('#limit_sell_total').val(amount.toFixed(4));
            $('#limit_quant_sell').val(quantity.toFixed(4));
        }

    });

    //Market Sell enter function
    $('#market_quantity_sell').on('input', function () {
        var quantity = parseFloat($(this).val());
        var total_assets = parseFloat($('#total_assets').val());
        if (!quantity || quantity == '' || quantity == 0) {
            $('#market_total_sell').val('0.0000');
        } else if (quantity < 0) {
            $('#sell-market-msg').html('** Negative value is not allowed. Please enter valid quantity');
            $('#sellMarketBtn').prop('disabled', true);
        } else {
            if (quantity > total_assets) {
                $('#sell-market-msg').html('** Sell Quantity can not be grater than available quantity');
                $('#sellMarketBtn').prop('disabled', true);
            } else {
                $('#sell-market-msg').html('');
                $('#sellMarketBtn').prop('disabled', false);
                $('#market_total_sell').val(quantity.toFixed(4));
            }
        }

    });

    //Market Sell range enter function
    $('#market_sell_range').on('input', function () {
        var quantity = parseFloat($(this).val());
        var total_assets = parseFloat($('#total_assets').val());
        if (quantity > total_assets) {
            $('#sell-market-msg').html('** Sell Quantity can not be grater than available quantity');
            $('#sellMarketBtn').prop('disabled', true);
        } else {
            $('#sell-market-msg').html('');
            $('#sellMarketBtn').prop('disabled', false);
            $('#market_total_sell').val(quantity.toFixed(4));
            $('#market_quantity_sell').val(quantity.toFixed(4));
        }

    });
});