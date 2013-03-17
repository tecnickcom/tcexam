/**
 *  $Id: scriptqueue.js 761 2011-05-06 14:13:15Z wingedfox $
 *  $HeadURL: https://svn.debugger.ru/repos/jslibs/BrowserExtensions/tags/BrowserExtensions.029/scriptqueue.js $
 *
 *  Dynamically load scripts and script queues (when load order is important)
 *
 **********NOTE********
 *  If you need to load any scripts before ScriptQueue exists, use the following snippet
 *  <code>
 *      if (!(window.ScriptQueueIncludes instanceof Array)) window.ScriptQueueIncludes = []
 *      window.ScriptQueueIncludes = window.ScriptQueueIncludes.concat(scriptsarray);
 *  </code>
 *  ScriptQueue loads all the scripts, queued before its' load in the ScriptQueueIncludes
 **********
 *
 *  @author Ilya Lebedev <ilya@lebedev.net>
 *  @modified $Date: 2011-05-06 18:13:15 +0400 (Пт., 06 мая 2011) $
 *  @version $Rev: 761 $
 *  @license LGPL 2.1 or later
 *
 *  @class ScriptQueue
 *  @param {Function} optional callback function, called on each successful script load
 *  @scope public
 */
ScriptQueue=function(i){var I=this,l=arguments.callee;if('function'!=typeof i)i=function(){};var o=[];I.load=function(C){O(C,i);};I.queue=function(C){var e=o.length;o[e]=C;if(!e)O(C,_);};var O=function(C,i){var e,v=l.scripts;if(e=v.hash[C]){v=l.scripts[e];if(v[2]){setTimeout(function(){i(C,v[2])},1);}else{v[1].push(i);}}else{e=v.length;v[e]=[C,[i],false];v.hash[C]=e;Q(C);}};var Q=function(C){if(document.body){var e=document.createElement('script'),v=document.getElementsByTagName("head")[0];e.type="text/javascript";e.charset="UTF-8";e.src=C;e.rSrc=C;e.onerror=e.onload=e.onreadystatechange=c;e.timeout=setTimeout(function(){c.call(e,{type:'error',q:C})},10000);v.appendChild(e);}else{document.write("<scr"+"ipt onload=\"return 1;\" src=\""+C+"\" type=\"text/javascript\" charset=\"UTF-8\"></scr"+"ipt>");c.call({'rSrc':C},{'type':'load'});}};var _=function(C,e){i(C,e);var v;while((!v||v==C)&&o.length){v=o.shift();}if(v&&e){O(v,arguments.callee);}else{setTimeout(function(){i(null,e)},1);o.length=0}};var c=function(C){var e=l.scripts,v=e.hash[this.rSrc],C=C||window.event,V;clearTimeout(this.timeout);e=e[v];if(e&&!e[2]){if(C&&'readystatechange'==C.type&&'loading'==this.readyState){}else{if(C&&('load'==C.type||'complete'==this.readyState||'loaded'==this.readyState)){e[2]=V=true}else if(!C||'error'==C.type||(C.toString&&C.toString().match(/error/i))){V=false}for(var x=0,i=e[1],X=i.length;x<X;x++){i[x](e[0],V);}if(!V){delete l.scripts.hash[this.rSrc];delete l.scripts[v]}}}}};ScriptQueue.scripts=[false];ScriptQueue.scripts.hash={};ScriptQueue.queue=function(i,I){if(!i.length)return;var l=new ScriptQueue(I);for(var o=0,O=i.length;o<O;o++){l.queue(i[o]);}};ScriptQueue.load=function(i,I){if(i){(new ScriptQueue(I)).load(i);}};if(window.ScriptQueueIncludes instanceof Array){ScriptQueue.queue(window.ScriptQueueIncludes);}
