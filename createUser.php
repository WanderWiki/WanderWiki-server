<?php
/*
	This file is part of WanderWiki project.

    WanderWiki project is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WanderWiki is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WanderWiki.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
This script creates a user in the WanderWiki database
*/

/* Cleaning the $_GET array of any security harmful code */
require("clean.inc.php");
$_CLEAN = clean($_GET);

/* Verification of the sent parameters */
if(!(isset($_CLEAN['email_adr'])&&isset($_CLEAN['pseudo'])&&isset($_CLEAN['security'])))
{
	die('Missing arguments');
}
if($_CLEAN['email_adr']==NULL)
{
	die('Missing email adresse');
}
if($_CLEAN['pseudo']==NULL)
{
	die('Missing pseudo');
}

/* Connexion to database */
require("infoDB.inc.php");
$mysqli = new mysqli($host,$user,$password,$dbname);

if ($mysqli->connect_error) 
{
	die('Connexion error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

/* Preparation and sending of the query */
$query="INSERT INTO `users` ( `account`, `pseudo`, `security` ) 
VALUES ('".$_CLEAN['email_adr']."','".$_CLEAN['pseudo']."',".$_CLEAN['security'].")";

if($mysqli->query($query))
{
	/* Send the id associated with the created user */
	echo 'success/'.$mysqli->insert_id;
}
else
{
	die('Request error (' . $mysqli->errno . ') '. $mysqli->error);
}


$mysqli->close();