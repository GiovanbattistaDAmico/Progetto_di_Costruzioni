<!DOCTYPE html>
<html>
    <head>
    <title>Edil Planner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php include 'indexCSS.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
<body>
    <!--Prima parte del sito con logo, titolo e pulsanti di navigazione-->
    <header>
        <div class="intestazione" id="home">
            <video class="logo" autoplay muted>
            <source src="edil_planner.mp4" type="video/mp4">
            </video>
            <h1 class="titolo">Edil Planner</h1>
            <div class="button_box">
            <!--Pulsanti Registrazione e Login-->
                <button onclick='location.href="register.php"'><i class="fa-solid fa-user-plus"></i>&nbsp;Registrazione</button>
                <button onclick='location.href="login.php"'> <i class="fa-solid fa-right-to-bracket"></i>&nbsp;Login</button>
            </div>
        </div> <hr>
        <nav>
        <!--Bottoni di Navigazione-->
            <div class="pulsanti">
                <button onclick="smoothScroll('#introduzione')"><i class="fa-solid fa-info-circle"></i>&nbsp;Introduzione</button>
                <button onclick="smoothScroll('#funzionalita')"><i class="fa-solid fa-cogs"></i>&nbsp;Funzionalità</button>
                <button onclick="smoothScroll('#supporto')"><i class="fa-solid fa-phone"></i>&nbsp;Contatti</button>
            </div>
        </nav>
    </header>
    <!--Immagine con slogan-->
        <div class="image_background">
            <div class="titolo">
                <h1 style="background-color:transparent;color:black">Benvenuto sul Sito Web di <br>Edil Planner Srl</h1>
                <h3 style="background-color:transparent;color:black;">Pianifica oggi, costruisci il futuro.</h3>
            </div>
        </div><br><br><br><hr>
    <!--Informazioni Sul Sito-->
    <main>
        <section class="info_sito" id="introduzione">
            <h2 class="sottotitolo">Edil Planner – La soluzione digitale per una gestione efficiente dei cantieri</h2>
            <p>Edil Planner è una piattaforma innovativa pensata per semplificare e ottimizzare la gestione dei progetti di costruzione. 
            Grazie a strumenti avanzati di pianificazione, monitoraggio e gestione delle risorse, imprese, committenti e professionisti 
            del settore possono lavorare in modo più efficiente, riducendo gli sprechi e migliorando la produttività.</p>
        </section>

        <section class="info_sito">
            <h2 class="sottotitolo">Gestione digitale del cantiere: trasparenza e controllo</h2>
            <p>Edil Planner sfrutta le più moderne tecnologie per centralizzare documenti, dati e comunicazioni in un unico spazio condiviso. 
            Ogni membro del team – dagli operai ai supervisori, dai responsabili ai contabili – può accedere alle informazioni necessarie 
            per garantire un flusso di lavoro senza interruzioni, riducendo errori e ritardi.</p>
        </section>

        <section class="info_sito">
            <h2 class="sottotitolo">Accesso riservato per i committenti: visibilità totale sui progetti</h2>
            <p>I committenti possono monitorare in tempo reale lo stato di avanzamento dei lavori, verificare i documenti e le certificazioni 
            dei professionisti coinvolti e assicurarsi che le scadenze e gli standard di qualità siano rispettati.</p>
        </section>

        <section class="info_sito">
            <h2 class="sottotitolo">Strumenti avanzati per le imprese: efficienza e monitoraggio</h2>
            <p>Le imprese possono controllare ogni fase del progetto, verificare l’operato dei lavoratori, gestire materiali e attrezzature, 
            tracciare i costi e i tempi di realizzazione. Grazie a report dettagliati e aggiornamenti in tempo reale, la gestione del cantiere 
            diventa più semplice e strategica.</p>
        </section>

        <section class="info_sito">
            <h2 class="sottotitolo">Sicurezza e conformità alle normative</h2>
            <p>Edil Planner aiuta a garantire il rispetto delle normative di sicurezza e delle certificazioni obbligatorie, facilitando la 
            gestione della documentazione e dei controlli di conformità.</p>
        </section>
    <!--Bottoni di iscrizione--><hr>
    <div class="div_inizia">
    <h2 >Unisciti a noi e rivoluziona la gestione dei tuoi cantieri!</h2>
    <button onclick='location.href="register.php"'><i class="fa-solid fa-user-plus"></i>&nbsp;Inizia Ora</button>
