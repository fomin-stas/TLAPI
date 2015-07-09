<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
//        require_once('./TravelLineAPI.php');
//        $travel = new TravelLineAPI("TlConnect.2690","KjlQbU53Kigv",2690);
//        $result = $travel->HotelAvail();
//        print_r($travel->result);
                
        require_once('./lib/nusoap.php');
        $proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
        $proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
        $proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
        $proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';

        $client = new nusoap_client('https://www.qatl.ru/Api/TLConnect.svc?singleWsdl', 'wsdl', $proxyhost, $proxyport, $proxyusername, $proxypassword);
        $client->soap_defencoding = 'UTF-8';
        $namespaces = $client->namespaces;
        $namespaces['ns2'] = "https://www.travelline.ru/Api/TLConnect";
        $client->namespaces = $namespaces;
        $namespaces2 = $client->namespaces;
        $client->setHeaders('<ns2:Security Username="TlConnect.2690" Password="KjlQbU53Kigv" />');
        

/*###############################################################################################
=================================================================================================
-------------------------------------------------------------------------------------------------
=================================================================================================
###############################################################################################*/
    $client->decode_utf8 = false; //magic
	$selectionCriteria = array();
//  set criteria, if any
	setSelectionCriteria(array(
        'ReadRequest' => array('20150712-2690-671651', '20150709-2690-671648'),
		'SelectionType' => 'Undelivered', //or PreviouslyDelivered
		'Start' => '2013-01-01', //Date
		'End' => '2016-01-01', //Date
		'DateType' => 'ArrivalDate', //possible: ArrivalDate, CreateDate, DepartureDate, LastUpdateDate.
//		'ResStatus' => 'Confirmed' //or Cancelled
	));

		$param =array('OTA_ReadRQ' => 
            array('ReadRequests' =>
                array('HotelReadRequest' =>
                    array('SelectionCriteria' => $selectionCriteria,
                        'HotelCode' => 2690
                    )
                ),
            ),
            'Version' => '1.0'
        );
        $result = $client->call('HotelReadReservationRQ', $param);


	function setSelectionCriteria ($options) {
		//white list of criteria
		$criteria = array(
			'SelectionType',
			'Start',
			'End',
			'DateType',
			'ResStatus'
		);
		foreach ($criteria as $key){
			if (isset($options[$key])){
				$selectionCriteria[$key] = $options[$key];
			}
		}
        if(isset($options['ReadRequest'])){
            foreach($options['ReadRequest'] as $id){
                $param['ReadRequest'] = array(
                    'UniqueID' => array(
                        "ID" => $id
                    )
                );
            }
        }
		return $selectionCriteria;
 	}

/*------------------Cancel------------------*/

//Sample CancelReservation() argument
    $cancelProp = array(
        'EchoTocken' => 'echo',
        'ID' => '123',
        'Amount' => '25',
        'CurrencyCode' => 'RUB'
    );
//main function
    function CancelReservation($options) {
        if(!isset($options['ID'])){
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
        if(isset($options['EchoTocken'])){
            $params['OTA_CancelRQ']['EchoToken'] = $options['EchoTocken'];
        }
        if(isset($options['Amount'])){
            $params['OTA_CancelRQ']['CancellationOverrides'] = array(
                'CancellationOverride' => array(
                    'Amount' => $options['Amount']
                )
            );
            if(isset($options['CurrencyCode'])){
                $params['OTA_CancelRQ']['CancellationOverrides']['CancellationOverride'] = $options['CurrencyCode'];
            }
        }
		$this->call('CancelRQ', $params); //or something like that?
	}



/*------------------Notif--------------------------*/
//sample argument
$notifProp = array(
    'HotelCode'=>'TRAVELLINE',
    'EchoTocken' => 'echo',
    'Status' => 'Success', //Success or Error
    'Warnings' => array('warning1', 'warning2', 'warning3'),
    'HotelReservations' => array(
        array(
            'ID' => '20150712-2690-671651',
            'CreateDateTime'=>'2015-07-08T14:55:49.873',
            'ResStatus' => 'Reserved', //possible: Reserved, Cancelled, Checkedout, Inhouse, Requestdenied, Waitlisted,
            'LastModifyDateTime' => '2015-07-08T14:55:50.187',
            'RoomStays' => array(
                'IndexNumber' => '705449'
            ),
            'ResGlobalInfo' => array(
                'ResID_Value' => 'ID' //Здесь должен быть идентификатор брони в АСУ.
            )
        ),
        array(
            'ID' => '20150709-2690-671648',
            'CreateDateTime'=>'2015-07-08T14:53:55.723',
            'ResStatus' => 'Cancelled',
            'LastModifyDateTime' => '2015-07-08T14:53:56.097',
            'RoomStays' => array(
                'IndexNumber' => '705446'
            )
        ),
        array(
            'ID' => '123',
            'CreateDateTime'=>'2014-09-30T00:45:30+02:00',
            'ResStatus' => 'Checkedout',
            'LastModifyDateTime' => '2014-09-30T01:43:43.323'
        )
    )
);

//function
function NotifReport($options, $errors) {
    
    $hotelReservations = function(){
        $result = array();
        $hrCollection = $options['HotelReservations'];
        foreach($hrCollection as $key=>$value){
            $result[$key] = array('HotelReservation' => array(
                'CreateDateTime' => $value['CreateDateTime'],
                'ResStatus' => $value['ResStatus'],
                'LastModifyDateTime' => $value['LastModifyDateTime'],
                'UniqueID' => array(
                    'Type' => 14,
                    'ID' => $value['ID']
                )
            ));
            if(isset($value['RoomStays'])){
                foreach($value['RoomStays'] as $roomStay=>$roomStayProp){
                    $result[$key]['HotelReservation']['RoomStays'] = array(
                        'RoomStay' => array(
                            'IndexNumber' => $roomStayProp['IndexNumber'],
                        )
                    );
                }
            }
            if($value['ResGlobalInfo']){
                $result[$key]['HotelReservation']['ResGlobalInfo'] = array(
                    'HotelReservationIDs' => array(
                        'HotelReservationID' => array(
                            'ResID_Type' => 14,
                            'ResID_Value' => $value['ResGlobalInfo']['ResID_Value']
                        )
                    )
                );
                if ($value['ResGlobalInfo']['Comments']){
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
    
    if(isset($options['EchoTocken'])){
        $params['OTA_NotifReportRQ']['EchoToken'] = $options['EchoTocken'];
    }
    if($options['Status'] === 'Success'){
        $params['OTA_NotifReportRQ'][] = 'Success';
    } elseif($options['Status'] === 'Errors'){
        $params['OTA_NotifReportRQ']['Errors'] = $errors;
    }
    if(isset($options['Warnings'])){
        $params['OTA_NotifReportRQ']['Warnings'] = $options['Warnings'];
    }
    
	$this->call('NotifReportRQ', $params); //or something like that?
}

/*###################################################################################################
=====================================================================================================
-----------------------------------------------------------------------------------------------------
=====================================================================================================
###################################################################################################*/

        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            } else {
                // Display the result
                echo '<h2>Result</h2><pre>';
                print_r($result);
                echo '</pre>';
            }
        }
        

        ?>
    </body>
</html>