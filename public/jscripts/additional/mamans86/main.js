$("a#timersection").click(function(){
	$("div#scrollayer").toggle();
});

$("div.warning, div.error, div.message").click(function(){
	$(this).hide();
})

$("form#form_usereditor div.row span.label acronym.requiredonbox").text("*");
$("form#form_usereditor div.row span.label acronym.requiredoffbox").text("");
