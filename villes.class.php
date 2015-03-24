<?php

	class Villes
	{
		private $list = array();

		/**
		* Ajoute une ville a la liste
		**/
		function ajouteVille($v)
		{
			array_push($this->list, $v);
		}

		function getList()
		{
			return $this->list;
		}

		function setList($list)
		{
			$this->list = $list;
		}
	}

?>