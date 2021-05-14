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

    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $endpoint = SquareGiftCardSettings::get('transaction_mode') == 'test' ? 'squareupsandbox' : 'squareup';
        $this->addJs('https://js.'.$endpoint.'.com/v2/paymentform', 'square-js');
        $this->addJs('$/cupnoodles/squaregiftcards/assets/js/process.squaregiftcards.js', 'process-square-gc-js');
        
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['applySquareGiftCardEventHandler'] = $this->getEventHandler('onApplySquareGiftCard');
        $this->page['application_id'] = SquareGiftCardSettings::get('transaction_mode') == 'test' ? SquareGiftCardSettings::get('test_app_id') :  SquareGiftCardSettings::get('live_app_id') ;        
        $this->page['location_id'] = SquareGiftCardSettings::get('transaction_mode') == 'test' ? SquareGiftCardSettings::get('test_location_id') :  SquareGiftCardSettings::get('live_location_id') ;
        
    }

    public function onApplySquareGiftCard()
    {
        try {

            $sq = new Square(Payments_model::where('code', 'square')->first());

            $gateway = Omnipay::create('Square');

            $gateway->setTestMode($sq->isTestMode());
            $gateway->setAppId($sq->getAppId());
            $gateway->setAccessToken($sq->getAccessToken());
            $gateway->setLocationId($sq->getLocationId());


            $post_data = post();
            $fields = [
                'idempotencyKey' => uniqid(),
                'amount' => number_format($this->cartManager->getCart()->total(), 2, '.', ''),
                'currency' => currency()->getUserCurrency(),
                'note' => 'Payment by Gift Card',
                'nonce' => $post_data['nonce'],
                'accept_partial_authorization' => true,
            ];


            $response = $gateway->purchase($fields)->send();

            // TODO you can't test gift cards in sandbox so this is only happy path
            $data = $response->getData();
            if($data['status'] == 'error'){ // handle errors
                throw new ApplicationException($data['detail']);
            }
            elseif($data['status'] == 'success'){
                // partially covered
                if(true == true){ // something here
                    $card_amt = 9;

                    $condition = $this->cartManager->applyCondition('squareGiftCard', [
                        'amount' => $card_amt
                    ]);

                }else if(true == true){ // something here
                    $card_amt = 100000;

                    $condition = $this->cartManager->applyCondition('squareGiftCard', [
                        'amount' => $card_amt
                    ]);
                }
                $this->controller->pageCycle();
                return $this->fetchPartials();
            }

            

            


            
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }


    public function fetchPartials()
    {
        $this->prepareVars();

        if($this->hasComponent) // if cupnoodles.pricebyweight is installed, assume we want 

        $return_templates = [];
        if($this->hasComponent('cartBox')){ $return_templates['#cart-totals'] = $this->renderPartial('cartBox::totals', ['cart' => $this->cartManager->getCart()]); }
        if($this->hasComponent('cartBoxAlias')){ $return_templates['#cart-totals'] = $this->renderPartial('cartBoxAlias::totals', ['cart' => $this->cartManager->getCart()]); }
        //if($this->hasComponent('checkout')){ $return_templates['#checkout-totals'] = $this->renderPartial('checkout::totals', ['cart' => $this->cartManager->getCart()]); }

        if($this->hasComponent('checkoutByWeight')){ $return_templates['#checkout-totals'] = $this->renderPartial('checkoutByWeight::totals', ['cart' => $this->cartManager->getCart()]); }
        

        return $return_templates;
    }

    public function onRefresh()
    {
        return $this->fetchPartials();
    }







}
