<?php

	/**
	* Instance de ville
	*/
	class Ville
	{
		private $nom;
		private $id;
		private $fils = array();
		private $visited;
		private $pere;
		private $poids;

		function __construct($n, $id){
			$this->nom = $n;
			$this->id = $id;
			$this->visited = false;
			$this->pere = null;
			$this->poids = -1;
		}

		function ajouteFils($i, $d){
			$fiston = array($i, $d);
			array_push($this->fils, $fiston);
		}

		function id(){
			return $this->id;
		}

		function nom(){
			return $this->nom;
		}

		function fils(){
			return $this->fils;
		}

		function visited(){
			return $this->visited;
		}

		function isVisited(){
			$this->visited = true;
		}

		function poids(){
			return $this->poids;
		}

		function setPoids($poids){
			$this->poids = $poids;
		}

		function pere(){
			return $this->pere;
		}

		function setPere($pere){
			$this->pere = $pere;
		}
	}




?>