<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class PackingDataController
{
    private $conn;
    // private $db_table = "ScrmPoData";
    private $utilController;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetPackingData($RequestBody)
    {
        //$RecordsPerPage = 100;

        // $FromDate = $RequestBody['FromDate'];
        // $ToDate = $RequestBody['ToDate'];
        $ExporterID = $RequestBody['Exporter'];
        $ProcessorID = $RequestBody['Processor'];
        $ConsigneeID = $RequestBody['Buyer'];
        $Brand = $RequestBody['Brand'];
        $Priority = $RequestBody['Priority'];
        $SearchKey = $RequestBody['SearchKey'];


        $Query = '';
        if ($ExporterID != "") {
            $Query .= 'and POD.ExporterID in (' . $ExporterID . ')';
            // $Query .= 'and POD.ExporterID in (' . $ExporterID . ')';
        }

        if ($ProcessorID != "") {
            $Query .= 'and POD.ProcessorID in (' . $ProcessorID . ')';
        } 
        
        if ($ConsigneeID != "") {
            $Query .= 'and POD.ConsigneeID in (' . $ConsigneeID . ')';
        } 
        
        if ($Priority != "") {
            $Query .= "and SPKD.Priority ='$Priority'";
        }

        // $DateFilter = "between '$FromDate' and '$ToDate'";

        
        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', CustomsInvNo) LIKE '%$SearchKey%'";
        }


        
        // $SqlFieldsList = "POD.PoID,PGD.ID as POGradeId,POD.PoNo,POD.ShipByDate, PGD.Brand,PGD.Grade,PGD.Mcs,PGD.FreezingType, AED.ExpApprovalNo,
        // APS.PackingStyle, SPV.PreparationType,SPV.ShortName,SPV.Species,
        // SCD.ConsigneeCode, SCI.CustomsInvNo, SCI.QCertificateNo,  SPCD.Name as PortOfDischarge, SPCF.Name as FinalDestination,
        //   SPCL.Name as PortofLoading, SCL.name as PortofLoadingCountry, SPKD.OrderedDate,SPKD.AvalMcs,SPKD.OrderRemarks,SPKD.InnerBags,
        //   SPKD.Priority,SPKD.ApprovalDate";
          
          $SqlFieldsList = "POD.PoID,POD.PoNo,POD.ShipByDate,  AED.ExpApprovalNo,SOC.CerificationName,
        
        SCD.ConsigneeCode, SCI.CustomsInvNo, SCI.QCertificateNo,  SPCD.Name as PortOfDischarge, SPCF.Name as FinalDestination,
          SPCL.Name as PortofLoading, SCL.name as PortofLoadingCountry, SPKD.OrderedDate,SPKD.AvalMcs,SPKD.OrderRemarks,SPKD.InnerBags,
          SPKD.Priority,SPKD.ApprovalDate";

       
            
            // $SqlPackingData = "SELECT $SqlFieldsList FROM ScrmCustomInvoices as SCI  			 
			// left join  ScrmPoGradeData as PGD on SCI.PiID=PGD.InvId
			// left join  ScrmPoData as POD on SCI.PiID=POD.PoID
			// left join  ScrmConsigneeDetails as SCD on SCD.ConID=POD.ConsigneeID 

			// left join  ScrmPortCities as SPCD on SPCD.PortId=POD.PortOfDischarge
			// left join  ScrmPortCities as SPCF on SPCF.PortId=POD.FinalDestination 
			// left join  ScrmPortCities as SPCL on SPCL.PortId=POD.PortofLoading 
			// left join  ScrmCountriesList as SCL on SCL.id=SPCL.Country 
			// left join  AdminExporterDetails as AED on AED.ExpID=POD.ProcessorID 
			// left join  AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
			// left join  ScrmPIVarietyList as SPV on SPV.VarietyID=PGD.VarietyID 
			// left join  ScrmPackingData as SPKD on SPKD.POGradeId=PGD.ID 
            // left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=POD.PoID
            //  where (SDB.TransportedDate ='' or SDB.TransportedDate is null) and POD.Status not in (2,3,4) $Query"; 
             
             
             $SqlPackingData = "SELECT $SqlFieldsList FROM  ScrmPoData as POD 			 
			
			left join   ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join  ScrmConsigneeDetails as SCD on SCD.ConID=POD.ConsigneeID 

			left join  ScrmPortCities as SPCD on SPCD.PortId=POD.PortOfDischarge
			left join  ScrmPortCities as SPCF on SPCF.PortId=POD.FinalDestination 
			left join  ScrmPortCities as SPCL on SPCL.PortId=POD.PortofLoading 
			left join  ScrmCountriesList as SCL on SCL.id=SPCL.Country 
			left join  AdminExporterDetails as AED on AED.ExpID=POD.ProcessorID 
			left join  ScrmOrderCertification as SOC on SOC.CerificationID=POD.Certification 
			
			left join  ScrmPackingData as SPKD on SPKD.PIID=POD.PoID 
            left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=POD.PoID
             where (SDB.TransportedDate ='' or SDB.TransportedDate is null) and POD.Status not in (2,3,4) $Query";

        $StmtPackingData = $this->conn->prepare($SqlPackingData);
        $StmtPackingData->execute();

       
        $i = 0;
        // $TotalNetWeightKgs = 0;
        // $TotalNetWeightLbs = 0;
        // $TotalFobValueINR = 0;
        // $TotalFOBValueUSD = 0;
        // $TotalInvoices = 0;
        while ($RowPackingData = $StmtPackingData->fetch(PDO::FETCH_ASSOC)) {

            $PoID = $RowPackingData['PoID'];
            $DocType = 'CMI';
           // $QtyUnit = $RowPackingData['QtyUnit'];
          //  $ArrayPaymentValues = $this->utilController->getPaymentValues($PoID, $DocType);
            // $FobValueUSD = $ArrayPaymentValues['FOBValueUSD'];
            // $FobValueINR = $ArrayPaymentValues['FOBValueINR'];



            $SqlExt = "PGD.ID as POGradeId,PGD.Brand,PGD.Grade,PGD.Mcs,PGD.FreezingType,APS.PackingStyle, 
            SPV.PreparationType,SPV.ShortName,SPV.Species,SPV.Treatment";


            $SqlBreakup = "SELECT  $SqlExt FROM `ScrmPoGradeData`  as PGD              
            left join  AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
			left join  ScrmPIVarietyList as SPV on SPV.VarietyID=PGD.VarietyID WHERE InvId='$PoID'";

            $StmtBreakup = $this->conn->prepare($SqlBreakup);
            $StmtBreakup->execute();
           while($RowBreakup = $StmtBreakup->fetch(PDO::FETCH_ASSOC)){
$ArrayGrade[]=$RowBreakup['Grade'];
$ArrayMcs[]=$RowBreakup['Mcs'];
$ArrayBrand[]=$RowBreakup['Brand'];
$ArrayFreezingType[]=$RowBreakup['FreezingType'];
$ArrayPackingStyle[]=$RowBreakup['PackingStyle'];
$ArrayTreatment[]=$RowBreakup['Treatment'];

$ArrayVariety[]=$RowBreakup['PreparationType'].' '.$RowBreakup['Species'].' '.$RowBreakup['ShortName'];

$Grade=implode(',',$ArrayGrade);
$Mcs=array_sum($ArrayMcs);
$Brand=implode(',',array_unique($ArrayBrand));
$FreezingType=implode(',',array_unique($ArrayFreezingType));
$PackingStyle=implode(',',array_unique($ArrayPackingStyle));
$Variety=implode(',',array_unique($ArrayVariety));
$Treatment=implode(',',array_unique($ArrayTreatment));

           }
           
           unset($ArrayGrade);
           unset($ArrayMcs);
           unset($ArrayBrand);
           unset($ArrayFreezingType);
           unset($ArrayPackingStyle);
           unset($ArrayVariety);
           unset($ArrayTreatment);
//echo $Grade; 
          

            //$PortofLoadingCountry = $RowPackingData['PortofLoading'] . ' , ' . $RowPackingData['PortofLoadingCountry'];


            $AryPackingData['SNO'] = ++$i;
            $AryPackingData['OrderID'] = $RowPackingData['PoID'];
            //$AryPackingData['POGradeId'] = $RowPackingData['POGradeId'];
            $AryPackingData['Buyer'] = $RowPackingData['ConsigneeCode'];
            $AryPackingData['Destination'] = $RowPackingData['FinalDestination'];
            $AryPackingData['Shipment'] = $this->utilController->getFormatedDateEmpty($RowPackingData['ShipByDate']);
            $AryPackingData['PoNumber'] = $RowPackingData['PoNo'];
            $AryPackingData['Brand'] = $Brand;
            $AryPackingData['Pro'] = 'p - '.$RowPackingData['ExpApprovalNo'];
            $AryPackingData['Pack'] = $PackingStyle;
            $AryPackingData['Form'] = $FreezingType;
            $AryPackingData['Variety'] = $Variety;
            $AryPackingData['Grade'] = $Grade;
            $AryPackingData['Spec'] = $Treatment.','.$RowPackingData['CerificationName'];
            $AryPackingData['Bup'] = $Mcs;
            $AryPackingData['ConfirmationDate'] = $RowPackingData['ApprovalDate'];
            $AryPackingData['OrderDate'] = $RowPackingData['OrderedDate'];
            $AryPackingData['OrderRemarks'] = $RowPackingData['OrderRemarks'];
            $AryPackingData['MasterCases'] = $RowPackingData['AvalMcs'];
            $AryPackingData['InnerBags'] = $RowPackingData['InnerBags'];
            $AryPackingData['Priority'] = $RowPackingData['Priority'];
            

            // $TotalNetWeightKgs += $NetWgtKgs;
            // $TotalNetWeightLbs += $NetWgtLbs;
            // $TotalFobValueINR += $FobValueINR;
            // $TotalFOBValueUSD += $FobValueUSD;

            $ArrayPackingData[] = $AryPackingData;
            


        }
        // $ArrayTotals['TotalInvoices'] = $i;
        // $ArrayTotals['TotalNetWeightKgs'] = number_format($TotalNetWeightKgs,3);
        // $ArrayTotals['TotalNetWeightLbs'] = number_format($TotalNetWeightLbs,3);
        // $ArrayTotals['TotalFobValueINR'] = number_format($TotalFobValueINR,2);
        // $ArrayTotals['TotalFOBValueUSD'] = number_format($TotalFOBValueUSD,2);
        
        // $data['Totals'] = $ArrayTotals;
        $data['PackingData'] = $ArrayPackingData;

        return $data;
    }

}
?>