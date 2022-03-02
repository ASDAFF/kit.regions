<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 07-Feb-18
 * Time: 2:20 PM
 */

namespace Sotbit\Regions\Location;

use Sotbit\Regions\Config\Option;
use Bitrix\Main\Service\GeoIp;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Sale\Location as SaleLocation;
use Bitrix\Main\Text\Encoding;
use Sotbit\Regions\Internals;

class User
{
    protected $findUserMethod;
    protected $ip;

    public function __construct()
    {
        $this->findUserMethod = Option::get('FIND_USER_METHOD', SITE_ID);

        if ($this->findUserMethod !== 'statistic') {
            $this->findUserMethod = 'services';
        }
    }

    public function getUserGeoData(): GeoIp\Data
    {
        if ($this->findUserMethod === 'statistic') {
            $Statistic = new User\Statistic();
            return $Statistic->getUserGeoData();
        } elseif ($this->findUserMethod === 'services') {
            $IpGeoManager = new User\GeoIpManager();
            return $IpGeoManager->getUserCity();
        }
    }

    public function getBestMatch(GeoIp\Data $geoData): array
    {
        $regions = Internals\RegionsTable::query()
            ->addSelect('ID')
            ->addSelect('NAME')
            ->addSelect('DEFAULT_DOMAIN')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->fetchAll();


        if (empty($geoData->cityName)) {
            $defoultRegion = array_reduce($regions, function(array $curry, array $i) {
                return $i['DEFAULT_DOMAIN'] === 'Y' ? $i : $curry;
            }, []);

            return $defoultRegion;
        }

        $bastMatch = array_reduce($regions, function(array $curry, array $i) use ($geoData) {
            return levenshtein($geoData->cityName, $i['NAME']) < levenshtein($geoData->cityName, $curry['NAME'])
                ? $i : $curry;
        }, current($regions));

        return $bastMatch;
    }

    public function getLocationById(int $id): array
    {
        $location = SaleLocation\LocationTable::query()
            ->setSelect(['ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'])
            ->addSelect('NAME.NAME')
            ->where('ID', $id)
            ->fetch();

        if (empty($location)) {
            return [];
        }

        return $this->langCorrection($location);
    }

    public function getDefaultLocation(): array
    {
        $defoultRegion = Internals\RegionsTable::query()
            ->addSelect('ID')
            ->addSelect('REGIONS.LOCATION_ID')
            ->addSelect('NAME')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->where('DEFAULT_DOMAIN', 'Y')
            ->fetchAll();

        $defoultLocation = SaleLocation\Name\LocationTable::query()
            ->addSelect('NAME')
            ->addSelect('LOCATION_ID')
            ->addSelect('LANGUAGE_ID')
            ->whereIn('LOCATION_ID', array_column($defoultRegion, 'SOTBIT_REGIONS_INTERNALS_REGIONS_REGIONS_LOCATION_ID'))
            ->fetchAll();

        $bestMatch = array_reduce($defoultLocation, function(array $curry , array $i) use($defoultRegion) {
            return levenshtein($defoultRegion['NAME'], $i['NAME']) < levenshtein($defoultRegion['NAME'], $curry['NAME'])
                ? $i : $curry;
        }, current($defoultLocation));

        $bestMatch = $this->langCorrection([
            'ID' => $bestMatch['LOCATION_ID'],
            'SALE_LOCATION_LOCATION_NAME_LANGUAGE_ID' => $bestMatch['LANGUAGE_ID'],
            'SALE_LOCATION_LOCATION_NAME_NAME' => $bestMatch['NAME']
        ]);

        return [
            'ID' => $bestMatch['ID'],
            'NAME' => $bestMatch['SALE_LOCATION_LOCATION_NAME_NAME'],
            'REGION_ID' => current($defoultRegion)['ID'],
        ];
    }

    public function getRegionByLocation(array $location): ?int
    {
        $filter = Query\Join::on('this.LOCATION_ID', "ref.ID")
            ->where('ref.LEFT_MARGIN', '<=', $location['LEFT_MARGIN'])
            ->where('ref.RIGHT_MARGIN', '>=', $location['RIGHT_MARGIN'])
        ;

        $ref = new Reference('SaleLocation', SaleLocation\LocationTable::class, $filter);
        $ref->configureJoinType(Query\Join::TYPE_INNER);

        $regionsId = Internals\RegionsTable::query()
            ->addSelect('ID')
            ->where('SITE_ID', serialize([SITE_ID]))
            ->fetchAll()
        ;

        $region = Internals\LocationsTable::query()
            ->addSelect('REGION_ID')
            ->whereIn('REGION_ID', array_column($regionsId, 'ID'))
            ->registerRuntimeField($ref)
            ->fetch();

        return $region['REGION_ID'];
    }

