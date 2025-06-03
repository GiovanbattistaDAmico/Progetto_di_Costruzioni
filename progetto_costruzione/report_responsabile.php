<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();
    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);

    //Select per lo stato e il numero totale di attivita per stato
    $stato = [];
    $n_stato = [];
    $sql_attivita = "SELECT stato,COUNT(*) AS totale FROM attivita WHERE id_responsabile = ? GROUP BY stato";
    $stmt_attivita = $conn->prepare($sql_attivita);
    $stmt_attivita -> bind_param("i",$_SESSION['id_utente']);
    $stmt_attivita ->execute();
    $result = $stmt_attivita->get_result();
    while($row = $result -> fetch_assoc()){
        $stato[] = $row['stato'];
        $n_stato[] = $row['totale'];
    }

    //Select per operai e compiti assegnati agli operai
    $utenti=[];
    $compitiPerUtenti=[];
    $sql_compiti = "SELECT c.id_operaio,u.id_utente,u.nome,u.cognome,a.id_attivita,a.id_responsabile,
    COUNT(*) AS totale FROM compiti AS c JOIN utenti AS u ON c.id_operaio = u.id_utente JOIN attivita AS a ON a.id_attivita=c.id_attivita
    WHERE a.id_responsabile = ?";
    $stmt_compiti = $conn->prepare($sql_compiti);
    $stmt_compiti -> bind_param("i",$_SESSION['id_utente']);
    $stmt_compiti ->execute();
    $result = $stmt_compiti->get_result();
    while($row = $result -> fetch_assoc()){
        $utenti[] = $row['nome']." ".$row['cognome'];
        $compitiPerUtenti[] = $row['totale'];
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
                <h1 class="titolo">Report</h1>
                <div class="div_button">
                <button onclick="window.location.href='<?php echo $menu; ?>'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>
            <!--Sezione dei grafici-->
            <div class="grafici_responsabile">
                <div>
                    <h2>Numero di Attività per Stato</h2>
                    <canvas id="graficoAttivita" class="graficoAttivita"></canvas>
                </div>
                <div>
                    <h2>Compiti assegnati agli Operai</h2>
                    <canvas id="compitiPerUtente" class="compitiPerUtente"></canvas> 
                </div>    
            </div>
    </body>
    <script>
        //Grafico a barre per le attività e il loro stato
        let barColors = ['#0B3C5D', '#1D65A6', '#3C8DAD','#7FC8F8'];
        new Chart("graficoAttivita", {
            type: "pie",
            data: {
                labels: <?php echo json_encode($stato); ?>,
                datasets: [{
                backgroundColor: barColors,
                borderColor: "black",
                data: <?php echo json_encode($n_stato); ?>
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

        //Grafico a barre per gli operai e i compiti per operaio
        new Chart("compitiPerUtente", {
            type: "bar",
            data: {
                labels: <?php echo json_encode($utenti); ?>,
                datasets: [{
                label:"Compiti Assegnati",
                backgroundColor:  ['#0B3C5D', '#1D65A6', '#3C8DAD','#7FC8F8','#D6F6FF'],
                borderColor: "black",
                borderWidth:2,
                data: <?php echo json_encode($compitiPerUtenti); ?>
                }],
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