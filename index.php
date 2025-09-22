<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    


    <input type="button" value="get all games." id="btn">



    <div id="output"></div>

    <script src="views/jquery.js" type></script>
    <script>
        $(document).ready(function() {
            $("#btn").on("click", function() {
                $.get("api/api_call.php", { id: }, function(data) {
                    $("#output").html("<b>" + data["name"] + "</b> and is in stock? <b>" + (data["in_stock"] ? "true" : "false") + "</b>.<br>", "json");
                });
            });
        });
    </script>

</body>
</html>