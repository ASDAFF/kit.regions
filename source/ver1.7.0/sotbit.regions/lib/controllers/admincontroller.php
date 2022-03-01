<?php

namespace Sotbit\Regions\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Request;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\Response\BFile;
use Bitrix\Main\IO;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Engine\UrlManager;

class AdminController extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->checkModules();
    }

    public function configureActions()
    {
        return [];
    }

    public function uploadLocationNamesAction(int $contryId, string $contry, string $charset): Uri
    {
        $content = $this->getLoactionLangs($contryId);

        $charsetConvert = false;

        if (strtolower(LANG_CHARSET) !== $charset) {
            $charsetConvert = true;
        }

        $contentString = [];

        foreach ($content as $value) {
            $string = implode(';', $value);
            $contentString[] = $charsetConvert
                ? Encoding::convertEncoding($string, LANG_CHARSET, $charset)
                : $string;
        }

        $root = Application::getDocumentRoot();
        $dir = $root . "/upload/tmp";
        $isDir = IO\Directory::isDirectoryExists($dir);
        if (!$isDir) {
            IO\Directory::createDirectory($dir);
        }
        $contryEng = \CUtil::translit($contry, LANGUAGE_ID);
        $path2file = "{$dir}/{$contryEng}.csv";

        if (IO\File::isFileExists($path2file)) {
            IO\File::deleteFile($path2file);
        }

        IO\File::putFileContents($path2file, implode("\n", $contentString));

        return static::getUrl('downloadCountry', ['name' => "{$contry}.csv", 'path' => $path2file]);
    }

    public function downloadLocationNamesAction(): array
    {
        $file = new IO\File($this->request->getFile('inputText')['tmp_name']);

        $text = Encoding::convertEncodingToCurrent($file->getContents());

        $arrStrings = explode("\n", $text);
        $content = [];
        foreach ($arrStrings as $string) {
            $content[] = explode(';', $string);
        }
        $contrys = array_chunk($content[0], 2);
        $id = current($contrys)[0];
        $contentFromDb = $this->getLoactionLangs($id);

        if (empty($contentFromDb) || count($contentFromDb) === 0) {
            return [
                'message' => Loc::getMessage('sotbit.regions_ADMIN_ALL_CONTROLLER_ERRORS'),
            ];
        }

        $errors = [];
        $sucssecCounter = 0;
        foreach ($content as $value) {
            if (count($errors) > 10) {
                return [
                    'message' => Loc::getMessage('sotbit.regions_ADMIN_CONTROLLER_ERRORS'),
                    'data' => $errors,
                ];
            }
            if ($value[0] === '') {
                continue;
            }

            if ((empty($value[1]) || sprintf('%010d', $value[1]) !== $contentFromDb[$value[0]][1])) {
                $errors[] = $value;
                continue;
            }
            $langs = array_filter(array_chunk($value, 2), function ($i) {
                return count($i) === 2 && strlen($i[0]) === 2 && $i[1] !== '';
            });
            unset($langs[0]);
            $updateData = [
                'NAME' => array_combine(
                    array_column($langs, 0),
                    array_map(function($i) { return ['NAME' => $i[1]]; }, $langs)
                ),
            ];

            try {
                $res = LocationTable::update($value[0], $updateData);
            } catch (\Throwable $e) {
                $result = ['OK' => str_replace('###', $sucssecCounter, Loc::getMessage('sotbit.regions_ADMIN_CONTROLLER_SUCCESS'))];
                $result[Loc::getMessage('sotbit.regions_ADMIN_CONTROLLER_ERRORS')] = $e->getMessage();
                return $result;
            }

            if(!$res->isSuccess()) {
                $errors[] = $res->getErrorMessages();
            } else {
                $sucssecCounter++;
            }
        }

        $result = ['OK' => str_replace('###', $sucssecCounter, Loc::getMessage('sotbit.regions_ADMIN_CONTROLLER_SUCCESS'))];

        if (count($errors) !== 0) {
            $result[Loc::getMessage('sotbit.regions_ADMIN_CONTROLLER_ERRORS')] = $errors;
        }

        return $result;
    }

    public function downloadFileAction(string $name): BFile
    {
        $year = getdate(time())['year'];
        $path = "/bitrix/modules/sotbit.regions/lib/sypexgeo/{$year}_sypexGeoUpdate.log";
        $fileAray = \CFile::MakeFileArray($path);

        return new BFile($fileAray, $year . '_' .$name);
    }

    public function downloadCountryAction(string $name, string $path): BFile
    {
        $fileAray = \CFile::MakeFileArray($path);

        return new BFile($fileAray, $name);
    }

    public static function getUrl(string $action, array $queryParams): Uri
    {
        $controller = "sotbit:regions.AdminController.{$action}";
        $queryParams['sessid'] = bitrix_sessid();

        return UrlManager::getInstance()->create($controller, $queryParams);
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('module sale is not installed');
        }
    }

    protected function getLoactionLangs(string $contryId): array
    {
        $margins = LocationTable::query()
            ->setSelect(['LEFT_MARGIN', 'RIGHT_MARGIN'])
            ->where('ID', $contryId)
            ->fetch();

        $result = LocationTable::query()
            ->setSelect(['ID', 'CODE', 'NAME.LANGUAGE_ID', 'NAME.NAME'])
            ->addOrder('PARENT_ID', 'ASC')
            ->where('LEFT_MARGIN', '>=', $margins['LEFT_MARGIN'])
            ->where('RIGHT_MARGIN', '<=', $margins['RIGHT_MARGIN'])
            ->fetchAll();

        $content = [];
        foreach ($result as $row) {
            foreach ($row as $key => $value) {
                if (isset($content[$row['ID']][0]) && $key === 'ID') {
                    continue;
                }
                if (isset($content[$row['ID']][1]) && $key === 'CODE') {
                    continue;
                }
                $content[$row['ID']][] = $value;
            }
        }

        return $content;
    }
}