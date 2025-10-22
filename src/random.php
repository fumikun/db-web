<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <p id="hoge">JS:</p>
    <script>
        document.getElementById("hoge").innerHTML += Math.floor(Math.random() * 6);
    </script>
    <p>PHP:
        <?php
        echo rand(0, 5), "\n";
        ?>
    </p>
</body>

</html>