</div><hr>
    <!--Sezione Funzionalità-->
    <section>
    <b><div class="titolo" id="funzionalita">Funzionalità</div></b><br>
    <div class="griglia">
    <!-- Card 1: Pianificazione -->
    <div class="card">
            <div class="titolo_card">
                <i class="fas fa-calendar-check"></i> <!-- Icona Calendario -->
                <i><h3>Pianificazione</h3></i>
            </div>
            <div class="descrizione">
                <p>Organizza e pianifica i tuoi progetti di costruzione in modo preciso e ottimizzato.</p>
            </div>
    </div>
    <!-- Card 2: Gestione Attività -->
    <div class="card">
            <div class="titolo_card">
                <i class="fas fa-tasks"></i><!-- Icona Attività -->
                <i><h3>Gestione Attività</h3></i> 
            </div>
            <div class="descrizione">
                <p>Gestisci le attività di cantiere con facilità, assegnando compiti e monitorando i progressi.</p>
            </div>
    </div>
    <!-- Card 3: Monitoraggio Avanzato -->
    <div class="card">
            <div class="titolo_card">
                <i class="fas fa-chart-line"></i><!-- Icona Grafico -->
                <i><h3>Monitoraggio Avanzato</h3></i>  
            </div>
            <div class="descrizione">
                <p>Controlla e monitora il progresso del cantiere in tempo reale, con report dettagliati e aggiornamenti costanti.</p>
            </div>
    </div>
    <!-- Card 4: Gestione Materiali e Attrezzature -->
    <div class="card">
            <div class="titolo_card">
                <i class="fa-solid fa-screwdriver-wrench"></i><!-- Icona Materiali -->
                <i><h3>Gestione Materiali</h3></i>
            </div>
            <div class="descrizione">
            <p>Gestisci materiali e attrezzature in modo efficiente, evitando sprechi e ottimizzando l'uso delle risorse.</p>
            </div>
    </div>
    <!-- Card 5: Reportistica sui Costi e Tempistiche -->
    <div class="card">
            <div class="titolo_card">
                <i class="fas fa-file-invoice-dollar"></i>
                <i><h3>Reportistica</h3></i>
            </div>
            <div class="descrizione">
                <p>Genera report sui costi e sulle tempistiche del progetto per tenere tutto sotto controllo.</p>
            </div>
    </div>
    <!-- Card 6: Gestione Imprevisti -->
    <div class="card">
            <div class="titolo_card">
                <i class="fas fa-exclamation-circle"></i><!-- Icona Imprevisti -->
                <i><h3>Gestione Imprevisti</h3></i> 
            </div>
            <div class="descrizione">
                <p>Gestisci in modo rapido ed efficace gli imprevisti che potrebbero emergere durante il progetto.</p>
            </div>
    </div>
</section><hr>
    <!-- Richiamo dei bottoni-->
        <div class="button_box2">
            <h2>Inizia subito a gestire i tuoi progetti di costruzione con strumenti avanzati. Registrati per un'esperienza senza complicazioni!</h2>
            <button onclick='location.href="register.php"'><i class="fa-solid fa-user-plus"></i>&nbsp;Registrazione</button>
            <button onclick='location.href="login.php"'><i class="fa-solid fa-right-to-bracket"></i>&nbsp;Login</button>
        </div><hr>
        <!-- Slideshow con esempi di Costruzione -->
        <div class="box_img">
            <h2>Alcuni Progetti creati grazie ad Edil Planner</h2>
            <div class="slideshow-container">
                <!-- Immagini di esempio -->
                <div class="mySlides fade">
                <img src="Casa1.jpg" style="width:100%">
                </div>
                <div class="mySlides fade">
                <img src="Casa2.jpg" style="width:100%">
                </div>
                <div class="mySlides fade">
                <img src="Spa.jpeg" style="width:100%">
                </div>
                <div class="mySlides fade">
                <img src="Casa3.jpg" style="width:100%">
                </div>
            </div>
        </div>    
        <br>
        </main>
    <!--Parte di Supporto-->
    <footer>
    <p>&copy; 2025 Edil Planner Srl</p>
        <div class="support" id="supporto">
          <!--Riservatezza dei Dati-->  
            <div class="riservatezza">
                <h2><i class="fas fa-lock"></i>  Riservatezza</h2>
                <p>La tua privacy è la nostra priorità. Trattiamo i dati in conformità con il Regolamento Generale sulla Protezione dei Dati (GDPR).</p>
                <ul>
                    <li>I tuoi dati sono protetti e utilizzati solo per migliorare il servizio.
                    <li> Nessuna informazione verrà condivisa con terze parti senza il tuo consenso.
                    <li> Puoi richiedere la cancellazione dei tuoi dati in qualsiasi momento.
                    <li>Visita il link per maggiori informazione : <a href="https://www.garanteprivacy.it/il-testo-del-regolamento" target="_blank">GDPR</a>
                </ul>
            </div>
            <!--Contatti di emergenza--> 
            <div class="contatti">
                <h2><i class="fas fa-envelope"></i>  Contatti</h2>
                <p>Hai bisogno di aiuto? Puoi contattarci in qualsiasi momento. Per qualsiasi domanda o supporto, puoi contattarci:</p>
            </ul>    
                <li><i class="fas fa-envelope"></i> Email: support.edilplanner@exempio.it</li><br>
                <li><i class="fas fa-phone"></i> Telefono: +39 1234 567891</li><br>
                <li><i class="fas fa-map-marker-alt"></i> Indirizzo: Via Edile N.3, Aversa, Italia</li><br>
            </ul>   
            </div>
            <div class="faq">
                <h2><i class="fas fa-question-circle"></i>  Domande Frequenti</h2>
                <p>Alcune domande frequenti:</p>
                <ol>
                    <li>Il Sito è gratuito? <br><i class="fas fa-comments"></i> Si è completamente gratuito.</li><br>
                    <li>Chi può usare Edil Planner? <br><i class="fas fa-comments"></i> Il Sito è pensato per Aziende, Committenti ed Esperti del Settore.</li>
                </ol>
            </div>
        </div>
    <footer>
        <!--Funziona per lo scorrimento lento--> 
        <script>
    function smoothScroll(target) {
        const element = document.querySelector(target);
        element.scrollIntoView({
            behavior: 'smooth', // Attiva lo scroll fluido
            block: 'start' // Allinea l'elemento all'inizio della finestra
        });
    }
    let slideIndex = 0;
    showSlides();
    function showSlides() {
    let i;
    let slides = document.getElementsByClassName("mySlides");
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    slideIndex++;
    if (slideIndex > slides.length) {slideIndex = 1}
    slides[slideIndex-1].style.display = "block";
    setTimeout(showSlides, 2000); // Change image every 2 seconds
    }
    </script>

    </body>
</html>
