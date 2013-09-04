function initTypeahead() {
  $(".portlet .resource-widget").typeahead({
    source: ['foaf:Person', 'foo', 'bar'],
    highlighter: function(item) {
        if(item === 'foaf:Person') {
          return '<div><p>' + item + '</p><p>http://xmlns.com/foaf/0.1/Person</p></div>';
        }
        return '<div><p>' + item + '</p><p>http://foo.bar</p></div>';
    }
  });

}

function toggleFullscreen() {
  var modal = $('#rdfauthor-view');
  modal.toggleClass('fullscreen');
  if (modal.hasClass('fullscreen')) {
    modal.draggable('option', 'disabled', true);
    modal.resizable('option', 'disabled', true);
    modal.css('width', '100%');
    modal.css('height', '100%');
    modal.css("margin-left", -modal.outerWidth()/2);
    modal.css("margin-top", -modal.outerHeight()/2);
    modal.css("top", "50%");
    modal.css("left", "50%");
    modal.find(".modal-body").each(function() {
      var maxHeight = modal.height()-$('.modal-header').outerHeight()-$('.modal-footer').outerHeight()-20;
      $(this).css("max-height", maxHeight);
      $(this).find('.tab-pane').css('height', maxHeight);
      $(this).find('.tab-pane').css('height', maxHeight-$('.modal-footer').outerHeight());
    });
  } else {
    var modalSize = modal.data('modalSize');
    modal.draggable('option', 'disabled', false);
    modal.resizable('option', 'disabled', false);
    modal.css('width', modalSize.modal.width);
    modal.css('height',modalSize.modal.height);
    modal.css("margin-left", modalSize.modal.marginLeft);
    modal.css("margin-top", modalSize.modal.marginTop);
    modal.css("top", "50%");
    modal.css("left", "50%");
    modal.find(".modal-body").each(function() {
      var maxHeight = modal.height()-$('.modal-header').outerHeight()-$('.modal-footer').outerHeight()-20;
      $(this).css("max-height", maxHeight);
      $(this).find('.tab-pane').css('height', maxHeight);
      $(this).find('.tab-pane').css('height', maxHeight-$('.modal-footer').outerHeight());
    });

  }
}

function storeSize() {
  var modal = $('#rdfauthor-view');
  // store values
  var modalSize = {
    'modal' : {
      'marginLeft' : -modal.outerWidth()/2,
        'marginTop' : -modal.outerHeight()/2,
        'height' : modal.outerHeight(),
        'width' : modal.outerWidth(),
        'top' : '50%',
        'left' : '50%'
    },
    'modalBody' : {
      'maxHeight' : modal.outerHeight()-$('.modal-header').outerHeight()-$('.modal-footer').outerHeight()
    }
  }
  // append values to rdfauthor view
  $('#rdfauthor-view').data('modalSize', modalSize);
}

function enableSettings() {
  // $('.portlet .settings, .tabs .settings, .portlet-entry .btn-group')
    // .toggleClass('hide-important');
  // $('.portlet input').toggleClass('input-fullsize input-size-135');

  $('.portlet').hover(function() {
    $(this).find('.settings').fadeTo(1,1);
  },function() {
    if($(this).parents('li').hasClass('open')) {
      $(this).find('.settings').fadeTo(1,1);
    } else {
      $(this).find('.settings').fadeTo(1,0);
    }  
  });

  $('.tabs').hover(function() {
    $(this).find('.settings').fadeTo(1,1);
  },function() {
    $(this).find('.settings').fadeTo(1,0);
  });

  $('.portlet-entry').hover(function() {
    $(this).find('.property-settings').fadeTo(1,1);
  },function() {
    if($(this).parents('li').hasClass('open')) {
      $(this).find('.property-settings').fadeTo(1,1);
    } else {
      $(this).find('.property-settings').fadeTo(1,0);
    }  
  });

}

