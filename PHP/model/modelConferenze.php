<?php
  require_once("../connessione.php"); // controlla se il file è già messo dentro (se è gia stato messo non lo fa)

define("TITLE", "Home Page");
include('layouts/header.php');
?>

<?php
  // verifico che il pulsante venga cliccato
  if(isset($_POST["RegistrazioneConf"])){
    $componenti=explode("_", $_POST['selectRegistrazioneConf']); // dividiamo gli elementi che abbiamo diviso con l'underscore (1° elemento: acronimo, 2° elemento: anno)

    $acronimoConferenza=$componenti[0];
    $annoConferenza=$componenti[1];

    
    $dbh->getEffettuaRegistrazioneConf($acronimoConferenza, $annoConferenza, $_SESSION["username"]);
  }

  ?>

<?php
  $conferenzeDisponibili = $dbh->getConferenzeDisponibili();
  $verificaRegistrazioneConf = $dbh->getVerificaRegistrazioneConf();
  
?>


<div class="position-relative overflow-hidden p-1 p-md-1 m-md-1 text-center bg-light">
  <div class="col-md-5 p-lg-5 mx-auto my-1">
    <h1 class="display-4 fw-normal">CONFERENZE</h1>
  </div>
  
  </div>

  <div class="m-5">
  <b><p class="col-md-8 fs-5">CONFERENZE DISPONIBILI: </p></b>
    <?php foreach($conferenzeDisponibili as $conf): ?>
      <?php $sponsors = $dbh->getFotoPerSponsor($conf['Acronimo'], $conf['AnnoEdizione']) ?>
      <li><?php echo "ACRONIMO: " ."<b>".$conf['Acronimo'] ."</b>". " - NOME: "."<b>" . $conf['Nome'] ."</b>" . " - ANNO: "."<b>" . $conf['AnnoEdizione'] ."</b>" ;?> 
        <ul>
          <?php foreach($sponsors as $sponsor): ?>
            <li><?php echo("SPONSOR: <b>". $sponsor["Nome"] ."</b> - IMPORTO: <b>". $sponsor["Importo"] ."</b> - Logo: "); echo("<img src='.".DIRECTORY_SEPARATOR."risorse".DIRECTORY_SEPARATOR."immagini".DIRECTORY_SEPARATOR.$sponsor["NomeFile"]."' width='80' height='50'");  ?></li>
          <?php endforeach; ?>  
          </ul>
      </li>
    <?php endforeach; ?>     
  </div>


  <div class="m-5">
  <b><p class="col-md-8 fs-5">REGISTRATI AD UNA CONFERENZA: </p></b>
  
  <form name="formRegistrazioneConf" method="post"> 
    <select name="selectRegistrazioneConf">
        <?php 
        foreach($verificaRegistrazioneConf as $verificata):
          echo "<option value='".$verificata['Acronimo']. "_". $verificata['AnnoEdizione']."'>".$verificata['Acronimo']. "_". $verificata['AnnoEdizione']."</option>";
        endforeach;
        ?>
      </select>
      <input type="submit" id="RegistrazioneConf" name="RegistrazioneConf" value="REGISTRATI" onclick="window.location.reload()"></input>

  </form>
  </div>

  <?php
    if(isset($_POST["RegistrazioneConf"])){
      echo("<p>Registrazione eseguita con successo! Ti sei registrato alla conferenza: ".$acronimoConferenza . $annoConferenza ."</p>");
    }
  ?>

<?php
include('layouts/footer.php');
?>