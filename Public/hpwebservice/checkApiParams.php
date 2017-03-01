<?php
/**
 * 工具 - 查看接口参数规则
 */

require_once dirname(__FILE__) . '/../init.php';

//装载你的接口
DI()->loader->addDirs('HPWebService');

$apiDesc = new PhalApi_Helper_ApiDesc();
$apiDesc->render();

