<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class ShipmentAdviceController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }


    function getDetails($RequestBody)
    {
        $PIID = $RequestBody['PIID'];

        $sqlFeilds = "CustomsInvNo, SCI.ExporterID, SCI.PiID, CInvDate, SCI.ContainerNo,SCI.ShippingLineID, ExpID,ExpName, ConsigneeID,
        PoNo, PoID, PoDate,LinerID,LinerName, SID.PIID, BLNo, SPD.PortofLoading, SPC1.Name AS PortofLoadingName, SCL1.name AS PortCountry,
        ExpAddress, SPD.FinalDestination, SPC2.Name AS FinalDestinationName, SCL2.name AS FinalCountry, ConsigneeName";

        $sqlQuery = "SELECT $sqlFeilds  FROM ScrmCustomInvoices AS SCI
        LEFT JOIN AdminExporterDetails AS AED ON SCI.ExporterID = AED.ExpID
        LEFT JOIN ScrmPoData AS SPD ON SCI.PiID = SPD.PoID
        LEFT JOIN ScrmISFDetails AS SID ON SCI.PiID = SID.PIID
        LEFT JOIN ScrmLinerMaster AS SLM ON SCI.ShippingLineID = SLM.LinerID
        LEFT JOIN ScrmPortCities AS SPC1 ON SPC1.PortId = SPD.PortofLoading
        LEFT JOIN ScrmCountriesList AS SCL1 ON SPC1.Country = SCL1.id
        LEFT JOIN ScrmPortCities AS SPC2 ON SPC2.PortId = SPD.FinalDestination
        LEFT JOIN ScrmCountriesList AS SCL2 ON SPC2.Country = SCL2.id
        LEFT JOIN ScrmConsigneeDetails AS SCDS ON SPD.ConsigneeID = SCDS.ConID
        WHERE SCI.PiID = '$PIID'";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        $PiID = $dataRow['PiID'];
        $ExporterID = $dataRow['ExporterID'];

        $sqlVessels = " SELECT VesselName,FromTOPID, FromDate, ToTOPID, TranshipDt FROM ScrmTranshipmentPortsList WHERE PIID ='$PiID'";
        $stmtVessels = $this->conn->prepare($sqlVessels);
        $stmtVessels->execute();
        while ($dataVessels = $stmtVessels->fetch(PDO::FETCH_ASSOC)) {
            $AryVessels['VesselName'] = $dataVessels['VesselName'];
            $AryVessels['ETD'] = $dataVessels['FromTOPID'] . ' ON ' . date('d-m-Y', strtotime($dataVessels['FromDate']));
            $AryVessels['ETA'] = $dataVessels['ToTOPID'] . ' ON ' . date('d-m-Y', strtotime($dataVessels['TranshipDt']));

            $VesselsData[] = $AryVessels;
        }

        $sqlVarietyFields = "sum(Mcs) As VarietyMcs,Variety,Grade,PackingStyle,Brand, PackingStyleID,BAP,PrintStatus";

        $sqlVarietySelect = "SELECT $sqlVarietyFields FROM `ScrmPoGradeData` AS PGD 
        LEFT JOIN `AdminPackingStyles` AS Aps ON PackingStyleID=Aps.PCKID 
        LEFT JOIN `ScrmBrandsMaster` AS SBM ON SBM.BrandName=PGD.Brand 
        WHERE InvId='$PiID' GROUP BY Variety,PackingStyleID,Brand";

        $stmtVariety = $this->conn->prepare($sqlVarietySelect);
        $stmtVariety->execute();

        $TotalMcs = 0;
        while ($dataVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC)) {
            $AryVarietyBrkUpData = [];

            $VarietyMcs = $dataVariety['VarietyMcs'];
            $Variety = $dataVariety['Variety'];
            $Brand = $dataVariety['Brand'];
            $PackingStyleID = $dataVariety['PackingStyleID'];

            $sqlVarietyBreakUp = "SELECT Mcs,Grade FROM `ScrmPoGradeData` AS PGD 
            LEFT JOIN AdminPackingStyles AS PS ON PGD.PackingStyleID=PS.PCKID 
            WHERE InvId='$PiID' AND Variety='$Variety' AND Brand='$Brand' AND PackingStyleID='$PackingStyleID' ORDER BY abs(Grade)";
            $stmtVarietyBreakUp = $this->conn->prepare($sqlVarietyBreakUp);
            $stmtVarietyBreakUp->execute();
            while ($dataVarietyBreakUp = $stmtVarietyBreakUp->fetch(PDO::FETCH_ASSOC)) {
                $AryVarietyBrkUp['Mcs'] = $dataVarietyBreakUp['Mcs'];
                $AryVarietyBrkUp['Grade'] = $dataVarietyBreakUp['Grade'];
                $AryVarietyBrkUpData[] = $AryVarietyBrkUp;
            }

            $AryVariety['VarietyMcs'] = $VarietyMcs;
            $AryVariety['Variety'] = $Variety;
            $AryVariety['Brand'] = $Brand;
            $AryVariety['PackingStyle'] = $PackingStyleID;
            $AryVariety['Grade'] = $dataVariety['Grade'];
            $AryVariety['PackingStyle'] = $dataVariety['PackingStyle'];
            $AryVariety['BAP'] = $dataVariety['BAP'];
            $AryVariety['PrintStatus'] = $dataVariety['PrintStatus'];
            $AryVariety['Mcs'] = $AryVarietyBrkUpData;

            $AryVarietyData[] = $AryVariety;
            $TotalMcs += $VarietyMcs;
        }


        $sqlHF = "SELECT LogoUrl,HeaderUrl,FooterUrl,WaterMark,ExpName, InvShortCode FROM  `QcDocLetterHeads` as QDLH
        LEFT JOIN `AdminExporterDetails` as AED on QDLH.ProcessorID=AED.ExpID where QDLH.ProcessorID='$ExporterID'";
        $stmtHF = $this->conn->prepare($sqlHF);
        $stmtHF->execute();
        $dataHF = $stmtHF->fetch(PDO::FETCH_ASSOC);



        $InvoiceVal = $this->utilController->getPaymentValues($PiID, '');

        $InvVal = round($InvoiceVal['InvValueUSD'], 2);

        $ArrayList['PiID'] = $PiID;
        $ArrayList['CustomsInvNo'] = $dataRow['CustomsInvNo'];
        $ArrayList['CInvDate'] = $dataRow['CInvDate'];
        $ArrayList['PoDate'] = $dataRow['PoDate'];
        $ArrayList['InVal'] = round($InvVal, 3);
        $ArrayList['ContainerNo'] = $dataRow['ContainerNo'];
        $ArrayList['ExporterID'] = $ExporterID;
        $ArrayList['ExpName'] = $dataRow['ExpName'];
        // $ArrayList['LogoUrl'] = $dataHF['LogoUrl'];
        $ArrayList['HeaderUrl'] = $dataHF['HeaderUrl'];
        $ArrayList['FooterUrl'] = $dataHF['FooterUrl'];
        $ArrayList['Client'] = $dataHF['InvShortCode'];
        $ArrayList['ExpAddress'] = $dataRow['ExpAddress'];
        $ArrayList['ConsigneeID'] = $dataRow['ConsigneeID'];
        $ArrayList['ConsigneeName'] = $dataRow['ConsigneeName'];
        $ArrayList['PoNo'] = $dataRow['PoNo'];
        $ArrayList['BLNo'] = $dataRow['BLNo'];
        $ArrayList['ShippingLineID'] = $dataRow['ShippingLineID'];
        $ArrayList['LinerName'] = $dataRow['LinerName'];
        $ArrayList['PortofLoading'] = $dataRow['PortofLoading'];
        $ArrayList['PortofLoadingName'] = $dataRow['PortofLoadingName'] . ', ' . $dataRow['PortCountry'];
        $ArrayList['PortCountry'] = $dataRow['PortCountry'];
        $ArrayList['FinalDestination'] = $dataRow['FinalDestination'];
        $ArrayList['FinalDestinationName'] = $dataRow['FinalDestinationName'] . ', ' . $dataRow['FinalCountry'];
        $ArrayList['FinalCountry'] = $dataRow['FinalCountry'];
        $ArrayList['VesselsData'] = $VesselsData;
        $ArrayList['VarietyData'] = $AryVarietyData;
        $ArrayList['TotalMcs'] = $TotalMcs;

        $ArraySpeciesData[] = $ArrayList;


        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Data'] = $ArraySpeciesData;

        return $data;
    }
}
