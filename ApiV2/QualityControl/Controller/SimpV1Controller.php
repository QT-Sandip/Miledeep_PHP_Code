<?php

include_once '../Global/Util.php';

include_once 'Controller/UtilController.php';
class SimpV1Controller
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

    public function getExporterData($RequestBody)
    {

        $OrderID = $RequestBody['OrderID'];
        $SqlCustomCount = "SELECT ExpName,SCI.CustomsInvNo,SPD.PoNo,SCI.ContainerNo ,TotalNetWt,ISF.BLDate,	
		SCD.ConsigneeName,SCD.ConsigneeIFTPCODE,PackerAddress,QtyUnit,SPD.PortofLoading,
		SPC.Name AS LoadingPort,ExpAddress,ImporterRecordAddress,ExpApprovalNo,
		ExpUsfdaNo,ExpFax,AED.ExpMailID,ExpPhoneNumber,ISF.BLNo, SCI.CInvDate FROM `ScrmPoData` SPD
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
        $BLNo = $dataRowExporter['BLNo'];

        $SqlTranshipData = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList` 
	    WHERE PIID='$OrderID' and Status=1 and FromTOPID='$LoadingPort'";

        //echo $SqlTranshipData; exit;
        $stmtTranshipData = $this->conn->prepare($SqlTranshipData);
        $stmtTranshipData->execute();
        $dataTranshipData = $stmtTranshipData->fetch(PDO::FETCH_ASSOC);
        $VesselName = $dataTranshipData['VesselName'];
        $VOYNO = $dataTranshipData['VOYNO'];

        $NetWeight = $this->utilController->getPriceDetails($OrderID, $QtyUnit);

        $NetWeight['NetWgtKgs'] = $NetWeight;
        // $NetWgtKgs = round($NetWeight['NetWgtKgs'], 3);
        $NetWeight = $NetWeight . '.000';

        $FileName = str_replace('/', '_', $dataRowExporter['CustomsInvNo']);

        $dataRowExporter['VesselName'] = $VesselName;
        $dataRowExporter['VOYNO'] = $VOYNO;
        // $dataRowExporter['NetWgtKgs'] = $NetWgtKgs;
        $dataRowExporter['NetWgtKgs'] = round($NetWeight['NetWgtKgs'], 3);
        $dataRowExporter['FileName'] = $FileName;

        $CInvDate = $dataRowExporter["CInvDate"];
        $tableName = 'QcShrimpUsers';

        try {
            // Step 1: Check if the table exists
            // This table is added only for coastal client
            $checkTableQuery = "SHOW TABLES LIKE :tableName";
            $stmtCheck = $this->conn->prepare($checkTableQuery);
            $stmtCheck->bindParam(':tableName', $tableName, PDO::PARAM_STR);
            $stmtCheck->execute();

            if ($stmtCheck->rowCount() > 0) {
                // Step 2: Table exists, proceed with the main query
                $SqlQcShrimpUsers = "SELECT * FROM `QcShrimpUsers` 
                             WHERE `EffectsFrom` < :CInvDate 
                             ORDER BY `EffectsFrom` DESC 
                             LIMIT 1;";

                $stmt = $this->conn->prepare($SqlQcShrimpUsers);
                $stmt->bindParam(':CInvDate', $CInvDate, PDO::PARAM_STR);
                $stmt->execute();
                $dataQcShrimpUsers = $stmt->fetch(PDO::FETCH_ASSOC);

                // Step 3: Handle fetched data
                if ($dataQcShrimpUsers) {
                    $dataRowExporter['RecipientName'] = $dataQcShrimpUsers["Name"];
                } else {
                    $dataRowExporter['RecipientName'] = null; // No matching records
                }
            } else {
                // Step 4: Handle table not existing
                $dataRowExporter['RecipientName'] = null;
                error_log("Table '$tableName' does not exist in the database.");
            }
        } catch (PDOException $e) {
            // Handle any database errors
            error_log("Database error: " . $e->getMessage());
            $dataRowExporter['RecipientName'] = null;
        }

        return $dataRowExporter;
    }


    public function getSimpDetails($RequestBody)
    {

        $OrderID = $RequestBody['OrderID'];

        $SqlFieldsList = "SPD.PortofLoading,FarmerName,FarmPhoneNumber,PurchaseLotCode,QCL.CLID,SPIVL.TypeProduct,SPIVL.Species,SPM.SpeciesName,
		SPIVL.Grade AS GradeName,AddressContinued,SPIVL.Species,SPIVL.ShortName,QCL.TracebilityID,
		Tracebility,RegAuthority,QCL.DayCode,SPD.QtyUnit,FarmerCode,
        GROUP_CONCAT(DISTINCT SPGD.Variety order by CLID asc SEPARATOR '<br> &nbsp;&nbsp;')  AS Variety 
        ,GROUP_CONCAT(DISTINCT QCL.McsGrade order by CLID asc SEPARATOR '<br> &nbsp;&nbsp;&nbsp; ') AS McsGrade,
        GROUP_CONCAT(DISTINCT APS.PackingStyle order by CLID asc SEPARATOR '<br> &nbsp;&nbsp;&nbsp;&nbsp;')  AS PackingStyle";

        $SqlCustomInvoicesQuery = "SELECT $SqlFieldsList FROM `QcCodeList` QCL
        LEFT JOIN QcTracebilityList QTL ON QTL.TraceID=QCL.TracebilityID
        LEFT JOIN ScrmPoData SPD ON SPD.PoID=QCL.InvID
        LEFT JOIN ScrmPoGradeData SPGD ON  QCL.PoGradeID=SPGD.ID
        LEFT JOIN ScrmPIVarietyList SPIVL ON SPIVL.VarietyID=SPGD.VarietyID
        LEFT JOIN `ScrmSpeciesMaster` as SPM on SPM.ShortCode=SPIVL.Species
        LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SPGD.PackingStyleID
        WHERE QCL.InvID='$OrderID' GROUP BY QTL.Tracebility,QCL.HarvestDt order by  CLID";

        //echo  $SqlCustomInvoicesQuery; 
        $stmt = $this->conn->prepare($SqlCustomInvoicesQuery);
        $stmt->execute();

        $AryCustomData = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $CodeListID = $dataRow['CLID'];
            $TraceID = $dataRow['TracebilityID'];
            $DayCode = $dataRow['DayCode'];
            $Species = $dataRow['Species'];
            $ScientificName = $dataRow['SpeciesName'];
            $ShortName = $dataRow['ShortName'];
            ;
            $QtyUnit = $dataRow['QtyUnit'];
            ;

            $SqlWeightsData = "SELECT HarvestingDate FROM `QcPhAbReports` WHERE 
	        InvID='$OrderID' AND TracebilityID='$TraceID'";

            //echo $SqlWeightsData;
            $stmtWeights = $this->conn->prepare($SqlWeightsData);
            $stmtWeights->execute();
            $dataRowWeights = $stmtWeights->fetch(PDO::FETCH_ASSOC);

            //$HarvestDate=explode('T',$dataRowWeights['HarvestingDate']);
            $HarvestDate = substr($dataRowWeights['HarvestingDate'], 10);

            $HarvestDt = $HarvestDate[0];
            if ($HarvestDate != '') {
                $HarvestDt = date("m-d-Y", strtotime($HarvestDate));
            } else {
                $HarvestDt = "";
            }

            $SqlRMRWeights = "SELECT sum(McsTotal) as McsTotal,Yeild,sum(TotalSlabs*SlabWeight*MFactor*McsTotal) 
            as NetWgtKgs ,sum(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs
			FROM `QcCodeList` as QCL 
			LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
			left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
			left join AdminPackingStyles As APS on APS.PCKID=QCL.PackingStyleID
			WHERE QCL.InvID='$OrderID' and DayCode='$DayCode'   
			and TracebilityID='$TraceID'"; //";   QCL.CLID=$CodeListID
            $stmtRMRWeights = $this->conn->prepare($SqlRMRWeights);
            $stmtRMRWeights->execute();
            $RowRMRWeights = $stmtRMRWeights->fetch(PDO::FETCH_ASSOC);


            if ($QtyUnit == 'KGS') {
                $FinishedQty = $RowRMRWeights['NetWgtKgs'];
            } else {
                $FinishedQty = $RowRMRWeights['NetWgtLbs'] * 0.454;
            }
            $FinishedQty = round($FinishedQty, 3);
            // $FinishedQty=$FinishedQty.'.000';

            $Yield = $RowRMRWeights['Yeild'];
            $UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
            // $ReceivedQty = $UsedQty = round($UsedQty); 
            $ReceivedQty = $UsedQty = round($UsedQty, 3);



            // $UsedQty=round($UsedQty);
            $UsedQty = number_format($UsedQty, 3);
            $FinishedQty = number_format($FinishedQty, 3);
            // $UsedQty=$UsedQty.'.000';		

            if ($Species == 'VM') {
                $ScientificName = 'PENAEUS VANNAMEI';
                $CommodityMarketName = 'White Leg Shrimp';
                $ProductASFIS = 'PNV';
            } else {
                $ProductASFIS = 'GIT';
                //   $ScientificName	='Penaeus Monodon';  
                $CommodityMarketName = 'GAINT TIGER PRAWN';
            }

            if ($dataRow['TypeProduct'] == 'RAW') {
                $dataRow['TypeProduct'] = '';
            } else if ($dataRow['TypeProduct'] == 'COOKED') {
                $dataRow['TypeProduct'] = 'C';
            }


            $ArrayCustomDetails['SNO'] = ++$i;
            $ArrayCustomDetails['ID'] = $dataRow['PoID'];
            $ArrayCustomDetails['FarmerName'] = $dataRow['FarmerName'];

            $DayCode = $this->utilController->getDayCodeDate($DayCode);
            $Data['DayCode'] = $DayCode;

            $TracebilityNumber = explode('-', $dataRow['Tracebility']);
            $PondNumber = $TracebilityNumber[2];
            // $Tracebility = $TracebilityNumber[1];
            $Tracebility = $dataRow['Tracebility'];

            $ReceivedLotIdentifiers = $TracebilityNumber[1] . '-' . $TracebilityNumber[2];
            $ProductionDt = date("m/d/Y", strtotime($DayCode));
            $HarvestDt = date("m/d/Y", strtotime($DayCode));

            //  $ArrayCustomDetails['InvoiceDate'] = date_format($date, "d-m-Y");
            $ArrayCustomDetails['AddressContinued'] = $dataRow['AddressContinued'];
            $ArrayCustomDetails['Tracebility'] = $Tracebility;
            $ArrayCustomDetails['ReceivedLotIdentifiers'] = $ReceivedLotIdentifiers;
            $ArrayCustomDetails['RegAuthority'] = $dataRow['RegAuthority'];
            $ArrayCustomDetails['DayCode'] = $DayCode;
            $ArrayCustomDetails['HarvestDt'] = $HarvestDt;
            $ArrayCustomDetails['ProductionDt'] = $ProductionDt;
            $ArrayCustomDetails['McsGrade'] = $dataRow['McsGrade'];
            $ArrayCustomDetails['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayCustomDetails['Variety'] = $dataRow['Variety'];
            $ArrayCustomDetails['ScientificName'] = $ScientificName;
            $ArrayCustomDetails['CommodityMarketName'] = $CommodityMarketName;
            $ArrayCustomDetails['ProductASFIS'] = $ProductASFIS;
            $ArrayCustomDetails['PurchaseManager'] = 'SDSV SUBBARAJU';
            $ArrayCustomDetails['PurchaseManagerNumber'] = '8790917555';
            // $ArrayCustomDetails['FinishedWeight'] = $dataRowWeights['FinishedWeight'];
            $ArrayCustomDetails['FinishedWeight'] = $FinishedQty;
            $ArrayCustomDetails['ReceivedQty'] = $UsedQty;
            //$ArrayCustomDetails['ReceivedQty'] = $dataRowWeights['ReceivedQty'];
            $ArrayCustomDetails['VarietyShortName'] = $dataRow['ShortName'];
            $ArrayCustomDetails['HarvestDate'] = $HarvestDt;
            $ArrayCustomDetails['GradeName'] = $dataRow['GradeName'];
            $ArrayCustomDetails['PondNumber'] = $PondNumber; //doubt
            $ArrayCustomDetails['FarmerPhoneNumber'] = $dataRow['FarmPhoneNumber']; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['PurchaseBillNumber'] = $dataRow['PurchaseLotCode']; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['TypeProduct'] = $dataRow['TypeProduct']; //NO FEILD IN DATA BASE
            $ArrayCustomDetails['SpeciesCode'] = $dataRow['Species']; //NO FEILD IN DATA BASE


            $AryCustomData[] = $ArrayCustomDetails;
        }



        //echo  $RequestBody['qc'];
        $data['EditLink'] = "DocAddReports?PoID=";
        $data['Data'] = $AryCustomData;

        $data['QcBreakUpDetails'] = $this->getQcBreakUpDetails($RequestBody);

        return $data;
    }

    public function getQcBreakUpDetails($RequestBody)
    {
        $OrderID = $RequestBody['OrderID'];

        $SqlQcBreakupData = "SELECT  Variety,PreparationType,ShortName FROM `ScrmPoGradeData` SPGD
LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID=SPGD.VarietyID WHERE InvID='$OrderID' group by SPGD.Variety";
        $StmtQcBreakupData = $this->conn->prepare($SqlQcBreakupData);
        $StmtQcBreakupData->execute();
        while ($RowQcBreakupData = $StmtQcBreakupData->fetch(PDO::FETCH_ASSOC)) {


            $ArrayQcBreakUpDetails['VarietyFullName'] = $RowQcBreakupData['Variety'];
            $ArrayQcBreakUpDetails['VarietyShortName'] = $RowQcBreakupData['ShortName'];
            $ArrayQcBreakUpDetails['PreparationType'] = $RowQcBreakupData['PreparationType'];

            $AryQcBreakUpData[] = $ArrayQcBreakUpDetails;
        }

        return $AryQcBreakUpData;
    }
}
