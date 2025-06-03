<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();

    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);

    //Recupero dei dipendenti e del numero
    $utenti = [];
    $numero_utenti=[];
    $sql_ruoli = "SELECT ruolo, COUNT(*) AS totale FROM utenti WHERE ruolo !='Amministratore Aziendale' AND id_azienda = ? 
     GROUP BY ruolo";
    $stmt_ruoli = $conn->prepare($sql_ruoli);
    $stmt_ruoli ->bind_param("i",$_SESSION['id_azienda']);
    $stmt_ruoli -> execute();
    $result = $stmt_ruoli->get_result();
    while($row = $result ->fetch_assoc()){
        $utenti[] = $row['ruolo'];
        $numero_utenti[] = $row['totale'];
    }

    //recuperto dei progetti attivita e compiti con il numero totale
    $progetti = [];
    $attivita = [];
    $compiti = [];
    $sql_progetti = "SELECT p.nome_progetto, COUNT(a.id_attivita) AS attivita, COUNT(c.id_compito) AS compito FROM progetti AS p 
    LEFT JOIN attivita AS a ON p.id_progetto = a.id_progetto LEFT JOIN compiti AS c ON c.id_attivita = a.id_attivita WHERE 
    id_azienda = ? GROUP BY p.id_progetto";
    $stmt_progetti = $conn->prepare($sql_progetti);
    $stmt_progetti -> bind_param("i",$_SESSION['id_azienda']);
    if($stmt_progetti->execute()){
        $result = $stmt_progetti->get_result();
        while($row = $result->fetch_assoc()){
            $progetti[] = $row['nome_progetto'];
            $attivita[] = $row['attivita'];
            $compiti[] = $row['compito'];
        }
    }

    //Recuperto dello stato e numero dei progetti
    $stato=[];
    $quantita=[];
    $sql_stato = "SELECT stato, COUNT(*) AS totale FROM progetti WHERE id_azienda =? GROUP BY stato";
    $stmt_stato = $conn -> prepare($sql_stato);
    $stmt_stato -> bind_param("i",$_SESSION['id_azienda']);
    $stmt_stato ->execute();
    $result = $stmt_stato->get_result();
    while($row = $result ->fetch_assoc()){
        $stato[] = $row['stato'];
        $quantita[] = $row['totale'];
    }

    // Recupera progetti in scadenza entro 7 giorni
    $progetti_scadenza = [];
    $sql_scadenza = "SELECT id_progetto, nome_progetto, data_scadenza 
            FROM progetti 
            WHERE id_azienda = ? AND data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY data_scadenza ASC";
    $stmt_scadenza = $conn->prepare($sql_scadenza);
    $stmt_scadenza->bind_param("i", $_SESSION['id_azienda']);
    $stmt_scadenza->execute();
    $result = $stmt_scadenza->get_result();
    while ($row = $result->fetch_assoc()) {
        $progetti_scadenza[] = $row;
    }
    $stmt_scadenza->close();
    $ritardo = [];

    // Query per i dettagli dei progetti con ritardo (positivo o negativo)
    $sql_ritardo = "SELECT p.nome_progetto, p.data_scadenza, p.data_fine_effettiva,DATEDIFF(p.data_fine_effettiva, p.data_scadenza) AS giorni_ritardo
    FROM progetti AS p WHERE p.stato = 'Completato' AND p.data_fine_effettiva IS NOT NULL AND p.id_azienda = ?";

    $stmt_ritardo = $conn->prepare($sql_ritardo);
    $stmt_ritardo->bind_param("i", $_SESSION['id_azienda']);
    $stmt_ritardo->execute();
    $result_ritardo = $stmt_ritardo->get_result();

    while($row = $result_ritardo->fetch_assoc()){
        $ritardo[] = $row;
    }

    // Query per la media generale (con ritardi positivi e negativi)
    $sql_media = "SELECT ROUND(AVG(DATEDIFF(data_fine_effettiva, data_scadenza)), 2) AS media_ritardo FROM progetti
    WHERE stato = 'Completato' AND data_fine_effettiva IS NOT NULL AND id_azienda = ?";

    $stmt_media = $conn->prepare($sql_media);
    $stmt_media->bind_param("i", $_SESSION['id_azienda']);
    $stmt_media->execute();
    $result_media = $stmt_media->get_result();
    $row_media = $result_media->fetch_assoc();
    $media_ritardo = $row_media['media_ritardo'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'reportCSS.php'?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <title>Report</title>
    </head>
    <body>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
        <!--Intestazione con logo-->
            <div class="intestazione">
                <video class="logo" autoplay muted>
                    <source src="edil_planner.mp4" type="video/mp4">
                </video> 
                <h1 class="titolo">Report</h1>
                <div class="div_button">
                <button onclick="window.location.href='<?php echo $menu; ?>'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>

            <!--Sezione dei grafici-->
            <div class="grafici">
                <div>
                    <h2>Tipi di Dipendenti dell'Azienda</h2>
                    <canvas id="graficoUtenti" class="graficoUtenti"></canvas>                                
                </div>
                <div>
                    <h2>Attività e Compiti per Progetto</h2>
                    <canvas id="graficoProgetti" class="graficoProgetti"></canvas> 
                </div>
                <div>
                    <h2>Numero Progetti per Stato</h2>
                    <canvas id="graficoStatoProgetti" class="graficoStatoProgetti"></canvas>
                </div>
                </div>

            <!--Sezione dei progetti in scadenza-->
            <div class="scadenze">
            <div>
                <h2>📅 Progetti in Scadenza (entro 7 giorni)</h2>
                </div>
            <div>
            <?php if (count($progetti_scadenza) > 0): ?>
                <table>
                    <tr>
                        <th>Nome Progetto</th>
                        <th>Data Fine</th>
                        <th>Azioni</th>
                    </tr>
                    <?php foreach ($progetti_scadenza as $progetto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($progetto['nome_progetto']); ?></td>
                            <td><?php echo $progetto['data_scadenza']; ?></td>
                            <td>
                                <a href="gestione_progetti.php">Dettagli</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>Nessun progetto in scadenza nei prossimi 7 giorni.</p>
            <?php endif; ?>
            </div>

            <!--Sezione dei progetti e media dei ritardi-->
            <div class="media_ritardo">
                <div>
                    <h2>Media dei Giorni di Ritardo dei Progetti</h2>
            </div>
            <div>
                    <table class="ritardi">
                        <tr>
                        <th>Nome Progetto</th>
                        <th>Data Scadenza</th>
                        <th>Data Fine Effettiva</th>
                        <th>Giorni Ritardo</th>
                        <th class="media-generale">Media Generale<br></th>
                        </tr>
                    
                        <?php foreach ($ritardo as $p): ?>
                        <tr>
                            <td><?= $p['nome_progetto'] ?></td>
                            <td><?= $p['data_scadenza'] ?></td>
                            <td><?= $p['data_fine_effettiva'] ?></td>
                            <td><?= $p['giorni_ritardo'] ?></td>
                            <td><?php echo $media_ritardo; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>   
            </div>   
            </body>
        <script>
        //Codici per i vari grafici
        //Grafico a torta per gli utenti
        let barColors = ['#0B3C5D', '#1D65A6', '#3C8DAD','#7FC8F8'];
        new Chart("graficoUtenti", {
            type: "pie",
            data: {
                labels: <?php echo json_encode($utenti); ?>,
                datasets: [{
                backgroundColor: barColors,
                borderColor: "black",
                data: <?php echo json_encode($numero_utenti); ?>
                }]
            },
            options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false 
                }
            }
        }
    });
    //Grafico a barre per progetti con numero di attivita e compiti
        new Chart("graficoProgetti", {
            type: "bar",
            data: {
                labels: <?php echo json_encode($progetti); ?>,
                datasets: [{
                label:"Attività",
                backgroundColor: "#0B3C5D",
                borderColor: "black",
                borderWidth:2,
                data: <?php echo json_encode($attivita); ?>
                },
                {
                label:"Compiti",
                backgroundColor: "#3C8DAD",
                borderColor: "black",
                borderWidth:2,
                data: <?php echo json_encode($compiti); ?>
                }]
            },
            options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false 
                }
            }
        }
    });
    //Grafico a ciambella per stato dei progetti
    new Chart("graficoStatoProgetti", {
            type: "doughnut",
            data: {
                labels: <?php echo json_encode($stato); ?>,
                datasets: [{
                    label:"Stato dei Progetti",
                    backgroundColor: ['#0B3C5D', '#1D65A6', '#3C8DAD','#7FC8F8','#D6F6FF'],
                    borderColor: "black",
                    borderWidth:2,
                    data: <?php echo json_encode($quantita); ?>
                }],
            },
            options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false 
                }
            }
        }
    });
    let colori = <?php echo json_encode($media_ritardo); ?>.map(valore => {
        if (valore <= 2) return 'green';
        if (valore <= 5) return 'yellow';
        if (valore <= 10) return 'orange';
        return 'red'; // oltre 10 giorni è rosso
    });
    </script>
</html>