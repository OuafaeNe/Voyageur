<?php
	include_once("ville.class.php");
	include_once ("villes.class.php");
	include_once ("dijkstra.class.php");

	$GLOBALS['Villes'] = new Villes();
	$gps = false;
	$result = false;
	$uploadfile = "villes.col";

	//On vérifie si le formulaire d'envoi de fichier a été soumis
	if(isset($_POST['Envoyer']))
	{
		//On vérifie si l'upload de fichier s'est bien effectué
		if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $uploadfile)) {
			$GLOBALS['msg'] = "Erreur dans l'enregistrement du fichier";
		}
	}

	//On vérifie si on dispose d'un fichier exploitable
	if (is_file($uploadfile))
	{
		//On parse le fichier en transférant toutes les données sous forme objet
		parse($uploadfile);
		$js = render_js($GLOBALS['Villes']->getList());

		//On indique par la variable gps que le formulaire de demande d'itinéraire est authorisé
		$gps = true;

		//Si le formulaire d'itinéraire a été soumis
		if (isset($_GET['go']))
		{
			// On créé une instance de la classe Dijkstra qui calcule l'itinéraire
			$dijkstra = new Dijkstra($GLOBALS['Villes']->getList(),$_GET['depart'],$_GET['arrivee']);
			$result = true;
		}
	}


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Recherche opérationnelle</title>
		<link rel="stylesheet" href="vis.min.css">
		<link rel="stylesheet" href="style.css">
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<header>
			<h1>Recherche opérationelle</h1>
			<h2>GPS</h2>
		</header>
		<div class="content large">
			<div id="the_graph"></div>

			<?php if ($result): ?>
				<h2>La distance entre <?= $dijkstra->xo()->nom() ?> et <?= $dijkstra->xn()->nom() ?> est de <div class="dist"><?= $dijkstra->xn()->poids() ?>kms</div></h2>

				<?php render_path($dijkstra->path()) ?>
			<?php endif ?>

			<?php if ($gps): ?>
				<form action="" method="GET">
					<?php render_select($GLOBALS['Villes'],'depart') ?>
					<?php render_select($GLOBALS['Villes'],'arrivee') ?>
					<input type="submit" name="go" class="button" value="Go">
				</form>
			<?php endif ?>

			<form action="" method="post" enctype="multipart/form-data">
				<input type="file" name="fichier" required>
				<input type="submit" name="Envoyer" class="button" value="Envoyer">
			</form>
			<script type="text/javascript" src="vis.min.js"></script>
			<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
			<script type="text/javascript"><?= $js ?></script>
		</div>
	</body>
</html>

<?php

	/**
	* Parse les villes
	**/
	function parse($file){

		//On extrait le contenu texte du fichier
		$str = file_get_contents($file);
		$array = explode("\n", $str);
		foreach ($array as $key => &$ligne) {
			if (substr($ligne, 0,1)== "c" || substr($ligne, 0,1)== "p")
			{
				unset($array[$key]);
			}
			else if(substr($ligne, 0,1)== "v")
			{
				//Pour chaque ligne commencant par un on créé une ville
				$ligne = explode(" ", $ligne);
				$vi = new Ville(strip_hidden_chars(beautifier($ligne[2])), $ligne[1]);

				// Mise à jour de la liste dans l'objet Villes
				$tempList = $GLOBALS['Villes']->getList();
				array_push($tempList, $vi);
				$GLOBALS['Villes']->setList($tempList);
				unset($array[$key]);
			}
			else
			{
				$ligne = explode(" ", $ligne);
			}
		}

		//On parcourt les lignes restantes qui sont les lignes étapes pour les associer aux villes correspondantes
		foreach ($array as $key => $ligne) {
			$id = $ligne[1];
			foreach ($GLOBALS['Villes']->getList() as $k2 => $ville) {
				if($id == $ville->id())
				{
					$ville->ajouteFils($ligne[2], $ligne[3]);

					unset($array[$key]);
				}
			}
		}
	}

	/**
	* Génère le code JS des noeuds et liens
	**/
	function render_js($villes){
		$nodes = "var nodes = [
			";
		$edges = "
		var edges = [
			";
		foreach ($villes as $key => $ville) {
			$nodes .= '{id: ' . $ville->id() . ', label: "' . $ville->nom() . '", title: ' . $ville->id() . '},';
			foreach ($ville->fils() as $key => $fils) {
				$edges .= '{from: ' . $ville->id() . ', to: ' . $fils[0] . ', value: ' . $fils[1] . ', label: ' . $fils[1] . '},';
			}
		}

		$nodes .= '
		];';
		$edges .= '
		];';

		$graph_settings = '
		var container = document.getElementById("the_graph");
		var data = {
			nodes: nodes,
			edges: edges,
		};
		  var options = {
			width: \'1300px\',
			height: \'1300px\',
			dragNetwork: \'false\',
			dragNodes: \'false\',
			smoothCurves: \'false\',
			zoomable: \'false\',
			hover: \'true\',
		};
		var network = new vis.Network(container, data, options);';
		return $nodes.$edges.$graph_settings;
	}

	/**
	* Affiche le chemin à suivre
	**/
	function render_path($path){
		$prev_poids = null;
		$cpt = 0;
		$path = array_reverse($path, true);
		echo '
		<div class="itineraire">';
		echo '	<h2>Itinéraire</h2>';
		echo '	<ul>';
		echo '		<li>';
		foreach ($path as $key => $ville) {
			echo '<div class="ville">' . $ville->nom() . '</div>';
			if($cpt>=1): echo ' sur ' . ($ville->poids() - $prev_poids) . 'kms';endif;
			if($key!=0): echo '</li>
			<li> puis suivre la direction de ';
			endif;
			$prev_poids = $ville->poids();
			$cpt++;
		}
		echo '</ul>
		</div>';
	}


	/********
	 ******** Helpers
	 ********
	 ********/


	/*
	 * Retire les caractères cachés par l'encodage
	 */

	function strip_hidden_chars($str)
	{
		$chars = array("\r\n", "\n", "\r", "\t", "\0", "\x0B");

		$str = str_replace($chars," ",$str);

		return preg_replace('/\s+/',' ',$str);
	}

	/**
	* Remplace les _ par des - et ajoute des majuscules
	**/
	function beautifier($str){
		return str_replace(' ', '-', (ucwords(str_replace('_', ' ', $str))));
	}

	/**
	* Génère un selecte avec l'ensemble des villes
	**/
	function render_select($villes, $name){
		echo '<select name="' . $name . '">';
		foreach ($villes->getList() as $key => $ville) {
			echo '<option value="' . $ville->id() . '">' . $ville->nom() . '</option>
			';
		}
		echo '</select>';

	}

?>