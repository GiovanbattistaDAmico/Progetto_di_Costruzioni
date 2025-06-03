<style>    
    .grafici {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
        margin-top: 20px;
        padding: 30px;
        border-radius: 20px;
    }
    .grafici div {
        background: linear-gradient(to bottom, #a4b0be, #747d8c);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        flex: 1 1 300px;
        max-width: 460px;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .grafici div:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
    }
    .grafici h2 {
        font-size: 20px;
        margin-bottom: 15px;
        color: #ffffff;
    }
    .grafici_responsabile{
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
        margin-top: 20px;
        padding: 30px;
        border-radius: 20px;
    }
    .grafici_responsabile div{
        background: linear-gradient(to bottom, #a4b0be, #747d8c);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        flex: 1 1 300px;
        max-width: 450px;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }
</style>