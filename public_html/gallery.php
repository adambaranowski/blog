<?php

//Removing Single Photos
if (isset($_POST['removePicture'])) {
    if (file_exists($_POST['removePicture'])) {
        $deleted = unlink($_POST['removePicture']);

        if ($deleted) {
            print '<span style="color: green;">Usunięto zdjęcie</span>';
        } else {
            print '<span style="color: red;">Nie udało się usunąć zdjęcia</span>';
        }
        try {
            $stmt = $dbh->prepare("DELETE FROM Pictures WHERE Src = :Src");
            $stmt->execute([':Src' => $_POST['removePicture']]);
        } catch (PDOException $e) {
            print '<span style="color: red;">Bład zapytania</span>';
        }
    }
}
//Removing a Category
if (isset($_POST['removeCategory'])) {

    try {
        $stmt = $dbh->prepare("SELECT Src FROM Pictures WHERE Category=:Category");
        $stmt->execute([':Category' => $_POST['removeCategory']]);
    } catch (PDOException $e) {
        print '<span style="color: red;">Bład zapytania</span>';
    }

    $files_srcs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($files_srcs as $srcs) {
        if (file_exists($srcs['Src'])) {
            $deleted = unlink($srcs['Src']);

            if ($deleted) {
                print '<span style="color: green;">Usunięto zdjęcie</span><br>';
            } else {
                print '<span style="color: red;">Nie udało się usunąć zdjęcia</span><br>';
            }

        }
    }

    $stmt = $dbh->prepare("DELETE FROM Pictures WHERE Category = :Category");
    $stmt->execute([':Category' => $_POST['removeCategory']]);
}

//Change Description
if (isset($_POST['changeDescription']) && $_POST['changeDescription']) {

    try {
        $stmt = $dbh->prepare("UPDATE Pictures SET Description=:Description WHERE Src=:Src");
        $stmt->execute([':Description' => $_POST['changeDescription'],':Src' => $_POST['changedPictureSrc']]);
        print '<span style="color: green;">Zmieniono opis</span><br>';
    } catch (PDOException $e) {
        print '<span style="color: red;">Bład zapytania do bazy danych</span>';
    }

}

//Change Category
if (isset($_POST['changeCategory']) && $_POST['changeCategory']) {

    try {
        $stmt = $dbh->prepare("UPDATE Pictures SET Category=:Category WHERE Src=:Src");
        $stmt->execute([':Category' => $_POST['changeCategory'], ':Src' => $_POST['changedPictureSrc']]);
        print '<span style="color: green;">Zmieniono kategorię</span><br>';
    } catch (PDOException $e) {
        print '<span style="color: red;">Bład zapytania do bazy danych</span>';
    }

}




//Adding new photos
if (isset($_FILES["file"]) && $_FILES["file"]["size"] > 100) {

    $target_dir = "/home/s32/public_html/pictures/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $message = "";


// Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["file"]["tmp_name"]);
        if ($check !== false) {
            $message .= "File is an image - " . $check["mime"] . ".\n";
            $uploadOk = 1;
        } else {
            $message .= "File is not an image.\n";
            $uploadOk = 0;
        }
    }

// Check if file already exists
    if (file_exists($target_file)) {
        $message .= "Sorry, file already exists.\n";
        $uploadOk = 0;
    }

// Check file size
    if ($_FILES["file"]["size"] > 500000) {
        $message .= "Sorry, your file is too large.\n";
        $uploadOk = 0;
    }

    //Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif") {
        $message .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {

        print '<h4 style="color: red">' . $message . '</h4>';

// if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {

            try {

                //Prepare source path
                $Src = 'pictures/' . basename($_FILES["file"]["name"]);

                if (isset($_POST['title']) && $_POST['title']) {
                    $Title = $_POST['title'];
                } else {
                    $Title = 'Brak Tytułu';
                }

                //Prepare category parameter
                if (isset($_POST['newCategory']) && $_POST['newCategory']) {
                    $Category = $_POST['newCategory'];
                } else {
                    if (isset($_POST['category']) && $_POST['category']) {
                        $Category = $_POST['category'];
                    } else {

                        $Category = 'Brak Kategorii';
                    }
                }

                //Prepare description parameter
                if (isset($_POST['description']) && $_POST['description']) {
                    $Description = $_POST['description'];
                } else {
                    $Description = 'Brak opisu';
                }


                $stmt = $dbh->prepare("INSERT INTO Pictures (Src, Title, Category, Description) VALUES (:Src, :Title, :Category, :Description)");
                $stmt->execute([':Src' => $Src, ':Title' => $Title, ':Category' => $Category, ':Description' => $Description]);

                print '<span style="color: green">' . 'Zdjęcie:  ' . basename($_FILES["file"]["name"]) . ' zostało przesłane.' . '</span>';

            } catch (PDOException $e) {
                print '<span style="color: red;">Bład zapytania</span>';
            }

        } else {
            print '<span style="color: red">' . $message . '</span>';
        }
    }
}


