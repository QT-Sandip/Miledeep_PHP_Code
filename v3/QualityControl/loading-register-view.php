

<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "Quality Control";
$PageTitle = "Loading Register View";

$id = ( isset( $_GET['id'] ) && $_GET['id'] )?$_GET['id']:0;
$master = $iconn->query("SELECT * FROM loading_register_master WHERE id=$id")->fetch_assoc();
$details = $iconn->query("SELECT * FROM loading_register_details WHERE master_id=$id order by CAST(lot_no AS UNSIGNED) asc");
$detailslist = array();
while( $row = $details->fetch_assoc() ){
	//$detailslist[$row['rec_id']][] = $row;
	$detailslist[] = $row;
}
$customInvData = $iconn->query("SELECT VehicelNumber,LinerSealNo,CustomSealNo,TotalNetWt FROM `ScrmCustomInvoices` WHERE PiID='".$master['invoice_no']."'")->fetch_assoc();

//get invoice id
$getInvQry = "SELECT PoID, InvNumFull FROM `ScrmPIData` where status=5 and PoID='".$master['invoice_no']."'";
$smtInv = $iconn->query( $getInvQry );
$invData = array();
$rowInvData = $smtInv->fetch_assoc();

//$SqlGetVlist = "SELECT ID,PackingStyleID,Grade,Variety,Brand FROM ScrmPoGradeData WHERE InvId = '".$id."'";
$SqlGetVlist = "SELECT 
    spgd.ID,
    aps.PackingStyle,
    spgd.Grade,
    spgd.Variety,
    spgd.GlazeType,
    spgd.Brand,
	SVTL.ShortName
FROM ScrmPoGradeData spgd
LEFT JOIN AdminPackingStyles aps ON aps.PCKID = spgd.PackingStyleID
LEFT JOIN ScrmPIVarietyList SVTL ON SVTL.VarietyID = spgd.VarietyID
WHERE spgd.InvId = '".$master['invoice_no']."'";
//echo $SqlGetVlist; exit;
$StmtVlist = $iconn->query($SqlGetVlist);
$vllist = array();

while( $RowVlistData = $StmtVlist->fetch_array() ){
	$vllist[$RowVlistData['ID']] = $RowVlistData;	
}

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
    $RowUsersLists[$RowUsers['USER_ID']] = $RowUsers['USER_NAME'];
}
?>
<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
    	tr, td {
    border: 1px solid #000 !important;
}
th {
    border: 1px solid #000 !important;
    color: #FFF !important;
}

thead th {
            position: sticky;
            top: 0;
            background-color: black; /* optional: for contrast */
            color: white;
            z-index: 1; /* ensures it stays above other rows */
        }
    </style>
    

</head>

<body data-sidebar="dark">

    <?php
    include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php include($IncludesPath . "page-title.php"); ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card p-5">                            	
                        		<h4 class="mb-3">Loading Register View - Invoice: <?php echo $rowInvData['InvNumFull'] ?></h4>								    
								    
								    <div class="mb-3">
								        <button class="btn btn-success" id="exportCSV">Export to CSV</button>								        
								        <a href="javascript:window.close();" class="btn btn-secondary">Back</a>
								    </div>
								    
								    <table class="table table-bordered" id="entryTable" border="1" style="table-layout: fixed; width: 100%; border:2px solid #000 !important">
									    <thead>
									        <tr>
									            <td>Container No:</td>
									            <td><?php echo $CustomInvoicesData['ContainerNo'] ?></td>
									            <td colspan="4" align="center"><strong>COASTAL AQUA PVT LTD</strong></td>
									            <td>Seal No:</td>
									            <td><?php echo $CustomInvoicesData['LinerSealNo'].",".$CustomInvoicesData['CustomSealNo'] ?></td>
									        </tr>
									        <tr>
									            <td>Vehicle No:</td>
									            <td><?php echo $CustomInvoicesData['VehicelNumber'] ?></td>
									            <td colspan="4" align="center"><u><strong>LOADING REGISTER</strong></u></td>
									            <td>PO No:</td>
									            <td><?php echo $master['po_no'] ?></td>
									        </tr>
									        <tr>
									            <td>Invoice No:</td>
									            <td><?php echo $rowInvData['InvNumFull'] ?></td>
									            <td colspan="4"></td>
									            <td>Date</td>
									            <td><?php echo $master['date'] ?></td>
									        </tr>
											 <!-- <tr>
									            <td>Buyer</td>
									            <td><?php echo $rowInvData['buyerName'] ?></td>
												<td><?php echo $CustomInvoicesData['ConsigneeName']; ?><br>
									            <td colspan="4"></td>
									           
									        </tr> -->

											<tr>
											<td>Buyer</td>
											<td><?php echo htmlspecialchars($master['buyerName']); ?></td>
											<td colspan="4"></td>
										</tr>
									        <tr style="background-color: #000;color: #FFF !important;">
									            <th style="font-weight: bold;">Sno</th>
									            <th>Packing Size</th>
									            <th>Grade</th>
									            <th>Variety</th>
									            <th>Brand</th>									            
									            				            
									            <th>Lot No</th>									            
									            <th>No. of Cases</th>
									            <th>Batch No</th>
									        </tr>
									    </thead>
