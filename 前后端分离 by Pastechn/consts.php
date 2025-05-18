<?php

$aff=31993; # aff号
$bwh_domain="bwh81.net"; # 搬瓦工域名，不带https://

$promo_code = [
    [
        'code' => 'BWHCGLUKKB',
        'discount' => 6.77,
        'name' => '优惠码'
    ]/*,
    [
        'code' => 'BWHNY2022',
        'discount' => 12.22,
        'name' => '节日优惠码'
    ]*/
];

# 运作模式相关
$cart_fetch_url = "https://bwh88.net/cart.php"; # 此处不可使用bwh81.net，会出错！建议国外vps使用bandwagonhost.com，国内vps使用bwh88.net
$instant_mode = false; # 即时模式，设为true则访客每访问/刷新一次页面，服务器会向搬瓦工抓取一次数据，显示即时的库存。false为缓存模式。建议使用缓存模式。
$expire = 60; # 非即时模式/缓存模式下，库存数据的过期时间，单位是秒，例如设为180就是3分钟过期。
$cart_filename = "cart.txt"; # 非即时模式/缓存模式下，保存库存的文件名，默认即可。务必记得修改php文件所属用户/组为PHP所使用用户组(比如www)，否则上传PHP文件默认为root会报错！

# 网站相关
$site_title="搬瓦工 - 即时库存检测"; # 标题，会显示在首页顶部卡片
$say_something="<a href=\"https://github.com/yeyingorg/php_bwh_stock_checker\" target=\"_blank\">点此获取本页面源码</a>"; # 副标题
$refer_root='https://example.com/Pastechn/bwg.php'; # 指向后端页面的地址 请务必记得同时修改index.html里158行的路径。

?>
