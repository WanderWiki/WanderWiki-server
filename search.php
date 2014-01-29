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
Parameters compulsory to fill in the URL:


searchType (from 0 to 5): define research type
	0 -> search users using pseudo,
	1 -> search traces using the end position of the trace,
	2 -> search traces using the start position of the trace,
	3 -> search traces using author,
	4 -> search traces using key words,
	5 -> advanced search.

For each search 1-4, atleast limtime or limdist has to be defined
For search 5, at least one of the paramater listed below has to be defined
*/


/* Different search and their parameters
searchType 0 :
	search users using pseudo
	To fill in the URL:
		-pseudo
		
searchType 1 :
	search traces using the ending position of the trace
	To fill in the URL:
		-Lat
		-Lng
		-limdist
		-limtime

searchType 2:
	search traces using the starting position of the trace
	To fill in the URL:
		-Lat
		-Lng
		-limdist
		-limtime
		
searchType 3 :
	search traces using key words
	To fill in the URL:
		-pseudo
		-limtime
		-limdist
		
searchType 4 : 
	recherche de trajets par mot clés
	To fill in the URL:
		-content
		-limtime
		-limdist

searchType 5 :
	advanced search
	To fill in the URL:
		-content
		-pseudo
		-Lat
		-Lng
		-limdist
		-limtime
		
		

/* defining constante useful for the search */
$long='startLng';
$lat='startLat';

$table=null;
$index=0;

/* cleaning the $_GET array of any security harmful code */
require("clean.inc.php");

$_CLEAN = clean($_GET);

/* cleaning the $_GET array of any security harmful code */
if (!(isset($_CLEAN['searchType'])))
{
	die ("searchType parameter is missing");
}


if (isset($_CLEAN['nbre_result']))
{
	$nbre_result=$_CLEAN['nbre_result'];
}
else
{
	$nbre_result=20;
}
if (isset($_CLEAN['offset']))
{
	$offset=$_CLEAN['offset'];
}
else
{
	$offset=0;
}

if (isset($_CLEAN['sort']))
{
	if ($_CLEAN['sort']==1)
	{
		$sort=' ORDER BY votePos-voteNeg LIMIT '.$nbre_result.' OFFSET '.$offset;
	}
	else
	{
		$sort=' ORDER BY dl LIMIT '.$nbre_result.' OFFSET '.$offset;
	}
}





/* defining fonction useful for search */

/* function get_distance_mètres($lon1,$lat1,$lon2,$lat2),
	calculate the distance between two point using GPS coordinates */
function get_distance_mètres($lon1,$lat1,$lon2,$lat2)
{
	$rlo1= deg2rad($lon1);
	$rla1= deg2rad($lat1);
	$rlo2= deg2rad($lon2);
	$rla2= deg2rad($lat2);
	
	$dlo=($rlo2-$rlo1)/2;
	$dla=($rla2-$rla1)/2;
	
	$aux=sin($dla)*sin($dla)+cos($rla1)*cos($rla2)*sin($dlo)*sin($dlo);
	
	$res= (6378137*2*atan2(sqrt($aux),sqrt(1-$aux)));

	return $res;
}

/* function get_indice_tri($dl,$vote)
calculate a make up indice for popularity */
function get_indice_tri($dl,$vote)
{
	return ($dl+4*$vote);
}

/* function array_sort ($array,$nom)
function used to sort the array according to the $nom field */
function array_sort ($array,$nom)
{
	foreach ($array as $key => $row)
		{
			$dist[$key] = $row[$nom];
		}
		array_multisort($dist,SORT_ASC,$array);
		return $array;
}
	
	
/* Connexion to database*/
require("infoDB.inc.php");

$mysqli = new mysqli($host,$user,$password,$dbname);

