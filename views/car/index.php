<head>
    <link rel="stylesheet" href="../../stylesheet/style.css">
</head>
<?php require_once "../includes/header.inc.php";?>

<div id="output">

</div>

<script src="../../views/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $.get("../../api/api_call.php", null, function(data) {
            let output = "";
            data.forEach(function(car) {
                output += '<span>' + car["marca"] +  " " + car["model"] + " " + car["numar"] + " <input type=\"button\" value=\"delete...\" class=\"delete_btn\" data-car-numar=" + car["numar"] + " data-car-id=" + car["id"] + "></button> " + "update part here" + '</span><br>';
            });
            $("#output").html(output);
        }, "json");

        $("#output").on("click", ".delete_btn", function() {
            let id = $(this).data("car-id");
            let numar = $(this).data("car-numar");
            if(!confirm("Do you want to delete the car with number plates: " + numar + ".")) return;

            //delete request

        });
    });
</script>
