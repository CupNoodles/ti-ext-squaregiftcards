<?php

namespace CupNoodles\SquareGiftCards\CartConditions;
use Igniter\Flame\Cart\CartCondition;
class SquareGiftCard extends CartCondition
{

    public $priority = 900;
    public $removeable = true;

    public function getLabel()
    {
        return "Gift Certificate ($".$this->getMetaData('full_gc_value')." value)";
    }

    public function onLoad()
    {

    }    
    public function beforeApply()
    {

    }

    public function getActions()
    {
        $amount = $this->getMetaData('amount');
        //$amount = 100;
        return [
            [
                'value' => "-{$amount}",
            ],
        ];
    }
    

}
