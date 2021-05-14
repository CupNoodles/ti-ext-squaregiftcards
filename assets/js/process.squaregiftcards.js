+function ($) {
    "use strict"

    var ProcessSquareGC = function (element, options) {
        this.$el = $(element)
        this.options = options || {}
        this.$submitButton = $('#squareGiftCardSubmitButton')
        this.square = null;

        this.init();
    }

    ProcessSquareGC.prototype.init = function () {

        var spOptions = {
            applicationId: this.options.applicationId,
            locationId: this.options.locationId,
            autoBuild: false,
            inputClass: 'form-control',
            giftCard: {
                elementId: 'sq-gift-card',
                placeholder: "* * * *  * * * *  * * * *  * * * *"
            },
            callbacks: {
                cardNonceResponseReceived: $.proxy(this.onResponseReceived, this)
            }
        }

        if (this.options.applicationId === undefined)
            throw new Error('Missing square application id')

        this.square = new SqPaymentForm($.extend(spOptions, this.options.cardFields))

        this.square.build()

        this.$submitButton.off('click').on('click', $.proxy(this.gcFormHandler, this))
    }

    ProcessSquareGC.prototype.gcFormHandler = function (event) {

        // Prevent the form from submitting with the default action
        event.preventDefault()
        event.stopPropagation();
        this.square.requestCardNonce();
    }

    ProcessSquareGC.prototype.onResponseReceived = function (errors, nonce, cardData) {


        if (errors) {
            var $el = '<b>Encountered errors:</b>';
            errors.forEach(function (error) {
                $el += '<div>' + error.message + '</div>'
            });
            this.$el.find(this.options.errorSelector).html($el);
            return;
        }

        
        console.log(this.options.gcHandler);
        // we have the token now, submit it to backend
        $.request(this.options.gcHandler, {
            data: {
                nonce: nonce
            }
        })

    }

    ProcessSquareGC.DEFAULTS = {
        applicationId: undefined,
        locationId: undefined,
        errorSelector: '#square-gift-card-errors',
        // Customize the CSS for SqPaymentForm iframe elements
        cardFields: {
            giftCard: {
                elementId: 'sq-gift-card',
                inputStyle: {
                    fontSize: '16px',
                    autoFillColor: '#000',    //Sets color of card nbr & exp. date
                    color: '#000',            //Sets color of CVV & Zip
                    placeholderColor: '#A5A5A5', //Sets placeholder text color
                    backgroundColor: '#FFF',  //Card entry background color
                    cardIconColor: '#A5A5A5', //Card Icon color
                },
            },
        }
    }

    // PLUGIN DEFINITION
    // ============================

    var old = $.fn.processSquareGC

    $.fn.processSquareGC = function (option) {
        var $this = $(this).first()
        var options = $.extend(true, {}, ProcessSquareGC.DEFAULTS, $this.data(), typeof option == 'object' && option)

        return new ProcessSquareGC($this, options)
    }

    $.fn.processSquareGC.Constructor = ProcessSquareGC

    $.fn.processSquareGC.noConflict = function () {
        $.fn.processSquareGC = old
        return this
    }

    $(document).render(function () {
        $('#squareGiftCardForm').processSquareGC()
    })
}(window.jQuery)