//MooTools, <http://mootools.net>, My Object Oriented (JavaScript) Tools. Copyright (c) 2006-2008 Valerio Proietti, <http://mad4milk.net>, MIT Style License.

var MooTools={version:"1.2.0",build:""};var Native=function(J){J=J||{};var F=J.afterImplement||function(){};var G=J.generics;G=(G!==false);var H=J.legacy;
var E=J.initialize;var B=J.protect;var A=J.name;var C=E||H;C.constructor=Native;C.$family={name:"native"};if(H&&E){C.prototype=H.prototype;}C.prototype.constructor=C;
if(A){var D=A.toLowerCase();C.prototype.$family={name:D};Native.typize(C,D);}var I=function(M,K,N,L){if(!B||L||!M.prototype[K]){M.prototype[K]=N;}if(G){Native.genericize(M,K,B);
}F.call(M,K,N);return M;};C.implement=function(L,K,N){if(typeof L=="string"){return I(this,L,K,N);}for(var M in L){I(this,M,L[M],K);}return this;};C.alias=function(M,K,N){if(typeof M=="string"){M=this.prototype[M];
if(M){I(this,K,M,N);}}else{for(var L in M){this.alias(L,M[L],K);}}return this;};return C;};Native.implement=function(D,C){for(var B=0,A=D.length;B<A;B++){D[B].implement(C);
}};Native.genericize=function(B,C,A){if((!A||!B[C])&&typeof B.prototype[C]=="function"){B[C]=function(){var D=Array.prototype.slice.call(arguments);return B.prototype[C].apply(D.shift(),D);
};}};Native.typize=function(A,B){if(!A.type){A.type=function(C){return($type(C)===B);};}};Native.alias=function(E,B,A,F){for(var D=0,C=E.length;D<C;D++){E[D].alias(B,A,F);
}};(function(B){for(var A in B){Native.typize(B[A],A);}})({"boolean":Boolean,"native":Native,object:Object});(function(B){for(var A in B){new Native({name:A,initialize:B[A],protect:true});
}})({String:String,Function:Function,Number:Number,Array:Array,RegExp:RegExp,Date:Date});(function(B,A){for(var C=A.length;C--;C){Native.genericize(B,A[C],true);
}return arguments.callee;})(Array,["pop","push","reverse","shift","sort","splice","unshift","concat","join","slice","toString","valueOf","indexOf","lastIndexOf"])(String,["charAt","charCodeAt","concat","indexOf","lastIndexOf","match","replace","search","slice","split","substr","substring","toLowerCase","toUpperCase","valueOf"]);
function $chk(A){return !!(A||A===0);}function $clear(A){clearTimeout(A);clearInterval(A);return null;}function $defined(A){return(A!=undefined);}function $empty(){}function $arguments(A){return function(){return arguments[A];
};}function $lambda(A){return(typeof A=="function")?A:function(){return A;};}function $extend(C,A){for(var B in (A||{})){C[B]=A[B];}return C;}function $unlink(C){var B;
switch($type(C)){case"object":B={};for(var E in C){B[E]=$unlink(C[E]);}break;case"hash":B=$unlink(C.getClean());break;case"array":B=[];for(var D=0,A=C.length;
D<A;D++){B[D]=$unlink(C[D]);}break;default:return C;}return B;}function $merge(){var E={};for(var D=0,A=arguments.length;D<A;D++){var B=arguments[D];if($type(B)!="object"){continue;
}for(var C in B){var G=B[C],F=E[C];E[C]=(F&&$type(G)=="object"&&$type(F)=="object")?$merge(F,G):$unlink(G);}}return E;}function $pick(){for(var B=0,A=arguments.length;
B<A;B++){if(arguments[B]!=undefined){return arguments[B];}}return null;}function $random(B,A){return Math.floor(Math.random()*(A-B+1)+B);}function $splat(B){var A=$type(B);
return(A)?((A!="array"&&A!="arguments")?[B]:B):[];}var $time=Date.now||function(){return new Date().getTime();};function $try(){for(var B=0,A=arguments.length;
B<A;B++){try{return arguments[B]();}catch(C){}}return null;}function $type(A){if(A==undefined){return false;}if(A.$family){return(A.$family.name=="number"&&!isFinite(A))?false:A.$family.name;
}if(A.nodeName){switch(A.nodeType){case 1:return"element";case 3:return(/\S/).test(A.nodeValue)?"textnode":"whitespace";}}else{if(typeof A.length=="number"){if(A.callee){return"arguments";
}else{if(A.item){return"collection";}}}}return typeof A;}var Hash=new Native({name:"Hash",initialize:function(A){if($type(A)=="hash"){A=$unlink(A.getClean());
}for(var B in A){this[B]=A[B];}return this;}});Hash.implement({getLength:function(){var B=0;for(var A in this){if(this.hasOwnProperty(A)){B++;}}return B;
},forEach:function(B,C){for(var A in this){if(this.hasOwnProperty(A)){B.call(C,this[A],A,this);}}},getClean:function(){var B={};for(var A in this){if(this.hasOwnProperty(A)){B[A]=this[A];
}}return B;}});Hash.alias("forEach","each");function $H(A){return new Hash(A);}Array.implement({forEach:function(C,D){for(var B=0,A=this.length;B<A;B++){C.call(D,this[B],B,this);
}}});Array.alias("forEach","each");function $A(C){if(C.item){var D=[];for(var B=0,A=C.length;B<A;B++){D[B]=C[B];}return D;}return Array.prototype.slice.call(C);
}function $each(C,B,D){var A=$type(C);((A=="arguments"||A=="collection"||A=="array")?Array:Hash).each(C,B,D);}var Browser=new Hash({Engine:{name:"unknown",version:""},Platform:{name:(navigator.platform.match(/mac|win|linux/i)||["other"])[0].toLowerCase()},Features:{xpath:!!(document.evaluate),air:!!(window.runtime)},Plugins:{}});
if(window.opera){Browser.Engine={name:"presto",version:(document.getElementsByClassName)?950:925};}else{if(window.ActiveXObject){Browser.Engine={name:"trident",version:(window.XMLHttpRequest)?5:4};
}else{if(!navigator.taintEnabled){Browser.Engine={name:"webkit",version:(Browser.Features.xpath)?420:419};}else{if(document.getBoxObjectFor!=null){Browser.Engine={name:"gecko",version:(document.getElementsByClassName)?19:18};
}}}}Browser.Engine[Browser.Engine.name]=Browser.Engine[Browser.Engine.name+Browser.Engine.version]=true;if(window.orientation!=undefined){Browser.Platform.name="ipod";
}Browser.Platform[Browser.Platform.name]=true;Browser.Request=function(){return $try(function(){return new XMLHttpRequest();},function(){return new ActiveXObject("MSXML2.XMLHTTP");
});};Browser.Features.xhr=!!(Browser.Request());Browser.Plugins.Flash=(function(){var A=($try(function(){return navigator.plugins["Shockwave Flash"].description;
},function(){return new ActiveXObject("ShockwaveFlash.ShockwaveFlash").GetVariable("$version");})||"0 r0").match(/\d+/g);return{version:parseInt(A[0]||0+"."+A[1]||0),build:parseInt(A[2]||0)};
})();function $exec(B){if(!B){return B;}if(window.execScript){window.execScript(B);}else{var A=document.createElement("script");A.setAttribute("type","text/javascript");
A.text=B;document.head.appendChild(A);document.head.removeChild(A);}return B;}Native.UID=1;var $uid=(Browser.Engine.trident)?function(A){return(A.uid||(A.uid=[Native.UID++]))[0];
}:function(A){return A.uid||(A.uid=Native.UID++);};var Window=new Native({name:"Window",legacy:(Browser.Engine.trident)?null:window.Window,initialize:function(A){$uid(A);
if(!A.Element){A.Element=$empty;if(Browser.Engine.webkit){A.document.createElement("iframe");}A.Element.prototype=(Browser.Engine.webkit)?window["[[DOMElement.prototype]]"]:{};
}return $extend(A,Window.Prototype);},afterImplement:function(B,A){window[B]=Window.Prototype[B]=A;}});Window.Prototype={$family:{name:"window"}};new Window(window);
var Document=new Native({name:"Document",legacy:(Browser.Engine.trident)?null:window.Document,initialize:function(A){$uid(A);A.head=A.getElementsByTagName("head")[0];
A.html=A.getElementsByTagName("html")[0];A.window=A.defaultView||A.parentWindow;if(Browser.Engine.trident4){$try(function(){A.execCommand("BackgroundImageCache",false,true);
});}return $extend(A,Document.Prototype);},afterImplement:function(B,A){document[B]=Document.Prototype[B]=A;}});Document.Prototype={$family:{name:"document"}};
new Document(document);Array.implement({every:function(C,D){for(var B=0,A=this.length;B<A;B++){if(!C.call(D,this[B],B,this)){return false;}}return true;
},filter:function(D,E){var C=[];for(var B=0,A=this.length;B<A;B++){if(D.call(E,this[B],B,this)){C.push(this[B]);}}return C;},clean:function(){return this.filter($defined);
},indexOf:function(C,D){var A=this.length;for(var B=(D<0)?Math.max(0,A+D):D||0;B<A;B++){if(this[B]===C){return B;}}return -1;},map:function(D,E){var C=[];
for(var B=0,A=this.length;B<A;B++){C[B]=D.call(E,this[B],B,this);}return C;},some:function(C,D){for(var B=0,A=this.length;B<A;B++){if(C.call(D,this[B],B,this)){return true;
}}return false;},associate:function(C){var D={},B=Math.min(this.length,C.length);for(var A=0;A<B;A++){D[C[A]]=this[A];}return D;},link:function(C){var A={};
for(var E=0,B=this.length;E<B;E++){for(var D in C){if(C[D](this[E])){A[D]=this[E];delete C[D];break;}}}return A;},contains:function(A,B){return this.indexOf(A,B)!=-1;
},extend:function(C){for(var B=0,A=C.length;B<A;B++){this.push(C[B]);}return this;},getLast:function(){return(this.length)?this[this.length-1]:null;},getRandom:function(){return(this.length)?this[$random(0,this.length-1)]:null;
},include:function(A){if(!this.contains(A)){this.push(A);}return this;},combine:function(C){for(var B=0,A=C.length;B<A;B++){this.include(C[B]);}return this;
},erase:function(B){for(var A=this.length;A--;A){if(this[A]===B){this.splice(A,1);}}return this;},empty:function(){this.length=0;return this;},flatten:function(){var D=[];
for(var B=0,A=this.length;B<A;B++){var C=$type(this[B]);if(!C){continue;}D=D.concat((C=="array"||C=="collection"||C=="arguments")?Array.flatten(this[B]):this[B]);
}return D;},hexToRgb:function(B){if(this.length!=3){return null;}var A=this.map(function(C){if(C.length==1){C+=C;}return C.toInt(16);});return(B)?A:"rgb("+A+")";
},rgbToHex:function(D){if(this.length<3){return null;}if(this.length==4&&this[3]==0&&!D){return"transparent";}var B=[];for(var A=0;A<3;A++){var C=(this[A]-0).toString(16);
B.push((C.length==1)?"0"+C:C);}return(D)?B:"#"+B.join("");}});Function.implement({extend:function(A){for(var B in A){this[B]=A[B];}return this;},create:function(B){var A=this;
B=B||{};return function(D){var C=B.arguments;C=(C!=undefined)?$splat(C):Array.slice(arguments,(B.event)?1:0);if(B.event){C=[D||window.event].extend(C);
}var E=function(){return A.apply(B.bind||null,C);};if(B.delay){return setTimeout(E,B.delay);}if(B.periodical){return setInterval(E,B.periodical);}if(B.attempt){return $try(E);
}return E();};},pass:function(A,B){return this.create({arguments:A,bind:B});},attempt:function(A,B){return this.create({arguments:A,bind:B,attempt:true})();
},bind:function(B,A){return this.create({bind:B,arguments:A});},bindWithEvent:function(B,A){return this.create({bind:B,event:true,arguments:A});},delay:function(B,C,A){return this.create({delay:B,bind:C,arguments:A})();
},periodical:function(A,C,B){return this.create({periodical:A,bind:C,arguments:B})();},run:function(A,B){return this.apply(B,$splat(A));}});Number.implement({limit:function(B,A){return Math.min(A,Math.max(B,this));
},round:function(A){A=Math.pow(10,A||0);return Math.round(this*A)/A;},times:function(B,C){for(var A=0;A<this;A++){B.call(C,A,this);}},toFloat:function(){return parseFloat(this);
},toInt:function(A){return parseInt(this,A||10);}});Number.alias("times","each");(function(B){var A={};B.each(function(C){if(!Number[C]){A[C]=function(){return Math[C].apply(null,[this].concat($A(arguments)));
};}});Number.implement(A);})(["abs","acos","asin","atan","atan2","ceil","cos","exp","floor","log","max","min","pow","sin","sqrt","tan"]);String.implement({test:function(A,B){return((typeof A=="string")?new RegExp(A,B):A).test(this);
},contains:function(A,B){return(B)?(B+this+B).indexOf(B+A+B)>-1:this.indexOf(A)>-1;},trim:function(){return this.replace(/^\s+|\s+$/g,"");},clean:function(){return this.replace(/\s+/g," ").trim();
},camelCase:function(){return this.replace(/-\D/g,function(A){return A.charAt(1).toUpperCase();});},hyphenate:function(){return this.replace(/[A-Z]/g,function(A){return("-"+A.charAt(0).toLowerCase());
});},capitalize:function(){return this.replace(/\b[a-z]/g,function(A){return A.toUpperCase();});},escapeRegExp:function(){return this.replace(/([-.*+?^${}()|[\]\/\\])/g,"\\$1");
},toInt:function(A){return parseInt(this,A||10);},toFloat:function(){return parseFloat(this);},hexToRgb:function(B){var A=this.match(/^#?(\w{1,2})(\w{1,2})(\w{1,2})$/);
return(A)?A.slice(1).hexToRgb(B):null;},rgbToHex:function(B){var A=this.match(/\d{1,3}/g);return(A)?A.rgbToHex(B):null;},stripScripts:function(B){var A="";
var C=this.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi,function(){A+=arguments[1]+"\n";return"";});if(B===true){$exec(A);}else{if($type(B)=="function"){B(A,C);
}}return C;},substitute:function(A,B){return this.replace(B||(/\\?\{([^}]+)\}/g),function(D,C){if(D.charAt(0)=="\\"){return D.slice(1);}return(A[C]!=undefined)?A[C]:"";
});}});Hash.implement({has:Object.prototype.hasOwnProperty,keyOf:function(B){for(var A in this){if(this.hasOwnProperty(A)&&this[A]===B){return A;}}return null;
},hasValue:function(A){return(Hash.keyOf(this,A)!==null);},extend:function(A){Hash.each(A,function(C,B){Hash.set(this,B,C);},this);return this;},combine:function(A){Hash.each(A,function(C,B){Hash.include(this,B,C);
},this);return this;},erase:function(A){if(this.hasOwnProperty(A)){delete this[A];}return this;},get:function(A){return(this.hasOwnProperty(A))?this[A]:null;
},set:function(A,B){if(!this[A]||this.hasOwnProperty(A)){this[A]=B;}return this;},empty:function(){Hash.each(this,function(B,A){delete this[A];},this);
return this;},include:function(B,C){var A=this[B];if(A==undefined){this[B]=C;}return this;},map:function(B,C){var A=new Hash;Hash.each(this,function(E,D){A.set(D,B.call(C,E,D,this));
},this);return A;},filter:function(B,C){var A=new Hash;Hash.each(this,function(E,D){if(B.call(C,E,D,this)){A.set(D,E);}},this);return A;},every:function(B,C){for(var A in this){if(this.hasOwnProperty(A)&&!B.call(C,this[A],A)){return false;
}}return true;},some:function(B,C){for(var A in this){if(this.hasOwnProperty(A)&&B.call(C,this[A],A)){return true;}}return false;},getKeys:function(){var A=[];
Hash.each(this,function(C,B){A.push(B);});return A;},getValues:function(){var A=[];Hash.each(this,function(B){A.push(B);});return A;},toQueryString:function(A){var B=[];
Hash.each(this,function(F,E){if(A){E=A+"["+E+"]";}var D;switch($type(F)){case"object":D=Hash.toQueryString(F,E);break;case"array":var C={};F.each(function(H,G){C[G]=H;
});D=Hash.toQueryString(C,E);break;default:D=E+"="+encodeURIComponent(F);}if(F!=undefined){B.push(D);}});return B.join("&");}});Hash.alias({keyOf:"indexOf",hasValue:"contains"});
var Event=new Native({name:"Event",initialize:function(A,F){F=F||window;var K=F.document;A=A||F.event;if(A.$extended){return A;}this.$extended=true;var J=A.type;
var G=A.target||A.srcElement;while(G&&G.nodeType==3){G=G.parentNode;}if(J.test(/key/)){var B=A.which||A.keyCode;var M=Event.Keys.keyOf(B);if(J=="keydown"){var D=B-111;
if(D>0&&D<13){M="f"+D;}}M=M||String.fromCharCode(B).toLowerCase();}else{if(J.match(/(click|mouse|menu)/i)){K=(!K.compatMode||K.compatMode=="CSS1Compat")?K.html:K.body;
var I={x:A.pageX||A.clientX+K.scrollLeft,y:A.pageY||A.clientY+K.scrollTop};var C={x:(A.pageX)?A.pageX-F.pageXOffset:A.clientX,y:(A.pageY)?A.pageY-F.pageYOffset:A.clientY};
if(J.match(/DOMMouseScroll|mousewheel/)){var H=(A.wheelDelta)?A.wheelDelta/120:-(A.detail||0)/3;}var E=(A.which==3)||(A.button==2);var L=null;if(J.match(/over|out/)){switch(J){case"mouseover":L=A.relatedTarget||A.fromElement;
break;case"mouseout":L=A.relatedTarget||A.toElement;}if(!(function(){while(L&&L.nodeType==3){L=L.parentNode;}return true;}).create({attempt:Browser.Engine.gecko})()){L=false;
}}}}return $extend(this,{event:A,type:J,page:I,client:C,rightClick:E,wheel:H,relatedTarget:L,target:G,code:B,key:M,shift:A.shiftKey,control:A.ctrlKey,alt:A.altKey,meta:A.metaKey});
}});Event.Keys=new Hash({enter:13,up:38,down:40,left:37,right:39,esc:27,space:32,backspace:8,tab:9,"delete":46});Event.implement({stop:function(){return this.stopPropagation().preventDefault();
},stopPropagation:function(){if(this.event.stopPropagation){this.event.stopPropagation();}else{this.event.cancelBubble=true;}return this;},preventDefault:function(){if(this.event.preventDefault){this.event.preventDefault();
}else{this.event.returnValue=false;}return this;}});var Class=new Native({name:"Class",initialize:function(B){B=B||{};var A=function(E){for(var D in this){this[D]=$unlink(this[D]);
}for(var F in Class.Mutators){if(!this[F]){continue;}Class.Mutators[F](this,this[F]);delete this[F];}this.constructor=A;if(E===$empty){return this;}var C=(this.initialize)?this.initialize.apply(this,arguments):this;
if(this.options&&this.options.initialize){this.options.initialize.call(this);}return C;};$extend(A,this);A.constructor=Class;A.prototype=B;return A;}});
Class.implement({implement:function(){Class.Mutators.Implements(this.prototype,Array.slice(arguments));return this;}});Class.Mutators={Implements:function(A,B){$splat(B).each(function(C){$extend(A,($type(C)=="class")?new C($empty):C);
});},Extends:function(self,klass){var instance=new klass($empty);delete instance.parent;delete instance.parentOf;for(var key in instance){var current=self[key],previous=instance[key];
if(current==undefined){self[key]=previous;continue;}var ctype=$type(current),ptype=$type(previous);if(ctype!=ptype){continue;}switch(ctype){case"function":if(!arguments.callee.caller){self[key]=eval("("+String(current).replace(/\bthis\.parent\(\s*(\))?/g,function(full,close){return"arguments.callee._parent_.call(this"+(close||", ");
})+")");}self[key]._parent_=previous;break;case"object":self[key]=$merge(previous,current);}}self.parent=function(){return arguments.callee.caller._parent_.apply(this,arguments);
};self.parentOf=function(descendant){return descendant._parent_.apply(this,Array.slice(arguments,1));};}};var Chain=new Class({chain:function(){this.$chain=(this.$chain||[]).extend(arguments);
return this;},callChain:function(){return(this.$chain&&this.$chain.length)?this.$chain.shift().apply(this,arguments):false;},clearChain:function(){if(this.$chain){this.$chain.empty();
}return this;}});var Events=new Class({addEvent:function(C,B,A){C=Events.removeOn(C);if(B!=$empty){this.$events=this.$events||{};this.$events[C]=this.$events[C]||[];
this.$events[C].include(B);if(A){B.internal=true;}}return this;},addEvents:function(A){for(var B in A){this.addEvent(B,A[B]);}return this;},fireEvent:function(C,B,A){C=Events.removeOn(C);
if(!this.$events||!this.$events[C]){return this;}this.$events[C].each(function(D){D.create({bind:this,delay:A,"arguments":B})();},this);return this;},removeEvent:function(B,A){B=Events.removeOn(B);
if(!this.$events||!this.$events[B]){return this;}if(!A.internal){this.$events[B].erase(A);}return this;},removeEvents:function(C){for(var D in this.$events){if(C&&C!=D){continue;
}var B=this.$events[D];for(var A=B.length;A--;A){this.removeEvent(D,B[A]);}}return this;}});Events.removeOn=function(A){return A.replace(/^on([A-Z])/,function(B,C){return C.toLowerCase();
});};var Options=new Class({setOptions:function(){this.options=$merge.run([this.options].extend(arguments));if(!this.addEvent){return this;}for(var A in this.options){if($type(this.options[A])!="function"||!(/^on[A-Z]/).test(A)){continue;
}this.addEvent(A,this.options[A]);delete this.options[A];}return this;}});Document.implement({newElement:function(A,B){if(Browser.Engine.trident&&B){["name","type","checked"].each(function(C){if(!B[C]){return ;
}A+=" "+C+'="'+B[C]+'"';if(C!="checked"){delete B[C];}});A="<"+A+">";}return $.element(this.createElement(A)).set(B);},newTextNode:function(A){return this.createTextNode(A);
},getDocument:function(){return this;},getWindow:function(){return this.defaultView||this.parentWindow;},purge:function(){var C=this.getElementsByTagName("*");
for(var B=0,A=C.length;B<A;B++){Browser.freeMem(C[B]);}}});var Element=new Native({name:"Element",legacy:window.Element,initialize:function(A,B){var C=Element.Constructors.get(A);
if(C){return C(B);}if(typeof A=="string"){return document.newElement(A,B);}return $(A).set(B);},afterImplement:function(A,B){if(!Array[A]){Elements.implement(A,Elements.multi(A));
}Element.Prototype[A]=B;}});Element.Prototype={$family:{name:"element"}};Element.Constructors=new Hash;var IFrame=new Native({name:"IFrame",generics:false,initialize:function(){var E=Array.link(arguments,{properties:Object.type,iframe:$defined});
var C=E.properties||{};var B=$(E.iframe)||false;var D=C.onload||$empty;delete C.onload;C.id=C.name=$pick(C.id,C.name,B.id,B.name,"IFrame_"+$time());B=new Element(B||"iframe",C);
var A=function(){var F=$try(function(){return B.contentWindow.location.host;});if(F&&F==window.location.host){var H=new Window(B.contentWindow);var G=new Document(B.contentWindow.document);
$extend(H.Element.prototype,Element.Prototype);}D.call(B.contentWindow,B.contentWindow.document);};(!window.frames[C.id])?B.addListener("load",A):A();return B;
}});var Elements=new Native({initialize:function(F,B){B=$extend({ddup:true,cash:true},B);F=F||[];if(B.ddup||B.cash){var G={},E=[];for(var C=0,A=F.length;
C<A;C++){var D=$.element(F[C],!B.cash);if(B.ddup){if(G[D.uid]){continue;}G[D.uid]=true;}E.push(D);}F=E;}return(B.cash)?$extend(F,this):F;}});Elements.implement({filter:function(A,B){if(!A){return this;
}return new Elements(Array.filter(this,(typeof A=="string")?function(C){return C.match(A);}:A,B));}});Elements.multi=function(A){return function(){var B=[];
var F=true;for(var D=0,C=this.length;D<C;D++){var E=this[D][A].apply(this[D],arguments);B.push(E);if(F){F=($type(E)=="element");}}return(F)?new Elements(B):B;
};};Window.implement({$:function(B,C){if(B&&B.$family&&B.uid){return B;}var A=$type(B);return($[A])?$[A](B,C,this.document):null;},$$:function(A){if(arguments.length==1&&typeof A=="string"){return this.document.getElements(A);
}var F=[];var C=Array.flatten(arguments);for(var D=0,B=C.length;D<B;D++){var E=C[D];switch($type(E)){case"element":E=[E];break;case"string":E=this.document.getElements(E,true);
break;default:E=false;}if(E){F.extend(E);}}return new Elements(F);},getDocument:function(){return this.document;},getWindow:function(){return this;}});
$.string=function(C,B,A){C=A.getElementById(C);return(C)?$.element(C,B):null;};$.element=function(A,D){$uid(A);if(!D&&!A.$family&&!(/^object|embed$/i).test(A.tagName)){var B=Element.Prototype;
for(var C in B){A[C]=B[C];}}return A;};$.object=function(B,C,A){if(B.toElement){return $.element(B.toElement(A),C);}return null;};$.textnode=$.whitespace=$.window=$.document=$arguments(0);
Native.implement([Element,Document],{getElement:function(A,B){return $(this.getElements(A,true)[0]||null,B);},getElements:function(A,D){A=A.split(",");
var C=[];var B=(A.length>1);A.each(function(E){var F=this.getElementsByTagName(E.trim());(B)?C.extend(F):C=F;},this);return new Elements(C,{ddup:B,cash:!D});
}});Element.Storage={get:function(A){return(this[A]||(this[A]={}));}};Element.Inserters=new Hash({before:function(B,A){if(A.parentNode){A.parentNode.insertBefore(B,A);
}},after:function(B,A){if(!A.parentNode){return ;}var C=A.nextSibling;(C)?A.parentNode.insertBefore(B,C):A.parentNode.appendChild(B);},bottom:function(B,A){A.appendChild(B);
},top:function(B,A){var C=A.firstChild;(C)?A.insertBefore(B,C):A.appendChild(B);}});Element.Inserters.inside=Element.Inserters.bottom;Element.Inserters.each(function(C,B){var A=B.capitalize();
Element.implement("inject"+A,function(D){C(this,$(D,true));return this;});Element.implement("grab"+A,function(D){C($(D,true),this);return this;});});Element.implement({getDocument:function(){return this.ownerDocument;
},getWindow:function(){return this.ownerDocument.getWindow();},getElementById:function(D,C){var B=this.ownerDocument.getElementById(D);if(!B){return null;
}for(var A=B.parentNode;A!=this;A=A.parentNode){if(!A){return null;}}return $.element(B,C);},set:function(D,B){switch($type(D)){case"object":for(var C in D){this.set(C,D[C]);
}break;case"string":var A=Element.Properties.get(D);(A&&A.set)?A.set.apply(this,Array.slice(arguments,1)):this.setProperty(D,B);}return this;},get:function(B){var A=Element.Properties.get(B);
return(A&&A.get)?A.get.apply(this,Array.slice(arguments,1)):this.getProperty(B);},erase:function(B){var A=Element.Properties.get(B);(A&&A.erase)?A.erase.apply(this,Array.slice(arguments,1)):this.removeProperty(B);
return this;},match:function(A){return(!A||Element.get(this,"tag")==A);},inject:function(B,A){Element.Inserters.get(A||"bottom")(this,$(B,true));return this;
},wraps:function(B,A){B=$(B,true);return this.replaces(B).grab(B,A);},grab:function(B,A){Element.Inserters.get(A||"bottom")($(B,true),this);return this;
},appendText:function(B,A){return this.grab(this.getDocument().newTextNode(B),A);},adopt:function(){Array.flatten(arguments).each(function(A){A=$(A,true);
if(A){this.appendChild(A);}},this);return this;},dispose:function(){return(this.parentNode)?this.parentNode.removeChild(this):this;},clone:function(D,C){switch($type(this)){case"element":var H={};
for(var G=0,E=this.attributes.length;G<E;G++){var B=this.attributes[G],L=B.nodeName.toLowerCase();if(Browser.Engine.trident&&(/input/i).test(this.tagName)&&(/width|height/).test(L)){continue;
}var K=(L=="style"&&this.style)?this.style.cssText:B.nodeValue;if(!$chk(K)||L=="uid"||(L=="id"&&!C)){continue;}if(K!="inherit"&&["string","number"].contains($type(K))){H[L]=K;
}}var J=new Element(this.nodeName.toLowerCase(),H);if(D!==false){for(var I=0,F=this.childNodes.length;I<F;I++){var A=Element.clone(this.childNodes[I],true,C);
if(A){J.grab(A);}}}return J;case"textnode":return document.newTextNode(this.nodeValue);}return null;},replaces:function(A){A=$(A,true);A.parentNode.replaceChild(this,A);
return this;},hasClass:function(A){return this.className.contains(A," ");},addClass:function(A){if(!this.hasClass(A)){this.className=(this.className+" "+A).clean();
}return this;},removeClass:function(A){this.className=this.className.replace(new RegExp("(^|\\s)"+A+"(?:\\s|$)"),"$1").clean();return this;},toggleClass:function(A){return this.hasClass(A)?this.removeClass(A):this.addClass(A);
},getComputedStyle:function(B){if(this.currentStyle){return this.currentStyle[B.camelCase()];}var A=this.getWindow().getComputedStyle(this,null);return(A)?A.getPropertyValue([B.hyphenate()]):null;
},empty:function(){$A(this.childNodes).each(function(A){Browser.freeMem(A);Element.empty(A);Element.dispose(A);},this);return this;},destroy:function(){Browser.freeMem(this.empty().dispose());
return null;},getSelected:function(){return new Elements($A(this.options).filter(function(A){return A.selected;}));},toQueryString:function(){var A=[];
this.getElements("input, select, textarea").each(function(B){if(!B.name||B.disabled){return ;}var C=(B.tagName.toLowerCase()=="select")?Element.getSelected(B).map(function(D){return D.value;
}):((B.type=="radio"||B.type=="checkbox")&&!B.checked)?null:B.value;$splat(C).each(function(D){if(D){A.push(B.name+"="+encodeURIComponent(D));}});});return A.join("&");
},getProperty:function(C){var B=Element.Attributes,A=B.Props[C];var D=(A)?this[A]:this.getAttribute(C,2);return(B.Bools[C])?!!D:(A)?D:D||null;},getProperties:function(){var A=$A(arguments);
return A.map(function(B){return this.getProperty(B);},this).associate(A);},setProperty:function(D,E){var C=Element.Attributes,B=C.Props[D],A=$defined(E);
if(B&&C.Bools[D]){E=(E||!A)?true:false;}else{if(!A){return this.removeProperty(D);}}(B)?this[B]=E:this.setAttribute(D,E);return this;},setProperties:function(A){for(var B in A){this.setProperty(B,A[B]);
}return this;},removeProperty:function(D){var C=Element.Attributes,B=C.Props[D],A=(B&&C.Bools[D]);(B)?this[B]=(A)?false:"":this.removeAttribute(D);return this;
},removeProperties:function(){Array.each(arguments,this.removeProperty,this);return this;}});(function(){var A=function(D,B,I,C,F,H){var E=D[I||B];var G=[];
while(E){if(E.nodeType==1&&(!C||Element.match(E,C))){G.push(E);if(!F){break;}}E=E[B];}return(F)?new Elements(G,{ddup:false,cash:!H}):$(G[0],H);};Element.implement({getPrevious:function(B,C){return A(this,"previousSibling",null,B,false,C);
},getAllPrevious:function(B,C){return A(this,"previousSibling",null,B,true,C);},getNext:function(B,C){return A(this,"nextSibling",null,B,false,C);},getAllNext:function(B,C){return A(this,"nextSibling",null,B,true,C);
},getFirst:function(B,C){return A(this,"nextSibling","firstChild",B,false,C);},getLast:function(B,C){return A(this,"previousSibling","lastChild",B,false,C);
},getParent:function(B,C){return A(this,"parentNode",null,B,false,C);},getParents:function(B,C){return A(this,"parentNode",null,B,true,C);},getChildren:function(B,C){return A(this,"nextSibling","firstChild",B,true,C);
},hasChild:function(B){B=$(B,true);return(!!B&&$A(this.getElementsByTagName(B.tagName)).contains(B));}});})();Element.Properties=new Hash;Element.Properties.style={set:function(A){this.style.cssText=A;
},get:function(){return this.style.cssText;},erase:function(){this.style.cssText="";}};Element.Properties.tag={get:function(){return this.tagName.toLowerCase();
}};Element.Properties.href={get:function(){return(!this.href)?null:this.href.replace(new RegExp("^"+document.location.protocol+"//"+document.location.host),"");
}};Element.Properties.html={set:function(){return this.innerHTML=Array.flatten(arguments).join("");}};Native.implement([Element,Window,Document],{addListener:function(B,A){if(this.addEventListener){this.addEventListener(B,A,false);
}else{this.attachEvent("on"+B,A);}return this;},removeListener:function(B,A){if(this.removeEventListener){this.removeEventListener(B,A,false);}else{this.detachEvent("on"+B,A);
}return this;},retrieve:function(B,A){var D=Element.Storage.get(this.uid);var C=D[B];if($defined(A)&&!$defined(C)){C=D[B]=A;}return $pick(C);},store:function(B,A){var C=Element.Storage.get(this.uid);
C[B]=A;return this;},eliminate:function(A){var B=Element.Storage.get(this.uid);delete B[A];return this;}});Element.Attributes=new Hash({Props:{html:"innerHTML","class":"className","for":"htmlFor",text:(Browser.Engine.trident)?"innerText":"textContent"},Bools:["compact","nowrap","ismap","declare","noshade","checked","disabled","readonly","multiple","selected","noresize","defer"],Camels:["value","accessKey","cellPadding","cellSpacing","colSpan","frameBorder","maxLength","readOnly","rowSpan","tabIndex","useMap"]});
Browser.freeMem=function(A){if(!A){return ;}if(Browser.Engine.trident&&(/object/i).test(A.tagName)){for(var B in A){if(typeof A[B]=="function"){A[B]=$empty;
}}Element.dispose(A);}if(A.uid&&A.removeEvents){A.removeEvents();}};(function(B){var C=B.Bools,A=B.Camels;B.Bools=C=C.associate(C);Hash.extend(Hash.combine(B.Props,C),A.associate(A.map(function(D){return D.toLowerCase();
})));B.erase("Camels");})(Element.Attributes);window.addListener("unload",function(){window.removeListener("unload",arguments.callee);document.purge();
if(Browser.Engine.trident){CollectGarbage();}});Element.Properties.events={set:function(A){this.addEvents(A);}};Native.implement([Element,Window,Document],{addEvent:function(E,G){var H=this.retrieve("events",{});
H[E]=H[E]||{keys:[],values:[]};if(H[E].keys.contains(G)){return this;}H[E].keys.push(G);var F=E,A=Element.Events.get(E),C=G,I=this;if(A){if(A.onAdd){A.onAdd.call(this,G);
}if(A.condition){C=function(J){if(A.condition.call(this,J)){return G.call(this,J);}return false;};}F=A.base||F;}var D=function(){return G.call(I);};var B=Element.NativeEvents[F]||0;
if(B){if(B==2){D=function(J){J=new Event(J,I.getWindow());if(C.call(I,J)===false){J.stop();}};}this.addListener(F,D);}H[E].values.push(D);return this;},removeEvent:function(D,C){var B=this.retrieve("events");
if(!B||!B[D]){return this;}var G=B[D].keys.indexOf(C);if(G==-1){return this;}var A=B[D].keys.splice(G,1)[0];var F=B[D].values.splice(G,1)[0];var E=Element.Events.get(D);
if(E){if(E.onRemove){E.onRemove.call(this,C);}D=E.base||D;}return(Element.NativeEvents[D])?this.removeListener(D,F):this;},addEvents:function(A){for(var B in A){this.addEvent(B,A[B]);
}return this;},removeEvents:function(B){var A=this.retrieve("events");if(!A){return this;}if(!B){for(var C in A){this.removeEvents(C);}A=null;}else{if(A[B]){while(A[B].keys[0]){this.removeEvent(B,A[B].keys[0]);
}A[B]=null;}}return this;},fireEvent:function(D,B,A){var C=this.retrieve("events");if(!C||!C[D]){return this;}C[D].keys.each(function(E){E.create({bind:this,delay:A,"arguments":B})();
},this);return this;},cloneEvents:function(D,A){D=$(D);var C=D.retrieve("events");if(!C){return this;}if(!A){for(var B in C){this.cloneEvents(D,B);}}else{if(C[A]){C[A].keys.each(function(E){this.addEvent(A,E);
},this);}}return this;}});Element.NativeEvents={click:2,dblclick:2,mouseup:2,mousedown:2,contextmenu:2,mousewheel:2,DOMMouseScroll:2,mouseover:2,mouseout:2,mousemove:2,selectstart:2,selectend:2,keydown:2,keypress:2,keyup:2,focus:2,blur:2,change:2,reset:2,select:2,submit:2,load:1,unload:1,beforeunload:2,resize:1,move:1,DOMContentLoaded:1,readystatechange:1,error:1,abort:1,scroll:1};
(function(){var A=function(B){var C=B.relatedTarget;if(C==undefined){return true;}if(C===false){return false;}return($type(this)!="document"&&C!=this&&C.prefix!="xul"&&!this.hasChild(C));
};Element.Events=new Hash({mouseenter:{base:"mouseover",condition:A},mouseleave:{base:"mouseout",condition:A},mousewheel:{base:(Browser.Engine.gecko)?"DOMMouseScroll":"mousewheel"}});
})();Native.implement([Document,Element],{getElements:function(H,G){H=H.split(",");var C,E={};for(var D=0,B=H.length;D<B;D++){var A=H[D],F=Selectors.Utils.search(this,A,E);
if(D!=0&&F.item){F=$A(F);}C=(D==0)?F:(C.item)?$A(C).concat(F):C.concat(F);}return new Elements(C,{ddup:(H.length>1),cash:!G});}});Element.implement({match:function(B){if(!B){return true;
}var D=Selectors.Utils.parseTagAndID(B);var A=D[0],E=D[1];if(!Selectors.Filters.byID(this,E)||!Selectors.Filters.byTag(this,A)){return false;}var C=Selectors.Utils.parseSelector(B);
return(C)?Selectors.Utils.filter(this,C,{}):true;}});var Selectors={Cache:{nth:{},parsed:{}}};Selectors.RegExps={id:(/#([\w-]+)/),tag:(/^(\w+|\*)/),quick:(/^(\w+|\*)$/),splitter:(/\s*([+>~\s])\s*([a-zA-Z#.*:\[])/g),combined:(/\.([\w-]+)|\[(\w+)(?:([!*^$~|]?=)["']?(.*?)["']?)?\]|:([\w-]+)(?:\(["']?(.*?)?["']?\)|$)/g)};
Selectors.Utils={chk:function(B,C){if(!C){return true;}var A=$uid(B);if(!C[A]){return C[A]=true;}return false;},parseNthArgument:function(F){if(Selectors.Cache.nth[F]){return Selectors.Cache.nth[F];
}var C=F.match(/^([+-]?\d*)?([a-z]+)?([+-]?\d*)?$/);if(!C){return false;}var E=parseInt(C[1]);var B=(E||E===0)?E:1;var D=C[2]||false;var A=parseInt(C[3])||0;
if(B!=0){A--;while(A<1){A+=B;}while(A>=B){A-=B;}}else{B=A;D="index";}switch(D){case"n":C={a:B,b:A,special:"n"};break;case"odd":C={a:2,b:0,special:"n"};
break;case"even":C={a:2,b:1,special:"n"};break;case"first":C={a:0,special:"index"};break;case"last":C={special:"last-child"};break;case"only":C={special:"only-child"};
break;default:C={a:(B-1),special:"index"};}return Selectors.Cache.nth[F]=C;},parseSelector:function(E){if(Selectors.Cache.parsed[E]){return Selectors.Cache.parsed[E];
}var D,H={classes:[],pseudos:[],attributes:[]};while((D=Selectors.RegExps.combined.exec(E))){var I=D[1],G=D[2],F=D[3],B=D[4],C=D[5],J=D[6];if(I){H.classes.push(I);
}else{if(C){var A=Selectors.Pseudo.get(C);if(A){H.pseudos.push({parser:A,argument:J});}else{H.attributes.push({name:C,operator:"=",value:J});}}else{if(G){H.attributes.push({name:G,operator:F,value:B});
}}}}if(!H.classes.length){delete H.classes;}if(!H.attributes.length){delete H.attributes;}if(!H.pseudos.length){delete H.pseudos;}if(!H.classes&&!H.attributes&&!H.pseudos){H=null;
}return Selectors.Cache.parsed[E]=H;},parseTagAndID:function(B){var A=B.match(Selectors.RegExps.tag);var C=B.match(Selectors.RegExps.id);return[(A)?A[1]:"*",(C)?C[1]:false];
},filter:function(F,C,E){var D;if(C.classes){for(D=C.classes.length;D--;D){var G=C.classes[D];if(!Selectors.Filters.byClass(F,G)){return false;}}}if(C.attributes){for(D=C.attributes.length;
D--;D){var B=C.attributes[D];if(!Selectors.Filters.byAttribute(F,B.name,B.operator,B.value)){return false;}}}if(C.pseudos){for(D=C.pseudos.length;D--;D){var A=C.pseudos[D];
if(!Selectors.Filters.byPseudo(F,A.parser,A.argument,E)){return false;}}}return true;},getByTagAndID:function(B,A,D){if(D){var C=(B.getElementById)?B.getElementById(D,true):Element.getElementById(B,D,true);
return(C&&Selectors.Filters.byTag(C,A))?[C]:[];}else{return B.getElementsByTagName(A);}},search:function(J,I,O){var B=[];var C=I.trim().replace(Selectors.RegExps.splitter,function(Z,Y,X){B.push(Y);
return":)"+X;}).split(":)");var K,F,E,V;for(var U=0,Q=C.length;U<Q;U++){var T=C[U];if(U==0&&Selectors.RegExps.quick.test(T)){K=J.getElementsByTagName(T);
continue;}var A=B[U-1];var L=Selectors.Utils.parseTagAndID(T);var W=L[0],M=L[1];if(U==0){K=Selectors.Utils.getByTagAndID(J,W,M);}else{var D={},H=[];for(var S=0,R=K.length;
S<R;S++){H=Selectors.Getters[A](H,K[S],W,M,D);}K=H;}var G=Selectors.Utils.parseSelector(T);if(G){E=[];for(var P=0,N=K.length;P<N;P++){V=K[P];if(Selectors.Utils.filter(V,G,O)){E.push(V);
}}K=E;}}return K;}};Selectors.Getters={" ":function(H,G,I,A,E){var D=Selectors.Utils.getByTagAndID(G,I,A);for(var C=0,B=D.length;C<B;C++){var F=D[C];if(Selectors.Utils.chk(F,E)){H.push(F);
}}return H;},">":function(H,G,I,A,F){var C=Selectors.Utils.getByTagAndID(G,I,A);for(var E=0,D=C.length;E<D;E++){var B=C[E];if(B.parentNode==G&&Selectors.Utils.chk(B,F)){H.push(B);
}}return H;},"+":function(C,B,A,E,D){while((B=B.nextSibling)){if(B.nodeType==1){if(Selectors.Utils.chk(B,D)&&Selectors.Filters.byTag(B,A)&&Selectors.Filters.byID(B,E)){C.push(B);
}break;}}return C;},"~":function(C,B,A,E,D){while((B=B.nextSibling)){if(B.nodeType==1){if(!Selectors.Utils.chk(B,D)){break;}if(Selectors.Filters.byTag(B,A)&&Selectors.Filters.byID(B,E)){C.push(B);
}}}return C;}};Selectors.Filters={byTag:function(B,A){return(A=="*"||(B.tagName&&B.tagName.toLowerCase()==A));},byID:function(A,B){return(!B||(A.id&&A.id==B));
},byClass:function(B,A){return(B.className&&B.className.contains(A," "));},byPseudo:function(A,D,C,B){return D.call(A,C,B);},byAttribute:function(C,D,B,E){var A=Element.prototype.getProperty.call(C,D);
if(!A){return false;}if(!B||E==undefined){return true;}switch(B){case"=":return(A==E);case"*=":return(A.contains(E));case"^=":return(A.substr(0,E.length)==E);
case"$=":return(A.substr(A.length-E.length)==E);case"!=":return(A!=E);case"~=":return A.contains(E," ");case"|=":return A.contains(E,"-");}return false;
}};Selectors.Pseudo=new Hash({empty:function(){return !(this.innerText||this.textContent||"").length;},not:function(A){return !Element.match(this,A);},contains:function(A){return(this.innerText||this.textContent||"").contains(A);
},"first-child":function(){return Selectors.Pseudo.index.call(this,0);},"last-child":function(){var A=this;while((A=A.nextSibling)){if(A.nodeType==1){return false;
}}return true;},"only-child":function(){var B=this;while((B=B.previousSibling)){if(B.nodeType==1){return false;}}var A=this;while((A=A.nextSibling)){if(A.nodeType==1){return false;
}}return true;},"nth-child":function(G,E){G=(G==undefined)?"n":G;var C=Selectors.Utils.parseNthArgument(G);if(C.special!="n"){return Selectors.Pseudo[C.special].call(this,C.a,E);
}var F=0;E.positions=E.positions||{};var D=$uid(this);if(!E.positions[D]){var B=this;while((B=B.previousSibling)){if(B.nodeType!=1){continue;}F++;var A=E.positions[$uid(B)];
if(A!=undefined){F=A+F;break;}}E.positions[D]=F;}return(E.positions[D]%C.a==C.b);},index:function(A){var B=this,C=0;while((B=B.previousSibling)){if(B.nodeType==1&&++C>A){return false;
}}return(C==A);},even:function(B,A){return Selectors.Pseudo["nth-child"].call(this,"2n+1",A);},odd:function(B,A){return Selectors.Pseudo["nth-child"].call(this,"2n",A);
}});Element.Events.domready={onAdd:function(A){if(Browser.loaded){A.call(this);}}};(function(){var B=function(){if(Browser.loaded){return ;}Browser.loaded=true;
window.fireEvent("domready");document.fireEvent("domready");};switch(Browser.Engine.name){case"webkit":(function(){(["loaded","complete"].contains(document.readyState))?B():arguments.callee.delay(50);
})();break;case"trident":var A=document.createElement("div");(function(){($try(function(){A.doScroll("left");return $(A).inject(document.body).set("html","temp").dispose();
}))?B():arguments.callee.delay(50);})();break;default:window.addEvent("load",B);document.addEvent("DOMContentLoaded",B);}})();var Cookie=new Class({Implements:Options,options:{path:false,domain:false,duration:false,secure:false,document:document},initialize:function(B,A){this.key=B;
this.setOptions(A);},write:function(B){B=encodeURIComponent(B);if(this.options.domain){B+="; domain="+this.options.domain;}if(this.options.path){B+="; path="+this.options.path;
}if(this.options.duration){var A=new Date();A.setTime(A.getTime()+this.options.duration*24*60*60*1000);B+="; expires="+A.toGMTString();}if(this.options.secure){B+="; secure";
}this.options.document.cookie=this.key+"="+B;return this;},read:function(){var A=this.options.document.cookie.match("(?:^|;)\\s*"+this.key.escapeRegExp()+"=([^;]*)");
return(A)?decodeURIComponent(A[1]):null;},dispose:function(){new Cookie(this.key,$merge(this.options,{duration:-1})).write("");return this;}});Cookie.write=function(B,C,A){return new Cookie(B,A).write(C);
};Cookie.read=function(A){return new Cookie(A).read();};Cookie.dispose=function(B,A){return new Cookie(B,A).dispose();};var Request=new Class({Implements:[Chain,Events,Options],options:{url:"",data:"",headers:{"X-Requested-With":"XMLHttpRequest",Accept:"text/javascript, text/html, application/xml, text/xml, */*"},async:true,format:false,method:"post",link:"ignore",isSuccess:null,emulation:true,urlEncoded:true,encoding:"utf-8",evalScripts:false,evalResponse:false},initialize:function(A){this.xhr=new Browser.Request();
this.setOptions(A);this.options.isSuccess=this.options.isSuccess||this.isSuccess;this.headers=new Hash(this.options.headers);},onStateChange:function(){if(this.xhr.readyState!=4||!this.running){return ;
}this.running=false;this.status=0;$try(function(){this.status=this.xhr.status;}.bind(this));if(this.options.isSuccess.call(this,this.status)){this.response={text:this.xhr.responseText,xml:this.xhr.responseXML};
this.success(this.response.text,this.response.xml);}else{this.response={text:null,xml:null};this.failure();}this.xhr.onreadystatechange=$empty;},isSuccess:function(){return((this.status>=200)&&(this.status<300));
},processScripts:function(A){if(this.options.evalResponse||(/(ecma|java)script/).test(this.getHeader("Content-type"))){return $exec(A);}return A.stripScripts(this.options.evalScripts);
},success:function(B,A){this.onSuccess(this.processScripts(B),A);},onSuccess:function(){this.fireEvent("complete",arguments).fireEvent("success",arguments).callChain();
},failure:function(){this.onFailure();},onFailure:function(){this.fireEvent("complete").fireEvent("failure",this.xhr);},setHeader:function(A,B){this.headers.set(A,B);
return this;},getHeader:function(A){return $try(function(){return this.xhr.getResponseHeader(A);}.bind(this));},check:function(A){if(!this.running){return true;
}switch(this.options.link){case"cancel":this.cancel();return true;case"chain":this.chain(A.bind(this,Array.slice(arguments,1)));return false;}return false;
},send:function(I){if(!this.check(arguments.callee,I)){return this;}this.running=true;var G=$type(I);if(G=="string"||G=="element"){I={data:I};}var D=this.options;
I=$extend({data:D.data,url:D.url,method:D.method},I);var E=I.data,B=I.url,A=I.method;switch($type(E)){case"element":E=$(E).toQueryString();break;case"object":case"hash":E=Hash.toQueryString(E);
}if(this.options.format){var H="format="+this.options.format;E=(E)?H+"&"+E:H;}if(this.options.emulation&&["put","delete"].contains(A)){var F="_method="+A;
E=(E)?F+"&"+E:F;A="post";}if(this.options.urlEncoded&&A=="post"){var C=(this.options.encoding)?"; charset="+this.options.encoding:"";this.headers.set("Content-type","application/x-www-form-urlencoded"+C);
}if(E&&A=="get"){B=B+(B.contains("?")?"&":"?")+E;E=null;}this.xhr.open(A.toUpperCase(),B,this.options.async);this.xhr.onreadystatechange=this.onStateChange.bind(this);
this.headers.each(function(K,J){if(!$try(function(){this.xhr.setRequestHeader(J,K);return true;}.bind(this))){this.fireEvent("exception",[J,K]);}},this);
this.fireEvent("request");this.xhr.send(E);if(!this.options.async){this.onStateChange();}return this;},cancel:function(){if(!this.running){return this;
}this.running=false;this.xhr.abort();this.xhr.onreadystatechange=$empty;this.xhr=new Browser.Request();this.fireEvent("cancel");return this;}});(function(){var A={};
["get","post","put","delete","GET","POST","PUT","DELETE"].each(function(B){A[B]=function(){var C=Array.link(arguments,{url:String.type,data:$defined});
return this.send($extend(C,{method:B.toLowerCase()}));};});Request.implement(A);})();Element.Properties.send={set:function(A){var B=this.retrieve("send");
if(B){B.cancel();}return this.eliminate("send").store("send:options",$extend({data:this,link:"cancel",method:this.get("method")||"post",url:this.get("action")},A));
},get:function(A){if(A||!this.retrieve("send")){if(A||!this.retrieve("send:options")){this.set("send",A);}this.store("send",new Request(this.retrieve("send:options")));
}return this.retrieve("send");}};Element.implement({send:function(A){var B=this.get("send");B.send({data:this,url:A||B.options.url});return this;}});






function clearPanes() {
	$('innercontent').empty();
}

function toggleMenuClick(e) {
	var event = new Event(e);
	var menuId = event.target.parentNode.parentNode.id;
	toggleMenu(menuId.substring(2));
}

function toggleMenu(b, forceExpand) {
	b = parseInt(b);
	var menuId = "db" + b;
	var menu = $(menuId);
	var sub = "sublist" + b;
	
	if (!menu.hasClass("expanded")) {
		// accordion
		var openMenus = $ES(".expanded");
		for (var m=0; m<openMenus.length; m++) {
			var openSub = openMenus[m].id.replace("db", "sublist");
			openMenus[m].removeClass("expanded");
			addAnimation(openSub, 0);
		}
		
		menu.addClass("expanded");
		$(sub).style.display = 'block';
		addAnimation(sub, sb.submenuHeights[b]);
	} else if (forceExpand != true) {
		menu.removeClass("expanded");
		addAnimation(sub, 0);
	}
}

function sideMainClick(page, top) {
	if (sb.topTabSet != 0)
		clearPanesOnLoad = true;
	sb.resetInternals();
	sb.page = page;
	sb.topTab = top;
	var x = new XHR({url: sb.page, onSuccess: finishTabLoad}).send();
}

function databaseClick(e) {
	var event = new Event(e);
	db = event.target.get('text');
	databaseLoad(db);
	return false;
}

function databaseLoad(db) {
	clearPanesOnLoad = true;
	sb.resetInternals();
	sb.page = "dboverview.php";
	sb.db = db;
	sb.topTabSet = 1;
	sb.topTab = 0;
	var x = new XHR({url: sb.page, onSuccess: finishTabLoad}).send();
}

function subTabClick(e) {
	var event = new Event(e);
	var subTabElem = event.target.parentNode;
	var subtabs = $$('#sidemenu li');
	for (var i=0; i<subtabs.length; i++) {
		subtabs[i].removeClass("loading");
	}
	
	if (!subTabElem.hasClass("selected")) {
		subTabElem.addClass("loading");
	}
}

function subTabLoad(db, table) {
	clearPanesOnLoad = true;
	sb.resetInternals();
	sb.topTabSet = 2;
	sb.db = db;
	sb.table = table;
	
	if (parseInt(sb.tableRowCounts[sb.db + "_" + sb.table]) == 0) {
		sb.page = "structure.php";
		sb.topTab = 1;
	} else {
		sb.page = "browse.php";
		sb.topTab = 0;
	}
	var x = new XHR({url: sb.page, onSuccess: finishTabLoad}).send();
	return false;
}

function topTabClicked(e) {
	var event = new Event(e);
	var tabClicked = event.target.parentNode.id;
	topTabLoad(tabClicked);
	return false;
}

function topTabLoad(tab) {	
	sb.topTab = tab;
	sb.page = sb.getTabUrl(sb.topTab);
	
	var pane = $(tab + 'pane');
	
	if (pane != undefined && !clearPanesOnLoad) {
		finishTabLoad();
	} else {
		var x = new XHR({url: sb.page, onSuccess: finishTabLoad}).send();
	}
}

function browseNav(start, view) {
	sb.s = start;
	sb.view = view;
	var x = new XHR({url: "browse.php", onSuccess: finishTabLoad}).send("s=" + f(sb.s) + "&view=" + f(sb.view) + "&sortKey=" + f(sb.sortKey) + "&sortDir=" + f(sb.sortDir));
}

function finishTabLoad(responseText) {
	if ($('bottom').style.opacity != '1') {
		$('bottom').style.opacity = '1';
		document.body.style.backgroundImage = "none";
	}
	
	if (clearPanesOnLoad) {
		clearPanes();
		clearColumnSizes();
		clearPanesOnLoad = false;	
	}
	
	if ($('pane' + sb.topTab) != undefined)
		showPane('pane' + sb.topTab);
	else
		addPane('pane' + sb.topTab);
	
	if (responseText)
		$('pane' + sb.topTab).innerHTML = responseText;
	
	scrollTo(0, 0);
	
	sb.removeTempTabs();
	sb.refreshTopTabSet();
	
	// update the grid variables
	sb.pane = $('pane' + sb.topTab);
	sb.grid = $E('.gridscroll', sb.pane);
	sb.gridHeader = $E('.gridheader', sb.pane);
	sb.leftChecks = $E('.leftchecks', sb.pane);
	
	runJavascriptContent();
	sizePage();
	
	var sideId = getSubMenuId(sb.db, sb.table);
	if (f(sideId) != "") {
		deselectSideMenu();
		$(sideId).addClass("selected");
		
		//make sure the side menu is expanded
		var targ = $(sideId);
		
		if (f(sb.db) && f(sb.table) && f(targ)) {
			while (f(targ) && !targ.hasClass("sublist")) {
				targ = targ.parentNode;
			}
			if (f(targ)) {
				var toExpand = targ.id;
				var toExpandId = parseInt(toExpand.substring(7));
				toggleMenu(toExpandId, true);
			}
		} else if (f(sb.db) && f(targ)) {
			var toExpand = $E('.sublist', targ).id;
			var toExpandId = parseInt(toExpand.substring(7));
			toggleMenu(toExpandId, true);
		}
	}
	
	sb.setHash();
	
	var pageTitle;
	if (sb.table) {
		pageTitle = sb.getTabTitle(sb.topTab) + " - " + sb.table;
	} else if (sb.db) {
		pageTitle = sb.getTabTitle(sb.topTab) + " - " + sb.db;
	} else {
		pageTitle = sb.getTabTitle(sb.topTab);
	}
	document.title = "SQL Buddy - " + pageTitle;
	
	refreshRowCount();
}

function deselectSideMenu() {
	var subtabs = $$('#sidemenu li');
	for (var i=0; i<subtabs.length; i++) {
		subtabs[i].removeClass("loading").removeClass("selected");
	}
}

function checkAll(context) {
	if (context) {
		var grid = $(context);
		var rows = $E('.gridscroll', grid).childNodes;
		var inputs = grid.getElementsByTagName("input");
		var lc = $E('.leftchecks', grid);
	} else {
		var grid = sb.grid;
		var rows = grid.childNodes;
		var inputs = sb.pane.getElementsByTagName("input");
		var lc = sb.leftChecks;
	}
	
	if (f(grid)) {
		for (var i=0; i<rows.length; i++) {
			if (inputs[i].type == "checkbox") {
				inputs[i].checked = true;
				if (rows[i].className.indexOf("highlighted") == -1) {
					rows[i].className += " highlighted";
					lc.childNodes[i].className += " highlighted";
				}
			}
		}
		lastActiveRow = -1;
	}
}

function checkNone(context) {
	if (context) {
		var grid = $(context);
		var rows = $E('.gridscroll', grid).childNodes;
		var inputs = grid.getElementsByTagName("input");
		var lc = $E('.leftchecks', grid);
	} else {
		var grid = sb.grid;
		var rows = grid.childNodes;
		var inputs = sb.pane.getElementsByTagName("input");
		var lc = sb.leftChecks;
	}
	
	if (f(grid)) {
		for (var i=0; i<inputs.length; i++) {
			if (inputs[i].type == "checkbox") {
				inputs[i].checked = false;
				if (rows[i] && rows[i].className.indexOf("highlighted") != -1) {
					rows[i].className = rows[i].className.replace("highlighted", "");
					lc.childNodes[i].className = lc.childNodes[i].className.replace("highlighted", "");
				}
			}
		}
		lastActiveRow = -1;
	}
}

function rowClicked(rowId, context) {	
	// ie changes checkbox after calling event
	if (!Browser.Engine.trident)
		highlightDataRow(rowId, context);
	else
		(function(){ highlightDataRow(rowId, context) }).delay(25);
	
	if (shiftPressed == true && lastActiveRow >= 0 && lastActiveRow != rowId) {
		if (context) {
			var grid = $E('.gridscroll', $(context));
			var checks = $E('.leftchecks', $(context)).childNodes;
		} else {
			var grid = sb.grid;
			var checks = sb.leftChecks.childNodes;
		}
		
		if (rowId < lastActiveRow) {
			for (var i=rowId+1; i<lastActiveRow; i++) {
				checks[i].firstChild.firstChild.checked = checks[rowId].firstChild.firstChild.checked;
				highlightDataRow(i, context);
			}
		} else {
			for (var i=rowId-1; i>lastActiveRow; i--) {
				checks[i].firstChild.firstChild.checked = checks[rowId].firstChild.firstChild.checked;
				highlightDataRow(i, context);
			}
		}
	}
	lastActiveRow = rowId;
}

function highlightDataRow(i, context) {
	if (context) {
		var grid = $(context);
		var rows = $E('.gridscroll', grid).childNodes;
		var lc = $E('.leftchecks', grid).childNodes;
	} else {
		var rows = sb.grid.childNodes;
		var lc = sb.leftChecks.childNodes;
	}
	
	if (lc[i].firstChild.firstChild.checked == true) {
		if (rows[i].className.indexOf("highlighted") == -1) {
			rows[i].className += " highlighted";
			lc[i].className += " highlighted";
		}
	} else {
		if (rows[i].className.indexOf("highlighted") != -1) {
			rows[i].className = rows[i].className.replace("highlighted", "");
			lc[i].className = lc[i].className.replace("highlighted", "");
		}
	}
}

function editSelectedRows() {
	var grid = sb.grid;
	if (f(grid)) {
		var editParts = "";
		var count = 0;
		var inputs = $ES("input", sb.leftChecks);
		for (var i=0; i<inputs.length; i++) {
			if (inputs[i].type == "checkbox" && inputs[i].checked == true) {
				editParts += (inputs[i]).get("querybuilder") + "; ";
				count++;
			}
		}
		if (count > 0) {
			if (sb.page == "structure.php")
				var loadPage = "editcolumn.php";
			else if (sb.page == "users.php")
				var loadPage = "edituser.php";
			else
				var loadPage = "edit.php";
			
			editParts = editParts.substring(0, editParts.length - 2);
			sb.topTabs[sb.topTabSet].addTab("Edit", loadPage, true);
			sb.page = loadPage;
			var x = new XHR({url: loadPage, onSuccess: finishTabLoad}).send("editParts=" + editParts);
		}
	}
}

function saveEdit(formId) {
	var form = $(formId);
	var queryPart = form.get("querypart");
	var x = new XHR({url: "ajaxsaveedit.php?queryPart=" + queryPart + "&form=" + formId, onSuccess: updateAfterEdit}).send(form.toQueryString());
}

function saveColumnEdit(formId) {
	var form = $(formId);
	var changes = getFieldSummary(form);
	var columnQuery = "ALTER TABLE `" + sb.table + "` CHANGE `" + form.get("querypart") + "` " + changes;
	var x = new XHR({url: "ajaxsavecolumnedit.php?form=" + formId, onSuccess: updateAfterEdit}).send("runQuery=" + columnQuery);
}

function saveUserEdit(formId) {
	var form = $(formId);
	var x = new XHR({url: "ajaxsaveuseredit.php?form=" + formId + "&user=" + form.get("querypart"), onSuccess: updateAfterEdit}).send(form.toQueryString());
}

function updateAfterEdit(json) {
	var response = eval('(' + json + ')');
	var formu = $(response.formupdate);
	if (response.errormess == "") {
		showUpdateMessage(formu);
	} else {
		var errHandle = $E('.errormessage', formu);
		errHandle.style.display = '';
		errHandle.set('text', response.errormess);
	}
	sizePage();
	clearPanesOnLoad = true;
}

function showUpdateMessage(formu) {
	// hide other messages
	hideUpdateMessages();
	
	formu.innerHTML = "";
	var updateId = sb.$GUID++;
	var updateDiv = new Element("div", {
		'class': 'insertmessage',
		'id': 'update' + updateId
	}).set('text', gettext("Your changes were saved to the database."));
	formu.appendChild(updateDiv);
	yellowFade($('update' + updateId));
	setTimeout(hideUpdateMessages, 1250);
}

function hideUpdateMessages() {
	var updates = $ES(".insertmessage");
	var edits = $ES(".edit");
	
	if (edits.length == 0) {
		updates[0].set('text', gettext("Redirecting..."));
		
		for (var i=1; i<updates.length; i++) {
			updates[i].dispose();
		}
		
		clearPanesOnLoad = true;
		
		if (sb.page == "edituser.php" || sb.page == "editcolumn.php")
			topTabLoad(1);
		else
			topTabLoad(0);
	} else {
		for (var i=0; i<updates.length; i++) {
			updates[i].dispose();
		}
	}
}

function cancelEdit(formu) {
	hideUpdateMessages();
	
	formu = $(formu);
	formu.set('html', '');
	
	var edits = $ES(".edit");
	
	if (edits.length == 0) {
		formu.set('text', gettext("Redirecting..."));
		formu.className = "insertmessage";
		
		clearPanesOnLoad = true;
		
		if (sb.page == "edituser.php" || sb.page == "editcolumn.php")
			topTabLoad(1);
		else
			topTabLoad(0);
	} else {
		formu.dispose();
	}
	
	sizePage();
}

function deleteSelectedRows() {
	generatePrompt("DELETE FROM " + quoteModifier(sb.table) + " ", "", gettext("delete this row"), gettext("delete these rows"), "runQuery", true);
}

function emptySelectedTables() {
	if (adapter == "sqlite")
		generatePrompt("DELETE FROM '", "'", gettext("empty this table"), gettext("empty these tables"), "runQuery", true);
	else if (adapter == "mysql")
		generatePrompt("TRUNCATE `", "`", gettext("empty this table"), gettext("empty these tables"), "runQuery", true);
}

function dropSelectedTables() {
	generatePrompt("DROP TABLE " + returnQuote(), returnQuote(), gettext("drop this table"), gettext("drop these tables"), "runQuery", true);
}

function deleteSelectedColumns() {
	generatePrompt("ALTER TABLE `" + sb.table + "` DROP `", "`", gettext("delete this column"), gettext("delete these columns"), "runQuery", true);
}

function deleteSelectedIndexes(context) {
	generatePrompt("DROP INDEX `", "` ON `" + sb.table + "`", gettext("delete this index"), gettext("delete these indexes"), "runQuery", true, context);
}

function deleteSelectedUsers() {
	generatePrompt("", "", gettext("delete this user"), gettext("delete these users"), "deleteUsers", false);
}

function optimizeSelectedTables() {
	var lc = sb.leftChecks;
	
	if (f(lc)) {
		var optimizeQuery = "";
		var inputs = $ES("input", lc);
		for (var i=0; i<inputs.length; i++) {
			if (inputs[i].type == "checkbox" && inputs[i].checked == true) {
				optimizeQuery += "OPTIMIZE TABLE `" + inputs[i].get("querybuilder") + "`; ";
			}
		}
		if (optimizeQuery) {	
			var x = new XHR({url: sb.page, onSuccess: finishTabLoad}).send("runQuery=" + optimizeQuery);
		}
	}
}

function executeQuery() {
	var query = encodeURIComponent($('QUERY').value);
	var x = new XHR({url: "query.php", onSuccess: finishTabLoad}).send("query=" + query);
}

function loadNewSort(key, direction) {
	sb.sortKey = key;
	sb.sortDir = direction;
	var x = new XHR({url: "browse.php", onSuccess: finishTabLoad}).send("view=" + sb.view + "&s=" + sb.s + "&sortKey=" + sb.sortKey + "&sortDir=" + sb.sortDir);
}

function confirmEmptyTable() {
	if (f(sb.table)) {
		if (adapter == "mysql") {
			var emptyQuery = "TRUNCATE TABLE `" + sb.table + "`";
		} else if (adapter == "sqlite") {
			var emptyQuery = "DELETE FROM '" + sb.table + "'";
		}
		
		showDialog(gettext("Confirm"),
			printf(gettext("Are you sure you want to empty the '%s' table? This will delete all the data inside of it. The following query will be run:"), sb.table) + "<div class=\"querybox\">" + emptyQuery + "</div>",
			"var x = new XHR({url: \"ajaxquery.php\", onSuccess: emptyTableCallback}).send(\"query=" + encodeURIComponent(emptyQuery) + "&silent=1\")"
		);
	}
}

function emptyTableCallback() {
	sb.tableRowCounts[sb.db + "_" + sb.table] = "0";
	subTabLoad(sb.db, sb.table);
}

function confirmDropTable() {
	var table = sb.table;
	if (f(table)) {
		var dropQuery = "DROP TABLE " + returnQuote() + table + returnQuote();
		var targ = $(getSubMenuId(sb.db, sb.table));
		while (!targ.hasClass("sublist")) {
			targ = targ.parentNode;
		}
		var toRecalculate = targ.id;
		showDialog(gettext("Confirm"),
			printf(gettext("Are you sure you want to drop the '%s' table? This will delete the table and all data inside of it. The following query will be run:"), table) + "<div class=\"querybox\">" + dropQuery + "</div>",
			"var x = new XHR({url: \"ajaxquery.php\"}).send(\"query=" + dropQuery + "&silent=1\"); $(getSubMenuId(sb.db, sb.table)).dispose(); databaseLoad(sb.db); recalculateSubmenuHeight(\"" + toRecalculate + "\");"
		);
	}
}

function optimizeTable() {
	if (sb.table) {
		var optimizeQuery = "OPTIMIZE TABLE `" + sb.table + "`;";
		var x = new XHR({url: "ajaxquery.php", onSuccess: function(){ sb.loadPage() } }).send("query=" + optimizeQuery + "&silent=1");
	}
}

function confirmDropDatabase() {
	var db = sb.db;
	if (f(db)) {
		var dropQuery = "DROP DATABASE `" + db + "`";
		showDialog(gettext("Confirm"),
			printf(gettext("Are you sure you want to drop the database '%s'? This will delete the database, the tables inside the database, and all data inside of the tables. The following query will be run:"), db) + "<div class=\"querybox\">" + dropQuery + "</div>",
			"var x = new XHR({url: \"ajaxquery.php\"}).send(\"query=" + dropQuery + "&silent=1\"); $(getSubMenuId(sb.db, sb.table)).dispose(); sideMainClick(\"home.php\",\"hometab\");"
		);
	}
}

function editTable() {
	var newName = $('RENAME').value;
	var charSelect = $('RECHARSET');
	if (charSelect)
		var newCharset = charSelect.options[charSelect.selectedIndex].value;
	
	var runQuery = "";
	
	if (newName != sb.table && adapter == "mysql") {
		runQuery += "RENAME TABLE `" + sb.table + "` TO `" + newName + "`;";
	} else if (newName != sb.table && adapter == "sqlite") {
		runQuery += "ALTER TABLE '" + sb.table + "' RENAME TO '" + newName + "';";
	}
	
	if (f(newCharset) != "") {
		runQuery += "ALTER TABLE `" + sb.table + "` CHARSET " + newCharset + ";";
	}
	
	if (f(runQuery) != "") {
		$('RENAME').blur();
		var x = new XHR({url: "ajaxquery.php", onSuccess: editTableCallback}).send("query=" + runQuery + "&silent=1");
	}
	
	// defined interally on purpose
	function editTableCallback() {
		if (f(newName) != "" && newName != sb.table) {
			var submenuItem = $(sb.submenuIds[sb.db + '_' + sb.table]);
			submenuItem.firstChild.set('text', newName);
			submenuItem.firstChild.href = "#page=browse&db=" + sb.db + "&table=" + newName + "&topTabSet=2";
			var subacount = new Element('span');
			subacount.className = "subcount";
			subacount.appendText("(" + approximateNumber(sb.tableRowCounts[sb.db + '_' + sb.table]) + ")");
			submenuItem.firstChild.appendChild(subacount);
			
			sb.submenuIds[sb.db + '_' + newName] = sb.submenuIds[sb.db + '_' + sb.table];
			sb.submenuIds[sb.db + '_' + sb.table] = '';
			sb.tableRowCounts[sb.db + '_' + newName] = sb.tableRowCounts[sb.db + '_' + sb.table];
			sb.tableRowCounts[sb.db + '_' + sb.table] = '';
			sb.table = newName;
			sb.setHash();
		}
		
		$('editTableMessage').set('text', gettext("Successfully saved changes."));
		yellowFade($('editTableMessage'));
		var clearTable = function() {
			$('editTableMessage').empty();
		};
		
		clearTable.delay(2000);
		
		clearPanesOnLoad = true;
	}
}

function editDatabase() {
	var charSelect = $('DBRECHARSET');
	var newCharset = charSelect.options[charSelect.selectedIndex].value;
	
	if (f(newCharset) != "") {
		var runQuery = "ALTER DATABASE `" + sb.db + "` CHARSET " + newCharset + ";";
		var x = new XHR({url: "ajaxquery.php", onSuccess: editDatabaseCallback}).send("query=" + runQuery + "&silent=1");
	}
	
	function editDatabaseCallback() {
		$('editDatabaseMessage').set('text', gettext("Successfully saved changes."));
		yellowFade($('editDatabaseMessage'));
		
		var clearDatabase = function() {
			$('editDatabaseMessage').empty();
		};
		
		clearDatabase.delay(2000);
		
		clearPanesOnLoad = true;
	}
}

function runJavascriptContent() {
	var scripts = $ES("script", sb.pane);
	for (var i=0; i<scripts.length; i++) {
		// basic xss prevention
		if (scripts[i].get("authkey") == requestKey) {
			var toRun = scripts[i].get('html');
			var newScript = new Element("script");
			newScript.set("type", "text/javascript");
			if (!Browser.Engine.trident) {
				newScript.innerHTML = toRun;
			} else {
				newScript.text = toRun;
			}
			document.body.appendChild(newScript);
			document.body.removeChild(newScript);
		}
	}
}

function recalculateSubmenuHeight(theMenu) {
	var idForMenu = parseInt(theMenu.substring(7));
	sb.submenuHeights[idForMenu] = $(theMenu).clientHeight;
}

function sizePage() {
	var windowInnerWidth = getWindowWidth();
	var windowInnerHeight = getWindowHeight();
	
	if (f(sb.grid) && (sb.page == "browse.php" || sb.page == "query.php")) {
		if (sb.page == "browse.php")
			var gridHeight = windowInnerHeight - 111;
		else if (sb.page == "query.php")
			var gridHeight = windowInnerHeight - 210;
		
		if (Browser.Engine.trident)
			gridHeight = gridHeight - 9;
		
		sb.grid.style.maxHeight = gridHeight + 'px';
		
		if (sb.leftChecks) {
			
			var scrollbarWidth = getScrollbarWidth();
			
			var otherWidth = sb.grid.scrollWidth + scrollbarWidth;
			
			// check for horizontal scrollbar
			if ((sb.grid.offsetHeight == sb.grid.scrollHeight && sb.grid.offsetWidth != sb.grid.scrollWidth) || (sb.grid.offsetHeight != sb.grid.scrollHeight && sb.grid.offsetWidth != otherWidth)) {
				sb.leftChecks.style.maxHeight = (gridHeight - scrollbarWidth) + 'px';
				sb.leftChecks.style.borderBottom = "1px solid rgb(200, 200, 200)";
			} else {
				sb.leftChecks.style.maxHeight = gridHeight + 'px';
			}
		}
		
		var gridWidth = windowInnerWidth - 19;
		sb.grid.style.maxWidth = gridWidth + 'px';
		
		sb.gridHeader.style.maxWidth = gridWidth + 'px';
		
	}
	if (f($('sidemenu'))) {
		var headerOffset = $('header').offsetHeight;
		var rightOffset = $('rightside').offsetHeight;
		
		// check to see if the right content is long enough to cause a scrollbar
		if ((headerOffset + rightOffset) < windowInnerHeight) {
			var sideHeight = windowInnerHeight - headerOffset - 16;
		} else {
			var sideHeight = rightOffset - 16;
		}
		
		if (Browser.Engine.trident)
			sideHeight -= 2;
		
		$('sidemenu').style.height = sideHeight + 'px';
	}
	if (f($('innercontent'))) {
		var inHeight = (windowInnerHeight - headerOffset - 33);
		
		if (Browser.Engine.trident)
			inHeight -= 4;
		
		$('innercontent').style.minHeight = inHeight + 'px';
	}
	
	// redraw page - for safari
	if (Browser.Engine.webkit) {
		var contentBox = $('content');
		var contentHeight = contentBox.offsetHeight;
		contentBox.style.height = (contentHeight - 1) + "px";
		contentBox.style.height = "";
	}
}

function startGrid() {
	if (f(sb.grid)) {
		sb.grid.addEvent("scroll", maintainScrollPos);
		var columns = $ES(".columnresizer", sb.gridHeader);
		var impotent = sb.gridHeader.className.indexOf("impotent");
		
		//setup the js event handlers
		if (impotent == -1 && columns.length > 0) {
			for (var i=0; i<columns.length; i++) {
				columns[i].addEvent("mousedown", startColumnResize);
			}
		}
	}
}

function maintainScrollPos() {
	sb.gridHeader.scrollLeft = sb.grid.scrollLeft;
	if (sb.leftChecks)
		sb.leftChecks.scrollTop = sb.grid.scrollTop;
}

function getSubMenuId(db, table) {	
	if (f(db) && f(table))
		return sb.submenuIds[db + "_" + table];
	else if (f(db))
		return sb.submenuIds[db];
	else if (sb.page == "query.php")
		return "sidequery";
	else if (sb.page == "users.php")
		return "sideusers";
	else if (sb.page == "import.php")
		return "sideimport";
	else if (sb.page == "export.php")
		return "sideexport";
	else
		return "sidehome";
}

function updateFieldName(inputElem) {
	var fancy = inputElem;
	while (fancy.className.indexOf("fieldbox") == -1) {
		fancy = fancy.parentNode;
	}
	
	var fieldSummary = getFieldSummary(fancy, true);
	
	if (f(fieldSummary) == "")
		fieldSummary = "&lt;" + gettext("New field") + "&gt;";
	
	$E(".fieldheader span", fancy).set('html', fieldSummary);
}

function getFieldSummary(elem, withFormatting) {
	var name, type, values, size, key, defaultval, charset, auto, unsign, binary, notnull, unique, headerBuild;
	var fieldBuild;
	if (f(elem)) {
		var inputs = elem.getElementsByTagName("input");
		for (var inp = 0; inp < inputs.length; inp++) {
			if (inputs[inp].name == "NAME")
				name = inputs[inp].value;
			if (inputs[inp].name == "VALUES")
				values = inputs[inp].value;
			if (inputs[inp].name == "SIZE")
				size = inputs[inp].value;
			if (inputs[inp].name == "DEFAULT")
				defaultval = inputs[inp].value;
			if (inputs[inp].name == "UNSIGN")
				unsign = inputs[inp].checked;
			if (inputs[inp].name == "BINARY")
				binary = inputs[inp].checked;
			if (inputs[inp].name == "AUTO")
				auto = inputs[inp].checked;
			if (inputs[inp].name == "NOTNULL")
				notnull = inputs[inp].checked;
			if (inputs[inp].name == "UNIQUE")
				unique = inputs[inp].checked;
		}
		
		var selects = elem.getElementsByTagName("select");
		for (sel = 0; sel < selects.length; sel++) {
			if (selects[sel].name == "TYPE")
				type = selects[sel].options[selects[sel].selectedIndex].value;
			if (selects[sel].name == "KEY")
				key = selects[sel].options[selects[sel].selectedIndex].value;
			if (selects[sel].name == "CHARSET")
				charset = selects[sel].options[selects[sel].selectedIndex].value;
		}
		
		if (f(name) != "") {
			if (withFormatting)
				fieldBuild = "<span style=\"color: steelblue\">" + name + "</span>";
			else if (adapter == "sqlite")
				fieldBuild = name;
			else
				fieldBuild = "`" + name + "`";
			
			if (adapter == "sqlite") {
				if (f(type))
					fieldBuild += " " + type;
				if (f(size) && f(type))
					fieldBuild += "(" + size + ")";
				if (f(notnull))
					fieldBuild += " not null";
				if (f(key))
					fieldBuild += " " + key + " key";
				if (f(auto))
					fieldBuild += " autoincrement";
				if (f(unique))
					fieldBuild += " unique";
				if (f(defaultval))
					fieldBuild += " default '" + defaultval + "'";
			} else {
				if (f(type))
					fieldBuild += " " + type;
				if (f(values) && (type == "set" || type == "enum"))
					fieldBuild += values + "";
				if (f(size) && f(type))
					fieldBuild += "(" + size + ")";
				if (f(unsign))
					fieldBuild += " unsigned";
				if (f(binary))
					fieldBuild += " binary";
				if (f(charset))
					fieldBuild += " charset " + charset;
				if (f(notnull))
					fieldBuild += " not null";
				if (f(defaultval))
					fieldBuild += " default '" + defaultval + "'";
				if (f(auto))
					fieldBuild += " auto_increment";
				if (f(key))
					fieldBuild += " " + key + " key";
			}
			
		}
	}
	return fieldBuild;
}

function addTableField() {
	var fieldList = $('fieldlist');
	var toCopy = $E('.fieldbox', fieldList).innerHTML;
	
	if (f(toCopy)) {
		var newField =  new Element('div');
		newField.set('html', toCopy);
		newField.className = "fieldbox";
		fieldList.appendChild(newField);
		
		clearForm(newField);
		
		var valueLine = $E(".valueline", newField);
		if (f(valueLine))
			valueLine.style.display = 'none';
		
		if (!Browser.Engine.trident) {
			var newHeader = $E(".fieldheader", newField).childNodes[1];
			newHeader.set('html', '&lt;' + gettext("New field") + '&gt;');
		}
		
	}
	sizePage();
}

function clearForm(elem) {
	var inputs = elem.getElementsByTagName("input");
	for (var i=0; i<inputs.length; i++) {
		if (inputs[i].type == "text")
			inputs[i].value = '';
		else if (inputs[i].type == "checkbox")
			inputs[i].checked = false;
	}
	var selects = elem.getElementsByTagName("select");
	for (var i=0; i<selects.length; i++) {
		selects[i].selectedIndex = 0;
	}
}

function createDatabase() {
	var elem = $('DBNAME');
	var dbName = elem.value;
	if (f(dbName)) {
		var createQuery = "CREATE DATABASE `" + dbName + "`";
		
		if ($('DBCHARSET')) {
			var charset = $('DBCHARSET').value;
			if (charset != "")
				createQuery += " CHARSET " + charset;
		}
		var x = new XHR({url: "ajaxquery.php", onSuccess: createDatabaseCallback}).send("query=" + createQuery + "&silent=1");
	}
	
	function createDatabaseCallback() {
		addMenuItem(dbName);
		databaseLoad(dbName);
	}
}

function createTable() {
	var tableName = $('TABLENAME').value;
	var fields = $ES(".fieldbox", $('fieldlist'));
	if (f(tableName) && fields.length > 0) {
		
		$('TABLENAME').style.border = "";
		
		if (adapter == "sqlite") {
			var createQuery = "CREATE TABLE " + tableName + " (";
		} else {
			var createQuery = "CREATE TABLE `" + tableName + "` (";
		}
		
		for (var i=0; i<fields.length; i++) {
			createQuery += getFieldSummary(fields[i]) + ", ";
		}
		createQuery = createQuery.substring(0, createQuery.length-2);
		createQuery += ")";
		
		if ($('TABLECHARSET')) {
			var charset = $('TABLECHARSET').value;
			if (charset != "")
				createQuery += " CHARSET " + charset;
		}
		
		var x = new XHR({url: "ajaxcreatetable.php", onSuccess: createTableCallback}).send("table=" + tableName + "&query=" + createQuery);
	}
	else if (!(f(tableName))) {
		$('TABLENAME').style.border = "1px solid rgb(200, 125, 125)";
	}
	
	function createTableCallback(response) {
		if (response != "") {
			$('reporterror').style.display = '';
			$('reporterror').innerHTML = response;
		} else {
			var submenu = getSubMenuId(sb.db).substring(2);
			submenu = "sublist" + submenu;
			sb.tableRowCounts[sb.db + "_" + tableName] = 0;
			addSubMenuItem(submenu, sb.db, tableName);
			subTabLoad(sb.db, tableName);
			window.scrollTo(0, 0);
		}
	}
}

function removeField(elem) {
	while (elem.className != "fieldbox") {
		elem = elem.parentNode;
	}
	if (f(elem))
		elem.dispose();
	sizePage();
}

function submitAddColumn() {
	var newColumn = getFieldSummary($('newfield'));
	
	if (adapter == "mysql") {
		var position = $('INSERTPOS').options[$('INSERTPOS').selectedIndex].value;
		var columnQuery = "ALTER TABLE `" + sb.table + "` ADD " + newColumn + position;
	} else if (adapter == "sqlite") {
		var columnQuery = "ALTER TABLE '" + sb.table + "' ADD " + newColumn;
	}
	
	var x = new XHR({url: sb.page, onSuccess: finishTabLoad}).send("runQuery=" + columnQuery);
}

function toggleVisibility(id) {
	id = $(id);
	if (id.style.display == "none")
		id.style.display = "block";
	else
		id.style.display = "none";
	sizePage();
}

function approximateNumber(num) {
	if (isNaN(num) || num == "NaN")
		num = 0;
	
	if (num < 10000)
		return num;
	else if (num < 1000000)
		return Math.floor(num / 1000) + "K";
	else if (num < 100000000)
		return Math.floor((num / 1000000) * 10) / 10 + "M";
	else
		return Math.floor(num / 1000000) + "M";
}

function refreshRowCount() {
	if (f(sb.db) && f(sb.table)) {
		if (adapter == "sqlite")
			var countQuery = "SELECT COUNT(*) AS 'RowCount' FROM '" + sb.table + "'";
		else
			var countQuery = "SELECT COUNT(*) AS `RowCount` FROM `" + sb.table + "`";
		
		var x = new XHR({url: "ajaxquery.php", onSuccess: updateRowCount, showLoader: false}).send("query=" + countQuery);
	}
}

function updateRowCount(responseText) {
	var updatedCount = parseInt(responseText);
	
	sb.tableRowCounts[sb.db + "_" + sb.table] = updatedCount;
	sb.refreshTopTabSet();
	
	var sideA = $(getSubMenuId(sb.db, sb.table));
	var counter = $E(".subcount", sideA);
	counter.set('text', "(" + approximateNumber(updatedCount) + ")");
}

function updatePane(toCheck, pane1, pane2, fromTimeout) {
	if ($(toCheck).checked) {
		$(pane1).style.display = '';
		if ($(pane2))
			$(pane2).style.display = 'none';
	} else {
		$(pane1).style.display = 'none';
		if ($(pane2))
			$(pane2).style.display = '';
	}
	sizePage();
		
	//ie is retarded, duh
	if (Browser.Engine.trident && !fromTimeout)
		setTimeout("updatePane('" + toCheck + "','" + pane1 + "','" + pane2 + "', true)", 100);
}

function toggleValuesLine(obj, box) {
	if (box) {
		box = $(box);
	} else {
		box = obj;
		while (!box.hasClass("overview") && box.parentNode) {
			box = box.parentNode;
		}
	}
	
	var valueLine = $E(".valueline", box);
	
	if (obj.value == "enum" || obj.value == "set")
		valueLine.style.display = '';
	else
		valueLine.style.display = 'none';
	
	var charsetToggle = $ES(".charsetToggle", box);
	
	if (charsetToggle) {
		if (obj.value.indexOf("char") >= 0 || obj.value.indexOf("text") >= 0 || obj.value == "enum" || obj.value == "set") {
			charsetToggle[0].style.display = '';
			charsetToggle[1].style.display = '';
		} else {
			charsetToggle[0].style.display = 'none';
			charsetToggle[1].style.display = 'none';
		}
	}
	
	sizePage();
}

function exportFilePrep() {
	var oft = $('OUTPUTFILETEXT');
	if ($('OUTPUTFILE').checked) {
		
		defaultFilename = gettext("Export").toLowerCase();
		
		if ($('SQLTOGGLE').checked) {
			if (oft.value == "" || oft.value == defaultFilename + ".csv")
				oft.value =  defaultFilename + ".sql";
			oft.focus();
		} else {
			if (oft.value == "" || oft.value == defaultFilename + ".sql")
				oft.value = defaultFilename + ".csv";
			oft.focus();
		}	
	}
}

function startImport() {
	$('importLoad').style.display = '';
	$('importForm').setAttribute("target", "importFrame");
}

function updateAfterImport(message) {
	$('importLoad').style.display = 'none';
	$('importMessage').style.display = '';
	$('importMessage').innerHTML = message;
	clearPanesOnLoad = true;
}

function paneCheckAll(elemId) {
	var elem = $(elemId);
	var inputs = elem.getElementsByTagName("input");
	for (var i=0; i<inputs.length; i++) {
		if (inputs[i].type == "checkbox")
			inputs[i].checked = true;
	}
}

function paneCheckNone(elemId) {
	var elem = $(elemId);
	var inputs = elem.getElementsByTagName("input");
	for (var i=0; i<inputs.length; i++) {
		if (inputs[i].type == "checkbox")
			inputs[i].checked = false;
	}
}

function selectAll(elemId) {
	var elem = $(elemId);
	for (var i=0; i<elem.options.length; i++) {
		elem.options[i].selected = "selected";
	}
	elem.focus();
}

function selectNone(elemId) {
	var elem = $(elemId);
	for (var i=0; i<elem.options.length; i++) {
		elem.options[i].selected = false;
	}
}

function focusWindow(e) {
	var event = new Event(e);
	var targ = event.target;
	
	while (targ && targ.className.indexOf("fulltextwin") == -1) {
		targ = targ.parentNode;
	}
	
	if (targ) {
		targ.style.zIndex = sb.$GUID++;
	}
}

function closeWindow(winId) {
	var opacities = [0.1, 0.4, 0.7];
	closeWindowCallback(winId, 20, opacities);
}

function closeWindowCallback(winId, speed, opacities) {
	var win = $(winId);
	if (opacities.length > 0) {
		win.style.opacity = opacities.pop();
		var nextWin = function(){ closeWindowCallback(winId, speed, opacities); };
		nextWin.delay(speed);
	} else {
		win.dispose();
	}
}

function switchLanguage() {
	var langSelect = $('langSwitcher');
	var lang = langSelect.options[langSelect.selectedIndex].value;
	var defaultLang = "en_US";
	
	if (lang != defaultLang) {
		var co = Cookie.write("sb_lang", lang, {duration: 60});
	} else if (Cookie.read("sb_lang")) {
		Cookie.dispose("sb_lang");
	}
	location.reload(true);
}

function switchTheme() {
	var themeSelect = $('themeSwitcher');
	var theme = themeSelect.options[themeSelect.selectedIndex].value;
	var defaultTheme = "bittersweet";
	
	if (theme != defaultTheme) {
		var co = Cookie.write("sb_theme", theme, {duration: 60});
	} else if (Cookie.read("sb_theme")) {
		Cookie.dispose("sb_theme");
	}
	location.reload(true);
}

function quoteModifier(mod) {
	return returnQuote() + mod + returnQuote();
}

function returnQuote() {
	if (adapter == "sqlite") {
		return "'";
	} else if (adapter == "mysql") {
		return "`";
	}
}

function autoExpandTextareas() {
	var taList = document.getElementsByTagName("textarea");
	if (taList.length > 0 && sb.page != "export.php") {
		var sizeDiv = new Element('div');
		sizeDiv.id = "sizeDiv";
		sizeDiv.style.visibility = "hidden";
		sizeDiv.style.position = "absolute";
		sizeDiv.style.lineHeight = "15px";
		sizeDiv.style.fontSize = "13px";
		sizeDiv.style.padding = "2px";
		document.body.appendChild(sizeDiv);
		
		for (var i=0; i<taList.length; i++) {
			var theDiv = $("sizeDiv");
			theDiv.style.width = taList[i].clientWidth + "px";
			theDiv.set('html', taList[i].value.replace(/\n/g,'<br />') + '&nbsp;');
			
			var newHeight = theDiv.clientHeight + 5;
			
			if (newHeight < 80) {
				newHeight = 80;
			} else if (newHeight > 300) {
				newHeight = 300;
			}
			
			taList[i].style.height = newHeight + "px";
		}
		
		document.body.removeChild(sizeDiv);
	}
}

function yellowFade(el, curr) {	
	if (!curr)
		curr = 175;
	
	el.style.background = 'rgb(255, 255, '+ (curr+=3) +')';
	
	if (curr < 255)
			setTimeout(function(){ yellowFade(el, curr) }, 25);
}








var sb;

var shiftPressed;
var lastActiveRow = -1;
var clearPanesOnLoad = false;

var activeWindow;
var animationStack = [];

var viewportSize = [0, 0];

function f(g) {
	if (g == undefined || g == "undefined" || g == null)
		return "";
	else
		return g;
}

var $E = function(selector, filter) {
	return ($(filter) || document).getElement(selector);
};

var $ES = function(selector, filter) {
	return ($(filter) || document).getElements(selector);
};

window.addEvent("domready", function() {
	document.addEvent("keydown", function(e) {
		var ev = new Event(e);
		if (ev.shift)
			shiftPressed = true;
	});
	
	document.addEvent("keyup", function() {
		shiftPressed = false;
	});
	
	// to disable keyboard shortcuts, comment out the following line
	window.addEvent("keydown", runKeyboardShortcuts);
	
	sb = new Page();
	
	initializeSidemenu();
	
	sb.loadHash();
	sb.loadPage();
	
	(function(){ sb.preload(); }).delay(500);
	(function(){ sb.checkHashState(); }).periodical(75);
	(function(){
		var winWidth = getWindowWidth();
		var winHeight = getWindowHeight();
		if (viewportSize[0] != winWidth || viewportSize[1] != winHeight) {
			viewportSize = [winWidth, winHeight];
			sizePage();
		}
	}).periodical(175);
	(function(){ autoExpandTextareas(); }).periodical(500);
});

function Page() {
	this.page = "home.php";
	this.db;
	this.table;
	this.topTabSet = 0;
	this.topTab = 0;
	this.s;
	this.view;
	this.sortDir;
	this.sortKey;
	
	this.$GUID = 1;
	
	this.pane;
	this.grid;
	this.gridHeader;
	this.leftChecks;
	
	this.hashMemory = "";
	
	this.submenuHeights = [];
	this.submenuIds = [];
	this.tableRowCounts = [];
	
	this.topTabs = [new TopTabGroup("Main"), new TopTabGroup("Database"), new TopTabGroup("Table")];
	
	if (showUsersMenu) {
		this.topTabs[0].addTab(gettext("Home"), "home.php").addTab(gettext("Users"), "users.php").addTab(gettext("Query"), "query.php").addTab(gettext("Import"), "import.php").addTab(gettext("Export"), "export.php");
	} else {
		this.topTabs[0].addTab(gettext("Home"), "home.php").addTab(gettext("Query"), "query.php").addTab(gettext("Import"), "import.php").addTab(gettext("Export"), "export.php");
	}
	
	this.topTabs[1].addTab(gettext("Overview"), "dboverview.php").addTab(gettext("Query"), "query.php").addTab(gettext("Import"), "import.php").addTab(gettext("Export"), "export.php");
	this.topTabs[2].addTab(gettext("Browse"), "browse.php").addTab(gettext("Structure"), "structure.php").addTab(gettext("Insert"), "insert.php").addTab(gettext("Query"), "query.php").addTab(gettext("Import"), "import.php").addTab(gettext("Export"), "export.php");
	
}

Page.prototype.loadPage = function() {
	if (this.page == "browse.php" && parseInt(this.tableRowCounts[this.db + "_" + this.table]) == 0) {
		this.page = "structure.php";
		this.topTab = 1;
	}
	
	var pageUrl = "";
	if (f(this.s))
		pageUrl += "s=" + this.s + "&";
	if (f(this.sortDir))
		pageUrl += "sortDir=" + this.sortDir + "&";
	if (f(this.sortKey))
		pageUrl += "sortKey=" + this.sortKey + "&";
	pageUrl = pageUrl.substring(0, pageUrl.length - 1);
	
	if (this.page == "editcolumn.php") {
		this.page = "structure.php";
		this.topTab = 1;
	} else if (this.page == "edituser.php") {
		this.page = "users.php";
		this.topTab = 1;
	} else if (this.page == "edit.php") {
		this.page = "browse.php";
		this.topTab = 0;
	}
	
	clearPanesOnLoad = true;
	var x = new XHR({url: this.page, onSuccess: finishTabLoad}).send(pageUrl);
}

Page.prototype.refreshTopTabSet = function(setNum) {
	if (f(setNum))
		this.topTabSet = setNum;
	
	var topTabsElem = $E('#toptabs ul');
	topTabsElem.empty();
	var loopStop = this.topTabs[this.topTabSet].tabCounter;
	var browseIsActive = true;
	var currentTab, rowCount, tabId, tabLiObj, tabAObj, tabACount;
	
	for (var i=0; i<loopStop; i++) {	
		currentTab = this.topTabs[this.topTabSet].tabList[i];
		tabId = i;
		
		rowCount = sb.tableRowCounts[sb.db + "_" + sb.table];
		
		if (isNaN(rowCount))
			rowCount = 0;
		else
			rowCount = approximateNumber(rowCount);
		
		if (rowCount == 0)
			browseIsActive = false;
		
		tabLiObj = new Element('li');
		tabLiObj.id = tabId;
		if (sb.page == currentTab.url)
			tabLiObj.addClass('selected');
		if (currentTab.url == "browse.php" && browseIsActive == false)
			tabLiObj.addClass('deactivated');
		tabAObj = new Element('a');
		tabAObj.appendText(currentTab.title);
		if (currentTab.url == "browse.php" && f(rowCount) != "") {
			tabACount = new Element("span");
			tabACount.addClass("rowcount");
			tabACount.appendText("(" + rowCount + ")");
			tabAObj.appendChild(tabACount);
		}
		if (tabId != this.topTab && !(currentTab.url == "browse.php" && browseIsActive == false) && !currentTab.temp)
			tabAObj.onclick = topTabClicked;
		if (!(currentTab.url == "browse.php" && browseIsActive == false) && !currentTab.temp) {
			var hrefBuild = "#page=" + currentTab.url.substring(0, currentTab.url.length - 4);
			if (f(this.db))
				hrefBuild += "&db=" + this.db;
			if (f(this.table))
				hrefBuild += "&table=" + this.table;
			if (f(this.topTabSet) && this.topTabSet != 0)
				hrefBuild += "&topTabSet=" + this.topTabSet;
			if (tabId != 0)
				hrefBuild += "&topTab=" + tabId;
				
			tabAObj.href = hrefBuild;
		}
		tabLiObj.appendChild(tabAObj);
		topTabsElem.appendChild(tabLiObj);
	}
}

Page.prototype.getTabUrl = function(tabId) {
	tabId = tabId || 0;
	return this.topTabs[this.topTabSet].tabList[tabId].url;
}

Page.prototype.getTabTitle = function(tabId) {
	tabId = tabId || 0;
	return this.topTabs[this.topTabSet].tabList[tabId].title;
}

Page.prototype.removeTempTabs = function() {
	for (var i=0; i<this.topTabs[this.topTabSet].tabCounter; i++) {
		if ((this.topTabs[this.topTabSet].tabList[i].temp) == true && (this.topTabs[this.topTabSet].tabList[i].url != this.page)) {
			this.topTabs[this.topTabSet].removeTab(i);
		}
	}
}

Page.prototype.preload = function() {
	var images = ["close.png", "loading.gif", "openArrow.png", "goto.png", "schemaHeader.png", "info.png", "infoHover.png", 
	"window-button.png", "window-center.png", "window-close.png", "window-header-center.png", "window-header-left.png",
	"window-header-right.png", "window-resize.png", "window-shadow-bottom-left.png", "window-shadow-bottom-right.png",
	"window-shadow-bottom.png", "window-shadow-left.png", "window-shadow-right.png"];
//	var pre;
//	for (var i=0; i<images.length; i++) {
	//	pre = new Image();
	//	pre.src = "images/" + images[i];
//	}
}

Page.prototype.setHash = function() {
	var newHash = "";
	if (f(this.page))
		newHash += "page=" + this.page.substr(0, this.page.length - 4) + "&";
	if (f(this.db))
		newHash += "db=" + this.db + "&";
	if (f(this.table))
		newHash += "table=" + this.table + "&";
	if (f(this.topTabSet) && this.topTabSet != 0)
		newHash += "topTabSet=" + this.topTabSet + "&";
	if (f(this.topTab) && this.topTab != 0)
		newHash += "topTab=" + this.topTab + "&";
	if (f(this.s))
		newHash += "s=" + this.s + "&";
	if (f(this.view))
		newHash += "view=" + this.view + "&";
	if (f(this.sortDir))
		newHash += "sortDir=" + this.sortDir + "&";
	if (f(this.sortKey))
		newHash += "sortKey=" + this.sortKey + "&";
	
	newHash = "#" + newHash.substring(0, newHash.length-1);
	if (window.location.hash != newHash) {
		window.location.hash = newHash;
		this.hashMemory = newHash;
	}
}

Page.prototype.loadHash = function() {
	var hash = window.location.hash;
	var part = hash.substring(1);
	var pairs = part.split("&");
	
	this.hashMemory = window.location.hash;
	
	for (var i=0; i<pairs.length; i++) {
		var pairsplit = pairs[i].split("=");
		var key = pairsplit[0];
		var value = pairsplit[1];
		if (key == "page"){ this.page = value + ".php" }
		if (key == "db"){ this.db = value }
		if (key == "table"){ this.table = value }
		if (key == "topTabSet"){ this.topTabSet = value }
		if (key == "topTab"){ this.topTab = value }
		if (key == "s"){ this.s = value }
		if (key == "view"){ this.view = value }
		if (key == "sortDir"){ this.sortDir = value }
		if (key == "sortKey"){ this.sortKey = value }
	}
}

Page.prototype.checkHashState = function() {
	if (window.location.hash != this.hashMemory) {
		this.resetInternals();
		this.loadHash();
		this.loadPage();
	}
}

Page.prototype.resetInternals = function() {
	this.page = "home.php";
	this.db = "";
	this.table = "";
	this.topTabSet = 0;
	this.topTab = 0;
	this.s = "";
	this.view = "";
	this.sortDir = "";
	this.sortKey = "";
}

function TopTabGroup(name) {
	this.name = name;
	this.tabList = [];
	this.tabCounter = 0;
}

TopTabGroup.prototype.addTab = function(title, url, temp) {
	this.tabList[this.tabCounter++] = new TopTab(title, url, temp);
	return this; // for chaining
}

TopTabGroup.prototype.removeTab = function(id) {
	this.tabList[id] = null;
	this.tabCounter--;
}

function TopTab(title, url, temp) {
	this.title = title;
	this.url = url;
	this.temp = temp;
}

function initializeSidemenu() {
	var menudata = eval(menujson);
	menujson = null;
	var ulmenu = $('databaselist').firstChild;
	var currentItem, newli, newa, togglediv, textdiv;
	var subul, subli, suba, subatext;
	var counter = 0;
	
	for (var i=0; i<menudata['menu'].length; i++) {
		currentItem = menudata['menu'][i];
		newli = returnMenuItem(currentItem['name'], i);
		
		subul = new Element('ul');
		subul.addClass("sublist");
		subul.id = "sublist" + i;
		if (currentItem['items']) {
			for (var j=0; j<currentItem['items'].length; j++) {
				subli = returnSubMenuItem(currentItem.name, currentItem['items'][j].name, currentItem['items'][j].rowcount);
				subul.appendChild(subli);
				
				sb.tableRowCounts[currentItem.name + '_' + currentItem['items'][j].name] = currentItem['items'][j].rowcount;
			}
		}
		newli.appendChild(subul);
		ulmenu.appendChild(newli);
		
		sb.submenuHeights[i] = subul.clientHeight;
		// these properties have to be set after the height is measured
		subul.style.height = "0px";
		subul.style.display = "none";
	}
	
	menudata = null;
}

function addSubMenuItem(sublist, db, table) {
	var subul = $(sublist);
	
	var newItem = returnSubMenuItem(db, table, 0);
	
	subul.appendChild(newItem);
	
	subul.style.height = '';
	subul.style.display = 'block';
	
	recalculateSubmenuHeight(sublist);
}

function returnSubMenuItem(db, table, count) {
	var subli, suba, subacount;
	var subId = sb.$GUID++;
	subli = new Element('li');
	subli.id = "sub" + subId;
	suba = new Element('a');
	suba.href = "#page=browse&db=" + db + "&table=" + table + "&topTabSet=2&topTab=0";
	suba.onclick = subTabClick;
	suba.appendText(table);
	subacount = new Element('span');
	subacount.className = "subcount";
	subacount.appendText("(" + approximateNumber(count) + ")");
	suba.appendChild(subacount);
	subli.appendChild(suba);
	sb.submenuIds[db + '_' + table] = "sub" + subId;
	
	return subli;
}

function returnMenuItem(db, i) {
	var menuli, menua, togglea, texta
	menuli = new Element('li');
	menuli.id = 'db' + i;
	menua = new Element('a');
	menua.onclick = $lambda(false);
	togglea = new Element('a');
	togglea.className = "menutoggler";
	togglea.onclick = toggleMenuClick;
	menua.appendChild(togglea);
	texta = new Element('a');
	texta.className = "menutext";
	texta.href = "#page=dboverview&db=" + db + "&topTabSet=1&topTab=0";
	texta.onclick = databaseClick;
	texta.innerHTML = db;
	menua.appendChild(texta);
	menuli.appendChild(menua);
	sb.submenuIds[db] = "db" + i;
	
	return menuli;
}

function addMenuItem(db) {
	var ulmenu = $E('#databaselist ul');
	var i = ulmenu.childNodes.length;
	var newli = returnMenuItem(db, i);
	
	var subul = new Element('ul');
	subul.className = "sublist";
	subul.id = "sublist" + i;
	subul.style.height = "0px";
	subul.style.display = "none";
	
	newli.appendChild(subul);
	ulmenu.appendChild(newli);
	
	sb.submenuHeights[i] = 0;
}

function showPane(paneId) {
	var panes = $$('#innercontent div[id^=pane]');
	for (var i=0; i<panes.length; i++) {
		panes[i].style.display = 'none';
	}
	$(paneId).style.display = '';
}

function addPane(paneId) {
	if (Browser.Engine.trident)
		clearPanes();
	var pane = new Element('div');
	pane.className = 'pane';
	pane.id = paneId;
	$('innercontent').appendChild(pane);
	showPane(paneId);
}

function generatePrompt(prepend, postpend, single, multiple, parameter, showQuery, context) {
	if (context) {
		var grid = $(context);
		var inputs = $ES("input", grid);
	} else {
		var grid = sb.grid;
		var inputs = $ES("input", sb.leftChecks);
	}
	
	if (f(grid)) {
		var buildList = "";
		var m = 0;
		for (var i=0; i<inputs.length; i++) {
			if (inputs[i].type == "checkbox" && inputs[i].checked == true) {
				buildList += prepend + inputs[i].get("querybuilder") + postpend + ";\n ";
				m++;
			}
		}
		if (buildList) {
			var prompter = gettext("Are you sure you want to") + " ";
			if (m == 1)
				prompter += single + "? ";
			else
				prompter += multiple + "? ";
			
			if (showQuery) {
				var formattedQuery = buildList.replace(/\n/g, "<br />");
				
				if (m == 1)
					prompter += gettext("The following query will be run:");
				else
					prompter += gettext("The following queries will be run:");
					
				prompter += " <div class=\"querybox\">" + formattedQuery + "</div>";
			}
			
			buildList = encodeURIComponent(buildList.replace(/\n/g, ""));
			buildList = buildList.replace(/'/g, "\\'");
			
			buildUrl = parameter + "=" + buildList;
			
			if (sb.page == "browse.php") {
				if (f(sb.view))
					buildUrl += "&view=" + sb.view;
					
				if (f(sb.s))
					buildUrl += "&s=" + sb.s;
					
				if (f(sb.sortKey))
					buildUrl += "&sortKey=" + sb.sortKey
					
				if (f(sb.sortDir))
					buildUrl += "&sortDir=" + sb.sortDir;
			}
			
			showDialog(gettext("Confirm"),
				prompter,
				"var x = new XHR({url: \"" + sb.page + "\", onSuccess: finishTabLoad}).send(\"" + buildUrl + "\");"
			);
		}
	}
}

function showDialog(title, content, action) {	
	createWindow(title, content, {isDialog: true, dialogAction: action});
}

function submitForm(formId) {
	var theForm = $(formId);
	var action = theForm.get("action");
	
	if (!action)
		action = sb.page;
	
	var x = new XHR({url: action, onSuccess: finishTabLoad}).send(theForm.toQueryString());
}

var XHR = new Class({

	Extends: Request,
	
	initialize: function(options) {
		
		if (!options.url)
			options.url = sb.page;
		
		if (options.url.indexOf("?") == -1)
			options.url += "?ajaxRequest=" + sb.$GUID++;
		else
			options.url += "&ajaxRequest=" + sb.$GUID++;
		
		options.url += "&requestKey=" + requestKey;
		
		if (f(sb.db))
			options.url += "&db=" + sb.db;
		if (f(sb.table))
			options.url += "&table=" + sb.table;
		
		this.parent(options);
		
		if (options && options.showLoader != false) {
			show('load');
			
			this.addEvent("onSuccess", function() {
				hide('load');
			});
			
			this.addEvent("onFailure", function() {
				hide('load');
				createWindow(gettext("Error"), gettext("There was an error receiving data from the server."), {isDismissible: true});
			});
		}
	},
	
	// redefined to avoid auto script execution
	success: function(text, xml) {
		this.onSuccess(text, xml);
	}
	
});

function gettext(str) {
	if (f(getTextArr[str]) != "")
		return getTextArr[str];
	else
		return str;
}

function printf() {
	var argv = printf.arguments;
	var argc = parseInt(argv.length);
	
	var inputString = argv[0];
	
	for (var i=1; i<argc; i++) {
		var position = inputString.indexOf("%s");
		var firstPart = inputString.substring(0, position + 2);
		var lastPart = inputString.substring(position + 2);
		firstPart = firstPart.replace("%s", argv[i]);
		inputString = firstPart + lastPart;
	}
	
	return inputString;
}

function fullTextWindow(rowId) {
	var rowQuery = $E(".check" + rowId, sb.pane).get("queryBuilder");
	var fullQuery = "SELECT * FROM " + returnQuote() + sb.table + returnQuote() + " " + rowQuery;
	var loadWin = createWindow(gettext("Loading..."), gettext("Loading..."));
	var x = new XHR({
		url: "ajaxfulltext.php", 
		onSuccess: function(responseText) {
			$(loadWin).dispose();
			createWindow(gettext("Full Text"), responseText)
		},
		onFailure: function() {
			$(loadWin).dispose();
		}
	}).send("query=" + fullQuery);
}

function createWindow(title, content, options) {
	options = options || {};
	
	var windowInnerWidth = getWindowWidth();
	
	var textWindow = new Element('div');
	textWindow.className = 'fulltextwin';
	if (options.isDialog || options.isDismissible) {
		textWindow.className += " dialog";
	}
	
	var windowId = "window" + sb.$GUID++;
	textWindow.id = windowId;
	
	var leftValue = Math.round((windowInnerWidth - 475) / 2);
	
	textWindow.style.left = leftValue + "px";
	
	var topValue = 120;
	
	if (window.scrollY)
		topValue += window.scrollY;
	
	textWindow.style.top = topValue + "px";
	textWindow.style.zIndex = sb.$GUID;
	var windowMain = new Element('div');
	windowMain.className = 'fulltextmain';
	var windowHeader = new Element('div');
	windowHeader.className = 'fulltextheader';
	windowHeader.addEvent("mousedown", focusWindow);
	windowHeader.addEvent("mousedown", startDrag);
	var windowHeaderContent = '<table cellspacing="0" width="100%"><tr><td class="headertl"></td><td class="headercenter"><p><img class="fulltextimage" src="http://sqlbuddylite.googlecode.com/svn/tags/r/window-close.png" align="right" onclick="closeWindow(\'' + windowId + '\')" />' + title + '</p></td><td class="headertr"></td></tr></table>';
	windowHeader.set('html', windowHeaderContent);
	windowMain.appendChild(windowHeader);
	
	if (options.isDialog && f(options.dialogAction) != "") {
		content += '<div class="buttons"><table cellspacing="0" width="100%"><tr><td>&nbsp;</td><td align="right" width="20" style="padding-right: 8px"><input type="submit" id="' + windowId + 'Click" class="windowbutton" value="' + gettext("Okay") + '" /></td><td align="right" width="20"><input type="button" onclick="closeWindow(\'' + windowId + '\')" class="windowbutton" value="' + gettext("Cancel") + '" /></td></tr></table></div>';
	} else if (options.isDismissible) {
		content += '<div class="buttons"><table cellspacing="0" width="100%"><tr><td>&nbsp;</td><td align="right"><input type="submit" id="' + windowId + 'Click" onclick="closeWindow(\'' + windowId + '\')" class="windowbutton" value="' + gettext("Okay") + '" /></td></tr></table></div>';
	}
	
	var windowInner = new Element('div');
	windowInner.className = 'fulltextinner';
	var innerContent = '<table cellspacing="0" width="100%"><tr><td class="mainl"></td><td class="maincenter"><div class="fulltextcontent" style="max-height: 400px">' + content + '</div>';
	
	if (!(options.isDialog || options.isDismissible)) {
		innerContent += '<div class="resizeHandle"><img src="images/window-resize.png" id="resize' + windowId + '"></div>';
	}
	
	innerContent += '</td><td class="mainr"></td></tr></table>';
	
	windowInner.set('html', innerContent);
	windowMain.appendChild(windowInner);
	textWindow.appendChild(windowMain);
	
	var windowFooter = new Element('div');
	windowFooter.className = 'fulltextfooter';
	var footerCode = '<table cellspacing="0" width="100%"><tr><td class="footerbl"></td><td class="footermiddle">&nbsp;</td><td class="footerbr"></td></tr></table>';
	windowFooter.set('html', footerCode);
	
	textWindow.appendChild(windowFooter);
	document.body.appendChild(textWindow);
	
	if (options.isDialog == true && f(options.dialogAction) != "") {
		var okayClick = $(windowId + 'Click');
		okayClick.addEvent("click", function() {
			closeWindow(windowId);
			eval(options.dialogAction);
		});
		okayClick.focus();
	} else if (options.isDismissible == true) {
		var okayClick = $(windowId + 'Click');
		okayClick.focus();
	} else if (!(options.isDialog || options.isDismissible)) {
		var resizeHandle = $('resize' + windowId);
		resizeHandle.addEvent("mousedown", startResize);
	}
	
	return windowId;
}

function show(a) {
	$(a).style.display = '';
}

function hide(a) {
	$(a).style.display = 'none';
}

function runKeyboardShortcuts(e) {
	var event = new Event(e);
	if (!((event.target.nodeName == "INPUT" && (event.target.type == "text" || event.target.type == "password")) || (event.target.nodeName == "TEXTAREA") || event.meta || event.control)) {
		if (event.key == "a") {
			checkAll();
		} else if (event.key == "n") {
			checkNone();
		} else if (event.key == "e") {
			if (sb.page == "browse.php" || sb.page == "structure.php" || sb.page == "users.php")
				editSelectedRows();
		} else if (event.key == "d") {
			if (sb.page == "structure.php")
				deleteSelectedColumns();
			else if (sb.page == "users.php")
				deleteSelectedUsers();
			else if (sb.page == "browse.php")
				deleteSelectedRows();
			else if (sb.page == "dboverview.php")
				dropSelectedTables();
		} else if (event.key == "r") {
			sb.loadPage();
		} else if (event.key == "f" && sb.page == "browse.php") {
			if ($('firstNav'))
				eval($('firstNav').get("onclick"));
		} else if (event.key == "g" && sb.page == "browse.php") {
			if ($('prevNav'))
				eval($('prevNav').get("onclick"));
		} else if (event.key == "h" && sb.page == "browse.php") {
			if ($('nextNav'))
				eval($('nextNav').get("onclick"));
		} else if (event.key == "l" && sb.page == "browse.php") {
			if ($('lastNav'))
				eval($('lastNav').get("onclick"));
		} else if (event.key == "q") {
			var tabId = 0;
			while (sb.getTabUrl(tabId) != "query.php") {
				tabId++;
			}
			topTabLoad(tabId);
			
			event.stop();
			event.stopPropagation();
		} else if (event.key == "o" && sb.page == "dboverview.php") {
			optimizeSelectedTables();
		}
		
	} else if (event.target.nodeName == "TEXTAREA" && event.control && event.key == "enter") {
		var curr = $(event.target);
		while (curr && curr.get('tag') != "form") {
			curr = $(curr.parentNode);
		}
		
		if (curr) {
			currButton = $E("input[type=submit]", curr);
			if (currButton) {
				currButton.click();
			}
		}
	}
}

function getWindowWidth() {
	if (window.innerWidth)
		return window.innerWidth;
	else
		return document.documentElement.clientWidth;
}

function getWindowHeight() {
	if (window.innerHeight)
		return window.innerHeight;
	else
		return document.documentElement.clientHeight;
}

function getScrollbarWidth() {
	
	var outer = new Element('div');
	outer.style.position = 'absolute';
	outer.style.top = '-1000px';
	outer.style.left = '-1000px';
	outer.style.width = '100px';
	outer.style.height = '50px';
	outer.style.overflow = 'hidden';
	
	var inner = new Element('div');
	inner.style.width = '100%';
	inner.style.height = '200px';
	
	outer.appendChild(inner);
	document.body.appendChild(outer);
	
	var w1 = inner.offsetWidth;
	outer.style.overflow = "auto";
	var w2 = inner.offsetWidth;
	
	document.body.removeChild(outer);
	
	return (w1 - w2);
};

function addAnimation(id, finish) {
	var elem = $(id);
	
	//remove duplicates
	for (var i in animationStack) {
		if (animationStack[i][0] == elem)
			animationStack.splice(i, 1);
	}
	
	var start = elem.offsetHeight;
	
	var change = finish - start;
	
	var totalFrames = 15;
	
	if (window.gecko)
		totalFrames -= 5;
	
	animationStack.push([elem, start, change, 0, totalFrames]);
	if (animationStack.length == 1)
		animate();
}

function animate() {
	var j, elem, start, change, currentFrame, totalFrames;
	for (var i = 0; i < animationStack.length; i++) {
		
		j = parseInt(i);
		
		elem = animationStack[j][0];
		start = animationStack[j][1];
		change = animationStack[j][2];
		animationStack[j][3] += 1;
		currentFrame = animationStack[j][3];
		totalFrames = animationStack[j][4];
		
		var newHeight = sineInOut(currentFrame, start, change, totalFrames);
		
		elem.style.height = newHeight + "px";
		
		if (currentFrame >= totalFrames) {
			animationStack.splice(j, 1);
			
			//if the menu is expanded, take off the explicit height attribute
			if (elem.style.height != "0px") {
				elem.style.height = '';
			}
		}
	}
	if (animationStack.length > 0)
		setTimeout('animate()', 25);
}

function sineInOut(t, b, c, d) {
	return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
}


var mouseX = -1;
var mouseY = -1;
var lastWidth = -1;
var lastHeight = -1;
var lastLeft = -1;
var lastTop = -1;
var activeContent;
var compX = 0;
var compY = 0;
var activeColumnId = -1;
var activeColumn;
var styleNodeKeys = [];
var styleNodes = [];

function startResize(e) {
	var event = new Event(e);
	
	activeWindow = event.target;
	while (activeWindow != null && activeWindow.className.indexOf("fulltextwin") == -1) {
		activeWindow = activeWindow.parentNode;
	}
	
	activeContent = $E(".fulltextcontent", activeWindow);
	
	lastWidth = parseInt(activeWindow.offsetWidth);
	lastHeight = parseInt(activeContent.offsetHeight);
	mouseX = event.page.x;
	mouseY = event.page.y;
	
	activeContent.style.height = lastHeight + "px";
	activeContent.style.maxHeight = '';
	
	window.addEvent("mousemove", doResize);
	window.addEvent("mouseup", endResize);
	
	return false;
}

function doResize(e) {
	if (activeWindow) {
		var event = new Event(e);
		
		var diffX = event.page.x - mouseX;
		var diffY = event.page.y - mouseY;
		
		if (compX > 0 && compX > diffX) {
			compX -= diffX;
			diffX = 0;
		} else if (compX > 0) {
			diffX -= compX;
			compX = 0;
		}
		
		if (compY > 0 && compY > diffY) {
			compY -= diffY;
			diffY = 0;
		} else if (compY > 0) {
			diffY -= compY;
			compY = 0;
		}
		
		lastWidth = lastWidth + diffX;
		lastHeight = lastHeight + diffY;
		
		if (lastWidth < 175) {
			compX += 175 - lastWidth;
			lastWidth = 175;
		}
		
		if (lastHeight < 100) {
			compY += 100 - lastHeight;
			lastHeight = 100;
		}
		
		mouseX = event.page.x;
		mouseY = event.page.y;
		
		activeWindow.style.width = lastWidth + "px";
		activeContent.style.height = lastHeight + "px";
	}
}

function endResize() {
	activeWindow = null;
	activeContent = null;
	compX = 0;
	compY = 0;
	window.removeEvent("mousemove", doResize);
	window.removeEvent("mouseup", endResize);
}

function startDrag(e) {
	var event = new Event(e);
	
	activeWindow = event.target;
	while (activeWindow != null && activeWindow.className.indexOf("fulltextwin") == -1) {
		activeWindow = activeWindow.parentNode;
	}
	
	lastLeft = activeWindow.style.left;
	lastLeft = parseInt(lastLeft.substring(0, lastLeft.length - 2));
	lastTop = activeWindow.style.top;
	lastTop = parseInt(lastTop.substring(0, lastTop.length - 2));
	mouseX = event.page.x;
	mouseY = event.page.y;
	
	window.addEvent("mousemove", doDrag);
	window.addEvent("mouseup", endDrag);
	
	return false;
}

function doDrag(e) {
	if (activeWindow) {
		var event = new Event(e);
		
		var diffX = event.page.x - mouseX;
		var diffY = event.page.y - mouseY;
		
		lastLeft = lastLeft + diffX;
		lastTop = lastTop + diffY;
		mouseX = event.page.x;
		mouseY = event.page.y;
		
		activeWindow.style.left = lastLeft + "px";
		activeWindow.style.top = lastTop + "px";
	}
}

function endDrag() {
	activeWindow = null;
	window.removeEvent("mousemove", doDrag);
	window.removeEvent("mouseup", endDrag);
}

function startColumnResize(e) {
	var event = new Event(e);
	
	activeColumn = $(event.target.offsetParent.previousSibling.firstChild);
	
	activeColumnId = parseInt(activeColumn.getProperty("column"));
	
	lastWidth = parseInt(activeColumn.clientWidth) - 11; // -11 to account for padding
	mouseX = event.page.x;
	
	document.body.style.cursor = "ew-resize";
	
	window.addEvent("mousemove", columnResize);
	window.addEvent("mouseup", endColumnResize);
	
	return false;
}

function columnResize(e) {
	if (activeColumn) {
		var event = new Event(e);
		
		var diff = (event.page.x - mouseX);
		
		lastWidth = (lastWidth + diff);
		mouseX = event.page.x;
		
		var removeLater = -1;
		var keyName = 'pane' + sb.topTab + '_' + activeColumnId;
		
		for (var i=0; i<styleNodeKeys.length; i++) {
			if (styleNodeKeys[i] == keyName) {
				document.getElementsByTagName("head")[0].removeChild(styleNodes[i]);
				removeLater = i;
			}
		}
		
		if (removeLater >= 0) {
			styleNodes.splice(removeLater, 1);
			styleNodeKeys.splice(removeLater, 1);
		}
		
		var newNode = new Element("style");
		newNode.setAttribute("type", "text/css");
		
		newNode.appendText("#pane" + sb.topTab + " .column" + activeColumnId + " { width: " + lastWidth + "px !important }");
		document.getElementsByTagName("head")[0].appendChild(newNode);
		
		styleNodes.push(newNode);
		styleNodeKeys.push(keyName);
	}
}

function endColumnResize() {
	document.body.style.cursor = "";
	activeColumn = null;
	window.removeEvent("mousemove", columnResize);
	window.removeEvent("mouseup", endColumnResize);
}

function clearColumnSizes() {
	if (styleNodes.length > 0) {
		for (var i=0; i<styleNodes.length; i++) {
			if (f(styleNodes[i]) != "") {
				document.getElementsByTagName("head")[0].removeChild(styleNodes[i]);
			}
		}
		styleNodes = [];
		styleNodeKeys = [];
	}
}