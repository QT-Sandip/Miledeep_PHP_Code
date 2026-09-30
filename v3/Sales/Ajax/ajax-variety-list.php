<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_VARIETY_LIST;

$BrandList = trim($_POST["BrandList"]);
$Species = trim($_POST["Species"]);
$TypeProduct = trim($_POST["TypeProduct"]);
$ShortName = trim($_POST["ShortName"]);
$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
        'BrandList'=>$BrandList,
        'Species'=>$Species,
        'TypeProduct'=>$TypeProduct,
        'ShortName'=>$ShortName,
        'Grade'=> $_POST["Grade"],
        'FreezingType'=> $_POST["FreezingType"],
        'SearchKey'=> $SearchKey,
        'PageNo'=> $PageNo,
        'LoadAll'=> $LoadAll);


$resVarietyList = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resVarietyList); 
// exit; 


if (isset($resVarietyList) && !empty($resVarietyList)) {

    $error = $resVarietyList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resVarietyList->PageCount;
        $resList = $resVarietyList->Data;
        $resTotal = $resVarietyList->TotalCount;
        // var_dump($resList[0]->ID);
?>
                                            
    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.NO</th>	
                <th>Short Name</th>	
                <th>Full Name</th>
                <th>Grade</th>	
                <th>Freezing Type</th>	
                <th>Treatment</th>
                <th>Preparation</th>
                <th>Brand Name</th>
                <th>Packing Style</th>	
                <th>Product Description</th>	
                <th>Type</th>	
                <th>Buyer SUPC</th>	
                <th>Our SUPC</th>	
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resList as $item) { ?>
            <tr>
                <td><?php echo $item->SNO?></td>	
                <td><?php echo $item->ShortName?></td>	
                <td><?php echo $item->FullName?></td>
                <td><?php echo $item->Grade?></td>	
                <td><?php echo $item->FreezingType?></td>	
                <td><?php echo $item->TreatmentName?></td>
                <td><?php echo $item->PreparationType?></td>
                <td><?php echo $item->BRND?></td>
                <td><?php echo $item->PCKSTYL?></td>	
                <td><?php echo $item->ProductDescription?></td>	
                <td><?php echo $item->TypeProduct?></td>	
                <td><?php echo $item->BuyerSUPC?></td>	
                <td><?php echo $item->DeviSUPC?></td>	
                <td class="text-center">
                    <?php  if ($resVarietyList->EditAccess) { ?>
                        <a  href='<?php echo $resVarietyList->EditLink. $item->VarietyID ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit VarietyList" data-original-title="Edit">
                            <i class="mdi mdi-pencil font-size-16"></i>
                        </a>
                    <?php } ?> 
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="mt-3 mb-3">Showing 1 to <?php echo count($resList) ?> out of <?php echo $resTotal ?> entries</div>
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
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : ". $error."</div>";
    } 
}
?>
