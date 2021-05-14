<?php

namespace CupNoodles\SquareGiftCards\Models;

use Model;

/**
 * @method static instance()
 */
class SquareGiftCardSettings extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    // A unique code
    public $settingsCode = 'cupnoodles_square_gift_card_settings';

    // Reference to field configuration
    public $settingsFieldsConfig = 'squaregiftcardsettings';

    //
    //
    //
}
