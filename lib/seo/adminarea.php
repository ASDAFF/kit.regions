<?php
namespace Kit\Regions\Seo;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
class AdminArea
{
	const POSITIONS_FOR_INPUT_CODE = "/(?<!BX\.adminShowMenu\(this\, \[\{\'TEXT\')\]\,\s*\'\s*\'\)\;/";
	const SEO_PROP_NAME_PATTERN = "/(?<!BX\.ready\(function\(\)\{\sBX\.bind\(BX\(\')mnu\_[\w]+?(?=[\'\"]\)\,\s\'click\')/";


	public static function addRegionsSeo(&$content)
	{
		global $APPLICATION;
		$curPage = $APPLICATION->GetCurPage();

		$workPages = [
		    '/bitrix/admin/cat_product_edit.php',
            '/bitrix/admin/cat_section_edit.php',
            '/bitrix/admin/iblock_element_edit.php',
            '/bitrix/admin/iblock_section_edit.php',
            '/bitrix/admin/iblock_edit.php',
        ];

		if(
			Loader::includeModule(\KitRegions::moduleId)
            && in_array($curPage, $workPages)
		)
		{
		    $sites = \KitRegions::getSites();
		    $site = [];
		    if(!empty($sites)) {
                $site[] = reset(array_keys(\KitRegions::getSites()));
            } else {
		        $site[] = 1;
            }

			$tags = \KitRegions::getTags([$site]);
            $countItemsForInsert = preg_match_all(self::POSITIONS_FOR_INPUT_CODE, $content);
            $countSeoPropName = preg_match_all(self::SEO_PROP_NAME_PATTERN, $content, $arrSeoPropNames);

            if($countItemsForInsert === $countSeoPropName) {
                $arrSeoPropNames = $arrSeoPropNames[0];
                $arrSeoPropNames = array_reverse($arrSeoPropNames);

                $result = preg_replace_callback(
                    self::POSITIONS_FOR_INPUT_CODE,
                    function ($match) use (&$arrSeoPropNames, $tags) {
                        $str = ",\n{'TEXT':";
                        $str .= "'".Loc::getMessage(\KitRegions::moduleId.'_SEO_PARENT')."'";
                        $str .= ",'MENU':[";

                        if(count($arrSeoPropNames) > 0) {
                            $propName = array_pop($arrSeoPropNames);
                        }

                        foreach($tags as $tag)
                        {
                            $str .= "{'TEXT':'".$tag['NAME']."','ONCLICK':'InheritedPropertiesTemplates.insertIntoInheritedPropertiesTemplate(\'".\KitRegions::genCodeVariable($tag['CODE'])."\', \'".$propName."\', \'".str_replace('mnu_', '', $propName)."\')'},";
                        }

                        $str .= "]}], '');";
                        return $str;
                    },
                    $content
                );

                if($result) {
                    $content = $result;
                }
            }
		}
	}
}