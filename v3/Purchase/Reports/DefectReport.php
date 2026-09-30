<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
    $QueryParms = urlDecode64($QueryParms);
    $queries = array();
    parse_str($QueryParms, $_GET);
}

$Type = $_GET['Type'];

$Month = $_GET['Month'];
$Year = $_GET['Year'];
$PsID = $_GET['PsID'];

$StartDate = $Year . '-' . $Month . '-' . '01';
$EndDate = $Year . '-' . $Month . '-' . '31 23:59:59';


$Query = '';

if ($PsID != '') {
    $Query .= " and PurchaseStationID='$PsID'";
} else {
    $Query .= '';
}


if ($Type == 1) {
    $Query = "month(ArrivalDate)='$Month'  and year(ArrivalDate)='$Year'  $Query";
} else {
    $Query = "ArrivalDate between '$Month'  and '$Year'  $Query";
}

// echo $Query;
// exit;
global $Query;


$arrivalSqlQuery = "SELECT  AD.ArrivalNumber,AD.ArrivalDate,UL.UserName as AgentName,ABD.Variety, ABD.Count, ABD.Rate, HonQty.HonQty, ABD.Weight  FROM `RmArrivalDetatils` AS AD
                    LEFT JOIN `RmUsersList` AS UL ON UL.UserType='3' AND  UL.ID = AD.AgentID
                    LEFT JOIN `RmArrivalBreakUpDetails` AS ABD ON ABD.ArrivalID = AD.RmArrivalID
                    LEFT JOIN `RmLotDetatils` AS LD ON LD.RmReferenceID = AD.RmArrivalID
                    LEFT JOIN (SELECT ArrivalID, SUM(Weight) AS HonQty FROM `RmArrivalBreakUpDetails` GROUP BY ArrivalID ) HonQty ON ABD.ArrivalID = HonQty.ArrivalID
                    WHERE  $Query AND LOWER(ABD.Variety) != 'head on'";
$arrivalDataRow = $iconn->query($arrivalSqlQuery);
// echo $arrivalSqlQuery;
$table = getDefectTable($arrivalDataRow);
echo $table;
function getDefectTable($arrivalDataRow)
{
    $SNO = 1;
    $HeadFontSize = 4;
    $DataFontSize = 3;
    $content = "";
    $content .= '<table border="1" cellspacing="0" cellpadding="1"> 
        <thead>
            <tr> 
                    
            <th  align="CENTER" width="5%"><font size="' . $HeadFontSize . '"> <B>S No</B></font></th>
            <th  align="CENTER" width="5%"><font size="' . $HeadFontSize . '"> <B>Purchase Date</B></font></th>
            <th  align="CENTER" width="5%"><font size="' . $HeadFontSize . '"> <B>Lot Number</B></font></th>
            <th  align="CENTER" width="5%"><font size="' . $HeadFontSize . '"> <B>Hon Qnty</B></font></th>
            <th  align="CENTER" width="5%"><font size="' . $HeadFontSize . '"> <B>Agent Name</B></font></th>
            <th  align="CENTER" width="5%"><font size="' . $HeadFontSize . '"> <B>Defect Type</B></font></th>
            <th  align="CENTER" width="5%"><font size="' . $HeadFontSize . '"> <B>Defect Quantity</B></font></th>
            <th  align="CENTER" width="5%"><font size="' . $HeadFontSize . '"> <B>Defect Rate</B></font></th>
            <th  align="CENTER" width="5%"><font size="' . $HeadFontSize . '"> <B>Defect Deduction Total Amount</B></font></th>
            
            </tr>
        </thead>
        <tbody>';
    while ($RowHlReportsList = $arrivalDataRow->fetch_array()) {
        // echo $RowHlReportsList["RmArrivalID"];
        // exit;
        $Rate = $RowHlReportsList["Rate"] > 0 ? $RowHlReportsList["Rate"] : 0;
        $content .= '<tr> 
            <td  align="CENTER" width="5%"><font size="' . $DataFontSize . '">' . $SNO . '</font></td>
            <td align="CENTER" width="5%"><font size="' . $DataFontSize . '">' . $RowHlReportsList["ArrivalDate"] . '</font></td>
            <td  align="CENTER" width="5%"><font size="' . $DataFontSize . '">' . $RowHlReportsList["ArrivalNumber"] . '</font></td>
            <td  align="CENTER" width="5%"><font size="' . $DataFontSize . '">' . $RowHlReportsList["HonQty"] . '</font></td>
            <td  align="CENTER" width="5%"><font size="' . $DataFontSize . '">' . $RowHlReportsList["AgentName"] . '</font></td>
            <td  align="CENTER" width="5%"><font size="' . $DataFontSize . '">' . $RowHlReportsList["Variety"] . '</font></td>
            <td  align="CENTER" width="5%"><font size="' . $DataFontSize . '">' . $RowHlReportsList["Weight"] . '</font></td>
            <td  align="CENTER" width="5%"><font size="' . $DataFontSize . '">' . $Rate . '</font></td>
            <td  align="CENTER" width="5%"><font size="' . $DataFontSize . '">' . $RowHlReportsList["Weight"] * $Rate . '</font></td>
        </tr>';
        $SNO++;
    }
    $content .= '</tbody></table>';
    return $content;
}
