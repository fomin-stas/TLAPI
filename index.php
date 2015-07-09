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

	$selectionCriteria = array();
//set criteria, if any
	$selectionCriteria = setSelectionCriteria(array(
		'SelectionType' => 'PreviouslyDelivered', //or PreviouslyDelivered Undelivered
		'Start' => '2015-11-01', //Date
		'End' => '2016-01-01', //Date
		'DateType' => 'CreateDate', //possible: ArrivalDate, CreateDate, DepartureDate, LastUpdateDate.
//		'ResStatus' => 'Confirmed' //or Cancelled
	));

		$param =array('OTA_ReadRQ' => 
            array('ReadRequests' =>
                array('HotelReadRequest' =>
                    array('SelectionCriteria' => $selectionCriteria,
                        'HotelCode' => 2690
                    )
                )
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
		return $selectionCriteria;
 	}


	function CancelReservation($options) {
		$params = array(
			'OTA_CancelRQ' => array(
				'version' => '1.0',
				'EchoToken' => 'echo test', //=>$options['EchoToken']
				'UniqueID' => array( 
					'ID' => '1003453543' //=>$options['ID']
				),
				'CancellationOverrides' => array(
					//there should be 0..n of this. Do not know, how to implemetn it simple
					'CancellationOverride' => array(
						'Amount' => '25',
						'CurrencyCode' => 'RUB'
					)
				)
			)
		);
		$this->call('HotelCancelReservationRQ', $params); //or something like that?
	}

function NotifReport($options) {
	$params = array(
		'OTA_NotifReportRQ' => array(
			'Version' => 1.0,
			'EchoToken' => 'echo test', //=>$options['EchoToken']
			'Success' => '', //or 'Errors'. $options['Success']
			'Warnings' => '', //string list of errors //$options['Warnings']
			'NotifDetails' => array(
				'HotelNotifReport' => array(
					'HotelReservations' => array(
						'HotelReservation' => array( //there could be multiple items with same key. Do not know, ho to do it simple
							'CreateDateTime' => '2014-09-30T00:45:30+02:00',
							'ResStatus' => 'Reserved', //Reserved, Cancelled, Checkedout, Inhouse, Requestdenied, Waitlisted
							'LastModifyDateTime' => '2014-09-30T01:43:43.323', //must be same as from OTA_ResRetrieveRS
							'UniqueID' => array(
								'Type' => '14',
								'ID' => '20150709-2690-671648' //идентификатр брони в канале
							),
							'RoomStays' => array(
								//same situation, as with HotelReservations
								'RoomStay' => array(
									'IndexNumber' => '705446' //from OTA_ResRetrieveRS.
								)
							),
							'ResGlobalInfo' => array(
								'HotelReservationIDs' => array(
									'HotelReservationID' => array(
										'ResID_Type' => '14',
										'ResID_Value' => '20150709-2690-671648' //�?дентификатор брони в АСУ
									)
								),
								'Comments' => array(
									'Comment' => array(
										'Text' => 'some text'
									)
								)
							)
						)
					)
			),
			'HotelCode' => 'TRAVELLINE' //$this->hotelID //ID of hotel
			)
		),
	);
	$this->call('HotelNotifReportRQ', $params); //or something like that?
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

