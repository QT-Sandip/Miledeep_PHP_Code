
 <?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBrandInCSReport = $ClientConfigData->ShowBrandInCSReport;
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;
$api = API_INWARD_REPORT;

$PageNo =  trim($_POST["pageNo"]);

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
     
    // echo print_r($_GET);

    $border = "border='1'";
    $bodyContent = array(
        "FromDate" => trim($_GET["fromDate"]),
        "ToDate" => trim($_GET["toDate"]),
        "ColdStore" => trim($_GET["coldStore"]),
        "Stockbelongsto" => trim($_GET["stockbelongsto"]),
        "PackingType" => trim($_GET["packingType"]),
        "Group" => trim($_GET["group"]),
        "PoNo" => trim($_GET["poNo"]),
        "Variety" => trim($_GET["variety"]),
        "Grade" => trim($_GET["grade"]),
        "Blocks" => trim($_GET["blocks"]),
        "PackingStyle" => trim($_GET["packingStyle"]),
        "PageNo" => '',
        "LoadAll" => 1,
        "SearchKey" => '',
        "TypeProduct" => trim($_GET["typeProduct"]),
        "Soaking" => trim($_GET["soaking"]),
        "PDCategory" => trim($_GET["pDCategory"]),
        "FreezingType" => trim($_GET["freezingType"]),
        "AbStatus" => trim($_GET["abstatus"]),
        "ShowBrandInCSReport" => $ShowBrandInCSReport,
         "ProductBatchCode" => $ProductBatchCode

        
    );
}

else {
    $border = "";
    $bodyContent = array(
        "FromDate" => trim($_POST["fromDate"]),
        "ToDate" => trim($_POST["toDate"]),
        "ColdStore" => trim($_POST["coldStore"]),
        "Stockbelongsto" => trim($_POST["stockbelongsto"]),
        "PackingType" => trim($_POST["packingType"]),
        "Group" => trim($_POST["group"]),
        "PoNo" => trim($_POST["poNo"]),
        "Variety" => trim($_POST["variety"]),
        "Grade" => trim($_POST["grade"]),
        "Blocks" => trim($_POST["blocks"]),
        "PackingStyle" => trim($_POST["packingStyle"]),
        "PageNo" => trim($_POST["pageNo"]),
        "LoadAll" => trim($_POST["loadAll"]),
        "SearchKey" => trim($_POST["searchKey"]),
        "TypeProduct" => trim($_POST["typeProduct"]),
        "Soaking" => trim($_POST["soaking"]),
        "PDCategory" => trim($_POST["pDCategory"]),
        "FreezingType" => trim($_POST["freezingType"]),
        "AbStatus" => trim($_POST["abstatus"]),
        "ShowBrandInCSReport" => $ShowBrandInCSReport,
          "ProductBatchCode" => $ProductBatchCode
    );
}


