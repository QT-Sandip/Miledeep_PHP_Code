<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class TreatmentRegisterReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = 'Success';
    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    public function getTreatmentRegisterReportDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PlantID = $RequestBody['PlantID'];
        $LineNumber = $RequestBody['LineNumber'];
        $Query = '';

        if ($PlantID != '') {
            $Query .= " AND  PlantID='$PlantID' ";
        }

        if ($LineNumber != '') {
            $Query .= " and PDH.LineNumber='$LineNumber'";
        }
        $DateFilter = " ArrivalDate between '$FromDate' and '$ToDate'";

        $i = 0;

        $sqlFields = "SckID,StationName,TrackCode,ArrivalDate,(select distinct LotNumber from RmLotDetatils RLD where RLD.RmLotID=PDH.LotID) as LotNumber,DATE_FORMAT(SoakingDate, '%d-%m-%Y') AS SoakingDate,TubNumber,
        LineNumber,PHVL.ShortName as Variety,PDH.VAGrade as Grade,
            ChemicalName,ChemicalQty,SaltQty as Salt,SoakInCount as SourceCount,SoakInQty as InQuantity, SoakInTime as StartTime,TimePeriod,
            SoakOutCount as OutCount,SoakOutQty as OutQuantity,PDH.Remarks,PHVL.Species,
            COALESCE(PFD.TotalFreezingWeight, 0) AS TotalFreezingWeight,IceQtyPercentage,WaterQtyPercentage,SoakInTemperature,SoakOutTemperature,ShiftName";//sriram 14-02-25


        $sqlSoakingBreakup = "SELECT  $sqlFields FROM `ProdSoakingDetails`  PDH        
            left join AdminShiftDetails ASD on ASD.ShiftID=PDH.Shift 		
            left join PpcHLVAVarietyList PHVL on PHVL.VarietyID=PDH.Variety 		
            left join GsChemicalMaster GCM on GCM.ChemID=PDH.ChemicalID
            left join RmReceivingDetails RRD on RRD.ReceivingID=PDH.TrackID 
            left join RmProcessingStations RPS on RPS.ProcessingStationID=PDH.PlantID
            LEFT JOIN (SELECT TrackID, TubNo, SUM(ProductionWeight + RejectionWeight) AS TotalFreezingWeight FROM ProdFreezingData 
            GROUP BY TrackID, TubNo) PFD ON PFD.TrackID = PDH.TrackID AND PFD.TubNo = PDH.TubNumber
            where $DateFilter  $Query ORDER BY `SoakingDate` asc"; 


        $stmtSoakingBreakup = $this->conn->prepare($sqlSoakingBreakup);
        $stmtSoakingBreakup->execute();

        while ($dataRowSoakingBreakup = $stmtSoakingBreakup->fetch(PDO::FETCH_ASSOC)) {

            $TotalSoakInQty += $dataRowSoakingBreakup['InQuantity'];
            // $TotalSoakOutQty += $dataRowSoakingBreakup['TotalFreezingWeight'];
           
            // $SoakingYield = ($dataRowSoakingBreakup['TotalFreezingWeight'] / $dataRowSoakingBreakup['InQuantity']) * 100;
            // // $SoakingYield = ($dataRowSoakingBreakup['OutQuantity'] / $dataRowSoakingBreakup['InQuantity']) * 100;
           $StartTime= $dataRowSoakingBreakup['StartTime'];
           $TimePeriod= $dataRowSoakingBreakup['TimePeriod'];
           $EndTime = date("H:i", strtotime($StartTime . " +$TimePeriod minutes"));

           $SoakOutQty = $dataRowSoakingBreakup['OutQuantity'];
           $SoakInQty = $dataRowSoakingBreakup['InQuantity'];
           // $PickUp = ($SoakOutQty / $SoakInQty) / 100;
           $PickUp = ($SoakOutQty - $SoakInQty) / $SoakInQty * 100;

            $AryBreakUp['SNo'] = ++$i;
            $AryBreakUp['ArrivalDate'] = $this->utilController->getFormatedDate($dataRowSoakingBreakup['ArrivalDate']);
            $AryBreakUp['TrackCode'] = $dataRowSoakingBreakup['TrackCode'];
            $AryBreakUp['LotNo'] = $dataRowSoakingBreakup['LotNumber'];
            $AryBreakUp['Plant'] = $dataRowSoakingBreakup['StationName'];
            $AryBreakUp['SoakingDate'] = $dataRowSoakingBreakup['SoakingDate'];
            $AryBreakUp['TubNumber'] = $dataRowSoakingBreakup['TubNumber'];
            $AryBreakUp['LineNumber'] = $dataRowSoakingBreakup['LineNumber'];
            $AryBreakUp['Variety'] = $dataRowSoakingBreakup['Variety'];
            $AryBreakUp['Grade'] = $dataRowSoakingBreakup['Grade'];
            $AryBreakUp['ChemicalName'] = $dataRowSoakingBreakup['ChemicalName'];
            $AryBreakUp['ChemicalQty'] = $dataRowSoakingBreakup['ChemicalQty'];
            $AryBreakUp['Salt'] = $dataRowSoakingBreakup['Salt'];
            $AryBreakUp['SourceCount'] = $dataRowSoakingBreakup['SourceCount'];
            $AryBreakUp['InQuantity'] = $dataRowSoakingBreakup['InQuantity'];
            $AryBreakUp['StartTime'] = $dataRowSoakingBreakup['StartTime'];
            $AryBreakUp['TimePeriod'] = $dataRowSoakingBreakup['TimePeriod'];
            $AryBreakUp['OutCount'] = $dataRowSoakingBreakup['OutCount'];
            $AryBreakUp['EndTime'] = $EndTime;
            $AryBreakUp['IceQtyPercentage'] = $dataRowSoakingBreakup['IceQtyPercentage'];
            $AryBreakUp['WaterQtyPercentage'] = $dataRowSoakingBreakup['WaterQtyPercentage'];
            $AryBreakUp['SoakInTemperature'] = $dataRowSoakingBreakup['SoakInTemperature'];
            $AryBreakUp['SoakOutTemperature'] = $dataRowSoakingBreakup['SoakOutTemperature'];
            $AryBreakUp['ShiftName'] = $dataRowSoakingBreakup['ShiftName'];
            $AryBreakUp['PickUp'] = number_format($PickUp,2);           


            //sriram 14-02-25
            // $AryBreakUp['OutQuantity'] = $dataRowSoakingBreakup['OutQuantity'];
            $AryBreakUp['OutQuantity'] = $dataRowSoakingBreakup['TotalFreezingWeight'];
            $AryBreakUp['Remarks'] = $dataRowSoakingBreakup['Remarks'];
            $AryBreakUp['Species'] = $dataRowSoakingBreakup['Species'];
            $AryBreakUp['SoakingYield'] = number_format($SoakingYield, 2);

            $ArrayBreakupData[] = $AryBreakUp;

        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $ArrayTotals['TotalSoakInQty'] = $TotalSoakInQty;
        $ArrayTotals['TotalSoakOutQty'] = $TotalSoakOutQty;
        $Data['SoakingData'] = $ArrayBreakupData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }


}
