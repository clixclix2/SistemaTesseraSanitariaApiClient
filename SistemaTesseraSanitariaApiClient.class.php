<?php

namespace Itala;
/**
 * Libreria Client PHP per utilizzare il servizio Sistema Tessera Sanitaria API v.1 - https://www.sistema-ts-api.it
 * Guida: https://www.sistema-ts-api.it/documentazione/
 * @author Itala Tecnologia Informatica S.r.l. - www.itala.it
 * @version 1.0.1
 * @since 2026-03-10
 */
class SistemaTesseraSanitariaApiClient
{
	/**
	 * Indicare credenziali utente fornite da Sistema Tessera Sanitaria API, oppure il Token di autenticazione se già disponibile
	 * @param string $username
	 * @param string $password
	 * @param string $authToken
	 * @param bool $testMode
	 * @throws \Exception
	 */
	function __construct($username = NULL, $password = NULL, $authToken = NULL, $testMode = false)
	{
		if ($username === NULL && $password === NULL && $authToken === NULL) {
			throw new \Exception('Either username and password, or authToken must be provided.');
		}
		
		$this->username = $username;
		$this->password = $password;
		
		if ($authToken) {
			$this->authToken = $authToken;
		}
		
		$this->testMode = $testMode;
		$this->endpoint = $this->endpoints[$testMode ? 'test' : 'prod'];
	}
	
	
	/**
	 * Invia un documento di spesa al STS
	 * PEr i valori dei campi, vedere la documentazione: https://www.sistema-ts-api.it/documentazione/
	 * @param string $partitaIvaErogatore La partita iva di un soggetto già censito nell'anagrafica Erogatori
	 * @param array $datiDocumento Campi: tipoDocumento, numeroDocumento, dataDocumento, dispositivo, dataPagamento, codiceFiscaleCittadino, flagOpposizione
	 * @param array[] $vociSpesa Array di array: Campi: tipoSpesa, importo, aliquotaIVA, naturaIVA
	 * @return null|array dati del documento inviato
	 */
	function inviaDocumentoSpesa($partitaIvaErogatore, $datiDocumento, $vociSpesa)
	{
		$data = array(
			'partitaIvaErogatore' => $partitaIvaErogatore,
			'tipoDocumento' => $datiDocumento['tipoDocumento'],
			'numeroDocumento' => $datiDocumento['numeroDocumento'],
			'dataDocumento' => $datiDocumento['dataDocumento'],
			'dispositivo' => $datiDocumento['dispositivo'] ?? NULL,
			'dataPagamento' => $datiDocumento['dataPagamento'] ?? NULL,
			'pagamentoTracciato' => $datiDocumento['pagamentoTracciato'] ?? NULL,
			'codiceFiscaleCittadino' => $datiDocumento['codiceFiscaleCittadino'],
			'flagOpposizione' => $datiDocumento['flagOpposizione'] ?? NULL,
			'vociSpesa' => $vociSpesa,
		);
		$ret = $this->call('post', '/documenti-spesa', $data);
		if ($ret) {
			return json_decode($ret, true);
		}
		return NULL;
	}
	
	/**
	 * Se il documento non era stato inviato a causa di un errore, questo metodo consente di aggiornare i dati e ritentare l'invio
	 * @param int $idDocumento ID precedentemente ritornato da SistemaTesseraSanitariaAPI
	 * @param array $datiDocumento Vedere inviaDocumentoSpesa()
	 * @param array[] $vociSpesaVedere inviaDocumentoSpesa()
	 * @return null|array dati del documento aggiornato
	 */
	function reinviaDocumentoSpesa($idDocumento, $datiDocumento, $vociSpesa)
	{
		$data = array(
			'tipoDocumento' => $datiDocumento['tipoDocumento'],
			'numeroDocumento' => $datiDocumento['numeroDocumento'],
			'dataDocumento' => $datiDocumento['dataDocumento'],
			'dispositivo' => $datiDocumento['dispositivo'] ?? NULL,
			'dataPagamento' => $datiDocumento['dataPagamento'] ?? NULL,
			'codiceFiscaleCittadino' => $datiDocumento['codiceFiscaleCittadino'],
			'flagOpposizione' => $datiDocumento['flagOpposizione'] ?? NULL,
		);
		$ret = $this->call('put', '/documenti-spesa/' . $idDocumento, $data);
		if ($ret) {
			return json_decode($ret, true);
		}
		return NULL;
	}
	
