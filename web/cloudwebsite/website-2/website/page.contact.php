<h1 id="title" class="longbg white" style="background-color:#00baff">Contact</h1>

<div id="p1" class="longbg" style="background-color:#fff">

<?php
$msg = $_SESSION["contactmsg"];
if ($msg != "") echo "<b>$msg</b><br /><br />";
$_SESSION["contactmsg"] = "";
?>

<form action="contact.php" method="post" onsubmit="return checkAll();">
<table border="0">
<tr><td>Your email address:</td><td style="width:40px"></td><td><input type="email" name="email" id="email" value="<?php echo $_SESSION["contact"]["email"]; ?>" style="width:300px" onblur="checkEmail()" /></td><td style="width:10px"></td><td id="emailmsg" style="color:#f00"></td></tr>
<tr><td>Subject:</td><td></td><td><input type="text" name="subject" id="subject" value="<?php echo $_SESSION["contact"]["subject"]; ?>" style="width:300px" onblur="checkSubject()" /></td><td style="width:10px"></td><td id="subjectmsg" style="color:#f00"></td></tr>
</table><br />
Message:<br />
<textarea id="msg" name="msg" style="width:920px;height:200px" onblur="checkMsg()"><?php echo $_SESSION["contact"]["msg"]; ?></textarea><br />
<img src="<?php echo $mainurl ?>captcha.php" /><br />
<table border="0" style="width:100%">
<tr><td>Enter the charachters shown above: <input type="text" name="captcha" /></td><td style="text-align:right;padding-right:10px"><input type="submit" value="Send message" /></td></tr>
</table>
</form>

</div>
<script type="text/javascript">
function checkAll() {
	var email = checkEmail();
	var subject = checkSubject();
	var msg = checkMsg();
	if (email == false || subject == false || msg == false) {
		return false;
	} else {
		return true;
	}
}
function checkEmail() {
	var email = $("#email").val();
	if (email == "") {
		$("#email").css("border-color", "#f00");
		$("#emailmsg").html("Required field");
		return false;
	} else {
		if (!isValidEmailAddress(email)) {
			$("#email").css("border-color", "#f00");
			$("#emailmsg").html("Email address is not valid");
			return false;
		} else {
			$("#email").css("border-color", "");
			$("#emailmsg").html("");
			return true;
		}
	}
}
function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};
function checkSubject() {
	var subject = $("#subject").val();
	if (subject == "") {
		$("#subject").css("border-color", "#f00");
		$("#subjectmsg").html("Required field");
		return false;
	} else {
		$("#subject").css("border-color", "");
		$("#subjectmsg").html("");
		return true;
	}
}
function checkMsg() {
	var msg = $("#msg").val();
	if (msg == "") {
		$("#msg").css("border-color", "#f00");
		return false;
	} else {
		$("#msg").css("border-color", "");
		return true;
	}
}

</script>