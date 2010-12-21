// REQUIRES Raphael.js to be included first!!
// Works with Raphael 0.7.4
// Also uses the constant GQB.view.conlabel_color defined in GQBView.js

/** 
 * Creates a connection with an arrow pointing at the target box and a label
 * in the middle.
 */
Raphael.fn.connectionWithArrowAndLabel = function (obj1, obj2, line, bg, label) {
    if (obj1.line && obj1.from && obj1.to) {
        line = obj1;
        obj1 = line.from;
        obj2 = line.to;
    }
    
    var bb1 = obj1.getBBox();
    var bb2 = obj2.getBBox();
    var p = [{x: bb1.x + bb1.width / 2, y: bb1.y - 1},
        {x: bb1.x + bb1.width / 2, y: bb1.y + bb1.height + 1},
        {x: bb1.x - 1, y: bb1.y + bb1.height / 2},
        {x: bb1.x + bb1.width + 1, y: bb1.y + bb1.height / 2},
        {x: bb2.x + bb2.width / 2, y: bb2.y - 1},
        {x: bb2.x + bb2.width / 2, y: bb2.y + bb2.height + 1},
        {x: bb2.x - 1, y: bb2.y + bb2.height / 2},
        {x: bb2.x + bb2.width + 1, y: bb2.y + bb2.height / 2}];
    var d = {}, dis = [];
    for (var i = 0; i < 4; i++) {
        for (var j = 4; j < 8; j++) {
            var dx = Math.abs(p[i].x - p[j].x),
                dy = Math.abs(p[i].y - p[j].y);
            if ((i == j - 4) || (((i != 3 && j != 6) || p[i].x < p[j].x) && ((i != 2 && j != 7) || p[i].x > p[j].x) && ((i != 0 && j != 5) || p[i].y > p[j].y) && ((i != 1 && j != 4) || p[i].y < p[j].y))) {
                dis.push(dx + dy);
                d[dis[dis.length - 1]] = [i, j];
            }
        }
    }
    var res = [0,4];
    if (dis.length != 0) {
        var res = d[Math.min.apply(Math, dis)];
    }
    if (!res) res = [0,4];
    var x1 = parseInt(p[res[0]].x),
        y1 = parseInt(p[res[0]].y),
        x4 = parseInt(p[res[1]].x),
        y4 = parseInt(p[res[1]].y),
        dx = Math.max(Math.abs(x1 - x4) / 2, 10),
        dy = Math.max(Math.abs(y1 - y4) / 2, 10),
        x2 = [x1, x1, x1 - dx, x1 + dx][res[0]].toFixed(3),
        y2 = [y1 - dy, y1 + dy, y1, y1][res[0]].toFixed(3),
        x3 = [0, 0, 0, 0, x4, x4, x4 - dx, x4 + dx][res[1]].toFixed(3),
        y3 = [0, 0, 0, 0, y1 + dy, y1 - dy, y4, y4][res[1]].toFixed(3);
    var path = ["M", x1.toFixed(3), y1.toFixed(3), "C", x2, y2, x3, y3, x4.toFixed(3), y4.toFixed(3)].join(",");

    //anchor for the label
    var anchorLabel = {x: ((x1+x4) / 2),y: ((y1+y4) / 2)};

    if (line && line.line) {
        line.bg && line.bg.attr({path: path});
        line.line.attr({path: path});
        //moving the arrow to the new position
        if(obj2.arrow) {
          obj2.arrow.remove();
          var color = typeof line == "string" ? line : "#000";

          //create arrow and set style
          var arrowPathR = ["M",3,6,"L",0,0,10,5,0,10,2,5].join(",");
          var arrowPathL = ["M",17,6,"L",20,0,10,5,20,10,18,5].join(",");
          var arrowPathD = ["M",11,-2,"L",5,-5,10,5,15,-5,10,-3].join(",");
          var arrowPathU = ["M",11,12,"L",5,15,10,5,15,15,10,13].join(",");
          var arrow;
          if (res[1] == 4)
            arrow = this.path({stroke: color,  fill: "black"}, arrowPathD);
          else if (res[1] == 5)
            arrow = this.path({stroke: color,  fill: "black"}, arrowPathU);
          else if (res[1] == 6)
            arrow = this.path({stroke: color,  fill: "black"}, arrowPathR);
          else
            arrow = this.path({stroke: color,  fill: "black"}, arrowPathL);
          //move arrow to end of line
          arrow.translate(x4-10,y4-5);
          
          obj2.arrow = arrow;

          //set to Front to overlap the boxed class,
          //it's not neccessary but it makes the connction looks better
          obj2.arrow.toFront();
          
       }
         //moving the label to the new position
       if(obj2.label) {
          //set the new coordinates
          obj2.label.attr({x: anchorLabel.x,y: anchorLabel.y});
          //set to Front to overlap the boxed class,
          //it's not neccessary but it makes the connction looks better
          obj2.label.toFront();
        }
    } else {
        var color = typeof line == "string" ? line : "#000";

        //create arrow and set style
        var arrowPathR = ["M",3,6,"L",0,0,10,5,0,10,2,5].join(",");
        var arrowPathL = ["M",17,6,"L",20,0,10,5,20,10,18,5].join(",");
        var arrowPathD = ["M",11,-2,"L",5,-5,10,5,15,-5,10,-3].join(",");
        var arrowPathU = ["M",11,12,"L",5,15,10,5,15,15,10,13].join(",");
        var arrow;
        if (res[1] == 4)
          arrow = this.path({stroke: color,  fill: "black"}, arrowPathD);
        else if (res[1] == 5)
          arrow = this.path({stroke: color,  fill: "black"}, arrowPathU);
        else if (res[1] == 6)
          arrow = this.path({stroke: color,  fill: "black"}, arrowPathR);
        else
          arrow = this.path({stroke: color,  fill: "black"}, arrowPathL);
        //move arrow to end of line
        arrow.translate(x4-10,y4-5);
        
        //create label for the connection
        var connectionLabel = this.text(anchorLabel.x, anchorLabel.y, label);
        //set label font size a.s.o.
        connectionLabel.attr({"font-size": 14, fill: GQB.view.conlabel_color});
        
        //create connection obj for easier understanding of source code
        var connection = {
                  bg: bg && bg.split && this.path({stroke: bg.split("|")[0], fill: "none", "stroke-width": bg.split("|")[1] || 3}, path),
                  line: this.path({stroke: color,  fill: "none"}, path),
          from: obj1,
                  to: obj2
              };
          
        //save the created arrowand label in the to-object for easier access
        connection.to.arrow = arrow;
        connection.to.label = connectionLabel;

        return connection;
    }
};