
function getKBList(){
    $.mobile.pageLoading();
    $.get(urlBase+"module/get?name=modellist&json=true", function(data){
        $.mobile.pageLoading(true);
        
        var objdata = $.parseJSON(data);
        objdata = objdata["data"];
        
        $("#kbTemplate").tmpl(objdata).appendTo("#kb-listholder");
        $('#kb-listholder').listview('refresh');
        
        console.log(objdata);
    });
}
