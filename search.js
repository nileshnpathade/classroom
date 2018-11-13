$(document).ready(function(){
    $("#search").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#myTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
        var rows = $('#myTable tbody tr:visible').length;
        if (rows == 0) {
            $(".nodata").show();
            $(".nodata1").hide();
        } else {
            $(".nodata").hide();
            $(".nodata1").show();
        }
    });
});