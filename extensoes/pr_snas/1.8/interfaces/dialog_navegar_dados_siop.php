<?php

?>

<!-- DIALOG NAVEGAR SIOP -->

<script type="text/javascript">

	var pesquisarSiop = function(ano, unidade) {
		$.post("modelos/prazos/dados_siop.php", {
			acao: 'listar-programas',
			ano: ano,
			unidade: unidade
		},
		function(data) {
			try {
				if (data) {
					$('#tdProgramas').empty();
					$('#tdUnidade').empty();
					
					var spanUnid = $('<span>');
					spanUnid.html('Unidade: ' + data[0].NOME_UNIDADE);
					$('#tdUnidade').append(spanUnid);
					
					$.each(data, function(i) {
						var spanPrg = $('<span>');
						var txt = 'Programa (' + (i+1) + '/' + data.length + '): ';
						spanPrg.html(txt + data[i].COD_PROGRAMA + ' - ' + data[i].TIT_PROGRAMA);
						spanPrg.attr('id', 'spnProg' + data[i].COD_PROGRAMA);
						spanPrg.attr('idProg', data[i].COD_PROGRAMA);
						if (i != 0) {
							spanPrg.addClass('spnOculto');
						}
						$('#tdProgramas').append(spanPrg);
					});
					$('#hdnTotalProg').val(data.length);
					$('#hdnProgAtual').val(0);
					$('#hdnAno').val(ano);
					$('#hdnUnidade').val(unidade);
					$('#hdnUnidadeBusca').val(data[0].ID_UNIDADE);
					$('#hdnCodigoOrgaoSiop').val(data[0].CODIGO_ORGAO_SIOP);
					$('#hdnIdMetasSelecionadas').val('');
					$('#hdnIdPOSelecionados').val('');
					
	            	$('#divDadosSiop').dialog('open');

	            	$('#btnSelecionar').show();
	            	$('.imgNavegacao').show();
	            	
	            	navegar(0);
                } else {
                	alert('Não foram encontrados programas para esta Unidade neste exercício!');
                }
			} catch (e) {
				alert('Ocorreu um erro ao tentar listar os programas!\n[' + e + ']');
			}
		}, "json");
		return false;
	}

	function navegar(ind) {
		$('.tbDadosSiop').html('<tr id="trTexto"><td colspan="5">Aguarde...</td></tr>');
		tot = $('#hdnTotalProg').val();
		if (ind < 0) {
			ind = 0;
		} else if (ind >= (tot - 1)) {
			ind = tot - 1;
		}
		$('#hdnProgAtual').val(ind);
		var spans = $("#tdProgramas").children();
		var idProg = '';
		spans.each(function(i, span) {
			if ($(span).hasClass('spnOculto')) {
				$(span).removeClass('spnOculto');
			}
			if (i != ind) {
				$(span).addClass('spnOculto');
			} else {
				idProg = $(span).attr('idProg'); 
			}
		});
		var opcoes = '&prazo=' + $('#hdnResPrazoIdPrazo').val() 
			+ '&prog=' + idProg 
			+ '&org=' + $('#hdnCodigoOrgaoSiop').val() 
			+ '&ano=' + $('#hdnAno').val() 
			+ '&unid=' + $('#hdnUnidade').val(); 
		$('#divTabObjetivos').load('tab_detalhes_programas_siop.php?aba=obj' + opcoes).show();
		$('#divTabAcoes').load('tab_detalhes_programas_siop.php?aba=acoes' + opcoes).show(); 
		$('#tabsDetalhes').tabs({ active: 1 });
	}

	$(document).ready(function() {
		$('#divDadosSiop').dialog({
			autoOpen: false,
	        resizable: false,
	        modal: true,
	        width: 1100,
	        heigth: 600,
	        buttons: {
				Selecionar: {id: "btnSelecionar",
					text: "Selecionar", 
					click: function() {
						if (($('#hdnIdMetasSelecionadas').val() != '') || ($('#hdnIdPOSelecionados').val() != '')) {
							if (confirm('Você tem certeza que deseja selecionar as metas e/ou ações?')) {
								$.post("modelos/prazos/prazos.php", {
										acao: 'salvar-resposta-ppa',
										sq_prazo: $('#hdnResPrazoIdPrazo').val(),
										id_unidade: $('#hdnUnidade').val(),
										orgao_siop: $('#hdnCodigoOrgaoSiop').val(),
										ano_exercicio: $('#hdnAno').val(),
										metas: $('#hdnIdMetasSelecionadas').val(),
										acoes_po: $('#hdnIdPOSelecionados').val()
									},
									function(data) {
										try {
											if (data.success == 'true') {
						                        alert('Metas e/ou Ações Selecionadas com Sucesso!');
						                        carregarMetasPPA($('#divBuscaPPA'), false, $('#hdnResPrazoIdPrazo').val(), false); /* ATUALIZA A LISTA DE METAS E AÇÕES NA TELA DE RESPOSTA */
						                        $('#divDadosSiop').dialog("close");
						                    } else {
												alert(data.error);
											}
										} catch (e) {
											alert('Ocorreu um erro ao selecionar as metas e/ou ações:\n[' + e + ']');
										}
									},
									"json"
								);
							}
						} else {
							alert('Escolha uma ou mais metas e/ou ações.');
						}
					}
				},
				Cancelar: function() {
					$(this).dialog("close");
				}
			}
		});

		$('#imgPrimeiroProg').click(function() { navegar(0); });
		$('#imgAnteriorProg').click(function() { navegar(parseInt($('#hdnProgAtual').val()) - 1); });
		$('#imgPosteriorProg').click(function() { navegar(parseInt($('#hdnProgAtual').val()) + 1); });
		$('#imgUltimoProg').click(function() { navegar(parseInt($('#hdnTotalProg').val()) - 1); });

	});
        
