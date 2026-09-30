<?php


/*
function base64_url_encode($input) {
 return strtr(base64_encode($input), '+/=', '._-');
}

function base64_url_decode($input) {
 return base64_decode(strtr($input, '._-', '+/='));
}
*/


function urlEncode64($input)
{
	return strtr(base64_encode($input), '+/=', '._-');
}

function urlDecode64($input)
{
	return base64_decode(strtr($input, '._-', '+/='));
}



function getDateFilter($Year, $Month)
{

	$Month = sprintf('%02d', $Month);
	$DateStart = $Year . '-' . $Month . '-01';
	$DateEnd = $Year . '-' . $Month . '-31';
	$DateFilter = "Between '$DateStart' and '$DateEnd'";
	return $DateFilter;
}

function getFinancialYear($FinClsYear)
{



	$DateStart = $FinClsYear . '-04-01';
	$DateEnd = ($FinClsYear + 1) . '-03-31';

	return $DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";

}


function getCurrentFinancialYear()
{

	$CurrentMonth = date('m');
	if ($CurrentMonth > 3) {
		$RunningFinStartYear = date('Y');
	} else {
		$RunningFinStartYear = (date('Y') - 1);
	}

	return $RunningFinStartYear;

}

function getFormatedDate($DATE)
{

	$TIME_STAMP = strtotime($DATE);
	$formated_date = date("d-m-Y", $TIME_STAMP);
	if ($formated_date == '01-01-1970') {
		$formated_date = '';
	}
	return $formated_date;
}

function getFormatedDateNA($DATE)
{

	$TIME_STAMP = strtotime($DATE);
	$formated_date = date("d-m-Y", $TIME_STAMP);
	if ($formated_date == '01-01-1970') {
		$formated_date = 'NA';
	}
	return $formated_date;
}

function getFormatedDateStar($DATE)
{

	$TIME_STAMP = strtotime($DATE);
	$formated_date = date("d-m-Y", $TIME_STAMP);
	if ($formated_date == '01-01-1970') {
		$formated_date = '****';
	}
	return $formated_date;
}
function getFormatedDatelines($DATE)
{

	$TIME_STAMP = strtotime($DATE);
	$formated_date = date("d-m-Y", $TIME_STAMP);
	if ($formated_date == '01-01-1970') {
		$formated_date = '--';
	}
	return $formated_date;
}


function getFormatedDateDMMY($DATE)
{

	$TIME_STAMP = strtotime($DATE);
	$formated_date = date("d-M-Y", $TIME_STAMP);
	if ($DATE == '') {
		$formated_date = '';
	}
	return $formated_date;
}


function getCurrentDateTime()
{
	$TimeFormat = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
	$TimeStamp = $TimeFormat->format("Y-m-d  H:i:s");
	$Year = $TimeFormat->format("Y");
	$AryCurrentDate['TimeStamp'] = $TimeStamp;
	$AryCurrentDate['Year'] = $Year;

	return $AryCurrentDate;
}

function getFormatedDateTime($DATE)
{

	$TIME_STAMP = strtotime($DATE);
	$formated_date = date("d-m-Y H:i:s", $TIME_STAMP);
	return $formated_date;
}

function getFormatedDateTimeEmpty($DATE)
{

	$TIME_STAMP = strtotime($DATE);
	$formated_date = date("d-m-Y H:i:s", $TIME_STAMP);
	if ($formated_date == '01-01-1970 00:00:00') {
		$formated_date = '';
	}
	return $formated_date;
}


function getFormatedFileName($FileName)
{

	$FileName = preg_replace('/\s+/', ' ', $FileName);
	return $FileName;
}


function addDaysToDate($Date, $Days)
{
	$Increment = '+ ' . $Days . ' days';
	$Date = $Date . ' 00:00:00';
	return date('Y-m-d', strtotime($Date . $Increment));

}

function addDaysToCurrentDate($Days)
{

	$AryCurrentDate = getCurrentDateTime();
	$Date = $AryCurrentDate['TimeStamp'];
	$Increment = '+ ' . $Days . ' days';

	return date('Y-m-d', strtotime($Date . $Increment));

}

function backDaysToCurrentDate($Days)
{

	$AryCurrentDate = getCurrentDateTime();
	$Date = $AryCurrentDate['TimeStamp'];
	$Increment = '- ' . $Days . ' days';

	return date('Y-m-d', strtotime($Date . $Increment));

}

function removePondIDFromString($input)
{
	// Use a regular expression to find the last occurrence of any special character
	$output = preg_replace('/[^a-zA-Z0-9]*[a-zA-Z0-9]*$/', '', $input);

	return $output;
}

