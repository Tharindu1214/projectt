<?php

/**
 * ShipStation API Wrapper
 *
 * Provides an Custom OOP interface to ShipStation API
 *
 * @package     Public
 * @subpackage  ShipStation
 * @author      Harsh Kumar Gandhi
 * @copyright   2017  
 * @version     1.0
 * @Date        1 March ,2017
 *
 */

class ShipStationCustom
{
      public function __construct() {


        require_once(CONF_INSTALLATION_PATH . '/vendor/shipstation/libraries/unirest/Unirest.php');
        require_once(CONF_INSTALLATION_PATH . '/vendor/shipstation/libraries/shipstation/Shipstation.class.php');


        // if (!Settings::getSetting('CONF_SHIPSTATION_API_STATUS')) {
        //       throw new Exception("Shipstation is disbaled");
        //   }

        $this->apiKey = Settings::getSetting('CONF_SHIPSTATION_API_KEY');
        $this->apiSecret = Settings::getSetting('CONF_SHIPSTATION_API_SECRET_KEY');
        Unirest::verifyPeer(false);

        $this->shipstation = new Shipstation();
        $this->productDim = new stdClass();
        $this->shipstation->setSsApiKey($this->apiKey);
        $this->shipstation->setSsApiSecret($this->apiSecret);
    }
    
}
 