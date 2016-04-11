<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Непродаваемые товары");
?><?$APPLICATION->IncludeComponent(
	"test:test.sale-report",
	"",
	Array(
		"USER_GROUP"=>array(1,11),
		"PERIOD"=>7,
		"MIN_SALES" => 3,
		"SET_TITLE" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>