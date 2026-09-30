<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$PageTitle = "View Stock";
$ClientConfigData = $_SESSION['ClientConfigData'];
$dashboard_show_totalkgs = !empty($ClientConfigData['dashboard_show_totalkgs']) ? $ClientConfigData['dashboard_show_totalkgs'] : 0;
$StoreRackMaxMcs = $ClientConfigData['StoreRackMaxMcs'];//anil
$FlooreRackIds = !empty($ClientConfigData['FlooreRackIds']) ? explode(',',$ClientConfigData['FlooreRackIds']):[]; //anil;
$PackagingStoreIds = !empty($ClientConfigData['PackagingStoreIds']) ? trim($ClientConfigData['PackagingStoreIds']):[]; //anil
$IsMultipleStockAssignToRack = !empty($ClientConfigData['IsMultipleStockAssignToRack']) ? $ClientConfigData['IsMultipleStockAssignToRack'] : 0; //anil;
$EnableCodeListPrint = !empty($ClientConfigData['EnableCodeListPrint']) ? $ClientConfigData['EnableCodeListPrint'] : 0;

$excludeList = array("Floors","StoreRacks","Stock","ProductGroup","Exporters","Consignee","Processor","Shifts","Soaking","Abstatus","OptType","OutType","Variety","StockAvailableVariety","StockInVarietyList","Plant","GeneralCategories","Status","TenantType","ProductionLines","BrandType","Tranction","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$filterColdStores = "T";
$filterBlocksTwo = "T";
$filterStockBlgsTo = "T";
$filterVarietyList = "T";
$filterGrade = "T";
$filterSpecies = "T";
$filterPckgStyle = "T";
$filterStockType = "T";
if(empty($IsMultipleStockAssignToRack)){
    $buttomButton = "T";
}else{
    $shimentOutButton = "T";
}
$filterPoNumber = "T";
$filterSpecies = "T";
$filterPackingType = "T";
$filterFreezingType = "T";
$filterTypeProduct = "T";
$filterInwardGroup = "T";
$displayBrandColumn = ($_SESSION['ClientID'] == 'DFLV3') ? 1 : 0;
if($displayBrandColumn){
    $filterBrand = "T";
}

$api = API_STORE_RACK_DETAILS;

$bodyContent = array(
    "BlockID" => ""
);

$resRackDetails = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resRackDetails, JSON_PRETTY_PRINT);
// exit;
$resStoresData = $resRackDetails->StoresData;
$resBlockData = $resRackDetails->BlockData;


$apiDrp = API_COLDSTORE_DROPDOWNS;
$bodyContent['exclude_list'] = $excludeList ? $excludeList : [];
$resDrpList = CallAPI($apiDrp, "GET", "", $bodyContent);
$resColdStores = $resList->Data->ColdStores;
$resBlocks = $resList->Data->Blocks;
$resFloors = $resList->Data->Floors;

$ProductionPlant = $_SESSION['UnitID'];
// echo $ProductionPlant;
// exit;

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <link href="/v3/assets/css/purchase-coldstore-theme.css" rel="stylesheet" type="text/css" />
    <style>
        div.dataTables_wrapper div.dataTables_filter input, .dt-search-input {
            width: 100% !important;
        }
        /* Tailwind button disabled styling */
#btnDownload:disabled {
    opacity: 0.5;              /* faded look */
    cursor: not-allowed;       /* show it’s disabled */
}

/* Optional: remove hover effect when disabled */
/*#btnDownload:disabled:hover {
    background-color: #4ade80; /* prevent hover change */
}*/

/*        .btn-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
    background-color: #6c757d; /* gray */
    color: #fff;
}*/

        /* Enable scrolling */
#scroll-vertical-datatable_wrapper .dataTables_scrollBody {
    overflow: auto !important;
}

/* 3rd column */
#scroll-vertical-datatable th:nth-child(3) {
    position: sticky;
    left: 0;                     /* adjust if needed */
    background: #fff;
    z-index: 3;
}


#scroll-vertical-datatable td:nth-child(3) {
    position: sticky;
    left: 0;                     /* adjust if needed */
    background: #fff;
    z-index: 3;
}

/* Last column */
#scroll-vertical-datatable th:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 3;
}

#scroll-vertical-datatable td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 3;
}

#scroll-vertical-datatable_wrapper
.dataTables_scrollHead th:nth-child(3) {
    position: sticky;
    left: 0;
    background: #f8f9fa;
    z-index: 5;
}

#scroll-vertical-datatable_wrapper
.dataTables_scrollHead th:last-child {
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 5;
}


/* Search wrapper */
.dt-search-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
}

/* Search input */
.dt-search-input {
    width: 320px;              /* increase width */
    height: 44px;              /* increase height */
    padding: 10px 14px 10px 40px;
    font-size: 14px;

    border: 1.5px solid #d0d5dd;
    border-radius: 8px;

    transition: all 0.25s ease;
}

/* Highlight on focus */
.dt-search-input:focus {
    outline: none;
    border-color: #4f46e58f;     /* highlight color */
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    background-color: #fff;
}

/* Search icon */
.dt-search-wrapper .ri-search-line {
    position: absolute;
    left: 14px;
    font-size: 22px;
    color: #9ca3af;
    pointer-events: none;
    padding-left:5px ;
}

/* Hover effect */
.dt-search-input:hover {
    border-color: #6366f1;
}




        .summary-panel-modern{
            display:flex;flex-wrap:wrap;gap:20px;
            font-family:'Segoe UI',Tahoma,sans-serif;justify-content:center;
            margin-bottom: 1.5rem;
        }

        .summary-card-modern{
            flex:1 1 180px;
            /*padding:18px 22px;*/
            display:flex;align-items:center;gap:14px;
            transition:.25s ease;position:relative;overflow:hidden
            --tw-shadow: 0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1));
            box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
        }
        .summary-icon{
            width:48px;height:48px;background:#eef2ff;
            border-radius:10px;display:flex;align-items:center;justify-content:center
        }
        .summary-card-modern .rounded-lg{
            width: 100%;
        }
        .p-5 {
            padding: calc(var(--spacing) * 5)!important;
        }
        .p-3 {
            padding: calc(var(--spacing) * 3)!important;
        }
        .text-xs,.text-3xl{ margin-bottom:0px;font-weight:400; }
      .border-1 {
        border-style: var(--tw-border-style);
        border-width: 1px;
      }
      table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td{
        color: oklch(37.3% 0.034 259.733);
      }

.dt-filter-wrapper {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    justify-content: end;
}

/* Toolbar */
.dt-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    /*margin-left: auto;*/
}

/* Search */
.dt-search-wrapper {
    position: relative;
    margin-top: 10px;
}

/*#scroll-vertical-datatable_length{
    margin-top: 20px;
}*/
#scroll-vertical-datatable_length label{margin-bottom: 0px!important;}
.icon-search {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 14px;
}

.dt-search-input {
    padding: 8px 12px 8px 40px;
    width: 220px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    font-size: 14px;
}

.dt-search-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

/* Date */
.dt-date {
    padding: 7px 10px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    font-size: 13px;
}

