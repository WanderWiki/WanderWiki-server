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

/* Script that clean variables of any security harmful code */
require("clean.inc.php");

/* Verification of the sent track */
if(!isset($_FILES['uploaded_file'])&&($_FILES['uploaded_file']['error']==0))
{
	die('Error in track sending');
}

/* Verification of the file's extension */
if(pathinfo($_FILES['uploaded_file']['name'],PATHINFO_EXTENSION)!= 'gpx')
{
	die('You are not allowed to send something else than a GPX file');
}

/** 
* Getting the parameters from the GPX file 
* The parameters title, length, description, articles, time and idAuthor are written between markup with their explicit names
* The parameters startLat, startLng, endLat and endLng are taken from the first and last point of the GPX, where they are attributes
*/
$xml = new SimpleXmlElement(file_get_contents($_FILES['uploaded_file']['tmp_name']));

if(!($title = clean($xml->xpath('title'))))
{
	die('Unabled to get title');
}
if(!($length = clean($xml->xpath('length'))))
{
	die('Unabled to get length');
}
if(!($description = clean($xml->xpath('description'))))
{
	die('Unabled to get description');
}
if(!($articles = clean($xml->xpath('articles'))))
{
	die('Unabled to get articles');
}
if(!($time = clean($xml->xpath('time'))))
{
	die('Unabled to get time');
}
if(!($idAuthor = clean($xml->xpath('idAuthor'))))
{
	die('Unabled to get idAuthor');
}

/* Getting latitude and longitude for first point */
if(!($firstPoint = $xml->trk->trkseg->trkpt[0]->attributes()))
{
	die('Unabled to get first point');
}
$startLat = $firstPoint[0];
$startLng = $firstPoint[1];

/* Getting the key for the last point */
$lastPointKey = count($xml->trk->trkseg->trkpt) - 1;

/* Getting latitude and longitude for last point */
if(!($lastPoint = $xml->trk->trkseg->trkpt[$lastPointKey]->attributes()))
{
	die('Unabled to get last point');
}
$endLat = $lastPoint[0];
$endLng = $lastPoint[1];

/* Connexion to database */
require("infoDB.inc.php");
$mysqli = new mysqli($host,$user,$password,$dbname);

if ($mysqli->connect_error) 
{
	die('Connexion error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

/* Preparation and sending of the query */
$query= "CALL new_trace ('". $title[0]."', ".$length[0].", '".$description[0]."', '".$articles[0]."', ".$startLat.", ".$startLng.", ".$endLat.", ".$endLng.", ".$time[0].", ".$idAuthor[0].",@id);
SELECT @id;";

if(!($mysqli->multi_query($query)))
{
	die('Request error (' . $mysqli->errno . ') '. $mysqli->error);
}

/* Getting the id sent by procedure 'new_trace' and obtained with LAST_INSERT_ID */
$mysqli->next_result();
$aux=$mysqli->use_result();
$id=$aux->fetch_row();

/* Storing the gpx file and giving it the name of its id in the database */
$file_path = 'tracks/' . $id[0] .'.gpx';

if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $file_path))
{
	chmod($file_path, 0755);
	echo "success/".$id[0];
}
else
{
	$delete_query='DELETE FROM files WHERE id=' .$id[0] ;
	$mysqli->query($delete_query);
	die('Error in moving file');
}


$mysqli->close();