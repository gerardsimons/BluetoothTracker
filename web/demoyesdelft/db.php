<?php
require_once("settings.php");

error_reporting(E_ALL);
ini_set("display_errors", 1);

query("CREATE TABLE IF NOT EXISTS YesDemo_Units
		(
		ID int AUTO_INCREMENT,
		MAC text,
		Lat double,
		Lon double,
		Acc double,
		Name text,
		Hide int,
		Calibration double,
		PRIMARY KEY (ID)
		)", array());

query("CREATE TABLE IF NOT EXISTS YesDemo_Tracking
		(
		ID int AUTO_INCREMENT,
		UnitID int,
		LabelID int,
		Timestamp double,
		SignalStrength double,
		Lat double,
		Lon double,
		Acc double,
		PRIMARY KEY (ID)
		)", array());

query("CREATE TABLE IF NOT EXISTS YesDemo_Version
		(
		Version int,
		PRIMARY KEY (VERSION)
		)", array());
		
query("CREATE TABLE IF NOT EXISTS YesDemo_Positions
		(
		ID int AUTO_INCREMENT,
		LabelID int,
		Timestamp double,
		Lat double,
		Lon double,
		Acc double,
		NrUnits int,
		PRIMARY KEY (ID)
		)", array());

query("CREATE TABLE IF NOT EXISTS YesDemo_Settings
		(
		ID int AUTO_INCREMENT,
		Var text,
		Val text,
		PRIMARY KEY (ID)
		)");

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