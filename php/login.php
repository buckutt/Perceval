<?php
if(isset($_POST['pwd'])) {
	if(sha1($_POST['pwd']) == "") {
		$_SESSION['logged'] = true;
		header('Location: ./');
	}
}
?>
<form method="post" action="./">
	<fieldset>
		<label for="pwd">Password:</label> <input type="password" name="pwd"/><br />
		<input type="submit" value="Yolo"/>
	</fieldset>
</form>
