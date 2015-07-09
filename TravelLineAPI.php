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
        $client->decode_utf8 = false;
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

    public function CancelReservation($options) {
        if (!isset($options['ID'])) {
            return;
        }
        $params = array(
            'OTA_CancelRQ' => array(
                'version' => '1.0',
                'UniqueID' => array(
                    'ID' => $options['ID']
                )
            )
        );
        if (isset($options['EchoTocken'])) {
            $params['OTA_CancelRQ']['EchoToken'] = $options['EchoTocken'];
        }
        if (isset($options['Amount'])) {
            $params['OTA_CancelRQ']['CancellationOverrides'] = array(
                'CancellationOverride' => array(
                    'Amount' => $options['Amount']
                )
            );
            if (isset($options['CurrencyCode'])) {
                $params['OTA_CancelRQ']['CancellationOverrides']['CancellationOverride'] = $options['CurrencyCode'];
            }
        }
        $this->call('CancelRQ', $params);
    }

    public function NotifReport($options, $errors) {
        $hotelReservations = function() {
            $result = array();
            $hrCollection = $options['HotelReservations'];
            foreach ($hrCollection as $key => $value) {
                $result[$key] = array('HotelReservation' => array(
                        'CreateDateTime' => $value['CreateDateTime'],
                        'ResStatus' => $value['ResStatus'],
                        'LastModifyDateTime' => $value['LastModifyDateTime'],
                        'UniqueID' => array(
                            'Type' => 14,
                            'ID' => $value['ID']
                        )
                ));
                if (isset($value['RoomStays'])) {
                    foreach ($value['RoomStays'] as $roomStay => $roomStayProp) {
                        $result[$key]['HotelReservation']['RoomStays'] = array(
                            'RoomStay' => array(
                                'IndexNumber' => $roomStayProp['IndexNumber'],
                            )
                        );
                    }
                }
                if ($value['ResGlobalInfo']) {
                    $result[$key]['HotelReservation']['ResGlobalInfo'] = array(
                        'HotelReservationIDs' => array(
                            'HotelReservationID' => array(
                                'ResID_Type' => 14,
                                'ResID_Value' => $value['ResGlobalInfo']['ResID_Value']
                            )
                        )
                    );
                    if ($value['ResGlobalInfo']['Comments']) {
                        $result[$key]['HotelReservation']['ResGlobalInfo']['Comments'] = array(
                            'Comment' => array(
                                'Text' => $value['ResGlobalInfo']['Comments']
                            )
                        );
                    }
                }
            }
            unset($hrCollection);
            return $result;
        };

        $params = array(
            'OTA_NotifReportRQ' => array(
                'Version' => 1.0,
                'NotifDetails' => array(
                    'HotelNotifReport' => array(
                        'HotelReservations' => $hotelReservations
                    ),
                    'HotelCode' => $options['HotelCode'] //$this->hotelID //ID of hotel
                )
            ),
        );

        if (isset($options['EchoTocken'])) {
            $params['OTA_NotifReportRQ']['EchoToken'] = $options['EchoTocken'];
        }
        if ($options['Status'] === 'Success') {
            $params['OTA_NotifReportRQ'][] = 'Success';
        } elseif ($options['Status'] === 'Errors') {
            $params['OTA_NotifReportRQ']['Errors'] = $errors;
        }
        if (isset($options['Warnings'])) {
            $params['OTA_NotifReportRQ']['Warnings'] = $options['Warnings'];
        }

        $this->call('NotifReportRQ', $params);
    }

}
