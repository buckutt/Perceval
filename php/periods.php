<?php
if(!isset($_SESSION['logged'])) exit();
if(!is_numeric($_GET['pid'])) {
	if(isset($_POST['name'])) {
		$query = $db->prepare("INSERT INTO t_period_per(per_id, fun_id, per_name, per_date_start, per_date_end, per_removed) VALUES('', ?, ?, ?, ?, 0)");
		$query->execute(array($_POST['fundation'], $_POST['name'], $_POST['start'], $_POST['end']));
	}
?>
<strong>Ajouter une période :</strong><br />
<form method="post" action="./?p=periods">
	<fieldset>
		<label for="name">Nom :</label> <input type="text" name="name"/><br />
		<label for="start">Début :</label> <input type="text" id="start" name="start"/><br />
		<label for="end">Fin :</label> <input type="text" id="end" name="end" /><br />
		<label for="fundation">Fondation :</label>
		<select name="fundation">
			<option value="">Aucune</option>
<?php
$query = $db->prepare("SELECT * FROM t_fundation_fun ORDER BY t_fundation_fun.fun_name");
$query->execute();
$query->setFetchMode(PDO::FETCH_OBJ);
while($data = $query->fetch()) {
	echo '<option value="'.$data->fun_id.'">'.$data->fun_name.'</option>';
}
?>
		</select>
		<input type="submit" value="Valider"/>
	</fieldset>
</form>
<?php
} else {
	if(isset($_POST['name'])) {
		$rem = 0;
		if($_POST['rem'] == 1) $rem = 1;
		$query = $db->prepare("UPDATE t_period_per SET fun_id=?, per_name=?, per_date_start=?, per_date_end=?, per_removed=? WHERE per_id=?");
		$query->execute(array($_POST['fundation'], $_POST['name'], $_POST['start'], $_POST['end'], $rem, $_GET['pid']));
	}

	$queryPer = $db->prepare("SELECT * FROM t_period_per WHERE per_id=?");
	$queryPer->execute(array($_GET['pid']));
	$queryPer->setFetchMode(PDO::FETCH_OBJ);
	$dataPer = $queryPer->fetch();
?>
<strong>Modifier une période :</strong><br />
<form method="post" action="./?p=periods&pid=<?php echo $dataPer->per_id; ?>">
	<fieldset>
		<label for="name">Nom :</label> <input type="text" name="name" value="<?php echo $dataPer->per_name; ?>"/><br />
		<label for="start">Début :</label> <input type="text" id="start" name="start" value="<?php echo $dataPer->per_date_start; ?>"/><br />
		<label for="end">Fin :</label> <input type="text" id="end" name="end" value="<?php echo $dataPer->per_date_end; ?>"/><br />
		<label for="fundation">Fondation :</label>
		<select name="fundation">
			<option value="">Aucune</option>
<?php
$query = $db->prepare("SELECT * FROM t_fundation_fun ORDER BY t_fundation_fun.fun_name");
$query->execute();
$query->setFetchMode(PDO::FETCH_OBJ);
while($data = $query->fetch()) {
	if($dataPer->fun_id == $data->fun_id) echo '<option value="'.$data->fun_id.'" selected="selected">'.$data->fun_name.'</option>';
	else echo '<option value="'.$data->fun_id.'">'.$data->fun_name.'</option>';
}
?>
		</select><br />
		<input type="checkbox" name="rem" value="1"<?php if($dataObj->per_removed == 1) echo ' checked="checked"'; ?>/> <label for="rem">Désactiver</label><br />
		<input type="submit" value="Valider"/>
	</fieldset>
</form>
<?php
}
?>

Périodes en cours - à venir:<br />
<table>
	<tr><th>id</th><th>Nom</th><th>Fondation</th><th>Dates</th><th>Actions</th></tr>
<?php
$query = $db->prepare("SELECT t_period_per.*, t_fundation_fun.fun_name FROM t_period_per LEFT JOIN t_fundation_fun ON t_fundation_fun.fun_id = t_period_per.fun_id WHERE t_period_per.per_date_end > NOW() AND t_period_per.per_removed=0 ORDER BY t_period_per.per_date_start");
$query->execute();
$query->setFetchMode(PDO::FETCH_OBJ);
while($data = $query->fetch()) {
	echo '<tr><td>'.$data->per_id.'</td><td>'.$data->per_name.'</td><td>'.$data->fun_name.'</td><td>'.$data->per_date_start.'<br />'.$data->per_date_end.'</td><td><a href="./?p=periods&pid='.$data->per_id.'">Modifier</a></td></tr>';
}
?>
</table>

<script>
$(function() {
	$("#start").AnyTime_picker(
	    { format: "%z-%m-%d %H:%i:00", labelTitle: "Heure de départ",
	      labelHour: "Heure", labelMinute: "Minute", labelMonth: "Mois", labelDayOfMonth: "Jour", labelYear: "Année" } );

	$("#end").AnyTime_picker(
	    { format: "%z-%m-%d %H:%i:00", labelTitle: "Heure de départ",
	      labelHour: "Heure", labelMinute: "Minute", labelMonth: "Mois", labelDayOfMonth: "Jour", labelYear: "Année" } );
});
</script>