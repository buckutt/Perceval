<?php
if(!isset($_SESSION['logged'])) exit();
if(is_numeric($_GET['oid'])) {
	if(isset($_POST['name'])) {
		$rem = 0;
		if($_POST['rem'] == 1) $rem = 1;
		$query = $db->prepare("UPDATE t_object_obj SET fun_id=?, obj_name=?, obj_type=?, obj_removed=? WHERE obj_id=?");
		$query->execute(array($_POST['fundation'], $_POST['name'], $_POST['type'], $rem, $_GET['oid']));
	}
	$queryObj = $db->prepare("SELECT * FROM t_object_obj WHERE obj_id=?");
	$queryObj->execute(array($_GET['oid']));
	$queryObj->setFetchMode(PDO::FETCH_OBJ);
	$dataObj = $queryObj->fetch();

	if(is_numeric($_GET['jrm'])) {
		$query = $db->prepare("DELETE FROM tj_obj_poi_jop WHERE obj_id=? AND poi_id=?");
		$query->execute(array($dataObj->obj_id, $_GET['jrm']));
	}
?>
<strong>Modifier un objet:</strong><br />
<form method="post" action="./?p=objects&oid=<?php echo $dataObj->obj_id; ?>">
	<fieldset>
		<label for="name">Nom:</label> <input type="text" name="name" value="<?php echo $dataObj->obj_name; ?>"/><br />
		<label for="type">Type:</label>
		<select name="type">
			<option value="product"<?php if($dataObj->obj_type == "product") echo ' selected="selected"'; ?>>product</option>
			<option value="promotion"<?php if($dataObj->obj_type == "promotion") echo ' selected="selected"'; ?>>promotion</option>
			<option value="category"<?php if($dataObj->obj_type == "category") echo ' selected="selected"'; ?>>category</option>
		</select><br />
		<label for="fundation">Fondation :</label>
		<select name="fundation">
			<option value="">Aucune</option>
<?php
	$query = $db->prepare("SELECT * FROM t_fundation_fun WHERE fun_removed=0 ORDER BY fun_name");
	$query->execute();
	$query->setFetchMode(PDO::FETCH_OBJ);
	while($data = $query->fetch()) {
		if($dataObj->fun_id == $data->fun_id) echo '<option value="'.$data->fun_id.'" selected="selected">'.$data->fun_name.'</option>';
		else echo '<option value="'.$data->fun_id.'">'.$data->fun_name.'</option>';
	}
?>
		</select><br />
		<input type="checkbox" name="rem" value="1"<?php if($dataObj->obj_removed == 1) echo ' checked="checked"'; ?>/> <label for="rem">Désactiver</label><br />
		<input type="submit" value="Valider"/>
	</fieldset>
</form>
<br />
<?php
	if(isset($_POST['priority'])) {
		$query = $db->prepare("INSERT INTO tj_obj_poi_jop(obj_id, poi_id, jop_priority) VALUES(?,?,?)");
		$query->execute(array($dataObj->obj_id, $_POST['point'], $_POST['priority']));
	}
?>
<strong>Gestion de ses points de vente :</strong>
<form method="post" action="./?p=objects&oid=<?php echo $dataObj->obj_id; ?>">
	<fieldset>
		<label for="point">Point:</label>
		<select name="point">
<?php
	$query = $db->prepare("SELECT * FROM t_point_poi WHERE poi_removed=0 ORDER BY poi_name");
	$query->execute();
	$query->setFetchMode(PDO::FETCH_OBJ);
	while($data = $query->fetch()) {
		echo '<option value="'.$data->poi_id.'">'.$data->poi_name.'</option>';
	}
?>
		</select>
		<label for="priority">Priorité:</label> <input type="text" name="priority" value="100"/>
		<input type="submit" value="Ajouter"/>
	</fieldset>
</form>
<table>
	<tr><th>Point</th><th>Priorité</th><th>Actions</th></tr>
<?php
	$queryJop = $db->prepare("SELECT tj_obj_poi_jop.*, t_point_poi.poi_name FROM tj_obj_poi_jop LEFT JOIN t_point_poi ON t_point_poi.poi_id = tj_obj_poi_jop.poi_id WHERE tj_obj_poi_jop.obj_id=? AND t_point_poi.poi_removed=0");
	$queryJop->execute(array($dataObj->obj_id));
	$queryJop->setFetchMode(PDO::FETCH_OBJ);
	while($dataJop = $queryJop->fetch()) {
		echo '<tr><td>'.$dataJop->poi_name.'</td><td>'.$dataJop->jop_priority.'</td><td><a href="./?p=objects&oid='.$dataObj->obj_id.'&jrm='.$dataJop->poi_id.'">Supprimer</a></td></tr>';
	}
?>
</table>
<br />
<?php
	if(is_numeric($_GET['pid'])) {
		if(isset($_POST['credit'])) {
			$rem = 0;
			if($_POST['rem'] == 1) $rem = 1;
			$query = $db->prepare("UPDATE t_price_pri SET grp_id=?, per_id=?, pri_credit=?, pri_removed=? WHERE pri_id=?");
			$query->execute(array($_POST['group'], $_POST['period'], $_POST['credit'],$rem, $_GET['pid']));
			header('Location: ./?p=objects&oid='.$dataObj->obj_id);
		}

		$queryPri = $db->prepare("SELECT * FROM t_price_pri WHERE pri_id=?");
		$queryPri->execute(array($_GET['pid']));
		$queryPri->setFetchMode(PDO::FETCH_OBJ);
		$dataPri = $queryPri->fetch();
?>
<strong>Modification du prix sélectionné:</strong><br />
<form method="post" action="./?p=objects&oid=<?php echo $dataObj->obj_id; ?>&pid=<?php echo $dataPri->pri_id; ?>">
	<fieldset>
		<label for="credit">Prix (cts d'€):</label> <input type="text" name="credit" value="<?php echo $dataPri->pri_credit; ?>"/>
		<label for="group">Groupe:</label>
		<select name="group">
<?php
	$query = $db->prepare("SELECT * FROM t_group_grp WHERE grp_removed=0 ORDER BY grp_name");
	$query->execute();
	$query->setFetchMode(PDO::FETCH_OBJ);
	while($data = $query->fetch()) {
		if($data->grp_id == $dataPri->grp_id) echo '<option value="'.$data->grp_id.'" selected="selected">'.$data->grp_name.'</option>';
		else echo '<option value="'.$data->grp_id.'">'.$data->grp_name.'</option>';
	}
?>
		</select>
		<label for="period">Période:</label>
		<select name="period">
<?php
	$query = $db->prepare("SELECT * FROM t_period_per WHERE per_removed=0 AND per_date_end > NOW() ORDER BY per_date_start");
	$query->execute();
	$query->setFetchMode(PDO::FETCH_OBJ);
	while($data = $query->fetch()) {
		if($data->per_id == $dataPri->per_id) echo '<option value="'.$data->per_id.'" selected="selected">'.$data->per_name.'</option>';
		else echo '<option value="'.$data->per_id.'">'.$data->per_name.'</option>';
	}
?>
		</select><br />
		<input type="checkbox" name="rem" value="1"<?php if($dataPri->pri_removed == 1) echo ' checked="checked"'; ?>/> <label for="rem">Désactiver</label><br />
		<input type="submit" value="Modifier"/>
	</fieldset>
</form>
<br />
<a href="./?p=objects&oid=<?php echo $dataObj->obj_id; ?>">Retour</a>
<?php

	} else {
		if(isset($_POST['credit'])) {
			$query = $db->prepare("INSERT INTO t_price_pri(pri_id,obj_id,grp_id,per_id,pri_credit,pri_removed) VALUES('',?,?,?,?,0)");
			$query->execute(array($dataObj->obj_id, $_POST['group'], $_POST['period'], $_POST['credit']));
		}
?>
<strong>Gestion de ses prix :</strong><br />
<form method="post" action="./?p=objects&oid=<?php echo $dataObj->obj_id; ?>">
	<fieldset>
		<label for="credit">Prix (cts d'€):</label> <input type="text" name="credit"/>
		<label for="group">Groupe:</label>
		<select name="group">
<?php
		$query = $db->prepare("SELECT * FROM t_group_grp WHERE grp_removed=0 ORDER BY grp_name");
		$query->execute();
		$query->setFetchMode(PDO::FETCH_OBJ);
		while($data = $query->fetch()) {
			echo '<option value="'.$data->grp_id.'">'.$data->grp_name.'</option>';
		}
?>
		</select>
		<label for="period">Période:</label>
		<select name="period">
<?php
		$query = $db->prepare("SELECT * FROM t_period_per WHERE per_removed=0 AND per_date_end > NOW() ORDER BY per_date_start");
		$query->execute();
		$query->setFetchMode(PDO::FETCH_OBJ);
		while($data = $query->fetch()) {
			echo '<option value="'.$data->per_id.'">'.$data->per_name.'</option>';
		}
?>
		</select>
		<input type="submit" value="Ajouter"/>
	</fieldset>
</form>
<table>
	<tr><th>id</th><th>Groupe</th><th>Période</th><th>Prix</th><th>Actions</th></tr>
<?php
		$queryPri = $db->prepare("SELECT t_price_pri.*, t_period_per.per_name, t_group_grp.grp_name FROM t_price_pri LEFT JOIN t_group_grp ON t_group_grp.grp_id = t_price_pri.grp_id LEFT JOIN t_period_per ON t_period_per.per_id = t_price_pri.per_id WHERE t_price_pri.obj_id=? AND t_price_pri.pri_removed=0 AND t_period_per.per_removed=0 AND t_group_grp.grp_removed=0");
		$queryPri->execute(array($dataObj->obj_id));
		$queryPri->setFetchMode(PDO::FETCH_OBJ);
		while($dataPri = $queryPri->fetch()) {
			echo '<tr><td>'.$dataPri->pri_id.'</td><td>'.$dataPri->grp_name.'</td><td>'.$dataPri->per_name.'</td><td>'.$dataPri->pri_credit.'</td><td><a href="./?p=objects&oid='.$dataObj->obj_id.'&pid='.$dataPri->pri_id.'">Modifier</a></td></tr>';
		}
?>
</table>
<?php
	}
} else {
?>
<strong>Ajouter un objet:</strong><br />
<?php
	if(isset($_POST['name'])) {
		$query = $db->prepare("INSERT INTO t_object_obj(obj_id, obj_name, obj_type, fun_id, obj_removed, obj_stock, img_id) VALUES('',?,?,?,0,-1,1)");
		$query->execute(array($_POST['name'], $_POST['type'], $_POST['fundation']));
	}
?>
<form method="post" action="./?p=objects">
	<fieldset>
		<label for="name">Nom:</label> <input type="text" name="name"/><br />
		<label for="type">Type:</label>
		<select name="type">
			<option value="product">product</option>
			<option value="promotion">promotion</option>
			<option value="category">category</option>
		</select><br />
		<label for="fundation">Fondation :</label>
		<select name="fundation">
			<option value="">Aucune</option>
<?php
	$query = $db->prepare("SELECT * FROM t_fundation_fun WHERE fun_removed=0 ORDER BY fun_name");
	$query->execute();
	$query->setFetchMode(PDO::FETCH_OBJ);
	while($data = $query->fetch()) {
		echo '<option value="'.$data->fun_id.'">'.$data->fun_name.'</option>';
	}
?>
		</select><br />
		<input type="submit" value="Ajouter"/>
	</fieldset>
</form><br />
<strong>Rechercher un objet:</strong> (cay un LIKE)<br />
<form method="post" action="./?p=objects">
	<fieldset>
		<label for="rname">Nom:</label> <input type="text" name="rname" /> <input type="submit" value="OK" />
	</fieldset>
</form>
<?php
	if(isset($_POST['rname'])) {
		echo 'Résultats pour <strong>'.htmlentities($_POST['rname']).'</strong>:<br /><br />';
		echo '<table><tr><th>id</th><th>Nom</th><th>Fondation</th><th>Action</th></tr>';
		$query = $db->prepare("SELECT t_object_obj.*, t_fundation_fun.fun_name FROM t_object_obj LEFT JOIN t_fundation_fun ON t_fundation_fun.fun_id = t_object_obj.fun_id WHERE t_object_obj.obj_removed=0 AND LOWER(t_object_obj.obj_name) LIKE LOWER(?)");
		$query->execute(array('%'.$_POST['rname'].'%'));
		$query->setFetchMode(PDO::FETCH_OBJ);
		while($data = $query->fetch()) {
			echo '<tr><td>'.$data->obj_id.'</td><td>'.$data->obj_name.'</td><td>'.$data->fun_name.'</td><td><a href="./?p=objects&oid='.$data->obj_id.'">Modifier</a></td></tr>';
		}
		echo '</table>';
	}
}
?>
