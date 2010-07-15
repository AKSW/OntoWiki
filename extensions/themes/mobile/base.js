/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var jQT = new $.jQTouch();

//$("#navigation").bind('pageAnimationEnd', function(e, info){
//   getBase(e);
//});

function getBase(element){
    // select base
    var url = 'http://localhost/ontowiki/model/select/?m=' + $(element).attr('about');
    var title = $(element).text();

    $.get(url, function(){
        $('#nav-title').text(title);
        navigationEvent('reset');

        jQT.goTo("#nav", "slide");
    })
}