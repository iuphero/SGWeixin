<?php

/**
 * 微信公众平台 PHP SDK 示例文件
 *
 * @author NetPuter <netputer@gmail.com>
 */

require ('lib/wechat.class.php');


/**
 * 微信公众平台演示类
 */
class MyWechat extends Wechat
{

    protected function setDb($host, $dbname, $user, $pass) {
        $text = sprintf("mysql:host=%s;dbname=%s", $host, $dbname);
        $this->conn = new PDO($text, $user, $pass, [PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8']);
    }

    /**
     * 用户关注时触发，回复「欢迎关注」
     *
     * @return void
     */
    protected function onSubscribe() {
        $this->responseText('欢迎关注');
    }

    /**
     * 用户已关注时,扫描带参数二维码时触发，回复二维码的EventKey (测试帐号似乎不能触发)
     *
     * @return void
     */
    protected function onScan() {
        $this->responseText('二维码的EventKey：' . $this->getRequest('EventKey'));
    }

    /**
     * 用户取消关注时触发
     *
     * @return void
     */
    protected function onUnsubscribe() {

        // 「悄悄的我走了，正如我悄悄的来；我挥一挥衣袖，不带走一片云彩。」

    }

    /**
     * 上报地理位置时触发,回复收到的地理位置
     *
     * @return void
     */
    protected function onEventLocation() {
        $this->responseText('收到了位置推送：' . $this->getRequest('Latitude') . ',' . $this->getRequest('Longitude'));
    }

    /**
     * 收到文本消息时触发，回复收到的文本消息内容
     *
     * @return void
     */
    protected function onText() {
        $content = $this->getRequest('content');
        if ($content == '1') {
             //随机发送一个人物
            $text = 'hello';
            $this->responseText($text);

        } else {
            // $this->responseText('Today is a good day');
            $name = $content;
            $this->setDb('127.0.0.1',  'sanguo', 'xfight', 'wgmmla');
            $sql = sprintf("select name, style_name, sex, ts, wl, zl, zz, ml, native_place,
          history_dpt, novel_dpt, assessment, office, live_year, die_year
          from person where name like '%s' or alias like '%s'  limit 1", $name . '%', $name . '%' );
            $results = $this->conn->query($sql);
            if ($results->rowCount() == 0) {
                $this->responseText('Sorry,小真没有查询到你要搜索的人物');
            }
            else {
                $person = $results->fetch();
                $text = '%s(%s-%s)';
                $text .= empty($person['style_name']) ? '' : '字['.$person['style_name'].']';
                $this->responseText($text);
            }
        }

    }

    /**
     * 收到图片消息时触发，回复由收到的图片组成的图文消息
     *
     * @return void
     */
    protected function onImage() {
        $items = array(new NewsResponseItem('标题一', '描述一', $this->getRequest('picurl'), $this->getRequest('picurl')), new NewsResponseItem('标题二', '描述二', $this->getRequest('picurl'), $this->getRequest('picurl')),);

        $this->responseNews($items);
    }

    /**
     * 收到地理位置消息时触发，回复收到的地理位置
     *
     * @return void
     */
    protected function onLocation() {
        $num = 1 / 0;

        // 故意触发错误，用于演示调试功能

        $this->responseText('收到了位置消息：' . $this->getRequest('location_x') . ',' . $this->getRequest('location_y'));
    }

    /**
     * 收到链接消息时触发，回复收到的链接地址
     *
     * @return void
     */
    protected function onLink() {
        $this->responseText('收到了链接：' . $this->getRequest('url'));
    }

    /**
     * 收到语音消息时触发，回复语音识别结果(需要开通语音识别功能)
     *
     * @return void
     */
    protected function onVoice() {
        $this->responseText('收到了语音消息,识别结果为：' . $this->getRequest('Recognition'));
    }

    /**
     * 收到自定义菜单消息时触发，回复菜单的EventKey
     *
     * @return void
     */
    protected function onClick() {
        $this->responseText('你点击了菜单：' . $this->getRequest('EventKey'));
    }

    /**
     * 收到未知类型消息时触发，回复收到的消息类型
     *
     * @return void
     */
    protected function onUnknown() {
        $this->responseText('收到了未知类型消息：' . $this->getRequest('msgtype'));
    }


}

$wechat = new MyWechat('xfight10000', TRUE);
$wechat->run();