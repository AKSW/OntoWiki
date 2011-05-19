$(function()
{

    function postToggle(button)
    {
        var name = $(button).parent().attr("id");
        var enabled = $(button).attr("selected");
        $.post(urlBase + "exconf/conf/?name="+name+"&enabled="+enabled,function(data){if(data==""){$("#numEnabled").html(parseInt($("#numEnabled").html())+( enabled == "true" ? 1 : -1));$("#numDisabled").html(parseInt($("#numDisabled").html())+( enabled == "true" ? -1 : 1));}});
    }

    $(".togglebutton").togglebutton(
        {"onEnable":
            postToggle,
         "onDisable":
            postToggle
        }
    );

    $("#show_extension input#viewCompact").change(function()
    {
        if ($(this).is(":checked"))
        {
            $("div.view_extended").addClass("view_compact").removeClass("view_extended");
            $("#extensions li").addClass("compact");
            $("#extensions li a.toggle").addClass('icon-arrow-next').removeClass('icon-arrow-down');
        }
        else
        {
            $("div.view_compact").addClass("view_extended").removeClass("view_compact");
            $("#extensions li").removeClass("compact");
            $("#extensions li a.toggle").addClass('icon-arrow-down').removeClass('icon-arrow-next');
        }
    });

    $("#extensions a.toggle").click(function()
    {
        if ($(this).is('.icon-arrow-down'))
        {
            $(this).parent('li').addClass('compact');
            $(this).addClass('icon-arrow-next').removeClass('icon-arrow-down');
        }
        else
        {
            $(this).parent('li').removeClass('compact');
            $(this).addClass('icon-arrow-down').removeClass('icon-arrow-next');
        }
    });

    $("#show_extension input:radio").change(function()
    {
       $("#show_extension label").removeClass("active");
       switch($("#show_extension input:checked").val()){
             case "all" :
                $("#show_extension label[for=showAll]").addClass("active");
                $("#extensions li").each(function(){
                    $(this).show();
                })
             break;
             case "enabled" :
                $("#show_extension label[for=showEnabled]").addClass("active");
                $("#extensions li").each(function(){if($(this).find(".togglebutton").attr("selected") == "true"){
                    $(this).show();
                } else {
                    $(this).hide();
                }})
             break;
             case "disabled" :
                $("#show_extension label[for=showDisabled]").addClass("active");
                $("#extensions li").each(function(){if($(this).find(".togglebutton").attr("selected") == "true"){
                    $(this).hide();
                } else {
                    $(this).show();
                }})
             break;
         }

         // re-populate the outline
         extensionOutline();

         //fix odd even style
         var even = true;
         $("#extensions li:visible").each(function(){
            if(even){
               $(this).addClass("even").removeClass("odd");
            } else {
                $(this).addClass("odd").removeClass("even");
            }
            even = !even;
         })
    }
    );

});