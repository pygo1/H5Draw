<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="css/defaults.css">
    <link rel="stylesheet" href="css/jquery.Jcrop.css">
    <script src="http://lib.sinaapp.com/js/jquery/1.9.1/jquery-1.9.1.min.js"></script>
    <script src="js/jquery.Jcrop.js"></script>
</head>
<body>
<?php
    $imgSrc = $_POST['img'];
    echo '<img style="width:100%;height:100%;display:block;" id="img" src='.$imgSrc.'>';
?>
<form id="coords" class="coords" onsubmit="return false;" action="http://example.com/post.php">
<div class="inline-labels">
<label>X1 <input type="text" size="4" id="x1" name="x1" /></label>
<label>Y1 <input type="text" size="4" id="y1" name="y1" /></label><br/>
<label>X2 <input type="text" size="4" id="x2" name="x2" /></label>
<label>Y2 <input type="text" size="4" id="y2" name="y2" /></label><br/>
<label>W <input type="text" size="4" id="w" name="w" /></label>
<label>H <input type="text" size="4" id="h" name="h" /></label><br/>
</div>
</form>
<button id="submit" style="z-index: 99999;position: fixed;top:0;right:0;">保存</button>
</body>
<script>
$(function(){
    var jcrop_api;
    var api = $.Jcrop('#img',{
        onChange:   showCoords,
        onSelect:   showCoords,
        onRelease:  clearCoords
    },function(){
        jcrop_api = this;
    });
    $('#coords').on('change','input',function(e){
        var x1 = $('#x1').val(),
            x2 = $('#x2').val(),
            y1 = $('#y1').val(),
            y2 = $('#y2').val();
        jcrop_api.setSelect([x1,y1,x2,y2]);
    });

    // Simple event handler, called from onChange and onSelect
    // event handlers, as per the Jcrop invocation above
    function showCoords(c)
    {
        $('#x1').val(c.x);
        $('#y1').val(c.y);
        $('#x2').val(c.x2);
        $('#y2').val(c.y2);
        $('#w').val(c.w);
        $('#h').val(c.h);
    };

    function clearCoords()
    {
        $('#coords input').val('');
    };
    $("#submit").click(function(){
        $.ajax({
            url:"http://192.168.0.110:82/hpwebservice/?service=Default.editimg",
            type:"post",
            dataType:"json",
            crossDomain: true,
            data:{
                x1:$('#x1').val(),
                y1:$('#y1').val(),
                w:$('#w').val(),
                h:$('#h').val(),
                source:"<?php echo $_POST['img'] ?>"
            },

            success:function(response){

            }
        })
    })
})
</script>
</html>