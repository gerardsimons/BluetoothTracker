<?php
//this file contains the class which generates the table structure for the API
//usually called by prepare.php

require_once(dirname(__FILE__)."/settings.php");
class APIDBStructure
{
	private static $dbstructure = array(
		/*"Table name" => array(
			"Field name" => array("type", auto increment?, primary key/index?, null allowed? (not required), array("foreign key reference", "on update behavior", "on delete behavior")[leave out to define no foreign key]) or "type"
		)*/
		"APIKeys" => array(
			"ID" => array("int", 1, 1),
			"APIKey" => "text",
			"Description" => "text",
			"Active" => "int",
			"Message" => array("text", 0, 0, 1),
			"Settings" => array("text", 0, 0, 1)
		),
		"LabelTypes" => array(
			"ID" => array("int", 1, 1),
			"Name" => "text",
			"Active" => "int"
		),
		"UserTypes" => array(
			"ID" => array("int", 1, 1),
			"Name" => "text",
			"Active" => "int",
			"LabelTypes" => "text"
		),
		"Users" => array(
			"ID" => array("int", 1, 1),
			"Name" => "text",
			"Email" => array("text", 0, 0, 1),
			"LoginName" => "text",
			"Password" => array("text", 0, 0, 1),
			"Salt" => array("text", 0, 0, 1),
			"UserType" => array("int", 0, 1, 0, array("UserTypes(ID)", "CASCADE", "RESTRICT")),
			"RegType" => "text",
			"Active" => "int"
		),
		"UserEmailData" => array(
			"ID" => array("int", 1, 1),
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Value" => "text",
			"Token" => array("text", 0, 0, 1),
			"Verified" => "int"
		),
		"UserTelephoneData" => array(
			"ID" => array("int", 1, 1),
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Value" => "text",
			"IsPrimary" => "int"
		),
		"UserAddressData" => array(
			"ID" => array("int", 1, 1),
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Value" => "text",
			"Lat" => array("double", 0, 0, 1),
			"Lon" => array("double", 0, 0, 1),
			"IsPrimary" => "int"
		),
		"UserEmailChange" => array(
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Token" => "text",
			"NewEmail" => "text",
			"Timestamp" => "int"
		),
		/*"UserLabelLostNotifications" => array(
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Email" => "int",
			"Text" => "int",
			"Push" => "int"
		),*/
		"UserSettings" => array(
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Var" => "text",
			"Value" => "text"
		),
		"UserAutoLogin" => array(
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"APIKeyID" => array("int", 0, 0, 0, array("APIKeys(ID)", "CASCADE", "CASCADE")),
			"LoginKey" => "text",
			"MACHash" => "text",
			"TimestampFirst" => "int",
			"TimestampLastLogin" => "int"
		),
		"Notifications" => array(
			"ID" => array("int", 1, 1),
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Type" => "text",
			"Message" => "text",
			"Data" => array("text", 0, 0, 1),
			"Timestamp" => "int",
			"Seen" => "int"
		),
		"SocialNetworks" => array(
			"ID" => array("int", 1, 1),
			"Name" => "text",
			"RegType" => "text",
			"Active" => "int"
		),
		"LabelIcons" => array(
			"ID" => array("int", 1, 1),
			"Description" => "text",
			"File" => "text"
		),
		"LabelPictures" => array(
			"ID" => array("int", 1, 1),
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"File" => "text",
			"Timestamp" => "int"
		),
		"Labels" => array(
			"ID" => array("int", 1, 1),
			"MAC" => "text",
			"Type" => array("int", 0, 0, 0, array("LabelTypes(ID)", "CASCADE", "RESTRICT")),
			"OwnerID" => array("int", 0, 1, 1, array("Users(ID)", "CASCADE", "SET NULL")),
			"Name" => array("text", 0, 0, 1),
			"IconID" => array("int", 0, 0, 1, array("LabelIcons(ID)", "CASCADE", "RESTRICT")),
			"PictureID" => array("int", 0, 0, 1, array("LabelPictures(ID)", "CASCADE", "SET NULL")),
			"Active" => "int",
			"Public" => "int",
			"Lat" => array("double", 0, 0, 1),
			"Lon" => array("double", 0, 0, 1),
			"Accuracy" => array("double", 0, 0, 1),
			"TimestampLocation" => array("int", 0, 0, 1),
			"LocationActive" => array("int", 0, 0, 1)
		),
		/*"LabelNotifications" => array(
			"LabelID" => array("int", 0, 1, 0, array("Labels(ID)", "CASCADE", "CASCADE")),
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"EmailRange" => "int",
			"TextRange" => "int",
			"PushRange" => "int",
			"EmailMaxDist" => "int",
			"TextMaxDist" => "int",
			"PushMaxDist" => "int",
			"MaxDist" => "int"
		),*/
		"LabelSettings" => array(
			"LabelID" => array("int", 0, 1, 0, array("Labels(ID)", "CASCADE", "CASCADE")),
			"OwnerID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Var" => "text",
			"Value" => "text"
		),
		"LabelMetaData" => array(
			"ID" => array("int", 1, 1),
			"LabelID" => array("int", 0, 1, 0, array("Labels(ID)", "CASCADE", "CASCADE")),
			"MetaKey" => "text",
			"Value" => "text"
		),
		"LabelBinaryMetaData" => array(
			"ID" => array("int", 1, 1),
			"LabelID" => array("int", 0, 1, 0, array("Labels(ID)", "CASCADE", "CASCADE")),
			"MetaKey" => "text",
			"Value" => "blob"
		),
		"LabelLost" => array(
			"ID" => array("int", 1, 1),
			"LabelID" => array("int", 0, 1, 1, array("Labels(ID)", "CASCADE", "SET NULL")),
			"UserID" => array("int", 0, 1, 1, array("Users(ID)", "CASCADE", "SET NULL")),
			"FoundByUserID" => array("int", 0, 1, 1, array("Users(ID)", "CASCADE", "SET NULL")),
			"TimestampLost" => "int",
			"TimestampFound" => array("int", 0, 0, 1),
			"MakePublic" => "int"
		),
		"LabelSharing" => array(
			"ID" => array("int", 1, 1),
			"LabelID" => array("int", 0, 1, 0, array("Labels(ID)", "CASCADE", "CASCADE")),
			"UserID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Name" => array("text", 0, 0, 1),
			"Timestamp" => "int",
			"Timeout" => array("int", 0, 0, 1),
			"EndWhenFound" => "int"
		),
		"LabelTransferRequest" => array(
			"LabelID" => array("int", 0, 1, 0, array("Labels(ID)", "CASCADE", "CASCADE")),
			"OwnerID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"NewOwnerID" => array("int", 0, 1, 0, array("Users(ID)", "CASCADE", "CASCADE")),
			"Timestamp" => "int",
			"Timeout" => "int",
			"Accepted" => "int"
		),
		"TrackingPrimary" => array(
			"ID" => array("int", 1, 1),
			"LabelID" => array("int", 0, 1, 1, array("Labels(ID)", "CASCADE", "SET NULL")),
			"UserID" => array("int", 0, 1, 1, array("Users(ID)", "CASCADE", "SET NULL")),
			"OwnerID" => array("int", 0, 1, 1, array("Users(ID)", "CASCADE", "SET NULL")),
			"PhoneType" => "text",
			"GPSLat" => "double",
			"GPSLon" => "double",
			"GPSAccuracy" => "double",
			"DirToLabel" => array("double", 0, 0, 1),
			"SignalStrength" => "double",
			"DistanceToLabel" => array("double", 0, 0, 1),
			"Timestamp" => "int"
		),
		"TrackingSecondary" => array(
			"ID" => array("int", 1, 1),
			"LabelID" => array("int", 0, 1, 1, array("Labels(ID)", "CASCADE", "SET NULL")),
			"UserID" => array("int", 0, 1, 1, array("Users(ID)", "CASCADE", "SET NULL")),
			"OwnerID" => array("int", 0, 1, 1, array("Users(ID)", "CASCADE", "SET NULL")),
			"PhoneType" => "text",
			"GPSLat" => "double",
			"GPSLon" => "double",
			"GPSAccuracy" => "double",
			"SignalStrength" => "double",
			"DistanceToLabel" => array("double", 0, 0, 1),
			"Timestamp" => "int"
		)
	);
	
