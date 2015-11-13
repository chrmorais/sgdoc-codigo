<?php

include_once(dirname(__FILE__) . '/include.soap.php');
agora("==================================================================");
agora("Qualitativo");
agora("==================================================================");

/*
 * Define que dados serão buscados no webservice. 
 * Utilizar quando precisar pegar apenas um tipo de dado específico.
 */

$baixar = array(
		'orgaos' => TRUE,
		'programas' => TRUE,
		'acoes' => TRUE,
		'objetivos' => TRUE,
		'metas' => TRUE,
		'localizadores' => TRUE,
		'planos' => TRUE
);
		
if(isset($qualitativo) && $qualitativo !== '') {
	foreach($baixar as $area => $valor) {
		$baixar[$area] = ($area == $qualitativo);		
	}
}

if($baixar['orgaos']) {
	/**
	 * ===================================================================
	 * Obtendo a base de dados de órgãos para o ano de exercício repassado
	 * 
	*/
	agora("Obtendo órgãos.");
	limparDadosSiop('orgaos', "exercicio = '{$exercicio}'","Limpando base de dados dos órgãos para o ano de {$exercicio}.");
	$orgaosSiorg = array();
	do {			
		$orgaosSiop = obterOrgaosPorAnoExercicio($exercicio);
		if(!$orgaosSiop['sucesso']) {
			agora("Houve um problema:");
			echo "\n";
			echo str_replace("<br>", "\n", $orgaosAux['mensagensErro']);
			echo "\n";
		} else {
			$numOrgaosSiop = 0;
			foreach($orgaosSiop['registros'] as $orgao) {
				if(isset($orgao['orgaoSiorg'])) {
					$orgaosSiorg[$orgao['orgaoSiorg']] = $orgao['snAtivo'];
				}
			}
		}
	} while (!$orgaosSiop['sucesso']);
	
	$contadorOrgaosSiop = 0;
	$totalOrgaosSiop = count($orgaosSiorg);
	$totalRegistrosOrgaos = 0;
	foreach($orgaosSiorg as $k => $j) {
		$codigoSiorg = $k;
		$contadorOrgaosSiop++;			
		do {			
			$orgaosAux = obterOrgaosPorCodigoSiorgAnoExercicio($codigoSiorg, $exercicio, $contadorOrgaosSiop, $totalOrgaosSiop);
			if(!$orgaosAux['sucesso']) {		
				agora("Houve um problema:");
				echo "\n";
				echo str_replace("<br>", "\n", $orgaosAux['mensagensErro']);
				echo "\n";
			} else {
				$numRegistrosOrgaos = 0;
				foreach($orgaosAux['registros'] as $orgao) {
					if(isset($orgao['tipoOrgao'])) {
						$orgao['orgaoSiorg'] = $codigoSiorg;
						salvarDadosSiop('orgaos', $orgao);
						$numRegistrosOrgaos++;
						$totalRegistrosOrgaos++;
					}						
				}
				if($numRegistrosOrgaos > 0) {
					agora("\tSalvados {$numRegistrosOrgaos} registros para o órgão {$codigoSiorg}.");
				} else {
					agora("\tNão foi encontrado nenhum registro para esse órgão.");
				}
			}
		} while (!$orgaosAux['sucesso']);
	}
	agora("Total de órgãos registrados: {$totalRegistrosOrgaos}.");
}

