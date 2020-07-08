(function($){
    loadLibrary = function( clientToken, paymentAmount, currencyCode ){
            try{
                if(typeof clientToken != typeof undefined){

                    var button = document.querySelector('#submit-button');

                    braintree.dropin.create({
                      authorization: clientToken,
                      container: '#dropin-container',
                        venmo:
                        {
                            allowNewBrowserTab: false
                        },
                        googlePay: {
                            /* merchantId: 'merchant-id-from-google', */
                            environment: 'TEST',
                            transactionInfo:
                            {
                              totalPriceStatus: 'FINAL',
                              totalPrice: paymentAmount,
                              currencyCode: currencyCode
                            },
                            cardRequirements:
                            {
                              billingAddressRequired: true
                            }
                        },
                        paypal: {
                            flow: 'vault',
                            amount: paymentAmount,
                            currency: currencyCode
                        },
                        applePay: {
                            displayName: 'My Store',
                            paymentRequest: {
                              total: {
                                amount: paymentAmount
                              },
                              // We recommend collecting billing address information, at minimum
                              // billing postal code, and passing that billing postal code with all
                              // Google Pay transactions as a best practice.
                              requiredBillingContactFields: ["postalAddress"]
                            }
                        }

                    }, function (createErr, instance) {
                        if (createErr) {
                            console.error('Create Error!!');
                            console.error(createErr);
                            return;
                        }
                        $(".waiting_message").remove();
                        $("#submit-button").removeAttr('disabled');
                        button.addEventListener('click', function () {

                            instance.requestPaymentMethod(function (requestPaymentMethodErr, payload)
                            {
                                // Submit payload.nonce to your server
                                var form$ = $("#frmPaymentForm");
                                var nonce = payload.nonce;
                                // insert the token into the form so it gets submitted to the server
                                form$.append("<input type='hidden' name='paymentMethodNonce' value='" + nonce + "' />");
                                form$.append("<input type='hidden' name='amount' value='" + paymentAmount + "' />");
                                form$.get(0).submit();
                                $("#cancelLink").remove();
                                $("#submit-button").val('Processing..');
                                $("#submit-button").attr('disabled','disabled');
                            });
                        });
                    });

                }
            }catch(e){
                console.log('Execution Error!!');
                console.log(e.message);
            }
	};
})(jQuery);
