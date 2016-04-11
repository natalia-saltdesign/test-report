<?php

$_SERVER["DOCUMENT_ROOT"] = "/home/s/saltdesign/bitbiz/public_html";
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


if (CModule::IncludeModule("sale") and CModule::IncludeModule("catalog") and CModule::IncludeModule("main"))
{

	$arFilter = array("GROUPS_ID" => $arParams["USER_GROUP"]);
	$arParam = array("EMAIL");
	$to='';
	$dbUsers = CUser::GetList($by = 'ID', $order = 'ASC', $arFilter, $arParam);
	while ($arUsers = $dbUsers->Fetch()) {
		$to .= ', '.$arUsers["EMAIL"];
	}
	if (strlen($to)>0)
	{
		$to = substr($to,2);
		$subject = 'Отчёт о товарах с продажами не более '.$arParams["MIN_SALES"].' шт. за '.$arParams["PERIOD"].' дней';
		$message = '
		<html>
		<head>
		<title>'.$subject.'</title>
		<style>
			div {border-bottom: 1px dotted #b0b0b0; padding:10px 0;}
			div th {font-size:10px; font-weight:400;}
			th, td {text-align:center;}
	        </style>
		</head>
		<body>';

		$dDate = ConvertTimeStamp((time()-$arParams["PERIOD"]*(24*60*60)),"FULL");

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
			if ($arItems["QUANTITY"] <= $arParams["MIN_SALES"] ) {
				$arSelectedSales["PRODUCT_ID"][$arItems["PRODUCT_ID"]]=$arItems["PRODUCT_ID"];
				$arSelectedSales["TOTAL_QUANTITY"][$arItems["PRODUCT_ID"]]=$arItems["QUANTITY"];
	 		}
		}

		$arSelectFields = array("PRODUCT_ID","PRICE","QUANTITY","DATE_UPDATE"); 
		foreach ($arSelectedSales["PRODUCT_ID"] as &$SaleItem) {
			$mesProduct='';
			$arProd = CCatalogProduct::GetByIDEx($SaleItem);

			$mesProduct .= '<div>';
			$mesProduct .= 'ID товара: '.$SaleItem["PRODUCT_ID"].'<br />';
			$mesProduct .= 'Товар: <a href="http://'.SITE_SERVER_NAME.$arProd["DETAIL_PAGE_URL"].'">'.$arProd["NAME"].'</a><br />';
			$mesProduct .= 'Артикул: '.$arProd["PROPERTIES"]["CML2_ARTICLE"]["VALUE"].'<br />';       
			$mesProduct .= '<table><tr><th>Дата продажи</th><th>Количество</th><th>Цена</th></tr>';    

			$dbBasketItems = CSaleBasket::GetList(array("ORDER_ID" => "ASC"), array("PRODUCT_ID" => $SaleItem), false, false, $arSelectFields);
			while ($arItems = $dbBasketItems->Fetch()) {
				$mesProduct .= '<tr><td>'.$arItems["DATE_UPDATE"].'</td><td>'.$arItems["QUANTITY"].'</td><td>'.round((float)$arItems["PRICE"],2).'</td></tr>';   
			}

			$mesProduct .= '</table><br />';    
			$mesProduct .= 'Всего продано: '.$arSelectedSales["TOTAL_QUANTITY"][$SaleItem].'<br /><br />';   
			$mesProduct .= '</div>';
                	$message    .= $mesProduct;

		}
	 

		$message .= '</body></html>';

		$verify = mail($to,$subject, $message,"MIME-Version: 1.0\r\nContent-type:text/html; Charset=utf-8\r\n","");

        } 

}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>