<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style><?php include (__DIR__."/home-style.css");
    ?></style>
    <link rel="home-style" href="/home-style.css">

</head>
<body>
    <header>
        <h1>Welcome to consult lenguage searcher</h1>
    </header>
    <section>
    <h2>Rules</h2>
    <p>Operator: AND, OR, NOT</p>
    <p>Functions: CADENA, PATRON, CAMPOS</p>
    <p>Example: Papas Potato AND NOT Chips AND CADENA (con chile) OR PATRON (sabri) CAMPOS (products.description)</p>
        <form action="index.php" method="get">
            <input type="text" name="fsearch" value="<?php if(isset($_GET['fsearch']))echo $_GET['fsearch']?>">
            <button>Search</button>
        </form>
        <div id="results">
            <table>
                <?php

                    foreach ($results as $result) {
                        echo "<tr>";
                        if(is_array($result)){
                            foreach ($result as $value){
                                echo "<td>".$value."</td>";
                            }
                        }
                        echo "</tr>";
                    }
                ?>
                
            </table>
        </div>
    </section>

    <footer>
            <div>
                <h3> Universidad Autonoma de Yucatán </h3>
            </div>
            <div>
                <h3> 2022 </h3>
            </div>
            <div>
                <h3>Created by: Emmanuel Chable</h3>
            </div>
    </footer>

    
</body>
</html>
