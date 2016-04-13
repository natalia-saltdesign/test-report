<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("COMP_TEST_REPORT_TITLE"),
	"DESCRIPTION" => GetMessage("COMP_TEST_REPORT_DESCR"),
	"ICON" => "/images/sale_basket.gif",
	"PATH" => array(
			"ID" => "test_utility",
			"CHILD" => array(
				"ID" => "user",
				"NAME" => GetMessage("MAIN_TEST_REPORT_NAME")
			)
		),
);
?>