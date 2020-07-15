<?php
if (isset($_GET['show']) && intval($_GET['show']) > 0) {
    print'
  <a href="/articles_list">Powrót</a>
  ';

    $id = intval($_GET['show']);
    $stmt = $dbh->prepare("SELECT * FROM articles WHERE id = :id");
    $stmt->execute([':id' => $id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        print '
              <div style="position: relative">
              <h5 style ="text-align: center">'.($row['title']).'</h5><hr>
              <p class="card-text">' .($row['content']).'</p>
              </div>
              ';
    }

    // podstrona /articles_list/show/<id>,
    // tutaj wyswietlamy tytul i tresc artykulu, ktorego ID mamy w zmiennej $id

} elseif (isset($_GET['edit']) && intval($_GET['edit']) > 0) {

    print '
        <a  href="/articles_list">Powrót</a>
        ';

    $id = intval($_GET['edit']);

    if (isset($_POST['title']) && isset($_POST['content'])) {
        try{
            $stmt = $dbh->prepare("UPDATE articles SET title = :title, content = :content WHERE id = :id AND user_id = :user_id");
            $stmt->execute([':title' => $_POST["title"], ':content' => $_POST["content"], ':id' => $id, ':user_id' => (isset($_SESSION['id']) ? $_SESSION['id'] : 0)]);
            print '<span style="color: green;">Twoje zmiany zostały zapisane</span>';
        }catch(PDOException $e){
            print '<span style="color: red;">Nie masz uprawnień aby edytować ten artykuł</span>';

            // tutaj zapisujemy zmiany w artykule $id, zakladajac, ze w formularzu edycji
            // dla tytulu i tresci nadano atrybuty name="title" oraz name="content",
            // przed zapisem nalezy upewnic sie, ze zalogowany uzytkownik jest autorem artykulu
        }
    }

    try{
        $stmt = $dbh->prepare("SELECT * FROM articles WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }catch(PDOException $e){
        print '<span style="color: red;">Artykuł nie istnieje</span>';
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print'
        <form action="/articles_list/edit/'.$row['id'].'" method="POST">
        <input type="textarea" style="width: 730px" name="title" value="'. htmlspecialchars($row['title']) .'">
        <br>
        <input class="art-selector" style="height: 300px" type="textarea" name="content" value="'. htmlspecialchars($row['content']) .'" >
        <br>
        <input type="submit" value="Zapisz">
        </form>
        ';
    }

    // podstrona /articles_list/edit/<id>,
    // tutaj wyswietlamy formularz edycji artykulu, ktorego ID mamy w zmiennej $id

} else {

    if (isset($_GET['delete']) && intval($_GET['delete']) > 0) {

        $id = intval($_GET['delete']);
        $stmt = $dbh->prepare("DELETE FROM articles WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => (isset($_SESSION['id']) ? $_SESSION['id'] : 0)]);
        // tutaj usuwamy artykul, ktorego ID mamy w zmiennej $id,
        // przed usunieciem nalezy upewnic sie, ze zalogowany uzytkownik jest autorem artykulu

    }

    if (isset($_GET['delete'])){
        $stmt = $dbh->prepare("SELECT * FROM articles ORDER BY id DESC");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {


            if(isset($_SESSION['id'])  && ($_SESSION['id'] == ($row['user_id'])) ){
                print '
                      <tr>
                      <td><a href="/articles_list/show/'.intval($row['id']).'">' . htmlspecialchars($row['title']) . '</a></td>
                      <td> <a href ="/articles_list/delete/'.intval($row['id']).'">Skasuj artykuł</a></td>
                      <td> <a href ="/articles_list/edit/'.intval($row['id']).'">Edytuj artykuł</a></td></br><hr>
                      </tr>
                      ';
            }else{
                print '
                       <tr>
                       <td><a href="/articles_list/show/'.intval($row['id']).'">' . htmlspecialchars($row['title']) . '</a></td></br><hr>
                       </tr>
                       ';
            };
        }
    }

    // podstrona /articles_list,
    // tutaj wyswietlamy listę wszystkich artykulow
}

if (!isset($_GET['show']) && !isset($_GET['edit']) && !isset($_GET['delete'])) {
    $stmt = $dbh->prepare("SELECT * FROM articles ORDER BY id DESC");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {


        if(isset($_SESSION['id'])  && ($_SESSION['id'] == ($row['user_id'])) ){
            print '<div style="postion: relative">
            <a href="/articles_list/show/'.intval($row['id']).'">' . htmlspecialchars($row['title']) . '</a>
            <a style="position: absolute; right:0px;" href ="/articles_list/delete/'.intval($row['id']).'">Skasuj artykuł</a>
            <a style="position: absolute; right:160px;" href ="/articles_list/edit/'.intval($row['id']).'">Edytuj artykuł</a><hr>
        </div>';
        }else{
            print
                '<div style="postion: relative">
            <a href="/articles_list/show/'.intval($row['id']).'">' .htmlspecialchars($row['title']). '</a><hr>
        </div>';
        };
    }
};
