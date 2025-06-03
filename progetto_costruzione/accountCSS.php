<style> 
    body{
        font-family:sans-serif;
        background-color:#1b3b5e;
        color:white;
    }
    .titolo{
        font-size: 50px;
        font-weight: bold;
        text-align:center;
        background-color:transparent;       
    }
    .container{
            width:100%;
            display:flex;
            justify-content:center;
            height:550px;
            align-items:center;
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
    .last{
        display:flex;
        flex-direction: column;
        align-items:center;
        margin-top:10px;
        gap:30px;
    }
    .salva{
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
    .salva:hover{background-color:darkgreen;}
    .back {
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
    }

    .back:hover{background-color:grey;}
    .reg:active , .back:active {transform:translateY(5px);    box-shadow: 0 2px #666;}
</style>