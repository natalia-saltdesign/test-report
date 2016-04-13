<?php

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__));
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
set_time_limit(0);

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

// умолчания ///////////////////
$arParams = Array(
		"USER_GROUP"=>array(11), 
		"PERIOD"=>7,
		"MIN_SALES" => 3
	);

////////////////////////////////

$EventName="TEST_REPORT";
$Site="s1";

if (CModule::IncludeModule("sale") and CModule::IncludeModule("catalog") and CModule::IncludeModule("main"))
{

	$arFilter = array("GROUPS_ID" => $arParams["USER_GROUP"]);
	$arParam = array("EMAIL");
	$arTo=array();
	$dbUsers = CUser::GetList($by = 'ID', $order = 'ASC', $arFilter, $arParam);
	while ($arUsers = $dbUsers->Fetch()) {
		$arTo[]=$arUsers["EMAIL"];
	}
	$to=implode(",", $arTo);
	if (strlen($to)>0)
	{

	$arResult=array();

	$arResult["MIN_SALES"]=$arParams["MIN_SALES"];
	$arResult["PERIOD"]=$arParams["PERIOD"];
	$dDate = ConvertTimeStamp((time()-$arParams["PERIOD"]*(24*60*60)),"FULL");

        // список товаров из каталога
	$arProdCat = array ();
	$arSelectFields = Array("CATALOG_GROUP_1","PROPERTY_CML2_ARTICLE","DETAIL_PAGE_URL","NAME","IBLOCK_SECTION_ID");
	$elProducts = CIBlockElement::GetList (Array("ID" => "ASC"), Array("IBLOCK_ID" => 6, "ACTIVE" => "Y"), false, false, $arSelectFields);
	while ($arProduct = $elProducts->Fetch()) {

                $arResult["REPORT"][$arProduct["ID"]]["PRODUCT_ID"]=$arProduct["ID"];
                $arResult["REPORT"][$arProduct["ID"]]["NAME"]=$arProduct["NAME"];
                $arResult["REPORT"][$arProduct["ID"]]["DETAIL_PAGE_URL"]=str_replace("#ELEMENT_ID#",$arProduct["ID"],str_replace("#SECTION_ID#",$arProduct["IBLOCK_SECTION_ID"],$arProduct["DETAIL_PAGE_URL"]));
                $arResult["REPORT"][$arProduct["ID"]]["ARTICLE"]=$arProduct["PROPERTY_CML2_ARTICLE_VALUE"];
                $arResult["REPORT"][$arProduct["ID"]]["PRICE"]=$arProduct["CATALOG_PRICE_1"];
                $arResult["REPORT"][$arProduct["ID"]]["SALES"]=0;
	}

	// заказы

	$arOrdersList=array();
	$arFilter = Array(">=DATE_INSERT" => $dDate);
	$arSales = CSaleOrder::GetList(array(), $arFilter);
	while ($arSalesItem = $arSales->Fetch()) {
		$arOrdersList[]=$arSalesItem["ID"];
	}

	$arSelectedSales = array ();
	$arFilter = array("ORDER_ID" => $arOrdersList);
	$arGroupBy = array("PRODUCT_ID", "SUM" => "QUANTITY");
	$arSelectFields = array("PRODUCT_ID"); 
	$dbBasketItems = CSaleBasket::GetList(array(), $arFilter, $arGroupBy, false, $arSelectFields);
	while ($arItems = $dbBasketItems->Fetch()) {
                $arResult["REPORT"][$arItems["PRODUCT_ID"]]["SALES"]=$arItems["QUANTITY"];

	}

	$message = '
	<html>
		<head><title>Непродаваемые товары</title></head>
		<body>
		<table>
			<tr><th>ID</th><th>Наименование</th><th>Артикул</th><th>Цена</th><th>Продано</th></tr>';

	foreach ($arResult["REPORT"] as &$SaleItem) {
		if ($SaleItem["SALES"]<=$arResult['MIN_SALES']) { 
			$message.='<tr><td>'.$SaleItem["PRODUCT_ID"]
				.'</td><td><a href="'.$SaleItem["DETAIL_PAGE_URL"].'" target="_blank">'.$SaleItem["NAME"].'</a></td><td>'
				.$SaleItem["ARTICLE"].'</td><td align=right>'
				.$SaleItem["PRICE"].'</td><td align=right>'
				.$SaleItem["SALES"].'</td></tr>';
		}
	}

	$message .= '</table></body></html>';
	$arEventFields = array("EMAIL_TO" => $to, "MIN_SALES" => $arParams["MIN_SALES"], "PERIOD" => $arParams["PERIOD"], "MESSAGE"  => $message);
        $res=CEvent::Send($EventName, $Site, $arEventFields);
	print_r($res);

        } 

}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>