function disableSettings() {
  $('.portlet, .tabs').unbind('mouseenter mouseleave');
  $('.portlet .settings, .tabs .settings, .portlet-entry .btn-group')
    .toggleClass('hide-important');
  $('.portlet input').toggleClass('input-fullsize input-size-135');
}

function saveChoreography() {
  // clear
  if (localStorage.getItem('choreography')) {
    localStorage.removeItem('choreography');
  }
  var choreography = {};
  $('#rdfauthor-view .subject').each(function(i) {
    var subjectURI = $(this).attr('name');
    choreography[subjectURI] = {};

    // read porlets
    $(this).find('.portlet').each(function(portletPosition) {
      var portletURI = $(this).attr('name');
      var portletBrand = $(this).find('.brand').text();
      choreography[subjectURI][portletURI] = {
        pos : portletPosition,
        brand : portletBrand,
        property : {}
      };

      // read properties
      $(this).find('.property').each(function(propertyPosition) {
        var propertyURI = $(this).attr('name');
        choreography[subjectURI][portletURI]['property'][propertyURI] = {
          pos : propertyPosition,
          object : {}
        }

        // read objects
        $(this).find('.object').each(function(objectPosition) {
          var objectValue = $(this).find('input').val();
          choreography[subjectURI][portletURI]['property'][propertyURI]['object'][objectPosition] = {
            value : objectValue,
            widget : 'reserved',
            type : 'reserved'
          }
        });

      });
    });
  });

  // save choreography to localStorage
  localStorage.setItem('choreography', JSON.stringify(choreography));

  console.log('choreography', choreography);

  console.log('choreographyJSON', JSON.stringify(choreography));

  var countResourceWidgets = 0;
  $('#rdfauthor-view input.resource-widget').each(function(i) {
    countResourceWidgets++;
  });

  var countLiteralWidgets = 0;
  $('#rdfauthor-view input.literal-widget').each(function(i) {
    countLiteralWidgets++;
  });

  console.log('ResourceWidgets', countResourceWidgets);
  console.log('LiteralWidgets', countLiteralWidgets);
}

function restoreChoreography() {
  var savedChoreography = JSON.parse(localStorage.getItem('choreography'));
  console.log('savedChoreography', savedChoreography ? savedChoreography : 'no saved choreography');

  if (savedChoreography) {
    // move porlets
    for (var subjectURI in savedChoreography) {
      var portlets = savedChoreography[subjectURI];
      console.log('read subject', subjectURI);
      for (var portletURI in portlets) {
        var portlet = portlets[portletURI];
        var properties = portlet['property'];
        console.log('portletURI', portletURI);
        console.log('portletPosition', portlet.pos);

        // console.log('properties', properties);
        $('#rdfauthor-view .portlet:eq('+portlet.pos+')').after($('#rdfauthor-view .portlet[name="'+portletURI+'"]'));

        // set brand
        $('#rdfauthor-view .portlet[name="'+portletURI+'"]').find('.brand').text(portlet.brand);
        
        // move properties
        for (var propertyURI in properties) {
          var property = properties[propertyURI];
          console.log('property', property);
          console.log('propertyPosition', property.pos);
          $('#rdfauthor-view .portlet[name="'+portletURI+'"] .property:eq('+property.pos+')').after($('#rdfauthor-view .portlet[name="'+portletURI+'"] .property[name="'+propertyURI+'"]'));
          console.log('TEST', $('#rdfauthor-view .portlet[name="'+portletURI+'"] .property:eq('+property.pos+')'));
        }
      }
    }

    // restore items which were marked as removed
    $('#rdfauthor-view .was-removed').removeClass('was-removed').fadeIn();
  } else {
    saveChoreography();
  }
}

