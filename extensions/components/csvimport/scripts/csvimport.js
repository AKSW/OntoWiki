$(document).ready(function () {
    /* functions */
    var _getColor = function () {
        var r = Math.round(Math.random() * (90 - 50) + 50);
        var g = Math.round(Math.random() * (90 - 50) + 50);
        var b = Math.round(Math.random() * (90 - 50) + 50);
        
        return 'rgb(' + r + '%,' + g + '%,' + b + '%)';
    };
    
    var _getURI = function (name) {
        var URI = defaultGraph + name.replace(/[^A-Za-z0-9_-]/, '');
        return URI;
    };
    
    /* vars */
    var currentColor;
    var dimensions = {};
    var currentDimension;
    
    $('table.csvimport td').click(function () {
        var id = $(this).attr('id');
        var URI = _getURI(id);
        var ids = id.split('-');
        var row = ids[0].replace('r', '');
        var col = ids[1].replace('c', '');
        
        if (!$(this).hasClass('csv-highlighted')) {
            $(this).data('dimension', null);
            $(this).css('background-color', currentColor);
            $(this).addClass('csv-highlighted');
            
            dimensions[currentDimension]['elements'][URI] = {
                'row': row, 
                'col': col, 
                'label': $.trim($(this).text())
            };
        } else {
            $(this).data('dimension', currentDimension);
            $(this).css('background-color', 'transparent');
            $(this).removeClass('csv-highlighted');
            
            // undefine
            delete dimensions[currentDimension]['elements'][id];
        }
    });
    
    $('#btn-add-dimension').click(function () {
        var name = prompt('Dimension name:');
        var dimensionInfo = {
            color: _getColor(), 
            label: $.trim(name), 
            elements: {}
        };
        var dimensionURI = _getURI(name);
        dimensions[dimensionURI] = dimensionInfo;
        currentDimension = dimensionURI;
        currentColor = dimensionInfo.color;
        $('#csvimport-dimensions').append('<li class="csvDimension" style="background-color:' + currentColor + '">' + name + '</li>');
    });
    
    $('#csvimport-dimensions li').live('click', function () {
        var name = $(this).text();
        var URI = _getURI(name);
        var dimInfo = dimensions[URI];
        currentDimension = URI;
        currentColor = dimInfo.color;
    });
    
    $('#csvimport-dimensions li').live('dblclick', function () {
        var name = $(this).text();
        var URI = _getURI(name);
        var newName = prompt('New name:', name);
        var newURI = _getURI(newName);
        
        var dimInfo = dimensions[URI];
        dimInfo.label = $.trim(newName);
        dimensions[newURI] = dimInfo;
        delete dimensions[URI];
        $(this).text(newName);
    });
    
    $('#extract').click(function () {
        var dimensionString = $.toJSON(dimensions);
        $.post(actionURL, {dimensions: dimensionString}, function () {
            alert('Success');
        });
    });
});