<style>
    body{
        font-family:sans-serif;
        background: linear-gradient(to right, #1b3b5e, #002147);        
        color: white;
        font-size:18px;
    }
    .intestazione {
        display: flex;
        justify-content: space-between; 
        align-items: center; 
        padding: 5px 10px; 
        background-color: #0f4c81; 
        color: white; 
        box-sizing: border-box;
        width: 100%;
    }

    .logo {
        width: 150px;
        height: 140px; 
    }

    .titolo {
        font-size: 40px;
        text-align: center; /* Centra il testo */
    }

    .div_button {
        display: flex;
        flex-direction: column; /* Colonna per i bottoni */
        gap: 10px; /* Spazio tra i bottoni */
        align-items: flex-end; /* Allinea i bottoni a destra */
    }

    .div_button button{
        width:150px;
        height:50px;
        margin: 5px 10px 5px 5px;
        border-radius:20px;
        background-color:#3169a9;
        border: 3px solid black;
        align-self:center;
        font-size:16px;
    }
    .div_button button:hover{background-color:#3675bc;}
    .div_button button:active{
        background-color:#204671;
        transform:translateY(5px);
    }
    .side_nav{
        height: 100%; 
        width: 0; /*cambierà successivamente*/
        position: fixed; 
        z-index: 1; 
        top: 0; 
        left: 0;
        background-color: grey; 
        overflow-x: hidden; /* Disable horizontal scroll */
        padding-top: 60px; 
        transition: 0.5s; /* 0.5 second transition effect to slide in the sidenav */
        display:flex;
        flex-direction:column;
    }
    .close_bttn{
        position:absolute;
        top: 5px;
        right: 15px;
        font-size: 30px;
        margin-left: 50px;
        color:white;
        padding:5px;
        text-decoration:none;
    }
    .menu{
        text-align:center;
        color:white;
        font-size:18px;
    }
    .voce{
        color:white;
        font-size:18px;
        margin-top:15px;
        padding:10px;
        text-decoration: none;

    }
    .voce:hover{
        background-color:white;
        color:#002147;
    }
    hr{
        width:100%;
    }

    .grafici {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;  /* 3 colonne uguali */
    gap: 20px; 
    justify-items: center; 
    margin: auto;
    }
    .grafico_torta {
        text-align: center;  /
        width: 60%;  /* Imposta una larghezza inferiore per il grafico a torta */
    }

    .grafico_barre, .grafico_barre_orizzontale {
        text-align: center;  
        width: 100%;  /* Imposta la larghezza dei grafici rimanenti uguale */
    }

    canvas {
        width: 100%;
        height: 300px; 
    }
    .table_box{
        width: 100%;
        border-collapse:collapse;/*unire i bordi delle celle*/
    }
    .table_progetti{
        margin-top:100px;
        width: 100%;
        text-align:center;
        background-color:#3169a9;
    }
    th,td {
        padding:10px;
        text-align:left;
        border:1px solid white;
    }
    .righe:hover{
        background:white;
        color:black;
    }
    .submenu {
    display: none;
    background: #444;
    padding-left: 20px;
}

    .submenu a {
        display: block;
        padding: 8px 10px;
    }
    .guida {
        max-width: 1200px;
        background-color: #1e1e1e;
        padding: 20px;
        margin: 15px auto;
        border-radius: 12px;
        font-family: 'Poppins', sans-serif;
        color: white;
    }

    .guida h2 {
        font-size: 2.2rem;
        color: #7bdff2;
        margin-bottom: 10px;
        font-weight: 700;
        text-align:center;
    }

    .guida ul li i {
        color: #7bdff2;
    }



</style>