# SistemaTesseraSanitariaApiClient
Client PHP per inviare i documenti di spesa al Sistema Tessera Sanitaria col servizio sistema-ts-api.it

Questa libreria PHP consente di inviare i documenti di spesa al Sistema Tessera Sanitaria dal tuo gestionale al Sistema Tessera Sanitaria (STS) del Ministero delle Finanze, tramite il servizio https://www.sistema-ts-api.it

## Utilizzo
La libreria è composta da un'unica classe: *SistemaTesseraSanitariaApiClient*

I metodi principali sono:
* ***inviaDocumentoSpesa()*** - Invia un documento di spesa al STS
* ***reinviaDocumentoSpesa()*** - Se il documento non era stato inviato a causa di un errore, questo metodo consente di aggiornare i dati e ritentare l'invio
* ***inserisciErogatore()*** - Aggiunge un soggetto erogatore alla propria anagrafica
* ***aggiornaErogatore()*** - Aggiorna un soggetto erogatore della propria anagrafica (es: aggiornamento del pincode scaduto)
* ***cancellaErogatore()*** - Elimina un soggetto erogatore dalla propria anagrafica

### Inizializzazone
```php
$username = '......'; // Username e password forniti dal servizio
$password = '......';
$isTest = true; // per lavorare con l'endpoint di test
$sistemaTsApi = new SistemaTesseraSanitariaApiClient($username, $password, NULL, $isTest);
```
### Invio di un documento di spesa
```php
/**
 * Invia un documento di spesa al STS
 * PEr i valori dei campi, vedere la documentazione: https://www.sistema-ts-api.it/documentazione/
 * @param string $partitaIvaErogatore La partita iva di un soggetto già censito nell'anagrafica Erogatori
 * @param array $datiDocumento Campi: tipoDocumento, numeroDocumento, dataDocumento, dispositivo, dataPagamento, codiceFiscaleCittadino, flagOpposizione
 * @param array[] $vociSpesa Array di array: Campi: tipoSpesa, importo, aliquotaIVA, naturaIVA
 * @return null|array dati del documento inviato
 */
function inviaDocumentoSpesa($partitaIvaErogatore, $datiDocumento, $vociSpesa) {}
```
### Re-Invio di un documento di spesa
```php
/**
 * Se il documento non era stato inviato a causa di un errore, questo metodo consente di aggiornare i dati e ritentare l'invio
 * @param int $idDocumento ID precedentemente ritornato da SistemaTesseraSanitariaAPI
 * @param array $datiDocumento Vedere inviaDocumentoSpesa()
 * @param array[] $vociSpesaVedere inviaDocumentoSpesa()
 * @return null|array dati del documento aggiornato
 */
function reinviaDocumentoSpesa($idDocumento, $datiDocumento, $vociSpesa) {}
```

### Aggiunta di un soggetto erogatore
```php
/**
 * Aggiunge un soggetto erogatore alla propria anagrafica
 * @param array $arrCampi Campi: denominazione, partitaIva, codiceFiscale, usernameSts, passwordSts, pincode, descrizione
 * @return null|array dati del soggetto
 */
function inserisciErogatore($arrCampi) {}
```

### Aggiornamento di un soggetto erogatore
```php
/**
 * Aggiorna un erogatore
 * @param int $idErogatore
 * @param array $arrCampi Campi: denominazione, partitaIva, codiceFiscale, usernameSts, passwordSts, pincode, descrizione
 * @return null|array dati del soggetto
 */
function aggiornaErogatore($idErogatore, $arrCampi) {}
```

### Eliminazione di un soggetto erogatore
```php
/**
 * Elimina un soggetto erogatore dalla propria anagrafica
 * @param int $idErogatore
 * @return null|array dati del soggetto erogatore cancellato
 */
function cancellaErogatore($idErogatore) {}

```

## Guida ai Dati per creare un Documento di spesa
Per un elenco completo dei dati utilizzabili per creare una documento di spesa vedere la guida: https://www.sistema-ts-api.it/documentazione

