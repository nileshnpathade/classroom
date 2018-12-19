$(function(){
    $("#black_only, #hide").click(function(e){
        $(this).parents(".modal").find("#hidethis1, #hidethis").toggleClass("hidden");
    });

    $("#equipment_black_only, #hide_equp").click(function(e){
        $(this).parents(".modal").find("#equipment1, #equipment").toggleClass("hidden");
    });
})