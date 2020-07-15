<div class="card mb-3">

    <div class="card-header">
        Dodaj artykuł
    </div>
    <div class="card-body">
        <form action="/articles_add" method="POST"  style="text-align: center">
            <input name="title" style="text-align: center " placeholder="Title">
            <br>
            <input class="art-selector" style="height:300px" style="text-align: center" type="textarea" name="content" placeholder="Your article">
            <br>
            <input type="submit" value="Dodaj">
        </form>

        <?php
        if (isset($_POST['content']))
        {
            $title = $_POST['title'];
            $content = $_POST['content'];
            $user_id = $_SESSION['id'];
            $stmt = $dbh->prepare("INSERT INTO articles (user_id, title, content, created) VALUES (:user_id, :title, :content, NOW())");
            $stmt->execute(['user_id' => $user_id, ':title' => $title, ':content' => $content]);
        }


        ?>
    </div>
</div>