	/**
	 * Recupera i dati di un documento di spesa già inviato al sistema
	 * @param $idDocumento
	 * @return mixed|null
	 */
	function ottieniDocumentoSpesa($idDocumento)
	{
		$ret = $this->call('get', '/documenti-spesa/' . $idDocumento);
		if ($ret) {
			return json_decode($ret, true);
		}
		return NULL;
	}
	
	/**
	 * @param array $filtri Eventuali filtri: date_from, date_to, ... vedere documentazione API
	 * @return mixed|null
	 */
	function elencaDocumentiSpesa($filtri = [])
	{
		$ret = $this->call('get', '/documenti-spesa', $filtri);
		if ($ret) {
			return json_decode($ret, true);
		}
		return NULL;
	}
	
	
	/**
	 * Aggiunge un soggetto erogatore alla propria anagrafica
	 * @param array $arrCampi Campi: denominazione, partitaIva, codiceFiscale, usernameSts, passwordSts, pincode, descrizione
	 * @return null|array dati del soggetto
	 */
	function inserisciErogatore($arrCampi)
	{
		$ret = $this->call('post', '/erogatori', $arrCampi);
		if ($ret) {
			return json_decode($ret, true);
		}
		return NULL;
	}
	
	
	/**
	 * Aggiorna un erogatore
	 * @param int $idErogatore
	 * @param array $arrCampi Campi: denominazione, partitaIva, codiceFiscale, usernameSts, passwordSts, pincode, descrizione
	 * @return null|array dati del soggetto
	 */
	function aggiornaErogatore($idErogatore, $arrCampi)
	{
		$ret = $this->call('put', '/erogatori/' . $idErogatore, $arrCampi);
		if ($ret) {
			return json_decode($ret, true);
		}
		return NULL;
	}
	
	
	/**
	 * Estrae l'elenco degli erogatori abilitati all'invio
	 * Paginazione: verificare hasMoreResults() e getNextResults()
	 * @return null|array array di array(id, denominazione, partitaIva, usernameSts, passwordSts, descrizione, dataInserimento)
	 */
	function elencaErogatori()
	{
		$ret = $this->call('get', '/erogatori');
		if ($ret) {
			return json_decode($ret, true);
		}
		return NULL;
	}
	
	/**
	 * @param int $idErogatore
	 * @return null|array dati del soggetto erogatore
	 */
	function ottieniErogatore($idErogatore)
	{
		$ret = $this->call('get', '/erogatori/' . $idErogatore);
		if ($ret) {
			return json_decode($ret, true);
		}
		return NULL;
	}
	
	
	
	/**
	 * Elimina un soggetto erogatore dalla propria anagrafica
	 * @param int $idErogatore
	 * @return null|array dati del soggetto erogatore cancellato
	 */
	function cancellaErogatore($idErogatore)
	{
		$ret = $this->call('delete', '/erogatori/' . $idErogatore);
		if ($ret) {
			return json_decode($ret, true);
		}
		return NULL;
	}
	
	
	/**
	 * In caso di errore della chiamata (risposta NULL) qui abbiamo l'eventuale messaggio di errore
	 * @return string
	 */
	function getLastError()
	{
		return $this->lastError;
	}
	
	/**
	 * Ritorna l'ultimo codice HTTP ricevuto dal server
	 * @return string
	 */
	function getLastCode()
	{
		return $this->lastCode;
	}
	
