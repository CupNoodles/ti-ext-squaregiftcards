@if ($cart->count())
        <div class="checkout-gc payment-header-box">

                <div id="squareGiftCardForm"
                data-application-id="{{ $application_id }}"
                data-location-id="{{ $location_id }}"
                data-gc-handler="{{ $applySquareGiftCardEventHandler }}"
                >
                    <div id="sq-gift-card"></div>
                    <div id="square-gift-card-message">
                    </div>
                    <div id="square-gift-card-errors">
                    </div>
                </div>
                <button
                    id="squareGiftCardSubmitButton"
                    type="button"
                    class="btn btn-light"
                    data-replace-loading="fa fa-spinner fa-spin"
                    title="@lang('cupnoodles.squaregiftcards::default.button_apply_gift_card')"
                ><i class="fa fa-check"></i></button>
                
        </div>
@endif
