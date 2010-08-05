<?php
include("xmlparse.php");
$page = 1;
if ($_POST) {
	extract($_POST);
	
	if (strlen($postcode) > 0 && strlen($lat) == 0) {
		$postcode = get_xml("http://www.uk-postcodes.com/postcode/". str_replace(" ", "", strtoupper($postcode)) .".xml");
		$lat = $postcode['result']['geo']['lat']['value'];
		$lng = $postcode['result']['geo']['lng']['value'];
		echo $lat;
	}
		
	$curl = 'curl -F "service=Fixmystreet Mobile"  -F "category='.$category.'" -F "name='.$name.'" -F "subject='.$title.'" -F "detail='.$detail.'" -F "email='. $email.'" -F "phone='. $phone.'" -F "lat='.$lat.'" -F "lon='.$lng.'" http://www.fixmystreet.com/import';
	
	$output = shell_exec($curl);
	$outputs = explode("ERROR:", $output);
	if ($outputs[0] == "SUCCESS") {
	$success = TRUE;
	$page = 2;
	} else {
	unset($outputs[0]);
	$success = FALSE;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Fixmystreet</title>
	<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	<meta http-equiv="Cache-Control" content="max-age=2000" /> 
	<link type="text/css" rel="stylesheet" href="style.css" media="all" />
	<link rel="apple-touch-icon" href="touchicon.png"/>
	<?php
	if ($page = 1) {
	?>
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<script src="js/gears_init.js"></script>
	<script src="js/geo.js"></script> 
	<script src="js/jquery.js"></script> 
	<script type="text/javascript" src="http://dev.jquery.com/view/trunk/plugins/validate/jquery.validate.js"></script>
	<script>
	function lookupLocation() {
		$("#locator").html("Fetching location...");
		$("#locator").toggleClass("loading");
		$('#submit').attr("disabled", "disabled");
		geo_position_js.getCurrentPosition(getLocation, locationError, {enableHighAccuracy:true, maximumAge: 75000});
	}
	
	function getLocation(loc) {
		$("#locator").html("Location Fetched!");
		$("#locator").toggleClass("done");
		$("#lat").val(loc.coords.latitude);
		$("#lng").val(loc.coords.longitude);
		$('#submit').removeAttr("disabled");
	}
	
	function locationError() {
		$("#fail").html('Unable to determine your location. Please manually enter your location in the postcode box below.');
		$('#postcodes').addClass('visible');
		$('#submit').removeAttr("disabled");
		$('#postcodes').toggleClass('invisible');
		$('#locator').addClass('invisible');
	}
	
	$(document).ready(function() {
	
	if (geo_position_js.init()) {
		$('#postcodes').addClass('invisible');
		lookupLocation();
	} else {
		alert("Your phone doesn't support Geolocation - you will have to enter a location manually.");
	}
	
	$("#form").validate();
	});
	</script> 
	<?php } ?>
</head>
<body>
<div id="header">
<h1>Fix<span id="my">My</span>Street</h1>
</div>
<div id="content" class="nolocation">
<h2>Report a problem</h2>
<?php
if ($success === TRUE) {
?>
<h2>Now check your email</h2>
<p>Your problem has been submitted. Please check your email and click the link provided to send your report to your council and list it on Fixmystreet.</p>
<p><a href="index.php">Return to homepage</a></p>
<?php } else { 
if ($success === FALSE) {
echo "<p>There were ". count($outputs) . " error(s)";
echo "<ul>";
	foreach ($outputs as $output) {
	echo "<li>". str_replace("photo", "postcode", $output)."</li>";
	}
echo "</ul>";
}
?>
<form action="" method="post" id="form" enctype="multipart/form-data">
<p>Use this form to report problems such as graffiti, flytipping, litter etc where you are. All problems will be listed on <a href="http://www.fixmystreet.com">Fixmystreet</a>, the national problem reporting website.</p>

<div id="fail"></div>

<input name="lat" id="lat" value="" type="hidden" />
<input name="lng" id="lng" value="" type="hidden" />

<p><label for="form_title">Subject: <em>(Required)</em></label><br />
<input value="" name="title" id="form_title" size="30" class="text required" type="text" /></p>

<p><label for="form_detail">Details:</label><br />
<textarea name="detail" id="form_detail" rows="7" cols="26"></textarea></p>

<!-- Fallback for non geo supporting phones -->
<p id="postcodes"><label for="postcode">Nearest postcode: <em>(Required)</em></label><br />
<input value="" name="postcode" id="postcode" class="text" type="text" />
</p>

<p><label for="name">Name: <em>(Required)</em></label><br />
<input value="" name="name" id="name" size="30" class="text required" type="text /"></p>

<p><label for="form_email">Email: <em>(Required)</em></label><br />
<input value="" name="email" id="form_email" size="30" class="text required" type="text"></p>

<p><label for="form_phone">Phone: <em>(Optional)</em></label><br />
<input value="" name="phone" id="form_phone" size="15" class="text" type="text" />

<div id="locator"></div>
<p id="problem_submit"><input name="submit_problem" value="Submit" type="submit" id="submit" /></p>
</form>
</div>
<?php } ?>
</body>
</html>