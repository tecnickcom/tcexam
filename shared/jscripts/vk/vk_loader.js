/**
 *  $Id: vk_loader.js 777 2011-06-17 17:17:48Z wingedfox $
 *
 *  Keyboard loader
 *
 *  This software is protected by patent No.2009611147 issued on 20.02.2009 by Russian Federal Service for Intellectual Property Patents and Trademarks.
 *
 *  @author Ilya Lebedev
 *  @copyright 2006-2011 Ilya Lebedev <ilya@lebedev.net>
 *  @version $Rev: 777 $
 *  @lastchange $Author: wingedfox $ $Date: 2011-06-17 21:17:48 +0400 (Пт., 17 июня 2011) $
 */
if(!window.VirtualKeyboard){VirtualKeyboard=new function(){var i=this,I=null;i.show=i.hide=i.toggle=i.attachInput=function(){window.status='VirtualKeyboard is not loaded yet.';if(!I)setTimeout(function(){window.status=''},1000);};i.isOpen=function(){return false};i.isReady=function(){return false}};(function(){var i=(function(_){var c=document.getElementsByTagName('script'),C=new RegExp('^(.*/|)('+_+')([#?]|$)');for(var l=0,e=c.length;l<e;l++){var v=String(c[l].src).match(C);if(v){if(!v[1])v[1]="";if(v[1].match(/^((https?|file|widget)\:\/{2,}|\w:[\\])/))return v[1];if(v[1].indexOf("/")==0)return v[1];var V=document.getElementsByTagName('base');if(V[0]&&V[0].href)return V[0].href+v[1];return(document.location.href.match(/(.*[\/\\])/)[0]+v[1]).replace(/^\/+/,"");}}return null})('vk_loader.js');var I=["extensions/e.js"];for(var l=0,o=I.length;l<o;l++)I[l]=i+I[l];I[l++]=i+'virtualkeyboard.js';I[l]=i+'layouts/layouts.js';if(window.ScriptQueue){ScriptQueue.queue(I);}else{if(!(window.ScriptQueueIncludes instanceof Array))window.ScriptQueueIncludes=[];window.ScriptQueueIncludes=window.ScriptQueueIncludes.concat(I);if(document.body){var O=document.createElement('script');O.type="text/javascript";O.src=i+'extensions/scriptqueue.js';var Q=document.getElementsByTagName("head")[0];Q.appendChild(O);}else{document.write("<scr"+"ipt type=\"text/javascript\" src=\""+i+'extensions/scriptqueue.js'+"\"></scr"+"ipt>");}}})();}
