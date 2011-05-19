/*
 * Patternmanager View Javascript
 *
 * @package    
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

// on site load callback
$(document).ready(function () {
    
    $('#patternmanager_view_save_button').hide();
    $('#patternmanager_view_cancel_button').hide();
    $('#patternmanager input').attr('readonly','true');
    $('#patternmanager select').attr('disabled','true');
    $('div#patternmanager fieldset table .icon.icon-add').hide();
    $('div#patternmanager fieldset table .icon.icon-delete').hide();
    $('#buttonAddSubpattern').hide();
    
    $('#patternmanager_view_edit_button').live('click', function (event) {
        $('#buttonAddSubpattern').show();
        $('#patternmanager_view_edit_button').hide();
        $('#patternmanager_view_exportJSON_button').hide();
        $('#patternmanager_view_save_button').show();
        $('#patternmanager_view_cancel_button').show();
        $('#patternmanager input').attr('readonly','');
        $('#patternmanager select').attr('disabled','');
        $('div#patternmanager fieldset table .icon.icon-add').show();
        $('div#patternmanager fieldset table .icon.icon-delete').show();
        return false;
    });
    
    // cancel button to discard changes and refresh the site
    $('#patternmanager_view_cancel_button').live('click', function (event) {
       location.reload();
       return false;
    });
    
    // submit first parent form of save button
    $('#patternmanager_view_save_button').live('click', function (event) {
       if ( $(event.target).parents('form:first').find('#patternUri').val() != '' && confirm("Pattern überschreiben?") ) {
           $(event.target).parents('form:first').submit();
       } else {
           // do nothing
       }
    });
    
    $('.PMcopyVar').live('click', function(event) {
        
		id = $(this).parents('fieldset.eSubpattern:first').attr('id');

		if ($(this).parent().hasClass('contextmenu')) {
		    
		    sp = $(this).children('div').text();
		    self = $(this).parent().find('div:eq(0)').text();
		    replace = $('#patternmanager ' + sp + ' table:eq(0)').clone();
		    if (confirm ('Confirm action: copy vars')) {
		        $('#patternmanager ' + self + ' table:eq(0)').replaceWith(replace);
		    } else {
		        // do nothing
		    }
		    
		    reindexPM();
		    
		} else {
		
		    // create menu for selection
		    id = id.replace('subpattern-','');
    		
		    var other = new Array();
    		
		    $('div#patternmanager fieldset.eSubpattern').each( function (i) {
		        if (i != id && i != 0) {
		            other.push(i);
	            }
		    });
    		
		    //TODO move this to a menu new service
    		cwin = '<div class="contextmenu" style="position:absolute; top:' + (event.pageY+10) + 'px; left:' + (event.pageX+5) + 'px;">';
    		cwin += '<div style="display:none;">#subpattern-' + id + '</div>';
    		for (i in other) {
    		    cwin += '<a class="PMcopyVar">from Subpattern ' + other[i] + '<div style="display:none">#subpattern-' + other[i] + '</div></a><br/>';
    		}
    		cwin += '</div>';
    		
    		if (other.length > 0) {
    		    $('.contextmenu-enhanced').append(cwin);
    		}
		}
		
		return false;
		
	});
});

function addPMPattern(name, desc) {
    
    count = $('div#patternmanager fieldset.eSubpattern').size();
    
    pattern = $('fieldset.eSubpattern:first').clone();
    pattern.attr('id','subpattern-' + count);
    pattern.show();

    $('div#patternmanager fieldset.eComplexPattern').append(pattern);

    var name_prefix = '<a class="icon icon-toggle-on" href="javascript:showPMPattern();"><span>open</span></a><a class="icon icon-toggle-off" href="javascript:hidePMPattern();"><span>close</span></a>';
    var name_suffix = '<a class="icon icon-delete" href="javascript:delPMPattern();" title="Delete this Subpattern"><span>Delete this Subpattern</span></a><a class="icon icon-copy PMcopyVar" title="Copy vars from this Subpattern"><span>Copy vars from this Subpattern</span></a>';

    $('#subpattern-' + count + ' input#patternlabel-').val(name);
    if (name != null) {
        $('#subpattern-' + count + ' legend').html(name_prefix+' Subpattern &quot;'+name+"&quot; "+name_suffix);
    } else {
        $('#subpattern-' + count + ' legend').html(name_prefix+' New Subpattern '+name_suffix);
    }
    $('#subpattern-' + count + ' input#patterndesc-').val(desc);
    
    reindexPM();
    
}

function delPMPattern(id) {
    
    if ( confirm('Confirm action: del pattern') ) {
        $('div#patternmanager > fieldset.eComplexPattern > fieldset#subpattern-'+id).remove();
    } else {
        
    }
    
    reindexPM();
    
}

function hidePMPattern(id) {
    
    $('fieldset#subpattern-' + id + ' a.icon-delete').show();
    $('fieldset#subpattern-' + id + ' a.icon-delete').show();
    
    $('fieldset#subpattern-'+id).addClass("subpattern-hidden");
    
}

function showPMPattern(id) {
    
    $('fieldset#subpattern-'+id).removeClass("subpattern-hidden");
 
    if ( $('#patternmanager_view_edit_button').is(':visible') ) {
        $('fieldset#subpattern-' + id + ' a.icon-delete').addClass('hidden');
        $('fieldset#subpattern-' + id + ' a.icon-copy').addClass('hidden');
    }
    
}

function addPMvar(p,unused,varname,vartype,vardesc) {
    
    input = $('fieldset.eSubpattern:first table.vars').find('tbody > tr').clone();
    varcount = $('fieldset#subpattern-' + p + ' table.vars').find('tbody > tr').size();
    
    table = $('fieldset#subpattern-' + p + ' table.vars > tbody');
    table.append('<tr>' + input.html() + '</tr>');
    table.find('tr:eq(' + varcount + ') input.eVarname').val(varname);
    table.find('tr:eq(' + varcount + ') input.eVardesc').val(vardesc);
    table.find('tr:eq(' + varcount + ') select > option').each( function (i) {
        if ($(this).text() == vartype) {
            $(this).attr('selected','true');
            return;
        } else {
            // do nothing
        }
    });
    
    reindexPM();
    
}

function delPMvar(p,c) {
    
    $('fieldset#subpattern-' + p + ' table.vars').find('tbody > tr').eq(c - 2).remove();
    
    reindexPM();
    
}

function addPMselect(p, c, select) {
    
    input = $('fieldset.eSubpattern:first table.selectqueries').find('tbody > tr').clone();
    varcount = $('fieldset#subpattern-' + p + ' table.selectqueries').find('tbody > tr').size();
    
    table = $('fieldset#subpattern-' + p + ' table.selectqueries tbody');
    table.append('<tr>' + input.html() + '</tr>');
    table.find('tr:eq(' + varcount + ') input').val(select);
    
    $('fieldset#subpattern-' + p + ' table.selectqueries tfoot a').hide();
    
    reindexPM();
    
}

function delPMselect(p,c) {
    
    $('fieldset#subpattern-' + p + ' table.selectqueries tbody tr').remove();
    $('fieldset#subpattern-' + p + ' table.selectqueries tfoot a').show();

    reindexPM();
}

function addPMinsert(p, c, insert) {
    
    input = $('fieldset.eSubpattern:first table.triplesInsert').find('tbody > tr').clone();
    varcount = $('fieldset#subpattern-' + p + ' table.triplesInsert').find('tbody > tr').size();
    
    table = $('fieldset#subpattern-' + p + ' table.triplesInsert > tbody');
    table.append('<tr>' + input.html() + '</tr>');
    table.find('tr:eq(' + varcount + ') input').val(insert);
    
    reindexPM();
    
}

function delPMinsert(p,c) {
    
    $('fieldset#subpattern-' + p + ' table.triplesInsert').find('tbody > tr').eq(c - 2).remove();
    
    reindexPM();
}

function addPMdelete(p, c, del) {
    
    input = $('fieldset.eSubpattern:first table.triplesDelete').find('tbody > tr').clone();
    varcount = $('fieldset#subpattern-' + p + ' table.triplesDelete').find('tbody > tr').size();
    
    table = $('fieldset#subpattern-' + p + ' table.triplesDelete > tbody');
    table.append('<tr>' + input.html() + '</tr>');
    table.find('tr:eq(' + varcount + ') input').val(del);    
    
    reindexPM();
    
}

function delPMdelete(p,c) {
    
    $('fieldset#subpattern-' + p + ' table.triplesDelete').find('tbody > tr').eq(c - 2).remove();
    
    reindexPM();
}

function loadPMpattern(uri,id) {
    
    $.getJSON(urlBase + 'patternmanager/loadpattern', { uri: uri , type: 'rdf'}, function(data) {
        current = data;

        $('fieldset#subpattern-' + id + ' input#patternlabel-' + id).val(data.label);
        $('fieldset#subpattern-' + id + ' input#patterndesc-' + id).val(data.desc);
        for (j in current['V']) {
            addPMvar(id,null,current['V'][j]['name'],current['V'][j]['type'],current['V'][j]['desc']);
        }
        
        if (current['S'].length > 0) {
            addPMselect(id,null,current['S']);
        } else {
            delPMselect(id,null);
        }
        for (pkey in current['U']) {
            if (current['U'][pkey]['type'] === 'insert' ) {
                addPMinsert(id, null, current['U'][pkey]['pattern']);
            } else if ( current['U'][pkey]['type'] === 'delete' ) {
                addPMdelete(id, null, current['U'][pkey]['pattern']);
            } else {
                // do nothing
            }
        }
    });
}

function reindexPM() {

    $('div#patternmanager input.BasicPattern').each( function (i) {
        node = $(this);
        if (i != 0 && !node.hasClass('ac_input')) {
            node.autocomplete(
                urlBase + 'patternmanager/autocomplete',
                {
                    loadingClass : 'is-processing',
                    minChars: 3 ,
                    delay: 1000 ,
                    max: 10 ,
                    extraParams: {
                        mode : 'view', vartype : 'BasicPattern' 
                    },
                    formatItem: function(row,pos,max,str) {
                        data = $.secureEvalJSON(row[0]);
                        return data[1];
                    },
                    formatResult: function(row,pos,max) {
                        data = $.secureEvalJSON(row[0]);
                        return data[1];
                    }
                }
            );
            node.result( function(event, item) {
                data = $.secureEvalJSON(item[0]);
                bp = $('fieldset#subpattern-' + i);
                //
                if ( confirm('Load this pattern? (will overwrite current)') ) {
                    bp.find('table:eq(0) > tbody > tr').remove();
                    bp.find('table:eq(1) > tbody > tr').remove();
                    bp.find('table:eq(2) > tbody > tr').remove();
                    bp.find('table:eq(3) > tbody > tr').remove();
                    loadPMpattern(data[0],i);
                } else {
                    // do nothing
                }
            });
        }
    });
    
    $('div#patternmanager').addClass('is-processing');
    
    $('div#patternmanager fieldset.eSubpattern').each( function (i) {
        
        if (i != 0) {
            
            currentId = $(this).attr('id');
            $(this).attr('id',currentId.substr(0,currentId.indexOf('-')) + '-' + i);
            
            $(this).find('a').each( function (k) {
                currentHref = $(this).attr('href');
                if (typeof currentHref == 'undefined') {
                	// do nothing (no href)
                } else {
                	$(this).attr('href', currentHref.substr(0,currentHref.indexOf('(')) + '(' + i + ');' );
                }
            });
            
            // (re)index input fields for pattern label and pattern description
            $(this).find('div > input').each( function () {
                currentName = $(this).attr('name');
                currentName = currentName.substr(0, currentName.indexOf('-')) + '-' + i;
                $(this).attr('name', currentName);
                currentId   = $(this).attr('id');
                currentId   = currentId.substr(0, currentId.indexOf('-')) + '-' + i;
                $(this).attr('id',currentId);
            });

            // TODO: hier im JS-Control auf die Nutzung von Tabellen im View zu bauen, ist nicht das sinnvollste!
            $(this).find('table').each( function (l) {
                $(this).find('tr').each( function (m) {
                    
                    $(this).find('[name]').each( function () {
                        currentName = $(this).attr('name');
                        $(this).attr('name', currentName.substr(0,currentName.indexOf('-')) + '-' + i + '-' + m);
                    });
                    
                    $(this).find('[id]').each( function () {
                        currentId = $(this).attr('id');
                        $(this).attr('id', currentId.substr(0,currentName.indexOf('-')) + '-' + i + '-' + m);
                    });

                    $(this).find('[href]').each( function() {
                        currentHref = $(this).attr('href');
                        currentHref = currentHref.substr(0,currentHref.indexOf('(')) + '(' + i + ', ' + m + ');';
                        $(this).attr('href', currentHref);
                    });
                    
                });
            });
        
        }
    
    });

    $('div#patternmanager').removeClass('is-processing');
    
}