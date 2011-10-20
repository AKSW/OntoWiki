/**
 * @class
 */
function FilterAPI(){
	/*
         * @var
         */
        this.uri = reloadUrl;
        /*
         *@var array
         */
        this.callbacks = new Array();

        /**
         * @var array
         */
        this.filters = filtersFromSession;
        
        /**
         * @var int
         */
        this.count = 0;
        for(onefilter in filtersFromSession){
            this.count++;
        }


        /**
         *@method
         *
         */
	this.addCallback = function(callback){
		if(typeof callback == 'function' || typeof callback == 'object')
			this.callbacks.push(callback);
	};
        
        /**
         *@method
         *
         */
	this.removeAllFiltersOfProperty = function(uri){
            var data = { filter: [] }
            for(afilterName in this.filters){
                if(this.filters[afilterName].property == uri){
                    data.filter.push({
                        "mode" : "box",
                        "action" : "remove",
                        "id" : this.filters[afilterName].id
                    })
                }
            }
            var dataserialized = $.toJSON(data);
            var url = this.uri + "?instancesconfig=" + encodeURIComponent(dataserialized)+"&list="+listName;
            //alert(dataserialized)
            window.location = url;
        };

	/**
         * add a filter
         * @method
         * @param id int,string
         * @param property string an iri (predicate) which values should be filtered
         * @param isInverse boolean if the property is inverse
         * @param propertyLabel string a label for the property (will be displayed instead)
         * @param filter string can be "contains" or "equals" . going to be enhanced
         * @param value1 mixed the value applied to the filter
         * @param value2 mixed the value applied to the filter. often optional (used for "between")
         * @param valuetype string may be "uri" or "literal" or "typedliteral" or "langtaggedliteral"
         * @param literaltype string if valuetype is "typedliteral" or "langtaggedliteral": you can put stuff like "de" or "xsd:int" here...
         * @param callback function will be called on success
         * @param hidden boolean will not show up in filterbox if true
         * @param negate 
         * @param dontReload prevent page reloading
         */
	this.add = function(id, property, isInverse, propertyLabel, filter, value1, value2, valuetype, literaltype, callback, hidden, negate, dontReload){
            if(typeof callback != 'function' && typeof callback != 'object'){
		callback = function(){};
            }

            if(id == null){
                id  = "filterbox"+this.count
            }
            var data =
                {
                filter:
                    [
                        {
                            "mode" : "box",
                            "action" : "add",
                            "id" : id,
                            "property" : property,
                            "isInverse" : typeof isInverse != 'undefined' ? isInverse : false,
                            "propertyLabel" : typeof propertyLabel != 'undefined' ? propertyLabel : null,
                            "filter" : filter,
                            "value1": value1,
                            "value2": typeof value2 != 'undefined' ? value2 : null,
                            "valuetype": typeof valuetype != 'undefined' ? valuetype : null,
                            "literaltype" : typeof literaltype != 'undefined' ? literaltype : null,
                            "hidden" : typeof hidden != 'undefined' ? hidden : false,
                            "negate" : typeof negate != 'undefined' ? negate : false
                        }
                    ]
            };

            var dataserialized = $.toJSON(data);
            var url = this.uri + "?instancesconfig=" + encodeURIComponent(dataserialized)+"&list="+listName;
            
            this.count++;
            
            if(dontReload == true){
                $.ajax(
                  {
                      "url": url,
                      "type" : "POST"
                  }
                );
            } else {
                window.location = url;
            }
	};

	this.reloadInstances = function(){
            //$('.content .innercontent').load(document.URL);
            //window.location = this.uri;
	};

        this.filterExists = function(id){
            return (typeof this.filters[id] != 'undefined');
        }

        this.getFilterById = function(id){
            return this.filters[id];
        }


	this.remove = function(id, callback){
            if(typeof callback != 'function' && typeof callback != 'object')
                callback = function(){};

            var data = {
                filter: [
                    {
                        "action" : "remove",
                        "id" : id
                    }
                ]
            };

            var dataserialized = $.toJSON(data);

            this.count--;
            
            window.location = this.uri + "?instancesconfig=" + encodeURIComponent(dataserialized);
	};

	this.removeAll = function(){
            this.count = 0;
            window.location = this.uri+"?init"
	};
}

var filter = new FilterAPI();