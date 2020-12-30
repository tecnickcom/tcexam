<script>
/*!
 * TCExam mobile theme based on Pico.css (https://picocss.com).
 * Author: Maman Sulaeman | email: mamansulaeman86@gmail.com | telegram: @mamans86 | blog: https://mamans86.blogspot.com
 * Info: https://picoman-theme.blogspot.com
 */
var $inputTimer = document.getElementById("timer");
$inputTimer.insertAdjacentHTML("afterend", "<span id='header_button'>☰</span>");

var $toggle = document.getElementById("header_button")
var $slider = document.getElementById("scrollayer");

$toggle.addEventListener("click", function() {
    var isOpen = $slider.classList.contains("slide-in");
    $slider.setAttribute("class", isOpen ? "slide-out" : "slide-in");
	$toggle.setAttribute("class", isOpen ? "hb_slideout" : "hb_slidein");	
	if(isOpen){
		$toggle.textContent="☰";	
	}else{
		$toggle.innerHTML="&times;";
	}
});

if(document.getElementById("testform")){
	if(document.querySelector('ol.qlist')){
		document.querySelector('ol.qlist').addEventListener('click', e => {
			var inputButton = document.getElementById(e.target.firstElementChild.id);
			inputButton.click();
		});
	}
}

function isInPage(node) {
  return (node === document.body) ? false : document.body.contains(node);
}

function togglePassField(id){
	if(document.getElementById(id)){
		document.getElementById(id).insertAdjacentHTML("afterend", "<span id='"+id+"_eye' class='icon-eye'></span>");
		
		var a = document.getElementById(id+"_eye");
		
		a.addEventListener("click", function() {
			var b = a.classList.contains("icon-eye");
			a.setAttribute("class", b ? "icon-eye-unblocked" : "icon-eye");
			document.getElementById(id).setAttribute("type", b ? "input" : "password");
		});
	}
}

togglePassField("currentpassword");
togglePassField("newpassword");
togglePassField("newpassword_repeat");
togglePassField("xuser_password");
 
 if(document.querySelectorAll(".okbox")){
	var okBox = document.querySelectorAll(".okbox");
    for (var i = 0; i < okBox.length; i++) {
        var str = okBox[i].innerHTML = "&check;";
        okBox[i].innerHTML = str;
    }
 }
var adaForm = document.forms.length;
if(adaForm===1){
	var tceFormBox = document.querySelector(".tceformbox");
	var tceContentBox = document.querySelector(".tcecontentbox");
	var tceTestList = document.querySelector(".testlist");
	if(tceFormBox){
		tceFormBox.className += " anyFormBox";
	}
	if(tceContentBox && !tceTestList){
		tceContentBox.className += " anyFormBox";
	}
	if(!tceFormBox && !tceContentBox){
		document.querySelector(".container").className += " anyFormBox";
	}
	if(tceTestList){
		var buttonGreen = document.querySelector(".buttongreen");
		var buttonBlue = document.querySelector(".buttonblue");
		if(buttonGreen){
			buttonGreen.setAttribute("onclick","clearUnsure()");
		}
		if(buttonBlue){
			buttonBlue.setAttribute("onclick","clearUnsure()");
		}
		
	}
}

var qNum = document.getElementById("confirmanswer");
var fTestForm = document.getElementById("testform");
if(fTestForm){
	var qNum = qNum.value;
	var qNumber = qNum.replace(/\D/g,'');
	fTestForm.insertAdjacentHTML("beforebegin", "<div id='qTopBar'><span id='qNum'>"+qNumber+"</span><span id='fontResizer'><span id='fontplus' onclick='zoomintext(\".tcecontentbox\")'>&plus;</span><span id='fontminus' onclick='zoomouttext(\".tcecontentbox\")'>&minus;</span><span id='unsure' onclick='addUnsure()' class='icon-flag'></span></span></div>");
}

if(fTestForm){
	var lsFontSize = localStorage.getItem("fontSize");
	if(lsFontSize){
		document.querySelector(".tcecontentbox").style.fontSize =lsFontSize+'px';
		if(document.querySelector(".answer")){
			document.querySelector(".answer").style.fontSize =lsFontSize+'px';
		}
	}
}

