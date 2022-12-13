<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        .drop-zone {
            background-color: white;
            min-height: min(20vw,200px);
            min-width: min(20vh,250px);
            border: 1px dashed darkgray;
            background-image: url("upload.svg");
            background-repeat: no-repeat;
            background-size:contain;
            background-position: center center;
        }
        .img-container, .drop-zone{
            display:flex;
            justify-content: center;
            align-items: center;
        }
    </style>
    <title>Document</title>
</head>

<body>
    <main class='container'>
        <?php
        require_once 'utils.php';
        $MAX_FILE_SIZE = 8_000_000;
        $MIME_TYPE_GENERATOR = [
            'image' => fn ($file_name, $file_basename) =>
            "<image src='$file_name' alt='$file_basename' />",

            'video' => fn ($file_name, $file_basename) =>
            "<video controls src='$file_name'></video>",

            'audio' => fn ($file_name, $file_basename) =>
            "<audio controls src='$file_name'></audio>",
        ];
        $file = $_FILES['uploadedFile'] ?? null;
        $error = FileUtils::upload_file_html($file, $MAX_FILE_SIZE, $MIME_TYPE_GENERATOR, $file_name, $content);
        if ($error !== null) {
            echo "<div class='alert alert-danger'>Error while uploading file";
            if ($file_basename = $file['name'] ?? null) {
                echo " '", $file_basename, "'";
            }
            echo ': ', $error, "</div>";
        } elseif($content) {
            echo "<div class='jumbotron img-container'>", $content, "</div>";
        }
        ?>
        <form class='jumbotron' method="post" enctype="multipart/form-data">
            <label for='finput' class="form-label">Soubor ke stažení:</label>
            <input class='form-control' id="finput" type="file" name="uploadedFile" accept='audio/*,video/*,image/*' required />
            <div id='drop_zone' data-for='finput' class='drop-zone text-primary h3'>
               Drop file here
            </div>
            <input type="submit" class="btn btn-primary mb-3" />
        </form>
        <script type="text/javascript">
            const dropZone = document.getElementById("drop_zone");
            const input = document.getElementById(dropZone.getAttribute("data-for"));
            dropZone.addEventListener("drop", function(e) {
                e.preventDefault();
                input.files = e.dataTransfer.files;
            });
            dropZone.addEventListener("dragover", e => e.preventDefault());
        </script>
    </main>
</body>

</html>