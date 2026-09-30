<script src="<?php echo $BaseDist ?>assets/libs/jquery/jquery.min.js"></script>
<script src="<?php echo $BaseDist ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $BaseDist ?>assets/libs/metismenu/metisMenu.min.js"></script>
<script src="<?php echo $BaseDist ?>assets/libs/simplebar/simplebar.min.js"></script>
<script src="<?php echo $BaseDist ?>assets/libs/node-waves/waves.min.js"></script>

<script>
    $(document).ajaxStart(function() {
        document.getElementById("preloader").style.display = "block";
        document.getElementById("status").style.display = "block";
    });
    $(document).ajaxComplete(function() {
        document.getElementById("preloader").style.display = "none";
        document.getElementById("status").style.display = "none";
    });
</script>
<script>
    $(document).ready(function() {
        $(".select2-selection").on("focus", function() {
            $(this).parent().parent().prev().select2("open");
        });
    });
</script>

<script>
    // $(document).on("click", "#btnMobileMenu, .btnMenuClose", function() {
    $(document).on("click", "#btnMobileMenu", function() {
        $("#topnav-menu-content").toggle();
        $(".topnav").toggleClass("showMenu");
    })
</script>
<script>
    $('input[type="date"]').keypress(function(e) {
        return false;
    });
</script>
<script>
    $("#btnNotify, #btnNotifyWeb").on("click", function() {
        // $('#modalNotify').modal('show');
        $.ajax({
            url: '../../includes/AjaxNotify.php',
            type: 'POST',            
            success: function (data) {
                $("#accNotifyPanel").html(data);
                $('#modalNotify').modal('show');
                return false;
            }
        });
    });
    $(".btnNotifyClose").on("click", function() {
        $('#modalNotify').modal('hide');
    })


        $(document).on("click", ".acc-header", function() {            
            var $this = $(this); // Cache for efficiency
            var $panel = $this.next(".acc-content");

            if ($panel.is(":visible")) { // If the panel is already open
                $this.parent(".acc-item").removeClass("active"); // Remove "active" class
            } else { // If the panel is closed
                $this.parent(".acc-item").addClass("active").siblings().removeClass("active"); // Handle "active" class
            }


            $(this).next(".acc-content").slideToggle();

            // Toggle icon for the clicked header
            $(this).find("i").toggleClass("fas fa-minus fas fa-plus"); /* Toggle icons */

            // Reset icons for other headers
            $(this).parent(".acc-item").siblings().find(".acc-header i").removeClass("fas fa-minus").addClass("fas fa-plus");

            $(this).parent(".acc-item").siblings().find(".acc-content").slideUp();
        });

</script>