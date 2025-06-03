<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();

    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    $ruolo = $_SESSION['ruolo'];
    $id_azienda = $_SESSION['id_azienda']; 
    $id_utente = $_SESSION['id_utente'];
    $link = $_SERVER['REQUEST_URI'];

    //Eliminazione delle notifiche riguardanti la pagina 
    $attivita=[];
    rimuoviNotifiche($conn,$id_utente,$link);

    //Select per L'Amministratore Aziendale per vedere tutte le attività dell'azienda
    if($_SESSION['tipo_utente']=='Azienda'){
        $sql_attivita_azienda="SELECT a.*,u.id_utente,u.nome AS nome_responsabile,u.cognome AS cognome_responsabile,pr.id_progetto,
        pr.nome_progetto FROM attivita AS a JOIN utenti AS u ON a.id_responsabile = u.id_utente JOIN progetti AS pr ON 
        a.id_progetto = pr.id_progetto WHERE pr.id_azienda=?";
        $stmt_attivita_azienda=$conn->prepare($sql_attivita_azienda);
        $stmt_attivita_azienda->bind_param("i",$id_azienda);
        $stmt_attivita_azienda->execute();
        $result=$stmt_attivita_azienda->get_result();
        while($row = $result->fetch_assoc()){
            $attivita[]=$row;
        }
        //Se l'utente è o Responsabile
    }elseif($_SESSION['ruolo']=='Responsabile'){
        $sql_attivita_professionista="SELECT a.*,u.id_utente,u.nome,u.cognome,pr.id_progetto,
        pr.nome_progetto FROM attivita AS a JOIN progetti AS pr ON 
        a.id_progetto = pr.id_progetto JOIN utenti AS u ON a.id_responsabile = u.id_utente WHERE a.id_responsabile=?";
        $stmt_attivita_professionista=$conn->prepare($sql_attivita_professionista);
        $stmt_attivita_professionista->bind_param("i",$id_utente);
        $stmt_attivita_professionista->execute();
        $result=$stmt_attivita_professionista->get_result();
        while($row = $result->fetch_assoc()){
            $attivita[]=$row;
        }
    }

    $attivita_responsabili=[];
    //Selezione per il Responsabile per le attività che ha creato
    $sql_attivita_responsabile="SELECT a.*,u.id_utente,u.nome AS nome_responsabile,u.cognome AS cognome_responsabile,pr.id_progetto,
        pr.nome_progetto FROM attivita AS a JOIN utenti AS u ON a.id_responsabile = u.id_utente JOIN progetti AS pr ON 
        a.id_progetto = pr.id_progetto WHERE pr.id_responsabile=?";
        $stmt_attivita_responsabile=$conn->prepare($sql_attivita_responsabile);
        $stmt_attivita_responsabile->bind_param("i",$id_utente);
        $stmt_attivita_responsabile->execute();
        $result=$stmt_attivita_responsabile->get_result();
        while($row = $result->fetch_assoc()){
            $attivita_responsabili[]=$row;
        }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'progettiCSS.php'?>
        <title>Gestione Progetti</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <!--Intestazione con logo del sito-->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Gestione Attività</h1>
                <div class="div_button">
                <button onclick="window.location.href='<?php echo $menu; ?>'" class="back">
                    <i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <!--Parte dell'Amministratore Aziendale e del Libero Professionista-->
            <div class="button">
                <div class="button_change">
                <?php if($_SESSION['tipo_utente']=='Azienda'){?>
                    <button onclick="mostraSezione('corso')" class="add_button1">In Corso</button>
                    <button onclick="mostraSezione('attesa')" class="add_button1">Sospese</button>
                    <button onclick="mostraSezione('concluse')" class="add_button1">Concluse</button>
                    <?php } ?> <?php if($_SESSION['ruolo']=='Responsabile'){?>
                    <button onclick="mostraSezioneResponsabile('create')" class="add_button1">Attività Progetto</button>
                    <button onclick="mostraSezioneResponsabile('assegnate')" class="add_button1">Le mie Attività </button>
                   <?php } ?>
                </div>
                <?php if( $_SESSION['ruolo']=='Responsabile'){?>
                <div class="azioni">
                <button class="add_button2" onclick='location.href="modifica_attivita.php"'>Modifica Attività</button>
                <button class="add_button3" onclick='location.href="crea_attivita.php"'>Crea Attività</button>
                <?php } ?></div>
            </div>
            <!--Elenco delle Attivita in Corso -->
            <?php if($_SESSION['tipo_utente']=='Azienda'){?>
            <div class="lista_attivita" id="corso">
                <h1>Elenco Attività In Corso</h1>
                <?php 
                $trovate = false;
                foreach($attivita as $a):
                    if($a['stato'] == 'In Corso'):
                        if(!$trovate): $trovate = true; ?>
                        <table>
                            <tr>
                                <th>Nome Progetto</th>
                                <th>Nome Attività</th>
                                <th>Descrizione</th>
                                <?php if($_SESSION['ruolo']!='Responsabile'){ ?><th>Responsabile</th><?php }?>
                            </tr>
                    <?php endif; ?>
                        <tr>
                            <td><?php echo $a['nome_progetto']; ?></td>
                            <td><?php echo $a['nome_attivita']; ?></td>
                            <td><?php echo $a['descrizione']; ?></td>
                            <?php if($_SESSION['ruolo']!='Responsabile'){ ?>
                                <td><?php echo $a['nome_responsabile']." " .$a['cognome_responsabile']; ?></td><?php }?>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if($trovate): ?>
                    </table>
                <?php else: ?>
                    <p><strong>Nessuna attività attualmente in corso.</strong></p>
                <?php endif; ?>
            </div>
            <!--Elenco delle Attivita Sospese -->
            <div class="lista_attivita" id="attesa" style="display:none">
                <h1>Elenco Attività Sospese</h1>
                <?php 
                $trovate = false;
                foreach($attivita as $s):
                    if($s['stato'] == 'Sospesa'):
                        if(!$trovate): $trovate = true; ?>
                        <table>
                            <tr>
                                <th>Nome Progetto</th>
                                <th>Nome Attività</th>
                                <th>Descrizione</th>
                                <?php if($_SESSION['tipo_utente']!='Libero Professionista'){ ?>
                                    <th>Responsabile</th> <?php } ?>
                                <th>Motivo Sospensione</th>
                            </tr>
                    <?php endif; ?>
                        <tr>
                            <td><?php echo $s['nome_progetto']; ?></td>
                            <td><?php echo $s['nome_attivita']; ?></td>
                            <td><?php echo $s['descrizione']; ?></td>
                            <?php if($_SESSION['tipo_utente']!='Libero Professionista'){ ?>
                            <td><?php echo $s['nome_responsabile']." " .$s['cognome_responsabile']; ?></td><?php } ?>
                            <td><?php echo $s['motivo_sospensione']; ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if($trovate): ?>
                    </table>
                <?php else: ?>
                    <p><strong>Nessuna attività attualmente sospesa.</strong></p>
                <?php endif; ?>
            </div>
            <!--Elenco delle Attivita Concluse -->
            <div class="lista_attivita" id="concluse" style="display:none">
                <h1>Elenco Attività Completate</h1>
                <?php 
                $trovate = false;
                foreach($attivita as $c):
                    if($c['stato'] == 'Conclusa'):
                        if(!$trovate): $trovate = true; ?>
                        <table>
                            <tr>
                                <th>Nome Progetto</th>
                                <th>Nome Attività</th>
                                <th>Descrizione</th>
                                <?php if($_SESSION['tipo_utente']!='Libero Professionista'){ ?>
                                <th>Responsabile</th><?php } ?>
                                <th>Costo Effettivo</th>
                            </tr>
                    <?php endif; ?>
                        <tr>
                            <td><?php echo $c['nome_progetto']; ?></td>
                            <td><?php echo $c['nome_attivita']; ?></td>
                            <td><?php echo $c['descrizione']; ?></td>
                            <?php if($_SESSION['tipo_utente']!='Libero Professionista'){ ?>
                            <td><?php echo $c['nome_responsabile']." " .$c['cognome_responsabile']; ?></td><?php } ?>
                            <td><?php echo $c['costo_effettivo']; ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if($trovate): ?>
                    </table>
                <?php else: ?>
                    <p><strong>Nessuna attività attualmente completata.</strong></p>
                <?php endif; ?>
            </div>
        <!--Funzione javascript per mostrare le diverse sezioni del menu in base al tasto cliccato -->
        <script>
            function mostraSezione(section){
                if(section ==='corso'){
                    document.getElementById('corso').style.display = 'block';
                    document.getElementById('attesa').style.display = 'none';
                    document.getElementById('concluse').style.display = 'none';
                }else if(section=='attesa'){
                    document.getElementById('corso').style.display = 'none';
                    document.getElementById('attesa').style.display = 'block';
                    document.getElementById('concluse').style.display = 'none';
                }else{
                    document.getElementById('corso').style.display = 'none';
                    document.getElementById('attesa').style.display = 'none';
                    document.getElementById('concluse').style.display = 'block';
                }
            } 
        </script>
        <?php }?>
        <!--Parte del Responsabile-->
        <?php if($_SESSION['ruolo'] == 'Responsabile'){ ?>
        <div class="lista_attivita" id="create">
            <!--Elenco attività del Responsabile del progetto a lui assegnato-->
            <h1>Attività dei Progetti che Gestisco</h1>
            <?php 
            $trovate = false;
            foreach($attivita_responsabili as $a):
                if(!$trovate): $trovate = true; ?>
                    <table>
                        <tr>
                            <th>Nome Progetto</th>
                            <th>Nome Attività</th>
                            <th>Descrizione</th>
                            <th>Responsabile</th>
                            <th>Stato</th>
                            <th>Costo Effettivo</th>
                        </tr>
            <?php endif; ?>
                    <tr>
                        <td><?php echo $a['nome_progetto']; ?></td>
                        <td><?php echo $a['nome_attivita']; ?></td>
                        <td><?php echo $a['descrizione']; ?></td>
                        <td><?php echo $a['nome_responsabile']." ".$a['cognome_responsabile']; ?></td>
                        <td><?php echo $a['stato']; ?></td>
                        <td><?php echo $a['costo_effettivo']; ?></td>
                    </tr>
            <?php endforeach; ?>
            <?php if($trovate): ?>
                </table>
            <?php else: ?>
                <p><strong>Nessuna attività trovata.</strong></p>
            <?php endif; ?>
        </div>
    <?php } ?>



    <div class="lista_attivita" id="assegnate" style="display:none">
        <!-- Elenco attività assegnate al responsabile da altri responsabili di progetto-->
    <h1>Elenco Attività Assegnate</h1>
    <?php if (!empty($attivita)): ?>
        <?php
            // Controllo se almeno un'attività è sospesa
            $mostra_motivo = false;
            foreach ($attivita as $s) {
                if ($s['stato'] == 'Sospesa') {
                    $mostra_motivo = true;
                    break;
                }
            }
        ?>
        <table>
            <tr>
                <th>Nome Progetto</th>
                <th>Nome Attività</th>
                <th>Descrizione</th>
                <th>Costo Effettivo</th>
                <th>Stato</th>
                <?php if ($mostra_motivo): ?>
                    <th>Motivo Sospensione</th>
                <?php endif; ?>
            </tr>
            <?php foreach($attivita as $s): ?>
                <tr>
                    <td><?php echo $s['nome_progetto']; ?></td>
                    <td><?php echo $s['nome_attivita']; ?></td>
                    <td><?php echo $s['descrizione']; ?></td>
                    <td><?php echo $s['costo_effettivo']; ?></td>
                    <td><?php echo $s['stato']; ?></td>
                    <?php if ($mostra_motivo): ?>
                        <td><?php echo ($s['stato'] == 'Sospesa') ? $s['motivo_sospensione'] : '-'; ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p><strong>Nessuna attività assegnata al momento.</strong></p>
        <?php endif; ?>
    </div>

        <!--Funzione javascript per mostrare le diverse sezioni del menu in base al tasto cliccato -->
        <script>
            function mostraSezioneResponsabile(section){
                if(section ==='create'){
                    document.getElementById('create').style.display = 'block';
                    document.getElementById('assegnate').style.display = 'none';
                }else if(section=='assegnate'){
                    document.getElementById('create').style.display = 'none';
                    document.getElementById('assegnate').style.display = 'block';
                }
            } 
        </script>
    </body>
</html>