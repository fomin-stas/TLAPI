<?php

/* ------------------------------------------------------------------------------
 * TLAccess - ????? ??????? ? API TravelLine
 * -----------------------------------------------------------------------------
 * __construct($username, $password, $HotelID) - ??????????? ???????????
 * @username - ????? ??? ??????? ? API ? TravelLine
 * @password - ?????? ??? ??????? ? API ? TravelLine
 * @HotelID - ID ????????? ? TravelLine
 */

class TLAccess {

    public function __construct($username, $password, $HotelID) {
        $this->travel = new TravelLineAPI($username, $password, $HotelID);
    }

    /* -------------------------------------------------------------------------
     * getCanceledReservation ??????? ????????? ??? ?????????? ????? ? ???????? 
     * ???????, ? ?????? ?????? ?????????? FALSE, ???? ?????? ?????? ???????, ?? 
     * ??????, ?????????? ??? ??????? ?????? ???, ?????????? TRUE, ?? ???? 
     * ?????????, ?????? ? ???????
     * -------------------------------------------------------------------------
     * @startDate = '2013-01-02' - ? ??????? ?????
     * @endDate = '2016-01-02' - ?? ?????? ?????
     */

    public function getCanceledReservation($startDate, $endDate) {
        $this->travel->ReadReservation(array(
            'Start' => $startDate,
            'End' => $endDate,
            'DateType' => 'LastUpdateDate',
            'ResStatus' => 'Cancelled'
        ));
        $result = $this->RRParsing($this->travel->result);
        if ($result === FALSE) {
            return FALSE;
        } elseif ($result === TRUE) {
            return TRUE;
        }
        return $result;
    }

    /* -------------------------------------------------------------------------
     * getUndelivered - ????????? ??? ?????????????? ?????, ? 
     * ?????? ?????? ?????????? FALSE, ???? ?????? ?????? ???????, ?? ??????, 
     * ?????????? ??? ??????? ?????? ???, ?????????? TRUE, ?? ???? ?????????, 
     * ?????? ? ???????
     * -------------------------------------------------------------------------
     */

    public function getUndelivered() {
        $this->travel->ReadReservation(array(
            'SelectionType' => 'Undelivered'
        ));
        $result = $this->RRParsing($this->travel->result);
        $result = $this->RRParsing($this->travel->result);
        if ($result === FALSE) {
            return FALSE;
        } elseif ($result === TRUE) {
            return TRUE;
        }
        return $result;
    }

    /* -------------------------------------------------------------------------
     * getLastUpdate - ????????? ??? ?????????? ????? ? ???????? ???????, ? 
     * ?????? ?????? ?????????? FALSE, ???? ?????? ?????? ???????, ?? ??????, 
     * ?????????? ??? ??????? ?????? ???, ?????????? TRUE, ?? ???? ?????????, 
     * ?????? ? ???????
     * -------------------------------------------------------------------------
     * @startDate = '2013-01-02' - ? ??????? ?????
     * @endDate = '2016-01-02' - ?? ?????? ?????
     */

    public function getLastUpdate($startDate, $endDate) {
        $this->travel->ReadReservation(array(
            'Start' => $startDate,
            'End' => $endDate,
            'DateType' => 'LastUpdateDate'
        ));
        $result = $this->RRParsing($this->travel->result);
        if ($result === FALSE) {
            return FALSE;
        } elseif ($result === TRUE) {
            return TRUE;
        }
        return $result;
    }
    
        /* -------------------------------------------------------------------------
     * getLastUpdate - ????????? ??? ?????????? ????? ? ???????? ???????, ? 
     * ?????? ?????? ?????????? FALSE, ???? ?????? ?????? ???????, ?? ??????, 
     * ?????????? ??? ??????? ?????? ???, ?????????? TRUE, ?? ???? ?????????, 
     * ?????? ? ???????
     * -------------------------------------------------------------------------
     * @startDate = '2013-01-02' - ? ??????? ?????
     * @endDate = '2016-01-02' - ?? ?????? ?????
     */

    public function getByCreateDate($startDate, $endDate) {
        $this->travel->ReadReservation(array(
            'Start' => $startDate,
            'End' => $endDate,
            'DateType' => 'CreateDate'
        ));
        $result = $this->RRParsing($this->travel->result);
        if ($result === FALSE) {
            return FALSE;
        } elseif ($result === TRUE) {
            return TRUE;
        }
        return $result;
    }

    /* -------------------------------------------------------------------------
     * setCancelReservation - ??????? ?????? ????? ?? ?? ID ? ??????, ? 
     * ??????????? ?? ?????????? ??? ???????????? ?????????? TRUE ??? FALSE
     * -------------------------------------------------------------------------
     * @IDReservation - ?????????? ????? ? ??????
     */

