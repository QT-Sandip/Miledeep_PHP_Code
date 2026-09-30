<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$iframview = isset($_GET['iframe']) && !empty($_GET['iframe'])? ture:false;
$bid = isset($_GET["BID"]) && !empty($_GET["BID"]) ? $_GET["BID"] : "";
$barCodeId = isset($_GET["BarCodeId"]) && !empty($_GET["BarCodeId"]) ? $_GET["BarCodeId"] : "";
$StockID = isset($_GET["StockID"]) && !empty($_GET["StockID"]) ? $_GET["StockID"] : "";
$ClientConfigData = $_SESSION['ClientConfigData'];
$StoreRackMaxMcs = $ClientConfigData['StoreRackMaxMcs'];//anil
$FlooreRackIds = !empty($ClientConfigData['FlooreRackIds']) ? explode(',',$ClientConfigData['FlooreRackIds']):[]; //anil;
$PackagingStoreIds = !empty($ClientConfigData['PackagingStoreIds']) ? trim($ClientConfigData['PackagingStoreIds']):[]; //anil


$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","FreezingType","Soaking","ProductCategory","Abstatus","OptType","OutType","Grade","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","Plant","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Species","Tranction","TypeProduct","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$bodyContent['exclude_list'] = $excludeList ? $excludeList : [];
$resList = CallAPI($api, "GET", "", $bodyContent);
$resColdStores = $resList->Data->ColdStores;
$resBlocks = $resList->Data->Blocks;
$resFloors = $resList->Data->Floors;

$ProductionPlant = $_SESSION['UnitID'];

if (empty($ProductionPlant) && count($resColdStores) == 1) {
    $ProductionPlant = $resColdStores[0]->ProductionPlantID;
}
// echo print_r($resColdStores[0]);exit;
//echo '<pre>';print_r($_SESSION);die;

?>

<!doctype html>
<html lang="en">

<head>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php include_once ROOT . 'includes/head.php' ?>
    <link rel="stylesheet" type="text/css" href="/assets/css/coldstore_viewracklayout.css">
    <!-- <link href="<?php echo ROOT_URL ?>assetsv2/assets/css/tailwindcss-v4.2.1.css?=<?php echo rand() ?>" rel="stylesheet" type="text/css" /> -->
</head>
    <style>
        .dropdown-list.show {
    display: block;
    opacity: 1;
    transform: scaleY(1);
}
        .used2-dot::before {
            background: oklch(0.5 0.26 301.69);
            border: 2px solid oklch(0.5 0.26 301.69);
        }
        .used3-dot::before {
            background: #3747e6;
            border: 2px solid #3747e6;
        }
        .text-xs{ font-weight: 400; }
        .summary-value{ font-weight: 400; }
      table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td{
        color: oklch(37.3% 0.034 259.733);
      }
    .total-stored-text-layout ul{margin: unset!important;}
    .store-filter-panel{padding: unset!important;}
    body{font-family: Inter}
    .total-box .label,.total-box .value{margin-bottom:unset!important;font-size: 14px;}
    .total-box-container{display: table; margin: 0 auto;margin-bottom: 15px;}
    .bg-green-50{background-color: var(--color-green-50);}
    .bg-amber-50{background-color: oklch(97.7% 0.014 308.299);}
    .bg-amber-200{background-color: oklch(90.2% 0.063 306.703);}
    .bg-amber-200{background-color: oklch(90.2% 0.063 306.703);}
    .border-amber-200{border-color:oklch(90.2% 0.063 306.703);}
    .bg-rose-50{background-color: oklch(98% 0.016 73.684);}
    .bg-rose-200{background-color: oklch(90.1% 0.076 70.697);}
    .bg-blue-50{background-color: var(--color-blue-50);}
      .border-blue-200 {
        border-color: var(--color-blue-200);
      }
      .summary-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 26px;
    background-color: var(--color-blue-200);
    </style>
