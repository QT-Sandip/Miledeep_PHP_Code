<?php

include_once '../Global/Util.php';
include ($_SERVER['DOCUMENT_ROOT']."/includes/cache.php");

class RawMaterialRequirementReportSMEController
{

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getRequiredRawMaterialDetails($RequestBody)
    {

        $ClientID = isset($RequestBody['ClientID'])
            ? $RequestBody['ClientID']
            : 0;

        $PoID = isset($RequestBody['PoID'])
            ? $RequestBody['PoID']
            : '';

        $SearchKey = isset($RequestBody['SearchKey'])
            ? $RequestBody['SearchKey']
            : '';

        $PageNo = isset($RequestBody['PageNo'])
            ? $RequestBody['PageNo']
            : 1;

        $LoadAll = isset($RequestBody['LoadAll'])
            ? $RequestBody['LoadAll']
            : 0;

        $ProductionPlantID = isset($RequestBody['Plant'])
            ? $RequestBody['Plant']
            : '';

        $RecordsPerPage = 100;

        $PlantIDQuery = '';
        $StorePlantIDQuery = '';

        if ($ProductionPlantID != '') {

            $PlantIDQuery = "
            AND FIND_IN_SET('$ProductionPlantID', SPD.ProductionPlantID) > 0";

            $StorePlantIDQuery = "
            AND FIND_IN_SET('$ProductionPlantID', CPID.PlantID) > 0";
        }

        $QueryData = $this->getFilterQuery($RequestBody);

        $FilterPoQuery = $QueryData['PoQuery'];

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;

        $Limit = " LIMIT $LimitMax,$RecordsPerPage ";

        if ($LoadAll == 1) {
            $Limit = '';
        }

        $SearchQuery = '';

        if ($SearchKey != '') {

            $SearchKey = addslashes($SearchKey);

            $SearchQuery = "
            AND CONCAT_WS('',
                VL.Species,
                VL.ProductDescription,
                VL.TypeProduct,
                VL.Glaze,
                VL.GlazePerc,
                VL.Treatment,
                VL.FreezingType,
                VL.Grade,
                VL.ShortName,
                BM.BrandName,
                CSP.PackingStyle
            ) LIKE '%$SearchKey%'";
        }

        // ---------------- GET PO IDS ----------------

        if ($PoID == '') {

            $SqlPoIds = "

            SELECT GROUP_CONCAT(SPD.PoID) as PoID

            FROM ScrmPoData AS SPD

            WHERE SPD.PoID NOT IN (

                SELECT PiID
                FROM ScrmCustomInvoices
                WHERE StockOutDate != ''

            )

            AND SPD.Status NOT IN (2,3,4)

            $PlantIDQuery";

            $stmtPoIds = $this->conn->prepare($SqlPoIds);
            $stmtPoIds->execute();

            $PoData = $stmtPoIds->fetch(PDO::FETCH_ASSOC);

            $PoID = isset($PoData['PoID'])
                ? $PoData['PoID']
                : '';
        }

        if ($PoID == '') {

            $data['Error'] = 'No PO Found';
            $data['Msg'] = 'No PO Found';
            $data['TotalCount'] = 0;
            $data['PageCount'] = 0;
            $data['UnitArray'] = array();
            $data['Data'] = array();

            return $data;
        }

        // ---------------- TOTAL COUNT ----------------

        $sqlTotals = "

        SELECT COUNT(*) AS Total

        FROM ScrmPoGradeData AS SPGD

        INNER JOIN ScrmPoData AS SPD
            ON SPGD.InvId = SPD.PoID

        LEFT JOIN ScrmPIVarietyList AS VL
            ON VL.VarietyID = SPGD.VarietyID

        LEFT JOIN ScrmBrandsMaster AS BM
            ON VL.BrandName = BM.BrandID

        LEFT JOIN AdminPackingStyles AS CSP
            ON CSP.PckID = SPGD.PackingStyleID

        WHERE SPD.PoID IN ($PoID)

        AND SPD.PoID NOT IN (

            SELECT PiID
            FROM ScrmCustomInvoices
            WHERE StockOutDate != ''

        )

        $FilterPoQuery
        $SearchQuery";

        $stmtTotals = $this->conn->prepare($sqlTotals);
        $stmtTotals->execute();

        $TotalRow = $stmtTotals->fetch(PDO::FETCH_ASSOC);

        $TotalCount = isset($TotalRow['Total'])
            ? $TotalRow['Total']
            : 0;

        $PageCount = ceil($TotalCount / $RecordsPerPage);

        // ---------------- MAIN QUERY ----------------

        $sql = "

        SELECT

            SPD.PoNo,
            VL.Species,
            VL.TypeProduct AS ProductType,
            VL.Glaze,
            VL.GlazePerc,
            BM.BrandName AS Brand,
            CSP.PackingStyle,
            VL.Treatment AS SockingType,
            VL.FreezingType,
            VL.Grade,
            VL.ShortName,

            IFNULL(SPGD.Mcs,0) AS OrderedKgs,

            IFNULL(CSD.AvalKGs,0) AS AvalKGs

        FROM ScrmPoGradeData AS SPGD

        INNER JOIN ScrmPoData AS SPD
            ON SPGD.InvId = SPD.PoID

        LEFT JOIN ScrmPIVarietyList AS VL
            ON VL.VarietyID = SPGD.VarietyID

        LEFT JOIN ScrmBrandsMaster AS BM
            ON VL.BrandName = BM.BrandID

        LEFT JOIN AdminPackingStyles AS CSP
            ON CSP.PckID = SPGD.PackingStyleID

        LEFT JOIN (

            SELECT

                CSD1.PoGradeId,

                SUM(
                    IFNULL(CSD1.AvalMcs,0) +
                    (
                        IFNULL(CSD1.LooseBags,0) /
                        IFNULL(NULLIF(CSP.TotalSlabs,0),1)
                    )
                ) AS AvalKGs

            FROM CsStockData AS CSD1

            LEFT JOIN AdminPackingStyles AS CSP
                ON CSP.PckID = CSD1.PackingStyleID

            WHERE CSD1.PackingTypeID='MASTER'

            GROUP BY CSD1.PoGradeId

        ) AS CSD
            ON CSD.PoGradeId = SPGD.ID

        WHERE SPD.PoID IN ($PoID)

        AND SPD.PoID NOT IN (

            SELECT PiID
            FROM ScrmCustomInvoices
            WHERE StockOutDate != ''

        )

        $FilterPoQuery
        $SearchQuery

        ORDER BY ABS(SPD.ShipByDate) ASC

        $Limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $Result = array();

        $i = $LimitMax;

        $UnitArray = array();
        $AddedUnits = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $Species = addslashes($row['Species']);
            $Grade = addslashes($row['Grade']);
            $ShortName = addslashes($row['ShortName']);
            $ProductType = addslashes($row['ProductType']);
            $FreezingType = addslashes($row['FreezingType']);
            $Glaze = addslashes($row['Glaze']);
            $SockingType = addslashes($row['SockingType']);

            $Filters = "

            AND VL.Species = '$Species'
            AND VL.Grade = '$Grade'
            AND VL.ShortName = '$ShortName'
            AND VL.TypeProduct = '$ProductType'
            AND VL.FreezingType = '$FreezingType'
            AND VL.Glaze = '$Glaze'
            AND VL.Treatment = '$SockingType'";

            $StoreQty = $this->getAvailableStorekgsNew(
                $Filters,
                $StorePlantIDQuery
            );

            foreach ($StoreQty as $store) {

                if (!in_array($store['UnitId'], $AddedUnits)) {

                    $UnitArray[] = array(
                        'UnitId' => $store['UnitId'],
                        'UnitName' => $store['StoreName']
                    );

                    $AddedUnits[] = $store['UnitId'];
                }
            }

            $OrderedKgs = (float)$row['OrderedKgs'];
            $AvalKGs = (float)$row['AvalKGs'];

            $RequiredQty = $OrderedKgs - $AvalKGs;

            $DisplayColour = '';

            if ($RequiredQty <= 0) {
                $DisplayColour = 'red';
            }

            $Result[] = array(

                'SNO' => ++$i,

                'PoNo' => $row['PoNo'],

                'Brand' => $row['Brand'],

                'Species' => $row['Species'],

                'Variety' => $row['ShortName'],

                'Grade' => $row['Grade'],

                'PackingStyle' => $row['PackingStyle'],

                'OrderQuantity' =>
                    number_format($OrderedKgs, 3, '.', ''),

                'AvalKGs' =>
                    number_format($AvalKGs, 3, '.', ''),

                'RequiredQuantity' =>
                    number_format($RequiredQty, 3, '.', ''),

                'DisplayColour' => $DisplayColour,

                'StoreQuantity' => $StoreQty
            );
        }

