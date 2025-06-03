<?php 

    //Funzione per verifica il login degli utenti
    function verificaLogin() {
        if (!isset($_SESSION['id_utente'])) {
            header('Location: index.php');
            exit;
        }
    }

    //Funzione per rimandare l'utente al suo menu in base al ruolo
    function getMenuPerUtente($tipo_utente, $ruolo=NULL) {
        switch ($tipo_utente) {
            case 'Admin':
                return 'menu_admin.php';
            case 'Azienda':
                return 'menu_Aaziendale.php';
            case 'Dipendente Aziendale':
                if ($ruolo == 'Responsabile') {
                    return 'menu_responsabile.php'; // Menu per il Responsabile
                } elseif ($ruolo == 'Operaio') {
                    return 'menu_operaio.php'; // Menu per l'Operaio
                }
                    elseif ($ruolo == 'Contabile') {
                    return 'menu_contabile.php'; // Menu per l'Operaio
                } else {
                    return 'menu_magazziniere.php'; // Menu generico per Dipendente
                }
            case 'Committente':
                return 'menu_committente.php';
            default:
                return 'index.php'; // o pagina di errore/accesso
        }
    }

    //Funzione per inviare la notifica ad un utente
    function inviaNotifica($conn,$id_utente,$tipo_notifica,$messaggio,$link){
        $data_notifica = date('Y-m-d H:i:s');
        $sql_notifica="INSERT INTO notifiche(id_utente,tipo_notifica,messaggio,link,data_notifica) VALUES (?,?,?,?,?)";
        $stmt_notifica = $conn->prepare($sql_notifica);
        $stmt_notifica->bind_param("issss",$id_utente,$tipo_notifica,$messaggio,$link,$data_notifica);
        $stmt_notifica->execute();
        $stmt_notifica->close();
    }

    //Funzione per la rimozione delle notifiche
    function rimuoviNotifiche($conn,$id_utente,$link){
        $pagina_corrente = basename($_SERVER['PHP_SELF']);
        $sql_delete = "DELETE FROM notifiche WHERE id_utente=? AND letto = 1 AND link = ? ";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete -> bind_param("is",$id_utente,$pagina_corrente);
        $stmt_delete -> execute();
        if ($stmt_delete->execute()) {
            return true;  // Se la query è stata eseguita con successo
        } else {
            return false; // In caso di errore nell'esecuzione
        }
    }
?>