<?php include_once ROOT . 'includes/body-preloader.php' ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php if(!$iframview){ include_once ROOT . 'includes/menu.php'; } ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content" <?php if($iframview){ echo "style='background:#FFF'"; } ?>>

            <div class="page-content" <?= ($_SESSION['ClientConfigData']['HeaderMenuV2']) 
                ? '' 
                : 'style="padding-top:25px !important;"' ?> >
                <div class="container-fluid">

                    <?php if($_SESSION['ClientConfigData']['HeaderMenuV2'])
                            include_once ROOT . 'includes/page-header.php'; 
                    ?>

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">

                            <div class="store-filter-panel">

                                <form id="frmCreateStoreLayout" method="post">

                                    <input type="hidden" id="hdnBlocks"
                                            value='<?= htmlspecialchars(json_encode($resBlocks), ENT_QUOTES, "UTF-8"); ?>'>
                                    <input type="hidden" id="hdnFloors"
                                                value='<?= htmlspecialchars(json_encode($resFloors), ENT_QUOTES, "UTF-8"); ?>'>

                                    <!-- Store -->
                                    <div class="filter-group">
                                        <label class="filter-label">Store ID <span class="req">*</span></label>
                                        <div class="dropdown-container" id="dropdownStore">
                                            <div class="dropdown-selected" id="selectedStore">Select Store</div>
                                            <div class="dropdown-list" id="listStore">
                                                <input type="text" class="dropdown-search" placeholder="Search..." id="searchStore">
                                                <?php foreach ($resColdStores as $coldStore): ?>
                                                <div class="dropdown-item " data-value="<?= $coldStore->StoreID ?>"
                                                    data-blockslist='<?= !empty($coldStore->StoreBlocks) ? $coldStore->StoreBlocks : "" ?>'
                                                    data-plant="<?= $coldStore->ProductionPlantID ?>">
                                                    <?= $coldStore->StoreName ?>

                                                </div>

                                                <?php endforeach; ?>
                                            </div>
                                            <input type="hidden" name="drpStoreID" id="hiddenStore">
                                        </div>
                                    </div>

                                    <!-- Block -->
                                    <div class="filter-group">
                                        <label class="filter-label">Block ID <span class="req">*</span></label>
                                        <div class="dropdown-container" id="dropdownBlock">
                                            <div class="dropdown-selected" id="selectedBlock">Select Block</div>
                                            <div class="dropdown-list" id="listBlock">
                                                <input type="text" class="dropdown-search" placeholder="Search..." id="searchBlock">
                                                <!-- Block items populated dynamically -->
                                            </div>
                                            <input type="hidden" name="drpBlockID" id="hiddenBlock">
                                        </div>
                                    </div>

                                    <!-- Floor -->
                                    <div class="filter-group">
                                        <label class="filter-label">Floors <span class="req">*</span></label>
                                        <div class="dropdown-container" id="dropdownFloor">
                                            <div class="dropdown-selected" id="selectedFloor">Select Floor</div>
                                            <div class="dropdown-list" id="listFloor">
                                                <input type="text" class="dropdown-search" placeholder="Search..." id="searchFloor">
                                                <!-- Floor items populated dynamically -->
                                            </div>
                                            <input type="hidden" name="drpNoFloors" id="hiddenFloor">
                                        </div>
                                    </div>

                                    <!-- Rack -->
                                    <div class="filter-group">
                                        <label class="filter-label">Rack Name</label>
                                        <div class="dropdown-container" id="dropdownRack">
                                            <div class="dropdown-selected" id="selectedRack">Select Rack</div>
                                            <div class="dropdown-list" id="listRack">
                                                <input type="text" class="dropdown-search" placeholder="Search..." id="searchRack">
                                                <!-- Rack items populated dynamically -->
                                            </div>
                                            <input type="hidden" name="drpRackName" id="hiddenRack">
                                        </div>
                                    </div>

                                    <div class="filter-submit">
                                        <button type="submit" class="btn-modern-submit">Search</button>
                                    </div>

                                </form>



                            </div>

                        </div>
                    </div>

                    <div class="row" id="canvasid" style="position:absolute; left:-9999px; top:-9999px;"> <canvas
                            id="graphCanvas"></canvas> </div>
                    <div id="canvasPopup"
                        style="display:none; padding:20px; background:#fff; border-radius:8px; margin-bottom: 5px;">

                        <br>
                        <button onclick="closePopup()">Close</button>
                    </div>

                    <div class="row" style="width: auto; min-height: 500px;">
                        <div class="col-12">

                            <div id="divStoreLayout" class="horizontal-scroll">

                            </div>
                        </div>
                    </div>

                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <div id="graphModal" class="graph-modal">
                <div class="graph-content" style="padding: 15px; margin-top: 10%; margin-left: 30%;">
                    <span class="close-btn" onclick="closeGraph()">&times;</span>
                    <h2 id="graphTitle" style="text-align:center;font-size: 20px;padding: 0px;margin: 0px;"></h2>
                    <canvas id="popupGraphCanvas" width="800" height="450"></canvas>
                </div>
            </div>

            <div>
                <!-- Modal -->
                <div class="modal fade" id="popupPalletDeails1" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content" style="width: 80%;">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Pallet Details</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered">
                                    <thead>

                                        <tr>
                                            <th>ID</th>
                                            <th>Variety</th>
                                            <th>Grade</th>
                                            <th>Packing Type</th>
                                            <th>Packing Style</th>
                                            <th>Feezing Type</th>
                                            <th>Category</th>
                                            <th>Shift</th>
                                            <th>MCS</th>
                                            <th>Loose</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>ID</td>
                                            <td>Variety</td>
                                            <td>Grade</td>
                                            <td>Packing Type</td>
                                            <td>Packing Style</td>
                                            <td>Feezing Type</td>
                                            <td>Category</td>
                                            <td>Shift</td>
                                            <td>MCS</td>
                                            <td>Loose</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="btnPlacePopup1">Place</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal -->

                <div class="modal fade" id="popupPalletDeails2" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">

                    <div class="modal-dialog modal-xl">
                        <div class="modal-content" style="width:85%">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Pallet Details</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="rackTable">

                                </div>
                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="btnPlacePopup2" data-rackid="">Place</button>
                            </div> -->
                        </div>
                    </div>
                </div>


            </div>

            <div>

            </div>

            <?php include_once ROOT . 'includes/footer.php' ?>

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?php include_once ROOT . 'includes/common-js.php' ?>
    <?php include_once WORKING_MODULE . 'modalSupplier.php' ?>
    <?php include_once ROOT . '/includes/app-js.php' ?>

    <script>
    const observer = new MutationObserver(() => {
          const height = document.documentElement.scrollHeight;
          window.parent.postMessage(
            { type: "resize-iframe", height },
            "*"
          );
          console.log("testing" + height);
        });

        observer.observe(document.body, {
          childList: true,
          subtree: true,
        });

        function getStoreIcon() {
            const svgNS = "http://www.w3.org/2000/svg";
            const svg = document.createElementNS(svgNS, "svg");
            svg.setAttribute("class", "dropdown-icon");
            svg.setAttribute("viewBox", "0 0 24 24");
            svg.setAttribute("fill", "none");
            svg.setAttribute("stroke", "#3b82f6");
            svg.setAttribute("stroke-width", "2");
            svg.setAttribute("stroke-linecap", "round");
            svg.setAttribute("stroke-linejoin", "round");

            const path = document.createElementNS(svgNS, "path");
            path.setAttribute("d", "M3 21V9l9-6 9 6v12");

            svg.appendChild(path);
            return svg;
        }

            function getBlockIcon() {
                const svgNS = "http://www.w3.org/2000/svg";
                const svg = document.createElementNS(svgNS, "svg");
                svg.setAttribute("class", "dropdown-icon");
                svg.setAttribute("viewBox", "0 0 24 24");
                svg.setAttribute("fill", "none");
                svg.setAttribute("stroke", "#6366f1");
                svg.setAttribute("stroke-width", "2");
                svg.setAttribute("stroke-linecap", "round");
                svg.setAttribute("stroke-linejoin", "round");

                const rects = [
                {x: 3, y: 3 }, {x: 14, y: 3 },
                {x: 14, y: 14 }, {x: 3, y: 14 }
                ];

                rects.forEach(pos => {
                    const rect = document.createElementNS(svgNS, "rect");
                    rect.setAttribute("x", pos.x);
                    rect.setAttribute("y", pos.y);
                    rect.setAttribute("width", "7");
                    rect.setAttribute("height", "7");
                    svg.appendChild(rect);
                });

                return svg;
            }

            function getFloorIcon() {
                const svgNS = "http://www.w3.org/2000/svg";
                const svg = document.createElementNS(svgNS, "svg");
                svg.setAttribute("class", "dropdown-icon");
                svg.setAttribute("viewBox", "0 0 24 24");
                svg.setAttribute("fill", "none");
                svg.setAttribute("stroke", "#f59e0b");
                svg.setAttribute("stroke-width", "2");
                svg.setAttribute("stroke-linecap", "round");
                svg.setAttribute("stroke-linejoin", "round");

                const path1 = document.createElementNS(svgNS, "path");
                path1.setAttribute("d", "M3 3h18v18H3z");
                const path2 = document.createElementNS(svgNS, "path");
                path2.setAttribute("d", "M3 12h18");

                svg.appendChild(path1);
                svg.appendChild(path2);
                return svg;
            }

            function getRackIcon() {
                const svgNS = "http://www.w3.org/2000/svg";
                const svg = document.createElementNS(svgNS, "svg");
                svg.setAttribute("class", "dropdown-icon");
                svg.setAttribute("viewBox", "0 0 24 24");
                svg.setAttribute("fill", "none");
                svg.setAttribute("stroke", "#10b981");
                svg.setAttribute("stroke-width", "2");
                svg.setAttribute("stroke-linecap", "round");
                svg.setAttribute("stroke-linejoin", "round");

                const path1 = document.createElementNS(svgNS, "path");
                path1.setAttribute("d", "M3 3h18v4H3z");
                const path2 = document.createElementNS(svgNS, "path");
                path2.setAttribute("d", "M3 10h18v11H3z");

                svg.appendChild(path1);
                svg.appendChild(path2);
                return svg;
            }

                function initDropdown(dropdownID) {
                const container = document.getElementById(dropdownID);
                const selected = container.querySelector(".dropdown-selected");
                const list = container.querySelector(".dropdown-list");
                const items = list.querySelectorAll(".dropdown-item");
                const search = list.querySelector(".dropdown-search");
                const hidden = container.querySelector("input[type=hidden]");

                // Toggle dropdown
                selected.addEventListener("click", () => {
                    e.stopPropagation();
                    list.classList.toggle("show");
                    container.classList.toggle("active");
                    search.value = "";
                    filterItems("");
                    search.focus();
                });

                // Select item
                items.forEach(item => {
                    item.addEventListener("click", () => {
                        selected.textContent = item.textContent.trim();
                        hidden.value = item.dataset.value; // set hidden input
                        container.classList.add("has-value");
                        list.classList.remove("show");
                        container.classList.remove("active");

                        // If Store selected, populate Block dynamically

                        if (dropdownID === "dropdownStore") {
                            const blocks = (item.dataset.blockslist || "").split(",").filter(b => b !== "");
                            const blockDropdown = document.getElementById("dropdownBlock");
                            const blockList = blockDropdown.querySelector(".dropdown-list");
                            const blockItemsContainer = blockList;
                            blockItemsContainer.querySelectorAll(".dropdown-item").forEach(i => i.remove());
                            blocks.forEach(block => {
                                const div = document.createElement("div");
                                div.className = "dropdown-item";
                                div.dataset.value = block.BlockID;
                                div.textContent = block.BlockName;
                                blockItemsContainer.appendChild(div);
                            });
                            setTimeout(() => {
                                const blockDropdown2 = document.getElementById("dropdownBlock");
                                const opener = blockDropdown2.querySelector(".dropdown-selected");
                                opener.click();
                            }, 150);

                        }
                    });
                });

                // Filter search
                function filterItems(value) {
                    items.forEach(item => {
                        if (item.textContent.toLowerCase().includes(value.toLowerCase())) {
                            item.style.display = "block";
                        } else {
                            item.style.display = "none";
                        }
                    });
                }

    search.addEventListener("keyup", (e) => {
                    filterItems(e.target.value);
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
        if(!container.contains(e.target)){
                    list.classList.remove("show");
                container.classList.remove("active");
        }
    });
}

                // Initialize dropdowns
                initDropdown("dropdownStore");


                var qParam = "<?php echo $bid ?>";
                var barCodeId = "<?php echo $barCodeId ?>";
                var stockID = "<?php echo $StockID ?>";

                function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerText = message;
                container.appendChild(toast);

        // Remove toast after 3s
        setTimeout(() => {
                    toast.remove();
        }, 3000);
    }

                $(document).on('click', ".divUnFilled .form-check-input", function() {

        if (barCodeId != "") {
                    $('#popupPalletDeails2').modal('show');
                $("#btnPlacePopup2").attr("data-rackid", $(this).attr("data-rackid"));
                $("#btnPlacePopup2").prop('disabled', false);
                window.parent.postMessage(
                  { action: "scrollToTop" },
                  "*"
                );
        } else {
                    alert("Rack empty");
        }

    });





                function populateBlocks(storeItem) {
    if (storeItem instanceof jQuery) storeItem = storeItem[0];
                if (!storeItem || !storeItem.dataset) return;

    const allowedBlocks = (storeItem.dataset.blockslist || "").split(",").filter(b => b !== "");
                if (allowedBlocks.length === 0) return;

                const blockDropdown = document.getElementById("dropdownBlock");
                const blockList = blockDropdown.querySelector(".dropdown-list");
    blockList.querySelectorAll(".dropdown-item").forEach(i => i.remove());

                const blocksList = JSON.parse(document.getElementById("hdnBlocks").value);
    blocksList.forEach(block => {
        if (allowedBlocks.includes(String(block.BlockID))) {
            if(block.BlockName != "Packing Store"){
                const div = document.createElement("div");
                    div.className = "dropdown-item";
                    div.dataset.value = block.BlockID;
                    div.textContent = block.BlockName;
                    blockList.appendChild(div);
                }
        }
    });

    setTimeout(() => {
        const dropdown = document.getElementById("dropdownBlock");
                const opener = dropdown.querySelector(".dropdown-selected");
                if (opener) opener.click();
    }, 500);


}

                function populateFloors(blockItem) {
    if (blockItem instanceof jQuery) blockItem = blockItem[0];
                if (!blockItem || !blockItem.dataset) return;

    const allowedFloors = (blockItem.dataset.floors || "").split(",").filter(f => f !== "");
                if (allowedFloors.length === 0) return;

                const floorDropdown = document.getElementById("dropdownFloor");
                const floorList = floorDropdown.querySelector(".dropdown-list");
    floorList.querySelectorAll(".dropdown-item").forEach(i => i.remove());

                const floorsList = JSON.parse(document.getElementById("hdnFloors").value);

    floorsList.forEach(floor => {
        if (allowedFloors.includes(String(floor.FloorID))) {
            const div = document.createElement("div");
                div.className = "dropdown-item";
                div.dataset.value = floor.FloorID;
                div.textContent = floor.FloorName;
                floorList.appendChild(div);
        }
    });


}


