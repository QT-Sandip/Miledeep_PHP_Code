<?php
require_once __DIR__ . '/SaimeReportFacts.php';

/**
 * Shared database reads for SAIME PDF reports.
 *
 * Keep SQL and schema knowledge here; report files should only format data.
 */
final class SaimeReportData
{
    public static function customInvoice($connection, $poId)
    {
        $sql = "SELECT SCV.*, SSD.*, SPC.Name AS PortofDischarge, SPC.CountryCode AS CountryCode, SCV.ContainerNo,
                SC.name AS PortofDischargeCountry, SL.name AS PortOfLoadingCountry,
                SP.Name AS PortOfLoading, SPR.name AS PlaceofReceipt,
                FD.Name AS FinalDestination, SCV.ContainerNo AS ContainerNo,
                FD.CountryCode AS FDCountryCode, FDC.name AS FinalDestinationCountry,
                SNPT.NotifyPartyName AS NotifyPartyName, SNPT.NPartyAddress AS NPartyAddress,
                SOC.CerificationName as CerificationName

            FROM ScrmPoData AS SSD
            LEFT JOIN ScrmCustomInvoices AS SCV ON SCV.PiID = SSD.PoID
            LEFT JOIN ScrmPortCities AS SPC ON SPC.PortId = SSD.PortofDischarge
            LEFT JOIN ScrmPortCities AS SP ON SP.PortId = SSD.PortOfLoading
            LEFT JOIN ScrmPortCities AS SPR ON SPR.PortId = SSD.PlaceofReceipt
            LEFT JOIN ScrmPortCities AS FD ON FD.PortId = SSD.FinalDestination
            LEFT JOIN ScrmCountriesList AS SC ON SC.id = SPC.Country
            LEFT JOIN ScrmCountriesList AS SL ON SL.id = SP.Country
            LEFT JOIN ScrmCountriesList AS FDC ON FDC.id = FD.Country
            LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID = SCV.PiID
            LEFT JOIN ScrmNotifyparty AS SNPT ON SNPT.NotifyPartyID = SSD.NotifyPartyID
            LEFT JOIN ScrmOrderCertification as SOC on SOC.CerificationID =SSD.Certification

            WHERE SSD.PoID = ?
            ORDER BY SCV.CInvID DESC
            LIMIT 1";

        return self::fetchOne($connection, $sql, $poId);
    }

    public static function commercialInvoice($connection, $poId)
    {
        $sql = "SELECT *, SSD.NotifyPartyID AS NPID, SCD.LcNo AS LcNo, SCD.LcDate AS LcDate,
                SPC.Name AS PortofDischarge, SPC.CountryCode AS CountryCode,
                SCD.ComDescriptionSpl, SCD.BankID, SCV.ContainerNo, SCD.Freight,
                SC.name AS PortofDischargeCountry, SL.name AS PortOfLoadingCountry,
                SP.Name AS PortOfLoading, SPR.name AS PlaceofReceipt,
                SCF.name AS FinalDestinationCountry, SPCF.Name AS FinalDestination,
                SPCF.CountryCode AS FinalDestinationCountryCode,
                SNPT.NotifyPartyName AS NotifyPartyName, SNPT.NPartyAddress AS NPartyAddress,
                SNPT.NPartyContactNumber AS NPartyContactNumber, SNPT.NPartyFaxNo AS NPartyFaxNo
            FROM ScrmCustomInvoices AS SCV
            LEFT JOIN ScrmPoData AS SSD ON SSD.PoID = SCV.PiID
            LEFT JOIN ScrmCommercialData AS SCD ON SCD.PoID = SCV.PiID
            LEFT JOIN ScrmPaymentTerms AS SPT ON SCD.ComPaymentTerms = SPT.ID
            LEFT JOIN ScrmPortCities AS SPC ON SPC.PortId = SSD.PortofDischarge
            LEFT JOIN ScrmPortCities AS SP ON SP.PortId = SSD.PortOfLoading
            LEFT JOIN ScrmPortCities AS SPR ON SPR.PortId = SSD.PlaceofReceipt
            LEFT JOIN ScrmPortCities AS SPCF ON SSD.FinalDestination = SPCF.PortId
            LEFT JOIN ScrmCountriesList AS SCF ON SCF.id = SPCF.Country
            LEFT JOIN ScrmNotifyparty AS SNPT ON SSD.NotifyPartyID = SNPT.NotifyPartyID
            LEFT JOIN ScrmCountriesList AS SC ON SC.id = SPC.Country
            LEFT JOIN ScrmCountriesList AS SL ON SL.id = SP.Country
            LEFT JOIN ScrmLogisticsDetails AS SLD ON SLD.PiID = SCV.PiID
            LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID = SCV.PiID
            LEFT JOIN ScrmShippingBillDetails AS SSBD ON SSBD.InvID = SCV.PiID
            LEFT JOIN ScrmImporterOfRecord AS SIR ON SIR.ImporterID = SSD.ImporterID
            WHERE SCV.PiID = ?";

        return self::fetchOne($connection, $sql, $poId);
    }

