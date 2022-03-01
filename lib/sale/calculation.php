<?php

namespace Kit\Regions\Sale;

use Bitrix\Main\{
    Loader,
    Context
};
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\PaySystem;

use Kit\Regions;

/**
 * Class Calculation
 *
 * @package Kit\Regions\Sale
 */
class Calculation
{
    const DEFAULT_LIMIT = 2;
    const DEFAULT_QUANTITY = 1;
    const DEFAULT_ELEMENT_ID = 1;

    public $params = [];
    public $delivery = [];
    public $paysystem = [];
    protected $order;
    protected $shipment;

    /**
     * Calculation constructor.
     *
     * @param array $params
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     */
    public function __construct($params = [])
    {
        global $USER;
        $this->params = $params;

        if (!Loader::includeModule('sale') || !Loader::includeModule('catalog')) {
            return false;
        }

        // Check params
        // site Id
        $this->params['siteId'] =
            isset($this->params['siteId']) ? intval($this->params['siteId']) : Context::getCurrent()->getSite();

        // user Id
        $this->params['userId'] = isset($this->params['userId'])
            ? intval($this->params['userId'])
            : ($USER->IsAuthorized() ? $USER->GetId
            () : null);

        // location code
        $this->params['currentCode'] = isset($this->params['currentCode']) ? $this->params["currentCode"] : null;

        // delivery Limit
        $this->params['limit'] = isset($this->params['limit']) ? intval($this->params["limit"]) : self::DEFAULT_LIMIT;

        // element Id
        $this->params['elementId'] = isset($this->params['elementId']) ? intval($this->params["elementId"]) : self::DEFAULT_ELEMENT_ID;

        // quantity
        $this->params['quantity'] = isset($this->params['quantity']) ? intval($this->params["quantity"]) : self::DEFAULT_QUANTITY;

        // person Type
        $this->params['personTypeId'] = isset($this->params['personTypeId']) ? intval($this->params['personTypeId'])
            : Regions\Sale\Helper::getPersonTypeUser($this->params['siteId'], $this->params['userId']);

        // currency
        $this->params['currency'] = isset($this->params['currency']) ? $this->params['currency']
            : Internals\SiteCurrencyTable::getSiteCurrency($this->params['siteId']);

        // Check location
        if (!$this->params['currentCode']) {
            $domain = new Regions\Location\Domain();
            $this->params['currentCode'] = $domain->getProp('LOCATION')['CODE'];
        }

        // Calculation
        if($this->createOrder()) {

            $this->initDelivery();
            $this->initPaySystem();
        }
    }

    /**
     * Get all available deliveries
     * @return array
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * Get all available payments
     * @return array
     */
    public function getPaySystem()
    {
        return $this->paysystem;
    }

