<?php

class Ship {

    protected $shipstation = null;
    protected $error = "";
    protected $productWeight = "";
    protected $productDim = "";
    protected $productDeliveryAddress = "";

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

    public function getCarriers() {

        if (!$list = $this->shipstation->getCarriers()) {
            $error = $this->shipstation->getLastError();
            throw new Exception($error->message);
        }
        return $list;
    }

    public function getProductShippingRates($carrier_code, $from_pin_code, stdClass $productWeight, stdClass $deliveryAddress, stdClass $productDim) {

        $order = new stdClass();
        $order->carrierCode = $carrier_code;
        $order->serviceCode = null;
        $order->packageCode = null;
        $order->fromPostalCode = $from_pin_code;
        $order->toState = $deliveryAddress->state;
        $order->toCountry = $deliveryAddress->country; // {awaiting_shipment, on_hold, shipped, cancelled}
        $order->toPostalCode = $deliveryAddress->pincode;
        $order->toCity = $deliveryAddress->city;
        $order->weight = $productWeight;
        if (!empty($order->dimensions)) {
            $order->dimensions = $productDim;
        }
        if (!$response = $this->shipstation->getRates((array) $order)) {
             $error = $this->shipstation->getLastError();
           
            throw new Exception($error->message);
        }

        return $response;
    }

    public function setProductDeliveryAddress($state, $country, $city, $postal_code) {
        $this->productDeliveryAddress = new stdClass();
        $this->productDeliveryAddress->state = $state;
        $this->productDeliveryAddress->country = $country;
        $this->productDeliveryAddress->pincode = $postal_code;
        $this->productDeliveryAddress->city = $city;
        return true;
    }

    public function setProductWeight($weight, $unit = "ounces") {

        $this->productWeight = new stdClass();
        $this->productWeight->value = intval($weight);
        $this->productWeight->units = trim('ounces');
        return true;
    }

    public function setProductDim($length, $width, $height) {

        $this->productDim = new stdClass();
        $this->productDim->length = $length;
        $this->productDim->width = $width;
        $this->productDim->height = $height;
        $this->productDim->units = "centimeters";
        return true;
    }

    public function getProductDim() {
        return $this->productDim;
    }

    public function getProductWeight() {
        return $this->productWeight;
    }

    public function getProductDeliveryAddress() {
        return $this->productDeliveryAddress;
    }

    public function getError() {

        return $this->error;
    }

    public function validateShipstationAccount($api_key, $api_secret) {

        $this->shipstation = new Shipstation();
        $this->shipstation->setSsApiKey($api_key);
        $this->shipstation->setSsApiSecret($api_secret);

        try {
            $this->getCarriers();
        } catch (Exception $ex) {
            $this->error = "Shipstation Error : " . $ex->getMessage();
            return false;
        }

        return true;
    }

}