        $data = array();

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['UnitArray'] = $UnitArray;
        $data['Data'] = $Result;

        return $data;
    }

    function getFilterQuery($RequestBody)
    {

        $PoQuery = '';

        if (
            isset($RequestBody['Species']) &&
            $RequestBody['Species'] != ''
        ) {

            $Species = addslashes($RequestBody['Species']);

            $PoQuery .= " AND VL.Species='$Species'";
        }

        if (
            isset($RequestBody['ProductType']) &&
            $RequestBody['ProductType'] != ''
        ) {

            $ProductType = addslashes($RequestBody['ProductType']);

            $PoQuery .= " AND VL.TypeProduct='$ProductType'";
        }

        if (
            isset($RequestBody['FreezingType']) &&
            $RequestBody['FreezingType'] != ''
        ) {

            $FreezingType = addslashes($RequestBody['FreezingType']);

            $PoQuery .= " AND VL.FreezingType='$FreezingType'";
        }

        if (
            isset($RequestBody['Grade']) &&
            $RequestBody['Grade'] != ''
        ) {

            $Grade = addslashes($RequestBody['Grade']);

            $PoQuery .= " AND VL.Grade='$Grade'";
        }

        if (
            isset($RequestBody['Plant']) &&
            $RequestBody['Plant'] != ''
        ) {

            $Plant = addslashes($RequestBody['Plant']);

            $PoQuery .= "
            AND FIND_IN_SET(
                '$Plant',
                SPD.ProductionPlantID
            ) > 0";
        }

        return array(
            'PoQuery' => $PoQuery
        );
    }

    function getAvailableStorekgsNew($Filters, $StorePlantIDQuery)
    {

        $sql = "

        SELECT

            SUM(
                IFNULL(CPID.AvalMcs,0) +
                (
                    IFNULL(CPID.LooseBags,0) /
                    IFNULL(NULLIF(CSP.TotalSlabs,0),1)
                )
            ) AS AvailableKgsinStore,

            CPID.UnitID,
            CS.StoreName

        FROM CsStockData AS CPID

        LEFT JOIN ScrmPIVarietyList AS VL
            ON VL.VarietyID = CPID.VarietyID

        LEFT JOIN AdminPackingStyles AS CSP
            ON CSP.PckID = CPID.PackingStyleID

        LEFT JOIN CsStores AS CS
            ON CS.StoreID = CPID.UnitID

        WHERE
            (CPID.AvalMcs != 0 OR CPID.LooseBags != 0)

            AND CPID.PackingTypeID='DUMMY'

            $Filters
            $StorePlantIDQuery

        GROUP BY CPID.UnitID";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $Result = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $AvailableKgsinStore = 0;

            if (isset($row['AvailableKgsinStore'])) {
                $AvailableKgsinStore = $row['AvailableKgsinStore'];
            }

            $Result[] = array(

                'UnitId' => $row['UnitID'],

                'StoreName' => $row['StoreName'],

                'AvailableKgsinStore' =>
                    number_format(
                        (float)$AvailableKgsinStore,
                        3,
                        '.',
                        ''
                    )
            );
        }

        return $Result;
    }
}