	/**
	 * @param string $method get|post|put|delete|patch
	 * @param string $path (se inizia con 'http' richiama in get la url così com'è)
	 * @param array|string|null $data
	 * @return string|null
	 */
	protected function call($method, $path, $data = NULL)
	{
		$methodUp = strtoupper($method);
		
		$httpHeaders = [];
		
		if ($this->authToken && (!$this->authExpires || $this->authExpires > (new \DateTime())->add(new \DateInterval('PT5M'))->format('Y-m-d H:i:s'))) {
			// token valido
			$httpHeaders[] = 'Authorization: Bearer ' . $this->authToken;
		} else {
			$httpHeaders[] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
		}
		
		$curlOpts = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $methodUp
		];
		
		
		if ($methodUp === 'GET' && substr($path, 0, 4) === 'http') { // risultati successivi
			
			$callUrl = $path;
			
		} else { // caso normale
			
			if ($methodUp === 'GET') {
				if ($data === NULL) {
					$data = [];
				}
				if (!isset($data['per_page'])) {
					$data['per_page'] = 1000; // massimo consentito
				}
			}
			
			$callUrl = $this->endpoint . $path;
			
			if ($data !== NULL) {
				if (in_array($methodUp, ['POST', 'PUT', 'PATCH'])) {
					if (is_string($data)) {
						$curlOpts[CURLOPT_POSTFIELDS] = $data;
						$httpHeaders[] = 'content-type: application/xml';
					} else {
						$curlOpts[CURLOPT_POSTFIELDS] = json_encode($data);
						$httpHeaders[] = 'content-type: application/json';
					}
				} else { // 'GET', 'DELETE'
					$joinChar = '?';
					foreach ($data as $key => $val) {
						$callUrl .= $joinChar . $key . '=' . urlencode($val);
						$joinChar = '&';
					}
				}
			}
			
		}
		
		
		$curlOpts[CURLOPT_URL] = $callUrl;
		$curlOpts[CURLOPT_HTTPHEADER] = $httpHeaders;
		
		$responseHeaders = [];
		
		$curlOpts[CURLOPT_HEADERFUNCTION] = function ($curl, $header) use (&$responseHeaders) {
			$len = strlen($header);
			$arrHeader = explode(':', $header, 2);
			if (count($arrHeader) >= 2) { // ignore invalid headers
				$responseHeaders[trim($arrHeader[0])] = trim($arrHeader[1]);
			}
			return $len;
		};
		
		$curl = curl_init();
		
		curl_setopt_array($curl, $curlOpts);
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		
		$this->lastCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		curl_close($curl);
		
		if (isset($responseHeaders['X-auth-token'])) {
			$this->authToken = $responseHeaders['X-auth-token'];
			$this->authExpires = $responseHeaders['X-auth-expires'];
		}
		
		$this->lastGetHasNextUrl = NULL;
		if ($methodUp === 'GET' && isset($responseHeaders['Link'])) {
			if (preg_match('#^<([^>]+)>; rel="next"#', $responseHeaders['Link'], $matches)) {
				$this->lastGetHasNextUrl = $matches[1];
			}
		}
		
		if ($err) {
			$this->lastError = $err;
			return NULL;
		} elseif ($this->lastCode != 200) {
			$ae = json_decode($response, true);
			$this->lastError = isset($ae['error']) ? $ae['error'] : '';
			return NULL;
		} else {
			$this->lastError = '';
			return $response;
		}
	}
	
	public function hasMoreResults()
	{
		return $this->lastGetHasNextUrl !== NULL;
	}
	
	public function getNextResults()
	{
		return $this->call('get', $this->lastGetHasNextUrl);
	}
	
	private $lastGetHasNextUrl = NULL;
	
	
	private $endpoints = [
		'test' => 'https://sistema-ts-api.it/api/v1/test',
		'prod' => 'https://sistema-ts-api.it/api/v1/prod'
	];
	
	private $lastCode = '';
	private $lastError = '';
	
	private $testMode = false;
	private $endpoint = '';
	private $username = NULL;
	private $password = NULL;
	
	private $authToken = NULL;
	private $authExpires = NULL;
	private $authError = '';
}
