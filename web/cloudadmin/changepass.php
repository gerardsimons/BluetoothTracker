<?php
require_once("settings.php");

if (isset($_POST["loginname"]))
{
	echo "<b style='color:#f00'>";
	
	$loginname = $_POST["loginname"];
	$pass = $_POST["pass"];
	$newpass = $_POST["newpass"];
	$newpass2 = $_POST["newpass2"];
	
	if ($newpass != "")
	{
		if ($newpass == $newpass2)
		{
			if ($loginname != "" && $pass != "")
			{
				$row = getRow("SELECT * FROM Users WHERE LoginName=?", array($loginname));
				if ($row)
				{
					$userid = $row["ID"];
					$regtype = $row["RegType"];
					if ($regtype == "form")
					{
						$dbpass = $row["Password"];
						$salt = $row["Salt"];
						$hash = $api->call("auth", "hashpass", array($pass, $salt));
						if ($hash == $dbpass)
						{
							$newsalt = GenerateCode();
							$newdbpass = $api->call("auth", "hashpass", array($newpass, $newsalt));
							if (query("UPDATE Users SET Password=?, Salt=? WHERE ID=?", array($newdbpass, $newsalt, $userid)))
								echo "Password updated!";
							else
								echo "Could not update password (db error).";
						}
						else
							echo "Incorrect current password.";
					}
					else
						echo "User not of right type (must be regular user, not via social network login).";
				}
				else
					echo "User not found.";
			}
			else
				echo "Login name or current password not provided.";
		}
		else
			echo "New passwords do not match.";
	}
	else
		echo "No new password provided.";
	echo "</b><br /><br />";
}
?>

<div style="text-align:center">
	<h1>Change password for your whereAt Cloud account</h1>
    <div style="text-align:left;display:inline-block">
        <form action="" method="post">
            <table border="0">
                <tr><td>Login name:</td><td style="width:10px"></td><td><input type="text" name="loginname" /></td></tr>
                <tr><td>Current password:</td><td></td><td><input type="password" name="pass" /></td></tr>
                <tr><td>New password:</td><td></td><td><input type="password" name="newpass" /></td></tr>
                <tr><td>Confirm new password:</td><td></td><td><input type="password" name="newpass2" /></td></tr>
                <tr><td></td><td></td><td><input type="submit" value="Change password" /></td></tr>
            </table>
        </form>
    </div>
</div>