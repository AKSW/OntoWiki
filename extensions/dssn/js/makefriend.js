$(document).ready(function() {
    //show a spinner when adding a friend before page reload
    $("#makefriend-form").submit(function(e) {
        //Prevent the submit event and remain on the screen
        e.preventDefault();
        $("#makefriend-input").attr("disabled", true); 
        
        //make ajax request and show spinner
        var orig = $("#makefriend-input").css("background");
        $("#makefriend-input").css("background", "url(\""+urlBase+"extensions/themes/silverblue/images/spinner.gif\") center left no-repeat");
        var loadurl = urlBase+"dssn/addfriend?friendUrl="+$("#makefriend-input").val();
        $("#makefriend-input").val(""); //delete input
        function abort(){
            $("#makefriend-input").css("background", orig); //restore
            $("#makefriend-input").attr("disabled", false); 
        }
        $.ajax(
            {
                url: loadurl, 
                success: function(data){
                    if(data != ""){
                        abort()
                    } else {
                    window.location = urlBase+"dssn/network"; //reload
                    }
                },
                error: function(){
                    abort();
                }
            }
        );

        return false;
    });

    return;
});