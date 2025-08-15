<?php
// 错误报告设置
error_reporting(0);
ini_set('display_errors', 0);

// 支持的图片尺寸
$size_arr = ['large', 'mw1024', 'mw690', 'bmiddle', 'small', 'thumb180', 'thumbnail', 'square'];

// 处理图片ID参数
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // 直接使用传入的图片ID
    $sina_img = trim($_GET['id']);
    
    // 清理ID - 移除可能存在的扩展名和特殊字符
    $sina_img = preg_replace('/\..+$/', '', $sina_img); // 移除文件扩展名
    $sina_img = preg_replace('/[^a-zA-Z0-9_\-]/', '', $sina_img); // 只保留字母数字和短横线
    
} else {
    // 读取文本文件获取随机图片
    $file_path = 'LuoTianyi/wpimg.txt';
    if (!file_exists($file_path)) {
        header("HTTP/1.1 500 Internal Server Error");
        die("Image database not found");
    }
    
    $str = explode("\n", file_get_contents($file_path));
    $str = array_filter($str); // 移除空行
    if (empty($str)) {
        header("HTTP/1.1 500 Internal Server Error");
        die("No images available");
    }
    
    // 随机选择图片
    $k = array_rand($str);
    $sina_img = trim($str[$k]);
    
    // 清理随机ID
    $sina_img = preg_replace('/\..+$/', '', $sina_img);
    $sina_img = preg_replace('/[^a-zA-Z0-9_\-]/', '', $sina_img);
}

// 处理尺寸参数
$size = 'large'; // 默认尺寸
if (isset($_GET['size']) && in_array($_GET['size'], $size_arr)) {
    $size = $_GET['size'];
}

// 生成新浪图片URL（支持多种格式）
$server = rand(1, 4);
$original_url = "https://tva{$server}.sinaimg.cn/{$size}/{$sina_img}.jpg";

// 使用百度代理解决防盗链
$proxy_url = "https://image.baidu.com/search/down?url=" . urlencode($original_url);

// 返回结果处理
$result = ["code" => "200", "imgurl" => $proxy_url];

// 处理返回类型
$type = isset($_GET['return']) ? $_GET['return'] : '';

// 添加缓存控制头（1小时缓存）
header("Cache-Control: public, max-age=3600");

switch ($type) {
    case 'json':
        // 获取图片信息
        $image_info = @getimagesize($original_url);
        if ($image_info) {
            $result['width'] = $image_info[0];
            $result['height'] = $image_info[1];
            $result['mime'] = $image_info['mime'];
        } else {
            $result['width'] = "unknown";
            $result['height'] = "unknown";
            $result['mime'] = "image/jpeg";
        }
        
        // 返回JSON
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
        
    default:
        // 重定向到图片
        header("Location: " . $result['imgurl']);
        break;
}