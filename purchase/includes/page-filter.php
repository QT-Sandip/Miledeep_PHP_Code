<?php
include_once '../../config/global.php';
include_once '../../config/api-call.php';

$api = API_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resSpecies = $resDropdownsAPI->Data->Species;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters; // Purchase Stations / Purchase centers
?>

<div class="row rowFilter">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- <div class="col">
                        <input type="text" class="form-control" aria-label="From Date" placeholder="From Date" onfocus="(this.type='date')" onblur="(this.type='text')">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control" aria-label="To Date"  placeholder="To Date" onfocus="(this.type='date')" onblur="(this.type='text')">
                    </div> -->
                    <?php if (isset($filterPsId) && !empty($filterPsId)) { ?>
                        <div class="col col-md-4">
                            <select class="drpSelect" id="drpPurchaseList" name="drpPurchaseList">
                                <option value="">Select</option>
                                <?php
                                foreach ($resPurchaseCenters as $item)
                                    echo '<option value="' . $item->PurchaseStationID . '">' . $item->StationName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterBookingDate) && !empty($filterBookingDate)) { ?>
                        <div class="col col-md-4">
                            <!-- <label></label> -->
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d') ?>" max="<?php echo date('Y-m-d') ?>" id="txtBookingDate" name="txtBookingDate" />
                        </div>
                    <?php } ?>
                    
                    <input type="button" id="btnSearch" name="btnSearch" class="btn btn-primary" style="max-width:150px" value="Submit" aria-label="Submit">
                    
                </div>
            </div>
        </div>
    </div>                            
</div>