    public function setCancelReservation($IDReservation) {
        $this->travel->CancelReservation(array(
            'ID' => $IDReservation
        ));
        if (isset($this->travel->result['Success'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /* -------------------------------------------------------------------------
     * setAvailRooms - ????????????? ??????????? ???????, ? ???????????
     * ?? ?????????? ??? ???????????? ?????????? TRUE ??? FALSE
     * -------------------------------------------------------------------------
     * @IDTypeRoom - ?????????? ????? ???? ??????
     * @start - ? ???? 
     * @end - ?? ????
     * @roomStatus - bool (0 - ?? ????????, 1 - ????????)
     * @roomAvail - ?????????? ????????? ???????
     */

    public function setAvailRooms($IDTypeRoom, $start, $end, $roomStatus) {
        $status = $roomStatus ? 'Open' : 'Close';
        $this->travel->setAvailRooms($IDTypeRoom, $start, $end, $status);
        if (isset($this->travel->result['Success'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /* -------------------------------------------------------------------------
     * RRParsing - ??????? ??????? ?????? ? ReadReservation, ????????? ?????? 
     * ??????? ? ???????????????? ???????, ???? ??????, ?????????? 0
     * -------------------------------------------------------------------------
     * @respons - ????? ? ??????? ?? ?????? ReadReservation
     */

    private function RRParsing($respons) {
        if (!isset($respons['Success'])) {
            return FALSE;
        }
        $result = array();
        if (!isset($respons['ReservationsList']['HotelReservation'])) {
            return TRUE;
        }
        $rooms = $respons['ReservationsList']['HotelReservation'];
        foreach ($rooms as $key => $value) {
            if (!is_int($key)) {
                $value = $rooms;
                $result[0]['IDReservation'] = $value['UniqueID']['!ID'];
                $result[0]['RoomTypeCode'] = $value['RoomStays']['RoomStay']['RoomTypes']['RoomType']['!RoomTypeCode'];
                $result[0]['Quantity'] = $value['RoomStays']['RoomStay']['RoomTypes']['RoomType']['!Quantity'];
                $result[0]['Start'] = $value['RoomStays']['RoomStay']['TimeSpan']['!Start'];
                $result[0]['End'] = $value['RoomStays']['RoomStay']['TimeSpan']['!End'];
                $result[0]['Duration'] = $value['RoomStays']['RoomStay']['TimeSpan']['!Duration'];
                $result[0]['Duration'] = $value['RoomStays']['RoomStay']['TimeSpan']['!Duration'];
                $result[0]['Status'] = $value['!ResStatus'];
                $result[0]['Persone'] = $value['ResGlobalInfo']['Profiles']['ProfileInfo']['Profile']['Customer']['PersonName'];
                $result[0]['Persone']['PhoneNumber'] = $value['ResGlobalInfo']['Profiles']['ProfileInfo']['Profile']['Customer']['Telephone']['!PhoneNumber'];
                $result[0]['Persone']['Email'] = $value['ResGlobalInfo']['Profiles']['ProfileInfo']['Profile']['Customer']['Email'];
                break;
            }
            $result[$key]['IDReservation'] = $value['UniqueID']['!ID'];
            $result[$key]['RoomTypeCode'] = $value['RoomStays']['RoomStay']['RoomTypes']['RoomType']['!RoomTypeCode'];
            $result[$key]['Quantity'] = $value['RoomStays']['RoomStay']['RoomTypes']['RoomType']['!Quantity'];
            $result[$key]['Start'] = $value['RoomStays']['RoomStay']['TimeSpan']['!Start'];
            $result[$key]['End'] = $value['RoomStays']['RoomStay']['TimeSpan']['!End'];
            $result[$key]['Duration'] = $value['RoomStays']['RoomStay']['TimeSpan']['!Duration'];
            $result[$key]['Duration'] = $value['RoomStays']['RoomStay']['TimeSpan']['!Duration'];
            $result[$key]['Status'] = $value['!ResStatus'];
            $result[$key]['Persone'] = $value['ResGlobalInfo']['Profiles']['ProfileInfo']['Profile']['Customer']['PersonName'];
            $result[$key]['Persone']['PhoneNumber'] = $value['ResGlobalInfo']['Profiles']['ProfileInfo']['Profile']['Customer']['Telephone']['!PhoneNumber'];
            $result[$key]['Persone']['Email'] = $value['ResGlobalInfo']['Profiles']['ProfileInfo']['Profile']['Customer']['Email'];
        }
        return $result;
    }

    /* -------------------------------------------------------------------------
     * NotifReportSuccess - ????????????? ????????? ????? ? ??????
     * ?? ?????????? ??? ???????????? ?????????? TRUE ??? FALSE
     * -------------------------------------------------------------------------
     * @createdDate - ???? ?????????? ? ???
     * @ResStatus - ????? ????????? ????????: Reserved ? ????? ???????;
     * Cancelled ? ????? ????????;Checkedout ? ????? ??? ???????;Inhouse ? ?????
     * ?????????;Requestdenied ? ????? ?? ???????;Waitlisted ? ????? ???????, ??
     * ??????? ?????? ????????? ??????????????? ???. 
     * @LastModifyDateTime - ?????? ????????? ? LastModifyDateTime ??? ??????
     * @ID - ????????????????? ????? ????? ? ??????
     * @IDASU - ????????????????? ????? ????? ? ???
     */

    public function NotifReportSuccess($createdDate, $ResStatus, $LastModifyDateTime, $ID, $IDASU) {
        $this->travel->NotifReportSuccess($createdDate, $ResStatus, $LastModifyDateTime, $ID, $IDASU);
        if (isset($this->travel->result['Success'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function getAllAvailRooms(){
        $this->travel->HotelAvail();
        if (isset($this->travel->result['Success'])) {
            return $this->travel->result;
        } else {
            return FALSE;
        }
    }

}

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
    private $hotelID;
    public $result;
    public $error;

    public function __construct($username, $password, $HotelID) {
        require_once("./lib/nusoap.php");
        $this->hotelID = $HotelID;
        $proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
        $proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
        $proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
        $proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
        $this->SoapAuth = new SoapAuth($username, $password);
        $this->soapServerURL = 'https://www.qatl.ru/Api/TLConnect.svc?singleWsdl';
        $this->client = new nusoap_client($this->soapServerURL, 'wsdl', $proxyhost, $proxyport, $proxyusername, $proxypassword);
        $this->client->soap_defencoding = 'UTF-8';
        $this->client->decode_utf8 = false;
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
        $selectionCriteria = array();
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
        if (isset($options['ReadRequest'])) {
            foreach ($options['ReadRequest'] as $id) {
                $param['ReadRequest'] = array(
                    'UniqueID' => array(
                        "ID" => $id
                    )
                );
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

    public function ReadReservation($selection = array()) {
        $selectionCriteria = $this->setSelectionCriteria($selection);
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

    public function CancelReservation($options) {
        if (!isset($options['ID'])) {
            echo('test');
            return;
        }
        $params = array('OTA_CancelRQ' => array(
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

    public function NotifReportSuccess($createdDate, $ResStatus, $LastModifyDateTime, $ID, $IDASU, $options = array()) {
        $params = array(
            'OTA_NotifReportRQ' => array(
                'Version' => 1.0,
                'Success' => array(),
                'NotifDetails' => array(
                    'HotelNotifReport' => array(
                        'HotelReservations' => array(
                            'HotelReservation' => array(
                                'CreateDateTime' => $createdDate,
                                'ResStatus' => $ResStatus,
                                'LastModifyDateTime' => $LastModifyDateTime,
                                'UniqueID' => array(
                                    'Type' => 14,
                                    'ID' => $ID
                                ),
                                'ResGlobalInfo' => array(
                                    'HotelReservationIDs' => array(
                                        'HotelReservationID' => array(
                                            'ResID_Type' => 14,
                                            'ResID_Value' => $IDASU
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    'HotelCode' => $this->hotelID //ID of hotel //$options['HotelCode']
                )
            ),
        );
        if (isset($options['EchoTocken'])) {
            $params['OTA_NotifReportRQ']['EchoToken'] = $options['EchoTocken'];
        }
        if (isset($options['Warnings'])) {
            $params['OTA_NotifReportRQ']['Warnings'] = $options['Warnings'];
        }

        $this->call('NotifReportRQ', $params);
    }

    public function setAvailRooms($IDTypeRoom, $start, $end, $status) {
        $params = array(
            'OTA_HotelAvailNotifRQ' => array(
                'Version' => 1.0,
                'AvailStatusMessages' => array(
                    'AvailStatusMessage' => array(
                        'StatusApplicationControl' => array(
                            'Start' => $start,
                            'End' => $end,
                            'InvTypeCode' => $IDTypeRoom
                        ),
                        'RestrictionStatus' => array(
                            'Status' => $status
                        )
                        
                    ),
                    'HotelCode' => $this->hotelID //ID of hotel //$options['HotelCode']
                )
            ),
        );
        $this->call('HotelAvailNotifRQ', $params);
    }

}
