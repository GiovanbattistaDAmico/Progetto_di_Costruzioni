<style>
    .button{
        width: 100%;
        height: 100px;
        margin-top:20px;
        margin-bottom:20px;
        display:flex;
        justify-content:space-between;
    }
    .add_button1,.add_button2,.add_button3{
        width:250px;
        height:50px;
        border-radius:20px;
        background-color:#3169a9;
        border: 3px solid black;
        font-size: 16px;
        margin-right:10px;
    }
    .add_button1:hover,.add_button2:hover,.add_button3:hover{background-color:#3675bc;}
    .add_button1:active,.add_button2:active,.add_button3:active{
        background-color:#204671;
        transform:translateY(5px);
    }
    .container{
        width:100%;
        display:flex;
        justify-content:center;
        height:600px;
        align-items:center;
    }
    .box_form{
        width:40%;
        background-color:#3675bc;
        border-radius:10px;
        border:4px solid black;
        height:90%;
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
    }
    .crea{
        margin-top: 15px;        
        background-color:green;
        border-radius:10px;
        height:40px;
        border:2px solid black;
        font-size:18px;
        font-weight: bold;
        color:white;
        padding:10px;
        width:250px;
        height:40px;
    }
    .crea:hover{background-color:darkgreen;}
    .back2 {
        text-align: center;
        text-decoration: none;
        background-color: darkgrey;
        border-radius: 10px;
        border: 2px solid black;
        font-size: 18px;
        font-weight: bold;
        color: white;
        display: block;              
        line-height: 40px;           
        text-align: center; 
        padding:2px;
        width:150px;
        margin-top:10px;
        height:40px;  
    }
    .back2:hover{background-color:grey;}
    .crea:active , .back2:active {transform:translateY(5px);    box-shadow: 0 2px #666;}
    .lista_progetti{display:block;}
    .lista_richieste_progetti{display:none;}
    
    
  
</style>