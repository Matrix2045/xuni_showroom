<?php

use think\Request;
// 公共助手函数

if (!function_exists('p')){
    /**
     * 打印数组
     * @author 王春志
     * @date 2019/11/29
     */
    function p($arr) {
        header('content-type:text/html;charset=utf-8');
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
}
// 公共助手函数

if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int    $size      大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int    $time   时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }
}

if (!function_exists('human_date')) {

    /**
     * 获取语义化时间
     * @param int $time  时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }
}

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string  $url    资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $url = preg_match($regex, $url) ? $url : \think\Config::get('upload.cdnurl') . $url;
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}


if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param    string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }
}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest   目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }
}

if (!function_exists('addtion')) {

    /**
     * 附加关联字段数据
     * @param array $items  数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields) {
            return $items;
        }
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model'];
            } else {
                $model = $v['name'] ? \think\Db::name($v['name']) : \think\Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = $model->where($primary, 'in', $ids[$v['field']])->column("{$primary},{$v['column']}");
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $v[$fieldsArr[$n]['display']] = implode(',', array_intersect_key($result[$n], $curr));
                }
            }
        }
        return $items;
    }
}

if (!function_exists('var_export_short')) {

    /**
     * 返回打印数组结构
     * @param string $var    数组
     * @param string $indent 缩进字符
     * @return string
     */
    function var_export_short($var, $indent = "")
    {
        switch (gettype($var)) {
            case "string":
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : var_export_short($key) . " => ")
                        . var_export_short($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "TRUE" : "FALSE";
            default:
                return var_export($var, true);
        }
    }
}

if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" alignment-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}

if (!function_exists('V')) {
    /**
     * 返回JSON通一格式
     * @author phpstorm
     * @date 2019/12/18
     */
    function V($code = 0, $msg = '', $data = '') {
        return array('code' => $code, 'msg' => $msg, 'data' => $data);
    }
}

if (!function_exists('url_avatar')){
    /**
     *  判断图片是否带有域名
     * @author phpstorm
     * @date 2020/1/2
     */
    function url_avatar($url){
        if ($url){
            $preg = "/^http(s)?:\\/\\/.+/";
            if(preg_match($preg,$url))
            {
                return $url;
            }else
            {

                return Request::instance()->domain().$url;
            }
        }else{
            return Request::instance()->domain().'/header.png';
        }

    }
}
if (!function_exists('sendSms')) {
    /**
     * 发送验证码
     * @param $mobile string 手机号
     * @param $content string 内容
     * @return array
     * @date 2020/1/13
     */
    function sendSms($mobile, $content)
    {
        //参数赋值
        $name = 'ln_showroom';//账号
        $password = 'lnkj0707';//密码
        $seed = date("YmdHis");//当前时间
        $dest = $mobile;//手机号码
        $content = '【鸿海收藏品】' . $content;//短信内容
        $ext = '';//扩展号码
        $reference = '';//参考信息
        $enCode = 'UTF-8';//编码（UTF-8、GBK）
        $method = 'GET';//请求方式（POST、GET）
        $url = '';
        if ($enCode == 'UTF-8') {
            $url = 'http://160.19.212.218:8080/eums/utf8/send_strong.do';//UTF-8编码接口地址
        } else if ($enCode == 'GBK') {
            $url = 'http://160.19.212.218:8080/eums/send_strong.do';//GBK编码接口地址
        }
        $content = encoding($content, $enCode);//注意编码，字段编码要和接口所用编码一致，有可能出现汉字之类的记得转换编码

        //请求参数
        //utf8和gbk编码请自行转换
        $params = array(
            'name' => encoding($name, $enCode),//帐号，由网关分配
            'seed' => $seed,//当前时间，格式：YYYYMMDD HHMMSS，例如：20130806102030。客户时间早于或晚于网关时间超过30分钟，则网关拒绝提交。
            //从php5.1.0开始，PHP.ini里加了date.timezone这个选项，并且默认情况下是关闭的也就是显示的时间（无论用什么php命令）都是格林威治标准时间和我们的时间（北京时间）差了正好8个小时。
            //找到php.ini中的“;date.timezone =”这行，将“;”去掉，改成“date.timezone = PRC”（PRC：People's Republic of China 中华人民共和国），重启Apache，问题解决。
            'key' => md5(md5($password) . $seed),//md5( md5(password)  +  seed) )
            //其中“+”表示字符串连接。即：先对密码进行md5加密，将结果与seed值合并，再进行一次md5加密。
            //两次md5加密后字符串都需转为小写。
            //例如：若当前时间为2013-08-06 10:20:30，密码为123456，
            //则：key=md5(md5(“123456”) + “20130806102030” )
            //则：key=md5(e10adc3949ba59abbe56e057f20f883e20130806102030)
            //则：key= cd6e1aa6b89e8e413867b33811e70153
            'dest' => $dest,//手机号码（多个号码用“半角逗号”分开），GET方式每次最多100个号码，POST方式号码个数不限制，但建议不超过3万个
            'content' => $content,//短信内容。最多500个字符。
            'ext' => $ext,//扩展号码（视通道是否支持扩展，可以为空或不填）
            'reference' => $reference//参考信息（最多50个字符，在推送状态报告、推送上行时，推送给合作方，本参数不参与任何下行控制，仅为合作方提供方便，可以为空或不填）如果不知道如何使用，请忽略该参数，不能含有半角的逗号和分号。
        );
        // echo $method.'请求如下：<br/>';
        if ($method == 'POST') {
            $resp = send_post_curl($url, $params);//POST请求，数据返回格式为error:xxx,success:xxx
        } else if ($method == 'GET') {
            $resp = send_get($url, $params);//GET请求，数据返回格式为error:xxx,success:xxx
        }
        $response = explode(':', $resp);
        $code = $response[1];//响应代码

        if ($response[0] == 'success') {
            return V(1, '短信发送成功');
        } else {
            return V(0, $code);
        }
    }
}
if (!function_exists('send_get')) {
    function encoding($str, $urlCode)
    {
        if (!empty($str)) {
            $fileType = mb_detect_encoding($str, array('UTF-8', 'GBK', 'LATIN1', 'BIG5'));
        }
        return mb_convert_encoding($str, $urlCode, $fileType);
    }
}
if (!function_exists('send_get')) {
    /**
     * get请求
     * @param $url
     * @param $params
     * @return bool|false|string
     * @date 2020/1/13
     */
    function send_get($url, $params)
    {
        $getdata = http_build_query($params);
        $content = file_get_contents($url . '?' . $getdata);
        return $content;
    }

}


if (!function_exists('send_post_curl')) {
    /**
     * post请求
     * @param $url
     * @param $params
     * @return bool|string
     * @date 2020/1/13
     */
    function send_post_curlsend_post_curl($url, $params)
    {

        $postdata = http_build_query($params);
        $length = strlen($postdata);
        $cl = curl_init($url);//①：初始化
        curl_setopt($cl, CURLOPT_POST, true);//②：设置属性
        curl_setopt($cl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($cl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-length: " . $length));
        curl_setopt($cl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($cl);//③：执行并获取结果
        curl_close($cl);//④：释放句柄
        return $content;
    }
}