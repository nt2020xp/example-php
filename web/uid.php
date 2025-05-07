<?php
/**
 * 生成随机的央视频投频助手 UID
 * 
 * @param int $length UID 长度 (默认16位)
 * @return string 生成的UID
 */
function generateCCTVUid($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $uid = '';
    
    // 确保长度至少为8位
    $length = max(8, $length);
    
    for ($i = 0; $i < $length; $i++) {
        $uid .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $uid;
}

// 使用示例
$uid = generateCCTVUid();
echo "生成的央视频投频助手UID: " . $uid . "\n";

// 生成多个示例
echo "\n生成5个示例UID:\n";
for ($i = 0; $i < 5; $i++) {
    echo ($i+1) . ". " . generateCCTVUid() . "\n";
}
?>