    public function getBestMatchWithLocation(array $location): array
    {
        if ($location === []) {
            return $this->getDefaultLocation();
        }

        $regionID = $this->getRegionByLocation($location);

        if (empty($regionID)) {
            $defoultRegion = Internals\RegionsTable::query()
                ->addSelect('ID')
                ->where('SITE_ID', serialize([SITE_ID]))
                ->where('DEFAULT_DOMAIN', 'Y')
                ->fetch();

            return [
                'ID' => $location['ID'],
                'NAME' => $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                'REGION_ID' => $defoultRegion['ID'],
            ];
        }

        return [
            'ID' => $location['ID'],
            'NAME' => $location['SALE_LOCATION_LOCATION_NAME_NAME'],
            'REGION_ID' => $regionID,
        ];
    }

    public function findByGeodata(GeoIp\Data $data): array
    {
        $type = array_column(SaleLocation\TypeTable::query()
            ->setSelect(['ID', 'CODE'])
            ->fetchAll(), 'ID', 'CODE',
        );

        $contryType = (int)$type['COUNTRY'] ?? 0;
        $regionType = (int)$type['REGION'] ?? 0;
        $cityType = (int)$type['CITY'] ?? 0;

        $initialRequest = SaleLocation\LocationTable::query()
            ->setSelect(['ID', 'NAME.NAME', 'NAME.LANGUAGE_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN']);

        if (!empty($data->countryName)) {
            $margins = $this->searchByType(clone $initialRequest, $contryType, $data->countryName);
        }

        if (isset($data->countryName) && count($margins) > 0) {
            $initialRequest
                ->where('LEFT_MARGIN', '>=', $margins['LEFT_MARGIN'])
                ->where('RIGHT_MARGIN', '<=', $margins['RIGHT_MARGIN']);
        }

        $result = isset($data->cityName)
            ? $this->searchByType(clone $initialRequest, $cityType, $data->cityName)
            : [];

        if (count($result) !== 0) {
            return $this->langCorrection($result);
        }

        if (empty($data->regionName)) {
            return [];
        }

        $regionData = $this->searchByType(clone $initialRequest, $regionType, $data->regionName);

        if (count($regionData) === 0) {
            return [];
        }

        if ($contryType !== 0) {
            $initialRequest->where('TYPE_ID', $cityType);
        }

        $result = $initialRequest->where('PARENT_ID', $regionData['ID'])->fetch();

        return $this->langCorrection($result);
    }

    /** @return string[] */
    private function getLevenshtein1(string $word): array
    {
        $words = [];
        for ($i = 0; $i < mb_strlen($word); $i++) {
            // insertions
            $words[] = mb_substr($word, 0, $i) . '_' . mb_substr($word, $i);
            // deletions
            $words[] = mb_substr($word, 0, $i) . mb_substr($word, $i + 1);
            // substitutions
            $words[] = mb_substr($word, 0, $i) . '_' . mb_substr($word, $i + 1);
        }
        // last insertion
        $words[] = $word . '_';
        return $words;
    }

    private function searchByType(Query\Query $initialRequest, int $type, string $name): array
    {
        $currentCharset = Encoding::convertEncodingToCurrent($name);
        $names = $this->getLevenshtein1($currentCharset);

        $filter = array_reduce($names, function (Query\Filter\ConditionTree $curry, $i) {
            return $curry->whereLike('NAME.NAME', $i);
        }, Query\Query::filter()->logic('or'));

        if ($type !== 0) {
            $initialRequest->where('TYPE_ID',  $type);
        }

        $result = $initialRequest
            ->where($filter)
            ->fetchAll();

        if (count($result) === 1) {
            return $result[0];
        }

        if (count($result) > 1) {
            return array_reduce($result, function($curry, $i) use ($currentCharset) {
                $key = 'SALE_LOCATION_LOCATION_NAME_NAME';
                return levenshtein($i[$key], $currentCharset) < levenshtein($curry[$key], $currentCharset) ? $i : $curry;
            }, current($result));
        }

        return [];
    }

    private function langCorrection(array $locationData): array
    {
        if (LANGUAGE_ID === $locationData['SALE_LOCATION_LOCATION_NAME_LANGUAGE_ID']) {
            return $locationData;
        }

        $langs = SaleLocation\Name\LocationTable::query()
            ->addSelect('LANGUAGE_ID')
            ->where('LOCATION_ID', $locationData['ID'])
            ->fetchAll();

        if (!in_array(LANGUAGE_ID, array_column($langs, 'LANGUAGE_ID'))) {

            if(!in_array('ru', array_column($langs, 'LANGUAGE_ID'))) {
                return $locationData;
            }

            $result = SaleLocation\LocationTable::query()
                ->setSelect(['ID', 'NAME.NAME', 'NAME.LANGUAGE_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'])
                ->where('ID', (int)$locationData['ID'])
                ->where('NAME.LANGUAGE_ID', 'ru')
                ->setLimit(1)
                ->fetch();

            return $result;
        }

        $result = SaleLocation\LocationTable::query()
            ->setSelect(['ID', 'NAME.NAME', 'NAME.LANGUAGE_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'])
            ->where('ID', (int)$locationData['ID'])
            ->where('NAME.LANGUAGE_ID', LANGUAGE_ID)
            ->setLimit(1)
            ->fetch();

        return $result;
    }
}