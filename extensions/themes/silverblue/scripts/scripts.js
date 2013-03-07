/*! jQuery v1.7.2 jquery.com | jquery.org/license */
(function(a,b){function cy(a){return f.isWindow(a)?a:a.nodeType===9?a.defaultView||a.parentWindow:!1}function cu(a){if(!cj[a]){var b=c.body,d=f("<"+a+">").appendTo(b),e=d.css("display");d.remove();if(e==="none"||e===""){ck||(ck=c.createElement("iframe"),ck.frameBorder=ck.width=ck.height=0),b.appendChild(ck);if(!cl||!ck.createElement)cl=(ck.contentWindow||ck.contentDocument).document,cl.write((f.support.boxModel?"<!doctype html>":"")+"<html><body>"),cl.close();d=cl.createElement(a),cl.body.appendChild(d),e=f.css(d,"display"),b.removeChild(ck)}cj[a]=e}return cj[a]}function ct(a,b){var c={};f.each(cp.concat.apply([],cp.slice(0,b)),function(){c[this]=a});return c}function cs(){cq=b}function cr(){setTimeout(cs,0);return cq=f.now()}function ci(){try{return new a.ActiveXObject("Microsoft.XMLHTTP")}catch(b){}}function ch(){try{return new a.XMLHttpRequest}catch(b){}}function cb(a,c){a.dataFilter&&(c=a.dataFilter(c,a.dataType));var d=a.dataTypes,e={},g,h,i=d.length,j,k=d[0],l,m,n,o,p;for(g=1;g<i;g++){if(g===1)for(h in a.converters)typeof h=="string"&&(e[h.toLowerCase()]=a.converters[h]);l=k,k=d[g];if(k==="*")k=l;else if(l!=="*"&&l!==k){m=l+" "+k,n=e[m]||e["* "+k];if(!n){p=b;for(o in e){j=o.split(" ");if(j[0]===l||j[0]==="*"){p=e[j[1]+" "+k];if(p){o=e[o],o===!0?n=p:p===!0&&(n=o);break}}}}!n&&!p&&f.error("No conversion from "+m.replace(" "," to ")),n!==!0&&(c=n?n(c):p(o(c)))}}return c}function ca(a,c,d){var e=a.contents,f=a.dataTypes,g=a.responseFields,h,i,j,k;for(i in g)i in d&&(c[g[i]]=d[i]);while(f[0]==="*")f.shift(),h===b&&(h=a.mimeType||c.getResponseHeader("content-type"));if(h)for(i in e)if(e[i]&&e[i].test(h)){f.unshift(i);break}if(f[0]in d)j=f[0];else{for(i in d){if(!f[0]||a.converters[i+" "+f[0]]){j=i;break}k||(k=i)}j=j||k}if(j){j!==f[0]&&f.unshift(j);return d[j]}}function b_(a,b,c,d){if(f.isArray(b))f.each(b,function(b,e){c||bD.test(a)?d(a,e):b_(a+"["+(typeof e=="object"?b:"")+"]",e,c,d)});else if(!c&&f.type(b)==="object")for(var e in b)b_(a+"["+e+"]",b[e],c,d);else d(a,b)}function b$(a,c){var d,e,g=f.ajaxSettings.flatOptions||{};for(d in c)c[d]!==b&&((g[d]?a:e||(e={}))[d]=c[d]);e&&f.extend(!0,a,e)}function bZ(a,c,d,e,f,g){f=f||c.dataTypes[0],g=g||{},g[f]=!0;var h=a[f],i=0,j=h?h.length:0,k=a===bS,l;for(;i<j&&(k||!l);i++)l=h[i](c,d,e),typeof l=="string"&&(!k||g[l]?l=b:(c.dataTypes.unshift(l),l=bZ(a,c,d,e,l,g)));(k||!l)&&!g["*"]&&(l=bZ(a,c,d,e,"*",g));return l}function bY(a){return function(b,c){typeof b!="string"&&(c=b,b="*");if(f.isFunction(c)){var d=b.toLowerCase().split(bO),e=0,g=d.length,h,i,j;for(;e<g;e++)h=d[e],j=/^\+/.test(h),j&&(h=h.substr(1)||"*"),i=a[h]=a[h]||[],i[j?"unshift":"push"](c)}}}function bB(a,b,c){var d=b==="width"?a.offsetWidth:a.offsetHeight,e=b==="width"?1:0,g=4;if(d>0){if(c!=="border")for(;e<g;e+=2)c||(d-=parseFloat(f.css(a,"padding"+bx[e]))||0),c==="margin"?d+=parseFloat(f.css(a,c+bx[e]))||0:d-=parseFloat(f.css(a,"border"+bx[e]+"Width"))||0;return d+"px"}d=by(a,b);if(d<0||d==null)d=a.style[b];if(bt.test(d))return d;d=parseFloat(d)||0;if(c)for(;e<g;e+=2)d+=parseFloat(f.css(a,"padding"+bx[e]))||0,c!=="padding"&&(d+=parseFloat(f.css(a,"border"+bx[e]+"Width"))||0),c==="margin"&&(d+=parseFloat(f.css(a,c+bx[e]))||0);return d+"px"}function bo(a){var b=c.createElement("div");bh.appendChild(b),b.innerHTML=a.outerHTML;return b.firstChild}function bn(a){var b=(a.nodeName||"").toLowerCase();b==="input"?bm(a):b!=="script"&&typeof a.getElementsByTagName!="undefined"&&f.grep(a.getElementsByTagName("input"),bm)}function bm(a){if(a.type==="checkbox"||a.type==="radio")a.defaultChecked=a.checked}function bl(a){return typeof a.getElementsByTagName!="undefined"?a.getElementsByTagName("*"):typeof a.querySelectorAll!="undefined"?a.querySelectorAll("*"):[]}function bk(a,b){var c;b.nodeType===1&&(b.clearAttributes&&b.clearAttributes(),b.mergeAttributes&&b.mergeAttributes(a),c=b.nodeName.toLowerCase(),c==="object"?b.outerHTML=a.outerHTML:c!=="input"||a.type!=="checkbox"&&a.type!=="radio"?c==="option"?b.selected=a.defaultSelected:c==="input"||c==="textarea"?b.defaultValue=a.defaultValue:c==="script"&&b.text!==a.text&&(b.text=a.text):(a.checked&&(b.defaultChecked=b.checked=a.checked),b.value!==a.value&&(b.value=a.value)),b.removeAttribute(f.expando),b.removeAttribute("_submit_attached"),b.removeAttribute("_change_attached"))}function bj(a,b){if(b.nodeType===1&&!!f.hasData(a)){var c,d,e,g=f._data(a),h=f._data(b,g),i=g.events;if(i){delete h.handle,h.events={};for(c in i)for(d=0,e=i[c].length;d<e;d++)f.event.add(b,c,i[c][d])}h.data&&(h.data=f.extend({},h.data))}}function bi(a,b){return f.nodeName(a,"table")?a.getElementsByTagName("tbody")[0]||a.appendChild(a.ownerDocument.createElement("tbody")):a}function U(a){var b=V.split("|"),c=a.createDocumentFragment();if(c.createElement)while(b.length)c.createElement(b.pop());return c}function T(a,b,c){b=b||0;if(f.isFunction(b))return f.grep(a,function(a,d){var e=!!b.call(a,d,a);return e===c});if(b.nodeType)return f.grep(a,function(a,d){return a===b===c});if(typeof b=="string"){var d=f.grep(a,function(a){return a.nodeType===1});if(O.test(b))return f.filter(b,d,!c);b=f.filter(b,d)}return f.grep(a,function(a,d){return f.inArray(a,b)>=0===c})}function S(a){return!a||!a.parentNode||a.parentNode.nodeType===11}function K(){return!0}function J(){return!1}function n(a,b,c){var d=b+"defer",e=b+"queue",g=b+"mark",h=f._data(a,d);h&&(c==="queue"||!f._data(a,e))&&(c==="mark"||!f._data(a,g))&&setTimeout(function(){!f._data(a,e)&&!f._data(a,g)&&(f.removeData(a,d,!0),h.fire())},0)}function m(a){for(var b in a){if(b==="data"&&f.isEmptyObject(a[b]))continue;if(b!=="toJSON")return!1}return!0}function l(a,c,d){if(d===b&&a.nodeType===1){var e="data-"+c.replace(k,"-$1").toLowerCase();d=a.getAttribute(e);if(typeof d=="string"){try{d=d==="true"?!0:d==="false"?!1:d==="null"?null:f.isNumeric(d)?+d:j.test(d)?f.parseJSON(d):d}catch(g){}f.data(a,c,d)}else d=b}return d}function h(a){var b=g[a]={},c,d;a=a.split(/\s+/);for(c=0,d=a.length;c<d;c++)b[a[c]]=!0;return b}var c=a.document,d=a.navigator,e=a.location,f=function(){function J(){if(!e.isReady){try{c.documentElement.doScroll("left")}catch(a){setTimeout(J,1);return}e.ready()}}var e=function(a,b){return new e.fn.init(a,b,h)},f=a.jQuery,g=a.$,h,i=/^(?:[^#<]*(<[\w\W]+>)[^>]*$|#([\w\-]*)$)/,j=/\S/,k=/^\s+/,l=/\s+$/,m=/^<(\w+)\s*\/?>(?:<\/\1>)?$/,n=/^[\],:{}\s]*$/,o=/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,p=/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,q=/(?:^|:|,)(?:\s*\[)+/g,r=/(webkit)[ \/]([\w.]+)/,s=/(opera)(?:.*version)?[ \/]([\w.]+)/,t=/(msie) ([\w.]+)/,u=/(mozilla)(?:.*? rv:([\w.]+))?/,v=/-([a-z]|[0-9])/ig,w=/^-ms-/,x=function(a,b){return(b+"").toUpperCase()},y=d.userAgent,z,A,B,C=Object.prototype.toString,D=Object.prototype.hasOwnProperty,E=Array.prototype.push,F=Array.prototype.slice,G=String.prototype.trim,H=Array.prototype.indexOf,I={};e.fn=e.prototype={constructor:e,init:function(a,d,f){var g,h,j,k;if(!a)return this;if(a.nodeType){this.context=this[0]=a,this.length=1;return this}if(a==="body"&&!d&&c.body){this.context=c,this[0]=c.body,this.selector=a,this.length=1;return this}if(typeof a=="string"){a.charAt(0)!=="<"||a.charAt(a.length-1)!==">"||a.length<3?g=i.exec(a):g=[null,a,null];if(g&&(g[1]||!d)){if(g[1]){d=d instanceof e?d[0]:d,k=d?d.ownerDocument||d:c,j=m.exec(a),j?e.isPlainObject(d)?(a=[c.createElement(j[1])],e.fn.attr.call(a,d,!0)):a=[k.createElement(j[1])]:(j=e.buildFragment([g[1]],[k]),a=(j.cacheable?e.clone(j.fragment):j.fragment).childNodes);return e.merge(this,a)}h=c.getElementById(g[2]);if(h&&h.parentNode){if(h.id!==g[2])return f.find(a);this.length=1,this[0]=h}this.context=c,this.selector=a;return this}return!d||d.jquery?(d||f).find(a):this.constructor(d).find(a)}if(e.isFunction(a))return f.ready(a);a.selector!==b&&(this.selector=a.selector,this.context=a.context);return e.makeArray(a,this)},selector:"",jquery:"1.7.2",length:0,size:function(){return this.length},toArray:function(){return F.call(this,0)},get:function(a){return a==null?this.toArray():a<0?this[this.length+a]:this[a]},pushStack:function(a,b,c){var d=this.constructor();e.isArray(a)?E.apply(d,a):e.merge(d,a),d.prevObject=this,d.context=this.context,b==="find"?d.selector=this.selector+(this.selector?" ":"")+c:b&&(d.selector=this.selector+"."+b+"("+c+")");return d},each:function(a,b){return e.each(this,a,b)},ready:function(a){e.bindReady(),A.add(a);return this},eq:function(a){a=+a;return a===-1?this.slice(a):this.slice(a,a+1)},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},slice:function(){return this.pushStack(F.apply(this,arguments),"slice",F.call(arguments).join(","))},map:function(a){return this.pushStack(e.map(this,function(b,c){return a.call(b,c,b)}))},end:function(){return this.prevObject||this.constructor(null)},push:E,sort:[].sort,splice:[].splice},e.fn.init.prototype=e.fn,e.extend=e.fn.extend=function(){var a,c,d,f,g,h,i=arguments[0]||{},j=1,k=arguments.length,l=!1;typeof i=="boolean"&&(l=i,i=arguments[1]||{},j=2),typeof i!="object"&&!e.isFunction(i)&&(i={}),k===j&&(i=this,--j);for(;j<k;j++)if((a=arguments[j])!=null)for(c in a){d=i[c],f=a[c];if(i===f)continue;l&&f&&(e.isPlainObject(f)||(g=e.isArray(f)))?(g?(g=!1,h=d&&e.isArray(d)?d:[]):h=d&&e.isPlainObject(d)?d:{},i[c]=e.extend(l,h,f)):f!==b&&(i[c]=f)}return i},e.extend({noConflict:function(b){a.$===e&&(a.$=g),b&&a.jQuery===e&&(a.jQuery=f);return e},isReady:!1,readyWait:1,holdReady:function(a){a?e.readyWait++:e.ready(!0)},ready:function(a){if(a===!0&&!--e.readyWait||a!==!0&&!e.isReady){if(!c.body)return setTimeout(e.ready,1);e.isReady=!0;if(a!==!0&&--e.readyWait>0)return;A.fireWith(c,[e]),e.fn.trigger&&e(c).trigger("ready").off("ready")}},bindReady:function(){if(!A){A=e.Callbacks("once memory");if(c.readyState==="complete")return setTimeout(e.ready,1);if(c.addEventListener)c.addEventListener("DOMContentLoaded",B,!1),a.addEventListener("load",e.ready,!1);else if(c.attachEvent){c.attachEvent("onreadystatechange",B),a.attachEvent("onload",e.ready);var b=!1;try{b=a.frameElement==null}catch(d){}c.documentElement.doScroll&&b&&J()}}},isFunction:function(a){return e.type(a)==="function"},isArray:Array.isArray||function(a){return e.type(a)==="array"},isWindow:function(a){return a!=null&&a==a.window},isNumeric:function(a){return!isNaN(parseFloat(a))&&isFinite(a)},type:function(a){return a==null?String(a):I[C.call(a)]||"object"},isPlainObject:function(a){if(!a||e.type(a)!=="object"||a.nodeType||e.isWindow(a))return!1;try{if(a.constructor&&!D.call(a,"constructor")&&!D.call(a.constructor.prototype,"isPrototypeOf"))return!1}catch(c){return!1}var d;for(d in a);return d===b||D.call(a,d)},isEmptyObject:function(a){for(var b in a)return!1;return!0},error:function(a){throw new Error(a)},parseJSON:function(b){if(typeof b!="string"||!b)return null;b=e.trim(b);if(a.JSON&&a.JSON.parse)return a.JSON.parse(b);if(n.test(b.replace(o,"@").replace(p,"]").replace(q,"")))return(new Function("return "+b))();e.error("Invalid JSON: "+b)},parseXML:function(c){if(typeof c!="string"||!c)return null;var d,f;try{a.DOMParser?(f=new DOMParser,d=f.parseFromString(c,"text/xml")):(d=new ActiveXObject("Microsoft.XMLDOM"),d.async="false",d.loadXML(c))}catch(g){d=b}(!d||!d.documentElement||d.getElementsByTagName("parsererror").length)&&e.error("Invalid XML: "+c);return d},noop:function(){},globalEval:function(b){b&&j.test(b)&&(a.execScript||function(b){a.eval.call(a,b)})(b)},camelCase:function(a){return a.replace(w,"ms-").replace(v,x)},nodeName:function(a,b){return a.nodeName&&a.nodeName.toUpperCase()===b.toUpperCase()},each:function(a,c,d){var f,g=0,h=a.length,i=h===b||e.isFunction(a);if(d){if(i){for(f in a)if(c.apply(a[f],d)===!1)break}else for(;g<h;)if(c.apply(a[g++],d)===!1)break}else if(i){for(f in a)if(c.call(a[f],f,a[f])===!1)break}else for(;g<h;)if(c.call(a[g],g,a[g++])===!1)break;return a},trim:G?function(a){return a==null?"":G.call(a)}:function(a){return a==null?"":(a+"").replace(k,"").replace(l,"")},makeArray:function(a,b){var c=b||[];if(a!=null){var d=e.type(a);a.length==null||d==="string"||d==="function"||d==="regexp"||e.isWindow(a)?E.call(c,a):e.merge(c,a)}return c},inArray:function(a,b,c){var d;if(b){if(H)return H.call(b,a,c);d=b.length,c=c?c<0?Math.max(0,d+c):c:0;for(;c<d;c++)if(c in b&&b[c]===a)return c}return-1},merge:function(a,c){var d=a.length,e=0;if(typeof c.length=="number")for(var f=c.length;e<f;e++)a[d++]=c[e];else while(c[e]!==b)a[d++]=c[e++];a.length=d;return a},grep:function(a,b,c){var d=[],e;c=!!c;for(var f=0,g=a.length;f<g;f++)e=!!b(a[f],f),c!==e&&d.push(a[f]);return d},map:function(a,c,d){var f,g,h=[],i=0,j=a.length,k=a instanceof e||j!==b&&typeof j=="number"&&(j>0&&a[0]&&a[j-1]||j===0||e.isArray(a));if(k)for(;i<j;i++)f=c(a[i],i,d),f!=null&&(h[h.length]=f);else for(g in a)f=c(a[g],g,d),f!=null&&(h[h.length]=f);return h.concat.apply([],h)},guid:1,proxy:function(a,c){if(typeof c=="string"){var d=a[c];c=a,a=d}if(!e.isFunction(a))return b;var f=F.call(arguments,2),g=function(){return a.apply(c,f.concat(F.call(arguments)))};g.guid=a.guid=a.guid||g.guid||e.guid++;return g},access:function(a,c,d,f,g,h,i){var j,k=d==null,l=0,m=a.length;if(d&&typeof d=="object"){for(l in d)e.access(a,c,l,d[l],1,h,f);g=1}else if(f!==b){j=i===b&&e.isFunction(f),k&&(j?(j=c,c=function(a,b,c){return j.call(e(a),c)}):(c.call(a,f),c=null));if(c)for(;l<m;l++)c(a[l],d,j?f.call(a[l],l,c(a[l],d)):f,i);g=1}return g?a:k?c.call(a):m?c(a[0],d):h},now:function(){return(new Date).getTime()},uaMatch:function(a){a=a.toLowerCase();var b=r.exec(a)||s.exec(a)||t.exec(a)||a.indexOf("compatible")<0&&u.exec(a)||[];return{browser:b[1]||"",version:b[2]||"0"}},sub:function(){function a(b,c){return new a.fn.init(b,c)}e.extend(!0,a,this),a.superclass=this,a.fn=a.prototype=this(),a.fn.constructor=a,a.sub=this.sub,a.fn.init=function(d,f){f&&f instanceof e&&!(f instanceof a)&&(f=a(f));return e.fn.init.call(this,d,f,b)},a.fn.init.prototype=a.fn;var b=a(c);return a},browser:{}}),e.each("Boolean Number String Function Array Date RegExp Object".split(" "),function(a,b){I["[object "+b+"]"]=b.toLowerCase()}),z=e.uaMatch(y),z.browser&&(e.browser[z.browser]=!0,e.browser.version=z.version),e.browser.webkit&&(e.browser.safari=!0),j.test(" ")&&(k=/^[\s\xA0]+/,l=/[\s\xA0]+$/),h=e(c),c.addEventListener?B=function(){c.removeEventListener("DOMContentLoaded",B,!1),e.ready()}:c.attachEvent&&(B=function(){c.readyState==="complete"&&(c.detachEvent("onreadystatechange",B),e.ready())});return e}(),g={};f.Callbacks=function(a){a=a?g[a]||h(a):{};var c=[],d=[],e,i,j,k,l,m,n=function(b){var d,e,g,h,i;for(d=0,e=b.length;d<e;d++)g=b[d],h=f.type(g),h==="array"?n(g):h==="function"&&(!a.unique||!p.has(g))&&c.push(g)},o=function(b,f){f=f||[],e=!a.memory||[b,f],i=!0,j=!0,m=k||0,k=0,l=c.length;for(;c&&m<l;m++)if(c[m].apply(b,f)===!1&&a.stopOnFalse){e=!0;break}j=!1,c&&(a.once?e===!0?p.disable():c=[]:d&&d.length&&(e=d.shift(),p.fireWith(e[0],e[1])))},p={add:function(){if(c){var a=c.length;n(arguments),j?l=c.length:e&&e!==!0&&(k=a,o(e[0],e[1]))}return this},remove:function(){if(c){var b=arguments,d=0,e=b.length;for(;d<e;d++)for(var f=0;f<c.length;f++)if(b[d]===c[f]){j&&f<=l&&(l--,f<=m&&m--),c.splice(f--,1);if(a.unique)break}}return this},has:function(a){if(c){var b=0,d=c.length;for(;b<d;b++)if(a===c[b])return!0}return!1},empty:function(){c=[];return this},disable:function(){c=d=e=b;return this},disabled:function(){return!c},lock:function(){d=b,(!e||e===!0)&&p.disable();return this},locked:function(){return!d},fireWith:function(b,c){d&&(j?a.once||d.push([b,c]):(!a.once||!e)&&o(b,c));return this},fire:function(){p.fireWith(this,arguments);return this},fired:function(){return!!i}};return p};var i=[].slice;f.extend({Deferred:function(a){var b=f.Callbacks("once memory"),c=f.Callbacks("once memory"),d=f.Callbacks("memory"),e="pending",g={resolve:b,reject:c,notify:d},h={done:b.add,fail:c.add,progress:d.add,state:function(){return e},isResolved:b.fired,isRejected:c.fired,then:function(a,b,c){i.done(a).fail(b).progress(c);return this},always:function(){i.done.apply(i,arguments).fail.apply(i,arguments);return this},pipe:function(a,b,c){return f.Deferred(function(d){f.each({done:[a,"resolve"],fail:[b,"reject"],progress:[c,"notify"]},function(a,b){var c=b[0],e=b[1],g;f.isFunction(c)?i[a](function(){g=c.apply(this,arguments),g&&f.isFunction(g.promise)?g.promise().then(d.resolve,d.reject,d.notify):d[e+"With"](this===i?d:this,[g])}):i[a](d[e])})}).promise()},promise:function(a){if(a==null)a=h;else for(var b in h)a[b]=h[b];return a}},i=h.promise({}),j;for(j in g)i[j]=g[j].fire,i[j+"With"]=g[j].fireWith;i.done(function(){e="resolved"},c.disable,d.lock).fail(function(){e="rejected"},b.disable,d.lock),a&&a.call(i,i);return i},when:function(a){function m(a){return function(b){e[a]=arguments.length>1?i.call(arguments,0):b,j.notifyWith(k,e)}}function l(a){return function(c){b[a]=arguments.length>1?i.call(arguments,0):c,--g||j.resolveWith(j,b)}}var b=i.call(arguments,0),c=0,d=b.length,e=Array(d),g=d,h=d,j=d<=1&&a&&f.isFunction(a.promise)?a:f.Deferred(),k=j.promise();if(d>1){for(;c<d;c++)b[c]&&b[c].promise&&f.isFunction(b[c].promise)?b[c].promise().then(l(c),j.reject,m(c)):--g;g||j.resolveWith(j,b)}else j!==a&&j.resolveWith(j,d?[a]:[]);return k}}),f.support=function(){var b,d,e,g,h,i,j,k,l,m,n,o,p=c.createElement("div"),q=c.documentElement;p.setAttribute("className","t"),p.innerHTML="   <link/><table></table><a href='/a' style='top:1px;float:left;opacity:.55;'>a</a><input type='checkbox'/>",d=p.getElementsByTagName("*"),e=p.getElementsByTagName("a")[0];if(!d||!d.length||!e)return{};g=c.createElement("select"),h=g.appendChild(c.createElement("option")),i=p.getElementsByTagName("input")[0],b={leadingWhitespace:p.firstChild.nodeType===3,tbody:!p.getElementsByTagName("tbody").length,htmlSerialize:!!p.getElementsByTagName("link").length,style:/top/.test(e.getAttribute("style")),hrefNormalized:e.getAttribute("href")==="/a",opacity:/^0.55/.test(e.style.opacity),cssFloat:!!e.style.cssFloat,checkOn:i.value==="on",optSelected:h.selected,getSetAttribute:p.className!=="t",enctype:!!c.createElement("form").enctype,html5Clone:c.createElement("nav").cloneNode(!0).outerHTML!=="<:nav></:nav>",submitBubbles:!0,changeBubbles:!0,focusinBubbles:!1,deleteExpando:!0,noCloneEvent:!0,inlineBlockNeedsLayout:!1,shrinkWrapBlocks:!1,reliableMarginRight:!0,pixelMargin:!0},f.boxModel=b.boxModel=c.compatMode==="CSS1Compat",i.checked=!0,b.noCloneChecked=i.cloneNode(!0).checked,g.disabled=!0,b.optDisabled=!h.disabled;try{delete p.test}catch(r){b.deleteExpando=!1}!p.addEventListener&&p.attachEvent&&p.fireEvent&&(p.attachEvent("onclick",function(){b.noCloneEvent=!1}),p.cloneNode(!0).fireEvent("onclick")),i=c.createElement("input"),i.value="t",i.setAttribute("type","radio"),b.radioValue=i.value==="t",i.setAttribute("checked","checked"),i.setAttribute("name","t"),p.appendChild(i),j=c.createDocumentFragment(),j.appendChild(p.lastChild),b.checkClone=j.cloneNode(!0).cloneNode(!0).lastChild.checked,b.appendChecked=i.checked,j.removeChild(i),j.appendChild(p);if(p.attachEvent)for(n in{submit:1,change:1,focusin:1})m="on"+n,o=m in p,o||(p.setAttribute(m,"return;"),o=typeof p[m]=="function"),b[n+"Bubbles"]=o;j.removeChild(p),j=g=h=p=i=null,f(function(){var d,e,g,h,i,j,l,m,n,q,r,s,t,u=c.getElementsByTagName("body")[0];!u||(m=1,t="padding:0;margin:0;border:",r="position:absolute;top:0;left:0;width:1px;height:1px;",s=t+"0;visibility:hidden;",n="style='"+r+t+"5px solid #000;",q="<div "+n+"display:block;'><div style='"+t+"0;display:block;overflow:hidden;'></div></div>"+"<table "+n+"' cellpadding='0' cellspacing='0'>"+"<tr><td></td></tr></table>",d=c.createElement("div"),d.style.cssText=s+"width:0;height:0;position:static;top:0;margin-top:"+m+"px",u.insertBefore(d,u.firstChild),p=c.createElement("div"),d.appendChild(p),p.innerHTML="<table><tr><td style='"+t+"0;display:none'></td><td>t</td></tr></table>",k=p.getElementsByTagName("td"),o=k[0].offsetHeight===0,k[0].style.display="",k[1].style.display="none",b.reliableHiddenOffsets=o&&k[0].offsetHeight===0,a.getComputedStyle&&(p.innerHTML="",l=c.createElement("div"),l.style.width="0",l.style.marginRight="0",p.style.width="2px",p.appendChild(l),b.reliableMarginRight=(parseInt((a.getComputedStyle(l,null)||{marginRight:0}).marginRight,10)||0)===0),typeof p.style.zoom!="undefined"&&(p.innerHTML="",p.style.width=p.style.padding="1px",p.style.border=0,p.style.overflow="hidden",p.style.display="inline",p.style.zoom=1,b.inlineBlockNeedsLayout=p.offsetWidth===3,p.style.display="block",p.style.overflow="visible",p.innerHTML="<div style='width:5px;'></div>",b.shrinkWrapBlocks=p.offsetWidth!==3),p.style.cssText=r+s,p.innerHTML=q,e=p.firstChild,g=e.firstChild,i=e.nextSibling.firstChild.firstChild,j={doesNotAddBorder:g.offsetTop!==5,doesAddBorderForTableAndCells:i.offsetTop===5},g.style.position="fixed",g.style.top="20px",j.fixedPosition=g.offsetTop===20||g.offsetTop===15,g.style.position=g.style.top="",e.style.overflow="hidden",e.style.position="relative",j.subtractsBorderForOverflowNotVisible=g.offsetTop===-5,j.doesNotIncludeMarginInBodyOffset=u.offsetTop!==m,a.getComputedStyle&&(p.style.marginTop="1%",b.pixelMargin=(a.getComputedStyle(p,null)||{marginTop:0}).marginTop!=="1%"),typeof d.style.zoom!="undefined"&&(d.style.zoom=1),u.removeChild(d),l=p=d=null,f.extend(b,j))});return b}();var j=/^(?:\{.*\}|\[.*\])$/,k=/([A-Z])/g;f.extend({cache:{},uuid:0,expando:"jQuery"+(f.fn.jquery+Math.random()).replace(/\D/g,""),noData:{embed:!0,object:"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",applet:!0},hasData:function(a){a=a.nodeType?f.cache[a[f.expando]]:a[f.expando];return!!a&&!m(a)},data:function(a,c,d,e){if(!!f.acceptData(a)){var g,h,i,j=f.expando,k=typeof c=="string",l=a.nodeType,m=l?f.cache:a,n=l?a[j]:a[j]&&j,o=c==="events";if((!n||!m[n]||!o&&!e&&!m[n].data)&&k&&d===b)return;n||(l?a[j]=n=++f.uuid:n=j),m[n]||(m[n]={},l||(m[n].toJSON=f.noop));if(typeof c=="object"||typeof c=="function")e?m[n]=f.extend(m[n],c):m[n].data=f.extend(m[n].data,c);g=h=m[n],e||(h.data||(h.data={}),h=h.data),d!==b&&(h[f.camelCase(c)]=d);if(o&&!h[c])return g.events;k?(i=h[c],i==null&&(i=h[f.camelCase(c)])):i=h;return i}},removeData:function(a,b,c){if(!!f.acceptData(a)){var d,e,g,h=f.expando,i=a.nodeType,j=i?f.cache:a,k=i?a[h]:h;if(!j[k])return;if(b){d=c?j[k]:j[k].data;if(d){f.isArray(b)||(b in d?b=[b]:(b=f.camelCase(b),b in d?b=[b]:b=b.split(" ")));for(e=0,g=b.length;e<g;e++)delete d[b[e]];if(!(c?m:f.isEmptyObject)(d))return}}if(!c){delete j[k].data;if(!m(j[k]))return}f.support.deleteExpando||!j.setInterval?delete j[k]:j[k]=null,i&&(f.support.deleteExpando?delete a[h]:a.removeAttribute?a.removeAttribute(h):a[h]=null)}},_data:function(a,b,c){return f.data(a,b,c,!0)},acceptData:function(a){if(a.nodeName){var b=f.noData[a.nodeName.toLowerCase()];if(b)return b!==!0&&a.getAttribute("classid")===b}return!0}}),f.fn.extend({data:function(a,c){var d,e,g,h,i,j=this[0],k=0,m=null;if(a===b){if(this.length){m=f.data(j);if(j.nodeType===1&&!f._data(j,"parsedAttrs")){g=j.attributes;for(i=g.length;k<i;k++)h=g[k].name,h.indexOf("data-")===0&&(h=f.camelCase(h.substring(5)),l(j,h,m[h]));f._data(j,"parsedAttrs",!0)}}return m}if(typeof a=="object")return this.each(function(){f.data(this,a)});d=a.split(".",2),d[1]=d[1]?"."+d[1]:"",e=d[1]+"!";return f.access(this,function(c){if(c===b){m=this.triggerHandler("getData"+e,[d[0]]),m===b&&j&&(m=f.data(j,a),m=l(j,a,m));return m===b&&d[1]?this.data(d[0]):m}d[1]=c,this.each(function(){var b=f(this);b.triggerHandler("setData"+e,d),f.data(this,a,c),b.triggerHandler("changeData"+e,d)})},null,c,arguments.length>1,null,!1)},removeData:function(a){return this.each(function(){f.removeData(this,a)})}}),f.extend({_mark:function(a,b){a&&(b=(b||"fx")+"mark",f._data(a,b,(f._data(a,b)||0)+1))},_unmark:function(a,b,c){a!==!0&&(c=b,b=a,a=!1);if(b){c=c||"fx";var d=c+"mark",e=a?0:(f._data(b,d)||1)-1;e?f._data(b,d,e):(f.removeData(b,d,!0),n(b,c,"mark"))}},queue:function(a,b,c){var d;if(a){b=(b||"fx")+"queue",d=f._data(a,b),c&&(!d||f.isArray(c)?d=f._data(a,b,f.makeArray(c)):d.push(c));return d||[]}},dequeue:function(a,b){b=b||"fx";var c=f.queue(a,b),d=c.shift(),e={};d==="inprogress"&&(d=c.shift()),d&&(b==="fx"&&c.unshift("inprogress"),f._data(a,b+".run",e),d.call(a,function(){f.dequeue(a,b)},e)),c.length||(f.removeData(a,b+"queue "+b+".run",!0),n(a,b,"queue"))}}),f.fn.extend({queue:function(a,c){var d=2;typeof a!="string"&&(c=a,a="fx",d--);if(arguments.length<d)return f.queue(this[0],a);return c===b?this:this.each(function(){var b=f.queue(this,a,c);a==="fx"&&b[0]!=="inprogress"&&f.dequeue(this,a)})},dequeue:function(a){return this.each(function(){f.dequeue(this,a)})},delay:function(a,b){a=f.fx?f.fx.speeds[a]||a:a,b=b||"fx";return this.queue(b,function(b,c){var d=setTimeout(b,a);c.stop=function(){clearTimeout(d)}})},clearQueue:function(a){return this.queue(a||"fx",[])},promise:function(a,c){function m(){--h||d.resolveWith(e,[e])}typeof a!="string"&&(c=a,a=b),a=a||"fx";var d=f.Deferred(),e=this,g=e.length,h=1,i=a+"defer",j=a+"queue",k=a+"mark",l;while(g--)if(l=f.data(e[g],i,b,!0)||(f.data(e[g],j,b,!0)||f.data(e[g],k,b,!0))&&f.data(e[g],i,f.Callbacks("once memory"),!0))h++,l.add(m);m();return d.promise(c)}});var o=/[\n\t\r]/g,p=/\s+/,q=/\r/g,r=/^(?:button|input)$/i,s=/^(?:button|input|object|select|textarea)$/i,t=/^a(?:rea)?$/i,u=/^(?:autofocus|autoplay|async|checked|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped|selected)$/i,v=f.support.getSetAttribute,w,x,y;f.fn.extend({attr:function(a,b){return f.access(this,f.attr,a,b,arguments.length>1)},removeAttr:function(a){return this.each(function(){f.removeAttr(this,a)})},prop:function(a,b){return f.access(this,f.prop,a,b,arguments.length>1)},removeProp:function(a){a=f.propFix[a]||a;return this.each(function(){try{this[a]=b,delete this[a]}catch(c){}})},addClass:function(a){var b,c,d,e,g,h,i;if(f.isFunction(a))return this.each(function(b){f(this).addClass(a.call(this,b,this.className))});if(a&&typeof a=="string"){b=a.split(p);for(c=0,d=this.length;c<d;c++){e=this[c];if(e.nodeType===1)if(!e.className&&b.length===1)e.className=a;else{g=" "+e.className+" ";for(h=0,i=b.length;h<i;h++)~g.indexOf(" "+b[h]+" ")||(g+=b[h]+" ");e.className=f.trim(g)}}}return this},removeClass:function(a){var c,d,e,g,h,i,j;if(f.isFunction(a))return this.each(function(b){f(this).removeClass(a.call(this,b,this.className))});if(a&&typeof a=="string"||a===b){c=(a||"").split(p);for(d=0,e=this.length;d<e;d++){g=this[d];if(g.nodeType===1&&g.className)if(a){h=(" "+g.className+" ").replace(o," ");for(i=0,j=c.length;i<j;i++)h=h.replace(" "+c[i]+" "," ");g.className=f.trim(h)}else g.className=""}}return this},toggleClass:function(a,b){var c=typeof a,d=typeof b=="boolean";if(f.isFunction(a))return this.each(function(c){f(this).toggleClass(a.call(this,c,this.className,b),b)});return this.each(function(){if(c==="string"){var e,g=0,h=f(this),i=b,j=a.split(p);while(e=j[g++])i=d?i:!h.hasClass(e),h[i?"addClass":"removeClass"](e)}else if(c==="undefined"||c==="boolean")this.className&&f._data(this,"__className__",this.className),this.className=this.className||a===!1?"":f._data(this,"__className__")||""})},hasClass:function(a){var b=" "+a+" ",c=0,d=this.length;for(;c<d;c++)if(this[c].nodeType===1&&(" "+this[c].className+" ").replace(o," ").indexOf(b)>-1)return!0;return!1},val:function(a){var c,d,e,g=this[0];{if(!!arguments.length){e=f.isFunction(a);return this.each(function(d){var g=f(this),h;if(this.nodeType===1){e?h=a.call(this,d,g.val()):h=a,h==null?h="":typeof h=="number"?h+="":f.isArray(h)&&(h=f.map(h,function(a){return a==null?"":a+""})),c=f.valHooks[this.type]||f.valHooks[this.nodeName.toLowerCase()];if(!c||!("set"in c)||c.set(this,h,"value")===b)this.value=h}})}if(g){c=f.valHooks[g.type]||f.valHooks[g.nodeName.toLowerCase()];if(c&&"get"in c&&(d=c.get(g,"value"))!==b)return d;d=g.value;return typeof d=="string"?d.replace(q,""):d==null?"":d}}}}),f.extend({valHooks:{option:{get:function(a){var b=a.attributes.value;return!b||b.specified?a.value:a.text}},select:{get:function(a){var b,c,d,e,g=a.selectedIndex,h=[],i=a.options,j=a.type==="select-one";if(g<0)return null;c=j?g:0,d=j?g+1:i.length;for(;c<d;c++){e=i[c];if(e.selected&&(f.support.optDisabled?!e.disabled:e.getAttribute("disabled")===null)&&(!e.parentNode.disabled||!f.nodeName(e.parentNode,"optgroup"))){b=f(e).val();if(j)return b;h.push(b)}}if(j&&!h.length&&i.length)return f(i[g]).val();return h},set:function(a,b){var c=f.makeArray(b);f(a).find("option").each(function(){this.selected=f.inArray(f(this).val(),c)>=0}),c.length||(a.selectedIndex=-1);return c}}},attrFn:{val:!0,css:!0,html:!0,text:!0,data:!0,width:!0,height:!0,offset:!0},attr:function(a,c,d,e){var g,h,i,j=a.nodeType;if(!!a&&j!==3&&j!==8&&j!==2){if(e&&c in f.attrFn)return f(a)[c](d);if(typeof a.getAttribute=="undefined")return f.prop(a,c,d);i=j!==1||!f.isXMLDoc(a),i&&(c=c.toLowerCase(),h=f.attrHooks[c]||(u.test(c)?x:w));if(d!==b){if(d===null){f.removeAttr(a,c);return}if(h&&"set"in h&&i&&(g=h.set(a,d,c))!==b)return g;a.setAttribute(c,""+d);return d}if(h&&"get"in h&&i&&(g=h.get(a,c))!==null)return g;g=a.getAttribute(c);return g===null?b:g}},removeAttr:function(a,b){var c,d,e,g,h,i=0;if(b&&a.nodeType===1){d=b.toLowerCase().split(p),g=d.length;for(;i<g;i++)e=d[i],e&&(c=f.propFix[e]||e,h=u.test(e),h||f.attr(a,e,""),a.removeAttribute(v?e:c),h&&c in a&&(a[c]=!1))}},attrHooks:{type:{set:function(a,b){if(r.test(a.nodeName)&&a.parentNode)f.error("type property can't be changed");else if(!f.support.radioValue&&b==="radio"&&f.nodeName(a,"input")){var c=a.value;a.setAttribute("type",b),c&&(a.value=c);return b}}},value:{get:function(a,b){if(w&&f.nodeName(a,"button"))return w.get(a,b);return b in a?a.value:null},set:function(a,b,c){if(w&&f.nodeName(a,"button"))return w.set(a,b,c);a.value=b}}},propFix:{tabindex:"tabIndex",readonly:"readOnly","for":"htmlFor","class":"className",maxlength:"maxLength",cellspacing:"cellSpacing",cellpadding:"cellPadding",rowspan:"rowSpan",colspan:"colSpan",usemap:"useMap",frameborder:"frameBorder",contenteditable:"contentEditable"},prop:function(a,c,d){var e,g,h,i=a.nodeType;if(!!a&&i!==3&&i!==8&&i!==2){h=i!==1||!f.isXMLDoc(a),h&&(c=f.propFix[c]||c,g=f.propHooks[c]);return d!==b?g&&"set"in g&&(e=g.set(a,d,c))!==b?e:a[c]=d:g&&"get"in g&&(e=g.get(a,c))!==null?e:a[c]}},propHooks:{tabIndex:{get:function(a){var c=a.getAttributeNode("tabindex");return c&&c.specified?parseInt(c.value,10):s.test(a.nodeName)||t.test(a.nodeName)&&a.href?0:b}}}}),f.attrHooks.tabindex=f.propHooks.tabIndex,x={get:function(a,c){var d,e=f.prop(a,c);return e===!0||typeof e!="boolean"&&(d=a.getAttributeNode(c))&&d.nodeValue!==!1?c.toLowerCase():b},set:function(a,b,c){var d;b===!1?f.removeAttr(a,c):(d=f.propFix[c]||c,d in a&&(a[d]=!0),a.setAttribute(c,c.toLowerCase()));return c}},v||(y={name:!0,id:!0,coords:!0},w=f.valHooks.button={get:function(a,c){var d;d=a.getAttributeNode(c);return d&&(y[c]?d.nodeValue!=="":d.specified)?d.nodeValue:b},set:function(a,b,d){var e=a.getAttributeNode(d);e||(e=c.createAttribute(d),a.setAttributeNode(e));return e.nodeValue=b+""}},f.attrHooks.tabindex.set=w.set,f.each(["width","height"],function(a,b){f.attrHooks[b]=f.extend(f.attrHooks[b],{set:function(a,c){if(c===""){a.setAttribute(b,"auto");return c}}})}),f.attrHooks.contenteditable={get:w.get,set:function(a,b,c){b===""&&(b="false"),w.set(a,b,c)}}),f.support.hrefNormalized||f.each(["href","src","width","height"],function(a,c){f.attrHooks[c]=f.extend(f.attrHooks[c],{get:function(a){var d=a.getAttribute(c,2);return d===null?b:d}})}),f.support.style||(f.attrHooks.style={get:function(a){return a.style.cssText.toLowerCase()||b},set:function(a,b){return a.style.cssText=""+b}}),f.support.optSelected||(f.propHooks.selected=f.extend(f.propHooks.selected,{get:function(a){var b=a.parentNode;b&&(b.selectedIndex,b.parentNode&&b.parentNode.selectedIndex);return null}})),f.support.enctype||(f.propFix.enctype="encoding"),f.support.checkOn||f.each(["radio","checkbox"],function(){f.valHooks[this]={get:function(a){return a.getAttribute("value")===null?"on":a.value}}}),f.each(["radio","checkbox"],function(){f.valHooks[this]=f.extend(f.valHooks[this],{set:function(a,b){if(f.isArray(b))return a.checked=f.inArray(f(a).val(),b)>=0}})});var z=/^(?:textarea|input|select)$/i,A=/^([^\.]*)?(?:\.(.+))?$/,B=/(?:^|\s)hover(\.\S+)?\b/,C=/^key/,D=/^(?:mouse|contextmenu)|click/,E=/^(?:focusinfocus|focusoutblur)$/,F=/^(\w*)(?:#([\w\-]+))?(?:\.([\w\-]+))?$/,G=function(
a){var b=F.exec(a);b&&(b[1]=(b[1]||"").toLowerCase(),b[3]=b[3]&&new RegExp("(?:^|\\s)"+b[3]+"(?:\\s|$)"));return b},H=function(a,b){var c=a.attributes||{};return(!b[1]||a.nodeName.toLowerCase()===b[1])&&(!b[2]||(c.id||{}).value===b[2])&&(!b[3]||b[3].test((c["class"]||{}).value))},I=function(a){return f.event.special.hover?a:a.replace(B,"mouseenter$1 mouseleave$1")};f.event={add:function(a,c,d,e,g){var h,i,j,k,l,m,n,o,p,q,r,s;if(!(a.nodeType===3||a.nodeType===8||!c||!d||!(h=f._data(a)))){d.handler&&(p=d,d=p.handler,g=p.selector),d.guid||(d.guid=f.guid++),j=h.events,j||(h.events=j={}),i=h.handle,i||(h.handle=i=function(a){return typeof f!="undefined"&&(!a||f.event.triggered!==a.type)?f.event.dispatch.apply(i.elem,arguments):b},i.elem=a),c=f.trim(I(c)).split(" ");for(k=0;k<c.length;k++){l=A.exec(c[k])||[],m=l[1],n=(l[2]||"").split(".").sort(),s=f.event.special[m]||{},m=(g?s.delegateType:s.bindType)||m,s=f.event.special[m]||{},o=f.extend({type:m,origType:l[1],data:e,handler:d,guid:d.guid,selector:g,quick:g&&G(g),namespace:n.join(".")},p),r=j[m];if(!r){r=j[m]=[],r.delegateCount=0;if(!s.setup||s.setup.call(a,e,n,i)===!1)a.addEventListener?a.addEventListener(m,i,!1):a.attachEvent&&a.attachEvent("on"+m,i)}s.add&&(s.add.call(a,o),o.handler.guid||(o.handler.guid=d.guid)),g?r.splice(r.delegateCount++,0,o):r.push(o),f.event.global[m]=!0}a=null}},global:{},remove:function(a,b,c,d,e){var g=f.hasData(a)&&f._data(a),h,i,j,k,l,m,n,o,p,q,r,s;if(!!g&&!!(o=g.events)){b=f.trim(I(b||"")).split(" ");for(h=0;h<b.length;h++){i=A.exec(b[h])||[],j=k=i[1],l=i[2];if(!j){for(j in o)f.event.remove(a,j+b[h],c,d,!0);continue}p=f.event.special[j]||{},j=(d?p.delegateType:p.bindType)||j,r=o[j]||[],m=r.length,l=l?new RegExp("(^|\\.)"+l.split(".").sort().join("\\.(?:.*\\.)?")+"(\\.|$)"):null;for(n=0;n<r.length;n++)s=r[n],(e||k===s.origType)&&(!c||c.guid===s.guid)&&(!l||l.test(s.namespace))&&(!d||d===s.selector||d==="**"&&s.selector)&&(r.splice(n--,1),s.selector&&r.delegateCount--,p.remove&&p.remove.call(a,s));r.length===0&&m!==r.length&&((!p.teardown||p.teardown.call(a,l)===!1)&&f.removeEvent(a,j,g.handle),delete o[j])}f.isEmptyObject(o)&&(q=g.handle,q&&(q.elem=null),f.removeData(a,["events","handle"],!0))}},customEvent:{getData:!0,setData:!0,changeData:!0},trigger:function(c,d,e,g){if(!e||e.nodeType!==3&&e.nodeType!==8){var h=c.type||c,i=[],j,k,l,m,n,o,p,q,r,s;if(E.test(h+f.event.triggered))return;h.indexOf("!")>=0&&(h=h.slice(0,-1),k=!0),h.indexOf(".")>=0&&(i=h.split("."),h=i.shift(),i.sort());if((!e||f.event.customEvent[h])&&!f.event.global[h])return;c=typeof c=="object"?c[f.expando]?c:new f.Event(h,c):new f.Event(h),c.type=h,c.isTrigger=!0,c.exclusive=k,c.namespace=i.join("."),c.namespace_re=c.namespace?new RegExp("(^|\\.)"+i.join("\\.(?:.*\\.)?")+"(\\.|$)"):null,o=h.indexOf(":")<0?"on"+h:"";if(!e){j=f.cache;for(l in j)j[l].events&&j[l].events[h]&&f.event.trigger(c,d,j[l].handle.elem,!0);return}c.result=b,c.target||(c.target=e),d=d!=null?f.makeArray(d):[],d.unshift(c),p=f.event.special[h]||{};if(p.trigger&&p.trigger.apply(e,d)===!1)return;r=[[e,p.bindType||h]];if(!g&&!p.noBubble&&!f.isWindow(e)){s=p.delegateType||h,m=E.test(s+h)?e:e.parentNode,n=null;for(;m;m=m.parentNode)r.push([m,s]),n=m;n&&n===e.ownerDocument&&r.push([n.defaultView||n.parentWindow||a,s])}for(l=0;l<r.length&&!c.isPropagationStopped();l++)m=r[l][0],c.type=r[l][1],q=(f._data(m,"events")||{})[c.type]&&f._data(m,"handle"),q&&q.apply(m,d),q=o&&m[o],q&&f.acceptData(m)&&q.apply(m,d)===!1&&c.preventDefault();c.type=h,!g&&!c.isDefaultPrevented()&&(!p._default||p._default.apply(e.ownerDocument,d)===!1)&&(h!=="click"||!f.nodeName(e,"a"))&&f.acceptData(e)&&o&&e[h]&&(h!=="focus"&&h!=="blur"||c.target.offsetWidth!==0)&&!f.isWindow(e)&&(n=e[o],n&&(e[o]=null),f.event.triggered=h,e[h](),f.event.triggered=b,n&&(e[o]=n));return c.result}},dispatch:function(c){c=f.event.fix(c||a.event);var d=(f._data(this,"events")||{})[c.type]||[],e=d.delegateCount,g=[].slice.call(arguments,0),h=!c.exclusive&&!c.namespace,i=f.event.special[c.type]||{},j=[],k,l,m,n,o,p,q,r,s,t,u;g[0]=c,c.delegateTarget=this;if(!i.preDispatch||i.preDispatch.call(this,c)!==!1){if(e&&(!c.button||c.type!=="click")){n=f(this),n.context=this.ownerDocument||this;for(m=c.target;m!=this;m=m.parentNode||this)if(m.disabled!==!0){p={},r=[],n[0]=m;for(k=0;k<e;k++)s=d[k],t=s.selector,p[t]===b&&(p[t]=s.quick?H(m,s.quick):n.is(t)),p[t]&&r.push(s);r.length&&j.push({elem:m,matches:r})}}d.length>e&&j.push({elem:this,matches:d.slice(e)});for(k=0;k<j.length&&!c.isPropagationStopped();k++){q=j[k],c.currentTarget=q.elem;for(l=0;l<q.matches.length&&!c.isImmediatePropagationStopped();l++){s=q.matches[l];if(h||!c.namespace&&!s.namespace||c.namespace_re&&c.namespace_re.test(s.namespace))c.data=s.data,c.handleObj=s,o=((f.event.special[s.origType]||{}).handle||s.handler).apply(q.elem,g),o!==b&&(c.result=o,o===!1&&(c.preventDefault(),c.stopPropagation()))}}i.postDispatch&&i.postDispatch.call(this,c);return c.result}},props:"attrChange attrName relatedNode srcElement altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),fixHooks:{},keyHooks:{props:"char charCode key keyCode".split(" "),filter:function(a,b){a.which==null&&(a.which=b.charCode!=null?b.charCode:b.keyCode);return a}},mouseHooks:{props:"button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),filter:function(a,d){var e,f,g,h=d.button,i=d.fromElement;a.pageX==null&&d.clientX!=null&&(e=a.target.ownerDocument||c,f=e.documentElement,g=e.body,a.pageX=d.clientX+(f&&f.scrollLeft||g&&g.scrollLeft||0)-(f&&f.clientLeft||g&&g.clientLeft||0),a.pageY=d.clientY+(f&&f.scrollTop||g&&g.scrollTop||0)-(f&&f.clientTop||g&&g.clientTop||0)),!a.relatedTarget&&i&&(a.relatedTarget=i===a.target?d.toElement:i),!a.which&&h!==b&&(a.which=h&1?1:h&2?3:h&4?2:0);return a}},fix:function(a){if(a[f.expando])return a;var d,e,g=a,h=f.event.fixHooks[a.type]||{},i=h.props?this.props.concat(h.props):this.props;a=f.Event(g);for(d=i.length;d;)e=i[--d],a[e]=g[e];a.target||(a.target=g.srcElement||c),a.target.nodeType===3&&(a.target=a.target.parentNode),a.metaKey===b&&(a.metaKey=a.ctrlKey);return h.filter?h.filter(a,g):a},special:{ready:{setup:f.bindReady},load:{noBubble:!0},focus:{delegateType:"focusin"},blur:{delegateType:"focusout"},beforeunload:{setup:function(a,b,c){f.isWindow(this)&&(this.onbeforeunload=c)},teardown:function(a,b){this.onbeforeunload===b&&(this.onbeforeunload=null)}}},simulate:function(a,b,c,d){var e=f.extend(new f.Event,c,{type:a,isSimulated:!0,originalEvent:{}});d?f.event.trigger(e,null,b):f.event.dispatch.call(b,e),e.isDefaultPrevented()&&c.preventDefault()}},f.event.handle=f.event.dispatch,f.removeEvent=c.removeEventListener?function(a,b,c){a.removeEventListener&&a.removeEventListener(b,c,!1)}:function(a,b,c){a.detachEvent&&a.detachEvent("on"+b,c)},f.Event=function(a,b){if(!(this instanceof f.Event))return new f.Event(a,b);a&&a.type?(this.originalEvent=a,this.type=a.type,this.isDefaultPrevented=a.defaultPrevented||a.returnValue===!1||a.getPreventDefault&&a.getPreventDefault()?K:J):this.type=a,b&&f.extend(this,b),this.timeStamp=a&&a.timeStamp||f.now(),this[f.expando]=!0},f.Event.prototype={preventDefault:function(){this.isDefaultPrevented=K;var a=this.originalEvent;!a||(a.preventDefault?a.preventDefault():a.returnValue=!1)},stopPropagation:function(){this.isPropagationStopped=K;var a=this.originalEvent;!a||(a.stopPropagation&&a.stopPropagation(),a.cancelBubble=!0)},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=K,this.stopPropagation()},isDefaultPrevented:J,isPropagationStopped:J,isImmediatePropagationStopped:J},f.each({mouseenter:"mouseover",mouseleave:"mouseout"},function(a,b){f.event.special[a]={delegateType:b,bindType:b,handle:function(a){var c=this,d=a.relatedTarget,e=a.handleObj,g=e.selector,h;if(!d||d!==c&&!f.contains(c,d))a.type=e.origType,h=e.handler.apply(this,arguments),a.type=b;return h}}}),f.support.submitBubbles||(f.event.special.submit={setup:function(){if(f.nodeName(this,"form"))return!1;f.event.add(this,"click._submit keypress._submit",function(a){var c=a.target,d=f.nodeName(c,"input")||f.nodeName(c,"button")?c.form:b;d&&!d._submit_attached&&(f.event.add(d,"submit._submit",function(a){a._submit_bubble=!0}),d._submit_attached=!0)})},postDispatch:function(a){a._submit_bubble&&(delete a._submit_bubble,this.parentNode&&!a.isTrigger&&f.event.simulate("submit",this.parentNode,a,!0))},teardown:function(){if(f.nodeName(this,"form"))return!1;f.event.remove(this,"._submit")}}),f.support.changeBubbles||(f.event.special.change={setup:function(){if(z.test(this.nodeName)){if(this.type==="checkbox"||this.type==="radio")f.event.add(this,"propertychange._change",function(a){a.originalEvent.propertyName==="checked"&&(this._just_changed=!0)}),f.event.add(this,"click._change",function(a){this._just_changed&&!a.isTrigger&&(this._just_changed=!1,f.event.simulate("change",this,a,!0))});return!1}f.event.add(this,"beforeactivate._change",function(a){var b=a.target;z.test(b.nodeName)&&!b._change_attached&&(f.event.add(b,"change._change",function(a){this.parentNode&&!a.isSimulated&&!a.isTrigger&&f.event.simulate("change",this.parentNode,a,!0)}),b._change_attached=!0)})},handle:function(a){var b=a.target;if(this!==b||a.isSimulated||a.isTrigger||b.type!=="radio"&&b.type!=="checkbox")return a.handleObj.handler.apply(this,arguments)},teardown:function(){f.event.remove(this,"._change");return z.test(this.nodeName)}}),f.support.focusinBubbles||f.each({focus:"focusin",blur:"focusout"},function(a,b){var d=0,e=function(a){f.event.simulate(b,a.target,f.event.fix(a),!0)};f.event.special[b]={setup:function(){d++===0&&c.addEventListener(a,e,!0)},teardown:function(){--d===0&&c.removeEventListener(a,e,!0)}}}),f.fn.extend({on:function(a,c,d,e,g){var h,i;if(typeof a=="object"){typeof c!="string"&&(d=d||c,c=b);for(i in a)this.on(i,c,d,a[i],g);return this}d==null&&e==null?(e=c,d=c=b):e==null&&(typeof c=="string"?(e=d,d=b):(e=d,d=c,c=b));if(e===!1)e=J;else if(!e)return this;g===1&&(h=e,e=function(a){f().off(a);return h.apply(this,arguments)},e.guid=h.guid||(h.guid=f.guid++));return this.each(function(){f.event.add(this,a,e,d,c)})},one:function(a,b,c,d){return this.on(a,b,c,d,1)},off:function(a,c,d){if(a&&a.preventDefault&&a.handleObj){var e=a.handleObj;f(a.delegateTarget).off(e.namespace?e.origType+"."+e.namespace:e.origType,e.selector,e.handler);return this}if(typeof a=="object"){for(var g in a)this.off(g,c,a[g]);return this}if(c===!1||typeof c=="function")d=c,c=b;d===!1&&(d=J);return this.each(function(){f.event.remove(this,a,d,c)})},bind:function(a,b,c){return this.on(a,null,b,c)},unbind:function(a,b){return this.off(a,null,b)},live:function(a,b,c){f(this.context).on(a,this.selector,b,c);return this},die:function(a,b){f(this.context).off(a,this.selector||"**",b);return this},delegate:function(a,b,c,d){return this.on(b,a,c,d)},undelegate:function(a,b,c){return arguments.length==1?this.off(a,"**"):this.off(b,a,c)},trigger:function(a,b){return this.each(function(){f.event.trigger(a,b,this)})},triggerHandler:function(a,b){if(this[0])return f.event.trigger(a,b,this[0],!0)},toggle:function(a){var b=arguments,c=a.guid||f.guid++,d=0,e=function(c){var e=(f._data(this,"lastToggle"+a.guid)||0)%d;f._data(this,"lastToggle"+a.guid,e+1),c.preventDefault();return b[e].apply(this,arguments)||!1};e.guid=c;while(d<b.length)b[d++].guid=c;return this.click(e)},hover:function(a,b){return this.mouseenter(a).mouseleave(b||a)}}),f.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "),function(a,b){f.fn[b]=function(a,c){c==null&&(c=a,a=null);return arguments.length>0?this.on(b,null,a,c):this.trigger(b)},f.attrFn&&(f.attrFn[b]=!0),C.test(b)&&(f.event.fixHooks[b]=f.event.keyHooks),D.test(b)&&(f.event.fixHooks[b]=f.event.mouseHooks)}),function(){function x(a,b,c,e,f,g){for(var h=0,i=e.length;h<i;h++){var j=e[h];if(j){var k=!1;j=j[a];while(j){if(j[d]===c){k=e[j.sizset];break}if(j.nodeType===1){g||(j[d]=c,j.sizset=h);if(typeof b!="string"){if(j===b){k=!0;break}}else if(m.filter(b,[j]).length>0){k=j;break}}j=j[a]}e[h]=k}}}function w(a,b,c,e,f,g){for(var h=0,i=e.length;h<i;h++){var j=e[h];if(j){var k=!1;j=j[a];while(j){if(j[d]===c){k=e[j.sizset];break}j.nodeType===1&&!g&&(j[d]=c,j.sizset=h);if(j.nodeName.toLowerCase()===b){k=j;break}j=j[a]}e[h]=k}}}var a=/((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^\[\]]*\]|['"][^'"]*['"]|[^\[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?((?:.|\r|\n)*)/g,d="sizcache"+(Math.random()+"").replace(".",""),e=0,g=Object.prototype.toString,h=!1,i=!0,j=/\\/g,k=/\r\n/g,l=/\W/;[0,0].sort(function(){i=!1;return 0});var m=function(b,d,e,f){e=e||[],d=d||c;var h=d;if(d.nodeType!==1&&d.nodeType!==9)return[];if(!b||typeof b!="string")return e;var i,j,k,l,n,q,r,t,u=!0,v=m.isXML(d),w=[],x=b;do{a.exec(""),i=a.exec(x);if(i){x=i[3],w.push(i[1]);if(i[2]){l=i[3];break}}}while(i);if(w.length>1&&p.exec(b))if(w.length===2&&o.relative[w[0]])j=y(w[0]+w[1],d,f);else{j=o.relative[w[0]]?[d]:m(w.shift(),d);while(w.length)b=w.shift(),o.relative[b]&&(b+=w.shift()),j=y(b,j,f)}else{!f&&w.length>1&&d.nodeType===9&&!v&&o.match.ID.test(w[0])&&!o.match.ID.test(w[w.length-1])&&(n=m.find(w.shift(),d,v),d=n.expr?m.filter(n.expr,n.set)[0]:n.set[0]);if(d){n=f?{expr:w.pop(),set:s(f)}:m.find(w.pop(),w.length===1&&(w[0]==="~"||w[0]==="+")&&d.parentNode?d.parentNode:d,v),j=n.expr?m.filter(n.expr,n.set):n.set,w.length>0?k=s(j):u=!1;while(w.length)q=w.pop(),r=q,o.relative[q]?r=w.pop():q="",r==null&&(r=d),o.relative[q](k,r,v)}else k=w=[]}k||(k=j),k||m.error(q||b);if(g.call(k)==="[object Array]")if(!u)e.push.apply(e,k);else if(d&&d.nodeType===1)for(t=0;k[t]!=null;t++)k[t]&&(k[t]===!0||k[t].nodeType===1&&m.contains(d,k[t]))&&e.push(j[t]);else for(t=0;k[t]!=null;t++)k[t]&&k[t].nodeType===1&&e.push(j[t]);else s(k,e);l&&(m(l,h,e,f),m.uniqueSort(e));return e};m.uniqueSort=function(a){if(u){h=i,a.sort(u);if(h)for(var b=1;b<a.length;b++)a[b]===a[b-1]&&a.splice(b--,1)}return a},m.matches=function(a,b){return m(a,null,null,b)},m.matchesSelector=function(a,b){return m(b,null,null,[a]).length>0},m.find=function(a,b,c){var d,e,f,g,h,i;if(!a)return[];for(e=0,f=o.order.length;e<f;e++){h=o.order[e];if(g=o.leftMatch[h].exec(a)){i=g[1],g.splice(1,1);if(i.substr(i.length-1)!=="\\"){g[1]=(g[1]||"").replace(j,""),d=o.find[h](g,b,c);if(d!=null){a=a.replace(o.match[h],"");break}}}}d||(d=typeof b.getElementsByTagName!="undefined"?b.getElementsByTagName("*"):[]);return{set:d,expr:a}},m.filter=function(a,c,d,e){var f,g,h,i,j,k,l,n,p,q=a,r=[],s=c,t=c&&c[0]&&m.isXML(c[0]);while(a&&c.length){for(h in o.filter)if((f=o.leftMatch[h].exec(a))!=null&&f[2]){k=o.filter[h],l=f[1],g=!1,f.splice(1,1);if(l.substr(l.length-1)==="\\")continue;s===r&&(r=[]);if(o.preFilter[h]){f=o.preFilter[h](f,s,d,r,e,t);if(!f)g=i=!0;else if(f===!0)continue}if(f)for(n=0;(j=s[n])!=null;n++)j&&(i=k(j,f,n,s),p=e^i,d&&i!=null?p?g=!0:s[n]=!1:p&&(r.push(j),g=!0));if(i!==b){d||(s=r),a=a.replace(o.match[h],"");if(!g)return[];break}}if(a===q)if(g==null)m.error(a);else break;q=a}return s},m.error=function(a){throw new Error("Syntax error, unrecognized expression: "+a)};var n=m.getText=function(a){var b,c,d=a.nodeType,e="";if(d){if(d===1||d===9||d===11){if(typeof a.textContent=="string")return a.textContent;if(typeof a.innerText=="string")return a.innerText.replace(k,"");for(a=a.firstChild;a;a=a.nextSibling)e+=n(a)}else if(d===3||d===4)return a.nodeValue}else for(b=0;c=a[b];b++)c.nodeType!==8&&(e+=n(c));return e},o=m.selectors={order:["ID","NAME","TAG"],match:{ID:/#((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,CLASS:/\.((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,NAME:/\[name=['"]*((?:[\w\u00c0-\uFFFF\-]|\\.)+)['"]*\]/,ATTR:/\[\s*((?:[\w\u00c0-\uFFFF\-]|\\.)+)\s*(?:(\S?=)\s*(?:(['"])(.*?)\3|(#?(?:[\w\u00c0-\uFFFF\-]|\\.)*)|)|)\s*\]/,TAG:/^((?:[\w\u00c0-\uFFFF\*\-]|\\.)+)/,CHILD:/:(only|nth|last|first)-child(?:\(\s*(even|odd|(?:[+\-]?\d+|(?:[+\-]?\d*)?n\s*(?:[+\-]\s*\d+)?))\s*\))?/,POS:/:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^\-]|$)/,PSEUDO:/:((?:[\w\u00c0-\uFFFF\-]|\\.)+)(?:\((['"]?)((?:\([^\)]+\)|[^\(\)]*)+)\2\))?/},leftMatch:{},attrMap:{"class":"className","for":"htmlFor"},attrHandle:{href:function(a){return a.getAttribute("href")},type:function(a){return a.getAttribute("type")}},relative:{"+":function(a,b){var c=typeof b=="string",d=c&&!l.test(b),e=c&&!d;d&&(b=b.toLowerCase());for(var f=0,g=a.length,h;f<g;f++)if(h=a[f]){while((h=h.previousSibling)&&h.nodeType!==1);a[f]=e||h&&h.nodeName.toLowerCase()===b?h||!1:h===b}e&&m.filter(b,a,!0)},">":function(a,b){var c,d=typeof b=="string",e=0,f=a.length;if(d&&!l.test(b)){b=b.toLowerCase();for(;e<f;e++){c=a[e];if(c){var g=c.parentNode;a[e]=g.nodeName.toLowerCase()===b?g:!1}}}else{for(;e<f;e++)c=a[e],c&&(a[e]=d?c.parentNode:c.parentNode===b);d&&m.filter(b,a,!0)}},"":function(a,b,c){var d,f=e++,g=x;typeof b=="string"&&!l.test(b)&&(b=b.toLowerCase(),d=b,g=w),g("parentNode",b,f,a,d,c)},"~":function(a,b,c){var d,f=e++,g=x;typeof b=="string"&&!l.test(b)&&(b=b.toLowerCase(),d=b,g=w),g("previousSibling",b,f,a,d,c)}},find:{ID:function(a,b,c){if(typeof b.getElementById!="undefined"&&!c){var d=b.getElementById(a[1]);return d&&d.parentNode?[d]:[]}},NAME:function(a,b){if(typeof b.getElementsByName!="undefined"){var c=[],d=b.getElementsByName(a[1]);for(var e=0,f=d.length;e<f;e++)d[e].getAttribute("name")===a[1]&&c.push(d[e]);return c.length===0?null:c}},TAG:function(a,b){if(typeof b.getElementsByTagName!="undefined")return b.getElementsByTagName(a[1])}},preFilter:{CLASS:function(a,b,c,d,e,f){a=" "+a[1].replace(j,"")+" ";if(f)return a;for(var g=0,h;(h=b[g])!=null;g++)h&&(e^(h.className&&(" "+h.className+" ").replace(/[\t\n\r]/g," ").indexOf(a)>=0)?c||d.push(h):c&&(b[g]=!1));return!1},ID:function(a){return a[1].replace(j,"")},TAG:function(a,b){return a[1].replace(j,"").toLowerCase()},CHILD:function(a){if(a[1]==="nth"){a[2]||m.error(a[0]),a[2]=a[2].replace(/^\+|\s*/g,"");var b=/(-?)(\d*)(?:n([+\-]?\d*))?/.exec(a[2]==="even"&&"2n"||a[2]==="odd"&&"2n+1"||!/\D/.test(a[2])&&"0n+"+a[2]||a[2]);a[2]=b[1]+(b[2]||1)-0,a[3]=b[3]-0}else a[2]&&m.error(a[0]);a[0]=e++;return a},ATTR:function(a,b,c,d,e,f){var g=a[1]=a[1].replace(j,"");!f&&o.attrMap[g]&&(a[1]=o.attrMap[g]),a[4]=(a[4]||a[5]||"").replace(j,""),a[2]==="~="&&(a[4]=" "+a[4]+" ");return a},PSEUDO:function(b,c,d,e,f){if(b[1]==="not")if((a.exec(b[3])||"").length>1||/^\w/.test(b[3]))b[3]=m(b[3],null,null,c);else{var g=m.filter(b[3],c,d,!0^f);d||e.push.apply(e,g);return!1}else if(o.match.POS.test(b[0])||o.match.CHILD.test(b[0]))return!0;return b},POS:function(a){a.unshift(!0);return a}},filters:{enabled:function(a){return a.disabled===!1&&a.type!=="hidden"},disabled:function(a){return a.disabled===!0},checked:function(a){return a.checked===!0},selected:function(a){a.parentNode&&a.parentNode.selectedIndex;return a.selected===!0},parent:function(a){return!!a.firstChild},empty:function(a){return!a.firstChild},has:function(a,b,c){return!!m(c[3],a).length},header:function(a){return/h\d/i.test(a.nodeName)},text:function(a){var b=a.getAttribute("type"),c=a.type;return a.nodeName.toLowerCase()==="input"&&"text"===c&&(b===c||b===null)},radio:function(a){return a.nodeName.toLowerCase()==="input"&&"radio"===a.type},checkbox:function(a){return a.nodeName.toLowerCase()==="input"&&"checkbox"===a.type},file:function(a){return a.nodeName.toLowerCase()==="input"&&"file"===a.type},password:function(a){return a.nodeName.toLowerCase()==="input"&&"password"===a.type},submit:function(a){var b=a.nodeName.toLowerCase();return(b==="input"||b==="button")&&"submit"===a.type},image:function(a){return a.nodeName.toLowerCase()==="input"&&"image"===a.type},reset:function(a){var b=a.nodeName.toLowerCase();return(b==="input"||b==="button")&&"reset"===a.type},button:function(a){var b=a.nodeName.toLowerCase();return b==="input"&&"button"===a.type||b==="button"},input:function(a){return/input|select|textarea|button/i.test(a.nodeName)},focus:function(a){return a===a.ownerDocument.activeElement}},setFilters:{first:function(a,b){return b===0},last:function(a,b,c,d){return b===d.length-1},even:function(a,b){return b%2===0},odd:function(a,b){return b%2===1},lt:function(a,b,c){return b<c[3]-0},gt:function(a,b,c){return b>c[3]-0},nth:function(a,b,c){return c[3]-0===b},eq:function(a,b,c){return c[3]-0===b}},filter:{PSEUDO:function(a,b,c,d){var e=b[1],f=o.filters[e];if(f)return f(a,c,b,d);if(e==="contains")return(a.textContent||a.innerText||n([a])||"").indexOf(b[3])>=0;if(e==="not"){var g=b[3];for(var h=0,i=g.length;h<i;h++)if(g[h]===a)return!1;return!0}m.error(e)},CHILD:function(a,b){var c,e,f,g,h,i,j,k=b[1],l=a;switch(k){case"only":case"first":while(l=l.previousSibling)if(l.nodeType===1)return!1;if(k==="first")return!0;l=a;case"last":while(l=l.nextSibling)if(l.nodeType===1)return!1;return!0;case"nth":c=b[2],e=b[3];if(c===1&&e===0)return!0;f=b[0],g=a.parentNode;if(g&&(g[d]!==f||!a.nodeIndex)){i=0;for(l=g.firstChild;l;l=l.nextSibling)l.nodeType===1&&(l.nodeIndex=++i);g[d]=f}j=a.nodeIndex-e;return c===0?j===0:j%c===0&&j/c>=0}},ID:function(a,b){return a.nodeType===1&&a.getAttribute("id")===b},TAG:function(a,b){return b==="*"&&a.nodeType===1||!!a.nodeName&&a.nodeName.toLowerCase()===b},CLASS:function(a,b){return(" "+(a.className||a.getAttribute("class"))+" ").indexOf(b)>-1},ATTR:function(a,b){var c=b[1],d=m.attr?m.attr(a,c):o.attrHandle[c]?o.attrHandle[c](a):a[c]!=null?a[c]:a.getAttribute(c),e=d+"",f=b[2],g=b[4];return d==null?f==="!=":!f&&m.attr?d!=null:f==="="?e===g:f==="*="?e.indexOf(g)>=0:f==="~="?(" "+e+" ").indexOf(g)>=0:g?f==="!="?e!==g:f==="^="?e.indexOf(g)===0:f==="$="?e.substr(e.length-g.length)===g:f==="|="?e===g||e.substr(0,g.length+1)===g+"-":!1:e&&d!==!1},POS:function(a,b,c,d){var e=b[2],f=o.setFilters[e];if(f)return f(a,c,b,d)}}},p=o.match.POS,q=function(a,b){return"\\"+(b-0+1)};for(var r in o.match)o.match[r]=new RegExp(o.match[r].source+/(?![^\[]*\])(?![^\(]*\))/.source),o.leftMatch[r]=new RegExp(/(^(?:.|\r|\n)*?)/.source+o.match[r].source.replace(/\\(\d+)/g,q));o.match.globalPOS=p;var s=function(a,b){a=Array.prototype.slice.call(a,0);if(b){b.push.apply(b,a);return b}return a};try{Array.prototype.slice.call(c.documentElement.childNodes,0)[0].nodeType}catch(t){s=function(a,b){var c=0,d=b||[];if(g.call(a)==="[object Array]")Array.prototype.push.apply(d,a);else if(typeof a.length=="number")for(var e=a.length;c<e;c++)d.push(a[c]);else for(;a[c];c++)d.push(a[c]);return d}}var u,v;c.documentElement.compareDocumentPosition?u=function(a,b){if(a===b){h=!0;return 0}if(!a.compareDocumentPosition||!b.compareDocumentPosition)return a.compareDocumentPosition?-1:1;return a.compareDocumentPosition(b)&4?-1:1}:(u=function(a,b){if(a===b){h=!0;return 0}if(a.sourceIndex&&b.sourceIndex)return a.sourceIndex-b.sourceIndex;var c,d,e=[],f=[],g=a.parentNode,i=b.parentNode,j=g;if(g===i)return v(a,b);if(!g)return-1;if(!i)return 1;while(j)e.unshift(j),j=j.parentNode;j=i;while(j)f.unshift(j),j=j.parentNode;c=e.length,d=f.length;for(var k=0;k<c&&k<d;k++)if(e[k]!==f[k])return v(e[k],f[k]);return k===c?v(a,f[k],-1):v(e[k],b,1)},v=function(a,b,c){if(a===b)return c;var d=a.nextSibling;while(d){if(d===b)return-1;d=d.nextSibling}return 1}),function(){var a=c.createElement("div"),d="script"+(new Date).getTime(),e=c.documentElement;a.innerHTML="<a name='"+d+"'/>",e.insertBefore(a,e.firstChild),c.getElementById(d)&&(o.find.ID=function(a,c,d){if(typeof c.getElementById!="undefined"&&!d){var e=c.getElementById(a[1]);return e?e.id===a[1]||typeof e.getAttributeNode!="undefined"&&e.getAttributeNode("id").nodeValue===a[1]?[e]:b:[]}},o.filter.ID=function(a,b){var c=typeof a.getAttributeNode!="undefined"&&a.getAttributeNode("id");return a.nodeType===1&&c&&c.nodeValue===b}),e.removeChild(a),e=a=null}(),function(){var a=c.createElement("div");a.appendChild(c.createComment("")),a.getElementsByTagName("*").length>0&&(o.find.TAG=function(a,b){var c=b.getElementsByTagName(a[1]);if(a[1]==="*"){var d=[];for(var e=0;c[e];e++)c[e].nodeType===1&&d.push(c[e]);c=d}return c}),a.innerHTML="<a href='#'></a>",a.firstChild&&typeof a.firstChild.getAttribute!="undefined"&&a.firstChild.getAttribute("href")!=="#"&&(o.attrHandle.href=function(a){return a.getAttribute("href",2)}),a=null}(),c.querySelectorAll&&function(){var a=m,b=c.createElement("div"),d="__sizzle__";b.innerHTML="<p class='TEST'></p>";if(!b.querySelectorAll||b.querySelectorAll(".TEST").length!==0){m=function(b,e,f,g){e=e||c;if(!g&&!m.isXML(e)){var h=/^(\w+$)|^\.([\w\-]+$)|^#([\w\-]+$)/.exec(b);if(h&&(e.nodeType===1||e.nodeType===9)){if(h[1])return s(e.getElementsByTagName(b),f);if(h[2]&&o.find.CLASS&&e.getElementsByClassName)return s(e.getElementsByClassName(h[2]),f)}if(e.nodeType===9){if(b==="body"&&e.body)return s([e.body],f);if(h&&h[3]){var i=e.getElementById(h[3]);if(!i||!i.parentNode)return s([],f);if(i.id===h[3])return s([i],f)}try{return s(e.querySelectorAll(b),f)}catch(j){}}else if(e.nodeType===1&&e.nodeName.toLowerCase()!=="object"){var k=e,l=e.getAttribute("id"),n=l||d,p=e.parentNode,q=/^\s*[+~]/.test(b);l?n=n.replace(/'/g,"\\$&"):e.setAttribute("id",n),q&&p&&(e=e.parentNode);try{if(!q||p)return s(e.querySelectorAll("[id='"+n+"'] "+b),f)}catch(r){}finally{l||k.removeAttribute("id")}}}return a(b,e,f,g)};for(var e in a)m[e]=a[e];b=null}}(),function(){var a=c.documentElement,b=a.matchesSelector||a.mozMatchesSelector||a.webkitMatchesSelector||a.msMatchesSelector;if(b){var d=!b.call(c.createElement("div"),"div"),e=!1;try{b.call(c.documentElement,"[test!='']:sizzle")}catch(f){e=!0}m.matchesSelector=function(a,c){c=c.replace(/\=\s*([^'"\]]*)\s*\]/g,"='$1']");if(!m.isXML(a))try{if(e||!o.match.PSEUDO.test(c)&&!/!=/.test(c)){var f=b.call(a,c);if(f||!d||a.document&&a.document.nodeType!==11)return f}}catch(g){}return m(c,null,null,[a]).length>0}}}(),function(){var a=c.createElement("div");a.innerHTML="<div class='test e'></div><div class='test'></div>";if(!!a.getElementsByClassName&&a.getElementsByClassName("e").length!==0){a.lastChild.className="e";if(a.getElementsByClassName("e").length===1)return;o.order.splice(1,0,"CLASS"),o.find.CLASS=function(a,b,c){if(typeof b.getElementsByClassName!="undefined"&&!c)return b.getElementsByClassName(a[1])},a=null}}(),c.documentElement.contains?m.contains=function(a,b){return a!==b&&(a.contains?a.contains(b):!0)}:c.documentElement.compareDocumentPosition?m.contains=function(a,b){return!!(a.compareDocumentPosition(b)&16)}:m.contains=function(){return!1},m.isXML=function(a){var b=(a?a.ownerDocument||a:0).documentElement;return b?b.nodeName!=="HTML":!1};var y=function(a,b,c){var d,e=[],f="",g=b.nodeType?[b]:b;while(d=o.match.PSEUDO.exec(a))f+=d[0],a=a.replace(o.match.PSEUDO,"");a=o.relative[a]?a+"*":a;for(var h=0,i=g.length;h<i;h++)m(a,g[h],e,c);return m.filter(f,e)};m.attr=f.attr,m.selectors.attrMap={},f.find=m,f.expr=m.selectors,f.expr[":"]=f.expr.filters,f.unique=m.uniqueSort,f.text=m.getText,f.isXMLDoc=m.isXML,f.contains=m.contains}();var L=/Until$/,M=/^(?:parents|prevUntil|prevAll)/,N=/,/,O=/^.[^:#\[\.,]*$/,P=Array.prototype.slice,Q=f.expr.match.globalPOS,R={children:!0,contents:!0,next:!0,prev:!0};f.fn.extend({find:function(a){var b=this,c,d;if(typeof a!="string")return f(a).filter(function(){for(c=0,d=b.length;c<d;c++)if(f.contains(b[c],this))return!0});var e=this.pushStack("","find",a),g,h,i;for(c=0,d=this.length;c<d;c++){g=e.length,f.find(a,this[c],e);if(c>0)for(h=g;h<e.length;h++)for(i=0;i<g;i++)if(e[i]===e[h]){e.splice(h--,1);break}}return e},has:function(a){var b=f(a);return this.filter(function(){for(var a=0,c=b.length;a<c;a++)if(f.contains(this,b[a]))return!0})},not:function(a){return this.pushStack(T(this,a,!1),"not",a)},filter:function(a){return this.pushStack(T(this,a,!0),"filter",a)},is:function(a){return!!a&&(typeof a=="string"?Q.test(a)?f(a,this.context).index(this[0])>=0:f.filter(a,this).length>0:this.filter(a).length>0)},closest:function(a,b){var c=[],d,e,g=this[0];if(f.isArray(a)){var h=1;while(g&&g.ownerDocument&&g!==b){for(d=0;d<a.length;d++)f(g).is(a[d])&&c.push({selector:a[d],elem:g,level:h});g=g.parentNode,h++}return c}var i=Q.test(a)||typeof a!="string"?f(a,b||this.context):0;for(d=0,e=this.length;d<e;d++){g=this[d];while(g){if(i?i.index(g)>-1:f.find.matchesSelector(g,a)){c.push(g);break}g=g.parentNode;if(!g||!g.ownerDocument||g===b||g.nodeType===11)break}}c=c.length>1?f.unique(c):c;return this.pushStack(c,"closest",a)},index:function(a){if(!a)return this[0]&&this[0].parentNode?this.prevAll().length:-1;if(typeof a=="string")return f.inArray(this[0],f(a));return f.inArray(a.jquery?a[0]:a,this)},add:function(a,b){var c=typeof a=="string"?f(a,b):f.makeArray(a&&a.nodeType?[a]:a),d=f.merge(this.get(),c);return this.pushStack(S(c[0])||S(d[0])?d:f.unique(d))},andSelf:function(){return this.add(this.prevObject)}}),f.each({parent:function(a){var b=a.parentNode;return b&&b.nodeType!==11?b:null},parents:function(a){return f.dir(a,"parentNode")},parentsUntil:function(a,b,c){return f.dir(a,"parentNode",c)},next:function(a){return f.nth(a,2,"nextSibling")},prev:function(a){return f.nth(a,2,"previousSibling")},nextAll:function(a){return f.dir(a,"nextSibling")},prevAll:function(a){return f.dir(a,"previousSibling")},nextUntil:function(a,b,c){return f.dir(a,"nextSibling",c)},prevUntil:function(a,b,c){return f.dir(a,"previousSibling",c)},siblings:function(a){return f.sibling((a.parentNode||{}).firstChild,a)},children:function(a){return f.sibling(a.firstChild)},contents:function(a){return f.nodeName(a,"iframe")?a.contentDocument||a.contentWindow.document:f.makeArray(a.childNodes)}},function(a,b){f.fn[a]=function(c,d){var e=f.map(this,b,c);L.test(a)||(d=c),d&&typeof d=="string"&&(e=f.filter(d,e)),e=this.length>1&&!R[a]?f.unique(e):e,(this.length>1||N.test(d))&&M.test(a)&&(e=e.reverse());return this.pushStack(e,a,P.call(arguments).join(","))}}),f.extend({filter:function(a,b,c){c&&(a=":not("+a+")");return b.length===1?f.find.matchesSelector(b[0],a)?[b[0]]:[]:f.find.matches(a,b)},dir:function(a,c,d){var e=[],g=a[c];while(g&&g.nodeType!==9&&(d===b||g.nodeType!==1||!f(g).is(d)))g.nodeType===1&&e.push(g),g=g[c];return e},nth:function(a,b,c,d){b=b||1;var e=0;for(;a;a=a[c])if(a.nodeType===1&&++e===b)break;return a},sibling:function(a,b){var c=[];for(;a;a=a.nextSibling)a.nodeType===1&&a!==b&&c.push(a);return c}});var V="abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",W=/ jQuery\d+="(?:\d+|null)"/g,X=/^\s+/,Y=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/ig,Z=/<([\w:]+)/,$=/<tbody/i,_=/<|&#?\w+;/,ba=/<(?:script|style)/i,bb=/<(?:script|object|embed|option|style)/i,bc=new RegExp("<(?:"+V+")[\\s/>]","i"),bd=/checked\s*(?:[^=]|=\s*.checked.)/i,be=/\/(java|ecma)script/i,bf=/^\s*<!(?:\[CDATA\[|\-\-)/,bg={option:[1,"<select multiple='multiple'>","</select>"],legend:[1,"<fieldset>","</fieldset>"],thead:[1,"<table>","</table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],col:[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"],area:[1,"<map>","</map>"],_default:[0,"",""]},bh=U(c);bg.optgroup=bg.option,bg.tbody=bg.tfoot=bg.colgroup=bg.caption=bg.thead,bg.th=bg.td,f.support.htmlSerialize||(bg._default=[1,"div<div>","</div>"]),f.fn.extend({text:function(a){return f.access(this,function(a){return a===b?f.text(this):this.empty().append((this[0]&&this[0].ownerDocument||c).createTextNode(a))},null,a,arguments.length)},wrapAll:function(a){if(f.isFunction(a))return this.each(function(b){f(this).wrapAll(a.call(this,b))});if(this[0]){var b=f(a,this[0].ownerDocument).eq(0).clone(!0);this[0].parentNode&&b.insertBefore(this[0]),b.map(function(){var a=this;while(a.firstChild&&a.firstChild.nodeType===1)a=a.firstChild;return a}).append(this)}return this},wrapInner:function(a){if(f.isFunction(a))return this.each(function(b){f(this).wrapInner(a.call(this,b))});return this.each(function(){var b=f(this),c=b.contents();c.length?c.wrapAll(a):b.append(a)})},wrap:function(a){var b=f.isFunction(a);return this.each(function(c){f(this).wrapAll(b?a.call(this,c):a)})},unwrap:function(){return this.parent().each(function(){f.nodeName(this,"body")||f(this).replaceWith(this.childNodes)}).end()},append:function(){return this.domManip(arguments,!0,function(a){this.nodeType===1&&this.appendChild(a)})},prepend:function(){return this.domManip(arguments,!0,function(a){this.nodeType===1&&this.insertBefore(a,this.firstChild)})},before:function(){if(this[0]&&this[0].parentNode)return this.domManip(arguments,!1,function(a){this.parentNode.insertBefore(a,this)});if(arguments.length){var a=f
.clean(arguments);a.push.apply(a,this.toArray());return this.pushStack(a,"before",arguments)}},after:function(){if(this[0]&&this[0].parentNode)return this.domManip(arguments,!1,function(a){this.parentNode.insertBefore(a,this.nextSibling)});if(arguments.length){var a=this.pushStack(this,"after",arguments);a.push.apply(a,f.clean(arguments));return a}},remove:function(a,b){for(var c=0,d;(d=this[c])!=null;c++)if(!a||f.filter(a,[d]).length)!b&&d.nodeType===1&&(f.cleanData(d.getElementsByTagName("*")),f.cleanData([d])),d.parentNode&&d.parentNode.removeChild(d);return this},empty:function(){for(var a=0,b;(b=this[a])!=null;a++){b.nodeType===1&&f.cleanData(b.getElementsByTagName("*"));while(b.firstChild)b.removeChild(b.firstChild)}return this},clone:function(a,b){a=a==null?!1:a,b=b==null?a:b;return this.map(function(){return f.clone(this,a,b)})},html:function(a){return f.access(this,function(a){var c=this[0]||{},d=0,e=this.length;if(a===b)return c.nodeType===1?c.innerHTML.replace(W,""):null;if(typeof a=="string"&&!ba.test(a)&&(f.support.leadingWhitespace||!X.test(a))&&!bg[(Z.exec(a)||["",""])[1].toLowerCase()]){a=a.replace(Y,"<$1></$2>");try{for(;d<e;d++)c=this[d]||{},c.nodeType===1&&(f.cleanData(c.getElementsByTagName("*")),c.innerHTML=a);c=0}catch(g){}}c&&this.empty().append(a)},null,a,arguments.length)},replaceWith:function(a){if(this[0]&&this[0].parentNode){if(f.isFunction(a))return this.each(function(b){var c=f(this),d=c.html();c.replaceWith(a.call(this,b,d))});typeof a!="string"&&(a=f(a).detach());return this.each(function(){var b=this.nextSibling,c=this.parentNode;f(this).remove(),b?f(b).before(a):f(c).append(a)})}return this.length?this.pushStack(f(f.isFunction(a)?a():a),"replaceWith",a):this},detach:function(a){return this.remove(a,!0)},domManip:function(a,c,d){var e,g,h,i,j=a[0],k=[];if(!f.support.checkClone&&arguments.length===3&&typeof j=="string"&&bd.test(j))return this.each(function(){f(this).domManip(a,c,d,!0)});if(f.isFunction(j))return this.each(function(e){var g=f(this);a[0]=j.call(this,e,c?g.html():b),g.domManip(a,c,d)});if(this[0]){i=j&&j.parentNode,f.support.parentNode&&i&&i.nodeType===11&&i.childNodes.length===this.length?e={fragment:i}:e=f.buildFragment(a,this,k),h=e.fragment,h.childNodes.length===1?g=h=h.firstChild:g=h.firstChild;if(g){c=c&&f.nodeName(g,"tr");for(var l=0,m=this.length,n=m-1;l<m;l++)d.call(c?bi(this[l],g):this[l],e.cacheable||m>1&&l<n?f.clone(h,!0,!0):h)}k.length&&f.each(k,function(a,b){b.src?f.ajax({type:"GET",global:!1,url:b.src,async:!1,dataType:"script"}):f.globalEval((b.text||b.textContent||b.innerHTML||"").replace(bf,"/*$0*/")),b.parentNode&&b.parentNode.removeChild(b)})}return this}}),f.buildFragment=function(a,b,d){var e,g,h,i,j=a[0];b&&b[0]&&(i=b[0].ownerDocument||b[0]),i.createDocumentFragment||(i=c),a.length===1&&typeof j=="string"&&j.length<512&&i===c&&j.charAt(0)==="<"&&!bb.test(j)&&(f.support.checkClone||!bd.test(j))&&(f.support.html5Clone||!bc.test(j))&&(g=!0,h=f.fragments[j],h&&h!==1&&(e=h)),e||(e=i.createDocumentFragment(),f.clean(a,i,e,d)),g&&(f.fragments[j]=h?e:1);return{fragment:e,cacheable:g}},f.fragments={},f.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(a,b){f.fn[a]=function(c){var d=[],e=f(c),g=this.length===1&&this[0].parentNode;if(g&&g.nodeType===11&&g.childNodes.length===1&&e.length===1){e[b](this[0]);return this}for(var h=0,i=e.length;h<i;h++){var j=(h>0?this.clone(!0):this).get();f(e[h])[b](j),d=d.concat(j)}return this.pushStack(d,a,e.selector)}}),f.extend({clone:function(a,b,c){var d,e,g,h=f.support.html5Clone||f.isXMLDoc(a)||!bc.test("<"+a.nodeName+">")?a.cloneNode(!0):bo(a);if((!f.support.noCloneEvent||!f.support.noCloneChecked)&&(a.nodeType===1||a.nodeType===11)&&!f.isXMLDoc(a)){bk(a,h),d=bl(a),e=bl(h);for(g=0;d[g];++g)e[g]&&bk(d[g],e[g])}if(b){bj(a,h);if(c){d=bl(a),e=bl(h);for(g=0;d[g];++g)bj(d[g],e[g])}}d=e=null;return h},clean:function(a,b,d,e){var g,h,i,j=[];b=b||c,typeof b.createElement=="undefined"&&(b=b.ownerDocument||b[0]&&b[0].ownerDocument||c);for(var k=0,l;(l=a[k])!=null;k++){typeof l=="number"&&(l+="");if(!l)continue;if(typeof l=="string")if(!_.test(l))l=b.createTextNode(l);else{l=l.replace(Y,"<$1></$2>");var m=(Z.exec(l)||["",""])[1].toLowerCase(),n=bg[m]||bg._default,o=n[0],p=b.createElement("div"),q=bh.childNodes,r;b===c?bh.appendChild(p):U(b).appendChild(p),p.innerHTML=n[1]+l+n[2];while(o--)p=p.lastChild;if(!f.support.tbody){var s=$.test(l),t=m==="table"&&!s?p.firstChild&&p.firstChild.childNodes:n[1]==="<table>"&&!s?p.childNodes:[];for(i=t.length-1;i>=0;--i)f.nodeName(t[i],"tbody")&&!t[i].childNodes.length&&t[i].parentNode.removeChild(t[i])}!f.support.leadingWhitespace&&X.test(l)&&p.insertBefore(b.createTextNode(X.exec(l)[0]),p.firstChild),l=p.childNodes,p&&(p.parentNode.removeChild(p),q.length>0&&(r=q[q.length-1],r&&r.parentNode&&r.parentNode.removeChild(r)))}var u;if(!f.support.appendChecked)if(l[0]&&typeof (u=l.length)=="number")for(i=0;i<u;i++)bn(l[i]);else bn(l);l.nodeType?j.push(l):j=f.merge(j,l)}if(d){g=function(a){return!a.type||be.test(a.type)};for(k=0;j[k];k++){h=j[k];if(e&&f.nodeName(h,"script")&&(!h.type||be.test(h.type)))e.push(h.parentNode?h.parentNode.removeChild(h):h);else{if(h.nodeType===1){var v=f.grep(h.getElementsByTagName("script"),g);j.splice.apply(j,[k+1,0].concat(v))}d.appendChild(h)}}}return j},cleanData:function(a){var b,c,d=f.cache,e=f.event.special,g=f.support.deleteExpando;for(var h=0,i;(i=a[h])!=null;h++){if(i.nodeName&&f.noData[i.nodeName.toLowerCase()])continue;c=i[f.expando];if(c){b=d[c];if(b&&b.events){for(var j in b.events)e[j]?f.event.remove(i,j):f.removeEvent(i,j,b.handle);b.handle&&(b.handle.elem=null)}g?delete i[f.expando]:i.removeAttribute&&i.removeAttribute(f.expando),delete d[c]}}}});var bp=/alpha\([^)]*\)/i,bq=/opacity=([^)]*)/,br=/([A-Z]|^ms)/g,bs=/^[\-+]?(?:\d*\.)?\d+$/i,bt=/^-?(?:\d*\.)?\d+(?!px)[^\d\s]+$/i,bu=/^([\-+])=([\-+.\de]+)/,bv=/^margin/,bw={position:"absolute",visibility:"hidden",display:"block"},bx=["Top","Right","Bottom","Left"],by,bz,bA;f.fn.css=function(a,c){return f.access(this,function(a,c,d){return d!==b?f.style(a,c,d):f.css(a,c)},a,c,arguments.length>1)},f.extend({cssHooks:{opacity:{get:function(a,b){if(b){var c=by(a,"opacity");return c===""?"1":c}return a.style.opacity}}},cssNumber:{fillOpacity:!0,fontWeight:!0,lineHeight:!0,opacity:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{"float":f.support.cssFloat?"cssFloat":"styleFloat"},style:function(a,c,d,e){if(!!a&&a.nodeType!==3&&a.nodeType!==8&&!!a.style){var g,h,i=f.camelCase(c),j=a.style,k=f.cssHooks[i];c=f.cssProps[i]||i;if(d===b){if(k&&"get"in k&&(g=k.get(a,!1,e))!==b)return g;return j[c]}h=typeof d,h==="string"&&(g=bu.exec(d))&&(d=+(g[1]+1)*+g[2]+parseFloat(f.css(a,c)),h="number");if(d==null||h==="number"&&isNaN(d))return;h==="number"&&!f.cssNumber[i]&&(d+="px");if(!k||!("set"in k)||(d=k.set(a,d))!==b)try{j[c]=d}catch(l){}}},css:function(a,c,d){var e,g;c=f.camelCase(c),g=f.cssHooks[c],c=f.cssProps[c]||c,c==="cssFloat"&&(c="float");if(g&&"get"in g&&(e=g.get(a,!0,d))!==b)return e;if(by)return by(a,c)},swap:function(a,b,c){var d={},e,f;for(f in b)d[f]=a.style[f],a.style[f]=b[f];e=c.call(a);for(f in b)a.style[f]=d[f];return e}}),f.curCSS=f.css,c.defaultView&&c.defaultView.getComputedStyle&&(bz=function(a,b){var c,d,e,g,h=a.style;b=b.replace(br,"-$1").toLowerCase(),(d=a.ownerDocument.defaultView)&&(e=d.getComputedStyle(a,null))&&(c=e.getPropertyValue(b),c===""&&!f.contains(a.ownerDocument.documentElement,a)&&(c=f.style(a,b))),!f.support.pixelMargin&&e&&bv.test(b)&&bt.test(c)&&(g=h.width,h.width=c,c=e.width,h.width=g);return c}),c.documentElement.currentStyle&&(bA=function(a,b){var c,d,e,f=a.currentStyle&&a.currentStyle[b],g=a.style;f==null&&g&&(e=g[b])&&(f=e),bt.test(f)&&(c=g.left,d=a.runtimeStyle&&a.runtimeStyle.left,d&&(a.runtimeStyle.left=a.currentStyle.left),g.left=b==="fontSize"?"1em":f,f=g.pixelLeft+"px",g.left=c,d&&(a.runtimeStyle.left=d));return f===""?"auto":f}),by=bz||bA,f.each(["height","width"],function(a,b){f.cssHooks[b]={get:function(a,c,d){if(c)return a.offsetWidth!==0?bB(a,b,d):f.swap(a,bw,function(){return bB(a,b,d)})},set:function(a,b){return bs.test(b)?b+"px":b}}}),f.support.opacity||(f.cssHooks.opacity={get:function(a,b){return bq.test((b&&a.currentStyle?a.currentStyle.filter:a.style.filter)||"")?parseFloat(RegExp.$1)/100+"":b?"1":""},set:function(a,b){var c=a.style,d=a.currentStyle,e=f.isNumeric(b)?"alpha(opacity="+b*100+")":"",g=d&&d.filter||c.filter||"";c.zoom=1;if(b>=1&&f.trim(g.replace(bp,""))===""){c.removeAttribute("filter");if(d&&!d.filter)return}c.filter=bp.test(g)?g.replace(bp,e):g+" "+e}}),f(function(){f.support.reliableMarginRight||(f.cssHooks.marginRight={get:function(a,b){return f.swap(a,{display:"inline-block"},function(){return b?by(a,"margin-right"):a.style.marginRight})}})}),f.expr&&f.expr.filters&&(f.expr.filters.hidden=function(a){var b=a.offsetWidth,c=a.offsetHeight;return b===0&&c===0||!f.support.reliableHiddenOffsets&&(a.style&&a.style.display||f.css(a,"display"))==="none"},f.expr.filters.visible=function(a){return!f.expr.filters.hidden(a)}),f.each({margin:"",padding:"",border:"Width"},function(a,b){f.cssHooks[a+b]={expand:function(c){var d,e=typeof c=="string"?c.split(" "):[c],f={};for(d=0;d<4;d++)f[a+bx[d]+b]=e[d]||e[d-2]||e[0];return f}}});var bC=/%20/g,bD=/\[\]$/,bE=/\r?\n/g,bF=/#.*$/,bG=/^(.*?):[ \t]*([^\r\n]*)\r?$/mg,bH=/^(?:color|date|datetime|datetime-local|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i,bI=/^(?:about|app|app\-storage|.+\-extension|file|res|widget):$/,bJ=/^(?:GET|HEAD)$/,bK=/^\/\//,bL=/\?/,bM=/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,bN=/^(?:select|textarea)/i,bO=/\s+/,bP=/([?&])_=[^&]*/,bQ=/^([\w\+\.\-]+:)(?:\/\/([^\/?#:]*)(?::(\d+))?)?/,bR=f.fn.load,bS={},bT={},bU,bV,bW=["*/"]+["*"];try{bU=e.href}catch(bX){bU=c.createElement("a"),bU.href="",bU=bU.href}bV=bQ.exec(bU.toLowerCase())||[],f.fn.extend({load:function(a,c,d){if(typeof a!="string"&&bR)return bR.apply(this,arguments);if(!this.length)return this;var e=a.indexOf(" ");if(e>=0){var g=a.slice(e,a.length);a=a.slice(0,e)}var h="GET";c&&(f.isFunction(c)?(d=c,c=b):typeof c=="object"&&(c=f.param(c,f.ajaxSettings.traditional),h="POST"));var i=this;f.ajax({url:a,type:h,dataType:"html",data:c,complete:function(a,b,c){c=a.responseText,a.isResolved()&&(a.done(function(a){c=a}),i.html(g?f("<div>").append(c.replace(bM,"")).find(g):c)),d&&i.each(d,[c,b,a])}});return this},serialize:function(){return f.param(this.serializeArray())},serializeArray:function(){return this.map(function(){return this.elements?f.makeArray(this.elements):this}).filter(function(){return this.name&&!this.disabled&&(this.checked||bN.test(this.nodeName)||bH.test(this.type))}).map(function(a,b){var c=f(this).val();return c==null?null:f.isArray(c)?f.map(c,function(a,c){return{name:b.name,value:a.replace(bE,"\r\n")}}):{name:b.name,value:c.replace(bE,"\r\n")}}).get()}}),f.each("ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split(" "),function(a,b){f.fn[b]=function(a){return this.on(b,a)}}),f.each(["get","post"],function(a,c){f[c]=function(a,d,e,g){f.isFunction(d)&&(g=g||e,e=d,d=b);return f.ajax({type:c,url:a,data:d,success:e,dataType:g})}}),f.extend({getScript:function(a,c){return f.get(a,b,c,"script")},getJSON:function(a,b,c){return f.get(a,b,c,"json")},ajaxSetup:function(a,b){b?b$(a,f.ajaxSettings):(b=a,a=f.ajaxSettings),b$(a,b);return a},ajaxSettings:{url:bU,isLocal:bI.test(bV[1]),global:!0,type:"GET",contentType:"application/x-www-form-urlencoded; charset=UTF-8",processData:!0,async:!0,accepts:{xml:"application/xml, text/xml",html:"text/html",text:"text/plain",json:"application/json, text/javascript","*":bW},contents:{xml:/xml/,html:/html/,json:/json/},responseFields:{xml:"responseXML",text:"responseText"},converters:{"* text":a.String,"text html":!0,"text json":f.parseJSON,"text xml":f.parseXML},flatOptions:{context:!0,url:!0}},ajaxPrefilter:bY(bS),ajaxTransport:bY(bT),ajax:function(a,c){function w(a,c,l,m){if(s!==2){s=2,q&&clearTimeout(q),p=b,n=m||"",v.readyState=a>0?4:0;var o,r,u,w=c,x=l?ca(d,v,l):b,y,z;if(a>=200&&a<300||a===304){if(d.ifModified){if(y=v.getResponseHeader("Last-Modified"))f.lastModified[k]=y;if(z=v.getResponseHeader("Etag"))f.etag[k]=z}if(a===304)w="notmodified",o=!0;else try{r=cb(d,x),w="success",o=!0}catch(A){w="parsererror",u=A}}else{u=w;if(!w||a)w="error",a<0&&(a=0)}v.status=a,v.statusText=""+(c||w),o?h.resolveWith(e,[r,w,v]):h.rejectWith(e,[v,w,u]),v.statusCode(j),j=b,t&&g.trigger("ajax"+(o?"Success":"Error"),[v,d,o?r:u]),i.fireWith(e,[v,w]),t&&(g.trigger("ajaxComplete",[v,d]),--f.active||f.event.trigger("ajaxStop"))}}typeof a=="object"&&(c=a,a=b),c=c||{};var d=f.ajaxSetup({},c),e=d.context||d,g=e!==d&&(e.nodeType||e instanceof f)?f(e):f.event,h=f.Deferred(),i=f.Callbacks("once memory"),j=d.statusCode||{},k,l={},m={},n,o,p,q,r,s=0,t,u,v={readyState:0,setRequestHeader:function(a,b){if(!s){var c=a.toLowerCase();a=m[c]=m[c]||a,l[a]=b}return this},getAllResponseHeaders:function(){return s===2?n:null},getResponseHeader:function(a){var c;if(s===2){if(!o){o={};while(c=bG.exec(n))o[c[1].toLowerCase()]=c[2]}c=o[a.toLowerCase()]}return c===b?null:c},overrideMimeType:function(a){s||(d.mimeType=a);return this},abort:function(a){a=a||"abort",p&&p.abort(a),w(0,a);return this}};h.promise(v),v.success=v.done,v.error=v.fail,v.complete=i.add,v.statusCode=function(a){if(a){var b;if(s<2)for(b in a)j[b]=[j[b],a[b]];else b=a[v.status],v.then(b,b)}return this},d.url=((a||d.url)+"").replace(bF,"").replace(bK,bV[1]+"//"),d.dataTypes=f.trim(d.dataType||"*").toLowerCase().split(bO),d.crossDomain==null&&(r=bQ.exec(d.url.toLowerCase()),d.crossDomain=!(!r||r[1]==bV[1]&&r[2]==bV[2]&&(r[3]||(r[1]==="http:"?80:443))==(bV[3]||(bV[1]==="http:"?80:443)))),d.data&&d.processData&&typeof d.data!="string"&&(d.data=f.param(d.data,d.traditional)),bZ(bS,d,c,v);if(s===2)return!1;t=d.global,d.type=d.type.toUpperCase(),d.hasContent=!bJ.test(d.type),t&&f.active++===0&&f.event.trigger("ajaxStart");if(!d.hasContent){d.data&&(d.url+=(bL.test(d.url)?"&":"?")+d.data,delete d.data),k=d.url;if(d.cache===!1){var x=f.now(),y=d.url.replace(bP,"$1_="+x);d.url=y+(y===d.url?(bL.test(d.url)?"&":"?")+"_="+x:"")}}(d.data&&d.hasContent&&d.contentType!==!1||c.contentType)&&v.setRequestHeader("Content-Type",d.contentType),d.ifModified&&(k=k||d.url,f.lastModified[k]&&v.setRequestHeader("If-Modified-Since",f.lastModified[k]),f.etag[k]&&v.setRequestHeader("If-None-Match",f.etag[k])),v.setRequestHeader("Accept",d.dataTypes[0]&&d.accepts[d.dataTypes[0]]?d.accepts[d.dataTypes[0]]+(d.dataTypes[0]!=="*"?", "+bW+"; q=0.01":""):d.accepts["*"]);for(u in d.headers)v.setRequestHeader(u,d.headers[u]);if(d.beforeSend&&(d.beforeSend.call(e,v,d)===!1||s===2)){v.abort();return!1}for(u in{success:1,error:1,complete:1})v[u](d[u]);p=bZ(bT,d,c,v);if(!p)w(-1,"No Transport");else{v.readyState=1,t&&g.trigger("ajaxSend",[v,d]),d.async&&d.timeout>0&&(q=setTimeout(function(){v.abort("timeout")},d.timeout));try{s=1,p.send(l,w)}catch(z){if(s<2)w(-1,z);else throw z}}return v},param:function(a,c){var d=[],e=function(a,b){b=f.isFunction(b)?b():b,d[d.length]=encodeURIComponent(a)+"="+encodeURIComponent(b)};c===b&&(c=f.ajaxSettings.traditional);if(f.isArray(a)||a.jquery&&!f.isPlainObject(a))f.each(a,function(){e(this.name,this.value)});else for(var g in a)b_(g,a[g],c,e);return d.join("&").replace(bC,"+")}}),f.extend({active:0,lastModified:{},etag:{}});var cc=f.now(),cd=/(\=)\?(&|$)|\?\?/i;f.ajaxSetup({jsonp:"callback",jsonpCallback:function(){return f.expando+"_"+cc++}}),f.ajaxPrefilter("json jsonp",function(b,c,d){var e=typeof b.data=="string"&&/^application\/x\-www\-form\-urlencoded/.test(b.contentType);if(b.dataTypes[0]==="jsonp"||b.jsonp!==!1&&(cd.test(b.url)||e&&cd.test(b.data))){var g,h=b.jsonpCallback=f.isFunction(b.jsonpCallback)?b.jsonpCallback():b.jsonpCallback,i=a[h],j=b.url,k=b.data,l="$1"+h+"$2";b.jsonp!==!1&&(j=j.replace(cd,l),b.url===j&&(e&&(k=k.replace(cd,l)),b.data===k&&(j+=(/\?/.test(j)?"&":"?")+b.jsonp+"="+h))),b.url=j,b.data=k,a[h]=function(a){g=[a]},d.always(function(){a[h]=i,g&&f.isFunction(i)&&a[h](g[0])}),b.converters["script json"]=function(){g||f.error(h+" was not called");return g[0]},b.dataTypes[0]="json";return"script"}}),f.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/javascript|ecmascript/},converters:{"text script":function(a){f.globalEval(a);return a}}}),f.ajaxPrefilter("script",function(a){a.cache===b&&(a.cache=!1),a.crossDomain&&(a.type="GET",a.global=!1)}),f.ajaxTransport("script",function(a){if(a.crossDomain){var d,e=c.head||c.getElementsByTagName("head")[0]||c.documentElement;return{send:function(f,g){d=c.createElement("script"),d.async="async",a.scriptCharset&&(d.charset=a.scriptCharset),d.src=a.url,d.onload=d.onreadystatechange=function(a,c){if(c||!d.readyState||/loaded|complete/.test(d.readyState))d.onload=d.onreadystatechange=null,e&&d.parentNode&&e.removeChild(d),d=b,c||g(200,"success")},e.insertBefore(d,e.firstChild)},abort:function(){d&&d.onload(0,1)}}}});var ce=a.ActiveXObject?function(){for(var a in cg)cg[a](0,1)}:!1,cf=0,cg;f.ajaxSettings.xhr=a.ActiveXObject?function(){return!this.isLocal&&ch()||ci()}:ch,function(a){f.extend(f.support,{ajax:!!a,cors:!!a&&"withCredentials"in a})}(f.ajaxSettings.xhr()),f.support.ajax&&f.ajaxTransport(function(c){if(!c.crossDomain||f.support.cors){var d;return{send:function(e,g){var h=c.xhr(),i,j;c.username?h.open(c.type,c.url,c.async,c.username,c.password):h.open(c.type,c.url,c.async);if(c.xhrFields)for(j in c.xhrFields)h[j]=c.xhrFields[j];c.mimeType&&h.overrideMimeType&&h.overrideMimeType(c.mimeType),!c.crossDomain&&!e["X-Requested-With"]&&(e["X-Requested-With"]="XMLHttpRequest");try{for(j in e)h.setRequestHeader(j,e[j])}catch(k){}h.send(c.hasContent&&c.data||null),d=function(a,e){var j,k,l,m,n;try{if(d&&(e||h.readyState===4)){d=b,i&&(h.onreadystatechange=f.noop,ce&&delete cg[i]);if(e)h.readyState!==4&&h.abort();else{j=h.status,l=h.getAllResponseHeaders(),m={},n=h.responseXML,n&&n.documentElement&&(m.xml=n);try{m.text=h.responseText}catch(a){}try{k=h.statusText}catch(o){k=""}!j&&c.isLocal&&!c.crossDomain?j=m.text?200:404:j===1223&&(j=204)}}}catch(p){e||g(-1,p)}m&&g(j,k,m,l)},!c.async||h.readyState===4?d():(i=++cf,ce&&(cg||(cg={},f(a).unload(ce)),cg[i]=d),h.onreadystatechange=d)},abort:function(){d&&d(0,1)}}}});var cj={},ck,cl,cm=/^(?:toggle|show|hide)$/,cn=/^([+\-]=)?([\d+.\-]+)([a-z%]*)$/i,co,cp=[["height","marginTop","marginBottom","paddingTop","paddingBottom"],["width","marginLeft","marginRight","paddingLeft","paddingRight"],["opacity"]],cq;f.fn.extend({show:function(a,b,c){var d,e;if(a||a===0)return this.animate(ct("show",3),a,b,c);for(var g=0,h=this.length;g<h;g++)d=this[g],d.style&&(e=d.style.display,!f._data(d,"olddisplay")&&e==="none"&&(e=d.style.display=""),(e===""&&f.css(d,"display")==="none"||!f.contains(d.ownerDocument.documentElement,d))&&f._data(d,"olddisplay",cu(d.nodeName)));for(g=0;g<h;g++){d=this[g];if(d.style){e=d.style.display;if(e===""||e==="none")d.style.display=f._data(d,"olddisplay")||""}}return this},hide:function(a,b,c){if(a||a===0)return this.animate(ct("hide",3),a,b,c);var d,e,g=0,h=this.length;for(;g<h;g++)d=this[g],d.style&&(e=f.css(d,"display"),e!=="none"&&!f._data(d,"olddisplay")&&f._data(d,"olddisplay",e));for(g=0;g<h;g++)this[g].style&&(this[g].style.display="none");return this},_toggle:f.fn.toggle,toggle:function(a,b,c){var d=typeof a=="boolean";f.isFunction(a)&&f.isFunction(b)?this._toggle.apply(this,arguments):a==null||d?this.each(function(){var b=d?a:f(this).is(":hidden");f(this)[b?"show":"hide"]()}):this.animate(ct("toggle",3),a,b,c);return this},fadeTo:function(a,b,c,d){return this.filter(":hidden").css("opacity",0).show().end().animate({opacity:b},a,c,d)},animate:function(a,b,c,d){function g(){e.queue===!1&&f._mark(this);var b=f.extend({},e),c=this.nodeType===1,d=c&&f(this).is(":hidden"),g,h,i,j,k,l,m,n,o,p,q;b.animatedProperties={};for(i in a){g=f.camelCase(i),i!==g&&(a[g]=a[i],delete a[i]);if((k=f.cssHooks[g])&&"expand"in k){l=k.expand(a[g]),delete a[g];for(i in l)i in a||(a[i]=l[i])}}for(g in a){h=a[g],f.isArray(h)?(b.animatedProperties[g]=h[1],h=a[g]=h[0]):b.animatedProperties[g]=b.specialEasing&&b.specialEasing[g]||b.easing||"swing";if(h==="hide"&&d||h==="show"&&!d)return b.complete.call(this);c&&(g==="height"||g==="width")&&(b.overflow=[this.style.overflow,this.style.overflowX,this.style.overflowY],f.css(this,"display")==="inline"&&f.css(this,"float")==="none"&&(!f.support.inlineBlockNeedsLayout||cu(this.nodeName)==="inline"?this.style.display="inline-block":this.style.zoom=1))}b.overflow!=null&&(this.style.overflow="hidden");for(i in a)j=new f.fx(this,b,i),h=a[i],cm.test(h)?(q=f._data(this,"toggle"+i)||(h==="toggle"?d?"show":"hide":0),q?(f._data(this,"toggle"+i,q==="show"?"hide":"show"),j[q]()):j[h]()):(m=cn.exec(h),n=j.cur(),m?(o=parseFloat(m[2]),p=m[3]||(f.cssNumber[i]?"":"px"),p!=="px"&&(f.style(this,i,(o||1)+p),n=(o||1)/j.cur()*n,f.style(this,i,n+p)),m[1]&&(o=(m[1]==="-="?-1:1)*o+n),j.custom(n,o,p)):j.custom(n,h,""));return!0}var e=f.speed(b,c,d);if(f.isEmptyObject(a))return this.each(e.complete,[!1]);a=f.extend({},a);return e.queue===!1?this.each(g):this.queue(e.queue,g)},stop:function(a,c,d){typeof a!="string"&&(d=c,c=a,a=b),c&&a!==!1&&this.queue(a||"fx",[]);return this.each(function(){function h(a,b,c){var e=b[c];f.removeData(a,c,!0),e.stop(d)}var b,c=!1,e=f.timers,g=f._data(this);d||f._unmark(!0,this);if(a==null)for(b in g)g[b]&&g[b].stop&&b.indexOf(".run")===b.length-4&&h(this,g,b);else g[b=a+".run"]&&g[b].stop&&h(this,g,b);for(b=e.length;b--;)e[b].elem===this&&(a==null||e[b].queue===a)&&(d?e[b](!0):e[b].saveState(),c=!0,e.splice(b,1));(!d||!c)&&f.dequeue(this,a)})}}),f.each({slideDown:ct("show",1),slideUp:ct("hide",1),slideToggle:ct("toggle",1),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(a,b){f.fn[a]=function(a,c,d){return this.animate(b,a,c,d)}}),f.extend({speed:function(a,b,c){var d=a&&typeof a=="object"?f.extend({},a):{complete:c||!c&&b||f.isFunction(a)&&a,duration:a,easing:c&&b||b&&!f.isFunction(b)&&b};d.duration=f.fx.off?0:typeof d.duration=="number"?d.duration:d.duration in f.fx.speeds?f.fx.speeds[d.duration]:f.fx.speeds._default;if(d.queue==null||d.queue===!0)d.queue="fx";d.old=d.complete,d.complete=function(a){f.isFunction(d.old)&&d.old.call(this),d.queue?f.dequeue(this,d.queue):a!==!1&&f._unmark(this)};return d},easing:{linear:function(a){return a},swing:function(a){return-Math.cos(a*Math.PI)/2+.5}},timers:[],fx:function(a,b,c){this.options=b,this.elem=a,this.prop=c,b.orig=b.orig||{}}}),f.fx.prototype={update:function(){this.options.step&&this.options.step.call(this.elem,this.now,this),(f.fx.step[this.prop]||f.fx.step._default)(this)},cur:function(){if(this.elem[this.prop]!=null&&(!this.elem.style||this.elem.style[this.prop]==null))return this.elem[this.prop];var a,b=f.css(this.elem,this.prop);return isNaN(a=parseFloat(b))?!b||b==="auto"?0:b:a},custom:function(a,c,d){function h(a){return e.step(a)}var e=this,g=f.fx;this.startTime=cq||cr(),this.end=c,this.now=this.start=a,this.pos=this.state=0,this.unit=d||this.unit||(f.cssNumber[this.prop]?"":"px"),h.queue=this.options.queue,h.elem=this.elem,h.saveState=function(){f._data(e.elem,"fxshow"+e.prop)===b&&(e.options.hide?f._data(e.elem,"fxshow"+e.prop,e.start):e.options.show&&f._data(e.elem,"fxshow"+e.prop,e.end))},h()&&f.timers.push(h)&&!co&&(co=setInterval(g.tick,g.interval))},show:function(){var a=f._data(this.elem,"fxshow"+this.prop);this.options.orig[this.prop]=a||f.style(this.elem,this.prop),this.options.show=!0,a!==b?this.custom(this.cur(),a):this.custom(this.prop==="width"||this.prop==="height"?1:0,this.cur()),f(this.elem).show()},hide:function(){this.options.orig[this.prop]=f._data(this.elem,"fxshow"+this.prop)||f.style(this.elem,this.prop),this.options.hide=!0,this.custom(this.cur(),0)},step:function(a){var b,c,d,e=cq||cr(),g=!0,h=this.elem,i=this.options;if(a||e>=i.duration+this.startTime){this.now=this.end,this.pos=this.state=1,this.update(),i.animatedProperties[this.prop]=!0;for(b in i.animatedProperties)i.animatedProperties[b]!==!0&&(g=!1);if(g){i.overflow!=null&&!f.support.shrinkWrapBlocks&&f.each(["","X","Y"],function(a,b){h.style["overflow"+b]=i.overflow[a]}),i.hide&&f(h).hide();if(i.hide||i.show)for(b in i.animatedProperties)f.style(h,b,i.orig[b]),f.removeData(h,"fxshow"+b,!0),f.removeData(h,"toggle"+b,!0);d=i.complete,d&&(i.complete=!1,d.call(h))}return!1}i.duration==Infinity?this.now=e:(c=e-this.startTime,this.state=c/i.duration,this.pos=f.easing[i.animatedProperties[this.prop]](this.state,c,0,1,i.duration),this.now=this.start+(this.end-this.start)*this.pos),this.update();return!0}},f.extend(f.fx,{tick:function(){var a,b=f.timers,c=0;for(;c<b.length;c++)a=b[c],!a()&&b[c]===a&&b.splice(c--,1);b.length||f.fx.stop()},interval:13,stop:function(){clearInterval(co),co=null},speeds:{slow:600,fast:200,_default:400},step:{opacity:function(a){f.style(a.elem,"opacity",a.now)},_default:function(a){a.elem.style&&a.elem.style[a.prop]!=null?a.elem.style[a.prop]=a.now+a.unit:a.elem[a.prop]=a.now}}}),f.each(cp.concat.apply([],cp),function(a,b){b.indexOf("margin")&&(f.fx.step[b]=function(a){f.style(a.elem,b,Math.max(0,a.now)+a.unit)})}),f.expr&&f.expr.filters&&(f.expr.filters.animated=function(a){return f.grep(f.timers,function(b){return a===b.elem}).length});var cv,cw=/^t(?:able|d|h)$/i,cx=/^(?:body|html)$/i;"getBoundingClientRect"in c.documentElement?cv=function(a,b,c,d){try{d=a.getBoundingClientRect()}catch(e){}if(!d||!f.contains(c,a))return d?{top:d.top,left:d.left}:{top:0,left:0};var g=b.body,h=cy(b),i=c.clientTop||g.clientTop||0,j=c.clientLeft||g.clientLeft||0,k=h.pageYOffset||f.support.boxModel&&c.scrollTop||g.scrollTop,l=h.pageXOffset||f.support.boxModel&&c.scrollLeft||g.scrollLeft,m=d.top+k-i,n=d.left+l-j;return{top:m,left:n}}:cv=function(a,b,c){var d,e=a.offsetParent,g=a,h=b.body,i=b.defaultView,j=i?i.getComputedStyle(a,null):a.currentStyle,k=a.offsetTop,l=a.offsetLeft;while((a=a.parentNode)&&a!==h&&a!==c){if(f.support.fixedPosition&&j.position==="fixed")break;d=i?i.getComputedStyle(a,null):a.currentStyle,k-=a.scrollTop,l-=a.scrollLeft,a===e&&(k+=a.offsetTop,l+=a.offsetLeft,f.support.doesNotAddBorder&&(!f.support.doesAddBorderForTableAndCells||!cw.test(a.nodeName))&&(k+=parseFloat(d.borderTopWidth)||0,l+=parseFloat(d.borderLeftWidth)||0),g=e,e=a.offsetParent),f.support.subtractsBorderForOverflowNotVisible&&d.overflow!=="visible"&&(k+=parseFloat(d.borderTopWidth)||0,l+=parseFloat(d.borderLeftWidth)||0),j=d}if(j.position==="relative"||j.position==="static")k+=h.offsetTop,l+=h.offsetLeft;f.support.fixedPosition&&j.position==="fixed"&&(k+=Math.max(c.scrollTop,h.scrollTop),l+=Math.max(c.scrollLeft,h.scrollLeft));return{top:k,left:l}},f.fn.offset=function(a){if(arguments.length)return a===b?this:this.each(function(b){f.offset.setOffset(this,a,b)});var c=this[0],d=c&&c.ownerDocument;if(!d)return null;if(c===d.body)return f.offset.bodyOffset(c);return cv(c,d,d.documentElement)},f.offset={bodyOffset:function(a){var b=a.offsetTop,c=a.offsetLeft;f.support.doesNotIncludeMarginInBodyOffset&&(b+=parseFloat(f.css(a,"marginTop"))||0,c+=parseFloat(f.css(a,"marginLeft"))||0);return{top:b,left:c}},setOffset:function(a,b,c){var d=f.css(a,"position");d==="static"&&(a.style.position="relative");var e=f(a),g=e.offset(),h=f.css(a,"top"),i=f.css(a,"left"),j=(d==="absolute"||d==="fixed")&&f.inArray("auto",[h,i])>-1,k={},l={},m,n;j?(l=e.position(),m=l.top,n=l.left):(m=parseFloat(h)||0,n=parseFloat(i)||0),f.isFunction(b)&&(b=b.call(a,c,g)),b.top!=null&&(k.top=b.top-g.top+m),b.left!=null&&(k.left=b.left-g.left+n),"using"in b?b.using.call(a,k):e.css(k)}},f.fn.extend({position:function(){if(!this[0])return null;var a=this[0],b=this.offsetParent(),c=this.offset(),d=cx.test(b[0].nodeName)?{top:0,left:0}:b.offset();c.top-=parseFloat(f.css(a,"marginTop"))||0,c.left-=parseFloat(f.css(a,"marginLeft"))||0,d.top+=parseFloat(f.css(b[0],"borderTopWidth"))||0,d.left+=parseFloat(f.css(b[0],"borderLeftWidth"))||0;return{top:c.top-d.top,left:c.left-d.left}},offsetParent:function(){return this.map(function(){var a=this.offsetParent||c.body;while(a&&!cx.test(a.nodeName)&&f.css(a,"position")==="static")a=a.offsetParent;return a})}}),f.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(a,c){var d=/Y/.test(c);f.fn[a]=function(e){return f.access(this,function(a,e,g){var h=cy(a);if(g===b)return h?c in h?h[c]:f.support.boxModel&&h.document.documentElement[e]||h.document.body[e]:a[e];h?h.scrollTo(d?f(h).scrollLeft():g,d?g:f(h).scrollTop()):a[e]=g},a,e,arguments.length,null)}}),f.each({Height:"height",Width:"width"},function(a,c){var d="client"+a,e="scroll"+a,g="offset"+a;f.fn["inner"+a]=function(){var a=this[0];return a?a.style?parseFloat(f.css(a,c,"padding")):this[c]():null},f.fn["outer"+a]=function(a){var b=this[0];return b?b.style?parseFloat(f.css(b,c,a?"margin":"border")):this[c]():null},f.fn[c]=function(a){return f.access(this,function(a,c,h){var i,j,k,l;if(f.isWindow(a)){i=a.document,j=i.documentElement[d];return f.support.boxModel&&j||i.body&&i.body[d]||j}if(a.nodeType===9){i=a.documentElement;if(i[d]>=i[e])return i[d];return Math.max(a.body[e],i[e],a.body[g],i[g])}if(h===b){k=f.css(a,c),l=parseFloat(k);return f.isNumeric(l)?l:k}f(a).css(c,h)},c,a,arguments.length,null)}}),a.jQuery=a.$=f,typeof define=="function"&&define.amd&&define.amd.jQuery&&define("jquery",[],function(){return f})})(window);/*!
 * jQuery UI 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI
 */
(function(c,j){function k(a){return!c(a).parents().andSelf().filter(function(){return c.curCSS(this,"visibility")==="hidden"||c.expr.filters.hidden(this)}).length}c.ui=c.ui||{};if(!c.ui.version){c.extend(c.ui,{version:"1.8.8",keyCode:{ALT:18,BACKSPACE:8,CAPS_LOCK:20,COMMA:188,COMMAND:91,COMMAND_LEFT:91,COMMAND_RIGHT:93,CONTROL:17,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,INSERT:45,LEFT:37,MENU:93,NUMPAD_ADD:107,NUMPAD_DECIMAL:110,NUMPAD_DIVIDE:111,NUMPAD_ENTER:108,NUMPAD_MULTIPLY:106,
NUMPAD_SUBTRACT:109,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190,RIGHT:39,SHIFT:16,SPACE:32,TAB:9,UP:38,WINDOWS:91}});c.fn.extend({_focus:c.fn.focus,focus:function(a,b){return typeof a==="number"?this.each(function(){var d=this;setTimeout(function(){c(d).focus();b&&b.call(d)},a)}):this._focus.apply(this,arguments)},scrollParent:function(){var a;a=c.browser.msie&&/(static|relative)/.test(this.css("position"))||/absolute/.test(this.css("position"))?this.parents().filter(function(){return/(relative|absolute|fixed)/.test(c.curCSS(this,
"position",1))&&/(auto|scroll)/.test(c.curCSS(this,"overflow",1)+c.curCSS(this,"overflow-y",1)+c.curCSS(this,"overflow-x",1))}).eq(0):this.parents().filter(function(){return/(auto|scroll)/.test(c.curCSS(this,"overflow",1)+c.curCSS(this,"overflow-y",1)+c.curCSS(this,"overflow-x",1))}).eq(0);return/fixed/.test(this.css("position"))||!a.length?c(document):a},zIndex:function(a){if(a!==j)return this.css("zIndex",a);if(this.length){a=c(this[0]);for(var b;a.length&&a[0]!==document;){b=a.css("position");
if(b==="absolute"||b==="relative"||b==="fixed"){b=parseInt(a.css("zIndex"),10);if(!isNaN(b)&&b!==0)return b}a=a.parent()}}return 0},disableSelection:function(){return this.bind((c.support.selectstart?"selectstart":"mousedown")+".ui-disableSelection",function(a){a.preventDefault()})},enableSelection:function(){return this.unbind(".ui-disableSelection")}});c.each(["Width","Height"],function(a,b){function d(f,g,l,m){c.each(e,function(){g-=parseFloat(c.curCSS(f,"padding"+this,true))||0;if(l)g-=parseFloat(c.curCSS(f,
"border"+this+"Width",true))||0;if(m)g-=parseFloat(c.curCSS(f,"margin"+this,true))||0});return g}var e=b==="Width"?["Left","Right"]:["Top","Bottom"],h=b.toLowerCase(),i={innerWidth:c.fn.innerWidth,innerHeight:c.fn.innerHeight,outerWidth:c.fn.outerWidth,outerHeight:c.fn.outerHeight};c.fn["inner"+b]=function(f){if(f===j)return i["inner"+b].call(this);return this.each(function(){c(this).css(h,d(this,f)+"px")})};c.fn["outer"+b]=function(f,g){if(typeof f!=="number")return i["outer"+b].call(this,f);return this.each(function(){c(this).css(h,
d(this,f,true,g)+"px")})}});c.extend(c.expr[":"],{data:function(a,b,d){return!!c.data(a,d[3])},focusable:function(a){var b=a.nodeName.toLowerCase(),d=c.attr(a,"tabindex");if("area"===b){b=a.parentNode;d=b.name;if(!a.href||!d||b.nodeName.toLowerCase()!=="map")return false;a=c("img[usemap=#"+d+"]")[0];return!!a&&k(a)}return(/input|select|textarea|button|object/.test(b)?!a.disabled:"a"==b?a.href||!isNaN(d):!isNaN(d))&&k(a)},tabbable:function(a){var b=c.attr(a,"tabindex");return(isNaN(b)||b>=0)&&c(a).is(":focusable")}});
c(function(){var a=document.body,b=a.appendChild(b=document.createElement("div"));c.extend(b.style,{minHeight:"100px",height:"auto",padding:0,borderWidth:0});c.support.minHeight=b.offsetHeight===100;c.support.selectstart="onselectstart"in b;a.removeChild(b).style.display="none"});c.extend(c.ui,{plugin:{add:function(a,b,d){a=c.ui[a].prototype;for(var e in d){a.plugins[e]=a.plugins[e]||[];a.plugins[e].push([b,d[e]])}},call:function(a,b,d){if((b=a.plugins[b])&&a.element[0].parentNode)for(var e=0;e<b.length;e++)a.options[b[e][0]]&&
b[e][1].apply(a.element,d)}},contains:function(a,b){return document.compareDocumentPosition?a.compareDocumentPosition(b)&16:a!==b&&a.contains(b)},hasScroll:function(a,b){if(c(a).css("overflow")==="hidden")return false;b=b&&b==="left"?"scrollLeft":"scrollTop";var d=false;if(a[b]>0)return true;a[b]=1;d=a[b]>0;a[b]=0;return d},isOverAxis:function(a,b,d){return a>b&&a<b+d},isOver:function(a,b,d,e,h,i){return c.ui.isOverAxis(a,d,h)&&c.ui.isOverAxis(b,e,i)}})}})(jQuery);
;/*!
 * jQuery UI Widget 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Widget
 */
(function(b,j){if(b.cleanData){var k=b.cleanData;b.cleanData=function(a){for(var c=0,d;(d=a[c])!=null;c++)b(d).triggerHandler("remove");k(a)}}else{var l=b.fn.remove;b.fn.remove=function(a,c){return this.each(function(){if(!c)if(!a||b.filter(a,[this]).length)b("*",this).add([this]).each(function(){b(this).triggerHandler("remove")});return l.call(b(this),a,c)})}}b.widget=function(a,c,d){var e=a.split(".")[0],f;a=a.split(".")[1];f=e+"-"+a;if(!d){d=c;c=b.Widget}b.expr[":"][f]=function(h){return!!b.data(h,
a)};b[e]=b[e]||{};b[e][a]=function(h,g){arguments.length&&this._createWidget(h,g)};c=new c;c.options=b.extend(true,{},c.options);b[e][a].prototype=b.extend(true,c,{namespace:e,widgetName:a,widgetEventPrefix:b[e][a].prototype.widgetEventPrefix||a,widgetBaseClass:f},d);b.widget.bridge(a,b[e][a])};b.widget.bridge=function(a,c){b.fn[a]=function(d){var e=typeof d==="string",f=Array.prototype.slice.call(arguments,1),h=this;d=!e&&f.length?b.extend.apply(null,[true,d].concat(f)):d;if(e&&d.charAt(0)==="_")return h;
e?this.each(function(){var g=b.data(this,a),i=g&&b.isFunction(g[d])?g[d].apply(g,f):g;if(i!==g&&i!==j){h=i;return false}}):this.each(function(){var g=b.data(this,a);g?g.option(d||{})._init():b.data(this,a,new c(d,this))});return h}};b.Widget=function(a,c){arguments.length&&this._createWidget(a,c)};b.Widget.prototype={widgetName:"widget",widgetEventPrefix:"",options:{disabled:false},_createWidget:function(a,c){b.data(c,this.widgetName,this);this.element=b(c);this.options=b.extend(true,{},this.options,
this._getCreateOptions(),a);var d=this;this.element.bind("remove."+this.widgetName,function(){d.destroy()});this._create();this._trigger("create");this._init()},_getCreateOptions:function(){return b.metadata&&b.metadata.get(this.element[0])[this.widgetName]},_create:function(){},_init:function(){},destroy:function(){this.element.unbind("."+this.widgetName).removeData(this.widgetName);this.widget().unbind("."+this.widgetName).removeAttr("aria-disabled").removeClass(this.widgetBaseClass+"-disabled ui-state-disabled")},
widget:function(){return this.element},option:function(a,c){var d=a;if(arguments.length===0)return b.extend({},this.options);if(typeof a==="string"){if(c===j)return this.options[a];d={};d[a]=c}this._setOptions(d);return this},_setOptions:function(a){var c=this;b.each(a,function(d,e){c._setOption(d,e)});return this},_setOption:function(a,c){this.options[a]=c;if(a==="disabled")this.widget()[c?"addClass":"removeClass"](this.widgetBaseClass+"-disabled ui-state-disabled").attr("aria-disabled",c);return this},
enable:function(){return this._setOption("disabled",false)},disable:function(){return this._setOption("disabled",true)},_trigger:function(a,c,d){var e=this.options[a];c=b.Event(c);c.type=(a===this.widgetEventPrefix?a:this.widgetEventPrefix+a).toLowerCase();d=d||{};if(c.originalEvent){a=b.event.props.length;for(var f;a;){f=b.event.props[--a];c[f]=c.originalEvent[f]}}this.element.trigger(c,d);return!(b.isFunction(e)&&e.call(this.element[0],c,d)===false||c.isDefaultPrevented())}}})(jQuery);
;/*!
 * jQuery UI Mouse 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Mouse
 *
 * Depends:
 *	jquery.ui.widget.js
 */
(function(c){c.widget("ui.mouse",{options:{cancel:":input,option",distance:1,delay:0},_mouseInit:function(){var a=this;this.element.bind("mousedown."+this.widgetName,function(b){return a._mouseDown(b)}).bind("click."+this.widgetName,function(b){if(true===c.data(b.target,a.widgetName+".preventClickEvent")){c.removeData(b.target,a.widgetName+".preventClickEvent");b.stopImmediatePropagation();return false}});this.started=false},_mouseDestroy:function(){this.element.unbind("."+this.widgetName)},_mouseDown:function(a){a.originalEvent=
a.originalEvent||{};if(!a.originalEvent.mouseHandled){this._mouseStarted&&this._mouseUp(a);this._mouseDownEvent=a;var b=this,e=a.which==1,f=typeof this.options.cancel=="string"?c(a.target).parents().add(a.target).filter(this.options.cancel).length:false;if(!e||f||!this._mouseCapture(a))return true;this.mouseDelayMet=!this.options.delay;if(!this.mouseDelayMet)this._mouseDelayTimer=setTimeout(function(){b.mouseDelayMet=true},this.options.delay);if(this._mouseDistanceMet(a)&&this._mouseDelayMet(a)){this._mouseStarted=
this._mouseStart(a)!==false;if(!this._mouseStarted){a.preventDefault();return true}}this._mouseMoveDelegate=function(d){return b._mouseMove(d)};this._mouseUpDelegate=function(d){return b._mouseUp(d)};c(document).bind("mousemove."+this.widgetName,this._mouseMoveDelegate).bind("mouseup."+this.widgetName,this._mouseUpDelegate);a.preventDefault();return a.originalEvent.mouseHandled=true}},_mouseMove:function(a){if(c.browser.msie&&!(document.documentMode>=9)&&!a.button)return this._mouseUp(a);if(this._mouseStarted){this._mouseDrag(a);
return a.preventDefault()}if(this._mouseDistanceMet(a)&&this._mouseDelayMet(a))(this._mouseStarted=this._mouseStart(this._mouseDownEvent,a)!==false)?this._mouseDrag(a):this._mouseUp(a);return!this._mouseStarted},_mouseUp:function(a){c(document).unbind("mousemove."+this.widgetName,this._mouseMoveDelegate).unbind("mouseup."+this.widgetName,this._mouseUpDelegate);if(this._mouseStarted){this._mouseStarted=false;a.target==this._mouseDownEvent.target&&c.data(a.target,this.widgetName+".preventClickEvent",
true);this._mouseStop(a)}return false},_mouseDistanceMet:function(a){return Math.max(Math.abs(this._mouseDownEvent.pageX-a.pageX),Math.abs(this._mouseDownEvent.pageY-a.pageY))>=this.options.distance},_mouseDelayMet:function(){return this.mouseDelayMet},_mouseStart:function(){},_mouseDrag:function(){},_mouseStop:function(){},_mouseCapture:function(){return true}})})(jQuery);
;/*
 * jQuery UI Position 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Position
 */
(function(c){c.ui=c.ui||{};var n=/left|center|right/,o=/top|center|bottom/,t=c.fn.position,u=c.fn.offset;c.fn.position=function(b){if(!b||!b.of)return t.apply(this,arguments);b=c.extend({},b);var a=c(b.of),d=a[0],g=(b.collision||"flip").split(" "),e=b.offset?b.offset.split(" "):[0,0],h,k,j;if(d.nodeType===9){h=a.width();k=a.height();j={top:0,left:0}}else if(d.setTimeout){h=a.width();k=a.height();j={top:a.scrollTop(),left:a.scrollLeft()}}else if(d.preventDefault){b.at="left top";h=k=0;j={top:b.of.pageY,
left:b.of.pageX}}else{h=a.outerWidth();k=a.outerHeight();j=a.offset()}c.each(["my","at"],function(){var f=(b[this]||"").split(" ");if(f.length===1)f=n.test(f[0])?f.concat(["center"]):o.test(f[0])?["center"].concat(f):["center","center"];f[0]=n.test(f[0])?f[0]:"center";f[1]=o.test(f[1])?f[1]:"center";b[this]=f});if(g.length===1)g[1]=g[0];e[0]=parseInt(e[0],10)||0;if(e.length===1)e[1]=e[0];e[1]=parseInt(e[1],10)||0;if(b.at[0]==="right")j.left+=h;else if(b.at[0]==="center")j.left+=h/2;if(b.at[1]==="bottom")j.top+=
k;else if(b.at[1]==="center")j.top+=k/2;j.left+=e[0];j.top+=e[1];return this.each(function(){var f=c(this),l=f.outerWidth(),m=f.outerHeight(),p=parseInt(c.curCSS(this,"marginLeft",true))||0,q=parseInt(c.curCSS(this,"marginTop",true))||0,v=l+p+(parseInt(c.curCSS(this,"marginRight",true))||0),w=m+q+(parseInt(c.curCSS(this,"marginBottom",true))||0),i=c.extend({},j),r;if(b.my[0]==="right")i.left-=l;else if(b.my[0]==="center")i.left-=l/2;if(b.my[1]==="bottom")i.top-=m;else if(b.my[1]==="center")i.top-=
m/2;i.left=Math.round(i.left);i.top=Math.round(i.top);r={left:i.left-p,top:i.top-q};c.each(["left","top"],function(s,x){c.ui.position[g[s]]&&c.ui.position[g[s]][x](i,{targetWidth:h,targetHeight:k,elemWidth:l,elemHeight:m,collisionPosition:r,collisionWidth:v,collisionHeight:w,offset:e,my:b.my,at:b.at})});c.fn.bgiframe&&f.bgiframe();f.offset(c.extend(i,{using:b.using}))})};c.ui.position={fit:{left:function(b,a){var d=c(window);d=a.collisionPosition.left+a.collisionWidth-d.width()-d.scrollLeft();b.left=
d>0?b.left-d:Math.max(b.left-a.collisionPosition.left,b.left)},top:function(b,a){var d=c(window);d=a.collisionPosition.top+a.collisionHeight-d.height()-d.scrollTop();b.top=d>0?b.top-d:Math.max(b.top-a.collisionPosition.top,b.top)}},flip:{left:function(b,a){if(a.at[0]!=="center"){var d=c(window);d=a.collisionPosition.left+a.collisionWidth-d.width()-d.scrollLeft();var g=a.my[0]==="left"?-a.elemWidth:a.my[0]==="right"?a.elemWidth:0,e=a.at[0]==="left"?a.targetWidth:-a.targetWidth,h=-2*a.offset[0];b.left+=
a.collisionPosition.left<0?g+e+h:d>0?g+e+h:0}},top:function(b,a){if(a.at[1]!=="center"){var d=c(window);d=a.collisionPosition.top+a.collisionHeight-d.height()-d.scrollTop();var g=a.my[1]==="top"?-a.elemHeight:a.my[1]==="bottom"?a.elemHeight:0,e=a.at[1]==="top"?a.targetHeight:-a.targetHeight,h=-2*a.offset[1];b.top+=a.collisionPosition.top<0?g+e+h:d>0?g+e+h:0}}}};if(!c.offset.setOffset){c.offset.setOffset=function(b,a){if(/static/.test(c.curCSS(b,"position")))b.style.position="relative";var d=c(b),
g=d.offset(),e=parseInt(c.curCSS(b,"top",true),10)||0,h=parseInt(c.curCSS(b,"left",true),10)||0;g={top:a.top-g.top+e,left:a.left-g.left+h};"using"in a?a.using.call(b,g):d.css(g)};c.fn.offset=function(b){var a=this[0];if(!a||!a.ownerDocument)return null;if(b)return this.each(function(){c.offset.setOffset(this,b)});return u.call(this)}}})(jQuery);
;/*
 * jQuery UI Draggable 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Draggables
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.mouse.js
 *	jquery.ui.widget.js
 */
(function(d){d.widget("ui.draggable",d.ui.mouse,{widgetEventPrefix:"drag",options:{addClasses:true,appendTo:"parent",axis:false,connectToSortable:false,containment:false,cursor:"auto",cursorAt:false,grid:false,handle:false,helper:"original",iframeFix:false,opacity:false,refreshPositions:false,revert:false,revertDuration:500,scope:"default",scroll:true,scrollSensitivity:20,scrollSpeed:20,snap:false,snapMode:"both",snapTolerance:20,stack:false,zIndex:false},_create:function(){if(this.options.helper==
"original"&&!/^(?:r|a|f)/.test(this.element.css("position")))this.element[0].style.position="relative";this.options.addClasses&&this.element.addClass("ui-draggable");this.options.disabled&&this.element.addClass("ui-draggable-disabled");this._mouseInit()},destroy:function(){if(this.element.data("draggable")){this.element.removeData("draggable").unbind(".draggable").removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled");this._mouseDestroy();return this}},_mouseCapture:function(a){var b=
this.options;if(this.helper||b.disabled||d(a.target).is(".ui-resizable-handle"))return false;this.handle=this._getHandle(a);if(!this.handle)return false;return true},_mouseStart:function(a){var b=this.options;this.helper=this._createHelper(a);this._cacheHelperProportions();if(d.ui.ddmanager)d.ui.ddmanager.current=this;this._cacheMargins();this.cssPosition=this.helper.css("position");this.scrollParent=this.helper.scrollParent();this.offset=this.positionAbs=this.element.offset();this.offset={top:this.offset.top-
this.margins.top,left:this.offset.left-this.margins.left};d.extend(this.offset,{click:{left:a.pageX-this.offset.left,top:a.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()});this.originalPosition=this.position=this._generatePosition(a);this.originalPageX=a.pageX;this.originalPageY=a.pageY;b.cursorAt&&this._adjustOffsetFromHelper(b.cursorAt);b.containment&&this._setContainment();if(this._trigger("start",a)===false){this._clear();return false}this._cacheHelperProportions();
d.ui.ddmanager&&!b.dropBehaviour&&d.ui.ddmanager.prepareOffsets(this,a);this.helper.addClass("ui-draggable-dragging");this._mouseDrag(a,true);return true},_mouseDrag:function(a,b){this.position=this._generatePosition(a);this.positionAbs=this._convertPositionTo("absolute");if(!b){b=this._uiHash();if(this._trigger("drag",a,b)===false){this._mouseUp({});return false}this.position=b.position}if(!this.options.axis||this.options.axis!="y")this.helper[0].style.left=this.position.left+"px";if(!this.options.axis||
this.options.axis!="x")this.helper[0].style.top=this.position.top+"px";d.ui.ddmanager&&d.ui.ddmanager.drag(this,a);return false},_mouseStop:function(a){var b=false;if(d.ui.ddmanager&&!this.options.dropBehaviour)b=d.ui.ddmanager.drop(this,a);if(this.dropped){b=this.dropped;this.dropped=false}if(!this.element[0]||!this.element[0].parentNode)return false;if(this.options.revert=="invalid"&&!b||this.options.revert=="valid"&&b||this.options.revert===true||d.isFunction(this.options.revert)&&this.options.revert.call(this.element,
b)){var c=this;d(this.helper).animate(this.originalPosition,parseInt(this.options.revertDuration,10),function(){c._trigger("stop",a)!==false&&c._clear()})}else this._trigger("stop",a)!==false&&this._clear();return false},cancel:function(){this.helper.is(".ui-draggable-dragging")?this._mouseUp({}):this._clear();return this},_getHandle:function(a){var b=!this.options.handle||!d(this.options.handle,this.element).length?true:false;d(this.options.handle,this.element).find("*").andSelf().each(function(){if(this==
a.target)b=true});return b},_createHelper:function(a){var b=this.options;a=d.isFunction(b.helper)?d(b.helper.apply(this.element[0],[a])):b.helper=="clone"?this.element.clone():this.element;a.parents("body").length||a.appendTo(b.appendTo=="parent"?this.element[0].parentNode:b.appendTo);a[0]!=this.element[0]&&!/(fixed|absolute)/.test(a.css("position"))&&a.css("position","absolute");return a},_adjustOffsetFromHelper:function(a){if(typeof a=="string")a=a.split(" ");if(d.isArray(a))a={left:+a[0],top:+a[1]||
0};if("left"in a)this.offset.click.left=a.left+this.margins.left;if("right"in a)this.offset.click.left=this.helperProportions.width-a.right+this.margins.left;if("top"in a)this.offset.click.top=a.top+this.margins.top;if("bottom"in a)this.offset.click.top=this.helperProportions.height-a.bottom+this.margins.top},_getParentOffset:function(){this.offsetParent=this.helper.offsetParent();var a=this.offsetParent.offset();if(this.cssPosition=="absolute"&&this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],
this.offsetParent[0])){a.left+=this.scrollParent.scrollLeft();a.top+=this.scrollParent.scrollTop()}if(this.offsetParent[0]==document.body||this.offsetParent[0].tagName&&this.offsetParent[0].tagName.toLowerCase()=="html"&&d.browser.msie)a={top:0,left:0};return{top:a.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:a.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}},_getRelativeOffset:function(){if(this.cssPosition=="relative"){var a=this.element.position();return{top:a.top-
(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:a.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}else return{top:0,left:0}},_cacheMargins:function(){this.margins={left:parseInt(this.element.css("marginLeft"),10)||0,top:parseInt(this.element.css("marginTop"),10)||0}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},_setContainment:function(){var a=this.options;if(a.containment==
"parent")a.containment=this.helper[0].parentNode;if(a.containment=="document"||a.containment=="window")this.containment=[(a.containment=="document"?0:d(window).scrollLeft())-this.offset.relative.left-this.offset.parent.left,(a.containment=="document"?0:d(window).scrollTop())-this.offset.relative.top-this.offset.parent.top,(a.containment=="document"?0:d(window).scrollLeft())+d(a.containment=="document"?document:window).width()-this.helperProportions.width-this.margins.left,(a.containment=="document"?
0:d(window).scrollTop())+(d(a.containment=="document"?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top];if(!/^(document|window|parent)$/.test(a.containment)&&a.containment.constructor!=Array){var b=d(a.containment)[0];if(b){a=d(a.containment).offset();var c=d(b).css("overflow")!="hidden";this.containment=[a.left+(parseInt(d(b).css("borderLeftWidth"),10)||0)+(parseInt(d(b).css("paddingLeft"),10)||0)-this.margins.left,a.top+(parseInt(d(b).css("borderTopWidth"),
10)||0)+(parseInt(d(b).css("paddingTop"),10)||0)-this.margins.top,a.left+(c?Math.max(b.scrollWidth,b.offsetWidth):b.offsetWidth)-(parseInt(d(b).css("borderLeftWidth"),10)||0)-(parseInt(d(b).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left,a.top+(c?Math.max(b.scrollHeight,b.offsetHeight):b.offsetHeight)-(parseInt(d(b).css("borderTopWidth"),10)||0)-(parseInt(d(b).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top]}}else if(a.containment.constructor==
Array)this.containment=a.containment},_convertPositionTo:function(a,b){if(!b)b=this.position;a=a=="absolute"?1:-1;var c=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,f=/(html|body)/i.test(c[0].tagName);return{top:b.top+this.offset.relative.top*a+this.offset.parent.top*a-(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():
f?0:c.scrollTop())*a),left:b.left+this.offset.relative.left*a+this.offset.parent.left*a-(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():f?0:c.scrollLeft())*a)}},_generatePosition:function(a){var b=this.options,c=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,f=/(html|body)/i.test(c[0].tagName),e=a.pageX,g=a.pageY;
if(this.originalPosition){if(this.containment){if(a.pageX-this.offset.click.left<this.containment[0])e=this.containment[0]+this.offset.click.left;if(a.pageY-this.offset.click.top<this.containment[1])g=this.containment[1]+this.offset.click.top;if(a.pageX-this.offset.click.left>this.containment[2])e=this.containment[2]+this.offset.click.left;if(a.pageY-this.offset.click.top>this.containment[3])g=this.containment[3]+this.offset.click.top}if(b.grid){g=this.originalPageY+Math.round((g-this.originalPageY)/
b.grid[1])*b.grid[1];g=this.containment?!(g-this.offset.click.top<this.containment[1]||g-this.offset.click.top>this.containment[3])?g:!(g-this.offset.click.top<this.containment[1])?g-b.grid[1]:g+b.grid[1]:g;e=this.originalPageX+Math.round((e-this.originalPageX)/b.grid[0])*b.grid[0];e=this.containment?!(e-this.offset.click.left<this.containment[0]||e-this.offset.click.left>this.containment[2])?e:!(e-this.offset.click.left<this.containment[0])?e-b.grid[0]:e+b.grid[0]:e}}return{top:g-this.offset.click.top-
this.offset.relative.top-this.offset.parent.top+(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollTop():f?0:c.scrollTop()),left:e-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():f?0:c.scrollLeft())}},_clear:function(){this.helper.removeClass("ui-draggable-dragging");this.helper[0]!=
this.element[0]&&!this.cancelHelperRemoval&&this.helper.remove();this.helper=null;this.cancelHelperRemoval=false},_trigger:function(a,b,c){c=c||this._uiHash();d.ui.plugin.call(this,a,[b,c]);if(a=="drag")this.positionAbs=this._convertPositionTo("absolute");return d.Widget.prototype._trigger.call(this,a,b,c)},plugins:{},_uiHash:function(){return{helper:this.helper,position:this.position,originalPosition:this.originalPosition,offset:this.positionAbs}}});d.extend(d.ui.draggable,{version:"1.8.8"});
d.ui.plugin.add("draggable","connectToSortable",{start:function(a,b){var c=d(this).data("draggable"),f=c.options,e=d.extend({},b,{item:c.element});c.sortables=[];d(f.connectToSortable).each(function(){var g=d.data(this,"sortable");if(g&&!g.options.disabled){c.sortables.push({instance:g,shouldRevert:g.options.revert});g._refreshItems();g._trigger("activate",a,e)}})},stop:function(a,b){var c=d(this).data("draggable"),f=d.extend({},b,{item:c.element});d.each(c.sortables,function(){if(this.instance.isOver){this.instance.isOver=
0;c.cancelHelperRemoval=true;this.instance.cancelHelperRemoval=false;if(this.shouldRevert)this.instance.options.revert=true;this.instance._mouseStop(a);this.instance.options.helper=this.instance.options._helper;c.options.helper=="original"&&this.instance.currentItem.css({top:"auto",left:"auto"})}else{this.instance.cancelHelperRemoval=false;this.instance._trigger("deactivate",a,f)}})},drag:function(a,b){var c=d(this).data("draggable"),f=this;d.each(c.sortables,function(){this.instance.positionAbs=
c.positionAbs;this.instance.helperProportions=c.helperProportions;this.instance.offset.click=c.offset.click;if(this.instance._intersectsWith(this.instance.containerCache)){if(!this.instance.isOver){this.instance.isOver=1;this.instance.currentItem=d(f).clone().appendTo(this.instance.element).data("sortable-item",true);this.instance.options._helper=this.instance.options.helper;this.instance.options.helper=function(){return b.helper[0]};a.target=this.instance.currentItem[0];this.instance._mouseCapture(a,
true);this.instance._mouseStart(a,true,true);this.instance.offset.click.top=c.offset.click.top;this.instance.offset.click.left=c.offset.click.left;this.instance.offset.parent.left-=c.offset.parent.left-this.instance.offset.parent.left;this.instance.offset.parent.top-=c.offset.parent.top-this.instance.offset.parent.top;c._trigger("toSortable",a);c.dropped=this.instance.element;c.currentItem=c.element;this.instance.fromOutside=c}this.instance.currentItem&&this.instance._mouseDrag(a)}else if(this.instance.isOver){this.instance.isOver=
0;this.instance.cancelHelperRemoval=true;this.instance.options.revert=false;this.instance._trigger("out",a,this.instance._uiHash(this.instance));this.instance._mouseStop(a,true);this.instance.options.helper=this.instance.options._helper;this.instance.currentItem.remove();this.instance.placeholder&&this.instance.placeholder.remove();c._trigger("fromSortable",a);c.dropped=false}})}});d.ui.plugin.add("draggable","cursor",{start:function(){var a=d("body"),b=d(this).data("draggable").options;if(a.css("cursor"))b._cursor=
a.css("cursor");a.css("cursor",b.cursor)},stop:function(){var a=d(this).data("draggable").options;a._cursor&&d("body").css("cursor",a._cursor)}});d.ui.plugin.add("draggable","iframeFix",{start:function(){var a=d(this).data("draggable").options;d(a.iframeFix===true?"iframe":a.iframeFix).each(function(){d('<div class="ui-draggable-iframeFix" style="background: #fff;"></div>').css({width:this.offsetWidth+"px",height:this.offsetHeight+"px",position:"absolute",opacity:"0.001",zIndex:1E3}).css(d(this).offset()).appendTo("body")})},
stop:function(){d("div.ui-draggable-iframeFix").each(function(){this.parentNode.removeChild(this)})}});d.ui.plugin.add("draggable","opacity",{start:function(a,b){a=d(b.helper);b=d(this).data("draggable").options;if(a.css("opacity"))b._opacity=a.css("opacity");a.css("opacity",b.opacity)},stop:function(a,b){a=d(this).data("draggable").options;a._opacity&&d(b.helper).css("opacity",a._opacity)}});d.ui.plugin.add("draggable","scroll",{start:function(){var a=d(this).data("draggable");if(a.scrollParent[0]!=
document&&a.scrollParent[0].tagName!="HTML")a.overflowOffset=a.scrollParent.offset()},drag:function(a){var b=d(this).data("draggable"),c=b.options,f=false;if(b.scrollParent[0]!=document&&b.scrollParent[0].tagName!="HTML"){if(!c.axis||c.axis!="x")if(b.overflowOffset.top+b.scrollParent[0].offsetHeight-a.pageY<c.scrollSensitivity)b.scrollParent[0].scrollTop=f=b.scrollParent[0].scrollTop+c.scrollSpeed;else if(a.pageY-b.overflowOffset.top<c.scrollSensitivity)b.scrollParent[0].scrollTop=f=b.scrollParent[0].scrollTop-
c.scrollSpeed;if(!c.axis||c.axis!="y")if(b.overflowOffset.left+b.scrollParent[0].offsetWidth-a.pageX<c.scrollSensitivity)b.scrollParent[0].scrollLeft=f=b.scrollParent[0].scrollLeft+c.scrollSpeed;else if(a.pageX-b.overflowOffset.left<c.scrollSensitivity)b.scrollParent[0].scrollLeft=f=b.scrollParent[0].scrollLeft-c.scrollSpeed}else{if(!c.axis||c.axis!="x")if(a.pageY-d(document).scrollTop()<c.scrollSensitivity)f=d(document).scrollTop(d(document).scrollTop()-c.scrollSpeed);else if(d(window).height()-
(a.pageY-d(document).scrollTop())<c.scrollSensitivity)f=d(document).scrollTop(d(document).scrollTop()+c.scrollSpeed);if(!c.axis||c.axis!="y")if(a.pageX-d(document).scrollLeft()<c.scrollSensitivity)f=d(document).scrollLeft(d(document).scrollLeft()-c.scrollSpeed);else if(d(window).width()-(a.pageX-d(document).scrollLeft())<c.scrollSensitivity)f=d(document).scrollLeft(d(document).scrollLeft()+c.scrollSpeed)}f!==false&&d.ui.ddmanager&&!c.dropBehaviour&&d.ui.ddmanager.prepareOffsets(b,a)}});d.ui.plugin.add("draggable",
"snap",{start:function(){var a=d(this).data("draggable"),b=a.options;a.snapElements=[];d(b.snap.constructor!=String?b.snap.items||":data(draggable)":b.snap).each(function(){var c=d(this),f=c.offset();this!=a.element[0]&&a.snapElements.push({item:this,width:c.outerWidth(),height:c.outerHeight(),top:f.top,left:f.left})})},drag:function(a,b){for(var c=d(this).data("draggable"),f=c.options,e=f.snapTolerance,g=b.offset.left,n=g+c.helperProportions.width,m=b.offset.top,o=m+c.helperProportions.height,h=
c.snapElements.length-1;h>=0;h--){var i=c.snapElements[h].left,k=i+c.snapElements[h].width,j=c.snapElements[h].top,l=j+c.snapElements[h].height;if(i-e<g&&g<k+e&&j-e<m&&m<l+e||i-e<g&&g<k+e&&j-e<o&&o<l+e||i-e<n&&n<k+e&&j-e<m&&m<l+e||i-e<n&&n<k+e&&j-e<o&&o<l+e){if(f.snapMode!="inner"){var p=Math.abs(j-o)<=e,q=Math.abs(l-m)<=e,r=Math.abs(i-n)<=e,s=Math.abs(k-g)<=e;if(p)b.position.top=c._convertPositionTo("relative",{top:j-c.helperProportions.height,left:0}).top-c.margins.top;if(q)b.position.top=c._convertPositionTo("relative",
{top:l,left:0}).top-c.margins.top;if(r)b.position.left=c._convertPositionTo("relative",{top:0,left:i-c.helperProportions.width}).left-c.margins.left;if(s)b.position.left=c._convertPositionTo("relative",{top:0,left:k}).left-c.margins.left}var t=p||q||r||s;if(f.snapMode!="outer"){p=Math.abs(j-m)<=e;q=Math.abs(l-o)<=e;r=Math.abs(i-g)<=e;s=Math.abs(k-n)<=e;if(p)b.position.top=c._convertPositionTo("relative",{top:j,left:0}).top-c.margins.top;if(q)b.position.top=c._convertPositionTo("relative",{top:l-c.helperProportions.height,
left:0}).top-c.margins.top;if(r)b.position.left=c._convertPositionTo("relative",{top:0,left:i}).left-c.margins.left;if(s)b.position.left=c._convertPositionTo("relative",{top:0,left:k-c.helperProportions.width}).left-c.margins.left}if(!c.snapElements[h].snapping&&(p||q||r||s||t))c.options.snap.snap&&c.options.snap.snap.call(c.element,a,d.extend(c._uiHash(),{snapItem:c.snapElements[h].item}));c.snapElements[h].snapping=p||q||r||s||t}else{c.snapElements[h].snapping&&c.options.snap.release&&c.options.snap.release.call(c.element,
a,d.extend(c._uiHash(),{snapItem:c.snapElements[h].item}));c.snapElements[h].snapping=false}}}});d.ui.plugin.add("draggable","stack",{start:function(){var a=d(this).data("draggable").options;a=d.makeArray(d(a.stack)).sort(function(c,f){return(parseInt(d(c).css("zIndex"),10)||0)-(parseInt(d(f).css("zIndex"),10)||0)});if(a.length){var b=parseInt(a[0].style.zIndex)||0;d(a).each(function(c){this.style.zIndex=b+c});this[0].style.zIndex=b+a.length}}});d.ui.plugin.add("draggable","zIndex",{start:function(a,
b){a=d(b.helper);b=d(this).data("draggable").options;if(a.css("zIndex"))b._zIndex=a.css("zIndex");a.css("zIndex",b.zIndex)},stop:function(a,b){a=d(this).data("draggable").options;a._zIndex&&d(b.helper).css("zIndex",a._zIndex)}})})(jQuery);
;/*
 * jQuery UI Droppable 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Droppables
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 *	jquery.ui.mouse.js
 *	jquery.ui.draggable.js
 */
(function(d){d.widget("ui.droppable",{widgetEventPrefix:"drop",options:{accept:"*",activeClass:false,addClasses:true,greedy:false,hoverClass:false,scope:"default",tolerance:"intersect"},_create:function(){var a=this.options,b=a.accept;this.isover=0;this.isout=1;this.accept=d.isFunction(b)?b:function(c){return c.is(b)};this.proportions={width:this.element[0].offsetWidth,height:this.element[0].offsetHeight};d.ui.ddmanager.droppables[a.scope]=d.ui.ddmanager.droppables[a.scope]||[];d.ui.ddmanager.droppables[a.scope].push(this);
a.addClasses&&this.element.addClass("ui-droppable")},destroy:function(){for(var a=d.ui.ddmanager.droppables[this.options.scope],b=0;b<a.length;b++)a[b]==this&&a.splice(b,1);this.element.removeClass("ui-droppable ui-droppable-disabled").removeData("droppable").unbind(".droppable");return this},_setOption:function(a,b){if(a=="accept")this.accept=d.isFunction(b)?b:function(c){return c.is(b)};d.Widget.prototype._setOption.apply(this,arguments)},_activate:function(a){var b=d.ui.ddmanager.current;this.options.activeClass&&
this.element.addClass(this.options.activeClass);b&&this._trigger("activate",a,this.ui(b))},_deactivate:function(a){var b=d.ui.ddmanager.current;this.options.activeClass&&this.element.removeClass(this.options.activeClass);b&&this._trigger("deactivate",a,this.ui(b))},_over:function(a){var b=d.ui.ddmanager.current;if(!(!b||(b.currentItem||b.element)[0]==this.element[0]))if(this.accept.call(this.element[0],b.currentItem||b.element)){this.options.hoverClass&&this.element.addClass(this.options.hoverClass);
this._trigger("over",a,this.ui(b))}},_out:function(a){var b=d.ui.ddmanager.current;if(!(!b||(b.currentItem||b.element)[0]==this.element[0]))if(this.accept.call(this.element[0],b.currentItem||b.element)){this.options.hoverClass&&this.element.removeClass(this.options.hoverClass);this._trigger("out",a,this.ui(b))}},_drop:function(a,b){var c=b||d.ui.ddmanager.current;if(!c||(c.currentItem||c.element)[0]==this.element[0])return false;var e=false;this.element.find(":data(droppable)").not(".ui-draggable-dragging").each(function(){var g=
d.data(this,"droppable");if(g.options.greedy&&!g.options.disabled&&g.options.scope==c.options.scope&&g.accept.call(g.element[0],c.currentItem||c.element)&&d.ui.intersect(c,d.extend(g,{offset:g.element.offset()}),g.options.tolerance)){e=true;return false}});if(e)return false;if(this.accept.call(this.element[0],c.currentItem||c.element)){this.options.activeClass&&this.element.removeClass(this.options.activeClass);this.options.hoverClass&&this.element.removeClass(this.options.hoverClass);this._trigger("drop",
a,this.ui(c));return this.element}return false},ui:function(a){return{draggable:a.currentItem||a.element,helper:a.helper,position:a.position,offset:a.positionAbs}}});d.extend(d.ui.droppable,{version:"1.8.8"});d.ui.intersect=function(a,b,c){if(!b.offset)return false;var e=(a.positionAbs||a.position.absolute).left,g=e+a.helperProportions.width,f=(a.positionAbs||a.position.absolute).top,h=f+a.helperProportions.height,i=b.offset.left,k=i+b.proportions.width,j=b.offset.top,l=j+b.proportions.height;
switch(c){case "fit":return i<=e&&g<=k&&j<=f&&h<=l;case "intersect":return i<e+a.helperProportions.width/2&&g-a.helperProportions.width/2<k&&j<f+a.helperProportions.height/2&&h-a.helperProportions.height/2<l;case "pointer":return d.ui.isOver((a.positionAbs||a.position.absolute).top+(a.clickOffset||a.offset.click).top,(a.positionAbs||a.position.absolute).left+(a.clickOffset||a.offset.click).left,j,i,b.proportions.height,b.proportions.width);case "touch":return(f>=j&&f<=l||h>=j&&h<=l||f<j&&h>l)&&(e>=
i&&e<=k||g>=i&&g<=k||e<i&&g>k);default:return false}};d.ui.ddmanager={current:null,droppables:{"default":[]},prepareOffsets:function(a,b){var c=d.ui.ddmanager.droppables[a.options.scope]||[],e=b?b.type:null,g=(a.currentItem||a.element).find(":data(droppable)").andSelf(),f=0;a:for(;f<c.length;f++)if(!(c[f].options.disabled||a&&!c[f].accept.call(c[f].element[0],a.currentItem||a.element))){for(var h=0;h<g.length;h++)if(g[h]==c[f].element[0]){c[f].proportions.height=0;continue a}c[f].visible=c[f].element.css("display")!=
"none";if(c[f].visible){c[f].offset=c[f].element.offset();c[f].proportions={width:c[f].element[0].offsetWidth,height:c[f].element[0].offsetHeight};e=="mousedown"&&c[f]._activate.call(c[f],b)}}},drop:function(a,b){var c=false;d.each(d.ui.ddmanager.droppables[a.options.scope]||[],function(){if(this.options){if(!this.options.disabled&&this.visible&&d.ui.intersect(a,this,this.options.tolerance))c=c||this._drop.call(this,b);if(!this.options.disabled&&this.visible&&this.accept.call(this.element[0],a.currentItem||
a.element)){this.isout=1;this.isover=0;this._deactivate.call(this,b)}}});return c},drag:function(a,b){a.options.refreshPositions&&d.ui.ddmanager.prepareOffsets(a,b);d.each(d.ui.ddmanager.droppables[a.options.scope]||[],function(){if(!(this.options.disabled||this.greedyChild||!this.visible)){var c=d.ui.intersect(a,this,this.options.tolerance);if(c=!c&&this.isover==1?"isout":c&&this.isover==0?"isover":null){var e;if(this.options.greedy){var g=this.element.parents(":data(droppable):eq(0)");if(g.length){e=
d.data(g[0],"droppable");e.greedyChild=c=="isover"?1:0}}if(e&&c=="isover"){e.isover=0;e.isout=1;e._out.call(e,b)}this[c]=1;this[c=="isout"?"isover":"isout"]=0;this[c=="isover"?"_over":"_out"].call(this,b);if(e&&c=="isout"){e.isout=0;e.isover=1;e._over.call(e,b)}}}})}}})(jQuery);
;/*
 * jQuery UI Resizable 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Resizables
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.mouse.js
 *	jquery.ui.widget.js
 */
(function(e){e.widget("ui.resizable",e.ui.mouse,{widgetEventPrefix:"resize",options:{alsoResize:false,animate:false,animateDuration:"slow",animateEasing:"swing",aspectRatio:false,autoHide:false,containment:false,ghost:false,grid:false,handles:"e,s,se",helper:false,maxHeight:null,maxWidth:null,minHeight:10,minWidth:10,zIndex:1E3},_create:function(){var b=this,a=this.options;this.element.addClass("ui-resizable");e.extend(this,{_aspectRatio:!!a.aspectRatio,aspectRatio:a.aspectRatio,originalElement:this.element,
_proportionallyResizeElements:[],_helper:a.helper||a.ghost||a.animate?a.helper||"ui-resizable-helper":null});if(this.element[0].nodeName.match(/canvas|textarea|input|select|button|img/i)){/relative/.test(this.element.css("position"))&&e.browser.opera&&this.element.css({position:"relative",top:"auto",left:"auto"});this.element.wrap(e('<div class="ui-wrapper" style="overflow: hidden;"></div>').css({position:this.element.css("position"),width:this.element.outerWidth(),height:this.element.outerHeight(),
top:this.element.css("top"),left:this.element.css("left")}));this.element=this.element.parent().data("resizable",this.element.data("resizable"));this.elementIsWrapper=true;this.element.css({marginLeft:this.originalElement.css("marginLeft"),marginTop:this.originalElement.css("marginTop"),marginRight:this.originalElement.css("marginRight"),marginBottom:this.originalElement.css("marginBottom")});this.originalElement.css({marginLeft:0,marginTop:0,marginRight:0,marginBottom:0});this.originalResizeStyle=
this.originalElement.css("resize");this.originalElement.css("resize","none");this._proportionallyResizeElements.push(this.originalElement.css({position:"static",zoom:1,display:"block"}));this.originalElement.css({margin:this.originalElement.css("margin")});this._proportionallyResize()}this.handles=a.handles||(!e(".ui-resizable-handle",this.element).length?"e,s,se":{n:".ui-resizable-n",e:".ui-resizable-e",s:".ui-resizable-s",w:".ui-resizable-w",se:".ui-resizable-se",sw:".ui-resizable-sw",ne:".ui-resizable-ne",
nw:".ui-resizable-nw"});if(this.handles.constructor==String){if(this.handles=="all")this.handles="n,e,s,w,se,sw,ne,nw";var c=this.handles.split(",");this.handles={};for(var d=0;d<c.length;d++){var f=e.trim(c[d]),g=e('<div class="ui-resizable-handle '+("ui-resizable-"+f)+'"></div>');/sw|se|ne|nw/.test(f)&&g.css({zIndex:++a.zIndex});"se"==f&&g.addClass("ui-icon ui-icon-gripsmall-diagonal-se");this.handles[f]=".ui-resizable-"+f;this.element.append(g)}}this._renderAxis=function(h){h=h||this.element;for(var i in this.handles){if(this.handles[i].constructor==
String)this.handles[i]=e(this.handles[i],this.element).show();if(this.elementIsWrapper&&this.originalElement[0].nodeName.match(/textarea|input|select|button/i)){var j=e(this.handles[i],this.element),k=0;k=/sw|ne|nw|se|n|s/.test(i)?j.outerHeight():j.outerWidth();j=["padding",/ne|nw|n/.test(i)?"Top":/se|sw|s/.test(i)?"Bottom":/^e$/.test(i)?"Right":"Left"].join("");h.css(j,k);this._proportionallyResize()}e(this.handles[i])}};this._renderAxis(this.element);this._handles=e(".ui-resizable-handle",this.element).disableSelection();
this._handles.mouseover(function(){if(!b.resizing){if(this.className)var h=this.className.match(/ui-resizable-(se|sw|ne|nw|n|e|s|w)/i);b.axis=h&&h[1]?h[1]:"se"}});if(a.autoHide){this._handles.hide();e(this.element).addClass("ui-resizable-autohide").hover(function(){e(this).removeClass("ui-resizable-autohide");b._handles.show()},function(){if(!b.resizing){e(this).addClass("ui-resizable-autohide");b._handles.hide()}})}this._mouseInit()},destroy:function(){this._mouseDestroy();var b=function(c){e(c).removeClass("ui-resizable ui-resizable-disabled ui-resizable-resizing").removeData("resizable").unbind(".resizable").find(".ui-resizable-handle").remove()};
if(this.elementIsWrapper){b(this.element);var a=this.element;a.after(this.originalElement.css({position:a.css("position"),width:a.outerWidth(),height:a.outerHeight(),top:a.css("top"),left:a.css("left")})).remove()}this.originalElement.css("resize",this.originalResizeStyle);b(this.originalElement);return this},_mouseCapture:function(b){var a=false;for(var c in this.handles)if(e(this.handles[c])[0]==b.target)a=true;return!this.options.disabled&&a},_mouseStart:function(b){var a=this.options,c=this.element.position(),
d=this.element;this.resizing=true;this.documentScroll={top:e(document).scrollTop(),left:e(document).scrollLeft()};if(d.is(".ui-draggable")||/absolute/.test(d.css("position")))d.css({position:"absolute",top:c.top,left:c.left});e.browser.opera&&/relative/.test(d.css("position"))&&d.css({position:"relative",top:"auto",left:"auto"});this._renderProxy();c=m(this.helper.css("left"));var f=m(this.helper.css("top"));if(a.containment){c+=e(a.containment).scrollLeft()||0;f+=e(a.containment).scrollTop()||0}this.offset=
this.helper.offset();this.position={left:c,top:f};this.size=this._helper?{width:d.outerWidth(),height:d.outerHeight()}:{width:d.width(),height:d.height()};this.originalSize=this._helper?{width:d.outerWidth(),height:d.outerHeight()}:{width:d.width(),height:d.height()};this.originalPosition={left:c,top:f};this.sizeDiff={width:d.outerWidth()-d.width(),height:d.outerHeight()-d.height()};this.originalMousePosition={left:b.pageX,top:b.pageY};this.aspectRatio=typeof a.aspectRatio=="number"?a.aspectRatio:
this.originalSize.width/this.originalSize.height||1;a=e(".ui-resizable-"+this.axis).css("cursor");e("body").css("cursor",a=="auto"?this.axis+"-resize":a);d.addClass("ui-resizable-resizing");this._propagate("start",b);return true},_mouseDrag:function(b){var a=this.helper,c=this.originalMousePosition,d=this._change[this.axis];if(!d)return false;c=d.apply(this,[b,b.pageX-c.left||0,b.pageY-c.top||0]);if(this._aspectRatio||b.shiftKey)c=this._updateRatio(c,b);c=this._respectSize(c,b);this._propagate("resize",
b);a.css({top:this.position.top+"px",left:this.position.left+"px",width:this.size.width+"px",height:this.size.height+"px"});!this._helper&&this._proportionallyResizeElements.length&&this._proportionallyResize();this._updateCache(c);this._trigger("resize",b,this.ui());return false},_mouseStop:function(b){this.resizing=false;var a=this.options,c=this;if(this._helper){var d=this._proportionallyResizeElements,f=d.length&&/textarea/i.test(d[0].nodeName);d=f&&e.ui.hasScroll(d[0],"left")?0:c.sizeDiff.height;
f={width:c.size.width-(f?0:c.sizeDiff.width),height:c.size.height-d};d=parseInt(c.element.css("left"),10)+(c.position.left-c.originalPosition.left)||null;var g=parseInt(c.element.css("top"),10)+(c.position.top-c.originalPosition.top)||null;a.animate||this.element.css(e.extend(f,{top:g,left:d}));c.helper.height(c.size.height);c.helper.width(c.size.width);this._helper&&!a.animate&&this._proportionallyResize()}e("body").css("cursor","auto");this.element.removeClass("ui-resizable-resizing");this._propagate("stop",
b);this._helper&&this.helper.remove();return false},_updateCache:function(b){this.offset=this.helper.offset();if(l(b.left))this.position.left=b.left;if(l(b.top))this.position.top=b.top;if(l(b.height))this.size.height=b.height;if(l(b.width))this.size.width=b.width},_updateRatio:function(b){var a=this.position,c=this.size,d=this.axis;if(b.height)b.width=c.height*this.aspectRatio;else if(b.width)b.height=c.width/this.aspectRatio;if(d=="sw"){b.left=a.left+(c.width-b.width);b.top=null}if(d=="nw"){b.top=
a.top+(c.height-b.height);b.left=a.left+(c.width-b.width)}return b},_respectSize:function(b){var a=this.options,c=this.axis,d=l(b.width)&&a.maxWidth&&a.maxWidth<b.width,f=l(b.height)&&a.maxHeight&&a.maxHeight<b.height,g=l(b.width)&&a.minWidth&&a.minWidth>b.width,h=l(b.height)&&a.minHeight&&a.minHeight>b.height;if(g)b.width=a.minWidth;if(h)b.height=a.minHeight;if(d)b.width=a.maxWidth;if(f)b.height=a.maxHeight;var i=this.originalPosition.left+this.originalSize.width,j=this.position.top+this.size.height,
k=/sw|nw|w/.test(c);c=/nw|ne|n/.test(c);if(g&&k)b.left=i-a.minWidth;if(d&&k)b.left=i-a.maxWidth;if(h&&c)b.top=j-a.minHeight;if(f&&c)b.top=j-a.maxHeight;if((a=!b.width&&!b.height)&&!b.left&&b.top)b.top=null;else if(a&&!b.top&&b.left)b.left=null;return b},_proportionallyResize:function(){if(this._proportionallyResizeElements.length)for(var b=this.helper||this.element,a=0;a<this._proportionallyResizeElements.length;a++){var c=this._proportionallyResizeElements[a];if(!this.borderDif){var d=[c.css("borderTopWidth"),
c.css("borderRightWidth"),c.css("borderBottomWidth"),c.css("borderLeftWidth")],f=[c.css("paddingTop"),c.css("paddingRight"),c.css("paddingBottom"),c.css("paddingLeft")];this.borderDif=e.map(d,function(g,h){g=parseInt(g,10)||0;h=parseInt(f[h],10)||0;return g+h})}e.browser.msie&&(e(b).is(":hidden")||e(b).parents(":hidden").length)||c.css({height:b.height()-this.borderDif[0]-this.borderDif[2]||0,width:b.width()-this.borderDif[1]-this.borderDif[3]||0})}},_renderProxy:function(){var b=this.options;this.elementOffset=
this.element.offset();if(this._helper){this.helper=this.helper||e('<div style="overflow:hidden;"></div>');var a=e.browser.msie&&e.browser.version<7,c=a?1:0;a=a?2:-1;this.helper.addClass(this._helper).css({width:this.element.outerWidth()+a,height:this.element.outerHeight()+a,position:"absolute",left:this.elementOffset.left-c+"px",top:this.elementOffset.top-c+"px",zIndex:++b.zIndex});this.helper.appendTo("body").disableSelection()}else this.helper=this.element},_change:{e:function(b,a){return{width:this.originalSize.width+
a}},w:function(b,a){return{left:this.originalPosition.left+a,width:this.originalSize.width-a}},n:function(b,a,c){return{top:this.originalPosition.top+c,height:this.originalSize.height-c}},s:function(b,a,c){return{height:this.originalSize.height+c}},se:function(b,a,c){return e.extend(this._change.s.apply(this,arguments),this._change.e.apply(this,[b,a,c]))},sw:function(b,a,c){return e.extend(this._change.s.apply(this,arguments),this._change.w.apply(this,[b,a,c]))},ne:function(b,a,c){return e.extend(this._change.n.apply(this,
arguments),this._change.e.apply(this,[b,a,c]))},nw:function(b,a,c){return e.extend(this._change.n.apply(this,arguments),this._change.w.apply(this,[b,a,c]))}},_propagate:function(b,a){e.ui.plugin.call(this,b,[a,this.ui()]);b!="resize"&&this._trigger(b,a,this.ui())},plugins:{},ui:function(){return{originalElement:this.originalElement,element:this.element,helper:this.helper,position:this.position,size:this.size,originalSize:this.originalSize,originalPosition:this.originalPosition}}});e.extend(e.ui.resizable,
{version:"1.8.8"});e.ui.plugin.add("resizable","alsoResize",{start:function(){var b=e(this).data("resizable").options,a=function(c){e(c).each(function(){var d=e(this);d.data("resizable-alsoresize",{width:parseInt(d.width(),10),height:parseInt(d.height(),10),left:parseInt(d.css("left"),10),top:parseInt(d.css("top"),10),position:d.css("position")})})};if(typeof b.alsoResize=="object"&&!b.alsoResize.parentNode)if(b.alsoResize.length){b.alsoResize=b.alsoResize[0];a(b.alsoResize)}else e.each(b.alsoResize,
function(c){a(c)});else a(b.alsoResize)},resize:function(b,a){var c=e(this).data("resizable");b=c.options;var d=c.originalSize,f=c.originalPosition,g={height:c.size.height-d.height||0,width:c.size.width-d.width||0,top:c.position.top-f.top||0,left:c.position.left-f.left||0},h=function(i,j){e(i).each(function(){var k=e(this),q=e(this).data("resizable-alsoresize"),p={},r=j&&j.length?j:k.parents(a.originalElement[0]).length?["width","height"]:["width","height","top","left"];e.each(r,function(n,o){if((n=
(q[o]||0)+(g[o]||0))&&n>=0)p[o]=n||null});if(e.browser.opera&&/relative/.test(k.css("position"))){c._revertToRelativePosition=true;k.css({position:"absolute",top:"auto",left:"auto"})}k.css(p)})};typeof b.alsoResize=="object"&&!b.alsoResize.nodeType?e.each(b.alsoResize,function(i,j){h(i,j)}):h(b.alsoResize)},stop:function(){var b=e(this).data("resizable"),a=b.options,c=function(d){e(d).each(function(){var f=e(this);f.css({position:f.data("resizable-alsoresize").position})})};if(b._revertToRelativePosition){b._revertToRelativePosition=
false;typeof a.alsoResize=="object"&&!a.alsoResize.nodeType?e.each(a.alsoResize,function(d){c(d)}):c(a.alsoResize)}e(this).removeData("resizable-alsoresize")}});e.ui.plugin.add("resizable","animate",{stop:function(b){var a=e(this).data("resizable"),c=a.options,d=a._proportionallyResizeElements,f=d.length&&/textarea/i.test(d[0].nodeName),g=f&&e.ui.hasScroll(d[0],"left")?0:a.sizeDiff.height;f={width:a.size.width-(f?0:a.sizeDiff.width),height:a.size.height-g};g=parseInt(a.element.css("left"),10)+(a.position.left-
a.originalPosition.left)||null;var h=parseInt(a.element.css("top"),10)+(a.position.top-a.originalPosition.top)||null;a.element.animate(e.extend(f,h&&g?{top:h,left:g}:{}),{duration:c.animateDuration,easing:c.animateEasing,step:function(){var i={width:parseInt(a.element.css("width"),10),height:parseInt(a.element.css("height"),10),top:parseInt(a.element.css("top"),10),left:parseInt(a.element.css("left"),10)};d&&d.length&&e(d[0]).css({width:i.width,height:i.height});a._updateCache(i);a._propagate("resize",
b)}})}});e.ui.plugin.add("resizable","containment",{start:function(){var b=e(this).data("resizable"),a=b.element,c=b.options.containment;if(a=c instanceof e?c.get(0):/parent/.test(c)?a.parent().get(0):c){b.containerElement=e(a);if(/document/.test(c)||c==document){b.containerOffset={left:0,top:0};b.containerPosition={left:0,top:0};b.parentData={element:e(document),left:0,top:0,width:e(document).width(),height:e(document).height()||document.body.parentNode.scrollHeight}}else{var d=e(a),f=[];e(["Top",
"Right","Left","Bottom"]).each(function(i,j){f[i]=m(d.css("padding"+j))});b.containerOffset=d.offset();b.containerPosition=d.position();b.containerSize={height:d.innerHeight()-f[3],width:d.innerWidth()-f[1]};c=b.containerOffset;var g=b.containerSize.height,h=b.containerSize.width;h=e.ui.hasScroll(a,"left")?a.scrollWidth:h;g=e.ui.hasScroll(a)?a.scrollHeight:g;b.parentData={element:a,left:c.left,top:c.top,width:h,height:g}}}},resize:function(b){var a=e(this).data("resizable"),c=a.options,d=a.containerOffset,
f=a.position;b=a._aspectRatio||b.shiftKey;var g={top:0,left:0},h=a.containerElement;if(h[0]!=document&&/static/.test(h.css("position")))g=d;if(f.left<(a._helper?d.left:0)){a.size.width+=a._helper?a.position.left-d.left:a.position.left-g.left;if(b)a.size.height=a.size.width/c.aspectRatio;a.position.left=c.helper?d.left:0}if(f.top<(a._helper?d.top:0)){a.size.height+=a._helper?a.position.top-d.top:a.position.top;if(b)a.size.width=a.size.height*c.aspectRatio;a.position.top=a._helper?d.top:0}a.offset.left=
a.parentData.left+a.position.left;a.offset.top=a.parentData.top+a.position.top;c=Math.abs((a._helper?a.offset.left-g.left:a.offset.left-g.left)+a.sizeDiff.width);d=Math.abs((a._helper?a.offset.top-g.top:a.offset.top-d.top)+a.sizeDiff.height);f=a.containerElement.get(0)==a.element.parent().get(0);g=/relative|absolute/.test(a.containerElement.css("position"));if(f&&g)c-=a.parentData.left;if(c+a.size.width>=a.parentData.width){a.size.width=a.parentData.width-c;if(b)a.size.height=a.size.width/a.aspectRatio}if(d+
a.size.height>=a.parentData.height){a.size.height=a.parentData.height-d;if(b)a.size.width=a.size.height*a.aspectRatio}},stop:function(){var b=e(this).data("resizable"),a=b.options,c=b.containerOffset,d=b.containerPosition,f=b.containerElement,g=e(b.helper),h=g.offset(),i=g.outerWidth()-b.sizeDiff.width;g=g.outerHeight()-b.sizeDiff.height;b._helper&&!a.animate&&/relative/.test(f.css("position"))&&e(this).css({left:h.left-d.left-c.left,width:i,height:g});b._helper&&!a.animate&&/static/.test(f.css("position"))&&
e(this).css({left:h.left-d.left-c.left,width:i,height:g})}});e.ui.plugin.add("resizable","ghost",{start:function(){var b=e(this).data("resizable"),a=b.options,c=b.size;b.ghost=b.originalElement.clone();b.ghost.css({opacity:0.25,display:"block",position:"relative",height:c.height,width:c.width,margin:0,left:0,top:0}).addClass("ui-resizable-ghost").addClass(typeof a.ghost=="string"?a.ghost:"");b.ghost.appendTo(b.helper)},resize:function(){var b=e(this).data("resizable");b.ghost&&b.ghost.css({position:"relative",
height:b.size.height,width:b.size.width})},stop:function(){var b=e(this).data("resizable");b.ghost&&b.helper&&b.helper.get(0).removeChild(b.ghost.get(0))}});e.ui.plugin.add("resizable","grid",{resize:function(){var b=e(this).data("resizable"),a=b.options,c=b.size,d=b.originalSize,f=b.originalPosition,g=b.axis;a.grid=typeof a.grid=="number"?[a.grid,a.grid]:a.grid;var h=Math.round((c.width-d.width)/(a.grid[0]||1))*(a.grid[0]||1);a=Math.round((c.height-d.height)/(a.grid[1]||1))*(a.grid[1]||1);if(/^(se|s|e)$/.test(g)){b.size.width=
d.width+h;b.size.height=d.height+a}else if(/^(ne)$/.test(g)){b.size.width=d.width+h;b.size.height=d.height+a;b.position.top=f.top-a}else{if(/^(sw)$/.test(g)){b.size.width=d.width+h;b.size.height=d.height+a}else{b.size.width=d.width+h;b.size.height=d.height+a;b.position.top=f.top-a}b.position.left=f.left-h}}});var m=function(b){return parseInt(b,10)||0},l=function(b){return!isNaN(parseInt(b,10))}})(jQuery);
;/*
 * jQuery UI Selectable 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Selectables
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.mouse.js
 *	jquery.ui.widget.js
 */
(function(e){e.widget("ui.selectable",e.ui.mouse,{options:{appendTo:"body",autoRefresh:true,distance:0,filter:"*",tolerance:"touch"},_create:function(){var c=this;this.element.addClass("ui-selectable");this.dragged=false;var f;this.refresh=function(){f=e(c.options.filter,c.element[0]);f.each(function(){var d=e(this),b=d.offset();e.data(this,"selectable-item",{element:this,$element:d,left:b.left,top:b.top,right:b.left+d.outerWidth(),bottom:b.top+d.outerHeight(),startselected:false,selected:d.hasClass("ui-selected"),
selecting:d.hasClass("ui-selecting"),unselecting:d.hasClass("ui-unselecting")})})};this.refresh();this.selectees=f.addClass("ui-selectee");this._mouseInit();this.helper=e("<div class='ui-selectable-helper'></div>")},destroy:function(){this.selectees.removeClass("ui-selectee").removeData("selectable-item");this.element.removeClass("ui-selectable ui-selectable-disabled").removeData("selectable").unbind(".selectable");this._mouseDestroy();return this},_mouseStart:function(c){var f=this;this.opos=[c.pageX,
c.pageY];if(!this.options.disabled){var d=this.options;this.selectees=e(d.filter,this.element[0]);this._trigger("start",c);e(d.appendTo).append(this.helper);this.helper.css({left:c.clientX,top:c.clientY,width:0,height:0});d.autoRefresh&&this.refresh();this.selectees.filter(".ui-selected").each(function(){var b=e.data(this,"selectable-item");b.startselected=true;if(!c.metaKey){b.$element.removeClass("ui-selected");b.selected=false;b.$element.addClass("ui-unselecting");b.unselecting=true;f._trigger("unselecting",
c,{unselecting:b.element})}});e(c.target).parents().andSelf().each(function(){var b=e.data(this,"selectable-item");if(b){var g=!c.metaKey||!b.$element.hasClass("ui-selected");b.$element.removeClass(g?"ui-unselecting":"ui-selected").addClass(g?"ui-selecting":"ui-unselecting");b.unselecting=!g;b.selecting=g;(b.selected=g)?f._trigger("selecting",c,{selecting:b.element}):f._trigger("unselecting",c,{unselecting:b.element});return false}})}},_mouseDrag:function(c){var f=this;this.dragged=true;if(!this.options.disabled){var d=
this.options,b=this.opos[0],g=this.opos[1],h=c.pageX,i=c.pageY;if(b>h){var j=h;h=b;b=j}if(g>i){j=i;i=g;g=j}this.helper.css({left:b,top:g,width:h-b,height:i-g});this.selectees.each(function(){var a=e.data(this,"selectable-item");if(!(!a||a.element==f.element[0])){var k=false;if(d.tolerance=="touch")k=!(a.left>h||a.right<b||a.top>i||a.bottom<g);else if(d.tolerance=="fit")k=a.left>b&&a.right<h&&a.top>g&&a.bottom<i;if(k){if(a.selected){a.$element.removeClass("ui-selected");a.selected=false}if(a.unselecting){a.$element.removeClass("ui-unselecting");
a.unselecting=false}if(!a.selecting){a.$element.addClass("ui-selecting");a.selecting=true;f._trigger("selecting",c,{selecting:a.element})}}else{if(a.selecting)if(c.metaKey&&a.startselected){a.$element.removeClass("ui-selecting");a.selecting=false;a.$element.addClass("ui-selected");a.selected=true}else{a.$element.removeClass("ui-selecting");a.selecting=false;if(a.startselected){a.$element.addClass("ui-unselecting");a.unselecting=true}f._trigger("unselecting",c,{unselecting:a.element})}if(a.selected)if(!c.metaKey&&
!a.startselected){a.$element.removeClass("ui-selected");a.selected=false;a.$element.addClass("ui-unselecting");a.unselecting=true;f._trigger("unselecting",c,{unselecting:a.element})}}}});return false}},_mouseStop:function(c){var f=this;this.dragged=false;e(".ui-unselecting",this.element[0]).each(function(){var d=e.data(this,"selectable-item");d.$element.removeClass("ui-unselecting");d.unselecting=false;d.startselected=false;f._trigger("unselected",c,{unselected:d.element})});e(".ui-selecting",this.element[0]).each(function(){var d=
e.data(this,"selectable-item");d.$element.removeClass("ui-selecting").addClass("ui-selected");d.selecting=false;d.selected=true;d.startselected=true;f._trigger("selected",c,{selected:d.element})});this._trigger("stop",c);this.helper.remove();return false}});e.extend(e.ui.selectable,{version:"1.8.8"})})(jQuery);
;/*
 * jQuery UI Sortable 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Sortables
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.mouse.js
 *	jquery.ui.widget.js
 */
(function(d){d.widget("ui.sortable",d.ui.mouse,{widgetEventPrefix:"sort",options:{appendTo:"parent",axis:false,connectWith:false,containment:false,cursor:"auto",cursorAt:false,dropOnEmpty:true,forcePlaceholderSize:false,forceHelperSize:false,grid:false,handle:false,helper:"original",items:"> *",opacity:false,placeholder:false,revert:false,scroll:true,scrollSensitivity:20,scrollSpeed:20,scope:"default",tolerance:"intersect",zIndex:1E3},_create:function(){this.containerCache={};this.element.addClass("ui-sortable");
this.refresh();this.floating=this.items.length?/left|right/.test(this.items[0].item.css("float")):false;this.offset=this.element.offset();this._mouseInit()},destroy:function(){this.element.removeClass("ui-sortable ui-sortable-disabled").removeData("sortable").unbind(".sortable");this._mouseDestroy();for(var a=this.items.length-1;a>=0;a--)this.items[a].item.removeData("sortable-item");return this},_setOption:function(a,b){if(a==="disabled"){this.options[a]=b;this.widget()[b?"addClass":"removeClass"]("ui-sortable-disabled")}else d.Widget.prototype._setOption.apply(this,
arguments)},_mouseCapture:function(a,b){if(this.reverting)return false;if(this.options.disabled||this.options.type=="static")return false;this._refreshItems(a);var c=null,e=this;d(a.target).parents().each(function(){if(d.data(this,"sortable-item")==e){c=d(this);return false}});if(d.data(a.target,"sortable-item")==e)c=d(a.target);if(!c)return false;if(this.options.handle&&!b){var f=false;d(this.options.handle,c).find("*").andSelf().each(function(){if(this==a.target)f=true});if(!f)return false}this.currentItem=
c;this._removeCurrentsFromItems();return true},_mouseStart:function(a,b,c){b=this.options;var e=this;this.currentContainer=this;this.refreshPositions();this.helper=this._createHelper(a);this._cacheHelperProportions();this._cacheMargins();this.scrollParent=this.helper.scrollParent();this.offset=this.currentItem.offset();this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left};this.helper.css("position","absolute");this.cssPosition=this.helper.css("position");d.extend(this.offset,
{click:{left:a.pageX-this.offset.left,top:a.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()});this.originalPosition=this._generatePosition(a);this.originalPageX=a.pageX;this.originalPageY=a.pageY;b.cursorAt&&this._adjustOffsetFromHelper(b.cursorAt);this.domPosition={prev:this.currentItem.prev()[0],parent:this.currentItem.parent()[0]};this.helper[0]!=this.currentItem[0]&&this.currentItem.hide();this._createPlaceholder();b.containment&&this._setContainment();
if(b.cursor){if(d("body").css("cursor"))this._storedCursor=d("body").css("cursor");d("body").css("cursor",b.cursor)}if(b.opacity){if(this.helper.css("opacity"))this._storedOpacity=this.helper.css("opacity");this.helper.css("opacity",b.opacity)}if(b.zIndex){if(this.helper.css("zIndex"))this._storedZIndex=this.helper.css("zIndex");this.helper.css("zIndex",b.zIndex)}if(this.scrollParent[0]!=document&&this.scrollParent[0].tagName!="HTML")this.overflowOffset=this.scrollParent.offset();this._trigger("start",
a,this._uiHash());this._preserveHelperProportions||this._cacheHelperProportions();if(!c)for(c=this.containers.length-1;c>=0;c--)this.containers[c]._trigger("activate",a,e._uiHash(this));if(d.ui.ddmanager)d.ui.ddmanager.current=this;d.ui.ddmanager&&!b.dropBehaviour&&d.ui.ddmanager.prepareOffsets(this,a);this.dragging=true;this.helper.addClass("ui-sortable-helper");this._mouseDrag(a);return true},_mouseDrag:function(a){this.position=this._generatePosition(a);this.positionAbs=this._convertPositionTo("absolute");
if(!this.lastPositionAbs)this.lastPositionAbs=this.positionAbs;if(this.options.scroll){var b=this.options,c=false;if(this.scrollParent[0]!=document&&this.scrollParent[0].tagName!="HTML"){if(this.overflowOffset.top+this.scrollParent[0].offsetHeight-a.pageY<b.scrollSensitivity)this.scrollParent[0].scrollTop=c=this.scrollParent[0].scrollTop+b.scrollSpeed;else if(a.pageY-this.overflowOffset.top<b.scrollSensitivity)this.scrollParent[0].scrollTop=c=this.scrollParent[0].scrollTop-b.scrollSpeed;if(this.overflowOffset.left+
this.scrollParent[0].offsetWidth-a.pageX<b.scrollSensitivity)this.scrollParent[0].scrollLeft=c=this.scrollParent[0].scrollLeft+b.scrollSpeed;else if(a.pageX-this.overflowOffset.left<b.scrollSensitivity)this.scrollParent[0].scrollLeft=c=this.scrollParent[0].scrollLeft-b.scrollSpeed}else{if(a.pageY-d(document).scrollTop()<b.scrollSensitivity)c=d(document).scrollTop(d(document).scrollTop()-b.scrollSpeed);else if(d(window).height()-(a.pageY-d(document).scrollTop())<b.scrollSensitivity)c=d(document).scrollTop(d(document).scrollTop()+
b.scrollSpeed);if(a.pageX-d(document).scrollLeft()<b.scrollSensitivity)c=d(document).scrollLeft(d(document).scrollLeft()-b.scrollSpeed);else if(d(window).width()-(a.pageX-d(document).scrollLeft())<b.scrollSensitivity)c=d(document).scrollLeft(d(document).scrollLeft()+b.scrollSpeed)}c!==false&&d.ui.ddmanager&&!b.dropBehaviour&&d.ui.ddmanager.prepareOffsets(this,a)}this.positionAbs=this._convertPositionTo("absolute");if(!this.options.axis||this.options.axis!="y")this.helper[0].style.left=this.position.left+
"px";if(!this.options.axis||this.options.axis!="x")this.helper[0].style.top=this.position.top+"px";for(b=this.items.length-1;b>=0;b--){c=this.items[b];var e=c.item[0],f=this._intersectsWithPointer(c);if(f)if(e!=this.currentItem[0]&&this.placeholder[f==1?"next":"prev"]()[0]!=e&&!d.ui.contains(this.placeholder[0],e)&&(this.options.type=="semi-dynamic"?!d.ui.contains(this.element[0],e):true)){this.direction=f==1?"down":"up";if(this.options.tolerance=="pointer"||this._intersectsWithSides(c))this._rearrange(a,
c);else break;this._trigger("change",a,this._uiHash());break}}this._contactContainers(a);d.ui.ddmanager&&d.ui.ddmanager.drag(this,a);this._trigger("sort",a,this._uiHash());this.lastPositionAbs=this.positionAbs;return false},_mouseStop:function(a,b){if(a){d.ui.ddmanager&&!this.options.dropBehaviour&&d.ui.ddmanager.drop(this,a);if(this.options.revert){var c=this;b=c.placeholder.offset();c.reverting=true;d(this.helper).animate({left:b.left-this.offset.parent.left-c.margins.left+(this.offsetParent[0]==
document.body?0:this.offsetParent[0].scrollLeft),top:b.top-this.offset.parent.top-c.margins.top+(this.offsetParent[0]==document.body?0:this.offsetParent[0].scrollTop)},parseInt(this.options.revert,10)||500,function(){c._clear(a)})}else this._clear(a,b);return false}},cancel:function(){var a=this;if(this.dragging){this._mouseUp();this.options.helper=="original"?this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper"):this.currentItem.show();for(var b=this.containers.length-1;b>=0;b--){this.containers[b]._trigger("deactivate",
null,a._uiHash(this));if(this.containers[b].containerCache.over){this.containers[b]._trigger("out",null,a._uiHash(this));this.containers[b].containerCache.over=0}}}this.placeholder[0].parentNode&&this.placeholder[0].parentNode.removeChild(this.placeholder[0]);this.options.helper!="original"&&this.helper&&this.helper[0].parentNode&&this.helper.remove();d.extend(this,{helper:null,dragging:false,reverting:false,_noFinalSort:null});this.domPosition.prev?d(this.domPosition.prev).after(this.currentItem):
d(this.domPosition.parent).prepend(this.currentItem);return this},serialize:function(a){var b=this._getItemsAsjQuery(a&&a.connected),c=[];a=a||{};d(b).each(function(){var e=(d(a.item||this).attr(a.attribute||"id")||"").match(a.expression||/(.+)[-=_](.+)/);if(e)c.push((a.key||e[1]+"[]")+"="+(a.key&&a.expression?e[1]:e[2]))});!c.length&&a.key&&c.push(a.key+"=");return c.join("&")},toArray:function(a){var b=this._getItemsAsjQuery(a&&a.connected),c=[];a=a||{};b.each(function(){c.push(d(a.item||this).attr(a.attribute||
"id")||"")});return c},_intersectsWith:function(a){var b=this.positionAbs.left,c=b+this.helperProportions.width,e=this.positionAbs.top,f=e+this.helperProportions.height,g=a.left,h=g+a.width,i=a.top,k=i+a.height,j=this.offset.click.top,l=this.offset.click.left;j=e+j>i&&e+j<k&&b+l>g&&b+l<h;return this.options.tolerance=="pointer"||this.options.forcePointerForContainers||this.options.tolerance!="pointer"&&this.helperProportions[this.floating?"width":"height"]>a[this.floating?"width":"height"]?j:g<b+
this.helperProportions.width/2&&c-this.helperProportions.width/2<h&&i<e+this.helperProportions.height/2&&f-this.helperProportions.height/2<k},_intersectsWithPointer:function(a){var b=d.ui.isOverAxis(this.positionAbs.top+this.offset.click.top,a.top,a.height);a=d.ui.isOverAxis(this.positionAbs.left+this.offset.click.left,a.left,a.width);b=b&&a;a=this._getDragVerticalDirection();var c=this._getDragHorizontalDirection();if(!b)return false;return this.floating?c&&c=="right"||a=="down"?2:1:a&&(a=="down"?
2:1)},_intersectsWithSides:function(a){var b=d.ui.isOverAxis(this.positionAbs.top+this.offset.click.top,a.top+a.height/2,a.height);a=d.ui.isOverAxis(this.positionAbs.left+this.offset.click.left,a.left+a.width/2,a.width);var c=this._getDragVerticalDirection(),e=this._getDragHorizontalDirection();return this.floating&&e?e=="right"&&a||e=="left"&&!a:c&&(c=="down"&&b||c=="up"&&!b)},_getDragVerticalDirection:function(){var a=this.positionAbs.top-this.lastPositionAbs.top;return a!=0&&(a>0?"down":"up")},
_getDragHorizontalDirection:function(){var a=this.positionAbs.left-this.lastPositionAbs.left;return a!=0&&(a>0?"right":"left")},refresh:function(a){this._refreshItems(a);this.refreshPositions();return this},_connectWith:function(){var a=this.options;return a.connectWith.constructor==String?[a.connectWith]:a.connectWith},_getItemsAsjQuery:function(a){var b=[],c=[],e=this._connectWith();if(e&&a)for(a=e.length-1;a>=0;a--)for(var f=d(e[a]),g=f.length-1;g>=0;g--){var h=d.data(f[g],"sortable");if(h&&h!=
this&&!h.options.disabled)c.push([d.isFunction(h.options.items)?h.options.items.call(h.element):d(h.options.items,h.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"),h])}c.push([d.isFunction(this.options.items)?this.options.items.call(this.element,null,{options:this.options,item:this.currentItem}):d(this.options.items,this.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"),this]);for(a=c.length-1;a>=0;a--)c[a][0].each(function(){b.push(this)});return d(b)},_removeCurrentsFromItems:function(){for(var a=
this.currentItem.find(":data(sortable-item)"),b=0;b<this.items.length;b++)for(var c=0;c<a.length;c++)a[c]==this.items[b].item[0]&&this.items.splice(b,1)},_refreshItems:function(a){this.items=[];this.containers=[this];var b=this.items,c=[[d.isFunction(this.options.items)?this.options.items.call(this.element[0],a,{item:this.currentItem}):d(this.options.items,this.element),this]],e=this._connectWith();if(e)for(var f=e.length-1;f>=0;f--)for(var g=d(e[f]),h=g.length-1;h>=0;h--){var i=d.data(g[h],"sortable");
if(i&&i!=this&&!i.options.disabled){c.push([d.isFunction(i.options.items)?i.options.items.call(i.element[0],a,{item:this.currentItem}):d(i.options.items,i.element),i]);this.containers.push(i)}}for(f=c.length-1;f>=0;f--){a=c[f][1];e=c[f][0];h=0;for(g=e.length;h<g;h++){i=d(e[h]);i.data("sortable-item",a);b.push({item:i,instance:a,width:0,height:0,left:0,top:0})}}},refreshPositions:function(a){if(this.offsetParent&&this.helper)this.offset.parent=this._getParentOffset();for(var b=this.items.length-1;b>=
0;b--){var c=this.items[b],e=this.options.toleranceElement?d(this.options.toleranceElement,c.item):c.item;if(!a){c.width=e.outerWidth();c.height=e.outerHeight()}e=e.offset();c.left=e.left;c.top=e.top}if(this.options.custom&&this.options.custom.refreshContainers)this.options.custom.refreshContainers.call(this);else for(b=this.containers.length-1;b>=0;b--){e=this.containers[b].element.offset();this.containers[b].containerCache.left=e.left;this.containers[b].containerCache.top=e.top;this.containers[b].containerCache.width=
this.containers[b].element.outerWidth();this.containers[b].containerCache.height=this.containers[b].element.outerHeight()}return this},_createPlaceholder:function(a){var b=a||this,c=b.options;if(!c.placeholder||c.placeholder.constructor==String){var e=c.placeholder;c.placeholder={element:function(){var f=d(document.createElement(b.currentItem[0].nodeName)).addClass(e||b.currentItem[0].className+" ui-sortable-placeholder").removeClass("ui-sortable-helper")[0];if(!e)f.style.visibility="hidden";return f},
update:function(f,g){if(!(e&&!c.forcePlaceholderSize)){g.height()||g.height(b.currentItem.innerHeight()-parseInt(b.currentItem.css("paddingTop")||0,10)-parseInt(b.currentItem.css("paddingBottom")||0,10));g.width()||g.width(b.currentItem.innerWidth()-parseInt(b.currentItem.css("paddingLeft")||0,10)-parseInt(b.currentItem.css("paddingRight")||0,10))}}}}b.placeholder=d(c.placeholder.element.call(b.element,b.currentItem));b.currentItem.after(b.placeholder);c.placeholder.update(b,b.placeholder)},_contactContainers:function(a){for(var b=
null,c=null,e=this.containers.length-1;e>=0;e--)if(!d.ui.contains(this.currentItem[0],this.containers[e].element[0]))if(this._intersectsWith(this.containers[e].containerCache)){if(!(b&&d.ui.contains(this.containers[e].element[0],b.element[0]))){b=this.containers[e];c=e}}else if(this.containers[e].containerCache.over){this.containers[e]._trigger("out",a,this._uiHash(this));this.containers[e].containerCache.over=0}if(b)if(this.containers.length===1){this.containers[c]._trigger("over",a,this._uiHash(this));
this.containers[c].containerCache.over=1}else if(this.currentContainer!=this.containers[c]){b=1E4;e=null;for(var f=this.positionAbs[this.containers[c].floating?"left":"top"],g=this.items.length-1;g>=0;g--)if(d.ui.contains(this.containers[c].element[0],this.items[g].item[0])){var h=this.items[g][this.containers[c].floating?"left":"top"];if(Math.abs(h-f)<b){b=Math.abs(h-f);e=this.items[g]}}if(e||this.options.dropOnEmpty){this.currentContainer=this.containers[c];e?this._rearrange(a,e,null,true):this._rearrange(a,
null,this.containers[c].element,true);this._trigger("change",a,this._uiHash());this.containers[c]._trigger("change",a,this._uiHash(this));this.options.placeholder.update(this.currentContainer,this.placeholder);this.containers[c]._trigger("over",a,this._uiHash(this));this.containers[c].containerCache.over=1}}},_createHelper:function(a){var b=this.options;a=d.isFunction(b.helper)?d(b.helper.apply(this.element[0],[a,this.currentItem])):b.helper=="clone"?this.currentItem.clone():this.currentItem;a.parents("body").length||
d(b.appendTo!="parent"?b.appendTo:this.currentItem[0].parentNode)[0].appendChild(a[0]);if(a[0]==this.currentItem[0])this._storedCSS={width:this.currentItem[0].style.width,height:this.currentItem[0].style.height,position:this.currentItem.css("position"),top:this.currentItem.css("top"),left:this.currentItem.css("left")};if(a[0].style.width==""||b.forceHelperSize)a.width(this.currentItem.width());if(a[0].style.height==""||b.forceHelperSize)a.height(this.currentItem.height());return a},_adjustOffsetFromHelper:function(a){if(typeof a==
"string")a=a.split(" ");if(d.isArray(a))a={left:+a[0],top:+a[1]||0};if("left"in a)this.offset.click.left=a.left+this.margins.left;if("right"in a)this.offset.click.left=this.helperProportions.width-a.right+this.margins.left;if("top"in a)this.offset.click.top=a.top+this.margins.top;if("bottom"in a)this.offset.click.top=this.helperProportions.height-a.bottom+this.margins.top},_getParentOffset:function(){this.offsetParent=this.helper.offsetParent();var a=this.offsetParent.offset();if(this.cssPosition==
"absolute"&&this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0])){a.left+=this.scrollParent.scrollLeft();a.top+=this.scrollParent.scrollTop()}if(this.offsetParent[0]==document.body||this.offsetParent[0].tagName&&this.offsetParent[0].tagName.toLowerCase()=="html"&&d.browser.msie)a={top:0,left:0};return{top:a.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:a.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}},_getRelativeOffset:function(){if(this.cssPosition==
"relative"){var a=this.currentItem.position();return{top:a.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:a.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}else return{top:0,left:0}},_cacheMargins:function(){this.margins={left:parseInt(this.currentItem.css("marginLeft"),10)||0,top:parseInt(this.currentItem.css("marginTop"),10)||0}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},
_setContainment:function(){var a=this.options;if(a.containment=="parent")a.containment=this.helper[0].parentNode;if(a.containment=="document"||a.containment=="window")this.containment=[0-this.offset.relative.left-this.offset.parent.left,0-this.offset.relative.top-this.offset.parent.top,d(a.containment=="document"?document:window).width()-this.helperProportions.width-this.margins.left,(d(a.containment=="document"?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-
this.margins.top];if(!/^(document|window|parent)$/.test(a.containment)){var b=d(a.containment)[0];a=d(a.containment).offset();var c=d(b).css("overflow")!="hidden";this.containment=[a.left+(parseInt(d(b).css("borderLeftWidth"),10)||0)+(parseInt(d(b).css("paddingLeft"),10)||0)-this.margins.left,a.top+(parseInt(d(b).css("borderTopWidth"),10)||0)+(parseInt(d(b).css("paddingTop"),10)||0)-this.margins.top,a.left+(c?Math.max(b.scrollWidth,b.offsetWidth):b.offsetWidth)-(parseInt(d(b).css("borderLeftWidth"),
10)||0)-(parseInt(d(b).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left,a.top+(c?Math.max(b.scrollHeight,b.offsetHeight):b.offsetHeight)-(parseInt(d(b).css("borderTopWidth"),10)||0)-(parseInt(d(b).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top]}},_convertPositionTo:function(a,b){if(!b)b=this.position;a=a=="absolute"?1:-1;var c=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0]))?
this.offsetParent:this.scrollParent,e=/(html|body)/i.test(c[0].tagName);return{top:b.top+this.offset.relative.top*a+this.offset.parent.top*a-(d.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():e?0:c.scrollTop())*a),left:b.left+this.offset.relative.left*a+this.offset.parent.left*a-(d.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():e?0:c.scrollLeft())*a)}},_generatePosition:function(a){var b=
this.options,c=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,e=/(html|body)/i.test(c[0].tagName);if(this.cssPosition=="relative"&&!(this.scrollParent[0]!=document&&this.scrollParent[0]!=this.offsetParent[0]))this.offset.relative=this._getRelativeOffset();var f=a.pageX,g=a.pageY;if(this.originalPosition){if(this.containment){if(a.pageX-this.offset.click.left<this.containment[0])f=this.containment[0]+
this.offset.click.left;if(a.pageY-this.offset.click.top<this.containment[1])g=this.containment[1]+this.offset.click.top;if(a.pageX-this.offset.click.left>this.containment[2])f=this.containment[2]+this.offset.click.left;if(a.pageY-this.offset.click.top>this.containment[3])g=this.containment[3]+this.offset.click.top}if(b.grid){g=this.originalPageY+Math.round((g-this.originalPageY)/b.grid[1])*b.grid[1];g=this.containment?!(g-this.offset.click.top<this.containment[1]||g-this.offset.click.top>this.containment[3])?
g:!(g-this.offset.click.top<this.containment[1])?g-b.grid[1]:g+b.grid[1]:g;f=this.originalPageX+Math.round((f-this.originalPageX)/b.grid[0])*b.grid[0];f=this.containment?!(f-this.offset.click.left<this.containment[0]||f-this.offset.click.left>this.containment[2])?f:!(f-this.offset.click.left<this.containment[0])?f-b.grid[0]:f+b.grid[0]:f}}return{top:g-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+(d.browser.safari&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollTop():
e?0:c.scrollTop()),left:f-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(d.browser.safari&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():e?0:c.scrollLeft())}},_rearrange:function(a,b,c,e){c?c[0].appendChild(this.placeholder[0]):b.item[0].parentNode.insertBefore(this.placeholder[0],this.direction=="down"?b.item[0]:b.item[0].nextSibling);this.counter=this.counter?++this.counter:1;var f=this,g=this.counter;window.setTimeout(function(){g==
f.counter&&f.refreshPositions(!e)},0)},_clear:function(a,b){this.reverting=false;var c=[];!this._noFinalSort&&this.currentItem[0].parentNode&&this.placeholder.before(this.currentItem);this._noFinalSort=null;if(this.helper[0]==this.currentItem[0]){for(var e in this._storedCSS)if(this._storedCSS[e]=="auto"||this._storedCSS[e]=="static")this._storedCSS[e]="";this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper")}else this.currentItem.show();this.fromOutside&&!b&&c.push(function(f){this._trigger("receive",
f,this._uiHash(this.fromOutside))});if((this.fromOutside||this.domPosition.prev!=this.currentItem.prev().not(".ui-sortable-helper")[0]||this.domPosition.parent!=this.currentItem.parent()[0])&&!b)c.push(function(f){this._trigger("update",f,this._uiHash())});if(!d.ui.contains(this.element[0],this.currentItem[0])){b||c.push(function(f){this._trigger("remove",f,this._uiHash())});for(e=this.containers.length-1;e>=0;e--)if(d.ui.contains(this.containers[e].element[0],this.currentItem[0])&&!b){c.push(function(f){return function(g){f._trigger("receive",
g,this._uiHash(this))}}.call(this,this.containers[e]));c.push(function(f){return function(g){f._trigger("update",g,this._uiHash(this))}}.call(this,this.containers[e]))}}for(e=this.containers.length-1;e>=0;e--){b||c.push(function(f){return function(g){f._trigger("deactivate",g,this._uiHash(this))}}.call(this,this.containers[e]));if(this.containers[e].containerCache.over){c.push(function(f){return function(g){f._trigger("out",g,this._uiHash(this))}}.call(this,this.containers[e]));this.containers[e].containerCache.over=
0}}this._storedCursor&&d("body").css("cursor",this._storedCursor);this._storedOpacity&&this.helper.css("opacity",this._storedOpacity);if(this._storedZIndex)this.helper.css("zIndex",this._storedZIndex=="auto"?"":this._storedZIndex);this.dragging=false;if(this.cancelHelperRemoval){if(!b){this._trigger("beforeStop",a,this._uiHash());for(e=0;e<c.length;e++)c[e].call(this,a);this._trigger("stop",a,this._uiHash())}return false}b||this._trigger("beforeStop",a,this._uiHash());this.placeholder[0].parentNode.removeChild(this.placeholder[0]);
this.helper[0]!=this.currentItem[0]&&this.helper.remove();this.helper=null;if(!b){for(e=0;e<c.length;e++)c[e].call(this,a);this._trigger("stop",a,this._uiHash())}this.fromOutside=false;return true},_trigger:function(){d.Widget.prototype._trigger.apply(this,arguments)===false&&this.cancel()},_uiHash:function(a){var b=a||this;return{helper:b.helper,placeholder:b.placeholder||d([]),position:b.position,originalPosition:b.originalPosition,offset:b.positionAbs,item:b.currentItem,sender:a?a.element:null}}});
d.extend(d.ui.sortable,{version:"1.8.8"})})(jQuery);
;/*
 * jQuery UI Accordion 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Accordion
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 */
(function(c){c.widget("ui.accordion",{options:{active:0,animated:"slide",autoHeight:true,clearStyle:false,collapsible:false,event:"click",fillSpace:false,header:"> li > :first-child,> :not(li):even",icons:{header:"ui-icon-triangle-1-e",headerSelected:"ui-icon-triangle-1-s"},navigation:false,navigationFilter:function(){return this.href.toLowerCase()===location.href.toLowerCase()}},_create:function(){var a=this,b=a.options;a.running=0;a.element.addClass("ui-accordion ui-widget ui-helper-reset").children("li").addClass("ui-accordion-li-fix");
a.headers=a.element.find(b.header).addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-all").bind("mouseenter.accordion",function(){b.disabled||c(this).addClass("ui-state-hover")}).bind("mouseleave.accordion",function(){b.disabled||c(this).removeClass("ui-state-hover")}).bind("focus.accordion",function(){b.disabled||c(this).addClass("ui-state-focus")}).bind("blur.accordion",function(){b.disabled||c(this).removeClass("ui-state-focus")});a.headers.next().addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom");
if(b.navigation){var d=a.element.find("a").filter(b.navigationFilter).eq(0);if(d.length){var h=d.closest(".ui-accordion-header");a.active=h.length?h:d.closest(".ui-accordion-content").prev()}}a.active=a._findActive(a.active||b.active).addClass("ui-state-default ui-state-active").toggleClass("ui-corner-all").toggleClass("ui-corner-top");a.active.next().addClass("ui-accordion-content-active");a._createIcons();a.resize();a.element.attr("role","tablist");a.headers.attr("role","tab").bind("keydown.accordion",
function(f){return a._keydown(f)}).next().attr("role","tabpanel");a.headers.not(a.active||"").attr({"aria-expanded":"false",tabIndex:-1}).next().hide();a.active.length?a.active.attr({"aria-expanded":"true",tabIndex:0}):a.headers.eq(0).attr("tabIndex",0);c.browser.safari||a.headers.find("a").attr("tabIndex",-1);b.event&&a.headers.bind(b.event.split(" ").join(".accordion ")+".accordion",function(f){a._clickHandler.call(a,f,this);f.preventDefault()})},_createIcons:function(){var a=this.options;if(a.icons){c("<span></span>").addClass("ui-icon "+
a.icons.header).prependTo(this.headers);this.active.children(".ui-icon").toggleClass(a.icons.header).toggleClass(a.icons.headerSelected);this.element.addClass("ui-accordion-icons")}},_destroyIcons:function(){this.headers.children(".ui-icon").remove();this.element.removeClass("ui-accordion-icons")},destroy:function(){var a=this.options;this.element.removeClass("ui-accordion ui-widget ui-helper-reset").removeAttr("role");this.headers.unbind(".accordion").removeClass("ui-accordion-header ui-accordion-disabled ui-helper-reset ui-state-default ui-corner-all ui-state-active ui-state-disabled ui-corner-top").removeAttr("role").removeAttr("aria-expanded").removeAttr("tabIndex");
this.headers.find("a").removeAttr("tabIndex");this._destroyIcons();var b=this.headers.next().css("display","").removeAttr("role").removeClass("ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content ui-accordion-content-active ui-accordion-disabled ui-state-disabled");if(a.autoHeight||a.fillHeight)b.css("height","");return c.Widget.prototype.destroy.call(this)},_setOption:function(a,b){c.Widget.prototype._setOption.apply(this,arguments);a=="active"&&this.activate(b);if(a=="icons"){this._destroyIcons();
b&&this._createIcons()}if(a=="disabled")this.headers.add(this.headers.next())[b?"addClass":"removeClass"]("ui-accordion-disabled ui-state-disabled")},_keydown:function(a){if(!(this.options.disabled||a.altKey||a.ctrlKey)){var b=c.ui.keyCode,d=this.headers.length,h=this.headers.index(a.target),f=false;switch(a.keyCode){case b.RIGHT:case b.DOWN:f=this.headers[(h+1)%d];break;case b.LEFT:case b.UP:f=this.headers[(h-1+d)%d];break;case b.SPACE:case b.ENTER:this._clickHandler({target:a.target},a.target);
a.preventDefault()}if(f){c(a.target).attr("tabIndex",-1);c(f).attr("tabIndex",0);f.focus();return false}return true}},resize:function(){var a=this.options,b;if(a.fillSpace){if(c.browser.msie){var d=this.element.parent().css("overflow");this.element.parent().css("overflow","hidden")}b=this.element.parent().height();c.browser.msie&&this.element.parent().css("overflow",d);this.headers.each(function(){b-=c(this).outerHeight(true)});this.headers.next().each(function(){c(this).height(Math.max(0,b-c(this).innerHeight()+
c(this).height()))}).css("overflow","auto")}else if(a.autoHeight){b=0;this.headers.next().each(function(){b=Math.max(b,c(this).height("").height())}).height(b)}return this},activate:function(a){this.options.active=a;a=this._findActive(a)[0];this._clickHandler({target:a},a);return this},_findActive:function(a){return a?typeof a==="number"?this.headers.filter(":eq("+a+")"):this.headers.not(this.headers.not(a)):a===false?c([]):this.headers.filter(":eq(0)")},_clickHandler:function(a,b){var d=this.options;
if(!d.disabled)if(a.target){a=c(a.currentTarget||b);b=a[0]===this.active[0];d.active=d.collapsible&&b?false:this.headers.index(a);if(!(this.running||!d.collapsible&&b)){var h=this.active;j=a.next();g=this.active.next();e={options:d,newHeader:b&&d.collapsible?c([]):a,oldHeader:this.active,newContent:b&&d.collapsible?c([]):j,oldContent:g};var f=this.headers.index(this.active[0])>this.headers.index(a[0]);this.active=b?c([]):a;this._toggle(j,g,e,b,f);h.removeClass("ui-state-active ui-corner-top").addClass("ui-state-default ui-corner-all").children(".ui-icon").removeClass(d.icons.headerSelected).addClass(d.icons.header);
if(!b){a.removeClass("ui-state-default ui-corner-all").addClass("ui-state-active ui-corner-top").children(".ui-icon").removeClass(d.icons.header).addClass(d.icons.headerSelected);a.next().addClass("ui-accordion-content-active")}}}else if(d.collapsible){this.active.removeClass("ui-state-active ui-corner-top").addClass("ui-state-default ui-corner-all").children(".ui-icon").removeClass(d.icons.headerSelected).addClass(d.icons.header);this.active.next().addClass("ui-accordion-content-active");var g=this.active.next(),
e={options:d,newHeader:c([]),oldHeader:d.active,newContent:c([]),oldContent:g},j=this.active=c([]);this._toggle(j,g,e)}},_toggle:function(a,b,d,h,f){var g=this,e=g.options;g.toShow=a;g.toHide=b;g.data=d;var j=function(){if(g)return g._completed.apply(g,arguments)};g._trigger("changestart",null,g.data);g.running=b.size()===0?a.size():b.size();if(e.animated){d={};d=e.collapsible&&h?{toShow:c([]),toHide:b,complete:j,down:f,autoHeight:e.autoHeight||e.fillSpace}:{toShow:a,toHide:b,complete:j,down:f,autoHeight:e.autoHeight||
e.fillSpace};if(!e.proxied)e.proxied=e.animated;if(!e.proxiedDuration)e.proxiedDuration=e.duration;e.animated=c.isFunction(e.proxied)?e.proxied(d):e.proxied;e.duration=c.isFunction(e.proxiedDuration)?e.proxiedDuration(d):e.proxiedDuration;h=c.ui.accordion.animations;var i=e.duration,k=e.animated;if(k&&!h[k]&&!c.easing[k])k="slide";h[k]||(h[k]=function(l){this.slide(l,{easing:k,duration:i||700})});h[k](d)}else{if(e.collapsible&&h)a.toggle();else{b.hide();a.show()}j(true)}b.prev().attr({"aria-expanded":"false",
tabIndex:-1}).blur();a.prev().attr({"aria-expanded":"true",tabIndex:0}).focus()},_completed:function(a){this.running=a?0:--this.running;if(!this.running){this.options.clearStyle&&this.toShow.add(this.toHide).css({height:"",overflow:""});this.toHide.removeClass("ui-accordion-content-active");this.toHide.parent()[0].className=this.toHide.parent()[0].className;this._trigger("change",null,this.data)}}});c.extend(c.ui.accordion,{version:"1.8.8",animations:{slide:function(a,b){a=c.extend({easing:"swing",
duration:300},a,b);if(a.toHide.size())if(a.toShow.size()){var d=a.toShow.css("overflow"),h=0,f={},g={},e;b=a.toShow;e=b[0].style.width;b.width(parseInt(b.parent().width(),10)-parseInt(b.css("paddingLeft"),10)-parseInt(b.css("paddingRight"),10)-(parseInt(b.css("borderLeftWidth"),10)||0)-(parseInt(b.css("borderRightWidth"),10)||0));c.each(["height","paddingTop","paddingBottom"],function(j,i){g[i]="hide";j=(""+c.css(a.toShow[0],i)).match(/^([\d+-.]+)(.*)$/);f[i]={value:j[1],unit:j[2]||"px"}});a.toShow.css({height:0,
overflow:"hidden"}).show();a.toHide.filter(":hidden").each(a.complete).end().filter(":visible").animate(g,{step:function(j,i){if(i.prop=="height")h=i.end-i.start===0?0:(i.now-i.start)/(i.end-i.start);a.toShow[0].style[i.prop]=h*f[i.prop].value+f[i.prop].unit},duration:a.duration,easing:a.easing,complete:function(){a.autoHeight||a.toShow.css("height","");a.toShow.css({width:e,overflow:d});a.complete()}})}else a.toHide.animate({height:"hide",paddingTop:"hide",paddingBottom:"hide"},a);else a.toShow.animate({height:"show",
paddingTop:"show",paddingBottom:"show"},a)},bounceslide:function(a){this.slide(a,{easing:a.down?"easeOutBounce":"swing",duration:a.down?1E3:200})}}})})(jQuery);
;/*
 * jQuery UI Autocomplete 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Autocomplete
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 *	jquery.ui.position.js
 */
(function(d){d.widget("ui.autocomplete",{options:{appendTo:"body",delay:300,minLength:1,position:{my:"left top",at:"left bottom",collision:"none"},source:null},pending:0,_create:function(){var a=this,b=this.element[0].ownerDocument,f;this.element.addClass("ui-autocomplete-input").attr("autocomplete","off").attr({role:"textbox","aria-autocomplete":"list","aria-haspopup":"true"}).bind("keydown.autocomplete",function(c){if(!(a.options.disabled||a.element.attr("readonly"))){f=false;var e=d.ui.keyCode;
switch(c.keyCode){case e.PAGE_UP:a._move("previousPage",c);break;case e.PAGE_DOWN:a._move("nextPage",c);break;case e.UP:a._move("previous",c);c.preventDefault();break;case e.DOWN:a._move("next",c);c.preventDefault();break;case e.ENTER:case e.NUMPAD_ENTER:if(a.menu.active){f=true;c.preventDefault()}case e.TAB:if(!a.menu.active)return;a.menu.select(c);break;case e.ESCAPE:a.element.val(a.term);a.close(c);break;default:clearTimeout(a.searching);a.searching=setTimeout(function(){if(a.term!=a.element.val()){a.selectedItem=
null;a.search(null,c)}},a.options.delay);break}}}).bind("keypress.autocomplete",function(c){if(f){f=false;c.preventDefault()}}).bind("focus.autocomplete",function(){if(!a.options.disabled){a.selectedItem=null;a.previous=a.element.val()}}).bind("blur.autocomplete",function(c){if(!a.options.disabled){clearTimeout(a.searching);a.closing=setTimeout(function(){a.close(c);a._change(c)},150)}});this._initSource();this.response=function(){return a._response.apply(a,arguments)};this.menu=d("<ul></ul>").addClass("ui-autocomplete").appendTo(d(this.options.appendTo||
"body",b)[0]).mousedown(function(c){var e=a.menu.element[0];d(c.target).closest(".ui-menu-item").length||setTimeout(function(){d(document).one("mousedown",function(g){g.target!==a.element[0]&&g.target!==e&&!d.ui.contains(e,g.target)&&a.close()})},1);setTimeout(function(){clearTimeout(a.closing)},13)}).menu({focus:function(c,e){e=e.item.data("item.autocomplete");false!==a._trigger("focus",c,{item:e})&&/^key/.test(c.originalEvent.type)&&a.element.val(e.value)},selected:function(c,e){var g=e.item.data("item.autocomplete"),
h=a.previous;if(a.element[0]!==b.activeElement){a.element.focus();a.previous=h;setTimeout(function(){a.previous=h;a.selectedItem=g},1)}false!==a._trigger("select",c,{item:g})&&a.element.val(g.value);a.term=a.element.val();a.close(c);a.selectedItem=g},blur:function(){a.menu.element.is(":visible")&&a.element.val()!==a.term&&a.element.val(a.term)}}).zIndex(this.element.zIndex()+1).css({top:0,left:0}).hide().data("menu");d.fn.bgiframe&&this.menu.element.bgiframe()},destroy:function(){this.element.removeClass("ui-autocomplete-input").removeAttr("autocomplete").removeAttr("role").removeAttr("aria-autocomplete").removeAttr("aria-haspopup");
this.menu.element.remove();d.Widget.prototype.destroy.call(this)},_setOption:function(a,b){d.Widget.prototype._setOption.apply(this,arguments);a==="source"&&this._initSource();if(a==="appendTo")this.menu.element.appendTo(d(b||"body",this.element[0].ownerDocument)[0]);a==="disabled"&&b&&this.xhr&&this.xhr.abort()},_initSource:function(){var a=this,b,f;if(d.isArray(this.options.source)){b=this.options.source;this.source=function(c,e){e(d.ui.autocomplete.filter(b,c.term))}}else if(typeof this.options.source===
"string"){f=this.options.source;this.source=function(c,e){a.xhr&&a.xhr.abort();a.xhr=d.ajax({url:f,data:c,dataType:"json",success:function(g,h,i){i===a.xhr&&e(g);a.xhr=null},error:function(g){g===a.xhr&&e([]);a.xhr=null}})}}else this.source=this.options.source},search:function(a,b){a=a!=null?a:this.element.val();this.term=this.element.val();if(a.length<this.options.minLength)return this.close(b);clearTimeout(this.closing);if(this._trigger("search",b)!==false)return this._search(a)},_search:function(a){this.pending++;
this.element.addClass("ui-autocomplete-loading");this.source({term:a},this.response)},_response:function(a){if(!this.options.disabled&&a&&a.length){a=this._normalize(a);this._suggest(a);this._trigger("open")}else this.close();this.pending--;this.pending||this.element.removeClass("ui-autocomplete-loading")},close:function(a){clearTimeout(this.closing);if(this.menu.element.is(":visible")){this.menu.element.hide();this.menu.deactivate();this._trigger("close",a)}},_change:function(a){this.previous!==
this.element.val()&&this._trigger("change",a,{item:this.selectedItem})},_normalize:function(a){if(a.length&&a[0].label&&a[0].value)return a;return d.map(a,function(b){if(typeof b==="string")return{label:b,value:b};return d.extend({label:b.label||b.value,value:b.value||b.label},b)})},_suggest:function(a){var b=this.menu.element.empty().zIndex(this.element.zIndex()+1);this._renderMenu(b,a);this.menu.deactivate();this.menu.refresh();b.show();this._resizeMenu();b.position(d.extend({of:this.element},this.options.position))},
_resizeMenu:function(){var a=this.menu.element;a.outerWidth(Math.max(a.width("").outerWidth(),this.element.outerWidth()))},_renderMenu:function(a,b){var f=this;d.each(b,function(c,e){f._renderItem(a,e)})},_renderItem:function(a,b){return d("<li></li>").data("item.autocomplete",b).append(d("<a></a>").text(b.label)).appendTo(a)},_move:function(a,b){if(this.menu.element.is(":visible"))if(this.menu.first()&&/^previous/.test(a)||this.menu.last()&&/^next/.test(a)){this.element.val(this.term);this.menu.deactivate()}else this.menu[a](b);
else this.search(null,b)},widget:function(){return this.menu.element}});d.extend(d.ui.autocomplete,{escapeRegex:function(a){return a.replace(/[-[\]{}()*+?.,\\^$|#\s]/g,"\\$&")},filter:function(a,b){var f=new RegExp(d.ui.autocomplete.escapeRegex(b),"i");return d.grep(a,function(c){return f.test(c.label||c.value||c)})}})})(jQuery);
(function(d){d.widget("ui.menu",{_create:function(){var a=this;this.element.addClass("ui-menu ui-widget ui-widget-content ui-corner-all").attr({role:"listbox","aria-activedescendant":"ui-active-menuitem"}).click(function(b){if(d(b.target).closest(".ui-menu-item a").length){b.preventDefault();a.select(b)}});this.refresh()},refresh:function(){var a=this;this.element.children("li:not(.ui-menu-item):has(a)").addClass("ui-menu-item").attr("role","menuitem").children("a").addClass("ui-corner-all").attr("tabindex",
-1).mouseenter(function(b){a.activate(b,d(this).parent())}).mouseleave(function(){a.deactivate()})},activate:function(a,b){this.deactivate();if(this.hasScroll()){var f=b.offset().top-this.element.offset().top,c=this.element.attr("scrollTop"),e=this.element.height();if(f<0)this.element.attr("scrollTop",c+f);else f>=e&&this.element.attr("scrollTop",c+f-e+b.height())}this.active=b.eq(0).children("a").addClass("ui-state-hover").attr("id","ui-active-menuitem").end();this._trigger("focus",a,{item:b})},
deactivate:function(){if(this.active){this.active.children("a").removeClass("ui-state-hover").removeAttr("id");this._trigger("blur");this.active=null}},next:function(a){this.move("next",".ui-menu-item:first",a)},previous:function(a){this.move("prev",".ui-menu-item:last",a)},first:function(){return this.active&&!this.active.prevAll(".ui-menu-item").length},last:function(){return this.active&&!this.active.nextAll(".ui-menu-item").length},move:function(a,b,f){if(this.active){a=this.active[a+"All"](".ui-menu-item").eq(0);
a.length?this.activate(f,a):this.activate(f,this.element.children(b))}else this.activate(f,this.element.children(b))},nextPage:function(a){if(this.hasScroll())if(!this.active||this.last())this.activate(a,this.element.children(".ui-menu-item:first"));else{var b=this.active.offset().top,f=this.element.height(),c=this.element.children(".ui-menu-item").filter(function(){var e=d(this).offset().top-b-f+d(this).height();return e<10&&e>-10});c.length||(c=this.element.children(".ui-menu-item:last"));this.activate(a,
c)}else this.activate(a,this.element.children(".ui-menu-item").filter(!this.active||this.last()?":first":":last"))},previousPage:function(a){if(this.hasScroll())if(!this.active||this.first())this.activate(a,this.element.children(".ui-menu-item:last"));else{var b=this.active.offset().top,f=this.element.height();result=this.element.children(".ui-menu-item").filter(function(){var c=d(this).offset().top-b+f-d(this).height();return c<10&&c>-10});result.length||(result=this.element.children(".ui-menu-item:first"));
this.activate(a,result)}else this.activate(a,this.element.children(".ui-menu-item").filter(!this.active||this.first()?":last":":first"))},hasScroll:function(){return this.element.height()<this.element.attr("scrollHeight")},select:function(a){this._trigger("selected",a,{item:this.active})}})})(jQuery);
;/*
 * jQuery UI Button 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Button
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 */
(function(a){var g,i=function(b){a(":ui-button",b.target.form).each(function(){var c=a(this).data("button");setTimeout(function(){c.refresh()},1)})},h=function(b){var c=b.name,d=b.form,e=a([]);if(c)e=d?a(d).find("[name='"+c+"']"):a("[name='"+c+"']",b.ownerDocument).filter(function(){return!this.form});return e};a.widget("ui.button",{options:{disabled:null,text:true,label:null,icons:{primary:null,secondary:null}},_create:function(){this.element.closest("form").unbind("reset.button").bind("reset.button",
i);if(typeof this.options.disabled!=="boolean")this.options.disabled=this.element.attr("disabled");this._determineButtonType();this.hasTitle=!!this.buttonElement.attr("title");var b=this,c=this.options,d=this.type==="checkbox"||this.type==="radio",e="ui-state-hover"+(!d?" ui-state-active":"");if(c.label===null)c.label=this.buttonElement.html();if(this.element.is(":disabled"))c.disabled=true;this.buttonElement.addClass("ui-button ui-widget ui-state-default ui-corner-all").attr("role","button").bind("mouseenter.button",
function(){if(!c.disabled){a(this).addClass("ui-state-hover");this===g&&a(this).addClass("ui-state-active")}}).bind("mouseleave.button",function(){c.disabled||a(this).removeClass(e)}).bind("focus.button",function(){a(this).addClass("ui-state-focus")}).bind("blur.button",function(){a(this).removeClass("ui-state-focus")});d&&this.element.bind("change.button",function(){b.refresh()});if(this.type==="checkbox")this.buttonElement.bind("click.button",function(){if(c.disabled)return false;a(this).toggleClass("ui-state-active");
b.buttonElement.attr("aria-pressed",b.element[0].checked)});else if(this.type==="radio")this.buttonElement.bind("click.button",function(){if(c.disabled)return false;a(this).addClass("ui-state-active");b.buttonElement.attr("aria-pressed",true);var f=b.element[0];h(f).not(f).map(function(){return a(this).button("widget")[0]}).removeClass("ui-state-active").attr("aria-pressed",false)});else{this.buttonElement.bind("mousedown.button",function(){if(c.disabled)return false;a(this).addClass("ui-state-active");
g=this;a(document).one("mouseup",function(){g=null})}).bind("mouseup.button",function(){if(c.disabled)return false;a(this).removeClass("ui-state-active")}).bind("keydown.button",function(f){if(c.disabled)return false;if(f.keyCode==a.ui.keyCode.SPACE||f.keyCode==a.ui.keyCode.ENTER)a(this).addClass("ui-state-active")}).bind("keyup.button",function(){a(this).removeClass("ui-state-active")});this.buttonElement.is("a")&&this.buttonElement.keyup(function(f){f.keyCode===a.ui.keyCode.SPACE&&a(this).click()})}this._setOption("disabled",
c.disabled)},_determineButtonType:function(){this.type=this.element.is(":checkbox")?"checkbox":this.element.is(":radio")?"radio":this.element.is("input")?"input":"button";if(this.type==="checkbox"||this.type==="radio"){this.buttonElement=this.element.parents().last().find("label[for="+this.element.attr("id")+"]");this.element.addClass("ui-helper-hidden-accessible");var b=this.element.is(":checked");b&&this.buttonElement.addClass("ui-state-active");this.buttonElement.attr("aria-pressed",b)}else this.buttonElement=
this.element},widget:function(){return this.buttonElement},destroy:function(){this.element.removeClass("ui-helper-hidden-accessible");this.buttonElement.removeClass("ui-button ui-widget ui-state-default ui-corner-all ui-state-hover ui-state-active  ui-button-icons-only ui-button-icon-only ui-button-text-icons ui-button-text-icon-primary ui-button-text-icon-secondary ui-button-text-only").removeAttr("role").removeAttr("aria-pressed").html(this.buttonElement.find(".ui-button-text").html());this.hasTitle||
this.buttonElement.removeAttr("title");a.Widget.prototype.destroy.call(this)},_setOption:function(b,c){a.Widget.prototype._setOption.apply(this,arguments);if(b==="disabled")c?this.element.attr("disabled",true):this.element.removeAttr("disabled");this._resetButton()},refresh:function(){var b=this.element.is(":disabled");b!==this.options.disabled&&this._setOption("disabled",b);if(this.type==="radio")h(this.element[0]).each(function(){a(this).is(":checked")?a(this).button("widget").addClass("ui-state-active").attr("aria-pressed",
true):a(this).button("widget").removeClass("ui-state-active").attr("aria-pressed",false)});else if(this.type==="checkbox")this.element.is(":checked")?this.buttonElement.addClass("ui-state-active").attr("aria-pressed",true):this.buttonElement.removeClass("ui-state-active").attr("aria-pressed",false)},_resetButton:function(){if(this.type==="input")this.options.label&&this.element.val(this.options.label);else{var b=this.buttonElement.removeClass("ui-button-icons-only ui-button-icon-only ui-button-text-icons ui-button-text-icon-primary ui-button-text-icon-secondary ui-button-text-only"),
c=a("<span></span>").addClass("ui-button-text").html(this.options.label).appendTo(b.empty()).text(),d=this.options.icons,e=d.primary&&d.secondary;if(d.primary||d.secondary){b.addClass("ui-button-text-icon"+(e?"s":d.primary?"-primary":"-secondary"));d.primary&&b.prepend("<span class='ui-button-icon-primary ui-icon "+d.primary+"'></span>");d.secondary&&b.append("<span class='ui-button-icon-secondary ui-icon "+d.secondary+"'></span>");if(!this.options.text){b.addClass(e?"ui-button-icons-only":"ui-button-icon-only").removeClass("ui-button-text-icons ui-button-text-icon-primary ui-button-text-icon-secondary");
this.hasTitle||b.attr("title",c)}}else b.addClass("ui-button-text-only")}}});a.widget("ui.buttonset",{options:{items:":button, :submit, :reset, :checkbox, :radio, a, :data(button)"},_create:function(){this.element.addClass("ui-buttonset")},_init:function(){this.refresh()},_setOption:function(b,c){b==="disabled"&&this.buttons.button("option",b,c);a.Widget.prototype._setOption.apply(this,arguments)},refresh:function(){this.buttons=this.element.find(this.options.items).filter(":ui-button").button("refresh").end().not(":ui-button").button().end().map(function(){return a(this).button("widget")[0]}).removeClass("ui-corner-all ui-corner-left ui-corner-right").filter(":first").addClass("ui-corner-left").end().filter(":last").addClass("ui-corner-right").end().end()},
destroy:function(){this.element.removeClass("ui-buttonset");this.buttons.map(function(){return a(this).button("widget")[0]}).removeClass("ui-corner-left ui-corner-right").end().button("destroy");a.Widget.prototype.destroy.call(this)}})})(jQuery);
;/*
 * jQuery UI Dialog 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Dialog
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 *  jquery.ui.button.js
 *	jquery.ui.draggable.js
 *	jquery.ui.mouse.js
 *	jquery.ui.position.js
 *	jquery.ui.resizable.js
 */
(function(c,j){var k={buttons:true,height:true,maxHeight:true,maxWidth:true,minHeight:true,minWidth:true,width:true},l={maxHeight:true,maxWidth:true,minHeight:true,minWidth:true};c.widget("ui.dialog",{options:{autoOpen:true,buttons:{},closeOnEscape:true,closeText:"close",dialogClass:"",draggable:true,hide:null,height:"auto",maxHeight:false,maxWidth:false,minHeight:150,minWidth:150,modal:false,position:{my:"center",at:"center",collision:"fit",using:function(a){var b=c(this).css(a).offset().top;b<0&&
c(this).css("top",a.top-b)}},resizable:true,show:null,stack:true,title:"",width:300,zIndex:1E3},_create:function(){this.originalTitle=this.element.attr("title");if(typeof this.originalTitle!=="string")this.originalTitle="";this.options.title=this.options.title||this.originalTitle;var a=this,b=a.options,d=b.title||"&#160;",e=c.ui.dialog.getTitleId(a.element),g=(a.uiDialog=c("<div></div>")).appendTo(document.body).hide().addClass("ui-dialog ui-widget ui-widget-content ui-corner-all "+b.dialogClass).css({zIndex:b.zIndex}).attr("tabIndex",
-1).css("outline",0).keydown(function(i){if(b.closeOnEscape&&i.keyCode&&i.keyCode===c.ui.keyCode.ESCAPE){a.close(i);i.preventDefault()}}).attr({role:"dialog","aria-labelledby":e}).mousedown(function(i){a.moveToTop(false,i)});a.element.show().removeAttr("title").addClass("ui-dialog-content ui-widget-content").appendTo(g);var f=(a.uiDialogTitlebar=c("<div></div>")).addClass("ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix").prependTo(g),h=c('<a href="#"></a>').addClass("ui-dialog-titlebar-close ui-corner-all").attr("role",
"button").hover(function(){h.addClass("ui-state-hover")},function(){h.removeClass("ui-state-hover")}).focus(function(){h.addClass("ui-state-focus")}).blur(function(){h.removeClass("ui-state-focus")}).click(function(i){a.close(i);return false}).appendTo(f);(a.uiDialogTitlebarCloseText=c("<span></span>")).addClass("ui-icon ui-icon-closethick").text(b.closeText).appendTo(h);c("<span></span>").addClass("ui-dialog-title").attr("id",e).html(d).prependTo(f);if(c.isFunction(b.beforeclose)&&!c.isFunction(b.beforeClose))b.beforeClose=
b.beforeclose;f.find("*").add(f).disableSelection();b.draggable&&c.fn.draggable&&a._makeDraggable();b.resizable&&c.fn.resizable&&a._makeResizable();a._createButtons(b.buttons);a._isOpen=false;c.fn.bgiframe&&g.bgiframe()},_init:function(){this.options.autoOpen&&this.open()},destroy:function(){var a=this;a.overlay&&a.overlay.destroy();a.uiDialog.hide();a.element.unbind(".dialog").removeData("dialog").removeClass("ui-dialog-content ui-widget-content").hide().appendTo("body");a.uiDialog.remove();a.originalTitle&&
a.element.attr("title",a.originalTitle);return a},widget:function(){return this.uiDialog},close:function(a){var b=this,d,e;if(false!==b._trigger("beforeClose",a)){b.overlay&&b.overlay.destroy();b.uiDialog.unbind("keypress.ui-dialog");b._isOpen=false;if(b.options.hide)b.uiDialog.hide(b.options.hide,function(){b._trigger("close",a)});else{b.uiDialog.hide();b._trigger("close",a)}c.ui.dialog.overlay.resize();if(b.options.modal){d=0;c(".ui-dialog").each(function(){if(this!==b.uiDialog[0]){e=c(this).css("z-index");
isNaN(e)||(d=Math.max(d,e))}});c.ui.dialog.maxZ=d}return b}},isOpen:function(){return this._isOpen},moveToTop:function(a,b){var d=this,e=d.options;if(e.modal&&!a||!e.stack&&!e.modal)return d._trigger("focus",b);if(e.zIndex>c.ui.dialog.maxZ)c.ui.dialog.maxZ=e.zIndex;if(d.overlay){c.ui.dialog.maxZ+=1;d.overlay.$el.css("z-index",c.ui.dialog.overlay.maxZ=c.ui.dialog.maxZ)}a={scrollTop:d.element.attr("scrollTop"),scrollLeft:d.element.attr("scrollLeft")};c.ui.dialog.maxZ+=1;d.uiDialog.css("z-index",c.ui.dialog.maxZ);
d.element.attr(a);d._trigger("focus",b);return d},open:function(){if(!this._isOpen){var a=this,b=a.options,d=a.uiDialog;a.overlay=b.modal?new c.ui.dialog.overlay(a):null;a._size();a._position(b.position);d.show(b.show);a.moveToTop(true);b.modal&&d.bind("keypress.ui-dialog",function(e){if(e.keyCode===c.ui.keyCode.TAB){var g=c(":tabbable",this),f=g.filter(":first");g=g.filter(":last");if(e.target===g[0]&&!e.shiftKey){f.focus(1);return false}else if(e.target===f[0]&&e.shiftKey){g.focus(1);return false}}});
c(a.element.find(":tabbable").get().concat(d.find(".ui-dialog-buttonpane :tabbable").get().concat(d.get()))).eq(0).focus();a._isOpen=true;a._trigger("open");return a}},_createButtons:function(a){var b=this,d=false,e=c("<div></div>").addClass("ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"),g=c("<div></div>").addClass("ui-dialog-buttonset").appendTo(e);b.uiDialog.find(".ui-dialog-buttonpane").remove();typeof a==="object"&&a!==null&&c.each(a,function(){return!(d=true)});if(d){c.each(a,function(f,
h){h=c.isFunction(h)?{click:h,text:f}:h;f=c('<button type="button"></button>').attr(h,true).unbind("click").click(function(){h.click.apply(b.element[0],arguments)}).appendTo(g);c.fn.button&&f.button()});e.appendTo(b.uiDialog)}},_makeDraggable:function(){function a(f){return{position:f.position,offset:f.offset}}var b=this,d=b.options,e=c(document),g;b.uiDialog.draggable({cancel:".ui-dialog-content, .ui-dialog-titlebar-close",handle:".ui-dialog-titlebar",containment:"document",start:function(f,h){g=
d.height==="auto"?"auto":c(this).height();c(this).height(c(this).height()).addClass("ui-dialog-dragging");b._trigger("dragStart",f,a(h))},drag:function(f,h){b._trigger("drag",f,a(h))},stop:function(f,h){d.position=[h.position.left-e.scrollLeft(),h.position.top-e.scrollTop()];c(this).removeClass("ui-dialog-dragging").height(g);b._trigger("dragStop",f,a(h));c.ui.dialog.overlay.resize()}})},_makeResizable:function(a){function b(f){return{originalPosition:f.originalPosition,originalSize:f.originalSize,
position:f.position,size:f.size}}a=a===j?this.options.resizable:a;var d=this,e=d.options,g=d.uiDialog.css("position");a=typeof a==="string"?a:"n,e,s,w,se,sw,ne,nw";d.uiDialog.resizable({cancel:".ui-dialog-content",containment:"document",alsoResize:d.element,maxWidth:e.maxWidth,maxHeight:e.maxHeight,minWidth:e.minWidth,minHeight:d._minHeight(),handles:a,start:function(f,h){c(this).addClass("ui-dialog-resizing");d._trigger("resizeStart",f,b(h))},resize:function(f,h){d._trigger("resize",f,b(h))},stop:function(f,
h){c(this).removeClass("ui-dialog-resizing");e.height=c(this).height();e.width=c(this).width();d._trigger("resizeStop",f,b(h));c.ui.dialog.overlay.resize()}}).css("position",g).find(".ui-resizable-se").addClass("ui-icon ui-icon-grip-diagonal-se")},_minHeight:function(){var a=this.options;return a.height==="auto"?a.minHeight:Math.min(a.minHeight,a.height)},_position:function(a){var b=[],d=[0,0],e;if(a){if(typeof a==="string"||typeof a==="object"&&"0"in a){b=a.split?a.split(" "):[a[0],a[1]];if(b.length===
1)b[1]=b[0];c.each(["left","top"],function(g,f){if(+b[g]===b[g]){d[g]=b[g];b[g]=f}});a={my:b.join(" "),at:b.join(" "),offset:d.join(" ")}}a=c.extend({},c.ui.dialog.prototype.options.position,a)}else a=c.ui.dialog.prototype.options.position;(e=this.uiDialog.is(":visible"))||this.uiDialog.show();this.uiDialog.css({top:0,left:0}).position(c.extend({of:window},a));e||this.uiDialog.hide()},_setOptions:function(a){var b=this,d={},e=false;c.each(a,function(g,f){b._setOption(g,f);if(g in k)e=true;if(g in
l)d[g]=f});e&&this._size();this.uiDialog.is(":data(resizable)")&&this.uiDialog.resizable("option",d)},_setOption:function(a,b){var d=this,e=d.uiDialog;switch(a){case "beforeclose":a="beforeClose";break;case "buttons":d._createButtons(b);break;case "closeText":d.uiDialogTitlebarCloseText.text(""+b);break;case "dialogClass":e.removeClass(d.options.dialogClass).addClass("ui-dialog ui-widget ui-widget-content ui-corner-all "+b);break;case "disabled":b?e.addClass("ui-dialog-disabled"):e.removeClass("ui-dialog-disabled");
break;case "draggable":var g=e.is(":data(draggable)");g&&!b&&e.draggable("destroy");!g&&b&&d._makeDraggable();break;case "position":d._position(b);break;case "resizable":(g=e.is(":data(resizable)"))&&!b&&e.resizable("destroy");g&&typeof b==="string"&&e.resizable("option","handles",b);!g&&b!==false&&d._makeResizable(b);break;case "title":c(".ui-dialog-title",d.uiDialogTitlebar).html(""+(b||"&#160;"));break}c.Widget.prototype._setOption.apply(d,arguments)},_size:function(){var a=this.options,b,d,e=
this.uiDialog.is(":visible");this.element.show().css({width:"auto",minHeight:0,height:0});if(a.minWidth>a.width)a.width=a.minWidth;b=this.uiDialog.css({height:"auto",width:a.width}).height();d=Math.max(0,a.minHeight-b);if(a.height==="auto")if(c.support.minHeight)this.element.css({minHeight:d,height:"auto"});else{this.uiDialog.show();a=this.element.css("height","auto").height();e||this.uiDialog.hide();this.element.height(Math.max(a,d))}else this.element.height(Math.max(a.height-b,0));this.uiDialog.is(":data(resizable)")&&
this.uiDialog.resizable("option","minHeight",this._minHeight())}});c.extend(c.ui.dialog,{version:"1.8.8",uuid:0,maxZ:0,getTitleId:function(a){a=a.attr("id");if(!a){this.uuid+=1;a=this.uuid}return"ui-dialog-title-"+a},overlay:function(a){this.$el=c.ui.dialog.overlay.create(a)}});c.extend(c.ui.dialog.overlay,{instances:[],oldInstances:[],maxZ:0,events:c.map("focus,mousedown,mouseup,keydown,keypress,click".split(","),function(a){return a+".dialog-overlay"}).join(" "),create:function(a){if(this.instances.length===
0){setTimeout(function(){c.ui.dialog.overlay.instances.length&&c(document).bind(c.ui.dialog.overlay.events,function(d){if(c(d.target).zIndex()<c.ui.dialog.overlay.maxZ)return false})},1);c(document).bind("keydown.dialog-overlay",function(d){if(a.options.closeOnEscape&&d.keyCode&&d.keyCode===c.ui.keyCode.ESCAPE){a.close(d);d.preventDefault()}});c(window).bind("resize.dialog-overlay",c.ui.dialog.overlay.resize)}var b=(this.oldInstances.pop()||c("<div></div>").addClass("ui-widget-overlay")).appendTo(document.body).css({width:this.width(),
height:this.height()});c.fn.bgiframe&&b.bgiframe();this.instances.push(b);return b},destroy:function(a){var b=c.inArray(a,this.instances);b!=-1&&this.oldInstances.push(this.instances.splice(b,1)[0]);this.instances.length===0&&c([document,window]).unbind(".dialog-overlay");a.remove();var d=0;c.each(this.instances,function(){d=Math.max(d,this.css("z-index"))});this.maxZ=d},height:function(){var a,b;if(c.browser.msie&&c.browser.version<7){a=Math.max(document.documentElement.scrollHeight,document.body.scrollHeight);
b=Math.max(document.documentElement.offsetHeight,document.body.offsetHeight);return a<b?c(window).height()+"px":a+"px"}else return c(document).height()+"px"},width:function(){var a,b;if(c.browser.msie&&c.browser.version<7){a=Math.max(document.documentElement.scrollWidth,document.body.scrollWidth);b=Math.max(document.documentElement.offsetWidth,document.body.offsetWidth);return a<b?c(window).width()+"px":a+"px"}else return c(document).width()+"px"},resize:function(){var a=c([]);c.each(c.ui.dialog.overlay.instances,
function(){a=a.add(this)});a.css({width:0,height:0}).css({width:c.ui.dialog.overlay.width(),height:c.ui.dialog.overlay.height()})}});c.extend(c.ui.dialog.overlay.prototype,{destroy:function(){c.ui.dialog.overlay.destroy(this.$el)}})})(jQuery);
;/*
 * jQuery UI Slider 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Slider
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.mouse.js
 *	jquery.ui.widget.js
 */
(function(d){d.widget("ui.slider",d.ui.mouse,{widgetEventPrefix:"slide",options:{animate:false,distance:0,max:100,min:0,orientation:"horizontal",range:false,step:1,value:0,values:null},_create:function(){var b=this,a=this.options;this._mouseSliding=this._keySliding=false;this._animateOff=true;this._handleIndex=null;this._detectOrientation();this._mouseInit();this.element.addClass("ui-slider ui-slider-"+this.orientation+" ui-widget ui-widget-content ui-corner-all");a.disabled&&this.element.addClass("ui-slider-disabled ui-disabled");
this.range=d([]);if(a.range){if(a.range===true){this.range=d("<div></div>");if(!a.values)a.values=[this._valueMin(),this._valueMin()];if(a.values.length&&a.values.length!==2)a.values=[a.values[0],a.values[0]]}else this.range=d("<div></div>");this.range.appendTo(this.element).addClass("ui-slider-range");if(a.range==="min"||a.range==="max")this.range.addClass("ui-slider-range-"+a.range);this.range.addClass("ui-widget-header")}d(".ui-slider-handle",this.element).length===0&&d("<a href='#'></a>").appendTo(this.element).addClass("ui-slider-handle");
if(a.values&&a.values.length)for(;d(".ui-slider-handle",this.element).length<a.values.length;)d("<a href='#'></a>").appendTo(this.element).addClass("ui-slider-handle");this.handles=d(".ui-slider-handle",this.element).addClass("ui-state-default ui-corner-all");this.handle=this.handles.eq(0);this.handles.add(this.range).filter("a").click(function(c){c.preventDefault()}).hover(function(){a.disabled||d(this).addClass("ui-state-hover")},function(){d(this).removeClass("ui-state-hover")}).focus(function(){if(a.disabled)d(this).blur();
else{d(".ui-slider .ui-state-focus").removeClass("ui-state-focus");d(this).addClass("ui-state-focus")}}).blur(function(){d(this).removeClass("ui-state-focus")});this.handles.each(function(c){d(this).data("index.ui-slider-handle",c)});this.handles.keydown(function(c){var e=true,f=d(this).data("index.ui-slider-handle"),h,g,i;if(!b.options.disabled){switch(c.keyCode){case d.ui.keyCode.HOME:case d.ui.keyCode.END:case d.ui.keyCode.PAGE_UP:case d.ui.keyCode.PAGE_DOWN:case d.ui.keyCode.UP:case d.ui.keyCode.RIGHT:case d.ui.keyCode.DOWN:case d.ui.keyCode.LEFT:e=
false;if(!b._keySliding){b._keySliding=true;d(this).addClass("ui-state-active");h=b._start(c,f);if(h===false)return}break}i=b.options.step;h=b.options.values&&b.options.values.length?(g=b.values(f)):(g=b.value());switch(c.keyCode){case d.ui.keyCode.HOME:g=b._valueMin();break;case d.ui.keyCode.END:g=b._valueMax();break;case d.ui.keyCode.PAGE_UP:g=b._trimAlignValue(h+(b._valueMax()-b._valueMin())/5);break;case d.ui.keyCode.PAGE_DOWN:g=b._trimAlignValue(h-(b._valueMax()-b._valueMin())/5);break;case d.ui.keyCode.UP:case d.ui.keyCode.RIGHT:if(h===
b._valueMax())return;g=b._trimAlignValue(h+i);break;case d.ui.keyCode.DOWN:case d.ui.keyCode.LEFT:if(h===b._valueMin())return;g=b._trimAlignValue(h-i);break}b._slide(c,f,g);return e}}).keyup(function(c){var e=d(this).data("index.ui-slider-handle");if(b._keySliding){b._keySliding=false;b._stop(c,e);b._change(c,e);d(this).removeClass("ui-state-active")}});this._refreshValue();this._animateOff=false},destroy:function(){this.handles.remove();this.range.remove();this.element.removeClass("ui-slider ui-slider-horizontal ui-slider-vertical ui-slider-disabled ui-widget ui-widget-content ui-corner-all").removeData("slider").unbind(".slider");
this._mouseDestroy();return this},_mouseCapture:function(b){var a=this.options,c,e,f,h,g;if(a.disabled)return false;this.elementSize={width:this.element.outerWidth(),height:this.element.outerHeight()};this.elementOffset=this.element.offset();c=this._normValueFromMouse({x:b.pageX,y:b.pageY});e=this._valueMax()-this._valueMin()+1;h=this;this.handles.each(function(i){var j=Math.abs(c-h.values(i));if(e>j){e=j;f=d(this);g=i}});if(a.range===true&&this.values(1)===a.min){g+=1;f=d(this.handles[g])}if(this._start(b,
g)===false)return false;this._mouseSliding=true;h._handleIndex=g;f.addClass("ui-state-active").focus();a=f.offset();this._clickOffset=!d(b.target).parents().andSelf().is(".ui-slider-handle")?{left:0,top:0}:{left:b.pageX-a.left-f.width()/2,top:b.pageY-a.top-f.height()/2-(parseInt(f.css("borderTopWidth"),10)||0)-(parseInt(f.css("borderBottomWidth"),10)||0)+(parseInt(f.css("marginTop"),10)||0)};this.handles.hasClass("ui-state-hover")||this._slide(b,g,c);return this._animateOff=true},_mouseStart:function(){return true},
_mouseDrag:function(b){var a=this._normValueFromMouse({x:b.pageX,y:b.pageY});this._slide(b,this._handleIndex,a);return false},_mouseStop:function(b){this.handles.removeClass("ui-state-active");this._mouseSliding=false;this._stop(b,this._handleIndex);this._change(b,this._handleIndex);this._clickOffset=this._handleIndex=null;return this._animateOff=false},_detectOrientation:function(){this.orientation=this.options.orientation==="vertical"?"vertical":"horizontal"},_normValueFromMouse:function(b){var a;
if(this.orientation==="horizontal"){a=this.elementSize.width;b=b.x-this.elementOffset.left-(this._clickOffset?this._clickOffset.left:0)}else{a=this.elementSize.height;b=b.y-this.elementOffset.top-(this._clickOffset?this._clickOffset.top:0)}a=b/a;if(a>1)a=1;if(a<0)a=0;if(this.orientation==="vertical")a=1-a;b=this._valueMax()-this._valueMin();return this._trimAlignValue(this._valueMin()+a*b)},_start:function(b,a){var c={handle:this.handles[a],value:this.value()};if(this.options.values&&this.options.values.length){c.value=
this.values(a);c.values=this.values()}return this._trigger("start",b,c)},_slide:function(b,a,c){var e;if(this.options.values&&this.options.values.length){e=this.values(a?0:1);if(this.options.values.length===2&&this.options.range===true&&(a===0&&c>e||a===1&&c<e))c=e;if(c!==this.values(a)){e=this.values();e[a]=c;b=this._trigger("slide",b,{handle:this.handles[a],value:c,values:e});this.values(a?0:1);b!==false&&this.values(a,c,true)}}else if(c!==this.value()){b=this._trigger("slide",b,{handle:this.handles[a],
value:c});b!==false&&this.value(c)}},_stop:function(b,a){var c={handle:this.handles[a],value:this.value()};if(this.options.values&&this.options.values.length){c.value=this.values(a);c.values=this.values()}this._trigger("stop",b,c)},_change:function(b,a){if(!this._keySliding&&!this._mouseSliding){var c={handle:this.handles[a],value:this.value()};if(this.options.values&&this.options.values.length){c.value=this.values(a);c.values=this.values()}this._trigger("change",b,c)}},value:function(b){if(arguments.length){this.options.value=
this._trimAlignValue(b);this._refreshValue();this._change(null,0)}return this._value()},values:function(b,a){var c,e,f;if(arguments.length>1){this.options.values[b]=this._trimAlignValue(a);this._refreshValue();this._change(null,b)}if(arguments.length)if(d.isArray(arguments[0])){c=this.options.values;e=arguments[0];for(f=0;f<c.length;f+=1){c[f]=this._trimAlignValue(e[f]);this._change(null,f)}this._refreshValue()}else return this.options.values&&this.options.values.length?this._values(b):this.value();
else return this._values()},_setOption:function(b,a){var c,e=0;if(d.isArray(this.options.values))e=this.options.values.length;d.Widget.prototype._setOption.apply(this,arguments);switch(b){case "disabled":if(a){this.handles.filter(".ui-state-focus").blur();this.handles.removeClass("ui-state-hover");this.handles.attr("disabled","disabled");this.element.addClass("ui-disabled")}else{this.handles.removeAttr("disabled");this.element.removeClass("ui-disabled")}break;case "orientation":this._detectOrientation();
this.element.removeClass("ui-slider-horizontal ui-slider-vertical").addClass("ui-slider-"+this.orientation);this._refreshValue();break;case "value":this._animateOff=true;this._refreshValue();this._change(null,0);this._animateOff=false;break;case "values":this._animateOff=true;this._refreshValue();for(c=0;c<e;c+=1)this._change(null,c);this._animateOff=false;break}},_value:function(){var b=this.options.value;return b=this._trimAlignValue(b)},_values:function(b){var a,c;if(arguments.length){a=this.options.values[b];
return a=this._trimAlignValue(a)}else{a=this.options.values.slice();for(c=0;c<a.length;c+=1)a[c]=this._trimAlignValue(a[c]);return a}},_trimAlignValue:function(b){if(b<=this._valueMin())return this._valueMin();if(b>=this._valueMax())return this._valueMax();var a=this.options.step>0?this.options.step:1,c=(b-this._valueMin())%a;alignValue=b-c;if(Math.abs(c)*2>=a)alignValue+=c>0?a:-a;return parseFloat(alignValue.toFixed(5))},_valueMin:function(){return this.options.min},_valueMax:function(){return this.options.max},
_refreshValue:function(){var b=this.options.range,a=this.options,c=this,e=!this._animateOff?a.animate:false,f,h={},g,i,j,l;if(this.options.values&&this.options.values.length)this.handles.each(function(k){f=(c.values(k)-c._valueMin())/(c._valueMax()-c._valueMin())*100;h[c.orientation==="horizontal"?"left":"bottom"]=f+"%";d(this).stop(1,1)[e?"animate":"css"](h,a.animate);if(c.options.range===true)if(c.orientation==="horizontal"){if(k===0)c.range.stop(1,1)[e?"animate":"css"]({left:f+"%"},a.animate);
if(k===1)c.range[e?"animate":"css"]({width:f-g+"%"},{queue:false,duration:a.animate})}else{if(k===0)c.range.stop(1,1)[e?"animate":"css"]({bottom:f+"%"},a.animate);if(k===1)c.range[e?"animate":"css"]({height:f-g+"%"},{queue:false,duration:a.animate})}g=f});else{i=this.value();j=this._valueMin();l=this._valueMax();f=l!==j?(i-j)/(l-j)*100:0;h[c.orientation==="horizontal"?"left":"bottom"]=f+"%";this.handle.stop(1,1)[e?"animate":"css"](h,a.animate);if(b==="min"&&this.orientation==="horizontal")this.range.stop(1,
1)[e?"animate":"css"]({width:f+"%"},a.animate);if(b==="max"&&this.orientation==="horizontal")this.range[e?"animate":"css"]({width:100-f+"%"},{queue:false,duration:a.animate});if(b==="min"&&this.orientation==="vertical")this.range.stop(1,1)[e?"animate":"css"]({height:f+"%"},a.animate);if(b==="max"&&this.orientation==="vertical")this.range[e?"animate":"css"]({height:100-f+"%"},{queue:false,duration:a.animate})}}});d.extend(d.ui.slider,{version:"1.8.8"})})(jQuery);
;/*
 * jQuery UI Tabs 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Tabs
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 */
(function(d,p){function u(){return++v}function w(){return++x}var v=0,x=0;d.widget("ui.tabs",{options:{add:null,ajaxOptions:null,cache:false,cookie:null,collapsible:false,disable:null,disabled:[],enable:null,event:"click",fx:null,idPrefix:"ui-tabs-",load:null,panelTemplate:"<div></div>",remove:null,select:null,show:null,spinner:"<em>Loading&#8230;</em>",tabTemplate:"<li><a href='#{href}'><span>#{label}</span></a></li>"},_create:function(){this._tabify(true)},_setOption:function(b,e){if(b=="selected")this.options.collapsible&&
e==this.options.selected||this.select(e);else{this.options[b]=e;this._tabify()}},_tabId:function(b){return b.title&&b.title.replace(/\s/g,"_").replace(/[^\w\u00c0-\uFFFF-]/g,"")||this.options.idPrefix+u()},_sanitizeSelector:function(b){return b.replace(/:/g,"\\:")},_cookie:function(){var b=this.cookie||(this.cookie=this.options.cookie.name||"ui-tabs-"+w());return d.cookie.apply(null,[b].concat(d.makeArray(arguments)))},_ui:function(b,e){return{tab:b,panel:e,index:this.anchors.index(b)}},_cleanup:function(){this.lis.filter(".ui-state-processing").removeClass("ui-state-processing").find("span:data(label.tabs)").each(function(){var b=
d(this);b.html(b.data("label.tabs")).removeData("label.tabs")})},_tabify:function(b){function e(g,f){g.css("display","");!d.support.opacity&&f.opacity&&g[0].style.removeAttribute("filter")}var a=this,c=this.options,h=/^#.+/;this.list=this.element.find("ol,ul").eq(0);this.lis=d(" > li:has(a[href])",this.list);this.anchors=this.lis.map(function(){return d("a",this)[0]});this.panels=d([]);this.anchors.each(function(g,f){var i=d(f).attr("href"),l=i.split("#")[0],q;if(l&&(l===location.toString().split("#")[0]||
(q=d("base")[0])&&l===q.href)){i=f.hash;f.href=i}if(h.test(i))a.panels=a.panels.add(a.element.find(a._sanitizeSelector(i)));else if(i&&i!=="#"){d.data(f,"href.tabs",i);d.data(f,"load.tabs",i.replace(/#.*$/,""));i=a._tabId(f);f.href="#"+i;f=a.element.find("#"+i);if(!f.length){f=d(c.panelTemplate).attr("id",i).addClass("ui-tabs-panel ui-widget-content ui-corner-bottom").insertAfter(a.panels[g-1]||a.list);f.data("destroy.tabs",true)}a.panels=a.panels.add(f)}else c.disabled.push(g)});if(b){this.element.addClass("ui-tabs ui-widget ui-widget-content ui-corner-all");
this.list.addClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all");this.lis.addClass("ui-state-default ui-corner-top");this.panels.addClass("ui-tabs-panel ui-widget-content ui-corner-bottom");if(c.selected===p){location.hash&&this.anchors.each(function(g,f){if(f.hash==location.hash){c.selected=g;return false}});if(typeof c.selected!=="number"&&c.cookie)c.selected=parseInt(a._cookie(),10);if(typeof c.selected!=="number"&&this.lis.filter(".ui-tabs-selected").length)c.selected=
this.lis.index(this.lis.filter(".ui-tabs-selected"));c.selected=c.selected||(this.lis.length?0:-1)}else if(c.selected===null)c.selected=-1;c.selected=c.selected>=0&&this.anchors[c.selected]||c.selected<0?c.selected:0;c.disabled=d.unique(c.disabled.concat(d.map(this.lis.filter(".ui-state-disabled"),function(g){return a.lis.index(g)}))).sort();d.inArray(c.selected,c.disabled)!=-1&&c.disabled.splice(d.inArray(c.selected,c.disabled),1);this.panels.addClass("ui-tabs-hide");this.lis.removeClass("ui-tabs-selected ui-state-active");
if(c.selected>=0&&this.anchors.length){a.element.find(a._sanitizeSelector(a.anchors[c.selected].hash)).removeClass("ui-tabs-hide");this.lis.eq(c.selected).addClass("ui-tabs-selected ui-state-active");a.element.queue("tabs",function(){a._trigger("show",null,a._ui(a.anchors[c.selected],a.element.find(a._sanitizeSelector(a.anchors[c.selected].hash))))});this.load(c.selected)}d(window).bind("unload",function(){a.lis.add(a.anchors).unbind(".tabs");a.lis=a.anchors=a.panels=null})}else c.selected=this.lis.index(this.lis.filter(".ui-tabs-selected"));
this.element[c.collapsible?"addClass":"removeClass"]("ui-tabs-collapsible");c.cookie&&this._cookie(c.selected,c.cookie);b=0;for(var j;j=this.lis[b];b++)d(j)[d.inArray(b,c.disabled)!=-1&&!d(j).hasClass("ui-tabs-selected")?"addClass":"removeClass"]("ui-state-disabled");c.cache===false&&this.anchors.removeData("cache.tabs");this.lis.add(this.anchors).unbind(".tabs");if(c.event!=="mouseover"){var k=function(g,f){f.is(":not(.ui-state-disabled)")&&f.addClass("ui-state-"+g)},n=function(g,f){f.removeClass("ui-state-"+
g)};this.lis.bind("mouseover.tabs",function(){k("hover",d(this))});this.lis.bind("mouseout.tabs",function(){n("hover",d(this))});this.anchors.bind("focus.tabs",function(){k("focus",d(this).closest("li"))});this.anchors.bind("blur.tabs",function(){n("focus",d(this).closest("li"))})}var m,o;if(c.fx)if(d.isArray(c.fx)){m=c.fx[0];o=c.fx[1]}else m=o=c.fx;var r=o?function(g,f){d(g).closest("li").addClass("ui-tabs-selected ui-state-active");f.hide().removeClass("ui-tabs-hide").animate(o,o.duration||"normal",
function(){e(f,o);a._trigger("show",null,a._ui(g,f[0]))})}:function(g,f){d(g).closest("li").addClass("ui-tabs-selected ui-state-active");f.removeClass("ui-tabs-hide");a._trigger("show",null,a._ui(g,f[0]))},s=m?function(g,f){f.animate(m,m.duration||"normal",function(){a.lis.removeClass("ui-tabs-selected ui-state-active");f.addClass("ui-tabs-hide");e(f,m);a.element.dequeue("tabs")})}:function(g,f){a.lis.removeClass("ui-tabs-selected ui-state-active");f.addClass("ui-tabs-hide");a.element.dequeue("tabs")};
this.anchors.bind(c.event+".tabs",function(){var g=this,f=d(g).closest("li"),i=a.panels.filter(":not(.ui-tabs-hide)"),l=a.element.find(a._sanitizeSelector(g.hash));if(f.hasClass("ui-tabs-selected")&&!c.collapsible||f.hasClass("ui-state-disabled")||f.hasClass("ui-state-processing")||a.panels.filter(":animated").length||a._trigger("select",null,a._ui(this,l[0]))===false){this.blur();return false}c.selected=a.anchors.index(this);a.abort();if(c.collapsible)if(f.hasClass("ui-tabs-selected")){c.selected=
-1;c.cookie&&a._cookie(c.selected,c.cookie);a.element.queue("tabs",function(){s(g,i)}).dequeue("tabs");this.blur();return false}else if(!i.length){c.cookie&&a._cookie(c.selected,c.cookie);a.element.queue("tabs",function(){r(g,l)});a.load(a.anchors.index(this));this.blur();return false}c.cookie&&a._cookie(c.selected,c.cookie);if(l.length){i.length&&a.element.queue("tabs",function(){s(g,i)});a.element.queue("tabs",function(){r(g,l)});a.load(a.anchors.index(this))}else throw"jQuery UI Tabs: Mismatching fragment identifier.";
d.browser.msie&&this.blur()});this.anchors.bind("click.tabs",function(){return false})},_getIndex:function(b){if(typeof b=="string")b=this.anchors.index(this.anchors.filter("[href$="+b+"]"));return b},destroy:function(){var b=this.options;this.abort();this.element.unbind(".tabs").removeClass("ui-tabs ui-widget ui-widget-content ui-corner-all ui-tabs-collapsible").removeData("tabs");this.list.removeClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all");this.anchors.each(function(){var e=
d.data(this,"href.tabs");if(e)this.href=e;var a=d(this).unbind(".tabs");d.each(["href","load","cache"],function(c,h){a.removeData(h+".tabs")})});this.lis.unbind(".tabs").add(this.panels).each(function(){d.data(this,"destroy.tabs")?d(this).remove():d(this).removeClass("ui-state-default ui-corner-top ui-tabs-selected ui-state-active ui-state-hover ui-state-focus ui-state-disabled ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide")});b.cookie&&this._cookie(null,b.cookie);return this},add:function(b,
e,a){if(a===p)a=this.anchors.length;var c=this,h=this.options;e=d(h.tabTemplate.replace(/#\{href\}/g,b).replace(/#\{label\}/g,e));b=!b.indexOf("#")?b.replace("#",""):this._tabId(d("a",e)[0]);e.addClass("ui-state-default ui-corner-top").data("destroy.tabs",true);var j=c.element.find("#"+b);j.length||(j=d(h.panelTemplate).attr("id",b).data("destroy.tabs",true));j.addClass("ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide");if(a>=this.lis.length){e.appendTo(this.list);j.appendTo(this.list[0].parentNode)}else{e.insertBefore(this.lis[a]);
j.insertBefore(this.panels[a])}h.disabled=d.map(h.disabled,function(k){return k>=a?++k:k});this._tabify();if(this.anchors.length==1){h.selected=0;e.addClass("ui-tabs-selected ui-state-active");j.removeClass("ui-tabs-hide");this.element.queue("tabs",function(){c._trigger("show",null,c._ui(c.anchors[0],c.panels[0]))});this.load(0)}this._trigger("add",null,this._ui(this.anchors[a],this.panels[a]));return this},remove:function(b){b=this._getIndex(b);var e=this.options,a=this.lis.eq(b).remove(),c=this.panels.eq(b).remove();
if(a.hasClass("ui-tabs-selected")&&this.anchors.length>1)this.select(b+(b+1<this.anchors.length?1:-1));e.disabled=d.map(d.grep(e.disabled,function(h){return h!=b}),function(h){return h>=b?--h:h});this._tabify();this._trigger("remove",null,this._ui(a.find("a")[0],c[0]));return this},enable:function(b){b=this._getIndex(b);var e=this.options;if(d.inArray(b,e.disabled)!=-1){this.lis.eq(b).removeClass("ui-state-disabled");e.disabled=d.grep(e.disabled,function(a){return a!=b});this._trigger("enable",null,
this._ui(this.anchors[b],this.panels[b]));return this}},disable:function(b){b=this._getIndex(b);var e=this.options;if(b!=e.selected){this.lis.eq(b).addClass("ui-state-disabled");e.disabled.push(b);e.disabled.sort();this._trigger("disable",null,this._ui(this.anchors[b],this.panels[b]))}return this},select:function(b){b=this._getIndex(b);if(b==-1)if(this.options.collapsible&&this.options.selected!=-1)b=this.options.selected;else return this;this.anchors.eq(b).trigger(this.options.event+".tabs");return this},
load:function(b){b=this._getIndex(b);var e=this,a=this.options,c=this.anchors.eq(b)[0],h=d.data(c,"load.tabs");this.abort();if(!h||this.element.queue("tabs").length!==0&&d.data(c,"cache.tabs"))this.element.dequeue("tabs");else{this.lis.eq(b).addClass("ui-state-processing");if(a.spinner){var j=d("span",c);j.data("label.tabs",j.html()).html(a.spinner)}this.xhr=d.ajax(d.extend({},a.ajaxOptions,{url:h,success:function(k,n){e.element.find(e._sanitizeSelector(c.hash)).html(k);e._cleanup();a.cache&&d.data(c,
"cache.tabs",true);e._trigger("load",null,e._ui(e.anchors[b],e.panels[b]));try{a.ajaxOptions.success(k,n)}catch(m){}},error:function(k,n){e._cleanup();e._trigger("load",null,e._ui(e.anchors[b],e.panels[b]));try{a.ajaxOptions.error(k,n,b,c)}catch(m){}}}));e.element.dequeue("tabs");return this}},abort:function(){this.element.queue([]);this.panels.stop(false,true);this.element.queue("tabs",this.element.queue("tabs").splice(-2,2));if(this.xhr){this.xhr.abort();delete this.xhr}this._cleanup();return this},
url:function(b,e){this.anchors.eq(b).removeData("cache.tabs").data("load.tabs",e);return this},length:function(){return this.anchors.length}});d.extend(d.ui.tabs,{version:"1.8.8"});d.extend(d.ui.tabs.prototype,{rotation:null,rotate:function(b,e){var a=this,c=this.options,h=a._rotate||(a._rotate=function(j){clearTimeout(a.rotation);a.rotation=setTimeout(function(){var k=c.selected;a.select(++k<a.anchors.length?k:0)},b);j&&j.stopPropagation()});e=a._unrotate||(a._unrotate=!e?function(j){j.clientX&&
a.rotate(null)}:function(){t=c.selected;h()});if(b){this.element.bind("tabsshow",h);this.anchors.bind(c.event+".tabs",e);h()}else{clearTimeout(a.rotation);this.element.unbind("tabsshow",h);this.anchors.unbind(c.event+".tabs",e);delete this._rotate;delete this._unrotate}return this}})})(jQuery);
;/*
 * jQuery UI Datepicker 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Datepicker
 *
 * Depends:
 *	jquery.ui.core.js
 */
(function(d,G){function K(){this.debug=false;this._curInst=null;this._keyEvent=false;this._disabledInputs=[];this._inDialog=this._datepickerShowing=false;this._mainDivId="ui-datepicker-div";this._inlineClass="ui-datepicker-inline";this._appendClass="ui-datepicker-append";this._triggerClass="ui-datepicker-trigger";this._dialogClass="ui-datepicker-dialog";this._disableClass="ui-datepicker-disabled";this._unselectableClass="ui-datepicker-unselectable";this._currentClass="ui-datepicker-current-day";this._dayOverClass=
"ui-datepicker-days-cell-over";this.regional=[];this.regional[""]={closeText:"Done",prevText:"Prev",nextText:"Next",currentText:"Today",monthNames:["January","February","March","April","May","June","July","August","September","October","November","December"],monthNamesShort:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],dayNames:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],dayNamesShort:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],dayNamesMin:["Su",
"Mo","Tu","We","Th","Fr","Sa"],weekHeader:"Wk",dateFormat:"mm/dd/yy",firstDay:0,isRTL:false,showMonthAfterYear:false,yearSuffix:""};this._defaults={showOn:"focus",showAnim:"fadeIn",showOptions:{},defaultDate:null,appendText:"",buttonText:"...",buttonImage:"",buttonImageOnly:false,hideIfNoPrevNext:false,navigationAsDateFormat:false,gotoCurrent:false,changeMonth:false,changeYear:false,yearRange:"c-10:c+10",showOtherMonths:false,selectOtherMonths:false,showWeek:false,calculateWeek:this.iso8601Week,shortYearCutoff:"+10",
minDate:null,maxDate:null,duration:"fast",beforeShowDay:null,beforeShow:null,onSelect:null,onChangeMonthYear:null,onClose:null,numberOfMonths:1,showCurrentAtPos:0,stepMonths:1,stepBigMonths:12,altField:"",altFormat:"",constrainInput:true,showButtonPanel:false,autoSize:false};d.extend(this._defaults,this.regional[""]);this.dpDiv=d('<div id="'+this._mainDivId+'" class="ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div>')}function E(a,b){d.extend(a,b);for(var c in b)if(b[c]==
null||b[c]==G)a[c]=b[c];return a}d.extend(d.ui,{datepicker:{version:"1.8.8"}});var y=(new Date).getTime();d.extend(K.prototype,{markerClassName:"hasDatepicker",log:function(){this.debug&&console.log.apply("",arguments)},_widgetDatepicker:function(){return this.dpDiv},setDefaults:function(a){E(this._defaults,a||{});return this},_attachDatepicker:function(a,b){var c=null;for(var e in this._defaults){var f=a.getAttribute("date:"+e);if(f){c=c||{};try{c[e]=eval(f)}catch(h){c[e]=f}}}e=a.nodeName.toLowerCase();
f=e=="div"||e=="span";if(!a.id){this.uuid+=1;a.id="dp"+this.uuid}var i=this._newInst(d(a),f);i.settings=d.extend({},b||{},c||{});if(e=="input")this._connectDatepicker(a,i);else f&&this._inlineDatepicker(a,i)},_newInst:function(a,b){return{id:a[0].id.replace(/([^A-Za-z0-9_-])/g,"\\\\$1"),input:a,selectedDay:0,selectedMonth:0,selectedYear:0,drawMonth:0,drawYear:0,inline:b,dpDiv:!b?this.dpDiv:d('<div class="'+this._inlineClass+' ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div>')}},
_connectDatepicker:function(a,b){var c=d(a);b.append=d([]);b.trigger=d([]);if(!c.hasClass(this.markerClassName)){this._attachments(c,b);c.addClass(this.markerClassName).keydown(this._doKeyDown).keypress(this._doKeyPress).keyup(this._doKeyUp).bind("setData.datepicker",function(e,f,h){b.settings[f]=h}).bind("getData.datepicker",function(e,f){return this._get(b,f)});this._autoSize(b);d.data(a,"datepicker",b)}},_attachments:function(a,b){var c=this._get(b,"appendText"),e=this._get(b,"isRTL");b.append&&
b.append.remove();if(c){b.append=d('<span class="'+this._appendClass+'">'+c+"</span>");a[e?"before":"after"](b.append)}a.unbind("focus",this._showDatepicker);b.trigger&&b.trigger.remove();c=this._get(b,"showOn");if(c=="focus"||c=="both")a.focus(this._showDatepicker);if(c=="button"||c=="both"){c=this._get(b,"buttonText");var f=this._get(b,"buttonImage");b.trigger=d(this._get(b,"buttonImageOnly")?d("<img/>").addClass(this._triggerClass).attr({src:f,alt:c,title:c}):d('<button type="button"></button>').addClass(this._triggerClass).html(f==
""?c:d("<img/>").attr({src:f,alt:c,title:c})));a[e?"before":"after"](b.trigger);b.trigger.click(function(){d.datepicker._datepickerShowing&&d.datepicker._lastInput==a[0]?d.datepicker._hideDatepicker():d.datepicker._showDatepicker(a[0]);return false})}},_autoSize:function(a){if(this._get(a,"autoSize")&&!a.inline){var b=new Date(2009,11,20),c=this._get(a,"dateFormat");if(c.match(/[DM]/)){var e=function(f){for(var h=0,i=0,g=0;g<f.length;g++)if(f[g].length>h){h=f[g].length;i=g}return i};b.setMonth(e(this._get(a,
c.match(/MM/)?"monthNames":"monthNamesShort")));b.setDate(e(this._get(a,c.match(/DD/)?"dayNames":"dayNamesShort"))+20-b.getDay())}a.input.attr("size",this._formatDate(a,b).length)}},_inlineDatepicker:function(a,b){var c=d(a);if(!c.hasClass(this.markerClassName)){c.addClass(this.markerClassName).append(b.dpDiv).bind("setData.datepicker",function(e,f,h){b.settings[f]=h}).bind("getData.datepicker",function(e,f){return this._get(b,f)});d.data(a,"datepicker",b);this._setDate(b,this._getDefaultDate(b),
true);this._updateDatepicker(b);this._updateAlternate(b);b.dpDiv.show()}},_dialogDatepicker:function(a,b,c,e,f){a=this._dialogInst;if(!a){this.uuid+=1;this._dialogInput=d('<input type="text" id="'+("dp"+this.uuid)+'" style="position: absolute; top: -100px; width: 0px; z-index: -10;"/>');this._dialogInput.keydown(this._doKeyDown);d("body").append(this._dialogInput);a=this._dialogInst=this._newInst(this._dialogInput,false);a.settings={};d.data(this._dialogInput[0],"datepicker",a)}E(a.settings,e||{});
b=b&&b.constructor==Date?this._formatDate(a,b):b;this._dialogInput.val(b);this._pos=f?f.length?f:[f.pageX,f.pageY]:null;if(!this._pos)this._pos=[document.documentElement.clientWidth/2-100+(document.documentElement.scrollLeft||document.body.scrollLeft),document.documentElement.clientHeight/2-150+(document.documentElement.scrollTop||document.body.scrollTop)];this._dialogInput.css("left",this._pos[0]+20+"px").css("top",this._pos[1]+"px");a.settings.onSelect=c;this._inDialog=true;this.dpDiv.addClass(this._dialogClass);
this._showDatepicker(this._dialogInput[0]);d.blockUI&&d.blockUI(this.dpDiv);d.data(this._dialogInput[0],"datepicker",a);return this},_destroyDatepicker:function(a){var b=d(a),c=d.data(a,"datepicker");if(b.hasClass(this.markerClassName)){var e=a.nodeName.toLowerCase();d.removeData(a,"datepicker");if(e=="input"){c.append.remove();c.trigger.remove();b.removeClass(this.markerClassName).unbind("focus",this._showDatepicker).unbind("keydown",this._doKeyDown).unbind("keypress",this._doKeyPress).unbind("keyup",
this._doKeyUp)}else if(e=="div"||e=="span")b.removeClass(this.markerClassName).empty()}},_enableDatepicker:function(a){var b=d(a),c=d.data(a,"datepicker");if(b.hasClass(this.markerClassName)){var e=a.nodeName.toLowerCase();if(e=="input"){a.disabled=false;c.trigger.filter("button").each(function(){this.disabled=false}).end().filter("img").css({opacity:"1.0",cursor:""})}else if(e=="div"||e=="span")b.children("."+this._inlineClass).children().removeClass("ui-state-disabled");this._disabledInputs=d.map(this._disabledInputs,
function(f){return f==a?null:f})}},_disableDatepicker:function(a){var b=d(a),c=d.data(a,"datepicker");if(b.hasClass(this.markerClassName)){var e=a.nodeName.toLowerCase();if(e=="input"){a.disabled=true;c.trigger.filter("button").each(function(){this.disabled=true}).end().filter("img").css({opacity:"0.5",cursor:"default"})}else if(e=="div"||e=="span")b.children("."+this._inlineClass).children().addClass("ui-state-disabled");this._disabledInputs=d.map(this._disabledInputs,function(f){return f==a?null:
f});this._disabledInputs[this._disabledInputs.length]=a}},_isDisabledDatepicker:function(a){if(!a)return false;for(var b=0;b<this._disabledInputs.length;b++)if(this._disabledInputs[b]==a)return true;return false},_getInst:function(a){try{return d.data(a,"datepicker")}catch(b){throw"Missing instance data for this datepicker";}},_optionDatepicker:function(a,b,c){var e=this._getInst(a);if(arguments.length==2&&typeof b=="string")return b=="defaults"?d.extend({},d.datepicker._defaults):e?b=="all"?d.extend({},
e.settings):this._get(e,b):null;var f=b||{};if(typeof b=="string"){f={};f[b]=c}if(e){this._curInst==e&&this._hideDatepicker();var h=this._getDateDatepicker(a,true);E(e.settings,f);this._attachments(d(a),e);this._autoSize(e);this._setDateDatepicker(a,h);this._updateDatepicker(e)}},_changeDatepicker:function(a,b,c){this._optionDatepicker(a,b,c)},_refreshDatepicker:function(a){(a=this._getInst(a))&&this._updateDatepicker(a)},_setDateDatepicker:function(a,b){if(a=this._getInst(a)){this._setDate(a,b);
this._updateDatepicker(a);this._updateAlternate(a)}},_getDateDatepicker:function(a,b){(a=this._getInst(a))&&!a.inline&&this._setDateFromField(a,b);return a?this._getDate(a):null},_doKeyDown:function(a){var b=d.datepicker._getInst(a.target),c=true,e=b.dpDiv.is(".ui-datepicker-rtl");b._keyEvent=true;if(d.datepicker._datepickerShowing)switch(a.keyCode){case 9:d.datepicker._hideDatepicker();c=false;break;case 13:c=d("td."+d.datepicker._dayOverClass+":not(."+d.datepicker._currentClass+")",b.dpDiv);c[0]?
d.datepicker._selectDay(a.target,b.selectedMonth,b.selectedYear,c[0]):d.datepicker._hideDatepicker();return false;case 27:d.datepicker._hideDatepicker();break;case 33:d.datepicker._adjustDate(a.target,a.ctrlKey?-d.datepicker._get(b,"stepBigMonths"):-d.datepicker._get(b,"stepMonths"),"M");break;case 34:d.datepicker._adjustDate(a.target,a.ctrlKey?+d.datepicker._get(b,"stepBigMonths"):+d.datepicker._get(b,"stepMonths"),"M");break;case 35:if(a.ctrlKey||a.metaKey)d.datepicker._clearDate(a.target);c=a.ctrlKey||
a.metaKey;break;case 36:if(a.ctrlKey||a.metaKey)d.datepicker._gotoToday(a.target);c=a.ctrlKey||a.metaKey;break;case 37:if(a.ctrlKey||a.metaKey)d.datepicker._adjustDate(a.target,e?+1:-1,"D");c=a.ctrlKey||a.metaKey;if(a.originalEvent.altKey)d.datepicker._adjustDate(a.target,a.ctrlKey?-d.datepicker._get(b,"stepBigMonths"):-d.datepicker._get(b,"stepMonths"),"M");break;case 38:if(a.ctrlKey||a.metaKey)d.datepicker._adjustDate(a.target,-7,"D");c=a.ctrlKey||a.metaKey;break;case 39:if(a.ctrlKey||a.metaKey)d.datepicker._adjustDate(a.target,
e?-1:+1,"D");c=a.ctrlKey||a.metaKey;if(a.originalEvent.altKey)d.datepicker._adjustDate(a.target,a.ctrlKey?+d.datepicker._get(b,"stepBigMonths"):+d.datepicker._get(b,"stepMonths"),"M");break;case 40:if(a.ctrlKey||a.metaKey)d.datepicker._adjustDate(a.target,+7,"D");c=a.ctrlKey||a.metaKey;break;default:c=false}else if(a.keyCode==36&&a.ctrlKey)d.datepicker._showDatepicker(this);else c=false;if(c){a.preventDefault();a.stopPropagation()}},_doKeyPress:function(a){var b=d.datepicker._getInst(a.target);if(d.datepicker._get(b,
"constrainInput")){b=d.datepicker._possibleChars(d.datepicker._get(b,"dateFormat"));var c=String.fromCharCode(a.charCode==G?a.keyCode:a.charCode);return a.ctrlKey||a.metaKey||c<" "||!b||b.indexOf(c)>-1}},_doKeyUp:function(a){a=d.datepicker._getInst(a.target);if(a.input.val()!=a.lastVal)try{if(d.datepicker.parseDate(d.datepicker._get(a,"dateFormat"),a.input?a.input.val():null,d.datepicker._getFormatConfig(a))){d.datepicker._setDateFromField(a);d.datepicker._updateAlternate(a);d.datepicker._updateDatepicker(a)}}catch(b){d.datepicker.log(b)}return true},
_showDatepicker:function(a){a=a.target||a;if(a.nodeName.toLowerCase()!="input")a=d("input",a.parentNode)[0];if(!(d.datepicker._isDisabledDatepicker(a)||d.datepicker._lastInput==a)){var b=d.datepicker._getInst(a);d.datepicker._curInst&&d.datepicker._curInst!=b&&d.datepicker._curInst.dpDiv.stop(true,true);var c=d.datepicker._get(b,"beforeShow");E(b.settings,c?c.apply(a,[a,b]):{});b.lastVal=null;d.datepicker._lastInput=a;d.datepicker._setDateFromField(b);if(d.datepicker._inDialog)a.value="";if(!d.datepicker._pos){d.datepicker._pos=
d.datepicker._findPos(a);d.datepicker._pos[1]+=a.offsetHeight}var e=false;d(a).parents().each(function(){e|=d(this).css("position")=="fixed";return!e});if(e&&d.browser.opera){d.datepicker._pos[0]-=document.documentElement.scrollLeft;d.datepicker._pos[1]-=document.documentElement.scrollTop}c={left:d.datepicker._pos[0],top:d.datepicker._pos[1]};d.datepicker._pos=null;b.dpDiv.empty();b.dpDiv.css({position:"absolute",display:"block",top:"-1000px"});d.datepicker._updateDatepicker(b);c=d.datepicker._checkOffset(b,
c,e);b.dpDiv.css({position:d.datepicker._inDialog&&d.blockUI?"static":e?"fixed":"absolute",display:"none",left:c.left+"px",top:c.top+"px"});if(!b.inline){c=d.datepicker._get(b,"showAnim");var f=d.datepicker._get(b,"duration"),h=function(){d.datepicker._datepickerShowing=true;var i=b.dpDiv.find("iframe.ui-datepicker-cover");if(i.length){var g=d.datepicker._getBorders(b.dpDiv);i.css({left:-g[0],top:-g[1],width:b.dpDiv.outerWidth(),height:b.dpDiv.outerHeight()})}};b.dpDiv.zIndex(d(a).zIndex()+1);d.effects&&
d.effects[c]?b.dpDiv.show(c,d.datepicker._get(b,"showOptions"),f,h):b.dpDiv[c||"show"](c?f:null,h);if(!c||!f)h();b.input.is(":visible")&&!b.input.is(":disabled")&&b.input.focus();d.datepicker._curInst=b}}},_updateDatepicker:function(a){var b=this,c=d.datepicker._getBorders(a.dpDiv);a.dpDiv.empty().append(this._generateHTML(a));var e=a.dpDiv.find("iframe.ui-datepicker-cover");e.length&&e.css({left:-c[0],top:-c[1],width:a.dpDiv.outerWidth(),height:a.dpDiv.outerHeight()});a.dpDiv.find("button, .ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-calendar td a").bind("mouseout",
function(){d(this).removeClass("ui-state-hover");this.className.indexOf("ui-datepicker-prev")!=-1&&d(this).removeClass("ui-datepicker-prev-hover");this.className.indexOf("ui-datepicker-next")!=-1&&d(this).removeClass("ui-datepicker-next-hover")}).bind("mouseover",function(){if(!b._isDisabledDatepicker(a.inline?a.dpDiv.parent()[0]:a.input[0])){d(this).parents(".ui-datepicker-calendar").find("a").removeClass("ui-state-hover");d(this).addClass("ui-state-hover");this.className.indexOf("ui-datepicker-prev")!=
-1&&d(this).addClass("ui-datepicker-prev-hover");this.className.indexOf("ui-datepicker-next")!=-1&&d(this).addClass("ui-datepicker-next-hover")}}).end().find("."+this._dayOverClass+" a").trigger("mouseover").end();c=this._getNumberOfMonths(a);e=c[1];e>1?a.dpDiv.addClass("ui-datepicker-multi-"+e).css("width",17*e+"em"):a.dpDiv.removeClass("ui-datepicker-multi-2 ui-datepicker-multi-3 ui-datepicker-multi-4").width("");a.dpDiv[(c[0]!=1||c[1]!=1?"add":"remove")+"Class"]("ui-datepicker-multi");a.dpDiv[(this._get(a,
"isRTL")?"add":"remove")+"Class"]("ui-datepicker-rtl");a==d.datepicker._curInst&&d.datepicker._datepickerShowing&&a.input&&a.input.is(":visible")&&!a.input.is(":disabled")&&a.input.focus();if(a.yearshtml){var f=a.yearshtml;setTimeout(function(){f===a.yearshtml&&a.dpDiv.find("select.ui-datepicker-year:first").replaceWith(a.yearshtml);f=a.yearshtml=null},0)}},_getBorders:function(a){var b=function(c){return{thin:1,medium:2,thick:3}[c]||c};return[parseFloat(b(a.css("border-left-width"))),parseFloat(b(a.css("border-top-width")))]},
_checkOffset:function(a,b,c){var e=a.dpDiv.outerWidth(),f=a.dpDiv.outerHeight(),h=a.input?a.input.outerWidth():0,i=a.input?a.input.outerHeight():0,g=document.documentElement.clientWidth+d(document).scrollLeft(),j=document.documentElement.clientHeight+d(document).scrollTop();b.left-=this._get(a,"isRTL")?e-h:0;b.left-=c&&b.left==a.input.offset().left?d(document).scrollLeft():0;b.top-=c&&b.top==a.input.offset().top+i?d(document).scrollTop():0;b.left-=Math.min(b.left,b.left+e>g&&g>e?Math.abs(b.left+e-
g):0);b.top-=Math.min(b.top,b.top+f>j&&j>f?Math.abs(f+i):0);return b},_findPos:function(a){for(var b=this._get(this._getInst(a),"isRTL");a&&(a.type=="hidden"||a.nodeType!=1);)a=a[b?"previousSibling":"nextSibling"];a=d(a).offset();return[a.left,a.top]},_hideDatepicker:function(a){var b=this._curInst;if(!(!b||a&&b!=d.data(a,"datepicker")))if(this._datepickerShowing){a=this._get(b,"showAnim");var c=this._get(b,"duration"),e=function(){d.datepicker._tidyDialog(b);this._curInst=null};d.effects&&d.effects[a]?
b.dpDiv.hide(a,d.datepicker._get(b,"showOptions"),c,e):b.dpDiv[a=="slideDown"?"slideUp":a=="fadeIn"?"fadeOut":"hide"](a?c:null,e);a||e();if(a=this._get(b,"onClose"))a.apply(b.input?b.input[0]:null,[b.input?b.input.val():"",b]);this._datepickerShowing=false;this._lastInput=null;if(this._inDialog){this._dialogInput.css({position:"absolute",left:"0",top:"-100px"});if(d.blockUI){d.unblockUI();d("body").append(this.dpDiv)}}this._inDialog=false}},_tidyDialog:function(a){a.dpDiv.removeClass(this._dialogClass).unbind(".ui-datepicker-calendar")},
_checkExternalClick:function(a){if(d.datepicker._curInst){a=d(a.target);a[0].id!=d.datepicker._mainDivId&&a.parents("#"+d.datepicker._mainDivId).length==0&&!a.hasClass(d.datepicker.markerClassName)&&!a.hasClass(d.datepicker._triggerClass)&&d.datepicker._datepickerShowing&&!(d.datepicker._inDialog&&d.blockUI)&&d.datepicker._hideDatepicker()}},_adjustDate:function(a,b,c){a=d(a);var e=this._getInst(a[0]);if(!this._isDisabledDatepicker(a[0])){this._adjustInstDate(e,b+(c=="M"?this._get(e,"showCurrentAtPos"):
0),c);this._updateDatepicker(e)}},_gotoToday:function(a){a=d(a);var b=this._getInst(a[0]);if(this._get(b,"gotoCurrent")&&b.currentDay){b.selectedDay=b.currentDay;b.drawMonth=b.selectedMonth=b.currentMonth;b.drawYear=b.selectedYear=b.currentYear}else{var c=new Date;b.selectedDay=c.getDate();b.drawMonth=b.selectedMonth=c.getMonth();b.drawYear=b.selectedYear=c.getFullYear()}this._notifyChange(b);this._adjustDate(a)},_selectMonthYear:function(a,b,c){a=d(a);var e=this._getInst(a[0]);e._selectingMonthYear=
false;e["selected"+(c=="M"?"Month":"Year")]=e["draw"+(c=="M"?"Month":"Year")]=parseInt(b.options[b.selectedIndex].value,10);this._notifyChange(e);this._adjustDate(a)},_clickMonthYear:function(a){var b=this._getInst(d(a)[0]);b.input&&b._selectingMonthYear&&setTimeout(function(){b.input.focus()},0);b._selectingMonthYear=!b._selectingMonthYear},_selectDay:function(a,b,c,e){var f=d(a);if(!(d(e).hasClass(this._unselectableClass)||this._isDisabledDatepicker(f[0]))){f=this._getInst(f[0]);f.selectedDay=f.currentDay=
d("a",e).html();f.selectedMonth=f.currentMonth=b;f.selectedYear=f.currentYear=c;this._selectDate(a,this._formatDate(f,f.currentDay,f.currentMonth,f.currentYear))}},_clearDate:function(a){a=d(a);this._getInst(a[0]);this._selectDate(a,"")},_selectDate:function(a,b){a=this._getInst(d(a)[0]);b=b!=null?b:this._formatDate(a);a.input&&a.input.val(b);this._updateAlternate(a);var c=this._get(a,"onSelect");if(c)c.apply(a.input?a.input[0]:null,[b,a]);else a.input&&a.input.trigger("change");if(a.inline)this._updateDatepicker(a);
else{this._hideDatepicker();this._lastInput=a.input[0];typeof a.input[0]!="object"&&a.input.focus();this._lastInput=null}},_updateAlternate:function(a){var b=this._get(a,"altField");if(b){var c=this._get(a,"altFormat")||this._get(a,"dateFormat"),e=this._getDate(a),f=this.formatDate(c,e,this._getFormatConfig(a));d(b).each(function(){d(this).val(f)})}},noWeekends:function(a){a=a.getDay();return[a>0&&a<6,""]},iso8601Week:function(a){a=new Date(a.getTime());a.setDate(a.getDate()+4-(a.getDay()||7));var b=
a.getTime();a.setMonth(0);a.setDate(1);return Math.floor(Math.round((b-a)/864E5)/7)+1},parseDate:function(a,b,c){if(a==null||b==null)throw"Invalid arguments";b=typeof b=="object"?b.toString():b+"";if(b=="")return null;for(var e=(c?c.shortYearCutoff:null)||this._defaults.shortYearCutoff,f=(c?c.dayNamesShort:null)||this._defaults.dayNamesShort,h=(c?c.dayNames:null)||this._defaults.dayNames,i=(c?c.monthNamesShort:null)||this._defaults.monthNamesShort,g=(c?c.monthNames:null)||this._defaults.monthNames,
j=c=-1,l=-1,u=-1,k=false,o=function(p){(p=z+1<a.length&&a.charAt(z+1)==p)&&z++;return p},m=function(p){var v=o(p);p=new RegExp("^\\d{1,"+(p=="@"?14:p=="!"?20:p=="y"&&v?4:p=="o"?3:2)+"}");p=b.substring(s).match(p);if(!p)throw"Missing number at position "+s;s+=p[0].length;return parseInt(p[0],10)},n=function(p,v,H){p=o(p)?H:v;for(v=0;v<p.length;v++)if(b.substr(s,p[v].length).toLowerCase()==p[v].toLowerCase()){s+=p[v].length;return v+1}throw"Unknown name at position "+s;},r=function(){if(b.charAt(s)!=
a.charAt(z))throw"Unexpected literal at position "+s;s++},s=0,z=0;z<a.length;z++)if(k)if(a.charAt(z)=="'"&&!o("'"))k=false;else r();else switch(a.charAt(z)){case "d":l=m("d");break;case "D":n("D",f,h);break;case "o":u=m("o");break;case "m":j=m("m");break;case "M":j=n("M",i,g);break;case "y":c=m("y");break;case "@":var w=new Date(m("@"));c=w.getFullYear();j=w.getMonth()+1;l=w.getDate();break;case "!":w=new Date((m("!")-this._ticksTo1970)/1E4);c=w.getFullYear();j=w.getMonth()+1;l=w.getDate();break;
case "'":if(o("'"))r();else k=true;break;default:r()}if(c==-1)c=(new Date).getFullYear();else if(c<100)c+=(new Date).getFullYear()-(new Date).getFullYear()%100+(c<=e?0:-100);if(u>-1){j=1;l=u;do{e=this._getDaysInMonth(c,j-1);if(l<=e)break;j++;l-=e}while(1)}w=this._daylightSavingAdjust(new Date(c,j-1,l));if(w.getFullYear()!=c||w.getMonth()+1!=j||w.getDate()!=l)throw"Invalid date";return w},ATOM:"yy-mm-dd",COOKIE:"D, dd M yy",ISO_8601:"yy-mm-dd",RFC_822:"D, d M y",RFC_850:"DD, dd-M-y",RFC_1036:"D, d M y",
RFC_1123:"D, d M yy",RFC_2822:"D, d M yy",RSS:"D, d M y",TICKS:"!",TIMESTAMP:"@",W3C:"yy-mm-dd",_ticksTo1970:(718685+Math.floor(492.5)-Math.floor(19.7)+Math.floor(4.925))*24*60*60*1E7,formatDate:function(a,b,c){if(!b)return"";var e=(c?c.dayNamesShort:null)||this._defaults.dayNamesShort,f=(c?c.dayNames:null)||this._defaults.dayNames,h=(c?c.monthNamesShort:null)||this._defaults.monthNamesShort;c=(c?c.monthNames:null)||this._defaults.monthNames;var i=function(o){(o=k+1<a.length&&a.charAt(k+1)==o)&&k++;
return o},g=function(o,m,n){m=""+m;if(i(o))for(;m.length<n;)m="0"+m;return m},j=function(o,m,n,r){return i(o)?r[m]:n[m]},l="",u=false;if(b)for(var k=0;k<a.length;k++)if(u)if(a.charAt(k)=="'"&&!i("'"))u=false;else l+=a.charAt(k);else switch(a.charAt(k)){case "d":l+=g("d",b.getDate(),2);break;case "D":l+=j("D",b.getDay(),e,f);break;case "o":l+=g("o",(b.getTime()-(new Date(b.getFullYear(),0,0)).getTime())/864E5,3);break;case "m":l+=g("m",b.getMonth()+1,2);break;case "M":l+=j("M",b.getMonth(),h,c);break;
case "y":l+=i("y")?b.getFullYear():(b.getYear()%100<10?"0":"")+b.getYear()%100;break;case "@":l+=b.getTime();break;case "!":l+=b.getTime()*1E4+this._ticksTo1970;break;case "'":if(i("'"))l+="'";else u=true;break;default:l+=a.charAt(k)}return l},_possibleChars:function(a){for(var b="",c=false,e=function(h){(h=f+1<a.length&&a.charAt(f+1)==h)&&f++;return h},f=0;f<a.length;f++)if(c)if(a.charAt(f)=="'"&&!e("'"))c=false;else b+=a.charAt(f);else switch(a.charAt(f)){case "d":case "m":case "y":case "@":b+=
"0123456789";break;case "D":case "M":return null;case "'":if(e("'"))b+="'";else c=true;break;default:b+=a.charAt(f)}return b},_get:function(a,b){return a.settings[b]!==G?a.settings[b]:this._defaults[b]},_setDateFromField:function(a,b){if(a.input.val()!=a.lastVal){var c=this._get(a,"dateFormat"),e=a.lastVal=a.input?a.input.val():null,f,h;f=h=this._getDefaultDate(a);var i=this._getFormatConfig(a);try{f=this.parseDate(c,e,i)||h}catch(g){this.log(g);e=b?"":e}a.selectedDay=f.getDate();a.drawMonth=a.selectedMonth=
f.getMonth();a.drawYear=a.selectedYear=f.getFullYear();a.currentDay=e?f.getDate():0;a.currentMonth=e?f.getMonth():0;a.currentYear=e?f.getFullYear():0;this._adjustInstDate(a)}},_getDefaultDate:function(a){return this._restrictMinMax(a,this._determineDate(a,this._get(a,"defaultDate"),new Date))},_determineDate:function(a,b,c){var e=function(h){var i=new Date;i.setDate(i.getDate()+h);return i},f=function(h){try{return d.datepicker.parseDate(d.datepicker._get(a,"dateFormat"),h,d.datepicker._getFormatConfig(a))}catch(i){}var g=
(h.toLowerCase().match(/^c/)?d.datepicker._getDate(a):null)||new Date,j=g.getFullYear(),l=g.getMonth();g=g.getDate();for(var u=/([+-]?[0-9]+)\s*(d|D|w|W|m|M|y|Y)?/g,k=u.exec(h);k;){switch(k[2]||"d"){case "d":case "D":g+=parseInt(k[1],10);break;case "w":case "W":g+=parseInt(k[1],10)*7;break;case "m":case "M":l+=parseInt(k[1],10);g=Math.min(g,d.datepicker._getDaysInMonth(j,l));break;case "y":case "Y":j+=parseInt(k[1],10);g=Math.min(g,d.datepicker._getDaysInMonth(j,l));break}k=u.exec(h)}return new Date(j,
l,g)};if(b=(b=b==null||b===""?c:typeof b=="string"?f(b):typeof b=="number"?isNaN(b)?c:e(b):new Date(b.getTime()))&&b.toString()=="Invalid Date"?c:b){b.setHours(0);b.setMinutes(0);b.setSeconds(0);b.setMilliseconds(0)}return this._daylightSavingAdjust(b)},_daylightSavingAdjust:function(a){if(!a)return null;a.setHours(a.getHours()>12?a.getHours()+2:0);return a},_setDate:function(a,b,c){var e=!b,f=a.selectedMonth,h=a.selectedYear;b=this._restrictMinMax(a,this._determineDate(a,b,new Date));a.selectedDay=
a.currentDay=b.getDate();a.drawMonth=a.selectedMonth=a.currentMonth=b.getMonth();a.drawYear=a.selectedYear=a.currentYear=b.getFullYear();if((f!=a.selectedMonth||h!=a.selectedYear)&&!c)this._notifyChange(a);this._adjustInstDate(a);if(a.input)a.input.val(e?"":this._formatDate(a))},_getDate:function(a){return!a.currentYear||a.input&&a.input.val()==""?null:this._daylightSavingAdjust(new Date(a.currentYear,a.currentMonth,a.currentDay))},_generateHTML:function(a){var b=new Date;b=this._daylightSavingAdjust(new Date(b.getFullYear(),
b.getMonth(),b.getDate()));var c=this._get(a,"isRTL"),e=this._get(a,"showButtonPanel"),f=this._get(a,"hideIfNoPrevNext"),h=this._get(a,"navigationAsDateFormat"),i=this._getNumberOfMonths(a),g=this._get(a,"showCurrentAtPos"),j=this._get(a,"stepMonths"),l=i[0]!=1||i[1]!=1,u=this._daylightSavingAdjust(!a.currentDay?new Date(9999,9,9):new Date(a.currentYear,a.currentMonth,a.currentDay)),k=this._getMinMaxDate(a,"min"),o=this._getMinMaxDate(a,"max");g=a.drawMonth-g;var m=a.drawYear;if(g<0){g+=12;m--}if(o){var n=
this._daylightSavingAdjust(new Date(o.getFullYear(),o.getMonth()-i[0]*i[1]+1,o.getDate()));for(n=k&&n<k?k:n;this._daylightSavingAdjust(new Date(m,g,1))>n;){g--;if(g<0){g=11;m--}}}a.drawMonth=g;a.drawYear=m;n=this._get(a,"prevText");n=!h?n:this.formatDate(n,this._daylightSavingAdjust(new Date(m,g-j,1)),this._getFormatConfig(a));n=this._canAdjustMonth(a,-1,m,g)?'<a class="ui-datepicker-prev ui-corner-all" onclick="DP_jQuery_'+y+".datepicker._adjustDate('#"+a.id+"', -"+j+", 'M');\" title=\""+n+'"><span class="ui-icon ui-icon-circle-triangle-'+
(c?"e":"w")+'">'+n+"</span></a>":f?"":'<a class="ui-datepicker-prev ui-corner-all ui-state-disabled" title="'+n+'"><span class="ui-icon ui-icon-circle-triangle-'+(c?"e":"w")+'">'+n+"</span></a>";var r=this._get(a,"nextText");r=!h?r:this.formatDate(r,this._daylightSavingAdjust(new Date(m,g+j,1)),this._getFormatConfig(a));f=this._canAdjustMonth(a,+1,m,g)?'<a class="ui-datepicker-next ui-corner-all" onclick="DP_jQuery_'+y+".datepicker._adjustDate('#"+a.id+"', +"+j+", 'M');\" title=\""+r+'"><span class="ui-icon ui-icon-circle-triangle-'+
(c?"w":"e")+'">'+r+"</span></a>":f?"":'<a class="ui-datepicker-next ui-corner-all ui-state-disabled" title="'+r+'"><span class="ui-icon ui-icon-circle-triangle-'+(c?"w":"e")+'">'+r+"</span></a>";j=this._get(a,"currentText");r=this._get(a,"gotoCurrent")&&a.currentDay?u:b;j=!h?j:this.formatDate(j,r,this._getFormatConfig(a));h=!a.inline?'<button type="button" class="ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all" onclick="DP_jQuery_'+y+'.datepicker._hideDatepicker();">'+this._get(a,
"closeText")+"</button>":"";e=e?'<div class="ui-datepicker-buttonpane ui-widget-content">'+(c?h:"")+(this._isInRange(a,r)?'<button type="button" class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" onclick="DP_jQuery_'+y+".datepicker._gotoToday('#"+a.id+"');\">"+j+"</button>":"")+(c?"":h)+"</div>":"";h=parseInt(this._get(a,"firstDay"),10);h=isNaN(h)?0:h;j=this._get(a,"showWeek");r=this._get(a,"dayNames");this._get(a,"dayNamesShort");var s=this._get(a,"dayNamesMin"),z=
this._get(a,"monthNames"),w=this._get(a,"monthNamesShort"),p=this._get(a,"beforeShowDay"),v=this._get(a,"showOtherMonths"),H=this._get(a,"selectOtherMonths");this._get(a,"calculateWeek");for(var L=this._getDefaultDate(a),I="",C=0;C<i[0];C++){for(var M="",D=0;D<i[1];D++){var N=this._daylightSavingAdjust(new Date(m,g,a.selectedDay)),t=" ui-corner-all",x="";if(l){x+='<div class="ui-datepicker-group';if(i[1]>1)switch(D){case 0:x+=" ui-datepicker-group-first";t=" ui-corner-"+(c?"right":"left");break;case i[1]-
1:x+=" ui-datepicker-group-last";t=" ui-corner-"+(c?"left":"right");break;default:x+=" ui-datepicker-group-middle";t="";break}x+='">'}x+='<div class="ui-datepicker-header ui-widget-header ui-helper-clearfix'+t+'">'+(/all|left/.test(t)&&C==0?c?f:n:"")+(/all|right/.test(t)&&C==0?c?n:f:"")+this._generateMonthYearHeader(a,g,m,k,o,C>0||D>0,z,w)+'</div><table class="ui-datepicker-calendar"><thead><tr>';var A=j?'<th class="ui-datepicker-week-col">'+this._get(a,"weekHeader")+"</th>":"";for(t=0;t<7;t++){var q=
(t+h)%7;A+="<th"+((t+h+6)%7>=5?' class="ui-datepicker-week-end"':"")+'><span title="'+r[q]+'">'+s[q]+"</span></th>"}x+=A+"</tr></thead><tbody>";A=this._getDaysInMonth(m,g);if(m==a.selectedYear&&g==a.selectedMonth)a.selectedDay=Math.min(a.selectedDay,A);t=(this._getFirstDayOfMonth(m,g)-h+7)%7;A=l?6:Math.ceil((t+A)/7);q=this._daylightSavingAdjust(new Date(m,g,1-t));for(var O=0;O<A;O++){x+="<tr>";var P=!j?"":'<td class="ui-datepicker-week-col">'+this._get(a,"calculateWeek")(q)+"</td>";for(t=0;t<7;t++){var F=
p?p.apply(a.input?a.input[0]:null,[q]):[true,""],B=q.getMonth()!=g,J=B&&!H||!F[0]||k&&q<k||o&&q>o;P+='<td class="'+((t+h+6)%7>=5?" ui-datepicker-week-end":"")+(B?" ui-datepicker-other-month":"")+(q.getTime()==N.getTime()&&g==a.selectedMonth&&a._keyEvent||L.getTime()==q.getTime()&&L.getTime()==N.getTime()?" "+this._dayOverClass:"")+(J?" "+this._unselectableClass+" ui-state-disabled":"")+(B&&!v?"":" "+F[1]+(q.getTime()==u.getTime()?" "+this._currentClass:"")+(q.getTime()==b.getTime()?" ui-datepicker-today":
""))+'"'+((!B||v)&&F[2]?' title="'+F[2]+'"':"")+(J?"":' onclick="DP_jQuery_'+y+".datepicker._selectDay('#"+a.id+"',"+q.getMonth()+","+q.getFullYear()+', this);return false;"')+">"+(B&&!v?"&#xa0;":J?'<span class="ui-state-default">'+q.getDate()+"</span>":'<a class="ui-state-default'+(q.getTime()==b.getTime()?" ui-state-highlight":"")+(q.getTime()==u.getTime()?" ui-state-active":"")+(B?" ui-priority-secondary":"")+'" href="#">'+q.getDate()+"</a>")+"</td>";q.setDate(q.getDate()+1);q=this._daylightSavingAdjust(q)}x+=
P+"</tr>"}g++;if(g>11){g=0;m++}x+="</tbody></table>"+(l?"</div>"+(i[0]>0&&D==i[1]-1?'<div class="ui-datepicker-row-break"></div>':""):"");M+=x}I+=M}I+=e+(d.browser.msie&&parseInt(d.browser.version,10)<7&&!a.inline?'<iframe src="javascript:false;" class="ui-datepicker-cover" frameborder="0"></iframe>':"");a._keyEvent=false;return I},_generateMonthYearHeader:function(a,b,c,e,f,h,i,g){var j=this._get(a,"changeMonth"),l=this._get(a,"changeYear"),u=this._get(a,"showMonthAfterYear"),k='<div class="ui-datepicker-title">',
o="";if(h||!j)o+='<span class="ui-datepicker-month">'+i[b]+"</span>";else{i=e&&e.getFullYear()==c;var m=f&&f.getFullYear()==c;o+='<select class="ui-datepicker-month" onchange="DP_jQuery_'+y+".datepicker._selectMonthYear('#"+a.id+"', this, 'M');\" onclick=\"DP_jQuery_"+y+".datepicker._clickMonthYear('#"+a.id+"');\">";for(var n=0;n<12;n++)if((!i||n>=e.getMonth())&&(!m||n<=f.getMonth()))o+='<option value="'+n+'"'+(n==b?' selected="selected"':"")+">"+g[n]+"</option>";o+="</select>"}u||(k+=o+(h||!(j&&
l)?"&#xa0;":""));a.yearshtml="";if(h||!l)k+='<span class="ui-datepicker-year">'+c+"</span>";else{g=this._get(a,"yearRange").split(":");var r=(new Date).getFullYear();i=function(s){s=s.match(/c[+-].*/)?c+parseInt(s.substring(1),10):s.match(/[+-].*/)?r+parseInt(s,10):parseInt(s,10);return isNaN(s)?r:s};b=i(g[0]);g=Math.max(b,i(g[1]||""));b=e?Math.max(b,e.getFullYear()):b;g=f?Math.min(g,f.getFullYear()):g;for(a.yearshtml+='<select class="ui-datepicker-year" onchange="DP_jQuery_'+y+".datepicker._selectMonthYear('#"+
a.id+"', this, 'Y');\" onclick=\"DP_jQuery_"+y+".datepicker._clickMonthYear('#"+a.id+"');\">";b<=g;b++)a.yearshtml+='<option value="'+b+'"'+(b==c?' selected="selected"':"")+">"+b+"</option>";a.yearshtml+="</select>";if(d.browser.mozilla)k+='<select class="ui-datepicker-year"><option value="'+c+'" selected="selected">'+c+"</option></select>";else{k+=a.yearshtml;a.yearshtml=null}}k+=this._get(a,"yearSuffix");if(u)k+=(h||!(j&&l)?"&#xa0;":"")+o;k+="</div>";return k},_adjustInstDate:function(a,b,c){var e=
a.drawYear+(c=="Y"?b:0),f=a.drawMonth+(c=="M"?b:0);b=Math.min(a.selectedDay,this._getDaysInMonth(e,f))+(c=="D"?b:0);e=this._restrictMinMax(a,this._daylightSavingAdjust(new Date(e,f,b)));a.selectedDay=e.getDate();a.drawMonth=a.selectedMonth=e.getMonth();a.drawYear=a.selectedYear=e.getFullYear();if(c=="M"||c=="Y")this._notifyChange(a)},_restrictMinMax:function(a,b){var c=this._getMinMaxDate(a,"min");a=this._getMinMaxDate(a,"max");b=c&&b<c?c:b;return b=a&&b>a?a:b},_notifyChange:function(a){var b=this._get(a,
"onChangeMonthYear");if(b)b.apply(a.input?a.input[0]:null,[a.selectedYear,a.selectedMonth+1,a])},_getNumberOfMonths:function(a){a=this._get(a,"numberOfMonths");return a==null?[1,1]:typeof a=="number"?[1,a]:a},_getMinMaxDate:function(a,b){return this._determineDate(a,this._get(a,b+"Date"),null)},_getDaysInMonth:function(a,b){return 32-(new Date(a,b,32)).getDate()},_getFirstDayOfMonth:function(a,b){return(new Date(a,b,1)).getDay()},_canAdjustMonth:function(a,b,c,e){var f=this._getNumberOfMonths(a);
c=this._daylightSavingAdjust(new Date(c,e+(b<0?b:f[0]*f[1]),1));b<0&&c.setDate(this._getDaysInMonth(c.getFullYear(),c.getMonth()));return this._isInRange(a,c)},_isInRange:function(a,b){var c=this._getMinMaxDate(a,"min");a=this._getMinMaxDate(a,"max");return(!c||b.getTime()>=c.getTime())&&(!a||b.getTime()<=a.getTime())},_getFormatConfig:function(a){var b=this._get(a,"shortYearCutoff");b=typeof b!="string"?b:(new Date).getFullYear()%100+parseInt(b,10);return{shortYearCutoff:b,dayNamesShort:this._get(a,
"dayNamesShort"),dayNames:this._get(a,"dayNames"),monthNamesShort:this._get(a,"monthNamesShort"),monthNames:this._get(a,"monthNames")}},_formatDate:function(a,b,c,e){if(!b){a.currentDay=a.selectedDay;a.currentMonth=a.selectedMonth;a.currentYear=a.selectedYear}b=b?typeof b=="object"?b:this._daylightSavingAdjust(new Date(e,c,b)):this._daylightSavingAdjust(new Date(a.currentYear,a.currentMonth,a.currentDay));return this.formatDate(this._get(a,"dateFormat"),b,this._getFormatConfig(a))}});d.fn.datepicker=
function(a){if(!d.datepicker.initialized){d(document).mousedown(d.datepicker._checkExternalClick).find("body").append(d.datepicker.dpDiv);d.datepicker.initialized=true}var b=Array.prototype.slice.call(arguments,1);if(typeof a=="string"&&(a=="isDisabled"||a=="getDate"||a=="widget"))return d.datepicker["_"+a+"Datepicker"].apply(d.datepicker,[this[0]].concat(b));if(a=="option"&&arguments.length==2&&typeof arguments[1]=="string")return d.datepicker["_"+a+"Datepicker"].apply(d.datepicker,[this[0]].concat(b));
return this.each(function(){typeof a=="string"?d.datepicker["_"+a+"Datepicker"].apply(d.datepicker,[this].concat(b)):d.datepicker._attachDatepicker(this,a)})};d.datepicker=new K;d.datepicker.initialized=false;d.datepicker.uuid=(new Date).getTime();d.datepicker.version="1.8.8";window["DP_jQuery_"+y]=d})(jQuery);
;/*
 * jQuery UI Progressbar 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Progressbar
 *
 * Depends:
 *   jquery.ui.core.js
 *   jquery.ui.widget.js
 */
(function(b,d){b.widget("ui.progressbar",{options:{value:0,max:100},min:0,_create:function(){this.element.addClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").attr({role:"progressbar","aria-valuemin":this.min,"aria-valuemax":this.options.max,"aria-valuenow":this._value()});this.valueDiv=b("<div class='ui-progressbar-value ui-widget-header ui-corner-left'></div>").appendTo(this.element);this.oldValue=this._value();this._refreshValue()},destroy:function(){this.element.removeClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").removeAttr("role").removeAttr("aria-valuemin").removeAttr("aria-valuemax").removeAttr("aria-valuenow");
this.valueDiv.remove();b.Widget.prototype.destroy.apply(this,arguments)},value:function(a){if(a===d)return this._value();this._setOption("value",a);return this},_setOption:function(a,c){if(a==="value"){this.options.value=c;this._refreshValue();this._value()===this.options.max&&this._trigger("complete")}b.Widget.prototype._setOption.apply(this,arguments)},_value:function(){var a=this.options.value;if(typeof a!=="number")a=0;return Math.min(this.options.max,Math.max(this.min,a))},_percentage:function(){return 100*
this._value()/this.options.max},_refreshValue:function(){var a=this.value(),c=this._percentage();if(this.oldValue!==a){this.oldValue=a;this._trigger("change")}this.valueDiv.toggleClass("ui-corner-right",a===this.options.max).width(c.toFixed(0)+"%");this.element.attr("aria-valuenow",a)}});b.extend(b.ui.progressbar,{version:"1.8.8"})})(jQuery);
;/*
 * jQuery UI Effects 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/
 */
jQuery.effects||function(f,j){function n(c){var a;if(c&&c.constructor==Array&&c.length==3)return c;if(a=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(c))return[parseInt(a[1],10),parseInt(a[2],10),parseInt(a[3],10)];if(a=/rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(c))return[parseFloat(a[1])*2.55,parseFloat(a[2])*2.55,parseFloat(a[3])*2.55];if(a=/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(c))return[parseInt(a[1],
16),parseInt(a[2],16),parseInt(a[3],16)];if(a=/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(c))return[parseInt(a[1]+a[1],16),parseInt(a[2]+a[2],16),parseInt(a[3]+a[3],16)];if(/rgba\(0, 0, 0, 0\)/.exec(c))return o.transparent;return o[f.trim(c).toLowerCase()]}function s(c,a){var b;do{b=f.curCSS(c,a);if(b!=""&&b!="transparent"||f.nodeName(c,"body"))break;a="backgroundColor"}while(c=c.parentNode);return n(b)}function p(){var c=document.defaultView?document.defaultView.getComputedStyle(this,null):this.currentStyle,
a={},b,d;if(c&&c.length&&c[0]&&c[c[0]])for(var e=c.length;e--;){b=c[e];if(typeof c[b]=="string"){d=b.replace(/\-(\w)/g,function(g,h){return h.toUpperCase()});a[d]=c[b]}}else for(b in c)if(typeof c[b]==="string")a[b]=c[b];return a}function q(c){var a,b;for(a in c){b=c[a];if(b==null||f.isFunction(b)||a in t||/scrollbar/.test(a)||!/color/i.test(a)&&isNaN(parseFloat(b)))delete c[a]}return c}function u(c,a){var b={_:0},d;for(d in a)if(c[d]!=a[d])b[d]=a[d];return b}function k(c,a,b,d){if(typeof c=="object"){d=
a;b=null;a=c;c=a.effect}if(f.isFunction(a)){d=a;b=null;a={}}if(typeof a=="number"||f.fx.speeds[a]){d=b;b=a;a={}}if(f.isFunction(b)){d=b;b=null}a=a||{};b=b||a.duration;b=f.fx.off?0:typeof b=="number"?b:b in f.fx.speeds?f.fx.speeds[b]:f.fx.speeds._default;d=d||a.complete;return[c,a,b,d]}function m(c){if(!c||typeof c==="number"||f.fx.speeds[c])return true;if(typeof c==="string"&&!f.effects[c])return true;return false}f.effects={};f.each(["backgroundColor","borderBottomColor","borderLeftColor","borderRightColor",
"borderTopColor","borderColor","color","outlineColor"],function(c,a){f.fx.step[a]=function(b){if(!b.colorInit){b.start=s(b.elem,a);b.end=n(b.end);b.colorInit=true}b.elem.style[a]="rgb("+Math.max(Math.min(parseInt(b.pos*(b.end[0]-b.start[0])+b.start[0],10),255),0)+","+Math.max(Math.min(parseInt(b.pos*(b.end[1]-b.start[1])+b.start[1],10),255),0)+","+Math.max(Math.min(parseInt(b.pos*(b.end[2]-b.start[2])+b.start[2],10),255),0)+")"}});var o={aqua:[0,255,255],azure:[240,255,255],beige:[245,245,220],black:[0,
0,0],blue:[0,0,255],brown:[165,42,42],cyan:[0,255,255],darkblue:[0,0,139],darkcyan:[0,139,139],darkgrey:[169,169,169],darkgreen:[0,100,0],darkkhaki:[189,183,107],darkmagenta:[139,0,139],darkolivegreen:[85,107,47],darkorange:[255,140,0],darkorchid:[153,50,204],darkred:[139,0,0],darksalmon:[233,150,122],darkviolet:[148,0,211],fuchsia:[255,0,255],gold:[255,215,0],green:[0,128,0],indigo:[75,0,130],khaki:[240,230,140],lightblue:[173,216,230],lightcyan:[224,255,255],lightgreen:[144,238,144],lightgrey:[211,
211,211],lightpink:[255,182,193],lightyellow:[255,255,224],lime:[0,255,0],magenta:[255,0,255],maroon:[128,0,0],navy:[0,0,128],olive:[128,128,0],orange:[255,165,0],pink:[255,192,203],purple:[128,0,128],violet:[128,0,128],red:[255,0,0],silver:[192,192,192],white:[255,255,255],yellow:[255,255,0],transparent:[255,255,255]},r=["add","remove","toggle"],t={border:1,borderBottom:1,borderColor:1,borderLeft:1,borderRight:1,borderTop:1,borderWidth:1,margin:1,padding:1};f.effects.animateClass=function(c,a,b,
d){if(f.isFunction(b)){d=b;b=null}return this.queue("fx",function(){var e=f(this),g=e.attr("style")||" ",h=q(p.call(this)),l,v=e.attr("className");f.each(r,function(w,i){c[i]&&e[i+"Class"](c[i])});l=q(p.call(this));e.attr("className",v);e.animate(u(h,l),a,b,function(){f.each(r,function(w,i){c[i]&&e[i+"Class"](c[i])});if(typeof e.attr("style")=="object"){e.attr("style").cssText="";e.attr("style").cssText=g}else e.attr("style",g);d&&d.apply(this,arguments)});h=f.queue(this);l=h.splice(h.length-1,1)[0];
h.splice(1,0,l);f.dequeue(this)})};f.fn.extend({_addClass:f.fn.addClass,addClass:function(c,a,b,d){return a?f.effects.animateClass.apply(this,[{add:c},a,b,d]):this._addClass(c)},_removeClass:f.fn.removeClass,removeClass:function(c,a,b,d){return a?f.effects.animateClass.apply(this,[{remove:c},a,b,d]):this._removeClass(c)},_toggleClass:f.fn.toggleClass,toggleClass:function(c,a,b,d,e){return typeof a=="boolean"||a===j?b?f.effects.animateClass.apply(this,[a?{add:c}:{remove:c},b,d,e]):this._toggleClass(c,
a):f.effects.animateClass.apply(this,[{toggle:c},a,b,d])},switchClass:function(c,a,b,d,e){return f.effects.animateClass.apply(this,[{add:a,remove:c},b,d,e])}});f.extend(f.effects,{version:"1.8.8",save:function(c,a){for(var b=0;b<a.length;b++)a[b]!==null&&c.data("ec.storage."+a[b],c[0].style[a[b]])},restore:function(c,a){for(var b=0;b<a.length;b++)a[b]!==null&&c.css(a[b],c.data("ec.storage."+a[b]))},setMode:function(c,a){if(a=="toggle")a=c.is(":hidden")?"show":"hide";return a},getBaseline:function(c,
a){var b;switch(c[0]){case "top":b=0;break;case "middle":b=0.5;break;case "bottom":b=1;break;default:b=c[0]/a.height}switch(c[1]){case "left":c=0;break;case "center":c=0.5;break;case "right":c=1;break;default:c=c[1]/a.width}return{x:c,y:b}},createWrapper:function(c){if(c.parent().is(".ui-effects-wrapper"))return c.parent();var a={width:c.outerWidth(true),height:c.outerHeight(true),"float":c.css("float")},b=f("<div></div>").addClass("ui-effects-wrapper").css({fontSize:"100%",background:"transparent",
border:"none",margin:0,padding:0});c.wrap(b);b=c.parent();if(c.css("position")=="static"){b.css({position:"relative"});c.css({position:"relative"})}else{f.extend(a,{position:c.css("position"),zIndex:c.css("z-index")});f.each(["top","left","bottom","right"],function(d,e){a[e]=c.css(e);if(isNaN(parseInt(a[e],10)))a[e]="auto"});c.css({position:"relative",top:0,left:0,right:"auto",bottom:"auto"})}return b.css(a).show()},removeWrapper:function(c){if(c.parent().is(".ui-effects-wrapper"))return c.parent().replaceWith(c);
return c},setTransition:function(c,a,b,d){d=d||{};f.each(a,function(e,g){unit=c.cssUnit(g);if(unit[0]>0)d[g]=unit[0]*b+unit[1]});return d}});f.fn.extend({effect:function(c){var a=k.apply(this,arguments),b={options:a[1],duration:a[2],callback:a[3]};a=b.options.mode;var d=f.effects[c];if(f.fx.off||!d)return a?this[a](b.duration,b.callback):this.each(function(){b.callback&&b.callback.call(this)});return d.call(this,b)},_show:f.fn.show,show:function(c){if(m(c))return this._show.apply(this,arguments);
else{var a=k.apply(this,arguments);a[1].mode="show";return this.effect.apply(this,a)}},_hide:f.fn.hide,hide:function(c){if(m(c))return this._hide.apply(this,arguments);else{var a=k.apply(this,arguments);a[1].mode="hide";return this.effect.apply(this,a)}},__toggle:f.fn.toggle,toggle:function(c){if(m(c)||typeof c==="boolean"||f.isFunction(c))return this.__toggle.apply(this,arguments);else{var a=k.apply(this,arguments);a[1].mode="toggle";return this.effect.apply(this,a)}},cssUnit:function(c){var a=this.css(c),
b=[];f.each(["em","px","%","pt"],function(d,e){if(a.indexOf(e)>0)b=[parseFloat(a),e]});return b}});f.easing.jswing=f.easing.swing;f.extend(f.easing,{def:"easeOutQuad",swing:function(c,a,b,d,e){return f.easing[f.easing.def](c,a,b,d,e)},easeInQuad:function(c,a,b,d,e){return d*(a/=e)*a+b},easeOutQuad:function(c,a,b,d,e){return-d*(a/=e)*(a-2)+b},easeInOutQuad:function(c,a,b,d,e){if((a/=e/2)<1)return d/2*a*a+b;return-d/2*(--a*(a-2)-1)+b},easeInCubic:function(c,a,b,d,e){return d*(a/=e)*a*a+b},easeOutCubic:function(c,
a,b,d,e){return d*((a=a/e-1)*a*a+1)+b},easeInOutCubic:function(c,a,b,d,e){if((a/=e/2)<1)return d/2*a*a*a+b;return d/2*((a-=2)*a*a+2)+b},easeInQuart:function(c,a,b,d,e){return d*(a/=e)*a*a*a+b},easeOutQuart:function(c,a,b,d,e){return-d*((a=a/e-1)*a*a*a-1)+b},easeInOutQuart:function(c,a,b,d,e){if((a/=e/2)<1)return d/2*a*a*a*a+b;return-d/2*((a-=2)*a*a*a-2)+b},easeInQuint:function(c,a,b,d,e){return d*(a/=e)*a*a*a*a+b},easeOutQuint:function(c,a,b,d,e){return d*((a=a/e-1)*a*a*a*a+1)+b},easeInOutQuint:function(c,
a,b,d,e){if((a/=e/2)<1)return d/2*a*a*a*a*a+b;return d/2*((a-=2)*a*a*a*a+2)+b},easeInSine:function(c,a,b,d,e){return-d*Math.cos(a/e*(Math.PI/2))+d+b},easeOutSine:function(c,a,b,d,e){return d*Math.sin(a/e*(Math.PI/2))+b},easeInOutSine:function(c,a,b,d,e){return-d/2*(Math.cos(Math.PI*a/e)-1)+b},easeInExpo:function(c,a,b,d,e){return a==0?b:d*Math.pow(2,10*(a/e-1))+b},easeOutExpo:function(c,a,b,d,e){return a==e?b+d:d*(-Math.pow(2,-10*a/e)+1)+b},easeInOutExpo:function(c,a,b,d,e){if(a==0)return b;if(a==
e)return b+d;if((a/=e/2)<1)return d/2*Math.pow(2,10*(a-1))+b;return d/2*(-Math.pow(2,-10*--a)+2)+b},easeInCirc:function(c,a,b,d,e){return-d*(Math.sqrt(1-(a/=e)*a)-1)+b},easeOutCirc:function(c,a,b,d,e){return d*Math.sqrt(1-(a=a/e-1)*a)+b},easeInOutCirc:function(c,a,b,d,e){if((a/=e/2)<1)return-d/2*(Math.sqrt(1-a*a)-1)+b;return d/2*(Math.sqrt(1-(a-=2)*a)+1)+b},easeInElastic:function(c,a,b,d,e){c=1.70158;var g=0,h=d;if(a==0)return b;if((a/=e)==1)return b+d;g||(g=e*0.3);if(h<Math.abs(d)){h=d;c=g/4}else c=
g/(2*Math.PI)*Math.asin(d/h);return-(h*Math.pow(2,10*(a-=1))*Math.sin((a*e-c)*2*Math.PI/g))+b},easeOutElastic:function(c,a,b,d,e){c=1.70158;var g=0,h=d;if(a==0)return b;if((a/=e)==1)return b+d;g||(g=e*0.3);if(h<Math.abs(d)){h=d;c=g/4}else c=g/(2*Math.PI)*Math.asin(d/h);return h*Math.pow(2,-10*a)*Math.sin((a*e-c)*2*Math.PI/g)+d+b},easeInOutElastic:function(c,a,b,d,e){c=1.70158;var g=0,h=d;if(a==0)return b;if((a/=e/2)==2)return b+d;g||(g=e*0.3*1.5);if(h<Math.abs(d)){h=d;c=g/4}else c=g/(2*Math.PI)*Math.asin(d/
h);if(a<1)return-0.5*h*Math.pow(2,10*(a-=1))*Math.sin((a*e-c)*2*Math.PI/g)+b;return h*Math.pow(2,-10*(a-=1))*Math.sin((a*e-c)*2*Math.PI/g)*0.5+d+b},easeInBack:function(c,a,b,d,e,g){if(g==j)g=1.70158;return d*(a/=e)*a*((g+1)*a-g)+b},easeOutBack:function(c,a,b,d,e,g){if(g==j)g=1.70158;return d*((a=a/e-1)*a*((g+1)*a+g)+1)+b},easeInOutBack:function(c,a,b,d,e,g){if(g==j)g=1.70158;if((a/=e/2)<1)return d/2*a*a*(((g*=1.525)+1)*a-g)+b;return d/2*((a-=2)*a*(((g*=1.525)+1)*a+g)+2)+b},easeInBounce:function(c,
a,b,d,e){return d-f.easing.easeOutBounce(c,e-a,0,d,e)+b},easeOutBounce:function(c,a,b,d,e){return(a/=e)<1/2.75?d*7.5625*a*a+b:a<2/2.75?d*(7.5625*(a-=1.5/2.75)*a+0.75)+b:a<2.5/2.75?d*(7.5625*(a-=2.25/2.75)*a+0.9375)+b:d*(7.5625*(a-=2.625/2.75)*a+0.984375)+b},easeInOutBounce:function(c,a,b,d,e){if(a<e/2)return f.easing.easeInBounce(c,a*2,0,d,e)*0.5+b;return f.easing.easeOutBounce(c,a*2-e,0,d,e)*0.5+d*0.5+b}})}(jQuery);
;/*
 * jQuery UI Effects Blind 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Blind
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(b){b.effects.blind=function(c){return this.queue(function(){var a=b(this),g=["position","top","bottom","left","right"],f=b.effects.setMode(a,c.options.mode||"hide"),d=c.options.direction||"vertical";b.effects.save(a,g);a.show();var e=b.effects.createWrapper(a).css({overflow:"hidden"}),h=d=="vertical"?"height":"width";d=d=="vertical"?e.height():e.width();f=="show"&&e.css(h,0);var i={};i[h]=f=="show"?d:0;e.animate(i,c.duration,c.options.easing,function(){f=="hide"&&a.hide();b.effects.restore(a,
g);b.effects.removeWrapper(a);c.callback&&c.callback.apply(a[0],arguments);a.dequeue()})})}})(jQuery);
;/*
 * jQuery UI Effects Bounce 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Bounce
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(e){e.effects.bounce=function(b){return this.queue(function(){var a=e(this),l=["position","top","bottom","left","right"],h=e.effects.setMode(a,b.options.mode||"effect"),d=b.options.direction||"up",c=b.options.distance||20,m=b.options.times||5,i=b.duration||250;/show|hide/.test(h)&&l.push("opacity");e.effects.save(a,l);a.show();e.effects.createWrapper(a);var f=d=="up"||d=="down"?"top":"left";d=d=="up"||d=="left"?"pos":"neg";c=b.options.distance||(f=="top"?a.outerHeight({margin:true})/3:a.outerWidth({margin:true})/
3);if(h=="show")a.css("opacity",0).css(f,d=="pos"?-c:c);if(h=="hide")c/=m*2;h!="hide"&&m--;if(h=="show"){var g={opacity:1};g[f]=(d=="pos"?"+=":"-=")+c;a.animate(g,i/2,b.options.easing);c/=2;m--}for(g=0;g<m;g++){var j={},k={};j[f]=(d=="pos"?"-=":"+=")+c;k[f]=(d=="pos"?"+=":"-=")+c;a.animate(j,i/2,b.options.easing).animate(k,i/2,b.options.easing);c=h=="hide"?c*2:c/2}if(h=="hide"){g={opacity:0};g[f]=(d=="pos"?"-=":"+=")+c;a.animate(g,i/2,b.options.easing,function(){a.hide();e.effects.restore(a,l);e.effects.removeWrapper(a);
b.callback&&b.callback.apply(this,arguments)})}else{j={};k={};j[f]=(d=="pos"?"-=":"+=")+c;k[f]=(d=="pos"?"+=":"-=")+c;a.animate(j,i/2,b.options.easing).animate(k,i/2,b.options.easing,function(){e.effects.restore(a,l);e.effects.removeWrapper(a);b.callback&&b.callback.apply(this,arguments)})}a.queue("fx",function(){a.dequeue()});a.dequeue()})}})(jQuery);
;/*
 * jQuery UI Effects Clip 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Clip
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(b){b.effects.clip=function(e){return this.queue(function(){var a=b(this),i=["position","top","bottom","left","right","height","width"],f=b.effects.setMode(a,e.options.mode||"hide"),c=e.options.direction||"vertical";b.effects.save(a,i);a.show();var d=b.effects.createWrapper(a).css({overflow:"hidden"});d=a[0].tagName=="IMG"?d:a;var g={size:c=="vertical"?"height":"width",position:c=="vertical"?"top":"left"};c=c=="vertical"?d.height():d.width();if(f=="show"){d.css(g.size,0);d.css(g.position,
c/2)}var h={};h[g.size]=f=="show"?c:0;h[g.position]=f=="show"?0:c/2;d.animate(h,{queue:false,duration:e.duration,easing:e.options.easing,complete:function(){f=="hide"&&a.hide();b.effects.restore(a,i);b.effects.removeWrapper(a);e.callback&&e.callback.apply(a[0],arguments);a.dequeue()}})})}})(jQuery);
;/*
 * jQuery UI Effects Drop 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Drop
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(c){c.effects.drop=function(d){return this.queue(function(){var a=c(this),h=["position","top","bottom","left","right","opacity"],e=c.effects.setMode(a,d.options.mode||"hide"),b=d.options.direction||"left";c.effects.save(a,h);a.show();c.effects.createWrapper(a);var f=b=="up"||b=="down"?"top":"left";b=b=="up"||b=="left"?"pos":"neg";var g=d.options.distance||(f=="top"?a.outerHeight({margin:true})/2:a.outerWidth({margin:true})/2);if(e=="show")a.css("opacity",0).css(f,b=="pos"?-g:g);var i={opacity:e==
"show"?1:0};i[f]=(e=="show"?b=="pos"?"+=":"-=":b=="pos"?"-=":"+=")+g;a.animate(i,{queue:false,duration:d.duration,easing:d.options.easing,complete:function(){e=="hide"&&a.hide();c.effects.restore(a,h);c.effects.removeWrapper(a);d.callback&&d.callback.apply(this,arguments);a.dequeue()}})})}})(jQuery);
;/*
 * jQuery UI Effects Explode 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Explode
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(j){j.effects.explode=function(a){return this.queue(function(){var c=a.options.pieces?Math.round(Math.sqrt(a.options.pieces)):3,d=a.options.pieces?Math.round(Math.sqrt(a.options.pieces)):3;a.options.mode=a.options.mode=="toggle"?j(this).is(":visible")?"hide":"show":a.options.mode;var b=j(this).show().css("visibility","hidden"),g=b.offset();g.top-=parseInt(b.css("marginTop"),10)||0;g.left-=parseInt(b.css("marginLeft"),10)||0;for(var h=b.outerWidth(true),i=b.outerHeight(true),e=0;e<c;e++)for(var f=
0;f<d;f++)b.clone().appendTo("body").wrap("<div></div>").css({position:"absolute",visibility:"visible",left:-f*(h/d),top:-e*(i/c)}).parent().addClass("ui-effects-explode").css({position:"absolute",overflow:"hidden",width:h/d,height:i/c,left:g.left+f*(h/d)+(a.options.mode=="show"?(f-Math.floor(d/2))*(h/d):0),top:g.top+e*(i/c)+(a.options.mode=="show"?(e-Math.floor(c/2))*(i/c):0),opacity:a.options.mode=="show"?0:1}).animate({left:g.left+f*(h/d)+(a.options.mode=="show"?0:(f-Math.floor(d/2))*(h/d)),top:g.top+
e*(i/c)+(a.options.mode=="show"?0:(e-Math.floor(c/2))*(i/c)),opacity:a.options.mode=="show"?1:0},a.duration||500);setTimeout(function(){a.options.mode=="show"?b.css({visibility:"visible"}):b.css({visibility:"visible"}).hide();a.callback&&a.callback.apply(b[0]);b.dequeue();j("div.ui-effects-explode").remove()},a.duration||500)})}})(jQuery);
;/*
 * jQuery UI Effects Fade 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Fade
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(b){b.effects.fade=function(a){return this.queue(function(){var c=b(this),d=b.effects.setMode(c,a.options.mode||"hide");c.animate({opacity:d},{queue:false,duration:a.duration,easing:a.options.easing,complete:function(){a.callback&&a.callback.apply(this,arguments);c.dequeue()}})})}})(jQuery);
;/*
 * jQuery UI Effects Fold 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Fold
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(c){c.effects.fold=function(a){return this.queue(function(){var b=c(this),j=["position","top","bottom","left","right"],d=c.effects.setMode(b,a.options.mode||"hide"),g=a.options.size||15,h=!!a.options.horizFirst,k=a.duration?a.duration/2:c.fx.speeds._default/2;c.effects.save(b,j);b.show();var e=c.effects.createWrapper(b).css({overflow:"hidden"}),f=d=="show"!=h,l=f?["width","height"]:["height","width"];f=f?[e.width(),e.height()]:[e.height(),e.width()];var i=/([0-9]+)%/.exec(g);if(i)g=parseInt(i[1],
10)/100*f[d=="hide"?0:1];if(d=="show")e.css(h?{height:0,width:g}:{height:g,width:0});h={};i={};h[l[0]]=d=="show"?f[0]:g;i[l[1]]=d=="show"?f[1]:0;e.animate(h,k,a.options.easing).animate(i,k,a.options.easing,function(){d=="hide"&&b.hide();c.effects.restore(b,j);c.effects.removeWrapper(b);a.callback&&a.callback.apply(b[0],arguments);b.dequeue()})})}})(jQuery);
;/*
 * jQuery UI Effects Highlight 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Highlight
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(b){b.effects.highlight=function(c){return this.queue(function(){var a=b(this),e=["backgroundImage","backgroundColor","opacity"],d=b.effects.setMode(a,c.options.mode||"show"),f={backgroundColor:a.css("backgroundColor")};if(d=="hide")f.opacity=0;b.effects.save(a,e);a.show().css({backgroundImage:"none",backgroundColor:c.options.color||"#ffff99"}).animate(f,{queue:false,duration:c.duration,easing:c.options.easing,complete:function(){d=="hide"&&a.hide();b.effects.restore(a,e);d=="show"&&!b.support.opacity&&
this.style.removeAttribute("filter");c.callback&&c.callback.apply(this,arguments);a.dequeue()}})})}})(jQuery);
;/*
 * jQuery UI Effects Pulsate 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Pulsate
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(d){d.effects.pulsate=function(a){return this.queue(function(){var b=d(this),c=d.effects.setMode(b,a.options.mode||"show");times=(a.options.times||5)*2-1;duration=a.duration?a.duration/2:d.fx.speeds._default/2;isVisible=b.is(":visible");animateTo=0;if(!isVisible){b.css("opacity",0).show();animateTo=1}if(c=="hide"&&isVisible||c=="show"&&!isVisible)times--;for(c=0;c<times;c++){b.animate({opacity:animateTo},duration,a.options.easing);animateTo=(animateTo+1)%2}b.animate({opacity:animateTo},duration,
a.options.easing,function(){animateTo==0&&b.hide();a.callback&&a.callback.apply(this,arguments)});b.queue("fx",function(){b.dequeue()}).dequeue()})}})(jQuery);
;/*
 * jQuery UI Effects Scale 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Scale
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(c){c.effects.puff=function(b){return this.queue(function(){var a=c(this),e=c.effects.setMode(a,b.options.mode||"hide"),g=parseInt(b.options.percent,10)||150,h=g/100,i={height:a.height(),width:a.width()};c.extend(b.options,{fade:true,mode:e,percent:e=="hide"?g:100,from:e=="hide"?i:{height:i.height*h,width:i.width*h}});a.effect("scale",b.options,b.duration,b.callback);a.dequeue()})};c.effects.scale=function(b){return this.queue(function(){var a=c(this),e=c.extend(true,{},b.options),g=c.effects.setMode(a,
b.options.mode||"effect"),h=parseInt(b.options.percent,10)||(parseInt(b.options.percent,10)==0?0:g=="hide"?0:100),i=b.options.direction||"both",f=b.options.origin;if(g!="effect"){e.origin=f||["middle","center"];e.restore=true}f={height:a.height(),width:a.width()};a.from=b.options.from||(g=="show"?{height:0,width:0}:f);h={y:i!="horizontal"?h/100:1,x:i!="vertical"?h/100:1};a.to={height:f.height*h.y,width:f.width*h.x};if(b.options.fade){if(g=="show"){a.from.opacity=0;a.to.opacity=1}if(g=="hide"){a.from.opacity=
1;a.to.opacity=0}}e.from=a.from;e.to=a.to;e.mode=g;a.effect("size",e,b.duration,b.callback);a.dequeue()})};c.effects.size=function(b){return this.queue(function(){var a=c(this),e=["position","top","bottom","left","right","width","height","overflow","opacity"],g=["position","top","bottom","left","right","overflow","opacity"],h=["width","height","overflow"],i=["fontSize"],f=["borderTopWidth","borderBottomWidth","paddingTop","paddingBottom"],k=["borderLeftWidth","borderRightWidth","paddingLeft","paddingRight"],
p=c.effects.setMode(a,b.options.mode||"effect"),n=b.options.restore||false,m=b.options.scale||"both",l=b.options.origin,j={height:a.height(),width:a.width()};a.from=b.options.from||j;a.to=b.options.to||j;if(l){l=c.effects.getBaseline(l,j);a.from.top=(j.height-a.from.height)*l.y;a.from.left=(j.width-a.from.width)*l.x;a.to.top=(j.height-a.to.height)*l.y;a.to.left=(j.width-a.to.width)*l.x}var d={from:{y:a.from.height/j.height,x:a.from.width/j.width},to:{y:a.to.height/j.height,x:a.to.width/j.width}};
if(m=="box"||m=="both"){if(d.from.y!=d.to.y){e=e.concat(f);a.from=c.effects.setTransition(a,f,d.from.y,a.from);a.to=c.effects.setTransition(a,f,d.to.y,a.to)}if(d.from.x!=d.to.x){e=e.concat(k);a.from=c.effects.setTransition(a,k,d.from.x,a.from);a.to=c.effects.setTransition(a,k,d.to.x,a.to)}}if(m=="content"||m=="both")if(d.from.y!=d.to.y){e=e.concat(i);a.from=c.effects.setTransition(a,i,d.from.y,a.from);a.to=c.effects.setTransition(a,i,d.to.y,a.to)}c.effects.save(a,n?e:g);a.show();c.effects.createWrapper(a);
a.css("overflow","hidden").css(a.from);if(m=="content"||m=="both"){f=f.concat(["marginTop","marginBottom"]).concat(i);k=k.concat(["marginLeft","marginRight"]);h=e.concat(f).concat(k);a.find("*[width]").each(function(){child=c(this);n&&c.effects.save(child,h);var o={height:child.height(),width:child.width()};child.from={height:o.height*d.from.y,width:o.width*d.from.x};child.to={height:o.height*d.to.y,width:o.width*d.to.x};if(d.from.y!=d.to.y){child.from=c.effects.setTransition(child,f,d.from.y,child.from);
child.to=c.effects.setTransition(child,f,d.to.y,child.to)}if(d.from.x!=d.to.x){child.from=c.effects.setTransition(child,k,d.from.x,child.from);child.to=c.effects.setTransition(child,k,d.to.x,child.to)}child.css(child.from);child.animate(child.to,b.duration,b.options.easing,function(){n&&c.effects.restore(child,h)})})}a.animate(a.to,{queue:false,duration:b.duration,easing:b.options.easing,complete:function(){a.to.opacity===0&&a.css("opacity",a.from.opacity);p=="hide"&&a.hide();c.effects.restore(a,
n?e:g);c.effects.removeWrapper(a);b.callback&&b.callback.apply(this,arguments);a.dequeue()}})})}})(jQuery);
;/*
 * jQuery UI Effects Shake 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Shake
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(d){d.effects.shake=function(a){return this.queue(function(){var b=d(this),j=["position","top","bottom","left","right"];d.effects.setMode(b,a.options.mode||"effect");var c=a.options.direction||"left",e=a.options.distance||20,l=a.options.times||3,f=a.duration||a.options.duration||140;d.effects.save(b,j);b.show();d.effects.createWrapper(b);var g=c=="up"||c=="down"?"top":"left",h=c=="up"||c=="left"?"pos":"neg";c={};var i={},k={};c[g]=(h=="pos"?"-=":"+=")+e;i[g]=(h=="pos"?"+=":"-=")+e*2;k[g]=
(h=="pos"?"-=":"+=")+e*2;b.animate(c,f,a.options.easing);for(e=1;e<l;e++)b.animate(i,f,a.options.easing).animate(k,f,a.options.easing);b.animate(i,f,a.options.easing).animate(c,f/2,a.options.easing,function(){d.effects.restore(b,j);d.effects.removeWrapper(b);a.callback&&a.callback.apply(this,arguments)});b.queue("fx",function(){b.dequeue()});b.dequeue()})}})(jQuery);
;/*
 * jQuery UI Effects Slide 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Slide
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(c){c.effects.slide=function(d){return this.queue(function(){var a=c(this),h=["position","top","bottom","left","right"],f=c.effects.setMode(a,d.options.mode||"show"),b=d.options.direction||"left";c.effects.save(a,h);a.show();c.effects.createWrapper(a).css({overflow:"hidden"});var g=b=="up"||b=="down"?"top":"left";b=b=="up"||b=="left"?"pos":"neg";var e=d.options.distance||(g=="top"?a.outerHeight({margin:true}):a.outerWidth({margin:true}));if(f=="show")a.css(g,b=="pos"?isNaN(e)?"-"+e:-e:e);
var i={};i[g]=(f=="show"?b=="pos"?"+=":"-=":b=="pos"?"-=":"+=")+e;a.animate(i,{queue:false,duration:d.duration,easing:d.options.easing,complete:function(){f=="hide"&&a.hide();c.effects.restore(a,h);c.effects.removeWrapper(a);d.callback&&d.callback.apply(this,arguments);a.dequeue()}})})}})(jQuery);
;/*
 * jQuery UI Effects Transfer 1.8.8
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Transfer
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(e){e.effects.transfer=function(a){return this.queue(function(){var b=e(this),c=e(a.options.to),d=c.offset();c={top:d.top,left:d.left,height:c.innerHeight(),width:c.innerWidth()};d=b.offset();var f=e('<div class="ui-effects-transfer"></div>').appendTo(document.body).addClass(a.options.className).css({top:d.top,left:d.left,height:b.innerHeight(),width:b.innerWidth(),position:"absolute"}).animate(c,a.duration,a.options.easing,function(){f.remove();a.callback&&a.callback.apply(b[0],arguments);
b.dequeue()})})}})(jQuery);
;/*
 * jQuery JSON Plugin
 * version: 2.1 (2009-08-14)
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * Brantley Harris wrote this plugin. It is based somewhat on the JSON.org 
 * website's http://www.json.org/json2.js, which proclaims:
 * "NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.", a sentiment that
 * I uphold.
 *
 * It is also influenced heavily by MochiKit's serializeJSON, which is 
 * copyrighted 2005 by Bob Ippolito.
 */
 
(function($) {
    /** jQuery.toJSON( json-serializble )
        Converts the given argument into a JSON respresentation.

        If an object has a "toJSON" function, that will be used to get the representation.
        Non-integer/string keys are skipped in the object, as are keys that point to a function.

        json-serializble:
            The *thing* to be converted.
     **/
    $.toJSON = function(o)
    {
        if (typeof(JSON) == 'object' && JSON.stringify)
            return JSON.stringify(o);
        
        var type = typeof(o);
    
        if (o === null)
            return "null";
    
        if (type == "undefined")
            return undefined;
        
        if (type == "number" || type == "boolean")
            return o + "";
    
        if (type == "string")
            return $.quoteString(o);
    
        if (type == 'object')
        {
            if (typeof o.toJSON == "function") 
                return $.toJSON( o.toJSON() );
            
            if (o.constructor === Date)
            {
                var month = o.getUTCMonth() + 1;
                if (month < 10) month = '0' + month;

                var day = o.getUTCDate();
                if (day < 10) day = '0' + day;

                var year = o.getUTCFullYear();
                
                var hours = o.getUTCHours();
                if (hours < 10) hours = '0' + hours;
                
                var minutes = o.getUTCMinutes();
                if (minutes < 10) minutes = '0' + minutes;
                
                var seconds = o.getUTCSeconds();
                if (seconds < 10) seconds = '0' + seconds;
                
                var milli = o.getUTCMilliseconds();
                if (milli < 100) milli = '0' + milli;
                if (milli < 10) milli = '0' + milli;

                return '"' + year + '-' + month + '-' + day + 'T' +
                             hours + ':' + minutes + ':' + seconds + 
                             '.' + milli + 'Z"'; 
            }

            if (o.constructor === Array) 
            {
                var ret = [];
                for (var i = 0; i < o.length; i++)
                    ret.push( $.toJSON(o[i]) || "null" );

                return "[" + ret.join(",") + "]";
            }
        
            var pairs = [];
            for (var k in o) {
                var name;
                var type = typeof k;

                if (type == "number")
                    name = '"' + k + '"';
                else if (type == "string")
                    name = $.quoteString(k);
                else
                    continue;  //skip non-string or number keys
            
                if (typeof o[k] == "function") 
                    continue;  //skip pairs where the value is a function.
            
                var val = $.toJSON(o[k]);
            
                pairs.push(name + ":" + val);
            }

            return "{" + pairs.join(", ") + "}";
        }
    };

    /** jQuery.evalJSON(src)
        Evaluates a given piece of json source.
     **/
    $.evalJSON = function(src)
    {
        if (typeof(JSON) == 'object' && JSON.parse)
            return JSON.parse(src);
        return eval("(" + src + ")");
    };
    
    /** jQuery.secureEvalJSON(src)
        Evals JSON in a way that is *more* secure.
    **/
    $.secureEvalJSON = function(src)
    {
        if (typeof(JSON) == 'object' && JSON.parse)
            return JSON.parse(src);
        
        var filtered = src;
        filtered = filtered.replace(/\\["\\\/bfnrtu]/g, '@');
        filtered = filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']');
        filtered = filtered.replace(/(?:^|:|,)(?:\s*\[)+/g, '');
        
        if (/^[\],:{}\s]*$/.test(filtered))
            return eval("(" + src + ")");
        else
            throw new SyntaxError("Error parsing JSON, source is not valid.");
    };

    /** jQuery.quoteString(string)
        Returns a string-repr of a string, escaping quotes intelligently.  
        Mostly a support function for toJSON.
    
        Examples:
            >>> jQuery.quoteString("apple")
            "apple"
        
            >>> jQuery.quoteString('"Where are we going?", she asked.')
            "\"Where are we going?\", she asked."
     **/
    $.quoteString = function(string)
    {
        if (string.match(_escapeable))
        {
            return '"' + string.replace(_escapeable, function (a) 
            {
                var c = _meta[a];
                if (typeof c === 'string') return c;
                c = a.charCodeAt();
                return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
            }) + '"';
        }
        return '"' + string + '"';
    };
    
    var _escapeable = /["\\\x00-\x1f\x7f-\x9f]/g;
    
    var _meta = {
        '\b': '\\b',
        '\t': '\\t',
        '\n': '\\n',
        '\f': '\\f',
        '\r': '\\r',
        '"' : '\\"',
        '\\': '\\\\'
    };
})(jQuery);
/*! Copyright (c) 2010 Brandon Aaron (http://brandonaaron.net)
 * Dual licensed under the MIT (MIT_LICENSE.txt)
 * and GPL Version 2 (GPL_LICENSE.txt) licenses.
 *
 * Version: 1.1.1
 * Requires jQuery 1.3+
 * Docs: http://docs.jquery.com/Plugins/livequery
 */

(function($) {

$.extend($.fn, {
	livequery: function(type, fn, fn2) {
		var self = this, q;

		// Handle different call patterns
		if ($.isFunction(type))
			fn2 = fn, fn = type, type = undefined;

		// See if Live Query already exists
		$.each( $.livequery.queries, function(i, query) {
			if ( self.selector == query.selector && self.context == query.context &&
				type == query.type && (!fn || fn.$lqguid == query.fn.$lqguid) && (!fn2 || fn2.$lqguid == query.fn2.$lqguid) )
					// Found the query, exit the each loop
					return (q = query) && false;
		});

		// Create new Live Query if it wasn't found
		q = q || new $.livequery(this.selector, this.context, type, fn, fn2);

		// Make sure it is running
		q.stopped = false;

		// Run it immediately for the first time
		q.run();

		// Contnue the chain
		return this;
	},

	expire: function(type, fn, fn2) {
		var self = this;

		// Handle different call patterns
		if ($.isFunction(type))
			fn2 = fn, fn = type, type = undefined;

		// Find the Live Query based on arguments and stop it
		$.each( $.livequery.queries, function(i, query) {
			if ( self.selector == query.selector && self.context == query.context &&
				(!type || type == query.type) && (!fn || fn.$lqguid == query.fn.$lqguid) && (!fn2 || fn2.$lqguid == query.fn2.$lqguid) && !this.stopped )
					$.livequery.stop(query.id);
		});

		// Continue the chain
		return this;
	}
});

$.livequery = function(selector, context, type, fn, fn2) {
	this.selector = selector;
	this.context  = context;
	this.type     = type;
	this.fn       = fn;
	this.fn2      = fn2;
	this.elements = [];
	this.stopped  = false;

	// The id is the index of the Live Query in $.livequery.queries
	this.id = $.livequery.queries.push(this)-1;

	// Mark the functions for matching later on
	fn.$lqguid = fn.$lqguid || $.livequery.guid++;
	if (fn2) fn2.$lqguid = fn2.$lqguid || $.livequery.guid++;

	// Return the Live Query
	return this;
};

$.livequery.prototype = {
	stop: function() {
		var query = this;

		if ( this.type )
			// Unbind all bound events
			this.elements.unbind(this.type, this.fn);
		else if (this.fn2)
			// Call the second function for all matched elements
			this.elements.each(function(i, el) {
				query.fn2.apply(el);
			});

		// Clear out matched elements
		this.elements = [];

		// Stop the Live Query from running until restarted
		this.stopped = true;
	},

	run: function() {
		// Short-circuit if stopped
		if ( this.stopped ) return;
		var query = this;

		var oEls = this.elements,
			els  = $(this.selector, this.context),
			nEls = els.not(oEls);

		// Set elements to the latest set of matched elements
		this.elements = els;

		if (this.type) {
			// Bind events to newly matched elements
			nEls.bind(this.type, this.fn);

			// Unbind events to elements no longer matched
			if (oEls.length > 0)
				$.each(oEls, function(i, el) {
					if ( $.inArray(el, els) < 0 )
						$.event.remove(el, query.type, query.fn);
				});
		}
		else {
			// Call the first function for newly matched elements
			nEls.each(function() {
				query.fn.apply(this);
			});

			// Call the second function for elements no longer matched
			if ( this.fn2 && oEls.length > 0 )
				$.each(oEls, function(i, el) {
					if ( $.inArray(el, els) < 0 )
						query.fn2.apply(el);
				});
		}
	}
};

$.extend($.livequery, {
	guid: 0,
	queries: [],
	queue: [],
	running: false,
	timeout: null,

	checkQueue: function() {
		if ( $.livequery.running && $.livequery.queue.length ) {
			var length = $.livequery.queue.length;
			// Run each Live Query currently in the queue
			while ( length-- )
				$.livequery.queries[ $.livequery.queue.shift() ].run();
		}
	},

	pause: function() {
		// Don't run anymore Live Queries until restarted
		$.livequery.running = false;
	},

	play: function() {
		// Restart Live Queries
		$.livequery.running = true;
		// Request a run of the Live Queries
		$.livequery.run();
	},

	registerPlugin: function() {
		$.each( arguments, function(i,n) {
			// Short-circuit if the method doesn't exist
			if (!$.fn[n]) return;

			// Save a reference to the original method
			var old = $.fn[n];

			// Create a new method
			$.fn[n] = function() {
				// Call the original method
				var r = old.apply(this, arguments);

				// Request a run of the Live Queries
				$.livequery.run();

				// Return the original methods result
				return r;
			}
		});
	},

	run: function(id) {
		if (id != undefined) {
			// Put the particular Live Query in the queue if it doesn't already exist
			if ( $.inArray(id, $.livequery.queue) < 0 )
				$.livequery.queue.push( id );
		}
		else
			// Put each Live Query in the queue if it doesn't already exist
			$.each( $.livequery.queries, function(id) {
				if ( $.inArray(id, $.livequery.queue) < 0 )
					$.livequery.queue.push( id );
			});

		// Clear timeout if it already exists
		if ($.livequery.timeout) clearTimeout($.livequery.timeout);
		// Create a timeout to check the queue and actually run the Live Queries
		$.livequery.timeout = setTimeout($.livequery.checkQueue, 20);
	},

	stop: function(id) {
		if (id != undefined)
			// Stop are particular Live Query
			$.livequery.queries[ id ].stop();
		else
			// Stop all Live Queries
			$.each( $.livequery.queries, function(id) {
				$.livequery.queries[ id ].stop();
			});
	}
});

// Register core DOM manipulation methods
$.livequery.registerPlugin('append', 'prepend', 'after', 'before', 'wrap', 'attr', 'removeAttr', 'addClass', 'removeClass', 'toggleClass', 'empty', 'remove', 'html');

// Run Live Queries when the Document is ready
$(function() { $.livequery.play(); });

})(jQuery);/* clickMenu - v0.1.6
 * Copyright (c) 2007 Roman Weich
 * http://p.sohei.org
 *
 * Changelog: 
 * v 0.1.6 - 2007-09-06
 *	-fix: having a link in the top-level menu would not open the menu but call the link instead
 * v 0.1.5 - 2007-07-07
 *	-change/fix: menu opening/closing now through simple show() and hide() calls - before fadeIn and fadeOut were used for which extra functions to stop a already running animation were created -> they were 
 *			buggy (not working with the interface plugin in jquery1.1.2 and not working with jquery1.1.3 at all) and now removed
 *	-change: removed option: fadeTime
 *	-change: now using the bgiframe plugin for adding iframes in ie6 when available
 * v 0.1.4 - 2007-03-20
 *	-fix: the default options were overwritten by the context related options
 *	-fix: hiding a submenu all hover- and click-events were unbound, even the ones not defined in this plugin - unbinding should work now
 * v 0.1.3 - 2007-03-13
 *	-fix: some display problems ie had when no width was set on the submenu, so on ie the width for each submenu will be explicitely set
 *	-fix: the fix to the ie-width-problem is a fix to the "ie does not support css min-width stuff" problem too which displayed some submenus too narrow (it looked just not right)
 *	-fix: some bugs, when user the was too fast with the mouse
 * v 0.1.2 - 2007-03-11
 *	-change: made a lot changes in the traversing routines to speed things up (having better memory usage now as well)
 *	-change: added $.fn.clickMenu.setDefaults() for setting global defaults
 *	-fix: hoverbug when a main menu item had no submenu
 *	-fix: some bugs i found while rewriting most of the stuff
 * v 0.1.1 - 2007-03-04
 *	-change: the width of the submenus is no longer fixed, its set in the plugin now
 *	-change: the submenu-arrow is now an img, not the background-img of the list element - that allows better positioning, and background-changes on hover (you have to set the image through the arrowSrc option)
 *	-fix: clicking on a clickMenu while another was already open, didn't close the open one
 *	-change: clicking on the open main menu item will close it
 *	-fix: on an open menu moving the mouse to a main menu item and moving it fastly elsewere hid the whole menu
 * v 0.1.0 - 2007-03-03
 */

(function($)
{
	var defaults = {
		onClick: function(){
			$(this).find('>a').each(function(){
				if ( this.href )
				{
					window.location = this.href;
				}
			});
		},
		arrowSrc: '',
		subDelay: 300,
		mainDelay: 10
	};

	$.fn.clickMenu = function(options) 
	{
		var shown = false;
		var liOffset = ( ($.browser.msie) ? 4 : 2 );

		var settings = $.extend({}, defaults, options);

		var hideDIV = function(div, delay)
		{
			//a timer running to show the div?
			if ( div.timer && !div.isVisible )
			{
				clearTimeout(div.timer);
			}
			else if (div.timer)
			{
				return; //hide-timer already running
			}
			if ( div.isVisible )
			{
				div.timer = setTimeout(function()
				{
					//remove events
					$(getAllChilds(getOneChild(div, 'UL'), 'LI')).unbind('mouseover', liHoverIn).unbind('mouseout', liHoverOut).unbind('click', settings.onClick);
					//hide it
					$(div).hide();
					div.isVisible = false;
					div.timer = null;
				}, delay);
			}
		};

		var showDIV = function(div, delay)
		{
			if ( div.timer )
			{
				clearTimeout(div.timer);
			}
			if ( !div.isVisible )
			{
				div.timer = setTimeout(function()
				{
					//check if the mouse is still over the parent item - if not dont show the submenu
					if ( !checkClass(div.parentNode, 'hover') )
					{
						return;
					}
					//assign events to all div>ul>li-elements
					$(getAllChilds(getOneChild(div, 'UL'), 'LI')).mouseover(liHoverIn).mouseout(liHoverOut).click(settings.onClick);
					//positioning
					if ( !checkClass(div.parentNode, 'main') )
					{
						$(div).css('left', div.parentNode.offsetWidth - liOffset);
					}
					//show it
					div.isVisible = true; //we use this over :visible to speed up traversing
					$(div).show();
					if ( $.browser.msie ) //fixing a display-bug in ie6 and adding min-width
					{
						var cW = $(getOneChild(div, 'UL')).width();
						if ( cW < 100 )
						{
							cW = 100;
						}
						$(div).css('width', cW);
					}
					div.timer = null;
				}, delay);
			}
		};

		//same as hover.handlehover in jquery - just can't use hover() directly - need the ability to unbind only the one hover event
		var testHandleHover = function(e)
		{
			// Check if mouse(over|out) are still within the same parent element
			var p = (e.type == "mouseover" ? e.fromElement : e.toElement) || e.relatedTarget;
			// Traverse up the tree
			while ( p && p != this )
			{
				try
				{ 
					p = p.parentNode;
				}
				catch(e)
				{ 
					p = this;
				}
			}
			// If we actually just moused on to a sub-element, ignore it
			if ( p == this )
			{
				return false;
			}
			return true;
		};
		
		var mainHoverIn = function(e)
		{
			//no need to test e.target==this, as no child has the same event binded
			//its possible, that a main menu item still has hover (if it has no submenu) - thus remove it
			var lis = getAllChilds(this.parentNode, 'LI');
			var pattern = new RegExp("(^|\\s)hover(\\s|$)");
			for (var i = 0; i < lis.length; i++)
			{
				if ( pattern.test(lis[i].className) )
				{
					$(lis[i]).removeClass('hover');
				}
			}
			$(this).addClass('hover');
			if ( shown )
			{
				hoverIn(this, settings.mainDelay);
			}
		};

		var liHoverIn = function(e)
		{
			if ( !testHandleHover(e) )
			{
				return false;
			}
			if ( e.target != this )
			{
				//look whether the target is a direct child of this (maybe an image)
				if ( !isChild(this, e.target) )
				{
					return;
				}
			}
			hoverIn(this, settings.subDelay);
		};

		var hoverIn = function(li, delay)
		{
			var innerDiv = getOneChild(li, 'DIV');
			//stop running timers from the other menus on the same level - a little faster than $('>*>div', li.parentNode)
			var n = li.parentNode.firstChild;
			for ( ; n; n = n.nextSibling ) 
			{
				if ( n.nodeType == 1 && n.nodeName.toUpperCase() == 'LI' )
				{
					var div = getOneChild(n, 'DIV');
					if ( div && div.timer && !div.isVisible ) //clear show-div timer
					{
						clearTimeout(div.timer);
						div.timer = null;
					}
				}
			}
			//is there a timer running to hide one of the parent divs? stop it
			var pNode = li.parentNode;
			for ( ; pNode; pNode = pNode.parentNode ) 
			{
				if ( pNode.nodeType == 1 && pNode.nodeName.toUpperCase() == 'DIV' )
				{
					if (pNode.timer)
					{
						clearTimeout(pNode.timer);
						pNode.timer = null;
						$(pNode.parentNode).addClass('hover');
					}
				}
			}
			//highlight the current element
			$(li).addClass('hover');
			//is the submenu already visible?
			if ( innerDiv && innerDiv.isVisible )
			{
				//hide-timer running?
				if ( innerDiv.timer )
				{
					clearTimeout(innerDiv.timer);
					innerDiv.timer = null;
				}
				else
				{
					return;
				}
			}
			//hide all open menus on the same level and below and unhighlight the li item (but not the current submenu!)
			$(li.parentNode.getElementsByTagName('DIV')).each(function(){
				if ( this != innerDiv && this.isVisible )
				{
					hideDIV(this, delay);
					$(this.parentNode).removeClass('hover');
				}
			});
			//show the submenu, if there is one
			if ( innerDiv )
			{
				showDIV(innerDiv, delay);
			}
		};

		var liHoverOut = function(e)
		{
			if ( !testHandleHover(e) )
			{
				return false;
			}
			if ( e.target != this )
			{
				if ( !isChild(this, e.target) ) //return only if the target is no direct child of this
				{
					return;
				}
			}
			//remove the hover from the submenu item, if the mouse is hovering out of the menu (this is only for the last open (levelwise) (sub-)menu)
			var div = getOneChild(this, 'DIV');
			if ( !div )
			{
				$(this).removeClass('hover');
			}
			else 
			{
				if ( !div.isVisible )
				{
					$(this).removeClass('hover');
				}
			}
		};

		var mainHoverOut = function(e)
		{
			//no need to test e.target==this, as no child has the same event binded
			//remove hover
			var div = getOneChild(this, 'DIV');
			var relTarget = e.relatedTarget || e.toElement; //this is undefined sometimes (e.g. when the mouse moves out of the window), so dont remove hover then
			var p;
			if ( !shown )
			{
				$(this).removeClass('hover');
			}
			else if ( !div && relTarget ) //menuitem has no submenu, so dont remove the hover if the mouse goes outside the menu
			{
				p = findParentWithClass(e.target, 'UL', 'clickMenu');
				if ( p.contains(relTarget))
				{
					$(this).removeClass('hover');
				}
			}
			else if ( relTarget )
			{
				//remove hover only when moving to anywhere inside the clickmenu
				p = findParentWithClass(e.target, 'UL', 'clickMenu');
				if ( !div.isVisible && (p.contains(relTarget)) )
				{
					$(this).removeClass('hover');
				}
			}
		};

		var mainClick = function()
		{
			var div = getOneChild(this, 'DIV');
			if ( div && div.isVisible ) //clicked on an open main-menu-item
			{
				clean();
				$(this).addClass('hover');
			}
			else
			{
				hoverIn(this, settings.mainDelay);
				shown = true;
				$(document).bind('mousedown', checkMouse);
			}
			return false;
		};

		var checkMouse = function(e)
		{
			//is the mouse inside a clickmenu? if yes, is it an open (the current) one?
			var vis = false;
			var cm = findParentWithClass(e.target, 'UL', 'clickMenu');
			if ( cm )
			{
				$(cm.getElementsByTagName('DIV')).each(function(){
					if ( this.isVisible )
					{
						vis = true;
					}
				});
			}
			if ( !vis )
			{
				clean();
			}
		};

		var clean = function()
		{
			//remove timeout and hide the divs
			$('ul.clickMenu div.outerbox').each(function(){
				if ( this.timer )
				{
					clearTimeout(this.timer);
					this.timer = null;
				}
				if ( this.isVisible )
				{
					$(this).hide();
					this.isVisible = false;
				}
			});
			$('ul.clickMenu li').removeClass('hover');
			//remove events
			$('ul.clickMenu>li li').unbind('mouseover', liHoverIn).unbind('mouseout', liHoverOut).unbind('click', settings.onClick);
			$(document).unbind('mousedown', checkMouse);
			shown = false;
		};

		var getOneChild = function(elem, name)
		{
			if ( !elem )
			{
				return null;
			}
			var n = elem.firstChild;
			for ( ; n; n = n.nextSibling ) 
			{
				if ( n.nodeType == 1 && n.nodeName.toUpperCase() == name )
				{
					return n;
				}
			}
			return null;
		};

		var getAllChilds = function(elem, name)
		{
			if ( !elem )
			{
				return [];
			}
			var r = [];
			var n = elem.firstChild;
			for ( ; n; n = n.nextSibling ) 
			{
				if ( n.nodeType == 1 && n.nodeName.toUpperCase() == name )
				{
					r[r.length] = n;
				}
			}
			return r;
		};

		var findParentWithClass = function(elem, searchTag, searchClass)
		{
			var pNode = elem.parentNode;
			var pattern = new RegExp("(^|\\s)" + searchClass + "(\\s|$)");
			for ( ; pNode; pNode = pNode.parentNode )
			{
				if ( pNode.nodeType == 1 && pNode.nodeName.toUpperCase() == searchTag && pattern.test(pNode.className) )
				{
					return pNode;
				}
			}
			return null;
		};
		
		var checkClass = function(elem, searchClass)
		{
			var pattern = new RegExp("(^|\\s)" + searchClass + "(\\s|$)");
			if ( pattern.test(elem.className) )
			{
				return true;
			}
			return false;
		};
		
		var isChild = function(elem, childElem)
		{
			var n = elem.firstChild;
			for ( ; n; n = n.nextSibling ) 
			{
				if ( n == childElem )
				{
					return true;
				}
			}
			return false;
		};

	    return this.each(function()
		{
			//add .contains() to mozilla - http://www.quirksmode.org/blog/archives/2006/01/contains_for_mo.html
			if (window.Node && Node.prototype && !Node.prototype.contains)
			{
				Node.prototype.contains = function(arg) 
				{
					return !!(this.compareDocumentPosition(arg) & 16);
				};
			}
			//add class
			if ( !checkClass(this, 'clickMenu') )
			{
				$(this).addClass('clickMenu');
			}
			//add shadows
			$('ul', this).shadowBox();
			//ie6? - add iframes
			if ( $.browser.msie && (!$.browser.version || parseInt($.browser.version) <= 6) )
			{
				if ( $.fn.bgiframe )
				{
					$('div.outerbox', this).bgiframe();
				}
				else
				{
					/* thanks to Mark Gibson - http://www.nabble.com/forum/ViewPost.jtp?post=6504414&framed=y */
					$('div.outerbox', this).append('<iframe style="display:block;position:absolute;top:0;left:0;z-index:-1;filter:mask();' + 
									'width:expression(this.parentNode.offsetWidth);height:expression(this.parentNode.offsetHeight)"/>');
				}
			}
			//assign events
			$(this).bind('closemenu', function(){clean();}); //assign closemenu-event, through wich the menu can be closed from outside the plugin
			//add click event handling, if there are any elements inside the main menu
			var liElems = getAllChilds(this, 'LI');
			for ( var j = 0; j < liElems.length; j++ )
			{
				if ( getOneChild(getOneChild(getOneChild(liElems[j], 'DIV'), 'UL'), 'LI') ) // >div>ul>li
				{
					$(liElems[j]).click(mainClick);
				}
			}
			//add hover event handling and assign classes
			$(liElems).hover(mainHoverIn, mainHoverOut).addClass('main').find('>div').addClass('inner');
			//add the little arrow before each submenu
			if ( settings.arrowSrc )
			{
				$('div.inner div.outerbox', this).before('<img src="' + settings.arrowSrc + '" class="liArrow" />');
			}

			//the floating list elements are destroying the layout..so make it nice again..
            $(this).wrap('<div class="cmDiv"></div>').after('<div style="clear: both; visibility: hidden;"></div>');
	    });
	};
	$.fn.clickMenu.setDefaults = function(o)
	{
		$.extend(defaults, o);
	};
})(jQuery);

(function($)
{
	$.fn.shadowBox = function() {
	    return this.each(function()
		{
			var outer = $('<div class="outerbox"></div>').get(0);
			if ( $(this).css('position') == 'absolute' )
			{
				//if the child(this) is positioned abolute, we have to use relative positioning and shrink the outerbox accordingly to the innerbox
				$(outer).css({position:'relative', width:this.offsetWidth, height:this.offsetHeight});
			}
			else
			{
				//shrink the outerbox
				$(outer).css('position', 'absolute');
			}
			//add the boxes
			$(this).addClass('innerBox').wrap(outer).
					before('<div class="shadowbox1"></div><div class="shadowbox2"></div><div class="shadowbox3"></div>');
	    });
	};
})(jQuery);/*
 * SimpleModal 1.3 - jQuery Plugin
 * http://www.ericmmartin.com/projects/simplemodal/
 * Copyright (c) 2009 Eric Martin
 * Dual licensed under the MIT and GPL licenses
 * Revision: $Id: jquery.simplemodal.js 205 2009-06-12 13:29:21Z emartin24 $
 */
;(function($){var ie6=$.browser.msie&&parseInt($.browser.version)==6&&typeof window['XMLHttpRequest']!="object",ieQuirks=null,w=[];$.modal=function(data,options){return $.modal.impl.init(data,options);};$.modal.close=function(){$.modal.impl.close();};$.fn.modal=function(options){return $.modal.impl.init(this,options);};$.modal.defaults={appendTo:'body',focus:true,opacity:50,overlayId:'simplemodal-overlay',overlayCss:{},containerId:'simplemodal-container',containerCss:{},dataId:'simplemodal-data',dataCss:{},minHeight:200,minWidth:300,maxHeight:null,maxWidth:null,autoResize:false,zIndex:1000,close:true,closeHTML:'<a class="modalCloseImg" title="Close"></a>',closeClass:'simplemodal-close',escClose:true,overlayClose:false,position:null,persist:false,onOpen:null,onShow:null,onClose:null};$.modal.impl={opts:null,dialog:{},init:function(data,options){if(this.dialog.data){return false;}ieQuirks=$.browser.msie&&!$.boxModel;this.opts=$.extend({},$.modal.defaults,options);this.zIndex=this.opts.zIndex;this.occb=false;if(typeof data=='object'){data=data instanceof jQuery?data:$(data);if(data.parent().parent().size()>0){this.dialog.parentNode=data.parent();if(!this.opts.persist){this.dialog.orig=data.clone(true);}}}else if(typeof data=='string'||typeof data=='number'){data=$('<div/>').html(data);}else{alert('SimpleModal Error: Unsupported data type: '+typeof data);return false;}this.create(data);data=null;this.open();if($.isFunction(this.opts.onShow)){this.opts.onShow.apply(this,[this.dialog]);}return this;},create:function(data){w=this.getDimensions();if(ie6){this.dialog.iframe=$('<iframe src="javascript:false;"/>').css($.extend(this.opts.iframeCss,{display:'none',opacity:0,position:'fixed',height:w[0],width:w[1],zIndex:this.opts.zIndex,top:0,left:0})).appendTo(this.opts.appendTo);}this.dialog.overlay=$('<div/>').attr('id',this.opts.overlayId).addClass('simplemodal-overlay').css($.extend(this.opts.overlayCss,{display:'none',opacity:this.opts.opacity/100,height:w[0],width:w[1],position:'fixed',left:0,top:0,zIndex:this.opts.zIndex+1})).appendTo(this.opts.appendTo);this.dialog.container=$('<div/>').attr('id',this.opts.containerId).addClass('simplemodal-container').css($.extend(this.opts.containerCss,{display:'none',position:'fixed',zIndex:this.opts.zIndex+2})).append(this.opts.close&&this.opts.closeHTML?$(this.opts.closeHTML).addClass(this.opts.closeClass):'').appendTo(this.opts.appendTo);this.dialog.wrap=$('<div/>').attr('tabIndex',-1).addClass('simplemodal-wrap').css({height:'100%',outline:0,width:'100%'}).appendTo(this.dialog.container);this.dialog.data=data.attr('id',data.attr('id')||this.opts.dataId).addClass('simplemodal-data').css($.extend(this.opts.dataCss,{display:'none'}));data=null;this.setContainerDimensions();this.dialog.data.appendTo(this.dialog.wrap);if(ie6||ieQuirks){this.fixIE();}},bindEvents:function(){var self=this;$('.'+self.opts.closeClass).bind('click.simplemodal',function(e){e.preventDefault();self.close();});if(self.opts.close&&self.opts.overlayClose){self.dialog.overlay.bind('click.simplemodal',function(e){e.preventDefault();self.close();});}$(document).bind('keydown.simplemodal',function(e){if(self.opts.focus&&e.keyCode==9){self.watchTab(e);}else if((self.opts.close&&self.opts.escClose)&&e.keyCode==27){e.preventDefault();self.close();}});$(window).bind('resize.simplemodal',function(){w=self.getDimensions();self.opts.autoResize?self.setContainerDimensions():self.setPosition();if(ie6||ieQuirks){self.fixIE();}else{self.dialog.iframe&&self.dialog.iframe.css({height:w[0],width:w[1]});self.dialog.overlay.css({height:w[0],width:w[1]});}});},unbindEvents:function(){$('.'+this.opts.closeClass).unbind('click.simplemodal');$(document).unbind('keydown.simplemodal');$(window).unbind('resize.simplemodal');this.dialog.overlay.unbind('click.simplemodal');},fixIE:function(){var p=this.opts.position;$.each([this.dialog.iframe||null,this.dialog.overlay,this.dialog.container],function(i,el){if(el){var bch='document.body.clientHeight',bcw='document.body.clientWidth',bsh='document.body.scrollHeight',bsl='document.body.scrollLeft',bst='document.body.scrollTop',bsw='document.body.scrollWidth',ch='document.documentElement.clientHeight',cw='document.documentElement.clientWidth',sl='document.documentElement.scrollLeft',st='document.documentElement.scrollTop',s=el[0].style;s.position='absolute';if(i<2){s.removeExpression('height');s.removeExpression('width');s.setExpression('height',''+bsh+' > '+bch+' ? '+bsh+' : '+bch+' + "px"');s.setExpression('width',''+bsw+' > '+bcw+' ? '+bsw+' : '+bcw+' + "px"');}else{var te,le;if(p&&p.constructor==Array){var top=p[0]?typeof p[0]=='number'?p[0].toString():p[0].replace(/px/,''):el.css('top').replace(/px/,'');te=top.indexOf('%')==-1?top+' + (t = '+st+' ? '+st+' : '+bst+') + "px"':parseInt(top.replace(/%/,''))+' * (('+ch+' || '+bch+') / 100) + (t = '+st+' ? '+st+' : '+bst+') + "px"';if(p[1]){var left=typeof p[1]=='number'?p[1].toString():p[1].replace(/px/,'');le=left.indexOf('%')==-1?left+' + (t = '+sl+' ? '+sl+' : '+bsl+') + "px"':parseInt(left.replace(/%/,''))+' * (('+cw+' || '+bcw+') / 100) + (t = '+sl+' ? '+sl+' : '+bsl+') + "px"';}}else{te='('+ch+' || '+bch+') / 2 - (this.offsetHeight / 2) + (t = '+st+' ? '+st+' : '+bst+') + "px"';le='('+cw+' || '+bcw+') / 2 - (this.offsetWidth / 2) + (t = '+sl+' ? '+sl+' : '+bsl+') + "px"';}s.removeExpression('top');s.removeExpression('left');s.setExpression('top',te);s.setExpression('left',le);}}});},focus:function(pos){var self=this,p=pos||'first';var input=$(':input:enabled:visible:'+p,self.dialog.wrap);input.length>0?input.focus():self.dialog.wrap.focus();},getDimensions:function(){var el=$(window);var h=$.browser.opera&&$.browser.version>'9.5'&&$.fn.jquery<='1.2.6'?document.documentElement['clientHeight']:$.browser.opera&&$.browser.version<'9.5'&&$.fn.jquery>'1.2.6'?window.innerHeight:el.height();return[h,el.width()];},getVal:function(v){return v=='auto'?0:parseInt(v.replace(/px/,''));},setContainerDimensions:function(){var ch=this.getVal(this.dialog.container.css('height')),cw=this.dialog.container.width(),dh=this.dialog.data.height(),dw=this.dialog.data.width();var mh=this.opts.maxHeight&&this.opts.maxHeight<w[0]?this.opts.maxHeight:w[0],mw=this.opts.maxWidth&&this.opts.maxWidth<w[1]?this.opts.maxWidth:w[1];if(!ch){if(!dh){ch=this.opts.minHeight;}else{if(dh>mh){ch=mh;}else if(dh<this.opts.minHeight){ch=this.opts.minHeight;}else{ch=dh;}}}else{ch=ch>mh?mh:ch;}if(!cw){if(!dw){cw=this.opts.minWidth;}else{if(dw>mw){cw=mw;}else if(dw<this.opts.minWidth){cw=this.opts.minWidth;}else{cw=dw;}}}else{cw=cw>mw?mw:cw;}this.dialog.container.css({height:ch,width:cw});if(dh>ch||dw>cw){this.dialog.wrap.css({overflow:'auto'});}this.setPosition();},setPosition:function(){var top,left,hc=(w[0]/2)-((this.dialog.container.height()||this.dialog.data.height())/2),vc=(w[1]/2)-((this.dialog.container.width()||this.dialog.data.width())/2);if(this.opts.position&&this.opts.position.constructor==Array){top=this.opts.position[0]||hc;left=this.opts.position[1]||vc;}else{top=hc;left=vc;}this.dialog.container.css({left:left,top:top});},watchTab:function(e){var self=this;if($(e.target).parents('.simplemodal-container').length>0){self.inputs=$(':input:enabled:visible:first, :input:enabled:visible:last',self.dialog.data);if(!e.shiftKey&&e.target==self.inputs[self.inputs.length-1]||e.shiftKey&&e.target==self.inputs[0]||self.inputs.length==0){e.preventDefault();var pos=e.shiftKey?'last':'first';setTimeout(function(){self.focus(pos);},10);}}else{e.preventDefault();setTimeout(function(){self.focus();},10);}},open:function(){this.dialog.iframe&&this.dialog.iframe.show();if($.isFunction(this.opts.onOpen)){this.opts.onOpen.apply(this,[this.dialog]);}else{this.dialog.overlay.show();this.dialog.container.show();this.dialog.data.show();}this.focus();this.bindEvents();},close:function(){if(!this.dialog.data){return false;}this.unbindEvents();if($.isFunction(this.opts.onClose)&&!this.occb){this.occb=true;this.opts.onClose.apply(this,[this.dialog]);}else{if(this.dialog.parentNode){if(this.opts.persist){this.dialog.data.hide().appendTo(this.dialog.parentNode);}else{this.dialog.data.hide().remove();this.dialog.orig.appendTo(this.dialog.parentNode);}}else{this.dialog.data.hide().remove();}this.dialog.container.hide().remove();this.dialog.overlay.hide().remove();this.dialog.iframe&&this.dialog.iframe.hide().remove();this.dialog={};}}};})(jQuery);/*
 * 
 * TableSorter 2.0 - Client-side table sorting with ease!
 * Version 2.0.3
 * @requires jQuery v1.2.3
 * 
 * Copyright (c) 2007 Christian Bach
 * Examples and docs at: http://tablesorter.com
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * 
 */
/**
 *
 * @description Create a sortable table with multi-column sorting capabilitys
 * 
 * @example $('table').tablesorter();
 * @desc Create a simple tablesorter interface.
 *
 * @example $('table').tablesorter({ sortList:[[0,0],[1,0]] });
 * @desc Create a tablesorter interface and sort on the first and secound column in ascending order.
 * 
 * @example $('table').tablesorter({ headers: { 0: { sorter: false}, 1: {sorter: false} } });
 * @desc Create a tablesorter interface and disableing the first and secound column headers.
 * 
 * @example $('table').tablesorter({ 0: {sorter:"integer"}, 1: {sorter:"currency"} });
 * @desc Create a tablesorter interface and set a column parser for the first and secound column.
 * 
 * 
 * @param Object settings An object literal containing key/value pairs to provide optional settings.
 * 
 * @option String cssHeader (optional) 			A string of the class name to be appended to sortable tr elements in the thead of the table. 
 * 												Default value: "header"
 * 
 * @option String cssAsc (optional) 			A string of the class name to be appended to sortable tr elements in the thead on a ascending sort. 
 * 												Default value: "headerSortUp"
 * 
 * @option String cssDesc (optional) 			A string of the class name to be appended to sortable tr elements in the thead on a descending sort. 
 * 												Default value: "headerSortDown"
 * 
 * @option String sortInitialOrder (optional) 	A string of the inital sorting order can be asc or desc. 
 * 												Default value: "asc"
 * 
 * @option String sortMultisortKey (optional) 	A string of the multi-column sort key. 
 * 												Default value: "shiftKey"
 * 
 * @option String textExtraction (optional) 	A string of the text-extraction method to use. 
 * 												For complex html structures inside td cell set this option to "complex", 
 * 												on large tables the complex option can be slow. 
 * 												Default value: "simple"
 * 
 * @option Object headers (optional) 			An array containing the forces sorting rules. 
 * 												This option let's you specify a default sorting rule. 
 * 												Default value: null
 * 
 * @option Array sortList (optional) 			An array containing the forces sorting rules. 
 * 												This option let's you specify a default sorting rule. 
 * 												Default value: null
 * 
 * @option Array sortForce (optional) 			An array containing forced sorting rules. 
 * 												This option let's you specify a default sorting rule, which is prepended to user-selected rules.
 * 												Default value: null
 *  
  * @option Array sortAppend (optional) 			An array containing forced sorting rules. 
 * 												This option let's you specify a default sorting rule, which is appended to user-selected rules.
 * 												Default value: null
 * 
 * @option Boolean widthFixed (optional) 		Boolean flag indicating if tablesorter should apply fixed widths to the table columns.
 * 												This is usefull when using the pager companion plugin.
 * 												This options requires the dimension jquery plugin.
 * 												Default value: false
 *
 * @option Boolean cancelSelection (optional) 	Boolean flag indicating if tablesorter should cancel selection of the table headers text.
 * 												Default value: true
 *
 * @option Boolean debug (optional) 			Boolean flag indicating if tablesorter should display debuging information usefull for development.
 *
 * @type jQuery
 *
 * @name tablesorter
 * 
 * @cat Plugins/Tablesorter
 * 
 * @author Christian Bach/christian.bach@polyester.se
 */

(function($) {
	$.extend({
		tablesorter: new function() {
			
			var parsers = [], widgets = [];
			
			this.defaults = {
				cssHeader: "header",
				cssAsc: "headerSortUp",
				cssDesc: "headerSortDown",
				sortInitialOrder: "asc",
				sortMultiSortKey: "shiftKey",
				sortForce: null,
				sortAppend: null,
				textExtraction: "simple",
				parsers: {}, 
				widgets: [],		
				widgetZebra: {css: ["even","odd"]},
				headers: {},
				widthFixed: false,
				cancelSelection: true,
				sortList: [],
				headerList: [],
				dateFormat: "us",
				decimal: '.',
				debug: false
			};
			
			/* debuging utils */
			function benchmark(s,d) {
				log(s + "," + (new Date().getTime() - d.getTime()) + "ms");
			}
			
			this.benchmark = benchmark;
			
			function log(s) {
				if (typeof console != "undefined" && typeof console.debug != "undefined") {
					console.log(s);
				} else {
					alert(s);
				}
			}
						
			/* parsers utils */
			function buildParserCache(table,$headers) {
				
				if(table.config.debug) { var parsersDebug = ""; }
				
				var rows = table.tBodies[0].rows;
				
				if(table.tBodies[0].rows[0]) {

					var list = [], cells = rows[0].cells, l = cells.length;
					
					for (var i=0;i < l; i++) {
						var p = false;
						
						if($.metadata && ($($headers[i]).metadata() && $($headers[i]).metadata().sorter)  ) {
						
							p = getParserById($($headers[i]).metadata().sorter);	
						
						} else if((table.config.headers[i] && table.config.headers[i].sorter)) {
	
							p = getParserById(table.config.headers[i].sorter);
						}
						if(!p) {
							p = detectParserForColumn(table,cells[i]);
						}
	
						if(table.config.debug) { parsersDebug += "column:" + i + " parser:" +p.id + "\n"; }
	
						list.push(p);
					}
				}
				
				if(table.config.debug) { log(parsersDebug); }

				return list;
			};
			
			function detectParserForColumn(table,node) {
				var l = parsers.length;
				for(var i=1; i < l; i++) {
					if(parsers[i].is($.trim(getElementText(table.config,node)),table,node)) {
						return parsers[i];
					}
				}
				// 0 is always the generic parser (text)
				return parsers[0];
			}
			
			function getParserById(name) {
				var l = parsers.length;
				for(var i=0; i < l; i++) {
					if(parsers[i].id.toLowerCase() == name.toLowerCase()) {	
						return parsers[i];
					}
				}
				return false;
			}
			
			/* utils */
			function buildCache(table) {
				
				if(table.config.debug) { var cacheTime = new Date(); }
				
				
				var totalRows = (table.tBodies[0] && table.tBodies[0].rows.length) || 0,
					totalCells = (table.tBodies[0].rows[0] && table.tBodies[0].rows[0].cells.length) || 0,
					parsers = table.config.parsers, 
					cache = {row: [], normalized: []};
				
					for (var i=0;i < totalRows; ++i) {
					
						/** Add the table data to main data array */
						var c = table.tBodies[0].rows[i], cols = [];
					
						cache.row.push($(c));
						
						for(var j=0; j < totalCells; ++j) {
							cols.push(parsers[j].format(getElementText(table.config,c.cells[j]),table,c.cells[j]));	
						}
												
						cols.push(i); // add position for rowCache
						cache.normalized.push(cols);
						cols = null;
					};
				
				if(table.config.debug) { benchmark("Building cache for " + totalRows + " rows:", cacheTime); }
				
				return cache;
			};
			
			function getElementText(config,node) {
				
				if(!node) return "";
								
				var t = "";
				
				if(config.textExtraction == "simple") {
					if(node.childNodes[0] && node.childNodes[0].hasChildNodes()) {
						t = node.childNodes[0].innerHTML;
					} else {
						t = node.innerHTML;
					}
				} else {
					if(typeof(config.textExtraction) == "function") {
						t = config.textExtraction(node);
					} else { 
						t = $(node).text();
					}	
				}
				return t;
			}
			
			function appendToTable(table,cache) {
				
				if(table.config.debug) {var appendTime = new Date()}
				
				var c = cache, 
					r = c.row, 
					n= c.normalized, 
					totalRows = n.length, 
					checkCell = (n[0].length-1), 
					tableBody = $(table.tBodies[0]),
					rows = [];
				
				for (var i=0;i < totalRows; i++) {
					rows.push(r[n[i][checkCell]]);	
					if(!table.config.appender) {
						
						var o = r[n[i][checkCell]];
						var l = o.length;
						for(var j=0; j < l; j++) {
							
							tableBody[0].appendChild(o[j]);
						
						}
						
						//tableBody.append(r[n[i][checkCell]]);
					}
				}	
				
				if(table.config.appender) {
				
					table.config.appender(table,rows);	
				}
				
				rows = null;
				
				if(table.config.debug) { benchmark("Rebuilt table:", appendTime); }
								
				//apply table widgets
				applyWidget(table);
				
				// trigger sortend
				setTimeout(function() {
					$(table).trigger("sortEnd");	
				},0);
				
			};
			
			function buildHeaders(table) {
				
				if(table.config.debug) { var time = new Date(); }
				
				var meta = ($.metadata) ? true : false, tableHeadersRows = [];
			
				for(var i = 0; i < table.tHead.rows.length; i++) { tableHeadersRows[i]=0; };
				
				$tableHeaders = $("thead th",table);
		
				$tableHeaders.each(function(index) {
							
					this.count = 0;
					this.column = index;
					this.order = formatSortingOrder(table.config.sortInitialOrder);
					
					if(checkHeaderMetadata(this) || checkHeaderOptions(table,index)) this.sortDisabled = true;
					
					if(!this.sortDisabled) {
						$(this).addClass(table.config.cssHeader);
					}
					
					// add cell to headerList
					table.config.headerList[index]= this;
				});
				
				if(table.config.debug) { benchmark("Built headers:", time); log($tableHeaders); }
				
				return $tableHeaders;
				
			};
						
		   	function checkCellColSpan(table, rows, row) {
                var arr = [], r = table.tHead.rows, c = r[row].cells;
				
				for(var i=0; i < c.length; i++) {
					var cell = c[i];
					
					if ( cell.colSpan > 1) { 
						arr = arr.concat(checkCellColSpan(table, headerArr,row++));
					} else  {
						if(table.tHead.length == 1 || (cell.rowSpan > 1 || !r[row+1])) {
							arr.push(cell);
						}
						//headerArr[row] = (i+row);
					}
				}
				return arr;
			};
			
			function checkHeaderMetadata(cell) {
				if(($.metadata) && ($(cell).metadata().sorter === false)) { return true; };
				return false;
			}
			
			function checkHeaderOptions(table,i) {	
				if((table.config.headers[i]) && (table.config.headers[i].sorter === false)) { return true; };
				return false;
			}
			
			function applyWidget(table) {
				var c = table.config.widgets;
				var l = c.length;
				for(var i=0; i < l; i++) {
					
					getWidgetById(c[i]).format(table);
				}
				
			}
			
			function getWidgetById(name) {
				var l = widgets.length;
				for(var i=0; i < l; i++) {
					if(widgets[i].id.toLowerCase() == name.toLowerCase() ) {
						return widgets[i]; 
					}
				}
			};
			
			function formatSortingOrder(v) {
				
				if(typeof(v) != "Number") {
					i = (v.toLowerCase() == "desc") ? 1 : 0;
				} else {
					i = (v == (0 || 1)) ? v : 0;
				}
				return i;
			}
			
			function isValueInArray(v, a) {
				var l = a.length;
				for(var i=0; i < l; i++) {
					if(a[i][0] == v) {
						return true;	
					}
				}
				return false;
			}
				
			function setHeadersCss(table,$headers, list, css) {
				// remove all header information
				$headers.removeClass(css[0]).removeClass(css[1]);
				
				var h = [];
				$headers.each(function(offset) {
						if(!this.sortDisabled) {
							h[this.column] = $(this);					
						}
				});
				
				var l = list.length; 
				for(var i=0; i < l; i++) {
					h[list[i][0]].addClass(css[list[i][1]]);
				}
			}
			
			function fixColumnWidth(table,$headers) {
				var c = table.config;
				if(c.widthFixed) {
					var colgroup = $('<colgroup>');
					$("tr:first td",table.tBodies[0]).each(function() {
						colgroup.append($('<col>').css('width',$(this).width()));
					});
					$(table).prepend(colgroup);
				};
			}
			
			function updateHeaderSortCount(table,sortList) {
				var c = table.config, l = sortList.length;
				for(var i=0; i < l; i++) {
					var s = sortList[i], o = c.headerList[s[0]];
					o.count = s[1];
					o.count++;
				}
			}
			
			/* sorting methods */
			function multisort(table,sortList,cache) {
				
				if(table.config.debug) { var sortTime = new Date(); }
				
				var dynamicExp = "var sortWrapper = function(a,b) {", l = sortList.length;
					
				for(var i=0; i < l; i++) {
					
					var c = sortList[i][0];
					var order = sortList[i][1];
					var s = (getCachedSortType(table.config.parsers,c) == "text") ? ((order == 0) ? "sortText" : "sortTextDesc") : ((order == 0) ? "sortNumeric" : "sortNumericDesc");
					
					var e = "e" + i;
					
					dynamicExp += "var " + e + " = " + s + "(a[" + c + "],b[" + c + "]); ";
					dynamicExp += "if(" + e + ") { return " + e + "; } ";
					dynamicExp += "else { ";
				}
				
				// if value is the same keep orignal order	
				var orgOrderCol = cache.normalized[0].length - 1;
				dynamicExp += "return a[" + orgOrderCol + "]-b[" + orgOrderCol + "];";
						
				for(var i=0; i < l; i++) {
					dynamicExp += "}; ";
				}
				
				dynamicExp += "return 0; ";	
				dynamicExp += "}; ";	
				
				eval(dynamicExp);
				
				cache.normalized.sort(sortWrapper);
				
				if(table.config.debug) { benchmark("Sorting on " + sortList.toString() + " and dir " + order+ " time:", sortTime); }
				
				return cache;
			};
			
			function sortText(a,b) {
				return ((a < b) ? -1 : ((a > b) ? 1 : 0));
			};
			
			function sortTextDesc(a,b) {
				return ((b < a) ? -1 : ((b > a) ? 1 : 0));
			};	
			
	 		function sortNumeric(a,b) {
				return a-b;
			};
			
			function sortNumericDesc(a,b) {
				return b-a;
			};
			
			function getCachedSortType(parsers,i) {
				return parsers[i].type;
			};
			
			/* public methods */
			this.construct = function(settings) {

				return this.each(function() {
					
					if(!this.tHead || !this.tBodies) return;
					
					var $this, $document,$headers, cache, config, shiftDown = 0, sortOrder;
					
					this.config = {};
					
					config = $.extend(this.config, $.tablesorter.defaults, settings);
					
					// store common expression for speed					
					$this = $(this);
					
					// build headers
					$headers = buildHeaders(this);
					
					// try to auto detect column type, and store in tables config
					this.config.parsers = buildParserCache(this,$headers);
					
					
					// build the cache for the tbody cells
					cache = buildCache(this);
					
					// get the css class names, could be done else where.
					var sortCSS = [config.cssDesc,config.cssAsc];
					
					// fixate columns if the users supplies the fixedWidth option
					fixColumnWidth(this);
					
					// apply event handling to headers
					// this is to big, perhaps break it out?
					$headers.click(function(e) {
						
						$this.trigger("sortStart");
						
						var totalRows = ($this[0].tBodies[0] && $this[0].tBodies[0].rows.length) || 0;
						
						if(!this.sortDisabled && totalRows > 0) {
							
							
							// store exp, for speed
							var $cell = $(this);
	
							// get current column index
							var i = this.column;
							
							// get current column sort order
							this.order = this.count++ % 2;
							
							// user only whants to sort on one column
							if(!e[config.sortMultiSortKey]) {
								
								// flush the sort list
								config.sortList = [];
								
								if(config.sortForce != null) {
									var a = config.sortForce; 
									for(var j=0; j < a.length; j++) {
										if(a[j][0] != i) {
											config.sortList.push(a[j]);
										}
									}
								}
								
								// add column to sort list
								config.sortList.push([i,this.order]);
							
							// multi column sorting
							} else {
								// the user has clicked on an all ready sortet column.
								if(isValueInArray(i,config.sortList)) {	 
									
									// revers the sorting direction for all tables.
									for(var j=0; j < config.sortList.length; j++) {
										var s = config.sortList[j], o = config.headerList[s[0]];
										if(s[0] == i) {
											o.count = s[1];
											o.count++;
											s[1] = o.count % 2;
										}
									}	
								} else {
									// add column to sort list array
									config.sortList.push([i,this.order]);
								}
							};
							setTimeout(function() {
								//set css for headers
								setHeadersCss($this[0],$headers,config.sortList,sortCSS);
								appendToTable($this[0],multisort($this[0],config.sortList,cache));
							},1);
							// stop normal event by returning false
							return false;
						}
					// cancel selection	
					}).mousedown(function() {
						if(config.cancelSelection) {
							this.onselectstart = function() {return false};
							return false;
						}
					});
					
					// apply easy methods that trigger binded events
					$this.bind("update",function() {
						
						// rebuild parsers.
						this.config.parsers = buildParserCache(this,$headers);
						
						// rebuild the cache map
						cache = buildCache(this);
						
					}).bind("sorton",function(e,list) {
						
						$(this).trigger("sortStart");
						
						config.sortList = list;
						
						// update and store the sortlist
						var sortList = config.sortList;
						
						// update header count index
						updateHeaderSortCount(this,sortList);
						
						//set css for headers
						setHeadersCss(this,$headers,sortList,sortCSS);
						
						
						// sort the table and append it to the dom
						appendToTable(this,multisort(this,sortList,cache));

					}).bind("appendCache",function() {
						
						appendToTable(this,cache);
					
					}).bind("applyWidgetId",function(e,id) {
						
						getWidgetById(id).format(this);
						
					}).bind("applyWidgets",function() {
						// apply widgets
						applyWidget(this);
					});
					
					if($.metadata && ($(this).metadata() && $(this).metadata().sortlist)) {
						config.sortList = $(this).metadata().sortlist;
					}
					// if user has supplied a sort list to constructor.
					if(config.sortList.length > 0) {
						$this.trigger("sorton",[config.sortList]);	
					}
					
					// apply widgets
					applyWidget(this);
				});
			};
			
			this.addParser = function(parser) {
				var l = parsers.length, a = true;
				for(var i=0; i < l; i++) {
					if(parsers[i].id.toLowerCase() == parser.id.toLowerCase()) {
						a = false;
					}
				}
				if(a) { parsers.push(parser); };
			};
			
			this.addWidget = function(widget) {
				widgets.push(widget);
			};
			
			this.formatFloat = function(s) {
				var i = parseFloat(s);
				return (isNaN(i)) ? 0 : i;
			};
			this.formatInt = function(s) {
				var i = parseInt(s);
				return (isNaN(i)) ? 0 : i;
			};
			
			this.isDigit = function(s,config) {
				var DECIMAL = '\\' + config.decimal;
				var exp = '/(^[+]?0(' + DECIMAL +'0+)?$)|(^([-+]?[1-9][0-9]*)$)|(^([-+]?((0?|[1-9][0-9]*)' + DECIMAL +'(0*[1-9][0-9]*)))$)|(^[-+]?[1-9]+[0-9]*' + DECIMAL +'0+$)/';
				return RegExp(exp).test($.trim(s));
			};
			
			this.clearTableBody = function(table) {
				if($.browser.msie) {
					function empty() {
						while ( this.firstChild ) this.removeChild( this.firstChild );
					}
					empty.apply(table.tBodies[0]);
				} else {
					table.tBodies[0].innerHTML = "";
				}
			};
		}
	});
	
	// extend plugin scope
	$.fn.extend({
        tablesorter: $.tablesorter.construct
	});
	
	var ts = $.tablesorter;
	
	// add default parsers
	ts.addParser({
		id: "text",
		is: function(s) {
			return true;
		},
		format: function(s) {
			return $.trim(s.toLowerCase());
		},
		type: "text"
	});
	
	ts.addParser({
		id: "digit",
		is: function(s,table) {
			var c = table.config;
			return $.tablesorter.isDigit(s,c);
		},
		format: function(s) {
			return $.tablesorter.formatFloat(s);
		},
		type: "numeric"
	});
	
	ts.addParser({
		id: "currency",
		is: function(s) {
			return /^[£$€?.]/.test(s);
		},
		format: function(s) {
			return $.tablesorter.formatFloat(s.replace(new RegExp(/[^0-9.]/g),""));
		},
		type: "numeric"
	});
	
	ts.addParser({
		id: "ipAddress",
		is: function(s) {
			return /^\d{2,3}[\.]\d{2,3}[\.]\d{2,3}[\.]\d{2,3}$/.test(s);
		},
		format: function(s) {
			var a = s.split("."), r = "", l = a.length;
			for(var i = 0; i < l; i++) {
				var item = a[i];
			   	if(item.length == 2) {
					r += "0" + item;
			   	} else {
					r += item;
			   	}
			}
			return $.tablesorter.formatFloat(r);
		},
		type: "numeric"
	});
	
	ts.addParser({
		id: "url",
		is: function(s) {
			return /^(https?|ftp|file):\/\/$/.test(s);
		},
		format: function(s) {
			return jQuery.trim(s.replace(new RegExp(/(https?|ftp|file):\/\//),''));
		},
		type: "text"
	});
	
	ts.addParser({
		id: "isoDate",
		is: function(s) {
			return /^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(s);
		},
		format: function(s) {
			return $.tablesorter.formatFloat((s != "") ? new Date(s.replace(new RegExp(/-/g),"/")).getTime() : "0");
		},
		type: "numeric"
	});
		
	ts.addParser({
		id: "percent",
		is: function(s) { 
			return /\%$/.test($.trim(s));
		},
		format: function(s) {
			return $.tablesorter.formatFloat(s.replace(new RegExp(/%/g),""));
		},
		type: "numeric"
	});

	ts.addParser({
		id: "usLongDate",
		is: function(s) {
			return s.match(new RegExp(/^[A-Za-z]{3,10}\.? [0-9]{1,2}, ([0-9]{4}|'?[0-9]{2}) (([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(AM|PM)))$/));
		},
		format: function(s) {
			return $.tablesorter.formatFloat(new Date(s).getTime());
		},
		type: "numeric"
	});

	ts.addParser({
		id: "shortDate",
		is: function(s) {
			return /\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/.test(s);
		},
		format: function(s,table) {
			var c = table.config;
			s = s.replace(/\-/g,"/");
			if(c.dateFormat == "us") {
				// reformat the string in ISO format
				s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$1/$2");
			} else if(c.dateFormat == "uk") {
				//reformat the string in ISO format
				s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$2/$1");
			} else if(c.dateFormat == "dd/mm/yy" || c.dateFormat == "dd-mm-yy") {
				s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})/, "$1/$2/$3");	
			}
			return $.tablesorter.formatFloat(new Date(s).getTime());
		},
		type: "numeric"
	});

	ts.addParser({
	    id: "time",
	    is: function(s) {
	        return /^(([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(am|pm)))$/.test(s);
	    },
	    format: function(s) {
	        return $.tablesorter.formatFloat(new Date("2000/01/01 " + s).getTime());
	    },
	  type: "numeric"
	});
	
	
	ts.addParser({
	    id: "metadata",
	    is: function(s) {
	        return false;
	    },
	    format: function(s,table,cell) {
			var c = table.config, p = (!c.parserMetadataName) ? 'sortValue' : c.parserMetadataName;
	        return $(cell).metadata()[p];
	    },
	  type: "numeric"
	});
	
	// add default widgets
	ts.addWidget({
		id: "zebra",
		format: function(table) {
			if(table.config.debug) { var time = new Date(); }
			$("tr:visible",table.tBodies[0])
	        .filter(':even')
	        .removeClass(table.config.widgetZebra.css[1]).addClass(table.config.widgetZebra.css[0])
	        .end().filter(':odd')
	        .removeClass(table.config.widgetZebra.css[0]).addClass(table.config.widgetZebra.css[1]);
			if(table.config.debug) { $.tablesorter.benchmark("Applying Zebra widget", time); }
		}
	});	
})(jQuery);/*
 * $ URIs @VERSION
 * 
 * Copyright (c) 2008,2009 Jeni Tennison
 * Licensed under the MIT (MIT-LICENSE.txt)
 *
 */
/**
 * @fileOverview $ URIs
 * @author <a href="mailto:jeni@jenitennison.com">Jeni Tennison</a>
 * @copyright (c) 2008,2009 Jeni Tennison
 * @license MIT license (MIT-LICENSE.txt)
 * @version 1.0
 */
/**
 * @class
 * @name jQuery
 * @exports $ as jQuery
 * @description rdfQuery is a <a href="http://jquery.com/">jQuery</a> plugin. The only fields and methods listed here are those that come as part of the rdfQuery library.
 */
(function ($) {

  var
    mem = {},
    uriRegex = /^(([a-z][\-a-z0-9+\.]*):)?(\/\/([^\/?#]+))?([^?#]*)?(\?([^#]*))?(#(.*))?$/i,
    docURI,

    parseURI = function (u) {
      var m = u.match(uriRegex);
      if (m === null) {
        throw "Malformed URI: " + u;
      }
      return {
        scheme: m[1] ? m[2].toLowerCase() : undefined,
        authority: m[3] ? m[4] : undefined,
        path: m[5] || '',
        query: m[6] ? m[7] : undefined,
        fragment: m[8] ? m[9] : undefined
      };
    },

    removeDotSegments = function (u) {
      var r = '', m = [];
      if (/\./.test(u)) {
        while (u !== undefined && u !== '') {
          if (u === '.' || u === '..') {
            u = '';
          } else if (/^\.\.\//.test(u)) { // starts with ../
            u = u.substring(3);
          } else if (/^\.\//.test(u)) { // starts with ./
            u = u.substring(2);
          } else if (/^\/\.(\/|$)/.test(u)) { // starts with /./ or consists of /.
            u = '/' + u.substring(3);
          } else if (/^\/\.\.(\/|$)/.test(u)) { // starts with /../ or consists of /..
            u = '/' + u.substring(4);
            r = r.replace(/\/?[^\/]+$/, '');
          } else {
            m = u.match(/^(\/?[^\/]*)(\/.*)?$/);
            u = m[2];
            r = r + m[1];
          }
        }
        return r;
      } else {
        return u;
      }
    },

    merge = function (b, r) {
      if (b.authority !== '' && (b.path === undefined || b.path === '')) {
        return '/' + r;
      } else {
        return b.path.replace(/[^\/]+$/, '') + r;
      }
    };

  /**
   * Creates a new jQuery.uri object. This should be invoked as a method rather than constructed using new.
   * @class Represents a URI
   * @param {String} [relative='']
   * @param {String|jQuery.uri} [base] Defaults to the base URI of the page
   * @returns {jQuery.uri} The new jQuery.uri object.
   * @example uri = jQuery.uri('/my/file.html');
   */
  $.uri = function (relative, base) {
    var uri;
    relative = relative || '';
    if (mem[relative]) {
      return mem[relative];
    }
    base = base || $.uri.base();
    if (typeof base === 'string') {
      base = $.uri.absolute(base);
    }
    uri = new $.uri.fn.init(relative, base);
    if (mem[uri]) {
      return mem[uri];
    } else {
      mem[uri] = uri;
      return uri;
    }
  };

  $.uri.fn = $.uri.prototype = {
    /**
     * The scheme used in the URI
     * @type String
     */
    scheme: undefined,
    /**
     * The authority used in the URI
     * @type String
     */
    authority: undefined,
    /**
     * The path used in the URI
     * @type String
     */
    path: undefined,
    /**
     * The query part of the URI
     * @type String
     */
    query: undefined,
    /**
     * The fragment part of the URI
     * @type String
     */
    fragment: undefined,
    
    init: function (relative, base) {
      var r = {};
      base = base || {};
      $.extend(this, parseURI(relative));
      if (this.scheme === undefined) {
        this.scheme = base.scheme;
        if (this.authority !== undefined) {
          this.path = removeDotSegments(this.path);
        } else {
          this.authority = base.authority;
          if (this.path === '') {
            this.path = base.path;
            if (this.query === undefined) {
              this.query = base.query;
            }
          } else {
            if (!/^\//.test(this.path)) {
              this.path = merge(base, this.path);
            }
            this.path = removeDotSegments(this.path);
          }
        }
      }
      if (this.scheme === undefined) {
        throw "Malformed URI: URI is not an absolute URI and no base supplied: " + relative;
      }
      return this;
    },
  
    /**
     * Resolves a relative URI relative to this URI
     * @param {String} relative
     * @returns jQuery.uri
     */
    resolve: function (relative) {
      return $.uri(relative, this);
    },
    
    /**
     * Creates a relative URI giving the path from this URI to the absolute URI passed as a parameter
     * @param {String|jQuery.uri} absolute
     * @returns String
     */
    relative: function (absolute) {
      var aPath, bPath, i = 0, j, resultPath = [], result = '';
      if (typeof absolute === 'string') {
        absolute = $.uri(absolute, {});
      }
      if (absolute.scheme !== this.scheme || 
          absolute.authority !== this.authority) {
        return absolute.toString();
      }
      if (absolute.path !== this.path) {
        aPath = absolute.path.split('/');
        bPath = this.path.split('/');
        if (aPath[1] !== bPath[1]) {
          result = absolute.path;
        } else {
          while (aPath[i] === bPath[i]) {
            i += 1;
          }
          j = i;
          for (; i < bPath.length - 1; i += 1) {
            resultPath.push('..');
          }
          for (; j < aPath.length; j += 1) {
            resultPath.push(aPath[j]);
          }
          result = resultPath.join('/');
        }
        result = absolute.query === undefined ? result : result + '?' + absolute.query;
        result = absolute.fragment === undefined ? result : result + '#' + absolute.fragment;
        return result;
      }
      if (absolute.query !== undefined && absolute.query !== this.query) {
        return '?' + absolute.query + (absolute.fragment === undefined ? '' : '#' + absolute.fragment);
      }
      if (absolute.fragment !== undefined && absolute.fragment !== this.fragment) {
        return '#' + absolute.fragment;
      }
      return '';
    },
  
    /**
     * Returns the URI as an absolute string
     * @returns String
     */
    toString: function () {
      var result = '';
      if (this._string) {
        return this._string;
      } else {
        result = this.scheme === undefined ? result : (result + this.scheme + ':');
        result = this.authority === undefined ? result : (result + '//' + this.authority);
        result = result + this.path;
        result = this.query === undefined ? result : (result + '?' + this.query);
        result = this.fragment === undefined ? result : (result + '#' + this.fragment);
        this._string = result;
        return result;
      }
    }
  
  };

  $.uri.fn.init.prototype = $.uri.fn;

  /**
   * Creates a {@link jQuery.uri} from a known-to-be-absolute URI
   * @param {String}
   * @returns {jQuery.uri}
   */
  $.uri.absolute = function (uri) {
    return $.uri(uri, {});
  };

  /**
   * Creates a {@link jQuery.uri} from a relative URI and an optional base URI
   * @returns {jQuery.uri}
   * @see jQuery.uri
   */
  $.uri.resolve = function (relative, base) {
    return $.uri(relative, base);
  };
  
  /**
   * Creates a string giving the relative path from a base URI to an absolute URI
   * @param {String} absolute
   * @param {String} base
   * @returns {String}
   */
  $.uri.relative = function (absolute, base) {
    return $.uri(base, {}).relative(absolute);
  };
  
  /**
   * Returns the base URI of the page
   * @returns {jQuery.uri}
   */
  $.uri.base = function () {
    return $(document).base();
  };
  
  /**
   * Returns the base URI in scope for the first selected element
   * @methodOf jQuery#
   * @name jQuery#base
   * @returns {jQuery.uri}
   * @example baseURI = $('img').base();
   */
  $.fn.base = function () {
    var base = $(this).parents().andSelf().find('base').attr('href'),
      doc = $(this)[0].ownerDocument || document,
      docURI = $.uri.absolute(doc.location === null ? document.location.href : doc.location.href);
    return base === undefined ? docURI : $.uri(base, docURI);
  };

})(jQuery);
/*
 * jQuery CURIE @VERSION
 * 
 * Copyright (c) 2008,2009 Jeni Tennison
 * Licensed under the MIT (MIT-LICENSE.txt)
 *
 * Depends:
 *  jquery.uri.js
 */
/**
 * @fileOverview XML Namespace processing
 * @author <a href="mailto:jeni@jenitennison.com">Jeni Tennison</a>
 * @copyright (c) 2008,2009 Jeni Tennison
 * @license MIT license (MIT-LICENSE.txt)
 * @version 1.0
 * @requires jquery.uri.js
 */

/*global jQuery */
(function ($) {

  var 
    xmlNs = 'http://www.w3.org/XML/1998/namespace',
    xmlnsNs = 'http://www.w3.org/2000/xmlns/',
    
    xmlnsRegex = /\sxmlns(?::([^ =]+))?\s*=\s*(?:"([^"]*)"|'([^']*)')/g,
    
    ncNameChar = '[-A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD\u10000-\uEFFFF\.0-9\u00B7\u0300-\u036F\u203F-\u2040]',
    ncNameStartChar = '[\u0041-\u005A\u0061-\u007A\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF\u0100-\u0131\u0134-\u013E\u0141-\u0148\u014A-\u017E\u0180-\u01C3\u01CD-\u01F0\u01F4-\u01F5\u01FA-\u0217\u0250-\u02A8\u02BB-\u02C1\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03CE\u03D0-\u03D6\u03DA\u03DC\u03DE\u03E0\u03E2-\u03F3\u0401-\u040C\u040E-\u044F\u0451-\u045C\u045E-\u0481\u0490-\u04C4\u04C7-\u04C8\u04CB-\u04CC\u04D0-\u04EB\u04EE-\u04F5\u04F8-\u04F9\u0531-\u0556\u0559\u0561-\u0586\u05D0-\u05EA\u05F0-\u05F2\u0621-\u063A\u0641-\u064A\u0671-\u06B7\u06BA-\u06BE\u06C0-\u06CE\u06D0-\u06D3\u06D5\u06E5-\u06E6\u0905-\u0939\u093D\u0958-\u0961\u0985-\u098C\u098F-\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09DC-\u09DD\u09DF-\u09E1\u09F0-\u09F1\u0A05-\u0A0A\u0A0F-\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32-\u0A33\u0A35-\u0A36\u0A38-\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8B\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2-\u0AB3\u0AB5-\u0AB9\u0ABD\u0AE0\u0B05-\u0B0C\u0B0F-\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32-\u0B33\u0B36-\u0B39\u0B3D\u0B5C-\u0B5D\u0B5F-\u0B61\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99-\u0B9A\u0B9C\u0B9E-\u0B9F\u0BA3-\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB5\u0BB7-\u0BB9\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C33\u0C35-\u0C39\u0C60-\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CDE\u0CE0-\u0CE1\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D28\u0D2A-\u0D39\u0D60-\u0D61\u0E01-\u0E2E\u0E30\u0E32-\u0E33\u0E40-\u0E45\u0E81-\u0E82\u0E84\u0E87-\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA-\u0EAB\u0EAD-\u0EAE\u0EB0\u0EB2-\u0EB3\u0EBD\u0EC0-\u0EC4\u0F40-\u0F47\u0F49-\u0F69\u10A0-\u10C5\u10D0-\u10F6\u1100\u1102-\u1103\u1105-\u1107\u1109\u110B-\u110C\u110E-\u1112\u113C\u113E\u1140\u114C\u114E\u1150\u1154-\u1155\u1159\u115F-\u1161\u1163\u1165\u1167\u1169\u116D-\u116E\u1172-\u1173\u1175\u119E\u11A8\u11AB\u11AE-\u11AF\u11B7-\u11B8\u11BA\u11BC-\u11C2\u11EB\u11F0\u11F9\u1E00-\u1E9B\u1EA0-\u1EF9\u1F00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2126\u212A-\u212B\u212E\u2180-\u2182\u3041-\u3094\u30A1-\u30FA\u3105-\u312C\uAC00-\uD7A3\u4E00-\u9FA5\u3007\u3021-\u3029_]',
    ncNameRegex = new RegExp('^' + ncNameStartChar + ncNameChar + '*$');
    

/**
 * Returns the namespaces declared in the scope of the first selected element, or
 * adds a namespace declaration to all selected elements. Pass in no parameters
 * to return all namespaces bindings on the first selected element. If only 
 * the prefix parameter is specified, this method will return the namespace
 * URI that is bound to the specified prefix on the first element in the selection
 * If the prefix and uri parameters are both specified, this method will
 * add the binding of the specified prefix and namespace URI to all elements
 * in the selection.
 * @methodOf jQuery#
 * @name jQuery#xmlns
 * @param {String} [prefix] Restricts the namespaces returned to only the namespace with the specified namespace prefix.
 * @param {String|jQuery.uri} [uri] Adds a namespace declaration to the selected elements that maps the specified prefix to the specified namespace.
 * @param {Object} [inherited] A map of inherited namespace bindings.
 * @returns {Object|jQuery.uri|jQuery}
 * @example 
 * // Retrieve all of the namespace bindings on the HTML document element
 * var nsMap = $('html').xmlns();
 * @example
 * // Retrieve the namespace URI mapped to the 'dc' prefix on the HTML document element
 * var dcNamespace = $('html').xmlns('dc');
 * @example
 * // Create a namespace declaration that binds the 'dc' prefix to the URI 'http://purl.org/dc/elements/1.1/'
 * $('html').xmlns('dc', 'http://purl.org/dc/elements/1.1/');
 */
  $.fn.xmlns = function (prefix, uri, inherited) {
    var 
      elem = this.eq(0),
      ns = elem.data('xmlns'),
      e = elem[0], a, p, i,
      decl = prefix ? 'xmlns:' + prefix : 'xmlns',
      value,
      tag, found = false;
    if (uri === undefined) {
      if (prefix === undefined) { // get the in-scope declarations on the first element
        if (!ns) {
          ns = {
//            xml: $.uri(xmlNs)
          };
          if (e.attributes && e.attributes.getNamedItemNS) {
            for (i = 0; i < e.attributes.length; i += 1) {
              a = e.attributes[i];
              if (/^xmlns(:(.+))?$/.test(a.nodeName)) {
                prefix = /^xmlns(:(.+))?$/.exec(a.nodeName)[2] || '';
                value = a.nodeValue;
                if (prefix === '' || (value !== '' && value !== xmlNs && value !== xmlnsNs && ncNameRegex.test(prefix) && prefix !== 'xml' && prefix !== 'xmlns')) {
                  ns[prefix] = $.uri(a.nodeValue);
                  found = true;
                }
              }
            }
          } else {
            tag = /<[^>]+>/.exec(e.outerHTML);
            a = xmlnsRegex.exec(tag);
            while (a !== null) {
              prefix = a[1] || '';
              value = a[2] || a[3];
              if (prefix === '' || (value !== '' && value !== xmlNs && value !== xmlnsNs && ncNameRegex.test(prefix) && prefix !== 'xml' && prefix !== 'xmlns')) {
                ns[prefix] = $.uri(a[2] || a[3]);
                found = true;
              }
              a = xmlnsRegex.exec(tag);
            }
            xmlnsRegex.lastIndex = 0;
          }
          inherited = inherited || (e.parentNode.nodeType === 1 ? elem.parent().xmlns() : {});
          ns = found ? $.extend({}, inherited, ns) : inherited;
          elem.data('xmlns', ns);
        }
        return ns;
      } else if (typeof prefix === 'object') { // set the prefix mappings defined in the object
        for (p in prefix) {
          if (typeof prefix[p] === 'string' && ncNameRegex.test(p)) {
            this.xmlns(p, prefix[p]);
          }
        }
        this.find('*').andSelf().removeData('xmlns');
        return this;
      } else { // get the in-scope declaration associated with this prefix on the first element
        if (!ns) {
          ns = elem.xmlns();
        }
        return ns[prefix];
      }
    } else { // set
      this.find('*').andSelf().removeData('xmlns');
      return this.attr(decl, uri);
    }
  };

/**
 * Removes one or more XML namespace bindings from the selected elements.
 * @methodOf jQuery#
 * @name jQuery#removeXmlns
 * @param {String|Object|String[]} prefix The prefix(es) of the XML namespace bindings that are to be removed from the selected elements.
 * @returns {jQuery} The original jQuery object.
 * @example
 * // Remove the foaf namespace declaration from the body element:
 * $('body').removeXmlns('foaf');
 * @example
 * // Remove the foo and bar namespace declarations from all h2 elements
 * $('h2').removeXmlns(['foo', 'bar']);
 * @example
 * // Remove the foo and bar namespace declarations from all h2 elements
 * var namespaces = { foo : 'http://www.example.org/foo', bar : 'http://www.example.org/bar' };
 * $('h2').removeXmlns(namespaces);
 */
  $.fn.removeXmlns = function (prefix) {
    var decl, p, i;
    if (typeof prefix === 'object') {
      if (prefix.length === undefined) { // assume an object representing namespaces
        for (p in prefix) {
          if (typeof prefix[p] === 'string') {
            this.removeXmlns(p);
          }
        }
      } else { // it's an array
        for (i = 0; i < prefix.length; i += 1) {
          this.removeXmlns(prefix[i]);
        }
      }
    } else {
      decl = prefix ? 'xmlns:' + prefix : 'xmlns';
      this.removeAttr(decl);
    }
    this.find('*').andSelf().removeData('xmlns');
    return this;
  };

  $.fn.qname = function (name) {
    var m, prefix, namespace;
    if (name === undefined) {
      if (this[0].outerHTML === undefined) {
        name = this[0].nodeName.toLowerCase();
      } else {
        name = /<([^ >]+)/.exec(this[0].outerHTML)[1].toLowerCase();
      }
    }
    if (name === '?xml:namespace') {
      // there's a prefix on the name, but we can't get at it
      throw "XMLinHTML: Unable to get the prefix to resolve the name of this element";
    }
    m = /^(([^:]+):)?([^:]+)$/.exec(name);
    prefix = m[2] || '';
    namespace = this.xmlns(prefix);
    if (namespace === undefined && prefix !== '') {
      throw "MalformedQName: The prefix " + prefix + " is not declared";
    }
    return {
      namespace: namespace,
      localPart: m[3],
      prefix: prefix,
      name: name
    };
  };

})(jQuery);
/*
 * jQuery CURIE @VERSION
 *
 * Copyright (c) 2008,2009 Jeni Tennison
 * Licensed under the MIT (MIT-LICENSE.txt)
 *
 * Depends:
 *  jquery.uri.js
 */
/**
 * @fileOverview XML Schema datatype handling
 * @author <a href="mailto:jeni@jenitennison.com">Jeni Tennison</a>
 * @copyright (c) 2008,2009 Jeni Tennison
 * @license MIT license (MIT-LICENSE.txt)
 * @version 1.0
 * @requires jquery.uri.js
 */

(function ($) {

  var strip = function (value) {
    return value.replace(/[ \t\n\r]+/, ' ').replace(/^ +/, '').replace(/ +$/, '');
  };

  /**
   * Creates a new jQuery.typedValue object. This should be invoked as a method
   * rather than constructed using new.
   * @class Represents a value with an XML Schema datatype
   * @param {String} value The string representation of the value
   * @param {String} datatype The XML Schema datatype URI
   * @returns {jQuery.typedValue}
   * @example intValue = jQuery.typedValue('42', 'http://www.w3.org/2001/XMLSchema#integer');
   */
  $.typedValue = function (value, datatype) {
    return $.typedValue.fn.init(value, datatype);
  };

  $.typedValue.fn = $.typedValue.prototype = {
    /**
     * The string representation of the value
     * @memberOf jQuery.typedValue#
     */
    representation: undefined,
    /**
     * The value as an object. The type of the object will
     * depend on the XML Schema datatype URI specified
     * in the constructor. The following table lists the mappings
     * currently supported:
     * <table>
     *   <tr>
     *   <th>XML Schema Datatype</th>
     *   <th>Value type</th>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#string</td>
     *     <td>string</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#token</td>
     *     <td>string</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#NCName</td>
     *     <td>string</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#boolean</td>
     *     <td>bool</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#decimal</td>
     *     <td>string</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#integer</td>
     *     <td>int</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#int</td>
     *     <td>int</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#float</td>
     *     <td>float</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#double</td>
     *     <td>float</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#dateTime</td>
     *     <td>string</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#date</td>
     *     <td>string</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#gYear</td>
     *     <td>int</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#gMonthDay</td>
     *     <td>string</td>
     *   </tr>
     *   <tr>
     *     <td>http://www.w3.org/2001/XMLSchema#anyURI</td>
     *     <td>{@link jQuery.uri}</td>
     *   </tr>
     * </table>
     * @memberOf jQuery.typedValue#
     */
    value: undefined,
    /**
     * The XML Schema datatype URI for the value's datatype
     * @memberOf jQuery.typedValue#
     */
    datatype: undefined,

    init: function (value, datatype) {
      var d = $.typedValue.types[datatype];
      if ($.typedValue.valid(value, datatype)) {
        this.representation = value;
        this.datatype = datatype;
        this.value = d === undefined ? strip(value) : d.value(d.strip ? strip(value) : value);
        return this;
      } else {
        throw {
          name: 'InvalidValue',
          message: value + ' is not a valid ' + datatype + ' value'
        };
      }
    }
  };

  $.typedValue.fn.init.prototype = $.typedValue.fn;

  /**
   * An object that holds the datatypes supported by the script. The properties of this object are the URIs of the datatypes, and each datatype has four properties:
   * <dl>
   *   <dt>strip</dt>
   *   <dd>A boolean value that indicates whether whitespace should be stripped from the value prior to testing against the regular expression or passing to the value function.</dd>
   *   <dt>regex</dt>
   *   <dd>A regular expression that valid values of the type must match.</dd>
   *   <dt>validate</dt>
   *   <dd>Optional. A function that performs further testing on the value.</dd>
   *   <dt>value</dt>
   *   <dd>A function that returns a Javascript object equivalent for the value.</dd>
   * </dl>
   * You can add to this object as necessary for your own datatypes, and {@link jQuery.typedValue} and {@link jQuery.typedValue.valid} will work with them.
   * @see jQuery.typedValue
   * @see jQuery.typedValue.valid
   */
  $.typedValue.types = {};

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#string'] = {
    regex: /^[\s\S]*$/,
    strip: false,
    /** @ignore */
    value: function (v) {
      return v;
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#token'] = {
    regex: /^.*$/,
    strip: true,
    /** @ignore */
    value: function (v) {
      return strip(v);
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#NCName'] = {
    regex: /^[a-z_][-\.a-z0-9]+$/i,
    strip: true,
    /** @ignore */
    value: function (v) {
      return strip(v);
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#boolean'] = {
    regex: /^(?:true|false|1|0)$/,
    strip: true,
    /** @ignore */
    value: function (v) {
      return v === 'true' || v === '1';
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#decimal'] = {
    regex: /^[\-\+]?(?:[0-9]+\.[0-9]*|\.[0-9]+|[0-9]+)$/,
    strip: true,
    /** @ignore */
    value: function (v) {
      v = v.replace(/^0+/, '')
        .replace(/0+$/, '');
      if (v === '') {
        v = '0.0';
      }
      if (v.substring(0, 1) === '.') {
        v = '0' + v;
      }
      if (/\.$/.test(v)) {
        v = v + '0';
      } else if (!/\./.test(v)) {
        v = v + '.0';
      }
      return v;
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#integer'] = {
    regex: /^[\-\+]?[0-9]+$/,
    strip: true,
    /** @ignore */
    value: function (v) {
      return parseInt(v, 10);
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#int'] = {
    regex: /^[\-\+]?[0-9]+$/,
    strip: true,
    /** @ignore */
    value: function (v) {
      return parseInt(v, 10);
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#float'] = {
    regex: /^(?:[\-\+]?(?:[0-9]+\.[0-9]*|\.[0-9]+|[0-9]+)(?:[eE][\-\+]?[0-9]+)?|[\-\+]?INF|NaN)$/,
    strip: true,
    /** @ignore */
    value: function (v) {
      if (v === '-INF') {
        return -1 / 0;
      } else if (v === 'INF' || v === '+INF') {
        return 1 / 0;
      } else {
        return parseFloat(v);
      }
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#double'] = {
    regex: $.typedValue.types['http://www.w3.org/2001/XMLSchema#float'].regex,
    strip: true,
    value: $.typedValue.types['http://www.w3.org/2001/XMLSchema#float'].value
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#duration'] = {
    regex: /^([\-\+])?P(?:([0-9]+)Y)?(?:([0-9]+)M)?(?:([0-9]+)D)?(?:T(?:([0-9]+)H)?(?:([0-9]+)M)?(?:([0-9]+(?:\.[0-9]+)?)?S)?)$/,
    /** @ignore */
    validate: function (v) {
      var m = this.regex.exec(v);
      return m[2] || m[3] || m[4] || m[5] || m[6] || m[7];
    },
    strip: true,
    /** @ignore */
    value: function (v) {
      return v;
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#yearMonthDuration'] = {
    regex: /^([\-\+])?P(?:([0-9]+)Y)?(?:([0-9]+)M)?$/,
    /** @ignore */
    validate: function (v) {
      var m = this.regex.exec(v);
      return m[2] || m[3];
    },
    strip: true,
    /** @ignore */
    value: function (v) {
      var m = this.regex.exec(v),
        years = m[2] || 0,
        months = m[3] || 0;
      months += years * 12;
      return m[1] === '-' ? -1 * months : months;
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#dateTime'] = {
    regex: /^(-?[0-9]{4,})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):(([0-9]{2})(\.([0-9]+))?)((?:[\-\+]([0-9]{2}):([0-9]{2}))|Z)?$/,
    /** @ignore */
    validate: function (v) {
      var
        m = this.regex.exec(v),
        year = parseInt(m[1], 10),
        tz = m[10] === undefined || m[10] === 'Z' ? '+0000' : m[10].replace(/:/, ''),
        date;
      if (year === 0 ||
          parseInt(tz, 10) < -1400 || parseInt(tz, 10) > 1400) {
        return false;
      }
      try {
        year = year < 100 ? Math.abs(year) + 1000 : year;
        month = parseInt(m[2], 10);
        day = parseInt(m[3], 10);
        if (day > 31) {
          return false;
        } else if (day > 30 && !(month === 1 || month === 3 || month === 5 || month === 7 || month === 8 || month === 10 || month === 12)) {
          return false;
        } else if (month === 2) {
          if (day > 29) {
            return false;
          } else if (day === 29 && (year % 4 !== 0 || (year % 100 === 0 && year % 400 !== 0))) {
            return false;
          }
        }
        date = '' + year + '/' + m[2] + '/' + m[3] + ' ' + m[4] + ':' + m[5] + ':' + m[7] + ' ' + tz;
        date = new Date(date);
        return true;
      } catch (e) {
        return false;
      }
    },
    strip: true,
    /** @ignore */
    value: function (v) {
      return v;
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#date'] = {
    regex: /^(-?[0-9]{4,})-([0-9]{2})-([0-9]{2})((?:[\-\+]([0-9]{2}):([0-9]{2}))|Z)?$/,
    /** @ignore */
    validate: function (v) {
      var
        m = this.regex.exec(v),
        year = parseInt(m[1], 10),
        month = parseInt(m[2], 10),
        day = parseInt(m[3], 10),
        tz = m[10] === undefined || m[10] === 'Z' ? '+0000' : m[10].replace(/:/, '');
      if (year === 0 ||
          month > 12 ||
          day > 31 ||
          parseInt(tz, 10) < -1400 || parseInt(tz, 10) > 1400) {
        return false;
      } else {
        return true;
      }
    },
    strip: true,
    /** @ignore */
    value: function (v) {
      return v;
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#gYear'] = {
    regex: /^-?([0-9]{4,})$/,
    /** @ignore */
    validate: function (v) {
      var i = parseInt(v, 10);
      return i !== 0;
    },
    strip: true,
    /** @ignore */
    value: function (v) {
      return parseInt(v, 10);
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#gMonthDay'] = {
    regex: /^--([0-9]{2})-([0-9]{2})((?:[\-\+]([0-9]{2}):([0-9]{2}))|Z)?$/,
    /** @ignore */
    validate: function (v) {
      var
        m = this.regex.exec(v),
        month = parseInt(m[1], 10),
        day = parseInt(m[2], 10),
        tz = m[3] === undefined || m[3] === 'Z' ? '+0000' : m[3].replace(/:/, '');
      if (month > 12 ||
          day > 31 ||
          parseInt(tz, 10) < -1400 || parseInt(tz, 10) > 1400) {
        return false;
      } else if (month === 2 && day > 29) {
        return false;
      } else if ((month === 4 || month === 6 || month === 9 || month === 11) && day > 30) {
        return false;
      } else {
        return true;
      }
    },
    strip: true,
    /** @ignore */
    value: function (v) {
      return v;
    }
  };

  $.typedValue.types['http://www.w3.org/2001/XMLSchema#anyURI'] = {
    regex: /^.*$/,
    strip: true,
    /** @ignore */
    value: function (v, options) {
      var opts = $.extend({}, $.typedValue.defaults, options);
      return $.uri.resolve(v, opts.base);
    }
  };

  $.typedValue.defaults = {
    base: $.uri.base(),
    namespaces: {}
  };

  /**
   * Checks whether a value is valid according to a given datatype. The datatype must be held in the {@link jQuery.typedValue.types} object.
   * @param {String} value The value to validate.
   * @param {String} datatype The URI for the datatype against which the value will be validated.
   * @returns {boolean} True if the value is valid or the datatype is not recognised.
   * @example validDate = $.typedValue.valid(date, 'http://www.w3.org/2001/XMLSchema#date');
   */
  $.typedValue.valid = function (value, datatype) {
    var d = $.typedValue.types[datatype];
    if (d === undefined) {
      return true;
    } else {
      value = d.strip ? strip(value) : value;
      if (d.regex.test(value)) {
        return d.validate === undefined ? true : d.validate(value);
      } else {
        return false;
      }
    }
  };

})(jQuery);
/*
 * jQuery CURIE @VERSION
 *
 * Copyright (c) 2008,2009 Jeni Tennison
 * Licensed under the MIT (MIT-LICENSE.txt)
 *
 * Depends:
 *  jquery.uri.js
 *  jquery.xmlns.js
 */

/**
 * @fileOverview jQuery CURIE handling
 * @author <a href="mailto:jeni@jenitennison.com">Jeni Tennison</a>
 * @copyright (c) 2008,2009 Jeni Tennison
 * @license MIT license (MIT-LICENSE.txt)
 * @version 1.0
 * @requires jquery.uri.js
 * @requires jquery.xmlns.js
 */
(function ($) {

   /**
    * Creates a {@link jQuery.uri} object by parsing a CURIE.
    * @methodOf jQuery
    * @param {String} curie The CURIE to be parsed
    * @param {String} uri The URI string to be converted to a CURIE.
    * @param {Object} [options] CURIE parsing options
    * @param {string} [options.reservedNamespace='http://www.w3.org/1999/xhtml/vocab#'] The namespace to apply to a CURIE that has no prefix and either starts with a colon or is in the list of reserved local names
    * @param {string} [options.defaultNamespace]  The namespace to apply to a CURIE with no prefix which is not mapped to the reserved namespace by the rules given above.
    * @param {Object} [options.namespaces] A map of namespace bindings used to map CURIE prefixes to URIs.
    * @param {string[]} [options.reserved=['alternate', 'appendix', 'bookmark', 'cite', 'chapter', 'contents', 'copyright', 'first', 'glossary', 'help', 'icon', 'index', 'last', 'license', 'meta', 'next', 'p3pv1', 'prev', 'role', 'section', 'stylesheet', 'subsection', 'start', 'top', 'up']] A list of local names that will always be mapped to the URI specified by reservedNamespace.
    * @param {string} [options.charcase='lower'] Specifies whether the curie's case is altered before it's interpreted. Acceptable values are:
    * <dl>
    * <dt>lower</dt><dd>Force the CURIE string to lower case.</dd>
    * <dt>upper</dt><dd>Force the CURIE string to upper case.</dd>
    * <dt>preserve</dt><dd>Preserve the original case of the CURIE. Note that this might not be possible if the CURIE has been taken from an HTML attribute value because of the case conversions performed automatically by browsers. For this reason, it's a good idea to avoid mixed-case CURIEs within RDFa.</dd>
    * </dl>
    * @returns {jQuery.uri} A new {@link jQuery.uri} object representing the full absolute URI specified by the CURIE.
    */
  $.curie = function (curie, options) {
    var
      opts = $.extend({}, $.curie.defaults, options || {}),
      m = /^(([^:]*):)?(.+)$/.exec(curie),
      prefix = m[2],
      local = m[3],
      ns = opts.namespaces[prefix];
    if (/^:.+/.test(curie)) { // This is the case of a CURIE like ":test"
      if (opts.reservedNamespace === undefined || opts.reservedNamespace === null) {
        throw "Malformed CURIE: No prefix and no default namespace for unprefixed CURIE " + curie;
      } else {
        ns = opts.reservedNamespace;
      }
    } else if (prefix) {
      if (ns === undefined) {
        throw "Malformed CURIE: No namespace binding for " + prefix + " in CURIE " + curie;
      }
    } else {
      if (opts.charcase === 'lower') {
        curie = curie.toLowerCase();
      } else if (opts.charcase === 'upper') {
        curie = curie.toUpperCase();
      }
      if (opts.reserved.length && $.inArray(curie, opts.reserved) >= 0) {
        ns = opts.reservedNamespace;
        local = curie;
      } else if (opts.defaultNamespace === undefined || opts.defaultNamespace === null) {
        // the default namespace is provided by the application; it's not clear whether
        // the default XML namespace should be used if there's a colon but no prefix
        throw "Malformed CURIE: No prefix and no default namespace for unprefixed CURIE " + curie;
      } else {
        ns = opts.defaultNamespace;
      }
    }
    return $.uri(ns + local);
  };

  $.curie.defaults = {
    namespaces: {},
    reserved: [],
    reservedNamespace: undefined,
    defaultNamespace: undefined,
    charcase: 'preserve'
  };

   /**
    * Creates a {@link jQuery.uri} object by parsing a safe CURIE string (a CURIE
    * contained within square brackets). If the input safeCurie string does not
    * start with '[' and end with ']', the entire string content will be interpreted
    * as a URI string.
    * @methodOf jQuery
    * @param {String} safeCurie The safe CURIE string to be parsed.
    * @param {Object} [options] CURIE parsing options
    * @param {string} [options.reservedNamespace='http://www.w3.org/1999/xhtml/vocab#'] The namespace to apply to a CURIE that has no prefix and either starts with a colon or is in the list of reserved local names
    * @param {string} [options.defaultNamespace]  The namespace to apply to a CURIE with no prefix which is not mapped to the reserved namespace by the rules given above.
    * @param {Object} [options.namespaces] A map of namespace bindings used to map CURIE prefixes to URIs.
    * @param {string[]} [options.reserved=['alternate', 'appendix', 'bookmark', 'cite', 'chapter', 'contents', 'copyright',
      'first', 'glossary', 'help', 'icon', 'index', 'last', 'license', 'meta', 'next',
      'p3pv1', 'prev', 'role', 'section', 'stylesheet', 'subsection', 'start', 'top', 'up']]
                        A list of local names that will always be mapped to the URI specified by reservedNamespace.
    * @param {string} [options.charcase='lower'] Specifies whether the curie's case is altered before it's interpreted. Acceptable values are:
    * <dl>
    * <dt>lower</dt><dd>Force the CURIE string to lower case.</dd>
    * <dt>upper</dt><dd>Force the CURIE string to upper case.</dd>
    * <dt>preserve</dt><dd>Preserve the original case of the CURIE. Note that this might not be possible if the CURIE has been taken from an HTML attribute value because of the case conversions performed automatically by browsers. For this reason, it's a good idea to avoid mixed-case CURIEs within RDFa.</dd>
    * </dl>
    * @returns {jQuery.uri} A new {@link jQuery.uri} object representing the full absolute URI specified by the CURIE.
    */
  $.safeCurie = function (safeCurie, options) {
    var m = /^\[([^\]]+)\]$/.exec(safeCurie);
    return m ? $.curie(m[1], options) : $.uri(safeCurie);
  };

   /**
    * Creates a CURIE string from a URI string.
    * @methodOf jQuery
    * @param {String} uri The URI string to be converted to a CURIE.
    * @param {Object} [options] CURIE parsing options
    * @param {string} [options.reservedNamespace='http://www.w3.org/1999/xhtml/vocab#']
    *        If the input URI starts with this value, the generated CURIE will
    *        have no namespace prefix and will start with a colon character (:),
    *        unless the local part of the CURIE is one of the reserved names specified
    *        by the reservedNames option (see below), in which case the generated
    *        CURIE will have no namespace prefix and will not start with a colon
    *        character.
    * @param {string} [options.defaultNamespace]  If the input URI starts with this value, the generated CURIE will have no namespace prefix and will not start with a colon.
    * @param {Object} [options.namespaces] A map of namespace bindings used to map CURIE prefixes to URIs.
    * @param {string[]} [options.reserved=['alternate', 'appendix', 'bookmark', 'cite', 'chapter', 'contents', 'copyright',
      'first', 'glossary', 'help', 'icon', 'index', 'last', 'license', 'meta', 'next',
      'p3pv1', 'prev', 'role', 'section', 'stylesheet', 'subsection', 'start', 'top', 'up']]
                        A list of local names that will always be mapped to the URI specified by reservedNamespace.
    * @param {string} [options.charcase='lower'] Specifies the case normalisation done to the CURIE. Acceptable values are:
    * <dl>
    * <dt>lower</dt><dd>Normalise the CURIE to lower case.</dd>
    * <dt>upper</dt><dd>Normalise the CURIE to upper case.</dd>
    * <dt>preserve</dt><dd>Preserve the original case of the CURIE. Note that this might not be possible if the CURIE has been taken from an HTML attribute value because of the case conversions performed automatically by browsers. For this reason, it's a good idea to avoid mixed-case CURIEs within RDFa.</dd>
    * </dl>
    * @returns {jQuery.uri} A new {@link jQuery.uri} object representing the full absolute URI specified by the CURIE.
    */
  $.createCurie = function (uri, options) {
    var opts = $.extend({}, $.curie.defaults, options || {}),
      ns = opts.namespaces,
      curie;
    uri = $.uri(uri).toString();
    if (opts.reservedNamespace !== undefined && 
        uri.substring(0, opts.reservedNamespace.toString().length) === opts.reservedNamespace.toString()) {
      curie = uri.substring(opts.reservedNamespace.toString().length);
      if ($.inArray(curie, opts.reserved) === -1) {
        curie = ':' + curie;
      }
    } else {
      $.each(ns, function (prefix, namespace) {
        if (uri.substring(0, namespace.toString().length) === namespace.toString()) {
          curie = prefix + ':' + uri.substring(namespace.toString().length);
          return null;
        }
      });
    }
    if (curie === undefined) {
      throw "No Namespace Binding: There's no appropriate namespace binding for generating a CURIE from " + uri;
    } else {
      return curie;
    }
  };

   /**
    * Creates a {@link jQuery.uri} object by parsing the specified
    * CURIE string in the context of the namespaces defined by the
    * jQuery selection.
    * @methodOf jQuery#
    * @name jQuery#curie
    * @param {String} curie The CURIE string to be parsed
    * @param {Object} options The CURIE parsing options.
    *        See {@link jQuery.curie} for details of the supported options.
    *        The namespace declarations declared on the current jQuery
    *        selection (and inherited from any ancestor elements) will automatically
    *        be included in the options.namespaces property.
    * @returns {jQuery.uri}
    * @see jQuery.curie
    */
  $.fn.curie = function (curie, options) {
    var opts = $.extend({}, $.fn.curie.defaults, { namespaces: this.xmlns() }, options || {});
    return $.curie(curie, opts);
  };

   /**
    * Creates a {@link jQuery.uri} object by parsing the specified
    * safe CURIE string in the context of the namespaces defined by
    * the jQuery selection.
    *
    * @methodOf jQuery#
    * @name jQuery#safeCurie
    * @param {String} safeCurie The safe CURIE string to be parsed. See {@link jQuery.safeCurie} for details on how safe CURIE strings are processed.
    * @param {Object} options   The CURIE parsing options.
    *        See {@link jQuery.safeCurie} for details of the supported options.
    *        The namespace declarations declared on the current jQuery
    *        selection (and inherited from any ancestor elements) will automatically
    *        be included in the options.namespaces property.
    * @returns {jQuery.uri}
    * @see jQuery.safeCurie
    */
  $.fn.safeCurie = function (safeCurie, options) {
    var opts = $.extend({}, $.fn.curie.defaults, { namespaces: this.xmlns() }, options || {});
    return $.safeCurie(safeCurie, opts);
  };

   /**
    * Creates a CURIE string from a URI string using the namespace
    * bindings in the context of the current jQuery selection.
    *
    * @methodOf jQuery#
    * @name jQuery#createCurie
    * @param {String|jQuery.uri} uri The URI string to be converted to a CURIE
    * @param {Object} options the CURIE parsing options.
    *        See {@link jQuery.createCurie} for details of the supported options.
    *        The namespace declarations declared on the current jQuery
    *        selection (and inherited from any ancestor elements) will automatically
    *        be included in the options.namespaces property.
    * @returns {String}
    * @see jQuery.createCurie
    */
  $.fn.createCurie = function (uri, options) {
    var opts = $.extend({}, $.fn.curie.defaults, { namespaces: this.xmlns() }, options || {});
    return $.createCurie(uri, opts);
  };

  $.fn.curie.defaults = {
    reserved: [
      'alternate', 'appendix', 'bookmark', 'cite', 'chapter', 'contents', 'copyright',
      'first', 'glossary', 'help', 'icon', 'index', 'last', 'license', 'meta', 'next',
      'p3pv1', 'prev', 'role', 'section', 'stylesheet', 'subsection', 'start', 'top', 'up'
    ],
    reservedNamespace: 'http://www.w3.org/1999/xhtml/vocab#',
    defaultNamespace: undefined,
    charcase: 'lower'
  };

})(jQuery);
/*
 * jQuery RDF @VERSION
 *
 * Copyright (c) 2008,2009 Jeni Tennison
 * Licensed under the MIT (MIT-LICENSE.txt)
 *
 * Depends:
 *  jquery.uri.js
 *  jquery.xmlns.js
 *  jquery.datatype.js
 *  jquery.curie.js
 *  jquery.json.js
 */
/**
 * @fileOverview jQuery RDF
 * @author <a href="mailto:jeni@jenitennison.com">Jeni Tennison</a>
 * @copyright (c) 2008,2009 Jeni Tennison
 * @license MIT license (MIT-LICENSE.txt)
 * @version 1.0
 */
/**
 * @exports $ as jQuery
 */
/**
 * @ignore
 */
(function ($) {
  var
    memResource = {},
    memBlank = {},
    memLiteral = {},
    memTriple = {},
    memPattern = {},
    
    xsdNs = "http://www.w3.org/2001/XMLSchema#",
    rdfNs = "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
    rdfsNs = "http://www.w3.org/2000/01/rdf-schema#",
    
    uriRegex = /^<(([^>]|\\>)*)>$/,
    literalRegex = /^("""((\\"|[^"])*)"""|"((\\"|[^"])*)")(@([a-z]+(-[a-z0-9]+)*)|\^\^(.+))?$/,
    tripleRegex = /(("""((\\"|[^"])*)""")|("(\\"|[^"]|)*")|(<(\\>|[^>])*>)|\S)+/g,

    blankNodeSeed = databankSeed = new Date().getTime() % 1000,
    blankNodeID = function () {
      blankNodeSeed += 1;
      return 'b' + blankNodeSeed.toString(16);
    },

    databankID = function () {
      databankSeed += 1;
      return 'data' + databankSeed.toString(16);
    },
    databanks = {},

    documentQueue = {},

    subject = function (subject, opts) {
      if (typeof subject === 'string') {
        try {
          return $.rdf.resource(subject, opts);
        } catch (e) {
          try {
            return $.rdf.blank(subject, opts);
          } catch (f) {
            throw "Bad Triple: Subject " + subject + " is not a resource: " + f;
          }
        }
      } else {
        return subject;
      }
    },

    property = function (property, opts) {
      if (property === 'a') {
        return $.rdf.type;
      } else if (typeof property === 'string') {
        try {
          return $.rdf.resource(property, opts);
        } catch (e) {
          throw "Bad Triple: Property " + property + " is not a resource: " + e;
        }
      } else {
        return property;
      }
    },

    object = function (object, opts) {
      if (typeof object === 'string') {
        try {
          return $.rdf.resource(object, opts);
        } catch (e) {
          try {
            return $.rdf.blank(object, opts);
          } catch (f) {
            try {
              return $.rdf.literal(object, opts);
            } catch (g) {
              throw "Bad Triple: Object " + object + " is not a resource or a literal " + g;
            }
          }
        }
      } else {
        return object;
      }
    },

    testResource = function (resource, filter, existing) {
      var variable;
      if (typeof filter === 'string') {
        variable = filter.substring(1);
        if (existing[variable] && existing[variable] !== resource) {
          return null;
        } else {
          existing[variable] = resource;
          return existing;
        }
      } else if (filter === resource) {
        return existing;
      } else {
        return null;
      }
    },

    findMatches = function (databank, pattern) {
      if (databank.union === undefined) {
        if (pattern.subject.type !== undefined) {
          if (databank.subjectIndex[pattern.subject] === undefined) {
            return [];
          }
          return $.map(databank.subjectIndex[pattern.subject], function (triple) {
            var bindings = pattern.exec(triple);
            return bindings === null ? null : { bindings: bindings, triples: [triple] };
          });
        } else if (pattern.object.type === 'uri' || pattern.object.type === 'bnode') {
          if (databank.objectIndex[pattern.object] === undefined) {
            return [];
          }
          return $.map(databank.objectIndex[pattern.object], function (triple) {
            var bindings = pattern.exec(triple);
            return bindings === null ? null : { bindings: bindings, triples: [triple] };
          });
        } else if (pattern.property.type !== undefined) {
          if (databank.propertyIndex[pattern.property] === undefined) {
            return [];
          }
          return $.map(databank.propertyIndex[pattern.property], function (triple) {
            var bindings = pattern.exec(triple);
            return bindings === null ? null : { bindings: bindings, triples: [triple] };
          });
        }
      }
      return $.map(databank.triples(), function (triple) {
        var bindings = pattern.exec(triple);
        return bindings === null ? null : { bindings: bindings, triples: [triple] };
      });
    },

    mergeMatches = function (existingMs, newMs, optional) {
      return $.map(existingMs, function (existingM, i) {
        var compatibleMs = $.map(newMs, function (newM) {
          // For newM to be compatible with existingM, all the bindings
          // in newM must either be the same as in existingM, or not
          // exist in existingM
          var k, b, isCompatible = true;
          for (k in newM.bindings) {
            b = newM.bindings[k];
            if (!(existingM.bindings[k] === undefined ||
                  existingM.bindings[k] === b)) {
              isCompatible = false;
              break;
            }
          }
          return isCompatible ? newM : null;
        });
        if (compatibleMs.length > 0) {
          return $.map(compatibleMs, function (compatibleM) {
            return {
              bindings: $.extend({}, existingM.bindings, compatibleM.bindings),
              triples: unique(existingM.triples.concat(compatibleM.triples))
            };
          });
        } else {
          return optional ? existingM : null;
        }
      });
    },

    registerQuery = function (databank, query) {
      var s, p, o;
      if (query.filterExp !== undefined && !$.isFunction(query.filterExp)) {
        if (databank.union === undefined) {
          s = typeof query.filterExp.subject === 'string' ? '' : query.filterExp.subject;
          p = typeof query.filterExp.property === 'string' ? '' : query.filterExp.property;
          o = typeof query.filterExp.object === 'string' ? '' : query.filterExp.object;
          if (databank.queries[s] === undefined) {
            databank.queries[s] = {};
          }
          if (databank.queries[s][p] === undefined) {
            databank.queries[s][p] = {};
          }
          if (databank.queries[s][p][o] === undefined) {
            databank.queries[s][p][o] = [];
          }
          databank.queries[s][p][o].push(query);
        } else {
          $.each(databank.union, function (i, databank) {
            registerQuery(databank, query);
          });
        }
      }
    },

    resetQuery = function (query) {
      query.length = 0;
      query.matches = [];
      $.each(query.children, function (i, child) {
        resetQuery(child);
      });
      $.each(query.partOf, function (i, union) {
        resetQuery(union);
      });
    },

    updateQuery = function (query, matches) {
      if (matches.length > 0) {
        $.each(query.children, function (i, child) {
          leftActivate(child, matches);
        });
        $.each(query.partOf, function (i, union) {
          updateQuery(union, matches);
        });
        $.each(matches, function (i, match) {
          query.matches.push(match);
          Array.prototype.push.call(query, match.bindings);
        });
      }
    },

    filterMatches = function (matches, variables) {
      var i, bindings, triples, j, k, variable, value, nvariables = variables.length,
        newbindings, match = {}, keyobject = {}, keys = {}, filtered = [];
      for (i = 0; i < matches.length; i += 1) {
        bindings = matches[i].bindings;
        triples = matches[i].triples;
        keyobject = keys;
        for (j = 0; j < nvariables; j += 1) {
          variable = variables[j];
          value = bindings[variable];
          if (j === nvariables - 1) {
            if (keyobject[value] === undefined) {
              match = { bindings: {}, triples: triples };
              for (k = 0; k < nvariables; k += 1) {
                match.bindings[variables[k]] = bindings[variables[k]];
              }
              keyobject[value] = match;
              filtered.push(match);
            } else {
              match = keyobject[value];
              match.triples = match.triples.concat(triples);
            }
          } else {
            if (keyobject[value] === undefined) {
              keyobject[value] = {};
            }
            keyobject = keyobject[value];
          }
        }
      }
      return filtered;
    },

    renameMatches = function (matches, old) {
      var i, match, newMatch, keys = {}, renamed = [];
      for (i = 0; i < matches.length; i += 1) {
        match = matches[i];
        if (keys[match.bindings[old]] === undefined) {
          newMatch = {
            bindings: { node: match.bindings[old] },
            triples: match.triples
          };
          renamed.push(newMatch);
          keys[match.bindings[old]] = newMatch;
        } else {
          newMatch = keys[match.bindings[old]];
          newMatch.triples = newMatch.triples.concat(match.triples);
        }
      }
      return renamed;
    },

    leftActivate = function (query, matches) {
      var newMatches;
      if (query.union === undefined) {
        if (query.top || query.parent.top) {
          newMatches = query.alphaMemory;
        } else {
          matches = matches || query.parent.matches;
          if ($.isFunction(query.filterExp)) {
            newMatches = $.map(matches, function (match, i) {
              return query.filterExp.call(match.bindings, i, match.bindings, match.triples) ? match : null;
            });
          } else if (query.filterExp !== undefined) {
            newMatches = mergeMatches(matches, query.alphaMemory, query.filterExp.optional);
          } else {
            newMatches = matches;
          }
        }
      } else {
        newMatches = $.map(query.union, function (q) {
          return q.matches;
        });
      }
      if (query.selections !== undefined) {
        newMatches = filterMatches(newMatches, query.selections);
      } else if (query.navigate !== undefined) {
        newMatches = renameMatches(newMatches, query.navigate);
      }
      updateQuery(query, newMatches);
    },

    rightActivate = function (query, match) {
      var newMatches;
      if (query.filterExp.optional) {
        resetQuery(query);
        leftActivate(query);
      } else {
        if (query.top || query.parent.top) {
          newMatches = [match];
        } else {
          newMatches = mergeMatches(query.parent.matches, [match], false);
        }
        updateQuery(query, newMatches);
      }
    },

    addToQuery = function (query, triple) {
      var match,
        bindings = query.filterExp.exec(triple);
      if (bindings !== null) {
        match = { triples: [triple], bindings: bindings };
        query.alphaMemory.push(match);
        rightActivate(query, match);
      }
    },

    removeFromQuery = function (query, triple) {
      query.alphaMemory.splice($.inArray(triple, query.alphaMemory), 1);
      resetQuery(query);
      leftActivate(query);
    },

    addToQueries = function (queries, triple) {
      $.each(queries, function (i, query) {
        addToQuery(query, triple);
      });
    },

    removeFromQueries = function (queries, triple) {
      $.each(queries, function (i, query) {
        removeFromQuery(query, triple);
      });
    },

    addToDatabankQueries = function (databank, triple) {
      var s = triple.subject,
        p = triple.property,
        o = triple.object;
      if (databank.union === undefined) {
        if (databank.queries[s] !== undefined) {
          if (databank.queries[s][p] !== undefined) {
            if (databank.queries[s][p][o] !== undefined) {
              addToQueries(databank.queries[s][p][o], triple);
            }
            if (databank.queries[s][p][''] !== undefined) {
              addToQueries(databank.queries[s][p][''], triple);
            }
          }
          if (databank.queries[s][''] !== undefined) {
            if (databank.queries[s][''][o] !== undefined) {
              addToQueries(databank.queries[s][''][o], triple);
            }
            if (databank.queries[s][''][''] !== undefined) {
              addToQueries(databank.queries[s][''][''], triple);
            }
          }
        }
        if (databank.queries[''] !== undefined) {
          if (databank.queries[''][p] !== undefined) {
            if (databank.queries[''][p][o] !== undefined) {
              addToQueries(databank.queries[''][p][o], triple);
            }
            if (databank.queries[''][p][''] !== undefined) {
              addToQueries(databank.queries[''][p][''], triple);
            }
          }
          if (databank.queries[''][''] !== undefined) {
            if (databank.queries[''][''][o] !== undefined) {
              addToQueries(databank.queries[''][''][o], triple);
            }
            if (databank.queries[''][''][''] !== undefined) {
              addToQueries(databank.queries[''][''][''], triple);
            }
          }
        }
      } else {
        $.each(databank.union, function (i, databank) {
          addToDatabankQueries(databank, triple);
        });
      }
    },

    removeFromDatabankQueries = function (databank, triple) {
      var s = triple.subject,
        p = triple.property,
        o = triple.object;
      if (databank.union === undefined) {
        if (databank.queries[s] !== undefined) {
          if (databank.queries[s][p] !== undefined) {
            if (databank.queries[s][p][o] !== undefined) {
              removeFromQueries(databank.queries[s][p][o], triple);
            }
            if (databank.queries[s][p][''] !== undefined) {
              removeFromQueries(databank.queries[s][p][''], triple);
            }
          }
          if (databank.queries[s][''] !== undefined) {
            if (databank.queries[s][''][o] !== undefined) {
              removeFromQueries(databank.queries[s][''][o], triple);
            }
            if (databank.queries[s][''][''] !== undefined) {
              removeFromQueries(databank.queries[s][''][''], triple);
            }
          }
        }
        if (databank.queries[''] !== undefined) {
          if (databank.queries[''][p] !== undefined) {
            if (databank.queries[''][p][o] !== undefined) {
              removeFromQueries(databank.queries[''][p][o], triple);
            }
            if (databank.queries[''][p][''] !== undefined) {
              removeFromQueries(databank.queries[''][p][''], triple);
            }
          }
          if (databank.queries[''][''] !== undefined) {
            if (databank.queries[''][''][o] !== undefined) {
              removeFromQueries(databank.queries[''][''][o], triple);
            }
            if (databank.queries[''][''][''] !== undefined) {
              removeFromQueries(databank.queries[''][''][''], triple);
            }
          }
        }
      } else {
        $.each(databank.union, function (i, databank) {
          removeFromDatabankQueries(databank, triple);
        });
      }
    },
    
    group = function (bindings, variables, base) {
      var variable = variables[0], grouped = {}, results = [], i, newbase;
      base = base || {};
      if (variables.length === 0) {
        for (i = 0; i < bindings.length; i += 1) {
          for (v in bindings[i]) {
            if (base[v] === undefined) {
              base[v] = [];
            }
            if ($.isArray(base[v])) {
              base[v].push(bindings[i][v]);
            }
          }
        }
        return [base];
      }
      // collect together the grouped results
      for (i = 0; i < bindings.length; i += 1) {
        key = bindings[i][variable];
        if (grouped[key] === undefined) {
          grouped[key] = [];
        }
        grouped[key].push(bindings[i]);
      }
      // call recursively on each group
      variables = variables.splice(1, 1);
      for (v in grouped) {
        newbase = $.extend({}, base);
        newbase[variable] = grouped[v][0][variable];
        results = results.concat(group(grouped[v], variables, newbase));
      }
      return results;
    },
    
    queue = function (databank, url, callbacks) {
      if (documentQueue[databank.id] === undefined) {
        documentQueue[databank.id] = {};
      }
      if (documentQueue[databank.id][url] === undefined) {
        documentQueue[databank.id][url] = callbacks;
        return false;
      }
      return true;
    },
    
    dequeue = function (databank, url, result, args) {
      var callbacks = documentQueue[databank.id][url];
      if ($.isFunction(callbacks[result])) {
        callbacks[result].call(databank, args);
      }
      documentQueue[databank.id][url] = undefined;
    },

    unique = function( b ) {
      var a = [];
      var l = b.length;
      for(var i=0; i<l; i++) {
        for(var j=i+1; j<l; j++) {
          // If b[i] is found later in the array
          if (b[i] === b[j])
            j = ++i;
        }
        a.push(b[i]);
      }
      return a;
     };


  $.typedValue.types['http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral'] = {
    regex: /^.*$/m,
    strip: false,
    value: function (v) {
      return v;
    }
  };

  /**
   * <p>Creates a new jQuery.rdf object. This should be invoked as a method rather than constructed using new; indeed you will usually want to generate these objects using a method such as {@link jQuery#rdf} or {@link jQuery.rdf#where}.</p>
   * @class <p>A jQuery.rdf object represents the results of a query over its {@link jQuery.rdf#databank}. The results of a query are a sequence of objects which represent the bindings of values to the variables used in filter expressions specified using {@link jQuery.rdf#where} or {@link jQuery.rdf#optional}. Each of the objects in this sequence has associated with it a set of triples that are the sources for the variable bindings, which you can get at using {@link jQuery.rdf#sources}.</p>
    * <p>The {@link jQuery.rdf} object itself is a lot like a {@link jQuery} object. It has a {@link jQuery.rdf#length} and the individual matches can be accessed using <code>[<var>n</var>]</code>, but you can also iterate through the matches using {@link jQuery.rdf#map} or {@link jQuery.rdf#each}.</p>
    * <p>{@link jQuery.rdf} is designed to mirror the functionality of <a href="http://www.w3.org/TR/rdf-sparql-query/">SPARQL</a> while providing an interface that's familiar and easy to use for jQuery programmers.</p>
   * @param {Object} [options]
   * @param {jQuery.rdf.databank} [options.databank] The databank that this query should operate over.
   * @param {jQuery.rdf.triple[]} [options.triples] A set of triples over which the query operates; this is only used if options.databank isn't specified, in which case a new databank with these triples is generated.
   * @param {Object} [options.namespaces] An object representing a set of namespace bindings. Rather than passing this in when you construct the {@link jQuery.rdf} instance, you will usually want to use the {@link jQuery.rdf#prefix} method.
   * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the query.
   * @returns {jQuery.rdf}
   * @example rdf = jQuery.rdf();
   * @see jQuery#rdf
   */
  $.rdf = function (options) {
    return new $.rdf.fn.init(options);
  };

  $.rdf.fn = $.rdf.prototype = {
    /**
     * The version of rdfQuery.
     * @type String
     */
    rdfquery: '1.1',

    init: function (options) {
      var databanks, i;
      options = options || {};
      /* must specify either a parent or a union, otherwise it's the top */
      this.parent = options.parent;
      this.union = options.union;
      this.top = this.parent === undefined && this.union === undefined;
      if (this.union === undefined) {
        if (options.databank === undefined) {
          /**
           * The databank over which this query operates.
           * @type jQuery.rdf.databank
           */
          this.databank = this.parent === undefined ? $.rdf.databank(options.triples, options) : this.parent.databank;
        } else {
          this.databank = options.databank;
        }
      } else {
        databanks = $.map(this.union, function (query) {
          return query.databank;
        });
        databanks = unique(databanks);
        if (databanks[1] !== undefined) {
          this.databank = $.rdf.databank(undefined, { union: databanks });
        } else {
          this.databank = databanks[0];
        }
      }
      this.children = [];
      this.partOf = [];
      this.filterExp = options.filter;
      this.selections = options.distinct;
      this.navigate = options.navigate;
      this.alphaMemory = [];
      this.matches = [];
      /**
       * The number of matches represented by the {@link jQuery.rdf} object.
       * @type Integer
       */
      this.length = 0;
      if (this.filterExp !== undefined) {
        if (!$.isFunction(this.filterExp)) {
          registerQuery(this.databank, this);
          this.alphaMemory = findMatches(this.databank, this.filterExp);
        }
      } else if (options.nodes !== undefined) {
        this.alphaMemory = [];
        for (i = 0; i < options.nodes.length; i += 1) {
          this.alphaMemory.push({
            bindings: { node: options.nodes[i] },
            triples: []
          });
        }
      }
      leftActivate(this);
      return this;
    },

    /**
     * Sets or returns the base URI of the {@link jQuery.rdf#databank}.
     * @param {String|jQuery.uri} [base]
     * @returns A {@link jQuery.uri} if no base URI is specified, otherwise returns this {@link jQuery.rdf} object.
     * @example baseURI = jQuery('html').rdf().base();
     * @example jQuery('html').rdf().base('http://www.example.org/');
     * @see jQuery.rdf.databank#base
     */
    base: function (base) {
      if (base === undefined) {
        return this.databank.base();
      } else {
        this.databank.base(base);
        return this;
      }
    },

    /**
     * Sets or returns a namespace binding on the {@link jQuery.rdf#databank}.
     * @param {String} [prefix]
     * @param {String} [namespace]
     * @returns {Object|jQuery.uri|jQuery.rdf} If no prefix or namespace is specified, returns an object providing all namespace bindings on the {@link jQuery.rdf.databank}. If a prefix is specified without a namespace, returns the {@link jQuery.uri} associated with that prefix. Otherwise returns this {@link jQuery.rdf} object after setting the namespace binding.
     * @example namespace = jQuery('html').rdf().prefix('foaf');
     * @example jQuery('html').rdf().prefix('foaf', 'http://xmlns.com/foaf/0.1/');
     * @see jQuery.rdf.databank#prefix
     */
    prefix: function (prefix, namespace) {
      if (namespace === undefined) {
        return this.databank.prefix(prefix);
      } else {
        this.databank.prefix(prefix, namespace);
        return this;
      }
    },

    /**
     * Adds a triple to the {@link jQuery.rdf#databank} or another {@link jQuery.rdf} object to create a union.
     * @param {String|jQuery.rdf.triple|jQuery.rdf.pattern|jQuery.rdf} triple The triple, {@link jQuery.rdf.pattern} or {@link jQuery.rdf} object to be added to this one. If the triple is a {@link jQuery.rdf} object, the two queries are unioned together. If the triple is a string, it's parsed as a {@link jQuery.rdf.pattern}. The pattern will be completed using the current matches on the {@link jQuery.rdf} object to create multiple triples, one for each set of bindings.
     * @param {Object} [options]
     * @param {Object} [options.namespaces] An object representing a set of namespace bindings used to interpret CURIEs within the triple. Defaults to the namespace bindings defined on the {@link jQuery.rdf#databank}.
     * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the triple. Defaults to the base URI defined on the {@link jQuery.rdf#databank}.
     * @returns {jQuery.rdf} This {@link jQuery.rdf} object.
     * @example
     * var rdf = $.rdf()
     *   .prefix('dc', ns.dc)
     *   .prefix('foaf', ns.foaf)
     *   .add('&lt;photo1.jpg> dc:creator &lt;http://www.blogger.com/profile/1109404> .')
     *   .add('&lt;http://www.blogger.com/profile/1109404> foaf:img &lt;photo1.jpg> .');
     * @example
     * var rdfA = $.rdf()
     *   .prefix('dc', ns.dc)
     *   .add('&lt;photo1.jpg> dc:creator "Jane"');
     * var rdfB = $.rdf()
     *   .prefix('foaf', ns.foaf)
     *   .add('&lt;photo1.jpg> foaf:depicts "Jane"');
     * var rdf = rdfA.add(rdfB);
     * @see jQuery.rdf.databank#add
     */
    add: function (triple, options) {
      var query, databank;
      if (triple.rdfquery !== undefined) {
        if (triple.top) {
          databank = this.databank.add(triple.databank);
          query = $.rdf({ parent: this.parent, databank: databank });
          return query;
        } else if (this.top) {
          databank = triple.databank.add(this.databank);
          query = $.rdf({ parent: triple.parent, databank: databank });
          return query;
        } else if (this.union === undefined) {
          query = $.rdf({ union: [this, triple] });
          this.partOf.push(query);
          triple.partOf.push(query);
          return query;
        } else {
          this.union.push(triple);
          triple.partOf.push(this);
        }
      } else {
        if (typeof triple === 'string') {
          options = $.extend({}, { base: this.base(), namespaces: this.prefix(), source: triple }, options);
          triple = $.rdf.pattern(triple, options);
        }
        if (triple.isFixed()) {
          this.databank.add(triple.triple(), options);
        } else {
          query = this;
          this.each(function (i, data) {
            var t = triple.triple(data);
            if (t !== null) {
              query.databank.add(t, options);
            }
          });
        }
      }
      return this;
    },

    /**
     * Removes a triple or several triples from the {@link jQuery.rdf#databank}.
     * @param {String|jQuery.rdf.triple|jQuery.rdf.pattern} triple The triple to be removed, or a {@link jQuery.rdf.pattern} that matches the triples that should be removed.
     * @param {Object} [options]
     * @param {Object} [options.namespaces] An object representing a set of namespace bindings used to interpret any CURIEs within the triple or pattern. Defaults to the namespace bindings defined on the {@link jQuery.rdf#databank}.
     * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the triple or pattern. Defaults to the base URI defined on the {@link jQuery.rdf#databank}.
     * @returns {jQuery.rdf} The {@link jQuery.rdf} object itself.
     * @example
     * var rdf = $('html').rdf()
     *   .prefix('foaf', ns.foaf)
     *   .where('?person foaf:givenname ?gname')
     *   .where('?person foaf:family_name ?fname')
     *   .remove('?person foaf:family_name ?fname');
     * @see jQuery.rdf.databank#remove
     */
    remove: function (triple, options) {
      if (typeof triple === 'string') {
        options = $.extend({}, { base: this.base(), namespaces: this.prefix() }, options);
        triple = $.rdf.pattern(triple, options);
      }
      if (triple.isFixed()) {
        this.databank.remove(triple.triple(), options);
      } else {
        query = this;
        this.each(function (i, data) {
          var t = triple.triple(data);
          if (t !== null) {
            query.databank.remove(t, options);
          }
        });
      }
      return this;
    },

    /**
     * Loads some data into the {@link jQuery.rdf#databank}
     * @param data
     * @param {Object} [options]
     * @see jQuery.rdf.databank#load
     */
    load: function (data, options) {
      var rdf = this,
        options = options || {},
        success = options.success;
      if (success !== undefined) {
        options.success = function () {
          success.call(rdf);
        }
      }
      this.databank.load(data, options);
      return this;
    },

    /**
     * Creates a new {@link jQuery.rdf} object whose databank contains all the triples in this object's databank except for those in the argument's databank.
     * @param {jQuery.rdf} query
     * @see jQuery.rdf.databank#except
     */
    except: function (query) {
      return $.rdf({ databank: this.databank.except(query.databank) });
    },

    /**
     * Creates a new {@link jQuery.rdf} object that is the result of filtering the matches on this {@link jQuery.rdf} object based on the filter that's passed into it.
     * @param {String|jQuery.rdf.pattern} filter An expression that filters the triples in the {@link jQuery.rdf#databank} to locate matches based on the matches on this {@link jQuery.rdf} object. If it's a string, the filter is parsed as a {@link jQuery.rdf.pattern}.
     * @param {Object} [options]
     * @param {Object} [options.namespaces] An object representing a set of namespace bindings used to interpret any CURIEs within the pattern. Defaults to the namespace bindings defined on the {@link jQuery.rdf#databank}.
     * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the pattern. Defaults to the base URI defined on the {@link jQuery.rdf#databank}.
     * @param {boolean} [options.optional] Not usually used (use {@link jQuery.rdf#optional} instead).
     * @returns {jQuery.rdf} A new {@link jQuery.rdf} object whose {@link jQuery.rdf#parent} is this {@link jQuery.rdf}.
     * @see jQuery.rdf#optional
     * @see jQuery.rdf#filter
     * @see jQuery.rdf#about
     * @example
     * var rdf = $.rdf()
     *   .prefix('foaf', ns.foaf)
     *   .add('_:a foaf:givenname   "Alice" .')
     *   .add('_:a foaf:family_name "Hacker" .')
     *   .add('_:b foaf:givenname   "Bob" .')
     *   .add('_:b foaf:family_name "Hacker" .')
     *   .where('?person foaf:family_name "Hacker"')
     *   .where('?person foaf:givenname "Bob");
     */ 
    where: function (filter, options) {
      var query, base, namespaces, optional;
      options = options || {};
      if (typeof filter === 'string') {
        base = options.base || this.base();
        namespaces = $.extend({}, this.prefix(), options.namespaces || {});
        optional = options.optional || false;
        filter = $.rdf.pattern(filter, { namespaces: namespaces, base: base, optional: optional });
      }
      query = $.rdf($.extend({}, options, { parent: this, filter: filter }));
      this.children.push(query);
      return query;
    },

    /**
     * Creates a new {@link jQuery.rdf} object whose set of bindings might optionally include those based on the filter pattern.
     * @param {String|jQuery.rdf.pattern} filter An pattern for a set of bindings that might be added to those in this {@link jQuery.rdf} object.
     * @param {Object} [options]
     * @param {Object} [options.namespaces] An object representing a set of namespace bindings used to interpret any CURIEs within the pattern. Defaults to the namespace bindings defined on the {@link jQuery.rdf#databank}.
     * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the pattern. Defaults to the base URI defined on the {@link jQuery.rdf#databank}.
     * @returns {jQuery.rdf} A new {@link jQuery.rdf} object whose {@link jQuery.rdf#parent} is this {@link jQuery.rdf}.
     * @see jQuery.rdf#where
     * @see jQuery.rdf#filter
     * @see jQuery.rdf#about
     * @example
     * var rdf = $.rdf()
     *   .prefix('foaf', 'http://xmlns.com/foaf/0.1/')
     *   .prefix('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#')
     *   .add('_:a  rdf:type        foaf:Person .')
     *   .add('_:a  foaf:name       "Alice" .')
     *   .add('_:a  foaf:mbox       &lt;mailto:alice@example.com> .')
     *   .add('_:a  foaf:mbox       &lt;mailto:alice@work.example> .')
     *   .add('_:b  rdf:type        foaf:Person .')
     *   .add('_:b  foaf:name       "Bob" .')
     *   .where('?x foaf:name ?name')
     *   .optional('?x foaf:mbox ?mbox');
     */
    optional: function (filter, options) {
      return this.where(filter, $.extend({}, options || {}, { optional: true }));
    },

    /**
     * Creates a new {@link jQuery.rdf} object whose set of bindings include <code>property</code> and <code>value</code> for every triple that is about the specified resource.
     * @param {String|jQuery.rdf.resource} resource The subject of the matching triples.
     * @param {Object} [options]
     * @param {Object} [options.namespaces] An object representing a set of namespace bindings used to interpret the resource if it's a CURIE. Defaults to the namespace bindings defined on the {@link jQuery.rdf#databank}.
     * @param {String|jQuery.uri} [options.base] The base URI used to interpret the resource if it's a relative URI (wrapped in <code>&lt;</code> and <code>&gt;</code>). Defaults to the base URI defined on the {@link jQuery.rdf#databank}.
     * @returns {jQuery.rdf} A new {@link jQuery.rdf} object whose {@link jQuery.rdf#parent} is this {@link jQuery.rdf}.
     * @see jQuery.rdf#where
     * @see jQuery.rdf#optional
     * @see jQuery.rdf#filter
     * @example
     * var rdf = $.rdf()
     *   .prefix('dc', ns.dc)
     *   .prefix('foaf', ns.foaf)
     *   .add('&lt;photo1.jpg> dc:creator &lt;http://www.blogger.com/profile/1109404> .')
     *   .add('&lt;http://www.blogger.com/profile/1109404> foaf:img &lt;photo1.jpg> .')
     *   .add('&lt;photo2.jpg> dc:creator &lt;http://www.blogger.com/profile/1109404> .')
     *   .add('&lt;http://www.blogger.com/profile/1109404> foaf:img &lt;photo2.jpg> .')
     *   .about('&lt;http://www.blogger.com/profile/1109404>');
     */
    about: function (resource, options) {
      return this.where(resource + ' ?property ?value', options);
    },

    /**
     * Creates a new {@link jQuery.rdf} object whose set of bindings include only those that satisfy some arbitrary condition. There are two main ways to call this method: with two arguments in which case the first is a binding to be tested and the second represents a condition on the test, or with one argument which is a function that should return true for acceptable bindings.
     * @param {Function|String} property <p>In the two-argument version, this is the name of a property to be tested against the condition specified in the second argument. In the one-argument version, this is a function in which <code>this</code> is an object whose properties are a set of {@link jQuery.rdf.resource}, {@link jQuery.rdf.literal} or {@link jQuery.rdf.blank} objects and whose arguments are:</p>
     * <dl>
     *   <dt>i</dt>
     *   <dd>The index of the set of bindings amongst the other matches</dd>
     *   <dt>bindings</dt>
     *   <dd>An object representing the bindings (the same as <code>this</code>)</dd>
     *   <dt>triples</dt>
     *   <dd>The {@link jQuery.rdf.triple}s that underly this set of bindings</dd>
     * </dl>
     * @param {RegExp|String} condition In the two-argument version of this function, the condition that the property's must match. If it is a regular expression, the value must match the regular expression. If it is a {@link jQuery.rdf.literal}, the value of the literal must match the property's value. Otherwise, they must be the same resource.
     * @returns {jQuery.rdf} A new {@link jQuery.rdf} object whose {@link jQuery.rdf#parent} is this {@link jQuery.rdf}.
     * @see jQuery.rdf#where
     * @see jQuery.rdf#optional
     * @see jQuery.rdf#about
     * @example
     * var rdf = $.rdf()
     *   .prefix('foaf', 'http://xmlns.com/foaf/0.1/')
     *   .add('_:a foaf:surname "Jones" .')
     *   .add('_:b foaf:surname "Macnamara" .')
     *   .add('_:c foaf:surname "O\'Malley"')
     *   .add('_:d foaf:surname "MacFee"')
     *   .where('?person foaf:surname ?surname')
     *     .filter('surname', /^Ma?c/)
     *       .each(function () { scottish.push(this.surname.value); })
     *     .end()
     *     .filter('surname', /^O'/)
     *       .each(function () { irish.push(this.surname.value); })
     *     .end();
     * @example
     * var rdf = $.rdf()
     *   .prefix('foaf', 'http://xmlns.com/foaf/0.1/')
     *   .add('_:a foaf:surname "Jones" .')
     *   .add('_:b foaf:surname "Macnamara" .')
     *   .add('_:c foaf:surname "O\'Malley"')
     *   .add('_:d foaf:surname "MacFee"')
     *   .where('?person foaf:surname ?surname')
     *   .filter(function () { return this.surname !== "Jones"; })
     */
    filter: function (property, condition) {
      var func, query;
      if (typeof property === 'string') {
        if (condition.constructor === RegExp) {
          /** @ignore func */
          func = function () {
            return condition.test(this[property].value);
          };
        } else {
          func = function () {
            return this[property].type === 'literal' ? this[property].value === condition : this[property] === condition;
          };
        }
      } else {
        func = property;
      }
      query = $.rdf({ parent: this, filter: func });
      this.children.push(query);
      return query;
    },

    /**
     * Creates a new {@link jQuery.rdf} object containing one binding for each selected resource.
     * @param {String|Object} node The node to be selected. If this is a string beginning with a question mark the resources are those identified by the bindings of that value in the currently selected bindings. Otherwise, only the named resource is selected as the node.
     * @returns {jQuery.rdf} A new {@link jQuery.rdf} object.
     * @see jQuery.rdf#find
     * @see jQuery.rdf#back
     * @example
     * // returns an rdfQuery object with a pointer to <http://example.com/aReallyGreatBook>
     * var rdf = $('html').rdf()
     *   .node('<http://example.com/aReallyGreatBook>');
     */
    node: function (resource) {
      var variable, query;
      if (resource.toString().substring(0, 1) === '?') {
        variable = resource.toString().substring(1);
        query = $.rdf({ parent: this, navigate: variable });
      } else {
        if (typeof resource === 'string') {
          resource = object(resource, { namespaces: this.prefix(), base: this.base() });
        }
        query = $.rdf({ parent: this, nodes: [resource] });
      }
      this.children.push(query);
      return query;
    },
    
    /**
     * Navigates from the resource identified by the 'node' binding to another node through the property passed as the argument.
     * @param {String|Object} property The property whose value will be the new node.
     * @returns {jQuery.rdf} A new {@link jQuery.rdf} object whose {@link jQuery.rdf#parent} is this {@link jQuery.rdf}.
     * @see jQuery.rdf#back
     * @see jQuery.rdf#node
     * @example
     * var creators = $('html').rdf()
     *   .node('<>')
     *   .find('dc:creator');
     */
    find: function (property) {
      return this.where('?node ' + property + ' ?object', { navigate: 'object' });
    },
    
    /**
     * Navigates from the resource identified by the 'node' binding to another node through the property passed as the argument, like {jQuery.rdf#find}, but backwards.
     * @param {String|Object} property The property whose value will be the new node.
     * @returns {jQuery.rdf} A new {@link jQuery.rdf} object whose {@link jQuery.rdf#parent} is this {@link jQuery.rdf}.
     * @see jQuery.rdf#find
     * @see jQuery.rdf#node
     * @example
     * var people = $('html').rdf()
     *   .node('foaf:Person')
     *   .back('rdf:type');
     */
    back: function (property) {
      return this.where('?subject ' + property + ' ?node', { navigate: 'subject' });
    },

    /**
     * Groups the bindings held by this {@link jQuery.rdf} object based on the values of the variables passed as the parameter.
     * @param {String[]} [bindings] The variables to group by. The returned objects will contain all their current properties, but those aside from the specified variables will be arrays listing the relevant values.
     * @returns {jQuery} A jQuery object containing objects representing the grouped bindings.
     * @example
     * // returns one object per person and groups all the names and all the emails together in arrays
     * var grouped = rdf
     *   .where('?person foaf:name ?name')
     *   .where('?person foaf:email ?email')
     *   .group('person');
     * @example
     * // returns one object per surname/firstname pair, with the person property being an array in the resulting objects
     * var grouped = rdf
     *   .where('?person foaf:first_name ?forename')
     *   .where('?person foaf:givenname ?surname')
     *   .group(['surname', 'forename']);
     */
    group: function (bindings) {
      var grouped = {}, results = [], i, key, v;
      if (!$.isArray(bindings)) {
        bindings = [bindings];
      }
      return $(group(this, bindings));
    },

    /**
     * Filters the variable bindings held by this {@link jQuery.rdf} object down to those listed in the bindings parameter. This mirrors the <a href="http://www.w3.org/TR/rdf-sparql-query/#select">SELECT</a> form in SPARQL.
     * @param {String[]} [bindings] The variables that you're interested in. The returned objects will only contain those variables. If bindings is undefined, you will get all the variable bindings in the returned objects.
     * @returns {Object[]} An array of objects with the properties named by the bindings parameter.
     * @example
     * var filtered = rdf
     *   .where('?photo dc:creator ?creator')
     *   .where('?creator foaf:img ?photo');
     * var selected = rdf.select(['creator']);
     */
    select: function (bindings) {
      var s = [], i, j;
      for (i = 0; i < this.length; i += 1) {
        if (bindings === undefined) {
          s[i] = this[i];
        } else {
          s[i] = {};
          for (j = 0; j < bindings.length; j += 1) {
            s[i][bindings[j]] = this[i][bindings[j]];
          }
        }
      }
      return s;
    },

    /**
     * Provides <a href="http://n2.talis.com/wiki/Bounded_Descriptions_in_RDF#Simple_Concise_Bounded_Description">simple concise bounded descriptions</a> of the resources or bindings that are passed in the argument. This mirrors the <a href="http://www.w3.org/TR/rdf-sparql-query/#describe">DESCRIBE</a> form in SPARQL.
     * @param {(String|jQuery.rdf.resource)[]} bindings An array that can contain strings, {@link jQuery.rdf.resource}s or a mixture of the two. Any strings that begin with a question mark (<code>?</code>) are taken as variable names; each matching resource is described by the function.
     * @returns {jQuery} A {@link jQuery} object that contains {@link jQuery.rdf.triple}s that describe the listed resources.
     * @see jQuery.rdf.databank#describe
     * @example
     * $.rdf.dump($('html').rdf().describe(['<photo1.jpg>']));
     * @example
     * $('html').rdf()
     *   .where('?person foaf:img ?picture')
     *   .describe(['?photo'])
     */
    describe: function (bindings) {
      var i, j, binding, resources = [];
      for (i = 0; i < bindings.length; i += 1) {
        binding = bindings[i];
        if (binding.substring(0, 1) === '?') {
          binding = binding.substring(1);
          for (j = 0; j < this.length; j += 1) {
            resources.push(this[j][binding]);
          }
        } else {
          resources.push(binding);
        }
      }
      return this.databank.describe(resources);
    },

    /**
     * Returns a new {@link jQuery.rdf} object that contains only one set of variable bindings. This is designed to mirror the <a href="http://docs.jquery.com/Traversing/eq#index">jQuery#eq</a> method.
     * @param {Integer} n The index number of the match that should be selected.
     * @returns {jQuery.rdf} A new {@link jQuery.rdf} object with just that match.
     * @example
     * var rdf = $.rdf()
     *   .prefix('foaf', 'http://xmlns.com/foaf/0.1/')
     *   .add('_:a  foaf:name       "Alice" .')
     *   .add('_:a  foaf:homepage   <http://work.example.org/alice/> .')
     *   .add('_:b  foaf:name       "Bob" .')
     *   .add('_:b  foaf:mbox       <mailto:bob@work.example> .')
     *   .where('?x foaf:name ?name')
     *   .eq(1);
     */
    eq: function (n) {
      return this.filter(function (i) {
        return i === n;
      });
    },

    /**
     * Returns a {@link jQuery.rdf} object that includes no filtering (and therefore has no matches) over the {@link jQuery.rdf#databank}.
     * @returns {jQuery.rdf} An empty {@link jQuery.rdf} object.
     * @example
     * $('html').rdf()
     *   .where('?person foaf:family_name "Hacker"')
     *   .where('?person foaf:givenname "Alice"')
     *   .each(...do something with Alice Hacker...)
     *   .reset()
     *   .where('?person foaf:family_name "Jones"')
     *   .where('?person foaf:givenname "Bob"')
     *   .each(...do something with Bob Jones...);
     */
    reset: function () {
      var query = this;
      while (query.parent !== undefined) {
        query = query.parent;
      }
      return query;
    },

    /**
     * Returns the parent {@link jQuery.rdf} object, which is equivalent to undoing the most recent filtering operation (such as {@link jQuery.rdf#where} or {@link jQuery.rdf#filter}). This is designed to mirror the <a href="http://docs.jquery.com/Traversing/end">jQuery#end</a> method.
     * @returns {jQuery.rdf}
     * @example
     * $('html').rdf()
     *   .where('?person foaf:family_name "Hacker"')
     *   .where('?person foaf:givenname "Alice"')
     *   .each(...do something with Alice Hacker...)
     *   .end()
     *   .where('?person foaf:givenname "Bob"')
     *   .each(...do something with Bob Hacker...);
     */
    end: function () {
      return this.parent;
    },

    /**
     * Returns the number of matches in this {@link jQuery.rdf} object (equivalent to {@link jQuery.rdf#length}).
     * @returns {Integer} The number of matches in this {@link jQuery.rdf} object.
     * @see jQuery.rdf#length
     */
    size: function () {
      return this.length;
    },

    /**
     * Gets the triples that form the basis of the variable bindings that are the primary product of {@link jQuery.rdf}. Getting hold of the triples can be useful for understanding the facts that form the basis of the variable bindings.
     * @returns {jQuery} A {@link jQuery} object containing arrays of {@link jQuery.rdf.triple} objects. A {@link jQuery} object is returned so that you can easily iterate over the contents.
     * @example
     * $('html').rdf()
     *   .where('?thing a foaf:Person')
     *   .sources()
     *   .each(function () {
     *     ...do something with the array of triples... 
     *   });
     */
    sources: function () {
      return $($.map(this.matches, function (match) {
        // return an array-of-an-array because arrays automatically get expanded by $.map()
        return [match.triples];
      }));
    },

    /**
     * Dumps the triples that form the basis of the variable bindings that are the primary product of {@link jQuery.rdf} into a format that can be shown to the user or sent to a server.
     * @param {Object} [options] Options that control the formatting of the triples. See {@link jQuery.rdf.dump} for details.
     * @see jQuery.rdf.dump
     */
    dump: function (options) {
      var triples = $.map(this.matches, function (match) {
        return match.triples;
      });
      options = $.extend({ namespaces: this.databank.namespaces, base: this.databank.base }, options || {});
      return $.rdf.dump(triples, options);
    },

    /**
     * Either returns the item specified by the argument or turns the {@link jQuery.rdf} object into an array. This mirrors the <a href="http://docs.jquery.com/Core/get">jQuery#get</a> method.
     * @param {Integer} [num] The number of the item to be returned.
     * @returns {Object[]|Object} Returns either a single Object representing variable bindings or an array of such.
     * @example
     * $('html').rdf()
     *   .where('?person a foaf:Person')
     *   .get(0)
     *   .subject
     *   .value;
     */
    get: function (num) {
      return (num === undefined) ? $.makeArray(this) : this[num];
    },

    /**
     * Iterates over the matches held by the {@link jQuery.rdf} object and performs a function on each of them. This mirrors the <a href="http://docs.jquery.com/Core/each">jQuery#each</a> method.
     * @param {Function} callback A function that is called for each match on the {@link jQuery.rdf} object. Within the function, <code>this</code> is set to the object representing the variable bindings. The function can take up to three parameters:
     * <dl>
     *   <dt>i</dt><dd>The index of the match amongst the other matches.</dd>
     *   <dt>bindings</dt><dd>An object representing the variable bindings for the match, the same as <code>this</code>.</dd>
     *   <dt>triples</dt><dd>An array of {@link jQuery.rdf.triple}s associated with the particular match.</dd>
     * </dl>
     * @returns {jQuery.rdf} The {@link jQuery.rdf} object.
     * @see jQuery.rdf#map
     * @example
     * var rdf = $('html').rdf()
     *   .where('?photo dc:creator ?creator')
     *   .where('?creator foaf:img ?photo')
     *   .each(function () {
     *     photos.push(this.photo.value);
     *   });
     */
    each: function (callback) {
      $.each(this.matches, function (i, match) {
        callback.call(match.bindings, i, match.bindings, match.triples);
      });
      return this;
    },

    /**
     * Iterates over the matches held by the {@link jQuery.rdf} object and creates a new {@link jQuery} object that holds the result of applying the passed function to each one. This mirrors the <a href="http://docs.jquery.com/Traversing/map">jQuery#map</a> method.
     * @param {Function} callback A function that is called for each match on the {@link jQuery.rdf} object. Within the function, <code>this</code> is set to the object representing the variable bindings. The function can take up to three parameters and should return some kind of value:
     * <dl>
     *   <dt>i</dt><dd>The index of the match amongst the other matches.</dd>
     *   <dt>bindings</dt><dd>An object representing the variable bindings for the match, the same as <code>this</code>.</dd>
     *   <dt>triples</dt><dd>An array of {@link jQuery.rdf.triple}s associated with the particular match.</dd>
     * </dl>
     * @returns {jQuery} A jQuery object holding the results of the function for each of the matches on the original {@link jQuery.rdf} object.
     * @example
     * var photos = $('html').rdf()
     *   .where('?photo dc:creator ?creator')
     *   .where('?creator foaf:img ?photo')
     *   .map(function () {
     *     return this.photo.value;
     *   });
     */
    map: function (callback) {
      return $($.map(this.matches, function (match, i) {
        // in the callback, "this" is the bindings, and the arguments are swapped from $.map()
        return callback.call(match.bindings, i, match.bindings, match.triples);
      }));
    },

    /**
     * Returns a {@link jQuery} object that wraps this {@link jQuery.rdf} object.
     * @returns {jQuery}
     */
    jquery: function () {
      return $(this);
    }
  };

  $.rdf.fn.init.prototype = $.rdf.fn;

  $.rdf.gleaners = [];
  $.rdf.parsers = {};

  /**
   * Dumps the triples passed as the first argument into a format that can be shown to the user or sent to a server.
   * @param {jQuery.rdf.triple[]} triples An array (or {@link jQuery} object) of {@link jQuery.rdf.triple}s.
   * @param {Object} [options] Options that control the format of the dump.
   * @param {String} [options.format='application/json'] The mime type of the format of the dump. The supported formats are:
   * <table>
   *   <tr><th>mime type</th><th>description</th></tr>
   *   <tr>
   *     <td><code>application/json</code></td>
   *     <td>An <a href="http://n2.talis.com/wiki/RDF_JSON_Specification">RDF/JSON</a> object</td>
   *   </tr>
   *   <tr>
   *     <td><code>application/rdf+xml</code></td>
   *     <td>An DOMDocument node holding XML in <a href="http://www.w3.org/TR/rdf-syntax-grammar/">RDF/XML syntax</a></td>
   *   </tr>
   *   <tr>
   *     <td><code>text/turtle</code></td>
   *     <td>A String holding a representation of the RDF in <a href="http://www.w3.org/TeamSubmission/turtle/">Turtle syntax</a></td>
   *   </tr>
   * </table>
   * @param {Object} [options.namespaces={}] A set of namespace bindings used when mapping resource URIs to CURIEs or QNames (particularly in a RDF/XML serialisation).
   * @param {boolean} [options.serialize=false] If true, rather than creating an Object, the function will return a string which is ready to display or send to a server.
   * @param {boolean} [options.indent=false] If true, the serialised (RDF/XML) output has indentation added to it to make it more readable.
   * @returns {Object|String} The alternative representation of the triples.
   */
  $.rdf.dump = function (triples, options) {
    var opts = $.extend({}, $.rdf.dump.defaults, options || {}),
      format = opts.format,
      serialize = opts.serialize,
      dump, parser, parsers;
    parser = $.rdf.parsers[format];
    if (parser === undefined) {
      parsers = [];
      for (p in $.rdf.parsers) {
        parsers.push(p);
      }
      throw "Unrecognised dump format: " + format + ". Expected one of " + parsers.join(", ");
    }
    dump = parser.dump(triples, opts);
    return serialize ? parser.serialize(dump) : dump;
  };

  $.rdf.dump.defaults = {
    format: 'application/json',
    serialize: false,
    indent: false,
    namespaces: {}
  }

  /**
   * Gleans RDF triples from the nodes held by the {@link jQuery} object, puts them into a {@link jQuery.rdf.databank} and returns a {@link jQuery.rdf} object that allows you to query and otherwise manipulate them. The mechanism for gleaning RDF triples from the web page depends on the rdfQuery modules that have been included. The core version of rdfQuery doesn't support any gleaners; other versions support a RDFa gleaner, and there are some modules available for common microformats.
   * @methodOf jQuery#
   * @name jQuery#rdf
   * @param {Function} [callback] A callback function that is called every time a triple is gleaned from the page. Within the function, <code>this</code> is set to the triple that has been located. The function can take up to two parameters:
   * <dl>
   *   <dt>node</dt><dd>The node on which the triple has been found; should be the same as <code>this.source</code>.</dd>
   *   <dt>triple</dt><dd>The triple that's been found; the same as <code>this</code>.</dd>
   * </dl>
   * The callback should return the triple or triples that should be added to the databank. This enables you to filter, extend or modify the contents of the databank itself, should you wish to.
   * @returns {jQuery.rdf} An empty query over the triples stored within the page.
   * @example $('#content').rdf().databank.dump();
   */
  $.fn.rdf = function (callback) {
    var triples = [],
      callback = callback || function () { return this; };
    if ($(this)[0] && $(this)[0].nodeType === 9) {
      return $(this).children('*').rdf(callback);
    } else if ($(this).length > 0) {
      triples = $(this).map(function (i, elem) {
        return $.map($.rdf.gleaners, function (gleaner) {
          return gleaner.call($(elem), { callback: callback });
        });
      });
      return $.rdf({ triples: triples, namespaces: $(this).xmlns() });
    } else {
      return $.rdf();
    }
  };

  $.extend($.expr[':'], {

    about: function (a, i, m) {
      var j = $(a),
        resource = m[3] ? j.safeCurie(m[3]) : null,
        isAbout = false;
      $.each($.rdf.gleaners, function (i, gleaner) {
        isAbout = gleaner.call(j, { about: resource });
        if (isAbout) {
          return null;
        }
      });
      return isAbout;
    },

    type: function (a, i, m) {
      var j = $(a),
        type = m[3] ? j.curie(m[3]) : null,
        isType = false;
      $.each($.rdf.gleaners, function (i, gleaner) {
        if (gleaner.call(j, { type: type })) {
          isType = true;
          return null;
        }
      });
      return isType;
    }

  });

  /**
   * <p>Creates a new jQuery.rdf.databank object. This should be invoked as a method rather than constructed using new; indeed you will not usually want to generate these objects directly, but manipulate them through a {@link jQuery.rdf} object.</p>
   * @class Represents a triplestore, holding a bunch of {@link jQuery.rdf.triple}s.
   * @param {(String|jQuery.rdf.triple)[]} [triples=[]] An array of triples to store in the databank.
   * @param {Object} [options] Initialisation of the databank.
   * @param {Object} [options.namespaces] An object representing a set of namespace bindings used when interpreting the CURIEs in strings representing triples. Rather than passing this in when you construct the {@link jQuery.rdf.databank} instance, you will usually want to use the {@link jQuery.rdf.databank#prefix} method.
   * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the strings representing triples.
   * @returns {jQuery.rdf.databank} The newly-created databank.
   * @see jQuery.rdf
   */
  $.rdf.databank = function (triples, options) {
    return new $.rdf.databank.fn.init(triples, options);
  };

  $.rdf.databank.fn = $.rdf.databank.prototype = {
    init: function (triples, options) {
      var i;
      triples = triples || [];
      options = options || {};
      this.id = databankID();
      databanks[this.id] = this;
      if (options.union === undefined) {
        this.queries = {};
        this.tripleStore = [];
        this.subjectIndex = {};
        this.propertyIndex = {};
        this.objectIndex = {};
        this.baseURI = options.base || $.uri.base();
        this.namespaces = $.extend({}, options.namespaces || {});
        for (i = 0; i < triples.length; i += 1) {
          this.add(triples[i]);
        }
      } else {
        this.union = options.union;
      }
      return this;
    },
    
    /**
     * Sets or returns the base URI of the {@link jQuery.rdf.databank}.
     * @param {String|jQuery.uri} [base]
     * @returns A {@link jQuery.uri} if no base URI is specified, otherwise returns this {@link jQuery.rdf.databank} object.
     * @see jQuery.rdf#base
     */
    base: function (base) {
      if (this.union === undefined) {
        if (base === undefined) {
          return this.baseURI;
        } else {
          this.baseURI = base;
          return this;
        }
      } else if (base === undefined) {
        return this.union[0].base();
      } else {
        $.each(this.union, function (i, databank) {
          databank.base(base);
        });
        return this;
      }
    },

    /**
     * Sets or returns a namespace binding on the {@link jQuery.rdf.databank}.
     * @param {String} [prefix]
     * @param {String} [namespace]
     * @returns {Object|jQuery.uri|jQuery.rdf} If no prefix or namespace is specified, returns an object providing all namespace bindings on the {@link jQuery.rdf#databank}. If a prefix is specified without a namespace, returns the {@link jQuery.uri} associated with that prefix. Otherwise returns this {@link jQuery.rdf} object after setting the namespace binding.
     * @see jQuery.rdf#prefix
     */
    prefix: function (prefix, uri) {
      var namespaces = {};
      if (this.union === undefined) {
        if (prefix === undefined) {
          return this.namespaces;
        } else if (uri === undefined) {
          return this.namespaces[prefix];
        } else {
          this.namespaces[prefix] = uri;
          return this;
        }
      } else if (uri === undefined) {
        $.each(this.union, function (i, databank) {
          $.extend(namespaces, databank.prefix());
        });
        if (prefix === undefined) {
          return namespaces;
        } else {
          return namespaces[prefix];
        }
      } else {
        $.each(this.union, function (i, databank) {
          databank.prefix(prefix, uri);
        });
        return this;
      }
    },

    /**
     * Adds a triple to the {@link jQuery.rdf.databank} or another {@link jQuery.rdf.databank} object to create a union.
     * @param {String|jQuery.rdf.triple|jQuery.rdf.databank} triple The triple or {@link jQuery.rdf.databank} object to be added to this one. If the triple is a {@link jQuery.rdf.databank} object, the two databanks are unioned together. If the triple is a string, it's parsed as a {@link jQuery.rdf.triple}.
     * @param {Object} [options]
     * @param {Object} [options.namespaces] An object representing a set of namespace bindings used to interpret CURIEs within the triple. Defaults to the namespace bindings defined on the {@link jQuery.rdf.databank}.
     * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the triple. Defaults to the base URI defined on the {@link jQuery.rdf.databank}.
     * @param {Integer} [options.depth] The number of links to traverse to gather more information about the subject, property and object of the triple.
     * @returns {jQuery.rdf.databank} This {@link jQuery.rdf.databank} object.
     * @see jQuery.rdf#add
     */
    add: function (triple, options) {
      var base = (options && options.base) || this.base(),
        namespaces = $.extend({}, this.prefix(), (options && options.namespaces) || {}),
        depth = (options && options.depth) || $.rdf.databank.defaults.depth,
        proxy = (options && options.proxy) || $.rdf.databank.defaults.proxy,
        databank;
      if (triple === this) {
        return this;
      } else if (triple.subjectIndex !== undefined) {
        // merging two databanks
        if (this.union === undefined) {
          databank = $.rdf.databank(undefined, { union: [this, triple] });
          return databank;
        } else {
          this.union.push(triple);
          return this;
        }
      } else {
        if (typeof triple === 'string') {
          triple = $.rdf.triple(triple, { namespaces: namespaces, base: base, source: triple });
        }
        if (this.union === undefined) {
          if (this.subjectIndex[triple.subject] === undefined) {
            this.subjectIndex[triple.subject] = [];
            if (depth > 0 && triple.subject.type === 'uri') {
              this.load(triple.subject.value, { depth: depth - 1, proxy: proxy });
            }
          }
          if (this.propertyIndex[triple.property] === undefined) {
            this.propertyIndex[triple.property] = [];
            if (depth > 0) {
              this.load(triple.property.value, { depth: depth - 1, proxy: proxy });
            }
          }
          if ($.inArray(triple, this.subjectIndex[triple.subject]) === -1) {
            this.tripleStore.push(triple);
            this.subjectIndex[triple.subject].push(triple);
            this.propertyIndex[triple.property].push(triple);
            if (triple.object.type === 'uri' || triple.object.type === 'bnode') {
              if (this.objectIndex[triple.object] === undefined) {
                this.objectIndex[triple.object] = [];
                if (depth > 0 && triple.object.type === 'uri') {
                  this.load(triple.object.value, { depth: depth - 1, proxy: proxy });
                }
              }
              this.objectIndex[triple.object].push(triple);
            }
            addToDatabankQueries(this, triple);
          }
        } else {
          $.each(this.union, function (i, databank) {
            databank.add(triple);
          });
        }
        return this;
      }
    },

    /**
     * Removes a triple from the {@link jQuery.rdf.databank}.
     * @param {String|jQuery.rdf.triple} triple The triple to be removed.
     * @param {Object} [options]
     * @param {Object} [options.namespaces] An object representing a set of namespace bindings used to interpret any CURIEs within the triple. Defaults to the namespace bindings defined on the {@link jQuery.rdf.databank}.
     * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the triple. Defaults to the base URI defined on the {@link jQuery.rdf.databank}.
     * @returns {jQuery.rdf.databank} The {@link jQuery.rdf.databank} object itself.
     * @see jQuery.rdf#remove
     */
    remove: function (triple, options) {
      var base = (options && options.base) || this.base(),
        namespaces = $.extend({}, this.prefix(), (options && options.namespaces) || {}),
        striples, ptriples, otriples,
        databank;
      if (typeof triple === 'string') {
        triple = $.rdf.triple(triple, { namespaces: namespaces, base: base, source: triple });
      }
      this.tripleStore.splice($.inArray(triple, this.tripleStore), 1);
      striples = this.subjectIndex[triple.subject];
      if (striples !== undefined) {
        striples.splice($.inArray(triple, striples), 1);
      }
      ptriples = this.propertyIndex[triple.property];
      if (ptriples !== undefined) {
        ptriples.splice($.inArray(triple, ptriples), 1);
      }
      if (triple.object.type === 'uri' || triple.object.type === 'bnode') {
        otriples = this.objectIndex[triple.object];
        if (otriples !== undefined) {
          otriples.splice($.inArray(triple, otriples), 1);
        }
      }
      removeFromDatabankQueries(this, triple);
      return this;
    },

    /**
     * Creates a new databank containing all the triples in this {@link jQuery.rdf.databank} except those in the {@link jQuery.rdf.databank} passed as the argument.
     * @param {jQuery.rdf.databank} data The other {@link jQuery.rdf.databank}
     * @returns {jQuery.rdf.databank} A new {@link jQuery.rdf.databank} containing the triples in this {@link jQuery.rdf.databank} except for those in the data parameter.
     * @example
     * var old = $('html').rdf().databank;
     * ...some processing occurs...
     * var new = $('html').rdf().databank;
     * var added = new.except(old);
     * var removed = old.except(new);
     */
    except: function (data) {
      var store = data.subjectIndex,
        diff = [];
      $.each(this.subjectIndex, function (s, ts) {
        var ots = store[s];
        if (ots === undefined) {
          diff = diff.concat(ts);
        } else {
          $.each(ts, function (i, t) {
            if ($.inArray(t, ots) === -1) {
              diff.push(t);
            }
          });
        }
      });
      return $.rdf.databank(diff);
    },

    /**
     * Provides a {@link jQuery} object containing the triples held in this {@link jQuery.rdf.databank}.
     * @returns {jQuery} A {@link jQuery} object containing {@link jQuery.rdf.triple} objects.
     */
    triples: function () {
      var s, triples = [];
      if (this.union === undefined) {
        triples = this.tripleStore;
      } else {
        $.each(this.union, function (i, databank) {
          triples = triples.concat(databank.triples().get());
        });
        triples = unique(triples);
      }
      return $(triples);
    },

    /**
     * Tells you how many triples the databank contains.
     * @returns {Integer} The number of triples in the {@link jQuery.rdf.databank}.
     * @example $('html').rdf().databank.size();
     */
    size: function () {
      return this.triples().length;
    },

    /**
     * Provides <a href="http://n2.talis.com/wiki/Bounded_Descriptions_in_RDF#Simple_Concise_Bounded_Description">simple concise bounded descriptions</a> of the resources that are passed in the argument. This mirrors the <a href="http://www.w3.org/TR/rdf-sparql-query/#describe">DESCRIBE</a> form in SPARQL.
     * @param {(String|jQuery.rdf.resource)[]} resources An array that can contain strings, {@link jQuery.rdf.resource}s or a mixture of the two.
     * @returns {jQuery} A {@link jQuery} object holding the {@link jQuery.rdf.triple}s that describe the listed resources.
     * @see jQuery.rdf#describe
     */
    describe: function (resources) {
      var i, r, t, rhash = {}, triples = [];
      while (resources.length > 0) {
        r = resources.pop();
        if (rhash[r] === undefined) {
          if (r.value === undefined) {
            r = $.rdf.resource(r);
          }
          if (this.subjectIndex[r] !== undefined) {
            for (i = 0; i < this.subjectIndex[r].length; i += 1) {
              t = this.subjectIndex[r][i];
              triples.push(t);
              if (t.object.type === 'bnode') {
                resources.push(t.object);
              }
            }
          }
          if (this.objectIndex[r] !== undefined) {
            for (i = 0; i < this.objectIndex[r].length; i += 1) {
              t = this.objectIndex[r][i];
              triples.push(t);
              if (t.subject.type === 'bnode') {
                resources.push(t.subject);
              }
            }
          }
          rhash[r] = true;
        }
      }
      return unique(triples);
    },

    /**
     * Dumps the triples in the databank into a format that can be shown to the user or sent to a server.
     * @param {Object} [options] Options that control the formatting of the triples. See {@link jQuery.rdf.dump} for details.
     * @returns {Object|Node|String}
     * @see jQuery.rdf.dump
     */
    dump: function (options) {
      options = $.extend({ namespaces: this.namespaces, base: this.base }, options || {});
      return $.rdf.dump(this.triples(), options);
    },

    /**
     * Loads some data into the databank.
     * @param {Node|Object|String} data If the data is a string and starts with 'http://' then it's taken to be a URI and data is loaded from that URI via the proxy specified in the options. If it doesn't start with 'http://' then it's taken to be a serialized version of some format capable of representing RDF, parsed and interpreted. If the data is a node, it's interpreted to be an <a href="http://www.w3.org/TR/rdf-syntax-grammar/">RDF/XML syntax</a> document and will be parsed as such. Otherwise, it's taken to be a <a href="http://n2.talis.com/wiki/RDF_JSON_Specification">RDF/JSON</a> object.
     * @param {Object} opts Options governing the loading of the data.
     * @param {String} [opts.format] The mime type of the format the data is in, particularly useful if you're supplying the data as a string. If unspecified, the data will be sniffed to see if it might be HTML, RDF/XML, RDF/JSON or Turtle.
     * @param {boolean} [opts.async=true] When loading data from a URI, this determines whether it will be done synchronously or asynchronously.
     * @param {Function} [opts.success] When loading data from a URI, a function that will be called after the data is successfully loaded.
     * @param {Function} [opts.error] When loading data from a URI, a function that will be called if there's an error when accessing the URI.
     * @param {String} [opts.proxy='http://www.jenitennison.com/rdfquery/proxy.php'] The URI for a server-side proxy through which the data can be accessed. This does not have to be hosted on the same server as this Javascript, the HTML page or the remote data. The proxy must accept id, url and depth parameters and respond with some Javascript that will invoke the {@link jQuery.rdf.databank.load} function. <a href="http://code.google.com/p/rdfquery/source/browse/#svn/trunk/proxies">Example proxies</a> that do the right thing are available. If you are intending to use this facility a lot, please do not use the default proxy.
     * @param {integer} [opts.depth=0] Triggers recursive loading of located resources, to the depth specified. This is useful for automatically populating a databank with linked data.
     * @returns {jQuery.rdf.databank} The {@link jQuery.rdf.databank} itself.
     * @see jQuery.rdf#load
     */
    load: function (data, opts) {
      var i, triples, url, script, parser, docElem,
        format = (opts && opts.format),
        async = (opts && opts.async) || $.rdf.databank.defaults.async,
        success = (opts && opts.success) || $.rdf.databank.defaults.success,
        error = (opts && opts.error) || $.rdf.databank.defaults.error,
        proxy = (opts && opts.proxy) || $.rdf.databank.defaults.proxy,
        depth = (opts && opts.depth) || $.rdf.databank.defaults.depth;
      url = (typeof data === 'string' && data.substring(1, 7) === 'http://') ? $.uri(data) : data;
      if (url.scheme) {
        if (!queue(this, url, { success: success, error: error })) {
          script = '<script type="text/javascript" src="' + proxy + '?id=' + this.id + '&amp;depth=' + depth + '&amp;url=' + encodeURIComponent(url.resolve('').toString()) + '"></script>';
          if (async) {
            setTimeout("$('head').append('" + script + "')", 0);
          } else {
            $('head').append(script);
          }
        }
        return this;
      } else {
        if (format === undefined) {
          if (typeof data === 'string') {
            if (data.substring(0, 1) === '{') {
              format = 'application/json';
            } else if (data.substring(0, 14) === '<!DOCTYPE html' || data.substring(0, 5) === '<html') {
              format = 'application/xhtml+xml';
            } else if (data.substring(0, 5) === '<?xml' || data.substring(0, 8) === '<rdf:RDF') {
              format = 'application/rdf+xml';
            } else {
              format = 'text/turtle';
            }
          } else if (data.documentElement || data.ownerDocument) {
            docElem = data.documentElement ? data.documentElement : data.ownerDocument.documentElement;
            if (docElem.nodeName === 'html') {
              format = 'application/xhtml+xml';
            } else {
              format = 'application/rdf+xml';
            }
          } else {
            format = 'application/json';
          }
        }
        parser = $.rdf.parsers[format];
        if (typeof data === 'string') {
          data = parser.parse(data);
        }
        triples = parser.triples(data);
        for (i = 0; i < triples.length; i += 1) {
          this.add(triples[i], opts);
        }
        return this;
      }
    },

    /**
     * Provides a string representation of the databank which simply specifies how many triples it contains.
     * @returns {String}
     */
    toString: function () {
      return '[Databank with ' + this.size() + ' triples]';
    }
  };

  $.rdf.databank.fn.init.prototype = $.rdf.databank.fn;
  
  $.rdf.databank.defaults = {
    parse: false,
    async: true,
    success: null,
    error: null,
    depth: 0,
    proxy: 'http://www.jenitennison.com/rdfquery/proxy.php'
  };
  
  $.rdf.databank.load = function (id, url, doc, opts) {
    if (doc !== undefined) {
      databanks[id].load(doc, opts);
    }
    dequeue(databanks[id], url, (doc === undefined) ? 'error' : 'success', opts);
  };

  /**
   * <p>Creates a new jQuery.rdf.pattern object. This should be invoked as a method rather than constructed using new; indeed you will not usually want to generate these objects directly, since they are automatically created from strings where necessary, such as by {@link jQuery.rdf#where}.</p>
   * @class Represents a pattern that may or may not match a given {@link jQuery.rdf.triple}.
   * @param {String|jQuery.rdf.resource|jQuery.rdf.blank} subject The subject pattern, or a single string that defines the entire pattern. If the subject is specified as a string, it can be a fixed resource (<code>&lt;<var>uri</var>&gt;</code> or <code><var>curie</var></code>), a blank node (<code>_:<var>id</var></code>) or a variable placeholder (<code>?<var>name</var></code>).
   * @param {String|jQuery.rdf.resource} [property] The property pattern. If the property is specified as a string, it can be a fixed resource (<code>&lt;<var>uri</var>&gt;</code> or <code><var>curie</var></code>) or a variable placeholder (<code>?<var>name</var></code>).
   * @param {String|jQuery.rdf.resource|jQuery.rdf.blank|jQuery.rdf.literal} [value] The value pattern. If the property is specified as a string, it can be a fixed resource (<code>&lt;<var>uri</var>&gt;</code> or <code><var>curie</var></code>), a blank node (<code>_:<var>id</var></code>), a literal (<code>"<var>value</var>"</code>) or a variable placeholder (<code>?<var>name</var></code>).
   * @param {Object} [options] Initialisation of the pattern.
   * @param {Object} [options.namespaces] An object representing a set of namespace bindings used when interpreting the CURIEs in the subject, property and object.
   * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the subject, property and object.
   * @param {boolean} [options.optional]
   * @returns {jQuery.rdf.pattern} The newly-created pattern.
   * @throws {String} Errors if any of the strings are not in a recognised format.
   * @example pattern = $.rdf.pattern('?person', $.rdf.type, 'foaf:Person', { namespaces: { foaf: "http://xmlns.com/foaf/0.1/" }});
   * @example 
   * pattern = $.rdf.pattern('?person a foaf:Person', { 
   *   namespaces: { foaf: "http://xmlns.com/foaf/0.1/" }, 
   *   optional: true 
   * });
   * @see jQuery.rdf#where
   * @see jQuery.rdf.resource
   * @see jQuery.rdf.blank
   * @see jQuery.rdf.literal
   */
  $.rdf.pattern = function (subject, property, object, options) {
    var pattern, m, optional;
    // using a two-argument version; first argument is a Turtle statement string
    if (object === undefined) {
      options = property || {};
      m = $.trim(subject).match(tripleRegex);
      if (m.length === 3 || (m.length === 4 && m[3] === '.')) {
        subject = m[0];
        property = m[1];
        object = m[2];
      } else {
        throw "Bad Pattern: Couldn't parse string " + subject;
      }
      optional = (options.optional === undefined) ? $.rdf.pattern.defaults.optional : options.optional;
    }
    if (memPattern[subject] && 
        memPattern[subject][property] && 
        memPattern[subject][property][object] && 
        memPattern[subject][property][object][optional]) {
      return memPattern[subject][property][object][optional];
    }
    pattern = new $.rdf.pattern.fn.init(subject, property, object, options);
    if (memPattern[pattern.subject] &&
        memPattern[pattern.subject][pattern.property] &&
        memPattern[pattern.subject][pattern.property][pattern.object] &&
        memPattern[pattern.subject][pattern.property][pattern.object][pattern.optional]) {
      return memPattern[pattern.subject][pattern.property][pattern.object][pattern.optional];
    } else {
      if (memPattern[pattern.subject] === undefined) {
        memPattern[pattern.subject] = {};
      }
      if (memPattern[pattern.subject][pattern.property] === undefined) {
        memPattern[pattern.subject][pattern.property] = {};
      }
      if (memPattern[pattern.subject][pattern.property][pattern.object] === undefined) {
        memPattern[pattern.subject][pattern.property][pattern.object] = {};
      }
      memPattern[pattern.subject][pattern.property][pattern.object][pattern.optional] = pattern;
      return pattern;
    }
  };

  $.rdf.pattern.fn = $.rdf.pattern.prototype = {
    init: function (s, p, o, options) {
      var opts = $.extend({}, $.rdf.pattern.defaults, options);
      /**
       * The placeholder for the subject of triples matching against this pattern.
       * @type String|jQuery.rdf.resource|jQuery.rdf.blank
       */
      this.subject = s.toString().substring(0, 1) === '?' ? s : subject(s, opts);
      /**
       * The placeholder for the property of triples matching against this pattern.
       * @type String|jQuery.rdf.resource
       */
      this.property = p.toString().substring(0, 1) === '?' ? p : property(p, opts);
      /**
       * The placeholder for the object of triples matching against this pattern.
       * @type String|jQuery.rdf.resource|jQuery.rdf.blank|jQuery.rdf.literal
       */
      this.object = o.toString().substring(0, 1) === '?' ? o : object(o, opts);
      /**
       * Whether the pattern should only optionally match against the triple
       * @type boolean
       */
      this.optional = opts.optional;
      return this;
    },

    /**
     * Creates a new {@link jQuery.rdf.pattern} with any variable placeholders within this one's subject, property or object filled in with values from the bindings passed as the argument.
     * @param {Object} bindings An object holding the variable bindings to be used to replace any placeholders in the pattern. These bindings are of the type held by the {@link jQuery.rdf} object.
     * @returns {jQuery.rdf.pattern} A new {@link jQuery.rdf.pattern} object.
     * @example
     * pattern = $.rdf.pattern('?thing a ?class');
     * // pattern2 matches all triples that indicate the classes of this page. 
     * pattern2 = pattern.fill({ thing: $.rdf.resource('<>') });
     */
    fill: function (bindings) {
      var s = this.subject,
        p = this.property,
        o = this.object;
      if (typeof s === 'string' && bindings[s.substring(1)]) {
        s = bindings[s.substring(1)];
      }
      if (typeof p === 'string' && bindings[p.substring(1)]) {
        p = bindings[p.substring(1)];
      }
      if (typeof o === 'string' && bindings[o.substring(1)]) {
        o = bindings[o.substring(1)];
      }
      return $.rdf.pattern(s, p, o, { optional: this.optional });
    },

    /**
     * Creates a new Object holding variable bindings by matching the passed triple against this pattern.
     * @param {jQuery.rdf.triple} triple A {@link jQuery.rdf.triple} for this pattern to match against.
     * @returns {null|Object} An object containing the bindings of variables (as specified in this pattern) to values (as specified in the triple), or <code>null</code> if the triple doesn't match the pattern.
     * pattern = $.rdf.pattern('?thing a ?class');
     * bindings = pattern.exec($.rdf.triple('<> a foaf:Person', { namespaces: ns }));
     * thing = bindings.thing; // the resource for this page
     * class = bindings.class; // a resource for foaf:Person
     */
    exec: function (triple) {
      var binding = {};
      binding = testResource(triple.subject, this.subject, binding);
      if (binding === null) {
        return null;
      }
      binding = testResource(triple.property, this.property, binding);
      if (binding === null) {
        return null;
      }
      binding = testResource(triple.object, this.object, binding);
      return binding;
    },

    /**
     * Tests whether this pattern has any variable placeholders in it or not.
     * @returns {boolean} True if the pattern doesn't contain any variable placeholders.
     * @example
     * $.rdf.pattern('?thing a ?class').isFixed(); // false
     * $.rdf.pattern('<> a foaf:Person', { namespaces: ns }).isFixed(); // true
     */
    isFixed: function () {
      return typeof this.subject !== 'string' &&
        typeof this.property !== 'string' &&
        typeof this.object !== 'string';
    },

    /**
     * Creates a new triple based on the bindings passed to the pattern, if possible.
     * @param {Object} bindings An object holding the variable bindings to be used to replace any placeholders in the pattern. These bindings are of the type held by the {@link jQuery.rdf} object.
     * @returns {null|jQuery.rdf.triple} A new {@link jQuery.rdf.triple} object, or null if not all the variable placeholders in the pattern are specified in the bindings. The {@link jQuery.rdf.triple#source} of the generated triple is set to the string value of this pattern.
     * @example
     * pattern = $.rdf.pattern('?thing a ?class');
     * // triple is a new triple '<> a foaf:Person'
     * triple = pattern.triple({ 
     *   thing: $.rdf.resource('<>'),
     *   class: $.rdf.resource('foaf:Person', { namespaces: ns }) 
     * });
     */
    triple: function (bindings) {
      var t = this;
      if (!this.isFixed()) {
        t = this.fill(bindings);
      }
      if (t.isFixed()) {
        return $.rdf.triple(t.subject, t.property, t.object, { source: this.toString() });
      } else {
        return null;
      }
    },

    /**
     * Returns a string representation of the pattern by concatenating the subject, property and object.
     * @returns {String}
     */
    toString: function () {
      return this.subject + ' ' + this.property + ' ' + this.object;
    }
  };

  $.rdf.pattern.fn.init.prototype = $.rdf.pattern.fn;

  $.rdf.pattern.defaults = {
    base: $.uri.base(),
    namespaces: {},
    optional: false
  };

  /**
   * <p>Creates a new jQuery.rdf.triple object. This should be invoked as a method rather than constructed using new; indeed you will not usually want to generate these objects directly, since they are automatically created from strings where necessary, such as by {@link jQuery.rdf#add}.</p>
   * @class Represents an RDF triple.
   * @param {String|jQuery.rdf.resource|jQuery.rdf.blank} subject The subject of the triple, or a single string that defines the entire triple. If the subject is specified as a string, it can be a fixed resource (<code>&lt;<var>uri</var>&gt;</code> or <code><var>curie</var></code>) or a blank node (<code>_:<var>id</var></code>).
   * @param {String|jQuery.rdf.resource} [property] The property pattern. If the property is specified as a string, it must be a fixed resource (<code>&lt;<var>uri</var>&gt;</code> or <code><var>curie</var></code>).
   * @param {String|jQuery.rdf.resource|jQuery.rdf.blank|jQuery.rdf.literal} [value] The value pattern. If the property is specified as a string, it can be a fixed resource (<code>&lt;<var>uri</var>&gt;</code> or <code><var>curie</var></code>), a blank node (<code>_:<var>id</var></code>), or a literal (<code>"<var>value</var>"</code>).
   * @param {Object} [options] Initialisation of the triple.
   * @param {Object} [options.namespaces] An object representing a set of namespace bindings used when interpreting the CURIEs in the subject, property and object.
   * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the subject, property and object.
   * @returns {jQuery.rdf.triple} The newly-created triple.
   * @throws {String} Errors if any of the strings are not in a recognised format.
   * @example pattern = $.rdf.triple('<>', $.rdf.type, 'foaf:Person', { namespaces: { foaf: "http://xmlns.com/foaf/0.1/" }});
   * @example 
   * pattern = $.rdf.triple('<> a foaf:Person', { 
   *   namespaces: { foaf: "http://xmlns.com/foaf/0.1/" }
   * });
   * @see jQuery.rdf#add
   * @see jQuery.rdf.resource
   * @see jQuery.rdf.blank
   * @see jQuery.rdf.literal
   */
  $.rdf.triple = function (subject, property, object, options) {
    var triple, graph, m;
    // using a two-argument version; first argument is a Turtle statement string
    if (object === undefined) {
      options = property;
      m = $.trim(subject).match(tripleRegex);
      if (m.length === 3 || (m.length === 4 && m[3] === '.')) {
        subject = m[0];
        property = m[1];
        object = m[2];
      } else {
        throw "Bad Triple: Couldn't parse string " + subject;
      }
    }
    graph = (options && options.graph) || '';
    if (memTriple[graph] &&
        memTriple[graph][subject] &&
        memTriple[graph][subject][property] &&
        memTriple[graph][subject][property][object]) {
      return memTriple[graph][subject][property][object];
    }
    triple = new $.rdf.triple.fn.init(subject, property, object, options);
    graph = triple.graph || '';
    if (memTriple[graph] &&
        memTriple[graph][triple.subject] &&
        memTriple[graph][triple.subject][triple.property] &&
        memTriple[graph][triple.subject][triple.property][triple.object]) {
      return memTriple[graph][triple.subject][triple.property][triple.object];
    } else {
      if (memTriple[graph] === undefined) {
        memTriple[graph] = {};
      }
      if (memTriple[graph][triple.subject] === undefined) {
        memTriple[graph][triple.subject] = {};
      }
      if (memTriple[graph][triple.subject][triple.property] === undefined) {
        memTriple[graph][triple.subject][triple.property] = {};
      }
      memTriple[graph][triple.subject][triple.property][triple.object] = triple;
      return triple;
    }
  };

  $.rdf.triple.fn = $.rdf.triple.prototype = {
    init: function (s, p, o, options) {
      var opts;
      opts = $.extend({}, $.rdf.triple.defaults, options);
      /**
       * The subject of the triple.
       * @type jQuery.rdf.resource|jQuery.rdf.blank
       */
      this.subject = subject(s, opts);
      /**
       * The property of the triple.
       * @type jQuery.rdf.resource
       */
      this.property = property(p, opts);
      /**
       * The object of the triple.
       * @type jQuery.rdf.resource|jQuery.rdf.blank|jQuery.rdf.literal
       */
      this.object = object(o, opts);
      /**
       * (Experimental) The named graph the triple belongs to.
       * @type jQuery.rdf.resource|jQuery.rdf.blank
       */
      this.graph = opts.graph === undefined ? undefined : subject(opts.graph, opts);
      /**
       * The source of the triple, which might be a node within the page (if the RDF is generated from the page) or a string holding the pattern that generated the triple.
       */
      this.source = opts.source;
      return this;
    },

    /**
     * Always returns true for triples.
     * @see jQuery.rdf.pattern#isFixed
     */
    isFixed: function () {
      return true;
    },

    /**
     * Always returns this triple.
     * @see jQuery.rdf.pattern#triple
     */
    triple: function (bindings) {
      return this;
    },

    /**
     * Returns a <a href="http://n2.talis.com/wiki/RDF_JSON_Specification">RDF/JSON</a> representation of this triple.
     * @returns {Object}
     */
    dump: function () {
      var e = {},
        s = this.subject.value.toString(),
        p = this.property.value.toString();
      e[s] = {};
      e[s][p] = this.object.dump();
      return e;
    },

    /**
     * Returns a string representing this triple in Turtle format.
     * @returns {String}
     */
    toString: function () {
      return this.subject + ' ' + this.property + ' ' + this.object + ' .';
    }
  };

  $.rdf.triple.fn.init.prototype = $.rdf.triple.fn;

  $.rdf.triple.defaults = {
    base: $.uri.base(),
    source: [document],
    namespaces: {}
  };

  /**
   * <p>Creates a new jQuery.rdf.resource object. This should be invoked as a method rather than constructed using new; indeed you will not usually want to generate these objects directly, since they are automatically created from strings where necessary, such as by {@link jQuery.rdf#add}.</p>
   * @class Represents an RDF resource.
   * @param {String|jQuery.uri} value The value of the resource. If it's a string it must be in the format <code>&lt;<var>uri</var>&gt;</code> or <code><var>curie</var></code>.
   * @param {Object} [options] Initialisation of the resource.
   * @param {Object} [options.namespaces] An object representing a set of namespace bindings used when interpreting the CURIE specifying the resource.
   * @param {String|jQuery.uri} [options.base] The base URI used to interpret any relative URIs used within the URI specifying the resource.
   * @returns {jQuery.rdf.resource} The newly-created resource.
   * @throws {String} Errors if the string is not in a recognised format.
   * @example thisPage = $.rdf.resource('<>');
   * @example foaf.Person = $.rdf.resource('foaf:Person', { namespaces: ns });
   * @see jQuery.rdf.pattern
   * @see jQuery.rdf.triple
   * @see jQuery.rdf.blank
   * @see jQuery.rdf.literal
   */
  $.rdf.resource = function (value, options) {
    var resource;
    if (memResource[value]) {
      return memResource[value];
    }
    resource = new $.rdf.resource.fn.init(value, options);
    if (memResource[resource]) {
      return memResource[resource];
    } else {
      memResource[resource] = resource;
      return resource;
    }
  };

  $.rdf.resource.fn = $.rdf.resource.prototype = {
    /**
     * Always fixed to 'uri' for resources.
     * @type String
     */
    type: 'uri',
    /**
     * The URI for the resource.
     * @type jQuery.rdf.uri
     */
    value: undefined,

    init: function (value, options) {
      var m, prefix, uri, opts;
      if (typeof value === 'string') {
        m = uriRegex.exec(value);
        opts = $.extend({}, $.rdf.resource.defaults, options);
        if (m !== null) {
          this.value = $.uri.resolve(m[1].replace(/\\>/g, '>'), opts.base);
        } else if (value.substring(0, 1) === ':') {
          uri = opts.namespaces[''];
          if (uri === undefined) {
            throw "Malformed Resource: No namespace binding for default namespace in " + value;
          } else {
            this.value = $.uri.resolve(uri + value.substring(1));
          }
        } else if (value.substring(value.length - 1) === ':') {
          prefix = value.substring(0, value.length - 1);
          uri = opts.namespaces[prefix];
          if (uri === undefined) {
            throw "Malformed Resource: No namespace binding for prefix " + prefix + " in " + value;
          } else {
            this.value = $.uri.resolve(uri);
          }
        } else {
          try {
            this.value = $.curie(value, { namespaces: opts.namespaces });
          } catch (e) {
            throw "Malformed Resource: Bad format for resource " + e;
          }
        }
      } else {
        this.value = value;
      }
      return this;
    }, // end init

    /**
     * Returns a <a href="http://n2.talis.com/wiki/RDF_JSON_Specification">RDF/JSON</a> representation of this triple.
     * @returns {Object}
     */
    dump: function () {
      return {
        type: 'uri',
        value: this.value.toString()
      };
    },

    /**
     * Returns a string representing this resource in Turtle format.
     * @returns {String}
     */
    toString: function () {
      return '<' + this.value + '>';
    }
  };

  $.rdf.resource.fn.init.prototype = $.rdf.resource.fn;

  $.rdf.resource.defaults = {
    base: $.uri.base(),
    namespaces: {}
  };

  /**
   * A {@link jQuery.rdf.resource} for rdf:type
   * @constant
   * @type jQuery.rdf.resource
   */
  $.rdf.type = $.rdf.resource('<' + rdfNs + 'type>');
  /**
   * A {@link jQuery.rdf.resource} for rdfs:label
   * @constant
   * @type jQuery.rdf.resource
   */
  $.rdf.label = $.rdf.resource('<' + rdfsNs + 'label>');
  /**
   * A {@link jQuery.rdf.resource} for rdf:first
   * @constant
   * @type jQuery.rdf.resource
   */
  $.rdf.first = $.rdf.resource('<' + rdfNs + 'first>');
  /**
   * A {@link jQuery.rdf.resource} for rdf:rest
   * @constant
   * @type jQuery.rdf.resource
   */
  $.rdf.rest = $.rdf.resource('<' + rdfNs + 'rest>');
  /**
   * A {@link jQuery.rdf.resource} for rdf:nil
   * @constant
   * @type jQuery.rdf.resource
   */
  $.rdf.nil = $.rdf.resource('<' + rdfNs + 'nil>');
  /**
   * A {@link jQuery.rdf.resource} for rdf:subject
   * @constant
   * @type jQuery.rdf.resource
   */
  $.rdf.subject = $.rdf.resource('<' + rdfNs + 'subject>');
  /**
   * A {@link jQuery.rdf.resource} for rdf:property
   * @constant
   * @type jQuery.rdf.resource
   */
  $.rdf.property = $.rdf.resource('<' + rdfNs + 'property>');
  /**
   * A {@link jQuery.rdf.resource} for rdf:object
   * @constant
   * @type jQuery.rdf.resource
   */
  $.rdf.object = $.rdf.resource('<' + rdfNs + 'object>');

  /**
   * <p>Creates a new jQuery.rdf.blank object. This should be invoked as a method rather than constructed using new; indeed you will not usually want to generate these objects directly, since they are automatically created from strings where necessary, such as by {@link jQuery.rdf#add}.</p>
   * @class Represents an RDF blank node.
   * @param {String} value A representation of the blank node in the format <code>_:<var>id</var></code> or <code>[]</code> (which automatically creates a new blank node with a unique ID).
   * @returns {jQuery.rdf.blank} The newly-created blank node.
   * @throws {String} Errors if the string is not in a recognised format.
   * @example newBlank = $.rdf.blank('[]');
   * @example identifiedBlank = $.rdf.blank('_:fred');
   * @see jQuery.rdf.pattern
   * @see jQuery.rdf.triple
   * @see jQuery.rdf.resource
   * @see jQuery.rdf.literal
   */
  $.rdf.blank = function (value) {
    var blank;
    if (memBlank[value]) {
      return memBlank[value];
    }
    blank = new $.rdf.blank.fn.init(value);
    if (memBlank[blank]) {
      return memBlank[blank];
    } else {
      memBlank[blank] = blank;
      return blank;
    }
  };

  $.rdf.blank.fn = $.rdf.blank.prototype = {
    /**
     * Always fixed to 'bnode' for blank nodes.
     * @type String
     */
    type: 'bnode',
    /**
     * The value of the blank node in the format <code>_:<var>id</var></code>
     * @type String
     */
    value: undefined,
    /**
     * The id of the blank node.
     * @type String
     */
    id: undefined,

    init: function (value) {
      if (value === '[]') {
        this.id = blankNodeID();
        this.value = '_:' + this.id;
      } else if (value.substring(0, 2) === '_:') {
        this.id = value.substring(2);
        this.value = value;
      } else {
        throw "Malformed Blank Node: " + value + " is not a legal format for a blank node";
      }
      return this;
    },

    /**
     * Returns a <a href="http://n2.talis.com/wiki/RDF_JSON_Specification">RDF/JSON</a> representation of this blank node.
     * @returns {Object}
     */
    dump: function () {
      return {
        type: 'bnode',
        value: this.value
      };
    },

    /**
     * Returns the value this blank node.
     * @returns {String}
     */
    toString: function () {
      return this.value;
    }
  };

  $.rdf.blank.fn.init.prototype = $.rdf.blank.fn;

  /**
   * <p>Creates a new jQuery.rdf.literal object. This should be invoked as a method rather than constructed using new; indeed you will not usually want to generate these objects directly, since they are automatically created from strings where necessary, such as by {@link jQuery.rdf#add}.</p>
   * @class Represents an RDF literal.
   * @param {String|boolean|Number} value Either the value of the literal or a string representation of it. If the datatype or lang options are specified, the value is taken as given. Otherwise, if it's a Javascript boolean or numeric value, it is interpreted as a value with a xsd:boolean or xsd:double datatype. In all other cases it's interpreted as a literal as defined in <a href="http://www.w3.org/TeamSubmission/turtle/#literal">Turtle syntax</a>.
   * @param {Object} [options] Initialisation options for the literal.
   * @param {String} [options.datatype] The datatype for the literal. This should be a safe CURIE; in other words, it can be in the format <code><var>uri</var></code> or <code>[<var>curie</var>]</code>. Must not be specified if options.lang is also specified.
   * @param {String} [options.lang] The language for the literal. Must not be specified if options.datatype is also specified.
   * @param {Object} [options.namespaces] An object representing a set of namespace bindings used when interpreting a CURIE in the datatype.
   * @param {String|jQuery.uri} [options.base] The base URI used to interpret a relative URI in the datatype.
   * @returns {jQuery.rdf.literal} The newly-created literal.
   * @throws {String} Errors if the string is not in a recognised format or if both options.datatype and options.lang are specified.
   * @example trueLiteral = $.rdf.literal(true);
   * @example numericLiteral = $.rdf.literal(5);
   * @example dateLiteral = $.rdf.literal('"2009-07-13"^^xsd:date', { namespaces: ns });
   * @see jQuery.rdf.pattern
   * @see jQuery.rdf.triple
   * @see jQuery.rdf.resource
   * @see jQuery.rdf.blank
   */
  $.rdf.literal = function (value, options) {
    var literal;
    if (memLiteral[value]) {
      return memLiteral[value];
    }
    literal = new $.rdf.literal.fn.init(value, options);
    if (memLiteral[literal]) {
      return memLiteral[literal];
    } else {
      memLiteral[literal] = literal;
      return literal;
    }
  };

  $.rdf.literal.fn = $.rdf.literal.prototype = {
    /**
     * Always fixed to 'literal' for literals.
     * @type String
     */
    type: 'literal',
    /**
     * The value of the literal as a string.
     * @type String
     */
    value: undefined,
    /**
     * The language of the literal, if it has one; otherwise undefined.
     * @type String
     */
    lang: undefined,
    /**
     * The datatype of the literal, if it has one; otherwise undefined.
     * @type jQuery.uri
     */
    datatype: undefined,

    init: function (value, options) {
      var
        m, datatype,
        opts = $.extend({}, $.rdf.literal.defaults, options);
      datatype = $.safeCurie(opts.datatype, { namespaces: opts.namespaces });
      if (opts.lang !== undefined && opts.datatype !== undefined && datatype.toString() !== (rdfNs + 'XMLLiteral')) {
        throw "Malformed Literal: Cannot define both a language and a datatype for a literal (" + value + ")";
      }
      if (opts.datatype !== undefined) {
        datatype = $.safeCurie(opts.datatype, { namespaces: opts.namespaces });
        $.extend(this, $.typedValue(value.toString(), datatype));
        if (datatype.toString() === rdfNs + 'XMLLiteral') {
          this.lang = opts.lang;
        }
      } else if (opts.lang !== undefined) {
        this.value = value.toString();
        this.lang = opts.lang;
      } else if (typeof value === 'boolean') {
        $.extend(this, $.typedValue(value.toString(), xsdNs + 'boolean'));
      } else if (typeof value === 'number') {
        $.extend(this, $.typedValue(value.toString(), xsdNs + 'double'));
      } else if (value === 'true' || value === 'false') {
        $.extend(this, $.typedValue(value, xsdNs + 'boolean'));
      } else if ($.typedValue.valid(value, xsdNs + 'integer')) {
        $.extend(this, $.typedValue(value, xsdNs + 'integer'));
      } else if ($.typedValue.valid(value, xsdNs + 'decimal')) {
        $.extend(this, $.typedValue(value, xsdNs + 'decimal'));
      } else if ($.typedValue.valid(value, xsdNs + 'double') &&
                 !/^\s*([\-\+]?INF|NaN)\s*$/.test(value)) {  // INF, -INF and NaN aren't valid literals in Turtle
        $.extend(this, $.typedValue(value, xsdNs + 'double'));
      } else if (true === opts.plain) {
        // Option to not use turtle syntax for a plain literal w/o datatype or lang
        this.value = String(value);
      } else {
        m = literalRegex.exec(value);
        if (m !== null) {
          this.value = (m[2] || m[4]).replace(/\\"/g, '"').replace(/\\n/g, '\n').replace(/\\t/g, '\t').replace(/\\r/g, '\r');
          if (m[9]) {
            datatype = $.rdf.resource(m[9], opts);
            $.extend(this, $.typedValue(this.value, datatype.value));
          } else if (m[7]) {
            this.lang = m[7];
          }
        } else {
          throw "Malformed Literal: Couldn't recognise the value " + value;
        }
      }
      return this;
    }, // end init

    /**
     * Returns a <a href="http://n2.talis.com/wiki/RDF_JSON_Specification">RDF/JSON</a> representation of this blank node.
     * @returns {Object}
     */
    dump: function () {
      var e = {
        type: 'literal',
        value: this.value.toString()
      };
      if (this.lang !== undefined) {
        e.lang = this.lang;
      } else if (this.datatype !== undefined) {
        e.datatype = this.datatype.toString();
      }
      return e;
    },
    
    /**
     * Returns a string representing this resource in <a href="http://www.w3.org/TeamSubmission/turtle/#literal">Turtle format</a>.
     * @returns {String}
     */
    toString: function () {
      var val = '"' + this.value + '"';
      if (this.lang !== undefined) {
        val += '@' + this.lang;
      } else if (this.datatype !== undefined) {
        val += '^^<' + this.datatype + '>';
      }
      return val;
    }
  };

  $.rdf.literal.fn.init.prototype = $.rdf.literal.fn;

  $.rdf.literal.defaults = {
    base: $.uri.base(),
    namespaces: {},
    datatype: undefined,
    lang: undefined,
    plain: false
  };

})(jQuery);
/*
 * jQuery RDF @VERSION
 *
 * Copyright (c) 2008,2009 Jeni Tennison
 * Licensed under the MIT (MIT-LICENSE.txt)
 *
 * Depends:
 *  jquery.uri.js
 *  jquery.xmlns.js
 *  jquery.datatype.js
 *  jquery.curie.js
 *  jquery.rdf.js
 *  jquery.json.js
 */
/**
 * @fileOverview jQuery RDF/JSON parser
 * @author <a href="mailto:jeni@jenitennison.com">Jeni Tennison</a>
 * @copyright (c) 2008,2009 Jeni Tennison
 * @license MIT license (MIT-LICENSE.txt)
 * @version 1.0
 */
/**
 * @exports $ as jQuery
 */
/**
 * @ignore
 */
(function ($) {

  $.rdf.parsers['application/json'] = {
    parse: $.secureEvalJSON,
    serialize: $.toJSON,
    triples: function (data) {
      var s, subject, p, property, o, object, i, opts, triples = [];
      for (s in data) {
        subject = (s.substring(0, 2) === '_:') ? $.rdf.blank(s) : $.rdf.resource('<' + s + '>');
        for (p in data[s]) {
          property = $.rdf.resource('<' + p + '>');
          for (i = 0; i < data[s][p].length; i += 1) {
            o = data[s][p][i];
            if (o.type === 'uri') {
              object = $.rdf.resource('<' + o.value + '>');
            } else if (o.type === 'bnode') {
              object = $.rdf.blank(o.value);
            } else {
              // o.type === 'literal'
              if (o.datatype !== undefined) {
                object = $.rdf.literal(o.value, { datatype: o.datatype });
              } else {
                opts = {};
                if (o.lang !== undefined) {
                  opts.lang = o.lang;
                }
                object = $.rdf.literal('"' + o.value + '"', opts);
              }
            }
            triples.push($.rdf.triple(subject, property, object));
          }
        }
      }
      return triples;
    },
    dump: function (triples) {
      var e = {},
        i, t, s, p;
      for (i = 0; i < triples.length; i += 1) {
        t = triples[i];
        s = t.subject.value.toString();
        p = t.property.value.toString();
        if (e[s] === undefined) {
          e[s] = {};
        }
        if (e[s][p] === undefined) {
          e[s][p] = [];
        }
        e[s][p].push(t.object.dump());
      }
      return e;
    }
  };

})(jQuery);

/*
 * jQuery RDF @VERSION
 *
 * Copyright (c) 2008,2009 Jeni Tennison
 * Licensed under the MIT (MIT-LICENSE.txt)
 *
 * Depends:
 *  jquery.uri.js
 *  jquery.xmlns.js
 *  jquery.datatype.js
 *  jquery.curie.js
 *  jquery.rdf.js
 *  jquery.rdf.json.js
 *  jquery.rdf.xml.js
 */
/**
 * @fileOverview jQuery RDF/XML parser
 * @author <a href="mailto:jeni@jenitennison.com">Jeni Tennison</a>
 * @copyright (c) 2008,2009 Jeni Tennison
 * @license MIT license (MIT-LICENSE.txt)
 * @version 1.0
 */
/**
 * @exports $ as jQuery
 */
/**
 * @ignore
 */
(function ($) {
  var
    rdfNs = "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
  
    addAttribute = function (parent, namespace, name, value) {
      var doc = parent.ownerDocument,
        a;
      if (namespace !== undefined && namespace !== null) {
        if (doc.createAttributeNS) {
          a = doc.createAttributeNS(namespace, name);
          a.nodeValue = value;
          parent.attributes.setNamedItemNS(a);
        } else {
          a = doc.createNode(2, name, namespace);
          a.nodeValue = value;
          parent.attributes.setNamedItem(a);
        }
      } else {
        a = doc.createAttribute(name);
        a.nodeValue = value;
        parent.attributes.setNamedItem(a);
      }
      return parent;
    },

    createXmlnsAtt = function (parent, namespace, prefix) {
      if (namespace === 'http://www.w3.org/XML/1998/namespace' || namespace === 'http://www.w3.org/2000/xmlns/') {
      } else if (prefix) {
        addAttribute(parent, 'http://www.w3.org/2000/xmlns/', 'xmlns:' + prefix, namespace);
      } else {
        addAttribute(parent, undefined, 'xmlns', namespace);
      }
      return parent;
    },

    createDocument = function (namespace, name) {
      var doc, xmlns = '', prefix, addAttribute = false;
      if (namespace !== undefined && namespace !== null) {
        if (/:/.test(name)) {
          prefix = /([^:]+):/.exec(name)[1];
        }
        addAttribute = true;
      }
      if (document.implementation &&
          document.implementation.createDocument) {
        doc = document.implementation.createDocument(namespace, name, null);
        if (addAttribute) {
          createXmlnsAtt(doc.documentElement, namespace, prefix);
        }
        return doc;
      } else {
        doc = new ActiveXObject("Microsoft.XMLDOM");
        doc.async = "false";
        if (prefix === undefined) {
          xmlns = ' xmlns="' + namespace + '"';
        } else {
          xmlns = ' xmlns:' + prefix + '="' + namespace + '"';
        }
        doc.loadXML('<' + name + xmlns + '/>');
        return doc;
      }
    },

    appendElement = function (parent, namespace, name, indent) {
      var doc = parent.ownerDocument,
        e;
      if (namespace !== undefined && namespace !== null) {
        e = doc.createElementNS ? doc.createElementNS(namespace, name) : doc.createNode(1, name, namespace);
      } else {
        e = doc.createElement(name);
      }
      if (indent !== -1) {
        appendText(parent, '\n');
        if (indent === 0) {
          appendText(parent, '\n');
        } else {
          appendText(parent, '  ');
        }
      }
      parent.appendChild(e);
      return e;
    },

    appendText = function (parent, text) {
      var doc = parent.ownerDocument,
        t;
      t = doc.createTextNode(text);
      parent.appendChild(t);
      return parent;
    },

    appendXML = function (parent, xml) {
      var parser, doc, i, child;
      try {
        doc = new ActiveXObject('Microsoft.XMLDOM');
        doc.async = "false";
        doc.loadXML('<temp>' + xml + '</temp>');
      } catch(e) {
        parser = new DOMParser();
        doc = parser.parseFromString('<temp>' + xml + '</temp>', 'text/xml');
      }
      for (i = 0; i < doc.documentElement.childNodes.length; i += 1) {
        parent.appendChild(doc.documentElement.childNodes[i].cloneNode(true));
      }
      return parent;
    },

    createRdfXml = function (triples, options) {
      var doc = createDocument(rdfNs, 'rdf:RDF'),
        dump = $.rdf.parsers['application/json'].dump(triples),
        namespaces = options.namespaces || {},
        indent = options.indent || false,
        n, s, se, p, pe, i, v,
        m, local, ns, prefix;
      for (n in namespaces) {
        createXmlnsAtt(doc.documentElement, namespaces[n], n);
      }
      for (s in dump) {
        if (dump[s][$.rdf.type.value] !== undefined) {
          m = /(.+[#\/])([^#\/]+)/.exec(dump[s][$.rdf.type.value][0].value);
          ns = m[1];
          local = m[2];
          for (n in namespaces) {
            if (namespaces[n].toString() === ns) {
              prefix = n;
              break;
            }
          }
          se = appendElement(doc.documentElement, ns, prefix + ':' + local, indent ? 0 : -1);
        } else {
          se = appendElement(doc.documentElement, rdfNs, 'rdf:Description', indent ? 0 : -1);
        }
        if (/^_:/.test(s)) {
          addAttribute(se, rdfNs, 'rdf:nodeID', s.substring(2));
        } else {
          addAttribute(se, rdfNs, 'rdf:about', s);
        }
        for (p in dump[s]) {
          if (p !== $.rdf.type.value.toString() || dump[s][p].length > 1) {
            m = /(.+[#\/])([^#\/]+)/.exec(p);
            ns = m[1];
            local = m[2];
            for (n in namespaces) {
              if (namespaces[n].toString() === ns) {
                prefix = n;
                break;
              }
            }
            for (i = (p === $.rdf.type.value.toString() ? 1 : 0); i < dump[s][p].length; i += 1) {
              v = dump[s][p][i];
              pe = appendElement(se, ns, prefix + ':' + local, indent ? 1 : -1);
              if (v.type === 'uri') {
                addAttribute(pe, rdfNs, 'rdf:resource', v.value);
              } else if (v.type === 'literal') {
                if (v.datatype !== undefined) {
                  if (v.datatype === 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral') {
                    addAttribute(pe, rdfNs, 'rdf:parseType', 'Literal');
                    if (indent) {
                      appendText(pe, '\n    ');
                    }
                    appendXML(pe, v.value);
                    if (indent) {
                      appendText(pe, '\n  ');
                    }
                  } else {
                    addAttribute(pe, rdfNs, 'rdf:datatype', v.datatype);
                    appendText(pe, v.value);
                  }
                } else if (v.lang !== undefined) {
                  addAttribute(pe, 'http://www.w3.org/XML/1998/namespace', 'xml:lang', v.lang);
                  appendText(pe, v.value);
                } else {
                  appendText(pe, v.value);
                }
              } else {
                // blank node
                addAttribute(pe, rdfNs, 'rdf:nodeID', v.value.substring(2));
              }
            }
            if (indent) {
              appendText(se, '\n');
            }
          }
        }
      }
      if (indent) {
        appendText(doc.documentElement, '\n\n');
      }
      return doc;
    },

    getDefaultNamespacePrefix = function (namespaceUri) {
      switch (namespaceUri) {
        case 'http://www.w3.org/1999/02/22-rdf-syntax-ns':
          return 'rdf';
        case 'http://www.w3.org/XML/1998/namespace':
          return 'xml';
        case 'http://www.w3.org/2000/xmlns/':
          return 'xmlns';
        default:
          throw ('No default prefix mapped for namespace ' + namespaceUri);
      }
    },

    hasAttributeNS  = function(elem, namespace, name){
      var basename;
      if (elem.hasAttributeNS) {
        return elem.hasAttributeNS(namespace, name);
      } else {
        try {
          basename = /:/.test(name) ? /:(.+)$/.exec(name)[1] : name;
          return elem.attributes.getQualifiedItem(basename, namespace) !== null;
        } catch (e) {
          return elem.getAttribute(getDefaultNamespacePrefix(namespace) + ':' + name) !== null;
        }
      }
    },

    getAttributeNS = function(elem, namespace, name){
      var basename;
      if (elem.getAttributeNS) {
        return elem.getAttributeNS(namespace, name);
      } else {
        try {
          basename = /:/.test(name) ? /:(.+)$/.exec(name)[1] : name;
          return elem.attributes.getQualifiedItem(basename, namespace).nodeValue;
        } catch (e) {
          return elem.getAttribute(getDefaultNamespacePrefix(namespace) + ':' + name);
        }
      }
    },

    getLocalName = function(elem){
      return elem.localName || elem.baseName;
    },

    parseRdfXmlSubject = function (elem, base) {
      var s, subject;
      if (hasAttributeNS(elem, rdfNs, 'about')) {
        s = getAttributeNS(elem, rdfNs, 'about');
        subject = $.rdf.resource('<' + s + '>', { base: base });
      } else if (hasAttributeNS(elem, rdfNs, 'ID')) {
        s = getAttributeNS(elem, rdfNs, 'ID');
        subject = $.rdf.resource('<#' + s + '>', { base: base });
      } else if (hasAttributeNS(elem, rdfNs, 'nodeID')) {
        s = getAttributeNS(elem, rdfNs, 'nodeID');
        subject = $.rdf.blank('_:' + s);
      } else {
        subject = $.rdf.blank('[]');
      }
      return subject;
    },

    parseRdfXmlDescription = function (elem, isDescription, base, lang) {
      var subject, p, property, o, object, reified, lang, i, j, li = 1,
        collection1, collection2, collectionItem, collectionItems = [],
        parseType, serializer, literalOpts = {}, oTriples, triples = [];
      lang = getAttributeNS(elem, 'http://www.w3.org/XML/1998/namespace', 'lang') || lang;
      base = getAttributeNS(elem, 'http://www.w3.org/XML/1998/namespace', 'base') || base;
      if (lang !== null && lang !== undefined && lang !== '') {
        literalOpts = { lang: lang };
      }
      subject = parseRdfXmlSubject(elem, base);
      if (isDescription && (elem.namespaceURI !== rdfNs || getLocalName(elem) !== 'Description')) {
        property = $.rdf.type;
        object = $.rdf.resource('<' + elem.namespaceURI + getLocalName(elem) + '>');
        triples.push($.rdf.triple(subject, property, object));
      }
      for (i = 0; i < elem.attributes.length; i += 1) {
        p = elem.attributes.item(i);
        if (p.namespaceURI !== undefined &&
            p.namespaceURI !== 'http://www.w3.org/2000/xmlns/' &&
            p.namespaceURI !== 'http://www.w3.org/XML/1998/namespace' &&
            p.prefix !== 'xmlns' &&
            p.prefix !== 'xml') {
          if (p.namespaceURI !== rdfNs) {
            property = $.rdf.resource('<' + p.namespaceURI + getLocalName(p) + '>');
            object = $.rdf.literal(literalOpts.lang ? p.nodeValue : '"' + p.nodeValue.replace(/"/g, '\\"') + '"', literalOpts);
            triples.push($.rdf.triple(subject, property, object));
          } else if (getLocalName(p) === 'type') {
            property = $.rdf.type;
            object = $.rdf.resource('<' + p.nodeValue + '>', { base: base });
            triples.push($.rdf.triple(subject, property, object));
          }
        }
      }
      var parentLang = lang;
      for (i = 0; i < elem.childNodes.length; i += 1) {
        p = elem.childNodes[i];
        if (p.nodeType === 1) {
          if (p.namespaceURI === rdfNs && getLocalName(p) === 'li') {
            property = $.rdf.resource('<' + rdfNs + '_' + li + '>');
            li += 1;
          } else {
            property = $.rdf.resource('<' + p.namespaceURI + getLocalName(p) + '>');
          }
          lang = getAttributeNS(p, 'http://www.w3.org/XML/1998/namespace', 'lang') || parentLang;
           if (lang !== null && lang !== undefined && lang !== '') {
             literalOpts = { lang: lang };
          } else {
            literalOpts = {};
          }
          if (hasAttributeNS(p, rdfNs, 'resource')) {
            o = getAttributeNS(p, rdfNs, 'resource');
            object = $.rdf.resource('<' + o + '>', { base: base });
          } else if (hasAttributeNS(p, rdfNs, 'nodeID')) {
            o = getAttributeNS(p, rdfNs, 'nodeID');
            object = $.rdf.blank('_:' + o);
          } else if (hasAttributeNS(p, rdfNs, 'parseType')) {
            parseType = getAttributeNS(p, rdfNs, 'parseType');
            if (parseType === 'Literal') {
              try {
                serializer = new XMLSerializer();
                o = serializer.serializeToString(p.getElementsByTagName('*')[0]);
              } catch (e) {
                o = "";
                for (j = 0; j < p.childNodes.length; j += 1) {
                  o += p.childNodes[j].xml;
                }
              }
              object = $.rdf.literal(o, { datatype: rdfNs + 'XMLLiteral' });
            } else if (parseType === 'Resource') {
              oTriples = parseRdfXmlDescription(p, false, base, lang);
              if (oTriples.length > 0) {
                object = oTriples[oTriples.length - 1].subject;
                triples = triples.concat(oTriples);
              } else {
                object = $.rdf.blank('[]');
              }
            } else if (parseType === 'Collection') {
              if (p.getElementsByTagName('*').length > 0) {
                for (j = 0; j < p.childNodes.length; j += 1) {
                  o = p.childNodes[j];
                  if (o.nodeType === 1) {
                    collectionItems.push(o);
                  }
                }
                collection1 = $.rdf.blank('[]');
                object = collection1;
                for (j = 0; j < collectionItems.length; j += 1) {
                  o = collectionItems[j];
                  oTriples = parseRdfXmlDescription(o, true, base, lang);
                  if (oTriples.length > 0) {
                    collectionItem = oTriples[oTriples.length - 1].subject;
                    triples = triples.concat(oTriples);
                  } else {
                    collectionItem = parseRdfXmlSubject(o);
                  }
                  triples.push($.rdf.triple(collection1, $.rdf.first, collectionItem));
                  if (j === collectionItems.length - 1) {
                    triples.push($.rdf.triple(collection1, $.rdf.rest, $.rdf.nil));
                  } else {
                    collection2 = $.rdf.blank('[]');
                    triples.push($.rdf.triple(collection1, $.rdf.rest, collection2));
                    collection1 = collection2;
                  }
                }
              } else {
                object = $.rdf.nil;
              }
            }
          } else if (hasAttributeNS(p, rdfNs, 'datatype')) {
            o = p.childNodes[0] ? p.childNodes[0].nodeValue : "";
            object = $.rdf.literal(o, { datatype: getAttributeNS(p, rdfNs, 'datatype') });
          } else if (p.getElementsByTagName('*').length > 0) {
            for (j = 0; j < p.childNodes.length; j += 1) {
              o = p.childNodes[j];
              if (o.nodeType === 1) {
                oTriples = parseRdfXmlDescription(o, true, base, lang);
                if (oTriples.length > 0) {
                  object = oTriples[oTriples.length - 1].subject;
                  triples = triples.concat(oTriples);
                } else {
                  object = parseRdfXmlSubject(o);
                }
              }
            }
          } else if (p.childNodes.length > 0) {
            o = p.childNodes[0].nodeValue;
            object = $.rdf.literal(literalOpts.lang ? o : '"' + o.replace(/"/g, '\\"') + '"', literalOpts);
          } else {
            oTriples = parseRdfXmlDescription(p, false, base, lang);
            if (oTriples.length > 0) {
              object = oTriples[oTriples.length - 1].subject;
              triples = triples.concat(oTriples);
            } else {
              object = $.rdf.blank('[]');
            }
          }
          triples.push($.rdf.triple(subject, property, object));
          if (hasAttributeNS(p, rdfNs, 'ID')) {
            reified = $.rdf.resource('<#' + getAttributeNS(p, rdfNs, 'ID') + '>', { base: base });
            triples.push($.rdf.triple(reified, $.rdf.subject, subject));
            triples.push($.rdf.triple(reified, $.rdf.property, property));
            triples.push($.rdf.triple(reified, $.rdf.object, object));
          }
        }
      }
      return triples;
    },

    parseRdfXml = function (doc) {
      var i, lang, d, triples = [];
      if (doc.documentElement.namespaceURI === rdfNs && getLocalName(doc.documentElement) === 'RDF') {
        lang = getAttributeNS(doc.documentElement, 'http://www.w3.org/XML/1998/namespace', 'lang');
        base = getAttributeNS(doc.documentElement, 'http://www.w3.org/XML/1998/namespace', 'base') || $.uri.base();
        triples = $.map(doc.documentElement.childNodes, function (d) {
          if (d.nodeType === 1) {
            return parseRdfXmlDescription(d, true, base, lang);
          } else {
            return null;
          }
        });
        /*
        for (i = 0; i < doc.documentElement.childNodes.length; i += 1) {
          d = doc.documentElement.childNodes[i];
          if (d.nodeType === 1) {
            triples = triples.concat(parseRdfXmlDescription(d, true, base, lang));
          }
        }
        */
      } else {
        triples = parseRdfXmlDescription(doc.documentElement, true);
      }
      return triples;
    };

  $.rdf.parsers['application/rdf+xml'] = {
    parse: function (data) {
      var doc;
      try {
        doc = new ActiveXObject("Microsoft.XMLDOM");
        doc.async = "false";
        doc.loadXML(data);
      } catch(e) {
        var parser = new DOMParser();
        doc = parser.parseFromString(data, 'text/xml');
      }
      return doc;
    },
    serialize: function (data) {
      if (data.xml) {
        return data.xml.replace(/\s+$/,'');
      } else {
        serializer = new XMLSerializer();
        return serializer.serializeToString(data);
      }
    },
    triples: parseRdfXml,
    dump: createRdfXml
  };

})(jQuery);
/*
 * jQuery RDFa @VERSION
 *
 * Copyright (c) 2008,2009 Jeni Tennison
 * Licensed under the MIT (MIT-LICENSE.txt)
 *
 * Depends:
 *  jquery.uri.js
 *  jquery.xmlns.js
 *  jquery.curie.js
 *  jquery.datatype.js
 *  jquery.rdf.js
 */
/**
 * @fileOverview jQuery RDFa processing
 * @author <a href="mailto:jeni@jenitennison.com">Jeni Tennison</a>
 * @copyright (c) 2008,2009 Jeni Tennison
 * @license MIT license (MIT-LICENSE.txt)
 * @version 1.0
 * @requires jquery.uri.js
 * @requires jquery.xmlns.js
 * @requires jquery.curie.js
 * @requires jquery.datatype.js
 * @requires jquery.rdf.js
 */
(function ($) {

  var
    ns = {
      rdf: "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
      xsd: "http://www.w3.org/2001/XMLSchema#"
    },

    rdfXMLLiteral = ns.rdf + 'XMLLiteral',

    rdfaCurieDefaults = $.fn.curie.defaults,

    attRegex = /\s([^ =]+)\s*=\s*(?:"([^"]*)"|'([^']*)'|([^ >]+))/g,

    docResource = $.rdf.resource('<>'),

    parseEntities = function (string) {
      var result = "", m, entity;
      if (!/&/.test(string)) {
         return string;
      }
      while (string.length > 0) {
        m = /([^&]*)(&([^;]+);)(.*)/g.exec(string);
        if (m === null) {
          result += string;
          break;
        }
        result += m[1];
        entity = m[3];
        string = m[4];
        if (entity.charAt(0) === '#') {
          if (entity.charAt(1) === 'x') {
              result += String.fromCharCode(parseInt(entity.substring(2), 16));
          } else {
              result += String.fromCharCode(parseInt(entity.substring(1), 10));
          }
        } else {
          switch(entity) {
            case 'amp':
              result += '&';
              break;
            case 'nbsp':
              result += String.fromCharCode(160);
              break;
            case 'quot':
              result += '"';
              break;
            case 'apos':
              result += "'";
              break;
            default:
              result += '&' + entity + ';';
          }
        }
      }
      return result;
    },

    getAttributes = function (elem) {
      var i, e, a, tag, name, value, attMap, prefix,
        ns = {}, atts = {};
      e = elem[0];
      ns[':length'] = 0;
      if (e.attributes && e.attributes.getNamedItemNS) {
        attMap = e.attributes;
        for (i = 0; i < attMap.length; i += 1) {
          a = attMap[i];
          if (/^xmlns(:(.+))?$/.test(a.nodeName) && a.nodeValue !== '') {
            prefix = /^xmlns(:(.+))?$/.exec(a.nodeName)[2] || '';
            ns[prefix] = $.uri(a.nodeValue);
            ns[':length'] += 1;
          } else if (/rel|rev|lang|xml:lang/.test(a.nodeName)) {
            atts[a.nodeName] = a.nodeValue === '' ? undefined : a.nodeValue;
          } else if (/about|href|src|resource|property|typeof|content|datatype/.test(a.nodeName)) {
            atts[a.nodeName] = a.nodeValue === null ? undefined : a.nodeValue;
          }
        }
      } else {
        tag = /<[^>]+>/.exec(e.outerHTML);
        a = attRegex.exec(tag);
        while (a !== null) {
          name = a[1];
          value = a[2] || a[3] || a[4];
          if (/^xmlns/.test(name) && name !== 'xmlns:' && value !== '') {
            prefix = /^xmlns(:(.+))?$/.exec(name)[2] || '';
            ns[prefix] = $.uri(value);
            ns[':length'] += 1;
          } else if (/about|href|src|resource|property|typeof|content|datatype|rel|rev|lang|xml:lang/.test(name)) {
            atts[name] = parseEntities(value);
          }
          a = attRegex.exec(tag);
        }
        attRegex.lastIndex = 0;
      }
      return { atts: atts, namespaces: ns };
    },

    getAttribute = function (elem, attr) {
      var val = elem[0].getAttribute(attr);
      if (attr === 'rev' || attr === 'rel' || attr === 'lang' || attr === 'xml:lang') {
        val = val === '' ? undefined : val;
      }
      return val === null ? undefined : val;
    },

    resourceFromUri = function (uri) {
      return $.rdf.resource(uri);
    },

    resourceFromCurie = function (curie, elem, options) {
      if (curie.substring(0, 2) === '_:') {
        return $.rdf.blank(curie);
      } else {
        try {
          return resourceFromUri($.curie(curie, options));
        } catch (e) {
          return undefined;
        }
      }
    },

    resourceFromSafeCurie = function (safeCurie, elem, options) {
      var m = /^\[([^\]]+)\]$/.exec(safeCurie),
        base = options.base || elem.base();
      return m ? resourceFromCurie(m[1], elem, options) : resourceFromUri($.uri(safeCurie, base));
    },

    resourcesFromCuries = function (curies, elem, options) {
      var i, resource, resources = [];
      curies = curies && curies.split ? curies.split(/[ \t\n\r\x0C]+/g) : [];
      for (i = 0; i < curies.length; i += 1) {
        if (curies[i] !== '') {
          resource = resourceFromCurie(curies[i], elem, options);
          if (resource !== undefined) {
            resources.push(resource);
          }
        }
      }
      return resources;
    },

    removeCurie = function (curies, resource, options) {
      var i, r, newCuries = [];
      resource = resource.type === 'uri' ? resource : $.rdf.resource(resource, options);
      curies = curies && curies.split ? curies.split(/\s+/) : [];
      for (i = 0; i < curies.length; i += 1) {
        if (curies[i] !== '') {
          r = resourceFromCurie(curies[i], null, options);
          if (r !== resource) {
            newCuries.push(curies[i]);
          }
        }
      }
      return newCuries.reverse().join(' ');
    },

    getObjectResource = function (elem, context, relation) {
      var r, resource, atts, curieOptions;
      context = context || {};
      atts = context.atts || getAttributes(elem).atts;
      r = relation === undefined ? atts.rel !== undefined || atts.rev !== undefined : relation;
      resource = atts.resource;
      resource = resource === undefined ? atts.href : resource;
      if (resource === undefined) {
        resource = r ? $.rdf.blank('[]') : resource;
      } else {
        curieOptions = context.curieOptions || $.extend({}, rdfaCurieDefaults, { namespaces: elem.xmlns() });
        resource = resourceFromSafeCurie(resource, elem, curieOptions);
      }
      return resource;
    },

    getSubject = function (elem, context, relation) {
      var r, atts, curieOptions, subject, skip = false;
      context = context || {};
      atts = context.atts || getAttributes(elem).atts;
      curieOptions = context.curieOptions || $.extend({}, rdfaCurieDefaults, { namespaces: elem.xmlns(), base: elem.base() });
      r = relation === undefined ? atts.rel !== undefined || atts.rev !== undefined : relation;
      if (atts.about !== undefined) {
        subject = resourceFromSafeCurie(atts.about, elem, curieOptions);
      }
      if (subject === undefined && atts.src !== undefined) {
        subject = resourceFromSafeCurie(atts.src, elem, curieOptions);
      }
      if (!r && subject === undefined && atts.resource !== undefined) {
        subject = resourceFromSafeCurie(atts.resource, elem, curieOptions);
      }
      if (!r && subject === undefined && atts.href !== undefined) {
        subject = resourceFromSafeCurie(atts.href, elem, curieOptions);
      }
      if (subject === undefined) {
        if (/head|body/i.test(elem[0].nodeName)) {
          subject = docResource;
        } else if (atts['typeof'] !== undefined) {
          subject = $.rdf.blank('[]');
        } else if (elem[0].parentNode.nodeType === 1) {
          subject = context.object || getObjectResource(elem.parent()) || getSubject(elem.parent()).subject;
          skip = !r && atts.property === undefined;
        } else {
          subject = docResource;
        }
      }
      return { subject: subject, skip: skip };
    },

    getLang = function (elem, context) {
      var lang;
      context = context || {};
      if (context.atts) {
        lang = context.atts.lang;
        lang = lang || context.atts['xml:lang'];
      } else {
        lang = elem[0].getAttribute('lang');
        lang = (lang === null || lang === '') ? elem[0].getAttribute('xml:lang') : lang;
        lang = (lang === null || lang === '') ? undefined : lang;
      }
      if (lang === undefined) {
        if (context.lang) {
          lang = context.lang;
        } else {
          if (elem[0].parentNode.nodeType === 1) {
            lang = getLang(elem.parent());
          }
        }
      }
      return lang;
    },

    entity = function (c) {
      switch (c) {
      case '<':
        return '&lt;';
      case '"':
        return '&quot;';
      case '&':
        return '&amp;';
      }
    },

    serialize = function (elem, ignoreNs) {
      var i, string = '', atts, a, name, ns, tag;
      elem.contents().each(function () {
        var j = $(this),
          e = j[0];
        if (e.nodeType === 1) { // tests whether the node is an element
          name = e.nodeName.toLowerCase();
          string += '<' + name;
          if (e.outerHTML) {
            tag = /<[^>]+>/.exec(e.outerHTML);
            a = attRegex.exec(tag);
            while (a !== null) {
              if (!/^jQuery/.test(a[1])) {
                string += ' ' + a[1] + '=';
                string += a[2] ? a[3] : '"' + a[1] + '"';
              }
              a = attRegex.exec(tag);
            }
            attRegex.lastIndex = 0;
          } else {
            atts = e.attributes;
            for (i = 0; i < atts.length; i += 1) {
              a = atts.item(i);
              string += ' ' + a.nodeName + '="';
              string += a.nodeValue.replace(/[<"&]/g, entity);
              string += '"';
            }
          }
          if (!ignoreNs) {
            ns = j.xmlns('');
            if (ns !== undefined && j.attr('xmlns') === undefined) {
              string += ' xmlns="' + ns + '"';
            }
          }
          string += '>';
          string += serialize(j, true);
          string += '</' + name + '>';
        } else if (e.nodeType === 8) { // tests whether the node is a comment
          string += '<!--';
          string += e.nodeValue;
          string += '-->';
        } else {
          string += e.nodeValue;
        }
      });
      return string;
    },

    rdfa = function (context) {
      var i, subject, resource, lang, datatype, content,
        types, object, triple, parent,
        properties, rels, revs,
        forward, backward,
        triples = [],
        attsAndNs, atts, namespaces, ns,
        children = this.children();
      context = context || {};
      forward = context.forward || [];
      backward = context.backward || [];
      attsAndNs = getAttributes(this);
      atts = attsAndNs.atts;
      context.atts = atts;
      namespaces = context.namespaces || this.xmlns();
      if (attsAndNs.namespaces[':length'] > 0) {
        namespaces = $.extend({}, namespaces);
        for (ns in attsAndNs.namespaces) {
          if (ns !== ':length') {
            namespaces[ns] = attsAndNs.namespaces[ns];
          }
        }
      }
      context.curieOptions = $.extend({}, rdfaCurieDefaults, { namespaces: namespaces, base: this.base() });
      subject = getSubject(this, context);
      lang = getLang(this, context);
      if (subject.skip) {
        rels = context.forward;
        revs = context.backward;
        subject = context.subject;
        resource = context.object;
      } else {
        subject = subject.subject;
        if (forward.length > 0 || backward.length > 0) {
          parent = context.subject || getSubject(this.parent()).subject;
          for (i = 0; i < forward.length; i += 1) {
            triple = $.rdf.triple(parent, forward[i], subject, { source: this[0] });
            triples.push(triple);
          }
          for (i = 0; i < backward.length; i += 1) {
            triple = $.rdf.triple(subject, backward[i], parent, { source: this[0] });
            triples.push(triple);
          }
        }
        resource = getObjectResource(this, context);
        types = resourcesFromCuries(atts['typeof'], this, context.curieOptions);
        for (i = 0; i < types.length; i += 1) {
          triple = $.rdf.triple(subject, $.rdf.type, types[i], { source: this[0] });
          triples.push(triple);
        }
        properties = resourcesFromCuries(atts.property, this, context.curieOptions);
        if (properties.length > 0) {
          datatype = atts.datatype;
          content = atts.content;
          if (datatype !== undefined && datatype !== '') {
            datatype = $.curie(datatype, context.curieOptions);
            if (datatype === rdfXMLLiteral) {
              object = $.rdf.literal(serialize(this), { datatype: rdfXMLLiteral });
            } else if (content !== undefined) {
              object = $.rdf.literal(content, { datatype: datatype });
            } else {
              object = $.rdf.literal(this.text(), { datatype: datatype });
            }
          } else if (content !== undefined) {
            if (lang === undefined) {
              object = $.rdf.literal('"' + content + '"');
            } else {
              object = $.rdf.literal(content, { lang: lang });
            }
          } else if (children.length === 0 ||
                     datatype === '') {
            lang = getLang(this, context);
            if (lang === undefined) {
              object = $.rdf.literal('"' + this.text() + '"');
            } else {
              object = $.rdf.literal(this.text(), { lang: lang });
            }
          } else {
            object = $.rdf.literal(serialize(this), { datatype: rdfXMLLiteral });
          }
          for (i = 0; i < properties.length; i += 1) {
            triple = $.rdf.triple(subject, properties[i], object, { source: this[0] });
            triples.push(triple);
          }
        }
        rels = resourcesFromCuries(atts.rel, this, context.curieOptions);
        revs = resourcesFromCuries(atts.rev, this, context.curieOptions);
        if (atts.resource !== undefined || atts.href !== undefined) {
          // make the triples immediately
          if (rels !== undefined) {
            for (i = 0; i < rels.length; i += 1) {
              triple = $.rdf.triple(subject, rels[i], resource, { source: this[0] });
              triples.push(triple);
            }
          }
          rels = [];
          if (revs !== undefined) {
            for (i = 0; i < revs.length; i += 1) {
              triple = $.rdf.triple(resource, revs[i], subject, { source: this[0] });
              triples.push(triple);
            }
          }
          revs = [];
        }
      }
      children.each(function () {
        triples = triples.concat(rdfa.call($(this), { forward: rels, backward: revs, subject: subject, object: resource || subject, lang: lang, namespaces: namespaces }));
      });
      return triples;
    },

    gleaner = function (options) {
      var type, atts;
      if (options && options.about !== undefined) {
        atts = getAttributes(this).atts;
        if (options.about === null) {
          return atts.property !== undefined ||
                 atts.rel !== undefined ||
                 atts.rev !== undefined ||
                 atts['typeof'] !== undefined;
        } else {
          return getSubject(this, {atts: atts}).subject.value === options.about;
        }
      } else if (options && options.type !== undefined) {
        type = getAttribute(this, 'typeof');
        if (type !== undefined) {
          return options.type === null ? true : this.curie(type) === options.type;
        }
        return false;
      } else {
        return rdfa.call(this);
      }
    },

    nsCounter = 1,

    createCurieAttr = function (elem, attr, uri) {
      var m, curie, value;
      try {
        curie = elem.createCurie(uri);
      } catch (e) {
        if (uri.toString() === rdfXMLLiteral) {
          elem.attr('xmlns:rdf', ns.rdf);
          curie = 'rdf:XMLLiteral';
        } else {
          m = /^(.+[\/#])([^#]+)$/.exec(uri);
          elem.attr('xmlns:ns' + nsCounter, m[1]);
          curie = 'ns' + nsCounter + ':' + m[2];
          nsCounter += 1;
        }
      }
      value = getAttribute(elem, attr);
      if (value !== undefined) {
        if ($.inArray(curie, value.split(/\s+/)) === -1) {
          elem.attr(attr, value + ' ' + curie);
        }
      } else {
        elem.attr(attr, curie);
      }
    },

    createResourceAttr = function (elem, attr, resource) {
      var ref;
      if (resource.type === 'bnode') {
        ref = '[_:' + resource.id + ']';
      } else {
        ref = $(elem).base().relative(resource.value);
      }
      elem.attr(attr, ref);
    },

    createSubjectAttr = function (elem, subject) {
      var s = getSubject(elem).subject;
      if (subject !== s) {
        createResourceAttr(elem, 'about', subject);
      }
      elem.removeData('rdfa.subject');
    },

    createObjectAttr = function (elem, object) {
      var o = getObjectResource(elem);
      if (object !== o) {
        createResourceAttr(elem, 'resource', object);
      }
      elem.removeData('rdfa.objectResource');
    },

    resetLang = function (elem, lang) {
      elem.wrapInner('<span></span>')
        .children('span')
        .attr('lang', lang);
      return elem;
    },

    addRDFa = function (triple) {
      var hasContent, hasRelation, hasRDFa, overridableObject, span,
        subject, sameSubject,
        object, sameObject,
        lang, content,
        i, atts,
        ns = this.xmlns();
      span = this;
      atts = getAttributes(this).atts;
      if (typeof triple === 'string') {
        triple = $.rdf.triple(triple, { namespaces: ns, base: this.base() });
      } else if (triple.rdfquery) {
        addRDFa.call(this, triple.sources().get(0));
        return this;
      } else if (triple.length) {
        for (i = 0; i < triple.length; i += 1) {
          addRDFa.call(this, triple[i]);
        }
        return this;
      }
      hasRelation = atts.rel !== undefined || atts.rev !== undefined;
      hasRDFa = hasRelation || atts.property !== undefined || atts['typeof'] !== undefined;
      if (triple.object.type !== 'literal') {
        subject = getSubject(this, {atts: atts}, true).subject;
        object = getObjectResource(this, {atts: atts}, true);
        overridableObject = !hasRDFa && atts.resource === undefined;
        sameSubject = subject === triple.subject;
        sameObject = object === triple.object;
        if (triple.property === $.rdf.type) {
          if (sameSubject) {
            createCurieAttr(this, 'typeof', triple.object.value);
          } else if (hasRDFa) {
            span = this.wrapInner('<span />').children('span');
            createCurieAttr(span, 'typeof', triple.object.value);
            if (object !== triple.subject) {
              createSubjectAttr(span, triple.subject);
            }
          } else {
            createCurieAttr(this, 'typeof', triple.object.value);
            createSubjectAttr(this, triple.subject);
          }
        } else if (sameSubject) {
          // use a rel
          if (sameObject) {
            createCurieAttr(this, 'rel', triple.property.value);
          } else if (overridableObject || !hasRDFa) {
            createCurieAttr(this, 'rel', triple.property.value);
            createObjectAttr(this, triple.object);
          } else {
            span = this.wrap('<span />').parent();
            createCurieAttr(span, 'rev', triple.property.value);
            createSubjectAttr(span, triple.object);
          }
        } else if (subject === triple.object) {
          if (object === triple.subject) {
            // use a rev
            createCurieAttr(this, 'rev', triple.property.value);
          } else if (overridableObject || !hasRDFa) {
            createCurieAttr(this, 'rev', triple.property.value);
            createObjectAttr(this, triple.subject);
          } else {
            // wrap in a span with a rel
            span = this.wrap('<span />').parent();
            createCurieAttr(span, 'rel', triple.property.value);
            createSubjectAttr(span, triple.subject);
          }
        } else if (sameObject) {
          if (hasRDFa) {
            // use a rev on a nested span
            span = this.wrapInner('<span />').children('span');
            createCurieAttr(span, 'rev', triple.property.value);
            createObjectAttr(span, triple.subject);
            span = span.wrapInner('<span />').children('span');
            createSubjectAttr(span, triple.object);
            span = this;
          } else {
            createSubjectAttr(this, triple.subject);
            createCurieAttr(this, 'rel', triple.property.value);
          }
        } else if (object === triple.subject) {
          if (hasRDFa) {
            // wrap the contents in a span and use a rel
            span = this.wrapInner('<span />').children('span');
            createCurieAttr(span, 'rel', this.property.value);
            createObjectAttr(span, triple.object);
            span = span.wrapInner('<span />').children('span');
            createSubjectAttr(span, object);
            span = this;
          } else {
            // use a rev on this element
            createSubjectAttr(this, triple.object);
            createCurieAttr(this, 'rev', triple.property.value);
          }
        } else if (hasRDFa) {
          span = this.wrapInner('<span />').children('span');
          createCurieAttr(span, 'rel', triple.property.value);
          createSubjectAttr(span, triple.subject);
          createObjectAttr(span, triple.object);
          if (span.children('*').length > 0) {
            span = this.wrapInner('<span />').children('span');
            createSubjectAttr(span, subject);
          }
          span = this;
        } else {
          createCurieAttr(span, 'rel', triple.property.value);
          createSubjectAttr(this, triple.subject);
          createObjectAttr(this, triple.object);
          if (this.children('*').length > 0) {
            span = this.wrapInner('<span />').children('span');
            createSubjectAttr(span, subject);
            span = this;
          }
        }
      } else {
        subject = getSubject(this, {atts: atts}).subject;
        object = getObjectResource(this, {atts: atts});
        sameSubject = subject === triple.subject;
        hasContent = this.text() !== triple.object.value;
        if (atts.property !== undefined) {
          content = atts.content;
          sameObject = content !== undefined ? content === triple.object.value : !hasContent;
          if (sameSubject && sameObject) {
            createCurieAttr(this, 'property', triple.property.value);
          } else {
            span = this.wrapInner('<span />').children('span');
            return addRDFa.call(span, triple);
          }
        } else {
          if (object === triple.subject) {
            span = this.wrapInner('<span />').children('span');
            return addRDFa.call(span, triple);
          }
          createCurieAttr(this, 'property', triple.property.value);
          createSubjectAttr(this, triple.subject);
          if (hasContent) {
            if (triple.object.datatype && triple.object.datatype.toString() === rdfXMLLiteral) {
              this.html(triple.object.value);
            } else {
              this.attr('content', triple.object.value);
            }
          }
          lang = getLang(this);
          if (triple.object.lang) {
            if (lang !== triple.object.lang) {
              this.attr('lang', triple.object.lang);
              if (hasContent) {
                resetLang(this, lang);
              }
            }
          } else if (triple.object.datatype) {
            createCurieAttr(this, 'datatype', triple.object.datatype);
          } else {
            // the empty datatype ensures that any child elements that might be added won't mess up this triple
            if (!hasContent) {
              this.attr('datatype', '');
            }
            // the empty lang ensures that a language won't be assigned to the literal
            if (lang !== undefined) {
              this.attr('lang', '');
              if (hasContent) {
                resetLang(this, lang);
              }
            }
          }
        }
      }
      this.parents().andSelf().trigger("rdfChange");
      return span;
    },

    removeRDFa = function (what) {
      var span, atts, property, rel, rev, type,
        ns = this.xmlns();
      atts = getAttributes(this).atts;
      if (what.length) {
        for (i = 0; i < what.length; i += 1) {
          removeRDFa.call(this, what[i]);
        }
        return this;
      }
      hasRelation = atts.rel !== undefined || atts.rev !== undefined;
      hasRDFa = hasRelation || atts.property !== undefined || atts['typeof'] !== undefined;
      if (hasRDFa) {
        if (what.property !== undefined) {
          if (atts.property !== undefined) {
            property = removeCurie(atts.property, what.property, { namespaces: ns });
            if (property === '') {
              this.removeAttr('property');
            } else {
              this.attr('property', property);
            }
          }
          if (atts.rel !== undefined) {
            rel = removeCurie(atts.rel, what.property, { namespaces: ns });
            if (rel === '') {
              this.removeAttr('rel');
            } else {
              this.attr('rel', rel);
            }
          }
          if (atts.rev !== undefined) {
            rev = removeCurie(atts.rev, what.property, { namespaces: ns });
            if (rev === '') {
              this.removeAttr('rev');
            } else {
              this.attr('rev', rev);
            }
          }
        }
        if (what.type !== undefined) {
          if (atts['typeof'] !== undefined) {
            type = removeCurie(atts['typeof'], what.type, { namespaces: ns });
            if (type === '') {
              this.removeAttr('typeof');
            } else {
              this.attr('typeof', type);
            }
          }
        }
        if (atts.property === this.attr('property') && atts.rel === this.attr('rel') && atts.rev === this.attr('rev') && atts['typeof'] === this.attr('typeof')) {
          return removeRDFa.call(this.parent(), what);
        }
      }
      this.parents().andSelf().trigger("rdfChange");
      return this;
    };

  /**
   * Creates a {@link jQuery.rdf} object containing the RDF triples parsed from the RDFa found in the current jQuery selection or adds the specified triple as RDFa markup on each member of the current jQuery selection. To create an {@link jQuery.rdf} object, you will usually want to use {@link jQuery#rdf} instead, as this may perform other useful processing (such as of microformats used within the page).
   * @methodOf jQuery#
   * @name jQuery#rdfa
   * @param {jQuery.rdf.triple} [triple] The RDF triple to be added to each item in the jQuery selection. 
   * @returns {jQuery.rdf}
   * @example
   * // Extract RDFa markup from all span elements contained inside #main
   * rdf = $('#main > span').rdfa();
   * @example
   * // Add RDFa markup to a particular element
   *  var span = $('#main > p > span');
   *  span.rdfa('&lt;> dc:date "2008-10-19"^^xsd:date .');
   */
  $.fn.rdfa = function (triple) {
    if (triple === undefined) {
      var triples = $.map($(this), function (elem) {
        return rdfa.call($(elem));
      });
      return $.rdf({ triples: triples });
    } else {
      $(this).each(function () {
        addRDFa.call($(this), triple);
      });
      return this;
    }
  };

  /**
   * Removes the specified RDFa markup from each of the items in the current jQuery selection. The input parameter can be either an object or an array of objects. The objects can either have a <code>type</code> property, in which case the specified type is removed from the RDFa provided on the selected elements, or a <code>property</code> property, in which case the specified property is removed from the RDFa provided on the selected elements.
   * @methodOf jQuery#
   * @name jQuery#removeRdfa
   * @param {Object|Object[]} triple The RDFa markup items to be removed
   * from the items in the jQuery selection.
   * @returns {jQuery} The original jQuery object.
   * @example 
   * // To remove a property resource or relation from an element 
   * $('#main > p > a').removeRdfa({ property: "dc:creator" });
   * @example
   * // To remove a type from an element
   * $('#main >p > a').removeRdfa({ type: "foaf:Person" });
   * @example
   * // To remove multiple triples from an element
   * $('#main > p > a').removeRdfa([{ property: "foaf:depicts" }, { property: "dc:creator" }]);
   */
  $.fn.removeRdfa = function (triple) {
    $(this).each(function () {
      removeRDFa.call($(this), triple);
    });
    return this;
  };

  $.rdf.gleaners.push(gleaner);

})(jQuery);
// namespace for script variables
var OntoWiki = {};

// how fast should we fade, slide, ...
var effectTime = 250;

// integer value of the dragNdrop z-index and context-menu z-index
var dragZIndex = 1000;
var menuZIndex = 1000;

// number of chars entered before autocompleting starts
var autoCompleteMinChars = 3;

// time to wait before autocompleting (ms)
var autoCompleteDelay = 500;

// The id counter is used to create autoids
idCounter = 1;

// This array is used to temp. store href attributes
tempHrefs = new Array();

/*
 * core css assignments
 */
$(document).ready(function() {
    // keyboard shortcuts
    $(document).keydown(function(e) {
        if (/view\/?|resource\/properties\/?/gi.test(window.document.baseURI)) {
            if (e.shiftKey && e.altKey) {
                e.preventDefault();
                switch(e.which) {
                    //e - 101 - edit E - 69
                    case 69 : $('.edit-enable').trigger('click'); break;
                    //a - 97 - add property - A - 65
                    case 65 : $('.property-add').trigger('click'); break;
                    //s - 115 - save S - 83
                    case 83 : if ($('.edit-enable').hasClass('active')) { 
                                   $('.edit.save').trigger('click'); 
                               }; 
                               break;
                    //c  - 99 - cancel C - 67
                    case 67 : if ($('.edit-enable').hasClass('active')) { 
                                  $('.edit.cancel').trigger('click'); 
                              }; 
                              break;
                    //l - 108 - clone L - 76
                    case 76 : $('.clone-resource').trigger('click'); break;
                }
            }
        }
    });
    // Object.keys support in older environments that do not natively support it
    if (!Object.keys) {
        Object.keys = (function () {
            var hasOwnProperty = Object.prototype.hasOwnProperty,
                hasDontEnumBug = !({toString: null}).propertyIsEnumerable('toString'),
                dontEnums = [
                  'toString',
                  'toLocaleString',
                  'valueOf',
                  'hasOwnProperty',
                  'isPrototypeOf',
                  'propertyIsEnumerable',
                  'constructor'
                ],
                dontEnumsLength = dontEnums.length

            return function (obj) {
                if (typeof obj !== 'object' && typeof obj !== 'function' || obj === null) throw new TypeError('Object.keys called on non-object')

                var result = []

                for (var prop in obj) {
                    if (hasOwnProperty.call(obj, prop)) result.push(prop)
                }

                if (hasDontEnumBug) {
                    for (var i=0; i < dontEnumsLength; i++) {
                        if (hasOwnProperty.call(obj, dontEnums[i])) result.push(dontEnums[i])
                    }
                }
              return result
            }
        })()
    };

    // the body gets a new class to indicate that javascript is turned on
    $('body').removeClass('javascript-off').addClass('javascript-on');

    // the body gets the contextmenu clone container
    $('body').append('<div class="contextmenu-enhanced"></div>');

    // every click fadeout (and remove) all contextmenus
    // every click un-marks all marked elements
    $('html').click(function(){
        $('.contextmenu-enhanced .contextmenu').fadeOut(effectTime, function(){$(this).remove();})
        $('.marked').removeClass('marked');
    });
    
    // add section resizer
    $('.section-sidewindows').append('<span class="resizer-horizontal"></span>');
    
    // give it a nice (non-standard) cursor
    if ($.browser.safari) {
        $('.resizer-horizontal').css('cursor', 'col-resize');
    } else if ($.browser.mozilla) {
        $('.resizer-horizontal').css('cursor', 'ew-resize');
    }
    
    // make resizer draggable
    // draggables need an explicit (inline) position
    $('.section-sidewindows .resizer-horizontal')
        .css('position', 'absolute')
        .draggable({
            axis: 'x', 
            zIndex: dragZIndex,  
            cursor: 'move', 
            start: function(event, ui) {
                $('.section-sidewindows .resizer-horizontal').addClass('dragging');
            }, 
            stop: function(event, ui) {
                var resizerWidth = $('.section-sidewindows .resizer-horizontal').width();
                var sectionRatioPercent = Math.round((((event.originalEvent.pageX) / $(document).width())) * 1000) * 0.1;
                setSectionRatio(sectionRatioPercent);
                sessionStore('sectionRation', sectionRatioPercent, {encode: true});
                $('.window div.cmDiv').adjustClickMenu();
                // jQuery UI bug in Safari
                $('.section-sidewindows').css('position', 'absolute');
                $('.section-sidewindows .resizer-horizontal').removeClass('dragging');
            }});
    
    // resize separator when all ajax crap is loaded
    window.setTimeout(function () {
        $('.section-sidewindows .resizer-horizontal').height(
            Math.max(
                $(document).height(),
                $(window).height(),
                /* for Opera: */
                document.documentElement.clientHeight
            ) + 'px');
    }, 750);

    if (typeof sectionRatio != 'undefined') {
        setSectionRatio(sectionRatio);
    }

    /* list selection */
    /* bind to selection events */
    $('body').bind(
        'ontowiki.selection.changed',
        function(event, data)
        {
            // select event
            $('.list-selected').removeClass('list-selected'); // should not add class in the click function
            $('table.resource-list > tbody > tr').each(
                function(key)
                {
                    var pos = $.inArray($(this).children('td').children('a').attr('about'), data);
                    if (pos >= 0) {
                        $(this).addClass('list-selected');
                    }
                }
            );
        }
    );

    /* trigger selection events */
    $('table.resource-list > tbody > tr').live('click', function(e) {
        var selectee     = $(this);
        var selectionURI = $(this).attr('about') | $(this).children('td').children('a').attr('about');

        // return if we have no URI (e.g. a Literal list)
        if (typeof selectionURI == 'undefined') {
            return false;
        }

        // return true if user clicked on a link (so the link is fired)
        if ( $(e.target).is('a') ) {
            return true;
        }

        // create array for all selected resources
        if (typeof OntoWiki.selectedResources == 'undefined') {
            OntoWiki.selectedResources = [];
        }

        if (!selectee.hasClass('list-selected')) { // select a resource
            // TODO: check for macos UI compability
            if (e.ctrlKey) {
                // ctrl+click for select multiple resources

            } else if (e.shiftKey) {
                // shift+click for select multiple resources in a range
                // not implemented yet
            } else {
                // normal click on unselected means deselect all and select this one
                // purge the container array
                OntoWiki.selectedResources = [];
            }

            // add this resource
            OntoWiki.selectedResources.push(selectionURI);
            // event for most recent selection
            $('body').trigger('ontowiki.resource.selected', [selectionURI]);
        } else { // deselect a resource
            // TODO: check for macos UI compability
            if (e.ctrlKey) {
                // ctrl+click on selected means deselect this one
                var pos = $.inArray(selectionURI, OntoWiki.selectedResources);
                OntoWiki.selectedResources.splice(pos, 1);
            } else if (e.shiftKey) {
                // shift+click for select multiple resources in a range
                // not implemented yet
            } else {
                // normal click on selected means deselect all
                // purge the container array
                OntoWiki.selectedResources = [];
            }

            // event for most recent unselection
            $('body').trigger('ontowiki.resource.unselected', [selectionURI]);
        }

        // event for all selected
        $('body').trigger('ontowiki.selection.changed', [OntoWiki.selectedResources]);
    });
    /* END list selection */

    $('body').bind('ontowiki.resource-list.reloaded', function() {
        // synchronize selection with list style
        $('.resource-list tr').each(function() {
            var resourceURI = $(this).find('*[about]').eq(0).attr('about');
            if ($.inArray(resourceURI, OntoWiki.selectedResources) > -1) {
                $(this).addClass('list-selected');
            }
        })
    })
    
    /* end: list selection */
    
    // inner labels
    $('input.inner-label').innerLabel().blur();
    
    // prefix preserving inputs
    $('input.prefix-value').prefixValue();
    
    $('.editable').makeEditable();
    
    // autosubmit
    $('a.submit').click(function() {
        // submit all forms inside this submit button's parent window
        var formName = $(this).attr('id');
        var formSpec = formName ? '[name=' + formName + ']' : '';
        
        $(this).parents('.window').eq(0).find('form' + formSpec).each(function() {
            if ($(this).hasClass('ajaxForm')) {
                // submit asynchronously
                var actionUrl = $(this).attr('action');
                var method    = $(this).attr('method');
                var data      = $(this).serialize();
                
                if ($(this).hasClass('reloadOnSuccess')) {
                    var mainContent = $(this).parents('.content.has-innerwindows').eq(0).children('.innercontent');
                    var onSuccess = function() {
                        mainContent.load(document.URL);
                    }
                }
                // alert(data);
                if (method == 'post') {
                    $.post(actionUrl, data, onSuccess);
                } else {
                    $.get(actionUrl, data, onSuccess);
                }
                
                this.reset();
            } else {
                // submit normally
                this.submit();
            }
        })
    });
    
    /*
     *  simulate Safari behaviour for other browsers
     *  on return/enter, submit the form
     */
    $('.submitOnEnter').keypress(function(event) {
        // return pressed
        if (event.target.tagName.toLowerCase() != 'textarea' && event.which == 13) {
            $(this).parents('form').submit();
        }
    });
    
    /*
     *  on press enter, this type of textbox looses focus and gives it to the next element of the same type
     */
    $('.focusNextOnEnter').keypress(function(event) {
        // return pressed
        if (event.target.tagName.toLowerCase() != 'textarea' && event.which == 13) {
            var me = $(this)
            var meType = me.get(0).tagName.toLowerCase()
            var next = me.next(); //next element
            if(next.get(0).tagName.toLowerCase() == meType){
                next.focus();
            } else {
                // if thats not of the same type, go to "parent and "cousin""
                var next2 = me.parent().next().find('>'+meType+':first')
                if (next2.length != 0){
                    next2.focus();
                } 
            } 
        }
    });
    
    // autosubmit
    $('a.reset').click(function() {
        // reset all forms inside this submit button's parent window
        $(this).parents('.window').find('form').each(function() {
            document.forms[$(this).attr('name')].reset();
        })
    });

    // init new resource based on type
    $('.init-resource').click(function(event) {
        // parse .resource-list and query for all types
        if ($('.resource-list').length != 0) {
            var types = $('.resource-list').rdf()
                                           .where('?type a rdfs:Class')
                                           .where('?type rdfs:label ?value')
                                           .dump();

            if (Object.keys(types).length == 1) {
                createInstanceFromClassURI(Object.keys(types)[0]);
            } else {
                showAddInstanceMenu(event, types);
            }
        } else {
            // workaround to create instance when number of instances of a class is null
            // The selected class should be hardcoded by ontowiki in the header as 
            // javascript variable.
            createInstanceFromClassURI($('#filterbox a').attr('about'));
        }
        
    });

    $('.edit.save').click(function() {
        RDFauthor.commit();
    });
    
    $('.edit.cancel').click(function() {
        $(body).data('editingMode', false);
        // reload page
        window.location.href = window.location.href;
        RDFauthor.cancel();
        // var mainInnerContent = $('.window .content.has-innerwindows').eq(0).find('.innercontent');
        // mainInnerContent.load(document.URL);
        // $('.edit-enable').click();
    });
    
//    $('.icon-edit').click(function() {return editProperty(this)});
    
    // disable inline-editing for not readable models
    if (typeof selectedGraph !== 'undefined' && !selectedGraph.editable) {
        $('.icon-edit').closest('a').remove();
    }
    
    // edit mode
    $('.edit-enable').click(function() {
        $(body).data('editingMode', true);
        var button = this;
        if ($(button).hasClass('active')) {
            RDFauthor.cancel();
            $('.edit').each(function() {
                $(this).fadeOut(effectTime);
            });
            $(button).removeClass('active');
            window.location.href = window.location.href;
        } else {
            if(typeof(RDFauthor) !== 'undefined') {
                RDFauthor.cancel();
            }
            loadRDFauthor(function () {
                RDFauthor.setOptions({
                    onSubmitSuccess: function () {
                        // var mainInnerContent = $('.window .content.has-innerwindows').eq(0).find('.innercontent');
                        // mainInnerContent.load(document.URL);

                        // tell RDFauthor that page content has changed
                        // RDFauthor.invalidatePage();

                        $('.edit').each(function() {
                            $(this).fadeOut(effectTime);
                        });
                        $('.edit-enable').removeClass('active');
                        
                        // HACK: reload whole page after 1000 ms
                        window.setTimeout(function () {
                            window.location.href = window.location.href;
                        }, 500);
                    }, 
                    onCancel: function () {
                        $('.edit').each(function() {
                            $(this).fadeOut(effectTime);
                        });
                        $('.edit-enable').removeClass('active');
                    }, 
                    saveButtonTitle: 'Save Changes', 
                    cancelButtonTitle: 'Cancel', 
                    title: $('.section-mainwindows .window').eq(0).children('.title').eq(0).text(),
                    loadOwStylesheet: false,
                    viewOptions: {
                        // no statements needs popover
                        type: $('.section-mainwindows table.Resource').length ? RDFAUTHOR_VIEW_MODE : 'popover', 
                        container: function (statement) {
                            var element = RDFauthor.elementForStatement(statement);
                            var parent  = $(element).closest('div');
                            
                            if (!parent.hasClass('ontowiki-processed')) {
                                parent.children().each(function () {
                                    $(this).hide();
                                });
                                parent.addClass('ontowiki-processed');
                            }
                            
                            return parent.get(0);
                        }
                    }
                });
                
                RDFauthor.start();
                
                $('.edit').each(function() {
                    $(this).fadeIn(effectTime, function() {
                        $(button).addClass('active');
                    });
                });
            });
        }
    });
    
    $('.clone-resource').click(function() {
        loadRDFauthor(function () {
            var serviceURI = urlBase + 'service/rdfauthorinit';
            var prototypeResource = selectedResource.URI;
            RDFauthor.reset();

            $.getJSON(serviceURI, {
               mode: 'clone',
               uri: prototypeResource
            }, function(data) {
                // get default resource uri for subjects in added statements (issue 673)
                // grab first object key
                for (var subjectUri in data) {break;};
                
                populateRDFauthor(data, true, subjectUri, selectedGraph.URI);
                
                RDFauthor.setOptions({
                    saveButtonTitle: 'Create Resource',
                    cancelButtonTitle: 'Cancel',
                    title: 'Create New Resource by Cloning ' + selectedResource.title,  
                    autoParse: false, 
                    showPropertyButton: true,
                    loadOwStylesheet: false,
                    onSubmitSuccess: function (responseData) {
                        var newLocation;
                        if (responseData && responseData.changed) {
                            newLocation = resourceURL(responseData.changed);
                        } else {
                            newLocation = window.location.href;
                        }
                        // HACK: reload whole page after 500 ms
                        window.setTimeout(function () {
                            window.location.href = newLocation;
                        }, 500);
                    }
                });
                
                RDFauthor.start();
            });
        });
    })
    
    // add property
    $('.property-add').click(function() {
        $(body).data('editingMode', true);
        if(typeof(RDFauthor) === 'undefined') {
            loadRDFauthor(function () {
                RDFauthor.setOptions({
                    onSubmitSuccess: function () {
                        // var mainInnerContent = $('.window .content.has-innerwindows').eq(0).find('.innercontent');
                        // mainInnerContent.load(document.URL);

                        // tell RDFauthor that page content has changed
                        // RDFauthor.invalidatePage();

                        $('.edit').each(function() {
                            $(this).fadeOut(effectTime);
                        });
                        $('.edit-enable').removeClass('active');
                        
                        // HACK: reload whole page after 1000 ms
                        window.setTimeout(function () {
                            window.location.href = window.location.href;
                        }, 500);
                    }, 
                    onCancel: function () {
                        $('.edit').each(function() {
                            $(this).fadeOut(effectTime);
                        });
                        $('.edit-enable').removeClass('active');
                    }, 
                    saveButtonTitle: 'Save Changes', 
                    cancelButtonTitle: 'Cancel',
                    loadOwStylesheet: false,
                    title: $('.section-mainwindows .window').eq(0).children('.title').eq(0).text(), 
                    viewOptions: {
                        // no statements needs popover
                        type: $('.section-mainwindows table.Resource').length ? RDFAUTHOR_VIEW_MODE : 'popover', 
                        container: function (statement) {
                            var element = RDFauthor.elementForStatement(statement);
                            var parent  = $(element).closest('div');
                            
                            if (!parent.hasClass('ontowiki-processed')) {
                                parent.children().each(function () {
                                    $(this).hide();
                                });
                                parent.addClass('ontowiki-processed');
                            }
                            
                            return parent.get(0);
                        }
                    }
                });
                //workaround: don't load widget
                RDFauthor.start($('head'));
                $('.edit-enable').addClass('active');
                setTimeout("addProperty()",500);
            });
        } else {
            addProperty();
        }
        
    });
    
    $('.tabs').children('li').children('a').click(function() {
        var url = $(this).attr('href');
        
        $(this).parents('.tabs').children('li').removeClass('active');
        $(this).parent('li').addClass('active');
                
        if (url.match(/#/)) {
            var wnd = $(this).parents('.window').eq(0);
            wnd.children('div').children('.content').removeClass('active-tab-content');
            wnd.children('div').children('.content' + url).addClass('active-tab-content');
            return false;
        } else {
            return true;
        }
    });
    
    // box display/hide    
    // $('.toggle-module-display').click(function() {
    //     var module = $('.window#' + $(this).attr('id').replace('toggle-', ''));
    //     var menuEntry = $(this);
    //     if (module.length) {
    //         if (module.hasClass('is-disabled')) {
    //             module.removeClass('is-disabled');
    //             module.fadeIn(effectTime, function() {
    //                 menuEntry.text(menuEntry.text().replace('Show', 'Hide'));
    //             })
    //         } else {
    //             module.fadeOut(effectTime, function() {
    //                 module.addClass('is-disabled');
    //                 menuEntry.text(menuEntry.text().replace('Hide', 'Show'));
    //             });
    //         }
    //     }
    // })
    
    
    // make sidebar windows sortable
/*    if ($('.section-sidewindows .window').length) {
        $('.section-sidewindows .window .title').css('cursor', 'move');
        $('.section-sidewindows').sortable({
            items: '.window', 
            handle: '.title', 
            // containment: 'parent', 
            opacity: 0.8, 
            axis: 'y', 
            cursor: 'move', 
            revert: true, 
            start: function(event, ui) {
                ui.helper.css('width', $('.section-sidewindows .window').eq(0).width() + 'px');
                ui.helper.css('margin-left', '0');
            }, 
            update: function() {
                var moduleOrder = $('.section-sidewindows').sortable('serialize', {
                    expression: '(.*)', 
                    key: 'value'
                });
                sessionStore('moduleOrder', moduleOrder, {encode: false, namespace: 'Module_Registry'});
            }
        });
        // draggables need an explicit (inline) position
        $('.section-sidewindows').css('position', 'absolute');
    }
*/
    
    // make tabs sortable
    // if ($('#tabs').children().length) {
    //     $('#tabs').sortable({
    //         axis: 'x', 
    //         // containment: 'parent', 
    //         opacity: 0.8, 
    //         revert: true, 
    //         update: function() {
    //             var tabOrder = $('#tabs').sortable('serialize', {
    //                 expression: '(.*)', 
    //                 key: 'value'
    //             });
    //             sessionStore('tabOrder', tabOrder, {encode: false, namespace: 'ONTOWIKI_NAVIGATION'});
    //         }
    //     });
    // }
    
    // inline widgets
    // $('.inline-edit-local').live('click', function() {
    //     RDFauthor.startInline($(this).closest('.editable').get(0));
    // });
    
    $('.hidden').hide();
    
    //-------------------------------------------------------------------------
    //---- liveQuery triggers
    //-------------------------------------------------------------------------
    
    // expandables
    $('.expandable').livequery(function() {
        $(this).expandable();
    });

    // create showResourceMenu toogle where wanted and applicable
    $('a.hasMenu[about]').livequery(function() {
        $(this).createResourceMenuToggle();
    });
    $('a.hasMenu[resource]').livequery(function() {
        $(this).createResourceMenuToggle();
    });

    $('.init-resource').livequery(function() {
        $(this).createResourceMenuToggle();
    });

    // All RDFa elements with @about or @resource attribute are resources
    $('*[about]').livequery(function() {
        $(this).addClass('Resource');
    });
    $('*[resource]').livequery(function() {
        $(this).addClass('Resource');
    });
    
    
    var liveSearchMinChars = 3;
    var liveSearchTimeout  = 250; // ms
    var count = 0;
    
    // live-search
    $('input.live-search').livequery('keyup', function() {
        var localCount = ++count;
        var searchInput = $(this);
        
        window.setTimeout(function() {
            // no more input, so do something
            if (count == localCount) {
                if (($(searchInput).val().length >= liveSearchMinChars)) {
                    $(searchInput).parents('.content').children('ul').hide();
                    if ($(searchInput).parents('.content').children('.messagebox').length < 1) {
                        $(searchInput).parents('.content').append(
                            '<div style="display:none" class="messagebox info">Not implemented yet.</div>');
                        $(searchInput).parents('.content').children('.messagebox').fadeIn(effectTime);
                    }
                } else {
                    // load normal hierarchy
                    $(searchInput).parents('.content').children('ul').fadeIn(effectTime);
                    $(searchInput).parents('.content').children('.messagebox').remove();
                }                
            }
        }, liveSearchTimeout);
    });
    
    /* RESOURCE CONTEXT MENUS */
    $('.has-contextmenus-block .Resource').livequery(function() {
        $(this).append('<span class="button"></span>');
    });
    
    $('.has-contextmenus-block .Resource span.button').livequery(function() {
        $(this).mouseover(function() {
            hideHref($(this).parent());
            $('.contextmenu-enhanced .contextmenu').remove(); // remove all other menus
        })
        .click(function(event) {
            showResourceMenu(event);
        }).mouseout(function() {
            showHref($(this).parent())
        });
    });
    
    var loadChildren = function(li) {
        var ul;
        var a   = $(li).children('.hierarchy-toggle');
        var uri = $(li).children('.has-children').attr('about');
        
        var toggleDisplay = function(ul) {
            if (ul.css('display') != 'none') {
                ul.slideUp(effectTime, function() {
                    a.removeClass('open');
                    sessionStore('hierarchyOpen', 'value=' + encodeURIComponent(uri), {method: 'unset', withValue: true});
                });
            } else {
                ul.slideDown(effectTime, function() {
                    a.addClass('open');
                    sessionStore('hierarchyOpen', 'value=' + encodeURIComponent(uri), {method: 'push', withValue: true});
                });
            }
        }
        
        var serviceUrl = urlBase + 'service/hierarchy?entry=' + encodeURIComponent(uri);
        $.get(serviceUrl, function(data) {
            ul = $(data);
            ul.css('display', 'none');
            $(li).append(ul);
            toggleDisplay(ul);
        })
    }
    
    $('ul .hierarchy .has-children').livequery(function() {
        // is open and should have children but has none
        if ($(this).prev('.hierarchy-toggle').hasClass('open') && $(this).parent().children('ul').length < 1) {
            loadChildren($(this).parent());
        }
    });
    
    $('.hierarchy-toggle').livequery('click', function(event) {
        var ul;
        var a   = $(this);
        var uri = a.next().attr('about');
        
        var toggleDisplay = function(ul) {
            if (ul.css('display') != 'none') {
                ul.slideUp(effectTime, function() {
                    a.removeClass('open');
                    sessionStore('hierarchyOpen', 'value=' + encodeURIComponent(uri), {method: 'unset', withValue: true});
                });
            } else {
                ul.slideDown(effectTime, function() {
                    a.addClass('open');
                    sessionStore('hierarchyOpen', 'value=' + encodeURIComponent(uri), {method: 'push', withValue: true});
                });
            }
        }
        
        if ($(this).parent('li').children('ul').length < 1) {
            // TODO: Ajax
            var serviceUrl = urlBase + 'service/hierarchy?entry=' + encodeURIComponent(uri);
            $.get(serviceUrl, function(data) {
                ul = $(data);
                ul.css('display', 'none');
                a.parent('li').append(ul);
                toggleDisplay(ul);
            })
        } else {
            ul = a.parent('li').children('ul');
            toggleDisplay(ul);
        }
        
        event.stopPropagation();
    })
    
    $('tbody a.toggle').live('click', function() {
        $(this).closest('tbody').toggleClass('closed');
    })
    
    // site is ready, processing is finished
    $('body').removeClass('is-processing');

    // enhance every window with buttons, menu and resizer
    // this must be done at the end of the onready block (because we generate the menu automatically)
    $('.window').enhanceWindow();
    
    // adjust neede space for clickmenu
    $('.window div.cmDiv').adjustClickMenu();
    
}) // $(document).ready


/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Support functions
 */

function toggleExpansion(event) {
    var target = $(event.target);
    var resourceUri = target.next().attr('about') ? target.next().attr('about') : target.next().attr('resource');
    
    if (target.hasClass('expand')) {
        target.removeClass('expand').addClass('collapse');
        
        if (target.parent().children('.expansion').length) {
            target.parent().children('.expansion').slideDown(effectTime);
        } else {
            var expansion = $('<div class="expansion" style="font-size:90%"></div>');
            target.parent().append(expansion);
            var url    = urlBase + 'view/';
            var params = 'r=' + encodeURIComponent(resourceUri);
            $.ajax({
                url:      url, 
                data:     params, 
                dataType: 'html', 
                success:  function(content) {
                    expansion.hide();
                    expansion.append(content);
                    expansion.slideDown(effectTime);
                    /*map.updateInfoWindow([
                        new GInfoWindowTab('', target.parent().html())
                    ])*/ // only a javascript error, i think it was neccessery for the old ontowiki
                }
            });
        }
    } else {
        target.removeClass('collapse').addClass('expand');
        target.parent().find('.expansion').slideUp(effectTime);
    }
}

function expand(event) {
    target = $(event.target);
    resourceURI = target.next().attr('about');
    encodedResourceURI = encodeURIComponent(resourceURI);
    resource = target.next();

    if (target.is('.expand')) {
        target.removeClass('expand').addClass('deexpand');
        // target.next().after('<div class="is-processing expanded-content"></div>');
        url = urlBase + 'resource/properties/';
        params = 'r=' + encodedResourceURI;
        $.ajax({
            url: url,
            data: params,
            dataType: 'html',
            // success: function(msg){alert( 'Data Saved: ' + msg );}
            success: function(content) {
                map.updateInfoWindow([new GInfoWindowTab('', target.parent().html() + '<div style="font-size:90%">' + content + '</div>')]);
                // resource.next().html(content);
                // resource.next().removeClass('is-processing');
                }
        });
    }
    else if (target.is('.deexpand')) {
        target.removeClass('deexpand').addClass('expand');
        target.next().next().remove();
    }
}

/**
 * Changes the Ratio between main and side-section
 */
function setSectionRatio(x) {
    $('div.section-sidewindows').css('width', x + '%');
    $('div.section-mainwindows').css('width', (100 - x) + '%');
    $('div.section-mainwindows').css('margin-left', x + '%');
}

function showWindowMenu(event) {
    // remove all other menus
    $('.contextmenu-enhanced .contextmenu').remove();
    
    menuX  = event.pageX - 11;
    menuY  = event.pageY - 11;
    menuId = 'windowmenu-' + menuX + '-' + menuY;

    // create the plain menu with correct style and position
    $('.contextmenu-enhanced').append('<div class="contextmenu is-processing" id="' + menuId + '"></div>');
    $('#' + menuId)
        .attr({style: 'z-index: ' + menuZIndex + '; top: ' + menuY + 'px; left: ' + menuX + 'px;'})
        .click(function(event) {event.stopPropagation();});

    $('#' + menuId).fadeIn();

    // setting url parameters
    var urlParams = {};
    urlParams.module = $(event.target).parents('.window').eq(0).attr('id');

    // load menu with specific options from service
    $.ajax({
        type: "GET",
        url: urlBase + 'service/menu/',
        data: urlParams,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("error occured - details at firebug-console");
            console.log("menu service error\nfailure message:\n" + textStatus);
            $('#' + menuId).fadeOut();
        },
        success: function(data, textStatus) {
            try {

                menuData = $.evalJSON(data);

                var menuStr = '';
                var tempStr = '';
                var href    = '';

                // construct menu content
                for (var key in menuData) {
                    if ( menuData[key] == '_---_' ) {
                        menuStr += '</ul><hr/><ul>';
                    } else {
                        if ( typeof(menuData[key]) == 'string' ) {
                            tempStr = '<a href="' + menuData[key] + '">' + key + '</a>';
                        } else {
                            tempStr = '<a ';
                            for (var attr in menuData[key]) {
                                tempStr += attr + '="' + menuData[key][attr] + '" ';
                            }
                            tempStr += '>' + key + '</a>';
                        }
                        menuStr += '<li>' + tempStr + '</li>';
                    }
                }

                // append menu string with surrounding list
                $('#' + menuId).append('<ul>' + menuStr + '</ul>');

                // remove is-processing
                $('#' + menuId).toggleClass('is-processing');

            } catch (e) {
                alert("error occured - details at firebug-console");
                console.log("menu service error\nmenu service replied:\n" + data);
                $('#' + menuId).fadeOut();
            }

        }
    });

    // prevent href trigger
    event.stopPropagation();

}

/*
 * Save a key-value pair via ajax
 */
function sessionStore(name, value, options) {
    var defaultOptions = {
        encode:    false, 
        namespace: _OWSESSION, 
        callback:  null, 
        method:    'set', 
        url:       urlBase + 'service/session/', 
        withValue: false
    };
    var config = $.extend(defaultOptions, options);

    // TODO
    if (!config.encode) {
        if (!config.withValue) {
            config.url += '?name=' + name + '&value=' + value + '&method=' + config.method + '&namespace=' + config.namespace;
        } else {
            config.url += '?name=' + name + '&' + value + '&method=' + config.method + '&namespace=' + config.namespace;
        }

        $.get(config.url, config.callback);
    } else {
        var params = {name: name, value: value, namespace: config.namespace};
        $.get(config.url, params, config.callback);
    }
}

/*
 * This function sets an automatic id attribute if no id exists
 * parameter: el -> jquery element
 */
function setAutoId(element) {
    if (!element.attr('id')) {
        element.attr('id', 'autoid' + idCounter++);
    }
}

/*
 * hide a href by putting this attribute into an array
 * parameter: el -> jquery element
 */
function hideHref(element) {
    setAutoId(element);
    
    if (element.attr('href')) {
        tempHrefs[element.attr('id')] = element.attr('href');
        element.removeAttr('href');
    }
}

function showHref(element) {
    if (tempHrefs[element.attr('id')]) {
        element.attr('href', tempHrefs[element.attr('id')]);
    }
}

function serializeArray(array, key)
{
    if (typeof key == 'undefined') {
        key = 'value';
    }
    
    var serialization = '';
    
    if (array.length) {
        serialization += key + '[]=' + encodeURIComponent(array[0]);
        
        for (var i = 1; i < array.length; ++i) {
            serialization += '&' + key + '[]=' + encodeURIComponent(array[i]);
        }
    } else {
        serialization += key + '=';
    }
    
    return serialization;
}

/*
 * remove all other menus
 */
function removeResourceMenus() {
    $('.contextmenu-enhanced .contextmenu').remove();
}

function showAddInstanceMenu(event, menuData) {
    // remove all other menus
    removeResourceMenus();

    var pos = $('.init-resource').offset();
    menuX = pos.left - $('.init-resource').innerWidth() + 4;
    menuY = pos.top + $('.init-resource').outerHeight();
    menuId = 'windowmenu-' + menuX.toFixed() + '-' + menuY.toFixed();

    // create the plain menu with correct style and position
    $('.contextmenu-enhanced').append('<div class="contextmenu is-processing" id="' + menuId + '"></div>');
    $('#' + menuId)
        .css({ 
          'z-index': menuZIndex,
          'top': menuY + 'px',
          'left': menuX + 'px'
        })
        .click(function(event) {event.stopPropagation();})
        .fadeIn();

    var tempMenu = "";
    for (var key in menuData) {
        var label = menuData[key]['http://www.w3.org/2000/01/rdf-schema#label'][0].value;
        tempMenu += '<li><a href="javascript:createInstanceFromClassURI(\'' + key + '\');">' + label + '</a></li>'
    }
    // append menu
    // console.log(tempMenu);
    $('#' + menuId).append('<ul>' + tempMenu + '</ul>');
    // remove is-processing
    $('#' + menuId).toggleClass('is-processing');
    // repositioning
    menuX = pos.left - $('#' + menuId).innerWidth() + $('.init-resource').outerWidth();
    menuY = pos.top + $('.init-resource').outerHeight();
    
    // set new position
    $('#' + menuId).css({ top: menuY + 'px', left: menuX + 'px'});

    // remove is-processing
    $('#' + menuId).removeClass("is-processing");
    // prevent href trigger
    event.stopPropagation();

}

function showResourceMenu(event, json) {
    // remove all other menus
    removeResourceMenus();
    
    menuX  = event.pageX - 30;
    menuY  = event.pageY - 20;
    menuId = 'windowmenu-' + menuX + '-' + menuY;
    
    // create the plain menu with correct style and position
    $('.contextmenu-enhanced').append('<div class="contextmenu is-processing" id="' + menuId + '"></div>');
    $('#' + menuId)
        .attr({style: 'z-index: ' + menuZIndex + '; top: ' + menuY + 'px; left: ' + menuX + 'px;'})
        .click(function(event) {event.stopPropagation();});

    $('#' + menuId).fadeIn();
    
    parentHref = tempHrefs[$(event.target).parent().attr('id')];
    
    function onJSON(menuData, textStatus) {
        try {
            //console.log(menuData)
            var menuStr = '';
            var tempStr = '';
            var href    = '';

            // construct menu content
            for (var key in menuData) {
                href = menuData[key];
                if ( menuData[key] == '_---_' ) {
                    menuStr += '</ul><hr/><ul>';
                } else {
                    if (typeof(href) == 'object') {
                        tempStr = '<a class="' + href['class'] + '" about="' + href['about'] + '">' + key + '</a>';
                    } else {
                        tempStr = '<a href="' + href + '">' + key + '</a>';
                        if (href == parentHref) {
                            tempStr = '<strong>' + tempStr + '</strong>';
                        }
                    }
                    menuStr += '<li>' + tempStr + '</li>';
                }
            }

            // append menu string with surrounding list
            $('#' + menuId).append('<ul>' + menuStr + '</ul>');

            // remove is-processing
            $('#' + menuId).toggleClass('is-processing');

        } catch (e) {
            alert("error occured - details at firebug-console");
            console.log("menu service error\nmenu service replied:\n" + data);
            $('#' + menuId).fadeOut();
        }
    }
    
    if(json == undefined){
        // URI of the resource clicked (used attribute can be about and resource)
        if ( typeof $(event.target).parent().attr('about') != 'undefined' ) {
            resourceUri = $(event.target).parent().attr('about');
        } else if ( typeof $(event.target).parent().attr('resource') != 'undefined' ) {
            resourceUri = $(event.target).parent().attr('resource');
        } else {
            // no usable resource uri, so we exit here
            return false;
        }

        encodedResourceUri = encodeURIComponent(resourceUri);
        resource = $(event.target).parent();

        

        var urlParams = {};
        urlParams.resource = resourceUri;


        // load menu with specific options from service
        $.ajax({
            type: "GET",
            url: urlBase + 'service/menu/',
            data: urlParams,
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert("error occured - details at firebug-console");
                console.log("menu service error\nfailure message:\n" + textStatus);
                $('#' + menuId).fadeOut();
            },
            success: function(data, textStatus){onJSON($.evalJSON(data), textStatus);}
        });
    } else {
        onJSON(json)
    }

    // prevent href trigger
    event.stopPropagation();
}

/**
 * Loads RDFauthor if necessary and executes callback afterwards.
 */
function loadRDFauthor(callback) {
    var loaderURI = RDFAUTHOR_BASE + 'src/rdfauthor.js';
    
    if ($('head').children('script[src="' + loaderURI + '"]').length > 0) {
        callback();
    } else {
        RDFAUTHOR_READY_CALLBACK = callback;
        // load script
        var s = document.createElement('script');
        s.type = 'text/javascript';
        s.src = loaderURI;
        document.getElementsByTagName('head')[0].appendChild(s);
    }
}

function populateRDFauthor(data, protect, resource, graph, workingmode) {
    protect  = arguments.length >= 2 ? protect : true;
    resource = arguments.length >= 3 ? resource : null;
    graph    = arguments.length >= 4 ? graph : null;
    
    for (var currentSubject in data) {
        for (var currentProperty in data[currentSubject]) {
            var objects = data[currentSubject][currentProperty];

            for (var i = 0; i < objects.length; i++) {
                var objSpec = objects[i];

                if ( objSpec.type == 'uri' ) { 
                    var value = '<' + objSpec.value + '>'; 
                } else if ( objSpec.type == 'bnode' ) { 
                    var value = '_:' + objSpec.value;
                } else {
                    // IE fix, object keys with empty strings are removed
                    var value = objSpec.value ? objSpec.value : ""; 
                }

                var newObjectSpec = {
                    value : value,
                    type: String(objSpec.type).replace('typed-', '')
                }

                if (objSpec.value) {
                    if (objSpec.type == 'typed-literal') {
                        newObjectSpec.options = {
                            datatype: objSpec.datatype
                        }
                    } else if (objSpec.lang) {
                        newObjectSpec.options = {
                            lang: objSpec.lang
                        }
                    }
                }

                var stmt = new Statement({
                    subject: '<' + currentSubject + '>', 
                    predicate: '<' + currentProperty + '>', 
                    object: newObjectSpec
                }, {
                    graph: graph, 
                    title: objSpec.title, 
                    "protected": protect ? true : false, 
                    hidden: objSpec.hidden ? objSpec.hidden : false
                });

                if (workingmode == 'class') {
                    // remove all values except for type
                    if ( !/type/gi.test(stmt._predicateLabel) ) {
                        stmt._object.value = "";
                    }
                }

                RDFauthor.addStatement(stmt);
            }
        }
    }
}

/*
 * get the rdfa init description from the service in class mode and start the
 * RDFauthor window
 * dataCallback is called right after the json request to manipulate the requested data
 */
function createInstanceFromClassURI(type, dataCallback) {
    var serviceUri = urlBase + 'service/rdfauthorinit';

    // check if an resource is in editing mode
    if($(body).data('editingMode')) {
        RDFauthor.cancel();
        RDFauthor.reset();
    }

    // remove resource menus
    removeResourceMenus();

    loadRDFauthor(function() {
        $.getJSON(serviceUri, {
            mode: 'class',
            uri: type
        }, function(data) {
            // pass data through callback
            if (typeof dataCallback == 'function') {
                data = dataCallback(data);
            }
            // get default resource uri for subjects in added statements (issue 673)
            // grab first object key
            for (var subjectUri in data) {break;};
            // add statements to RDFauthor
            populateRDFauthor(data, true, subjectUri, selectedGraph.URI, 'class');
            RDFauthor.setOptions({
                saveButtonTitle: 'Create Resource',
                cancelButtonTitle: 'Cancel',
                title: 'Create New Instance of ' + type,  
                autoParse: false, 
                showPropertyButton: true,
                loadOwStylesheet: false,
                onSubmitSuccess: function (responseData) {
                    var newLocation;
                    if (responseData && responseData.changed) {
                        newLocation = resourceURL(responseData.changed);
                    } else {
                        newLocation = window.location.href;
                    }
                    // HACK: reload whole page after 500 ms
                    window.setTimeout(function () {
                        window.location.href = newLocation;
                    }, 500);
                }
            });
           
            RDFauthor.start();
        })
    });
}

/*
 * get the rdfauthor init description from the service in and start the RDFauthor window
 */
function editResourceFromURI(resource) {
    var serviceUri = urlBase + 'service/rdfauthorinit';

    // remove resource menus
    removeResourceMenus();

    loadRDFauthor(function() {
        $.getJSON(serviceUri, {
           mode: 'edit',
           uri: resource
        }, function(data) {
            
            // get default resource uri for subjects in added statements (issue 673)
            // grab first object key
            for (var subjectUri in data) {break;};

            // add statements to RDFauthor
            populateRDFauthor(data, false, resource, selectedGraph.URI);

            RDFauthor.setOptions({
                saveButtonTitle: 'Save Changes',
                cancelButtonTitle: 'Cancel',
                title: 'Edit Resource ' + resource,  
                autoParse: false, 
                showPropertyButton: true,
                loadOwStylesheet: false,
                onSubmitSuccess: function () {
                    // HACK: reload whole page after 500 ms
                    window.setTimeout(function () {
                        window.location.href = window.location.href;
                    }, 500);
                }
            });

            RDFauthor.start();
        })
    });
}

/**
 * Creates a new internal OntoWiki URL for the given resource URI.
 * @return string
 */
function resourceURL(resourceURI) {
    if (resourceURI.indexOf(urlBase) === 0) {
        // URL base is a prefix of requested resource URL
        return resourceURI;
    }

    return urlBase + 'view/?r=' + encodeURIComponent(resourceURI);
}

/*
 * Edit a complete OW property view property section
 */
function editProperty(event) {
    var element = $.event.fix(event).target;
    loadRDFauthor(function () {
        RDFauthor.setOptions({
            onSubmitSuccess: function () {
                $('.edit').each(function() {
                    $(this).fadeOut(effectTime);
                });
                $('.edit-enable').removeClass('active');

                // HACK: reload whole page after 1000 ms
                window.setTimeout(function () {
                    window.location.href = window.location.href;
                }, 1000);
            }, 
            onCancel: function () {
                $('.edit').each(function() {
                    $(this).fadeOut(effectTime);
                });
                $('.edit-enable').removeClass('active');
            }, 
            saveButtonTitle: 'Save Changes', 
            cancelButtonTitle: 'Cancel',
            loadOwStylesheet: false,
            title: $('.section-mainwindows .window').eq(0).children('.title').eq(0).text(), 
            viewOptions: {
                type: RDFAUTHOR_VIEW_MODE,
                container: function (statement) {
                    var element = RDFauthor.elementForStatement(statement);
                    var parent  = $(element).closest('div');

                    if (!parent.hasClass('ontowiki-processed')) {
                        parent.children().each(function () {
                            $(this).hide();
                        });
                        parent.addClass('ontowiki-processed');
                    }

                    return parent.get(0);
                }
            }
        });

        RDFauthor.start($(element).parents('td'));
        $('.edit-enable').addClass('active');
        $('.edit').each(function() {
            var button = this;
            $(this).fadeIn(effectTime);
        });
    });

    //return false;
}

/*
 * Edit a complete OW property view property section in table listview
 */
function editPropertyListmode(event) {
    var element = $.event.fix(event).target;
    var resource = $(element).parents('td').rdf().where('?s ?p ?o').dump();
    var resourceUri = Object.keys(resource)[0];
    var serviceUri = urlBase + 'service/rdfauthorinit';

    // remove resource menus
    removeResourceMenus();

    loadRDFauthor(function() {
        // add statements to RDFauthor
        populateRDFauthor(resource, false, resource, selectedGraph.URI);

        RDFauthor.setOptions({
            saveButtonTitle: 'Save Changes',
            cancelButtonTitle: 'Cancel',
            title: 'Edit Resource ' + resourceUri,
            autoParse: false, 
            showPropertyButton: false,
            loadOwStylesheet: false,
            onSubmitSuccess: function () {
                // HACK: reload whole page after 500 ms
                window.setTimeout(function () {
                    window.location.href = window.location.href;
                }, 500);
            }
        });

        RDFauthor.start();
    });
}

function addProperty() {
    var ID = RDFauthor.nextID();
    var td1ID = 'rdfauthor-property-selector-' + ID;
    var td2ID = 'rdfauthor-property-widget-' + ID;

    $('.edit').each(function() {
        $(this).fadeIn(effectTime);
    });

    $('table.rdfa')
        .removeClass('hidden')
        .show()
        .children('tbody')
        .prepend('<tr><td colspan="2" width="120"><div style="width:75%" id="' + td1ID + '"></div></td></tr>');

    $('table.rdfa').parent().find('p.messagebox').hide();
    
    var selectorOptions = {
        container: $('#' + td1ID), 
        selectionCallback: function (uri, label) {
            var statement = new Statement({
                subject: '<' + RDFAUTHOR_DEFAULT_SUBJECT + '>', 
                predicate: '<' + uri + '>'
            }, {
                title: label, 
                graph: RDFAUTHOR_DEFAULT_GRAPH
            });
            
            var owURL = urlBase + 'view?r=' + encodeURIComponent(uri);
            $('#' + td1ID).closest('td')
                .attr('colspan', '1')
                .html('<a class="hasMenu" about="' + uri + '" href="' + owURL + '">' + label + '</a>')
                .after('<td id="' + td2ID + '"></td>');
            RDFauthor.getView().addWidget(statement, null, {container: $('#' + td2ID), activate: true});
        }
    };
    
    var selector = new Selector(RDFAUTHOR_DEFAULT_GRAPH, RDFAUTHOR_DEFAULT_SUBJECT, selectorOptions);
    selector.presentInContainer();
}
/*
 * OntoWiki jQuery extensions
 *
 * @package    theme
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
(function($) {
    
    /**
     *  Enhances input fields with inner labels
     */
    $.fn.innerLabel = function() {
        return this.each(function() {
            // the input field
            var input = $(this);
            // the associated label element
            var label = $('label[for=' + input.attr('id') + ']');
            // the label text
            var labelText = label.text();
            
            if (typeof label != 'undefined') {
                input.focus(function() {
                    // if if label text is input's only content, set it empty
                    if (input.val() == labelText) {
                        input.val('');
                    }
                }).blur(function() {
                    // if nothing has been entered, set label text
                    if (input.val() == '') {
                        input.val(labelText);
                    }
                })
                
                label.addClass('onlyAural');
            }
        });
    }
    
    /**
     * Enhances input fields where the predefined value must be kept as a
     * prefix for any value entered.
     */
    $.fn.prefixValue = function() {
        return this.each(function() {
            var input = $(this);
            var prefix = input.val();
            
            input.keyup(function() {
                if (!input.val().match(prefix)) {
                    input.val(prefix);
                }
            });
            
            input.blur(function() {
                if (!input.val().match(prefix)) {
                    input.val(prefix);
                }
            });
        });
    }
    
    /**
     * Enhances windows with desktop-style GUI elements
     */
    $.fn.enhanceWindow = function() {
        return this.each(function() {
            var win = $(this);

            // add window buttons
            win.children('.window-buttons').remove();
            win.append('<div class="window-buttons"><div class="window-buttons-left"></div><div class="window-buttons-right"></div></div>');

            win.find('.window-buttons-right').append('<span class="button button-windowminimize"></span>');
            win.addClass('windowbuttonscount-right-1');

            if (win.hasClass('is-minimized')) {
                win.find('.button-windowminimize')
                    .removeClass('button-windowminimize')
                    .addClass('button-windowrestore');
            }

            // minimize
            win.find('.button-windowminimize').click(function() {
                win.toggleWindow();
            })
            // restore
            win.find('.button-windowrestore').click(function() {
                win.toggleWindow();
            })
            // minimize/maximize on title
            // win.find('.title').dblclick(function() {
            //     win.toggleWindow();
            // })
            
            // context menu button
            if (win.children('div').children('.contextmenu').length) {
                win.find('.window-buttons-left').append('<span class="button button-contextmenu"></span>');
                win.addClass('windowbuttonscount-left-1');

                // context menu action
                win.find('.button-contextmenu').click(function(event) {
                    showWindowMenu(event);
                })
            }

            // add menu
            if (win.children('div').children('ul.menu').length) {
                win.addClass('has-menu');
                win.children('div').children('ul.menu').clickMenu();
            }

            // create the additional tabbed class
            if (win.children('div').children('.tabs').length > 0) {
                win.addClass('tabbed');
                
                if (win.children('div').children('.active-tab-content').length == 0) {
                    win.children('div').children('.content').eq(0).addClass('active-tab-content');
                }
            }
            return win;
        });
    }
    
    /**
     * Minimizes/restores a window
     */
    $.fn.toggleWindow = function() {
        var win = this;
        
        if (win.hasClass('is-minimized')) {
            // TODO: why is this necessary
            win.children('.slidehelper').hide();
            win.removeClass('is-minimized');

            if (win.hasClass('has-menu-disabled')) {
                win.removeClass('has-menu-disabled').addClass('has-menu');
            }

            win.children('.slidehelper')
                .slideDown(effectTime, function() {
                    win.find('.button-windowrestore')
                        .removeClass('button-windowrestore')
                        .addClass('button-windowminimize');
                    }
                );

            win.find('div.cmDiv').adjustClickMenu();

            sessionStore(win.attr('id'), 1, {encode: true, namespace: 'Module_Registry'});
        } else {
            win.find('h1.title').attr('style', '');
            win.children('.slidehelper')
                .slideUp(effectTime, function() {
                    win.find('.button-windowminimize')
                        .removeClass('button-windowminimize')
                        .addClass('button-windowrestore');

                        if (win.hasClass('has-menu')) {
                            win.removeClass('has-menu').addClass('has-menu-disabled');
                        }
                        win.addClass('is-minimized');
                    }
                );

            sessionStore(win.attr('id'), 2, {encode: true, namespace: 'Module_Registry'});
        }
    }
    
    /**
     * Make link expandable
     */
    $.fn.expandable = function() {
        return this.each(function() {
            if (!$(this).prev().hasClass('collapse')) {
                $(this).before('<span class="icon-button expand"></span>');
            }
            $(this).prev().click(function(event) {
                toggleExpansion(event);
                return false; // -> event is not given further
            });
        })
    }

    /**
     * Enhance link with a menu toogle for showResourceMenu
     */
    $.fn.createResourceMenuToggle = function() {
        return this.each(function() {
            //if (!$(this).find('span.toggle')) {
                $(this).append('<span class="toggle" title="Menu"></span>');
            //}
            $(this).children('span.toggle')
                .mouseover(function() {
                    hideHref($(this).parent());
                    $('.contextmenu-enhanced .contextmenu').remove(); // remove all other menus
                })
                .click(function(event) {
                    showResourceMenu(event);
                })
                .mouseout(function() {
                    showHref($(this).parent())
                });
        })
    }
    
    /**
     * Make inline elements editable.
     */
    $.fn.makeEditable = function () {
         return this.each(function() {
             if($(this).hasClass('editable')){
                 $(this).addClass('has-contextmenu-area').css('display', 'block');

                 if ($(this).children('.contextmenu').length < 1) {
                     $(this).append('<div class="contextmenu"></div>');
                 }

                 $(this).children('.contextmenu').append('\
                     <div class="item">\
                         <span class="icon icon-edit" title="Edit these values">\
                         </span>\
                         <!--span class="icon icon-delete" title="Delete all values">\
                         </span-->\
                     </div>\
                 ');
             }
         })
    }
    
    /**
     * Checks whether two elements are equal
     */
    $.fn.equals = function (element) {
        return this.each(function () {
            
        });
    }

    /**
     * adjust the space what is needed by the window menu
     */
    $.fn.adjustClickMenu = function () {
        return this.each(function () {
            var menu = $(this);
            var window = menu.parents('div.window');
            if (window.attr('id') !== 'application') {
                window.children('h1.title').attr('style', 'margin-bottom:'+menu.outerHeight(true)+'px !important;');
            }
        });
    }

})(jQuery);

//-----------------------------------------------------------------------------
// Defaults
//-----------------------------------------------------------------------------

// set defaults for clickmenu
$.fn.clickMenu.setDefaults({arrowSrc: themeUrlBase + 'images/submenu-indicator.png'});
