<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="stylesheet/style.css">
</head>
<body>
    
    <?php include_once "views/includes/header.inc.php" ;?>


    <input type="button" value="get all games." id="btn">
    <div id="output"></div>

    <script src="views/jquery.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#btn").on("click", function() {
                $.get("api/api_call.php", null, function(data) {
                    let output = "";
                    data.forEach(function(car) {
                        output += '<span>' + car["marca"] +  " " + car["model"] + '</span><br>';
                    });

                    $("#output").html(output);

                }, "json");
            });
        });
    </script>

</body>
</html>