if ($mysqli->connect_error) 
	{
	die('Connexion error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
	}


/* Slection of research mode */

switch ($_CLEAN['searchType'])
{

/* search of an user */	
	case 0 :
		
		if (!(isset($_CLEAN['pseudo'])))
		{
			die ("pseudo parameter is missing");
		}
		/* preparing a request seeking a match of pseudo */ 
		$quer='SELECT * FROM users WHERE pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\' LIMIT '.$nbre_result.' OFFSET '.$offset;
		
		if(!($result=$mysqli->query($quer)))
		{
			die('Request error (' . $mysqli->errno . ') '. $mysqli->error);
		}
		
		/* checking that security allows to display the user account otherwise setting it to an empty string */
		while($row=mysqli_fetch_array($result,MYSQLI_ASSOC))
		{
			if ($row['security']==0)
			{
				$row['account']="";
			}
			
			$table[$index]=$row;
			$index++;
		}

	break;	
	
	
	
/* search using the ending position of the trace*/
	case 1:             
		
		$long='endLng';
		$lat='endLat';
		/* not using break command since case 1 and 2 use exactly the same methods once $long and $lat are defined */
/* recherche par rapport à la position de départ */		
	case 2:
		
		/* checking that basic parameters for this search are defined */
		if (!(isset($_CLEAN['Lon'])))
		{
			die ("Lon parameter is missing");
		}
		
		if (!(isset($_CLEAN['Lat'])))
		{
			die ("Lat parameter is missing");
		}
		
		
		/* preparing the request according to what parameters are defined */
		
		if (isset($_CLEAN['limdist']))
		{
		if (isset($_CLEAN['limtime']))
		{	
			$quer='SELECT * FROM files  WHERE time< '.$_CLEAN['limtime'].' AND length < '.$_CLEAN['limdist'];
		}
		
		else
		{
			$quer='SELECT * FROM files  WHERE length < '.$_CLEAN['limdist'];
		}
		}
		elseif (isset($_CLEAN['limtime']))
		{
			$quer='SELECT * FROM files WHERE time < '.$_CLEAN['limtime'];
		}
		else
		{
			die('Missing arguments : limtime or limdist');
		}
	
		if (!($result=$mysqli->query($quer)))
		{
			die('Request error (' . $mysqli->errno . ') '. $mysqli->error);
		}
		
		
		/* adding a column in the table that contain the dist between the position given in the URL and the position defined by $long and $lat*/
		
		while($row=mysqli_fetch_array($result,MYSQLI_ASSOC))
		{
			$table[$index]=array_merge($row,$tab_aux);
			$tab_aux= array("dist" => get_distance_mètres($row[$long],$row[$lat],$_CLEAN['Lon'],$_CLEAN['Lat']));
			$index++;				
		}
		
		/* sorting the table using the dist field */
		$table=array_sort($table,"dist");
		$table=array_slice($table,$offset,$nbre_result);

	break;
	
	
/* search using author*/		
	case 3:
		
		/* checking that basic parameters for this search are defined */
		if (!(isset($_CLEAN['pseudo'])))
		{
			die ("Pseudo parameter is missing");
		}

		/* preparing the request according to what parameters are defined */
		if (isset($_CLEAN['limdist']))
		{
			if (isset($_CLEAN['limtime']))
			{
				$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\'AND time< '.$_CLEAN['limtime'].' AND length < '.$_CLEAN['limdist'] ;
			}
			else
			{
				$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\'AND length < '.$_CLEAN['limdist'] ;
			}
		}
		elseif (isset($_CLEAN['limtime']))
		{
			$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE  users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\' AND time< '.$_CLEAN['limtime'];
		}
		else
		{
			die('Missing argument : limtime or limdist');
		}
		
		$quer=$quer.$sort;
		
		if(!($result=$mysqli->query($quer)))
		{
			die('Request error (' . $mysqli->errno . ') '. $mysqli->error);
		}
		
		/* There is a JOIN used in the request, so the request returns both files and users table, that is why an array_slice is used, to only get files content,
			beside there is a conflict between files.id and users.id that why idfiles appear in the request and is added at the beginning of the array */
		while($row=mysqli_fetch_array($result,MYSQLI_ASSOC))
		{
			$tab_aux= array("id" =>$row['idfiles']);
			$row=array_merge ($tab_aux,array_slice($row,1,13));
			$table[$index]=$row;
			$index++;				
		}
		
	break;


/* recherche par contenu */		
	case 4 :
		/* checking that basic parameters for this search are defined */
		if (!(isset($_CLEAN['content'])))
			{
				die ("content parameter is missing");
			}
		
		
		/* preparing the request according to what parameters are defined */
		if (isset($_CLEAN['limdist']))
		{
			if (isset($_CLEAN['limtime']))
			{
				$quer='Select *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND time< '.$_CLEAN['limtime'].' AND length < '.$_CLEAN['limdist'] ;
			}
			else
			{
				$quer='Select *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND length < '.$_CLEAN['limdist'] ;
			}
		}
		elseif (isset($_CLEAN['limtime']))
		{
			$quer='Select *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND time< '.$_CLEAN['limtime'];
		}
		else
		{
			die('Missing argument : limdist or limtime');
		}
		
		$quer=$quer.$sort;
		if(!($result=$mysqli->query($quer)))
		{
			die('Request error (' . $mysqli->errno . ') '. $mysqli->error);
		}
		

		while($row=mysqli_fetch_array($result,MYSQLI_ASSOC))
		{
			$tab_aux= array("id" =>$row['idfiles']);
			$tab_aux_2= array("pseudo"=>$row['pseudo']);
			$row=array_merge ($tab_aux,array_slice($row,1,13));
			$row=array_merge ($row,$tab_aux_2);
			$table[$index]=$row;
			$index++;
		}

	break;

	
// Recherche détaillée
	case 5 :
	
	
		/* preparing the request according to what parameters are defined */
		if (isset($_CLEAN['limdist']))
		{
			if (isset($_CLEAN['limtime']))
			{
				if (isset($_CLEAN['userid']))
				{
					if (isset($_CLEAN['content']))
					{
						$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND  users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\' AND time< '.$_CLEAN['limtime'].' AND length < '.$_CLEAN['limdist'];
					}
					else
					{
						$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE  users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\' AND time< '.$_CLEAN['limtime'].' AND length < '.$_CLEAN['limdist'];
					}
				}
				elseif (isset($_CLEAN['content']))
				{
					$quer='SELECT *, files.id as idfiles FROM files WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND time< '.$_CLEAN['limtime'].' AND length < '.$_CLEAN['limdist'];
				}
				else
				{
					$quer='SELECT *, files.id as idfiles FROM files WHERE  time< '.$_CLEAN['limtime'].' AND length < '.$_CLEAN['limdist'];
				}
			}
			elseif (isset($_CLEAN['userid']))
			{
				if(isset($_CLEAN['content']))
				{
					$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\' AND length < '.$_CLEAN['limdist'];
				}
				else
				{
					$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE  users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\' AND length < '.$_CLEAN['limdist'];
				}
			}
			else
			{
				if (isset($_CLEAN['content']))
				{
					$quer='SELECT *, files.id as idfiles FROM files WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND length < '.$_CLEAN['limdist'];
				}
				else
				{
					$quer='SELECT *, files.id as idfiles FROM files WHERE length < '.$_CLEAN['limdist'];
				}
			}
		}
		elseif (isset($_CLEAN['limtime']))
		{
			if (isset($_CLEAN['userid']))
			{
				if (isset($_CLEAN['content']))
				{
					$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND  users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\' AND time< '.$_CLEAN['limtime'];
				}
				else
				{
					$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\' AND time< '.$_CLEAN['limtime'];
				}
			}
			elseif (isset($_CLEAN['content']))
			{
				$quer='SELECT *, files.id as idfiles FROM files WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND time< '.$_CLEAN['limtime'];
			}
			else
			{
				$quer='SELECT *, files.id as idfiles FROM files WHERE time< '.$_CLEAN['limtime'];
			}
		}
		elseif (isset($_CLEAN['userid']))
		{
			if (isset($_CLEAN['content']))
			{
				$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\' AND  users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\'';
			}
			else
			{
				$quer='SELECT *, files.id as idfiles FROM files INNER JOIN users ON files.idAuthor=users.id WHERE  users.pseudo LIKE \'%'.$_CLEAN['pseudo'].'%\'';
			}
		}
		elseif (isset($_CLEAN['content']))
		{
			$quer='SELECT *, files.id as idfiles FROM files WHERE description LIKE \'%'.$_CLEAN['content'].'%\' OR title LIKE \'%'.$_CLEAN['content'].'%\'';
		}
		else
		{
			die('Too many parameters missing');
		}
		
		
		
		/* treating the result of the request differently whether or not it is necessary to calculate dist column */
		if (isset($_CLEAN['Lat']) and isset($_CLEAN['Lon']))
		{
			if(!($result=$mysqli->query($quer)))
			{
				die('Request error (' . $mysqli->errno . ') '. $mysqli->error);
			}
		
			while($row=mysqli_fetch_array($result,MYSQLI_ASSOC))
			{
				$tab_aux= array("id" => $row['idfiles'],"dist" => get_distance_mètres($row[$long],$row[$lat],$_CLEAN['Lon'],$_CLEAN['Lat']));
				$row=array_slice($row,1,13);
				$table[$index]=array_merge($tab_aux,$row);
				$index++;
			
			}
		
			$table=array_sort($table,"dist");
			$table=array_slice($table,$offset,$nbre_result);
		}
		else
		{
			$quer=$quer.$sort;
			if(!($result=$mysqli->query($quer)))
			{
				die('Request error (' . $mysqli->errno . ') '. $mysqli->error);
			}
		
			while($row=mysqli_fetch_array($result,MYSQLI_ASSOC))
			{
				$tab_aux= array("id" =>$row['idfiles']);
			$row=array_merge ($tab_aux,array_slice($row,1,13));
			$table[$index]=$row;
			$index++;
			
			}
		}
}
	
echo json_encode($table);

$result->free_result();

$mysqli->close();
	