    public static function packingInvoice($connection, $poId)
    {
        $sql = "SELECT *, SCD.LcNo AS LcNo, SCD.LcDate AS LcDate,
                SPC.Name AS PortofDischarge, SPC.CountryCode AS CountryCode,
                SC.name AS PortofDischargeCountry, SL.name AS PortOfLoadingCountry,
                SPR.name AS PlaceofReceipt, SP.Name AS PortOfLoading, SSD.QtyUnit,
                SCF.name AS FinalDestinationCountry, SPCF.Name AS FinalDestination,
                SPCF.CountryCode AS FinalDestinationCountryCode,
                SNPT.NotifyPartyName AS NotifyPartyName, SNPT.NPartyAddress AS NPartyAddress,
                SNPT.NPartyContactNumber AS NPartyContactNumber, SNPT.NPartyFaxNo AS NPartyFaxNo,
                SCV.ContainerNo, ISF.DataLogger AS DataLogger
            FROM ScrmCustomInvoices AS SCV
            LEFT JOIN ScrmPoData AS SSD ON SSD.PoID = SCV.PiID
            LEFT JOIN ScrmCommercialData AS SCD ON SCD.PoID = SCV.PiID
            LEFT JOIN ScrmNotifyparty AS SNPT ON SSD.NotifyPartyID = SNPT.NotifyPartyID
            LEFT JOIN ScrmPortCities AS SPC ON SPC.PortId = SSD.PortofDischarge
            LEFT JOIN ScrmPortCities AS SP ON SP.PortId = SSD.PortOfLoading
            LEFT JOIN ScrmPortCities AS SPR ON SPR.PortId = SSD.PlaceofReceipt
            LEFT JOIN ScrmPortCities AS SPCF ON SSD.FinalDestination = SPCF.PortId
            LEFT JOIN ScrmCountriesList AS SCF ON SCF.id = SPCF.Country
            LEFT JOIN ScrmCountriesList AS SC ON SC.id = SPC.Country
            LEFT JOIN ScrmCountriesList AS SL ON SL.id = SP.Country
            LEFT JOIN ScrmLogisticsDetails AS SLD ON SLD.PiID = SCV.PiID
            LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID = SCV.PiID
            LEFT JOIN ScrmShippingBillDetails AS SSBD ON SSBD.InvID = SCV.PiID
            WHERE SCV.PiID = ?";

        return self::fetchOne($connection, $sql, $poId);
    }


    /**
     * Resolve the saved liner name, preferring the latest customs invoice and
     * falling back to the ISF shipping line when an invoice liner is absent.
     */
    public static function linerName($connection, $poId)
    {
        $sql = "SELECT COALESCE(CIL.LinerName, ISFL.LinerName, '') AS LinerName
            FROM ScrmPoData AS POD
            LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID = POD.PoID
            LEFT JOIN ScrmLinerMaster AS CIL ON CIL.LinerID = SCI.ShippingLineID
            LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID = POD.PoID
            LEFT JOIN ScrmLinerMaster AS ISFL ON ISFL.LinerID = ISF.ShippingLineID
            WHERE POD.PoID = ?
            ORDER BY SCI.CInvID DESC
            LIMIT 1";

        $row = self::fetchOne($connection, $sql, $poId);
        return isset($row['LinerName']) ? trim((string) $row['LinerName']) : '';
    }
    public static function importerSecurityInvoice($connection, $poId)
    {
        $sql = "SELECT * FROM ScrmCustomInvoices AS SCI
            LEFT JOIN ScrmShipmentDispatchDetails AS SSD ON SSD.PIID = SCI.PiID
            LEFT JOIN ScrmShipmentDetails AS SD ON SD.PiID = SCI.PiID
            LEFT JOIN ScrmLinerMaster AS SLM ON SLM.LinerID = SCI.ShippingLineID
            LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID = SCI.PiID
            LEFT JOIN ScrmLogisticsMaster AS SL ON SL.ID = ISF.ConsolidatorNameAddress
            WHERE SCI.PiID = ?";

        return self::fetchOne($connection, $sql, $poId);
    }