document.addEventListener("DOMContentLoaded", () => {
    const preselectedStoreID = document.getElementById("hiddenStore").value;
                if (preselectedStoreID) {
        const storeItem = Array.from(document.querySelectorAll("#dropdownStore .dropdown-item"))
                               .find(item => item.dataset.value === preselectedStoreID);
                if (storeItem) populateBlocks(storeItem);
    }
});



                $(document).ready(function() {
    var productionPlant = <?= $ProductionPlant ?>;

                if(productionPlant != 0) {
        // Find the store item with matching data-plant
        var storeItem = $("#listStore .dropdown-item").filter(function() {
            return $(this).data("plant") == productionPlant;
        }).first();

                if(storeItem.length) {
                    // Set hidden input
                    $("#hiddenStore").val(storeItem.data("value"));

                // Update visible selected text
                $("#selectedStore").text(storeItem.text().trim());
                $("#dropdownStore").addClass("has-value");

                // Trigger population of Block dropdown (same logic as clicking the item)
                storeItem.click();
        }

                // Disable dropdown if needed
                $("#dropdownStore .dropdown-selected").css("pointer-events", "none"); // or add class "disabled"
                $("#dropdownStore").addClass("disabled");

    } else {
                    // Enable dropdown
                    $("#dropdownStore .dropdown-selected").css("pointer-events", "auto");
                $("#hiddenStore").val("");
                $("#selectedStore").text("Select Store");
    }
});

    document.addEventListener("DOMContentLoaded", () => {
                    // Generic dropdown toggle
                    document.querySelectorAll(".dropdown-container").forEach(container => {
                        const selected = container.querySelector(".dropdown-selected");
                        const list = container.querySelector(".dropdown-list");
                        const search = list.querySelector(".dropdown-search");

                        selected.addEventListener("click", () => {
                            list.classList.toggle("show");
                            container.classList.toggle("active");
                            search.value = "";
                            filterItems(search.value, list);
                            search.focus();
                        });

                        // Search filter
                        search.addEventListener("keyup", e => filterItems(e.target.value, list));
                    });




                function filterItems(value, list){
                    list.querySelectorAll(".dropdown-item").forEach(item => {
                        if (item.textContent.toLowerCase().includes(value.toLowerCase())) {
                            item.style.display = "block";
                        } else item.style.display = "none";
                    });
    }

    // -----------------------
    // STORE → BLOCK
    // -----------------------
    document.getElementById("listStore").addEventListener("click", e => {
        const item = e.target.closest(".dropdown-item");
                if(!item) return;

                const container = document.getElementById("dropdownStore");
                const selected = container.querySelector(".dropdown-selected");
                const hidden = container.querySelector("input[type=hidden]");

                hidden.value = item.dataset.value;
                selected.textContent = "";
                selected.appendChild(getStoreIcon());
                selected.appendChild(document.createTextNode(item.textContent.trim()));
                container.classList.add("has-value");
                container.querySelector(".dropdown-list").classList.remove("show");
                container.classList.remove("active");

                // Populate Blocks
                const blocksList = JSON.parse(document.getElementById("hdnBlocks").value);
                const blockDropdown = document.getElementById("dropdownBlock");
                const blockList = blockDropdown.querySelector(".dropdown-list");

        // Clear old items (except search)
        blockList.querySelectorAll(".dropdown-item").forEach(i => i.remove());

        const selectedBlocks = (item.dataset.blockslist || "").split(",").filter(b => b !== "");
        blocksList.forEach(b => {
            if (selectedBlocks.includes(String(b.BlockID))) {
                if(b.BlockName != "Packing Store"){
                    const div = document.createElement("div");
                    div.className = "dropdown-item";
                    div.dataset.value = b.BlockID;
                    div.dataset.floors = b.Floors;
                    div.textContent = b.BlockName;
                    blockList.appendChild(div);
                }
            }
        });


                // Reset Block selection
                //blockDropdown.querySelector(".dropdown-selected").textContent = "Select Block";
                setDropdownSelected("dropdownBlock", getBlockIcon, "Select Block");
                blockDropdown.querySelector("input[type=hidden]").value = "";

                // Reset Floor and Rack
                resetDropdown("dropdownFloor");
                resetDropdown("dropdownRack");
    });

                // -----------------------
                // BLOCK → FLOOR
                // -----------------------
                document.getElementById("listBlock").onclick = async function (e) {
    const item = e.target.closest(".dropdown-item");
                if (!item) return;

                handleBlockSelect(item);
};
                function setDropdownSelected(dropdownID, getIconFunc, text) {
    const container = document.getElementById(dropdownID);
                if (!container) return;
                const selected = container.querySelector(".dropdown-selected");

                // Clear existing content
                selected.textContent = "";

                // Append icon
                selected.appendChild(getIconFunc());

                // Append text
                selected.appendChild(document.createTextNode(text));

}

                function handleBlockSelect(item) {
    const container = document.getElementById("dropdownBlock");
                const selected = container.querySelector(".dropdown-selected");
                const hidden = container.querySelector("input[type=hidden]");

                hidden.value = item.dataset.value;
                //selected.textContent = item.textContent.trim();
                selected.textContent = "";  // Clear previous content

                // Append the SVG icon:
                selected.appendChild(getBlockIcon());

                // Append the selected text:
                selected.appendChild(document.createTextNode(item.textContent.trim()));

                container.classList.add("has-value");
                container.querySelector(".dropdown-list").classList.remove("show");
                container.classList.remove("active");
setTimeout(() => {
        const floorContainer = document.getElementById("dropdownFloor");
                const opener = floorContainer.querySelector(".dropdown-selected");
                opener.click();
    }, 150);
    //populateFloorsFromBlock(item);

}

                document.getElementById("listFloor").onclick = async function (e) {
    const item = e.target.closest(".dropdown-item");
                if (!item) return;

                handleFloorSelect(item);
};

                function handleFloorSelect(item) {
    const container = document.getElementById("dropdownFloor");
                const selected = container.querySelector(".dropdown-selected");
                const hidden = container.querySelector("input[type=hidden]");

                hidden.value = item.dataset.value;
                selected.textContent = "";
                selected.appendChild(getFloorIcon());
                selected.appendChild(document.createTextNode(item.textContent.trim()));
                container.classList.add("has-value");
                container.querySelector(".dropdown-list").classList.remove("show");
                container.classList.remove("active");

    //populateFloorsFromBlock(item);
}

                document.getElementById("listRack").onclick = async function (e) {
    const item = e.target.closest(".dropdown-item");
                if (!item) return;

                handleRackSelect(item);
};

                function handleRackSelect(item) {
    const container = document.getElementById("dropdownRack");
                const selected = container.querySelector(".dropdown-selected");
                const hidden = container.querySelector("input[type=hidden]");

                hidden.value = item.dataset.value;
                selected.textContent = "";
                selected.appendChild(getRackIcon());
                selected.appendChild(document.createTextNode(item.textContent.trim()));
                container.classList.add("has-value");
                container.querySelector(".dropdown-list").classList.remove("show");
                container.classList.remove("active");

    //populateFloorsFromBlock(item);
}


                // -----------------------
                // FLOOR → RACK
                // -----------------------


                // Reset a dropdown

                function resetDropdown(dropdownID){
        const dropdown = document.getElementById(dropdownID);
                dropdown.querySelector("input[type=hidden]").value = "";
        dropdown.querySelector(".dropdown-list").querySelectorAll(".dropdown-item").forEach(i => i.remove());

                // Set default text + icon
                switch(dropdownID) {
            case "dropdownStore":
                setDropdownSelected(dropdownID, getStoreIcon, "Select Store");
                break;
                case "dropdownBlock":
                setDropdownSelected(dropdownID, getBlockIcon, "Select Block");
                break;
                case "dropdownFloor":
                setDropdownSelected(dropdownID, getFloorIcon, "Select Floor");
                break;
                case "dropdownRack":
                setDropdownSelected(dropdownID, getRackIcon, "Select Rack");
                break;
        }
    }


    // Close dropdown if click outside
    document.addEventListener("click", e => {
                    document.querySelectorAll(".dropdown-container").forEach(container => {
                        if (!container.contains(e.target)) {
                            container.querySelector(".dropdown-list").classList.remove("show");
                            container.classList.remove("active");
                        }
                    });
    });
});


                function rackDetails(rack){
                    $.ajax({
                        url: 'Ajax/AjaxViewStoreLayout.php',
                        type: 'POST',
                        data: {
                            methodType: 'rackDetails',
                            rackNo: rack
                        },
                        success: function (data) {
                            $("#rackTable").html(data);
                            return false;
                        },
                    });
    }

                // Durga Prasad 09-02-2024
                $(document).on('click', '.rackno', function() {

        var rack = $(this).data('rackid');
                console.log('Hello, RackID = ' + rack);
                $.ajax({
                    url: 'Ajax/AjaxViewStoreLayout.php',
                type: 'POST',
                data: {
                    methodType: 'rackDetails',
                rackNo: rack
            },
                success: function(data) {
                    $("#rackTable").html(data);
                return false;
            },
        });
    });
                // Durga Prasad 09-02-2024
                $(document).on('click', ".divFilled .form-check-input", function() {

        if (barCodeId != "") {
                    alert("Rack filled");
            //showToast("Rack filled", "info");
        } else {
                    $('#popupPalletDeails2').modal('show');
                $("#btnPlacePopup2").attr("data-rackid", $(this).attr("data-rackid"));
                $("#btnPlacePopup2").prop('disabled', true);
                window.parent.postMessage(
                  { action: "scrollToTop" },
                  "*"
                );
        }
    });


    // $(document).on("click", "#btnSubmit", function() {
                    //     if($("#drpNoFloors").val() != "") {
                    //         $.ajax({
                    //             type: 'POST',
                    //             url: 'Ajax/AjaxViewStoreLayout.php',
                    //             method: "post",
                    //             data: {
                    //                 methodType: "search",
                    //                 storeID: $("#drpStoreID").val(),
                    //                 blockID: $("#drpBlockID").val(),
                    //                 floorsID: $("#drpNoFloors").val(),
                    //                 rackName: $("#drpRackName").val(),
                    //                 qparam: qParam
                    //             },
                    //             success: function(data) {
                    //                 $("#divStoreLayout").html(data);
                    //                 // jsonObject();
                    //             }
                    //         });
                    //     } else {
                    //         alert("please select Floors");
                    //     }
                    // });

                    function initDropdownHiddenValues() {
                        // Store Dropdown
                        document.getElementById("dropdownStore").addEventListener("click", function (e) {
                            const item = e.target.closest(".dropdown-item");
                            if (!item) return;

                            // Set hidden value
                            document.getElementById("hiddenStore").value = item.dataset.value;

                            // Trigger population of next dropdown
                            populateBlocks(item.dataset.blockslist);
                        });

                        // Block Dropdown
                        document.getElementById("dropdownBlock").addEventListener("click", function (e) {
                            const item = e.target.closest(".dropdown-item");
                            if (!item) return;

                            document.getElementById("hiddenBlock").value = item.dataset.value;

                            // Trigger population of floors based on this block
                            populateFloors(item.dataset.floors);
                        });

                        // Floor Dropdown
                        document.getElementById("dropdownFloor").addEventListener("click", function (e) {
                            const item = e.target.closest(".dropdown-item");
                            if (!item) return;

                            document.getElementById("hiddenFloor").value = item.dataset.value;

                            // Trigger rack dropdown population via AJAX
                            loadRacks(item.dataset.value);
                        });

                        // Rack Dropdown
                        document.getElementById("dropdownRack").addEventListener("click", function (e) {
                            const item = e.target.closest(".dropdown-item");
                            if (!item) return;

                            document.getElementById("hiddenRack").value = item.dataset.value;
                        });

                    }

