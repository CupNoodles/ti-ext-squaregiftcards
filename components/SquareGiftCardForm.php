<?php

namespace CupNoodles\SquareGiftCards\Components;

use ApplicationException;
use Cart;
use Exception;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Models\CartSettings;
use Location;
use Redirect;
use Request;
use App;
use Omnipay\Omnipay;


use Admin\Models\Payments_model;
use Igniter\PayRegister\Payments\Square;
use CupNoodles\SquareGiftCards\Models\SquareGiftCardSettings;

class SquareGiftCardForm extends \System\Classes\BaseComponent
{

    /**
     * @var \Igniter\Cart\Classes\CartManager
     */
    protected $cartManager;

    public function initialize()
    {
        $this->cartManager = CartManager::instance();
        // as annoying as this is, GC can only be use though the tokenized webpayments form, which means it can't persist across pageloads in the same session.
        // remove it if it exits on page load to make sure that a customer who refreshes the page on /checkout does NOT have GC applied (page needs to re-request a nonce and customer neds to re-enter the GC number)
        $post_data = post();
        if(!isset($post_data['square_gc_nonce']) || $post_data['square_gc_nonce'] == ''){
            $this->cartManager->getCart()->removeCondition('squareGiftCard');
        }
        
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        
        $endpoint = SquareGiftCardSettings::get('transaction_mode') == 'test' ? 'sandbox.' : '';
        $this->addJs('https://'.$endpoint.'web.squarecdn.com/v1/square.js', 'square-js');
        //$this->addJs('https://js.'.$endpoint.'.com/v2/paymentform', 'square-js');
        $this->addJs('$/cupnoodles/squaregiftcards/assets/js/webpaymentssdk.giftcard.js', 'square-gc-js');
        
        $this->prepareVars();
        
    }

    protected function prepareVars()
    {
        $this->page['applySquareGiftCardEventHandler'] = $this->getEventHandler('onApplySquareGiftCard');
        $this->page['application_id'] = SquareGiftCardSettings::get('transaction_mode') == 'test' ? SquareGiftCardSettings::get('test_app_id') :  SquareGiftCardSettings::get('live_app_id') ;        
        $this->page['location_id'] = SquareGiftCardSettings::get('transaction_mode') == 'test' ? SquareGiftCardSettings::get('test_location_id') :  SquareGiftCardSettings::get('live_location_id') ;
        
    }

    // While Omnipay is great, the square-omnipay library seems to not be up to date on squareup's v2 endpoints, and on top of that Square's GC handling leaves a lot to be desired. 
    // Instead of trying to integrate it into omnipay we're just going to write a curl wrapper here and make calls manually. 
    // As of writing this, doc reference is at https://developer.squareup.com/reference/square
    public function curl_square_v2_request($endpoint, $post_data = null){


        $url = SquareGiftCardSettings::get('transaction_mode') == 'test' ? 'https://connect.squareupsandbox.com/v2/' : 'https://connect.squareup.com/v2/';
        try{

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url . $endpoint);
            curl_setopt($ch, CURLOPT_POST, 1);
            if($post_data){
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization:  Bearer ' . (SquareGiftCardSettings::get('transaction_mode') == 'test' ? SquareGiftCardSettings::get('test_access_token') :  SquareGiftCardSettings::get('live_access_token')),
                'Content-Type: application/json'
            ));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $res = curl_exec($ch);
            
            curl_close($ch);
            return json_decode($res);
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
        
    }

    public function onApplySquareGiftCard()
    {
        
        try {
            
            // So this suuuuuuucks as of 5-14-2021 Square has no way of querying for a gift card value via API (there is a web form where customers can do so, but its not exposed programatically)
            // In order to get the GC value, we charge it for $10000, use the server response, and then immediately cancel the transaction
            
            $post_data = post();
            $idemp = uniqid();
            $fields = [
                'idempotency_key' => $idemp,
                'amount_money' => [
                    'amount' => 1000000,
                    'currency' => currency()->getUserCurrency()
                ],
                'note' => 'Payment by Gift Card',
                'source_id' => $post_data['nonce'],
                'accept_partial_authorization' => true,
                'autocomplete' => false
            ];
            
            $authorize_response = $this->curl_square_v2_request('payments', $fields);

            if(isset($authorize_response->errors)){
                flash()->alert($authorize_response->errors[0]->detail);
            }
            
            // cancelling the payment needs to happen regardeless of whether the auth errored out
            $cancel_response = $this->curl_square_v2_request('payments/cancel', ['idempotency_key' => $idemp]);
            if(isset($cancel_response->errors)){
                flash()->alert($cancel_response->errors[0]->detail);
            }

            // remove the GC cart condition if it exists (we're going to re-add it in a moment, but need to cart total to be the non-GC applied value)
            $this->cartManager->getCart()->removeCondition('squareGiftCard');

            if(isset($authorize_response->payment) && $authorize_response->payment->status == 'APPROVED'){

                $gc_amount = $authorize_response->payment->amount_money->amount / 100;
                
                if($gc_amount >= $this->cartManager->getCart()->total()){  // GC covers the cost, set it to full order value
                    
                    $condition = $this->cartManager->applyCondition('squareGiftCard', [
                        'amount' => $this->cartManager->getCart()->total(),
                        'full_gc_value' => $gc_amount
                    ]);
                    
                }
                else{ // GC does not cover cost - set cart condition to GC value and take another payment
                    $condition = $this->cartManager->applyCondition('squareGiftCard', [
                        'amount' => $gc_amount,
                        'full_gc_value' => $gc_amount
                    ]);
                }
            }


            $this->controller->pageCycle();
            return $this->fetchPartials();
        
        
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }


    public function fetchPartials()
    {
        $this->prepareVars();

        $return_templates = [];
        if($this->hasComponent('cartBox')){ $return_templates['#cart-totals'] = $this->renderPartial('cartBox::totals', ['cart' => $this->cartManager->getCart()]); }
        if($this->hasComponent('cartBoxAlias')){ $return_templates['#cart-totals'] = $this->renderPartial('cartBoxAlias::totals', ['cart' => $this->cartManager->getCart()]); }
        
        if($this->hasComponent('checkout')){ $return_templates['#checkout-totals'] = $this->renderPartial('checkout::totals', ['cart' => $this->cartManager->getCart()]); }
        if($this->hasComponent('checkoutByWeight')){ $return_templates['#checkout-totals'] = $this->renderPartial('checkoutByWeight::totals', ['cart' => $this->cartManager->getCart()]); }
        

        // use this if you want the payment fields to update after applying a GC (you probably don't, since you might need to continue to take further payments)
        //if($this->hasComponent('checkout')){$return_templates['#checkout-payments'] = $this->renderPartial('checkout::payments'); }
        //if($this->hasComponent('checkoutByWeight')){ $return_templates['#checkout-payments'] = $this->renderPartial('checkoutByWeight::payments');}
        

        return $return_templates;
    }

    public function onRefresh()
    {
        return $this->fetchPartials();
    }







}
