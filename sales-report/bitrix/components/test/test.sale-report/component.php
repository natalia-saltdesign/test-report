<?
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @param array $arParams
 * @param CBitrixComponent $this
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

// умолчания
if (!isset($arParams["USER_GROUP"])) {$arParams["USER_GROUP"]=array(1,11);}	// группы пользователей, которым виден отчёт
if (!isset($arParams["MIN_SALES"])) {$arParams["MIN_SALES"]=2;}			// минимально количество 
if (!isset($arParams["PERIOD"])) {$arParams["PERIOD"]=7;}			// период в днях

$arResult=array();

if (CSite::InGroup($arParams["USER_GROUP"]) and CModule::IncludeModule("sale") and CModule::IncludeModule("catalog"))
{
	$arResult["MIN_SALES"]=$arParams["MIN_SALES"];
	$arResult["PERIOD"]=$arParams["PERIOD"];
	$dDate = ConvertTimeStamp((time()-$arParams["PERIOD"]*(24*60*60)),"FULL");

	$arOrdersList=array();
	$arFilter = Array(">=DATE_INSERT" => $dDate);
	// заказы проверяются только по дате; можно добавить проверку статуса
	$arSales = CSaleOrder::GetList(array(), $arFilter);
	while ($arSalesItem = $arSales->Fetch()) {
		$arOrdersList[]=$arSalesItem["ID"];
	}

	$arSelectedSales = array ();
	$arFilter = array("ORDER_ID" => $arOrdersList);
	$arGroupBy = array("PRODUCT_ID", "SUM" => "QUANTITY");
	$arSelectFields = array("PRODUCT_ID"); 
	// группировку и фильтрацию по сумме в одном вызове CSaleBasket::GetList получить не удалось,
	// поэтому фильтрация выполняется отдельно в другой массив
	$dbBasketItems = CSaleBasket::GetList(array(), $arFilter, $arGroupBy, false, $arSelectFields);
	while ($arItems = $dbBasketItems->Fetch()) {
		if ($arItems["QUANTITY"] <= $arParams["MIN_SALES"] ) {
			$arSelectedSales["PRODUCT_ID"][$arItems["PRODUCT_ID"]]=$arItems["PRODUCT_ID"];
			$arSelectedSales["TOTAL_QUANTITY"][$arItems["PRODUCT_ID"]]=$arItems["QUANTITY"];
 		}
	}

	// наименование, артикул и детальная страница берутся из каталога
	// для получения поля артикула варианты типа CCatalogProduct::GetList(array(), array("ID" => $arSelectedSales["PRODUCT_ID"]), false, false, array("PROPERTY_CML2_ARTICLE") );не сработали
	// поэтому используется CCatalogProduct::GetByIDEx
	$arSelectFields = array("PRODUCT_ID","PRICE","QUANTITY","DATE_UPDATE"); 
	foreach ($arSelectedSales["PRODUCT_ID"] as &$SaleItem) {
		$arProd = CCatalogProduct::GetByIDEx($SaleItem);
                $arResult["REPORT"][$SaleItem]["PRODUCT_ID"]=$SaleItem;
                $arResult["REPORT"][$SaleItem]["NAME"]=$arProd["NAME"];
                $arResult["REPORT"][$SaleItem]["DETAIL_PAGE_URL"]=$arProd["DETAIL_PAGE_URL"];
                $arResult["REPORT"][$SaleItem]["ARTICLE"]=$arProd["PROPERTIES"]["CML2_ARTICLE"]["VALUE"];

		// цена загружается из корзины - именно та, которая была во время заказа, поэтому добавила расшифровку по времени заказа
		// здесь можно сделать также вариант одним запросом, указав в фильтре весь список PRODUCT_ID, но поскольку остальные параметры определяются в цикле, оставила этот вариант
		$dbBasketItems = CSaleBasket::GetList(array("ORDER_ID" => "ASC"), array("PRODUCT_ID" => $SaleItem), false, false, $arSelectFields);
		while ($arItems = $dbBasketItems->Fetch()) {
                	$arResult["REPORT"][$SaleItem]["SALES"][]=array("DATE_UPDATE" => $arItems["DATE_UPDATE"], "QUANTITY" => $arItems["QUANTITY"], "PRICE" => round((float)$arItems["PRICE"],2));
		}
                $arResult["REPORT"][$SaleItem]["TOTAL_QUANTITY"]=$arSelectedSales["TOTAL_QUANTITY"][$SaleItem];

	}
	 
}

$this->IncludeComponentTemplate();
