<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="home.js"></script>
    <style><?php include (__DIR__."/home-style.css");
    ?></style>
    <link rel="home-style" href="/home-style.css">

</head>
<body>
    <header>
        <h1>Welcome to wikipedia searcher</h1>
    </header>

    <section>
        <form action="index.php" method="get">
            <span> Title: </span>
            <input type="text" name="fsearch" value="<?php if(isset($_GET['fsearch']))echo $_GET['fsearch']?>">
            <span> Sort by: </span>
            <select name="sort_by" selected="relevance">
                <option value="relevance" <?php if(isset($_GET['sort_by'])) if($_GET['sort_by'] == 'relevance') echo 'selected'?> >Relevance</option>

                <option value="views" <?php if(isset($_GET['sort_by'])) if($_GET['sort_by'] == 'views') echo 'selected'?> >Most views</option>
                
                <option value="size_asc" <?php if(isset($_GET['sort_by'])) if($_GET['sort_by'] == 'size_asc') echo 'selected'?> >Size ascending</option>
                <option value="size_des" <?php if(isset($_GET['sort_by'])) if($_GET['sort_by'] == 'size_des') echo 'selected'?> >Size descending</option>

                <option value="timestamp_asc" <?php if(isset($_GET['sort_by'])) if($_GET['sort_by'] == 'timestamp_asc') echo 'selected'?> >Created date ascending</option>
                <option value="timestamp_des" <?php if(isset($_GET['sort_by'])) if($_GET['sort_by'] == 'timestamp_des') echo 'selected'?> >Created date descending</option>

            </select>
            <button>Search</button>
        </form>
        <div id="results">
            <table>
                <tr>
                    <th> Title </th>
                    <th> Description </th>
                    <th> Page size </th>
                    <th> Created Date </th>
                <?php

                    foreach ($results as $result) {
                        $date = new DateTime($result->timestamp); 
                        $date = $date->format('Y-m-d H:i');

                        echo "
                        <tr>
                            <td>".$result->title."</td>
                            <td>".$result->snippet."... <a href=".$result->url.">Check wiki page! </a> </td>
                            <td>".$result->size."</td>
                            <td>".$date."</td>
                        </tr>
                        ";
                    }
                ?>
                </tr>
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
