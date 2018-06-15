// CSS Top Menu- By JavaScriptKit.com (http://www.javascriptkit.com)
// Adopted from SuckerFish menu
// For this and over 400+ free scripts, visit JavaScript Kit- http://www.javascriptkit.com/
// Please keep this credit intact

startMenu = function() {
  if (document.all && document.getElementById) {
    cssmenu = document.getElementById("csstopmenu");

    for (i=0; i<cssmenu.childNodes.length; i++) {
      node = cssmenu.childNodes[i];

      if (node.nodeName=="LI") {
        node.onmouseover=function() {
          this.className+=" over";
        }
        node.onmouseout = function() {
          this.className=this.className.replace(" over", "");
        }
      }
    }
  }
}

if (window.attachEvent){
  window.attachEvent("onload", startMenu);
}else{
  window.onload=startMenu;
}
