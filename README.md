# PayPal Payment

## Maintainer Contacts
---------------------
*  Ryan Dao
*  Frank Mullenger
*  Jeremy Shipman

## Requirements
---------------------
* SilverStripe 3.0
* SilverStripe Payment

## Documentation
---------------------
### Usage Overview

This module provides PayPal payment support for the SilverStripe Payment module. 

### Installation guide
  Add to mysite/_config:
  
  	PayPalGateway_Express: 
		  live:
		    authentication:
		      username: ''
		      password: ''
		      signature: ''
		  dev:
		    authentication:
		      username: ''
		      password: ''
		      signature: ''

To get PayPal Sandbox test accounts, follow the [PayPal documentation](https://cms.paypal.com/cms_content/US/en_US/files/developer/PP_Sandbox_UserGuide.pdf).
 
**Note:** If you have "Bad request" problems with PayPal, try to empty the browser cache and cookies. If you get error 3005 try testing on a different machine/IP address.