<?php

use Itala\SistemaTesseraSanitariaApiClient;

// Invio di undocumento di spesa al STS

$username = '......'; // Username e password forniti dal servizio
$password = '......';

$isTest = true; // modalità test o produzione
require_once '../SistemaTesseraSanitariaApiClient.class.php';
$sistemaTsApi = new SistemaTesseraSanitariaApiClient($username, $password, NULL, $isTest);

$codiceFiscaleErogatore = '12345678901';

$datiDocumento = [
	'tipoDocumento' => 'F', // F = fattura, D = documento commerciale
	'numeroDocumento' => '123',
	'dataDocumento' => '2024-01-13',
	'codiceFiscaleCittadino' => 'AAABBB00A00H501A'
];

$vociSpesa = [
	[
		'tipoSpesa' => 'SP', // Prestazioni sanitarie
		'importo' => 100,
		'naturaIVA' => 'N2.2'
	]
];

$res = $sistemaTsApi->inviaDocumentoSpesa($codiceFiscaleErogatore, $datiDocumento, $vociSpesa);

if ($res) {
	$idSistemaTsApi = $res['id'];
	$statoSts = $res['statoSts'];
	$protocolloSts = $res['protocolloSts'];
	$messaggioSts = $res['messaggioSts'];
	if ($statoSts == 'ERRO') {
		echo "Errore durante l'invio al STS: " . $messaggioSts . "\n";
	} elseif ($statoSts == 'INVI') {
		echo "Documento inviato al STS con protocollo: " . $protocolloSts . "\n";
	}
} else {
    echo "Errore API: " . $sistemaTsApi->getLastError() . "\n";
}

