/**
 *  RDFA in Javascript
 *  Ben Adida - ben@adida.net
 *  Nathan Yergler - nathan@creativecommons.org
 *  Jeni Tennison - jeni@jenitennison.com
 *
 *  W3C Open Source License
 * 
 * includes the AJAR (Tabulator) stuff
 */

//
//
//

//  Implementing URI-specific functions
//
//  See RFC 2386
//
// This is or was   http://www.w3.org/2005/10/ajaw/uri.js
// 2005 W3C open source licence
//
//
//  Take a URI given in relative or absolute form and a base
//  URI, and return an absolute URI
//
//  See also http://www.w3.org/2000/10/swap/uripath.py
//

// 2010-04-07 NH
// for IE
if (!('indexOf' in Array.prototype)) {
    Array.prototype.indexOf = function(obj) {
        for (var i = 0; i < this.length; i++) {
            if (this[i] == obj) {
                return i;
            }
        }
        return -1;
    }
}

if (typeof Util == "undefined") { Util = {}}
if (typeof Util.uri == "undefined") { Util.uri = {}}

Util.uri.join = function (given, base) {
    // if (typeof tabulator.log.debug != 'undefined') tabulator.log.debug("   URI given="+given+" base="+base)
    var baseHash = base.indexOf('#');
    if (baseHash > 0) base = base.slice(0, baseHash);
    if (given.length==0) return base // before chopping its filename off
    if (given.indexOf('#')==0) return base + given;
    var colon = given.indexOf(':');
    if (colon >= 0) return given    // Absolute URI form overrides base URI
    var baseColon = base.indexOf(':');
    if (base == "") return given;
    if (baseColon < 0) {
        alert("Invalid base: "+ base + ' in join with ' +given);
        return given;
    }
    var baseScheme = base.slice(0,baseColon+1)  // eg http:
    if (given.indexOf("//") == 0)     // Starts with //
    return baseScheme + given;
    if (base.indexOf('//', baseColon)==baseColon+1) {  // Any hostpart?
        var baseSingle = base.indexOf("/", baseColon+3)
    if (baseSingle < 0) {
        if (base.length-baseColon-3 > 0) {
        return base + "/" + given
        } else {
        return baseScheme + given
        }
    }
    } else {
    var baseSingle = base.indexOf("/", baseColon+1)
    if (baseSingle < 0) {
        if (base.length-baseColon-1 > 0) {
        return base + "/" + given
        } else {
        return baseScheme + given
        }
    }
    }

    if (given.indexOf('/') == 0)    // starts with / but not //
    return base.slice(0, baseSingle) + given
    
    var path = base.slice(baseSingle)
    var lastSlash = path.lastIndexOf("/")
    if (lastSlash <0) return baseScheme + given
    if ((lastSlash >=0) && (lastSlash < (path.length-1)))
    path = path.slice(0, lastSlash+1) // Chop trailing filename from base
    
    path = path + given
    while (path.match(/[^\/]*\/\.\.\//)) // must apply to result of prev
    path = path.replace( /[^\/]*\/\.\.\//, '') // ECMAscript spec 7.8.5
    path = path.replace( /\.\//g, '') // spec vague on escaping
    return base.slice(0, baseSingle) + path
}

//  refTo:    Make a URI relative to a given base
//
// based on code in http://www.w3.org/2000/10/swap/uripath.py
//
Util.uri.commonHost = new RegExp("^[-_a-zA-Z0-9.]+:(//[^/]*)?/[^/]*$");
Util.uri.refTo = function(base, uri) {
    if (!base) return uri;
    if (base == uri) return "";
    var i =0; // How much are they identical?
    while (i<uri.length && i < base.length)
        if (uri[i] == base[i]) i++;
        else break;
    if (base.slice(0,i).match(Util.uri.commonHost)) {
        var k = uri.indexOf('//');
        if (k<0) k=-2; // no host
        var l = uri.indexOf('/', k+2);   // First *single* slash
        if (uri.slice(l+1, l+2) != '/' && base.slice(l+1, l+2) != '/'
                           && uri.slice(0,l) == base.slice(0,l)) // common path to single slash
            return uri.slice(l); // but no other common path segments
    }
     // fragment of base?
    if (uri.slice(i, i+1) == '#' && base.length == i) return uri.slice(i);
    while (i>0 && uri[i-1] != '/') i--;

    if (i<3) return uri; // No way
    if ((base.indexOf('//', i-2) > 0) || uri.indexOf('//', i-2) > 0)
        return uri; // an unshared '//'
    if (base.indexOf(':', i) >0) return uri; // unshared ':'
    var n = 0;
    for (var j=i; j<base.length; j++) if (base[j]=='/') n++;
    if (n==0 && i < uri.length && uri[i] =='#') return './' + uri.slice(i);
    if (n==0 && i == uri.length) return './';
    var str = '';
    for (var j=0; j<n; j++) str+= '../';
    return str + uri.slice(i);
}


/** returns URI without the frag **/
Util.uri.docpart = function (uri) {
    var i = uri.indexOf("#")
    if (i < 0) return uri
    return uri.slice(0,i)
} 

/** return the protocol of a uri **/
Util.uri.protocol = function (uri) {
    return uri.slice(0, uri.indexOf(':'))
} //protocol

URIjoin = Util.uri.join
uri_docpart = Util.uri.docpart
uri_protocol = Util.uri.protocol


//ends


// These are the classes corresponding to the RDF and N3 data models
//
// Designed to look like rdflib and cwm designs.
//
// Issues: Should the names start with RDF to make them
//      unique as program-wide symbols?
//
// W3C open source licence 2005.
//

RDFTracking = 0  // Are we requiring reasons for statements?

//takes in an object and makes it an object if it's a literal
function makeTerm(val) {
    //  tabulator.log.debug("Making term from " + val)
    if (typeof val == 'object') return val;
    if (typeof val == 'string') return new RDFLiteral(val);
    if (typeof val == 'number') return new RDFLiteral(val); // @@ differet types
    if (typeof val == 'boolean') return new RDFLiteral(val?"1":"0", undefined, 
                                                RDFSymbol.prototype.XSDboolean);
    if (typeof val == 'undefined') return undefined;
    alert("Can't make term from " + val + " of type " + typeof val);
}


//  Symbol

function RDFEmpty() {
    return this;
}
RDFEmpty.prototype.termType = 'empty'
RDFEmpty.prototype.toString = function () { return "()" }
RDFEmpty.prototype.toNT = function () { return "@@" }

function RDFSymbol_toNT(x) {
    return ("<" + x.uri + ">")
}

function toNT() {
    return RDFSymbol_toNT(this)
}

function RDFSymbol(uri) {
    this.uri = uri
    return this
}
    
RDFSymbol.prototype.termType = 'symbol'
RDFSymbol.prototype.toString = toNT
RDFSymbol.prototype.toNT = toNT

//  Some precalculaued symbols

RDFSymbol.prototype.XSDboolean = new RDFSymbol('http://www.w3.org/2001/XMLSchema#boolean');
RDFSymbol.prototype.integer = new RDFSymbol('http://www.w3.org/2001/XMLSchema#integer');


//  Blank Node

var RDFNextId = 0;  // Gobal genid
RDFGenidPrefix = "genid:"
NTAnonymousNodePrefix = "_:n"

function RDFBlankNode(id) {
    // 2007-11-26 JT
    // uncommented out next three lines
    if (id != null) {
        this.id = id;
    } else {
      this.id = RDFNextId++
    }
    return this
}

RDFBlankNode.prototype.termType = 'bnode'

RDFBlankNode.prototype.toNT = function() {
    return NTAnonymousNodePrefix + this.id
}
RDFBlankNode.prototype.toString = RDFBlankNode.prototype.toNT  

//  Literal

function RDFLiteral(value, lang, datatype) {
    this.value = value
    this.lang=lang;   // string
    this.datatype=datatype;  // term
    this.toString = RDFLiteralToString
    this.toNT = RDFLiteral_toNT
    return this
}

RDFLiteral.prototype.termType = 'literal'

function RDFLiteral_toNT() {
    var str = this.value
    if (typeof str != 'string') {
    throw Error("Value of RDF literal is not string: "+str)
    }
    str = str.replace(/\\/g, '\\\\');  // escape
    str = str.replace(/\"/g, '\\"');
    str = '"' + str + '"'  //'

    if (this.datatype){
    str = str + '^^' + this.datatype//.toNT()
    }
    if (this.lang) {
    str = str + "@" + this.lang
    }
    return str
}

function RDFLiteralToString() {
    return this.value
}
    
RDFLiteral.prototype.toString = RDFLiteralToString   
RDFLiteral.prototype.toNT = RDFLiteral_toNT

function RDFCollection() {
    this.id = RDFNextId++
    this.elements = []
    this.closed = false
}

RDFCollection.prototype.termType = 'collection'

RDFCollection.prototype.toNT = function() {
    return NTAnonymousNodePrefix + this.id
}
RDFCollection.prototype.toString = RDFCollection.prototype.toNT 

RDFCollection.prototype.append = function (el) {
    this.elements.push(el)
}
RDFCollection.prototype.unshift=function(el){
    this.elements.unshift(el);
}
RDFCollection.prototype.shift=function(){
    return this.elements.shift();
}
        
RDFCollection.prototype.close = function () {
    this.closed = true
}

//  Statement
//
//  This is a triple with an optional reason.
//
//   The reason can point to provenece or inference
//
function RDFStatement_toNT() {
    return (this.subject.toNT() + " "
        + this.predicate.toNT() + " "
        +  this.object.toNT() +" .")
}

function RDFStatement(subject, predicate, object, why) {
    this.subject = makeTerm(subject)
    this.predicate = makeTerm(predicate)
    this.object = makeTerm(object)
    if (typeof why !='undefined') {
    this.why = why
    } else if (RDFTracking) {
        //tabulator.log.debug("WARNING: No reason on "+subject+" "+predicate+" "+object)
    }
    return this
}

RDFStatement.prototype.toNT = RDFStatement_toNT
RDFStatement.prototype.toString = RDFStatement_toNT
    

//  Formula
//
//  Set of statements.

function RDFFormula() {
    this.statements = []
    this.constraints = []
    this.initBindings = []
    this.optional = []
    this.superFormula = null;
    return this
}

function RDFFormula_toNT() {
    return "{\n" + this.statements.join('\n') + "}"
}

//RDFQueryFormula.prototype = new RDFFormula()
//RDFQueryFormula.termType = 'queryFormula'
RDFFormula.prototype.termType = 'formula'
RDFFormula.prototype.toNT = RDFFormula_toNT
RDFFormula.prototype.toString = RDFFormula_toNT   

RDFFormula.prototype.add = function(subj, pred, obj, why) {
    this.statements.push(new RDFStatement(subj, pred, obj, why))
}

// Convenience methods on a formula allow the creation of new RDF terms:

RDFFormula.prototype.sym = function(uri,name) {
    if (name != null) {
        throw 'second parameter temporarily not supported';
        /*
        if (!tabulator.ns[uri]) throw 'The prefix "'+uri+'" is not set in the API';
          uri = tabulator.ns[uri] + name
          */
    }
    return new RDFSymbol(uri)
}

RDFFormula.prototype.literal = function(val, lang, dt) {
    return new RDFLiteral(val.toString(), lang, dt)
}

RDFFormula.prototype.bnode = function(id) {
    return new RDFBlankNode(id)
}

RDFFormula.prototype.formula = function() {
    return new RDFFormula()
}

RDFFormula.prototype.collection = function () { // obsolete
    return new RDFCollection()
}

RDFFormula.prototype.list = function (values) {
    li = new RDFCollection();
    if (values) {
        for(var i = 0; i<values.length; i++) {
            li.append(values[i]);
        }
    }
    return li;
}

RDFFormula.instances={};
RDFFormula.prototype.registerFormula = function(accesskey){
    var superFormula = this.superFormula || this;
    RDFFormula.instances[accesskey] = this;
    var formulaTerm = superFormula.bnode();
    superFormula.add(formulaTerm,rdf('type'),superFormula.sym("http://www.w3.org/2000/10/swap/log#Formula"));
    /*
    superFormula.add(formulaTerm, tabulator.ns.foaf('name'), superFormula.literal(accesskey));
    superFormula.add(formulaTerm, tabulator.ns.link('accesskey'), superFormula.literal(accesskey));
    */
    //RDFFormula.instances.push("accesskey");
}
/*  Variable
**
** Variables are placeholders used in patterns to be matched.
** In cwm they are symbols which are the formula's list of quantified variables.
** In sparl they are not visibily URIs.  Here we compromise, by having
** a common special base URI for variables.
*/

RDFVariableBase = "varid:"; // We deem variabe x to be the symbol varid:x 

function RDFVariable(rel) {
    this.uri = URIjoin(rel, RDFVariableBase);
    return this;
}

RDFVariable.prototype.termType = 'variable';
RDFVariable.prototype.toNT = function() {
    if (this.uri.slice(0, RDFVariableBase.length) == RDFVariableBase) {
    return '?'+ this.uri.slice(RDFVariableBase.length);} // @@ poor man's refTo
    return '?' + this.uri;
};

RDFVariable.prototype.toString = RDFVariable.prototype.toNT;
RDFVariable.prototype.classOrder = 7;

RDFFormula.prototype.variable = function(name) {
    return new RDFVariable(name);
};

RDFVariable.prototype.hashString = RDFVariable.prototype.toNT;


// The namespace function generator 

function Namespace(nsuri) {
    return function(ln) { return new RDFSymbol(nsuri+(ln===undefined?'':ln)) }
}

RDFFormula.prototype.ns = function(nsuri) {
    return function(ln) { return new RDFSymbol(nsuri+(ln===undefined?'':ln)) }
}


// Parse a single token
//
// The bnode bit should not be used on program-external values; designed
// for internal work such as storing a bnode id in an HTML attribute.
// Not coded for literals.

RDFFormula.prototype.fromNT = function(str) {
    var len = str.length
    var ch = str.slice(0,1)
    if (ch == '<') return this.sym(str.slice(1,len-1))
    if (ch == '_') {
    var x = new RDFBlankNode()
    x.id = parseInt(str.slice(3))
    RDFNextId--
    return x
    }
    //alert("Can't yet convert from NT: '"+str+"', "+str[0])
}

// ends

// Matching a statement against a formula
//
//
// W3C open source licence 2005.
//
// We retpresent a set as an associative array whose value for
// each member is set to true.

function RDFTermMatch(pattern, term) {
    if (typeof pattern == 'undefined') return true;
    return pattern.sameTerm(term)
}

RDFSymbol.prototype.sameTerm = function(other) {
    if (!other) { return false }
    return ((this.termType == other.termType) && (this.uri == other.uri))
}

RDFBlankNode.prototype.sameTerm = function(other) {
    if (!other) { return false }
    return ((this.termType == other.termType) && (this.id == other.id))
}

RDFLiteral.prototype.sameTerm = function(other) {
    if (!other) { return false }
    return ((this.termType == other.termType)
        && (this.value == other.value)
        && (this.lang == other.lang) &&
        ((!this.datatype && !other.datatype)
         || this.datatype.sameTerm(other.datatype)))
}

RDFVariable.prototype.sameTerm = function (other) {
    if (!other) { return false }
    return((this.termType == other.termType) && (this.uri == other.uri))
}

RDFCollection.prototype.sameTerm = RDFBlankNode.prototype.sameTerm

//  Comparison for ordering
//
// These compare with ANY term
//
//
// When we smush nodes we take the lowest value. This is not
// arbitrary: we want the value actually used to be the literal
// (or list or formula). 

RDFLiteral.prototype.classOrder = 1
// RDFList.prototype.classOrder = 2
// RDFSet.prototype.classOrder = 3
RDFCollection.prototype.classOrder = 3
RDFFormula.prototype.classOrder = 4
RDFSymbol.prototype.classOrder = 5
RDFBlankNode.prototype.classOrder = 6

//  Compaisons return  sign(self - other)
//  Literals must come out before terms for smushing

RDFLiteral.prototype.compareTerm = function(other) {
    if (this.classOrder < other.classOrder) return -1
    if (this.classOrder > other.classOrder) return +1
    if (this.value < other.value) return -1
    if (this.value > other.value) return +1
    return 0
} 

RDFSymbol.prototype.compareTerm = function(other) {
    if (this.classOrder < other.classOrder) return -1
    if (this.classOrder > other.classOrder) return +1
    if (this.uri < other.uri) return -1
    if (this.uri > other.uri) return +1
    return 0
} 

RDFBlankNode.prototype.compareTerm = function(other) {
    if (this.classOrder < other.classOrder) return -1
    if (this.classOrder > other.classOrder) return +1
    if (this.id < other.id) return -1
    if (this.id > other.id) return +1
    return 0
} 

RDFCollection.prototype.compareTerm = RDFBlankNode.prototype.compareTerm

//  Convenience routines

// Only one of s p o can be undefined, and w is optional.
RDFFormula.prototype.each = function(s,p,o,w) {
    var results = []
    var st, sts = this.statementsMatching(s,p,o,w)
    var i, n=sts.length
    if (typeof s == 'undefined') {
    for (i=0; i<n; i++) {st=sts[i]; results.push(st.subject)}
    } else if (typeof p == 'undefined') {
    for (i=0; i<n; i++) {st=sts[i]; results.push(st.predicate)}
    } else if (typeof o == 'undefined') {
    for (i=0; i<n; i++) {st=sts[i]; results.push(st.object)}
    } else if (typeof w == 'undefined') {
    for (i=0; i<n; i++) {st=sts[i]; results.push(st.why)}
    }
    return results
}

RDFFormula.prototype.any = function(s,p,o,w) {
    var st = this.anyStatementMatching(s,p,o,w)
    if (typeof st == 'undefined') return undefined;
    
    if (typeof s == 'undefined') return st.subject;
    if (typeof p == 'undefined') return st.predicate;
    if (typeof o == 'undefined') return st.object;

    return undefined
}

RDFFormula.prototype.the = function(s,p,o,w) {
    // the() should contain a check there is only one
    var x = this.any(s,p,o,w)
    return x
}

RDFFormula.prototype.whether = function(s,p,o,w) {
    return this.statementsMatching(s,p,o,w).length;
}
 
// Not a method. For use in sorts
function RDFComparePredicateObject(self, other) {
    var x = self.predicate.compareTerm(other.predicate)
    if (x !=0) return x
    return self.object.compareTerm(other.object)
}
function RDFComparePredicateSubject(self, other) {
    var x = self.predicate.compareTerm(other.predicate)
    if (x !=0) return x
    return self.subject.compareTerm(other.subject)
}
// ends


//  Identity management and indexing for RDF
//
// This file provides  RDFIndexedFormula a formula (set of triples) which
// indexed by predicate, subject and object.
//
// It "smushes"  (merges into a single node) things which are identical 
// according to owl:sameAs or an owl:InverseFunctionalProperty
// or an owl:FunctionalProperty
//
// Missing: Equating predicates will not propagate these actions if there are >1
//
//  2005-10 Written Tim Berners-Lee
//
// 

/*jsl:option explicit*/ // Turn on JavaScriptLint variable declaration checking

owl_ns = "http://www.w3.org/2002/07/owl#";
link_ns = "http://www.w3.org/2006/link#";

/* hashString functions are used as array indeces. This is done to avoid
** conflict with existing properties of arrays such as length and map.
** See issue 139.
*/
RDFLiteral.prototype.hashString = RDFLiteral.prototype.toNT;
RDFSymbol.prototype.hashString = RDFSymbol.prototype.toNT;
RDFBlankNode.prototype.hashString = RDFBlankNode.prototype.toNT;
RDFCollection.prototype.hashString = RDFCollection.prototype.toNT;

RDFIndexedFormula.prototype = new RDFFormula();
RDFIndexedFormula.prototype.constructor = RDFIndexedFormula;
// RDFIndexedFormula.superclass = RDFFormula.prototype;
RDFIndexedFormula.SuperClass = RDFFormula;

RDFArrayRemove = function(a, x) {  //removes all elements equal to x from a
    for(var i=0; i<a.length; i++) {
    if (a[i] == x) {
            a.splice(i,1);
            return;
    }
    }
    throw "RDFArrayRemove: Array did not contain " + x;
};

//Stores an associative array that maps URIs to functions
function RDFIndexedFormula(features) {
    this.statements = [];    // As in RDFFormula
    this.propertyAction = []; // What to do when getting statement with {s X o}
    //maps <uri> to f(F,s,p,o)
    this.classAction = [];   // What to do when adding { s type X }
    this.redirection = [];   // redirect to lexically smaller equivalent symbol
    this.aliases = [];   // reverse mapping to redirection: aliases for this
    this.HTTPRedirects = []; // redirections we got from HTTP
    this.subjectIndex = [];  // Array of statements with this X as subject
    this.predicateIndex = [];  // Array of statements with this X as subject
    this.objectIndex = [];  // Array of statements with this X as object
    this.namespaces = {} // Dictionary of namespace prefixes
    // 2010-04-07 NH
    // would cause problem w/ sameAs etc.
    // if (typeof features == 'undefined') features = ["sameAs", "InverseFunctionalProperty", "FunctionalProperty"];
    if (typeof features == 'undefined') features = [];
//    this.features = features

    // Callbackify?
    
    function handleRDFType(formula, subj, pred, obj, why) {
        if (typeof formula.typeCallback != 'undefined')
            formula.typeCallback(formula, obj, why);

        var x = formula.classAction[obj.hashString()];
        if (x) return x(formula, subj, pred, obj);
        return false; // statement given is needed
    } //handleRDFType

    //If the predicate is #type, use handleRDFType to create a typeCallback on the object
    this.propertyAction[
    '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>'] = handleRDFType;

    // Assumption: these terms are not redirected @@fixme
    if (features.indexOf("sameAs") >=0)
        this.propertyAction['<http://www.w3.org/2002/07/owl#sameAs>'] =
    function(formula, subj, pred, obj, why) {
            formula.equate(subj,obj);
            return true; // true if statement given is NOT needed in the store
    }; //sameAs -> equate & don't add to index

    function newPropertyAction(formula, pred, action) {
        formula.propertyAction[pred] = action;
    var toBeFixed = formula.statementsMatching(undefined, pred, undefined);
    var i;
    for (i=0; i<toBeFixed.length; i++) { // NOT optimized - sort toBeFixed etc
        if (action(formula, toBeFixed[i].subject, pred, toBeFixed[i].object)) {
            // tabulator.log.debug("newPropertyAction: NOT removing "+toBeFixed[i]);
        }
    }
    return false;
    }

    if (features.indexOf("InverseFunctionalProperty") >= 0)
        this.classAction["<"+owl_ns+"InverseFunctionalProperty>"] =
            function(formula, subj, pred, obj, addFn) {
                return newPropertyAction(formula, subj, handle_IFP); // yes subj not pred!
            }; //IFP -> handle_IFP, do add to index

    if (features.indexOf("FunctionalProperty") >= 0)
        this.classAction["<"+owl_ns+"FunctionalProperty>"] =
            function(formula, subj, proj, obj, addFn) {
                return newPropertyAction(formula, subj, handle_FP);
            }; //FP => handleFP, do add to index

    function handle_IFP(formula, subj, pred, obj)  {
        var s1 = formula.any(undefined, pred, obj);
        if (typeof s1 == 'undefined') return false; // First time with this value
        formula.equate(s1, subj);
        return true;
    } //handle_IFP

    function handle_FP(formula, subj, pred, obj)  {
        var o1 = formula.any(subj, pred, undefined);
        if (typeof o1 == 'undefined') return false; // First time with this value
        formula.equate(o1, obj);
        return true ;
    } //handle_FP
    
} /* end RDFIndexedFormula */

RDFPlainFormula = function() { return RDFIndexedFormula([]); } // No features


RDFIndexedFormula.prototype.setPrefixForURI = function(prefix, nsuri) {
    //TODO:This is a hack for our own issues, which ought to be fixed post-release
    //See http://dig.csail.mit.edu/cgi-bin/roundup.cgi/tabulator/issue227
    if(prefix=="tab" && this.namespaces["tab"]) {
        return;
    }
    this.namespaces[prefix] = nsuri
}

// Deprocated ... name too generic
RDFIndexedFormula.prototype.register = function(prefix, nsuri) {
    this.namespaces[prefix] = nsuri
}


/** simplify graph in store when we realize two identifiers are equal

We replace the bigger with the smaller.

*/
RDFIndexedFormula.prototype.equate = function(u1, u2) {
    //tabulator.log.info("Equating "+u1+" and "+u2)
    
    var d = u1.compareTerm(u2);
    if (!d) return true; // No information in {a = a}
    var big, small;
    if (d < 0)  {  // u1 less than u2
    return this.replaceWith(u2, u1);
    } else {
    return this.replaceWith(u1, u2);
    }
}

// Replace big with small, obsoleted with obsoleting.
//
RDFIndexedFormula.prototype.replaceWith = function(big, small) {

    var i, toBeFixed, hash;
    toBeFixed = this.statementsMatching(big, undefined, undefined);
    //tabulator.log.debug("Replacing "+big+" with "+small) // @@

    for (i=0; i < toBeFixed.length; i++) {
        toBeFixed[i].subject = small;
    hash = small.hashString();
        if (typeof this.subjectIndex[hash] == 'undefined')
                this.subjectIndex[hash] = [];
        this.subjectIndex[hash].push(toBeFixed[i]);
    }
    delete this.subjectIndex[big.hashString()];

    // If we allow equating predicates we must index them.
    toBeFixed = this.statementsMatching(undefined, big, undefined);
    
    for (i = 0; i < toBeFixed.length; i++) {
    toBeFixed[i].predicate = small;
    hash = small.hashString();
    if (typeof this.predicateIndex[hash] == 'undefined')
        this.predicateIndex[hash] = [];
    this.predicateIndex[hash].push(toBeFixed[i]);
    }
    delete this.predicateIndex[big.hashString()];
    
    toBeFixed = this.statementsMatching(undefined, undefined, big);
    for (i=0; i < toBeFixed.length; i++) {
        //  RDFArrayRemove(this.objectIndex[big], st)
        toBeFixed[i].object = small;
    hash = small.hashString()
        if (typeof this.objectIndex[hash] == 'undefined')
                this.objectIndex[hash] = [];
        this.objectIndex[hash].push(toBeFixed[i]);
    }
    delete this.objectIndex[big.hashString()];
    
    this.redirection[big.hashString()] = small;
    if (big.uri) {
    if (typeof (this.aliases[small.hashString()]) == 'undefined')
         this.aliases[small.hashString()] = [];
    this.aliases[small.hashString()].push(big); // Back link

    this.add(small, this.sym('http://www.w3.org/2006/link#uri'), big.uri)

    // If two things are equal, and one is requested, we should request the other.
    if (this.sf) {
        this.sf.nowKnownAs(big, small)
    }
    
    }
    
    /* merge actions @@ assumes never > 1 action*/
    var action = this.classAction[big.hashString()];
    if ((typeof action != 'undefined') &&
    (typeof this.classAction[small.hashString()] == 'undefined')) {
        this.classAction[small.hashString()] = action;
    }
    
    action = this.propertyAction[big.hashString()];
    if ((typeof action != 'undefined') &&
    (typeof this.propertyAction[small.hashString()] == 'undefined')) {
        this.propertyAction[small.hashString()] = action;
    }
    // tabulator.log.debug("Equate done. "+big+" to be known as "+small)    
    return true;  // true means the statement does not need to be put in
};

// Return the symbol with canonical URI as smushed
RDFIndexedFormula.prototype.canon = function(term) {
    var y = this.redirection[term.hashString()];
    if (typeof y == 'undefined') return term;
    return y;
}

// A list of all the URIs by which this thing is known
RDFIndexedFormula.prototype.uris = function(term) {
    var cterm = this.canon(term)
    var terms = this.aliases[cterm.hashString()];
    if (!cterm.uri) return []
    var res = [ cterm.uri ]
    if (typeof terms != 'undefined') {
    for (var i=0; i<terms.length; i++) {
        res.push(terms[i].uri)
    }
    }
    return res
}

// On input parameters, do redirection and convert constants to terms
// We do not redirect literals
function RDFMakeTerm(formula,val) {
    if (typeof val != 'object') {   
    if (typeof val == 'string')
        return new RDFLiteral(val);
        if (typeof val == 'number')
            return new RDFLiteral(val); // @@ differet types
        if (typeof val == 'boolean')
            return new RDFLiteral(val?"1":"0", undefined, 
                                            RDFSymbol.prototype.XSDboolean);
    else if (typeof val == 'number')
        return new RDFLiteral(''+val);   // @@ datatypes
    else if (typeof val == 'undefined')
        return undefined;
        else    // @@ add converting of dates and numbers
        throw "Can't make Term from " + val + " of type " + typeof val; 
    }
    if (typeof formula.redirection == 'undefined') {
        throw 'Internal: No redirection index for formula: '+ formula+', term: '+val;
    }
    OY_VAL = val;
    var y = formula.redirection[val.hashString()];
    if (typeof y == 'undefined') return val;
//    tabulator.log.debug(" redirecting "+val+" to "+y)
    return y;
}

// add a triple to the store
RDFIndexedFormula.prototype.add = function(subj, pred, obj, why) {
    var action, st, hashS, hashP, hashO;

    if (typeof obj == 'undefined') {
    throw ('Undefined object in identity.js#RDFIndexedFormula.prototype.add, subj='
        +subj+', pred='+pred)
    }

    subj = RDFMakeTerm(this, subj);
    pred = RDFMakeTerm(this, pred);
    obj = RDFMakeTerm(this, obj);
    why = RDFMakeTerm(this, why);
    

    // Look for strange bug that this is called with no object very occasionally
    // and only when running script in file: space
    if (typeof obj == 'undefined') {
    throw ('Undefined object in identity.js#RDFIndexedFormula.prototype.add, subj='
        +subj+', pred='+pred)
    }

    
  
    var hashS = subj.hashString();
    var hashP = pred.hashString();
    var hashO = obj.hashString();
    
    // Check we don't already know it -- esp when working with dbview
    st = this.anyStatementMatching(subj,pred,obj) // @@@@@@@ temp fix <====WATCH OUT!
    if (typeof st != 'undefined') return; // already in store
    //    tabulator.log.debug("\nActions for "+s+" "+p+" "+o+". size="+this.statements.length)
    if (typeof this.predicateCallback != 'undefined')
    this.predicateCallback(this, pred, why);
    
    // Action return true if the statement does not need to be added
    action = this.propertyAction[hashP];
    if (action && action(this, subj, pred, obj, why)) return;
    
    st = new RDFStatement(subj, pred, obj, why);
    if (typeof this.subjectIndex[hashS] =='undefined') this.subjectIndex[hashS] = [];
    this.subjectIndex[hashS].push(st); // Set of things with this as subject
    
    if (typeof this.predicateIndex[hashP] =='undefined') this.predicateIndex[hashP] = [];
    this.predicateIndex[hashP].push(st); // Set of things with this as subject
    
    if (typeof this.objectIndex[hashO] == 'undefined') this.objectIndex[hashO] = [];
    this.objectIndex[hashO].push(st); // Set of things with this as object
    //tabulator.log.debug("ADDING    {"+subj+" "+pred+" "+obj+"} "+why);
    var newIndex=this.statements.push(st);
    return this.statements[newIndex-1];
}; //add

// Find out whether a given URI is used as symbol in the formula
RDFIndexedFormula.prototype.mentionsURI = function(uri) {
    var hash = '<' + uri + '>';
    return (!!this.subjectIndex[hash] || !!this.objectIndex[hash]
            || !!this.predicateIndex[hash]);
}

// Find an unused id for a file being edited: return a symbol
RDFIndexedFormula.prototype.nextSymbol = function(doc) {
    for(var i=0;;i++) {
        var uri = doc.uri + '#n' + i;
        if (!this.mentionsURI(uri)) return kb.sym(uri);
    }
}

// Find an unused id for a file being edited
/* RDFIndexedFormula.prototype.newId = function(doc) {
    for(var i=0;;i++) {
        var uri = doc.uri + '#n' + i;
        if (!this.mentionsURI(uri)) return uri;
    }
}
*/

RDFIndexedFormula.prototype.anyStatementMatching = function(subj,pred,obj,why) {
    var x = this.statementsMatching(subj,pred,obj,why,true);
    if (!x || x == []) return undefined;
    return x[0];
};

// return statements matching a pattern
// ALL CONVENIENCE LOOKUP FUNCTIONS RELY ON THIS!
RDFIndexedFormula.prototype.statementsMatching = function(subj,pred,obj,why,justOne) {
    var results = [];
    var candidates;
    //tabulator.log.debug("Matching {"+subj+" "+pred+" "+obj+"}");
    subj = RDFMakeTerm(this, subj);
    pred = RDFMakeTerm(this, pred);
    obj = RDFMakeTerm(this, obj);
    why = RDFMakeTerm(this, why);

    if (typeof(pred) != 'undefined' && this.redirection[pred.hashString()])
    pred = this.redirection[pred.hashString()];

    if (typeof(obj) != 'undefined' && this.redirection[obj.hashString()])
    obj = this.redirection[obj.hashString()];

    //looks for candidate statements matching a given s/p/o
    if (typeof(subj) =='undefined') {
        if (typeof(obj) =='undefined') {
        if (typeof(pred) == 'undefined') { 
        candidates = this.statements; //all wildcards
        } else {
        candidates = this.predicateIndex[pred.hashString()];
//      tabulator.log.debug("@@Trying predciate "+p+" length "+candidates.length)

        if (typeof candidates == 'undefined') return [];
    }
    //      tabulator.log.debug("Trying all "+candidates.length+" statements")
        } else { // subj undefined, obj defined
            candidates = this.objectIndex[obj.hashString()];
            if (typeof candidates == 'undefined') return [];
            if ((typeof pred == 'undefined') &&
        (typeof why == 'undefined')) {
                // tabulator.log.debug("Returning all statements for object")
                return candidates ;
            }
            // tabulator.log.debug("Trying only "+candidates.length+" object statements")
        }
    } else {  // s defined
        if (this.redirection[subj.hashString()])
            subj = this.redirection[subj.hashString()];
        candidates = this.subjectIndex[subj.hashString()];
        if (typeof candidates == 'undefined') return [];
        if (typeof(obj) =='undefined') {
        if ((typeof pred == 'undefined') && (typeof why == 'undefined')) {
        // tabulator.log.debug("Trying all "+candidates.length+" subject statements")
        return candidates;
        }
    } else { // s and o defined ... unusual in practice?
            var oix = this.objectIndex[obj.hashString()];
            if (typeof oix == 'undefined') return [];
        if (oix.length < candidates.length) {
        candidates = oix;
//      tabulator.log.debug("Wow!  actually used object index instead of subj");
        }
    
        }
        // tabulator.log.debug("Trying only "+candidates.length+" subject statements")
    }
    
    if (typeof candidates == 'undefined') return [];
//    tabulator.log.debug("Matching {"+s+" "+p+" "+o+"} against "+n+" stmts")
    var st;
    for(var i=0; i<candidates.length; i++) {
        st = candidates[i]; //for each candidate, match terms of candidate with input, then return all
        // tabulator.log.debug("  Matching against st=" + st +" why="+st.why);
        if (RDFTermMatch(pred, st.predicate) &&  // first as simplest
            RDFTermMatch(subj, st.subject) &&
            RDFTermMatch(obj, st.object) &&
            RDFTermMatch(why, st.why)) {
            // tabulator.log.debug("   Found: "+st)
            if (justOne) return [st];
            results.push(st);
        }
    }
    return results;
}; // statementsMatching


/** remove a particular statement from the bank **/
RDFIndexedFormula.prototype.remove = function (st) {
    //tabulator.log.debug("entering remove w/ st=" + st);
    var subj = st.subject, pred = st.predicate, obj = st.object;
    /*
    if (typeof this.subjectIndex[subj.hashString()] == 'undefined')
        tabulator.log.warn ("statement not in sbj index: "+st);
    if (typeof this.predicateIndex[pred.hashString()] == 'undefined')
        tabulator.log.warn ("statement not in pred index: "+st);
    if (typeof this.objectIndex[obj.hashString()] == 'undefined')
        tabulator.log.warn ("statement not in obj index: " +st);
        */
        
    RDFArrayRemove(this.subjectIndex[subj.hashString()], st);
    RDFArrayRemove(this.predicateIndex[pred.hashString()], st);
    RDFArrayRemove(this.objectIndex[obj.hashString()], st);
    RDFArrayRemove(this.statements, st);
}; //remove

/** remove all statements matching args (within limit) **/
RDFIndexedFormula.prototype.removeMany = function (subj, pred, obj, why, limit) {
    //tabulator.log.debug("entering removeMany w/ subj,pred,obj,why,limit = " + subj +", "+ pred+", " + obj+", " + why+", " + limit);
    var statements = this.statementsMatching (subj, pred, obj, why, false);
    if (limit) statements = statements.slice(0, limit);
    for (var st in statements) this.remove(statements[st]);
}; //removeMany

/** Load a resorce into the store **/

RDFIndexedFormula.prototype.load = function(url) {
    // get the XML
    var xhr = Util.XMLHTTPFactory(); // returns a new XMLHttpRequest, or ActiveX XMLHTTP object
    if (xhr.overrideMimeType) {
    xhr.overrideMimeType("text/xml");
    }

    // Get privileges for cross-domain web access
    if(!isExtension) {
        try {
            Util.enablePrivilege("UniversalXPConnect UniversalBrowserRead")
        } catch(e) {
            throw ("Failed to get privileges: (see http://dig.csail.mit.edu/2005/ajar/ajaw/Privileges.html)" + e)
        }
    }

    xhr.open("GET", url, false);  // Synchronous
    xhr.send("");

    // Get XML DOM Tree

    var nodeTree = xhr.responseXML;
    if (nodeTree === null && xhr.responseText !== null) {
    // Only if the server fails to set Content-Type: text/xml AND xmlhttprequest doesn't have the overrideMimeType method
    nodeTree = (new DOMParser()).parseFromString(xhr.responseText, 'text/xml');
    }

    // Get RDF statements fromm XML

    // must be an XML document node tree
    var parser = new RDFParser(this);
    parser.parse(nodeTree,url);
}


/** Utility**/

/*  @method: copyTo
    @discription: replace @template with @target and add appropriate triples (no triple removed)
                  one-direction replication 
*/ 
RDFIndexedFormula.prototype.copyTo = function(template,target,flags){
    if (!flags) flags=[];
    var statList=this.statementsMatching(template);
    if (flags.indexOf('two-direction')!=-1) 
        statList.concat(this.statementsMatching(undefined,undefined,template));
    for (var i=0;i<statList.length;i++){
        var st=statList[i];
        switch (st.object.termType){
            case 'symbol':
                this.add(target,st.predicate,st.object);
                break;
            case 'literal':
            case 'bnode':
            case 'collection':
                this.add(target,st.predicate,st.object.copy(this));
        }
        if (flags.indexOf('delete')!=-1) this.remove(st);
    }
};
//for the case when you alter this.value (text modified in userinput.js)
RDFLiteral.prototype.copy = function(){ 
    return new RDFLiteral(this.value,this.lang,this.datatype);
};
RDFBlankNode.prototype.copy = function(formula){ //depends on the formula
    var bnodeNew=new RDFBlankNode();
    formula.copyTo(this,bnodeNew);
    return bnodeNew;
}
/**  Full N3 bits  -- placeholders only to allow parsing, no functionality! **/

RDFIndexedFormula.prototype.newUniversal = function(uri) {
    var x = this.sym(uri);
    if (!this._universalVariables) this._universalVariables = [];
    this._universalVariables.push(x);
    return x;
}

RDFIndexedFormula.prototype.newExistential = function(uri) {
    if (!uri) return this.bnode();
    var x = this.sym(uri);
    return this.declareExistential(x);
}

RDFIndexedFormula.prototype.declareExistential = function(x) {
    if (!this._existentialVariables) this._existentialVariables = [];
    this._existentialVariables.push(x);
    return x;
}

RDFIndexedFormula.prototype.formula = function(features) {
    return new RDFIndexedFormula(features);
}

RDFIndexedFormula.prototype.close = function() {
    return this;
}

RDFIndexedFormula.prototype.hashString = RDFIndexedFormula.prototype.toNT;


// ends

// EXPECTING __RDFA_BASE
if (typeof(__RDFA_BASE) == 'undefined')
  __RDFA_BASE = 'http://www.w3.org/2006/07/SWD/RDFa/impl/js/';

// setup the basic
if (typeof(RDFA) == 'undefined') {
    RDFA = new Object();
}

RDFA.reset = function() {
   // reset the triple container
   // 2010-04-07 NH
   // TODO: doesn't work in IE
   RDFA.triplestore = new RDFIndexedFormula();
   RDFA.bnode_counter = 0;
   RDFNextId = 0;
   RDFA.elements_by_subject = new Object();
   RDFA.elements = new Array();
   RDFA.warnings = new Array();
};

RDFA.reset();

//
// dummy callbacks in case they're not defined
//


if (!RDFA.CALLBACK_DONE_LOADING)
    RDFA.CALLBACK_DONE_LOADING = function() {};

if (!RDFA.CALLBACK_DONE_PARSING)
    RDFA.CALLBACK_DONE_PARSING = function() {};

if (!RDFA.CALLBACK_NEW_TRIPLE_WITH_URI_OBJECT)
    RDFA.CALLBACK_NEW_TRIPLE_WITH_URI_OBJECT = function(foo,bar) {};

if (!RDFA.CALLBACK_NEW_TRIPLE_WITH_LITERAL_OBJECT)
    RDFA.CALLBACK_NEW_TRIPLE_WITH_LITERAL_OBJECT = function(foo,bar) {};

if (!RDFA.CALLBACK_NEW_TRIPLE_WITH_SUBJECT)
    RDFA.CALLBACK_NEW_TRIPLE_WITH_SUBJECT = function(foo,bar) {};

// a shallow copy of an array (only the named items)
RDFA.object_copy = function(obj) {
    var the_copy = new Object();

    for (i in obj) {
        the_copy[i] = obj[i];
    }
    
    return the_copy;
};

// iterate through the object keys that are not part of the default object
RDFA.object_each = function(obj,f) {
   var dummy_obj = new Object();
   var dummy_arr = new Array();
   for (var k in obj) {
       if (!dummy_obj[k] && !dummy_arr[k]) {
           f(k);
       }
   }
};

// RDFA CURIE abstraction
RDFA.CURIE = {};

RDFA.CURIE.parse = function(str, namespaces) {
    var position = str.indexOf(':');

    // this will work even if prefix == -1
    var prefix = str.substring(0,position);
    var suffix = str.substring(position+1);

    // 2007-11-26 JT
    // added support for id'd blank nodes generated by CURIEs
    if (prefix == '_') {
      return new RDFBlankNode(suffix);
    }
    else if (namespaces[prefix] == null) {
      // add a warning
      RDFA.warnings.push("prefix " + prefix + " used without declaration");
      return null;
    } else {
      return namespaces[prefix](suffix);
    }
};

// 2007-11-25 JT
// RDFA CURIEorURI abstraction 
RDFA.CURIEorURI = {};

RDFA.CURIEorURI.parse = function(str, namespaces) {
  if (str.substring(0,1) == "[")
    return RDFA.CURIE.parse(str.substring(1,str.length-1), namespaces);
  else
    return str;
}

RDFA.getAbsoluteURI = function(uri) {
  return Util.uri.join(uri, RDFA.BASE);
};

//
// This would be done by editing Node.prototype if all browsers supported it... (-Ben)
//
RDFA.getNodeAttributeValue = function(element, attr) {
    if (element == null)
        return null;

    if (element.getAttribute) {
        if (element.getAttribute(attr))
            return(element.getAttribute(attr));
    }

    if (element.attributes == undefined)
      return null;

      if (!element.attributes[attr])
          return null;

    return element.attributes[attr].value;
};

// 2010-04-07 NH
// namespace-aware node attribute values
RDFA.getNodeAttributeValueNS = function (element, attr, namespace, currentNamespaces) {
    // Safari, Chrome, FF, Opera
    if (element.getAttributeNS) {
        var value = element.getAttributeNS(namespace, attr);
        if ((null !== value) && ('' !== value)) {
            return value;
        }
    }
    
    // IE
    for (n in currentNamespaces) {
        var ns = currentNamespaces[n]('');
        ns = String(ns).replace('<', '').replace('>', '');
        
        if (namespace === ns) {
            var namespacedAttribute = n + ':' + attr;
            return RDFA.getNodeAttributeValue(element, namespacedAttribute);
        }
    }
    
    return null;
}

RDFA.setNodeAttributeValue = function(element, attr, value) {
    if (element == null)
        return;
        
    if (element.setAttribute != undefined) {
        element.setAttribute(attr,value);
        return;
    }
    
    if (element.attributes == undefined)
        element.attributes = new Object();

    element.attributes[attr] = new Object();
    element.attributes[attr].value = value;
};

// utility for processing attribute values
RDFA.each_attr_value = function(attr_values, attr_val_func) {
  if (!attr_values)
    return;
    
  var attr_value_arr = attr_values.split(' ');
  for (var i=0; i<attr_value_arr.length; i++) {
    attr_val_func(attr_value_arr[i]);
  }
};

RDFA.each_prefixed_attr_value = function(attr_values, attr_val_func) {
  return RDFA.each_attr_value(attr_values, function(attr_value) {
    // don't look at non-prefixed values
    if (attr_value.indexOf(':') == -1)
      return;
    
    // call the func
    attr_val_func(attr_value);
  });
};


//
// Support for loading other files
//

RDFA.GRDDL = new Object();

RDFA.GRDDL.CALLBACKS = new Array();

RDFA.GRDDL.DONE_LOADING = function(url) {
    RDFA.GRDDL.CALLBACKS[url]();
};

RDFA.GRDDL.load = function(doc, url, callback)
{
    var s = doc.createElement("script");
    s.type = 'text/javascript';
    s.src = url;

    // set up the callback
    RDFA.GRDDL.CALLBACKS[url] = callback;

    // add it to the document tree, load it up!
    doc.getElementsByTagName('head')[0].appendChild(s);
};

//
// Support of in-place-GRDDL
//

RDFA.GRDDL._profiles = new Array();

RDFA.GRDDL.addProfile = function(js_url) {
    RDFA.GRDDL._profiles[RDFA.GRDDL._profiles.length] = js_url;
};

RDFA.GRDDL.runProfiles = function(doc, callback) {
    // patch for prototype <= 1.4
    if (RDFA.GRDDL._profiles.length == 0) {
      callback();
      return;
    }

    var next_profile = RDFA.GRDDL._profiles.shift();

    if (!next_profile) {
        callback();
        return;
    }

    // load the next profile, and when that is done, run the next profiles
    RDFA.GRDDL.load(doc, next_profile, function() {
        RDFA.GRDDL.runProfiles(doc, callback);
    });
}


//
//
//

RDFA.add_triple = function (base, subject, predicate, object, literal_p, literal_datatype, literal_lang) {
  // 2008-01-18 JT
  // changed the test here, since an empty string is a valid (and meaningful)
  // URI
  if (subject == null) {
    return null;
  }
  
  if (predicate == null) {
    // likely a bad CURIE
    return null;
  }
  
  // if subject is string, then create a URI
  if (typeof(subject) == 'string')
    subject = new RDFSymbol(Util.uri.join(subject, base));

  if (literal_p) {
    object = new RDFLiteral(object, literal_lang, literal_datatype);
  } else {
    if (typeof(object) == 'string')
      object = new RDFSymbol(Util.uri.join(object, base));
  }
  
  return RDFA.triplestore.add(subject, predicate, object, 'RDFa');
};

//
// Process Namespaces
//
RDFA.add_namespaces = function(element, namespaces) {
    if (!namespaces)
        namespaces = {};

    // we only copy the namespaces array if we really need to
    var copied_yet = 0;

    // go through the attributes
    var attributes = element.attributes;
    
    if (!attributes) {
      return namespaces;      
    }
    
    for (var i=0; i<attributes.length; i++) {
        if (attributes[i].name.substring(0,5) == "xmlns") {
            if (!copied_yet) {
                namespaces = RDFA.object_copy(namespaces);
                copied_yet = 1;
            }

            if (attributes[i].name.substring(5,6) != ':') {
              continue;              
            }

            var prefix = attributes[i].name.substring(6);
            var uri = attributes[i].value;
            
            namespaces[prefix] = new Namespace(uri);
        }
    }

    return namespaces;
};

RDFA.associateElementAndSubject = function(element,subject,namespaces) {
   RDFA.elements_by_subject[subject] = element;
   RDFA.elements.push(element);
   
   element._RDFA_SUBJECT = subject;
   element._RDFA_NAMESPACES = RDFA.object_copy(namespaces);
};

RDFA.init_hanging = function(subject, base, namespaces) {
  // initialize the hanging structure
  return {'rels' : [], 'revs' : [], 'subject': subject, 'base': base, 'namespaces': RDFA.object_copy(namespaces), 'bnode': new RDFBlankNode()};
  
}
// Hanging Chains (Mark's idea)
RDFA.add_hanging_rel = function(hanging, subject, rel, base, namespaces) {
  if (!hanging) hanging = RDFA.init_hanging(subject, base, namespaces);
  hanging.rels.push(rel);
  return hanging;
};

RDFA.add_hanging_rev = function(hanging, subject, rev, base, namespaces) {
  if (!hanging) hanging = RDFA.init_hanging(subject, base, namespaces);
  hanging.revs.push(rev);
  return hanging;
};

RDFA.complete_hanging = function(hanging, new_object) {
  // nothing hanging?
  if (hanging == null) {
    return {
      'hanging_result': null,
      'new_subject': null, 
      'new_triple': null
    };
  }

  // link to the bnode if there's no new object
  if (!new_object)
    new_object = hanging.bnode;
  
  var triple;
  // go through the rels
  for (var i=0; i<hanging.rels.length; i++) {
    triple = RDFA.add_triple(hanging.base, hanging.subject, RDFA.CURIE.parse(hanging.rels[i],hanging.namespaces), new_object, false);    
  }

  // go through the revs
  for (var i=0; i<hanging.revs.length; i++) {
    triple = RDFA.add_triple(hanging.base, new_object, RDFA.CURIE.parse(hanging.revs[i],hanging.namespaces), hanging.subject, false);    
  }
  
  return {
    'hanging_result' : null,
    'new_subject' : new_object, 
    'new_triple': triple
  }
};

RDFA.clear_hanging = function(hanging) {
  return null;
}

// this function takes a given element in the DOM tree and:
//
// - determines RDFa statements about this particular element and adds the triples.
// - recurses down the DOM tree appropriately
//
// the namespaces is an associative array where the default namespace is namespaces['']
//
// 2010-04-07 NH
// added graph parameter
RDFA.traverse = function (element, subject, namespaces, lang, base, hanging, graph) {
    // are there namespaces declared
    namespaces = RDFA.add_namespaces(element,namespaces);
    
    // replace the lang if it's non null
    lang = RDFA.getNodeAttributeValue(element, 'xml:lang') || lang;
    
    // 2010-04-07 NH
    // check for named graph attribute
    var newGraph;
    if (undefined !== RDFA.NAMED_GRAPH_ATTRIBUTE) {
        var namedGraphs = RDFA.NAMED_GRAPH_ATTRIBUTE;
        newGraph = RDFA.getNodeAttributeValueNS(element, namedGraphs.attribute, namedGraphs.ns, namespaces) || graph;
    }

    // special case the BODY
    if (element.nodeName == 'body')
      RDFA.associateElementAndSubject(element, RDFA.triplestore.sym(document.location), namespaces);

    // determine the current about
    var element_to_callback = element;

    // fetch some attributes so we don't need to refetch multiple times, and so code is cleaner.
    var attr_names = ['about','src','resource','href','instanceof','typeof','rel','rev','property','content','datatype'];
    var attrs = {};
    for (var i=0; i < attr_names.length; i++)
      attrs[attr_names[i]] = RDFA.getNodeAttributeValue(element, attr_names[i]);

    // do we explicitly override it?
    var explicit_subject = null;
    
    // @src is a left-hand, explicit subject attribute
    if (attrs['src'])
      explicit_subject = attrs['src'];
    
    // @about is a left-hand, explicit subject attribute that overrides @src
    // 2008-01-18 JT
    // since about="" is legal (and meaningful), changed condition here from
    // just if (attrs['about'])
    if (attrs['about'] != null) {
        // 2007-11-25 JT
        // @about is a CURIEorURI
        explicit_subject = RDFA.CURIEorURI.parse(attrs['about'], namespaces);
    } else {
		  // special case the HEAD
		  if (element.nodeName == 'head')
			explicit_subject = RDFA.CURIEorURI.parse("",namespaces);
	  }

    // warn
    if (attrs['instanceof'])
      RDFA.warnings.push("@instanceof has been replaced with @typeof.");
      
    // determine the object
    var explicit_object = null;

    // @href is a right-hand, explicit object attribute
    if (attrs['href'])
      explicit_object = attrs['href'];

    // @resource is a right-hand, explicit object attribute that overrides @href.
    if (attrs['resource']) {
      // 2007-11-25 JT
      // @resource is a CURIEorURI
      explicit_object = RDFA.CURIEorURI.parse(attrs['resource'], namespaces);
    }
    
    // 2010-04-08 NH
    // if the current element defines a new property, the graph must be changed immediately
    if (attrs['rel'] != null || attrs['rev'] != null || attrs['property']) {
        graph = newGraph;
    }
 
    // if there's only an explicit object but no explicit subject and rel/rev
	  // that explicit object becomes effectively an explicit subject
	  if (attrs['rel'] == null && attrs['rev'] == null && explicit_subject == null)
		  explicit_subject = explicit_object;
		  
    // @typeofof replaces instanceof
    if (attrs['typeof']) {
        var rdf_type = RDFA.CURIE.parse(attrs['typeof'], namespaces);

        // determine the subject of typeof, which is either explicitly given, or a new blank node.
        // note that this bnode becomes the new explicit subject of any properties and rels, too, so
        // we are effectively updating explicit_subject 
        // 2008-01-18 JT
        // since the explicit_subject could be an empty URI ("") which will be
        // resolved relative to the base, changed conditional

       	explicit_subject = (explicit_subject == null ?  new RDFBlankNode() : explicit_subject);

        var triple = RDFA.add_triple(base, explicit_subject, RDFA.CURIE.parse("rdf:type",namespaces), rdf_type, false);
        // 2010-04-07 NH
        // added graph parameter
        RDFA.CALLBACK_NEW_TRIPLE_WITH_LITERAL_OBJECT(element_to_callback, triple, graph);
    }

    // Now we handle the hanging stuff
    // Ben: bug fix for @about="" (2008-11-03)
    if (explicit_subject != null) {
        subject = explicit_subject;
        RDFA.associateElementAndSubject(element, RDFA.triplestore.sym(explicit_subject), namespaces);
          
        // complete hanging if necessary
        var hanging_result = RDFA.complete_hanging(hanging, explicit_subject);
        hanging = hanging_result.new_hanging;
        
        // 2010-04-08 NH
        // completed hanging triples would not call callback
        if (hanging_result.new_triple) {
            RDFA.CALLBACK_NEW_TRIPLE_WITH_URI_OBJECT(element_to_callback, hanging_result.new_triple, graph);
        }
    } else {
      // no explicit subject, but we may need to complete the hanging triple if we declare new predicates
      // if we have @rel, @rev, @property, we behave in the same way, by attempting to complete the hanging
      if (attrs['rel'] != null || attrs['rev'] != null || attrs['property']) {
        var hanging_result = RDFA.complete_hanging(hanging);
        hanging = hanging_result.new_hanging;
        if (hanging_result.new_subject) {
          subject = hanging_result.new_subject;
        }
        // 2010-04-08 NH
        // completed hanging triples would not call callback
        if (hanging_result.new_triple) {
            RDFA.CALLBACK_NEW_TRIPLE_WITH_URI_OBJECT(element_to_callback, hanging_result.new_triple, graph);
        }
      }      
    }

    // REL attribute
    RDFA.each_prefixed_attr_value(attrs['rel'], function(rel_value) {
      if (explicit_object != null) {
        var triple = RDFA.add_triple(base, subject, RDFA.CURIE.parse(rel_value,namespaces), explicit_object, false);
        // 2010-04-07 NH
        // added graph parameter
        RDFA.CALLBACK_NEW_TRIPLE_WITH_URI_OBJECT(element_to_callback, triple, graph);
      } else {
        // we hang
        hanging = RDFA.add_hanging_rel(hanging, subject, rel_value, base, namespaces);
      }      
    });

    // REV attribute
    RDFA.each_prefixed_attr_value(attrs['rev'], function(rev_value) {
      if (explicit_object != null) {
        var triple = RDFA.add_triple(base, explicit_object, RDFA.CURIE.parse(rev_value,namespaces), subject, false);
        // 2010-04-07 NH
        // added graph parameter
        RDFA.CALLBACK_NEW_TRIPLE_WITH_URI_OBJECT(element_to_callback, triple, graph);
      } else {
        // we hang
        hanging = RDFA.add_hanging_rev(hanging, subject, rev_value, base, namespaces);
      }      
    });
    
    
    // PROPERTY attribute
    // 2007-11-26 JT
    // property is a space-separated list of CURIEs
    if (attrs['property']) {
        var content = attrs['content'];
        var datatype = attrs['datatype'];

        if (!content) {
          if (datatype != null && RDFA.CURIE.parse(datatype,namespaces) != "<http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral>") {
            content = element.textContent;
            if (datatype == '')
              datatype = null;
          } else {
            content = element.innerHTML;
            if (content != element.textContent)
              datatype = "rdf:XMLLiteral";
          }
        }

        // datatype is actually a CURIE here    
        if (datatype != null) {
          datatype = RDFA.CURIE.parse(datatype, namespaces);
        }
        
        // go through each prop
        RDFA.each_prefixed_attr_value(attrs['property'], function(prop_value) {
          var property = RDFA.CURIE.parse(prop_value, namespaces);
          var triple = RDFA.add_triple(base, subject, property, content, true, datatype, lang);
          // 2010-04-07 NH
          // added graph parameter
          RDFA.CALLBACK_NEW_TRIPLE_WITH_LITERAL_OBJECT(element_to_callback, triple, graph);                    
        });
    }
    
    // if we have an explicit object, we chain
    if (explicit_object != null) {
      RDFA.associateElementAndSubject(element, explicit_object, namespaces);
      subject = explicit_object;
    }

    // recurse down the children
    var children = element.childNodes;
    for (var i=0; i < children.length; i++) {
        // 2010-04-08 NH
        // children get the new graph
        graph = newGraph;
        // 2010-04-07 NH
        // added graph parameter
        RDFA.traverse(children[i], subject, namespaces, lang, base, hanging, graph);
    }
};

RDFA.parse = function(parse_document, base) {
    parse_document = parse_document || document;

	parse_document.RDFA = RDFA;
	RDFA.document = parse_document;
	RDFA.document.__RDFA_BASE = __RDFA_BASE;
    
    // is there a base
	if (typeof(base) != 'undefined')
		RDFA.BASE = base;
	else
		RDFA.BASE = parse_document.location.href;

    // is it overriden by the HTML itself?
    if (parse_document.getElementsByTagName('base').length > 0)
      RDFA.BASE = parse_document.getElementsByTagName('base')[0].href;
    
    // by default, the current namespace for CURIEs is vocab#
    var location = parse_document.location || document.location;
    var default_ns = new Namespace('http://www.w3.org/1999/xhtml/vocab#');
    var namespaces = new Object();

    // set up default namespace
    namespaces[''] = default_ns;
    namespaces['rdf'] = new Namespace('http://www.w3.org/1999/02/22-rdf-syntax-ns#');

    // hGRDDL for XHTML1 special needs
    RDFA.GRDDL.addProfile(__RDFA_BASE + 'xhtml1-hgrddl.js');
    
    // do the profiles, and then traverse
    RDFA.GRDDL.runProfiles(parse_document, function() {
        // 2010-04-07 NH
        // init graph w/ new blank node
        var graph = new RDFBlankNode();
        // 2008-01-18 JT
        // the <base> provides the initial subject if it's given
        // 2010-04-07 NH
        // added graph parameter
        RDFA.traverse(parse_document, RDFA.BASE, namespaces, null, RDFA.BASE, null, graph);

        RDFA.CALLBACK_DONE_PARSING();
    });
};

RDFA.log = function(str) {
    alert(str);
};

RDFA.CALLBACK_DONE_LOADING();