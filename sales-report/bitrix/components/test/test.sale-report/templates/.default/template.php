<?
/**
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
?>


<p><?=GetMessage('PERIOD')?> <?=$arResult['PERIOD']?> </p>
<p><?=GetMessage('MIN_SALES')?> <?=$arResult['MIN_SALES']?></p>
<table>
	<tr><th><?=GetMessage('PRODUCT_ID')?></th><th><?=GetMessage('PRODUCT_NAME')?></th><th><?=GetMessage('ARTICLE')?></th><th><?=GetMessage('PRICE')?></th><th><?=GetMessage('TOTAL_QUANTITY')?></th></tr>

<?
	foreach ($arResult["REPORT"] as &$SaleItem) {
		if ($SaleItem["SALES"]<=$arResult['MIN_SALES']) { 
?>
		<tr>
			<td><?=$SaleItem["PRODUCT_ID"]?></td>
			<td><a href="<?=$SaleItem["DETAIL_PAGE_URL"]?>" target="_blank"><?=$SaleItem["NAME"]?></a></td>
			<td><?=$SaleItem["ARTICLE"]?></td>       
			<td><?=$SaleItem["PRICE"]?></td>       
			<td><?=$SaleItem["SALES"]?></td>       
		</tr>
<?
		}
	}

?>
</table>
