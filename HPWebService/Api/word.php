<?php
/**
 * 默认接口服务类
 *
 * @author: dogstar <chanzonghuang@gmail.com> 2014-10-04
 */

class Api_word extends PhalApi_Api {

    public function getRules() {
        return array(
            'index' => array(),
        );
    }

    /**
     * 默认接口服务
     * * @desc 生成word文档
     * @return string info 生成信息
     * @return string version 版本，格式：X.X.X
     * @return int time 当前时间戳
     */
    public function index() {
        $domain1 = new Domain_AdvancedTable();
        $domain2 = new Domain_BasicTable();
        $domain3 = new Domain_HeaderFooter();
        $domain4 = new Domain_Image();
        $domain5 = new Domain_Link();
        $domain6 = new Domain_ListItem();
        $domain7 = new Domain_Object();
        $domain8 = new Domain_Section();
        $domain9 = new Domain_Template();
        $domain10 = new Domain_Text();
        $domain11 = new Domain_TextRun();
        $domain12 = new Domain_TitleTOC();
        $domain13 = new Domain_WaterMake();
        $info[] = $domain1->getAdvancedTable();
        $info[] = $domain2->getBasicTable();
        $info[] = $domain3->getHeaderFooter();
        $info[] = $domain4->getImage();
        $info[] = $domain5->getLink();
        $info[] = $domain6->getListItem();
        $info[] = $domain7->getObject();
        $info[] = $domain8->getSection();
        $info[] = $domain9->getTemplate();
        $info[] = $domain10->getText();
        $info[] = $domain11->getTextRun();
        $info[] = $domain12->getTitleTOC();
        $info[] = $domain13->getWaterMake();
        return array(
            'info' => $info,
            'version' => PHALAPI_VERSION,
            'time' => $_SERVER['REQUEST_TIME'],
        );
    }
}
