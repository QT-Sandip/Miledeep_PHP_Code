<?php 

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';

//$filterArrivalPlant = "T";
//$filterReceivingPlant = "T";
$filterPlantTwo = "T";
$filterArrivalDateTwo = "T";
//$filterEndDate = "T";
$buttonBottom = "T";
?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . 'includes/head.php' ?>

    </head>

    <?php include_once ROOT . '/includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include_once ROOT . 'includes/menu.php' ?>                   

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include_once ROOT . 'includes/page-header.php' ?>   
                        <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>   

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">

                                        <div id="divDataList">
                                            <!-- -->
                                        </div>

                                    </div>
                                </div>


                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include_once ROOT . 'includes/footer.php' ?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include_once ROOT . 'includes/common-js.php' ?>

        <!-- Required datatable js -->
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <?php include_once ROOT . 'includes/app-js.php' ?>

        <script>
            function fnShowDataTable(GradeingDate = '', Plant = '') {                 
                var table = $('#tblDataTable').DataTable({                    
                    scrollY: "600px",
                    scrollX:true,
                    sScrollXInner: "100%", 
                    paging:false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse:true,
                    dom: 'Blfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'Grading Summery Report',
                            //title : null,
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            },     
                            //messageTop: '\n\n',
                            
                            customize: function (xlsx) {

                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                var totalCols = $('#tblDataTable').DataTable().columns().count();
                                $('row:first c t', sheet).text('GRADING SUMMARY');// override title
                                function getExcelColumnName(colNum) {
                                    let columnName = '';
                                    while (colNum > 0) {
                                        let remainder = (colNum - 1) % 26;
                                        columnName = String.fromCharCode(65 + remainder) + columnName;
                                        colNum = Math.floor((colNum - 1) / 26);
                                    }
                                    return columnName;
                                }

                                var lastColumn = getExcelColumnName(totalCols);

                                // Shift existing rows by 2
                                $('row', sheet).each(function () {
                                    var r = parseInt($(this).attr('r'));
                                    $(this).attr('r', r + 1);
                                });

                                // Shift cell references by 2
                                $('c', sheet).each(function () {
                                    var cellRef = $(this).attr('r');

                                    if (cellRef) {
                                        var col = cellRef.replace(/[0-9]/g, '');
                                        var row = parseInt(cellRef.replace(/[A-Z]/g, ''));

                                        $(this).attr('r', col + (row + 1));
                                    }
                                });

                                // Remove old merges
                                $('mergeCells', sheet).remove();

                                // Calculate split
                                var firstPart  = Math.ceil(totalCols * 0.4);
                                var secondPart = Math.ceil(totalCols * 0.2);
                                var thirdPart  = totalCols - firstPart - secondPart;

                                var firstEnd = getExcelColumnName(firstPart);

                                var secondStart = getExcelColumnName(firstPart + 1);
                                var secondEnd = getExcelColumnName(firstPart + secondPart);

                                var thirdStart = getExcelColumnName(firstPart + secondPart + 1);

                                
                                var DateStr = '';
                                if(GradeingDate){
                                    var date = new Date(GradeingDate);

                                    var day = String(date.getDate()).padStart(2, '0');
                                    var month = String(date.getMonth() + 1).padStart(2, '0');
                                    var year = date.getFullYear();

                                    DateStr =  day + '-' + month + '-' + year;
                                }
                                // Add Row 1 & Row 2
                                $('sheetData', sheet).prepend(
                                    '<row r="2">' +
                                        '<c r="A2" t="inlineStr"><is><t>GRADING SUMMARY</t></is></c>' +
                                        '<c r="' + secondStart + '2" t="inlineStr"><is><t>DATE : ' + DateStr + '</t></is></c>' +
                                        '<c r="' + thirdStart + '2" t="inlineStr"><is><t>PLANT : ' + Plant + '</t></is></c>' +
                                    '</row>'
                                );
                                var clientName = '';
                                <?php 
                                if(!empty($_SESSION['ClientID']) && $_SESSION['ClientID'] == 'CAPLV3'){ ?>
                                    clientName = 'COASTAL AQUA PVT. LTD., KAKINADA';
                               <?php } ?>
                                $('sheetData', sheet).prepend(
                                    '<row r="1">' +
                                        '<c r="A1" t="inlineStr"><is><t>' + clientName + '</t></is></c>' +
                                    '</row>'
                                );
                                var rowCount = $('#tblDataTable').DataTable()
                                                .rows({ search: 'applied' }).count();

                                var footerRow = rowCount + 6;
                                $('sheetData', sheet).append(
                                    '<row r="'+ footerRow +'">' +                                       
                                        '<c r="A'  + footerRow +'" t="inlineStr"><is><t>Grading Sup Sign :</t></is></c>' +
                                    '</row>'+
                                    '<row r="'+ (footerRow+1) +'">' +                                       
                                        '<c r="A'+ (footerRow+1) +'" t="inlineStr"><is><t>Remarks :</t></is></c>' +
                                    '</row>'
                                );
                                


                                // Add merges
                                var mergeCells =
                                    '<mergeCells count="7">' +
                                        '<mergeCell ref="A1:' + lastColumn + '1"/>' +
                                        '<mergeCell ref="A2:' + firstEnd + '2"/>' +
                                        '<mergeCell ref="' + secondStart + '2:' + secondEnd + '2"/>' +
                                        '<mergeCell ref="' + thirdStart + '2:' + lastColumn + '2"/>' +
                                        '<mergeCell ref="A' + footerRow + ':' + lastColumn + footerRow +'"/>' +
                                        '<mergeCell ref="A' + (footerRow+1) + ':' + lastColumn + (footerRow+1) +'"/>' +
                                    '</mergeCells>';

                                $('worksheet', sheet).append(mergeCells);

                                // Center alignment (adjust style id if needed)
                                $('c[r="A1"]', sheet).attr('s', '51');
                                $('c[r="A2"]', sheet).attr('s', '51');
                                $('c[r="' + secondStart + '2"]', sheet).attr('s', '51');
                                $('c[r="' + thirdStart + '2"]', sheet).attr('s', '51');
                            }
                        },                    
                    ],
                    
                });            
            }
           
            function ShowList() {
                $.ajax({
                    url: 'Ajax/ajax-grading-summery-report.php',
                    type: 'POST',
                    data: {
                        Plant: $("#drpPlant").val(),
                        GradingDate: $("#txtArrivalDate").val(),
                        //endDate: $("#txtEndDate").val()
                        drpArrivalPlantTxt : $("#drpPlant option:selected").text() != 'Select' ? $("#drpPlant option:selected").text() : ''
                    },
                    success: function (data) {
                        $("#divDataList").html(data);
                        var drpArrivalPlant = $("#drpPlant option:selected").text() != 'Select' ? $("#drpPlant option:selected").text() : '';
                        fnShowDataTable($("#txtArrivalDate").val(), drpArrivalPlant);
                        return false;
                    }
                });
            }
            $(document).ready(function () {
                $('label[for="txtArrivalDate"]').text('Grading Date');
                //  ShowList();
            });

            $("#btnSearch").on("click", function () {
                // ShowList();
                $StartDate = $("#txtStartDate").val();
                $EndDate = $("#txtEndDate").val();
                if ($StartDate != '') {
                    ShowList();
                } else {
                    alert("Please select Date");
                }
            })

            function FormattedDate(oldformatDate) {
                var dateObject = new Date(oldformatDate);
                var FormattedDate = dateObject.getDate()+"/"+(dateObject.getMonth()+1)+"/"+dateObject.getFullYear();
                return FormattedDate;
            }

        </script>

    </body>
</html>