/* Buttons */
.dt-action-group {
    display: flex;
    gap: 8px;
}

.dt-btn {
    padding: 7px 14px;
    border-radius: 8px;
    border: none;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.dt-btn-primary {
    background: linear-gradient(90deg, #3b82f6, #6366f1);
    color: #fff;
}

.dt-btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.dt-btn:hover {
    opacity: 0.9;
}
.ri-search-line{font-size: 16px;position: absolute;left: 22px;top: 7px;color: var(--bs-secondary-color);}
.dt-search-input{ width: 500px!important; border-radius: 30px; height:45px   }
#fromDate,#toDate{background: #fff;border-radius: 8px;min-height: 39px;color: var(--bs-secondary-color);}
.download_btn {
  padding: 5px 10px;
  font-size: 16px;
  background-color: white;
  color: #217346;
  border: 2px solid #217346;
  border-radius: 5px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  margin-bottom: 20px;
}
#btnDownload{background: #fff;border-radius: 8px;color: var(--color-green-700) !important;border: 1px solid var(--color-green-700) !important;}
#scroll-vertical-datatable_filter{margin-left: 20px;}
#scroll-vertical-datatable td:last-child{text-align: center;}
.dt-search-input {
    /*border-color: #4f46e5;     */
    border-color: #4f46e58f;/* highlight color */
}
#stock-status p{line-height: 1.3!important;}
.dt-buttons{display:inline-flex;gap:0;margin-left:10px;vertical-align:middle;}
.dt-buttons .buttons-excel{padding:3px 5px;margin-left:10px;font-size:13px;border-radius:4px;background:#198754;color:#fff;border:none;cursor:pointer; margin-top:4px }
.dt-buttons .buttons-excel:hover{opacity:0.85;}
    </style>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . '/includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . '/includes/page-filter.php';
                include "PopUpTable.php";

                ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                      <div class="summary-panel-modern">
                         <div class="summary-card-modern capacity-card" onclick="graphCapacity([&quot;200&quot;,&quot;35&quot;,165])">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md p-5 border-1 border-blue-200" data-fg-rvh160="28.21:28.18466:/components/ViewStock.tsx:331:9:10272:627:e:div">
                               <div class="flex items-center justify-between" data-fg-rvh161="28.21:28.18466:/components/ViewStock.tsx:332:11:10391:493:e:div">
                                  <div data-fg-rvh162="28.21:28.18466:/components/ViewStock.tsx:333:13:10455:274:e:div">
                                     <p class="text-xs text-blue-700 uppercase tracking-wide" data-fg-rvh163="28.21:28.18466:/components/ViewStock.tsx:334:15:10475:78:e:p">Total Records</p>
                                     <p class="text-3xl mt-2 text-blue-900" data-fg-rvh165="28.21:28.18466:/components/ViewStock.tsx:335:15:10568:61:e:p" id="totalActiveStocks">6</p>
                                     <p class="text-xs text-blue-600 mt-1" data-fg-rvh167="28.21:28.18466:/components/ViewStock.tsx:336:15:10644:66:e:p">Active stock entries</p>
                                  </div>
                                  <div class="bg-blue-200 p-3 rounded-full" data-fg-rvh169="28.21:28.18466:/components/ViewStock.tsx:338:13:10742:125:e:div">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-7 h-7 text-blue-700" data-fg-rvh170="28.21:28.18466:/components/ViewStock.tsx:339:15:10803:45:e:Package::::::reo">
                                        <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                        <path d="M12 22V12"></path>
                                        <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                        <path d="m7.5 4.27 9 5.15"></path>
                                     </svg>
                                  </div>
                               </div>
                            </div>
                         </div>
                         <div class="summary-card-modern" style="display:<?php echo $dashboard_show_totalkgs?'block':'none';?>">
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md p-5 border-1 border-green-200" data-fg-rvh171="28.21:28.18466:/components/ViewStock.tsx:344:9:10909:629:e:div">
                               <div class="flex items-center justify-between" data-fg-rvh172="28.21:28.18466:/components/ViewStock.tsx:345:11:11031:492:e:div">
                                  <div data-fg-rvh173="28.21:28.18466:/components/ViewStock.tsx:346:13:11095:268:e:div">
                                     <p class="text-xs text-green-700 uppercase tracking-wide" data-fg-rvh174="28.21:28.18466:/components/ViewStock.tsx:347:15:11115:75:e:p">Total</p>
                                     <p class="text-3xl mt-2 text-green-900" data-fg-rvh176="28.21:28.18466:/components/ViewStock.tsx:348:15:11205:58:e:p" id="totalMcs">72</p>
                                     <p class="text-xs text-green-600 mt-1" data-fg-rvh178="28.21:28.18466:/components/ViewStock.tsx:349:15:11278:66:e:p">stock</p>
                                  </div>
                                  <div class="bg-green-200 p-3 rounded-full" data-fg-rvh180="28.21:28.18466:/components/ViewStock.tsx:351:13:11376:130:e:div">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up w-7 h-7 text-green-700" data-fg-rvh181="28.21:28.18466:/components/ViewStock.tsx:352:15:11438:49:e:TrendingUp::::::D6b0">
                                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                        <polyline points="16 7 22 7 22 13"></polyline>
                                     </svg>
                                  </div>
                               </div>
                            </div>
                         </div>
                         <div class="summary-card-modern capacity-card" style="display:<?php echo $dashboard_show_totalkgs?'block':'none';?>">
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-md p-5 border-1 border-orange-200" data-fg-rvh182="28.21:28.18466:/components/ViewStock.tsx:357:9:11548:630:e:div">
                               <div class="flex items-center justify-between" data-fg-rvh183="28.21:28.18466:/components/ViewStock.tsx:358:11:11673:490:e:div">
                                  <div data-fg-rvh184="28.21:28.18466:/components/ViewStock.tsx:359:13:11737:267:e:div">
                                     <p class="text-xs text-orange-700 uppercase tracking-wide" data-fg-rvh185="28.21:28.18466:/components/ViewStock.tsx:360:15:11757:78:e:p">Total Loose</p>
                                     <p class="text-3xl mt-2 text-orange-900" data-fg-rvh187="28.21:28.18466:/components/ViewStock.tsx:361:15:11850:61:e:p" id="totalLooseBags">3</p>
                                     <p class="text-xs text-orange-600 mt-1" data-fg-rvh189="28.21:28.18466:/components/ViewStock.tsx:362:15:11926:59:e:p">Loose items</p>
                                  </div>
                                  <div class="bg-orange-200 p-3 rounded-full" data-fg-rvh191="28.21:28.18466:/components/ViewStock.tsx:364:13:12017:129:e:div">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-7 h-7 text-orange-700" data-fg-rvh192="28.21:28.18466:/components/ViewStock.tsx:365:15:12080:47:e:Package::::::reo">
                                        <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                        <path d="M12 22V12"></path>
                                        <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                        <path d="m7.5 4.27 9 5.15"></path>
                                     </svg>
                                  </div>
                               </div>
                            </div>
                         </div>
                         <div class="summary-card-modern" id="stock-status" onclick="graphVarietyKgs([&quot;PDTO&quot;,&quot;EZPL&quot;,&quot;PD&quot;,&quot;HLSO&quot;],[2924,2202,1851,22])">
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-md p-5 border-1 border-purple-200" data-fg-rvh193="28.21:28.18466:/components/ViewStock.tsx:370:9:12188:639:e:div">
                               <div class="flex items-center justify-between" data-fg-rvh194="28.21:28.18466:/components/ViewStock.tsx:371:11:12313:499:e:div" style="height: 80px;">
                                  <div data-fg-rvh195="28.21:28.18466:/components/ViewStock.tsx:372:13:12377:275:e:div">
                                     <p class="text-xs text-purple-700 uppercase tracking-wide" data-fg-rvh196="28.21:28.18466:/components/ViewStock.tsx:373:15:12397:81:e:p">Stock Status</p>
                                     <p class="text-3xl mt-2 text-purple-900" data-fg-rvh198="28.21:28.18466:/components/ViewStock.tsx:374:15:12493:64:e:p" id="totalFloorStocks" style="font-size: 18px;"></p>
                                     <p class="text-3xl mt-2 text-purple-900" data-fg-rvh198="28.21:28.18466:/components/ViewStock.tsx:374:15:12493:64:e:p" id="totalStoreStocks" style="font-size: 18px;"></p>
                                  </div>
                                  <div class="bg-purple-200 p-3 rounded-full" data-fg-rvh202="28.21:28.18466:/components/ViewStock.tsx:377:13:12665:130:e:div">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar w-7 h-7 text-purple-700" data-fg-rvh203="28.21:28.18466:/components/ViewStock.tsx:378:15:12728:48:e:Calendar::::::C7bG">
                                        <path d="M8 2v4"></path>
                                        <path d="M16 2v4"></path>
                                        <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                        <path d="M3 10h18"></path>
                                     </svg>
                                  </div>
                               </div>
                            </div>
                         </div>
                      </div>
                   </div>
                </div>
                    <div class="col-12">
                        <input type="hidden" id="hdnBlocks" value="<?php echo htmlspecialchars(json_encode($resBlockData), ENT_QUOTES, 'UTF-8'); ?>" />
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                </div>
                                <input type="hidden" id="hdnActionType" />
                            </div>

                        </div>

                    </div>
                </div>






            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    <!-- end main content-->
    <!-- Modal -->

    <div class="modal" id="modalNewPalette" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Palette</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="frmPopup">
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-4 mb-4">
                                <label for="drpStoreID" class="form-label">Store ID <span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpStoreID" id="drpStoreID">
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($resStoresData as $item) {
                                        echo '<option value="' . $item->StoreID . '" data-blocks="' . $item->StoreBlocks . '">' . $item->StoreName . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpBlockID" class="form-label">Block ID <span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpBlockID" id="drpBlockID">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpRackName" class="form-label">Rack Name<span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpRackName" id="drpRackName">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="btnPlacePallet" class="btn btn-primary">Place Palette</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="modalAdd" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Palette</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="frmAddPopup">
                    <div class="modal-body">
                        <div class="row">

                            <input type="hidden" id="hdnCSBlocks" value="<?php echo htmlspecialchars(json_encode($resBlocks), ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="hidden" id="hdnCSFloors" value="<?php echo htmlspecialchars(json_encode($resFloors), ENT_QUOTES, 'UTF-8'); ?>" />

                            <div class="col-md-4 mb-4">
                                <label for="drpStoreID" class="form-label">Store ID <span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpStoreID" id="drpStoreID" disabled>
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($resColdStores as $coldStore) {
                                        echo '<option value="' . $coldStore->StoreID . '" data-blockslist="' . $coldStore->StoreBlocks . '">' . $coldStore->StoreName . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpAddBlockID" class="form-label">Block ID <span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpAddBlockID" id="drpAddBlockID">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpAddRackName" class="form-label">Rack Name<span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpAddRackName" id="drpAddRackName">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="hdnStockId" value="" />
                        <input type="hidden" id="hdnExistingId" value="" />
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="btnPlacePallet" class="btn btn-primary">Place Palette</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php if(!empty($IsMultipleStockAssignToRack)){?>
        <div class="modal" id="modalMultiShift" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Place Palette</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="frmAddPopupMultiShift">
                        <div class="modal-body">
                            <div class="row">

                                <input type="hidden" id="hdnCSBlocksMultiShift" value="<?php echo htmlspecialchars(json_encode($resBlocks), ENT_QUOTES, 'UTF-8'); ?>" />
                                <input type="hidden" id="hdnCSFloorsMultiShift" value="<?php echo htmlspecialchars(json_encode($resFloors), ENT_QUOTES, 'UTF-8'); ?>" />

                                <div class="col-md-4 mb-4">
                                    <label for="drpStoreIDMultiShift" class="form-label">Store ID <span style="color:red">*</span></label>
                                    <select class="form-select drpSelectModal" name="drpStoreID" id="drpStoreIDMultiShift">
                                        <option value="">Select</option>
                                        <?php
                                        foreach ($resColdStores as $coldStore) {
                                            echo '<option value="' . $coldStore->StoreID . '" data-blockslist="' . $coldStore->StoreBlocks . '">' . $coldStore->StoreName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label for="drpAddBlockIDMultiShift" class="form-label">Block ID <span style="color:red">*</span></label>
                                    <select class="form-select drpSelectModal" name="drpAddBlockID" id="drpAddBlockIDMultiShift">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label for="drpAddRackNameMultiShift" class="form-label">Rack Name<span style="color:red">*</span></label>
                                    <select class="form-select drpSelectModal" name="drpAddRackName" id="drpAddRackNameMultiShift">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="hdnSelectedRow" />
                            <!-- <input type="hidden" id="hdnExistingId" value="" /> -->
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="btnPlacePalletMultiShift" class="btn btn-primary">Place Palette</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<!-- END layout-wrapper -->




<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
<!-- included for fixed column -->
 <?php
 if(!empty($IsMultipleStockAssignToRack)){?>
<script
    src="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns@4.3.0/js/dataTables.fixedColumns.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns-dt@4.3.0/css/fixedColumns.dataTables.min.css" />
<!-- included for fixed column -->
 <?php }?>

<script>


function customeDownload() {
    // Get date values
    const fromDate = $('#fromDate').val();
    const toDate = $('#toDate').val();

    // Validate dates
    if (!fromDate || !toDate) {
        alert("Both From and To dates are required!");
        return;
    }


    // Collect all parameters
    const params = {
        FromDate: fromDate,
        ToDate: toDate,
        ColdstoreID: $("#drpColdStore").val(),
        TenantID: $("#drpStockBlgsTo").val(),
        VarietyID: $("#drpVariety").val(),
        PoGradeId: $("#drpGrade").val(),
        PackingStyleID: $("#drpPckgStyle").val(),
        StockType: $("#drpStockType").val(),
        PackingType: $("#drpPackingType").val(),
        ShortName: $("#hdnFilterShortName").val(),
        PoNumber: $("#drpPoNumber").val(),
        Species: $("#drpSpecies").val(),
        TypeProduct: $("#drpTypeProduct").val(),
        FreezingType: $("#drpFreezingType").val(),
        Blocks: $("#drpBlocks").val(),
        drpBrand: $("#drpBrand").val()
    };

    // Build query string (automatically URL-encodes values)
    const queryString = new URLSearchParams(params).toString();

    // Construct the download URL
    const url = `/v3/ColdStore/Ajax-store-on-floor-stock-export.php?${queryString}`;
console.log(url);
    // Open the URL in a new tab
    window.open(url, '_blank');
}

function GetAnalytics(){
        $.ajax({
            url: 'Ajax/ajax-store-on-floor-stock.php',
            type: 'POST',
            dataType: 'json',
            data: {
                methodType:'getAnalytics',
                coldStore: $("#drpColdStore").val(),
                stockBlgsTo: $("#drpStockBlgsTo").val(),
            },
            success: function(getTotals) {
                //let getTotals = JSON.parse(data);
                $("#totalActiveStocks").text(getTotals.totalLst24);
                $("#totalMcs").text(getTotals.totalMcs + ' kgs');
                $("#totalLooseBags").text(getTotals.totalLooseBags + ' kgs');
                $("#totalLst24").text(getTotals.totalLst24);
                $("#totalFloorStocks").text(getTotals.totalFloorStocks);
                $("#totalStoreStocks").text(getTotals.totalStoreStocks);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    }

function toggleDownloadButtonState() {
    const hasFrom = $('#fromDate').val();
    const hasTo = $('#toDate').val();
    const enable = Boolean(hasFrom && hasTo);

    $('#btnDownload')
        .prop('disabled', !enable)
        .toggleClass('btn-disabled', !enable);
}
function attachDataTableToolbar() {
    const $toolbarContainer = $(".custom-filters");
    if (!$toolbarContainer.length) return;

    const toolbarMarkup = `
        <div class="dt-toolbar">
            <div class="dt-date-group">
                <input type="text" id="fromDate" class="dt-date" placeholder="Start date" style="width:200px">
                <span class="dt-separator">&nbsp;→</span>
                <input type="text" id="toDate" class="dt-date" placeholder="End date" style="width:200px">
            </div>
            <button id="btnDownload"
                onClick="customeDownload()"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm">
                Download Excel
            </button>
        </div>`;

    if ($toolbarContainer.find(".dt-toolbar").length === 0) {
        $toolbarContainer
            .addClass("dt-filter-wrapper")
            .append(toolbarMarkup);
    }

    // ✅ Restore previous values
    const prevFrom = ''; //sessionStorage.getItem("fromDate");
    const prevTo = ''; //sessionStorage.getItem("toDate");

    if (prevFrom) $("#fromDate").val(prevFrom);
    if (prevTo) $("#toDate").val(prevTo);

    // ✅ Fix correct selector
    const $searchInput = $("#tblDataTable_filter input[type='search']");
    $searchInput
        .attr("placeholder", "Search records...")
        .addClass("dt-search-input");

    const $dateInputs = $(".dt-date");

    $dateInputs.each(function () {
        const $input = $(this);

        $input.off(".dateToggle")
            .on("focus.dateToggle", function () {
                $input.attr("type", "date");
            })
            .on("blur.dateToggle", function () {
                if (!$input.val()) {
                    $input.attr("type", "text");
                }
            })
            .on("change.dateToggle input.dateToggle", function () {

                // ✅ Save values
                sessionStorage.setItem("fromDate", $("#fromDate").val());
                sessionStorage.setItem("toDate", $("#toDate").val());

                toggleDownloadButtonState();
            });
    });

    // ✅ Ensure correct state after restore
    toggleDownloadButtonState();
}

    function fnShowDataTable() {
        var IsMultipleStockAssignToRack = '<?php echo !empty($IsMultipleStockAssignToRack) ? $IsMultipleStockAssignToRack : 0;?>';
        var drpStockType = $('#drpStockType').val();
        var fixedColOption = false;
        if(drpStockType == 1 && IsMultipleStockAssignToRack == 1){
            fixedColOption = {
                leftColumns: 1
            };
        }
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            searching: true,
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: fixedColOption,
            scrollCollapse: true,
            bInfo: false,
            bsort: false,
            language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            //dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf<"custom-after-length">><"col-md-6 custom-filters">>rtip',
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lBf><"col-md-6 custom-filters">>rtip',

            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Store on Floor Stock <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    //columns: ':visible',
                    columns: ':visible:not(.noExport)',
                    format: {
                        body: function (data, row, column, node) {
                            // if input exists, get its value
                            var input = $(node).find('input');
                            if (input.length) {
                                return input.val();
                            }
                            return data;
                        }
                    }
                }
            }],
        });
        table.on('draw.dt', function () {
            let AvalMcs= 0, LooseBags = 0;

            $('.AvalMcs').each(function () {
                // Parse the text content as a float, ignoring any commas
                const Mcs = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(Mcs)) {
                    AvalMcs += Mcs;
                }
            });

            $('.LooseBags').each(function () {
                // Parse the text content as a float, ignoring any commas
                const Bags = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(Bags)) {
                    LooseBags += Bags;
                }
            });

            $('#TotalMcs').text(AvalMcs.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

             $('#TotalBags').text(LooseBags.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
    }

    $("#drpVariety").on("change", function() {
        var species = $.trim($(this).find(':selected').data('species'));
        var shortName = $.trim($(this).find(':selected').data('shortname'));
        var typeProduct = $.trim($(this).find(':selected').data('typeproduct'));

        $("#hdnFilterSpecies").val(species);
        $("#hdnFilterShortName").val(shortName);
        $("#hdnFilterTypeProduct").val(typeProduct);

    });

    function fnSuccess(data) {
        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
                "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
            )
            .insertAfter("#tblDataTable_filter label");

        $('.dt-buttons').detach().insertAfter('#btnLoadAll');

            setTimeout(function () {
                attachDataTableToolbar();
            }, 100);
        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }


function attachDataTableToolbar2() {
        const $toolbarContainer = $(".custom-filters");
        if (!$toolbarContainer.length) {
            return;
        }
        const toolbarMarkup = `
            <div class="dt-toolbar">
                <div class="dt-date-group">
                    <input type="text" id="fromDate" class="dt-date" placeholder="Start date" style="width:200px">
                    <span class="dt-separator">&nbsp;→</span>
                    <input type="text" id="toDate" class="dt-date" placeholder="End date" style="width:200px">
                </div>
                <button id="btnDownload" onClick="customeDownload()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors flex items-center gap-2">
                Download Excel</button>
            </div>`;
        if ($toolbarContainer.find(".dt-toolbar").length === 0) {
            $toolbarContainer.addClass("dt-filter-wrapper").append(toolbarMarkup);
        }
        const $searchInput = $("#tblDataTable_filter input[type='search']");
        $searchInput.attr("placeholder", "Search records...").addClass("dt-search-input");
        const $dateInputs = $(".dt-date");
        $dateInputs.each(function () {
            const $input = $(this);
            $input.off(".dateToggle").on("focus.dateToggle", function () {
                $input.attr("type", "date");
            }).on("blur.dateToggle", function () {
                if (!$input.val()) {
                    $input.attr("type", "text");
                }
            }).on("change.dateToggle input.dateToggle", toggleDownloadButtonState);
        });
        //toggleDownloadButtonState();
    }
    function poNumber(productionPlant) {
        var $drpPoNumber = $("#drpPoNumber");

        $drpPoNumber.find('option').each(function() {
            var optionPlantId = $(this).data('plantid');
            var plantIdArray = String(optionPlantId).split(',');

            if (productionPlant == 0 || plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                $(this).show();
            } else {
                $(this).remove();
            }
        });
    }

    function plantAutoFill() {
        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }

        poNumber(productionPlant);
    }

    $(document).on("change", "#drpColdStore", function() {
        storeBlocks();

        var productionPlant = <?php echo $ProductionPlant ?>;
        poNumber(productionPlant);
    });

    $(document).ready(function() {

        plantAutoFill();

        var blockIds = $("#drpColdStore option:selected").data("blocks");

        if (blockIds && blockIds.length > 0) {
            var blockIdsArray = blockIds.split(',').map(function(id) {
                return id.trim();
            });

            var $drpBlocks = $("#drpBlocks");

            $drpBlocks.find('option').each(function() {
                var optionValue = $(this).val();
                if (optionValue === "" || blockIdsArray.includes(optionValue)) {
                    $(this).show();
                } else {
                    $(this).remove();
                }
            });
        }

        storeBlocks();

        $("#drpPoNumber").on("change", function() {
            var hdnGradeList = JSON.parse($("#hdnGradeList").val());
            var hdnPackingStyleList = JSON.parse($("#hdnPackingStyle").val());

            if ($("#drpPoNumber").val() !== '') {
                var gradeId = $("#drpPoNumber option:selected").data('grade');
                var allowedGradeIds = gradeId.split(",");

                var listGrade = "<option value=''>Select</option>";
                $.each(hdnGradeList, function(index, val) {
                    if (allowedGradeIds.indexOf(val.value) !== -1) {
                        listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                    }
                });
                $("#drpGrade").html("").html(listGrade);

                var packingstyleId = $("#drpPoNumber option:selected").data('packingstyleid').toString();
                var allowedPackingStyleId = packingstyleId.split(",");

                var listPackingStyle = "<option value=''>Select</option>";
                $.each(hdnPackingStyleList, function(index, val) {
                    if (allowedPackingStyleId.indexOf(val.PCKID) !== -1) {
                        listPackingStyle += '<option value="' + val.PCKID + '">' + val.PackingStyle + '</option>';
                    }
                });
                $("#drpPckgStyle").html("").html(listPackingStyle);

            } else {
                var listGrade = "<option value=''>Select</option>";
                $.each(hdnGradeList, function(index, val) {
                    listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                });
                $("#drpGrade").html("").html(listGrade);

                var listPackingStyle = "<option value=''>Select</option>";
                $.each(hdnPackingStyleList, function(index, val) {
                    listPackingStyle += '<option value="' + val.PCKID + '">' + val.PackingStyle + '</option>';
                });
                $("#drpPckgStyle").html("").html(listPackingStyle);
            }
        });

        var productionPlant = <?php echo $ProductionPlant ?>;

        console.log('productionPlant' + productionPlant);
        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            $("#drpStockBlgsTo").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpStockBlgsTo").val("").trigger('change');
        }
    });

    function storeBlocks() {
        var blocksList = JSON.parse($("#hdnBlocks").val());

        var coldStoreId = $("#drpColdStore");

        var selectBlocks = $(coldStoreId).find(':selected').data("blocks");

        if (selectBlocks) {
            var selectBlocksAry = selectBlocks.split(",");

            var listBlock = "<option value=''>Select</option>";
            $.each(blocksList, function(index, val) {
                if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                }
            });
            $("#drpBlocks").html("").html(listBlock);
        } else {
            $("#drpBlocks").html("<option value=''>Select</option>");
        }
    }

    function ShowList(pageNo, searchkey, loadAll) {

        $.ajax({
            url: 'Ajax/ajax-store-on-floor-stock.php',
            type: 'POST',
            data: {
                coldStore: $("#drpColdStore").val(),
                stockBlgsTo: $("#drpStockBlgsTo").val(),
                variety: $("#drpVariety").val(),
                grade: $("#drpGrade").val(),
                pckgStyle: $("#drpPckgStyle").val(),
                stockType: $("#drpStockType").val(),
                pckgType: $("#drpPackingType").val(),
                fShortName: $("#hdnFilterShortName").val(),
                PoNumber: $("#drpPoNumber").val(),
                species: $("#drpSpecies").val(),
                typeProduct: $("#drpTypeProduct").val(),
                freezingType: $("#drpFreezingType").val(),
                blocks: $("#drpBlocks").val(),
                group: $("#drpGroup").val(),
                pageNo: pageNo,
                searchKey: searchkey,
                loadAll: loadAll,
                drpBrand: $("#drpBrand").val(),
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    $(document).ready(function() {
        ShowList("1", "", "0");
        GetAnalytics();
        setTimeout(function() {
    //attachDataTableToolbar();
}, 1000); // delay in milliseconds (1000ms = 1 second)
    });

    $(document).on("click", "#btnMoreSearch", function() {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function() {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function() {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function() {
        ShowList("1", "", "0");
    });

    $(document).on('click', '.AddToPallet', function(e) {
        e.preventDefault();

        var StockID = $(this).data('id');
        $('#Update').val(StockID);

        var r = confirm("Are You Sure Do You Want To Add To Existing Pallet ?");
        if (r == true) {
            $('#modalNewPalette').modal('show');

        } else {}
    });

    $(document).on('click', '.unplacePallate', function() {

        var bid = $(this).data('id');
        var stockid = $(this).data('stockid');
        var storeRackId = $(this).attr('data-storerackid');

        console.log('storeRackId ' + storeRackId);

        var r = confirm("Are You Sure Do You Want To Unplace Palette ?");
        if (r == true) {
            $.ajax({
                url: 'Ajax/AjaxAddStock.php',
                type: 'POST',
                data: {
                    BID: bid,
                    StockID: stockid,
                    storeRackId: storeRackId,
                    type: "PALLET_UNPLACE"
                },
                success: function(data) {
                    data = JSON.parse(data);
                    console.log(data);
                    alert(data.msg);

                    $("#btnSearch").trigger("click");
                }
            });
        }
    });




$(document).on('click', '.shiftPallate', function() {


    var blocks = $("option:selected", this).attr("data-blockslist");
      //  var blockArray = blocks.split(',');
        var blockArray = Array();

        var blocksList = JSON.parse($("#hdnBlocks").val());

        var filteredArray = $.grep(blocksList, function(element) {
            return $.inArray(element.BlockID, blockArray) !== -1;
        });

        var listBlock = "<option value=''>Select</option>";
        $.each(filteredArray, function(index, val) {
            listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
        });

        $("#drpBlockID").html(listBlock);
        $("#drpRackName").html("<option value=''>select</option>");

   // var storeID = $("#drpColdStores").val();
       // $("#drpStoreID").val(storeID).trigger('change');

    //    var listBlock = "<option value=''>Select</option>";
    //     listBlock += "<option value='1'>B1</option>";
    //     // $.each(filteredArray, function(index, val) {
    //     //     listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
    //     // });

    //     $("#drpBlockID").html(listBlock);
    //     $("#drpRackName").html("<option value=''>select</option>");


        $('#modalNewPalette').modal('show');
    $("#aNewPlacePalette").trigger("click");
var bid = $(this).data('id');
var stockid = $(this).data('stockid');
var storeRackId = $(this).attr('data-storerackid');

console.log('storeRackId ' + storeRackId);

var r = confirm("Are You Sure Do You Want To Shift Palette ?");
// if (r == true) {
//     $.ajax({
//         url: 'Ajax/AjaxAddStock.php',
//         type: 'POST',
//         data: {
//             BID: bid,
//             StockID: stockid,
//             storeRackId: storeRackId,
//             type: "PALLET_UNPLACE"
//         },
//         success: function(data) {
//             data = JSON.parse(data);
//             console.log(data);
//             alert(data.msg);

//             $("#btnSearch").trigger("click");
//         }
//     });
// }
});



$(document).on("change", "#drpBlockID", function() {
        var blockid = $(this).val();

        if (blockid != "") {
            $.ajax({
                url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: {
                    methodType: "getRackList",
                    blockID: $.trim($(this).val()),
                    storeID: $.trim($("#drpStoreID").val())
                },
                success: function(data) {
                    var listFloor = "<option value=''>select</option>";
                    $.each(data, function(index, val) {
                        if (val.IsPath != "1") {
                            if (val.Status == "1") {
                                listFloor += '<option value="' + val.RackID + '">' + val.RackName + " (Filled)" + '</option>';
                            } else {
                                listFloor += '<option value="' + val.RackID + '">' + val.RackName + '</option>';
                            }
                        }
                    });

                    $("#drpRackName").html(listFloor);

                    return false;
                }
            });
        } else {
            $("#drpRackName").html("<option value=''>select</option>");
        }

    })



    $('.drpSelectModal').select2({
        dropdownParent: $('#modalNewPalette')
    });

    $('.drpSelectModal').select2({
        dropdownParent: $('#modalAdd')
    });


    $(document).on("change", "#drpStoreID", function() {
        var blocksList = JSON.parse($("#hdnCSBlocks").val());
        $("#drpNoFloors").html("").html("<option value='' data-floors=''>Select</option>");
        /*packing store starts*/
        var PackagingStoreIdsArr = <?php echo json_encode(!is_array($PackagingStoreIds) ? explode(',',$PackagingStoreIds) :[]);?>;
        /*packing store ENDS*///2,4

        var selectBlocks = $(this).find(':selected').attr('data-blockslist');

        if (selectBlocks) {

            var selectBlocksAry = selectBlocks.split(",");
            var OtherThenPackingStoreIds = selectBlocksAry.filter(value => !PackagingStoreIdsArr.includes(value));

            var listBlock = "<option value='' data-floors=''>Select</option>";
            $.each(blocksList, function(index, val) {
                if (OtherThenPackingStoreIds.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '" data-floors="' + val.Floors + '">' + val.BlockName + '</option>';
                }
            });
            /* $.each(blocksList, function(index, val) {
                if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '" data-floors="' + val.Floors + '">' + val.BlockName + '</option>';
                }
            }); */
            // console.log(listBlock);
            $("#drpAddBlockID").html("").html(listBlock);
        } else {
            try{ $('#drpAddBlockID, #drpAddRackName').select2('destroy'); }catch(e){}

            // $("#drpAddBlockID").val("");
            $("#drpAddBlockID").html("").html("<option value=''>Select</option>");
            $("#drpAddRackName").html("").html("<option value=''>Select</option>");

            $('#drpAddBlockID, #drpAddRackName').select2({
                dropdownParent: $('#modalAdd')
            });
        }

    })

    $(document).on("change", "#drpAddBlockID", function() {
        var blockid = $(this).val();
        var drpStoreID = $("#frmAddPopup #drpStoreID").val();

        $.ajax({
            url: 'Ajax/AjaxInward.php',
            type: 'POST',
            data: {
                methodType: "getRackList",
                blockID: $.trim($(this).val()),
                storeID: drpStoreID
            },
            success: function(data) {

                var listFloor = "<option value=''>Select</option>";
                $.each(data, function(index, val) {
                    if (val.IsPath != "1") {
                        if (val.Status == "1") {
                            listFloor += '<option value="' + val.RackID + '">' + val.RackName + " (Filled)" + '</option>';
                        } else {
                            listFloor += '<option value="' + val.RackID + '">' + val.RackName + '</option>';
                        }
                    }
                });

                $("#drpAddRackName").html(listFloor);

                return false;
            }
        });

    })


    $(document).on('click', '.aListAdd', function() {
        var sId = $(this).data('stockid');
        var storeid = $(this).closest("tr").find(".tdStoreName").data('storeid');
        var existingRackID = $(this).data('storerackid');

       // alert(existingRackID);

        try{ $('#drpAddBlockID, #drpAddRackName').select2('destroy'); }catch(e){ }

        $("#drpStoreID, #drpAddBlockID").val("");
        $("#drpAddRackName").html("").html("<option value=''>Select</option>");

        $('#drpStoreID, #drpAddBlockID, #drpAddRackName').select2({
            dropdownParent: $('#modalAdd')
        });

        if(existingRackID==0){
            var r = confirm("Are You Sure Do You Want To Add To Existing Palette ?");
            var isShifting=0; //false
        }else{
            var r = confirm("Are You Sure Do You Want To Shift Palette ?");
            var isShifting=1; // true
        }

        if (r == true) {
            $('#modalAdd').modal('show');
            $("#hdnStockId").val(sId);
            $("#hdnExistingId").val(existingRackID);
            $("#frmAddPopup #drpStoreID").val(storeid).trigger("change");
        } else {
            $("#hdnStockId").val("");
            $("#hdnExistingId").val("");
        }
    });

    $("#frmAddPopup").validate({

        onkeyup: false,
        onfocusout: false,
        invalidHandler: function(form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                firstInvalidElement.focus();
            }
        },
        ignore: "",
        rules: {
            drpStoreID: {
                required: true
            },
            drpAddBlockID: {
                required: true
            },
            drpAddRackName: {
                required: true
            },
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {

            var hdnSid = $("#hdnStockId").val();
            var existingRackID = $("#hdnExistingId").val();

            $.ajax({
                type: 'POST',
                url: 'Ajax/ajax-store-on-floor-stock.php',
                method: "post",
                data: {
                    methodType: "popupAdd",
                    stockID: hdnSid,
                    existingRackID:existingRackID,
                    drpAddRackName: $("#drpAddRackName").val()
                },
                success: function(data) {
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);
                        $("#btnSearch").trigger("click");
                        $('#modalAdd').modal('hide');
                        $("#hdnStockId").val("");
                        $("#existingRackID").val("");
                    }
                }
            });

            return false;

        }
    });
    <?php
    if(!empty($StoreRackMaxMcs)){?>
        var FlooreRackIdsArr = <?php echo json_encode($FlooreRackIds);?>;

         $(document).on("change", "#drpAddRackName", function () {
            let IsNotFlooreIds = true;
            if(FlooreRackIdsArr.length !=0){
                if($.inArray($(this).val(), FlooreRackIdsArr) === -1){//not in
                    IsNotFlooreIds = true;
                }else{//in
                    IsNotFlooreIds = false;
                }
            }
            if($(this).val() && $('#drpAddBlockID').val() && IsNotFlooreIds){
                $.ajax({
                    url: 'Ajax/AjaxInward.php',
                    type: 'POST',
                    data: {
                        methodType: "validateRackId",
                        rackId: $.trim($(this).val()),
                        StoreRackMaxMcs : '<?php echo $StoreRackMaxMcs;?>',
                        BlockID: $.trim($('#drpAddBlockID').val()),
                        //storeID: $.trim($("#drpStoreID").val()),
                        StockId: $.trim($("#hdnStockId").val())
                    },
                    success: function (res) {
                        if(res.Error == null){
                            $('#btnPlacePallet').prop('disabled', false);
                        }else{
                            alert(res.Error);
                            $('#btnPlacePallet').prop('disabled', true);
                            $('#drpAddRackName').val(null).trigger('change');
                        }
                    }
                })
            }else{
                $('#btnPlacePallet').prop('disabled', false);
            }
         })
   <?php } ?>
   <?php
   if(!empty($IsMultipleStockAssignToRack)){
        $ProductionPlant = $_SESSION['UnitID'];?>
        $(document).ready(function(){
            $('#hdnSelectedRow').val('');
            $('#LoadToCons').prop('disabled', true);
        });
        $('#btnSearch').click(function () {
            $('#hdnSelectedRow').val('')
            $('#LoadToCons').prop('disabled',true)
        });
        $('.drpSelectModal').select2({
            dropdownParent: $('#modalMultiShift')
        });
        var productionPlant = '<?php echo $ProductionPlant ?>';
        if (productionPlant != 0) {
            $("#drpStoreIDMultiShift").prop('disabled', true);
            $("#drpStoreIDMultiShift").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpStoreIDMultiShift").prop('disabled', false);
            $("#drpStoreIDMultiShift").val("").trigger('change');
        }
        $(document).on("click", "input.rowCheck", function() {

            var isChecked = $(this).prop('checked');
            var msc = parseFloat($(this).closest("tr").find(".txtMCS").val());
            var looseBags = parseFloat($(this).closest("tr").find(".txtLooseBags").val());

            // console.log("msc", msc);
            // console.log("looseBags", looseBags);

            if (isChecked) {
                checkOptions();
                //sumMcsLoosBags(msc, looseBags, "checked");
                $(this).closest("tr").find(".txtMCS").prop("disabled", true);
                $(this).closest("tr").find(".txtLooseBags").prop("disabled", true);
            } else {
                checkOptions($(this).val());
                //sumMcsLoosBags(msc, looseBags, "unChecked");
                $(this).closest("tr").find(".txtMCS").prop("disabled", false);
                $(this).closest("tr").find(".txtLooseBags").prop("disabled", false);
            }
            $("#checkall").prop("checked", false);

        });

        $(document).on("click", "#checkall", function() {
            if (this.checked) {
                $(".rowCheck").prop("checked", true);
                checkOptions();
            } else {
                $(".rowCheck").prop("checked", false);
                checkOptions();
            }
        });
        $(document).on("keyup", ".txtMCS", function() {
            var actualMsc = parseInt($(this).attr("data-mcs"));
            var enteredMsc = parseInt($(this).val());
            if(enteredMsc < 0){
                alert("Minimum value allowed is " + actualMsc)
                $(this).val(actualMsc);
                return;
            }
            if (enteredMsc > actualMsc) {
                alert("MCS exceeds actual value.");
                $(this).val(actualMsc);
            } else {
                if (actualMsc == enteredMsc) {
                    var $looseBagsField = $(this).closest("tr").find(".txtLooseBags");
                    var actualBags = parseFloat($looseBagsField.attr("data-bags"));//1
                    var enteredBags = parseFloat($looseBagsField.val());//2
                    if (enteredBags > actualBags) {
                        alert("Loose Bags exceeds actual value.");
                        $(this).val(actualMsc);
                        $looseBagsField.val(actualBags);
                    }
                }
            }
        });

        $(document).on("keyup", ".txtLooseBags", function() {
            var actualBags = parseFloat($(this).attr("data-bags"));//1
            var enteredBags = parseFloat($(this).val());//3
            if(enteredBags < 0){
                alert("Minimum value allowed is " + actualBags)
                $(this).val(actualBags);
                return;
            }

            var $mcsField = $(this).closest("tr").find(".txtMCS");
            var actualMsc = parseInt($mcsField.attr("data-mcs"));//2
            var enteredMsc = parseInt($mcsField.val());//1
            let TotalSlabs = parseInt($(this).attr("data-totalslabs")) || 0;
             TotalSlabs = (TotalSlabs * (actualMsc-enteredMsc)) + actualBags;

            if (actualMsc == enteredMsc && enteredBags > actualBags) {
                alert("Loose Bags and MCS exceeds actual value.");
                $mcsField.val(actualMsc);
                $(this).val(actualBags);
                return
            }
            if(TotalSlabs < enteredBags){
                alert("Loose Bags Exceeds, "+TotalSlabs+" Bags Allowed.");
                //$mcsField.val(actualMsc);
                $(this).val(actualBags);
                return;
            }
        })
        function checkOptions(unCheckVal) {
            var selectedOptions = [];
            var jsonObj = [];

            $("input.rowCheck:checked").each(function() {

                var sno = $(this).data('sno');
                var existingRackID = $(this).data('store_rack_id');
                var bid = $(this).data('bid');

                selectedOptions.push(sno);

                var stock_Id = parseInt(this.value);
                var txtMCS = parseInt($("#" + sno + "-txtMCS").val()) || 0;
                var txtLooseBags = parseFloat($("#" + sno + "-txtLooseBags").val()) || 0;

                jsonObj.push({
                    stock_Id : parseInt(stock_Id),
                    bid : parseInt(bid),
                    mcs : txtMCS,
                    LooseBags : txtLooseBags,
                    existingRackID : existingRackID
                });

            });
            //console.log(jsonObj);

            var jsonData = JSON.stringify(jsonObj);
             $('#hdnSelectedRow').val('');
            if(jsonObj.length != 0){
                $('#hdnSelectedRow').val(jsonData);
            }
            //console.log(jsonData);


            if ($("#hdnSelectedRow").val().length > 0) {
                $("#LoadToCons").prop("disabled", false);
            } else {
                $("#LoadToCons").prop("disabled", true);
            }
        }
        $(document).on("click", "#LoadToCons", function() {

           /*  if ($('#frmAddPopupMultiShift #drpAddBlockIDMultiShift').hasClass("select2-hidden-accessible")) {
                $('#frmAddPopupMultiShift #drpAddBlockIDMultiShift').select2('destroy');
            } */

           /*  if ($('#frmAddPopupMultiShift #drpAddRackNameMultiShift').hasClass("select2-hidden-accessible")) {
                $('#frmAddPopupMultiShift #drpAddRackNameMultiShift').select2('destroy');
            } */

            $("#frmAddPopupMultiShift #drpStoreIDMultiShift")
                .val($("[data-plant='" + productionPlant + "']").val())
                .trigger("change");

            $('#drpStoreIDMultiShift, #drpAddBlockIDMultiShift, #drpAddRackNameMultiShift').select2({
                dropdownParent: $('#modalMultiShift')
            });

            $('#modalMultiShift').modal('show');

        });
        $(document).on("change", "#drpStoreIDMultiShift", function() {
            var blocksList = JSON.parse($("#hdnCSBlocksMultiShift").val());
            $("#drpNoFloorsMultiShift").html("").html("<option value='' data-floors=''>Select</option>");
            /*packing store starts*/
            var PackagingStoreIdsArr = <?php echo json_encode(!is_array($PackagingStoreIds) ? explode(',',$PackagingStoreIds) :[]);?>;
            /*packing store ENDS*///2,4

            var selectBlocks = $(this).find(':selected').attr('data-blockslist');

            if (selectBlocks) {

                var selectBlocksAry = selectBlocks.split(",");
                var OtherThenPackingStoreIds = selectBlocksAry.filter(value => !PackagingStoreIdsArr.includes(value));

                var listBlock = "<option value='' data-floors=''>Select</option>";
                $.each(blocksList, function(index, val) {
                    if (OtherThenPackingStoreIds.indexOf(val.BlockID) !== -1) {
                        listBlock += '<option value="' + val.BlockID + '" data-floors="' + val.Floors + '">' + val.BlockName + '</option>';
                    }
                });
                /* $.each(blocksList, function(index, val) {
                    if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                        listBlock += '<option value="' + val.BlockID + '" data-floors="' + val.Floors + '">' + val.BlockName + '</option>';
                    }
                }); */
                // console.log(listBlock);
                $("#drpAddBlockIDMultiShift").html("").html(listBlock);
            } else {
                try { $('#drpAddBlockIDMultiShift, #drpAddRackNameMultiShift').select2('destroy'); }catch(e){ }

                // $("#drpAddBlockID").val("");
                $("#drpAddBlockIDMultiShift").html("").html("<option value=''>Select</option>");
                $("#drpAddRackNameMultiShift").html("").html("<option value=''>Select</option>");

                $('#drpAddBlockIDMultiShift, #drpAddRackNameMultiShift').select2({
                    dropdownParent: $('#modalMultiShift')
                });
            }

        })

        $(document).on("change", "#drpAddBlockIDMultiShift", function() {
            var blockid = $(this).val();
            var drpStoreID = $("#frmAddPopupMultiShift #drpStoreIDMultiShift").val();

            $.ajax({
                url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: {
                    methodType: "getRackList",
                    blockID: $.trim($(this).val()),
                    storeID: drpStoreID
                },
                success: function(data) {

                    var listFloor = "<option value=''>Select</option>";
                    $.each(data, function(index, val) {
                        if (val.IsPath != "1") {
                            if (val.Status == "1") {
                                listFloor += '<option value="' + val.RackID + '">' + val.RackName + " (Filled)" + '</option>';
                            } else {
                                listFloor += '<option value="' + val.RackID + '">' + val.RackName + '</option>';
                            }
                        }
                    });

                    $("#drpAddRackNameMultiShift").html(listFloor);

                    return false;
                }
            });

        })
        $(document).on("change", "#drpBlockIDMultiShift", function() {
            var blockid = $(this).val();

            if (blockid != "") {
                $.ajax({
                    url: 'Ajax/AjaxInward.php',
                    type: 'POST',
                    data: {
                        methodType: "getRackList",
                        blockID: $.trim($(this).val()),
                        storeID: $.trim($("#drpStoreIDMultiShift").val())
                    },
                    success: function(data) {
                        var listFloor = "<option value=''>select</option>";
                        $.each(data, function(index, val) {
                            if (val.IsPath != "1") {
                                if (val.Status == "1") {
                                    listFloor += '<option value="' + val.RackID + '">' + val.RackName + " (Filled)" + '</option>';
                                } else {
                                    listFloor += '<option value="' + val.RackID + '">' + val.RackName + '</option>';
                                }
                            }
                        });

                        $("#drpRackNameMultiShift").html(listFloor);

                        return false;
                    }
                });
            } else {
                $("#drpRackNameMultiShift").html("<option value=''>select</option>");
            }
        })
        $("#frmAddPopupMultiShift").validate({
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: {
                drpStoreID: {
                    required: true
                },
                drpAddBlockID: {
                    required: true
                },
                drpAddRackName: {
                    required: true
                },
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                var josnObj = JSON.parse($("#hdnSelectedRow").val());
                //var existingRackID = $("#hdnExistingId").val();
                if(josnObj.length == 0){
                    alert('Please select checkboxes First.');
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: 'Ajax/ajax-store-on-floor-stock.php',
                    method: "post",
                    cache: false,
                    //dataType: 'json',
                    data: {
                        methodType: "MiltipleShiftPalette",
                        StockData: $("#hdnSelectedRow").val(),
                        //existingRackID:existingRackID,
                        drpAddRackName: $("#drpAddRackNameMultiShift").val()
                    },
                    success: function(data) {
                        if (data.Error) {
                            alert(data.Error);
                        } else {
                            alert(data.Msg);
                            $("#btnSearch").trigger("click");
                            try{ $('#frmAddPopupMultiShift #drpAddBlockIDMultiShift,#frmAddPopupMultiShift #drpAddRackNameMultiShift').select2('destroy').val(null).select2(); }catch(e){}
                            $('#modalMultiShift').modal('hide');
                            $("#hdnSelectedRow").val("");
                            $("#existingRackID").val("");
                        }
                    }
                });

                return false;
            }
        });
   <?php } ?>
   <?php if($EnableCodeListPrint){?>
        $(document).on("change", "#drpPoNumber", function() {
            $(this).prev("label").text('Po No');
            if($(this).val()){
                var urlParam = btoa('PoID='+$(this).val()+'&ReportName=Internal code list');
                var printLink = `
                                <a target="_blank"
                                href="/v3/QualityControl/Reports/D1/DocMpedaCodeList?Q=${urlParam}&sourceUrl=Freezing"
                                class="mr-3 text-primary"
                                title="Code List Print">
                                <i class="mdi mdi-printer font-size-18"></i>
                                </a>
                            `;
                $(this).prev('label').append(printLink);
            }
        })
   <?php }?>
</script>

</body>

</html>
