<?php

/*
搬瓦工自动即时库存检测
作者: 夜桜
https://github.com/yeyingorg/php_bwh_stock_checker
*/

// ---------- 配置部分 ----------

require('consts.php');

// ---------- 配置部分结束 ----------

$get_id = isset($_GET['id']) ? $_GET['id'] : null;

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

$cart = file_get_contents("https://bwh88.net/cart.php"); # 此处不可使用bwh81.net，会出错！建议国外vps使用bandwagonhost.com

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
    $tmp=str_replace(' ECOMMERCE','-E',$tmp);
    $tmp=str_replace('LIMITED EDITION','限量版',$tmp);
    $tmp=str_replace('JAPAN','日本(软银)',$tmp); # SPECIAL 10G KVM PROMO V5 - JAPAN LIMITED EDITION # 其实这个套餐已经下架了加不加这条无所谓吧...
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
    $tmp=str_replace('$','',$tmp);
    $tmp=explode('<br/>',$tmp);
    if (count($tmp) == 1) {
        $tmp = array($tmp[0]);
    } elseif (count($tmp) == 2) {
        $tmp = array($tmp[0],$tmp[1]);
    } elseif (count($tmp) == 3) {
        $tmp = array($tmp[0],$tmp[2]);
    } elseif (count($tmp) == 4) {
        $tmp = array($tmp[0],$tmp[3]);
    }
    $plan['pricing']=[];
    foreach ($tmp as $single) {
        $tmp2 = explode('/', $single);
        array_push($plan['pricing'], ['cycle'=>$tmp2[1],'pricing'=>floatval($tmp2[0])]);
    }

    #pid
    if (strpos($cart_explode[$i],"/cart.php?a=add&pid=") !== false) {
        $tmp=explode('/cart.php?a=add&pid=',$cart_explode[$i]);
        $tmp=explode("'",$tmp[1]);
        $tmp=$tmp[0];
        $pid=$tmp;
    }

    #link and stock
    if (empty($pid)) {
        $plan['link']=null;
        $plan['stock']=false;
    } else {
        $plan['link']=$refer_root . '?id=' . $pid;
        $plan['stock']=true;
    }

    $plans[]=$plan;
    unset($plan);
    unset($pid);
}
echo(json_encode(array(
    'title' => $site_title,
    'banner' => $say_something,
    'promo' => $promo_code,
    'plans' => $plans
), JSON_UNESCAPED_UNICODE));

?>
