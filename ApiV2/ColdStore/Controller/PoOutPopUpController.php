<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';

class PoOutPopUpController
{
    private $conn;
    //private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $appController;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
        $this->appController = new AppUtilController($this->conn);
    }


    function getProductionlist($RequestBody)
    {
        $InvId = $RequestBody['InvId'];
        $mcsValues = json_decode($RequestBody['mcsValues'],true);

        $ClientID = $RequestBody['ClientID'];
        $IsCheckStockOutDateAtCs = $this->appController->getClientConfig($ClientID)['IsStockOutDateAtCs'];

        foreach ($mcsValues as $item) {
            $varietyID = $item['varietyId'];
            $mcsTotal = intval($item['mcsValue']); // Convert McsTotal to integer for safe addition
            $looseValue = intval($item['looseValue']); // Convert McsTotal to integer for safe addition
        
            // If the VarietyID exists in the $sums array, add to the existing sum
            if (array_key_exists($varietyID, $sums)) {
                $sums[$varietyID] += $mcsTotal;
                $looseSums[$varietyID] += $looseValue;
            } else {
                // If the VarietyID does not exist in the $sums array, initialize it
                $sums[$varietyID] = $mcsTotal;
                $looseSums[$varietyID] = $looseValue;
                $looseBagsArray[$varietyID] = 0;
            }
        }
        
        // Output the sums grouped by VarietyID
        foreach ($sums as $varietyID => $sum) {
           // echo "VarietyID: $varietyID, Sum of McsTotal: $sum\n";
        }

       // var_dump($sums);

        $sqlFields = "PGD.ID AS PoGradeId,Variety,PGD.Grade,CSP.PackingStyle,PGD.PackingStyleID,CSP.TotalSlabs,
        PGD.FreezingType,sum(Mcs) as Mcs,GlazePerc,Treatment,Brand,PGD.VarietyID ";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmPoGradeData` AS PGD 
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PCKID= PGD.PackingStyleID
       
        WHERE InvId = '$InvId' GROUP by VarietyID ORDER BY PGD.ID ASC"; // LEFT JOIN CsStockData AS CSD ON CSD.PoID = PGD.InvId

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        
        // $errorInfo = $stmt->errorInfo();
         //var_dump($RequestBody);

        $i = 0;
        $TotalMcs = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $TotalSlabs = $dataRow['TotalSlabs'];
            $PoGradeId = $dataRow['PoGradeId'];

            $ArrayStockData['SNo'] = ++$i;
            $ArrayStockData['PoGradeId'] = $PoGradeId;
            $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
            $VarietyID = $dataRow['VarietyID'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockData['OrderedMcs'] = $dataRow['Mcs'];
            $ArrayStockData['SelectedMcs'] = $sums[$VarietyID];
            $looseMcs = $looseSums[$VarietyID];
            $looseBags = $looseMcs % $TotalSlabs;
            $looseMcs = floor($looseMcs / $TotalSlabs);
            $ArrayStockData['SelectedLoose'] = $looseMcs;
            $ArrayStockData['looseBags'] = $looseBags;
            $looseBagsArray[$VarietyID] = $looseBags;
            $ArrayStockData['GlazePerc'] = $dataRow['GlazePerc'];
            $ArrayStockData['Treatment'] = $dataRow['Treatment'];
            $ArrayStockData['Brand'] = $dataRow['Brand'];

            $AryData[] = $ArrayStockData;
            $TotalMcs += $dataRow['Mcs'];
        }

        $sqlCustInvQuery = "SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$InvId'";
        $stmtCustInv = $this->conn->prepare($sqlCustInvQuery);
        $stmtCustInv->execute();
        $RowCustInvData = $stmtCustInv->fetch(PDO::FETCH_ASSOC);   

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $AryData;
        $Data['TotalMcs'] = $TotalMcs;
        $Data['LooseArray'] = $looseBagsArray;
        $Data['IsStockOutDateAtCs'] = $IsCheckStockOutDateAtCs;
        $Data['SaleStockOutDate'] = $RowCustInvData['StockOutDate'];

        return $Data;
    }

    // public function getBalanceKgs($TotalWeight, $CsStockOutID, $proddataRow)
    // {

    //     $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,
    //     sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs";

    //     $sqlQuery = "SELECT $sqlFields   from CsStockOutDetails as CSOTD 
    //     left join CsDailyProductionData as CSD on CSOTD.StockOutID=CSD.StockOutID  
    //     left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID where CSOTD.StockOutID='$CsStockOutID' ";

    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();

    //     $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
    //     $TotalWeight;
    //     $TotalWeight = round($TotalWeight - ($dataRow['Kgs'] + $dataRow['LKgs']), 2);
    //     $ArrayBalance['BalanceWeight'] = $TotalWeight;

    //     return  $ArrayBalance;
    // }
}
