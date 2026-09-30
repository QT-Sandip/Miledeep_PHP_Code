<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class RealizationDoneController
{
    private $conn;
    private $db_table = "ScrmPIData";
    private $utilController;



    // Db connection 
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);

    }
    // GET ALL
    public function GetRealizationDoneDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $Year = $RequestBody['Year'];
        $ExporterID = $RequestBody['ExporterID'];
        $Type = $RequestBody['Type'];

        $Buyer = $RequestBody['Buyer'];
        $Month = $RequestBody['Month'];
        $Buyer = $RequestBody['Buyer'];
        $Brand = $RequestBody['Brand'];
        $Country = $RequestBody['Country'];
        $Processor = $RequestBody['Processor'];

        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DashBoard = $RequestBody['DashBoard'];



        //$DateFilter = $this->getFinancialYear($Year);
        $DateFilter = $this->utilController->getFinancialYear($Year);
        if($DashBoard){
            if($Year){
                $FromDate = $Year.'-04-01';
				$ToDate = ($Year+1) .'-03-31';
            }
            $DateFilter = " BETWEEN '$FromDate' and '$ToDate' ";
        }

        $Query = '';

        if ($ExporterID != "") {
            $Query .= ' and POD.ExporterID in (' . $ExporterID . ') ';
        }
        if ($Type != "") {
            $Query .= ' and SCP.Type in (' . $Type . ') ';
        }


        if ($Processor != "") {
            $Query .= ' and POD.ProcessorID=' . $Processor.' ';
        }
        if ($Country != "") {
            $Query .= ' and POD.DestinationCountry=' . $Country.' ';
        }

        if ($Buyer != "") {
            $Query .= ' and POD.ConsigneeID=' . $Buyer.' ';
        }

        if ($Brand != '') {
            $Query .= " and POD.PoID  in (SELECT DISTINCT(InvId) FROM `ScrmPoGradeData` WHERE Brand='$Brand')";
        }

        // getCollections();
// getNegotiations();
        $SqlFieldsList = "
		 POD.PoID,
		 SCI.CustomsInvNo,
		 SCI.CInvDate,
		 SCI.PaymentType,
		 SCP.BnkChrges,
		 SCP.OtherAmount,
		 SCP.EtraAmount,
         SRD.RezStatus,
         SRD.FBKCharges,
         SRD.OtherCharges,
		 POD.PoNO,
         POD.ConsigneeID,
         SCD.ConsigneeName,
         (SELECT sum(UtilizedValue)  FROM `ScrmCollectionBreakUP` WHERE PiID=POD.PoID) as RealisedValueUSD ";

        $SqlRealizationDoneQuery = "SELECT $SqlFieldsList FROM ScrmCustomInvoices as SCI 
        left join ScrmPoData as POD on SCI.PiID=POD.PoID
         left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmPaymentCollections as SCP on POD.PoID=SCP.PIID			
        left join ScrmRealizationDetails as SRD on SRD.PoID=SCP.PIID	
        left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID		
        WHERE SCI.CInvDate $DateFilter  and POD.Status not in (3,7,4)   and	SSDB.TransportedDate!=''
        $Query  and POD.PoID in (SELECT PoID from ScrmRealizationDetails where RezStatus=1) 
        order by POD.PoID desc ";

        // (SELECT PIID FROM ScrmPaymentCollections as SPC where SPC.PIID=POD.PoID and SPC.Status=1)
        //in (SELECT PIID FROM ScrmPaymentCollections as SPC where SPC.PIID=POD.PoID and SPC.Status=1) order by POD.PoID desc
