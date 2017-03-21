<!doctype html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?=(!empty($title)) ? $title : 'Качканарское время в PDF';?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="shortcut icon" href="/favicon.png" type="image/png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lora:400,700">
        <link rel="stylesheet" href="/css/main.css">
    </head>
    <body>
        <header class="header">
            <a href="/" class="logo">Качканарское время</a>
        </header>

        <main class="content container">
            <?=$content?>
        </main>

        <footer class="footer">
            <p class="container">© Редакция газеты «<a href="http://kvtime.ru">Качканарское время</a>». Архив выпусков газеты в формате PDF</p>
        </footer>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
        <script src="/js/main.js"></script>

        <!--Openstat-->
        <span id="openstat4038194"></span>
        <script type="text/javascript">
        var openstat = { counter: 4038194, next: openstat };
        (function(d, t, p) {
        var j = d.createElement(t); j.async = true; j.type = "text/javascript";
        j.src = ("https:" == p ? "https:" : "http:") + "//openstat.net/cnt.js";
        var s = d.getElementsByTagName(t)[0]; s.parentNode.insertBefore(j, s);
        })(document, "script", document.location.protocol);
        </script>
    </body>
</html>
