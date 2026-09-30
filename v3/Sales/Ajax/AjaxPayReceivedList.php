<?php
session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ("../RestAPI.php");

$Type = (isset($_POST["Type"]) && !empty($_POST["Type"])) ? trim($_POST["Type"]) : "";

if($Type == "Dashboard") { 

} else {

}

// $Type = trim($_POST["Type"]);
// $Year = trim($_POST["Year"]);
// $Exporter = trim($_POST["Exporter"]); 

// $api = GetForwardContractorList_API;

// $bodyContent = array("BillsType"=>$Type,"Year"=>$Year,"Exporter"=>$Exporter);

// $resGetForwardContractorListAPI = CallAPI($api, "GET", "", $bodyContent);

// $resList = $resGetForwardContractorListAPI->Data->ForwardContractorsList;

// // var_dump($resGetForwardContractsListAPI);

if ($Type == "Dashboard") {
	echo '<table  id="key-datatableFullscreen" data-paging="false" class="table  wrap w-150 table-bordered" >';
} else {
	echo '<table  id="key-datatable" data-paging="false" class="table wrap w-100" >';
}

?>
<!-- <table  id="key-datatable" data-paging="false" class="table wrap w-100" > -->


	<thead class="thead-light">
		<tr>
			<th style="min-width: 46px;">S.No.</th>
			<th style="min-width: 110px;">INV NO	</th>
			<th style="min-width: 110px;">Invoice Date</th>	
			<th style="min-width: 150px;">Transaction Type	</th>
			<th style="min-width: 150px;">Negotiation Date	</th>
			<th style="min-width: 110px;">Bank Ref No	</th>
			<th style="min-width: 110px;">Total Value	</th>
			<th style="min-width: 110px;">Realised Value	</th>
			<th style="min-width: 110px;">Utilized Date1	</th>
			<th style="min-width: 110px;">Utilised Value1	</th>
			<th style="min-width: 110px;">Relaised Date	</th>
			<th style="min-width: 110px;">Rate1	</th>
			<th style="min-width: 110px;">Total Value INR	</th>
			<th style="min-width: 110px;">Realised Date1	</th>
			<th style="min-width: 110px;">Realised Value1	</th>
			<th style="min-width: 150px;">Realised Value INR	</th>
			<th style="min-width: 150px;">Realised Value1 INR	</th>
			<th style="min-width: 110px;">Utilized Date2	</th>
			<th style="min-width: 110px;">Utilised Value2	</th>
			<th style="min-width: 110px;">Rate2	</th>
			<th style="min-width: 150px;">Realised Value2 INR</th>	
			<th style="min-width: 110px;">Utilized Date3	</th>
			<th style="min-width: 110px;">Utilised Value3	</th>
			<th style="min-width: 110px;">Rate3	</th>
			<th style="min-width: 150px;">Realised Value3 INR</th>	
			<th style="min-width: 110px;">Total Utilized	</th>
			<th style="min-width: 150px;">Realised Value INR	</th>
			<th style="min-width: 110px;">Short Excess	</th>
			<th style="min-width: 110px;">Bank charges	</th>
			<th style="min-width: 110px;">FBK Charges	</th>
			<th style="min-width: 110px;">Other Charges	</th>
			<?php if($Type != "Dashboard") {  ?>
				<th style="min-width: 70px;" class="text-center">View	</th>
				<th style="min-width: 70px;">Delete</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php // foreach($resList as $item) { ?>
		
		<tr>
			<td>S.No.</td> <!--S.No. -->
			<td>INV NO</td> <!--INV NO -->
			<td>Invoice Date</td>	<!--Invoice Date -->
			<td>Transaction Type	</td><!--Transaction Type -->
			<td>Negotiation Date	</td><!--Negotiation Date -->
			<td>Bank Ref No	</td><!--Bank Ref No -->
			<td>Total Value	</td><!--Total Value -->
			<td>Realised Value	</td><!--Realised Value -->
			<td>Utilized Date1	</td><!--Utilized Date1 -->
			<td>Utilised Value1	</td><!--Utilised Value1 -->
			<td>Relaised Date	</td><!--Relaised Date -->
			<td>Rate1	</td><!--Rate1 -->
			<td>Total Value INR	</td><!--Total Value INR -->
			<td>Realised Date1	</td><!--Realised Date1 -->
			<td>Realised Value1	</td><!--Realised Value1 -->
			<td>Realised Value INR	</td><!--Realised Value INR -->
			<td>Realised Value1 INR	</td><!--Realised Value1 INR -->
			<td>Utilized Date2	</td><!--Utilized Date2 -->
			<td>Utilised Value2	</td><!--Utilised Value2 -->
			<td>Rate2	</td><!--Rate2 -->
			<td>Realised Value2 INR</td><!--Realised Value2 INR -->	
			<td>Utilized Date3	</td><!--Utilized Date3 -->
			<td>Utilised Value3	</td><!--Utilised Value3 -->
			<td>Rate3	</td><!--Rate3 -->
			<td>Realised Value3 INR</td><!--Realised Value3 INR -->	
			<td>Total Utilized	</td><!--Total Utilized -->
			<td>Realised Value INR	</td><!--Realised Value INR -->
			<td>Short Excess	</td><!--Short Excess -->
			<td>Bank charges	</td><!--Bank charges -->
			<td>FBK Charges	</td><!--FBK Charges -->
			<td>Other Charges	</td><!--Other Charges -->
			<?php if($Type != "Dashboard") {  ?>
				<td class="text-center">
					<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
				</td><!--View -->
				<td class="text-center">
					<button class="btn btn-link text-danger"  data-id="ID">
						<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
					</button>
				</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
				<td class="text-center">
					<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
				</td><!--View -->
				<td class="text-center">
					<button class="btn btn-link text-danger"  data-id="ID">
						<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
					</button>
				</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
					<td class="text-center">
						<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
					</td><!--View -->
					<td class="text-center">
						<button class="btn btn-link text-danger"  data-id="ID">
							<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
						</button>
					</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
					<td class="text-center">
						<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
					</td><!--View -->
					<td class="text-center">
						<button class="btn btn-link text-danger"  data-id="ID">
							<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
						</button>
					</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
					<td class="text-center">
						<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
					</td><!--View -->
					<td class="text-center">
						<button class="btn btn-link text-danger"  data-id="ID">
							<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
						</button>
					</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
					<td class="text-center">
						<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
					</td><!--View -->
					<td class="text-center">
						<button class="btn btn-link text-danger"  data-id="ID">
							<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
						</button>
					</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
					<td class="text-center">
						<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
					</td><!--View -->
					<td class="text-center">
						<button class="btn btn-link text-danger"  data-id="ID">
							<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
						</button>
					</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
					<td class="text-center">
						<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
					</td><!--View -->
					<td class="text-center">
						<button class="btn btn-link text-danger"  data-id="ID">
							<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
						</button>
					</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
					<td class="text-center">
						<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
					</td><!--View -->
					<td class="text-center">
						<button class="btn btn-link text-danger"  data-id="ID">
							<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
						</button>
					</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
					<td class="text-center">
						<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
					</td><!--View -->
					<td class="text-center">
						<button class="btn btn-link text-danger"  data-id="ID">
							<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
						</button>
					</td>
			<?php } ?>
		</tr>
		<tr>
			<td>S.No.</td>
			<td>INV NO	</td>
			<td>Invoice Date</td>	
			<td>Transaction Type	</td>
			<td>Negotiation Date	</td>
			<td>Bank Ref No	</td>
			<td>Total Value	</td>
			<td>Realised Value	</td>
			<td>Utilized Date1	</td>
			<td>Utilised Value1	</td>
			<td>Relaised Date	</td>
			<td>Rate1	</td>
			<td>Total Value INR	</td>
			<td>Realised Date1	</td>
			<td>Realised Value1	</td>
			<td>Realised Value INR	</td>
			<td>Realised Value1 INR	</td>
			<td>Utilized Date2	</td>
			<td>Utilised Value2	</td>
			<td>Rate2	</td>
			<td>Realised Value2 INR</td>	
			<td>Utilized Date3	</td>
			<td>Utilised Value3	</td>
			<td>Rate3	</td>
			<td>Realised Value3 INR</td>	
			<td>Total Utilized	</td>
			<td>Realised Value INR	</td>
			<td>Short Excess	</td>
			<td>Bank charges	</td>
			<td>FBK Charges	</td>
			<td>Otder Charges	</td>
			<?php if ($Type != "Dashboard") { ?>
					<td class="text-center">
						<a href="#" class="mx-2 text-primary" data-insid="2"><i class="mdi mdi-eye font-size-18"></i></a>
					</td><!--View -->
					<td class="text-center">
						<button class="btn btn-link text-danger"  data-id="ID">
							<i class="mdi mdi-trash-can font-size-16" style="padding: 0px 0.75rem;position: relative;top: -8px;"></i>
						</button>
					</td>
			<?php } ?>
		</tr>
		
	<?php // } ?>
	</tbody>
</table>
