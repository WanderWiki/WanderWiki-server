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
This script gets the list of Wikipedia articles geolocalised in a certain area from a database, modify this list and send it to the Android app
	
It uses the JSON-Kolossos.php script written by Kolossos <http://de.wikipedia.org/wiki/User:Kolossos> and that can be found on <http://toolserver.org/~kolossos/geoworld/marks-json.php.orig>
*/

/* Cleaning the $_GET array of any security harmful code */
require("clean.inc.php");
$_CLEAN = clean($_GET);

/* Verification of the sent parameters */
if(!(isset($_CLEAN['lon_inf'])&&isset($_CLEAN['lat_inf'])&&isset($_CLEAN['lon_sup'])&&isset($_CLEAN['lat_sup'])&&isset($_CLEAN['lang'])&&isset($_CLEAN['maxRows'])))
{
	die('Missing arguments');
}

/* JSON recovery */
$url_to_call = 'http://toolserver.org/~kolossos/geoworld/marks-json.php?bbox='.$_CLEAN['lon_inf'].','.$_CLEAN['lat_inf'].','.$_CLEAN['lon_sup'].','.$_CLEAN['lat_sup'].'&LANG='.$_CLEAN['lang'].'&maxRows='.$_CLEAN['maxRows'];
$json =file_get_contents($url_to_call);

if(!$json)
{
	die('Error with ToolServer');
}

/* Deletion of all parts that will prevent the JSON to be decoded  */
$table_modif = array(
	"\"elevation\":," => "",
	"\"population\":," => "",
	"},\n]}" => "}]",
	"{\"geonames\":" => "");

$json = strtr($json , $table_modif);

/* Remove all parts that are not interesting for our application */
$articles = json_decode($json , true);

foreach ($articles as $i => $value) 
{
	unset($articles[$i]['countryCode']);
	unset($articles[$i]['distance']);
	unset($articles[$i]['bearing']);
	unset($articles[$i]['elevation']);
	unset($articles[$i]['lang']);
	unset($articles[$i]['population']);
	unset($articles[$i]['image']);
	unset($articles[$i]['region']);
}

/* Displaying JSON */
echo json_encode($articles);