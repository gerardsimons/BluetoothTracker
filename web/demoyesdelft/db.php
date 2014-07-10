<?php
require_once("settings.php");

error_reporting(E_ALL);
ini_set("display_errors", 1);

query("CREATE TABLE IF NOT EXISTS YesDemo_Units
		(
		ID int AUTO_INCREMENT,
		MAC text,
		CoordX double,
		CoordY double,
		PRIMARY KEY (ID)
		)", array());

query("CREATE TABLE IF NOT EXISTS YesDemo_Tracking
		(
		ID int AUTO_INCREMENT,
		UnitID int,
		LabelID int,
		Timestamp double,
		SignalStrength double,
		PRIMARY KEY (ID)
		)", array());

/*query("CREATE TABLE IF NOT EXISTS YesDemo_Position
		(
		ID int AUTO_INCREMENT,
		LabelID int,
		Timestamp int,
		CoordX double,
		CoordY double,
		PRIMARY KEY (ID)
		)", array());*/
?>