initDropdownHiddenValues();

                $("#frmCreateStoreLayout").validate({

                    onkeyup: false,
                onfocusout: false,

                ignore: "", // we want hidden inputs validated

                rules: {
                    drpStoreID: {required: true },
                drpBlockID: {required: true },
                drpNoFloors: {required: true }
    },

                errorPlacement: function(error, element) {
        // Don't insert default error labels
        return false;
    },

                highlight: function(element) {
                    // Add error class to the visible dropdown container
                    $(element).closest(".dropdown-container").addClass("input-error");
    },

                unhighlight: function(element) {
                    $(element).closest(".dropdown-container").removeClass("input-error");
    },

                invalidHandler: function(form, validator) {
        if (validator.numberOfInvalids()) {
                    // Focus the first invalid hidden input's dropdown
                    $(validator.errorList[0].element).closest(".dropdown-container").find(".dropdown-selected").focus();
        }
    },


                submitHandler: function() {
                    // All fields are valid, run AJAX
                    $.ajax({
                        method: "POST",
                        url: 'Ajax/AjaxViewStoreLayout.php',
                        data: {
                            methodType: "search",
                            storeID: $("#hiddenStore").val(),
                            blockID: $("#hiddenBlock").val(),
                            floorsID: $("#hiddenFloor").val(),
                            rackName: $("#hiddenRack").val(),
                            qparam: qParam
                        },
                        success: function (data) {
                            $("#divStoreLayout").html(data);
                        }
                    });
                return false;
    }
});







                // Blocks and Floors JSON
                var blocksList = JSON.parse($("#hdnBlocks").val());
                var floorList = JSON.parse($("#hdnFloors").val());

                // --- Store Dropdown ---


                // --- Block Dropdown ---
                document.addEventListener("click", function(e){
    if(e.target.closest("#dropdownBlock .dropdown-item")){
        var item = e.target;
                var selectFloors = item.dataset.floors || "";

                // Populate Floor dropdown
                var floorDropdown = document.getElementById("dropdownFloor");
                var selectedFloor = floorDropdown.querySelector(".dropdown-selected");
                var hiddenFloor = document.getElementById("hiddenFloor");
                var floorListDiv = floorDropdown.querySelector(".dropdown-list");
        floorListDiv.querySelectorAll(".dropdown-item").forEach(i => i.remove());
                //selectedFloor.textContent = "Select Floor";
                selectedFloor.textContent = "";  // Clear previous content

                // Append the SVG icon:
                selectedFloor.appendChild(getFloorIcon());

                // Append the selected text:
                selectedFloor.appendChild(document.createTextNode("Select Floor"));
                hiddenFloor.value = "";

                if(selectFloors != ""){
            var selectFloorsAry = selectFloors.split(",");
                floorList.forEach(function(f){
                if(selectFloorsAry.indexOf(f.FloorID) !== -1){
                    var div = document.createElement("div");
                div.className = "dropdown-item";
                div.dataset.value = f.FloorID;
                div.textContent = f.FloorName;
                floorListDiv.appendChild(div);
                }
            });
            //initDropdown("dropdownFloor"); // re-init floor dropdown
        }
    }
});

                // --- Floor Dropdown (AJAX for Racks) ---


                function loadRacks(floorID) {

    const rackDropdown = document.getElementById("dropdownRack");
                if (!rackDropdown) return;

                let rackList = rackDropdown.querySelector(".dropdown-list");

                // If dropdown-list missing, recreate it
                if (!rackList) {
                    rackList = document.createElement("div");
                rackList.className = "dropdown-list";

                const searchInput = document.createElement("input");
                searchInput.type = "text";
                searchInput.className = "dropdown-search";
                searchInput.placeholder = "Search...";
                searchInput.id = "searchRack";

                rackList.appendChild(searchInput);
                rackDropdown.insertBefore(rackList, rackDropdown.querySelector("input[type=hidden]"));
    }

    // Clear old items (keep search box)
    rackList.querySelectorAll(".dropdown-item").forEach(i => i.remove());

                $.ajax({
                    url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: {
                    methodType: "getRackList",
                storeID: $("#hiddenStore").val(),
                blockID: $("#hiddenBlock").val(),
                floorID: floorID
        },
                success: function (response) {

            if (!Array.isArray(response)) return;

                const rackDropdown = document.getElementById("dropdownRack");
                if (!rackDropdown) return;

                const rackList = rackDropdown.querySelector(".dropdown-list");
                if (!rackList) return;

            // Remove old items but KEEP search bar
            rackList.querySelectorAll(".dropdown-item").forEach(el => el.remove());

            // Append the new items
            response.forEach(item => {
                const div = document.createElement("div");
                div.className = "dropdown-item";
                div.dataset.value = item.RackID;
                div.textContent = item.RackName;
                rackList.appendChild(div);
            });
        }
    });
}



                $(document).on("click", "#btnPlacePopup1", function() {

                    $.ajax({
                        type: 'POST',
                        url: 'Ajax/AjaxViewStoreLayout.php',
                        method: "post",
                        data: {
                            methodType: "palletDetails_pop1",
                        },
                        success: function (data) {
                            // $("#divStoreLayout").html(data);
                            // jsonObject();
                        }
                    });

    });

                $(document).on("click", "#btnPlacePopup2", function() {

                    $.ajax({
                        type: 'POST',
                        url: 'Ajax/AjaxViewStoreLayout.php',
                        method: "post",
                        data: {
                            methodType: "placePalette",
                            barCId: barCodeId,
                            stockID: stockID,
                            rackID: $.trim($(this).attr("data-rackid"))
                        },
                        success: function (data) {
                            console.log(data);
                            $('#modalNewPalette').modal('hide');

                            if (data.Error) {
                                alert(data.Error);
                                //showToast(data.Error, "error");
                            } else {
                                alert(data.Msg);
                                //showToast(data.Msg, "success");
                                // $("#btnSubmit").trigger('click');
                                window.location.href = "view-store-layout";
                            }
                            $('#popupPalletDeails2').modal('hide');
                        }
                    });

    });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>



    <script>
        function closeGraph() {
                    document.getElementById("graphModal").style.display = "none";
}

                const chartAnimation = {
                    duration: 1200,
                easing: 'easeInOutQuart'
};


                let chartInstance = null;




                /* ---------------------------------------------------------
   CUSTOM LOG TICK PLUGIN  (MUST BE AT TOP)
--------------------------------------------------------- */
const CustomLogTicks = {
    id: "customLogTicks",

    beforeBuildTicks(axis) {
        if (axis.type !== "logarithmic") return;

        const min = axis.min;
        const max = axis.max;
        const ticks = [];

        const add = (from, to, step) => {
            for (let v = from; v <= to; v += step) {
                if (v >= min && v <= max) ticks.push(v);
            }
        };


        // Custom step logic
        add(1, 10000, 1000);            // 1 → 10k (step 1k)
        add(10000, 100000, 10000);      // 10k → 100k (step 10k)
        add(100000, 10000000, 100000);  // 100k → 10M (step 100k)

        axis.ticks = ticks.map(v => ({ value: v }));
    }
};

