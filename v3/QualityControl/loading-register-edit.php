<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "Quality Control";
$PageTitle = "Loading Register Edit";

$id = $_GET['id'];
$master = $iconn->query("SELECT * FROM loading_register_master WHERE id=$id")->fetch_assoc();
//echo "<pre>"; print_r($master); exit;
$details = $iconn->query("SELECT * FROM loading_register_details WHERE master_id='".$master['id']."'");
$subEntries = [];
while ($rowe = $details->fetch_assoc()) {
    $subEntries[$rowe['rec_id']][]=$rowe;
}
$customInvData = $iconn->query("SELECT VehicelNumber,LinerSealNo,CustomSealNo,TotalNetWt FROM `ScrmCustomInvoices` WHERE PiID='".$master['invoice_no']."'")->fetch_assoc();

$getInvQry = "SELECT PoID, InvNumFull FROM `ScrmPIData` where status=5 and PoID='".$master['invoice_no']."'";
$smtInv = $iconn->query($getInvQry);
$rowInvData = $smtInv->fetch_assoc();

$groupedDetails = [];
//$detailsRes = $iconn->query("SELECT * FROM ScrmPoGradeData WHERE InvId=$id");
$detail_query = "SELECT 
    spgd.ID,
    aps.PackingStyle,
    spgd.Grade,
    spgd.Variety,
    spgd.Brand
FROM ScrmPoGradeData spgd
LEFT JOIN AdminPackingStyles aps ON aps.PCKID = spgd.PackingStyleID
WHERE spgd.InvId ='".$master['invoice_no']."'";
$detailsRes = $iconn->query($detail_query);
while ($row = $detailsRes->fetch_assoc()) {
    $groupKey = $row['PackingStyle'] . '|' . $row['Grade'] . '|' . $row['Variety'] . '|' . $row['Brand'] . '|' . $row['ID'];
    $key = $row['ID'];
    $groupedDetails[$groupKey]= isset($subEntries[$key])?$subEntries[$key]:[];
}
//echo "<pre>"; print_r($groupedDetails); exit;
// $cusquery = "SELECT * FROM `ScrmCustomInvoices` where PiID='".$master['invoice_no']."' limit 1";

$cusquery = "
    SELECT ci.*, cd.ConsigneeName, cd.ConsigneeAddress, cd.ConsigneeCountry, cd.ContactPerson, cd.ContactNumber 
    FROM ScrmCustomInvoices ci
    LEFT JOIN ScrmConsigneeDetails cd ON cd.ConID = ci.ExporterID
    WHERE ci.PiID = '".$master['invoice_no']."' 
    LIMIT 1
";
$Stmtcus = $iconn->query($cusquery);

$CustomInvoicesData = [];
while( $RowCusData = $Stmtcus->fetch_array() ){
    $CustomInvoicesData = $RowCusData;  
}

$sqlQuery = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ORDER BY LbuName ASC";
$stmtUsers = $iconn->query($sqlQuery);
$RowUsersLists = [];
while ($RowUsers = $stmtUsers->fetch_array()) {
    $RowUsersLists[] = $RowUsers;
}

?>
<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
</head>

