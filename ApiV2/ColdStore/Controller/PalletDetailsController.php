<?php
include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
class PalletDetailsController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
    }
    function getPalletDetails($RequestBody)
    {

        $RackID = $RequestBody['RackID'];
        $Query = '';

        $SqlRackData = "SELECT * FROM `CsStoresRackData` WHERE RowID = '$RackID'";
        $stmtRackData = $this->conn->prepare($SqlRackData);
        $stmtRackData->execute();
        $dataRow = $stmtRackData->fetch(PDO::FETCH_ASSOC);
        $DataCount = $stmtRackData->rowCount();
        $BarCodeID = $dataRow['BarCodeID'];
        $PlacedStockID = $dataRow['PlacedStockID'];

        if ($DataCount == 0) {
            $Data['Error'] = 'No Data Found';
            $Data['Msg'] = 'No Data Found';
            return $Data;
        } else {
            $SqlFields = 'VL.TypeProduct,ShortName,CSD.Grade,CSD.StockID,CSD.PackingTypeID,APS.PackingStyle,CSD.FreezingType,CSD.ProductCategoryID,CSD.AvalMcs,CSD.LooseBags,
             AGO.Name as Shift,SPD.PoNo,CSD.ProductionID,VL.Species,((CSD.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) + (CSD.LooseBags * APS.SlabWeight * APS.MFactor)) AS Kgs';

            // SELECT PoID PoNo FROM ScrmPoData WHERE 1

            // left JOIN CsStoresRackData as CRD on CRD.BarCodeID=CSD.BarCodeID #teja
            // where CSD.BarCodeID = '$BarCodeID' #Teja
             $sqlStock = "SELECT $SqlFields FROM `CsStockData` as CSD

            left JOIN ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
            left join AdminPackingStyles as APS   on APS.PCKID=CSD.PackingStyleID
            left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
            LEFT JOIN ScrmPoData AS SPD ON CSD.PoID = SPD.PoID

            where CSD.StockID in ($PlacedStockID) and StockStatus=1
            AND (AvalMcs!=0 or LooseBags !=0)";
            debug_report($sqlStock);
            $stmtStock = $this->conn->prepare($sqlStock);
            $stmtStock->execute();

            $i = 0;
            $Total = [];
            while ($dataRowStock = $stmtStock->fetch(PDO::FETCH_ASSOC)) {

                $Mcs = $dataRowStock['AvalMcs'];
                $LoosBags = $dataRowStock['LooseBags'];
                $Kgs = $dataRowStock['Kgs'];
                $ArrayPalletDetails['SNO'] = ++$i;
                $ArrayPalletDetails['StockID'] = $dataRowStock['StockID'];
                $ArrayPalletDetails['PoNo'] = $dataRowStock['PoNo'];
                $ArrayPalletDetails['Species'] = $dataRowStock['Species'];
                $ArrayPalletDetails['Grade'] = $dataRowStock['Grade'];
                $ArrayPalletDetails['Variety'] = $dataRowStock['ShortName'];
                $ArrayPalletDetails['PackingType'] = $dataRowStock['PackingTypeID'];
                $ArrayPalletDetails['TypeProduct'] = $dataRowStock['TypeProduct'];
                $ArrayPalletDetails['PackingStyle'] = $dataRowStock['PackingStyle'];
                $ArrayPalletDetails['FreezingType'] = $dataRowStock['FreezingType'];
                $ArrayPalletDetails['Category'] = $dataRowStock['ProductCategoryID'];
                $ArrayPalletDetails['Shift'] = $dataRowStock['Shift'];
                $ArrayPalletDetails['Mcs'] = $Mcs;
                $ArrayPalletDetails['Loose'] = $LoosBags;
                $ArrayPalletDetails['Kgs'] = round($Kgs,2);


                $ArrayPalleteData[] = $ArrayPalletDetails;
                $Total['TotalMcs'] += $Mcs;
                $Total['TotalLooseBags'] += $LoosBags;
                $Total['TotalKgs'] += $Kgs;
            }


            $data['Error'] = null;
            $data['Msg'] = 'Success';
            $data['Data'] = $ArrayPalleteData;
            $data['Totals'] = $Total;             
            return $data;
        }
    }
}
