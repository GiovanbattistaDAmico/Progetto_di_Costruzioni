<style>
    .container{
            width:100%;
            display:flex;
            justify-content:center;
            height:550px;
            align-items:center;
            margin-top:30px;
    }
    .box_form{
            width:30%;
            background-color:#3675bc;
            border-radius:10px;
            border:4px solid black;
            height:97%;
            padding:5px;
    }
    .parametri{
    display:flex;
    flex-direction:column;
    padding:5px;
    }
    .parametri label{
        margin-top:10px;
        margin-bottom:5px;
    }
    .last{
        display:flex;
        flex-direction: column;
        align-items:center;
        margin-top:10px;
    }
    .invia{
        margin-top: 15px;
        width: 60%;        
        background-color:green;
        border-radius:10px;
        height:40px;
        border:2px solid black;
        font-size:18px;
        font-weight: bold;
        color:white;
    }
    .annulla{
        text-align: center;
    text-decoration: none;
    background-color: darkgrey;
    border-radius: 10px;
    width: 50%;
    height: 40px;
    border: 2px solid black;
    font-size: 18px;
    font-weight: bold;
    color: white;
    display: block;              
    line-height: 40px;           
    text-align: center; 
    margin-top:10px;

    }
    .messaggio{
        display: flex;
        justify-content:space-between;
        align-items:center;
        padding: 10px;
        margin: 10px 0;
        background-color: #f1f1f1;
        border: 2px solid black;
        border-radius: 8px;
        color:black;
    }
    .mittente,.oggetto_messaggio{
        font-weight:bold;
        font-size:20px;
        color:#002147;
        margin-right:10px;
        flex-grow:1;
    }
    .contenuto_messaggio{
        font-weight:bold;
        margin-right:10px;
    }
    .annulla:hover{background-color:grey;}
    .invia:hover{background-color:darkgreen;}
    .invia:active , .annulla:active{
        transform:translateY(5px);
    }
    a {text-decoration: none;}
    .notifica_box a:hover {text-decoration: underline;}
</style>