Chart.register(CustomLogTicks);
/* ---------------------------------------------------------
   END CUSTOM LOG TICK PLUGIN
--------------------------------------------------------- */





/* ---------------------------------------------------------
   YOUR ORIGINAL FUNCTION (UNCHANGED, except log scale)
--------------------------------------------------------- */
function createChart(title, labels, data, type, ax) {
    const isHorizontal = ax === "y";

    const modal = document.getElementById("graphModal");
    modal.style.display = "block";
    modal.style.backdropFilter = "none";
    modal.style.background = "rgba(0,0,0,0.45)";

    document.getElementById("graphTitle").innerText = title;

    if (chartInstance) chartInstance.destroy();

    const ctx = document.getElementById("graphCanvas");

    const total = data.reduce((a, b) => a + b, 0);

    const colors = [
        "#2563eb", "#10b981", "#f59e0b", "#ef4444",
        "#8b5cf6", "#14b8a6", "#f97316", "#6366f1"
    ];

    const dataset = {
        label: title,
        data,
        backgroundColor: colors.map(c => c + "cc"),
        borderColor: colors,
        borderWidth: 1,
        barPercentage: 0.9,
        categoryPercentage: 0.85,

        datalabels: isHorizontal
            ? {
                enabled: true,
                formatter: (value, ctx) => {
                    const pct = (value / total) * 100;
                    return pct.toFixed(1) > 1 ? pct.toFixed(1) + "%" : "";   // only >= 1%
                },
                color: "#FFF",
                anchor: "end",
                align: "end",
                offset: -50,
                font: { weight: "400", size: 14 },
                clamp: true,
                clip: false,
            }
            : {
                enabled: true,
                anchor: "end",
                align: "top",
                color: "#000",
                offset: 0,
                font: { weight: "bold", size: 12 }
            }
    };

    chartInstance = new Chart(ctx, {
        type,
        data: { labels, datasets: [dataset] },

        plugins: [
            ChartDataLabels,
            {
                id: "valueOutside",
                afterDatasetsDraw(chart) {
                    if (!isHorizontal) return;
                    const ctx = chart.ctx;
                    const meta = chart.getDatasetMeta(0);

                    ctx.save();
                    ctx.fillStyle = "#111";
                    ctx.font = "600 13px sans-serif";

                    meta.data.forEach((bar, i) => {
                        const formatted = data[i].toLocaleString();
                        ctx.fillText(formatted, bar.x + 10, bar.y + 4);
                    });

                    ctx.restore();
                }
            }
        ],

        options: {
            indexAxis: ax,
            responsive: true,

            layout: {
                padding: {
                    right: 100,
                    left: 15,
                    top: 25,
                }
            },

            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "#1e293b",
                    titleColor: "#fff",
                    bodyColor: "#fff",
                    padding: 10,
                    borderRadius: 6
                },
            },

            scales: isHorizontal
                ? {
                    x: {
                        type: "logarithmic",
                        min: Math.max(1, Math.min(...data)),
                        max: Math.max(...data),
                        grid: { display: false },
                        ticks: {
                            color: "#FFF",
                            callback: val => Number(val).toLocaleString()
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            color: "#222",
                            font: { size: 13, weight: 600 },
                            padding: 5
                        }
                    }
                }
                : {
                    x: {
                        ticks: {
                            color: "#000",
                            font: { size: 13, weight: 600 },
                            padding: 5
                        },
                        grid: { color: "#ffffff22", display: true }
                    },
                    y: {
                        ticks: { color: "#fff" },
                        grid: { color: "#ffffff22", display: true }
                    }
                },

            animation: {
                onComplete: () => {
                    window.chartReady = true;
                }
            }
        }
    });

    showPopup();
    setTimeout(() => {
          if (chartInstance) {
            chartInstance.resize();
          }
        }, 100);
}



