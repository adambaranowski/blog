<div class="card mb-3">
    <div class="card-header">
        Księga gości
    </div>
    <div class="card-body">
        <?php
        if (!defined('IN_INDEX')) { exit("Nie można uruchomić tego pliku bezpośrednio."); }
        if (isset($_POST['opinion'])) {
        $opinion = $_POST['opinion'];
        $ip = $_SERVER['REMOTE_ADDR'];

        $recaptcha_response = $_POST["g-recaptcha-response"];
        $recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_private']);
        $resp = $recaptcha->setExpectedHostname('s32.labwww.pl')->verify($recaptcha_response, $_SERVER["REMOTE_ADDR"]);



        if (mb_strlen($opinion) >= 5 && mb_strlen($opinion) <= 200 && $resp->isSuccess()) {

        $stmt = $dbh->prepare("INSERT INTO guest_book (opinion, ip, created) VALUES (:opinion, :ip, NOW())");
        $stmt->execute([':opinion' => $opinion, ':ip' => $ip]);

        print '<p style="font-weight: bold; color: green;">Dane zostały dodane do bazy.</p>';
        } else {
        print '<p style="font-weight: bold; color: red;">Podane dane są nieprawidłowe.</p>';
        }
        }


        if (isset($_GET['delete'])) {
            $stmt = $dbh->prepare("DELETE FROM guest_book WHERE id = :id AND ip = :ip");
            $stmt->execute([':id' => $_GET['delete'], ':ip' => $_SERVER['REMOTE_ADDR']]);
        }

        ?>

        <form action="/guest_book" method="POST">
            <input type="text" name="opinion" placeholder="Opinia">

            <input type="submit" value="Dodaj">
            <?php print '<div class="g-recaptcha" data-sitekey="'.$config['recaptcha_public'].'"></div>
            <br/>'
            ?>
        </form>


        <table class="table table-striped" id="moja-tabelka">
            <thead>
            <tr id="wiersz-naglowka">
                <th scope="col">ID</th>
                <th scope="col">Opinia</th>

            </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $dbh->prepare("SELECT id, opinion, ip FROM guest_book");
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                //przyjazny link  $rlink = 'index.php?page=guest_book&delete=' . $row['id'];
                $rlink = '/guest_book/delete/' . $row['id'];
                if($row['ip'] == $_SERVER["REMOTE_ADDR"]){
                    print '
                <tr>
                  <td>' . intval($row['id']) . '</td>
                  <td>' . htmlspecialchars($row['opinion'], ENT_QUOTES | ENT_HTML401, 'UTF-8') . '</td>
                   <td><a href='.$rlink.'><button>Delete</button></a></td>
                </tr>';
                }else{
                    print '
                <tr>
                  <td>' . intval($row['id']) . '</td>
                  <td>' . htmlspecialchars($row['opinion'], ENT_QUOTES | ENT_HTML401, 'UTF-8') . '</td>
                  <td></td>
    
                </tr>';
                }


            }
            ?>
            </tbody>

        </table>



    </div>
</div>