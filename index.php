<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        require_once('./TravelLineAPI.php');
        $travel = new TravelLineAPI();

/*#######################################################################################
=========================================================================================
----------------------------------------TESTS--------------------------------------------
=========================================================================================
#######################################################################################*/

/*----------------------HotelAvail()----------------------*/

/*Выводит весь список*/
//        $result = $travel->HotelAvail();


/*--------------------ReadReservation()-------------------*/

/*Выводит полный список*/
//        $result = $travel->ReadReservation();

/*выводит неполный список. Точнее, только сообщение об успехе. 
Списываю на счет отсутствия соответствующих запросу данных*/
//        $result = $travel->ReadReservation(array(
//            'SelectionType' => 'Undelivered'
//        ));

/*Вообще никак не влияет на результат. Скорее всего, я просто чего-то не понимаю*/
//        $result = $travel->ReadReservation(array(
////            'ReadRequest' => array('20150712-2690-671651')
//            'ReadRequest' => array('20150712-2690-671651', '20150709-2690-671648')
//        ));

/*Очень похоже, что все работает как надо*/
//        $result = $travel->ReadReservation(array(
////            'Start' => '2013-01-01'
//            'Start' => '2016-01-01'
//        ));

/*Результат приходит, но о фильтрации по типу ничего сказать не могу*/
        $result = $travel->ReadReservation(array(
            'Start' => '2012-01-02',
            'End' => '2016-01-02',
            'DateType' => 'LastUpdateDate'
        ));


/*-------------------CancelReservation()------------------*/
/*Если посылать несколько параметров помимо ID, ответа не приходит. Вообще никакого. Как и сообщений об ошибках.
Если посылать Amount или EchoTocken, то лезут ошибки из nusoap. Если посылать только ID, то какой-то ответ приходит.
Что-то не работает.*/
//        $result = $travel->CancelReservation(array(
//            
//            'ID' => '20150721-2690-672467'
//           
//        ));


/*----------------------NotifReport()---------------------*/

//        $result = $travel->NotifReport(array(
////          'HotelCode'=>'TRAVELLINE',
//            'EchoTocken' => 'echo',
//            'Status' => 'Success', //Success or Error
//            'Warnings' => array('warning1', 'warning2', 'warning3'),
//            'HotelReservations' => array(
///*Я хотел отдавать массив из таких массивов, но это не работает. Функция, которая строит из всего этого XML такое не съедает. Так что сейчас есть возможность отдавать только один элемент HotelReservation.*/
//                array(
//                    'ID' => '20150712-2690-671651q',
//                    'CreateDateTime'=>'2015-07-08T14:55:49.873',
//                    /*ResStatus знает только Reserved. Остальные значения выдают ошибку*/
//                    'ResStatus' => 'Reserved', //possible: Reserved, Cancelled, Checkedout, Inhouse, Requestdenied, Waitlisted,
//                    'LastModifyDateTime' => '2015-07-08T14:55:50.187',
//                    'RoomStays' => array(
//                        array('IndexNumber' => '705449')
//                    ),
//                    'ResGlobalInfo' => array(
//                        'ResID_Value' => 'ID1', //Здесь должен быть идентификатор брони в АСУ.
////                        'Comments' => 'Test' //Почему-то препятствует работе.
//                    )
//                ),
////                array(
////                    'ID' => '20150709-2690-671648',
////                    'CreateDateTime'=>'2015-07-08T14:53:55.723',
////                    'ResStatus' => 'Cancelled',
////                    'LastModifyDateTime' => '2015-07-08T14:53:56.097',
////                    'RoomStays' => array(
////                        'IndexNumber' => '705446'
////                    )
////                ),
////                array(
////                    'ID' => '123',
////                    'CreateDateTime'=>'2014-09-30T00:45:30+02:00',
////                    'ResStatus' => 'Checkedout',
////                    'LastModifyDateTime' => '2014-09-30T01:43:43.323'
////                )
//            )
//        ));


/*--------------------------------------------------------*/

        echo '<pre>';
        print_r($travel->result);
        echo '</pre>';
/*--------------------------------------------------------*/
        

/*###############################################################################################
=================================================================================================
-------------------------------------------SAMPLES-----------------------------------------------
=================================================================================================
###############################################################################################*/


/*------------------Cancel------------------*/

////Sample CancelReservation() argument
//    $cancelProp = array(
//        'EchoTocken' => 'echo',
//        'ID' => '123',
//        'Amount' => '25',
//        'CurrencyCode' => 'RUB'
//    );


/*------------------Notif--------------------------*/
////sample argument
//$notifProp = array(
//    'HotelCode'=>'TRAVELLINE',
//    'EchoTocken' => 'echo',
//    'Status' => 'Success', //Success or Error
//    'Warnings' => array('warning1', 'warning2', 'warning3'),
//    'HotelReservations' => array(
//        array(
//            'ID' => '20150712-2690-671651',
//            'CreateDateTime'=>'2015-07-08T14:55:49.873',
//            'ResStatus' => 'Reserved', //possible: Reserved, Cancelled, Checkedout, Inhouse, Requestdenied, Waitlisted,
//            'LastModifyDateTime' => '2015-07-08T14:55:50.187',
//            'RoomStays' => array(
//                'IndexNumber' => '705449'
//            ),
//            'ResGlobalInfo' => array(
//                'ResID_Value' => 'ID' //Здесь должен быть идентификатор брони в АСУ.
//            )
//        ),
//        array(
//            'ID' => '20150709-2690-671648',
//            'CreateDateTime'=>'2015-07-08T14:53:55.723',
//            'ResStatus' => 'Cancelled',
//            'LastModifyDateTime' => '2015-07-08T14:53:56.097',
//            'RoomStays' => array(
//                'IndexNumber' => '705446'
//            )
//        ),
//        array(
//            'ID' => '123',
//            'CreateDateTime'=>'2014-09-30T00:45:30+02:00',
//            'ResStatus' => 'Checkedout',
//            'LastModifyDateTime' => '2014-09-30T01:43:43.323'
//        )
//    )
//);


        ?>
    </body>
</html>