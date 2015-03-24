<?php

	/**
	* Effectue les traitements de Dijkstra
	*/
	class Dijkstra
	{
		private $villes; //Liste des villes
		private $xo; //Pointeur sur la ville de départ
		private $xn; //Pointeur sur la ville d'arrivée
		private $path = array(); //Itiéraire

		function __construct($villes, $id_xo, $id_xn)
		{
			//Initialisation de Dijkstra
			$this->villes = $villes;
			$this->xo = $this->getVilleById($id_xo);
			$this->xn = $this->getVilleById($id_xn);

			//Le poids de la ville de départ est de 0, et est considérée comme déjà visitée
			$this->xo->setPoids(0);
			$this->xo->isVisited();

			// On utilise la fonction reverse afin d'ajouter le sens inverse de chaque relation, afin de pouvoir circuler dans tous les sens possibles
			$this->reverse();

			$this->process($this->xo);
			$this->get_path();
		}

		function xo(){
			return $this->xo;
		}

		function xn(){
			return $this->xn;
		}

		function path(){
			return $this->path;
		}

		/**
		* Processus récursif de Dijkstra : en partant du point de départ on parcourt chaque fils
		* Si le fils courant n'a pas été visité ou que son poids est supérieur au poids courant,
		* on le met à jour et on parcourt ses fils
		**/
		function process($ville){
			foreach ($ville->fils() as $key => $value) {
				$fils = &$this->getVilleById($value[0]);
				if(!$fils->visited() || ($ville->poids()+$value[1]<$fils->poids()))
				{
					$fils->setPoids($ville->poids()+$value[1]);
					$fils->setPere($ville);
					$fils->isVisited();
					$this->process($fils);
				}
			}
		}

		/**
		* Retourne la référence d'une ville en fonction d'un id
		**/
		function &getVilleById($id){
			foreach ($this->villes as $key => $ville) {
				if($ville->id()==$id):return $this->villes[$key];endif;
			}
			return null;
		}

		/**
		* Ajoute l'inversement pere-fils / fils-pere
		**/
		function reverse(){
			foreach ($this->villes as $key => &$ville) {
				foreach ($ville->fils() as $key => $fils) {
					$this->getVilleById($fils[0])->ajouteFils($ville->id(), $fils[1]);
				}
			}
		}

		/**
		* Retourne un tableau contenant le chemin complet
		**/
		function get_path(){
			$ville = $this->xn;
			while($ville->id() != $this->xo->id())
			{
				array_push($this->path, $ville);
				$ville = $ville->pere();
			}
			array_push($this->path, $this->xo);
		}
	}

?>