<?php
namespace Kit\Regions\Sale;

use Bitrix\Main\Loader;
use Bitrix\Sale\Internals;

/**
 * Class Helper
 *
 * @package Kit\Regions\Sale
 * Date: 24.10.2019
 */
class Helper
{
    public static function getPersonTypeUser($siteId, $userId = null)
    {
        $personTypeId = 1;
        $personType = [];

        if(!Loader::includeModule('sale') || !Loader::includeModule('catalog')) {
            return $personTypeId;
        }

        // list all type user
        if(class_exists("\Bitrix\Sale\Internals\PersonTypeSiteTable")) {
            $personTypeSites = Internals\PersonTypeSiteTable::getList(
                [
                    "select" => ["PERSON_TYPE_ID"],
                    "filter" => ["SITE_ID" => $siteId],
                ]
            )->fetchAll();

            if($personTypeSites && is_array($personTypeSites)) {
                $personType = Internals\PersonTypeTable::getList(
                    [
                        'filter' => [
                            "ACTIVE" => "Y",
                            "ID"     => array_map(
                                function($v) {
                                    return $v['PERSON_TYPE_ID'];
                                },
                                $personTypeSites
                            ),
                        ],
                    ]
                )->fetchAll();
            }
        }

        if(empty($personType)) {
            $personType = Internals\PersonTypeTable::getList(
                [
                    'filter' => [
                        "ACTIVE" => "Y",
                        "LID"    => $siteId,
                    ],
                ]
            )->fetchAll();
        }


        if(!empty($personType)) {
            if($userId) {
                $arProfiles = \CSaleOrderUserProps::GetList(
                    ["DATE_UPDATE" => "DESC"],
                    ["USER_ID" => $userId]
                );

                if($arProfile = $arProfiles->Fetch()) {
                    $personTypeId = $arProfile['PERSON_TYPE_ID'];
                } else {
                    $personTypeId = $personType[0]['ID'];
                }
            } else {
                $personTypeId = $personType[0]['ID'];
            }
        }

        return $personTypeId;
    }

}