<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrer un numéro</title>
    <style>
        :root {
            --orange: #e67e22;
            --orange-deep: #cc711e;
            --mid: #475569;
            --light: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            
            background: linear-gradient(rgba(255, 255, 255, 0.49), rgba(255, 255, 255, 0.5)), url('img/img (2).jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: black;
        }

        .container {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 40px 30px;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: #e67e22;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 1.1rem;
            color: black;
        }

        input[type="number"] {
            width: 100%;
            padding: 16px 20px;
            font-size: 1.2rem;
            border: 2px solid var(--mid);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: black;
            outline: none;
            transition: all 0.3s ease;
        }

        input[type="number"]:focus {
            border-color: var(--orange);
            box-shadow: 0 0 0 4px rgba(230, 126, 34, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        input[type="number"]::placeholder {
            color: black;
        }

        button {
            width: 100%;
            padding: 16px;
            font-size: 1.2rem;
            font-weight: bold;
            background: var(--orange);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background: var(--orange-deep);
            transform: translateY(-2px);
        }



        @media (max-width: 480px) {
            body{
                background: linear-gradient(to right, 
                #ffffff00 84.5%, 
                #e67e22 15.5% ), url('img/img (2).jpg');
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: black;
            }
            
            .container {
                padding: 30px 20px;
                margin: 15px;
            }
            
            h1 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Entrez votre numéro</h1>
        
        <form action="traitement_inscription.php" method="POST">
            <div class="form-group">
                <label for="numero">Numéro :</label>
                <input 
                    type="number" 
                    id="numero" 
                    name="numero" 
                    placeholder="Ex: 6XX XXX XXX" 
                    required
                    autofocus
                >
            </div>

            <button type="submit">Valider</button>
        </form>

    </div>

</body>
</html>