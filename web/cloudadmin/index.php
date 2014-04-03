<?php
require_once("settings.php");

if ($_SESSION["loggedin"] != true)
{
	if (isset($_POST["pass"]))
	{
		if ($_POST["pass"] == $adminpass) $_SESSION["loggedin"] = true;
	}
	if ($_SESSION["loggedin"] != true)
	{ ?>
<div style="text-align:center">
    <form action="." method="post">
    	<input type="password" name="pass" /> <input type="submit" value="Login" />
    </form>
</div>
    <?php
		exit();
	}
}
?>

<style type="text/css">
form {
	margin:0;
}

table {
	border-collapse:collapse;
}
</style>

<?php if ($_SESSION["actionmsg"] != "")
{
	echo "<b>".$_SESSION["actionmsg"]."</b><br /><br />";
	$_SESSION["actionmsg"] = "";
}
?>

<h1>Users:</h1>
<table border="0" style="width:100%">
	<tr style="font-weight:bold"><td>ID</td><td>Name</td><td>Email</td><td>Login name</td><td>User Type</td><td>Registration Type</td><td>Active</td><td>Action</td></tr>
    <?php
	$usertypes = array();
	$rows = getRows("SELECT * FROM UserTypes", array());
	foreach ($rows as $row) $usertypes[$row["ID"]] = $row["Name"];
	
	$regtypes = array("form" => "Normal");
	$rows = getRows("SELECT * FROM SocialNetworks", array());
	foreach ($rows as $row) $usertypes[$row["RegType"]] = $row["Name"];
	
	$i = 0;
	$users = array();
	$editid = $_GET["usereditid"];
	$rows = getRows("SELECT * FROM Users ORDER BY ID", array());
	foreach ($rows as $row)
	{
		$i++;
		$id = $row["ID"];
		$name = $row["Name"];
		$email = $row["Email"];
		$loginname = $row["LoginName"];
		$usertypeid = $row["UserType"];
		$regtypeid = $row["RegType"];
		$active = $row["Active"];
		
		$users[$id] = $name;
		
		$usertype = $usertypes[$usertypeid];
		$regtype = $regtypes[$regtypeid];
		
		$edit = "<a href='/?usereditid=$id'>Edit</a>";
		$delete = "<form action='/action.php?action=deleteuser&id=$id' method='post' style='display:inline-block'><input type='password' name='pass' /> <input type='submit' value='Delete' /></form>";
		
		$style = ($i % 2 == 1) ? " style='background:#eee'": "";
		
		if ($editid != $id) echo "<tr$style><td>$id</td><td>$name</td><td><a href='mailto:$email'>$email</a></td><td>$loginname</td><td>$usertype</td><td>$regtype</td><td>$active</td><td>$edit $delete</td></tr>";
		if ($editid == $id)
		{ ?>
<form action="action.php?action=edituser&id=<?php echo $id ?>" method="post">
	<tr<?php echo $style ?>>
    	<td><?php echo $id ?></td>
        <td><input type="text" name="name" value="<?php echo $name ?>" /></td>
        <td><input type="text" name="email" value="<?php echo $email ?>" /></td>
        <td><input type="text" name="loginname" value="<?php echo $loginname ?>" /></td>
        <td><select name="usertype"><?php
		foreach ($usertypes as $key=>$val)
		{
			$selected = ($key == $usertypeid) ? " selected": "";
			echo "<option value='$key'$selected>$val</option>";
		}
		?></select></td>
        <td><select name="regtype"><?php
		foreach ($regtypes as $key=>$val)
		{
			$selected = ($key == $regtypeid) ? " selected": "";
			echo "<option value='$key'$selected>$val</option>";
		}
		?></select></td>
        <td><input type="hidden" name="active" value="0" /><input type="checkbox" name="active" value="1"<?php if ($active == 1) echo " checked"; ?> /></td>
        <td><input type="submit" value="Save" /></td>
    </tr>
</form>
        <?php }
	}
	?>
<form action="action.php?action=adduser" method="post">
	<tr>
    	<td></td>
        <td><input type="text" name="name" /></td>
        <td><input type="text" name="email" /></td>
        <td><input type="text" name="loginname" /></td>
        <td>main</td>
        <td><select name="regtype"><?php
		foreach ($regtypes as $key=>$val) echo "<option value='$key'>$val</option>";
		?></select></td>
        <td></td>
        <td><input type="submit" value="Add" /></td>
    </tr>
</form>
</table><br />

