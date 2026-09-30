<?php

include_once '../Global/Util.php';
include_once '../App/Controller/AppUtilController.php';

class DropdownController
{

    private $conn;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getDropdowns($RequestBody)
    {
        $exclude_list_array = $RequestBody['exclude_list'] ? $RequestBody['exclude_list'] : [];
        $Data['GeneralItems'] = in_array("GeneralItems", $exclude_list_array) ? [] : $this->getGeneralItems();
        $Data['Vendors'] = in_array("Vendors", $exclude_list_array) ? [] : $this->getVendors();
        $Data['Brands'] = in_array("Brands", $exclude_list_array) ? [] : $this->getBrands();
        $Data['Departments'] = in_array("Departments", $exclude_list_array) ? [] : $this->getDepartments();
        $Data['Variety'] = in_array("Variety", $exclude_list_array) ? [] : $this->getVariety();
        $Data['PackingStyle'] = in_array("PackingStyle", $exclude_list_array) ? [] : $this->getPackingStyle();
        $Data['FreezingType'] = in_array("FreezingType", $exclude_list_array) ? [] : $this->getFreezingType();
        $Data['Grade'] = in_array("Grade", $exclude_list_array) ? [] : $this->getGrade();
        $Data['Brand'] = in_array("Brand", $exclude_list_array) ? [] : $this->getBrand();
        $Data['LastFiveMonthsData'] = in_array("LastFiveMonthsData", $exclude_list_array) ? [] : $this->getLastFiveMonthsData();
        return $Data;
    }


    function getGeneralItems()
    {
        $sqlQuery = "SELECT GeneralItemId,`name`,code  FROM `GeneralItems` ORDER BY GeneralItemId ASC ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getVendors()
    {
        $sqlQuery = "SELECT VendorID ,VendorName,VendorCode FROM `Vendors` ORDER BY VendorID ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getBrands()
    {
        $sqlQuery = "SELECT BrandID,`BrandName`,BrandCode  FROM `BrandMaster` ORDER BY BrandID ASC ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getDepartments()
    {
        $sqlQuery = "SELECT DepartmentID,
                    CASE WHEN ISD.SubDepartmentName != '' THEN ISD.SubDepartmentName ELSE DPT.DepartmentName END AS `DepartmentName`,
                    ISD.Value,
                    DepartmentCode  
                    FROM `Departments` DPT
                    LEFT JOIN InventorySubDepartments ISD ON ISD.Value = DPT.DepartmentName
                    WHERE DPT.Status = 'Active' ORDER BY DepartmentID ASC ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getVariety()
    {
        $sqlQuery = "SELECT VarietyID,ShortName,ProductDescription,Grade,FullName,DeviSUPC,TypeProduct FROM ScrmPIVarietyList WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getFreezingType()
    {
        $sqlQuery = "SELECT ID, FreezingTypeName, ShortCode FROM ScrmFreezingTypeMaster WHERE Status=1 ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getGrade()
    {
        $sqlQuery = "SELECT ID,value,Options FROM ProductionGrades ORDER BY ABS(value) asc";
        // $sqlQuery = "SELECT ID,value,Options FROM ProductionGrades ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPackingStyle()
    {
        $sqlQuery = "SELECT PCKID, PackingStyle FROM AdminPackingStyles WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getBrand()
    {
        $sqlQuery = "SELECT BrandID, BrandName FROM ScrmBrandsMaster WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getLastFiveMonthsData()
    {
        $CurrentDate = date('Y-m-d', strtotime('-5 months'));
        $sqlQuery = "SELECT 
    IDB.GeneralItemId,
    ID.Date,
    IDB.Quantity,
    VD.VendorName,
    SUM(GRNB.ReceivedQuantity) AS RQty,SUM(GPOB.Quantity) AS PoQty
FROM IndentDetailsBreakUpGs AS IDB

LEFT JOIN IndentDetailsGs AS ID 
    ON IDB.IndentId = ID.Id

LEFT JOIN (
    SELECT DocNo, MIN(Id) AS GPID, Supplier,GROUP_CONCAT(Id) AS GPIDs
    FROM GsProformaOrder
    GROUP BY DocNo
) AS GP1 ON GP1.DocNo = ID.Id

LEFT JOIN GsProformaOrder AS GP 
    ON GP.Id = GP1.GPID

    LEFT JOIN GsProformaOrderBreakup AS GPOB 
    ON GPOB.GeneralItemId = IDB.GeneralItemId
    AND FIND_IN_SET(GPOB.PoId, GP1.GPIDs)

LEFT JOIN (
    SELECT PoID, GROUP_CONCAT(Id) AS GRIDs, MIN(Id) AS GRID
    FROM GoodsReceiptNote
    GROUP BY PoID
) AS GRN1 ON GRN1.PoID = GP.Id

LEFT JOIN GoodsReceiptNote AS GRN 
    ON GRN.Id = GRN1.GRID

LEFT JOIN GoodsReceiptNoteBreakup AS GRNB 
    ON GRNB.GeneralItemId = IDB.GeneralItemId
    AND FIND_IN_SET(GRNB.NoteId, GRN1.GRIDs)

LEFT JOIN Vendors AS VD 
    ON GP1.Supplier = VD.VendorID

WHERE 
    ID.Date >= '$CurrentDate'
    AND GP1.GPID IS NOT NULL

GROUP BY 
    IDB.GeneralItemId,
    ID.Date,
    IDB.Quantity,
    VD.VendorName;
";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $final = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $key = $row['GeneralItemId'];  // group key

            // convert date to required format (m/d/Y)
            $formattedDate = date("m-d-Y", strtotime($row['Date']));

            $final[$key][] = [
                "date" => $formattedDate,
                "qty" => (int) $row['Quantity'],
                "VendorName" => $row['VendorName'],
                "poqty" => (int) $row['PoQty'],
                "reqty" => $row['RQty']
                // "price" => number_format((float)$row['Price'], 2, '.', '')
            ];
        }

        return $final;
    }

}
