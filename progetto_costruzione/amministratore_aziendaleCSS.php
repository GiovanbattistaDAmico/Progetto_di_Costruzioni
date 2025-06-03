<style>
    .avviso_container {
        position: fixed;
        top: 23%;
        right: 20px;
    }

    .avviso {
        font-size: 30px;
        color: red;
    }

    .messaggio {
        display: none;
        background: yellow;
        color: black;
        border:1px solid black;
        border-radius: 5px;
        padding:10px;
        width: 450px;
        position: absolute;
        right: 30px;
        top: 0;
        font-size: 16px;
    }
    .messaggio i {
        color:red;
    }
    .avviso_container:hover .messaggio{
        display: block;
    }

    .avviso_container:hover .avviso{
        visibility: hidden;
    }

</style>