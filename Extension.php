<?php 

namespace CupNoodles\SquareGiftCards;

use System\Classes\BaseExtension;
use Igniter\Cart\Classes\CartConditionManager;
use CupNoodles\SquareGiftCards\Components\SquareGiftCardForm;
use Event;

class Extension extends BaseExtension
{
    /**
     * Returns information about this extension.
     *
     * @return array
     */
    public function extensionMeta()
    {
        return [
            'name'        => 'SquareGiftCards',
            'author'      => 'CupNoodles',
            'description' => 'Accept Square Gift Cards from Checkout',
            'icon'        => 'fas fa-money-check-alt',
            'version'     => '1.0.0'
        ];
    }

    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        

        // This is the actual payment that doesn't get cancelled
        Event::listen('igniter.checkout.beforePayment', function($order, $data){

            foreach($order->getOrderTotals() as $key=>$ot){
                if($ot->code == 'squareGiftCard' && $ot->value > 0 && isset($data['square_gc_nonce'])){
                    $sqgf = new SquareGiftCardForm();

                    $idemp = uniqid();
                    $fields = [
                        'idempotency_key' => $idemp,
                        'amount_money' => [
                            'amount' => (int)($ot->value * 100),
                            'currency' => currency()->getUserCurrency()
                        ],
                        'note' => 'Payment by Gift Card',
                        'source_id' => $data['square_gc_nonce'],
                        'accept_partial_authorization' => true,
                        'autocomplete' => false // this is a lie, SQ payments api fails if you don't mark autocomplete false on a GC but there is no AUTH/CAPT distinction for gift cards
                    ];
                    $payment_response = $sqgf->curl_square_v2_request('payments', $fields);
                    if(isset($payment_response->errors)){
                        flash()->alert($payment_response->errors[0]->detail);
                    }
                    
                }
            }

        });

    }
        
    public function registerComponents()
    {
        return [
            'CupNoodles\SquareGiftCards\Components\SquareGiftCardForm' => [
                'code' => 'squareGiftCardForm',
                'name' => 'lang:cupnoodles.squaregiftcards::default.text_component_title',
                'description' => 'lang:cupnoodles.squaregiftcards::default.text_component_desc',
            ]
        ];
    }
    

    public function registerPaymentGateways()
    {

    }


    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Square Gift Card Settings',
                'description' => 'Manage Square Gift Card settings.',
                'icon' => 'fas fa-money-check-alt',
                'model' => 'CupNoodles\SquareGiftCards\Models\SquareGiftCardSettings',
                'permissions' => ['Module.SquareGiftCards'],
            ],
        ];
    }



    public function registerCartConditions()
    {
        return [
            \CupNoodles\SquareGiftCards\CartConditions\SquareGiftCard::class => [
                'name' => 'squareGiftCard',
                'label' => 'lang:igniter.coupons::default.text_coupon',
                'description' => 'lang:igniter.coupons::default.help_coupon_condition',
            ],
        ];
    }


    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [

        ];
    }

    public function registerNavigation()
    {
        return [

        ];
    }



}
