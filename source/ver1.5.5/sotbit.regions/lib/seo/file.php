<?php
namespace Sotbit\Regions\Seo;

use Bitrix\Main\Result;

/**
 * Class File
 *
 * @package Sotbit\Regions\Seo
 * @author  Andrey Sapronov <a.sapronov@sotbit.ru>
 * Date: 18.12.2019
 */
abstract class File
{
    /**
     * @var Result
     */
    protected $result;
    protected $htaccess;

    public function __construct()
    {
        $this->result = new Result();
    }

    abstract public function run();

    protected function addFileHeader()
    {
        return '<?php
					use Bitrix\Main\Loader;
					require_once ($_SERVER[\'DOCUMENT_ROOT\'].\'/bitrix/modules/main/include/prolog_before.php\');
					if(!Loader::includeModule("sotbit.regions"))
					{
						return false;
					}
					header(\'Content-Type: text/xml\');
					$domain = new \Sotbit\Regions\Location\Domain();
					$domainCode = $domain->getProp("CODE");
					?>';
    }

    protected function genNewFile($oldFile = '',$content = '')
    {
        $newFile = str_replace(array('.xml','.txt'),'.php',$oldFile);
        if(file_exists($newFile))
        {
            unlink($newFile);
        }
        file_put_contents(
            $newFile,
            $content
        );
        return $newFile;
    }

    protected function addRuleToHtaccess(
        $oldFile = '',
        $newFile = '',
        $site = '',
        $siteRoot = ''
    )
    {
        $this->htaccess = (empty($siteRoot) ? $_SERVER['DOCUMENT_ROOT'] : $siteRoot) . '/.htaccess';

        if($oldFile && $newFile && file_exists($this->htaccess))
        {
            if($site[0] == '/')
            {
                $site = mb_substr($site,1, null, LANG_CHARSET);
            }
            $htaccess = file_get_contents($this->htaccess);
            if(mb_strpos($htaccess, 'RewriteRule ^' . $site . $oldFile . '$ ' . $site .$newFile . ' [L,QSA]') === false)
            {
                $modRewrite = '<IfModule mod_rewrite.c>';
                $modRewritePos = mb_strpos($htaccess,'<IfModule mod_rewrite.c>');
                if($modRewrite !== false)
                {
                    $nhtaccess = mb_substr($htaccess,0,$modRewritePos+mb_strlen($modRewrite), LANG_CHARSET);
                    $nhtaccess .= "\r\n";
                    $nhtaccess .= "# sotbit.regions\r\n";
                    $nhtaccess .= 'RewriteRule ^' . $site . $oldFile . '$ ' . $site . $newFile . ' [L,QSA]';
                    $nhtaccess .= mb_substr($htaccess,$modRewritePos+mb_strlen($modRewrite), null, LANG_CHARSET);
                    $htaccess = $nhtaccess;
                }
                else
                {
                    $htaccess .= "\r\n";
                    $htaccess .= 'RewriteRule ^' . $site . $oldFile . '$ ' . $site . $newFile . ' [L,QSA]';
                }
                $i=0;
                while(file_exists($this->htaccess.'.back'.$i))
                {
                    ++$i;
                    if($i > 100)
                    {
                        break;
                    }
                }
                copy($this->htaccess, $this->htaccess.'.back'.$i);
                file_put_contents($this->htaccess, $htaccess);
            }
        }
    }
}