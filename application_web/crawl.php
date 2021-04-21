<?php
//Cette page affiche le detail de la requete (si elle est valide..)
//Puis appelle le script correspondant, avec certains parametres
//PENSER A BIEN FAIRE EN SORTE QU'ON NE PEUT PAS INJECTER DE CODE MALVEILLANT.


//Formation du chemin pour appeler le script correspondant.
switch (strtolower($_GET['source'])) {
	case "reddit":
		$path = realpath("src/crawlers/test/test.py");
		break;
	case "discord":
		$path = realpath("src/crawlers/crawler_discord/fetch.js");
		break;
	case "quora":
		$path = realpath("src/crawlers/crawler_web/quora/question.py");
		break;
	case "test":
		$path = realpath("src/crawlers/test/test.py");
		break;
}

//https://stackoverflow.com/questions/34957283/how-to-properly-call-python-3-script-from-php


//Appel du code correspondant.. on va trouver un moyen de faire autre chose que exec plus tard.
$args = array($_GET['source'], $_GET['limit'], $_GET['aux']);

//list of things https://stackoverflow.com/questions/34957283/how-to-properly-call-python-3-script-from-php

echo $path . " " . escapeshellarg(json_encode($args));

//2>&1 pour afficher stderr dans stdout. pratique.
$result = shell_exec($path . " " . escapeshellarg(json_encode($args)) . " 2>&1");
echo "<br/>";
echo "<br/>";
echo "<br/>";

$resultData = json_decode($result, true);

var_dump($result);
echo "<br/>";echo "<br/>";echo "<br/>";echo "<br/>";
var_dump($resultData);

//Cela semble fonctionner, maintenant il faut verifier que les VRAIS scripts fonctionnent.



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>La meilleure page web de l'univers 2</title>
</head>

<body>
	<h1>Application web Crawler</h1>
    <h3>Debug infos demande</h2>
    
	<div id="debug"> 
	<p> source = <?php echo $_GET['source'];?> </p>
	<p> limit = <?php echo $_GET['limit'];?> </p>
	<p> aux = <?php echo $_GET['aux'];?> </p>
	</div>
    
	<div id="stuff">
	<p> requete = <?php
	echo "shell_exec(" . $path . " " . escapeshellarg(json_encode($args)) . " 2>&1)";
	?>
	</p>
	</div>

</body>

</html>
