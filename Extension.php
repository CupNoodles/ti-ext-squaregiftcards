<?php 

namespace CupNoodles\SquareGiftCards;

use System\Classes\BaseExtension;

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