if($baixar['programas']) {
	/**
	 * ======================================================================
	 * Obtendo a base de dados de programas para o ano de exercício repassado
	 * 
	*/
	agora("Obtendo programas.");
	limparDadosSiop('programas', "exercicio = '{$exercicio}'","Limpando base de dados dos programas para o ano de {$exercicio}.");
	$orgaosSiop = obterOrgaosSiopPorExercicio($exercicio);
	$programas = array();
	$camposPrograma = obterCampos('programas');	
	$totalOrgaosSiop = count($orgaosSiop);
	$contadorProgramasPorOrgaoSiop = 0;
	foreach($orgaosSiop as $k => $j) {
		$codigoSiop = $j['CODIGOSIOP'];
		$contadorProgramasPorOrgaoSiop++;
		do {
			$programasAux = obterProgramasPorOrgaoAnoExercicio($codigoSiop, $exercicio, $contadorProgramasPorOrgaoSiop, $totalOrgaosSiop);
			if(!$programasAux['sucesso']) {
				agora("\tNão foi possível obter os dados dos programas:");
				echo "\n";
				echo str_replace("<br>", "\n", $programasAux['mensagensErro']);
				echo "\n";
			} else {
				$contadorProgramasPorOrgao = 0;
				foreach($programasAux['registros'] as $programa) {
					$contadorProgramasPorOrgao++;
					$programa['codigoOrgao'] = $codigoSiop;
					$chvPrograma = $programa['codigoPrograma'];
					foreach($camposPrograma as $chave => $tipo) {
						$default = '';
						if($tipo == \PDO::PARAM_INT) {
							$default = 0;
						}
						if($tipo == \PDO::PARAM_BOOL) {
							$default = false;
						}
						$programas["{$chvPrograma}:{$codigoSiop}"][$chave] = retornaValorValido($programa, $chave, $default);  
					}
					reset($camposPrograma);	
				}
				if($contadorProgramasPorOrgao > 0) {
					agora("\tEncontrados {$contadorProgramasPorOrgao} programa(s) para esse órgão.");
				} else {
					agora("\nNão foi encontrado nenhum programa para esse órgão.");
				}
			}
		} while(!$programasAux['sucesso']); 
	}		
	agora("Salvando programas encontrados.");
	foreach($programas as $programa) {
		salvarDadosSiop('programas', $programa);
	}	
	reset($programas);
}

if($baixar['acoes']) {
	/**
	 * ======================================================================
	 * Obtendo a base de dados de ações para o ano de exercício repassado
	 * 
	*/
	agora("Obtendo ações.");
	limparDadosSiop('acoes', " exercicio = '{$exercicio}' ","Limpando base de dados das ações para o ano de {$exercicio}.");
	$programas = obterProgramasSiopPorExercicio($exercicio);
	$totalProgramas = count($programas);
	$contadorProgramas = 0;
	foreach($programas as $programa) {
		$contadorProgramas++;
		$codigoPrograma = $programa['CODIGOPROGRAMA'];
		$acoesDoPrograma = obterAcoesPorPrograma($codigoPrograma, $exercicio, $contadorProgramas, $totalProgramas);
		if( !$acoesDoPrograma['sucesso'] ) {
			agora("\tNão foi possível obter os dados das ações do programa '{$codigoPrograma}':");
			echo "\n";
			echo str_replace("<br>", "\n", $acoesDoPrograma['mensagensErro']);
			echo "\n";
		} else {
			$numRegistrosAcoes = 0;
			foreach($acoesDoPrograma['registros'] as $acaoDoPrograma) {
				salvarDadosSiop('acoes', $acaoDoPrograma);
				$numRegistrosAcoes++;					
			}
			if($numRegistrosAcoes > 0) {
				agora("\tSalvadas {$numRegistrosAcoes} ações do programa '{$codigoPrograma}'.");
			} else {
				agora("\tO programa não tem ações.");
			}
			reset($acoesDoPrograma);
		}
	}
	reset($programas);
}

if($baixar["objetivos"]) {
	/**
	 * ======================================================================
	 * Obtendo a base de dados de objetivos para o ano de exercício repassado
	 *
	 */
	agora("Obtendo objetivos.");
	limparDadosSiop('objetivos', "exercicio = '{$exercicio}'","Limpando base de dados dos objetivos para o ano de {$exercicio}.");
	$objetivos = obterTodosObjetivosPorAnoExercicio($exercicio);
	if(!$objetivos['sucesso']) {
		agora("\tNão foi possível obter os dados dos objetivos:");
		echo "\n";
		echo str_replace("<br>", "\n", $objetivos['mensagensErro']);
		echo "\n";
	} else {
		$numRegistrosObjetivos = 0;
		foreach($objetivos['registros'] as $objetivo) {
			salvarDadosSiop('objetivos', $objetivo);
			$numRegistrosObjetivos++;
		}
		if($numRegistrosObjetivos > 0) {
			agora("Salvados {$numRegistrosObjetivos} objetivos.");
		} else {
			agora("Sem objetivos para serem salvados.");
		}
		reset($objetivos);
	}
}

