<?php
namespace Sotbit\Regions\Seo;

use Bitrix\Main\Error;
use Bitrix\Main\SiteTable;
use Sotbit\Regions\Config\Option;

/**
 * Class Sitemap
 *
 * @package Sotbit\Regions\Seo
 * @author  Andrey Sapronov <a.sapronov@sotbit.ru>
 * Date: 18.12.2019
 */
class Sitemap extends File
{
    /**
     * @var string
     */
    public $dir       = '';
    /**
     * @var string
     */
    public $site      = '';
    /**
     * @var string
     */
    public $siteDir      = '';
    /**
     * @var array
     */
    public $rootFiles = array(
        'sitemap_index.xml',
        'sitemap.xml'
    );

    /**
     * Sitemap constructor.
     * @param $siteLid
     * @throws \Bitrix\Main\ArgumentException
     */
    public function __construct($siteLid)
    {
        parent::__construct();
        $site = SiteTable::getList(array(
            'filter' => array('LID' => $siteLid),
            'limit' => 1,
            'select' => array('DIR', 'DOC_ROOT')
        ))->fetch();
        $this->site = $siteLid;
        $this->siteDir = $site['DIR'];
        $this->siteRoot = (empty($site['DOC_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : $site['DOC_ROOT']);
        $this->dir = $this->siteRoot . $site['DIR'];
    }

    /**
     * @return \Bitrix\Main\Result
     */
    public function run()
    {
        if(Option::get('SINGLE_DOMAIN',$this->site) != 'Y')
        {
            $find = false;
            foreach ($this->rootFiles as $rootFile)
            {
                if(file_exists($this->dir . $rootFile))
                {
                    $find = true;
                    $xmlRoot = simplexml_load_file($this->dir . $rootFile);
                    $newXml = $this->addFileHeader();
                    $newXml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

                    // main sitemap
                    foreach ($xmlRoot->sitemap as $sitemap)
                    {
                        $path = explode('/', $sitemap->loc);
                        $file = end($path);

                        // sub sitemaps
                        if(file_exists($this->dir . $file))
                        {
                            $xml = simplexml_load_file($this->dir . $file);
                            // main sitemap link to sub php
                            $newXml .= $this->createNewUrl($this->replaceExtension($sitemap->loc),
                                $sitemap->lastmod);

                            // Gen sub sitemaps
                            $newSubXml = $this->addFileHeader();
                            $newSubXml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                            foreach ($xml->url as $url)
                            {
                                $newSubXml .= $this->createNewUrl($url->loc, $url->lastmod);
                            }
                            $newSubXml .= '</urlset>';

                            // sub sitemap.php
                            $newSubFile = $this->genNewFile($this->dir . $file, $newSubXml);
                            // sub sitemap.php
                            /*$this->addRuleToHtaccess(
                                $file,
                                str_replace($_SERVER['DOCUMENT_ROOT'] . $this->siteDir, '', $newSubFile),
                                $this->siteDir);*/

                        }
                    }
                    $newXml .= '</urlset>';

                    // main sitemap.php
                    $newFile = $this->genNewFile($this->dir . $rootFile, $newXml);

                    // main sitemap.php htaccess
                    $this->addRuleToHtaccess(
                        $rootFile,
                        str_replace($this->siteRoot . $this->siteDir, '', $newFile),
                        $this->siteDir,
                        $this->siteRoot
                    );
                }
            }

            if(!$find){
                $error = new Error('',2);
                $this->result->addError($error);
            }
        }
        else{
            $error = new Error('',1);
            $this->result->addError($error);
        }
        return $this->result;
    }

    // create url for xml
    public function createNewUrl($loc, $lastmod)
    {
        $newXml = '';
        $newXml .= '<url>';
        $loc = str_replace([
            'http://',
            'https://',
        ], '', $loc);

        $path = explode('/', $loc);
        $root = $path[0];
        $loc = str_replace($root, '', $loc);

        $newXml .= '<loc>'.'<?=$domainCode?>'.$loc.'</loc>';
        $newXml .= '<lastmod>'.$lastmod.'</lastmod>';
        $newXml .= '</url>';

        return $newXml;
    }

    public function replaceExtension($filename)
    {
        return preg_replace('"\.xml$"', '.php', $filename);
    }
}