YUI.add("moodle-tool_certificate-rearrange",function(e,t){var n=function(){n.superclass.constructor.apply(this,[arguments])};e.extend(n,e.Base,{templateid:0,page:[],elements:[],pdfx:0,pdfy:0,pdfwidth:0,pdfheight:0,elementxy:0,pdfleftboundary:0,pdfrightboundary:0,pixelsinmm:3.779527559055,initializer:function(t){this.templateid=t[0],this.page=t[1],this.elements=t[2],this.pdfx=e.one("#pdf").getX(),this.pdfy=e.one("#pdf").getY(),this.pdfwidth=parseFloat(e.one("#pdf").getComputedStyle("width")),this.pdfheight=parseFloat(e.one("#pdf").getComputedStyle("height")),this.pdfleftboundary=this.pdfx,this.page.leftmargin&&(this.pdfleftboundary+=parseInt(this.page.leftmargin*this.pixelsinmm,10)),this.pdfrightboundary=this.pdfx+this.pdfwidth,this.page.rightmargin&&(this.pdfrightboundary-=parseInt(this.page.rightmargin*this.pixelsinmm,10)),this.setpositions(),this.createevents()},setpositions:function(){for(var t in this.elements){var n=this.elements[t],r=this.pdfx+n.posx*this.pixelsinmm,i=this.pdfy+n.posy*this.pixelsinmm,s=parseFloat(e.one("#element-"+n.id).getComputedStyle("width")),o=n.width*this.pixelsinmm;o&&s>o&&(s=o);switch(n.refpoint){case"1":r-=s/2;break;case"2":r=r-s+2}e.one("#element-"+n.id).setX(r),e.one("#element-"+n.id).setY(i)}},createevents:function(){e.one(".savepositionsbtn [type=submit]").on("click",function(e){this.savepositions(e)},this),e.one(".applypositionsbtn [type=submit]").on("click",function(e){this.savepositions(e),e.preventDefault()},this);var t=new e.DD.Delegate({container:"#pdf",nodes:".element"});t.on("drag:start",function(){var e=t.get("currentNode");this.elementxy=e.getXY()},this),t.on("drag:end",function(){var e=t.get("currentNode");this.isoutofbounds(e)&&e.setXY(this.elementxy)},this)},isoutofbounds:function(e){var t=parseFloat(e.getComputedStyle("width")),n=parseFloat(e.getComputedStyle("height")),r=e.getX(),i=r+t,s=e.getY(),o=s+n;return r<this.pdfleftboundary||i>this.pdfrightboundary?!0:s<this.pdfy||o>this.pdfy+this.pdfheight?!0:!1},savepositions:function(t){var n={tid:this.templateid,values:[]};for(var r in this.elements){var i=this.elements[r],s=e.one("#element-"+i.id),o=s.getX()-this.pdfx,u=s.getY()-this.pdfy,a=s.getData("refpoint"),f=parseFloat(s.getComputedStyle("width"));switch(a){case"1":o+=f/2;break;case"2":o+=f}n.values.push({id:i.id,posx:Math.round(parseFloat(o/this.pixelsinmm)),posy:Math.round(parseFloat(u/this.pixelsinmm))})}n.values=JSON.stringify(n.values),e.io(M.cfg.wwwroot+"/mod/customcert/ajax.php",{method:"POST",data:n,on:{failure:function(e,t){this.ajaxfailure(t)},success:function(){var e=t.currentTarget.ancestor("form",!0),n=e.getAttribute("action"),r=e.one("[name=pid]");if(r){var i=r.get("value");window.location=n+"?pid="+i}else{var s=e.one("[name=tid]").get("value");window.location=n+"?tid="+s}}},context:this}),t.preventDefault()},ajaxfailure:function(e){var t={name:e.status+" "+e.statusText,message:e.responseText};return new M.core.exception(t)}}),e.namespace("M.tool_certificate.rearrange").init=function(e,t,r){new n(e,t,r)}},"@VERSION@",{requires:["dd-delegate","dd-drag"]});
