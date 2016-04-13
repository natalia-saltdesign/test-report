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
	// группировку и фильтрацию по сумме в одном вызове CSaleBasket::GetList получить не удалось,
	// в $arResult записываются все продажи; фильтр по количеству - при выводе в шаблоне (для упрощения)
	// также можно сделать вариант с сортировкой результата (задать порядок сортировки в параметрах компонента), и с удалением лишних элементов
	$dbBasketItems = CSaleBasket::GetList(array(), $arFilter, $arGroupBy, false, $arSelectFields);
	while ($arItems = $dbBasketItems->Fetch()) {
                $arResult["REPORT"][$arItems["PRODUCT_ID"]]["SALES"]=$arItems["QUANTITY"];

	}
	 
}

$this->IncludeComponentTemplate();
