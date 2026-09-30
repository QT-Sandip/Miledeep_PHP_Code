<?php

include_once '../Global/Util.php';
class UtilController
{

	private $conn;



	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
	}
	// GET ALL

	function getFormatedDate($RawDate)
	{
		// $RawDate='';
		$RawDate = date("d-m-Y", strtotime($RawDate));

		if ($RawDate == '01-01-1970') {
			$RawDate = 'NA';
		}

		return $RawDate;
	}
	function getFormatedDateEmpty($RawDate)
	{
		// $RawDate='';
		$RawDate = date("d-m-Y", strtotime($RawDate));

		if ($RawDate == '01-01-1970') {
			$RawDate = '';
		}

		return $RawDate;
	}
	function getFormatedDateLines($RawDate)
	{
		// $RawDate='';
		$RawDate = date("d-m-Y", strtotime($RawDate));

		if ($RawDate == '01-01-1970') {
			$RawDate = '--';
		}

		return $RawDate;
	}


	function getDayCodeDate($Day_Code)
	{
		//$Day_Code='9H07';
		$D_C_S = str_split($Day_Code);

		$Year = $D_C_S[0];
		$Month = $D_C_S[1];
		$Day = $D_C_S[2] . $D_C_S[3];

		$Date = '';
		if ($Year != '') {
			$Y = '202' . $Year;
		}
		$monthMapping = [
			'A' => '01',
			'B' => '02',
			'C' => '03',
			'D' => '04',
			'E' => '05',
			'F' => '06',
			'G' => '07',
			'H' => '08',
			'J' => '09',
			'K' => '10',
			'L' => '11',
			'M' => '12'
		];

		$Month = strtoupper($Month);
		$M = $monthMapping[$Month];
		return $Day . '-' . $M . '-' . $Y;
	}

	function getDaycodeofDate($date)
	{

		$year = date('Y', strtotime($date)) % 10; // Single digit of the year
		$monthCode = chr(64 + date('n', strtotime($date))); // Convert month to A-L (1=A, 2=B, ...)
		$day = date('d', strtotime($date)); // Day of the month in two digits


		$month = date('n', strtotime($date));
		if ($month >= 9) {
			$month++; // skip I
		}

		$monthCode = chr(64 + $month);
		$DayCode = $year . $monthCode . $day;
		return $DayCode; // Output: 4K18
	}

	public function getPriceDetails($PoID, $QtyUnit)
	{
		$Qry = "WHERE InvId in ($PoID)";
		$PriceQry = 'Price';
		$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
                sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as FrozenWeightKgs,
                sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as FrozenWeightLbs";

		$sqlQuery = "SELECT sum(Mcs) as MCS,TotalSlabs,InvId,  $sqlExt  ,
			$PriceQry ,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			sum(Mcs*TotalSlabs*Price ) as PriceBags
			FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";

		//echo  $sqlQuery; exit;

		$stmt = $this->conn->prepare($sqlQuery);
		// $stmt->bindParam(':proformaID', $proformaID);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$ItemData['NetWeightKGS'] = $dataRow['NetWgtKgs'];
		$ItemData['NetWeightLBS'] = $dataRow['NetWgtLbs'];
		$ItemData['GrossWeightKGS'] = $dataRow['GrossWeightKgs'];
		$ItemData['GrossWeightLBS'] = $dataRow['GrossWeightLbs'];
		$ItemData['FrozenWeightKgs'] = $dataRow['NetWgtKgs'] - $dataRow['FrozenWeightKgs'];
		$ItemData['FrozenWeightLbs'] = $dataRow['NetWgtLbs'] - $dataRow['FrozenWeightLbs'];
		if ($QtyUnit == 'KGS') {
			$ItemData['InvoiceValue'] = round($dataRow['PriceKgs'], 2);
		} else {
			$ItemData['InvoiceValue'] = round($dataRow['PriceLbs'], 2);
		}

		return $ItemData;
	}
}
