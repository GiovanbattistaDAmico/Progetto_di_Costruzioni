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

    .sottotitolo{
        font-size: 20px;
        text-align: left; /* Centra il testo */
    }
    .back {
        width:150px;
        height:50px;
        margin: 5px 10px 5px 5px;
        border-radius:20px;
        background-color:darkgrey;
        border: 3px solid black;
        align-self:center;
        font-size:16px; 
        color:white;         
    }
    .back:hover{background-color:grey;}
    .reg:active , .back:active {transform:translateY(5px);    box-shadow: 0 2px #666;}
    .div_button {
        display: flex;
        flex-direction: column; /* Colonna per i bottoni */
        gap: 10px; /* Spazio tra i bottoni */
        align-items: flex-end; /* Allinea i bottoni a destra */
    }
    .buttons{
        display:flex;
        justify-content:flex-end;
        align-items:center;
        margin-top:5px;
    }
    .active , .active2{
        width:150px;
        height:50px;
        margin: 5px 10px 5px 5px;
        border-radius:20px;
        background-color:#3169a9;
        border: 3px solid black;
        align-self:center;
        font-size:16px;
    }
    .active:hover , .active2:hover{background-color:#3675bc;}
    .active:active , .active2:active{
        background-color:#204671;
        transform:translateY(5px);
    }
    table{
        width: 100%;
        border-collapse:collapse;
        background-color:#3169a9;
    }
    th,td {
        padding:10px;
        text-align:left;
        border:1px solid white;
    }
    .utenti:hover{
        background:white;
        color:black;
    }
    

</style>