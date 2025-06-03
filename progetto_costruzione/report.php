<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();
    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);

    //Select del tipo di utenti e numero per tipo degli utenti
    $utenti = [];
    $numero_utenti=[];
    $sql_ruoli = "SELECT tipo_utente, COUNT(*) AS totale FROM utenti WHERE tipo_utente IS NOT NULL AND tipo_utente != 
    'Admin' GROUP BY tipo_utente";
    $result = $conn->query($sql_ruoli);
    while($row = $result ->fetch_assoc()){
        $utenti[] = $row['tipo_utente'];
        $numero_utenti[] = $row['totale'];
    }

    //Select per mesi e registrazioni degli utenti
    $mesi=[];
    $registrazioni=[];
    $sql_reg="SELECT DATE_FORMAT(data_registrazione, '%Y-%m') AS mese, COUNT(*) AS totale 
    FROM utenti WHERE data_registrazione IS NOT NULL GROUP BY mese ORDER BY mese ASC";
    $result = $conn->query($sql_reg);
    while($row1 = $result->fetch_assoc()){
        $mesi[]=$row1['mese'];
        $registrazioni[]=$row1['totale'];
    }

    //Select per lo stato e il numero di progetti per stato delle varie Aziende
    $stato=[];
    $nProgetti=[];
    $sql_progetti = "SELECT stato, COUNT(*) AS totale FROM progetti GROUP BY stato";
    $result = $conn->query($sql_progetti);
    while($row2 = $result->fetch_assoc()){
        $stato[]=$row2['stato'];
        $nProgetti[]=$row2['totale'];
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
            <!--Sezione Grafici-->
            <div class="grafici">
                <div>
                    <h2>Tipi di Utenti Registrati</h2>
                    <canvas id="graficoUtenti" class="graficoUtenti"></canvas>
                </div>
                <div>
                    <h2>Registrazioni Utenti per mese</h2>
                    <canvas id="graficoLinee" class="graficoLinee"></canvas>
                </div>   
                <div>
                    <h2>Progetti nei Vari Stati</h2>
                    <canvas id="graficoProgetti" class="graficoProgetti"></canvas>   
                </div>         
            </div>
            
    </body>
        <script>
            //Grafico a torta per utenti e numero di utenti per tipo
            let pieColors = ['#1D65A6', '#3C8DAD','#7FC8F8'];
            new Chart("graficoUtenti", {
                type: "pie",
                data: {
                    labels: <?php echo json_encode($utenti); ?>,
                    datasets: [{
                    backgroundColor: pieColors,
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

        //Grafico a linea per le registrazioni mensili degli utenti
            new Chart("graficoLinee", {
            type: "line",
            data: {
                labels: <?php echo json_encode($mesi); ?>,
                datasets: [{
                    label: "Registrazioni utenti",
                    data: <?php echo json_encode($registrazioni); ?>,
                    borderColor: "#0B3C5D",
                    backgroundColor: "#7FC8F8",
                    fill: false,
                    tension: 0.3
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

        //Grafico a barre per i progetti e il loro stato
        let barColors =  ['#0B3C5D', '#1D65A6', '#3C8DAD','#7FC8F8','#D6F6FF'];
        new Chart("graficoProgetti", {
                type: "bar",
                data: {
                    labels: <?php echo json_encode($stato); ?>,
                    datasets: [{
                        label: "Progetti per Stato",
                        data: <?php echo json_encode($nProgetti); ?>,
                        backgroundColor: barColors,
                        borderColor: "black",
                        borderWidth: 1
                    }]
                },
                options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false 
                    }
                }
            }
        });
        </script>
</html>