<?php 
    require 'db.php';
    session_start();
    include 'funzioni.php';
    verificaLogin();
    if ($_SESSION['ruolo'] != 'Contabile') {
        header('Location: menu.php');
        exit();
    }

    // Recupero il menu appropriato in base al tipo di utente e ruolo
    $menu = getMenuPerUtente($_SESSION['tipo_utente'], $_SESSION['ruolo']);

    //Recupero dei progetti con i relativi costi
    $costo_totale = [];
    $progetti = [];
    $nominativi_progetti =[];
    $sql_costo =  "SELECT id_progetto, nome_progetto, budget, costo_effettivo FROM progetti WHERE stato = 'Completato' AND id_azienda=?";
    $stmt_costo = $conn->prepare($sql_costo);
    $stmt_costo -> bind_param("i",$_SESSION['id_azienda']);
    $stmt_costo -> execute();
    $result = $stmt_costo -> get_result();
    while($row = $result ->fetch_assoc()){
        $progetti[] = $row;
        $nominativi_progetti[] = $row['nome_progetto'];
        $costo_totale[] = $row['costo_effettivo'];
    }

    //Recupero dei materiali e costi totale per materiale 
    $materiali = [];
    $costo =[];
    $sql_mat = "SELECT nome,categoria,(costo_unitario * quantita) AS costo_totale FROM materiali_attrezzature 
    WHERE stato = 'Disponibile' AND id_azienda = ?";
    $stmt_mat = $conn->prepare($sql_mat);
    $stmt_mat ->bind_param("i",$_SESSION['id_azienda']);
    $stmt_mat ->execute();
    $result = $stmt_mat -> get_result();
    while($row = $result -> fetch_assoc()){
        $materiali[] = $row['nome']." ".$row['categoria'];
        $costo[] = $row['costo_totale'];
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
                <button onclick="location.href='menu_contabile.php'" class="back"><i class="fas fa-arrow-left"></i>&nbsp; Indietro</button>    
                </div>
            </div>

            <!--Sezione per i grafici-->
            <div class="grafici">
                <div>
                    <h2>Costo Totale per Progetti</h2>
                    <canvas id="graficoCostoProgetti" class="graficoCostoProgetti"></canvas>
                </div>
            
             <!--Distribuzionedei costi dei materiali-->
                <div>
                    <h2>Distribuzioni costi Materiali e Attrezzature</h2>
                    <canvas id="graficoCostoMat" class="graficoCostoMat"></canvas> 
                </div>
                </div>
            <div>
                <?php if(count($progetti)>0):?>
                    <table>
                        <tr>
                            <th>ID Progetto</th>
                            <th>Nome Progetto</th>
                            <th>Budget</th>
                            <th>Costo Effettivo</th>
                            <th>Guadagno/Perdita</th>
                        </tr>
                            <?php foreach($progetti as $progetto):?>
                            <tr>
                                <td><?php echo $progetto['id_progetto']; ?></td> 
                                <td><?php echo $progetto['nome_progetto'];?></td>
                                <td><?php echo $progetto['budget']." €"; ?></td>
                                <td><?php echo $progetto['costo_effettivo']." €";?></td>
                                <?php $differenza=$progetto['budget']-$progetto['costo_effettivo'];?>
                                <?php if($differenza>=0): ?><td style="color:green"><?php echo $differenza." €";?></td>
                                    <?php else:?><td style="color:red"><?php echo $differenza." €";?></td><?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                    </table>    
                    <?php else: ?>
                        <p>Nessun log trovato per questo filtro.</p>
                <?php endif; ?>
            </div>  
            </body>
    <script>
        //Grafico a barre per il costo dei progetti
        new Chart("graficoCostoProgetti", {
                type: "bar",
                data: {
                    labels: <?php echo json_encode($nominativi_progetti); ?>,
                    datasets: [{
                    label:"Costo",
                    backgroundColor: ['#0B3C5D', '#1D65A6', '#3C8DAD','#7FC8F8','#D6F6FF'],
                    borderColor: "black",
                    borderWidth:2,
                    data: <?php echo json_encode($costo_totale); ?>
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
        //Grafico per i costi dei materiali
        new Chart("graficoCostoMat", {
                type: "bar",
                data: {
                    labels: <?php echo json_encode($materiali); ?>,
                    datasets: [{
                    label:"Distribuzioni costi Materiali e Attrezzature",
                    backgroundColor: ['#0B3C5D', '#1D65A6', '#3C8DAD','#7FC8F8','#D6F6FF'],
                    borderColor: "black",
                    borderWidth:2,
                    data: <?php echo json_encode($costo); ?>
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
    </script>
</html>