function addPortlet(subject) {
  var markup = '<div name="http://rdfauthor.com/choreo_common" class="span5 portlet">\
                  <div class="navbar navbar-fixed-top portlet-navbar" style="position: absolute;">\
                    <div class="navbar-inner">\
                      <div class="container" style="width: auto; padding: 0 20px;">\
                        <a class="brand" href="#"></a>\
                        <input type="text" value="" class="input brand-input">\
                        <ul class="nav actionbar pull-right">\
                          <li class="dropdown">\
                            <a href="#" class="dropdown-toggle settings" data-toggle="dropdown" style="opacity: 0;"><i class="icon-cog"></i></a>\
                            <ul class="dropdown-menu">\
                              <li><a class="hide-show hide-show-portlet" href="#"><i class="icon-arrow-up" style="padding-right: 5px;"></i>Hide/Show</a></li>\
                              <li><a class="remove remove-portlet enabled" href="#"><i class="icon-trash" style="padding-right: 5px;"></i>Remove Portlet</a></li>\
                              <li><a class="add add-portlet" href="#"><i class="icon-plus-sign" style="padding-right: 5px;"></i>Add Property</a></li>\
                              <li><a class="rename rename-portlet enabled" href="#&quot;"><i class="icon-pencil" style="padding-right: 5px;"></i>Rename Portlet</a></li>\
                            </ul>\
                          </li>\
                        </ul>\
                      </div>\
                    </div>\
                  </div>\
                </div>';
  subject.append(markup);
  enableSettings();
}


function addObject(property) {
  var markup = '<div class="object line input-prepend input-append">\
                      <span class="add-on">\
                        <i class="icon-bookmark"></i>\
                        </span><input name="1-rdfs-label-2" type="text" placeholder="new object" value="" class="input input-fullsize literal-widget">\
                        <div class="btn-group">\
                          <button class="btn dropdown-toggle" data-toggle="dropdown">\
                            <i class="icon-cog"></i>\
                          </button>\
                          <ul class="dropdown-menu">\
                            <!-- <li><a class="object-language" href="#"><i class="icon-globe" style="padding-right: 5px;"></i>Language</a></li> -->\
                            <!--li class="dropdown-submenu">\
                              <a class="object-language" tabindex="-1" href="#"><i class="icon-globe" style="padding-right: 5px;"></i>Language</a>\
                              <ul class="dropdown-menu">\
                                <li><a tabindex="-1" href="#">en</a></li>\
                                <li><a tabindex="-1" href="#">de</a></li>\
                                <li><a tabindex="-1" href="#">nl</a></li>\
                                <li><a tabindex="-1" href="#">fr</a></li>\
                                <li><a tabindex="-1" href="#">it</a></li>\
                                <li class="divider"></li>\
                                <li><input type="text" class="input-small input-custom" placeholder="custom lang"></li>\
                              </ul>\
                            </li>\
                            <li class="dropdown-submenu">\
                              <a class="object-datatype" tabindex="-1" href="#"><i class="icon-tag" style="padding-right: 5px;"></i>Datatype</a>\
                              <ul class="dropdown-menu">\
                                <li><a tabindex="-1" href="#">xsd:string</a></li>\
                                <li><a tabindex="-1" href="#">xsd:decimal</a></li>\
                                <li><a tabindex="-1" href="#">xsd:integer</a></li>\
                                <li><a tabindex="-1" href="#">xsd:float</a></li>\
                                <li><a tabindex="-1" href="#">xsd:boolean</a></li>\
                                <li><a tabindex="-1" href="#">xsd:date</a></li>\
                                <li><a tabindex="-1" href="#">xsd:time</a></li>\
                                <li class="divider"></li>\
                                <li><input type="text" class="input-small input-custom" placeholder="custom type"></li>\
                              </ul>\
                            </li-->\
                            <!-- <li><a class="object-remove" href="#"><i class="icon-tag" style="padding-right: 5px;"></i>Datatype</a></li> -->\
                            <li><a class="object-remove" href="#"><i class="icon-trash" style="padding-right: 5px;"></i>Remove</a></li>\
                          </ul>\
                        </div>\
                    </div>';

  property.find('.controls').append(markup);
}

