function _vis()
{
return this;
}

   _vis.prototype.change = function(id)
   {
        var open_object = document.getElementById ? document.getElementById(id) : (document.all ? document.all[id] : (document.layers ? document.layers[id] : null));
        open_object.style.display = (open_object.style.display == 'none') ? '' : 'none';
   }

// instantiate
vis = new _vis();