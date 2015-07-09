<?php

class SoapAuth {

    public $Security;

    public function __construct($username, $password) {
        $Security = new stdClass();
        $Security->Username = $username;
        $Security->Password = $password;
    }

}

class TravelLineAPI {

    private $SoapAuth;
    private $client;
    private $selectionCriteria = array();
    private $hotelID;
    public $result;
    public $error;

    public function __construct($username, $password, $HotelID) {
        require_once('./lib/nusoap.php');
        $this->hotelID = $HotelID;
        $proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
        $proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
        $proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
        $proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
        $this->SoapAuth = new SoapAuth($username, $password);
        $this->soapServerURL = 'https://www.qatl.ru/Api/TLConnect.svc?singleWsdl';
        $this->client = new nusoap_client($this->soapServerURL, 'wsdl', $proxyhost, $proxyport, $proxyusername, $proxypassword);
        $this->client->soap_defencoding = 'UTF-8';
        $namespaces = $this->client->namespaces;
        $namespaces['ns2'] = "https://www.travelline.ru/Api/TLConnect";
        $this->client->namespaces = $namespaces;
        $this->client->setHeaders('<ns2:Security Username="' . $username . '" Password="' . $password . '" />');
    }

    private function call($metod, $params) {
        $this->result = $this->client->call($metod, $params);

        if ($this->client->fault) {
            $this->error = 'Error fault. See $result.';
            return 0;
        } else {
            $this->error = $this->client->getError();
            if ($this->error) {
                return 0;
            } else {
                $this->error = '';
                return 1;
            }
        }
    }

    private function setSelectionCriteria($options) {
        //white list of criteria
        $criteria = array(
            'SelectionType',
            'Start',
            'End',
            'DateType',
            'ResStatus'
        );
        foreach ($criteria as $key) {
            if (isset($options[$key])) {
                $selectionCriteria[$key] = $options[$key];
            }
        }
        return $selectionCriteria;
    }

    public function HotelAvail() {
        $params = array('OTA_HotelAvailRQ' =>
            array('AvailRequestSegments' =>
                array('AvailRequestSegment' =>
                    array('HotelSearchCriteria' =>
                        array('Criterion' =>
                            array('HotelRef' =>
                                array('HotelCode' => $this->hotelID)
                            )
                        )
                    )
                )
            ),
            'Version' => '1.0',
            'TimeStamp' => time()
        );
        $this->call('HotelAvailRQ', $params);
    }

    public function ReadReservation($selection) {
        if (isset($selection)) {
            $params = array('OTA_ReadRQ' =>
                array('ReadRequests' =>
                    array('HotelReadRequest' =>
                        array('SelectionCriteria' => $selectionCriteria,
                            'HotelCode' => $this->hotelID
                        )
                    )
                ),
                'Version' => '1.0'
            );
            $this->call('HotelReadReservationRQ', $params);
        }
    }

}
