<?php

include_once '../Global/Util.php';

include_once 'Controller/UtilController.php';
class SimpController
{
    private $conn;
    private $db_table = "ScrmCustomInvoices";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    // GET ALL

    public function getOrderDetails($RequestBody)
    {

        $OrderID = $RequestBody['OrderID'];
        $SqlCustomCount = "SELECT ExpName,SCI.CustomsInvNo,SPD.PoNo,
        SCD.ConsigneeName,SCD.ConsigneeIFTPCODE,PackerAddress,QtyUnit,SPD.PortofLoading,
        SPC.Name AS LoadingPort,ExpAddress,ImporterRecordAddress,ExpApprovalNo,
        ExpUsfdaNo,ExpFax,ExpMailID,ExpPhoneNumber,ISF.ContainerNo,ISF.BLNo FROM `ScrmPoData` SPD
        LEFT JOIN AdminExporterDetails AED ON AED.ExpID=SPD.ProcessorID
        LEFT JOIN ScrmPortCities SPC ON SPC.PortId=SPD.PortofLoading
        LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID=SPD.ConsigneeID
        LEFT JOIN ScrmCustomInvoices SCI ON SCI.PiID=SPD.PoID
        LEFT JOIN ScrmISFDetails ISF ON ISF.PIID=SPD.PoID
        WHERE SPD.PoID= $OrderID";

        $stmt = $this->conn->prepare($SqlCustomCount);
        $stmt->execute();
        $dataRowExporter = $stmt->fetch(PDO::FETCH_ASSOC);
        $LoadingPort = $dataRowExporter['LoadingPort'];
        $QtyUnit = $dataRowExporter['QtyUnit'];

        $SqlTranshipData = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList` 
            WHERE PIID='$OrderID' and Status=1 and FromTOPID='$LoadingPort'";
        $stmtTranshipData = $this->conn->prepare($SqlTranshipData);
        $stmtTranshipData->execute();
        $dataTranshipData = $stmtTranshipData->fetch(PDO::FETCH_ASSOC);
        $VesselName = $dataTranshipData['VesselName'];
        $VOYNO = $dataTranshipData['VOYNO'];

        $dataRowExporter['NetWeightKgs'] = $this->utilController->getPriceDetails($OrderID, $QtyUnit)['NetWeightKGS'];
        $dataRowExporter['VesselName'] = $VesselName;
        $dataRowExporter['VOYNO'] = $VOYNO;
        $FileName = str_replace('/', '_', $dataRowExporter['CustomsInvNo']);

        $responseData['ExpName'] = $dataRowExporter['ExpName'];
        $responseData['ProcessorName'] = $dataRowExporter['ExpName'];
        $responseData['ExpPhoneNumber'] = $dataRowExporter['ExpPhoneNumber'];
        $responseData['PackerAddress'] = $dataRowExporter['ExpAddress'];
        $responseData['ExpAddress'] = $dataRowExporter['ExpAddress'];
        $responseData['ImporterRecordAddress'] = $dataRowExporter['ImporterRecordAddress'];
        $responseData['ExpApprovalNo'] = $dataRowExporter['ExpApprovalNo'];
        $responseData['ExpUsfdaNo'] = $dataRowExporter['ExpUsfdaNo'];
        $responseData['ExpFax'] = $dataRowExporter['ExpFax'];
        $responseData['ExpMailID'] = $dataRowExporter['ExpMailID'];
        $responseData['VesselName'] = $dataRowExporter['VesselName'];
        $responseData['VesselNO'] = $dataRowExporter['VOYNO'];
        $responseData['NetWeightKgs'] = $dataRowExporter['NetWeightKgs'];
        $responseData['ContainerNo'] = $dataRowExporter['ContainerNo'];
        $responseData['LoadingDate'] = '10/31/2023';
        $responseData['RecipientName'] = 'M R MURTHY';
        $responseData['InvNumber'] = $dataRowExporter['CustomsInvNo'];
        $responseData['FileName'] = $FileName;
        $responseData['ConsigneeName'] = $dataRowExporter['ConsigneeName'];
        $responseData['PoNumber'] = $dataRowExporter['PoNo'];
        $responseData['ConsigneeIFTPCODE'] = $dataRowExporter['ConsigneeIFTPCODE']; //doubt
        $responseData['BillOfLading'] = $dataRowExporter['BLNo']; //doubt



        return $responseData;
    }


    public function getData($RequestBody)
    {
        $Error = null;
        $Msg = "Success";
        $OrderDetails = $this->getOrderDetails($RequestBody);
        $LineItems = $this->getLineItems($RequestBody);
        $Products = $this->getProducts($RequestBody);
        $Tracibility = $this->getTracebilities($RequestBody);
        $LineItemsPurchaseBillNowise = $this->getLineItemsPurchaseBillNowise($RequestBody);

        $Data['Error'] = $Error;
        $Data['Msg'] = $Msg;
        $Data['EditLink'] = "DocAddReports?PoID=";
        $Data['OrderDetails'] = $OrderDetails;
        $Data['LineItemData'] = $LineItems;
        $Data['Products'] = $Products;
        $Data['Tracibility'] = $Tracibility;
        $Data['LineItemsPurchaseBillNowise'] = $LineItemsPurchaseBillNowise;
        return $Data;


    }
    public function getLineItemsPurchaseBillNowise($RequestBody)
    {
        $OrderID = $RequestBody['OrderID'];



        //     $SqlFieldsList = "FarmerName,FarmPhoneNumber,QCL.CLID,SPIVL.TypeProduct,
        //     SPIVL.Species 
        //     ,SPM.SpeciesName,
        //     GROUP_CONCAT( SPIVL.Grade order by CLID asc SEPARATOR '<br>')  AS GradeName,

        //     GROUP_CONCAT(DISTINCT SPGD.QcDescription order by CLID asc SEPARATOR '<br>&nbsp;&nbsp&nbsp;&nbsp;')  as QcDescription,
        //     GROUP_CONCAT(DISTINCT SPGD.Variety order by CLID asc SEPARATOR '<br>&nbsp;&nbsp;&nbsp;&nbsp;')  as Variety,


        //     GROUP_CONCAT(CLID order by CLID asc ) as CLID,
        //      GROUP_CONCAT(DISTINCT PurchaseLotCode order by CLID asc ) as PurchaseLotCodess,

        //     GROUP_CONCAT( SPIVL.PreparationType,'&',SPIVL.Species,'&',SPIVL.ShortName order by CLID asc SEPARATOR '<BR>') AS ShortName ,
        //     AddressContinued,SPIVL.Species,SPIVL.PreparationType,QCL.TracebilityID,QCL.HarvestDt,QCL.ReceivingDt,
        //     Tracebility,RegAuthority,QCL.DayCode,GROUP_CONCAT( McsGrade order by CLID asc SEPARATOR '<br>  MASTER CARTONS - ') AS McsGrade,
        //     GROUP_CONCAT( APS.PackingStyle order by CLID asc SEPARATOR '<br> &nbsp;&nbsp;&nbsp;&nbsp;')  AS PackingStyle
        //    ";  

        $SqlFieldsList = "FarmerName,FarmPhoneNumber,QCL.CLID,SPIVL.TypeProduct,
        SPIVL.Species 
        ,SPM.SpeciesName,
        GROUP_CONCAT( SPIVL.Grade order by CLID asc SEPARATOR '<br>')  AS GradeName,
        
        GROUP_CONCAT(DISTINCT SPGD.QcDescription order by CLID asc SEPARATOR '<br>&nbsp;&nbsp&nbsp;&nbsp;')  as QcDescription,
        GROUP_CONCAT(DISTINCT SPGD.Variety order by CLID asc SEPARATOR '<br>&nbsp;&nbsp;&nbsp;&nbsp;')  as Variety,
        
        
        GROUP_CONCAT(CLID order by CLID asc ) as CLID,
         GROUP_CONCAT(DISTINCT PurchaseLotCode order by CLID asc ) as PurchaseLotCodess,
       
        GROUP_CONCAT( SPRM.QcPreparationShortCode,'&',SPM.QcSpeciesShortCode,'&',SPIVL.ShortName order by CLID asc SEPARATOR '<BR>') AS ShortName ,
        AddressContinued,SPIVL.Species,SPIVL.PreparationType,QCL.TracebilityID,QCL.HarvestDt,QCL.ReceivingDt,
        Tracebility,RegAuthority,QCL.DayCode,GROUP_CONCAT( McsGrade order by CLID asc SEPARATOR '<br>  MASTER CARTONS - ') AS McsGrade,
        GROUP_CONCAT( APS.PackingStyle order by CLID asc SEPARATOR '<br> &nbsp;&nbsp;&nbsp;&nbsp;')  AS PackingStyle
       ";

        // $SqlCustomInvoicesQuery = "SELECT $SqlFieldsList FROM `QcCodeList` QCL
        //         LEFT JOIN QcTracebilityList QTL ON QTL.TraceID=QCL.TracebilityID
        //         LEFT JOIN ScrmPoGradeData SPGD ON  QCL.PoGradeID=SPGD.ID
        //         LEFT JOIN ScrmPIVarietyList SPIVL ON SPIVL.VarietyID=SPGD.VarietyID
        //         LEFT JOIN `ScrmSpeciesMaster` as SPM on SPM.ShortCode=SPIVL.Species
        //         LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SPGD.PackingStyleID
        //         WHERE QCL.InvID='$OrderID' GROUP BY QTL.Tracebility,QCL.HarvestDt  order by  QCL.CLID ASC  ";

        // $SqlCustomInvoicesQuery = "SELECT $SqlFieldsList FROM `QcCodeList` QCL
        //         LEFT JOIN QcTracebilityList QTL ON QTL.TraceID=QCL.TracebilityID
        //         LEFT JOIN ScrmPoGradeData SPGD ON  QCL.PoGradeID=SPGD.ID
        //         LEFT JOIN ScrmPIVarietyList SPIVL ON SPIVL.VarietyID=SPGD.VarietyID
        //         LEFT JOIN `ScrmSpeciesMaster` as SPM on SPM.ShortCode=SPIVL.Species
        //         LEFT JOIN `ScrmPreparationMaster` as SPRM on SPRM.ShortCode=SPIVL.PreparationType
        //         LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SPGD.PackingStyleID
        //         WHERE QCL.InvID='$OrderID' GROUP BY QTL.Tracebility,QCL.HarvestDt  order by  QCL.CLID ASC  ";



        $SqlCustomInvoicesQuery = "SELECT $SqlFieldsList FROM `QcCodeList` QCL
                LEFT JOIN QcTracebilityList QTL ON QTL.TraceID=QCL.TracebilityID
                LEFT JOIN ScrmPoGradeData SPGD ON  QCL.PoGradeID=SPGD.ID
                LEFT JOIN ScrmPIVarietyList SPIVL ON SPIVL.VarietyID=SPGD.VarietyID
                LEFT JOIN `ScrmSpeciesMaster` as SPM on SPM.ShortCode=SPIVL.Species
                LEFT JOIN `ScrmPreparationMaster` as SPRM on SPRM.ShortCode=SPIVL.PreparationType
                LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SPGD.PackingStyleID
                WHERE QCL.InvID='$OrderID' GROUP BY QTL.Tracebility,QCL.HarvestDt  order by  QCL.DayCode ASC , QCL.CLID ASC "; //ADDED DAYCODE


        $stmt = $this->conn->prepare($SqlCustomInvoicesQuery);
        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $CodeListID = $dataRow['CLID'];
            $DayCode = $dataRow['DayCode'];
            $Species = $dataRow['Species'];
            $ScientificName = $dataRow['SpeciesName'];
            $ShortName = $dataRow['ShortName'];
            $QtyUnit = $dataRow['QtyUnit'];
            $HarvestDt = $dataRow['HarvestDt'];
            $ReceivingDt = $dataRow['ReceivingDt'];

            $HarvestDate = substr($HarvestDt, 10);
            $ReceivingDt = substr($ReceivingDt, 10);

            // $HarvestDt = $HarvestDate[0];
            if ($ReceivingDt != '') {
                $ReceivingDt = date("m-d-Y", strtotime($ReceivingDt));
            } else {
                $ReceivingDt = "";
            }
            $ReceivingDt = "test";

            if ($HarvestDt != '') {
                $HarvestDt = date("m-d-Y", strtotime($HarvestDt));
            } else {
                $HarvestDt = "";
            }

            $SqlRMRWeights = "SELECT sum(McsTotal) as McsTotal,
            sum(TotalSlabs*SlabWeight*MFactor*McsTotal)  as NetWgtKgs ,
            sum(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,

            GROUP_CONCAT(TotalSlabs*SlabWeight*MFactor*McsTotal)  as GroupWeightFinished ,
            GROUP_CONCAT(Yeild)  as Yeild 
            FROM `QcCodeList` as QCL 
            LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
            left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
            left join AdminPackingStyles As APS on APS.PCKID=QCL.PackingStyleID
            WHERE QCL.InvID='$OrderID'    and  QCL.CLID in ($CodeListID) 
            order by QCL.CLID ASC";  // echo and DayCode='$DayCode'

            // echo "<br>";
            $stmtRMRWeights = $this->conn->prepare($SqlRMRWeights);
            $stmtRMRWeights->execute();
            $RowRMRWeights = $stmtRMRWeights->fetch(PDO::FETCH_ASSOC);


            if ($QtyUnit == 'KGS') {
                $FinishedQty = $RowRMRWeights['NetWgtKgs'];
            } else {
                $FinishedQty = $RowRMRWeights['NetWgtLbs'] * 0.454;
            }

            $FinishedQty = sprintf('%0.3f', ($FinishedQty), 3);
            //$FinishedQty=$FinishedQty.'.000';
            $GroupWeightFinished = $RowRMRWeights['GroupWeightFinished']; //echo "<br>";
            $Yield = $RowRMRWeights['Yeild'];

            $aryYield = explode(",", $Yield);
            $aryWeightKgs = explode(",", $GroupWeightFinished);
            //  var_dump( $aryWeightKgs);
            $aryUsedQty = array();
            $aryUsedFinished = array();

            if (count($aryYield) > 1) {
                $UsedQty = 0;
                $FinishedQty = $GroupWeightFinished = $RowRMRWeights['GroupWeightFinished'];
                $i = 0;

                foreach ($aryYield as $Item) {
                    $Item;


                    $FinishedQty = $aryWeightKgs[$i];
                    // $FinishedQty = round($FinishedQty);
                    $FinishedQty = sprintf('%0.3f', ($FinishedQty), 3);// $FinishedQty . '.000';
                    $aryUsedFinished[] = $FinishedQty;


                    $GroupWeightRcved = ((($aryWeightKgs[$i] / $Item) * 100) + 0.001);
                    $GroupWeightRcved = round($GroupWeightRcved);

                    $aryUsedReceived[] = $GroupWeightRcved;//uma
                    $UsedQty += $GroupWeightRcved;
                    $GroupWeightRcved = $GroupWeightRcved . '.000';
                    $aryUsedQty[] = $GroupWeightRcved;

                    $i++;
                }
                $UsedQty = $UsedQty . '.000';
                $GroupWeightRcved = implode("<br>", $aryUsedQty);
                $FinishedQty = $GroupWeightFinished = implode("<br>", $aryUsedFinished);
            } else {
                $UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
                $ReceivedQty = $UsedQty = round($UsedQty);
                // $ReceivedQty = $GroupWeightRcved = implode("<br>", $aryUsedReceived);
                $GroupWeightRcved = $UsedQty = $UsedQty . '.000';
            }


            // $UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
            // $UsedQty = round($UsedQty);
            // $UsedQty = $UsedQty . '.000';

            //    echo $dataRow['PreparationType']; exit;


            if ($Species == 'VM') {
                $CommodityMarketName = 'White Leg Shrimp';
                $ProductASFIS = 'PNV';
                $SpeciesCode = 'V';
            } else {
                $ProductASFIS = 'GIT';
                $CommodityMarketName = 'GAINT TIGER PRAWN';
                $SpeciesCode = 'BT';
            }

            if ($dataRow['TypeProduct'] == 'RAW') {
                $dataRow['TypeProduct'] = '';
            } else if ($dataRow['TypeProduct'] == 'COOKED') {
                $dataRow['TypeProduct'] = 'C';
            }

            if ($dataRow['PreparationType'] == 'CIS') {

                $dataRow['TypeProduct'] = 'CIS';
            }

            $ArrayCustomDetails['SNO'] = ++$i;
            $ArrayCustomDetails['ID'] = $dataRow['PoID'];
            $ArrayCustomDetails['FarmerName'] = $dataRow['FarmerName'];

            $DayCode = $this->utilController->getDayCodeDate($DayCode);
            $Data['DayCode'] = $DayCode;

            $TracebilityNumber = explode('-', $dataRow['Tracebility']);
            $PondNumber = $TracebilityNumber[2];
            $Tracebility = $TracebilityNumber[1];
            $TracebilityFull = $dataRow['Tracebility'];
            $ReceivedLotIdentifiers = $TracebilityNumber[1] . '-' . $TracebilityNumber[2];
            $ProductionDt = date("m-d-Y", strtotime($DayCode));
            $replacement = "<br>" . $dataRow['TypeProduct'] . " " . $SpeciesCode . " ";

            // $dataRow['ShortName']= str_replace("<br>",$replacement ,$dataRow['ShortName']); by teja

            $ShortName = str_replace("&", " ", $dataRow['ShortName']);
            // $ShortName = str_replace("VM", "V", $ShortName);
            // $ShortName = str_replace("RI", "", $ShortName);
            // $ShortName = str_replace("RB", "", $ShortName); //UMA IF RB REMOVE RB 18-04-2024            
            // // $ShortName=str_replace("CIS","CIS" ,$ShortName); 
            // $ShortName = str_replace("CIS", "#CIS#", $ShortName); //UMA IF RB REMOVE RB 18-04-2024
            // $ShortName = str_replace("CI", "C", $ShortName);
            // $ShortName = str_replace("#CIS#", "CIS", $ShortName); //UMA IF RB REMOVE RB 18-04-2024
//uma 18-04-2024
            // $ShortName = str_replace("CI", "__CI__", $ShortName);
            // $search = array("VM", "RI", "RB", "CIS");
            // $replace = array("V", "", "", "__CIS__");
            // $ShortName = str_replace($search, $replace, $ShortName);
            // $ShortName = str_replace("__CI__", "CI", $ShortName);
            // $ShortName = str_replace("__CIS__", "CIS", $ShortName);
            $dataRow['ShortName'] = $ShortName;
            //uma 18-04-2024

            //  $ArrayCustomDetails['InvoiceDate'] = date_format($date, "d-m-Y");
            $ArrayCustomDetails['AddressContinued'] = $dataRow['AddressContinued'];
            $ArrayCustomDetails['Tracebility'] = $Tracebility;
            $ArrayCustomDetails['ReceivedLotIdentifiers'] = $ReceivedLotIdentifiers;
            $ArrayCustomDetails['RegAuthority'] = $dataRow['RegAuthority'];
            $ArrayCustomDetails['DayCode'] = $DayCode;
            $ArrayCustomDetails['ProductionDt'] = $ProductionDt;
            $ArrayCustomDetails['McsGrade'] = $dataRow['McsGrade'];
            $ArrayCustomDetails['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayCustomDetails['Variety'] = $dataRow['QcDescription'] ? $dataRow['QcDescription'] : $dataRow['Variety'];
            // $ArrayCustomDetails['Variety'] = $dataRow['Variety'];
            $ArrayCustomDetails['ScientificName'] = $ScientificName;
            $ArrayCustomDetails['CommodityMarketName'] = $CommodityMarketName;
            $ArrayCustomDetails['ProductASFIS'] = $ProductASFIS;
            $ArrayCustomDetails['PurchaseManager'] = 'SDSV SUBBARAJU';
            $ArrayCustomDetails['PurchaseManagerNumber'] = '8790917555';
            // $ArrayCustomDetails['FinishedWeight'] = $dataRowWeights['FinishedWeight'];
            $ArrayCustomDetails['GroupWeightRcved'] = $GroupWeightRcved; //="hii";
            $ArrayCustomDetails['FinishedWeight'] = $FinishedQty;
            $ArrayCustomDetails['ReceivedQty'] = $UsedQty;
            //$ArrayCustomDetails['ReceivedQty'] = $dataRowWeights['ReceivedQty'];
            $ArrayCustomDetails['VarietyShortName'] = $dataRow['ShortName'];
            $ArrayCustomDetails['HarvestDate'] = $HarvestDt;
            $ArrayCustomDetails['ReceivingDt'] = $ReceivingDt;
            $ArrayCustomDetails['GradeName'] = $dataRow['GradeName'];
            $ArrayCustomDetails['PondNumber'] = $PondNumber; //doubt
            $ArrayCustomDetails['FarmerPhoneNumber'] = $dataRow['FarmPhoneNumber']; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['PurchaseBillNumber'] = $dataRow['PurchaseLotCode']; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['TypeProduct'] = $dataRow['TypeProduct']; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['SpeciesCode'] = $SpeciesCode; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['TracebilityFull'] = $TracebilityFull; //NO FEILD IN DATA BASE

            $aryLineItemDetails[] = $ArrayCustomDetails;

        }



        //echo  $RequestBody['qc'];

        // $data['OrderDetail'] = $AryCustomData;
        // $data['LineItemDetails'] = $AryCustomData;
        // $data['ProductDetails'] = $this->getProducts($RequestBody);
        // $data['TracebilityDetails'] = $this->getTracebilities($RequestBody);

        return $aryLineItemDetails;
    }



    public function getLineItems($RequestBody)
    {
        $OrderID = $RequestBody['OrderID'];


        // $SqlFieldsList = "FarmerName,FarmPhoneNumber,QCL.CLID,SPIVL.TypeProduct,
        // SPIVL.Species
        // ,SPM.SpeciesName,
        // GROUP_CONCAT( SPIVL.Grade order by CLID asc SEPARATOR '<br>')  AS GradeName,
        // GROUP_CONCAT(DISTINCT SPGD.QcDescription order by CLID asc SEPARATOR '<br>&nbsp;&nbsp;&nbsp;&nbsp;')  as QcDescription,
        // GROUP_CONCAT(DISTINCT SPGD.Variety order by CLID asc SEPARATOR '<br>&nbsp;&nbsp;&nbsp;&nbsp;')  as Variety,
        // GROUP_CONCAT(CLID order by CLID asc) as GroupCLID,
        // GROUP_CONCAT(DISTINCT PurchaseLotCode order by CLID asc) as PurchaseLotCode,
        // AddressContinued,SPIVL.Species,SPIVL.PreparationType,

        // GROUP_CONCAT( SPIVL.PreparationType,'&',SPIVL.Species,'&',SPIVL.ShortName order by CLID asc SEPARATOR '<BR>') AS ShortName ,
        // QCL.TracebilityID,QCL.HarvestDt,QCL.ReceivingDt,
        // Tracebility,RegAuthority,QCL.DayCode,
        // GROUP_CONCAT( McsGrade order by CLID asc SEPARATOR '<br> &nbsp;&nbsp;&nbsp; MASTER CARTONS - ') AS McsGrade,
        // GROUP_CONCAT( DISTINCT APS.PackingStyle order by CLID asc SEPARATOR '<br> &nbsp;&nbsp;&nbsp;&nbsp;')  AS PackingStyle
        // "; 

        $SqlFieldsList = "FarmerName,FarmPhoneNumber,QCL.CLID,SPIVL.TypeProduct,
        SPIVL.Species
        ,SPM.SpeciesName,
        GROUP_CONCAT( SPIVL.Grade order by CLID asc SEPARATOR '<br>')  AS GradeName,
        GROUP_CONCAT(DISTINCT SPGD.QcDescription order by CLID asc SEPARATOR '<br>&nbsp;&nbsp;&nbsp;&nbsp;')  as QcDescription,
        GROUP_CONCAT(DISTINCT SPGD.Variety order by CLID asc SEPARATOR '<br>&nbsp;&nbsp;&nbsp;&nbsp;')  as Variety,
        GROUP_CONCAT(CLID order by CLID asc) as GroupCLID,
        GROUP_CONCAT(DISTINCT PurchaseLotCode order by CLID asc) as PurchaseLotCode,
        AddressContinued,SPIVL.Species,SPIVL.PreparationType,
        
        GROUP_CONCAT( SPRM.QcPreparationShortCode,'&',SPM.QcSpeciesShortCode,'&',SPIVL.ShortName order by CLID asc SEPARATOR '<BR>') AS ShortName ,
        QCL.TracebilityID,QCL.HarvestDt,QCL.ReceivingDt,
        Tracebility,RegAuthority,QCL.DayCode,
        GROUP_CONCAT( McsGrade order by CLID asc SEPARATOR '<br> &nbsp;&nbsp;&nbsp; MASTER CARTONS - ') AS McsGrade,
        GROUP_CONCAT( DISTINCT APS.PackingStyle order by CLID asc SEPARATOR '<br> &nbsp;&nbsp;&nbsp;&nbsp;')  AS PackingStyle
        ";

        // $SqlCustomInvoicesQuery = "SELECT $SqlFieldsList FROM `QcCodeList` QCL
        //         LEFT JOIN QcTracebilityList QTL ON QTL.TraceID=QCL.TracebilityID
        //         LEFT JOIN ScrmPoGradeData SPGD ON  QCL.PoGradeID=SPGD.ID
        //         LEFT JOIN ScrmPIVarietyList SPIVL ON SPIVL.VarietyID=SPGD.VarietyID
        //         LEFT JOIN `ScrmSpeciesMaster` as SPM on SPM.ShortCode=SPIVL.Species
        //         LEFT JOIN `ScrmPreparationMaster` as SPRM on SPRM.ShortCode=SPIVL.PreparationType
        //         LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SPGD.PackingStyleID
        //         WHERE QCL.InvID='$OrderID' GROUP BY QTL.Tracebility,QCL.HarvestDt  order by  CLID ASC  ";



        $SqlCustomInvoicesQuery = "SELECT $SqlFieldsList FROM `QcCodeList` QCL
                LEFT JOIN QcTracebilityList QTL ON QTL.TraceID=QCL.TracebilityID
                LEFT JOIN ScrmPoGradeData SPGD ON  QCL.PoGradeID=SPGD.ID
                LEFT JOIN ScrmPIVarietyList SPIVL ON SPIVL.VarietyID=SPGD.VarietyID
                LEFT JOIN `ScrmSpeciesMaster` as SPM on SPM.ShortCode=SPIVL.Species
                LEFT JOIN `ScrmPreparationMaster` as SPRM on SPRM.ShortCode=SPIVL.PreparationType
                LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SPGD.PackingStyleID
                WHERE QCL.InvID='$OrderID' GROUP BY QTL.Tracebility,QCL.HarvestDt  order by  DayCode ASC, QCL.CLID ASC  "; //ADDED DAYCODE



        $stmt = $this->conn->prepare($SqlCustomInvoicesQuery);
        $stmt->execute();

        $dataRow_index = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dataRow_index++;
            $CodeListID = $dataRow['GroupCLID'];
            $DayCode = $dataRow['DayCode'];
            $Species = $dataRow['Species'];
            $ScientificName = $dataRow['SpeciesName'];

            $ShortName = $dataRow['ShortName'];
            $QtyUnit = $dataRow['QtyUnit'];
            $HarvestDt = $dataRow['HarvestDt'];
            $ReceivingDt = $dataRow['ReceivingDt'];



            //  $HarvestDate = substr($HarvestDt, 0,10);
            //  $ReceivingDt = substr($ReceivingDt,0, 10);

            // $HarvestDt = $HarvestDate[0];
            if ($HarvestDt != '') {
                $HarvestDt = date("m-d-Y", strtotime($HarvestDt));
            } else {
                $HarvestDt = "";
            }

            if ($ReceivingDt != '') {
                $ReceivingDt = date("m-d-Y", strtotime($ReceivingDt));
            } else {
                $ReceivingDt = "";
            }


            $SqlRMRWeights = "SELECT sum(McsTotal) as McsTotal,
            sum(TotalSlabs*SlabWeight*MFactor*McsTotal)  as NetWgtKgs ,
            sum(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,

            GROUP_CONCAT(TotalSlabs*SlabWeight*MFactor*McsTotal)  as GroupWeightFinished ,
            GROUP_CONCAT(Yeild)  as Yeild 
            FROM `QcCodeList` as QCL 
            LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
            left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
            left join AdminPackingStyles As APS on APS.PCKID=QCL.PackingStyleID
            WHERE QCL.InvID='$OrderID'    and  QCL.CLID in ($CodeListID) 
            order by QCL.CLID ASC";  // echo and DayCode='$DayCode'


            // echo "<br>";
            $stmtRMRWeights = $this->conn->prepare($SqlRMRWeights);
            $stmtRMRWeights->execute();
            $RowRMRWeights = $stmtRMRWeights->fetch(PDO::FETCH_ASSOC);

            $AddressContentQuery = "SELECT Content FROM `AdminContentTemplates` WHERE `type` = 'simp_product_address'";
            $AddressContent = $this->conn->prepare($AddressContentQuery);
            $AddressContent->execute();
            $AddressContent = $AddressContent->fetch(PDO::FETCH_ASSOC);
            $AddressContent = $AddressContent['Content'];
            $AddressContent = str_replace("<farm_no>", $dataRow_index, $AddressContent);
            $AddressContent = str_replace("<name>", $dataRow['FarmerName'], $AddressContent);
            $AddressContent = str_replace("<address>", $dataRow['AddressContinued'], $AddressContent);
            $AddressContent = str_replace("<mobile>", $dataRow['FarmPhoneNumber'], $AddressContent);

            if ($QtyUnit == 'KGS') {
                $FinishedQty = $RowRMRWeights['NetWgtKgs'];
            } else {
                $FinishedQty = $RowRMRWeights['NetWgtLbs'] * 0.454;
            }

            $FinishedQty = sprintf('%0.3f', ($FinishedQty), 3);
            //$FinishedQty=$FinishedQty.'.000';
            $GroupWeightFinished = $RowRMRWeights['GroupWeightFinished']; //echo "<br>";
            $Yield = $RowRMRWeights['Yeild'];

            $aryYield = explode(",", $Yield);
            $aryWeightKgs = explode(",", $GroupWeightFinished);
            //  var_dump( $aryWeightKgs);
            $aryUsedQty = array();
            $aryUsedFinished = "";

            if (count($aryYield) > 1) {
                $UsedQty = 0;
                $FinishedQty = $GroupWeightFinished = $RowRMRWeights['GroupWeightFinished'];
                $i = 0;

                foreach ($aryYield as $Item) {
                    $Item;


                    $FinishedQty = $aryWeightKgs[$i];
                    $FinishedQty = round($FinishedQty);
                    $FinishedQty = $FinishedQty . '.000';
                    $aryUsedFinished[] = $FinishedQty;


                    $GroupWeightRcved = ((($aryWeightKgs[$i] / $Item) * 100) + 0.001);
                    $GroupWeightRcved = round($GroupWeightRcved);

                    $aryUsedReceived[] = $GroupWeightRcved;//uma
                    $UsedQty += $GroupWeightRcved;
                    $GroupWeightRcved = $GroupWeightRcved . '.000';
                    $aryUsedQty[] = $GroupWeightRcved;

                    $i++;
                }
                $UsedQty = $UsedQty . '.000';
                $GroupWeightRcved = implode("<br>", $aryUsedQty);
                $FinishedQty = $GroupWeightFinished = implode("<br>", $aryUsedFinished);
            } else {
                $UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
                $ReceivedQty = $UsedQty = round($UsedQty);
                // $ReceivedQty = $GroupWeightRcved = implode("<br>", $aryUsedReceived);
                $GroupWeightRcved = $UsedQty = $UsedQty . '.000';
            }


            // $UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
            // $UsedQty = round($UsedQty);
            // $UsedQty = $UsedQty . '.000';




            if ($Species == 'VM') {
                $CommodityMarketName = 'White Leg Shrimp';
                $ProductASFIS = 'PNV';
                $SpeciesCode = 'V';
            } else {
                $ProductASFIS = 'GIT';
                $CommodityMarketName = 'GAINT TIGER PRAWN';
                $SpeciesCode = 'BT';
            }

            if ($dataRow['TypeProduct'] == 'RAW') {
                $dataRow['TypeProduct'] = '';
            } else if ($dataRow['TypeProduct'] == 'COOKED') {
                $dataRow['TypeProduct'] = 'C';
            }

            if ($dataRow['PreparationType'] == 'CIS') {

                $dataRow['TypeProduct'] = 'CIS';
            }

            $ArrayCustomDetails['SNO'] = ++$i;
            $ArrayCustomDetails['ID'] = $dataRow['PoID'];
            $ArrayCustomDetails['FarmerName'] = $dataRow['FarmerName'];

            $DayCode = $this->utilController->getDayCodeDate($DayCode);
            $Data['DayCode'] = $DayCode;

            $TracebilityNumber = explode('-', $dataRow['Tracebility']);
            $TracebilityArray = $this->splitTracebilityAndPondID(trim($dataRow['Tracebility']));
            $PondNumber = $TracebilityArray[1];
            $Tracebility = $TracebilityArray[0];
            $Tracebility = removeStartingString($TracebilityArray[0]);
            $TracebilityFull = $dataRow['Tracebility'];
            $ReceivedLotIdentifiers = removeStartingString($TracebilityArray[0]) . '-' . $TracebilityArray[1];
            $ProductionDt = date("m-d-Y", strtotime($DayCode));

            $replacement = "<br>" . $dataRow['TypeProduct'] . " " . $SpeciesCode . " ";
            $replacement = "<br> tEJA";
            $ShortName = str_replace("&", " ", $dataRow['ShortName']);

            // $ShortName = str_replace("VM", "V", $ShortName);
            // $ShortName = str_replace("RI", "", $ShortName);
            // $ShortName = str_replace("RB", "", $ShortName);   //UMA IF RB REMOVE RB 18-04-2024            
            // $ShortName=str_replace("CIS","CIS" ,$ShortName); 
            // $ShortName = str_replace("CI", "C", $ShortName);

            //uma 18-04-2024
            // $ShortName = str_replace("CI", "__CI__", $ShortName);
            // $search = array("VM", "RI", "RB", "CIS");
            // $replace = array("V", "", "", "__CIS__");
            // $ShortName = str_replace($search, $replace, $ShortName);
            // $ShortName = str_replace("__CI__", "CI", $ShortName);
            // $ShortName = str_replace("__CIS__", "CIS", $ShortName);
            //uma 18-04-2024



            $dataRow['ShortName'] = str_replace("<br>", $replacement, $dataRow['ShortName']);
            $dataRow['ShortName'] = $ShortName;
            //$dataRow['ShortName']= "test"  ; 

            //  $ArrayCustomDetails['InvoiceDate'] = date_format($date, "d-m-Y");
            $ArrayCustomDetails['AddressContinued'] = $dataRow['AddressContinued'];
            $ArrayCustomDetails['Tracebility'] = $Tracebility;
            $ArrayCustomDetails['ReceivedLotIdentifiers'] = $ReceivedLotIdentifiers;
            $ArrayCustomDetails['RegAuthority'] = $dataRow['RegAuthority'];
            $ArrayCustomDetails['DayCode'] = $DayCode;
            $ArrayCustomDetails['ProductionDt'] = $ProductionDt;
            $ArrayCustomDetails['McsGrade'] = $dataRow['McsGrade'];
            $ArrayCustomDetails['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayCustomDetails['Variety'] = $dataRow['Variety'];
            $ArrayCustomDetails['QcDescription'] = $dataRow['QcDescription'] ? $dataRow['QcDescription'] : $dataRow['Variety'];
            ;
            // $ArrayCustomDetails['Variety'] = $dataRow['Variety'];
            $ArrayCustomDetails['ScientificName'] = $ScientificName;
            $ArrayCustomDetails['CommodityMarketName'] = $CommodityMarketName;
            $ArrayCustomDetails['ProductASFIS'] = $ProductASFIS;
            $ArrayCustomDetails['PurchaseManager'] = 'SDSV SUBBARAJU';
            $ArrayCustomDetails['PurchaseManagerNumber'] = '8790917555';
            // $ArrayCustomDetails['FinishedWeight'] = $dataRowWeights['FinishedWeight'];
            $ArrayCustomDetails['GroupWeightRcved'] = $GroupWeightRcved;//="hii";
            $ArrayCustomDetails['FinishedWeight'] = $FinishedQty = "test";
            $ArrayCustomDetails['ReceivedQty'] = $UsedQty;
            //$ArrayCustomDetails['ReceivedQty'] = $dataRowWeights['ReceivedQty'];
            $ArrayCustomDetails['VarietyShortName'] = $dataRow['ShortName'];
            $ArrayCustomDetails['HarvestDate'] = $HarvestDt;
            $ArrayCustomDetails['ReceivingDt'] = $ReceivingDt;
            $ArrayCustomDetails['GradeName'] = $dataRow['GradeName'];
            $ArrayCustomDetails['PondNumber'] = $PondNumber; //doubt
            $ArrayCustomDetails['FarmerPhoneNumber'] = $dataRow['FarmPhoneNumber']; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['PurchaseBillNumber'] = $dataRow['PurchaseLotCode']; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['TypeProduct'] = $dataRow['TypeProduct']; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['SpeciesCode'] = $SpeciesCode; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['TracebilityFull'] = $TracebilityFull; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['ProductAddress'] = $AddressContent;

            $aryLineItemDetails[] = $ArrayCustomDetails;

        }



        //echo  $RequestBody['qc'];

        // $data['OrderDetail'] = $AryCustomData;
        // $data['LineItemDetails'] = $AryCustomData;
        // $data['ProductDetails'] = $this->getProducts($RequestBody);
        // $data['TracebilityDetails'] = $this->getTracebilities($RequestBody);

        return $aryLineItemDetails;
    }

    public function getProducts($RequestBody)
    {
        $OrderID = $RequestBody['OrderID'];

        // $SqlQcBreakupData = "SELECT  Variety,PreparationType,ShortName,QcDescription
        // FROM `ScrmPoGradeData` SPGD
        // LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID=SPGD.VarietyID 

        // WHERE InvID='$OrderID'  group by SPGD.Variety ORDER BY SPGD.ID ASC"; 

        // $SqlQcBreakupData = "SELECT   QCL.CLID,Variety,PreparationType,ShortName,QcDescription
        // FROM QcCodeList as  QCL   LEFT JOIN `ScrmPoGradeData` SPGD on QCL.PoGradeID=SPGD.ID
        // LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID=SPGD.VarietyID  
        // WHERE QCL.InvID='$OrderID'  group by QcDescription ORDER BY QCL.CLID ASC";

        $SqlQcBreakupData = "SELECT   QCL.CLID,Variety,PreparationType,ShortName,Description,QcDescription
        FROM QcCodeList as  QCL   LEFT JOIN `ScrmPoGradeData` SPGD on QCL.PoGradeID=SPGD.ID
        LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID=SPGD.VarietyID  
        WHERE QCL.InvID='$OrderID'  group by QcDescription ORDER BY QCL.CLID ASC";
        // group by SPGD.Variety
        $StmtQcBreakupData = $this->conn->prepare($SqlQcBreakupData);
        $StmtQcBreakupData->execute();
        while ($RowQcBreakupData = $StmtQcBreakupData->fetch(PDO::FETCH_ASSOC)) {


            // $ArrayQcBreakUpDetails['VarietyFullName'] = $RowQcBreakupData['Variety'];
            $ArrayQcBreakUpDetails['VarietyFullName'] = $RowQcBreakupData['Description'];
            $ArrayQcBreakUpDetails['QcDescription'] = $RowQcBreakupData['QcDescription'];
            $ArrayQcBreakUpDetails['VarietyShortName'] = $RowQcBreakupData['ShortName'];
            $ArrayQcBreakUpDetails['PreparationType'] = $RowQcBreakupData['PreparationType'];
            $AryQcBreakUpData[] = $ArrayQcBreakUpDetails;
        }
        return $AryQcBreakUpData;
    }

    public function getTracebilities($RequestBody)
    {
        $OrderID = $RequestBody['OrderID'];
        // distinct TCL.Tracebility,
        $SqlQcBreakupData = "SELECT  TCL.Tracebility,TCL.FarmerName,TCL.AddressContinued,TCL.FarmPhoneNumber FROM QcCodeList QCL
      LEFT JOIN QcTracebilityList TCL ON TCL.TraceID=QCL.TracebilityID 
      WHERE QCL.InvID='$OrderID' group by QCL.TracebilityID ,QCL.HarvestDt
      ORDER BY QCL.`DayCode` ASC, MIN(QCL.CLID) ASC";

        //  ECHO $SqlQcBreakupData;
        $StmtQcBreakupData = $this->conn->prepare($SqlQcBreakupData);
        $StmtQcBreakupData->execute();
        while ($RowQcBreakupData = $StmtQcBreakupData->fetch(PDO::FETCH_ASSOC)) {
            /*    $ArrayQcBreakUpDetails['VarietyFullName'] = $RowQcBreakupData['Variety'];
                $ArrayQcBreakUpDetails['VarietyShortName'] = $RowQcBreakupData['ShortName'];
                $ArrayQcBreakUpDetails['PreparationType'] = $RowQcBreakupData['PreparationType'];*/

            $TracebilityNumber = explode('-', $RowQcBreakupData['Tracebility']);
            $TracebilityArray = $this->splitTracebilityAndPondID(trim($RowQcBreakupData['Tracebility']));
            $TraceNumberWOBatchCode = removeStartingString($TracebilityArray[0]) . '-' . $TracebilityArray[1];
            $RowQcBreakupData['TraceNumberWOBatchCode'] = $TraceNumberWOBatchCode;
            $AryQcBreakUpData[] = $RowQcBreakupData;


        }
        return $AryQcBreakUpData;
    }

    public function splitTracebilityAndPondID($input)
    {
        // Regular expression to match the last occurrence of any special character and split the string
        if (preg_match('/^(.*?)([-\/_]+)([a-zA-Z0-9]+)$/', $input, $matches)) {
            return array($matches[1], $matches[3]);
        }

        // If no special character is found, return the original string and an empty string as the second part
        return array($input, '');
    }

    public function countNumberOfSeparators($str)
    {
        $specialCharCount = preg_match_all('/[^a-zA-Z0-9]/', $str);
        return $specialCharCount;
    }
}
?>