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
    public function __construct($token, $debug = FALSE) {
        parent::__construct($token, $debug);
        $this->basic_text = "欢迎关注「真三国志」，小真志在提供最给力的三国人物查询～\r\n";
        $this->help_text = "回复１，即可随机获得一个三国人物介绍。\r\n
        回复２，随机获得一个三国女性人物介绍。\r\n
        回复０，查看帮助信息。\r\n
        回复一个人名（如'赵云'）或表字(如'子龙')，获取对应的三国人物。";
    }

    protected function setDb($host, $dbname, $user, $pass) {
        $text = sprintf("mysql:host=%s;dbname=%s", $host, $dbname);
        $this->conn = new PDO($text, $user, $pass, [PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8']);
    }

    protected function sendPerson($person) {
        $text = sprintf('%s(%s-%s) ', $person['name'], $person['live_year'], $person['die_year']);
        $text .= empty($person['style_name']) ? "\r\n" : '，字'.$person['style_name']."。\r\n";
        $text .= empty($person['native_place']) ? '' : '籍贯：'.$person['native_place']."\r\n";
        $text .= empty($person['office']) ? '' : '称谓：' . $person['office']."\r\n";
        $has_history = !empty($person['history_dpt']);
        $has_novel = !empty($person['novel_dpt']);
        if($has_history) {
            $text  .=  "历史简介：\r\n" . $person['history_dpt']. "\r\n";
        }
        else if($has_novel){
            $text  .=  "演义简介：\r\n[" . $person['novel_dpt']. "\r\n";
        }
        $skillText = sprintf("假想能力：\r\n武力%s, 智力%s, 统率%s, 政治%s, 魅力%s", $person['wl'], $person['zl'], $person['ts'], $person['zz'], $person['ml']);
        $text .= $skillText;
        $this->responseText($text);
    }


    /**
     * 用户关注时触发，回复「欢迎关注」
     *
     * @return void
     */
    protected function onSubscribe() {
        $this->responseText($this->basic_text. $this->$help_text);
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

       $this->responseText("「悄悄的我走了，正如我悄悄的来；我挥一挥衣袖，不带走一片云彩。」\r\n请问您有什么不满意的地方，可加微信号xfight10000直接反馈，小真会做得更好～");

    }

    /**
     * 上报地理位置时触发,回复收到的地理位置
     *
     * @return void
     */
    protected function onEventLocation() {
        // $this->responseText('收到了位置推送：' . $this->getRequest('Latitude') . ',' . $this->getRequest('Longitude'));
    }

    /**
     * 收到文本消息时触发，回复收到的文本消息内容
     *
     * @return void
     */
    protected function onText() {
        $content = trim($this->getRequest('content'));
        $this->setDb('127.0.0.1',  'sanguo', 'root', 'wgmmla');
        switch($content) {
            case '1'://随机发送一个人物
                $sql = 'SELECT name, style_name, sex, ts, wl, zl, zz, ml, native_place,
          history_dpt, novel_dpt, assessment, office, live_year, die_year
FROM `person` AS t1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM `person`)) AS id) AS t2
WHERE t1.id >= t2.id and (t1.history_dpt is not null or t1.novel_dpt is not null)
ORDER BY t1.id ASC LIMIT 1';
                $results = $this->conn->query($sql);
                $person = $results->fetch();
                $this->sendPerson($person);
                break;

//             case '2'://随机发送一个女性人物
//                 $sql = 'SELECT name, style_name, sex, ts, wl, zl, zz, ml, native_place,
//           history_dpt, novel_dpt, assessment, office, live_year, die_year
// FROM `person` AS t1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM `person`)) AS id) AS t2
// WHERE t1.id >= t2.id and (t1.history_dpt is not null or t1.novel_dpt is not null) and t1.sex = 2
// ORDER BY t1.id ASC LIMIT 1';
//                 $results = $this->conn->query($sql);
//                 $person = $results->fetch();
//                 $this->sendPerson($person);
//                 break;

            // case '0'://获取帮助信息
            //     $this->responseText($this->help_text);
            //     break;

            default:
                $name = $content;
                $sql = sprintf("select name, style_name, sex, ts, wl, zl, zz, ml, native_place,
              history_dpt, novel_dpt, assessment, office, live_year, die_year
              from person where name like '%s' or alias like '%s'  limit 1 or style_name = '%s' ", $name . '%', $name . '%', $name );
                $results = $this->conn->query($sql);
                if ($results->rowCount() == 0) {
                    $this->responseText("Sorry,小真没有查询到你要搜索的人物,请输入正确的人名。\r\n回复１可以随机获得一个人物介绍。");
                }
                else {
                    $person = $results->fetch();
                    $this->sendPerson($person);
                }
                break;
        }



    }

    /**
     * 收到图片消息时触发，回复由收到的图片组成的图文消息
     *
     * @return void
     */
    protected function onImage() {
        // $items = array(new NewsResponseItem('标题一', '描述一', $this->getRequest('picurl'), $this->getRequest('picurl')), new NewsResponseItem('标题二', '描述二', $this->getRequest('picurl'), $this->getRequest('picurl')),);

        // $this->responseNews($items);
    }

    /**
     * 收到地理位置消息时触发，回复收到的地理位置
     *
     * @return void
     */
    protected function onLocation() {
        // $num = 1 / 0;

        // // 故意触发错误，用于演示调试功能

        // $this->responseText('收到了位置消息：' . $this->getRequest('location_x') . ',' . $this->getRequest('location_y'));
    }

    /**
     * 收到链接消息时触发，回复收到的链接地址
     *
     * @return void
     */
    protected function onLink() {
        // $this->responseText('收到了链接：' . $this->getRequest('url'));
    }

    /**
     * 收到语音消息时触发，回复语音识别结果(需要开通语音识别功能)
     *
     * @return void
     */
    protected function onVoice() {
        // $this->responseText('收到了语音消息,识别结果为：' . $this->getRequest('Recognition'));
    }

    /**
     * 收到自定义菜单消息时触发，回复菜单的EventKey
     *
     * @return void
     */
    protected function onClick() {
        // $this->responseText('你点击了菜单：' . $this->getRequest('EventKey'));
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