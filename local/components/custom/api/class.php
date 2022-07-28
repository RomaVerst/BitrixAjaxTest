<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Application;

class Api extends CBitrixComponent implements Controllerable
{
    const API_KEY = 'RUN2021';
    const IBLOCK_ID = 5;

    public function executeComponent()
    {
        $oRequest = Application::getInstance()->getContext()->getRequest();

        $sApiKeyRequest = htmlspecialchars($oRequest->getQuery("apikey"));
        $iIblockIdRequest = (int)htmlspecialchars($oRequest->getQuery("IBLOCK"));
        $iCountRequest = (int)htmlspecialchars($oRequest->getQuery("COUNT"));
        $iStepRequest = (int)htmlspecialchars($oRequest->getQuery("STEP"));
        $arCheck = $this->checkParams($sApiKeyRequest, $iIblockIdRequest, $iCountRequest, $iStepRequest);
        if ($arCheck['status'] === 'error') {
            echo $arCheck['message'];
            return false;
        }
        $iIteration = ceil($iCountRequest / $iStepRequest);
        $this->arResult['REQUEST'] = [
            'ITERATION' => $iIteration,
            'STEP' => $iStepRequest
        ];
        $_SESSION['API'] = [
            'COUNT' => $iCountRequest,
            'ADDING_ELEMENTS' => 0
        ];
        $this->includeComponentTemplate();
    }


    /**
     * Проверка переданных параметров
     * @param string $sApikey "апи ключ"
     * @param int $iIBlockId "id инфоблока"
     * @param int $iCount "общее количество элементов для добавления"
     * @param int $iStep "максимальное количество элементов в одной итерации"
     * @return array
     */
    public function checkParams(string $sApikey, int $iIBlockId, int $iCount, int $iStep)
    {
        $sMessage = '';
        if (!$sApikey || $sApikey !== self::API_KEY) {
            $sMessage = 'Api key is invalid';
        } elseif (!$iIBlockId || $iIBlockId !== self::IBLOCK_ID) {
            $sMessage = 'Iblock is invalid';
        } elseif (!$iCount) {
            $sMessage = 'Count not defined';
        } elseif (!$iStep) {
            $sMessage = 'Step not defined';
        } elseif ($iStep > $iCount) {
            $sMessage = 'Count must be more then Step';
        }
        return [
            'status' => ($sMessage === '') ? 'success' : 'error',
            'message' => $sMessage
        ];
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function configureActions()
    {
        return [
            'addItems' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                ],
                'postfilters' => []
            ],
        ];
    }

    /**
     * Добавление элементов в инфоблок (метод ajax)
     * @param int $iStep "максимальное количество элементов"
     * @param int $iNumIteration "номер итерации"
     * @return string
     */
    public function addItemsAction(int $iStep, int $iNumIteration)
    {
        set_time_limit(60);

        $iTimeStart = microtime(true);
        $sMessage = '';
        $oIblockEl = new CIBlockElement;
        $iCountElements = 0;

        for ($i = 0; $i < $iStep; $i++) {
            if ($_SESSION['API']['ADDING_ELEMENTS'] < $_SESSION['API']['COUNT']) {
                $iNumber = $i . $iNumIteration;
                $arProp = [
                    9 => [
                        'Город ' . $iNumber,
                        'Страна ' . $iNumber,
                        'Регион ' . $iNumber
                    ]
                ];
                $arLoadProductFields = [
                    'IBLOCK_ID' => self::IBLOCK_ID,
                    'PROPERTY_VALUES' => $arProp,
                    'NAME' => 'Тест материал ' . $iNumber,
                    'ACTIVE' => 'Y'
                ];

                if ($iProductId = $oIblockEl->Add($arLoadProductFields)) {
                    $sMessage .= 'New ID: ' . $iProductId . '<br/>';
                    $_SESSION['API']['ADDING_ELEMENTS']++;
                    $iCountElements++;
                } else {
                    $sMessage .= 'Error: ' . $oIblockEl->LAST_ERROR . '<br/>';
                }
            } else {
                break;
            }
        }
        $iTimeEnd = round(microtime(true) - $iTimeStart, 4);
        return json_encode(
            [
                'message' => $sMessage,
                'countElements' => $iCountElements,
                'workTime' => $iTimeEnd . ' сек.'
            ],
            JSON_UNESCAPED_UNICODE
        );
    }
}