function graphPackingType(labels, data) {
    window.parent.postMessage(
      { action: "scrollToTop" },
      "*"
    );
    const graphContent = document.querySelector('#graphModal .graph-content');
    graphContent.style.width = window.innerWidth * 0.5 + 'px'; // 50% width

    createChart(
        "Packing Type Stock Availability",
        labels,
        data,
        "bar",
        "y"
    );
}






                function graphCapacity(labels) {
                    window.parent.postMessage(
                      { action: "scrollToTop" },
                      "*"
                    );
    const graphContent = document.querySelector('#graphModal .graph-content');

                // Example: 70% of window width
                graphContent.style.width = window.innerWidth * 0.4 + 'px';

// Optional: update on resize
window.addEventListener('resize', () => {
                    graphContent.style.width = window.innerWidth * 0.4 + 'px';
});

                createChart(
                "Racks Overview",
                ["Total", "Used", "Free"],
                labels,
                "bar",
                "x"
                );
}

                function graphUtilization(labels) {
                    window.parent.postMessage(
                  { action: "scrollToTop" },
                  "*"
                );
    const graphContent = document.querySelector('#graphModal .graph-content');

                // Example: 70% of window width
                graphContent.style.width = window.innerWidth * 0.4 + 'px';

// Optional: update on resize
window.addEventListener('resize', () => {
                    graphContent.style.width = window.innerWidth * 0.4 + 'px';
});

                createChart(
                "Utilization %",
                ["Utilized", "Free"],
                labels,
                "doughnut"
                );
}

                function graphStoredWeight(labels) {
                    window.parent.postMessage(
                  { action: "scrollToTop" },
                  "*"
                );
    const graphContent = document.querySelector('#graphModal .graph-content');

                // Example: 70% of window width
                graphContent.style.width = window.innerWidth * 0.4 + 'px';

// Optional: update on resize
window.addEventListener('resize', () => {
                    graphContent.style.width = window.innerWidth * 0.4 + 'px';
});

                createChart(
                "Stored Weight Breakdown",
                ["Packed Kgs", "Loose Kgs"],
                labels,
                "pie"
                );
}

                function graphVarietyKgs(varieties,kgs) {
                    window.parent.postMessage(
                  { action: "scrollToTop" },
                  "*"
                );
                    let vlen = varieties.length;
                    let vwdt = 0.7;
                    if( vlen > 7 ){
                        vwdt = 0.7;
                    }else{
                        vlen = vlen-1;
                        vwdt = vlen/10;
                    }
                    if( vlen <= 3 ){
                        vwdt = 0.3;
                    }
    const graphContent = document.querySelector('#graphModal .graph-content');

                // Example: 70% of window width
                graphContent.style.width = window.innerWidth * vwdt + 'px';

// Optional: update on resize
window.addEventListener('resize', () => {
                    graphContent.style.width = window.innerWidth * vwdt + 'px';
});

                createChart(
                "Variety Wise Storage (Kgs)",
                varieties,
                kgs,
                "bar",
                "y"
                );
}

                let popupChartInstance;
                function showPopup1() {
    if (!window.chartReady) {
                    requestAnimationFrame(showPopup);
                return;
    }

                const popupCanvasEl = document.getElementById("popupGraphCanvas");
                const popupCtx = popupCanvasEl.getContext("2d");

                if (popupChartInstance) {
                    popupChartInstance.destroy();
    }

                popupChartInstance = new Chart(popupCtx, {
                    type: chartInstance.config.type,
                data: chartInstance.config.data,
                options: chartInstance.config.options
    });

                document.getElementById("canvasPopup").style.display = "none";
}

                function showPopup() {
    if (!window.chartReady) {
                    requestAnimationFrame(showPopup);
                return;
    }

                const popupCanvas = document.getElementById("popupGraphCanvas");
                const ctx = popupCanvas.getContext("2d");

                if (popupChartInstance) {
                    popupChartInstance.destroy();
    }

                // Create new popup chart using same plugins and options
                popupChartInstance = new Chart(ctx, {
                    type: chartInstance.config.type,
                data: chartInstance.config.data,
                options: chartInstance.config.options,
                plugins: chartInstance.config.plugins  // make sure datalabels + custom plugin work
    });

                popupChartInstance.resize();
                popupChartInstance.update();

                document.getElementById("canvasPopup").style.display = "none";
}




                function closePopup() {
    const graphContent = document.querySelector('#graphModal .graph-content');

                // Example: 70% of window width
                graphContent.style.width = window.innerWidth * 0.2 + 'px';

// Optional: update on resize
window.addEventListener('resize', () => {
                    graphContent.style.width = window.innerWidth * 0.2 + 'px';
});
                document.getElementById("canvasPopup").style.display = "none";
}


