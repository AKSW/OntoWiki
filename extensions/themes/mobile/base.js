/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var jQT = new $.jQTouch();

var loader_img = "extensions/themes/mobile/image/spinner.gif";
var loader_src = '<small id="loader"><img src="'+loader_img+'"></small>';

//$("#navigation").bind('pageAnimationEnd', function(e, info){
//   getBase(e);
//});

function getBase(element){
    // select base
    var url = 'http://localhost/ontowiki/model/select/?m=' + $(element).attr('about');
    var title = $(element).text();
    $(element).append(loader_src);

    $.get(url, function(){
        $('#nav-title').text(title);
        navigationEvent('reset');

        $(document).bind("navigation.done", function(e, status){
            $("#loader").remove();
            $(document).unbind(e);
            
            jQT.goTo("#nav", "slide");
        });
    })
}

function onNavigationEntryClick(entry){
    $(entry).append(loader_src);

    if($(entry).parent().attr('class') == "arrow"){
        $(document).bind("navigation.done", function(e, status){
            $("#loader").remove();
            $(document).unbind(e);
        });
        navigationEvent('navigateDeeper', $(entry).parent().attr('about'));
        //$("#loader").remove();
    }else{
        url = $(entry).attr('about');
        title = $(entry).text();
        $.get(url, function(data){
            $("#instance-title").text(title);
            $('#instance-content').html(data);
            $("#loader").remove();
            //navigationEvent('reset');

            jQT.goTo("#instance-list", "slide");
        })
        //"http://localhost/ontowiki/model/select/?m=http%3A%2F%2Flocalhost%2Fontowiki%2Fclasstree";
    }
}