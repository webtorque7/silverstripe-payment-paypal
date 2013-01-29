<?php

class PayPal_Express extends Payment { 
  
  static $db = array(
    'Token' => 'Varchar',
    'PayerID' => 'Varchar'
  );
}
