<?php

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => ['label' => 'lang:admin::lang.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave'],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
    'fields' => [
        'setup' => [
            'type' => 'section',
            'comment' => 'lang:cupnoodles.squaregiftcards::default.help_square',
        ],
        'enable' => [
            'label' => 'lang:cupnoodles.squaregiftcards::default.label_enable_square_gc',
            'type' => 'switch',
            'default' => FALSE,
            'offText' => 'lang:admin::lang.text_no',
            'onText' => 'lang:admin::lang.text_yes',

        ],
        'transaction_mode' => [
            'label' => 'lang:cupnoodles.squaregiftcards::default.label_transaction_mode',
            'type' => 'radiotoggle',
            'default' => 'test',
            'options' => [
                'test' => 'lang:cupnoodles.squaregiftcards::default.text_test',
                'live' => 'lang:cupnoodles.squaregiftcards::default.text_live',
            ],
        ],
        'live_app_id' => [
            'label' => 'lang:cupnoodles.squaregiftcards::default.label_live_app_id',
            'type' => 'text',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[live]',
            ],
        ],
        'live_access_token' => [
            'label' => 'lang:cupnoodles.squaregiftcards::default.label_live_access_token',
            'type' => 'text',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[live]',
            ],
        ],
        'live_location_id' => [
            'label' => 'lang:cupnoodles.squaregiftcards::default.label_live_location_id',
            'type' => 'text',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[live]',
            ],
        ],
        'test_app_id' => [
            'label' => 'lang:cupnoodles.squaregiftcards::default.label_test_app_id',
            'type' => 'text',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[test]',
            ],
        ],
        'test_access_token' => [
            'label' => 'lang:cupnoodles.squaregiftcards::default.label_test_access_token',
            'type' => 'text',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[test]',
            ],
        ],
        'test_location_id' => [
            'label' => 'lang:cupnoodles.squaregiftcards::default.label_test_location_id',
            'type' => 'text',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[test]',
            ],
        ]
    ]
    ]
];
