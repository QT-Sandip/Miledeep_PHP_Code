<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class IbtOutController
{
    private $conn;

    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    public function getIbtOutDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        // $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionID = $RequestBody['ProductionID'];
        $PoID = $RequestBody['PoID'];
        $PackingType = $RequestBody['PackingType'];
        $VarietyID = $RequestBody['VarietyID'];
        $ShortName = $RequestBody['ShortName'];
        $UnitID = $RequestBody['ColdStore'];
        $Blocks = $RequestBody['Blocks'];
        $Grade = $RequestBody['Grade'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $FreezingType = $RequestBody['FreezingType'];
        $Species = $RequestBody['Species'];
        $TypeofProduct = $RequestBody['TypeofProduct'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'] ? $RequestBody['PageNo'] : 1;
        $LoadAll = $RequestBody['LoadAll'];
        $PDCategory = $RequestBody['PDCategory'];
        $SoakingType = $RequestBody['SoakingType'];
        $abStatus = $RequestBody['abStatus'];

        $Query = '';
        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " AND CONCAT_WS('', PoNo, CSD.ProductionID,AvalMcs,LooseBags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,
            StockID,CSD.BarCodeID,CSRD.StoreID,CSRD.RowID,CSRD.RackName,CSRD.BlockID,CSD.ProductCategoryID, BlockName,
            StoreName,UnitID,CSD.PoID) LIKE '%$SearchKey%'";
        }

        if ($UnitID != '') {
            $Query .= " and UnitID='$UnitID'";
        }
        if ($ProductionID != '') {
            $Query .= " and ProductionID='$ProductionID'";
        }
        if ($VarietyID != '') {
            $Query .= " and CSD.VarietyID='$VarietyID'";
        }
        if ($ShortName != '') {
            $Query .= " and SPV.ShortName='$ShortName'";
        }
        if ($PoID != '') {
            $Query .= " and CSD.PoID='$PoID'";
        }
        if ($PackingType != '') {
            $Query .= " and PackingTypeID='$PackingType'";
        }
        if ($Blocks != '') {
            $Query .= " and CSBD.BlockID='$Blocks'";
        }
        if ($Grade != '') {
            $Query .= " and CSD.Grade='$Grade'";
        }
        if ($PackingStyle != '') {
            $Query .= " and CSD.PackingStyleID='$PackingStyle'";
        }
        if ($FreezingType != '') {
            $Query .= " and CSD.FreezingType='$FreezingType'";
        }
        if ($SoakingType != '') {
            $Query .= " and SPV.Treatment='$SoakingType'";
        }
        if ($TypeofProduct != '') {
            $Query .= " and SPV.TypeProduct='$TypeofProduct'";
        }
        if ($Species != '') {
            $Query .= " and SPV.Species='$Species'";
        }
        if ($PDCategory != '') {
            $Query .= " and CSD.ProductCategoryID='$PDCategory'";
        }
        if ($abStatus != '') {
            $Query .= " and CSD.AbStatus='$abStatus'";
        }

        $sqlCount = "SELECT StockID FROM CsStockData AS CSD
        LEFT JOIN ScrmPIVarietyList AS SPV ON SPV.VarietyID=CSD.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID =CSD.StoreRackID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
        LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID
        LEFT JOIN CsStores AS CS ON CS.StoreID=CSD.UnitID
        WHERE CSD.StockID!='' $Query AND (AvalMcs!=0 OR LooseBags!=0) AND (AvalMcs > 0 OR LooseBags > 0)
        GROUP BY CSRD.BlockID,PackingStyleID,StoreRackID,CSD.ProductCategoryID,CSD.VarietyID,CSD.AbStatus
        ORDER BY abs(CSD.Grade) ASC ";
        // WHERE CSD.StockID!='' $Query AND (AvalMcs!=0 OR LooseBags!=0)
        // WHERE StockID!='' $Query AND (AvalMcs!=0 OR LooseBags!=0) AND (AvalMcs>0 OR LooseBags>0)

        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        //$dataRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $Total = 0;
        while ($dataRow = $stmtCount->fetch(PDO::FETCH_ASSOC)) {
            $Total++;
        }

        //$TotalCount = $dataRow['Total'];
        $TotalCount = $Total;
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $dataRow = null;
        // $sqlFields = "PoNo, CSD.ProductionID,AvalMcs,LooseBags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,CSD.StockID,CSD.BarCodeID,
        // CSRD.StoreID,CSRD.RowID as RackID,CSRD.RackName,CSRD.BlockID,CSD.ProductCategoryID as ProductCategory,CSD.ProductionDate,CSD.TransactionDate,
        // (AvalMcs*TotalSlabs*SlabWeight*MFactor) as AvalMcsKgs, (LooseBags*SlabWeight*MFactor) as LooseKgs,
        // BlockName,StoreName,UnitID,CSD.PoID,CSD.FreezingType,SPV.TypeProduct,SPV.Species,SPV.Treatment as Soaking,CSD.StockHolderID";

        $sqlFields = "PoNo, CSD.ProductionID,SUM(AvalMcs) AS AvalMcs,SUM(LooseBags) AS LooseBags,SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) AS AvalMcsKgs,SUM(CSD.AvalKgs) AS AvalKgs,
        SUM(LooseBags*SlabWeight*MFactor) AS LooseKgs,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,GROUP_CONCAT(CSD.StockID) AS StockID,CSD.BarCodeID,
        CSRD.StoreID,CSRD.RowID as RackID,CSRD.RackName,CSRD.BlockID,CSD.ProductCategoryID as ProductCategory,CSD.ProductionDate,CSD.TransactionDate,
        BlockName,StoreName,UnitID,GROUP_CONCAT(CSD.PoID) AS POID,CSD.FreezingType,SPV.TypeProduct,SPV.Species,SPV.Treatment as Soaking,CSD.StockHolderID,GROUP_CONCAT(concat(CSD.StockID,'-',CSD.PoID,'-',AvalMcs,'-',LooseBags)) AS AvailableStock,
        CSD.`AbStatus`";
        /* $sqlFields = "PoNo, CSD.ProductionID,AvalMcs,LooseBags,(AvalMcs*TotalSlabs*SlabWeight*MFactor) AS AvalMcsKgs,CSD.AvalKgs,
        (LooseBags*SlabWeight*MFactor) AS LooseKgs,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,CSD.StockID,CSD.BarCodeID,
        CSRD.StoreID,CSRD.RowID as RackID,CSRD.RackName,CSRD.BlockID,CSD.ProductCategoryID as ProductCategory,CSD.ProductionDate,CSD.TransactionDate,
        BlockName,StoreName,UnitID,CSD.PoID,CSD.FreezingType,SPV.TypeProduct,SPV.Species,SPV.Treatment as Soaking,CSD.StockHolderID,concat(CSD.StockID,'-',CSD.PoID,'-',AvalMcs,'-',LooseBags) AS AvailableStock,
        CSD.`AbStatus`"; */


        $sqlQuery = "SELECT $sqlFields FROM CsStockData AS CSD
        LEFT JOIN ScrmPIVarietyList AS SPV ON SPV.VarietyID=CSD.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID =CSD.StoreRackID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
        LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID
        LEFT JOIN CsStores AS CS ON CS.StoreID=CSD.UnitID
        WHERE CSD.StockID!='' $Query AND (AvalMcs!=0 OR LooseBags!=0) AND (AvalMcs > 0 OR LooseBags >0)
        GROUP BY CSRD.BlockID,PackingStyleID,StoreRackID,CSD.ProductCategoryID,CSD.VarietyID,CSD.AbStatus
        ORDER BY abs(CSD.Grade) ASC " . $Limit;
        // LEFT JOIN CsIBTRequestBreakUpDetails AS CIRBD ON CIRBD.StockID=CSD.StockID
        // ,CIRBD.NoOfMcs as IBTOutMcs,NoOfLooseBags as IBTOutLooseBags,CIRBD.IBTRequestID
        // echo $sqlQuery;exit;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRowRequestStatus = $stmt->fetch(PDO::FETCH_ASSOC);
        $RequestStatus = $dataRowRequestStatus['RequestStatus'];

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        // echo  $sqlQuery;
        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);

        $SumTotalMcs = 0;
        $SumTotalLooseBags = 0;
        $SumMcs = 0;
        $SumLooseBags = 0;

        $i = 0;
        $TotalKgs = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $StockID = $dataRow['StockID'];
            $IBTRequestID = $dataRow['IBTRequestID'];
            $sqlFields = "sum(CIRBD.NoOfMcs) as IBTOutMcs,sum(NoOfLooseBags) as IBTOutLooseBags, RequestStatus,OrderDate";

            $sqlIBTMcs = "SELECT $sqlFields FROM  CsIBTRequestBreakUpDetails AS CIRBD 
            LEFT JOIN CsIBTRequestDetails AS CIRD ON CIRD.IBTID = CIRBD.IBTRequestID
            WHERE StockID IN ($StockID)  and Status!=3 ";

            $stmtIBTMcs = $this->conn->prepare($sqlIBTMcs);
            $stmtIBTMcs->execute();
            $dataRowIBTMcs = $stmtIBTMcs->fetch(PDO::FETCH_ASSOC);

            $Mcs = $dataRow['AvalMcs'] - $dataRowIBTMcs['IBTOutMcs'];
            $SumMcs += $Mcs;
            $Bags = $dataRow['LooseBags'] - $dataRowIBTMcs['IBTOutLooseBags'];
            $SumLooseBags += $Bags;

            if ($Mcs > 0 || $Bags > 0) {
                $SumTotalMcs += $dataRow['AvalMcs'];
                $SumTotalLooseBags += $dataRow['LooseBags'];
                // $Kgs = $dataRow['AvalMcsKgs'] + $dataRow['LooseKgs'];
                $Kgs = $dataRow['AvalKgs'];
                if (($dataRow['AvalMcs'] - $dataRowIBTMcs['IBTOutMcs']) > 0 || ($dataRow['LooseBags'] - $dataRowIBTMcs['IBTOutLooseBags']) > 0) {
                    $ArrayStockOutData['SNo'] = ++$i;
                    $ArrayStockOutData['ProductionID'] = $dataRow['ProductionID'];
                    $ArrayStockOutData['ProductionDate'] = $dataRow['ProductionDate'];
                    $ArrayStockOutData['TransactionDate'] = $dataRow['TransactionDate'];
                    $ArrayStockOutData['Variety'] = $dataRow['ShortName'];
                    $ArrayStockOutData['Grade'] = $dataRow['Grade'];
                    $ArrayStockOutData['PackingType'] = $dataRow['PackingTypeID'];
                    $ArrayStockOutData['Mcs'] = (($dataRow['AvalMcs'] - $dataRowIBTMcs['IBTOutMcs']) > 0 ? $dataRow['AvalMcs'] - $dataRowIBTMcs['IBTOutMcs'] : 0);
                    $ArrayStockOutData['Bags'] = (($dataRow['LooseBags'] - $dataRowIBTMcs['IBTOutLooseBags']) > 0 ? $dataRow['LooseBags'] - $dataRowIBTMcs['IBTOutLooseBags'] : 0);
                    $ArrayStockOutData['TotalMcs'] = $dataRow['AvalMcs'];
                    $ArrayStockOutData['TotalBags'] = $dataRow['LooseBags'];
                    $ArrayStockOutData['PackingStyle'] = $dataRow['PackingStyle'];
                    $ArrayStockOutData['Kgs'] = sprintf('%0.2f', round($Kgs, 2));
                    $ArrayStockOutData['StockID'] = $dataRow['StockID'];
                    $ArrayStockOutData['BarCodeID'] = $dataRow['BarCodeID'];
                    $ArrayStockOutData['StoreID'] = $dataRow['StoreID'];
                    $ArrayStockOutData['RackID'] = $dataRow['RackID'];
                    $ArrayStockOutData['BlockID'] = $dataRow['BlockID'];
                    $ArrayStockOutData['ProductCategory'] = $dataRow['ProductCategory'];
                    $ArrayStockOutData['PoID'] = $dataRow['PoID'];
                    $ArrayStockOutData['PoNumber'] = $dataRow['PoNo'];
                    $ArrayStockOutData['UnitID'] = $dataRow['UnitID'];
                    $ArrayStockOutData['StoreName'] = $dataRow['StoreName'];
                    $ArrayStockOutData['Location'] = $dataRow['BlockName'] . ' - ' . $dataRow['RackName'];
                    $ArrayStockOutData['AvailableStock'] = $dataRow['AvailableStock'];
                    $ArrayStockOutData['FreezingType'] = $dataRow['FreezingType'];
                    $ArrayStockOutData['Species'] = $dataRow['Species'];
                    $ArrayStockOutData['TypeofProduct'] = $dataRow['TypeProduct'];
                    $ArrayStockOutData['Soaking'] = $dataRow['Soaking'];
                    $ArrayStockOutData['OrderDate'] = $dataRowIBTMcs['OrderDate'];
                    $ArrayStockOutData['RequestStatus'] = $dataRowIBTMcs['RequestStatus'];
                    $ArrayStockOutData['AbStatus'] = $dataRow['AbStatus'];
                    $TotalKgs += $Kgs;
                    $AryData[] = $ArrayStockOutData;
                }
            }
        }

        $OrderDate = $this->getOrderDate($RequestBody);
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DailyStockOutData'] = $AryData;
        $Data['TotalKgs'] = $TotalKgs;
        $Data['PageCount'] = $PageCount;
        $Data['TotalCount'] = $TotalCount;
        $Data['SumTotalMcs'] = $SumTotalMcs;
        $Data['SumTotalLooseBags'] = $SumTotalLooseBags;
        $Data['SumMcs'] = $SumMcs;
        $Data['SumLooseBags'] = $SumLooseBags;
        $Data['RequestStatus'] = $RequestStatus;
        $Data['OrderDate'] = $OrderDate;

        return $Data;
    }


    function getOrderDate($RequestBody)
    {
        $IBTID = $RequestBody['IBTID'];
        $sqlSelect = "SELECT OrderDate FROM CsIBTRequestDetails WHERE IBTID = '$IBTID'";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $rowData = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $OrderDate = $rowData['OrderDate'];

        return $OrderDate;
    }

    // public function postIbtOutDetails($Request)  // need execute loop 03-05-2024
    // {

    //     $ProductionID = $Request['ProductionID'];
    //     // $ProductionDate = $Request['ProductionDate'];
    //     $StockOutDate = $Request['StockOutDate'];
    //     $StockOutReason = $Request['StockOutReason'];
    //     // $ReStockinStage = $Request['ReStockinStage'];
    //     $TotalMsc = $Request['TotalMsc'];
    //     $TotalLooseBags = $Request['TotalLooseBags'];
    //     $StockItems = $Request['StockItems'];
    //     // $Remarks = $Request['Remarks'];
    //     if ($this->isStockAvailable($StockItems)) {
    //         $ResData['Error'] = "Stock not available in other store to confirm request.";
    //         $ResData['Msg'] = null;
    //         return $ResData;
    //     }
    //     $sqlQuery = "select * from  CsDailyStockOutData where StockOutDate='$StockOutDate'";
    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();
    //     $rowCount = $stmt->rowCount();
    //     $rowCount = $rowCount + 1;



    //     $BatchCode = str_replace('-', "", $StockOutDate);
    //     $BatchCode = $BatchCode . "-" . $rowCount;

    //     $sqlQuery = "INSERT INTO CsDailyStockOutData 
    //        ( ProductionID,  StockOutDate, Reason, ActualOutMcs, ActualOutLooseBags, 
    //        AvalOutMcs, AvalOutLooseBags, Remarks,OutBatchCode) 
    //        VALUES ('$ProductionID','$StockOutDate','$StockOutReason','$TotalMsc','$TotalLooseBags',
    //        '$TotalMsc','$TotalLooseBags','','$BatchCode')";

    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();
    //     $lastInsertId = $this->conn->lastInsertId(); //uma

    //     if ($stmt) {

    //         $TotalKgs = 0;
    //         foreach ($StockItems as $Item) {
    //             $Data = $this->postStockOutItems($Request, $Item, $lastInsertId, $TotalKgs); //uma
    //             $TotalKgs = $TotalKgs + (($Data['Kgs'] && $Data['Kgs'] > 0) ? (float) $Data['Kgs'] : 0);
    //             //echo "Hi";
    //         }
    //         $sqlUpdateQuery = "UPDATE CsDailyStockOutData SET `ActualOutKgs`=$TotalKgs,`AvalOutKgs`=$TotalKgs WHERE CsStockOutID='$lastInsertId'";
    //         $stmtUpdateQuery = $this->conn->prepare($sqlUpdateQuery);
    //         $stmtUpdateQuery->execute();
    //         // if($ReStockinStage!=''){
    //         $ProductionID = $this->postDailyProductionData($Request);

    //         $ResData['Error'] = null;
    //         $ResData['Msg'] = "Data Updated successfully";
    //         $ResData['ProductionID'] = $ProductionID;

    //         // }
    //     }
    //     return $ResData;
    // }

    // function postStockOutItems($Request, $Item, $CsStockOutID, $TotalKgs)
    // {

    //     // $Error = null;
    //     // $Msg = "Data Updated successfully";


    //     // $this->Error = null;
    //     // $this->Msg = "Data Updated successfully";

    //     // date_default_timezone_set("Asia/Kolkata");
    //     // $PostedDate = date("Y-m-d H:i:s");


    //     // $StockOutReason = $Request['StockOutReason'];
    //     // // $Remarks = $Request['Remarks'];
    //     // $ProductionID = $Item['ProductionID'];
    //     // $StockOutDate = $Request['StockOutDate'];
    //     // $BarCodeID = $Item['BarCodeID'];
    //     // $RackID = $Item['RackID'];
    //     // $BatchCode = str_replace('-', "", $StockOutDate);
    //     // $StockID = $Item['StockID'];
    //     // $Remarks = $Item['IBTOutReason'];

    //     // $TotalOutMcs = $Item['OutMcs'];
    //     // $TotalOutLooseBags = $Item['OutLooseBags'];

    //     // $AvailableStocks = $Item['AvailableStock'];
    //     // $AvailableStocksArr = explode(',', $AvailableStocks);

    //     // foreach ($AvailableStocksArr as $AvailableStock) {

    //     //     $AvailableStockData = explode('-', $AvailableStock);
    //     //     $StockID = $AvailableStockData[0];
    //     //     $PoID = $AvailableStockData[1];
    //     //     $OutMcs = $AvailableStockData[2];
    //     //     $OutLooseBags = $AvailableStockData[3];

    //     //     if ($TotalOutMcs > 0 || $TotalOutLooseBags > 0) {
    //     //         $Sqlfields = "BarCodeID,AvalMcs,LooseBags,AvalKgs,PackingStyleID";
    //     //         $SqlStockDetails = "select $Sqlfields from  CsStockData WHERE StockID ='$StockID'";
    //     //         $stmt = $this->conn->prepare($SqlStockDetails);
    //     //         $stmt->execute();
    //     //         $stockRow = $stmt->fetch(PDO::FETCH_ASSOC);
    //     //         $BarCodeID = $stockRow['BarCodeID'];
    //     //         $PackingStyleID = $stockRow['PackingStyleID'];
    //     //         $BalanceMcs = $stockRow['AvalMcs'] - $OutMcs;
    //     //         $BalanceLooseBags = $stockRow['LooseBags'] - $OutLooseBags;
    //     //         $RowStockDetails = json_encode($stockRow, true);

    //     //         $sqlKgsQuery = "SELECT TotalSlabs,SlabWeight,MFactor FROM AdminPackingStyles WHERE PCKID='$PackingStyleID'";
    //     //         $stmtKgsQuery = $this->conn->prepare($sqlKgsQuery);
    //     //         $stmtKgsQuery->execute();
    //     //         $RowStockData = $stmtKgsQuery->fetch(PDO::FETCH_ASSOC);
    //     //         $McsKgs = $OutMcs * $RowStockData['TotalSlabs'] * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
    //     //         $LooseKgs = $OutLooseBags * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
    //     //         $OutKgs = $McsKgs + $LooseKgs;


    //     //         $BalanceAvailableKgs = $stockRow['AvalKgs'] - $OutKgs;

    //     //         $SqlStockUpdate = "UPDATE  CsStockData SET AvalMcs ='$BalanceMcs' , LooseBags='$BalanceLooseBags', 
    //     //         AvalKgs='$BalanceAvailableKgs', StockStatus='2' WHERE StockID ='$StockID'";
    //     //         $StmtStockUpdate = $this->conn->prepare($SqlStockUpdate);
    //     //         $StmtStockUpdate->execute();

    //     //         if ($StmtStockUpdate) {
    //     //             $SqlStockOutData = "INSERT INTO CsStockOutDetails 
    //     //             (PostedDate,BarCodeID,StockID,RackID, OutMcs,OutLooseBags,Remarks,OutType,OutStatus,BatchCode,OutDate,
    //     //             ProductionID,CsStockOutID) VALUES 
    //     //             ('$PostedDate','$BarCodeID','$StockID','$RackID','$OutMcs','$OutLooseBags','$Remarks','$StockOutReason','3','$BatchCode','$StockOutDate',
    //     //             '$ProductionID','$CsStockOutID')";
    //     //             $StmtStockOutData = $this->conn->prepare($SqlStockOutData);
    //     //             $StmtStockOutData->execute();
    //     //         } else {
    //     //             // $Error = "Unable To Update Stock";
    //     //             $this->Error = "Unable To Update Stock";
    //     //         }

    //     //         if ($StmtStockOutData) {

    //     //             $SqlStockNo = "SELECT sum(AvalMcs) AS McsTotal,sum(LooseBags) as LooseTotal,BarCodeID 
    //     //             from CsStockData where BarCodeID='$BarCodeID'";

    //     //             $StmtStockNo = $this->conn->prepare($SqlStockNo);
    //     //             $StmtStockNo->execute();
    //     //             $RowStock = $StmtStockNo->fetch(PDO::FETCH_ASSOC);
    //     //             $McsTotal = $RowStock['McsTotal'];
    //     //             $LooseTotal = $RowStock['LooseTotal'];

    //     //             if ($McsTotal == 0 && $LooseTotal == 0) {
    //     //                 $SqlRackData = "UPDATE CsStoresRackData SET Status ='0', BarCodeID='0' WHERE BarCodeID ='$BarCodeID'";
    //     //                 $StmtRackData = $this->conn->prepare($SqlRackData);
    //     //                 $StmtRackData->execute();
    //     //             } else {
    //     //                 $StmtRackData = true;
    //     //             }
    //     //         } else {
    //     //             // $Error = "Unable To Update Out Stock";
    //     //             $this->Error = "Unable To Update Out Stock";
    //     //         }

    //     //         if ($StmtRackData) {
    //     //             $SqlTransactionHistory = "INSERT INTO CsTransactionHistory( TrnsDate,TrnsType, Data) VALUES 
    //     //             ('$PostedDate','$StockOutReason','$RowStockDetails')";

    //     //             $StmtTransactionHistory = $this->conn->prepare($SqlTransactionHistory);
    //     //             $StmtTransactionHistory->execute();
    //     //         } else {
    //     //             // $Error = "Unable To Update Rack Details ";
    //     //             $this->Error = "Unable To Update Rack Details ";
    //     //         }

    //     //     }
    //     // }


    //     // // $Data['Error'] = $Error;
    //     // // $Data['Msg'] = $Msg;
    //     // $Data['Error'] = $this->Error;
    //     // $Data['Msg'] = $this->Msg;

    //     // return $Data;

    //     $this->Error = null;
    //     $this->Msg = "Data Updated successfully";

    //     date_default_timezone_set("Asia/Kolkata");
    //     $PostedDate = date("Y-m-d H:i:s");


    //     $StockOutReason = $Request['StockOutReason'];
    //     // $Remarks = $Request['Remarks'];
    //     $ProductionID = $Item['ProductionID'];
    //     $StockOutDate = $Request['StockOutDate'];
    //     $BarCodeID = $Item['BarCodeID'];
    //     $RackID = $Item['RackID'];
    //     $BatchCode = str_replace('-', "", $StockOutDate);
    //     $StockID = $Item['StockID'];
    //     $Remarks = $Item['IBTOutReason'];

    //     $OutMcs = $Item['OutMcs'];
    //     $OutLooseBags = $Item['OutLooseBags'];

    //     $Sqlfields = "BarCodeID,AvalMcs,LooseBags,AvalKgs,PackingStyleID,TotalSlabs,SlabWeight,MFactor";
    //     $SqlStockDetails = "select $Sqlfields from  CsStockData AS CSD LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID=PCKID WHERE StockID ='$StockID'";
    //     $stmt = $this->conn->prepare($SqlStockDetails);
    //     $stmt->execute();
    //     $stockRow = $stmt->fetch(PDO::FETCH_ASSOC);
    //     $BarCodeID = $stockRow['BarCodeID'];
    //     $PackingStyleID = $stockRow['PackingStyleID'];
    //     if ($stockRow['LooseBags'] < $OutLooseBags) {
    //         $remOutLooseBags = $OutLooseBags - $stockRow['LooseBags'];
    //         $deductMcs = ceil($remOutLooseBags / $stockRow['TotalSlabs']);

    //         $deductLoose = $deductMcs * $stockRow['TotalSlabs'];

    //         $stockRow['AvalMcs'] = $stockRow['AvalMcs'] - $deductMcs;
    //         $stockRow['LooseBags'] = $stockRow['LooseBags'] + $deductLoose;

    //     }
    //     $BalanceMcs = $stockRow['AvalMcs'] - $OutMcs;
    //     $BalanceLooseBags = $stockRow['LooseBags'] - $OutLooseBags;
    //     $RowStockDetails = json_encode($stockRow, true);

    //     $sqlKgsQuery = "SELECT TotalSlabs,SlabWeight,MFactor,Uom,LbsFactor FROM AdminPackingStyles WHERE PCKID='$PackingStyleID'";
    //     $stmtKgsQuery = $this->conn->prepare($sqlKgsQuery);
    //     $stmtKgsQuery->execute();
    //     $RowStockData = $stmtKgsQuery->fetch(PDO::FETCH_ASSOC);
    //     // $McsKgs = $OutMcs * $RowStockData['TotalSlabs'] * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
    //     // $LooseKgs = $OutLooseBags * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];

    //     if ($RowStockData['Uom'] == 'KGS' || $RowStockData['Uom'] == 'GRAMS') {
    //         $OutKgs = ($OutMcs * $RowStockData['TotalSlabs'] + $OutLooseBags) * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
    //     } else {
    //         $OutKgs = ($OutMcs * $RowStockData['TotalSlabs'] + $OutLooseBags) * $RowStockData['SlabWeight'] * $RowStockData['LbsFactor'] * 0.454;
    //     }

    //     // $OutKgs = $McsKgs + $LooseKgs;


    //     $BalanceAvailableKgs = $stockRow['AvalKgs'] - $OutKgs;

    //     $SqlStockUpdate = "UPDATE  CsStockData SET AvalMcs ='$BalanceMcs' , LooseBags='$BalanceLooseBags', 
    //     AvalKgs='$BalanceAvailableKgs', StockStatus='2' WHERE StockID ='$StockID'";
    //     $StmtStockUpdate = $this->conn->prepare($SqlStockUpdate);
    //     $StmtStockUpdate->execute();

    //     if ($StmtStockUpdate) {
    //         $SqlStockOutData = "INSERT INTO CsStockOutDetails 
    //         (PostedDate,BarCodeID,StockID,RackID, OutMcs,OutLooseBags,ActualOutKgs,AvalOutKgs,Remarks,OutType,OutStatus,BatchCode,OutDate,
    //         ProductionID,CsStockOutID) VALUES 
    //         ('$PostedDate','$BarCodeID','$StockID','$RackID','$OutMcs','$OutLooseBags','$OutKgs','$OutKgs','$Remarks','$StockOutReason','3','$BatchCode','$StockOutDate',
    //         '$ProductionID','$CsStockOutID')";
    //         $StmtStockOutData = $this->conn->prepare($SqlStockOutData);
    //         $StmtStockOutData->execute();
    //         $TotalKgs = $TotalKgs + $OutKgs;
    //     } else {
    //         // $Error = "Unable To Update Stock";
    //         $this->Error = "Unable To Update Stock";
    //     }

    //     if ($StmtStockOutData) {

    //         $SqlStockNo = "SELECT sum(AvalMcs) AS McsTotal,sum(LooseBags) as LooseTotal,BarCodeID 
    //         from CsStockData where BarCodeID='$BarCodeID'";

    //         $StmtStockNo = $this->conn->prepare($SqlStockNo);
    //         $StmtStockNo->execute();
    //         $RowStock = $StmtStockNo->fetch(PDO::FETCH_ASSOC);
    //         $McsTotal = $RowStock['McsTotal'];
    //         $LooseTotal = $RowStock['LooseTotal'];

    //         if ($McsTotal == 0 && $LooseTotal == 0) {
    //             $SqlRackData = "UPDATE CsStoresRackData SET Status ='0', BarCodeID='0' WHERE BarCodeID ='$BarCodeID'";
    //             $StmtRackData = $this->conn->prepare($SqlRackData);
    //             $StmtRackData->execute();
    //         } else {
    //             $StmtRackData = true;
    //         }
    //     } else {
    //         // $Error = "Unable To Update Out Stock";
    //         $this->Error = "Unable To Update Out Stock";
    //     }

    //     if ($StmtRackData) {
    //         $SqlTransactionHistory = "INSERT INTO CsTransactionHistory( TrnsDate,TrnsType, Data) VALUES 
    //         ('$PostedDate','$StockOutReason','$RowStockDetails')";

    //         $StmtTransactionHistory = $this->conn->prepare($SqlTransactionHistory);
    //         $StmtTransactionHistory->execute();
    //     } else {
    //         // $Error = "Unable To Update Rack Details ";
    //         $this->Error = "Unable To Update Rack Details ";
    //     }

    //     // $Data['Error'] = $Error;
    //     // $Data['Msg'] = $Msg;
    //     $Data['Error'] = $this->Error;
    //     $Data['Msg'] = $this->Msg;
    //     $Data['Kgs'] = $OutKgs;

    //     return $Data;
    // }


    public function postIbtOutDetails($Request)
    {
        $ProductionID = $Request['ProductionID'];
        $StockOutDate = $Request['StockOutDate'];
        $StockOutReason = $Request['StockOutReason'];
        $TotalMsc = $Request['TotalMsc'];
        $TotalLooseBags = $Request['TotalLooseBags'];
        $StockItems = $Request['StockItems'];
        $IBTRequestID = $Request['IBTRequestID'];

        if ($this->isStockAvailable($StockItems)) {
            $sqlUpdateIbtStatus = "UPDATE `CsIBTRequestDetails` SET `RequestStatus`='1' WHERE `IBTID`='$IBTRequestID'";
            $stmtUpdateIbtStatus = $this->conn->prepare($sqlUpdateIbtStatus);
            $stmtUpdateIbtStatus->execute();
            if ($stmtUpdateIbtStatus) {

                $sqlUpdateIbtBreakUpStatus = "UPDATE `CsIBTRequestBreakUpDetails` SET `Status`='1' WHERE `IBTRequestID`='$IBTRequestID'";
                $stmtUpdateUpdateIbtBreakUpStatus = $this->conn->prepare($sqlUpdateIbtBreakUpStatus);
                $stmtUpdateUpdateIbtBreakUpStatus->execute();
            }
            return [
                'Error' => "Stock not available in other store to confirm request.",
                'Msg' => null
            ];
        }

        try {
            $this->conn->beginTransaction();

            $sqlQuery = "SELECT COUNT(*) AS cnt FROM CsDailyStockOutData 
            WHERE StockOutDate = " . $this->conn->quote($StockOutDate) . " FOR UPDATE";
            $result = $this->conn->query($sqlQuery);
            if ($result === false) {
                throw new Exception("Unable to read daily stock out data: " . implode(' | ', $this->conn->errorInfo()));
            }
            $rowCount = (int) $result->fetchColumn() + 1;

            $BatchCode = str_replace('-', "", $StockOutDate) . "-" . $rowCount;

            $sqlQuery = "INSERT INTO CsDailyStockOutData 
           (ProductionID, StockOutDate, Reason, ActualOutMcs, ActualOutLooseBags, 
            AvalOutMcs, AvalOutLooseBags, Remarks, OutBatchCode) 
           VALUES (
                " . $this->conn->quote($ProductionID) . ",
                " . $this->conn->quote($StockOutDate) . ",
                " . $this->conn->quote($StockOutReason) . ",
                " . $this->conn->quote($TotalMsc) . ",
                " . $this->conn->quote($TotalLooseBags) . ",
                " . $this->conn->quote($TotalMsc) . ",
                " . $this->conn->quote($TotalLooseBags) . ",
                '',
                " . $this->conn->quote($BatchCode) . "
           )";
            $ok = $this->conn->exec($sqlQuery);
            if ($ok === false) {
                throw new Exception("Unable to create daily stock out record: " . implode(' | ', $this->conn->errorInfo()));
            }

            $lastInsertId = $this->conn->lastInsertId();

            $TotalKgs = 0;
            foreach ($StockItems as $Item) {
                $Data = $this->postStockOutItems($Request, $Item, $lastInsertId);
                if (!empty($Data['Error'])) {
                    throw new Exception($Data['Error']);
                }
                $TotalKgs += (isset($Data['Kgs']) && $Data['Kgs'] > 0) ? (float) $Data['Kgs'] : 0;
            }

            $sqlUpdateQuery = "UPDATE CsDailyStockOutData 
            SET ActualOutKgs = " . $this->conn->quote($TotalKgs) . ", 
                AvalOutKgs = " . $this->conn->quote($TotalKgs) . " 
            WHERE CsStockOutID = " . $this->conn->quote($lastInsertId);
            $ok = $this->conn->exec($sqlUpdateQuery);
            if ($ok === false) {
                throw new Exception("Unable to update daily stock out totals: " . implode(' | ', $this->conn->errorInfo()));
            }

            $ProductionID = $this->postDailyProductionData($Request);

            $this->conn->commit();

            return [
                'Error' => null,
                'Msg' => "Data Updated successfully",
                'ProductionID' => $ProductionID
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return [
                'Error' => $e->getMessage(),
                'Msg' => null
            ];
        }
    }

    function postStockOutItems($Request, $Item, $CsStockOutID)
    {
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");

        $StockOutReason = $Request['StockOutReason'];
        $StockOutDate = $Request['StockOutDate'];
        $ProductionID = $Item['ProductionID'];
        $RackID = $Item['RackID'];
        $StockID = $Item['StockID'];
        $Remarks = $Item['IBTOutReason'];
        $OutMcs = $Item['OutMcs'];
        $OutLooseBags = $Item['OutLooseBags'];
        $BatchCode = str_replace('-', "", $StockOutDate);

        $Sqlfields = "BarCodeID,AvalMcs,LooseBags,AvalKgs,PackingStyleID,TotalSlabs,SlabWeight,MFactor";
        $SqlStockDetails = "SELECT $Sqlfields FROM CsStockData AS CSD 
        LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID 
        WHERE StockID = " . $this->conn->quote($StockID) . " FOR UPDATE";
        $result = $this->conn->query($SqlStockDetails);
        if ($result === false) {
            return ['Error' => "Unable to read stock data for StockID $StockID: " . implode(' | ', $this->conn->errorInfo()), 'Msg' => null, 'Kgs' => 0];
        }
        $stockRow = $result->fetch(PDO::FETCH_ASSOC);

        if (!$stockRow) {
            return ['Error' => "Stock record not found for StockID $StockID", 'Msg' => null, 'Kgs' => 0];
        }

        $BarCodeID = $stockRow['BarCodeID'];
        $PackingStyleID = $stockRow['PackingStyleID'];

        if ($stockRow['LooseBags'] < $OutLooseBags) {
            $remOutLooseBags = $OutLooseBags - $stockRow['LooseBags'];
            $deductMcs = ceil($remOutLooseBags / $stockRow['TotalSlabs']);
            $deductLoose = $deductMcs * $stockRow['TotalSlabs'];
            $stockRow['AvalMcs'] -= $deductMcs;
            $stockRow['LooseBags'] += $deductLoose;
        }

        $BalanceMcs = $stockRow['AvalMcs'] - $OutMcs;
        $BalanceLooseBags = $stockRow['LooseBags'] - $OutLooseBags;

        if ($BalanceMcs < 0 || $BalanceLooseBags < 0) {
            return ['Error' => "Insufficient stock available for StockID $StockID", 'Msg' => null, 'Kgs' => 0];
        }

        $RowStockDetails = json_encode($stockRow);

        $sqlKgsQuery = "SELECT TotalSlabs, SlabWeight, MFactor, Uom, LbsFactor FROM AdminPackingStyles 
        WHERE PCKID = " . $this->conn->quote($PackingStyleID);
        $result = $this->conn->query($sqlKgsQuery);
        if ($result === false) {
            return ['Error' => "Unable to read packing style: " . implode(' | ', $this->conn->errorInfo()), 'Msg' => null, 'Kgs' => 0];
        }
        $RowStockData = $result->fetch(PDO::FETCH_ASSOC);

        if (!$RowStockData) {
            return ['Error' => "Packing style not found for PCKID $PackingStyleID", 'Msg' => null, 'Kgs' => 0];
        }

        if ($RowStockData['Uom'] == 'KGS' || $RowStockData['Uom'] == 'GRAMS') {
            $OutKgs = ($OutMcs * $RowStockData['TotalSlabs'] + $OutLooseBags) * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        } else {
            $OutKgs = ($OutMcs * $RowStockData['TotalSlabs'] + $OutLooseBags) * $RowStockData['SlabWeight'] * $RowStockData['LbsFactor'] * 0.454;
        }

        $BalanceAvailableKgs = $stockRow['AvalKgs'] - $OutKgs;

        $SqlStockUpdate = "UPDATE CsStockData 
        SET AvalMcs = " . $this->conn->quote($BalanceMcs) . ", 
            LooseBags = " . $this->conn->quote($BalanceLooseBags) . ", 
            AvalKgs = " . $this->conn->quote($BalanceAvailableKgs) . ", 
            StockStatus = '2' 
        WHERE StockID = " . $this->conn->quote($StockID);
        $ok = $this->conn->exec($SqlStockUpdate);
        if ($ok === false) {
            return ['Error' => "Unable to update stock: " . implode(' | ', $this->conn->errorInfo()), 'Msg' => null, 'Kgs' => 0];
        }

        $SqlStockOutData = "INSERT INTO CsStockOutDetails 
        (PostedDate, BarCodeID, StockID, RackID, OutMcs, OutLooseBags, ActualOutKgs, AvalOutKgs, Remarks, OutType, OutStatus, BatchCode, OutDate,
         ProductionID, CsStockOutID) VALUES (
            " . $this->conn->quote($PostedDate) . ",
            " . $this->conn->quote($BarCodeID) . ",
            " . $this->conn->quote($StockID) . ",
            " . $this->conn->quote($RackID) . ",
            " . $this->conn->quote($OutMcs) . ",
            " . $this->conn->quote($OutLooseBags) . ",
            " . $this->conn->quote($OutKgs) . ",
            " . $this->conn->quote($OutKgs) . ",
            " . $this->conn->quote($Remarks) . ",
            " . $this->conn->quote($StockOutReason) . ",
            '3',
            " . $this->conn->quote($BatchCode) . ",
            " . $this->conn->quote($StockOutDate) . ",
            " . $this->conn->quote($ProductionID) . ",
            " . $this->conn->quote($CsStockOutID) . "
        )";
        $ok = $this->conn->exec($SqlStockOutData);
        if ($ok === false) {
            return ['Error' => "Unable to insert stock out details: " . implode(' | ', $this->conn->errorInfo()), 'Msg' => null, 'Kgs' => 0];
        }

        $SqlStockNo = "SELECT SUM(AvalMcs) AS McsTotal, SUM(LooseBags) AS LooseTotal, BarCodeID 
        FROM CsStockData WHERE BarCodeID = " . $this->conn->quote($BarCodeID);
        $result = $this->conn->query($SqlStockNo);
        if ($result === false) {
            return ['Error' => "Unable to read stock totals: " . implode(' | ', $this->conn->errorInfo()), 'Msg' => null, 'Kgs' => 0];
        }
        $RowStock = $result->fetch(PDO::FETCH_ASSOC);
        $McsTotal = $RowStock['McsTotal'];
        $LooseTotal = $RowStock['LooseTotal'];

        if ($McsTotal == 0 && $LooseTotal == 0) {
            $SqlRackData = "UPDATE CsStoresRackData SET Status = '0', BarCodeID = '0' 
            WHERE BarCodeID = " . $this->conn->quote($BarCodeID);
            $ok = $this->conn->exec($SqlRackData);
            if ($ok === false) {
                return ['Error' => "Unable to update rack details: " . implode(' | ', $this->conn->errorInfo()), 'Msg' => null, 'Kgs' => 0];
            }
        }

        $SqlTransactionHistory = "INSERT INTO CsTransactionHistory (TrnsDate, TrnsType, Data) VALUES (
        " . $this->conn->quote($PostedDate) . ",
        " . $this->conn->quote($StockOutReason) . ",
        " . $this->conn->quote($RowStockDetails) . "
    )";
        $ok = $this->conn->exec($SqlTransactionHistory);
        if ($ok === false) {
            return ['Error' => "Unable to write transaction history: " . implode(' | ', $this->conn->errorInfo()), 'Msg' => null, 'Kgs' => 0];
        }

        return [
            'Error' => null,
            'Msg' => "Data Updated successfully",
            'Kgs' => $OutKgs
        ];
    }


    function postDailyProductionData($Request)
    {

        $IBTRequestID = $Request['IBTRequestID'];
        $ToStoreID = $Request['ToStoreID'];
        $convertLooseToMcs = $Request['convertLooseToMcs'];

        $sqlgetPlant = "SELECT ProductionPlantID,TenantID FROM `CsStores` as CS left join CsTenant as CST on CST.ProcessorID=CS.ProductionPlantID WHERE StoreID='$ToStoreID'";
        $stmtgetPlant = $this->conn->prepare($sqlgetPlant);
        $stmtgetPlant->execute();
        $dataRowgetPlant = $stmtgetPlant->fetch(PDO::FETCH_ASSOC);
        $ProductionPlantID = $dataRowgetPlant['ProductionPlantID'];
        $TenantID = $dataRowgetPlant['TenantID'];


        $sqlUpdateIbtStatus = "UPDATE `CsIBTRequestDetails` SET `RequestStatus`='3' WHERE `IBTID`='$IBTRequestID'";
        $stmtUpdateIbtStatus = $this->conn->prepare($sqlUpdateIbtStatus);
        $stmtUpdateIbtStatus->execute();
        if ($stmtUpdateIbtStatus) {

            $sqlUpdateIbtBreakUpStatus = "UPDATE `CsIBTRequestBreakUpDetails` SET `Status`='3' WHERE `IBTRequestID`='$IBTRequestID'";
            $stmtUpdateUpdateIbtBreakUpStatus = $this->conn->prepare($sqlUpdateIbtBreakUpStatus);
            $stmtUpdateUpdateIbtBreakUpStatus->execute();

            $sqlFields = "sum(NoOfMcs) as ActualMcs,sum(NoOfLooseBags) as ActualLooseBags,
            CSD.PackingTypeID,CSD.Grade,CSD.PoGradeId,CSD.FreezingType,CSD.VarietyID,CSD.Galze,CSD.SockingType,CSD.Salt,
            CSD.ProductionDate,CSD.PoID,RmLotID,ProductionTypeID,ProductCategoryID,UnitID,PlantID, ShiftID, PackingStyleID,
            StockHolderID,  AbStatus,ProdDayCode, ProdBatchCode, LineNumber,CRBD.TransferDate,Uom,TotalSlabs,SlabWeight,LbsFactor,MFactor";
            // echo
            $sqlQuery = "SELECT $sqlFields FROM CsIBTRequestBreakUpDetails AS CRBD
            LEFT JOIN CsStockData AS CSD ON CRBD.StockID=CSD.StockID
            LEFT JOIN ScrmPIVarietyList AS SPVL ON CSD.VarietyID=SPVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID=APS.PCKID
            LEFT JOIN CsIBTRequestDetails AS CIR ON CIR.IBTID=CRBD.IBTRequestID
            WHERE CRBD.IBTRequestID='$IBTRequestID' group by ShortName,PackingStyleID,SockingType,Grade";

            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();
            // $AryData = [];

            $i = 0;
            while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

                // $Kgs = $dataRow['AvalMcsKgs'] + $dataRow['LooseKgs'];

                $ActualMcs = $dataRow['ActualMcs'];
                $ActualLooseBags = $dataRow['ActualLooseBags'];
                if ($dataRow['Uom'] == 'KGS' || $dataRow['Uom'] == 'GRAMS') {
                    $ActualKgs = ($ActualMcs * $dataRow['TotalSlabs'] + $ActualLooseBags) * $dataRow['SlabWeight'] * $dataRow['MFactor'];
                } else {
                    $ActualKgs = ($ActualMcs * $dataRow['TotalSlabs'] + $ActualLooseBags) * $dataRow['SlabWeight'] * $dataRow['LbsFactor'] * 0.454;
                }

                // $ActualKgs = $dataRow['ActualKgs'] + $dataRow['ActualLooseKgs'];
                $PackingTypeID = $dataRow['PackingTypeID'];
                $Grade = $dataRow['Grade'];
                $PoGradeID = $dataRow['PoGradeId'];
                $FreezingType = $dataRow['FreezingType'];
                $VarietyID = $dataRow['VarietyID'];
                $Galze = $dataRow['Galze'];
                $SockingType = $dataRow['SockingType'];
                // $Salt = $dataRow['Salt'];
                $ProductionDate = $dataRow['TransferDate'];
                $PoID = $dataRow['PoID'];
                $RmLotID = $dataRow['RmLotID'];
                $ProductionTypeID = 'IBT IN';
                $ProductCategoryID = $dataRow['ProductCategoryID'];
                $UnitID = $ToStoreID;
                $PlantID = $ProductionPlantID;
                $ShiftID = $dataRow['ShiftID'];
                $PackingStyleID = $dataRow['PackingStyleID'];
                $StockHolderID = $TenantID;
                $AbStatus = $dataRow['AbStatus'];
                $ProdDayCode = $dataRow['ProdDayCode'];
                $ProdBatchCode = $dataRow['ProdBatchCode'];
                $LineNumber = $dataRow['LineNumber'];
                date_default_timezone_set("Asia/Kolkata");
                $CreatedDateTime = date("Y-m-d H:i:s");
                $TotalSlabs = $dataRow['TotalSlabs'];

                if ($convertLooseToMcs == 1) {
                    $LooseMcs = floor($ActualLooseBags / $TotalSlabs);
                    $ActualLooseBags = $ActualLooseBags % $TotalSlabs;
                    $ActualMcs += $LooseMcs;

                }


                if ($ActualMcs > 0 || $ActualLooseBags > 0) {
                    $sqlQuery = "INSERT INTO `CsDailyProductionData`(`ActualMcs`, `ActualLooseBags`, `AvalMcs`, `LooseBags`, 
                    `ActualKgs`,`AvalKgs`,
                    `PackingTypeID`, `Grade`, `PoGradeId`, `FreezingType`, `VarietyID`, `Galze`, `SockingType`,  
                    `ProductionDate`, `PostedDate`,
                    `PoID`, `RmLotID`, `ProductionTypeID`, `ProductCategoryID`, `UnitID`,`PlantID`, `ShiftID`, `BarCodeID`,
                    `PackingStyleID`, `StockHolderID`,  `AbStatus`, `ProdDayCode`, `ProdBatchCode`,`LineNumber`,`IBTOutID`)
                    values('$ActualMcs','$ActualLooseBags','$ActualMcs','$ActualLooseBags','$ActualKgs','$ActualKgs','$PackingTypeID','$Grade','$PoGradeID',
                    '$FreezingType','$VarietyID','$Galze','$SockingType','$ProductionDate','$CreatedDateTime','$PoID','$RmLotID','$ProductionTypeID',
                    '$ProductCategoryID','$UnitID','$PlantID','$ShiftID','0','$PackingStyleID',
                    '$StockHolderID','$AbStatus', '$ProdDayCode', '$ProdBatchCode','$LineNumber','$IBTRequestID')";

                    $stmt = $this->conn->prepare($sqlQuery);
                    $stmt->execute();
                    $ProductionID = $this->conn->lastInsertId(); //uma

                }
            }
        }
        return $ProductionID;
    }
    function isStockAvailable($StockItems)
    {
        foreach ($StockItems as $Item) {
            $StockId = $Item['StockID'];
            $OutMcs = $Item['OutMcs'];
            $OutLooseBags = $Item['OutLooseBags'];

            $sqlSelect = "SELECT AvalMcs, LooseBags,TotalSlabs,SlabWeight,MFactor FROM CsStockData AS CSD LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID=PCKID WHERE StockID = :StockId";
            $stmtSelect = $this->conn->prepare($sqlSelect);
            $stmtSelect->bindParam(':StockId', $StockId, PDO::PARAM_INT);
            $stmtSelect->execute();

            $rowData = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            if ($rowData['LooseBags'] < $OutLooseBags) {
                $remOutLooseBags = $OutLooseBags - $rowData['LooseBags'];
                $deductMcs = ceil($remOutLooseBags / $rowData['TotalSlabs']);

                $deductLoose = $deductMcs * $rowData['TotalSlabs'];

                $rowData['AvalMcs'] = $rowData['AvalMcs'] - $deductMcs;
                $rowData['LooseBags'] = $rowData['LooseBags'] + $deductLoose;

            }
            if (!$rowData || $rowData['AvalMcs'] < $OutMcs || $rowData['LooseBags'] < $OutLooseBags) {
                return true;
            }
        }
        return false;
    }
}
