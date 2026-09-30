<?php

include_once '../Global/Util.php';

class InvoicesPrintPagesListPopupController
{
	private $conn;
	private $db_table = "ScrmExportAvalReports";

	// DB connection
	public function __construct($db)
	{
		$this->conn = $db;
	}

	public function getInvoicesPrintPagesList($RequestBody)
	{
		$Error = null;
		$Msg = '';
		$InvID = $RequestBody['PoID'];
		$HasSauce = $RequestBody['HasSauce'];
		$ReportCat = $RequestBody['ReportCat'];
		$DocType = $RequestBody['DocType'];

		if ($InvID != '' && $ReportCat != '' && $DocType != '') {

			if ($HasSauce == 'Yes') {
				$SauceQry = ' AND HasSauce IN (1,2)';
			} else {
				$SauceQry = ' AND HasSauce IN (0,2)';
			}

			$SqlFields = 'ID,TableName,QueryID,ReportName,PoID,DisplayParam,OptionType,PrintLink';

			$SqlInvoicesPrintsListQuery = "SELECT $SqlFields FROM " . $this->db_table .
			" where ReportType='$DocType' and ReportCat='$ReportCat' and OptionType !=1 and ReportStatus=1
			$SauceQry Order by SortOrder asc";
			$StmtInvoicesPrintsList = $this->conn->prepare($SqlInvoicesPrintsListQuery);
			$StmtInvoicesPrintsList->execute();


			while ($RowAvalRep = $StmtInvoicesPrintsList->fetch(PDO::FETCH_ASSOC)) {

				$TableName = $RowAvalRep['TableName'];
				$QueryID = $RowAvalRep['QueryID'];
				$PoID = $RowAvalRep['PoID'];
				$ReportName = $RowAvalRep['ReportName'];
				$DisplayId = $RowAvalRep['DisplayParam'];
				$OptionType = $RowAvalRep['OptionType'];

				$SqlReport = "select * from $TableName where $PoID='$InvID'";

				$StmtReports = $this->conn->prepare($SqlReport);
				$StmtReports->execute();
				while ($RowRep = $StmtReports->fetch(PDO::FETCH_ASSOC)) {

					$QueryParamVal = $RowRep["$QueryID"];
					if ($DisplayId != '') {
						$DisplayName = $RowRep["$DisplayId"];
					} else {
						$DisplayName = '';
					}

					$RowAval['Count'] = 1;

					if ($DisplayName != '') {
						$DisplayName = ' (' . $DisplayName . ')';
					}



					if ($RowAval['Count'] > 0) {
						$Url = "Reports/" . $RowAvalRep['PrintLink'] . "id=" . $InvID . "&ID=" . $InvID;
					}


					$ArrayInvoicesPrintsList['ReportName'] = $ReportName . $DisplayName;
					$ArrayInvoicesPrintsList['Url'] = $Url;

					$AryRowAvalreports[] = $ArrayInvoicesPrintsList;
				}
			}

			$data['AvailableReports'] = $AryRowAvalreports;
			return $data;

			// if ($StmtInvoicesPrintsList) {
			// 	$data['Error'] = null;
			// 	$data['Msg'] = "Success";
			// 	$records = array('Data' => $data,);
			// 	$this->response($this->json($records), 200);
			// } else {
			// 	$data['Error'] = "Unable To Fetch Data";
			// 	$records = array('Data' => $data);
			// 	$this->response($this->json($records), 200);
			// }

			/* $sqlQuery = "SELECT BuyerID,BuyerName,BuyerCode,BuyerAddress,BuyerCountry,BuyerEmailAddress FROM " . $this->db_table . " where Status=1";
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$Data[] = $dataRow;
			}
			return $Data; */
		} else {
			$data['Error'] = "Unable To Fetch Data";
		}
	}
}