<body data-sidebar="dark">
    <?php include($IncludesPath . "PreLoader.php"); ?>
    <div id="layout-wrapper">
        <?php include($IncludesPath . "topbar.php"); ?>
        <?php include($IncludesPath . "sidebar.php"); ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php include($IncludesPath . "page-title.php"); ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card p-5">
                                <h4>Edit Entries for Invoice: <?php echo $rowInvData['InvNumFull'] ?></h4>
                                <div class="mb-3 row">
                                    <label class="col-sm-2 col-form-label">Container No:</label>
                                    <div class="col-sm-4"><input type="text" readonly class="form-control" value="<?php echo $CustomInvoicesData['ContainerNo'] ?>"></div>
                                    <label class="col-sm-2 col-form-label">Vehicle No:</label>
                                    <div class="col-sm-4"><input type="text" readonly class="form-control" value="<?php echo $CustomInvoicesData['VehicelNumber'] ?>"></div>
                                    <br></br>
                                     <!-- <label class="col-sm-2 col-form-label">Buyer:</label>
                                    <div class="col-sm-4"><input type="text" readonly class="form-control" value="<?php echo $CustomInvoicesData['ConsigneeName'] ?>"></div> -->
                              <label class="col-sm-2 col-form-label">Buyer:</label>
                                <div class="col-sm-4">
                                    <input type="text" readonly class="form-control" name="buyerName" id="buyerName"
                                        value="<?php echo htmlspecialchars($master['buyerName']); ?>">
                                </div>

                                <!-- </div> -->
                                <!-- <div class="mb-3 row"> -->
                                    <label class="col-sm-2 col-form-label">PO No:</label>
                                    <br></br>
                                    <div class="col-sm-4"><input type="text" readonly class="form-control" value="<?php echo $master['po_no'] ?>"></div>
                                    <label class="col-sm-2 col-form-label">Date:</label>
                                    <div class="col-sm-4"><input type="text" readonly class="form-control" value="<?php echo $master['date'] ?>"></div>
                                   
                                   
                                    <!-- <label class="col-sm-2 col-form-label">Remarks:</label>
                                    <div class="col-sm-4"><input type="text" name="remarks" id="remarks" class="form-control" value="<?php echo ($master['remarks']);?>"></div> -->

                                </div>
                                <form id="editEntriesForm">
                                <div class="mb-3 row">                                    
                                        <label for="drpVerifiedBy" class="col-sm-2 col-form-label">Verified By</label>
                                        <div class="col-sm-4">
                                            <select class="drpSelect form-control" name="drpVerifiedBy"
                                                id="drpVerifiedBy" required>
                                                <option value=""> Select</option>                                            
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                            ?>
                                                            <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($master['verified']==$RowUsersList['USER_ID'])?"selected='selected'":''; ?>>
                                                                <?php echo $RowUsersList['USER_NAME']; ?>
                                                            </option>
                                                        <?php } ?>
                                            </select>
                                        </div>                                        
                                        <label for="drpInspectedBy" class="col-sm-2 col-form-label">Inspected By</label>
                                        <div class="col-sm-4">
                                            <select class="drpSelect form-control select2" name="drpInspectedBy" id="drpInspectedBy" required>
                                                <option value=""> Select</option>
                                                <?php foreach ($RowUsersLists as $RowUsersList) {
                                                    ?>
                                                    <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($master['inspected']==$RowUsersList['USER_ID'])?"selected='selected'":''; ?>>
                                                        <?php echo $RowUsersList['USER_NAME']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <br></br>
                                        <!-- <label class="col-sm-2 col-form-label">remarks:</label>
                                    <div class="col-sm-4"><input type="text" class="form-control" value="<?php echo ($master['remarks']);?>"></div> -->

                                     <label class="col-sm-2 col-form-label">Remarks:</label>
                                    <div class="col-sm-4"><input type="text" name="remarks" id="remarks" class="form-control" value="<?php echo ($master['remarks']);?>"></div>

                                    </div>
                                
                                
                                    <input type="hidden" name="master_id" value="<?php echo $id ?>">
                                    <input type="hidden" name="action" value="logdataupdate">
                                    <input type="hidden" name="TotalNetWt" id="TotalNetWt" value="<?php echo $customInvData['TotalNetWt']; ?>">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>SNo</th>
                                                <th>Packing Size</th>
                                                <th>Grade</th>
                                                <th>Variety</th>
                                                <th>Brand</th>
                                                <th>SNo</th>
                                                <th>Lot No</th>
                                                <th>No. of Cases</th>
                                                <th>Batch No</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="entryRows">
                                            <?php 
                                            $sno=0;
                                            $groupIndex = 0;
                                            foreach ($groupedDetails as $variantKey => $rows):
                                                list($PackingStyleID, $Grade, $Variety, $Brand, $Id) = explode('|', $variantKey);
                                                $groupIndex = $Id;
                                                $sno++;
                                            ?>
                                            <tr class="variant-header bg-light">
                                                <td><?php echo $sno;?></td>
                                                <td><input type="text" readonly class="form-control" value="<?php echo $PackingStyleID ?>">
                                                    <input type="hidden" name="variant[<?php echo $groupIndex ?>][PackingStyleID]" value="<?php echo $PackingStyleID ?>">
                                                </td>
                                                <td><input type="text" readonly class="form-control" value="<?php echo $Grade ?>">
                                                    <input type="hidden" name="variant[<?php echo $groupIndex ?>][Grade]" value="<?php echo $Grade ?>">
                                                </td>
                                                <td><input type="text" readonly class="form-control" value="<?php echo $Variety ?>">
                                                    <input type="hidden" name="variant[<?php echo $groupIndex ?>][Variety]" value="<?php echo $Variety ?>">
                                                </td>
                                                <td><input type="text" readonly class="form-control" value="<?php echo $Brand ?>">
                                                    <input type="hidden" name="variant[<?php echo $groupIndex ?>][Brand]" value="<?php echo $Brand ?>">
                                                </td>
                                                <td colspan="5" align="right"><button type="button" class="btn btn-sm btn-secondary addSubEntry" data-group="grp<?php echo $groupIndex ?>">+ Add Entry</button></td>
                                            </tr>
                                            <?php if(isset($rows) && count($rows)>0){
                                                $j=1;
                                                foreach ($rows as $row): ?>
                                            <tr class="sub-entry" data-group="grp<?php echo $groupIndex ?>">
                                                <td colspan="5"></td>
                                                <td class="serial"><?php echo $j++; ?></td>
                                                <td><input type="text" name="lot_no[grp<?php echo $groupIndex ?>][]" class="form-control lot_no" value="<?php echo $row['lot_no'] ?>" required></td>
                                                <td><input type="number" name="no_of_cases[grp<?php echo $groupIndex ?>][]" class="form-control no_of_cases" value="<?php echo $row['no_of_cases'] ?>" required></td>
                                                <td><input type="text" name="batch_no[grp<?php echo $groupIndex ?>][]" class="form-control" value="<?php echo $row['batch_no'] ?>" required>
                                                    <input type="hidden" name="rid[grp<?php echo $groupIndex ?>][]" value="<?php echo $row['id'] ?>">
                                                </td>
                                                <td><!--button type="button" class="btn btn-danger btn-sm removeRow">Remove</button--></td>
                                            </tr>
                                            <?php endforeach; }else{
                                                ?>
                                                <tr class="sub-entry" data-group="grp<?php echo $groupIndex ?>">
                                                <td colspan="5"></td>                                                
                                                <td class="serial">1</td>
                                                <td><input type="text" name="lot_no[grp<?php echo $groupIndex ?>][]" class="form-control batch_no" value="<?php echo $row['lot_no'] ?>" required></td>
                                                <td><input type="number" name="no_of_cases[grp<?php echo $groupIndex ?>][]" class="form-control no_of_cases" value="<?php echo $row['no_of_cases'] ?>" required></td>
                                                <td><input type="text" name="batch_no[grp<?php echo $groupIndex ?>][]" class="form-control" value="<?php echo $row['batch_no'] ?>" required>
                                                    <input type="hidden" name="rid[grp<?php echo $groupIndex ?>][]" value="0">
                                                    </td>
                                                <!--td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td-->
                                            </tr>
                                            <?php } endforeach; ?>
                                        </tbody>
                                    </table>
                                    <button type="submit" class="btn btn-primary">Update Entries</button>
                                    <a href="javascript:window.close();" class="btn btn-secondary">Back</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include($IncludesPath . "vendor-scripts.php"); ?>
        <?php include($IncludesPath . "NewScripts.php"); ?>
        <?php include($IncludesPath . "ShowDataTable.php"); ?>
    </div>
    <script>
        $(function () {            
            $(document).on('click', '.addSubEntry', function () {
                let group = $(this).data('group');                
                let newRow = `<tr class="sub-entry" data-group="${group}">
                    <td colspan="5"></td>
                    <td class="serial"></td>
                    <td><input type="text" name="lot_no[${group}][]" class="form-control lot_no" required></td>
                    <td><input type="number" name="no_of_cases[${group}][]" class="form-control no_of_cases" required></td>
                    <td><input type="text" name="batch_no[${group}][]" class="form-control" required>
                        <input type="hidden" name="rid[${group}][]" class="form-control" required>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td>
                </tr>`;                
                $(`tr[data-group='${group}']:last`).after(newRow);
                updateSerialNumbers(group); 
            });

            $(document).on('click', '.removeRow', function () {
                let group = $(this).closest('tr').data('group');
                $(this).closest('tr').remove();
                updateSerialNumbers(group); 
            });

            function updateSerialNumbers(group) {
                $(`tr.sub-entry[data-group='${group}']`).each(function (index) {
                    $(this).find('.serial').text(index + 1); // Serial starts from 1
                });
            }

            $('#editEntriesForm').on('submit', function (e) {
                e.preventDefault();
                let valid = true;
                let totalCases = 0;
                let requiredCases = parseInt($("#TotalNetWt").val());
                $('.no_of_cases').each(function () {
                    let val = parseInt($(this).val()) || 0;
                    if (val > 200) {
                        alert('No. of cases must be <= 200');
                        $(this).focus();
                        valid = false;
                        return false;
                    }
                    totalCases += val;
                });
                if (!valid) return;
                if (totalCases > requiredCases) {
                    alert('Total cases must be <= ' + requiredCases);
                    return;
                }

                
                $.post('Ajax/Ajax-loading-register.php', $('#editEntriesForm').serialize(), function (res) { 
                    alert('Entries updated successfully');
                    window.location.href = 'loading-register-list';
                }).fail(function () {
                    alert('Update failed.');
                });
            });
        });
    </script>
</body>
</html>