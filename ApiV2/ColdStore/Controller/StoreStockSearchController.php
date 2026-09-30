<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class StoreStockSearchController
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
        // $this->utilController = new UtilController($this->conn);
    }

    function getStoreStockDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TenantID = $RequestBody['TenantID'];
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['VarietyID'];
        $Grade = $RequestBody['Grade'];
        $PackingStyleID = $RequestBody['PackingStyleID'];
        $PackingTypeID = $RequestBody['PackingTypeID'];
        $ProductCategoryID = $RequestBody['ProductCategoryID'];

        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($Grade != "") {
            $Query .= " and CPID.Grade='$Grade'";
        }
        if ($VarietyID != "") {
            $TypeProduct = $RequestBody['TypeProduct'];
            $ShortName = $RequestBody['ShortName'];
            $Species = $RequestBody['Species'];

            $sql2 = "SELECT COALESCE(GROUP_CONCAT(VarietyID),0) VarietyID FROM ScrmPIVarietyList WHERE  Species='$Species' and  ProductID='$VarietyID' and TypeProduct='$TypeProduct'";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute();

            $dataRow2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $VarietyID = $dataRow2['VarietyID'];

            if ($VarietyID != 0) {
                $Query .= " and CPID.VarietyID in ($VarietyID)";
            } else {
                $Query .= " and CPID.VarietyID =''";
            }
        }
        if ($PackingStyleID != "") {
            $Query .= " and CPID.PackingStyleID='$PackingStyleID'";
        }
        if ($TenantID != "") {
            $Query .= " and CPID.StockHolderID='$TenantID'";
        }
        if ($ColdstoreID != "") {
            $Query .= " and CPID.UnitID='$ColdstoreID'";
        }
        if ($PoID != "") {
            $Query .= " and CPID.PoID='$PoID'";
        }
        if ($PackingTypeID != "") {
            $Query .= " and CPID.PackingTypeID='$PackingTypeID'";
        }
        if ($ProductCategoryID != "") {
            $Query .= " and CPID.ProductCategoryID='$ProductCategoryID'";
        }
        // if ($ProductCategoryID != "") {
        //     $Query .= " and CPID.PoID='$ProductCategoryID'";
        // }
        // if ($ProductCategoryID != "") {
        //     $Query .= " and CPID.VarietyID='$ProductCategoryID'";
        // }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $SearchQuery = " and CONCAT_WS('', cb.BarCodeID, ShortName,CSP.PackingStyle, LotNo,ProductionID,
            StockID, AvalMcs, LooseBags,PackingTypeID,CPID.Grade,CPID.FreezingType, 
            ProductionDate,`ProductionTypeID`, ProductCategoryID,CPID.Remarks, UnitID) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `CsBarCodes` as cb 
         left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID
         WHERE  (CPID.AvalMcs!=0 or CPID.LooseBags !=0)  $Query";
        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "coalesce(PoNo,'NA') as PoNo,cb.BarCodeID,ShortName,CPID.FreezingType,CPID.Grade, PackingTypeID,
        AvalMcs,LooseBags,AvalKgs,PackingTypeID,CSP.PackingStyle,ProductionTypeID,ProductionDate,RackName,BlockName,cr.BlockID,
        ProductCategoryID,CPID.UnitID,VL.DeviSUPC,BlockName,StoreName";

        $sqlQuery = "SELECT $sqlFields FROM `CsBarCodes` as cb 
                    left join `CsStoresRackData` as cr on cb.BarCodeID = cr.BarCodeID
                    left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID		
                    left join `CsStoresBlockData` as CBD on CBD.BlockID = cr.BlockID 		
                    left join `ScrmPoData` as CPOS on CPOS.PoID  = CPID.PoID 
                    left join `ScrmPIVarietyList` as VL on VL.VarietyID=CPID.VarietyID
                    left join `AdminPackingStyles` as CSP on CSP.PckID = CPID.PackingStyleID
                    left join CsStores as CS on CS.StoreID=cr.StoreID
                    where cb.BarCodeStatus=1 and (CPID.AvalMcs!=0 or CPID.LooseBags !=0)  $Query $SearchQuery" . $Limit;



// SELECT PoNo, CSD.ProductionID,AvalMcs,LooseBags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,StockID,CSD.BarCodeID,
//         CSRD.StoreID,CSRD.RowID as RackID,CSRD.RackName,CSRD.BlockID,CSD.ProductCategoryID as ProductCategory,
//         (AvalMcs*TotalSlabs*SlabWeight*MFactor) as AvalMcsKgs, (LooseBags*TotalSlabs*SlabWeight*MFactor) as LooseKgs,
//         BlockName,StoreName FROM CsStockData as CSD
//         left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
//         left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
//         left join CsStoresRackData as CSRD on CSRD.BarCodeID=CSD.BarCodeID
//         left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
//         left join CsStoresBlockData as CSBD on CSBD.BlockID=CSRD.BlockID
//                 left join CsStores as CS on CS.StoreID=CSRD.StoreID


//         WHERE StockID!='' AND (AvalMcs!=0 or LooseBags!=0)
//       order by StockID asc;

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayStoreData = [];
        $TotalsList = '';
        $TotalLoose = '';
        $TotalKgs = '';
        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $AvalMcs = $dataRow['AvalMcs'];
            $LooseBags = $dataRow['LooseBags'];
            $AvalKgs = $dataRow['AvalKgs'];

            $ArrayStoreList['SNO'] = ++$i;
            $ArrayStoreList['PoNo'] = $dataRow['PoNo'];
            $ArrayStoreList['StockID'] = $dataRow['StockID'];
            $ArrayStoreList['BarCodeID'] = $dataRow['BarCodeID'];
            $ArrayStoreList['Variety'] = $dataRow['ShortName'];
            $ArrayStoreList['Grade'] = $dataRow['Grade'];
            $ArrayStoreList['SUPC'] = $dataRow['DeviSUPC'];
            $ArrayStoreList['Mcs'] = $AvalMcs;
            $ArrayStoreList['Loose'] = $LooseBags;
            $ArrayStoreList['Kgs'] = $AvalKgs;
            $ArrayStoreList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStoreList['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayStoreList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStoreList['Group'] = $dataRow['ProductionTypeID'];
            $ArrayStoreList['Category'] = $dataRow['ProductCategoryID'];
            $ArrayStoreList['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayStoreList['RackNumber'] = $dataRow['RackName'];
            $ArrayStoreList['Block'] = $dataRow['BlockName'];
            $ArrayStoreList['Store'] = $dataRow['StoreName'];

            $ArrayStoreData[] =  $ArrayStoreList;

            $TotalsList['TotalMcs'] += $AvalMcs;
            $TotalsList['TotalLoose'] += $LooseBags;
            $TotalsList['TotalKgs'] += $AvalKgs;

        }

        if (count($ArrayStoreData) < $RecordsPerPage) {
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
        $data['Data'] = $ArrayStoreData;
        $data['Totals'] = $TotalsList;


        return $data;
    }
}
