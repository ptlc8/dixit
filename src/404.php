<?php
include("init.php");
$images = json_decode(file_get_contents("https://pixabay.com/api/?key=".getPixabayApiKey()."&image_type=photo&per_page=5&page=1&q=404+Not+found"))->hits;
shuffle($images);
$image = $images[0]->largeImageURL;
?>
<?php print_r($a); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Page introuvable</title>
        <link rel="stylesheet" href="style.css" />
        <style>
            html {
                background: url('<?= $image ?>') no-repeat center/cover fixed;
            }
            main {
                text-align: center;
            }
            a {
                color: inherit;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>Page introuvable</h1>
            <a href="/">
                <button>
                    Retourner à l'accueil
                </button>
            </a>
        </main>
    </body>
</html>