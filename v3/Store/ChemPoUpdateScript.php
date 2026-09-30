<?php
$PageTitle = "Update Existing Chem PO";
$PageTitleFolder = "Store";

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

updateChemPo();
exit;

function updateChemPo()
{
    global $iconn;

    $sqlSuppliers = "SELECT ChemSupplierID, SupplierCode FROM GsChemSuppliersData WHERE Status = 1";
    $stmtSuppliers = $iconn->query($sqlSuppliers);

    if (!$stmtSuppliers) {
        die("Supplier query failed: " . $iconn->error);
    }

    while ($supplier = $stmtSuppliers->fetch_assoc()) {
        $SupplierID = $supplier['ChemSupplierID'];
        $SupplierCode = $supplier['SupplierCode'];

        echo "<h4>Supplier: $SupplierCode ($SupplierID)</h4>";

        // Fetch all Chemical POs for this supplier ordered by PostedDate
        $sqlPos = "SELECT * FROM GsChemicalPos 
               WHERE SupplierPartyID = ?
               ORDER BY PostedDateTime ASC";

        $stmtPos = $iconn->prepare($sqlPos);
        $stmtPos->bind_param("i", $SupplierID);
        $stmtPos->execute();
        $resultPos = $stmtPos->get_result();

        if ($resultPos->num_rows > 0) {
            $i = 1;
            while ($row = $resultPos->fetch_assoc()) {
                $ID = $row['ChemPoID'];
                $financialYear = getFinancialYearFromPostedDate($row['PostedDateTime']);
                $formattedNumber = str_pad($i++, 3, '0', STR_PAD_LEFT);
                $NewPoNo = "DFL/$SupplierCode/$financialYear/$formattedNumber";
                echo "<h4>ID: $ID ==> Chemical PO changed from " . $row['ChemPoNo'] . " to $NewPoNo since Posted Time: "
                    . $row['PostedDateTime'] . "<br>";

                $updateSql = "UPDATE GsChemicalPos SET ChemPoNo = ?, FinancialYear = ? WHERE ChemPoID = ?";
                $stmt = $iconn->prepare($updateSql);
                $stmt->bind_param("ssi", $NewPoNo, $financialYear, $ID);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    echo "<h4>Record $ID updated successfully!</h4>";
                }
                $stmt->close();
            }
        } else {
            echo "No POs found for this supplier.<br>";
        }
        $stmtPos->close();
    }
}

function getFinancialYearFromPostedDate($dateString)
{
    $timestamp = strtotime($dateString);

    if ($timestamp === false) {
        return null;
    }

    $year = date('Y', $timestamp);
    $month = date('n', $timestamp);

    if ($month >= 4) {
        $startYear = $year;
        $endYear = $year + 1;
    } else {
        $startYear = $year - 1;
        $endYear = $year;
    }

    return substr($startYear, 2, 2) . substr($endYear, 2, 2);
}
