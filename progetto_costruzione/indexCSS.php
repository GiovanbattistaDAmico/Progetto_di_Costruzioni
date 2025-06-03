<style> 
    body{
        font-family:sans-serif;
        background: linear-gradient(to right, #1b3b5e, #002147);        
        color: #e0e6ed;
        font-size:18px;
    }
    .intestazione {
        display: flex;
        justify-content: space-between; 
        align-items: center; 
        padding: 5px 10px; 
        color: white; 
        box-sizing: border-box;
        width: 100%;
    }

    .logo {
        width: 150px;
        height: 140px; 
    }
    .titolo{
        font-size:60px;
        font-family:sans-serif;
        align-self:center;
       text-align:center;
    }
    
    .button_box {
        display: flex;
        flex-direction: column; /* Colonna per i bottoni */
        gap: 10px; /* Spazio tra i bottoni */
        align-items: flex-end; /* Allinea i bottoni a destra */
    }
    .button_box button{
        width:150px;
        height:50px;
        margin: 5px 10px 5px 5px;
        border-radius:20px;
        background-color:#3169a9;
        border: 3px solid black;
        align-self:center;
        font-size:16px;
    
    }
    .button_box button:hover , .div_inizia button:hover , .button_box2 button:hover{
        background-color:#3675bc;
    }
    .button_box button:active , .div_inizia button:active , .button_box2 button:active{
        background-color:#1b3b5e;
        transform: translateY(2px);
    }
    .pulsanti{
        display:flex;
        justify-content:center;
        padding:30px;
    }
    .pulsanti button{
        width:150px;
        height:50px;
        border-radius:20px;
        background-color:#3169a9;
        border: 3px solid black;
        font-size: 16px;
        margin:5px 10px 5px 10px;
    }
    .pulsanti button:hover{
        background-color:#3675bc;
    }
    .pulsanti button:active{
        background-color:#1b3b5e;
        transform: translateY(2px);
    }
    .image_background {
            background-image: url('Img.jpg');
            text-align:center;
            background-repeat: no-repeat;
            background-size: cover;
            background-size: 100% 100%;
            color:#265284; 
            padding: 50px;
            min-height: 500px;
        }
        .info_sito {
        max-width: 1400px;
        margin: 60px auto;
        padding: 0 20px;
        font-family: 'Poppins', sans-serif;
        font-size: 1.15rem;
        color: white;
        line-height: 1.9;
    }

    .sottotitolo {
        font-size: 2.2rem;
        color:#7bdff2;
        margin-top: 70px;
        margin-bottom: 20px;
        font-weight: 700;
        position: relative;
        text-align: left;
    }

    .sottotitolo::before {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 80px;
        height: 3px;
        background:linear-gradient(90deg, #7bdff2, #b2f7ef);
        border-radius: 2px;
    }

    hr {
        border: none;
        margin: 40px 0;
        height: 2px;
        background: linear-gradient(to right, #7bdff2 0%, #b2f7ef 50%, transparent 100%);
    }
    
    .div_inizia{
        width: 100%;
        height: 100px;
        text-align:center;
        margin-top:20px;
        margin-bottom:20px;
    }
    .div_inizia button{
        width:150px;
        height:50px;
        border-radius:20px;
        background-color:#3169a9;
        border: 3px solid black;
        padding: 10px 20px;
        font-size: 16px;
    }
    /* SEZIONE CARD DELLE FUNZIONALITA'*/
    .griglia{
        display:grid;
        gap:30px;
        grid-template-columns: repeat(3, 1fr);   
        justify-items:center; 
        padding:40px;
    }
    .card{
        background: linear-gradient(to bottom, #3c6fa8, #5d88c5);;
        padding:20px;
        border-radius:10px;
        width:90%;
        height:250px;
        text-align:center;
        transition: transform 0.6s ease;

    }
    .card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
    .titolo_card{
        position:relative;
        top:50%;
        transform: translateY(-50%);
        transition: transform 0.6s ease;
    }
    .titolo_card i{
        font-size:40px;
        color: #7dd3fc;
    }
    .titolo_card h3{
        font-size:30px;
        color: #bae6fd; 
    }
    .descrizione{
        opacity:0;
        transition: opacity 0.6s ease;
        color: #cbd5e1; 
        font-size:20px;
    }
    .card:hover .titolo_card{
        transform:translateY(-120px);
    }
    .card:hover .descrizione{
        opacity:1;
    }
    hr {
    border: 0;
    height: 2px;
    background: linear-gradient(to right, #3b82f6, #9333ea); /* blu → viola */
    margin: 30px 0;
    }
    /* Stili base per la card */
        .button_box2{
        display:block;
        align-items:center;
        align-self:center;
        text-align:center;
    }
    .button_box2 button{
        width:200px;
        height:50px;
        border-radius:20px;
        background-color:#3169a9;
        border: 3px solid black;
        padding: 10px 20px;
        font-size: 16px;
    }
    .support{
        display:grid;
        grid-template-columns: repeat(3,1fr);
        border-radius:20px;
        justify-content:center;
        background-color:black;
    }
    .support h2{
        color:yellow;
        text-align:center;
    }
    .support div{
        padding: 15px;
        border-radius: 8px;
        border: 2px solid white;
    }
    .slideshow-container {
        max-width: 90%;
        position: relative;
        object-fit: contain;
        margin: auto;
    }
    /* Nasconde le immagini */
    .mySlides {
    display: none;
    }
    .mySlides img {
        width: 600px;
        height: 600px;
        border-radius: 15px; /* angoli arrotondati */
        transition: opacity 2s ease-in-out;
    }
    .box_img h2{
        font-weight:bold;
        font-size:40px;
        text-align:center;
    }
    /* Fading animation */
    .fade {
    animation-name: fade;
    animation-duration: 1.5s;
    }
    @keyframes fade {
    from {opacity: 0.1}
    to {opacity: 1}
    }
</style>