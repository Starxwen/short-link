<!--
BY：云猫
Time：2023.10.2
QQ：3522934828
-->
<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <meta name="viewport"
    content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=2.0, user-scalable=no, width=device-width">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>小猫咪短链接生成器</title>
<script src="js/jquery.js"></script>
<link rel="stylesheet" href="https://cdn.staticfile.org/layui/2.5.6/css/layui.min.css" media="all">
<script src="//cdn.bootcss.com/layer/2.3/layer.js"></script>
<script>
$(function(){
    re = /http/;
    $("#b").click(function(){
        if(re.test($('#t').val())){
            $.post("add.php",{'url':$('#t').val()},function(data,status){
                $('#aaa').html("<input class='layui-input' type='text' value='"+data+"' />" );
            });
        }else{
            alert("链接不规范，必须使用http://或https://开头");
        }
    });
});


</script>
</head>
<center>
<body class="center-vh" style="background-image: url(1.jpg); background-size: cover;">
<div class="layui-form-item">

    <h1>短链接在线生成</h1>
    <input class='layui-input' type='text' id='t' placeholder="请输入你的长链接" value='' /></br>
    <p><button class="layui-btn layui-btn-primary" id='b'>生成短链接</button></p>
    <p id='aaa'></p>
    </div>
</center>
<center><div class="layui-trans layadmin-user-login-footer" style="color: red;">
      <p>© 2023 <a href="/" target="_blank" style="color: red;">星跃云</a></p></center>
    </div>
</body>

</html>