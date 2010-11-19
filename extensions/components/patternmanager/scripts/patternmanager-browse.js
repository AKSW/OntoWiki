// Configure exploretags module via jscript only
$(document).ready(function() {
    if ( typeof reloadExploreTagsModule === 'function' && $('#exploretags') !== null ) {
        
        if ( $('#exploretags *[title="Pattern Level"]').empty() ) {
            
            var data = {
                uri :  evopatLevelUri ,
                label : 'Pattern Level' ,
                isInverse : false
            };
            var getUri = urlBase + 'service/session?method=setArrayValue&name=cloudproperties&valueIsSerialized=true';
            $.post(getUri,
                {
                   "key": evopatLevelUri,
                   "value" : $.toJSON(data)
               }, //as post because of possible size
              function(res) {
                  if(res==""){
                      reloadExploreTagsModule();
                  } else alert('could not add cloudproperty\nReason:\n'+res)
              });
            
        } else {
            // do nothing 
        }
    }
});