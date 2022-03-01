<?php
namespace Sotbit\Regions\Sale;
use Sotbit\Regions\Config\Option;
use \Bitrix\Sale\PropertyValue;
use Bitrix\Main\Localization\Loc;

/**
 * Class Order
 * @package Sotbit\Regions\Sale
 * @author Sergey Danilkin <s.danilkin@sotbit.ru>
 */
class Order
{
    protected $needAddOrderProperty = false;
    protected $needAddOrderManager = false;

    /**
     * Order constructor.
     * @param \Bitrix\Sale\Order $order
     */
    public function __construct( \Bitrix\Sale\Order $order)
    {
        $this->setNeedAddOrderProperty($this->checkNeedAddOrderProperty($order));

        if($_SESSION['SOTBIT_REGIONS']['MANAGER'] > 0)
        {
            $this->needAddOrderManager = true;
        }
    }

    /**
     * @param \Bitrix\Sale\Order $order
     * @return bool
     */
    public function checkNeedAddOrderProperty(\Bitrix\Sale\Order $order)
    {
        if(Option::get('ADD_ORDER_PROPERTY',$order->getSiteId()) == 'Y')
        {
            $propertyCollection = $order->getPropertyCollection();
            foreach($propertyCollection as $property)
            {
                if($property->getField('CODE') == 'REGION')
                {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * @param \Bitrix\Sale\Order $order
     * @return PropertyValue|bool
     */
    public function getProperty(\Bitrix\Sale\Order $order)
    {
        $propertyCollection = $order->getPropertyCollection();
        $prop = PropertyValue::create($propertyCollection,array(
            'TYPE' => 'STRING',
            'NAME' => Loc::getMessage(\SotbitRegions::moduleId.'_PROP_TITLE'),
            'CODE' => 'REGION',
        ));
        $prop->setValue( ( isset($_SESSION['SOTBIT_REGIONS']['LOCATION']['SALE_LOCATION_LOCATION_NAME_NAME']) ? $_SESSION['SOTBIT_REGIONS']['LOCATION']['SALE_LOCATION_LOCATION_NAME_NAME'] : $_SESSION['SOTBIT_REGIONS']['NAME'] ) );
        $return = $prop;

        return $return;
    }

    /**
     * @param bool $needAddOrderProperty
     */
    public function setNeedAddOrderProperty($needAddOrderProperty)
    {
        $this->needAddOrderProperty = $needAddOrderProperty;
    }

    /**
     * @return bool
     */
    public function isNeedAddOrderProperty()
    {
        return $this->needAddOrderProperty;
    }

    /**
     * @return bool
     */
    public function isNeedAddOrderManager()
    {
        return $this->needAddOrderManager;
    }

    /**
     * @param bool $needAddOrderManager
     */
    public function setNeedAddOrderManager($needAddOrderManager)
    {
        $this->needAddOrderManager = $needAddOrderManager;
    }
}