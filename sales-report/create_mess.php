<?php

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__));

$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
set_time_limit(0);

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$Description="
#EMAIL_TO# - E-Mail получателя сообщения (#OWNER_EMAIL#)
#BCC# - скрытая копия (#ADMIN_EMAIL#)
#MIN_SALES# - продано, не более
#PERIOD# - период в днях
#MESSAGE# - текст сообщения
";

$EventName="TEST_REPORT";
$Site="s1";

if (CModule::IncludeModule("main"))
{
	// создание типа почтового сообщения
	$evType = new CEventType;
	$evType->Add(array(
        	"LID"           => "ru",
        	"EVENT_NAME"    => $EventName,
        	"NAME"          => "Непродаваемые товары",
        	"DESCRIPTION"   => $Description
        ));

	print_r($evType);

 
	// создание шаблона почтового сообщения
	$arMess["ACTIVE"] = "Y";
	$arMess["EVENT_NAME"] = $EventName;
	$arMess["LID"] = $Site;
	$arMess["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
	$arMess["EMAIL_TO"] = "#EMAIL_TO#";
	$arMess["BCC"] = "#BCC#";
	$arMess["SUBJECT"] = "Отчёт о товарах с продажами не более #MIN_SALES# шт. за #PERIOD# дней";
	$arMess["BODY_TYPE"] = "html";
	$arMess["MESSAGE"] = "#MESSAGE#";

	$newMess = new CEventMessage;
	$newMess->Add($arMess);

	print_r($newMess);

}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

?>