    public static function beaverStreetInvoice($connection, $poId)
    {
        $sql = "SELECT * FROM ScrmCustomInvoices AS SCI
            LEFT JOIN ScrmShipmentDetails AS SSD ON SSD.PiID = SCI.PiID
            LEFT JOIN ScrmShipmentDispatchDetails AS SD ON SD.PIID = SSD.PiID
            WHERE SCI.PiID = ?";

        return self::fetchOne($connection, $sql, $poId);
    }

    public static function proformaProcessor($connection, $poId)
    {
        $sql = "SELECT * FROM ScrmPIData AS SCI
            LEFT JOIN AdminExporterDetails AS SSD ON SSD.ExpID = SCI.ProcessorID
            WHERE SCI.PoID = ?";

        return self::fetchOne($connection, $sql, $poId);
    }
    public static function proformaTax($connection, $poId)
    {
        $sql = "SELECT SPD.OtherCharges, COALESCE(SETR.OtherTax, 0) AS OtherTax
            FROM ScrmPoData AS SPD
            LEFT JOIN ScrmExporterTaxRates AS SETR ON SETR.ExpID = SPD.ExporterID
            WHERE SPD.PoID = ?
            ORDER BY SETR.ExpTRID DESC
            LIMIT 1";
        $row = self::fetchOne($connection, $sql, $poId);
        $percentage = !empty($row['OtherCharges']) ? (float) $row['OtherTax'] : 0.0;

        return array(
            'percentage' => $percentage,
            'multiplier' => $percentage > 0 ? round(1 + ($percentage / 100), 3) : 1.0,
        );
    }

    /**
     * Most recent LUT/IGST reference number for an exporter as of a given date.
     * The client reissues the LUT reference every financial year, so this is
     * looked up by effective date rather than pulled from a single static
     * column on AdminExporterDetails.
     */
    public static function lutReference($connection, $exporterId, $type, $asOfDate)
    {
        // bind_result(), not get_result(): the latter needs the mysqlnd driver,
        // which older PHP/mysqli builds may not have.
        $stmt = $connection->prepare(
            'SELECT ReferenceNumber, FinancialYear, Remarks
                FROM ScrmExporterLutIgstDetails
                WHERE ExporterID = ? AND Type = ? AND Status = 1 AND FromDate <= ?
                ORDER BY FromDate DESC
                LIMIT 1'
        );
        $exporterId = (int) $exporterId;
        $stmt->bind_param('iss', $exporterId, $type, $asOfDate);
        $stmt->execute();
        $stmt->bind_result($referenceNumber, $financialYear, $remarks);

        $row = array();
        if ($stmt->fetch()) {
            $row = array(
                'ReferenceNumber' => $referenceNumber,
                'FinancialYear' => $financialYear,
                'Remarks' => $remarks,
            );
        }
        $stmt->close();

        return $row;
    }

    private static function fetchOne($connection, $sql, $poId)
    {
        // Integer normalization keeps this safe without mysqli_stmt::get_result(),
        // which is unavailable on older PHP installations without mysqlnd.
        $sql = str_replace('?', (string) (int) $poId, $sql);
        $result = $connection->query($sql);
        if ($result === false) {
            throw new RuntimeException('Unable to execute SAIME report query: ' . $connection->error);
        }
        $row = $result->fetch_assoc();

        return $row ?: array();
    }
}

