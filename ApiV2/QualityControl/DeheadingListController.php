
<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class DeheadingListController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    function getBatchData($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $sqlVehicleFields = "ArrivalNumber,RmArrivalID,RRD.ReceivingID,VehicalID,VehicalNumber ,ArrivalStationID as CenterID,
        ProcessingStationID as PlantID,TrackCode,RRB.ReceivingCount,RRB.ReceivingQty";
        // $sqlVehicle = "SELECT $sqlVehicleFields FROM RmArrivalDetatils AS RAD
        $sqlVehicle = "SELECT $sqlVehicleFields FROM RmReceivingBreakup As RRB 
		LEFT JOIN RmArrivalDetatils as RAD on RAD.RmArrivalID=RRB.ArrivalID 
        LEFT JOIN RmVehicalDetails AS RVD ON RAD.VehicalID=RVD.ID
        LEFT JOIN RmReceivingDetails AS RRD ON RAD.ArrivalDate=RRD.ArrivalDate AND RAD.VehicalID = RRD.VehicleID  
        AND RAD.ProcessingStationID = RRD.ReceivingPlantID 
        WHERE  RAD.ArrivalDate='$ArrivalDate'AND LotStatus =1";

        // echo $sqlVehicle;
        // exit;

        $stmt = $this->conn->prepare($sqlVehicle);
        $stmt->execute();

        $vehicleData = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayVehicleData['VehicleID'] = $dataRow['VehicalID'] ? $dataRow['VehicalID'] : '0';
            $ArrayVehicleData['VehicalNumber'] = $dataRow['VehicalNumber'] ? $dataRow['VehicalNumber'] : 'Others';
            $ArrayVehicleData['ArrivalNumber'] = $dataRow['ArrivalNumber'] ? $dataRow['ArrivalNumber'] : '';
            $ArrayVehicleData['ReceivingCount'] = $dataRow['ReceivingCount'] ? $dataRow['ReceivingCount'] : '';
             $ArrayVehicleData['ReceivingQty'] = $dataRow['ReceivingQty'] ? $dataRow['ReceivingQty'] : '';
            $ArrayVehicleData['ArrivalId'] = $dataRow['RmArrivalID'];
            $vehicleData[] = $ArrayVehicleData;
        }

        //  echo $dataRow;
        // exit;
        return $vehicleData;
    }
    function getReportsBatchData($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $sql = "SELECT DeheadingID,BatchCode,ArrivalNumber  FROM DeheadingCheckList WHERE ArrivalDate='$ArrivalDate' GROUP BY ArrivalNumber" ;
        

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $ReportsBatchData = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayReportsBatchData['DeheadingID'] = $dataRow['DeheadingID'] ? $dataRow['DeheadingID'] : '';
            $ArrayReportsBatchData['BatchCode'] = $dataRow['BatchCode'] ? $dataRow['BatchCode'] : '';
            $ArrayReportsBatchData['ArrivalNumber'] = $dataRow['ArrivalNumber'] ? $dataRow['ArrivalNumber'] : '';
            $ReportsBatchData[] = $ArrayReportsBatchData;
        }

        //  echo $dataRow;
        // exit;
        return $ReportsBatchData;
    }

    function getDeheadingListData($RequestBody)
    {


        $MethodType = $RequestBody['MethodType'];

        if ($MethodType == 'BATCH_NUMBERS') {
            $Data['BatchData'] = $this->getBatchData($RequestBody);
            $responseData['Error'] = '';
            $responseData['Msg'] = '';
            $responseData['Data'] = $Data;

            return $responseData;

        }
        if($MethodType == 'REPORT_BATCH_NUMBERS'){
            $Data['BatchData'] = $this->getReportsBatchData($RequestBody);
            $responseData['Error'] = '';
            $responseData['Msg'] = '';
            $responseData['Data'] = $Data;
            return $responseData;
        }

        $ArrivalDate = $RequestBody['ArrivalDate'];
        $BatchCode = $RequestBody['BatchCode'];
        

        $Query ='';

        
        if($BatchCode != ''){
            $Query .= 'AND DCL.BatchCode IN('.$BatchCode.')';
        }


        $sql = "SELECT *,DCL.ArrivalNumber AS ArrivalNumbers,DCL.ArrivalDate AS adate  FROM DeheadingCheckList AS DCL
        LEFT JOIN RmArrivalDetatils as AR on AR.RmArrivalID=DCL.BatchCode WHERE DCL.ArrivalDate='$ArrivalDate' $Query" ;
        

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $i = 0;
        $DeheadingData = [];
        $totalSendingQty = 0;
        $totalReceivingQty = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $DeheadingList['SNO'] = ++$i;
            $DeheadingList['DeheadingID'] = $row['DeheadingID'];
            $DeheadingList['BatchCode'] = $row['BatchCode'];
            $DeheadingList['BatchCodeName'] = $row['ArrivalNumbers'];
            //  $DeheadingList['BatchCodeNumber'] = explode(",", $row['BatchCode']);
            $DeheadingList['Variety'] = $row['Variety'];
            $DeheadingList['Count'] = $row['Count'];
            $DeheadingList['ReceivingQuantity'] = $row['ReceivingQuantity'];
            $DeheadingList['GeneralAppearance'] = $row['GeneralAppearance'];
            $DeheadingList['Colour'] = $row['Colour'];
            $DeheadingList['Freshness'] = $row['Freshness'];
            $DeheadingList['Odour'] = $row['Odour'];
            $DeheadingList['Texture'] = $row['Texture'];
            $DeheadingList['Temperature'] = $row['Temperature'];
            $DeheadingList['Deterioration'] = $row['Deterioration'];
            $DeheadingList['SendingQuantity'] = $row['SendingQuantity'];
            $DeheadingList['Discolouration'] = $row['Discolouration'];
            $DeheadingList['BlackSpots'] = $row['BlackSpots'];
            $DeheadingList['SoftShell'] = $row['SoftShell'];
            $DeheadingList['BrokenDamaged'] = $row['BrokenDamaged'];
            $DeheadingList['ReceivingTime'] = $row['ReceivingTime'];
            $DeheadingList['SendingTime'] = $row['SendingTime'];
            $DeheadingList['ForeignMeterial'] = $row['ForeignMeterial'];
            $DeheadingList['ArrivalDate'] = $row['adate'];
            $DeheadingList['VerifiedBy'] = $row['VerifiedBy'];
            $DeheadingList['InspectedBy'] = $row['InspectedBy'];
            $DeheadingList['HangingMeat'] = $row['HangingMeat'];
            $DeheadingList['AttachedLegs'] = $row['AttachedLegs'];
            $totalSendingQty +=  $row['SendingQuantity'];
            $totalReceivingQty += $row['ReceivingQuantity'];




            $DeheadingData[] = $DeheadingList;
        }
        $Totals = [];
        $Totals['SendingQuantity'] = $totalSendingQty;
        $Totals['ReceivingQuantity'] = $totalReceivingQty;

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['DeheadingData'] = $DeheadingData;
        $data['Totals'] = $Totals;
         $data['sql'] = $BatchCode;

        return $data;

        
    }

    public function postDeheadingListData($RequestBody)
    {
        // echo print_r($RequestBody);
        // exit;


        $DeheadingID = $RequestBody['Deheadingid'];
        $BatchCode = $RequestBody['BatchCode'];
        $ArrivalNumber = $RequestBody['ArrivalNumber'];
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $Variety = $RequestBody['Variety'];
        $Count = $RequestBody['Count'];
        $ReceivingQuantity = $RequestBody['ReceivingQuantity'];
        $ReceivingTime = $RequestBody['ReceivingTime'];
        $GeneralAppearance = $RequestBody['GeneralAppearance'];
        $Colour = $RequestBody['Colour'];
        $Freshness = $RequestBody['Freshness'];
        $Odour = $RequestBody['Odour'];
        $Texture = $RequestBody['Texture'];
        $Temperature = $RequestBody['Temperature'];
        $Deterioration = $RequestBody['Deterioration'];
        $SendingQuantity = $RequestBody['SendingQuantity'];
        $Discolouration = $RequestBody['Discolouration'];
        $BlackSpots = $RequestBody['BlackSpots'];
        $SoftShell = $RequestBody['SoftShell'];
        $BrokenDamaged = $RequestBody['BrokenDamaged'];
        $ForeignMeterial = $RequestBody['ForeignMeterial'];
        $SendingTime = $RequestBody['SendingTime'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $InspectedBy = $RequestBody['InspectedBy'];
        $HangingMeat = $RequestBody['HangingMeat'];
         $AttachedLegs = $RequestBody['AttachedLegs'];

//  echo print_r($RequestBody);exit;
        if (!empty($DeheadingID)) {
            $sqlQuery = "UPDATE `DeheadingCheckList` SET
        `BatchCode` = '$BatchCode',
        `ArrivalNumber` = '$ArrivalNumber',
    `Variety` = '$Variety',
     `ArrivalDate` = '$ArrivalDate',
    `Count` = '$Count',
    `ReceivingQuantity` = '$ReceivingQuantity',
    `ReceivingTime` = '$ReceivingTime',
    `GeneralAppearance` = '$GeneralAppearance',
    `Colour` = '$Colour',
    `Freshness` = '$Freshness',
    `Odour` = '$Odour',
    `Texture` = '$Texture',
    `Temperature` = '$Temperature',
    `Deterioration` = '$Deterioration',
    `SendingQuantity` = '$SendingQuantity',
    `Discolouration` = '$Discolouration',
    `BlackSpots` = '$BlackSpots',
    `SoftShell` = '$SoftShell',
    `BrokenDamaged` = '$BrokenDamaged',
    `ForeignMeterial` = '$ForeignMeterial',
    `SendingTime` = '$SendingTime',
    `VerifiedBy` = '$VerifiedBy',
    `InspectedBy` = '$InspectedBy',
    `HangingMeat` = '$HangingMeat',
    `AttachedLegs` = '$AttachedLegs'
    
    
        WHERE DeheadingID = '$DeheadingID'";


            // echo print_r($sqlQuery);
            // exit;

            try {
                $stmt = $this->conn->prepare($sqlQuery);
                $result = $stmt->execute();

                if ($result) {
                    return [
                        'Error' => '',
                        'Msg' => 'Data Updated successfully',
                        'Data' => $DeheadingID
                    ];
                } else {
                    $errorInfo = $stmt->errorInfo();
                    return [
                        'Error' => ' Update failed: ' . $errorInfo[2],
                        'Msg' => 'Failed to Update record',
                        'Data' => null
                    ];
                }
            } catch (PDOException $e) {
                return [
                    'Error' => 'Exception: ' . $e->getMessage(),
                    'Msg' => 'Failed to Update record',
                    'Data' => null
                ];
            }
        } else {
            $sqlQuery = "INSERT INTO DeheadingCheckList (
    ArrivalDate, BatchCode, ArrivalNumber, Variety, Count, ReceivingQuantity, GeneralAppearance, ReceivingTime,
    Colour, Freshness, Odour, Texture, Temperature, Deterioration, SendingQuantity,
    Discolouration, BlackSpots, SoftShell, BrokenDamaged, ForeignMeterial, SendingTime, VerifiedBy, InspectedBy,HangingMeat,AttachedLegs
) VALUES (
    '$ArrivalDate', '$BatchCode', '$ArrivalNumber', '$Variety', '$Count', '$ReceivingQuantity', '$GeneralAppearance', '$ReceivingTime',
    '$Colour', '$Freshness', '$Odour', '$Texture', '$Temperature', '$Deterioration', '$SendingQuantity',
    '$Discolouration', '$BlackSpots', '$SoftShell', '$BrokenDamaged', '$ForeignMeterial', '$SendingTime', '$VerifiedBy', '$InspectedBy','$HangingMeat','$AttachedLegs'
)";


try {
    $stmt = $this->conn->prepare($sqlQuery);
    $result = $stmt->execute();
    // print_r([
    //     $ArrivalDate, $BatchCode, $ArrivalNumber, $Variety, $Count, $ReceivingQuantity, $GeneralAppearance, $ReceivingTime,
    //     $Colour, $Freshness, $Odour, $Texture, $Temperature, $Deterioration, $SendingQuantity,
    //     $Discolouration, $BlackSpots, $SoftShell, $BrokenDamaged, $ForeignMeterial, $SendingTime, $VerifiedBy, $InspectedBy
    // ]);exit;

                if ($result) {
                    return [
                        'Error' => '',
                        'Msg' => 'Data saved successfully',
                        'Data' => $this->conn->lastInsertId()
                    ];
                } else {
                    $errorInfo = $stmt->errorInfo();
                    return [
                        'Error' => 'Insert failed: ' . $errorInfo[2],
                        'Msg' => 'Failed to insert record',
                        'Data' => null
                    ];
                }
            } catch (PDOException $e) {
                return [
                    'Error' => 'Exception: ' . $e->getMessage(),
                    'Msg' => 'Failed to insert record',
                    'Data' => null
                ];
            }
        }
    }
    public function deleteDeheadingListData($RequestBody)
  {
   
    $DeheadingID = $RequestBody['Deheadingid'];

     
    $sqlDelete = "DELETE FROM `DeheadingCheckList` WHERE DeheadingID = $DeheadingID";

    // echo ($sqlDelete);
    // exit;
    try {
        $stmt = $this->conn->prepare($sqlDelete);
        $result = $stmt->execute();

        if ($result) {
            return [
                'Error' => '',
                'Msg' => 'Data deleted successfully',
                'Data' => $DeheadingID
            ];
        } else {
            $errorInfo = $stmt->errorInfo();
            return [
                'Error' => 'Delete failed: ' . $errorInfo[2],
                'Msg' => 'Failed to delete record',
                'Data' => null
            ];
        }
    } catch (PDOException $e) {
        return [
            'Error' => 'Exception: ' . $e->getMessage(),
            'Msg' => 'Failed to delete record',
            'Data' => null
        ];
    }
       
    }
  
}
