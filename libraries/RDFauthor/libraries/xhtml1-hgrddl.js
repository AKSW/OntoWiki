
//
// An in-place GRDDL profile
// for XHTML1 hGRDDL
//
// Ben Adida (ben@adida.net)
// 2006-10-08
//

//
// helper functions
//

XH = new Object();

//
// the transformation
//

// FIXME: more of these
XH.SPECIAL_RELS = ['next','prev','home','license','alternate','appendix','bookmark','cite','chapter',
                  'contents','copyright','glossary','help','icon','index','last','meta','p3pv1','role',
                  'section','subsection','start','stylesheet','up'];

XH.RDF_PREFIX = 'rdf';
XH.RDF_URI = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';

XH.XHTML_PREFIX = 'xh';
XH.XHTML_URI = 'http://www.w3.org/1999/xhtml/vocab#';

XH.transform = function(element) {
    // recurse down the children
    // depth-first search here, because we don't need to
    // explore nodes we add along the way, only existing ones.
    var children = element.childNodes;
    for (var i=0; i < children.length; i++) {
	    XH.transform(children[i]);
    }

    // the special RELs
    var new_rels= [];
    var add_namespaces = false;
    
    var rel_value= RDFA.getNodeAttributeValue(element,'rel');

    RDFA.each_attr_value(rel_value, function(rel_val) {
      if (XH.SPECIAL_RELS.indexOf(rel_val) > -1) {
        new_rels.push(XH.XHTML_PREFIX + ':' + rel_val);
        add_namespaces = true;
      }
      
      // keep the existing rel anyways, it might be used for other purposes by other libraries
      new_rels.push(rel_val);
    });

    // REVs
    var new_revs= [];
    var rev_value= RDFA.getNodeAttributeValue(element,'rev');

    RDFA.each_attr_value(rev_value, function(rev_val) {
      if (XH.SPECIAL_RELS.indexOf(rev_val) > -1) {
        new_revs.push(XH.XHTML_PREFIX + ':' + rev_val);
        add_namespaces = true;
      }
      
      // keep the existing rel anyways, it might be used for other purposes by other libraries
      new_revs.push(rev_val);
    });
      
    // set the new @rel
    if (new_rels.length > 0)
      RDFA.setNodeAttributeValue(element,'rel',new_rels.join(" "));

    // set the new @rev
    if (new_revs.length > 0)
      RDFA.setNodeAttributeValue(element,'rev',new_revs.join(" "));
      
    // add namespaces if we added RDFa rels
    if (add_namespaces) {
      RDFA.setNodeAttributeValue(element,'xmlns:' + XH.RDF_PREFIX, XH.RDF_URI);
      RDFA.setNodeAttributeValue(element,'xmlns:' + XH.XHTML_PREFIX, XH.XHTML_URI);      
    }
};

RDFA = document.RDFA;

XH.transform(document.getElementsByTagName('body')[0]);
XH.transform(document.getElementsByTagName('head')[0]);

// added by norman.heino@gmail.com
// freakin' IE cannot handle this
if (!$.browser.msie) {
    RDFA.GRDDL.DONE_LOADING(__RDFA_BASE + 'xhtml1-hgrddl.js');
}