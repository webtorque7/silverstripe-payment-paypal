SilverStripe Payment PayPal Module
==================================

Maintainer Contacts
-------------------
*  Ryan Dao

Requirements
------------
* SilverStripe 3.0

Documentation
-------------

Installation Instructions
-------------------------
1. Place this directory in the root of your SilverStripe installation and call it 'payment-paypal'.
2. Visit yoursite.com/dev/build to rebuild the database.
3. Enable supported payment methods in your application yaml file

e.g: mysite/_config/Mysite.yaml
PaymentGateway:
  environment:
    'dev'
PaymentProcessor:
  supported_methods:
    dev:
      - 'PayPalDirect'
      - 'PayPalExpress'
    live:
      - 'PayPalDirect'
      - 'PayPalExpress'

4. Configure the Paypal gateways

e.g: mysite/_config/Mysite.yaml
PayPalGateway: 
  dev: 
    authentication:
      username:
      password:
      signature: 
  live:
    authentication:
      username:
      password:
      signature: 

Usage Overview
--------------
