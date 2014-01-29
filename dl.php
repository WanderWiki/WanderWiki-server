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
/* cleaning the $_GET array of any security harmful code */
require("clean.inc.php");

$_CLEAN[] = clean($_GET);

if (!(isset($_CLEAN['traceid'])))
{
	die ("traceid parameter is missing");
}

/* connexion to database */
require("infoDB.inc.php");
	
$mysqli = new mysqli($host,$user,$password,$dbname);

if ($mysqli->connect_error) 
	{
		die('Connexion error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
	}
	
/* preparing query */	

$quer='CALL `'.$dbname.'`.`dl_update`('.$_CLEAN['traceid'].')';


if(!($result=$mysqli->query($quer)))
	{
		die('Request error (' . $mysqli->errno . ') '. $mysqli->error);
	}

$result->free_result();
	
$mysqli->close();