	private static $enabled = false;
	
	private static $db = false;
	public static $msg = "";
	
	private static function connectDatabase() {
		//connect to database
		if (self::$db === false)
		{
			try {
				$connstr = "mysql:host=".APISettings::$dbhost.";dbname=".APISettings::$dbname.";charset=utf8";
				self::$db = new PDO($connstr, APISettings::$dbuser, APISettings::$dbpass);
			} catch (Exception $e) {
				return false;
			}
		}
		return true;
	}
	
	public static function prepareDatabase() {
		//check status
		self::$enabled = APISettings::$dbprepenabled;
		if (self::$enabled == false)
		{
			self::$msg = "Database preparation disabled.";
			return false;
		}
		
		//connect to database
		if (!self::connectDatabase())
		{
			self::$msg = "Could not connect to database!";
			return false;
		}
		
		//build the SQL queries
		$queries = array();
		foreach (self::$dbstructure as $table=>$data)
		{
			$primary = false;
			$fields = array();
			$index = array();
			$foreign = array();
			foreach ($data as $fieldname=>$fielddata)
			{
				if (is_array($fielddata))
				{
					$fieldtype = $fielddata[0];
					$autoincrement = $fielddata[1];
					$isindex = $fielddata[2];
					$notnull = (isset($fielddata[3])) ? (($fielddata[3] == 1) ? false: true) : true;
					
					$fk = (isset($fielddata[4])) ? true: false;
					if ($fk == true)
					{
						$ref = $fielddata[4][0];
						$onupdate = $fielddata[4][1];
						$ondelete = $fielddata[4][2];
						$foreign[] = "FOREIGN KEY ($fieldname) REFERENCES $ref ON UPDATE $onupdate ON DELETE $ondelete";
					}
					
					if ($notnull == true) $fieldtype .= " NOT NULL";
					if ($autoincrement == true) $fieldtype .= " AUTO_INCREMENT";
					
					if ($isindex == true)
					{
						if ($fk == false)
							$primary = $fieldname;
						else
							$index[] = "INDEX ($fieldname)";
					}
				}
				else
					$fieldtype = $fielddata." NOT NULL";
				$fields[] = "$fieldname $fieldtype";
			}
			if ($primary !== false) $fields[] = "PRIMARY KEY ($primary)";
			if (count($index) > 0) $fields = array_merge($fields, $index);
			if (count($foreign) > 0) $fields = array_merge($fields, $foreign);
			$fields = implode(", ", $fields);
			$sql = "CREATE TABLE IF NOT EXISTS $table ($fields) ENGINE=INNODB";
			$queries[] = $sql;
		}
		
		//execute the queries
		foreach ($queries as $sql)
		{
			if (self::$db->exec($sql) === false)
			{
				$errorinfo = self::$db->errorInfo();
				self::$msg = "Error in SQL:\n".$errorinfo[2]."\n\nSQL:\n$sql";
				return false;
			}
		}
		
		return true;
	}
}
?>