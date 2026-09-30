<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "Quality Control";
$PageTitle = "Loading Register";
$FromDate = $_GET['FromDate'];
$ToDate = $_GET['ToDate'];

// Get invoice id
$getInvQry = "SELECT PoID, InvNumFull FROM `ScrmPIData` WHERE status=5 
              AND InvDate BETWEEN '$FromDate' AND '$ToDate' 
              AND PoID NOT IN (SELECT invoice_no FROM loading_register_master)";
$smtInv = $iconn->query($getInvQry);
$invData = [];
while ($rowInvData = $smtInv->fetch_assoc()) {
    $invData[] = $rowInvData;
}

$sqlQuery = "SELECT MIN(LUID) AS USER_ID, LbuName AS USER_NAME 
             FROM `QcLabTechnologist` 
             WHERE LbuStatus=1 
             GROUP BY LbuName 
             ORDER BY LbuName ASC";
$stmtUsers = $iconn->query($sqlQuery);
$RowUsersLists = [];
while ($RowUsers = $stmtUsers->fetch_array()) {
    $RowUsersLists[] = $RowUsers;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
</head>

<body data-sidebar="dark">
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
                                <form id="loadingForm">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Invoice Number</label>
                                            <select class="form-control select2" name="invoice_no" id="invoice_no" required onchange="getInvData(this.value);">
                                                <option value="">Select</option>
                                                <?php foreach ($invData as $RowUsersList) { ?>
                                                    <option value="<?php echo $RowUsersList['PoID']; ?>">
                                                        <?php echo $RowUsersList['InvNumFull']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3"><label>Container Number</label>
                                            <input type="text" class="form-control" name="container_no" id="container_no" placeholder="Container No" required readonly>
                                        </div>
                                        <div class="col-md-3"><label>Vehicle Number</label>
                                            <input type="text" class="form-control" name="vehicle_no" id="vehicle_no" placeholder="Vehicle No" required readonly>
                                        </div>
                                        <div class="col-md-3"><label>Seal Number</label>
                                            <input type="text" class="form-control" name="seal_no" id="seal_no" placeholder="Seal No" readonly>
                                        </div>
                                        <div class="col-md-3"><label>Buyer</label>
                                            <input type="text" class="form-control" name="ConsigneeName" id="ConsigneeName" placeholder="ConsigneeName" required readonly>
                                        </div>
                                        <div class="col-md-3"><label>PO Number</label>
                                            <input type="text" class="form-control" name="po_no" id="po_no" placeholder="PO No" readonly>
                                        </div>
                                        <div class="col-md-3"><label>Loading Date</label>
                                            <input type="date" class="form-control" name="date" required>
                                        </div>

                                        <div class="col-md-3"><label>Inspected By</label>
                                            <select class="drpSelect form-control select2" name="drpInspectedBy" id="drpInspectedBy" required>
                                                <option value="">Select</option>
                                                <?php foreach ($RowUsersLists as $RowUsersList) { ?>
                                                    <option value="<?php echo $RowUsersList['USER_ID']; ?>">
                                                        <?php echo $RowUsersList['USER_NAME']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3"><label for="drpVerifiedBy">Verified By</label>
                                            <select class="drpSelect form-control" name="drpVerifiedBy" id="drpVerifiedBy" required>
                                                <option value="">Select</option>
                                                <?php foreach ($RowUsersLists as $RowUsersList) { ?>
                                                    <option value="<?php echo $RowUsersList['USER_ID']; ?>">
                                                        <?php echo $RowUsersList['USER_NAME']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3"><label>Remarks</label>
                                            <input type="text" class="form-control" name="remarks" id="remarks" placeholder="Remarks">
                                        </div>
                                    </div>

                                    <hr>
                                    <h5>Entries</h5>
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
                                        <tbody id="entryRows"></tbody>
                                    </table>

                                    <input type="hidden" name="action" value="logdata">
                                    <input type="hidden" name="TotalNetWt" id="TotalNetWt" value="0">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include($IncludesPath . "NewScripts.php"); ?>
        <?php include($IncludesPath . "ShowDataTable.php"); ?>
    </div>

    <script>
        function renderTable(records) {
            const tbody = document.querySelector('#entryRows');
            tbody.innerHTML = '';

            const grouped = {};
            records.forEach(record => {
                const key = `${record.PackingStyleID}|${record.Grade}|${record.Variety}|${record.Brand}|${record.ID}`;
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(record);
            });

            let sno = 0;
            Object.entries(grouped).forEach(([key, rows]) => {
                sno++;
                const [PackingStyleID, Grade, Variety, Brand, groupIndex] = key.split('|');

                // Header row
                const headerRow = document.createElement('tr');
                headerRow.classList.add('variant-header', 'bg-light');
                headerRow.innerHTML = `
            <td>${sno}</td>
            <td><input type="text" readonly class="form-control" value="${PackingStyleID}">
                <input type="hidden" name="variant[${groupIndex}][PackingStyleID]" value="${PackingStyleID}">
            </td>
            <td><input type="text" readonly class="form-control" value="${Grade}">
                <input type="hidden" name="variant[${groupIndex}][Grade]" value="${Grade}">
            </td>
            <td><input type="text" readonly class="form-control" value="${Variety}">
                <input type="hidden" name="variant[${groupIndex}][Variety]" value="${Variety}">
            </td>
            <td><input type="text" readonly class="form-control" value="${Brand}">
                <input type="hidden" name="variant[${groupIndex}][Brand]" value="${Brand}">
            </td>
            <td colspan="5" align="right">
                <button type="button" class="btn btn-sm btn-secondary addSubEntry" data-group="grp${groupIndex}">+ Add Entry</button>
            </td>
        `;
                tbody.appendChild(headerRow);

                // Sub-entries
                rows.forEach((record, index) => {
                    const subRow = document.createElement('tr');
                    subRow.classList.add('sub-entry');
                    subRow.setAttribute('data-group', `grp${groupIndex}`);
                    subRow.innerHTML = `
                <td colspan="5"></td>
                <td class="serial">${index+1}</td>
                <td><input name="lot_no[grp${groupIndex}][]" class="form-control" required type="text" value="${record.lot_no || ''}"></td>
                <td><input name="no_of_cases[grp${groupIndex}][]" class="form-control no_of_cases" required type="number" value="${record.no_of_cases || ''}"></td>
                <td><input name="batch_no[grp${groupIndex}][]" class="form-control" required type="text" value="${record.batch_no || ''}">
                    <input type="hidden" name="rid[grp${groupIndex}][]" value="">
                </td>
                <td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td>
            `;
                    tbody.appendChild(subRow);
                });
            });
        }

        $(function() {
            $(document).on('click', '.addSubEntry', function() {
                let group = $(this).data('group');
                let tbody = $('#entryRows');
                let newRow = `<tr class="sub-entry" data-group="${group}">
            <td colspan="5"></td>
            <td class="serial">1</td>
            <td><input name="lot_no[${group}][]" class="form-control" required type="text"></td>
            <td><input type="number" name="no_of_cases[${group}][]" class="form-control no_of_cases" required></td>
            <td><input type="text" name="batch_no[${group}][]" class="form-control" required>
                <input type="hidden" name="rid[${group}][]" value="">
            </td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td>
        </tr>`;
                $(`tr[data-group='${group}']:last`).after(newRow);
                updateSerialNumbers(group);
            });

            $(document).on('click', '.removeRow', function() {
                let group = $(this).closest('tr').data('group');
                $(this).closest('tr').remove();
                updateSerialNumbers(group);
            });

            function updateSerialNumbers(group) {
                $(`tr.sub-entry[data-group='${group}']`).each(function(index) {
                    $(this).find('.serial').text(index + 1);
                });
            }

            $('#loadingForm').on('submit', function(e) {
                e.preventDefault();

                let valid = true;
                let totalCases = 0;
                let requiredCases = parseInt($("#TotalNetWt").val());
                $('.no_of_cases').each(function() {
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

                // Serialize form data
                var formData = $('#loadingForm').serialize();
                console.log("Form data to be sent:", formData);

                $.post('Ajax/Ajax-loading-register.php', formData, function(res) {
                    alert('Entries inserted successfully');
                    window.location.href = 'loading-register-list';
                }).fail(function() {
                    alert('Insert failed.');
                });
            });
        });

        function getInvData(poid) {
            $("#container_no, #vehicle_no, #seal_no, #po_no, #ConsigneeName").val('');
            $.ajax({
                url: 'Ajax/Ajax-loading-register.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    poid: poid,
                    action: "getInvData"
                },
                success: function(response) {
                    $("#container_no").val(response.Data.ContainerNo);
                    $("#vehicle_no").val(response.Data.VehicelNumber);
                    $("#seal_no").val(response.Data.SealNo);
                    $("#po_no").val(response.Data.PoNo);
                    $("#ConsigneeName").val(response.Data.ConsigneeName);
                    $("#TotalNetWt").val(response.Data.TotalNetWt);
                    renderTable(response.Data.vllist || []);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                }
            });
        }
    </script>
</body>

</html>