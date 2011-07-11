$(document).ready(function() {
    //show a spinner when adding a friend before page reload
    $("#makefriend-form").submit(function(e) {
        //Prevent the submit event and remain on the screen
        e.preventDefault();
        $("#makefriend-input").attr("disabled", true); 
        
        var orig = $("#makefriend-input").css("background");
        $("#makefriend-input").css("background", "url(\""+urlBase+"extensions/themes/silverblue/images/spinner.gif\") center left no-repeat");
        //make ajax request and show spinner
        var loadurl = urlBase+"dssn/network?friend-input="+$("#makefriend-input").val();
        $("#makefriend-input").val(""); //delete input
        $.ajax(
            {
                url: loadurl, 
                success: function(data){
                    window.location = urlBase+"dssn/network"; //reload
                },
                error: function(data){
                    $("#makefriend-input").css("background", orig);
                    $("#makefriend-input").attr("disabled", false); 
                }
            }
        );

        return false;
    });

    return;
});