function zoomintext(idText){
	if(fTestForm){
		var fs=parseFloat(window.getComputedStyle(document.querySelector(".tcecontentbox")).fontSize);
		newfontSize=fs*(1.1);
		document.querySelector(idText).style.fontSize =newfontSize+'px';
		if(document.querySelector(".answer")){
			document.querySelector(".answer").style.fontSize =newfontSize+'px';
		}
		fontSize=newfontSize;
		localStorage.setItem("fontSize", fontSize);
	}
}
function zoomouttext(idText){
	if(fTestForm){
		var fs=parseFloat(window.getComputedStyle(document.querySelector(".tcecontentbox")).fontSize);
		newfontSize=fs/(1.1);
		document.querySelector(idText).style.fontSize =newfontSize+'px';
		if(document.querySelector(".answer")){
			document.querySelector(".answer").style.fontSize =newfontSize+'px';
		}
		fontSize=newfontSize;
		localStorage.setItem("fontSize", fontSize);
	}	
}

function clearUnsure(){
	localStorage.setItem('unsure', '[]');
}

function setUnsureLiBg(a){
	var direction = window.getComputedStyle(document.querySelector("html")).direction;
	if(direction==='ltr'){
		document.querySelectorAll("ol.qlist li")[a].style.backgroundImage = "linear-gradient(90deg, transparent 50%, rgb(255, 241, 118) 100%), var(--icon-flag)";
		document.querySelectorAll("ol.qlist li")[a].style.backgroundPosition = "center right";
	}else{
		document.querySelectorAll("ol.qlist li")[a].style.backgroundImage = "linear-gradient(270deg, transparent 50%, rgb(255, 241, 118) 100%), var(--icon-flag)";
		document.querySelectorAll("ol.qlist li")[a].style.backgroundPosition = "center left";
	}
	document.querySelectorAll("ol.qlist li")[a].style.backgroundRepeat = "no-repeat";	
	document.querySelectorAll("ol.qlist li")[a].style.backgroundSize = "contain";
}
var qNumSpan = document.getElementById("qNum");
if(localStorage.getItem('unsure') && fTestForm){
	const unsures = JSON.parse(localStorage.getItem('unsure'));
	var unsureBtn = document.getElementById("unsure");
	unsures.forEach(el => {
		var currNo = document.getElementById("qNum").textContent-1;
		if(currNo===el){
			unsureBtn.setAttribute("onclick","removeUnsure()");
			unsureBtn.style.backgroundColor = "#fff176";
			unsureBtn.style.color = "#575757";
			unsureBtn.style.borderColor = "#08769b";
			
			qNumSpan.style.backgroundColor = "#fff176";
			qNumSpan.style.color = "#575757";
			qNumSpan.style.borderColor = "#08769b";
		}
	  setUnsureLiBg(el);
	});
}

function addUnsure(){
	if(fTestForm){
		var index = document.getElementById("qNum").textContent-1;
		var unsureBtn = document.getElementById("unsure");
		unsureBtn.setAttribute("onclick","removeUnsure()");
		
		unsureBtn.style.backgroundColor = "#fff176";
		unsureBtn.style.color = "#575757";
		unsureBtn.style.borderColor = "#08769b";
		
		qNumSpan.style.backgroundColor = "#fff176";
		qNumSpan.style.color = "#575757";
		qNumSpan.style.borderColor = "#08769b";
		
		setUnsureLiBg(index);
		let unsure;		
		if(localStorage.getItem('unsure') === null){
			unsure = [];
		}else{
			unsure = JSON.parse(localStorage.getItem('unsure'));
		}
		unsure.push(index);
		localStorage.setItem('unsure', JSON.stringify(unsure));
	}
}

function removeUnsure(){
	if(fTestForm){
		var index = document.getElementById("qNum").textContent-1;
		var unsureBtn = document.getElementById("unsure");
		unsureBtn.setAttribute("onclick","addUnsure()");
		unsureBtn.removeAttribute("style");
		qNumSpan.removeAttribute("style");
		document.querySelectorAll("ol.qlist li")[index].removeAttribute("style");
		let unsure;
		if(localStorage.getItem('unsure') === null){
			unsure = [];
		}else{
			unsure = JSON.parse(localStorage.getItem('unsure'));
		}
		for(var i = 0; i < unsure.length;){
			if(unsure[i] === index){
				unsure.splice(i, 1);
			}else{
				i++;
			}
		}
		localStorage.setItem('unsure', JSON.stringify(unsure));
	}
}
</script>