<h1>Labels:</h1>
<table border="0" style="width:100%">
	<tr style="font-weight:bold"><td>ID</td><td>Name</td><td>MAC</td><td>Type</td><td>Owner</td><td>Active</td><td>Lost</td><td>Public</td><td>Location</td><td>Action</td></tr>
    <?php
	$labeltypes = array();
	$rows = getRows("SELECT * FROM LabelTypes", array());
	foreach ($rows as $row) $labeltypes[$row["ID"]] = $row["Name"].(($row["Active"] == 1) ? "": " (not active)");
	
	$labelsharing = array();
	$rows = getRows("SELECT * FROM LabelSharing", array());
	foreach ($rows as $row) $labelsharing[$row["LabelID"]][] = array($row["UserID"], $row["ID"]);
	
	$i = 0;
	$editid = $_GET["labeleditid"];
	$rows = getRows("SELECT * FROM Labels ORDER BY ID", array());
	foreach ($rows as $row)
	{
		$i++;
		$id = $row["ID"];
		$name = $row["Name"];
		$mac = $row["MAC"];
		$labeltypeid = $row["Type"];
		$ownerid = $row["OwnerID"];
		$active = $row["Active"];
		$lost = $row["Lost"];
		$public = $row["Public"];
		$lat = $row["Lat"];
		$lon = $row["Lon"];
		$accuracy = $row["Accuracy"];
		$tslocation = $row["TimestampLocation"];
		$locationactive = $row["LocationActive"];
		
		$owner = ($ownerid == NULL) ? "No owner": $users[$ownerid];
		$labeltype = $labeltypes[$labeltypeid];
		
		$location = "N/A";
		if ($lat != NULL)
		{
			$location = "$lat, $lon (+- $accuracy m): determined on ".date("d M Y H:i:s", $tslocation).", active: $locationactive";
		}
		
		$edit = "<a href='/?labeleditid=$id'>Edit</a>";
		$delete = "<form action='/action.php?action=deletelabel&id=$id' method='post' style='display:inline-block'><input type='password' name='pass' /> <input type='submit' value='Delete' /></form>";
		
		$style = ($i % 2 == 1) ? " style='background:#eee'": "";
		
		if ($editid != $id) echo "<tr$style><td>$id</td><td>$name</td><td>$mac</td><td>$labeltype</td><td>$owner</td><td>$active</td><td>$lost</td><td>$public</td><td>$location</td><td>$edit $delete</td></tr>";
		if ($editid == $id)
		{ ?>
<form action="action.php?action=editlabel&id=<?php echo $id ?>" method="post">
	<tr<?php echo $style ?>>
    	<td><?php echo $id ?></td>
        <td><input type="text" name="name" value="<?php echo $name ?>" /></td>
        <td><input type="text" name="mac" value="<?php echo $mac ?>" /></td>
        <td><select name="labeltype"><?php
		foreach ($labeltypes as $key=>$val)
		{
			$selected = ($key == $labeltypeid) ? " selected": "";
			echo "<option value='$key'$selected>$val</option>";
		}
		?></select></td>
        <td><select name="owner"><option value="NULL">No owner</option><?php
		foreach ($users as $key=>$val)
		{
			$selected = ($key == $ownerid) ? " selected": "";
			echo "<option value='$key'$selected>$val</option>";
		}
		?></select></td>
        <td><input type="hidden" name="active" value="0" /><input type="checkbox" name="active" value="1"<?php if ($active == 1) echo " checked"; ?> /></td>
        <td><input type="hidden" name="lost" value="0" /><input type="checkbox" name="lost" value="1"<?php if ($lost == 1) echo " checked"; ?> /></td>
        <td><input type="hidden" name="public" value="0" /><input type="checkbox" name="public" value="1"<?php if ($public == 1) echo " checked"; ?> /></td>
        <td><?php echo $location ?></td>
        <td><input type="submit" value="Save" /></td>
    </tr>
</form>
        <?php }
		?>
<form action="action.php?action=addsharing&id=<?php echo $id ?>" method="post">
	<tr<?php echo $style ?>>
    	<td colspan="2"></td>
        <td colspan="8">
        	<?php
			$alreadyshared = array();
			foreach ($labelsharing[$id] as $sharedata) $alreadyshared[$sharedata[0]] = true;
			?>
        	Label sharing: <?php if (count($users) - count($alreadyshared) - 1 > 0) { ?><select name="userid"><?php
			foreach ($users as $key=>$val)
			{
				if ($key != $ownerid && !isset($alreadyshared[$key])) echo "<option value='$key'>$val</option>";
			}
			?></select> <input type="submit" value="Share" />
            <?php } ?>
        </td>
    </tr>
    <?php
	foreach ($labelsharing[$id] as $sharedata)
	{
		$userid = $sharedata[0];
		$shareid = $sharedata[1];
		$user = $users[$userid];
		$delete = "<a href='/action.php?action=cancelsharing&id=$shareid'>Cancel</a>";
		echo "<tr$style><td colspan='2'></td><td colspan='8'>$user ($delete)</td></tr>";
	}
	?>
</form>
        <?php
	}
	?>
<form action="action.php?action=addlabel" method="post">
	<tr>
    	<td></td>
        <td><input type="text" name="name" /></td>
        <td><input type="text" name="mac" /></td>
        <td><select name="labeltype"><?php
		foreach ($labeltypes as $key=>$val) echo "<option value='$key'>$val</option>";
		?></select></td>
        <td><select name="owner"><option value="NULL">No owner</option><?php
		foreach ($users as $key=>$val) echo "<option value='$key'>$val</option>";
		?></select></td>
        <td><input type="hidden" name="active" value="0" /><input type="checkbox" name="active" value="1" checked="checked" /></td>
        <td><input type="hidden" name="lost" value="0" /><input type="checkbox" name="lost" value="1" /></td>
        <td><input type="hidden" name="public" value="0" /><input type="checkbox" name="public" value="1" /></td>
        <td></td>
        <td><input type="submit" value="Add" /></td>
    </tr>
</form>
</table>