</script>

<style type="text/css" title="currentStyle">

	.imgBotao {
		margin: 0px;
		cursor: pointer;
	}
	
	.tblLayout {
		font-family: inherit;
		font-size: inherit;
		font-style: inherit;
		color: inherit;
		border-collapse: collapse;
		margin-bottom: 5px;
	}
	
	.wdt100 {
		width: 100%;
	}
	
	.tdNavegacao {
		width: 34px;
		text-align: center;
		vertical-align: middle;
	}
	
	#tdUnidade {
		width: 100%;
		font-size: 1.2em;
		font-weight: bold;
		text-align: center;
		vertical-align: middle;
	}
	
	#tdProgramas {
		font-size: 1.2em;
		padding-left: 5px;
		padding-right: 5px;
		width: 100%;
	}
	
	.spnOculto {
		display: none;
	}
	
	#divDetalhes {
		height: 500px;
	}
	
	.divOverflow {
		height: 440px;
		overflow: auto;
	}
</style>

<div id="divDadosSiop" title="Dados SIOP">
	<input type="hidden" id="hdnAno"/>
	<input type="hidden" id="hdnUnidade"/>
	<input type="hidden" id="hdnUnidadeBusca"/>
	<input type="hidden" id="hdnCodigoOrgaoSiop"/>
	<input type="hidden" id="hdnTotalProg"/>
	<input type="hidden" id="hdnProgAtual"/>
	
	<input type="hidden" id="hdnIdMetasSelecionadas" />
	<input type="hidden" id="hdnIdPOSelecionados" />

	<div id="divPrograma" class="wdt100">
		<table class="wdt100 tblLayout">
			<tr>
				<td id="tdUnidade" colspan="5">
				
				</td>
			</tr>
			<tr style="height: 50px;">
				<td class="tdNavegacao">
					<img id="imgPrimeiroProg" class="imgBotao imgNavegacao" src="imagens/icones/32/Gnome-Go-First-32.png" alt="Primeiro Programa" title="Primeiro Programa" />
				</td>
				<td class="tdNavegacao">
					<img id="imgAnteriorProg" class="imgBotao imgNavegacao" src="imagens/icones/32/Gnome-Go-Previous-32.png" alt="Programa Anterior" title="Programa Anterior" />
				</td>
				<td id="tdProgramas">
	
				</td>
				<td class="tdNavegacao">
					<img id="imgPosteriorProg" class="imgBotao imgNavegacao" src="imagens/icones/32/Gnome-Go-Next-32.png" alt="Pr&oacute;ximo Programa" title="Pr&oacute;ximo Programa" />
				</td>
				<td class="tdNavegacao">
					<img id="imgUltimoProg" class="imgBotao imgNavegacao" src="imagens/icones/32/Gnome-Go-Last-32.png" alt="&Uacute;ltimo Programa" title="&Uacute;ltimo Programa" />
				</td>
			</tr>
		</table>
	</div>
	
	<div id="divDetalhes">
		<div id="tabsDetalhes">
			<ul>
				<li><a href="#divTabObjetivos">Objetivos / Metas</a></li>
				<li><a href="#divTabAcoes">A&ccedil;&otilde;es / Localizadores / PO</a></li>
			</ul>
			<div id="divTabObjetivos" class="divOverflow"></div>
			<div id="divTabAcoes" class="divOverflow"></div>
		</div>
	</div>
	
</div>