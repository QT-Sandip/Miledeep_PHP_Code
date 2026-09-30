<?php

$PageTitle = "Schedule";
$PageTitleFolder = "HRM";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


$SqlShift = "SELECT * FROM HrmShiftDetails";
$query = $iconn->query($SqlShift);
?>

<!DOCTYPE html>

<html lang="en">


<head>

  <?php include($IncludesPath . "title-meta.php"); ?>
  <?php include("Includes/DbLinks.php"); ?>
  <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">


  <?php include($IncludesPath . "PreLoader.php"); ?>


  <!-- Begin page -->
  <div id="layout-wrapper">


    <?php
    include($IncludesPath . "topbar.php");
    include($IncludesPath . "sidebar.php");
    ?>



    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

      <div class="page-content">
        <div class="container-fluid">

          <?php include($IncludesPath . "page-title.php"); ?>

          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body" id="Schedules">
                  <div class="col-lg-12 box-footer" align="center">

                    <a href="CreateShedule.php" class="btn btn-success waves-effect waves-light">Add</a>

                  </div>
                  <table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

                    <thead class="thead-light">
                      <th>Shift Name</th>
                      <th>Time In</th>
                      <th>Time Out</th>
                      <th>Break Start Time</th>
                      <th>Break End Time</th>
                      <th>Tools</th>
                    </thead>

                    <tbody>
                      <?php

                      while ($row = $query->fetch_assoc()) {
                        ?>
                        <tr>
                          <td>
                            <?php echo $row['ShiftName'] ?>
                          </td>
                          <td>
                            <?php echo date('h:i A', strtotime($row['StartTime'])) ?>
                          </td>
                          <td>
                            <?php echo date('h:i A', strtotime($row['EndTime'])) ?>
                          </td>
                          <td>
                            <?php echo date('h:i A', strtotime($row['BreakStartTime'])) ?>
                          </td>
                          <td>
                            <?php echo date('h:i A', strtotime($row['BreakEndTime'])) ?>
                          </td>
                          <td>

                            <a target="" class="mr-3 text-primary"
                              href="CreateShedule.php?ID=<?php echo $row['ShiftID'] ?>" data-toggle="tooltip"
                              data-placement="top" title="" data-original-title="Edit">
                              <i class="mdi mdi-pencil font-size-18"></i> </a>



                            <button class="btn btn-link text-danger Delete" data-id="<?php echo $row['ShiftID']; ?>"
                              data-toggle="tooltip" data-placement="top" title="" value="" data-original-title="Delete"><i
                                class="mdi mdi-trash-can font-size-18"></i></button>

                          </td>
                        </tr>
                        <?php
                      }
                      ?>
                    </tbody>
                  </table>


                </div> <!-- end card body-->
              </div> <!-- end card -->
            </div><!-- end col-->
          </div>
          <!-- end row-->



        </div>
        <!-- End Page-content -->

        <?php include($IncludesPath . "footer.php"); ?>
        <?php include('Includes/ScheduleModal.php'); ?>
      </div>
      <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

  </div>

  <?php include($IncludesPath . "vendor-scripts.php"); ?>
  <?php include("Includes/NewScripts.php"); ?>

  <?php // echo $IncludesPath; 
  ?>



</body>

</html>
<script>
  /*$(document ).ready(function() {
   showData();
});

$(document).on('click', '.Delete', function(e){
    e.preventDefault();
  //console.log('check');

    var Id = $(this).data('id');
    var Type = "DeleteSchedule";
  
    
   $.ajax({
    type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
  dataType: 'json',
    data: {'Id':id,'Type':Type},

    success: function(response){
      //$('#deleteholiday').modal('show');
     var obj = JSON.parse(JSON.stringify(response));
           
         $('#del_attid').val(Id);

    
     if(obj.error == null){
      
     }else{
     }
         

     }
  });

  });
  */
  $(document).ready(function () {
    showData();
  });
  $(document).on('click', '.Delete', function (e) {
    //showData();
    e.preventDefault();
    //alert('hiiiii');

    var Id = $(this).data('id');
    var Type = "DeleteSchedule";

    var r = confirm("Are You Sure You Want To Delete?");
    if (r == true) {

      $.ajax({

        type: 'POST',
        url: 'Ajax/AjDeptDetails.php',
        data: {
          'Id': Id,
          'Type': Type
        },
        success: function (response) {
          var obj = JSON.parse(JSON.stringify(response));

          //showData(data);
          if (obj.error == null) {
            alert("Shift Deleted  Successfully");
            window.location.href = 'Schedule.php';

          } else {
            alert(obj.error);

          }


          //console.log(data);
          //$("#ATT_DATA").html(data);
          //$('#example1').DataTable()

        }
      });
    } else { }
    //getRow(id,type);
  });


  function showData(data) {

    $("#EmpList").html(data);
    var table = $('#key-datatable').DataTable({
      scrollY: "600px",
      scrollX: true,
      //sScrollX: "100%",
      sScrollXInner: "150%",
      // scrollCollapse:true,
      paging: true,
      destroy: true,
      fixedColumns: true,
      bSort: true,
      "bStateSave": true,
      "fnStateSave": function (oSettings, oData) {
        localStorage.setItem('key-datatable', JSON.stringify(oData));
      },
      "fnStateLoad": function (oSettings) {
        return JSON.parse(localStorage.getItem('key-datatable'));
      }
    });
  }
</script>