if($baixar['metas']) {
	/**
	 * ======================================================================
	 * Obtendo a base de dados de metas para o ano de exercício repassado
	 *
	*/
	agora("Obtendo metas.");
	limparDadosSiop('metas', "exercicio = '{$exercicio}'","Limpando base de dados das metas para o ano de {$exercicio}.");
	$metas = obterTodasMetasPorAnoExercicio($exercicio);
	if(!$metas['sucesso']) {
		agora("\tNão foi possível obter os dados das metas:");
		echo "\n";
		echo str_replace("<br>", "\n", $metas['mensagensErro']);
		echo "\n";
	} else {
		$numRegistrosMetas = 0;
		foreach($metas['registros'] as $meta) {
			salvarDadosSiop('metas', $meta);
			$numRegistrosMetas++;
		}
		if($numRegistrosMetas > 0) {
			agora("Salvadas {$numRegistrosMetas} metas.");
		} else {
			agora("Sem metas para serem salvadas.");
		}
		reset($metas);
	}
}

if($baixar['localizadores']) {
	/**
	 * ======================================================================
	 * Obtendo a base de dados de localizações para o ano de exercício repassado
	 *
	*/
	agora("Carregando ações obtidos na base SIOP.");

	$acoesSiop = obterDadosSiop('acoes', "exercicio = {$exercicio}", array('"identificadorUnico"','"codigoAcao"'));
	$totalAcoes = count($acoesSiop);
	$contadorAcoes = 0;
	agora("Obtendo localizadores");
	limparDadosSiop('localizadores', "exercicio = '{$exercicio}'","Limpando base de dados dos localizadores para o ano de {$exercicio}.");
	$numTotalLocalizadores = 0;

	foreach($acoesSiop as $acaoSiop) {
		$contadorAcoes++;
		$identificadorUnicoAcao = $acaoSiop['IDENTIFICADORUNICO'];
		do {
			$localizadores = obterLocalizadoresPorAcao($exercicio, $identificadorUnicoAcao, $contadorAcoes, $totalAcoes);
			if(!$localizadores['sucesso']) {
				agora("\tNão foi possível obter os dados dos localizadores para a ação {$identificadorUnicoAcao}:");
				echo "\n";
				echo str_replace("<br>", "\n", $localizadores['mensagensErro']);
				echo "\n";
			} else {
				$numRegistrosLocalizadores = 0;
				foreach($localizadores['registros'] as $localizador) {
					salvarDadosSiop('localizadores', $localizador);
					$numRegistrosLocalizadores++;
					$numTotalLocalizadores++;
				}
				if($numRegistrosLocalizadores > 0) {
					agora("Salvados {$numRegistrosLocalizadores} localizadores para a ação {$identificadorUnicoAcao}.");
				} else {
					agora("Sem localizadores para a ação {$identificadorUnicoAcao}.");
				}
			}
		} while(!$localizadores['sucesso']);
	}
	agora("Salvados no total {$numTotalLocalizadores} localizadores.");
}

if($baixar['planos']) {
	agora("Carregando ações obtidos na base SIOP.");
	
	$acoesSiop = obterDadosSiop('acoes', "exercicio = {$exercicio}", array('"identificadorUnico"','"codigoAcao"'));
	
	agora("Obtendo planos orçamentários.");
	limparDadosSiop('planos_orcamentarios', "exercicio = '{$exercicio}'","Limpando base de dados dos planos orçamentários para o ano de {$exercicio}.");
	$numTotalRegistrosPlanos = 0;
	
	foreach($acoesSiop as $acaoSiop) {
		$identificadorUnicoAcao = $acaoSiop['IDENTIFICADORUNICO'];
		do {
			$planosOrcamentarios = obterPlanosOrcamentariosPorAcao($exercicio, $identificadorUnicoAcao);
			if(!$planosOrcamentarios['sucesso']) {
				agora("\tNão foi possível obter os dados dos planos orçamentários para a ação {$identificadorUnicoAcao}:");
				echo "\n";
				echo str_replace("<br>", "\n", $planosOrcamentarios['mensagensErro']);
				echo "\n";
			} else {
				$numRegistrosPlanos = 0;
				foreach($planosOrcamentarios['registros'] as $planoOrcamentario) {
					salvarDadosSiop('planos_orcamentarios', $planoOrcamentario);
					$numRegistrosPlanos++;
					$numTotalRegistrosPlanos++;
				}
				if($numRegistrosPlanos > 0) {
					agora("Salvados {$numRegistrosPlanos} planos orçamentários para a ação {$identificadorUnicoAcao}.");
				} else {
					agora("Sem planos orçamentários para a ação {$identificadorUnicoAcao}.");
				}
			}
		} while(!$planosOrcamentarios['sucesso']);
	}	
	agora("Salvados no total {$numTotalRegistrosPlanos} planos orçamentários.");
}