$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
        $Totals = $resList->Totals;

        $Count = count($List);

        $content = '
        <div class="card">
            <div class="card-body">  
              <table id="tblDataTable"' . $border . ' class="table table-hover table-bordered">
            <thead class="thead-light">
      
                        <tr>
                            <th>S.NO</th>
                            <th>StockID</th>
                            <th style="min-width:80px;">Production Date</th>
                            <th style="min-width:80px;">Transaction Date</th>
                            <th style="min-width:80px;">Arrival Date</th>
                            <th style="min-width:80px;">Track Code</th>';
                            if($ShowBatchCode == 1){
                                $content .= '<th style="min-width:80px;">Batch Code</th>';
                            }
                            if($ProductBatchCode == 1){
                                   $content .= '<th style="min-width: 160px;">Arrival Code</th>';
                            }
                            $content .= '<th style="min-width:80px;">Station </th>
                            <th style="min-width:100px;">Po No </th>
                            <!-- <th>Bar Code </th> -->
                            <th style="min-width:100px;">Shift </th>
                            <th>Variety </th>
                            <th>Product Type </th>';
                            if($ShowBrandInCSReport == 1){
                                 $content .= '<th style="min-width: 160px;">Brand</th>';
                             }
                            $content .= '<th>Freezing </th>
                            <th>Soaking </th>
                            <th style="min-width:100px;">Group </th>
                            <th>Grade </th>
                            <th style="min-width:100px;">Packing Style </th>
                            <th>Category </th>
                            <th style="min-width:100px;">Store </th>
                            <th>Ab Status </th>
                            <th>Location </th>
                            <th>Mcs </th>
                            <th>Loose Bags </th>
                            <th>Slabs </th>
                            <th>Kgs</th>
                            <th>Remarks </th>
                            <th style="min-width:180px;">Posted Date</th>
                        </tr>
                    </thead>
                    <tbody>';
                       
                    
            $TotalMcs = 0;
            $TotalLooseBags = 0;
            $TotalKGs = 0;
            $TotalSlabs = 0;
            foreach ($List as $item) {
                $TotalMcs += $item->Mcs;
            $TotalLooseBags +=$item->LooseBags;
            $TotalKGs += $item->Kgs;
            $TotalSlabs += $item->McsSlabs;
            

            // var_dump($List);
            // exit;
             $content .= '<tr>           
                                <td>'. $item->SNo.'</td>
                                <td>'.$item->StockID.'</td>
                                <td style="min-width:120px;">'.date('d-m-Y', strtotime($item->ProductionDate)).'</td>
                                <td style="min-width:120px;">'.$item->Date.'</td>
                                <td style="min-width:120px;">'. $item->ArrivalDate.'</td> 
                                <td style="min-width:120px;">'. $item->TrackCode.'</td>';
                                if($ShowBatchCode == 1){
                                    $content .= '<td style="min-width:120px;">'. $item->LotNumber.'</td>';
                                }
                                if($ProductBatchCode == 1){
                                    $content .= '<td>'.$item->ProdBatchCode.'</td>';
                                }
                                $content .= '<td>'.$item->Station.'</td>
                                <td style="min-width:100px;">'.$item->PoNo.'</td>
                                <!-- <td><?php echo $item->BarCode ?></td> -->
                                <td style="min-width:100px;">'.$item->Shift.'</td>
                                <td>'.$item->Variety.'</td>
                                <td>'. $item->TypeProduct.'</td>';
                                if($ShowBrandInCSReport == 1){
                                    $content .= '<td>'.$item->BrandName.'</td>';
                                }
                                $content .= '<td>'.$item->Freezing.'</td>
                                <td>'.$item->Soaking.'</td>
                                <td style="min-width:100px;">'. $item->Group.'</td>
                                <td>'.$item->Grade.'</td>
                                <td style="min-width:100px;">'.$item->PackingStyle.'</td>
                                <td>'. $item->Category.'</td>
                                <td>'.$item->Store.'</td>
                                <td>'.$item->AbStatus.'</td>
                                <td>'. $item->Location.'</td>
                                <!-- <td><?php echo $item->Mcs ?></td>
                                <td>'.$item->LooseBags.'</td>
                                <td> $item->Kgs ?></td> -->
                                <td class="mcs">'. (int)$item->Mcs.'</td>
                                <td class="loose-bags">'.$item->LooseBags.'</td>
                                <td class="slabs">'.$item->McsSlabs.'</td>
                                <td class="kgs">'.$item->Kgs.'</td>
                                <td>'.$item->Remarks.'</td>
                                <td>'. $item->PostedDate.'</td>
                            </tr>';
                        }

                    
                        $content .= ' </tbody>
                    <tfoot>
                        <tr>

                        <td></td>
                                <td>Totals</td>
                                <td style="min-width:120px;"></td>
                                <td style="min-width:120px;"></td>
                                <td style="min-width:120px;"></td>
                                <td style="min-width:120px;"></td>

                                <td></td>
                                <td style="min-width:100px;"></td>
                                <td style="min-width:100px;"></td>
                                <td></td>
                                <td></td>';
                                if($ShowBrandInCSReport == 1){
                                    $content .= '<td></td>';
                                }
                                 if($ProductBatchCode == 1){
                                    $content .= '<td></td>';
                                }
                                $content .= '
                                <td></td>
                                <td></td>
                                <td style="min-width:100px;"></td>
                                <td></td>
                                <td style="min-width:100px;"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td id="TotalMcs">'.$TotalMcs.'</td>
                                <td id="TotalLooseBags">'.$TotalLooseBags.'</td>
                                <td id="TotalLooseBags">'.$TotalSlabs.'</td>
                                <td id="TotalKGs">'.$TotalKGs.'</td>
                                <td></td>
                                <td></td>                     
            </tr>';
    }
    $content .= '  
            </tfoot>
        </table> 

        ';
}


if (!isset($_GET["excelDownload"]) || $_GET["excelDownload"] != "T") {
    $content .= '<div class="mt-3 mb-3">Showing 1 to ' . count($List) . ' out of ' . $resTotal . ' entries</div>';
    $content .= '<input type="hidden" id="hdnTotalRecords" value="' . $resTotal . '" />';
    /*$content .= '<div>';
    $content .= '<ul class="pagination pagination-rounded justify-content-center">';

    for ($i = 1; $i <= $PageCount; $i++) {
        $pageActive = ($PageNo == $i ? "active" : "");
        $content .= '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
    }

    $content .= '</ul></div>';*/
    $pagination_content = "";
    ob_start();
    ?>
    <div>
    <ul class="pagination pagination-rounded justify-content-center">
        <?php
        $start = max(1, $PageNo - 2);
        $end   = min($PageCount, $PageNo + 2);

        // PREVIOUS arrow
        if ($PageNo > 1) {
            echo '<li class="page-item">
                    <button class="page-link btnPageno" data-pageno="'.($PageNo - 1).'">&laquo;</button>
                  </li>';
        }

        // Show first 2 pages
        for ($i = 1; $i <= min(2, $PageCount); $i++) {
            $active = ($PageNo == $i) ? 'active' : '';
            echo '<li class="page-item '.$active.'">
                    <button class="page-link btnPageno" data-pageno="'.$i.'">'.$i.'</button>
                  </li>';
        }

        // Dots before middle
        if ($start > 3) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        // Middle pages (current ±2)
        for ($i = $start; $i <= $end; $i++) {
            if ($i > 2 && $i < ($PageCount - 1)) {
                $active = ($PageNo == $i) ? 'active' : '';
                echo '<li class="page-item '.$active.'">
                        <button class="page-link btnPageno" data-pageno="'.$i.'">'.$i.'</button>
                      </li>';
            }
        }

        // Dots after middle
        if ($end < $PageCount - 2) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        // Last 2 pages
        for ($i = max($PageCount - 1, 3); $i <= $PageCount; $i++) {
            $active = ($PageNo == $i) ? 'active' : '';
            echo '<li class="page-item '.$active.'">
                    <button class="page-link btnPageno" data-pageno="'.$i.'">'.$i.'</button>
                  </li>';
        }

        // NEXT arrow
        if ($PageNo < $PageCount) {
            echo '<li class="page-item">
                    <button class="page-link btnPageno" data-pageno="'.($PageNo + 1).'">&raquo;</button>
                  </li>';
        }
        ?>
    </ul>
</div>
    <?php
    $pagination_content = ob_get_clean();
    $content .=$pagination_content;
} else {
    $content .= "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
}


  
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    
    $filename = 'inward_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}
     echo $content;
   
