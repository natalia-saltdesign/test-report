<?
/**
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
?>


<?
echo '<p>'.GetMessage('PERIOD').' '.$arResult['PERIOD'].'</p>';
echo '<p>'.GetMessage('MIN_SALES').' '.$arResult['MIN_SALES'].'</p>';

foreach ($arResult["REPORT"] as &$SaleItem) {
	echo '<div class="sales">';
	echo GetMessage('PRODUCT_ID').' '.$SaleItem["PRODUCT_ID"].'<br />';
	echo GetMessage('PRODUCT_NAME').' <a href="'.$SaleItem["DETAIL_PAGE_URL"].'" target="_blank">'.$SaleItem["NAME"].'</a><br />';
	echo GetMessage('ARTICLE').' '.$SaleItem["ARTICLE"].'<br />';       
	echo '<table><tr><th>'.GetMessage('DATE_UPDATE').'</th><th>'.GetMessage('QUANTITY').'</th><th>'.GetMessage('PRICE').'</th></tr>';    

	foreach ($SaleItem["SALES"] as &$Sales) {
		echo '<tr><td>'.$Sales["DATE_UPDATE"].'</td><td>'.$Sales["QUANTITY"].'</td><td>'.$Sales["PRICE"].'</td></tr>';   
	}
	echo '</table>';    
	echo GetMessage('TOTAL_QUANTITY').' '.$SaleItem["TOTAL_QUANTITY"].'<br />';   
	echo '</div>';
}

?>
