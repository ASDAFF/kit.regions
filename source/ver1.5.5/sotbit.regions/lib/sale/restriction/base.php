<?php
namespace Sotbit\Regions\Sale\Restriction;
use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Main\Localization\Loc;
use Sotbit\Regions\Internals\RegionsTable;

/**
 * Trait Base
 * @package Sotbit\Regions\Sale\Restriction
 */
trait Base
{
	/**
	 * @return string
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage(\SotbitRegions::moduleId.'_TITLE');
	}

	/**
	 * @return string
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage(\SotbitRegions::moduleId.'_DESCRIPTION');
	}

	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $idService
	 * @return bool
	 */
	public static function check($params, array $restrictionParams, $idService = 0)
	{
	    if(!empty($restrictionParams['REGION'])) {
	        if(is_array($restrictionParams['REGION'])) {
	            if(in_array($_SESSION['SOTBIT_REGIONS']['ID'], $restrictionParams['REGION'])) {
	                return true;
                }
            } else {
                if($_SESSION['SOTBIT_REGIONS']['ID'] == $restrictionParams['REGION'])
                {
                    return true;
                }
            }
        }

        return false;
	}

	/**
	 * @param Entity $entity
	 * @return null
	 */
	protected static function extractParams(Entity $entity)
	{
		return null;
	}

	/**
	 * @param int $entityId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getParamsStructure($entityId = 0)
	{
		$options = [];
		if(Loader::includeModule('sotbit.regions'))
		{
			$rs = RegionsTable::getList(['select' => ['ID','NAME']]);
			while($region = $rs->fetch())
			{
				$options[$region['ID']] = $region['NAME'];
			}
		}

		return [
			'REGION' => [
				'TYPE' => 'ENUM',
                'MULTIPLE' => 'Y',
				'LABEL' => Loc::getMessage(\SotbitRegions::moduleId.'_LIST'),
				"OPTIONS" => $options
			]
		];
	}
}
?>