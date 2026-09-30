<?php
include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
class StockOptionDetailsController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;

    }
    function getStockOptionDetails($RequestBody)
    {
        $StoreID = $RequestBody['StoreID'];
        $BlockID = $RequestBody['BlockID'];
        $Type = $RequestBody['Type'];
        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];

        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $Query = '';

        if ($Type == 'repck') {
            $Query = '';
        }
        if ($BlockID != '') {
            $Query .= " and cr.BlockID='" . $BlockID . "'";
        }
        if ($StoreID != '') {
            $Query .= " and CPID.UnitID='" . $StoreID . "'";
        }


        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $SearchQuery = " and CONCAT_WS('', CBS.BarCodeID, PoNo,AbStatus,SockingType,ShortName,CSP.PackingStyle, LotNo,ProductionID,
            StockID, AvalMcs, LooseBags,PackingTypeID,CPID.Grade,CPID.FreezingType, 
            ProductionDate,`ProductionTypeID`, ProductCategoryID,CPID.Remarks, UnitID) LIKE '%$SearchKey%'";
        }

         $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `CsBarCodes` as cb 
         left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID
         WHERE  cb.BarCodeStatus=1 and Year(TransactionDate)='$Year' and Month(TransactionDate)='$Month'  $Query ";
        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $SqlFields = "coalesce(PoNo,'NA') as PoNo,CPID.Grade,cb.BarCodeID,StockID,ShortName,AvalMcs,LooseBags,
            CSP.PackingStyle,CPID.FreezingType,ProductionTypeID,cb.BarCodeValue, CPID.PostedDate,CPID.ProductionID,PackingTypeID";


        $sqlStock = "SELECT $SqlFields FROM `CsBarCodes` as cb 
                         left join `CsStoresRackData` as cr on cb.BarCodeID = cr.BarCodeID
                         left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID			
                         left join `AdminGeneralOptions` as CGOPG on CGOPG.OptID = CPID.ProductionTypeID 
                         left join `CsStoresBlockData` as CBD on CBD.BlockID = cr.BlockID 		
                         left join `ScrmPoData` as CPOS on CPOS.PoID  = CPID.PoID 
                         left join `ScrmPIVarietyList` as VL on VL.VarietyID=CPID.VarietyID
                         left join `AdminPackingStyles` as CSP on CSP.PckID = CPID.PackingStyleID
                         where cb.BarCodeStatus=1 and Year(TransactionDate)='$Year' and Month(TransactionDate)='$Month'
                         $Query $SearchQuery $Limit";

        $stmtStock = $this->conn->prepare($sqlStock);
        $stmtStock->execute();

        $i = 0;
        while ($dataRowStock = $stmtStock->fetch(PDO::FETCH_ASSOC)) {
            $BarCodeID = $dataRowStock['BarCodeID'];
            $ProductionID = $dataRowStock['ProductionID'];

            $ArrayStockOptionDetails['SNO'] = ++$i;
            $ArrayStockOptionDetails['ID'] = $dataRowStock['StockID'];
            $ArrayStockOptionDetails['EntryDate'] = $dataRowStock['PostedDate'];
            $ArrayStockOptionDetails['PoNumber'] = $dataRowStock['PoNo'];
            $ArrayStockOptionDetails['Variety'] = $dataRowStock['ShortName'];
            $ArrayStockOptionDetails['Grade'] = $dataRowStock['Grade'];
            $ArrayStockOptionDetails['Mcs'] = $dataRowStock['AvalMcs'];
            $ArrayStockOptionDetails['Loose'] = $dataRowStock['LooseBags'];
            $ArrayStockOptionDetails['PackingStyle'] = $dataRowStock['PackingStyle'];
            $ArrayStockOptionDetails['FreezingType'] = $dataRowStock['FreezingType'];
            $ArrayStockOptionDetails['ProductionType'] = $dataRowStock['ProductionTypeID'];
            $ArrayStockOptionDetails['PackingType'] = $dataRowStock['PackingTypeID'];
            $ArrayStockOptionDetails['BatchCode'] = $dataRowStock['BarCodeValue'];
            $ArrayStockOptionDetails['PrintLink'] = "CsBarCodeLabelPrint.php?BID=$BarCodeID";
            $ArrayStockOptionDetails['EditLink'] = "inward.php?ProductionDataID=$ProductionID&BarCodeID=$BarCodeID";

            $ArrayStockOptionData[] = $ArrayStockOptionDetails;
        }

        if (count($ArrayStockOptionData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "";
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayStockOptionData;
        return $data;
        // }
    }
}