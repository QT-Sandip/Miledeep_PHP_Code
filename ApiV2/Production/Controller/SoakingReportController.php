<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class SoakingReportController
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

    public function getSoakingReportDetails($RequestBody)
    {
        if(!empty($RequestBody['methodType']) && $RequestBody['methodType'] == 'getLinersByPlant'){
            $results = $this->getLinersByPlant($RequestBody['plantID']);
            $Data['Error'] = null;
            $Data['Msg'] = 'Success';            
            $Data['Data'] = $results;
            return $Data;
        }
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PlantID = $RequestBody['PlantID'];
        $LineNumber = $RequestBody['LineNumber'];
        $SoakingDate = $RequestBody['SoakingDate'];
        $ProductType = $RequestBody['ProductType'];
         $ArrivalID = $RequestBody['ArrivalID'];
        $Query = '';

        if ($PlantID != '') {
            $Query .= " AND  PDH.PlantID='$PlantID' ";
        }

        if ($LineNumber != '') {
            $Query .= " and PDH.LineNumber='$LineNumber'";
        }

        if ($ProductType != '') {
            $Query .= " AND  PHVL.TypeProduct='$ProductType' ";
        }
         if ($ArrivalID != '') {
			$Query .= " AND RLD.RmReferenceID = '$ArrivalID'";
		}
        $DateFilter = '';

        if($SoakingDate !='' ){
            $DateFilter = "  PDH.SoakingDate='$SoakingDate'";
        }else{
            if($FromDate !=''){
            $DateFilter .= " ArrivalDate >= '$FromDate'";
            }
             if($ToDate !=''){
                 $DateFilter .= "AND  ArrivalDate <= '$ToDate'";
            }
           // $DateFilter = " ArrivalDate between '$FromDate' and '$ToDate'";
        }
        // echo print_r($RequestBody);
        // exit;

        //  $DateFilter = " ArrivalDate between '$FromDate' and '$ToDate'";

        $i = 0;

        // $sqlFields = "SckID,StationName,TrackCode,ArrivalDate,(select distinct LotNumber from RmLotDetatils RLD where RLD.RmLotID=PDH.LotID) as LotNumber,DATE_FORMAT(SoakingDate, '%d-%m-%Y') AS SoakingDate,TubNumber,PDH.LineNumber,PHVL.ShortName as Variety,PDH.VAGrade as Grade,
        //     ChemicalName,ChemicalQty,SaltQty as Salt,SoakInCount as SourceCount,SoakInQty as InQuantity, SoakInTime as StartTime,TimePeriod,PHVL.TypeProduct,
        //     SoakOutCount as OutCount,SoakOutQty as OutQuantity,PDH.Remarks,PHVL.Species,
        //     COALESCE(PFD.TotalFreezingWeight, 0) AS TotalFreezingWeight,PHVL1.ShortName as valueAddVariety,PHVL2.ShortName as gradingVariety";//sriram 14-02-25

           $sqlFields = "SckID,StationName,TrackCode,ArrivalDate,(select distinct LotNumber from RmLotDetatils RLD where RLD.RmLotID=PDH.LotID) as LotNumber,DATE_FORMAT(SoakingDate, '%d-%m-%Y') AS SoakingDate,
             TubNumber,PDH.LineNumber,PHVL.ShortName as Variety,PDH.VAGrade as Grade,
            ChemicalName,ChemicalQty,SaltQty as Salt,SoakInCount as SourceCount,SoakInQty as InQuantity, SoakInTime as StartTime,TimePeriod,PHVL.TypeProduct,
            SoakOutCount as OutCount,SoakOutQty as OutQuantity,PDH.Remarks,PHVL.Species,
            COALESCE(PFD.TotalFreezingWeight, 0) AS TotalFreezingWeight,PHVL1.ShortName as valueAddVariety,PHVL2.ShortName as gradingVariety,InCounts"; //sriram 14-02-25


        $sqlSoakingBreakup = "SELECT  $sqlFields FROM `ProdSoakingDetails`  PDH
            LEFT JOIN RmLotDetatils RLD ON RLD.RmLotID = PDH.LotID        
            left join AdminShiftDetails ASD on ASD.ShiftID=PDH.Shift 		
            left join PpcHLVAVarietyList PHVL on PHVL.VarietyID=PDH.Variety 		
            left join GsChemicalMaster GCM on GCM.ChemID=PDH.ChemicalID
            left join RmReceivingDetails RRD on RRD.ReceivingID=PDH.TrackID 
            left join RmProcessingStations RPS on RPS.ProcessingStationID=PDH.PlantID

             LEFT JOIN PpcValueAddition PPV 
            ON (
                (PDH.ValueAdditionID IS NOT NULL AND PDH.ValueAdditionID <> 0 AND PPV.VLID = PDH.ValueAdditionID)
                OR
                ((PDH.ValueAdditionID IS NULL OR PDH.ValueAdditionID = 0) AND PDH.VABreakupID IS NOT NULL AND PDH.VABreakupID <> 0 AND PPV.GradingBreakupID = PDH.VABreakupID))
                
            LEFT JOIN PpcHLGradingBreakup PHG
            ON ( PHG.DataID = PDH.VABreakupID)
                    left join PpcHLVAVarietyList PHVL1 on PHVL1.VarietyID=PPV.VarietyID 
                    left join PpcHLVAVarietyList PHVL2 on PHVL2.VarietyID=PDH.Variety

            LEFT JOIN (SELECT TrackID, TubNo,SoakingID, SUM(ProductionWeight + RejectionWeight) AS TotalFreezingWeight FROM ProdFreezingData 
            GROUP BY TrackID, TubNo,SoakingID) PFD ON PFD.TrackID = PDH.TrackID AND PFD.TubNo = PDH.TubNumber AND PFD.SoakingID = PDH.SckID
            where $DateFilter  $Query ORDER BY `SoakingDate` asc";


            // echo $sqlSoakingBreakup;exit;

        $stmtSoakingBreakup = $this->conn->prepare($sqlSoakingBreakup);
        $stmtSoakingBreakup->execute();

        while ($dataRowSoakingBreakup = $stmtSoakingBreakup->fetch(PDO::FETCH_ASSOC)) {

            $TotalSoakInQty += $dataRowSoakingBreakup['InQuantity'];
            $TotalSoakOutQty += $dataRowSoakingBreakup['TotalFreezingWeight'];
            //sriram 14-02-25 -> OutQuantity is missing for so many records gettign quantity from freezing
            $SoakingYield = ($dataRowSoakingBreakup['TotalFreezingWeight'] / $dataRowSoakingBreakup['InQuantity']) * 100;
            // $SoakingYield = ($dataRowSoakingBreakup['OutQuantity'] / $dataRowSoakingBreakup['InQuantity']) * 100;

            $AryBreakUp['SNo'] = ++$i;
            $AryBreakUp['ArrivalDate'] = $this->utilController->getFormatedDate($dataRowSoakingBreakup['ArrivalDate']);
            $AryBreakUp['TrackCode'] = $dataRowSoakingBreakup['TrackCode'];
            $AryBreakUp['LotNo'] = $dataRowSoakingBreakup['LotNumber'];
            $AryBreakUp['Plant'] = $dataRowSoakingBreakup['StationName'];
            $AryBreakUp['SoakingDate'] = $dataRowSoakingBreakup['SoakingDate'];
            $AryBreakUp['TubNumber'] = $dataRowSoakingBreakup['TubNumber'];
            $AryBreakUp['LineNumber'] = $dataRowSoakingBreakup['LineNumber'];
            // $AryBreakUp['Variety'] = $dataRowSoakingBreakup['Variety'];

              $Variety = $dataRowSoakingBreakup['Variety'];
            $valueAddVariety = $dataRowSoakingBreakup['valueAddVariety'];
            $gradingVariety = $dataRowSoakingBreakup['gradingVariety'];


            if ($valueAddVariety !== null && $valueAddVariety != $Variety) {

                $AryBreakUp['Species'] = "<span <span style='color:red; '>" . $dataRowSoakingBreakup['Species'] . "</span>";
                $AryBreakUp['Variety'] = "<span <span style='color:red; '>" . $Variety . "</span>";
            } elseif ($valueAddVariety == null && $gradingVariety != $Variety) {

                $AryBreakUp['Species'] = "<span style='color:red; font-weight:bold;'>" . $dataRowSoakingBreakup['Species'] . "</span>";
                $AryBreakUp['Variety'] = "<span style='color:red; font-weight:bold;'>" . $Variety . "</span>";
            } else {

                $AryBreakUp['Species'] = $dataRowSoakingBreakup['Species'];
                $AryBreakUp['Variety'] = "<span>" . $Variety . "</span>";
            }

            $AryBreakUp['ProductType'] = $dataRowSoakingBreakup['TypeProduct'];
            $AryBreakUp['Grade'] = $dataRowSoakingBreakup['Grade'];
            $AryBreakUp['ChemicalName'] = $dataRowSoakingBreakup['ChemicalName'];
            $AryBreakUp['ChemicalQty'] = $dataRowSoakingBreakup['ChemicalQty'];
            $AryBreakUp['Salt'] = $dataRowSoakingBreakup['Salt'];
            $AryBreakUp['SourceCount'] = $dataRowSoakingBreakup['SourceCount'];
            $AryBreakUp['InQuantity'] = $dataRowSoakingBreakup['InQuantity'];
            $AryBreakUp['StartTime'] = $dataRowSoakingBreakup['StartTime'];
            $AryBreakUp['TimePeriod'] = $dataRowSoakingBreakup['TimePeriod'];
            $AryBreakUp['OutCount'] = $dataRowSoakingBreakup['OutCount'];
            //sriram 14-02-25
            // $AryBreakUp['OutQuantity'] = $dataRowSoakingBreakup['OutQuantity'];
            $AryBreakUp['OutQuantity'] = $dataRowSoakingBreakup['TotalFreezingWeight'];
            $AryBreakUp['Remarks'] = $dataRowSoakingBreakup['Remarks'];
            // $AryBreakUp['Species'] = $dataRowSoakingBreakup['Species'];
            $AryBreakUp['SoakingYield'] = number_format($SoakingYield, 2);
            $AryBreakUp['InCounts'] = $dataRowSoakingBreakup['InCounts'];

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

    function getArrivalsByFinancialYear($RequestBody)
	{
		//   error_log(print_r($RequestBody, true));
        // echo print_r($RequestBody);
        // exit;
		$fromDate = $RequestBody['FromDate'];
		$toDate = $RequestBody['ToDate'];
		$PlantID = $RequestBody['PlantID'];
        $soakingDate = $RequestBody['SoakingDate'];
        if($soakingDate){
            $ArrayArrivalList = $this->getArrivalsByFinancialYearBySoakingDate($RequestBody);
        }else{
            if (!$fromDate && !$toDate) {
                return false;
            }
            $Query = "";
            if ($fromDate) {
                $Query .= " AND ArrivalDate >= '$fromDate'";
            }
            if ($toDate) {
                $Query .= " AND ArrivalDate <= '$toDate'";
            }
            if ($PlantID != '') {
                $Query .= " AND ProcessingStationID = '$PlantID'";
            }



            $SqlArrivalsList = "SELECT RmArrivalID, ArrivalNumber
                                FROM RmArrivalDetatils
                                WHERE 1 
                                $Query
                                ORDER BY ArrivalDate DESC";

            // error_log(print_r($SqlArrivalsList, true));
            //  echo print_r($SqlArrivalsList);exit;

            $stmt = $this->conn->prepare($SqlArrivalsList);
            $stmt->execute();
            $ArrayArrivalList = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ArrayArrivalList[] = $row;
            } 
        }
		

		return $ArrayArrivalList;
	}
    function getArrivalsByFinancialYearBySoakingDate($RequestBody)
	{
		$PlantID = $RequestBody['PlantID'];
        $soakingDate = $RequestBody['SoakingDate'];
		if (!$soakingDate) {
			return false;
		}
		$Query = "";
		if ($soakingDate) {
			$Query .= " AND PDH.SoakingDate = '$soakingDate'";
		}
		if ($PlantID != '') {
			$Query .= " AND ProcessingStationID = '$PlantID'";
		}



		$SqlArrivalsList = "SELECT RmArrivalID, ArrivalNumber
                            FROM `ProdSoakingDetails`  PDH
                            LEFT JOIN RmLotDetatils RLD ON RLD.RmLotID = PDH.LotID
                            LEFT JOIN RmArrivalDetatils RAD ON RLD.RmReferenceID=RAD.RmArrivalID
                            WHERE RAD.RmArrivalID IS NOT NULL 
                            $Query
                            GROUP BY RAD.RmArrivalID
                            ORDER BY PDH.SoakingDate DESC";

		// error_log(print_r($SqlArrivalsList, true));
		//  echo print_r($SqlArrivalsList);exit;

		$stmt = $this->conn->prepare($SqlArrivalsList);
		$stmt->execute();
		$ArrayArrivalList = [];

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$ArrayArrivalList[] = $row;
		}

		return $ArrayArrivalList;
	}
    function getLinersByPlant($plantID = ''){
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='IQFLine' and Status=1 ";
        if(!empty($plantID)){
            $sqlQuery .= "  AND FIND_IN_SET($plantID, UnitIds) > 0 ";
        }
        $sqlQuery .= " ORDER by SortOrder asc";       
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

}