// echo $SqlRealizationDoneQuery; exit;

        $stmt = $this->conn->prepare($SqlRealizationDoneQuery);
        $stmt->execute();

        $AryRealizationDoneData = [];
        $DocType = '';
        $Sno = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $PoID = $dataRow['PoID'];
            //  echo   $sqlBreakup = "SELECT UtilizedValue,UtilizedDate,BankRefNo,ColDate FROM `ScrmCollectionBreakUP` where PiID=$PoID";exit;
            //     $stmtBreakup = $this->conn->prepare($sqlBreakup);
            //     $stmtBreakup->execute();
            //     $dataBreakup= $stmtBreakup->fetch(PDO::FETCH_ASSOC);
            $sqlBreakupCount = " SELECT * FROM `ScrmCollectionBreakUP` where PiID=$PoID";
            //echo       $sqlBreakupCount; exit;
            $stmtBreakupCount = $this->conn->prepare($sqlBreakupCount);
            $stmtBreakupCount->execute();
            $dataBreakupCount = $stmtBreakupCount->fetch(PDO::FETCH_ASSOC);
            $Total = $dataBreakupCount['Total'];
            $AryBankRef = array();
            $AryBankRef = $this->getUtilizedValue($PoID, $dataRow['PaymentType']);

            /* for($i=0;$i<$Total;$i++){
                  $Count=1;
                  $Limit=1;
                  $Offset=$i;
                 
                  $ArrayData=$this->getUtilizedValue($Limit, $Offset, $PoID);
                  $UtilizedDate=$ArrayData['UtilizedDate'];
                  $UtilizedValue=$ArrayData['UtilizedValue'];
                  $BankRefNo=$ArrayData['BankRefNo'];
                  $SpotRate=$ArrayData['SpotRate'];
                  $TotalUtlizedValue=$ArrayData['TotalUtlizedValue'];
                  $TotalUtlizedValue = $TotalUtlizedValue ? $TotalUtlizedValue : 0;
                  $SpotRate = $SpotRate ? $SpotRate : 0;

                  $TotalUtlizedValueINR=($TotalUtlizedValue*$SpotRate);
                  $TotalUtlizedValueINR=number_format($TotalUtlizedValueINR,2);

                  $AryBankRef[]=$ArrayData;
              //    $AryBankRef[]=$ArrayData['BankRefNo'];
                  $Count++;
              }

                  /*  while ($dataBreakup= $stmtBreakup->fetch(PDO::FETCH_ASSOC)) {
                        $Arraybreakup=$dataBreakup;

                   }
        foreach($dataBreakup){

        }*/
            if ($dataRow['PaymentType'] == 'Negotiations') {
                $CollectionDate = $this->utilController->getFormatedDate($dataBreakupCount['ColDate']);
                // $dataRow['RealisedValueUSD'] = '-';
                $dataRow['RealisedValueUSD'] = $dataRow['RealisedValueUSD'] - $dataRow['FBKCharges']; //uma
            } else {
                $dataRow['RealisedValueUSD'] = '-';

            }



            $ArrayPaymentValues = $this->utilController->getPaymentValues($PoID, $DocType);
            $InvValueUSD = $ArrayPaymentValues['InvValueUSD'];

            $ArrayRealizationDoneDetails['SNO'] = ++$Sno;
            $ArrayRealizationDoneDetails['InvoiceNo'] = $dataRow['CustomsInvNo'];
            $ArrayRealizationDoneDetails['SaleInvoiceDate'] = $this->utilController->getFormatedDate($dataRow['CInvDate']);
            $ArrayRealizationDoneDetails['TransactionType'] = $dataRow['PaymentType'];
            $ArrayRealizationDoneDetails['BnkChrges'] = $dataRow['BnkChrges'] ? $dataRow['BnkChrges'] : '0';
            // $ArrayRealizationDoneDetails['OtherAmount'] = $dataRow['OtherAmount']?$dataRow['OtherAmount'] :'0';
            $ArrayRealizationDoneDetails['EtraAmount'] = $dataRow['EtraAmount'] ? $dataRow['EtraAmount'] : '0';

            $ArrayRealizationDoneDetails['TotalInvoiceValueUSD'] = number_format($InvValueUSD, 2);
            $ArrayRealizationDoneDetails['RealisedValueUSD'] = $dataRow['RealisedValueUSD'];

              $ArrayRealizationDoneDetails['Buyer'] = $dataRow['ConsigneeName'];

            $ArrayRealizationDoneDetails['DateofDiscountingwithBank'] = $CollectionDate ? $CollectionDate : '-';

            if ($dataRow['PaymentType'] == 'Negotiations') {
                $ArrayRealizationDoneDetails["BankRefNo1"] = $AryBankRef[0]["RezRef1"];
                $ArrayRealizationDoneDetails["UtilizedDate1"] = $AryBankRef[0]["RezDate1"];
                $ArrayRealizationDoneDetails["UtilizedValue1"] = $AryBankRef[0]["RezValue1"];

                $ArrayRealizationDoneDetails["BankRefNo2"] = $AryBankRef[0]["RezRef2"];
                $ArrayRealizationDoneDetails["UtilizedDate2"] = $AryBankRef[0]["RezDate2"];
                $ArrayRealizationDoneDetails["UtilizedValue2"] = $AryBankRef[0]["RezValue2"];

                $ArrayRealizationDoneDetails["BankRefNo3"] = $AryBankRef[0]["RezRef3"];
                $ArrayRealizationDoneDetails["UtilizedDate3"] = $AryBankRef[0]["RezDate3"];
                $ArrayRealizationDoneDetails["UtilizedValue3"] = $AryBankRef[0]["RezValue3"];
            } else {
                for ($i = 0; $i < 3; $i++) {
                    $BankrefKey = 'BankRefNo' . ($i + 1);
                    $UtilizedDateKey = 'UtilizedDate' . ($i + 1);
                    $UtilizedValueKey = 'UtilizedValue' . ($i + 1);

                    $ArrayRealizationDoneDetails[$BankrefKey] = $AryBankRef[$i]['BankRefNo'] ? $AryBankRef[$i]['BankRefNo'] : '-';
                    $ArrayRealizationDoneDetails[$UtilizedDateKey] = $this->utilController->getFormatedDate($AryBankRef[$i]['UtilizedDate'] ? $AryBankRef[$i]['UtilizedDate'] : '-');
                    $ArrayRealizationDoneDetails[$UtilizedValueKey] = $AryBankRef[$i]['UtilizedValue'] ? $AryBankRef[$i]['UtilizedValue'] : '-';
                }
            }


            $AryTotal = $this->getUtilizedValueTotal($PoID);
            $ArrayRealizationDoneDetails['TotalUtlizedValue'] = $AryTotal['TotalUtilzed'];
            $ArrayRealizationDoneDetails['TotalUtlizedValueINR'] = round($AryTotal['TotalUtilzedInr'], 2);
            $ArrayRealizationDoneDetails['FbkChrges'] = $dataRow['FBKCharges'];
            $ArrayRealizationDoneDetails['OtherAmount'] = $dataRow['OtherCharges'];


            if ($dataRow['PaymentType'] == 'Negotiations' && $dataRow['RezStatus'] == 1) {
                $AryRealizationDoneData[] = $ArrayRealizationDoneDetails;
            } else if ($dataRow['PaymentType'] == 'Collections') {
                $AryRealizationDoneData[] = $ArrayRealizationDoneDetails;
            }




        }
        if (count($AryRealizationDoneData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['RealizationDoneData'] = $AryRealizationDoneData;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }


    public function getUtilizedValue($PoID, $PaymentType)
    {
        $sqlQuery = "SELECT * FROM `ScrmCollectionBreakUP` WHERE PiID=$PoID order by ColBrkID ";

        if ($PaymentType == 'Negotiations') {
            $sqlQuery = "SELECT *  FROM `ScrmRealizationDetails` WHERE `PoID` = $PoID";
        }
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        //echo  $sqlQuery; exit;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;

    }

    public function getUtilizedValueTotal($PoID)
    {
        $sqlQuery = "SELECT sum(UtilizedValue) as TotalUtilzed, sum(UtilizedValue*SpotRate) TotalUtilzedInr FROM `ScrmCollectionBreakUP` WHERE PiID=$PoID order by ColBrkID ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dataRow;

    }

}
?>