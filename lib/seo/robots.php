<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 29-Jan-18
 * Time: 3:06 PM
 */

namespace Kit\Regions\Seo;

use Bitrix\Main\Error;
use Bitrix\Main\SiteTable;
use Kit\Regions\Config\Option;

class Robots extends File
{
	/**
	 * @var string
	 */
	public $txtFile;
	/**
	 * @var string
	 */
	public $phpFile;

	public $dir       = '';
    public $site      = '';
    public $siteDir   = '';

	/**
	 * Robots constructor.
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
		$this->txtFile = $this->dir . '/robots.txt';
		$this->phpFile = $this->dir . '/robots.php';
	}

	public function run()
	{
		if(file_exists($this->txtFile))
		{
			$newRobots = '<?php
use Bitrix\Main\Loader;
require_once ($_SERVER[\'DOCUMENT_ROOT\'].\'/bitrix/modules/main/include/prolog_before.php\');
if(!Loader::includeModule("kit.regions"))
{
    return false;
}
$domain = new \Kit\Regions\Location\Domain();
$domainCode = $domain->getProp("CODE");
if(!empty($domain->getProp("UF_ROBOTS")))
    $domainRobots = $domain->getProp("UF_ROBOTS");
?>
';
			$robots = file_get_contents($this->txtFile);

            $site = SiteTable::getList(
				array(
					'select' => array('SERVER_NAME'),
					'filter' => array(
					    'ACTIVE' => 'Y',
                        'LID' => $this->site
                    ),
                    'limit' => 1,
				)
			)->fetch();

            $domain = $site['SERVER_NAME'];

			if($domain)
			{
			    if(file_exists($this->dir . 'sitemap.php')) {
                    $robots = str_replace(
                        $domain . '/sitemap.xml',
                        $domain . '/sitemap.php',
                        $robots);
                }

                $robots = str_replace(
                    $domain,
                    '<?=str_replace(array("http://","https://"),"",$domainCode)?>',
                    $robots);
			}
			$newRobots.=$robots;

			// UF_ROBOTS
            $newRobots .= '
<?=(!empty($domainRobots) ? $domainRobots : "")?>';

			$newFile = $this->genNewFile($this->txtFile, $newRobots);
			$this->addRuleToHtaccess(
				'robots.txt',
				str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', $newFile));
		}
		else
		{
            $error = new Error('',1);
            $this->result->addError($error);
		}
		return $this->result;
	}
}