<?php
//error_reporting(0);//抑制所有错误信息
header('Content-Type:text/html; charset=utf-8');//设置编码
date_default_timezone_set("Asia/Shanghai");//设置时区
/**
 * Medoo类的说明文档
 * https://medoo.lvtao.net/1.2/doc.php
 */
require_once './Medoo.php';
use Medoo\Medoo;//命名空间

$KuaiTie = new KuaiTie();
$type    = @$_REQUEST['type'];
$text    = @$_REQUEST['text'];
$id      = @$_REQUEST['id'];

if ($type == 1) {
    //http://192.168.5.100/Kt_Web/index.php?type=1&text=a啊1!
    echo $KuaiTie->Set($text) ? '成功' : '失败';
} elseif ($type == 2) {
    //http://192.168.5.100/Kt_Web/index.php?type=2
    echo $KuaiTie->Clear($id);
} else {
    //http://192.168.5.100/Kt_Web/index.php
    $html = file_get_contents('./view.html');
    $var  = $KuaiTie->View();
    if (count($var) === 0) {
        echo str_replace('{$data}', '暂无数据', $html);
    } else {
        $data = '';
        foreach ($var as $key => $value) {
            $data .= '
            <ul class="list-group" style="margin:10px;" id="b' . $value["id"] . '">
                <li class="list-group-item text-right"><strong>' . $value["date"] . '</strong></li>
                <li class="list-group-item" id="a' . $value["id"] . '">' . nl2br($value["text"]) . '</li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-xs-4"><input class="btn btn-warning" style="width: 100%;" type="button" value="删除" onclick="Del_Button_Click(' . $value["id"] . ');"/></div>
                        <div class="col-xs-8"><input class="btn btn-primary" style="width: 100%;" type="button" value="复制内容" data-clipboard-target="#a' . $value["id"] . '" onclick="Copy_Button_Click(' . $value["id"] . ');"/></div>
                    </div>
                </li>
            </ul>';
        }
        echo str_replace('{$data}', $data, $html);
    }
}

/**
 * 数据库操作 class
 * 张雷 2020年3月13日01:58:27
 */
class KuaiTie
{
    private $obj;

    public function __construct()
    {
        require_once './config.php';
        $this->obj = new Medoo([
            // required
            'database_type' => $DB_CONNECTION,
            'database_name' => $DB_DATABASE,
            'server'        => $DB_HOST,
            'username'      => $DB_USERNAME,
            'password'      => $DB_PASSWORD,
            // [optional]
            'charset'       => 'utf8',
            'port'          => $DB_PORT,
        ]);
        //var_dump($this->obj->info());
    }

    public function Set($var = null)
    {
        $result = $this->obj->insert('data', [
            'date' => date('Y-m-d H:i:s'),
            'text' => $var,
        ]);
        return $result;
    }

    public function Clear($id = null)
    {
        if (empty($id)) {
            $result = $this->obj->delete('data', []); //[]清空所有数据.
        } else {
            $result = $this->obj->delete('data', ['id' => $id]); //清空指定ID数据
        }
        return $result->rowCount();
    }

    public function View()
    {
        $result = $this->obj->select("data", "*", ["ORDER" => ["id" => "DESC"]]);
        return $result;
    }
}