<tbody>
									        <?php $i=0; $total_no_of_cases=0;
									        //foreach( $vllist as $id => $vlistdata){ $i++; $j=0; ?>									        	
										        <?php 	
										        //foreach ($detailslist[$vlistdata['ID']] as $row): $j++; 
										        foreach ($detailslist as $row): $j=1; $i++;
										        	$vlistdata = $vllist[$row['rec_id']];
										        	$total_no_of_cases += $row['no_of_cases'];
										        	//echo "<pre>"; print_r($row); exit;
										        	?>
										        <tr>
										            <td style="mso-number-format:'\@'; white-space: normal; word-wrap: break-word;"><?php echo $j==1?$i:""; ?></td>
										            <td style="mso-number-format:'\@'; white-space: normal; word-wrap: break-word;"><?php echo $j==1?$vlistdata['PackingStyle']:"" ?></td>
										            <td style="mso-number-format:'\@'; white-space: normal; word-wrap: break-word;"><?php echo $j==1?$vlistdata['Grade']:"" ?></td>
										            <td style="mso-number-format:'\@'; white-space: normal; word-wrap: break-word;"><?php echo $j==1?$vlistdata['ShortName']:"" ?></td>
										            <td style="mso-number-format:'\@'; white-space: normal; word-wrap: break-word;"><?php echo $j==1?$vlistdata['Brand']:"" ?></td>
										            
										            <td style="mso-number-format:'\@'; white-space: normal; word-wrap: break-word;"><?php echo $row['lot_no'] ?></td>
										            <td style="mso-number-format:'\@'; white-space: normal; word-wrap: break-word;"><?php echo $row['no_of_cases']; ?></td>
										            <td style="mso-number-format:'\@'; white-space: normal; word-wrap: break-word;"><?php echo $row['batch_no'] ?></td>
										        </tr>
										        <?php endforeach; 
										    //} 
										    ?>
										    <tr>
										    	<td></td>
										    	<td></td>
										    	<td></td>
										    	<td></td>
										    	<td></td>
										    	<td>Total</td>
										    	<td><?php echo $total_no_of_cases; ?></td>
										    	<td></td>

										    </tr>
									    </tbody>
										<tfoot>
											<tr>
												<td>Inspected By:</td>
												<td><?php echo $RowUsersLists[$master['inspected']];?></td>
												<td colspan="4"></td>
												<td>Verified By:</td>
												<td><?php echo $RowUsersLists[$master['verified']];?></td>

										
											</tr>
										</tfoot>
										
									</table>
												<td>Remarks:</td>
                                              <td colspan="7"><?php echo htmlspecialchars($master['remarks']); ?></td>
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
		<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

		<script>
			function exportTableToExcel() {
			    const table = document.getElementById("entryTable");

			    // Convert HTML table to SheetJS worksheet
			    const ws = XLSX.utils.table_to_sheet(table, {raw: true});

			    // Get range of worksheet
			    const range = XLSX.utils.decode_range(ws['!ref']);

			    for (let R = range.s.r; R <= range.e.r; ++R) {
			        for (let C = range.s.c; C <= range.e.c; ++C) {
			            const cell_address = { c: C, r: R };
			            const cell_ref = XLSX.utils.encode_cell(cell_address);
			            const cell = ws[cell_ref];
			            if (!cell) continue;

			            // Initialize style
			            if (!cell.s) cell.s = { alignment: {}, font: {} };

			            // Default wrap and vertical alignment
			            cell.s.alignment.wrapText = true;
			            cell.s.alignment.vertical = "top";

			            // If cell contains strong tag (or text was bold in HTML), make it bold and center
			            const cellText = cell.v?.toString().toLowerCase() || '';
			            if (table.rows[R] && table.rows[R].cells[C]) {
			                const htmlContent = table.rows[R].cells[C].innerHTML.toLowerCase();
			                if (htmlContent.includes('<strong') || htmlContent.includes('<b')) {
			                    cell.s.font.bold = true;
			                    cell.s.alignment.horizontal = "center";
			                }
			            }
			        }
			    }

			    // Set column widths (adjust as needed)
			    ws['!cols'] = [
			        { wch: 10 }, { wch: 15 }, { wch: 15 },
			        { wch: 20 }, { wch: 20 }, { wch: 15 }, { wch: 15 }
			    ];

			    // Create workbook and export
			    const wb = XLSX.utils.book_new();
			    XLSX.utils.book_append_sheet(wb, ws, "Loading Register");
			    XLSX.writeFile(wb, "Loading_Register.xlsx");
			}		

		// Export to CSV
		$('#exportCSV').click(function () { 
		    window.location.href="loading-register-reportdownload?id=<?php echo ( isset( $_GET['id'] ) && $_GET['id'] )?$_GET['id']:0 ?>";
			return false;
		});

		// Export to PDF
		$('#exportPDF').click(function () {
		    const { jsPDF } = window.jspdf;
		    const doc = new jsPDF();
		    doc.text("Loading Register - Invoice <?php echo $master['invoice_no'] ?>", 14, 15);
		    doc.autoTable({ html: '#entryTable', startY: 25 });
		    doc.save("LoadingRegister_<?php echo $master['invoice_no'] ?>.pdf");
		});
		</script>
	</body>
	</html>