function splitTracebilityAndPondID($input)
{
	// Regular expression to match the last occurrence of any special character and split the string
	if (preg_match('/^(.*?)([-\/_]+)([a-zA-Z0-9]+)$/', $input, $matches)) {
		return array($matches[1], $matches[3]);
	}

	// If no special character is found, return the original string and an empty string as the second part
	return array($input, '');
}

/*function getUserDetails($UserID){
	include ($_SERVER['DOCUMENT_ROOT']."/SerpDbConfig/config.php");
	$SqlUserDetails="SELECT * from AdminLoginDetails as ALD LEFT join AdminDepartments as  AD on AD.DeptID=ALD.DeptID WHERE  `UserID`='$UserID'"; 
	$StmtUserDetails=$iconn->query($SqlUserDetails);
	$AryUserDetails=$StmtUserDetails->fetch_array();
	
	return $AryUserDetails;
	
}
*/

function getDaysDifference($date1, $date2)
{

	$firstDate = getFormatedDate($date1);
	$secondDate = getFormatedDate($date2);
	// Ensure both dates are valid before proceeding
	if (empty($firstDate) || empty($secondDate)) {
		return '';  // Return empty string if any of the dates are invalid
	}
	try {
		// Create DateTime objects
		$firstDateTime = new DateTime($firstDate);
		$secondDateTime = new DateTime($secondDate);

		// Calculate the difference between the two dates
		$difference = $firstDateTime->diff($secondDateTime);

		// Return the difference in days
		return $difference->days;
	} catch (Exception $e) {
		// Handle exception if DateTime creation fails
		return '';  // Return empty string if there's an issue
	}
}

function prepareDateFilter($Year, $Month)
{
	$DateStart = '';
	$DateEnd = '';
	if ($Month != '') {
		$Month = sprintf('%02d', $Month);
		if ($Month > 3) {
			$DateStart = $Year . '-' . $Month . '-01';
			$DateEnd = ($Year) . '-' . $Month . '-31';
		} else {
			$DateStart = ($Year + 1) . '-' . $Month . '-01';
			$DateEnd = ($Year + 1) . '-' . $Month . '-31';
		}
	} else {
		$DateStart = $Year . '-04-01';
		$DateEnd = ($Year + 1) . '-03-31';
	}

	return " BETWEEN '$DateStart' and '$DateEnd'";
}

function convertValueToIndianFormat($number)
{
	// Convert the number to string format
	$number = (string) $number;

	// Split the number into integer and decimal parts (if any)
	$parts = explode('.', $number);
	$integerPart = $parts[0];
	$decimalPart = isset($parts[1]) ? '.' . $parts[1] : '';

	// Check the length of the integer part
	$length = strlen($integerPart);

	// If the length is less than or equal to 3, no need to add commas
	if ($length <= 3) {
		return $integerPart . $decimalPart;
	}

	// Get the last 3 digits of the integer part
	$lastThreeDigits = substr($integerPart, -3);
	// Get the remaining digits of the integer part
	$remainingDigits = substr($integerPart, 0, $length - 3);

	// Add commas for every 2 digits in the remaining part
	$formatted = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remainingDigits);

	// Combine the formatted remaining digits with the last three digits
	return $formatted . ',' . $lastThreeDigits . $decimalPart;
}

function CalculateWeekoffs($TotalDays)
{
    if ($TotalDays >= 26) {
        return 4;
    } elseif ($TotalDays >= 21) {
        return 3;
    } elseif ($TotalDays >= 14) {
        return 2;
    } elseif ($TotalDays >= 7) {
        return 1;
    } else {
        return 0;
    }
}

function getMonthSegments($CurrentMonth,$CurrentYear,$MonthSegment){
	$selectedMonth = (int)$CurrentMonth;
	$selectedYear = (int)$CurrentYear;

	$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

	$response = [];

	$monthName = date('F', mktime(0, 0, 0, $selectedMonth, 1));

	if($MonthSegment == 1){
		// --- First Half (1–15) ---
		$firstStart = new DateTime("$selectedYear-$selectedMonth-01");
		$firstEnd = new DateTime("$selectedYear-$selectedMonth-15");
		$response = array("StartDate"=>$firstStart->format('Y-m-d'),"EndDate"=>$firstEnd->format('Y-m-d'));		
	}

	if($MonthSegment == 2){
		// --- Second Half (16–end) ---
		$secondStart = new DateTime("$selectedYear-$selectedMonth-16");
		$secondEnd = new DateTime("$selectedYear-$selectedMonth-$daysInMonth");
		$response =  array("StartDate"=>$secondStart->format('Y-m-d'),"EndDate"=>$secondEnd->format('Y-m-d'));		
	}

    return $response;
}

?>