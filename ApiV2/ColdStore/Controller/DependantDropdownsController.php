<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';

class DependantDropdownsController
{
    private $conn;
    //private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    public function getDependantDropdowns($RequestBody)
    {
        $Type = $RequestBody['Type'];
        $PoID = $RequestBody['PoID'];

        if ($Type == 'Variety') {
            $DependentDropdownsData = $this->getVarieties($RequestBody);
        } else if ($Type == 'Grades') {
            $DependentDropdownsData = $this->getGrades($RequestBody);
        } else if ($Type == 'ADVariety') {
            $DependentDropdownsData = $this->getVarietiesForAntiDumping($RequestBody);
        } else if ($Type == 'ADGrades') {
            $DependentDropdownsData = $this->getAntiDumpingGrades($RequestBody);
        }


        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DependentDropdownsData'] = $DependentDropdownsData;
        return $Data;
    }


    function getVarieties($RequestBody)
    {

        $PoID = $RequestBody['PoID'];
        $PackingType = $RequestBody['PackingType'];
        $Variety = $RequestBody['Variety'];

        $Shortname = $RequestBody['Shortname'];
        $Typeproduct = $RequestBody['Typeproduct'];
        $Grade = $RequestBody['Grade'];
        $Treatment = $RequestBody['Treatment'];
        $Species = $RequestBody['Species'];

        $dataRow = null;
        // $Query = '';

        // if ($Shortname != '') {
        //     $shortNameList = implode(", ", array_map(function ($name) {
        //         return "'" . addslashes($name) . "'";
        //     }, explode(",", $Shortname)));
        //     $Query .= " AND VL.ShortName IN ($shortNameList)";
        // }
        // if ($Typeproduct != '') {
        //     $TypeproductList = implode(", ", array_map(function ($typeproduct) {
        //         return "'" . addslashes($typeproduct) . "'";
        //     }, explode(",", $Typeproduct)));
        //     $Query .= " AND VL.TypeProduct IN ($TypeproductList)";
        // }
        // if ($Grade != '') {
        //     $GradeList = implode(", ", array_map(function ($grade) {
        //         return "'" . addslashes($grade) . "'";
        //     }, explode(",", $Grade)));
        //     $Query .= " AND VL.Grade IN ($GradeList)";
        // }
        // if ($Treatment != '') {
        //     $TreatmentList = implode(", ", array_map(function ($treatment) {
        //         return "'" . addslashes($treatment) . "'";
        //     }, explode(",", $Treatment)));
        //     $Query .= " AND VL.Treatment IN ($TreatmentList)";
        // }
        // if ($Species != '') {
        //     $SpeciesList = implode(", ", array_map(function ($species) {
        //         return "'" . addslashes($species) . "'";
        //     }, explode(",", $Species)));
        //     $Query .= " AND VL.Species IN ($SpeciesList)";
        // }

        if ($PoID != '') {
            $sqlFields = " ShortName ,PGD.Grade as GradeName,ID as PoGradeID,TypeProduct,VL.DeviSUPC,VL.FullName,VL.Glaze,VL.GlazePerc,
            VL.VarietyID,SBM.BrandName as Brand,CPS.PackingStyle,PGD.Mcs, VL.Species, VL.TypeProduct, CPS.PckID,VL.FreezingType,VL.Treatment,SBM.BrandID";

            $sqlQuery = "SELECT  $sqlFields   FROM `ScrmPoGradeData` as PGD 
            left Join AdminPackingStyles as CPS on PGD.PackingStyleID=CPS.PckID 
            left Join ScrmPIVarietyList as VL on VL.VarietyID=PGD.VarietyID 
            left Join ScrmBrandsMaster as SBM on SBM.BrandID=VL.BrandName 
            WHERE PGD.InvId='$PoID'";
            // WHERE PGD.InvId='$PoID' $Query";
        } else if ($Variety != '') {            
            $sqlFields = " ShortName ,VL.Grade as GradeName,VL.VarietyID,TypeProduct,VL.DeviSUPC,VL.FullName,VL.ProductDescription,VL.Glaze,VL.GlazePerc,
            SBM.BrandName as Brand,CPS.PackingStyle,VL.Species, VL.TypeProduct, CPS.PckID, VL.FreezingType,VL.Treatment,SBM.BrandID";
            $sqlQuery = "SELECT $sqlFields FROM ScrmPIVarietyList as VL 
            left Join AdminPackingStyles as CPS on VL.PackingStyle=CPS.PckID 
            left Join ScrmBrandsMaster as SBM on SBM.BrandID=VL.BrandName where VL.VarietyID=$Variety";
        } else {
            $Query = '';
            if ($Species != '') {
                $Query = " AND VL.Species = '$Species' ";
            }
            $sqlFields = " ShortName ,VL.Grade as GradeName,VL.VarietyID,TypeProduct,VL.DeviSUPC,VL.FullName,VL.ProductDescription,VL.Glaze,VL.GlazePerc,
            SBM.BrandName as Brand,CPS.PackingStyle,VL.Species, VL.TypeProduct, CPS.PckID, VL.FreezingType,VL.Treatment,SBM.BrandID";
            $sqlQuery = "SELECT $sqlFields FROM ScrmPIVarietyList as VL 
            left Join AdminPackingStyles as CPS on VL.PackingStyle=CPS.PckID 
            left Join ScrmBrandsMaster as SBM on SBM.BrandID=VL.BrandName where VL.Status=1 $Query";
            // left Join ScrmBrandsMaster as SBM on SBM.BrandID=VL.BrandName where VL.Status=1 $Query";

        }

        // $sqlQuery;

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $ArrayVarietyData = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if ($PoID == '') {
                $VarietyID = $dataRow["VarietyID"];
                $VarietyName = $dataRow["ShortName"] . ' - ' . $dataRow["GradeName"] . ' - ' . $dataRow["PackingStyle"] . ' - ' . $dataRow["Brand"];
                $VarietyName = $dataRow["ProductDescription"];
            } else {
                $VarietyID = $dataRow['VarietyID'];
                $VarietyName = $dataRow["ShortName"] . ' - ' . $dataRow["GradeName"] . ' - ' . $dataRow["PackingStyle"] . ' (' . $dataRow["Mcs"] . ' Mcs)';
            }

            if (isset($dataRow['PoGradeID']) && !empty($dataRow['PoGradeID'])) {
                $PoGradeID = $dataRow['PoGradeID'];
            } else {
                $PoGradeID = '';
            }

            $PackingStyle = $dataRow['PackingStyle'];
            $PackingStyleNew = str_replace(" ", "", $PackingStyle);
            $ArrayVarietyData['VarietyID'] = $VarietyID;
            $ArrayVarietyData['VarietyName'] = $VarietyName;
            $ArrayVarietyData['PoGradeID'] = $PoGradeID;
            $ArrayVarietyData['ShortName'] = $dataRow['ShortName'];
            $ArrayVarietyData['Grade'] = $dataRow['GradeName'];
            $ArrayVarietyData['FullName'] = $dataRow['FullName'];
            $ArrayVarietyData['DeviSUPC'] = $dataRow['DeviSUPC'];
            $ArrayVarietyData['TypeProduct'] = $dataRow['TypeProduct'];
            $ArrayVarietyData['PackingStyle'] = $PackingStyleNew;
            $ArrayVarietyData['Brand'] = $dataRow['Brand'];
            $ArrayVarietyData['BrandID'] = $dataRow['BrandID'];
            $ArrayVarietyData['Species'] = $dataRow['Species'];
            $ArrayVarietyData['TypeProduct'] = $dataRow['TypeProduct'];
            $ArrayVarietyData['PackingStyleID'] = $dataRow['PckID'];
            $ArrayVarietyData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayVarietyData['Soaking'] = $dataRow['Treatment'];
            $ArrayVarietyData['Glaze'] = $dataRow['Glaze'];
            $ArrayVarietyData['GlazePerc'] = $dataRow['GlazePerc'];

            $AryVarietyList[] = $ArrayVarietyData;
        }
        if ($PackingType == 'MASTER') {
            $ArrayGradeData['PackingStyle'] = 'Disabled';
        } else {
            $ArrayGradeData['PackingStyle'] = 'Enable';
        }

