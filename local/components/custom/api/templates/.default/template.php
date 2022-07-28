<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

?>
<div data-block="app"></div>
<script>
    BX.ready( () => {
        for (let i = 0; i < <?=$arResult['REQUEST']['ITERATION']?>; i++) {
            BX.ajax.runComponentAction(
                'custom:api',
                'addItems',
                {
                    mode: 'class',
                    data: {
                        iStep: <?=$arResult['REQUEST']['STEP']?>,
                        iNumIteration: i
                    },
                }
            ).then((oResponse) => {
                let oData = JSON.parse(oResponse.data);
                if (typeof oData != 'undefined' && oData) {
                    let sHtml = `Время выполнения: ${oData.workTime}<br/>Кол-во элементов, успешно добавленных в итерации:${oData.countElements}<br/>Добавленные элементы:<br/> ${oData.message}<br/>`;
                    document.querySelector('[data-block="app"]').innerHTML += sHtml;
                } else {
                    document.querySelector('[data-block="app"]').innerHTML += 'Что-то пошло не так<br/>';
                }
            });
        }
    });
</script>
