<?php

/*
搬瓦工自动即时库存检测
作者: 夜桜
https://github.com/yeyingorg/php_bwh_stock_checker
*/

// ---------- 配置部分 ----------

# 搬瓦工相关
$aff=31993; #aff号
$bwh_domain="bwh81.net"; #点击跳转的搬瓦工域名，不带https://

$promo_code="BWH3HYATVBJW"; #优惠码
$promo_percentage="6.58%"; #优惠码百分比(带%)

$special_promo_code=""; #节日优惠码 没有则不填
$special_promo_percentage=""; #节日优惠码百分比(带%)

# 网站相关
$site_title="搬瓦工全方案 即时库存检测";
$site_since_year="2018";
$say_something="<a href=\"https://github.com/yeyingorg/php_bwh_stock_checker\" target=\"_blank\">点此获取本页面源码</a>";

// ---------- 配置部分结束 ----------

$get_id = isset($_GET['id']) ? $_GET['id'] : null;
$get_promo_code = isset($_GET['promo_code']) ? $_GET['promo_code'] : null;

if (is_numeric($get_id)) {
    header("HTTP/1.1 303 See Other");
    if ($get_id == 0) {
        header("location: https://$bwh_domain/aff.php?aff=$aff");
    } elseif ($get_id == 1) {
        header("location: https://$bwh_domain/aff.php?aff=$aff&gid=1");
    } else {
        header("location: https://$bwh_domain/aff.php?aff=$aff&pid=" . $get_id);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo $site_title;?></title>
<style type="text/css">
table#border{
border-top:#000 1px solid;
border-left:#000 1px solid;
}
table#border td{
border-bottom:#000 1px solid;
border-right:#000 1px solid;
}
table#border tr{
height:50px;
}
</style>
</head>
<body style="width: 99%; font-size: 20px;">

<?php
$cart = file_get_contents("https://bwh88.net/cart.php"); # 此处不可使用bwh81.net，会出错！建议国外vps使用bandwagonhost.com，国内vps使用bwh88.net

$tmp=strstr($cart, '<div class="cartbox">');
$tmp=explode('<p align="center">',$tmp);
$tmp=$tmp[0];
$tmp=str_replace("</div>\n",'',$tmp);
$cart_explode=explode('<div class="cartbox">',$tmp);

$plan_count=count($cart_explode) - 1;

$plans=array();

