/*
Simple Image Trail script- By JavaScriptKit.com
Visit http://web.archive.org/web/20110527211424/http://www.javascriptkit.com for this script and more
This notice must stay intact
*/
var itemWidth = 250;
var offsetfrommouse=[15,15]; //image x,y offsets from cursor position in pixels. Enter 0,0 for no offset
var displayduration=0; //duration in seconds image should remain visible. 0 for always.
var currentimageheight = 270;    // maximum image size.

//if (document.getElementById || document.all){
//    document.write('<div id="trailimageid">');
//    document.write('</div>');
//}

function gettrailobj(){
    if (document.getElementById)
        return document.getElementById("trailimageid").style
    else if (document.all)
        return document.all.trailimagid.style
}

function gettrailobjnostyle(){
    if (document.getElementById)
        return document.getElementById("trailimageid")
    else if (document.all)
        return document.all.trailimagid
}


function truebody(){
    return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function showtrail(imagename,title,description,showthumb,height, newItemWidth){

    if (newItemWidth != false) {
        itemWidth = newItemWidth;
    }

    if (height > 0){
        currentimageheight = height;
    }

    document.onmousemove=followmouse;

    cameraHTML = '';

    newHTML = '<div>';
    newHTML = newHTML + '<span>' + title + '</span><div class="borderbot"></div>';

    if (showthumb > 0){
        newHTML = newHTML + '<div style="border: 1px solid #000000;" align="center"><img src="' + imagename + '" border="0"></div>';
    }

    newHTML = newHTML + '</div>';

    gettrailobjnostyle().innerHTML = newHTML;

    gettrailobj().visibility="visible";

}


function showhtml(newHtml,height) {

    document.onmousemove=followmouse;
    newHtml = '<div style="border: 1px solid #000000;background-color:#FFFFFF;padding:5px" align="left">' + newHtml + '<div>';
    gettrailobjnostyle().innerHTML = newHtml;
    gettrailobj().visibility="visible";

}

function hidetrail(){
    gettrailobj().visibility="hidden"
    document.onmousemove=""
    gettrailobj().left="-500px"

}

function followmouse(e){

    var xcoord=offsetfrommouse[0]
    var ycoord=offsetfrommouse[1]

    var docwidth=document.all? truebody().scrollLeft+truebody().clientWidth : pageXOffset+window.innerWidth-15
    // var docheight=document.all? Math.min(truebody().scrollHeight, truebody().clientHeight) : Math.min(document.body.offsetHeight, window.innerHeight)
    var docheight=document.all? Math.min(truebody().scrollHeight, truebody().clientHeight) : window.innerHeight
    
    var boh = document.body.offsetHeight;
    var ih = window.innerHeight;

    if (typeof e != "undefined"){
        if (docwidth - e.pageX < itemWidth + 50){
            xcoord = e.pageX - xcoord - itemWidth; // Move to the left side of the cursor
        } else {
            xcoord += e.pageX;
        }
        if ((docheight - e.pageY) < (currentimageheight + 110)){
            ycoord += e.pageY - Math.max(0,(110 + currentimageheight + e.pageY - docheight - truebody().scrollTop));
        } else {
            ycoord += e.pageY;
        }

    } else if (typeof window.event != "undefined"){
        if (docwidth - event.clientX < itemWidth + 50){
            xcoord = event.clientX + truebody().scrollLeft - xcoord - itemWidth; // Move to the left side of the cursor
        } else {
            xcoord += truebody().scrollLeft+event.clientX
        }
        if (docheight - event.clientY < (currentimageheight + 110)){
            ycoord += event.clientY + truebody().scrollTop - Math.max(0,(110 + currentimageheight + event.clientY - docheight));
        } else {
            ycoord += truebody().scrollTop + event.clientY;
        }
    }

    var docwidth=document.all? truebody().scrollLeft+truebody().clientWidth : pageXOffset+window.innerWidth-15
    var docheight=document.all? Math.max(truebody().scrollHeight, truebody().clientHeight) : Math.max(document.body.offsetHeight, window.innerHeight)

    gettrailobj().left=xcoord+"px"
    gettrailobj().top=ycoord+"px"

}