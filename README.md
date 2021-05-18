## Square Gift Cards

This is a pre-release and hasn't been tested on a live shop yet! YMMV use at your own risk.

Square Gift Cards allows you to accept gift card information at checkout, and checks against Square to see what balance remains for the gift card in question. 

### Dependancies

This plugin uses a custom curl wrapper to send manually constructed API requests to the Square Connect V2 endpoint. As such, it's not supported as party of the Omnipay system, and has it's own set of API credentials to be set in Settings => Square Gift Card Settings (though they can be the same as what's used in payregister). 

This plugin is written and tested against Square Connect API version 2021-05-13, which can be set in your Square Developer console.


### Installation

Clone these files into `extensions/cupnoodles/squaregiftcards/`. 

### Usage 

Note that as of the 2021-05-13 API, Square only supports a very limited set of testing nonces (see the full list at https://developer.squareup.com/docs/testing/test-values). Until that's updated, the only way to develop your checkout process (specifically what happens when you have a gift card that doesn't cover the order value) is to set the plugin to production mode. 


