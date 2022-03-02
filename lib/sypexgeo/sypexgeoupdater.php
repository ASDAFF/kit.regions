<?php

namespace Kit\Regions\SypexGeo;

use Bitrix\Main\Service\GeoIp;

class SypexGeoUpdater {

    public static function download(string $lang="utf8", string $path2log='sypexGeoUpdate.log')
    {
        $charset1 = 'utf8';
        $charset2 = 'cp1251';

        $path2log = self::removeOldLog($path2log);

        if (mb_strtoupper($lang) === 'UTF-8') {
            $dowloadCharSet = $charset1;
        } elseif (mb_strtoupper($lang) === 'WINDOWS-1251') {
            $dowloadCharSet = $charset2;
        } else {
            $dowloadCharSet = $charset1;
        }

        $now = getdate(time());
        $dateString = sprintf("%02d:%02d, %02d.%02d.%02d", $now['hours'], $now['minutes'], $now['mday'], $now['mon'], $now['year']);


        // ���������� ����� ���� ������ Sypex Geo
        // ���������
        $url = "https://sypexgeo.net/files/SxGeoCity_{$dowloadCharSet}.zip";  // ���� � ������������ �����
        $dat_file_dir = './'; // ������� � ������� ��������� dat-����
        $last_updated_file = __DIR__ . '/SxGeo.upd'; // ���� � ������� �������� ���� ���������� ����������
        define('INFO', true);
        // ����� ��������

        header('Content-type: text/plain; charset=utf8');

        chdir(__DIR__);
        $types = array(
            'Country' =>  'SxGeo.dat',
            'City' =>  'SxGeoCity.dat',
            'Max' =>  'SxGeoMax.dat',
        );
        // ��������� �����
        preg_match("/(Country|City|Max)/", pathinfo($url, PATHINFO_BASENAME), $m);
        $type = $m[1];
        $dat_file = $types[$type];
        if (INFO) {
            file_put_contents($path2log, $dateString . ' => ' . "��������� ����� � �������\n", FILE_APPEND);
        }

        $fp = fopen(__DIR__ .'/SxGeoTmp.zip', 'wb');
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_FILE => $fp,
            CURLOPT_HTTPHEADER => file_exists($last_updated_file) ? array("If-Modified-Since: " .file_get_contents($last_updated_file)) : array(),
        ));
        if(!curl_exec($ch)) {
            file_put_contents($path2log, $dateString . ' => ' . "������ ��� ���������� ������\n", FILE_APPEND);
            return;
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        if ($code == 304) {
            @unlink(__DIR__ . '/SxGeoTmp.zip');
            if (INFO) {
                file_put_contents($path2log, $dateString . ' => ' . "����� �� ��������� � ������� ����������� ����������\n", FILE_APPEND);
                return;
            }
        }

        if (INFO) {
            file_put_contents($path2log, $dateString . ' => ' . "����� � ������� ������\n", FILE_APPEND);
        }
        // ������������� �����
        $fp = fopen('zip://' . __DIR__ . '/SxGeoTmp.zip#' . $dat_file, 'rb');
        $fw = fopen($dat_file, 'wb');
        if (!$fp) {
            file_put_contents($path2log, $dateString . ' => ' . "�� ���������� �������\n", FILE_APPEND);
            return;
        }
        if (INFO) {
            file_put_contents($path2log, $dateString . ' => ' . "������������� �����\n", FILE_APPEND);
        };
        stream_copy_to_stream($fp, $fw);
        fclose($fp);
        fclose($fw);
        if(filesize($dat_file) == 0) {
            file_put_contents($path2log, $dateString . ' => ' . "������ ��� ���������� ������\n", FILE_APPEND);
            return;
        }
        @unlink(__DIR__ . '/SxGeoTmp.zip');
        $res = rename(__DIR__ . '/' . $dat_file, $dat_file_dir . $dat_file);
        if (!$res) {
            file_put_contents($path2log, $dateString . ' => ' . "������ ��� �������������� �����\n", FILE_APPEND);
            return;
        }
        file_put_contents($last_updated_file, gmdate('D, d M Y H:i:s') . ' GMT');
        if (INFO) {
            file_put_contents($path2log, $dateString . ' => ' . "��������� ���� � {$dat_file_dir}{$dat_file}\n", FILE_APPEND);
        }
    }

    public static function updater(): string
    {
        $state = GeoIp\HandlerTable::query()
            ->addSelect('ACTIVE')
            ->where('CLASS_NAME', '\\' . SypexGeoLocal::class)
            ->fetchObject();

        if ($state->getActive() === 'N') {
            return __METHOD__ . "();";
        }

        self::download(LANG_CHARSET);

        return __METHOD__ . "();";
    }

    public static function setAgent(): void
    {
        $week = 60 * 60 * 24 * 7;
        \CAgent::AddAgent(
            self::updater(),
            'kit.regions',
            'Y',
            $week,
        );
    }

    public static function removeAgent(): void
    {
        \CAgent::RemoveModuleAgents('kit.regions');
    }

    protected static function removeOldLog(string $path2log): string
    {
        $year = round(time() / (60 * 60 * 24 * 365)) + 1970 - 1;
        $oldLogs = array_filter(scandir(__DIR__), function ($i) use ($path2log, $year) {
            $n = strpos($i, $path2log);
            if (!is_int($n)) {
                return false;
            }
            $result = (int)mb_ereg_replace($path2log, '', $i);

            if ($result < $year - 1) {
                return true;
            }

            return false;
        });

        foreach ($oldLogs as $log) {
            unlink(__DIR__ . "/$log");
        }

        return $year . '_' . $path2log;
    }
}