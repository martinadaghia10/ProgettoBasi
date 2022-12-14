<?php
     require 'C:\Users\stefa\vendor/autoload.php';  //da modificare con proprio path di autoload.php
  //per utilizzo mongodb
      use MongoDB\Client;
      
    class DatabaseHelper{
        
        private $db;

        private $client;
        private $collection;

        public function __construct($servername, $username, $password, $dbname, $port){
            $this->db = new mysqli($servername, $username, $password, $dbname, $port);
            if ($this->db->connect_error) {
                die("Connection failed: " . $this->db->connect_error);
            }   
            $this->client = new Client('mongodb://localhost:27017/');   
            $this->collection = $this->client->ConfvirtualMongo->logInserimenti;       
        }


        public function getUtente($username, $password){
            $query="call autenticazioneUtente(?, ?, @tipo, @ok)";
            // prende la connessione nella variabile db, esegue la funzione -> prepare che serve per far preparare la query
            $stmt=$this->db->prepare($query); 
            // diciamo che c'è un param da passare alle query (nel ?) (con s dico che è una stringa)
            $stmt->bind_param('ss', $username, $password); 
            // eseguo la query
            $stmt->execute();
            // prendo ciò che ritorna la query (non sai se c'è un error o meno)

            $query="SELECT @ok, @tipo";
            $stmt=$this->db->prepare($query);  
            $stmt->execute();
            $result=$stmt->get_result();
            // fetch_all prende tutte le righe dal risultato della query
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        

        public function insertUser($username, $password, $nome, $cognome, $data, $luogo){
            $query="call registrazioneUtente(?,?,?,?,?,?,@a)";
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ssssss', $username, $password, $nome, $cognome, $data, $luogo); 
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "Utente",
                'username'   => $username,
                'password' => $password,
                'nome'     => $nome,
                'cognome'     => $cognome,
                'data'     => $data,
                'luogo' => $luogo
            ]);
        }

        public function insertAmministratore($username){
            $query="call registrazioneAmministratore(?)";
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('s', $username); 
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "UtenteAmministratore",
                'username'   => $username
            ]);
        }

        public function insertSpeaker($username, $nome, $cv, $foto, $dipartimento){
            $query="call registrazioneSpeaker(?,?,?,?,?,@a)";
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sssss', $username, $nome, $cv, $foto, $dipartimento); 
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "UtenteSpeaker",
                'username'   => $username,
                'nome' => $nome,
                'cv'     => $cv,
                'foto'     => $foto,
                'dipartimento'     => $dipartimento
            ]);
        }

        public function insertPresenter($username, $nome, $cv, $foto, $dipartimento){
            $query="call registrazionePresenter(?,?,?,?,?,@a)";
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sssss', $username, $nome, $cv, $foto, $dipartimento); 
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "UtentePresenter",
                'username'   => $username,
                'nome' => $nome,
                'cv'     => $cv,
                'foto'     => $foto,
                'dipartimento'     => $dipartimento
            ]);
        }


        public function checkTipoUser($username, $password){
            $query="call autenticazioneUtente(?, ?, @tipo, @a)"; // chiama procedure per controllare che tipo di utente è loggato
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sssss', $username, $nome, $cv, $foto, $dipartimento); 
            $stmt->execute();

            


            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }


        public function caricaFoto($nomeFile, $datiFoto){
            $query="call inserimentoFoto(?,?,@a)";
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ss', $nomeFile, $datiFoto);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "Foto",
                'nomeFile'   => $nomeFile,
            ]);
        }

        public function caricaFileEsterni($nomeFile, $datiFoto){
            $query="call inserimentoFileEsterni(?,?,@a)";
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ss', $nomeFile, $datiFoto);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "FileEsterno",
                'nomeFile'   => $nomeFile,
            ]);
        }

        

        public function getUltimaFoto(){
            $query="SELECT idLogo FROM LOGO ORDER BY idLogo desc limit 1"; // ritorna l'ultimo inserito
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        public function getUltimoFile(){
            $query="SELECT idFile FROM fileesterni ORDER BY idFile desc limit 1"; // ritorna l'ultimo inserito
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }



        // HOMEPAGE
        // totale registrate 
        public function getTotaleConferenze(){
            $query="SELECT count(Acronimo) as totale FROM CONFERENZA"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // totale attive 
        public function getTotaleConfAttive(){
            $query="SELECT count(Acronimo) as totale FROM CONFERENZA WHERE CampoSvolgimento='ATTIVA'"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // totale utenti registrati 
        public function getTotaleUtenti(){
            $query="SELECT count(Username) as totale FROM UTENTE "; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // classifica presenter 
        public function getClassificaPresenter(){
            $query="SELECT U.Username, AVG(V.Voto) as Voto, 'Presenter' as tipo FROM UTENTE as U JOIN PRESENTER as P ON P.UsernameUtente=U.Username 
                    JOIN PRESENTAZIONEDIARTICOLI as PA ON PA.UsernameUtentePresenter=P.UsernameUtente 
                    JOIN VALUTAZIONE as V ON V.CodicePresentazioneDiArticoli=PA.Codice 
                    GROUP BY U.Username 
                    ORDER BY Voto desc"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // classifica speaker 
        public function getClassificaSpeaker(){
            $query="SELECT U.Username, AVG(V.Voto) as Voto, 'Speaker' as tipo FROM UTENTE as U JOIN SPEAKER as S ON S.UsernameUtente=U.Username 
                    JOIN PRESENTAZIONE as PR ON PR.UsernameUtenteSpeaker=S.UsernameUtente JOIN TUTORIAL as T ON T.Codice=PR.CodiceTutorial 
                    JOIN VALUTAZIONE as V ON V.CodiceTutorial=T.Codice 
                    GROUP BY U.Username 
                    ORDER BY Voto desc"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }


        // conferenze disponibili
        public function getConferenzeDisponibili(){
            $query="SELECT Acronimo, Nome, AnnoEdizione FROM CONFERENZA WHERE CampoSvolgimento='ATTIVA'"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // verifica registrazione ad una conferenza
        public function getVerificaRegistrazioneConf(){
            $query="SELECT C2.Acronimo, C2.Nome, C2.AnnoEdizione 
			        FROM CONFERENZA as C2 
                    LEFT JOIN (SELECT R.AcronimoConferenza, R.AnnoEdizioneConferenza FROM registrazione as R WHERE R.UsernameUtente='". $_SESSION["username"] ."') as T 
                    ON ((T.AcronimoConferenza=C2.Acronimo) AND (T.AnnoEdizioneConferenza=C2.AnnoEdizione)) 
                    WHERE T.AcronimoConferenza IS NULL AND T.AnnoEdizioneConferenza IS NULL 
                    AND C2.CampoSvolgimento='attiva' GROUP BY Acronimo"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // effettua registrazione ad una conferenza
        public function getEffettuaRegistrazioneConf($acrConferenza, $annoEdizioneConferenza, $username){
            $query="call registrazioneConferenza(?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sis', $acrConferenza, $annoEdizioneConferenza, $username);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "RegistrazioneUtenteConf",
                'acronimoConferenza'   => $acrConferenza,
                'annoEdizioneConferenza' => $annoEdizioneConferenza,
                'username'     => $username
            ]);
        }

        // get numero di sponsorrizazioni per home
        public function getSponsorizzazioniHome(){
            $query="SELECT * FROM numerosponsorizzazioni"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // sessioni 
        public function getSessioni(){
            $query="SELECT Codice, Titolo, Link FROM SESSIONE"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // articoli
        public function getArticoli($codiceSessione){
            $query="SELECT PA.Codice, PA.OraInizio, PA.OraFine, PA.Titolo, UsernameUtentePresenter, f.NomeFile
                    FROM PRESENTAZIONEDIARTICOLI as PA 
                    JOIN SESSIONE as S 
                    ON PA.CodiceSessione=S.Codice 
                    JOIN fileesterni AS f
                    ON f.idFile=PA.FilePresentazione
                    WHERE PA.CodiceSessione=?"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('i', $codiceSessione);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // tutorial
        public function getTutorial($codiceSessione){
            $query="SELECT T.Codice, T.OraInizio, T.OraFine, T.Titolo 
                    FROM TUTORIAL as T 
                    JOIN SESSIONE as S 
                    ON T.CodiceSessione=S.Codice 
                    WHERE T.CodiceSessione=?"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('i', $codiceSessione);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }





        // prendo messaggi in una chat di sessione 
        public function getMessaggi($codiceSessione){
            $query="SELECT m.UsernameUtente, m.Testo, m.DataMessaggio FROM messaggio as m 
                    JOIN chat as c ON m.CodiceChat=c.Codice JOIN sessione as s ON c.CodiceSessione=s.Codice WHERE s.Codice=?"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('i', $codiceSessione);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }




        // ritorna codice chat
        public function getCodiceChat($codiceSessione){
            $query="SELECT c.Codice FROM chat as c JOIN sessione as s ON c.CodiceSessione=s.Codice WHERE s.Codice=?"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('i', $codiceSessione);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }


        // inserimento messaggio
        public function inserisciMessaggio($testo, $codiceChat, $username){
            $query="call inserimentoMessaggi(?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sss', $testo, $codiceChat, $username);
            $stmt->execute();
            if($codiceChat != NULL){
                $result = $this->collection->insertOne([
                  'tipoOggetto'    => "Messaggio",
                  'testo'   => $testo,
                  'codiceChat' => $codiceChat,
                  'username'     => $username
              ]);
               }
        }


        // inserimento conferenza
        public function inserisciConferenza($acronimo, $annoEdizione, $nome, $idLogo, $totSponsor, $username, $giornoProgramma){
            $query="call creazioneConferenza(?,?,?,?,?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sisiiss', $acronimo, $annoEdizione, $nome, $idLogo, $totSponsor, $username, $giornoProgramma);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "Conferenza",
                'acronimo'   => $acronimo,
                'annoEdizione' => $annoEdizione,
                'nome'     => $nome,
                'idLogo'     => $idLogo,
                'totSponsor' => $totSponsor,
                'username' => $username,
                'giornoProgramma'     => $giornoProgramma
            ]);
        }

        // ritorna tutti i programmi giornalieri
        public function getProgrammiGiornalieri(){
            $query="SELECT p.Codice, c.Nome, c.Acronimo, c.AnnoEdizione, p.DataConferenza 
            FROM programmagiornaliero AS p 
            JOIN conferenza AS c 
            ON c.Acronimo=p.AcronimoConferenza and c.AnnoEdizione=p.AnnoEdizioneConferenza"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // inserimento sessione
        public function inserisciSessione($titoloSessione, $numPresentazioni, $oraInizioSessione, $oraFineSessione, $linkSessione, $programmiSessione){
            $query="call creazioneSessione(?,?,?,?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sissss', $titoloSessione, $numPresentazioni, $oraInizioSessione, $oraFineSessione, $linkSessione, $programmiSessione);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "Sessione",
                'titoloSessione'   => $titoloSessione,
                'numPresentazioni' => $numPresentazioni,
                'oraInizioSessione'     => $oraInizioSessione,
                'oraFineSessione'     => $oraFineSessione,
                'linkSessione'     => $linkSessione,
                'programmiSessione'     => $programmiSessione
            ]);
        }

        // inserimento sponsor
        public function inserisciSponsor($titoloSponsor, $idLogo, $importoSponsor){
            $query="call inserimentoSponsor(?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sii', $titoloSponsor, $idLogo, $importoSponsor);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "Sponsor",
                'titoloSponsor'   => $titoloSponsor,
                'idLogo' => $idLogo,
                'importoSponsorr'     => $importoSponsor
             
            ]);
        }

        // inserimento sponsor
        public function getNomeSponsor(){
            $query="SELECT Nome FROM sponsor"; 
            $stmt=$this->db->prepare($query); 
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // inserimento sponsorizzazione
        public function inserisciSponsorizzazione($titolo, $anno, $nomeSponsor){
            $query="call sponsorizzazione(?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sis', $titolo, $anno, $nomeSponsor);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "Sponsorizzazione",
                'titolo'   => $titolo,
                'anno'   => $anno,
                'nomeSponsor' => $nomeSponsor
            ]);
        }

        // modifica foto presenter
        public function modificaFotoPresenter($username, $datiFoto){
            $query="call modificaFotoPresenter(?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ss', $username, $datiFoto);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "modificaFotoPresenter",
                'username'   => $username
            ]);

        }

        // modifica cv presenter
        public function modificaCVPresenter($username, $cv){
            $query="call modificaCVPresenter(?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ss', $username, $cv);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "modificaCVPresenter",
                'username'   => $username,
                'CV'   => $cv
            ]);
        }

        // modifica Universita presenter
        public function modificaUniPresenter($username, $nome, $dipa){
            $query="call modificaUniversitaPresenter(?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sss', $username, $nome, $dipa);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "modificaUniPresenter",
                'username'   => $username,
                'nome'   => $nome,
                'dipartimento'   => $dipa
            ]);
        }


        public function getFotoProfiloSpeaker($username){
            $query="Select l.NomeFile FROM speaker AS s JOIN logo AS l ON l.idLogo=s.Foto WHERE s.UsernameUtente=?"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 


        }


        public function getFotoProfiloPresenter($username){
            $query="Select l.NomeFile FROM presenter AS s JOIN logo AS l ON l.idLogo=s.Foto WHERE s.UsernameUtente=?"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }



        // modifica foto speaker
        public function modificaFotoSpeaker($username, $datiFoto){
            $query="call modificaFotoSpeaker(?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ss', $username, $datiFoto);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "modificaFotoSpeaker",
                'usernameSpeaker'   => $username
            ]);
        }

        // modifica cv speaker
        public function modificaCVspeaker($username, $cv){
            $query="call modificaCVSpeaker(?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ss', $username, $cv);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "modificaCVSpeaker",
                'username'   => $username,
                'CV'   => $cv
            ]);
        }

        // modifica Universita speaker
        public function modificaUniSpeaker($username, $nome, $dipa){
            $query="call modificaUniversitaSpeaker(?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('sss', $username, $nome, $dipa);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "modificaUniSpeaker",
                'username'   => $username,
                'nome'   => $nome,
                'dipartimento'   => $dipa
            ]);
        }

        // get speaker codici
        public function getSpeakerCodici($username){
            $query="SELECT p.CodiceTutorial 
            FROM `presentazione` AS p 
            JOIN risorsaaggiuntiva AS r 
            ON r.CodiceTutorial=p.CodiceTutorial 
            WHERE p.UsernameUtenteSpeaker=?"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }
        
        // inserisci risorsa speaker
        public function inserimentoRisorsaSpeaker($linkWeb, $desc, $codeTutorial, $username){
            $query="call inserimentoRisorsa(?,?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ssss', $linkWeb, $desc, $codeTutorial, $username);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto' => "RisorsaSpeaker",
                'linkWeb'   => $linkWeb,
                'descrizione' => $desc,
                'codeTutorial' => $codeTutorial,
                'username'     => $username
            ]);
        }

        // modifica risorsa speaker
        public function modificaRisorsaSpeaker($linkWeb, $desc, $codeTutorial, $username){
            $query="call modificaRisorsa(?,?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ssss', $linkWeb, $desc, $codeTutorial, $username);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "modificaRisorsaSpeaker",
                'linkweb'   => $linkWeb,
                'descrizione'   => $desc,
                'codiceTutorial'   => $codeTutorial,
                'username'   => $username
            ]);
        }




        // associa tutorial con speaker
        public function associaPresentazioneSpeaker($codiceTutorial, $username){
            $query="call presentazioneSpeaker(?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ss', $codiceTutorial, $username);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "associaPresentazioneSpeaker",
                'codiceTutorial'   => $codiceTutorial,
                'username'   => $username
            ]);
        }

        // associa presenter con artiocli
        public function associaPresentazionePresenter($codiceArticoli, $username){
        $query="UPDATE presentazionediarticoli
            SET UsernameUtentePresenter = ?
            WHERE Codice=?"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ss', $username, $codiceArticoli);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "associaPresentazionePresenter",
                'codiceTutorial'   => $codiceArticoli,
                'username'   => $username
            ]);
        }
        
        
        // get speaker
        public function getSpeaker(){
            $query="SELECT UsernameUtente FROM speaker"; 
            $stmt=$this->db->prepare($query);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get presenters
        public function getPresenters(){
            $query="SELECT UsernameUtente FROM presenter"; 
            $stmt=$this->db->prepare($query);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get tutorials senza speaker
        public function getTutorialsWSpeakers(){
            $query="SELECT t.Codice 
            FROM tutorial AS t 
            LEFT JOIN presentazione AS p 
            ON p.CodiceTutorial=t.Codice 
            WHERE p.CodiceTutorial IS NULL"; 
            $stmt=$this->db->prepare($query);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }
        
        // get articoli senza speaker
        public function getArticolisWPresenter(){
            $query="SELECT p.Codice 
            FROM presentazionediarticoli AS p 
            WHERE p.UsernameUtentePresenter IS NULL"; 
            $stmt=$this->db->prepare($query);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }



        // inserisci tutorial
        public function inserisciTutorial($oraInizo, $oraFine, $numSeq, $titolo, $abstract, $codSessione){
            $query="call creazioneTutorial(?,?,?,?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('ssissi', $oraInizo, $oraFine, $numSeq, $titolo, $abstract, $codSessione);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "Tutorial",
                'oraInizio'   => $oraInizo,
                'oraFine' => $oraFine,
                'numSeq' => $numSeq,
                'titolo'     => $titolo,
                'abstract'     => $abstract,
                'codSessione' => $codSessione
            ]);
        }

        // inserisci articoli
        public function inserisciArticoli($oraInizo, $oraFine, $numSeq, $titolo, $numPagine, $idPresent, $codSessione){
            $query="call creazioneArticoli(?,?,?,?,?,?,?,@a)"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('ssisiii', $oraInizo, $oraFine, $numSeq, $titolo, $numPagine, $idPresent, $codSessione);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "Articolo",
                'oraInizio'   => $oraInizo,
                'oraFine' => $oraFine,
                'numSeq' => $numSeq,
                'titolo'     => $titolo,
                'numPagine'     => $numPagine,
                'idPresenter'     => $idPresent,
                'codSessione' => $codSessione
            ]);
        }


        // get tutti gli articoli esistenti
        public function getArticoliWVote($username){
        $query="SELECT p.Codice 
                FROM presentazionediarticoli AS p 
                LEFT JOIN (SELECT v.CodicePresentazioneDiArticoli 
                            FROM valutazione AS v 
                            WHERE v.UsernameUtenteAmministratore=?) AS t 
                ON t.CodicePresentazioneDiArticoli=p.Codice 
                WHERE t.CodicePresentazioneDiArticoli IS NULL"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get tutti gli articoli esistenti
        public function getTutorialWVote($username){
        $query="SELECT p.Codice 
                FROM tutorial AS p 
                LEFT JOIN (SELECT v.CodiceTutorial 
                            FROM valutazione AS v 
                            WHERE v.UsernameUtenteAmministratore=?) AS t 
                ON t.CodiceTutorial=p.Codice 
                WHERE t.CodiceTutorial IS NULL"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }


        // inserisci valutazione Articolo
        public function inserisciValutazioneArticoli($voto, $username, $note, $codice){
            $query="call inserimentoValutazioneArticolo(?,?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('isss', $voto, $username, $note, $codice);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "ValutazioneArticoli",
                'voto'   => $voto,
                'username' => $username,
                'note'     => $note,
                'codice'     => $codice
            ]);
        }


        // inserisci valutazione Tutorial
        public function inserisciValutazioneTutorial($voto, $username, $note, $codice){
            $query="call inserimentoValutazioneTutorial(?,?,?,?,@a)"; 
            $stmt=$this->db->prepare($query); 
            $stmt->bind_param('isss', $voto, $username, $note, $codice);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "ValutazioneTutorial",
                'voto'   => $voto,
                'username' => $username,
                'note'     => $note,
                'codice'     => $codice
            ]);
        }

        // get valutazione di articolo
        public function getValutazioniArticolo($codice){
            $query="call visualizzaValutazioniArticoli(?)"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('i', $codice);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get valutazione di tutorials
        public function getValutazioniTutorial($codice){
            $query="call visualizzaValutazioniTutorial(?)"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $codice);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get articoli
        public function getTuttiArticoli(){
            $query="SELECT Codice FROM presentazionediarticoli"; 
            $stmt=$this->db->prepare($query);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get tutorial
        public function getTuttiTutorial(){
            $query="SELECT Codice FROM tutorial"; 
            $stmt=$this->db->prepare($query);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get not favourite Articoli
        public function getNotArticoliFavoriti($username){
            $query="SELECT p.Titolo 
                    FROM presentazionediarticoli AS p 
                    LEFT JOIN (Select TitoloPresentazione 
                                FROM presentazionefavorita 
                                WHERE UsernameUtente=?) AS t ON t.TitoloPresentazione=p.Titolo 
                    WHERE t.TitoloPresentazione IS NULL"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get not favourite Tutorial
        public function getNotTutorialFavoriti($username){
            $query="SELECT p.Titolo 
                    FROM tutorial AS p 
                    LEFT JOIN (Select TitoloPresentazione 
                                FROM presentazionefavorita 
                                WHERE UsernameUtente=?) AS t ON t.TitoloPresentazione=p.Titolo 
                    WHERE t.TitoloPresentazione IS NULL"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get favourite Artiocli
        public function getArticoliTutorialFavoriti($username){
            $query="Select TitoloPresentazione FROM presentazionefavorita WHERE UsernameUtente=?"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        // get inserisci Favourite Presentazioni
        public function inserisciPresentazioneFavorita($username, $titolo){
            $query="INSERT INTO presentazionefavorita (TitoloPresentazione, UsernameUtente)
            VALUES (?, ?);"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('ss', $titolo, $username);
            $stmt->execute();
            $result = $this->collection->insertOne([
                'tipoOggetto'    => "PresentazioneFavorita",
                'username'   => $username,
                'password' => $titolo
            ]);
        }

        public function getInfoUtente($username){
            $query="SELECT Username, Nome, Cognome, DataNascita, LuogoNascita FROM Utente WHERE Username=?"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }


        public function getFotoPerSponsor($acronimo, $anno){
            $query="SELECT s.Nome, s.Importo, l.NomeFile
            FROM Logo AS l
            JOIN sponsor AS s
            ON s.Logo=l.idLogo
            JOIN disposizione AS d
            ON d.NomeSponsor=s.Nome
            WHERE d.AcronimoConferenza = ? AND d.AnnoEdizioneConferenza = ?"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('ss', $acronimo, $anno);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        public function getInfoPresenter($username){
            $query="SELECT Cv, Nome, Dipartimento FROM presenter WHERE UsernameUtente=?"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

        public function getInfoSpeaker($username){
            $query="SELECT Cv, Nome, Dipartimento FROM speaker WHERE UsernameUtente=?"; 
            $stmt=$this->db->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result=$stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC); 
        }

    }
?>

