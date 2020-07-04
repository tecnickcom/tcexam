<?php
	//fill this array value with your additional JS file
	$add_js = array('jquery.min.js', 'vendor/modernizr-3.11.2.min.js', 'main.js', 'plugins.js');
	foreach($add_js as $key => $val){
		echo '<script src="'.K_PATH_JSCRIPTS.'additional/'.K_PUBLIC_THEME.'/'.$val.'"></script>'.K_NEWLINE;
	}
?>