    /**
     * Create virtual order for calculation
     *
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    protected function createOrder()
    {
        $items = [];
        $arParentNames = [];

        // Create order
        $order = Sale\Order::create($this->params['siteId'], $this->params['userId']);
        $order->isStartField();
        $order->setPersonTypeId($this->params['personTypeId']);

        // Create basket
        $basket = Sale\Basket::create($this->params['siteId']);
        $basketItem = Sale\BasketItem::create($basket, "catalog", $this->params['elementId']);

        $basketItem->setFields([
            'QUANTITY'               => $this->params['quantity'],
            'CURRENCY'               => $this->params['currency'],
            'LID'                    => Context::getCurrent()->getSite(),
            'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
        ]);

        $basketItem->setField('PRODUCT_PROVIDER_CLASS', 'CCatalogProductProvider');
        $basketItem->setField('QUANTITY', $this->params['quantity']);

        $basket->addItem($basketItem);
        $order->setBasket($basket);

        $props = $order->getPropertyCollection();
        if ($loc = $props->getDeliveryLocation()) {
            $loc->setValue($this->params['currentCode']);
        }

        // Shipment
        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $shipment->setField("CURRENCY", $this->params['currency']);
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        foreach ($order->getBasket() as $item) {
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }


        if ($order instanceof Sale\Order && $shipment instanceof Sale\Shipment) {
            // return
            $this->order = $order;
            $this->shipment = $shipment;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Calculation delivery
     *
     * @return null
     */
    protected function initDelivery()
    {
        $items = [];

        if ($this->order instanceof Sale\Order && $this->shipment instanceof Sale\Shipment) {
            $order = $this->order;
            $shipment = $this->shipment;

            //Delivery restriction
            $arDeliveryServiceAll = Manager::getServicesForShipment($shipment);

            $arRestrictionIds = [];
            if (!empty($arDeliveryServiceAll)) {
                $arRestrictionIds = array_map(function ($v) {
                    return $v->getId();
                }, $arDeliveryServiceAll);
            }

            // Deliveries table
            $rs = Sale\Delivery\Services\Table::getList(
                [
                    'select' => [
                        'ID',
                        'NAME',
                        'DESCRIPTION',
                        'LOGOTIP',
                        'CLASS_NAME',
                        'PARENT_ID',
                    ],
                    'order'  => ['SORT' => 'asc'],
                    'filter' => [
                        'ACTIVE' => 'Y',
                    ],
                ]
            );


            while ($delivery = $rs->fetch()) {
                if ($delivery['CLASS_NAME'] == '\Bitrix\Sale\Delivery\Services\EmptyDeliveryService') {
                    $arParentNames[$delivery["ID"]] = $delivery["NAME"];
                    continue;
                }

                if ($delivery['PARENT_ID'] == 0
                    && $delivery['CLASS_NAME'] != '\Bitrix\Sale\Delivery\Services\Configurable'
                ) {
                    $arParentNames[$delivery["ID"]] = $delivery["NAME"];
                    continue;
                }

                // Check restriction
                if (empty($arRestrictionIds[$delivery['ID']])) {
                    continue;
                }

                $this->params['limit']--;
                if ($this->params['limit'] && $this->params['limit'] < 0) {
                    break;
                }


                $deliveryObg = Manager::getObjectById($delivery['ID']);
                if (!$deliveryObg) {
                    continue;
                }
                $calcResult = $deliveryObg->calculate($shipment);

                if ($delivery['LOGOTIP'] > 0) {
                    $delivery['LOGOTIP'] = \CFile::GetByID($delivery['LOGOTIP'])->Fetch();
                    $delivery['LOGOTIP']['SRC'] = \CFile::GetFileSRC($delivery['LOGOTIP']);
                }

                $price = $calcResult->getPrice();

                $name = isset($arParentNames[$delivery["PARENT_ID"]]) ? $arParentNames[$delivery["PARENT_ID"]]." ("
                    .$delivery['NAME'].")" : $delivery['NAME'];

                $items[] = [
                    'ID'          => $delivery['ID'],
                    'NAME'        => $name,
                    'DESCRIPTION' => $delivery['DESCRIPTION'],
                    'LOGOTIP'     => $delivery['LOGOTIP'],
                    'PERIOD_FROM' => $calcResult->getPeriodFrom(),
                    'PERIOD_TO'   => $calcResult->getPeriodTo(),
                    'PERIOD_TYPE' => $calcResult->getPeriodType(),
                    'TIME'        => $calcResult->getPeriodDescription(),
                    'PRICE'       => $price,
                    'PRINT_PRICE' => \CCurrencyLang::CurrencyFormat($price, $this->params['currency']),
                ];
            }

            // Return
            $this->delivery = $items;

            return true;
        }

        return false;
    }

    /**
     * Calculation payments
     *
     * @return null
     */
    protected function initPaySystem()
    {
        $items = [];

        if ($this->order instanceof Sale\Order) {
            $order = $this->order;

            // Create payment
            $paymentCollection = $order->getPaymentCollection();
            $payment = $paymentCollection->createItem();
            $payment->setField("CURRENCY", $order->getCurrency());
            $payment->setField("SUM", $order->getPrice());

            $paymentsRestrictions = array_keys(PaySystem\Manager::getListWithRestrictions($payment));

            // Get payments
            $rs = PaySystemActionTable::getList(
                [
                    'filter' => ['ACTIVE' => 'Y', '!ACTION_FILE' => 'inner'],
                    'select' => ['ID', 'NAME', 'LOGOTIP'],
                ]
            );
            while ($payment = $rs->fetch()) {
                if(!in_array($payment['ID'], $paymentsRestrictions))
                    continue;
                if ($payment['LOGOTIP'] > 0) {
                    $payment['LOGOTIP'] = \CFile::GetByID($payment['LOGOTIP'])->Fetch();
                    $payment['LOGOTIP']['SRC'] = \CFile::GetFileSRC($payment['LOGOTIP']);
                }
                $items[] = $payment;
            }

            $this->paysystem = $items;
            return true;
        }

        return false;
    }
}