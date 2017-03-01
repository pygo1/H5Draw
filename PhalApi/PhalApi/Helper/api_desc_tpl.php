<?php
echo <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{$service} - 在线接口文档</title>

    <link rel="stylesheet" href="https://staticfile.qnssl.com/semantic-ui/2.1.6/semantic.min.css">
    <link rel="stylesheet" href="https://staticfile.qnssl.com/semantic-ui/2.1.6/components/table.min.css">
    <link rel="stylesheet" href="https://staticfile.qnssl.com/semantic-ui/2.1.6/components/container.min.css">
    <link rel="stylesheet" href="https://staticfile.qnssl.com/semantic-ui/2.1.6/components/message.min.css">
    <link rel="stylesheet" href="https://staticfile.qnssl.com/semantic-ui/2.1.6/components/label.min.css">
    <script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
</head>

<body>

<br /> 

    <div class="ui text container" style="max-width: none !important;">
        <div class="ui floating message">

EOT;

echo "<h2 class='ui header'>接口：$service</h2><br/> <span class='ui teal tag label'>$description</span>";

echo <<<EOT
            <div class="ui raised segment">
                <span class="ui red ribbon label">接口说明</span>
                <div class="ui message">
                    <p>{$descComment}</p>
                </div>
            </div>
            <h3>接口参数</h3>
            <table class="ui red celled striped table" >
                <thead>
                    <tr><th>参数名字</th><th>类型</th><th>是否必须</th><th>默认值</th><th>其他</th><th>说明</th></tr>
                </thead>
                <tbody>
EOT;

foreach ($rules as $key => $rule) {
    $name = $rule['name'];
    if (!isset($rule['type'])) {
        $rule['type'] = 'string';
    }
    $type = isset($typeMaps[$rule['type']]) ? $typeMaps[$rule['type']] : $rule['type'];
    $require = isset($rule['require']) && $rule['require'] ? '<font color="red">必须</font>' : '可选';
    $default = isset($rule['default']) ? $rule['default'] : '';
    if ($default === NULL) {
        $default = 'NULL';
    } else if (is_array($default)) {
        $default = json_encode($default);
    } else if (!is_string($default)) {
        $default = var_export($default, true);
    }

    $other = '';
    if (isset($rule['min'])) {
        $other .= ' 最小：' . $rule['min'];
    }
    if (isset($rule['max'])) {
        $other .= ' 最大：' . $rule['max'];
    }
    if (isset($rule['range'])) {
        $other .= ' 范围：' . implode('/', $rule['range']);
    }
    $desc = isset($rule['desc']) ? trim($rule['desc']) : '';

    echo "<tr><td>$name</td><td>$type</td><td>$require</td><td>$default</td><td>$other</td><td>$desc</td></tr>\n";
}

echo <<<EOT
                </tbody>
            </table>
            <h3>返回结果</h3>
            <table class="ui green celled striped table" >
                <thead>
                    <tr><th>返回字段</th><th>类型</th><th>说明</th></tr>
                </thead>
                <tbody>
EOT;

foreach ($returns as $item) {
	$name = $item[1];
	$type = isset($typeMaps[$item[0]]) ? $typeMaps[$item[0]] : $item[0];
	$detail = $item[2];
	
	echo "<tr><td>$name</td><td>$type</td><td>$detail</td></tr>";
}

$version = PHALAPI_VERSION;

echo <<<EOT
            </tbody>
        </table>

        <div class="ui raised segment">
            <span class="ui red ribbon label">在线调用</span>
            <div class="ui message">
                <label>
                    地址: <input id="callHref" type="text" style="width:70%;">
                </label>
                <select id="callMethod">
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                </select>
                <button id="call">调用</button>
            </div>
            <div class="ui message">
                <p>参数 (example:{name:'abc',pwd:'123'}):</p>
                <textarea id="param" style="width:100%;"></textarea>
            </div>
            <div class="ui message">
                <p>返回:</p>
                <ul id="list">
                </ul>
            </div>
        </div>
        <p>&copy; Powered  By <a href="http://www.wuhanhope.com/" target="_blank">武汉浩谱 v{$version}</a> <p>
        </div>
    </div>
    <script>
        $(function(){
            var href = window.location.href;
            var callHref = href.replace("checkApiParams.php","");
            $("#callHref").val(callHref);
            $("#call").click(function(){
            var param = $("#param").val() =="" ? "{}": eval('(' + $("#param").val() + ')');
                $.ajax({
					url:$("#callHref").val(),
					type:$("#callMethod").val(),
					dataType:"json",
					data:param,
					success:succFunction
				})

            })
            var jsonStr="";
            function succFunction(json) {
                jsonStr="";
                $("#list").html('');
                eachJson("JSON",json);
                $("#list").html(jsonStr);
            }
            function eachJson(name,json){
                jsonStr+=name+":<br/>{<br>";
                $.each(json, function (index, item) {
                    if(typeof(item) == 'object'){
                        eachJson(index,item);
                    }else{
                        jsonStr+=index+":"+item+'<br>';
                    }
                });
                jsonStr+="}<br>";
            }
        })
    </script>
</body>
</html>
EOT;


