# PayPal Payment

## Maintainer Contacts
* [Ryan Dao](https://github.com/ryandao)
* [Frank Mullenger](https://github.com/frankmullenger)
* [Jeremy Shipman](https://github.com/jedateach)

## Requirements
* SilverStripe 3.*
* SilverStripe Payment 1.*

## Documentation

### Usage Overview
This module provides PayPal Express Payment support for the SilverStripe Payment module. 

### Installation guide
1. Place this directory in the root of your SilverStripe installation and call it 'payment-paypal'.

2. Enable the PayPalExpress payment method in the payment YAML configuration file.  
e.g: mysite/_config/Mysite.yaml

```yaml
PaymentGateway:
  environment:
    'dev'

PaymentProcessor:
  supported_methods:
    dev:
      - 'PayPalExpress'
    live:
      - 'PayPalExpress'
```

3. Configure the PayPalExpress payment method with your PayPal API details in the payment YAML configuration file.  
e.g mysite/_config/Mysite.yaml

```yaml
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
```

**Notes:**  
To get PayPal Sandbox test accounts, follow the [PayPal documentation](https://cms.paypal.com/cms_content/US/en_US/files/developer/PP_Sandbox_UserGuide.pdf).
 
If you have "Bad request" problems with PayPal, try to empty the browser cache and cookies.

If you get error 3005 try testing on a different machine/IP address or with different PayPal test accounts which do not have the same credit card details stored.