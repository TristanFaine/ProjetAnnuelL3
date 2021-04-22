<?php
//Cette page affiche le detail de la requete (si elle est valide..)
//Puis appelle le script correspondant, avec certains parametres
//PENSER A BIEN FAIRE EN SORTE QU'ON NE PEUT PAS INJECTER DE CODE MALVEILLANT.

//Separer plus tard la page en une page de verification et une page d'execution, je suppose.

//responses to get must be cached, responses to post must not

//Formation du chemin pour appeler le script correspondant.
switch (strtolower($_POST['source'])) {
	case "reddit":
		$path = realpath("src/crawlers/crawler_reddit/france/QueryCrawler.py");
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
	default:
	//On essaye de faire une demande invalide, donc afficher une page d'erreur
	//Do: Route to error page
		$path = realpath("src/crawlers/test/test.py");
		break;
}

//Sauveur de vie en dessous:
//https://stackoverflow.com/questions/34957283/how-to-properly-call-python-3-script-from-php


//Appel du code correspondant.. on va trouver un moyen de faire autre chose que exec plus tard.
$args = array($_POST['source'], $_POST['limit'], $_POST['aux']);

echo "DEBUG APPEL : " . $path . " " . escapeshellarg(json_encode($args));
//2>&1 pour afficher stderr dans stdout. pratique.

//Donne un enorme string qui est du JSON (normalement):
$result = shell_exec($path . " " . escapeshellarg(json_encode($args)) . " 2>&1");
echo "<br/>";
echo "<br/>";

//Convertit la chaine encodee JSON en une variable PHP... peut-etre pas necessaire de faire cette etape
#$resultData = json_decode($result, true);

var_dump($result);
echo "<br/>";echo "<br/>";echo "<br/>";echo "<br/>";
#var_dump($resultData);

//Cela semble fonctionner, maintenant il faut verifier que les VRAIS scripts fonctionnent.


//Vu que nos sorties json sont a etage unique (pas un vrai terme mais bon)
//on peut simplement faire une boucle for values X[0] puis X[1] puis X[2] etc..


//Pour mettre du json dans postgres:
//faire une colonne json et un truc du genre:
// INSERT INTO orders (infojson)
// VALUES('{ "customer": "Lily Bush", "items": {"product": "Diaper","qty": 24}}'),
//       ('{ "customer": "Josh William", "items": {"product": "Toy Car","qty": 1}}'),
//       ('{ "customer": "Mary Clark", "items": {"product": "Toy Train","qty": 2}}');

//Pour recup du JSON:
//SELECT infojson FROM orders;

//Un peu plus complique:
//SELECT info ->> 'customer' AS customer
//FROM orders
//WHERE info -> 'items' ->> 'product' = 'Diaper';

//-> est un operateur par cle, ->> est un operateur par text.


//Encore un peu plus complique:
//SELECT info ->> 'customer' AS customer,
//	info -> 'items' ->> 'product' AS product
//FROM orders
//WHERE CAST ( info -> 'items' ->> 'qty' AS INTEGER) = 2

//On peut utiliser les fonctions d'agregat et autre, de facon normal:
//SELECT 
//   MIN (CAST (info -> 'items' ->> 'qty' AS INTEGER)),
//   MAX (CAST (info -> 'items' ->> 'qty' AS INTEGER)),
//   SUM (CAST (info -> 'items' ->> 'qty' AS INTEGER)),
//   AVG (CAST (info -> 'items' ->> 'qty' AS INTEGER))
//FROM orders;

//D'autres fonctions existent tel json_each, json_object_keys, json_typeof, etc..


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
	<p> source = <?php echo $_POST['source'];?> </p>
	<p> limit = <?php echo $_POST['limit'];?> </p>
	<p> aux = <?php echo $_POST['aux'];?> </p>
	</div>
    
	<div id="stuff">
	<p> requete = <?php
	echo "shell_exec(" . $path . " " . escapeshellarg(json_encode($args)) . " 2>&1)";
	?>
	</p>
	<p> mettre un truc de progression si c'est possible? </p>
	</div>

</body>

</html>
