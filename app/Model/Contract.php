<?php

	class Contract extends AppModel
	{
	
		public $belongsTo = array('Person', 'Course', 'Period');

		public $hasMany = array('Event');

		public $validate = array
		(			

			'year' => array
			(
				'rule1' => array
				(
					'rule' => 'notEmpty',
					'message' => 'Preenchimento obrigatório.'														
				),

				'rule2' => array
				(
					'rule' => 'checkOnCreate',
					'on' => 'create',
					'message' => 'Erro, verifique todo o formulário' // Mensagem igual ao do método !
				),

				'rule3' => array
				(
					'rule' => 'checkOnUpdate',
					'on' => 'update',
					'message' => 'TESTE'
				),

				'rule4' => array
				(
					'rule' => 'validateYear' /*,
					'message' => 'Data inválida.'*/
				)
			),

			//SEMESTRE
			'semester' => array
			(
				'rule1' => array
				(
					'rule' => 'notEmpty',
					'message' => 'Preenchimento obrigatório.'
				),

				'rule2' => array
				(
					'rule' => 'validateSemester',
					'message' => 'Semestre inválido.'
				)
			),

			//IDENTIFICAÇÃO DO BANCO
			'bank_num' => array
			(
				'rule' => 'notEmpty',
				'message' => 'Preenchimento obrigatório.'					
			),

			//Datas
			'date_of_execution' => array
			(
				'rule1' => array
				(
					'rule' => 'notEmpty',
					'message' => 'Digite uma data.'
				),

				'rule2' => array
				(
					'rule' => array('date', 'dmy'),
					'message' => 'Digite uma data válida.'
				)
			),

			'date_of_closing' => array
			(

				'rule1' => array
				(
					'rule' => 'notEmpty',
					'message' => 'Digite uma data.'
				),

				'rule2' => array
				(
					'rule' => array('date', 'dmy'),
					'message' => 'Digite uma data válida.'
				)

					
			),

			'course_id' => array
			(
				'rule' => 'notEmpty',
				'message' => 'Escolha um curso.'
			),


			// Data da rescisão, se estiver vazio okay, se não, deve ser no formato date dmy !!
			'date_rescinded' => array
			(
				'rule1' => array
				(
					'rule' => array('date', 'dmy'),
					'allowEmpty' => true,
					'message' => 'Digite uma data válida.'
				)
			),

			'period_id' => array
			(
				'rule' => 'notEmpty',
				'message' => 'Escolha um período.'
			),
		);	
	
		public function beforeValidate()
		{
			
			// if ( isset($this->data['Contract']['date_of_closing']) or isset($this->data['Contract']['date_of_execution']) )
			// {
			// 	$contractDateOfClosing = $this->data['Contract']['date_of_closing'];
			// 	$contractDateOfExecution = $this->data['Contract']['date_of_execution'];

			// 	// Bloco responsável por trocar barras por hífens
			// 	if ( strlen($contractDateOfExecution) == 8 or strlen($contractDateOfExecution) == 10 )
			// 	{
			// 		$this->data['Contract']['date_of_execution'] = str_replace('/', '-', $contractDateOfExecution);
			// 	}
				
			// 	// Bloco responsável por trocar barras por hífens
			// 	if ( strlen($contractDateOfClosing) == 8 or strlen($contractDateOfClosing) )
			// 	{
			// 		$this->data['Contract']['date_of_closing'] = str_replace('/', '-', $contractDateOfClosing);
					
			// 	}
				

			// }

			return true;
		}

		// Antes de criar
		public function checkOnCreate()
		{
			// Se o validador ao final deste método continuar valendo 1 retorne TRUE, ou seja, valide !
			$validador = 1;


			// Se no formulário estiver marcado ativo
			if ( $this->data['Contract']['active'] == 1 )
			{

				// Conta quantos contratos existem ativos da pessoa
				$pequisa = $this->find('count', array
				(
					'conditions' => array
					(
						'Contract.active' => '1',
						'Contract.person_id' => $this->data['Contract']['person_id']
					)
				));


				// Se existir algum contrato ativo retorna false,
				if ( $pequisa > 0 )
				{
					// Avisa o usuário do erro
					$this->invalidate('active', 'Já existem contratos ativos deste cliente.');
					
					// Existe erro
					$validador = 0;
				}

			}


			//verificando se existe ano e semestre cadastrado para o cliente
			$resultado = $this->find('count', array
			(
				'conditions' => array
				(
					'Contract.year' => $this->data['Contract']['year'],
					'Contract.person_id' => $this->data['Contract']['person_id'],
					'Contract.semester' => $this->data['Contract']['semester'],
				),



				'fields' => array
				(
					'year', 'person_id', 'semester'
				)
			));

			// se houverem cadastro para o ano e semestre do cliente avise para o usuário
			if ( $resultado > 0 )
			{
				//Avisando o usuário sobre os erros
				//$this->invalidate('year', 'Já existe cadastro para o ano e semestre informado.');
				$this->invalidate('semester', 'Já existe cadastro para o ano e semestre informado.'); //igual ao rule2
				
				//Existe erro
				$validador = 0;
			}

			// Se existem pare por aqui
			if ( $validador == 0 )
			{
				return false;
			}


			//Se não retorne true !
			return true;
		}

		public function validateSemester( $data )
		{
			$semester = $data['semester'];

			if ( $semester != 1 and $semester != 2 )
			{
				return false;
			}

			return true;
		}

		// Validação do ANO
		public function validateYear( $data )
		{
			// 1 = Válido
			$validador = 1;

			$year = (int) $data['year'];

			if ( strlen($year) == 2 )
			{
				// Usuário digitou 12 retorne 2012
				$year += 2000;
			}


			if ( $year < 2000 )
			{
				// Mostrando o problema na tela !
				$this->invalidate('year', 'São aceitos apenas anos maiores ou iguais a 2 mil.');
				
				// 0 = Inválido
				$validador = 0;
			}

			// Inválido ?
			if ( $validador == 0 )
			{
				return false;
			}
			
			return true;
		}

		

		public function checkOnUpdate()
		{	

			// pega os dados do checkbox do form
			
			$selecaoActive = $this->data['Contract']['active'];

			debug($this->data);

			// pesquisa o contrato
			$pesquisa = $this->read(null, $this->data['Contract']['id']);

			debug($this->data);

			// pega a flag do contrato pesquisado
			$bancoActive = $pesquisa['Contract']['active'];

			// debug($pesquisa['Contract']['active']);
			// debug($selecaoActive);

			// // se o usuario selecionou o checkbox faça
			// if ( $selecaoActive )
			// {
			// 	// exit;

			// 	// Se o que o usuário ativou o checkbox e no banco está inativo, ou se são diferentes, faça
			// 	if ( $selecaoActive != $bancoActive )
			// 	{

			// 		exit;

			// 		$argumento2 = array
			// 		(
			// 			'conditions' => array
			// 			(
			// 				'Contract.person_id' => $this->data['Contract']['person_id'],
			// 				'Contract.active' => '1'
			// 			),

			// 			'fields' => array
			// 			(
			// 				'Contract.person_id', 
			// 				'Contract.active'
			// 			)
			// 		);



			// 		// Conte quantos registros existem ativos da pessoa em questão !
			// 		$pesquisa = $this->find('count', $argumento2);

			// 		debug($pesquisa);


			// 		// Se for diferente de zero é pq existem contratos, ou seja, pare por ai...
			// 		if ($pesquisa != 0)
			// 		{
			// 			$this->invalidate('active', 'É permitido no máximo um contrato ativo por pessoa, e esse cliente já possui um cadastro ativo.');
			// 			return false;
			// 		}
			// 	}
			// }

			return true;
		}

		public function beforeSave( ) 
		{
			// Se a variável for passada faça isso
			if ( isset( $this->data['Contract']['date_of_execution'] ) )
			{
				// Utilizando uma var com nome menor
				$dt = $this->data['Contract']['date_of_execution'];

				// Se passarem 31/12/12 converta para 31-12-2012
				if ( strlen( $dt ) == 8 )
				{
					$dt = substr($dt, 0,2) . '-' . substr($dt, 3,2) . '-20' . substr($dt, 6,2);
				}

				// Exemplo: Entrada -> 31-12-2012 ... Saída 2012-12-31
				$this->data['Contract']['date_of_execution'] = substr($dt, 6, 4).'-'.substr($dt, 3, 2).'-'.substr($dt, 0, 2);

			}

			// Se a variável for passada faça isso
			if ( isset( $this->data['Contract']['date_of_closing'] ) )
			{
				// Utilizando uma var com nome menor
				$dt = $this->data['Contract']['date_of_closing'];

				// Se passarem 31/12/12 converta para 31-12-2012
				if ( strlen( $dt ) == 8 )
				{
					$dt = substr($dt, 0, 2) . '-' . substr($dt, 3, 2) . '-20' . substr($dt, 6, 2);
				}

				// Exemplo: Entrada -> 31-12-2012 ... Saída 2012-12-31
				$this->data['Contract']['date_of_closing'] = substr($dt, 6, 4) . '-' . substr($dt, 3, 2) . '-' . substr($dt, 0, 2) ;

			}

			// Se a variável for passada faça isso
			if ( isset($this->data['Contract']['date_rescinded']) )
			{
				// Utilizando uma var com nome menor
				$dt = $this->data['Contract']['date_rescinded'];
				
				if ( strlen($dt) == 8 )
				{	// Se passarem 31/12/12 converta para 31-12-2012
					$dt = substr($dt, 0,2) . '-' . substr($dt, 3, 2) . '-20' . substr($dt, 6,4);
				}
				// Exemplo: Entrada -> 31-12-2012 ... Saída 2012-12-31
				if ( strlen($dt) == 10 )
				{
					$this->data['Contract']['date_rescinded'] = substr($dt, 6, 4) . '-' . substr($dt, 3, 2) . '-' . substr($dt, 0, 2);
				}

				if ( empty($this->data['Contract']['date_rescinded']) )
				{
					if ( !$this->data['Contract']['active'] )
					{
						$this->invalidate('active', 'Contrato inativo, por favor insira a data da rescisão.');

						return false;
					}
				}
			}
			return true;
		}
	}