$(document).ready(function() {

  // restore choreography
  restoreChoreography();

  initTypeahead();
  storeSize();
  $('#rdfauthor-view').resizable().draggable({
    handle: '.modal-header',
    cursor: 'move'
  });
  $('.portlet-container').sortable({
    disabled : true
  }).disableSelection();
  $('.modal-header button').tooltip();

  // disable input and textarea
  // $('#rdfauthor-view input, #rdfauthor-view textarea').prop('disabled', true);

  // disable settings
  // disableSettings();

  // enable settings by default
  enableSettings();

  // markItUp settings
  var markItUpSettings = {
    nameSpace: 'markdown', // Useful to prevent multi-instances CSS conflict
    onShiftEnter: {keepDefault:false, openWith:'\n\n'},
    returnParserData: function(data){
      //added new callback in markItUp to use json markdown parser (in this case: showdown)
      return self._converter.makeHtml(data);
    },
    markupSet: [
      // {name:'First Level Heading', key:"1", placeHolder:'Your title here...',
      // closeWith:function(markItUp) { return self._miu.markdownTitle(markItUp, '=') } },
      // {name:'Second Level Heading', key:"2", placeHolder:'Your title here...',
      // closeWith:function(markItUp) { return self._miu.markdownTitle(markItUp, '-') } },
      // {name:'Heading 3', key:"3", openWith:'### ', placeHolder:'Your title here...' },
      // {name:'Heading 4', key:"4", openWith:'#### ', placeHolder:'Your title here...' },
      // {name:'Heading 5', key:"5", openWith:'##### ', placeHolder:'Your title here...' },
      // {name:'Heading 6', key:"6", openWith:'###### ', placeHolder:'Your title here...' },
      // {separator:'---------------' },
      {name:'Bold', key:"B", openWith:'**', closeWith:'**'},
      {name:'Italic', key:"I", openWith:'_', closeWith:'_'},
      {separator:'---------------' },
      {name:'Bulleted List', openWith:'- ' },
      {name:'Numeric List', openWith:function(markItUp) {
        return markItUp.line+'. ';
      }},
      {separator:'---------------' },
      {name:'Picture', key:"P", replaceWith:'![[![Alternative text]!]]([![Url:!:http://]!] "[![Title]!]")'},
      {name:'Link', key:"L", openWith:'[', closeWith:']([![Url:!:http://]!] "[![Title]!]")',
       placeHolder:'Your text to link here...' },
      // {separator:'---------------'},
      // {name:'Quotes', openWith:'> '},
      // {name:'Code Block / Code', openWith:'(!(\t|!|`)!)', closeWith:'(!(`)!)'},
      {separator:'---------------'},
      {name:'Preview', call:'preview', className:"preview"}
    ]
  };

  $("#markItUp").markItUp(markItUpSettings);
  // $( ".portlet" )
    // .addClass( "ui-widget ui-widget-content ui-corner-all" )
    // .find( ".portlet-header" )
    // .addClass( "ui-widget-header ui-corner-all" )
    // .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
    // .end()
    // .find( ".portlet-content" );

  // double click handler on portlet navbar
  $(document).on('dblclick', '.portlet-navbar', function(event) {
    event.preventDefault();
    // hide - show
    $(this).find('.hide-show').trigger('click');
  });

  // PORTLET CLICK EVENTS
  $(document).on('click', '.portlet .actionbar .dropdown-menu li a', function(event) {
    event.preventDefault();
    var portlet = $(this).parents('.portlet');

    if (!$(this).hasClass('disabled')) {

      // add property
      if ($(this).hasClass('add-portlet')) {
        var markup = '<div data-content="foo:bar" class="portlet-entry">\
                        <div class="control-group">\
                          <!-- <label class="control-label">foo:bar</label> -->\
                          <div class="controls">\
                            <div class="line input-prepend input-append">\
                              <span class="add-on">\
                                <i class="icon-bookmark"></i>\
                              </span><input type="text" value="Add Foo Bar Test" class="input resource-widget"><button class="btn" type="button"><i class="icon-cog"></i></button>\
                            </div>\
                          </div>\
                        </div>\
                      </div>';
        portlet.find('.portlet-content').append(markup);
      }

      // remove portlet
      if ($(this).hasClass('remove-portlet')) {
        // hide and mark as removed
        portlet.addClass('was-removed').fadeOut(400);
        // portlet.fadeOut(400, function() {
          // $(this).remove();
        // });
      }

      // hide - show
      if ($(this).hasClass('hide-show-portlet')) {
        $(this).parents('.dropdown-menu').css('z-index', '1500');
        $(this).find('i').toggleClass('icon-arrow-up').toggleClass('icon-arrow-down');
        portlet.toggleClass('portlet-minimized');
      }

      // rename
      if ($(this).hasClass('rename-portlet')) {
        if (portlet.find('.container .brand-input').length === 0) {
          var brandValue = portlet.find('a.brand').addClass('hide-important').text();
          console.log('rename', brandValue);
          portlet.find('.container').prepend('<input type="text" value="' + brandValue + '" class="input brand-input">');
          portlet.find('.container .brand-input').select();
        } else {
           portlet.find('.container .brand-input').select();
        }
      }

    } // disabled check

  });

// double click handler on property header
  $(document).on('dblclick', '#rdfauthor-view .property', function(event) {
    event.preventDefault();
    // hide - show
    $(this).find('.hide-show-property').trigger('click');
  });


  // PORTLET PROPERTY CLICK EVENTS
  $(document).on('click', '.portlet .property .dropdown-menu li a', function(event) {
    event.preventDefault();
    var portletEntry = $(this).parents('.portlet-entry');
    
    if (!$(this).hasClass('disabled')) {

      // add object
      if ($(this).hasClass('add-object')) {
        console.log('add object');
        addObject(portletEntry);
      }

      // remove property
      if ($(this).hasClass('remove-property')) {
        console.log('remove property');
        // hide and mark as removed
        portletEntry.addClass('was-removed').fadeOut(400);
      }

      // hide - show
      if ($(this).hasClass('hide-show-property')) {
        // $(this).parents('.dropdown-menu').css('z-index', '1500');
        $(this).find('i').toggleClass('icon-arrow-up').toggleClass('icon-arrow-down');
        portletEntry.toggleClass('property-minimized');
        portletEntry.find('.object-listing, .object-details').toggleClass('hide');
        var objectLength = portletEntry.find('.object').length;
        portletEntry.find('.object-count').text(objectLength);
      }

    }
  });

  $(document).on('click', '.portlet .property .btn-object-details', function(event) {
    event.preventDefault();
    console.log('click', $(this).parents('.property').find('.hide-show-property'));
    $(this).parents('.property').find('.hide-show-property').trigger('click');
  });

  // $('.portlet .actionbar .icon-trash').click(function(event) {
    // event.preventDefault();
    // $(this).parents('.portlet:first').fadeOut(400, function() {
      // $(this).remove();
    // });
  // });

  // $('.portlet .actionbar .icon-plus-sign').click(function(event) {
    // event.preventDefault();
    // $('#addProperty').modal('show');
  // });

  $( ".portlet-content" ).sortable({
    disabled: true,
    conectWith: '.portlet-container'
  }).disableSelection();

  $('[rel=tooltip]').tooltip();

  
  $(document).on('click', '.portlet .btn', function() {
      var controlGroup = $(this).parents('.control-group');
      var btnMarkup = '<div style="display: none;" class="line input-prepend input-append">\
            <span class="add-on">\
              <i class="icon-bookmark" ></i></button>\
            </span><input type="text" placeholder="start typing..." value="" class="input resource-widget"><button class="btn" type="button"><i class="icon-plus-sign" ></i></button><button class="btn" type="button"><i class="icon-minus-sign" ></i></button>\
        </div>';
      if($(this).find('i').hasClass('icon-plus-sign')) {
        $(this).parent('div').parent().append(btnMarkup);
        $(this).parent('div').parent().find('div:last').fadeIn(function() {
          initTypeahead();
        });
      }
      if($(this).find('i').hasClass('icon-minus-sign')) {
        if($(this).parents('.portlet-entry').find('input').length === 1) {
          $(this).parents('.portlet-entry').fadeOut(function() {
            $(this).parents('.portlet-entry').remove();
          });
        } else {
          $(this).parent().fadeOut(function() {
            $(this).remove();
          });
        }
      }
  }); 
    
  $(document).on('click', '#btn-addProperty', function() {
      console.log('test');
      var markup = '<div data-content="foo:bar" class="portlet-entry">\
                <div class="control-group">\
                  <!-- <label class="control-label">foo:bar</label> -->\
                  <div class="controls">\
                    <div class="line input-prepend input-append">\
                      <span class="add-on">\
                        <i class="icon-bookmark"></i>\
                      </span><input type="text" value="Add Foo Bar Test" class="input resource-widget"><button class="btn" type="button"><i class="icon-plus-sign"></i></button><button class="btn" type="button"><i class="icon-minus-sign"></i>\
                    </button></div>\
                  </div>\
                </div>\
              </div>';
    $('.portlet:first .portlet-content').append(markup);
    $('#addProperty').modal('toggle');
  });

  $(".modal").on("resize", function(event, ui) {
    // console.log('ui',ui);
    ui.element.css("margin-left", -ui.size.width/2);
    ui.element.css("margin-top", -ui.size.height/2);
    ui.element.css("left", "50%");
    ui.element.css("top", "50%");
    // fit size of modal body to prevent layout glitches
    $(ui.element).find(".modal-body").each(function() {
      var maxHeight = ui.size.height-$('.modal-header').outerHeight()-$('.modal-footer').outerHeight();
      $(this).css("max-height", maxHeight);
      $(this).find('.tab-pane').css('height', maxHeight-$('.modal-footer').outerHeight());
      // store size of modal
      storeSize();
    });
  });

  //tab show
  // $(".modal").on("show", function() {
    // console.log('ui',$(this).height());
    // $(this).resizable('destroy');
    // $(this).css("margin-left", -$(this).width()/2);
    // $(this).css("margin-top", -$(this).height()/2);
    // $(this).css("top", "50%");
    // $(this).css("left", "50%");
    // $(this).find(".modal-body").each(function() {
      // var maxHeight = $(this).height()-$('.modal-header').outerHeight()-$('.modal-footer').outerHeight();
      // console.log('max-height', maxHeight);
      // $(this).css("max-height", maxHeight);
    // });
  // });

  $(document).on('click', '#rdfauthor-view .tabbable .dropdown-menu a', function(event) {
    console.log('click on dropdown item');
    var subjectContentID = $(this).parents('.dropdown').find('a.tabs').attr('href');
    var subjectContent = $('#rdfauthor-view ' + subjectContentID);
    console.log('id', subjectContentID);
    console.log('content', subjectContent);

    if (!$(this).hasClass('disabled')) {
      // add portlet
      if($(this).hasClass('add-portlet')) {
        addPortlet(subjectContent);
      }
    }
  });

  $(document).on('click', '.tabs', function(event) {
    event.preventDefault();
    $(this).tab('show');
    // $(this).parent().dropdown('toggle');
  });

  var openTabDropdown = false;
  var tabDropdown = $('.tabs .dropdown');
  $(document).on('click', '.tabs i', function(event) {
    event.preventDefault();
    tabDropdown = $(this).parents('.dropdown');
    tabDropdown.toggleClass('open');
  });

  // manually open close dropdown on tabs' dropdown
  $('html').unbind('click').click(function(event){
    if ($('.nav-tabs li').hasClass('open') && openTabDropdown == false) {
      $('.nav-tabs li').removeClass('open');
    }
  });
  $('.nav-tabs .dropdown-menu').mouseover(function() {
    openTabDropdown = true;
  });
  $('.nav-tabs .dropdown-menu').mouseout(function() {
    openTabDropdown = false;
  });

  $(document).on('click', '.input-custom', function(event) {
    event.preventDefault();
    console.log('click', $(this));
    $(this).parens('.btn-group').addClass('open');
  });

  $(document).on('click', '.checkbox-submenu', function(event) {
    console.log('click', $(this));
    $(this).parens('.btn-group').addClass('open');
  });

  $('.portlet .image').parents('.input-prepend').popover({
    trigger: 'hover',
    placement: 'top',
    html: true,
    title: 'Preview',
    content: '<img src="img/leipzig2.gif" />'
  });


  $(document).on('click', '.modal-header button', function(event) {
    if ($(this).hasClass('fullscreen')) {
      $(this).toggleClass('icon-fullscreen icon-resize-small');
      toggleFullscreen();
    }
  });

  $(document).on('click', '.modal-footer a', function(event) {
    if ($(this).hasClass('edit') || $(this).hasClass('save') || $(this).hasClass('cancel')) {
      $(this).parent().find('.btn').toggleClass('hide');
    }
    
    // enable edit mode
    if ($(this).hasClass('edit')) {
      // enable disabled actions
      $('#rdfauthor-view .disabled').toggleClass('disabled enabled');
      $('#rdfauthor-view').toggleClass('consumer-mode edit-mode');
      $('.portlet-container, .portlet-content').sortable('option', 'disabled', false);
      // $('#rdfauthor-view input, #rdfauthor-view textarea').prop('disabled', false);
      // enableSettings();
    }

    // disable edit mode and save
    if ($(this).hasClass('save')) {
      // enable disabled actions
      $('#rdfauthor-view .enabled').toggleClass('disabled enabled');
      $('#rdfauthor-view').toggleClass('consumer-mode edit-mode');
      $('.portlet-container, .portlet-content').sortable( 'option', 'disabled', true );
      // $('#rdfauthor-view input, #rdfauthor-view textarea').prop('disabled', true);
      // disableSettings();
      saveChoreography();
    }
    // disable edit mode and don't save anything
    if ($(this).hasClass('cancel')) {
      // enable disabled actions
      $('#rdfauthor-view .enabled').toggleClass('disabled enabled');
      $('#rdfauthor-view').toggleClass('consumer-mode edit-mode');
      $('.portlet-container, .portlet-content').sortable( 'option', 'disabled', true );
      // disableSettings();
      restoreChoreography();
    }
  });

  $(document).on('keypress', '.portlet .brand-input', function(event) {
    var portlet = $(this).parents('.portlet');

    // enter
    if (event.which === 13) {
      console.log('enter');
      var brandValue = $(this).val();
      $(this).remove();
      portlet.find('.brand').removeClass('hide-important').text(brandValue);
    }
  });

  $('input').on('keyup', function() {
    var object = $(this).attr('name');
    var value = $(this).val();
    if ($('input[name='+object+']').length > 1) {
      $('input[name='+object+']').val(value);
    }
  });

  // $( ".portlet-entry" )
    // .addClass( "ui-widget ui-widget-content ui-corner-all" )
    // .find( ".portlet-header" )
    // .addClass( "ui-widget-header ui-corner-all" )
    // .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
    // .end()
    // .find( ".portlet-content" );


  // $('.group').draggable({
    // // containment: ".group-parent",
    // distance: 20,
    // snap: ".group-parent",
    // grid: [ 10, 10]
  // });
});
