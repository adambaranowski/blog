<div class="card mb-3">
    <div class="card-header">
        Rejestracja
    </div>
    <div class="card-body">

        <?php
        //if (!defined('IN_INDEX')) { exit("Nie można uruchomić tego pliku bezpośrednio."); }
        if (isset($_POST['register_email'])) {

            $register_email = $_POST['register_email'];



        if (isset($_POST['register_password'])) {

            $register_password = $_POST['register_password'];
        }

        $ip = $_SERVER['REMOTE_ADDR'];

        $recaptcha_response = $_POST["g-recaptcha-response"];
        $recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_private']);
        $resp = $recaptcha->setExpectedHostname('s32.labwww.pl')->verify($recaptcha_response, $_SERVER["REMOTE_ADDR"]);



        if ($resp->isSuccess()&&
        preg_match('/^[a-zA-Z0-9\-\_\.]+\@[a-zA-Z0-9\-\_\.]+\.[a-zA-Z]{2,5}$/D', $register_email)

        ) {

        $register_password = password_hash($register_password, PASSWORD_DEFAULT);

        try {
            $stmt = $dbh->prepare('
                        INSERT INTO users (
                            id, email, password, created
                        ) VALUES (
                            null, :email, :password, NOW()
                        )
                    ');
            $stmt->execute([':email' => $register_email, ':password' => $register_password]);
            print '<span style="color: green;">Konto zostało założone.</span>';
        } catch (PDOException $e) {
            print '<span style="color: red;">Podany adres email jest już zajęty.</span>';
        }
        }else {
                         print '<p style="font-weight: bold; color: red;">Sprawdź czy zaznacyłeś captche i wpisałeś prawidłowy e-mail!</p>';
                     }
        }

        ?>


        <form action="/register" method="POST">
            <input type="text" name="register_email" placeholder="E-mail">
            <input type="text" name="register_password" placeholder="Hasło">

            <input type="submit" value="Dodaj">
            <?php print '<div class="g-recaptcha" data-sitekey="'.$config['recaptcha_public'].'"></div>
            <br/>'
            ?>
        </form>

    </div>
</div>