        if ($PoID == '') {
            $ArrayGradeData['DisablePackingType'] = 'Disabled';
        } else {
            $ArrayGradeData['DisablePackingType'] = 'Enable';
        }
        $Data['VarietyData'] = $AryVarietyList;
        $Data['ReadonlyDropdowns'] = $ArrayGradeData;
        return $Data;
    }
    function getGrades($RequestBody)
    {
        $Variety = $RequestBody['VarietyID'];
        $BreakUpID = $RequestBody['PoGradeID'];
        $PackingType = $RequestBody['PackingType'];
        $dataRow = null;


        if ($BreakUpID != 0 && $BreakUpID != '') {
            $sqlQuery = "SELECT VarietyID FROM `ScrmPoGradeData` WHERE ID=$BreakUpID";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            $VarietyData = $stmt->fetch(PDO::FETCH_ASSOC);
            $Variety = $VarietyData['VarietyID'];
        }
        // $sqlQuery = "SELECT Grade as GradeName,
        //     FreezingType as FreezingTypeID,
        //     PackingStyle as PackingStyleID,
        //     VarietyID,
        //     GlazePerc,Treatment as SockingType,PackingStyle
        //     FROM `ScrmPIVarietyList` WHERE VarietyID='$Variety'";

        $sqlQuery = "SELECT Grade as GradeName,
            FreezingType as FreezingTypeID,
            CASE 
                WHEN HasSauce = 'Yes' THEN ShrimpPackingStyle 
                ELSE PackingStyle
            END AS PackingStyleID,
            VarietyID,
            GlazePerc,Glaze, Treatment as SockingType, PackingStyle
            FROM `ScrmPIVarietyList` WHERE VarietyID='$Variety'";


        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $ArrayGradeData['Grade'] = $dataRow['GradeName'];
        $ArrayGradeData['VarietyID'] = $Variety;
        $ArrayGradeData['PoGradeID'] = $BreakUpID;
        $ArrayGradeData['FreezingTypeID'] = $dataRow['FreezingTypeID'];
        $ArrayGradeData['PackingStyleID'] = $dataRow['PackingStyleID'];
        $ArrayGradeData['GlazePerc'] = $dataRow['GlazePerc'];
        $ArrayGradeData['Glaze'] = $dataRow['Glaze'];
        $ArrayGradeData['SoakingType'] = $dataRow['SockingType'];
        if ($PackingType == 'MASTER') {
            $ArrayGradeData['PackingStyle'] = 'Disabled';
        } else {
            $ArrayGradeData['PackingStyle'] = 'Enable';
        }

        $Data['GradeData'] = $ArrayGradeData;
        return $Data;
    }

    function getVarietiesForAntiDumping($RequestBody)
    {

        $PoID = $RequestBody['PoID'];
        $PackingType = $RequestBody['PackingType'];
        $Variety = $RequestBody['Variety'];

        $Shortname = $RequestBody['Shortname'];
        $Typeproduct = $RequestBody['Typeproduct'];
        $Grade = $RequestBody['Grade'];
        $Treatment = $RequestBody['Treatment'];
        $Species = $RequestBody['Species'];

        $dataRow = null;

        if ($PoID != '') {
            // $sqlFields = " ShortName ,PGD.Grade as GradeName,ID as PoGradeID,TypeProduct,VL.DeviSUPC,VL.FullName,
            // VL.VarietyID,SBM.BrandName as Brand,CPS.PackingStyle,PGD.Mcs, VL.Species, VL.TypeProduct, CPS.PckID,VL.FreezingType,VL.Treatment";

            // $sqlQuery = "SELECT  $sqlFields   FROM `ScrmPoGradeData` as PGD 
            // left Join AdminPackingStyles as CPS on PGD.PackingStyleID=CPS.PckID 
            // left Join ScrmPIVarietyList as VL on VL.VarietyID=PGD.VarietyID 
            // left Join ScrmBrandsMaster as SBM on SBM.BrandID=VL.BrandName 
            // WHERE PGD.InvId='$PoID'";
            $sqlFields = "
    VL.ShortName,
    PGD.Grade AS GradeName,
    PGD.ID AS PoGradeID,
    VL.TypeProduct,
    VL.DeviSUPC,
    VL.FullName,
    VL.VarietyID,
    SBM.BrandName AS Brand,
    CPS.PackingStyle,
    PGD.Mcs,
    VL.Species,
    VL.TypeProduct,
    CPS.PckID,
    VL.FreezingType,
    VL.Treatment
";

            $sqlQuery = "
SELECT 
    $sqlFields
FROM ScrmPoGradeData AS PGD
LEFT JOIN AdminPackingStyles AS CPS 
    ON PGD.PackingStyleID = CPS.PckID
LEFT JOIN (
    SELECT v1.*
    FROM ScrmPIVarietyList v1
    INNER JOIN (
        SELECT
            MAX(VarietyID) AS MaxVarietyID
        FROM ScrmPIVarietyList
        WHERE STATUS = 1
        GROUP BY ShortName, PackingStyle, Species, TypeProduct, FreezingType, Treatment, Grade, Glaze, PreparationType
    ) latest 
        ON v1.VarietyID = latest.MaxVarietyID
) VL
    ON VL.ShortName     = (SELECT ShortName FROM ScrmPIVarietyList WHERE VarietyID = PGD.VarietyID LIMIT 1)
   AND VL.PackingStyle = (SELECT PackingStyle FROM ScrmPIVarietyList WHERE VarietyID = PGD.VarietyID LIMIT 1)
   AND VL.Species      = (SELECT Species FROM ScrmPIVarietyList WHERE VarietyID = PGD.VarietyID LIMIT 1)
   AND VL.TypeProduct  = (SELECT TypeProduct FROM ScrmPIVarietyList WHERE VarietyID = PGD.VarietyID LIMIT 1)
   AND VL.FreezingType = (SELECT FreezingType FROM ScrmPIVarietyList WHERE VarietyID = PGD.VarietyID LIMIT 1)
   AND VL.Treatment    = (SELECT Treatment FROM ScrmPIVarietyList WHERE VarietyID = PGD.VarietyID LIMIT 1)
   AND VL.Grade        = (SELECT Grade FROM ScrmPIVarietyList WHERE VarietyID = PGD.VarietyID LIMIT 1)
   AND VL.Glaze        = (SELECT Glaze FROM ScrmPIVarietyList WHERE VarietyID = PGD.VarietyID LIMIT 1)
   AND VL.PreparationType = (SELECT PreparationType FROM ScrmPIVarietyList WHERE VarietyID = PGD.VarietyID LIMIT 1)
LEFT JOIN ScrmBrandsMaster AS SBM 
    ON SBM.BrandID = VL.BrandName
WHERE PGD.InvId = '$PoID'
";

        } else if ($Variety != '') {
            $sqlFields = " ShortName ,VL.Grade as GradeName,VL.VarietyID,TypeProduct,VL.DeviSUPC,VL.FullName,VL.ProductDescription,
            SBM.BrandName as Brand,CPS.PackingStyle,VL.Species, VL.TypeProduct, CPS.PckID, VL.FreezingType,VL.Treatment";
            $sqlQuery = "SELECT $sqlFields FROM ScrmPIVarietyList as VL 
            left Join AdminPackingStyles as CPS on VL.PackingStyle=CPS.PckID 
            left Join ScrmBrandsMaster as SBM on SBM.BrandID=VL.BrandName where VL.VarietyID=$Variety";
        } else {
            //         $sqlFields = " ShortName ,VL.Grade as GradeName,VL.VarietyID,TypeProduct,VL.DeviSUPC,VL.FullName,GROUP_CONCAT(
            //     DISTINCT CASE 
            //         WHEN VL.ProductDescription IS NOT NULL 
            //              AND TRIM(VL.ProductDescription) <> '' 
            //         THEN VL.ProductDescription 
            //     END
            // ) AS ProductDescription,
            //         SBM.BrandName as Brand,CPS.PackingStyle,VL.Species, VL.TypeProduct, CPS.PckID, VL.FreezingType,VL.Treatment";
            //         $sqlQuery = "SELECT $sqlFields FROM ScrmPIVarietyList as VL 
            //         left Join AdminPackingStyles as CPS on VL.PackingStyle=CPS.PckID 
            //         left Join ScrmBrandsMaster as SBM on SBM.BrandID=VL.BrandName where VL.Status=1 
            //         GROUP BY ShortName,CPS.PackingStyle,VL.Species, VL.TypeProduct, VL.FreezingType,VL.Treatment,VL.Grade,VL.GlazePerc,VL.Glaze,VL.PreparationType";
            //         $sqlFields = "
            //         VL.ShortName,
            //         VL.Grade AS GradeName,
            //         VL.VarietyID,
            //         TypeProduct,
            //         VL.DeviSUPC,
            //         VL.FullName,
            //         GROUP_CONCAT(
            //             DISTINCT CASE 
            //                 WHEN VL.ProductDescription IS NOT NULL 
            //                     AND TRIM(VL.ProductDescription) <> '' 
            //                 THEN VL.ProductDescription 
            //             END
            //         ) AS ProductDescription,
            //         SBM.BrandName AS Brand,
            //         CPS.PackingStyle,
            //         VL.Species,
            //         VL.TypeProduct,
            //         CPS.PckID,
            //         VL.FreezingType,
            //         VL.Treatment
            //     ";

            //     $sqlQuery = "
            //     SELECT $sqlFields
            //     FROM ScrmPIVarietyList AS VL
            //     LEFT JOIN AdminPackingStyles AS CPS ON VL.PackingStyle = CPS.PckID
            //     LEFT JOIN ScrmBrandsMaster AS SBM ON SBM.BrandID = VL.BrandName
            //     INNER JOIN (
            //         SELECT ShortName, MAX(VarietyID) AS MaxVarietyID
            //         FROM ScrmPIVarietyList
            //         WHERE Status = 1
            //         GROUP BY ShortName,PackingStyle,Species, TypeProduct, FreezingType,Treatment,Grade,GlazePerc,Glaze,PreparationType
            //     ) AS latest ON VL.VarietyID = latest.MaxVarietyID
            //     WHERE VL.Status = 1
            //    GROUP BY ShortName,CPS.PackingStyle,VL.Species, VL.TypeProduct, VL.FreezingType,VL.Treatment,VL.Grade,VL.GlazePerc,VL.Glaze,VL.PreparationType";


            $sqlFields = "
                    VL.ShortName,
                    VL.Grade AS GradeName,
                    VL.VarietyID,
                    VL.TypeProduct,
                    VL.DeviSUPC,
                    VL.FullName,
                    GROUP_CONCAT(
                        DISTINCT CASE 
                            WHEN VL.ProductDescription IS NOT NULL 
                                AND TRIM(VL.ProductDescription) <> '' 
                            THEN VL.ProductDescription 
                        END
                    ) AS ProductDescription,
                    SBM.BrandName AS Brand,
                    CPS.PackingStyle,
                    VL.Species,
                    VL.TypeProduct,
                    CPS.PckID,
                    VL.FreezingType,
                    VL.Treatment
                ";

            $sqlQuery = "
              SELECT 
    $sqlFields
FROM ScrmPIVarietyList AS VL
INNER JOIN (
    SELECT 
        ShortName,
        MAX(VarietyID) AS MaxVarietyID,
        PackingStyle,
        Species,
        TypeProduct,
        FreezingType,
        Treatment,
        Grade,
        Glaze,
        PreparationType,
        (CASE WHEN GlazePerc IS NULL OR GlazePerc = 0 THEN 0 ELSE GlazePerc END) AS NormalizedGlazePerc
    FROM ScrmPIVarietyList
    WHERE Status = 1
    GROUP BY ShortName, PackingStyle, Species, TypeProduct, FreezingType, Treatment, Grade,
             Glaze, PreparationType,
             (CASE WHEN GlazePerc IS NULL OR GlazePerc = 0 THEN 0 ELSE GlazePerc END)
) AS latest 
    ON VL.VarietyID = latest.MaxVarietyID
LEFT JOIN AdminPackingStyles AS CPS 
    ON latest.PackingStyle = CPS.PckID
LEFT JOIN ScrmBrandsMaster AS SBM
    ON SBM.BrandID = VL.BrandName
WHERE VL.Status = 1
GROUP BY 
    VL.ShortName, 
    CPS.PackingStyle,
    VL.Species, 
    VL.TypeProduct, 
    VL.FreezingType, 
    VL.Treatment, 
    VL.Grade, 
    latest.NormalizedGlazePerc,
    VL.Glaze, 
    VL.PreparationType;
";
        }

        // echo $sqlQuery;exit;

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $ArrayVarietyData = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if ($PoID == '') {
                $VarietyID = $dataRow["VarietyID"];
                $VarietyName = $dataRow["ShortName"] . ' - ' . $dataRow["GradeName"] . ' - ' . $dataRow["PackingStyle"] . ' - ' . $dataRow["Brand"];
                $VarietyName = $dataRow["ProductDescription"];
            } else {
                $VarietyID = $dataRow['VarietyID'];
                // if (empty($VarietyID)) {
                //     $sqlFields1 = "
                //         VL.ShortName,
                //         PGD.Grade AS GradeName,
                //         PGD.ID AS PoGradeID,
                //         VL.TypeProduct,
                //         VL.DeviSUPC,
                //         VL.FullName,
                //         VL.VarietyID,
                //         SBM.BrandName AS Brand,
                //         CPS.PackingStyle,
                //         PGD.Mcs,
                //         VL.Species,
                //         VL.TypeProduct,
                //         CPS.PckID,
                //         VL.FreezingType,
                //         VL.Treatment
                //     ";

                //     $sqlQuery1 = "
                //     SELECT 
                //         $sqlFields1
                //     FROM ScrmPoGradeData AS PGD
                //     LEFT JOIN AdminPackingStyles AS CPS 
                //         ON PGD.PackingStyleID = CPS.PckID
                //     LEFT JOIN (
                //         SELECT 
                //             v1.*
                //         FROM ScrmPIVarietyList v1
                //         INNER JOIN (
                //             SELECT 
                //                 ShortName,
                //                 VarietyID AS MaxVarietyID,
                //                 PackingStyle,
                //                 Species,
                //                 TypeProduct,
                //                 FreezingType,
                //                 Treatment,
                //                 Grade,
                //                 Glaze,
                //                 PreparationType,
                //                 (CASE WHEN GlazePerc IS NULL OR GlazePerc = 0 THEN 0 ELSE GlazePerc END) AS NormalizedGlazePerc
                //             FROM ScrmPIVarietyList
                //             WHERE Status = 1
                //             GROUP BY ShortName, PackingStyle, Species, TypeProduct, FreezingType, Treatment, Grade,
                //                     Glaze, PreparationType,
                //                     (CASE WHEN GlazePerc IS NULL OR GlazePerc = 0 THEN 0 ELSE GlazePerc END)
                //         ) latest 
                //             ON v1.VarietyID = latest.MaxVarietyID
                //     ) VL ON VL.VarietyID = PGD.VarietyID
                //     LEFT JOIN ScrmBrandsMaster AS SBM 
                //         ON SBM.BrandID = VL.BrandName
                //     WHERE PGD.InvId = '$PoID'
                //     ";
                    

                //     $stmt1 = $this->conn->prepare($sqlQuery1);
                //     $stmt1->execute();
                //     $dataRow = $stmt1->fetch(PDO::FETCH_ASSOC);
                // }
                $VarietyName = $dataRow["ShortName"] . ' - ' . $dataRow["GradeName"] . ' - ' . $dataRow["PackingStyle"] . ' (' . $dataRow["Mcs"] . ' Mcs)';
            }

            if (isset($dataRow['PoGradeID']) && !empty($dataRow['PoGradeID'])) {
                $PoGradeID = $dataRow['PoGradeID'];
            } else {
                $PoGradeID = '';
            }

            $PackingStyle = $dataRow['PackingStyle'];
            $PackingStyleNew = str_replace(" ", "", $PackingStyle);
            $ArrayVarietyData['VarietyID'] = $VarietyID;
            $ArrayVarietyData['VarietyName'] = $VarietyName;
            $ArrayVarietyData['PoGradeID'] = $PoGradeID;
            $ArrayVarietyData['ShortName'] = $dataRow['ShortName'];
            $ArrayVarietyData['Grade'] = $dataRow['GradeName'];
            $ArrayVarietyData['FullName'] = $dataRow['FullName'];
            $ArrayVarietyData['DeviSUPC'] = $dataRow['DeviSUPC'];
            $ArrayVarietyData['TypeProduct'] = $dataRow['TypeProduct'];
            $ArrayVarietyData['PackingStyle'] = $PackingStyleNew;
            $ArrayVarietyData['Brand'] = $dataRow['Brand'];
            $ArrayVarietyData['Species'] = $dataRow['Species'];
            $ArrayVarietyData['TypeProduct'] = $dataRow['TypeProduct'];
            $ArrayVarietyData['PackingStyleID'] = $dataRow['PckID'];
            $ArrayVarietyData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayVarietyData['Soaking'] = $dataRow['Treatment'];

            $AryVarietyList[] = $ArrayVarietyData;
        }
        if ($PackingType == 'MASTER') {
            $ArrayGradeData['PackingStyle'] = 'Disabled';
        } else {
            $ArrayGradeData['PackingStyle'] = 'Enable';
        }

        if ($PoID == '') {
            $ArrayGradeData['DisablePackingType'] = 'Disabled';
        } else {
            $ArrayGradeData['DisablePackingType'] = 'Enable';
        }
        $Data['VarietyData'] = $AryVarietyList;
        $Data['ReadonlyDropdowns'] = $ArrayGradeData;
        return $Data;
    }

    function getAntiDumpingGrades($RequestBody)
    {
        $Variety = $RequestBody['VarietyID'];
        $BreakUpID = $RequestBody['PoGradeID'];
        $PackingType = $RequestBody['PackingType'];
        $dataRow = null;


        if ($BreakUpID != 0 && $BreakUpID != '') {
            $sqlQuery = "SELECT VarietyID FROM `ScrmPoGradeData` WHERE ID=$BreakUpID";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            $VarietyData = $stmt->fetch(PDO::FETCH_ASSOC);
            $Variety = $VarietyData['VarietyID'];
        }
        // $sqlQuery = "SELECT Grade as GradeName,
        //     FreezingType as FreezingTypeID,
        //     PackingStyle as PackingStyleID,
        //     VarietyID,
        //     GlazePerc,Treatment as SockingType,PackingStyle
        //     FROM `ScrmPIVarietyList` WHERE VarietyID='$Variety'";

        $sqlQuery = "SELECT Grade as GradeName,
            FreezingType as FreezingTypeID,
            GlazePerc, Treatment as SockingType, PackingStyle AS PackingStyleID
            FROM `ScrmPIVarietyList` WHERE VarietyID='$Variety'";


        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $ArrayGradeData['Grade'] = $dataRow['GradeName'];
        $ArrayGradeData['VarietyID'] = $Variety;
        $ArrayGradeData['PoGradeID'] = $BreakUpID;
        $ArrayGradeData['FreezingTypeID'] = $dataRow['FreezingTypeID'];
        $ArrayGradeData['PackingStyleID'] = $dataRow['PackingStyleID'];
        $ArrayGradeData['GlazePerc'] = $dataRow['GlazePerc'];
        $ArrayGradeData['SoakingType'] = $dataRow['SockingType'];
        if ($PackingType == 'MASTER') {
            $ArrayGradeData['PackingStyle'] = 'Disabled';
        } else {
            $ArrayGradeData['PackingStyle'] = 'Enable';
        }

        $Data['GradeData'] = $ArrayGradeData;
        return $Data;
    }

}
