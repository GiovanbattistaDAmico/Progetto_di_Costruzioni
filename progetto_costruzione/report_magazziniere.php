<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin(); 

    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);
    $ruolo = $_SESSION['ruolo'];
    $azienda_id = $_SESSION['id_azienda']; 
    $id_utente = $_SESSION['id_utente'];
    $link = $_SERVER['REQUEST_URI'];

    //Selezione del tipo e numero di movimento dei materiali
    $tipo=[];
    $quantita=[];
    $sql_tipo = "SELECT tipo,COUNT(*) AS totale FROM movimenti_materiali WHERE id_destinatario=? OR id_mittente=? GROUP BY tipo";
    $stmt_tipo = $conn->prepare($sql_tipo);
    $stmt_tipo -> bind_param("ii",$_SESSION['id_utente'],$_SESSION['id_utente']);
    $stmt_tipo ->execute();
    $result = $stmt_tipo->get_result();
    while($row = $result->fetch_assoc()){
        $tipo[] = $row['tipo'];
        $quantita[] = $row['totale'];
    }

    //Selezione utenti e movimento per utenti
    $utenti = [];
    $movimentoPerUtente = [];
    $sql_utenti = "SELECT u.nome,u.cognome,u.id_utente,COUNT(*) AS totale FROM movimenti_materiali AS m JOIN utenti AS u
    ON m.id_mittente = u.id_utente WHERE m.id_destinatario = ? GROUP BY m.id_destinatario";
    $stmt_utenti = $conn->prepare($sql_utenti);
    $stmt_utenti->bind_param("i", $_SESSION['id_utente']);
    $stmt_utenti->execute();
    $result = $stmt_utenti->get_result();
    while ($row = $result->fetch_assoc()) {
        $utenti[] = $row['nome'] . " " . $row['cognome'];
        $movimentoPerUtente[] = $row['totale'];
    }

    //Selezione materiali e numero di movimenti per materiale
    $materiali=[];
    $richieste_materiali=[];
    $sql_materiali = "SELECT mat.nome,mat.id,COUNT(*) AS totale FROM movimenti_materiali AS m JOIN materiali_attrezzature
    AS mat ON m.id_risorsa = mat.id WHERE id_destinatario = ? OR id_mittente = ? GROUP BY m.id_risorsa ORDER BY totale DESC" ;
    $stmt_materiali = $conn->prepare($sql_materiali);
    $stmt_materiali->bind_param("ii", $_SESSION['id_utente'],$_SESSION['id_utente']);
    $stmt_materiali->execute();
    $result = $stmt_materiali->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $materiali[] = $row['nome'];
        $richieste_materiali[] = $row['totale'];
    }
    $conn->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include 'gestioneCSS.php'?>
        <?php include 'materialiCSS.php'?>
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
                <h1 class="titolo">Report Magazziniere</h1>
                <div class="div_button">
                <button onclick="window.location.href='<?php echo $menu; ?>'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <!--Sezione dei grafici-->
            <div class="grafici">
                <div>
                    <h2>Tipi di Movimenti Materiali/Attrezzature Registrate</h2>
                    <canvas id="tipoMovimento" class="tipoMovimento"></canvas>
                </div>
                <div>
                    <h2>Movimenti per Utente</h2>
                    <canvas id="movimentoMatPerUtenti" class="movimentoMatPerUtenti"></canvas> 
                </div>
                <div>
                    <h2>Movimenti per Materiale</h2>
                    <canvas id="movimentoPerMateriale" class="movimentoPerMateriale"></canvas>
                </div>
            </div>
    </body>
        <script>
            //Grafico a barre per il tipo di movimento
            let barColors = ['#0B3C5D', '#1D65A6', '#3C8DAD','#7FC8F8'];  
            new Chart("tipoMovimento", {
                type: "pie",
                data: {
                    labels: <?php echo json_encode($tipo); ?>,
                    datasets: [{
                    backgroundColor: barColors,
                    borderColor: "black",
                    data: <?php echo json_encode($quantita); ?>
                    }]
                },
                options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false 
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });
        //Grafico a barre per il movimento per utenti
            new Chart("movimentoMatPerUtenti", {
                type: "bar",
                data: {
                    labels: <?php echo json_encode($utenti); ?>,
                    datasets: [{
                    label:'Movimenti per Utente',
                    backgroundColor: barColors,
                    borderColor: "black",
                    borderWidth:2,
                    data: <?php echo json_encode($movimentoPerUtente); ?>
                    }]
                },
                options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false 
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });
        //Grafico a barre per il movimento dei materiali
        new Chart("movimentoPerMateriale", {
                type: "bar",
                data: {
                    labels: <?php echo json_encode($materiali); ?>,
                    datasets: [{
                    label:'Movimenti per Materiale',
                    backgroundColor: barColors,
                    borderColor: "black",
                    borderWidth:2,
                    data: <?php echo json_encode($richieste_materiali); ?>
                    }]
                },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false 
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });
    </script>
</html>