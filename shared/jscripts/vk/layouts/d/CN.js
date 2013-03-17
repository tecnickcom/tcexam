/**
 * $Id: CN.js 643 2009-07-09 15:19:14Z wingedfox $
 *
 * Chinese char processor implementation
 *
 * This software is protected by patent No.2009611147 issued on 20.02.2009 by Russian Federal Service for Intellectual Property Patents and Trademarks.
 *
 * @author Konstantin Wiolowan
 * @copyright 2007-2009 Konstantin Wiolowan <wiolowan@mail.ru>
 * @version $Rev: 643 $
 * @lastchange $Author: wingedfox $ $Date: 2009-07-09 19:19:14 +0400 (Чт, 09 июл 2009) $
 */
VirtualKeyboard.Langs.CN=new function(){var i=this;i.INPArr=[];i.processChar=function(I,l){var o,O,Q;if(I=='\u0008'){if(l&&(O=l.slice(0,-1))){VirtualKeyboard.IME.show(i.INPArr[O.toLowerCase()]||[]);return[O,O.length]}else{VirtualKeyboard.IME.hide();return['',0]}}else{O=l+I;Q=i.INPArr[O.toLowerCase()]||[];if(Q.length){VirtualKeyboard.IME.show((typeof Q=='string')?i.INPArr[O.toLowerCase()]=Q.split(''):Q);return[O,O.length]}else if(VirtualKeyboard.IME.getSuggestions().length){if(isFinite(o=parseInt(I))){O=VirtualKeyboard.IME.getChar(o);if(!O){return[l,l.length]}else{VirtualKeyboard.IME.hide();return[O,0]}}else if((Q=i.INPArr[I.toLowerCase()]||[]).length){O=VirtualKeyboard.IME.getSuggestions()[0];VirtualKeyboard.IME.setSuggestions((typeof Q=='string')?i.INPArr[O.toLowerCase()]=Q.split(''):Q);return[O+I,1]}else{O=VirtualKeyboard.IME.getSuggestions()[0];VirtualKeyboard.IME.hide();return[O+(I.charCodeAt()==10?'':I),0]}}}return[l+I,0]}};