for ($i=1; $i<=$plan_count; $i++) {

    #name
    $tmp=explode('<nobr>',$cart_explode[$i]);
    $tmp=explode('</nobr>',$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace('SPECIAL ','',$tmp);
    $tmp=str_replace('KVM - PROMO ','',$tmp);
    $tmp=str_replace('KVM PROMO V3 - LOS ANGELES - ','',$tmp);
    $tmp=str_replace('KVM PROMO V5 - LOS ANGELES - ','',$tmp);
    $tmp=str_replace('KVM PROMO V5 - ','',$tmp);
    $tmp=str_replace(' VPS','',$tmp);
    $tmp=str_replace('- HIBW','大流量',$tmp);
    $tmp=str_replace('HONG KONG','香港',$tmp);
    $tmp=str_replace('TOKYO','东京',$tmp);
    $tmp=str_replace('DUBAI - ECOMMERCE','杜拜',$tmp);
    $tmp=str_replace(' ECOMMERCE','-E',$tmp);
    $tmp=str_replace('LIMITED EDITION','限量版',$tmp);
    $tmp=str_replace('JAPAN','日本(软银)',$tmp); # SPECIAL 10G KVM PROMO V5 - JAPAN LIMITED EDITION # 其实这个套餐已经下架了加不加这条无所谓吧...
    $tmp=str_replace('香港 85','香港 85 (CMI)',$tmp);
    $plan['name']=$tmp;

    #ssd
    $tmp=explode('SSD: ',$cart_explode[$i]);
    $tmp=explode('<br />',$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace(' RAID-10','',$tmp);
    $tmp=str_replace(' GB','GB',$tmp); #fix
    $tmp=str_replace('GB',' GB',$tmp); #fix
    $plan['ssd']=$tmp;

    #ram
    $tmp=explode('RAM: ',$cart_explode[$i]);
    $tmp=explode('<br />',$tmp[1]);
    $tmp=$tmp[0];
    #for ($x=1; $x<=64; $x*=2) { #convert
    #    $tmp=str_replace(1024*$x,$x,$tmp);
    #}
    for ($x=1; $x<=64; $x++) { #convert
        $tmp=str_replace(1024*$x,$x,$tmp);
    }
    $tmp=str_replace('MB','GB',$tmp); #convert
    $plan['ram']=$tmp;

    #cpu
    $tmp=explode('CPU: ',$cart_explode[$i]);
    $tmp=explode('<br />',$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace(' Intel Xeon','',$tmp);
    $plan['cpu']=$tmp;

    #transfer
    $tmp=explode('Transfer: ',$cart_explode[$i]);
    $tmp=explode('<br />',$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace('/mo','',$tmp);
    for ($x=1; $x<=16; $x++) { #convert
        $tmp=str_replace(1000*$x,$x,$tmp);
    }
    $tmp=str_replace('500','0.5',$tmp); #convert
    $tmp=str_replace('GB','TB',$tmp); #convert
    $plan['transfer']=$tmp;

    #linkspeed
    $tmp=explode('Link speed: ',$cart_explode[$i]);
    $tmp=explode('<br />',$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace('Gigabit','Gb',$tmp);
    $plan['linkspeed']=$tmp;

    #pricing
    $tmp=explode('pricing textcenter"> ',$cart_explode[$i]);
    $tmp=explode("<br />\n</td>",$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace(' ','',$tmp);
    $tmp=str_replace("\n",'',$tmp);
    $tmp=str_replace('USD','',$tmp);
    $tmp=str_replace('Monthly','/月',$tmp);
    $tmp=str_replace('Quarterly','/季度',$tmp);
    $tmp=str_replace('Semi-Annually','/半年',$tmp);
    $tmp=str_replace('Annually','/年',$tmp);
    $tmp=explode('<br/>',$tmp);
    if (count($tmp) == 1) {
        $tmp = $tmp[0];
    } elseif (count($tmp) == 2) {
        $tmp = $tmp[0] . '<br />' . $tmp[1];
    } elseif (count($tmp) == 3) {
        $tmp = $tmp[0] . '<br />' . $tmp[2];
    } elseif (count($tmp) == 4) {
        $tmp = $tmp[0] . '<br />' . $tmp[3];
    }
    $plan['pricing']=$tmp;

    #pid
    if (strpos($cart_explode[$i],"/cart.php?a=add&pid=") != false) {
        $tmp=explode('/cart.php?a=add&pid=',$cart_explode[$i]);
        $tmp=explode("'",$tmp[1]);
        $tmp=$tmp[0];
        $plan['pid']=$tmp;
    }

    #link and stock
    if (empty($plan['pid'])) {
        $plan['link']='无货';
        $plan['stock']='<span style="color: black;">无货</span>';
    } else {
        $plan['link']='<a href="?id=' . $plan['pid'] . '" target="_blank">' . $plan['name'] . '</a>';
        $plan['stock']='<span style="color: red;">有货</span>';
    }

    #promo_code_pricing
    $promo_price = false;
    if (in_array($get_promo_code, array(1,2))) {
        if ($get_promo_code == 1 && is_numeric(str_replace("%",'',$promo_percentage))) {
            $percentage=str_replace("%",'',$promo_percentage) * 0.01;
            $promo_price = true;
        } elseif ($get_promo_code == 2 && is_numeric(str_replace("%",'',$special_promo_percentage))) {
            $percentage=str_replace("%",'',$special_promo_percentage) * 0.01;
            $promo_price = true;
        }

        if ($promo_price) {

            $tmp=$plan['pricing'];

            if (strpos($plan['pricing'],"<br />") != false) {
                $tmp=explode('<br />',$tmp);

                $tmp[0]=str_replace('$','',$tmp[0]);
                $tmp[1]=str_replace('$','',$tmp[1]);
                $tmp[0]=explode('/',$tmp[0]);
                $tmp[1]=explode('/',$tmp[1]);
                $tmp[0][0]=$tmp[0][0] - round($tmp[0][0] * $percentage, 2);
                $tmp[1][0]=$tmp[1][0] - round($tmp[1][0] * $percentage, 2);
                $tmp[0][0]=number_format($tmp[0][0], 2, '.', '');
                $tmp[1][0]=number_format($tmp[1][0], 2, '.', '');
                $tmp[0]='$' . $tmp[0][0] . '/' . $tmp[0][1];
                $tmp[1]='$' . $tmp[1][0] . '/' . $tmp[1][1];

                $tmp=$tmp[0] . '<br />' . $tmp[1];
            } else {
                $tmp=str_replace('$','',$tmp);
                $tmp=explode('/',$tmp);
                $tmp[0]=$tmp[0] - round($tmp[0] * $percentage, 2);
                $tmp[0]=number_format($tmp[0], 2, '.', '');
                $tmp='$' . $tmp[0] . '/' . $tmp[1];
            }

            $plan['pricing']='<span style="color: red;">' . $tmp . '</span>';
        }
    }

    $plans[]=$plan;
    unset($plan);
}

$plans_limited_edition=array();
$plans_hk=array();
$plans_tokyo=array();
$plans_dubai=array();
$plans_cn2_gia=array();
$plans_cn2=array();
$plans_general=array();
$plans_others=array();

foreach ($plans as $i => $plan){
    if (strpos($plan['name'],"限量版") != false) {
        $plans_limited_edition[]=$plan;
    }
}
foreach ($plans as $i => $plan){
    if (strpos($plan['name'],"香港") != false) {
        $plans_hk[]=$plan;
        unset($plans[$i]);
    }
}
foreach ($plans as $i => $plan){
    if (strpos($plan['name'],"东京") != false) {
        $plans_tokyo[]=$plan;
        unset($plans[$i]);
    }
}
foreach ($plans as $i => $plan){
    if (strpos($plan['name'],"杜拜") != false) {
        $plans_dubai[]=$plan;
        unset($plans[$i]);
    }
}
foreach ($plans as $i => $plan){
    if (strpos($plan['name'],"CN2 GIA") != false || strpos($plan['name'],"CN2 GIA-E") != false) {
        $plans_cn2_gia[]=$plan;
        unset($plans[$i]);
    }
}
foreach ($plans as $i => $plan){
    if (strpos($plan['name'],"CN2") != false) {
        $plans_cn2[]=$plan;
        unset($plans[$i]);
    }
}
foreach ($plans as $i => $plan){
    if (substr($plan['name'], -1) == "G") {
        $plans_general[]=$plan;
        unset($plans[$i]);
    }
}

$plans_others[]=$plan;
unset($plan);

$plans_cheapest=array();
$plans_cheapest[]=$plans_general[0];
$plans_cheapest[]=$plans_cn2[0];
$plans_cheapest[]=$plans_cn2_gia[0];
$plans_cheapest[]=$plans_hk[0];
$plans_cheapest[]=$plans_tokyo[0];
$plans_cheapest[]=$plans_dubai[0];

?>

<div align="center">
<h1><?php echo $site_title;?></h1>

<!-- OwO -->

<div>
<p><?php echo $say_something;?></p>
<p><?php if (!empty($promo_code) && !empty($promo_percentage)) {
    if ($get_promo_code == 1 && $promo_price) {
        echo "优惠码 <span style=\"color: red;\">$promo_code</span> - $promo_percentage" . ' <a href="?promo_code=0">显示原价</a>';
    } else {
        echo "优惠码 $promo_code - $promo_percentage" . ' <a href="?promo_code=1">显示优惠价</a>';
    }
}

if (!empty($promo_code) && !empty($promo_percentage) && !empty($special_promo_code) && !empty($special_promo_percentage)) {
    echo "<br />";
}

if (!empty($special_promo_code) && !empty($special_promo_percentage)) {
    if ($get_promo_code == 2 && $promo_price) {
        echo "节日优惠码 <span style=\"color: red;\">$special_promo_code</span> - $special_promo_percentage" . ' <a href="?promo_code=0">显示原价</a>';
    } else {
        echo "节日优惠码 $special_promo_code - $special_promo_percentage" . ' <a href="?promo_code=2">显示优惠价</a>';
    }
}?></p>
<table width="95%" id="border" border="0" cellspacing="0" align="center" style="text-align: center;">
<?php
$tr_dict = [
    "limited_edition" => "限量版",
    "cheapest" => "最便宜",
    "general" => "普通",
    "cn2" => "CN2",
    "cn2_gia" => "GIA",
    "hk" => "香港",
    "tokyo" => "东京",
    "dubai" => "杜拜",
    "others" => "其他",
];
foreach ( array_keys($tr_dict) as $e ) {
    echo '<tr style="font-weight: bold;"><td>';
    foreach ($tr_dict as $k => $v){
        if ( $e == $k ) {
            echo "<span id=\"plans_${k}\" style=\"color: red;\">${v}</span>";
        } else {
            echo "<a href=\"#plans_${k}\">${v}</a>";
        }
        if ( $k != end(array_keys($tr_dict)) ) {
            echo "|";
        }
    }
    echo "</td><td>SSD</td><td>RAM</td><td>CPU</td><td>月流量</td><td>端口</td><td>价格</td><td>购买链接</td><td>库存</td></tr>";   
    foreach (${"plans_" . $e} as $plan){
        echo '<tr><td>' . $plan['name'] . '</td><td>' . $plan['ssd'] . '</td><td>' . $plan['ram'] . '</td><td>' . $plan['cpu'] . '</td><td>' . $plan['transfer'] . '</td><td>' . $plan['linkspeed'] . '</td><td>' . $plan['pricing'] . '</td><td>' . $plan['link'] . '</td><td>' . $plan['stock'] . '</td></tr>';
    }
}
?>
</table>
</div>

<br />
<div>&copy; <?php echo $site_since_year . "-" . date('Y');?> <a href="https://<?php echo $_SERVER['HTTP_HOST'];?>/"><?php echo $_SERVER['HTTP_HOST'];?></a>. Powered by PHP & HTML5. Hosted by <a href="?id=0" target="_blank">BandwagonHost</a>.</div>
</div>
</body>
</html>