try {
    $stmt = $dbh->prepare("SELECT * FROM Pictures");
    $stmt->execute();
} catch (PDOException $e) {
    print '<span style="color: red;">Bład zapytania</span>';
}

$records_array = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
 * Connecting each category with color
 */
$colors = array("Aquamarine", "CornflowerBlue", "LightCoral", "LightGreen",
    "LightSkyBlue", "MediumPurple", "OrangeRed");

$categories = array();
$category_color = array();


$i = 0;
foreach ($records_array as $record) {
    if (!array_key_exists($record['Category'], $category_color)) {

        $value[$record['Category']] = $colors[$i];
        $category_color += $value;

        array_push($categories, $record['Category']);
        $i++;
    }
}
?>
<div class="row mx-auto" style="margin-bottom: 20px">
    <div class="col">
        <h2>
            Galeria Zdjęć
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-4" style="margin-top: 45px">
    <form class="add-form text-center mx-auto" method="post" enctype="multipart/form-data" action="/gallery"
          style="background: lightgray; border-radius: 10px" novalidate>
        <div class="form-row mx-auto">
            <div class="form-group mx-auto">
                <label for="chooseFile">Przeciągnij Zdjęcie Na Zielone Pole</label>

                <input name="file" type="file" class="file-path add-dropzone" id="chooseFile" style="background: lightgreen; border: solid 1px green; min-height: 80px; max-width: 90%">

                </div>
        </div>
        <div class="form-row mx-auto">
            <div class="col-md-4 mb-3 mx-auto">
                <label for="validationCustom01">Tytuł</label>
                <input name="title" type="text" class="form-control" id="validationCustom01" placeholder="Tytuł">
            </div>
            <div class="col-md-4 mb-3 mx-auto">
                <label for="validationCustom02">Opis</label>
                <input name="description" type="text" class="form-control" id="validationCustom02" placeholder="Opis">
            </div>
        </div>
        <div class="form-row mx-auto">
            <select name="category" class="custom-select" style="margin: 5px">
                <option value="">Wybierz Kategorie</option>
                <?php
                foreach ($categories as $category) {
                    print '<option value="' . $category . '">' . $category . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="form-row mx-auto" style="margin: 5px">
            <label class="mx-auto" for="validationCustom02">Lub utwórz nową:</label>
            <input name="newCategory" type="text" class="form-control" id="validationCustom02"
                   placeholder="Nowa Kategoria">
        </div>
        <button class="btn btn-primary" style="margin: 5px" type="submit">Dodaj Zdjęcie!</button>
    </form>
    </div>


<div class="col-8" style="margin-bottom:10px; background: lightgray; border-radius: 5px">
    <div class="card-columns">

        <?php
        foreach ($records_array as $row) {
            print'
<div class="card"  style="margin: 10px; max-width:200px; min-width:100px; background: ' . $category_color[$row["Category"]] . '">
  <img class="card-img-top" src="' . $row['Src'] . '" alt="Card image cap" width="100px" height="200px" oncontextmenu="return false;">
  <div class="card-body" style="align-content: center">
    <h6 class="card-title">' . $row['Title'] . '</h6>
    <h7 class="card-text" style="color: gray">' . $row['Category'] . '</h7>
    <p class="card-text">' . $row['Description'] . '</p>

  </div style="margin-top: 10px">
      <div class="card-footer" style=":hover{background: lightgray}">
      <small class="text-muted"> 
      <div class="container text-center">
          
       <button class="btn btn-secondary btn-sm" onclick=\'
       $(this).prop("disabled", true);
       $(this).hide();
       $(this).parent().append(function(){
      return "<form action=\\"/gallery\\" method=\\"post\\">"+
      "<button style=\\"font-size: 12px; margin: 5px\\" class=\\"btn btn-secondary\\" name=\\"removePicture\\" value=\\"' . $row['Src'] . '\\">Usuń Zdjęcie</button>"+
      "<button style=\\"font-size: 12px; margin: 5px\\" class=\\"btn btn-secondary\\" name=\\"removeCategory\\" value=\\"' . $row['Category'] . '\\">Usuń Kategorie</button>"+
      "<input type=\\"text\\" name=\\"changeCategory\\" style=\\"font-size: 12px; margin: 5px\\" class=\\"form-control\\" placeholder=\\"Nowa Kategoria\\">"+
      "<input type=\\"text\\" name=\\"changeDescription\\" style=\\"font-size: 12px; margin: 5px\\" class=\\"form-control\\" placeholder=\\"Nowy opis\\">"+
      "<button type=\\"submit\\" name=\\"changedPictureSrc\\" value=\\"'.$row['Src'].'\\" style=\\"font-size: 10px; margin: 5px\\" class=\\"btn btn-secondary\\">Aktualizuj Kategorie i Opis</button>"+
      "</form>";
    });
       
       \'>Edit</button></small>
   </div>
</div>
</div>

';
        } ?>
    </div>
    </div>

</div>