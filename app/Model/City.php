<?php


	class City extends AppModel{

		public $hasMany = array('Address');


		public $validate = array
						  (
						   	'name' => array
						   	 (
						   	 	'rule1' => array
						   	 	(
						   	 		'rule' => 'notEmpty',
						   	 		'message' => 'Preenchimento obrigatório.'
						   	 	),

						   	 	'rule2' => array
						   	 	(
						   	 		'rule' => 'isUnique',
						   	 		'message' => 'Cidade já cadastrada.'
						   	 	)
						   	 )
					      );

	}


?>
