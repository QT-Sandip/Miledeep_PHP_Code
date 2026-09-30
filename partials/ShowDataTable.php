<script>

function showData(data,sort){

        var IsSort=true;
            if(sort==false){
                IsSort=false;
            }else{
                IsSort=true;
            }
        $("#ShowDataTable").html(data);

         var table = $('#key-datatable').DataTable( {

        scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: true,
            pageLength: 10,
            lengthMenu: [[10, 30, 50, 100], [10, 30, 50, 100]],
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons: [],

         bSort : IsSort,
          "bStateSave": true,
           "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );


}

</script>
