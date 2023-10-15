<?php

/*
PHP搬瓦工即时库存检测aff站
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

if ( $instant_mode ) {
    $cart = file_get_contents($cart_fetch_url);
} else {

    if ( is_file($cart_filename) ) { 
        $cart = null;
        if ( ( time() - filemtime($cart_filename) ) > $expire ) {
            $cart = file_get_contents($cart_fetch_url);
            if ( !empty($cart) ) {
                unlink($cart_filename);
                $file_cart = fopen($cart_filename, "w");
                fwrite($file_cart, $cart);
                fclose($file_cart);
            } 
        }
        if ( empty($cart) ) {
            $file_cart = fopen($cart_filename, "r");
            $cart = fread($file_cart, filesize($cart_filename));
            fclose($file_cart);
        }
    } else {
        $cart = file_get_contents($cart_fetch_url);
        if ( !empty($cart) ) {
            $file_cart = fopen($cart_filename, "w");
            fwrite($file_cart, $cart);
            fclose($file_cart);
        }
    }

}

$tmp=strstr($cart, '<div class="cartbox">');
$tmp=explode('<p align="center">',$tmp);
$tmp=$tmp[0];
$tmp=str_replace("</div>\n",'',$tmp);
$cart_explode=explode('<div class="cartbox">',$tmp);

$plan_count=count($cart_explode) - 1;

$plans=array();

for ($i=1; $i<=$plan_count; $i++) {

    # name
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
    $tmp=str_replace('OSAKA','大阪',$tmp);
    $tmp=str_replace('DUBAI - ECOMMERCE','杜拜',$tmp);
    $tmp=str_replace('DUBAI','杜拜',$tmp);
    $tmp=str_replace(' ECOMMERCE','-E',$tmp);
    $tmp=str_replace('LIMITED EDITION','限量版',$tmp);
    $tmp=str_replace('JAPAN','日本 (软银)',$tmp); # SPECIAL 10G KVM PROMO V5 - JAPAN LIMITED EDITION # 该套餐已下架...
    $tmp=str_replace('SYDNEY','悉尼',$tmp);
    $tmp=str_replace('香港 85','香港 85 (CMI)',$tmp);
    $plan['name']=$tmp;

    # ssd
    $tmp=explode('SSD: ',$cart_explode[$i]);
    $tmp=explode('<br/>',$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace(' RAID-10','',$tmp);
    $tmp=str_replace('GB','',$tmp);
    $tmp=trim($tmp) . " GB";
    $plan['ssd']=$tmp;

    # ram
    $tmp=explode('RAM: ',$cart_explode[$i]);
    $tmp=explode('<br/>',$tmp[1]);
    $tmp=$tmp[0];
    if (strpos($tmp,'MB') !== false) {
        $tmp=str_replace('MB','',$tmp);
        $tmp=round(trim($tmp / 1024),2) . " GB";
    } elseif (strpos($tmp,'GB') !== false) {
        $tmp=str_replace('GB','',$tmp);
        $tmp=trim($tmp) . " GB";
    }
    $plan['ram']=$tmp;

    # cpu
    $tmp=explode('CPU: ',$cart_explode[$i]);
    $tmp=explode('<br/>',$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace('Intel Xeon','',$tmp);
    $tmp=trim($tmp);
    $plan['cpu']=$tmp;

    # transfer
    $tmp=explode('Transfer: ',$cart_explode[$i]);
    $tmp=explode('<br/>',$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace('/mo','',$tmp);
    if (strpos($tmp,'GB') !== false) {
        $tmp=str_replace('GB','',$tmp);
        $tmp=round(trim($tmp / 1000),2) . " TB";
    } elseif (strpos($tmp,'TB') !== false) {
        $tmp=str_replace('TB','',$tmp);
        $tmp=trim($tmp) . " TB";
    }
    $plan['transfer']=$tmp;

    # linkspeed
    $tmp=explode('Link speed: ',$cart_explode[$i]);
    $tmp=explode('<br/>',$tmp[1]);
    $tmp=$tmp[0];
    $tmp=str_replace('Gigabit','Gb',$tmp);
    $plan['linkspeed']=$tmp;

    # pricing
    $tmp=explode('pricing textcenter"> ',$cart_explode[$i]);
    $tmp=explode("<br/>\n</td>",$tmp[1]);
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

    # pid
    if (strpos($cart_explode[$i],"/cart.php?a=add&pid=") !== false) {
        $tmp=explode('/cart.php?a=add&pid=',$cart_explode[$i]);
        $tmp=explode("'",$tmp[1]);
        $tmp=$tmp[0];
        $pid=$tmp;
    }

    # link and stock
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
