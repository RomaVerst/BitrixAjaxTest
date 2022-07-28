<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;

$APPLICATION->IncludeComponent(
    "custom:api",
    "",
    []
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");