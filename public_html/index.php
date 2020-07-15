<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);
define("IN_INDEX", 1);
require __DIR__ . '/vendor/autoload.php';

include("config.inc.php");

if (isset($config) && is_array($config)) {

    try {
        $dbh = new PDO('mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4', $config['db_user'], $config['db_password']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        print "Nie mozna polaczyc sie z baza danych: " . $e->getMessage();
        exit();
    }

} else {
    exit("Nie znaleziono konfiguracji bazy danych.");
}

include("functions.inc.php");
$user = 0;
if (isset($_POST['login'])&&isset($user)) {
    $stmt = $dbh->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $_POST['login']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($_POST['password'], $user['password'])) {

            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
        }
    }
}
if (isset($_POST['logout'])) {
    unset($_SESSION['id']);
    unset($_SESSION['email']);
}

if (isset($_SESSION['id'])) {
    $stmt = $dbh->prepare("UPDATE users SET last_seen = NOW() WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['id']]);
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Strona <?php print domena(); ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="dragDropStyle.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
            integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
            crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
            integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
            crossorigin="anonymous"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>tinymce.init({selector:'.art-selector'});</script>
    <script>
        $('img').mousedown(function (e) {
            if(e.button === 2) { // right click
                return false; // do nothing!
            }});

    </script>

    <style>
        html {
            position: relative;
            min-height: 100%;
        }

        body {
            margin-bottom: 60px;
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 60px;
            line-height: 60px;
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>


<nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#"><?php print domena(); ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav" id="menu-buttons">
                <li class="nav-item active">
                    <a class="nav-link" href="/index.php">Strona główna</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/articles_list">Artykuły</a>

                </li>
                <?php
                if (isset($_SESSION['id']) && isset($_SESSION['email'])) {
                    print '
                <li class="nav-item">
                    <a class="nav-link" href="/articles_add">Dodaj artykuł</a>
                </li>';
                } ?>
                <li class="nav-item">
                    <a class="nav-link" href="/register">Rejestracja</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/guest_book">Księga gości</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/gallery">Galeria</a>
                </li>
            </ul>
        </div>
        <?php

        if (isset($_SESSION['id']) && isset($_SESSION['email'])) {
            print '

          <form action="" method="POST">
            <button class="btn btn-outline-info my-2 my-sm-0" type = "submit" name="logout" value="logout"> Wyloguj </button >
           </form>
        ';
        } else {
            print '

        <form action = "" method = "POST" class="form-inline my-2 my-lg-0" >
            <input type = "text" name = "login" class="form-control mr-sm-2" placeholder = "Login" aria - label = "login" style = "width: 150px;" >
            <input type = "password" name = "password" class="form-control mr-sm-2" placeholder = "Hasło" aria - label = "password" style = "width: 150px;" >
            <button class="btn btn-outline-info my-2 my-sm-0" type = "submit" > Zaloguj się </button >
        </form >';
        }

        ?>
    </div>
</nav>

<div class="jumbotron mx-auto">
    <div class="container">
        <h1 class="display-4">Blog osobisty</h1>
        <p class="lead">Znajdziesz tutaj artykuły na każdy temat.</p>
    </div>
</div>

<div class="container text-center">
            <?php
            $allowed_pages = ['main', 'articles_list', 'articles_add', 'register', 'guest_book', 'gallery'];
            $protected_pages = ['articles_add', 'gallery'];

            if (isset($_GET['page']) && $_GET['page'] && in_array($_GET['page'], $allowed_pages)) {

                if (in_array($_GET['page'], $protected_pages) && !isset($_SESSION['id'])) {
                    print 'Nie możesz tu wejść';
                } else {

                    if (file_exists($_GET['page'] . '.php')) {
                        include($_GET['page'] . '.php');
                    } else {
                        print 'Plik ' . $_GET['page'] . '.php nie istnieje.';
                    }
                }
            } else {
                include('main.php');
            }
?>
</div>
<footer class="footer mt-auto" style="background-color: #f5f5f5;">
    <div class="container text-center">
        <span class="text-muted">Aktualna data: <?php print date('Y-m-d'); ?></span>
    </div>
</footer>
</body>
</html>