$(document).on("click", "#btnPlacePopup2", function() {

    });
    $(document).on("click", "#btnPlacePopup2", function() {

        var rackid = $(this).attr("data-rackid");
        <?php
            if(!empty($StoreRackMaxMcs) && !empty($StockID)){?>
                var FlooreRackIdsArr = <?php echo json_encode($FlooreRackIds);?>;
                let IsNotFlooreIds = true;
                if(FlooreRackIdsArr.length !=0){
                    if($.inArray(rackid, FlooreRackIdsArr) === -1){//not in
                        IsNotFlooreIds = true;
                    }else{//in
                        IsNotFlooreIds = false;
                    }
                }
                if(rackid && $('#drpBlockID').val() && IsNotFlooreIds){
                    $.ajax({
                        url: 'Ajax/AjaxInward.php',
                        type: 'POST',
                        data: {
                            methodType: "validateRackId",
                            rackId: $.trim(rackid),
                            StoreRackMaxMcs : '<?php echo $StoreRackMaxMcs;?>',
                            BlockID: $.trim($('#drpBlockID').val()),
                            //storeID: $.trim($("#drpStoreID").val()),
                            StockId: $.trim(stockID)
                        },
                        success: function (res) {
                            if(res.Error == null){
                                PlacePalleBtnClick(rackid);
                            }else{
                                alert(res.Error);
                            }
                        }
                    })
                }else{
                    PlacePalleBtnClick(rackid);
                }
        <?php }else{?>
            PlacePalleBtnClick(rackid);
        <?php }?>

    });
    function PlacePalleBtnClick(Rackid){
        if(Rackid){
            $.ajax({
                type: 'POST',
                url: 'Ajax/AjaxViewStoreLayout.php',
                method: "post",
                data: {
                    methodType: "placePalette",
                    barCId: barCodeId,
                    stockID: stockID,
                    //rackID: $.trim($(this).attr("data-rackid"))
                    rackID: $.trim(Rackid)
                },
                success: function(data) {
                    console.log(data);
                    $('#modalNewPalette').modal('hide');

                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);
                        // $("#btnSubmit").trigger('click');
                        window.location.href = "view-store-layout";
                    }
                    $('#popupPalletDeails2').modal('hide');
                }
            });
        }
    }
    </script>


</html>
