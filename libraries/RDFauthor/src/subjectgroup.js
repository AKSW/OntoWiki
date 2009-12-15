//
// This file is part of the RDFauthor Widget Library
//
// A library that allows to inject editing functionality into an 
// RDFa-annotated HTML web site.
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
// Version: $Id: propertyrow.js 4272 2009-10-10 20:10:01Z norman.heino $
//

//
// RDFauthorSubjectGroup
//
// Object grouping statements about the same subject
// Author: Norman Heino <norman.heino@gmail.com>
//

function RDFauthorSubjectGroup(subject) {
    // the subject for this group
    this.subject = subject;
    
    // property rows for the subject
    this.rows = {};
}

RDFauthorSubjectGroup.prototype.display = function () {
    
};