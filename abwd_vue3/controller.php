<?php
/**
 * @package ABWD Vue 3
 * @author Alec Bielanos
 * @license Apache-2.0
 */
namespace Concrete\Package\AbwdVue3;

defined('C5_EXECUTE') or die('Access Denied.');

// Aliases are defined in concrete/config/app.php
use Asset;
use AssetList;
use BlockType;
use Package;
use SinglePage;

class Controller extends Package
{
    protected $pkgHandle = 'abwd_vue3';
    protected $appVersionRequired = '8.5.17';
    protected $phpVersionRequired = '7.4.0';
    protected $pkgVersion = '0.0.1';

    public function getPackageDescription()
    {
        return t('A Vue 3 framework skeleton for ConcreteCMS.');
    }

    public function getPackageName()
    {
        return t('ABWD Vue 3');
    }

    private function installOrUpgrade($pkg = null){
        if(is_null($pkg)) $pkg = Package::getByHandle('abwd_vue3');

        // Add block types
        // $bt = BlockType::getByHandle('block_name');
        // if (!is_object($bt)) {
        //     $bt = BlockType::installBlockType('block_name', $pkg);
        // }

        // Add single pages
        // $this->setupSinglePage($pkg, '/dashboard/example', 'Example Page Title', 'Example Page Description', array('exclude_nav'=>1))
    }

    /**
     * Runs whenever the package is installed to a site for the first time
     */
    public function install()
    {
        if (version_compare(phpversion(), $this->phpVersionRequired, '<')) {
            throw new \Exception(t('This package requires a minimum PHP version of '.$this->phpVersionRequired.' to run correctly.'));
        }
        $pkg = parent::install();
        $this->installOrUpgrade($pkg);
    }

    /**
     * Runs when the package is updated to a new version through the CMS
     */
    public function upgrade(){
        parent::upgrade();
        $this->installOrUpgrade();
    }

    /**
     * Runs when this package is uninstalled from the CMS
     * Block Types are uninstalled automatically
     */
    public function uninstall(){
        parent::uninstall();
    }

    /**
     * Code to bootstrap onto the application startup routine
     * before any blocks, pages, etc. are loaded.
     */
    public function on_start(){
        // Register css and js files for display
        // This does NOT include the tailor-made css/js for single pages
        // Those are manually included in the single page template(s) themselves
        $al = AssetList::getInstance();
        $al->register('javascript', 'abwd-vue3', 'js/main.js', array('version' => $this->pkgVersion), 'abwd_vue3');
        $al->register('css', 'abwd-vue3', 'css/main.css', array('version' => $this->pkgVersion), 'abwd_vue3');
        $al->registerGroup('abwd-vue3-core', array(
            array('javascript','abwd-vue3'),
            array('css','abwd-vue3')
        ));

        // Register API routes?
    }

    /**
     * Adds a new single page OR updates one if it already exists at the given path
     * @param Package $pkg The package object passed from installOrUpgrade
     * @param String $cPath Relative page path (e.g. /dashboard/my_functionality/my_page)
     * @param String $cDescription The page's description - will show in the CMS backend
     * @param array $pageAttributes An array of page attributes to set on the single page ['attribute_handle'=>'value']
     * @return SinglePage $sp The page object for the added/modified SinglePage
     */
    private function setupSinglePage($pkg, $cPath, $cName = '', $cDescription = '', $pageData = array()) {
        $sp = SinglePage::add($cPath, $pkg);

        // $sp is null if the SinglePage already exists
        if (is_null($sp)) $sp = Page::getByPath($cPath);

        // Set page title and description
        $data = array();
        if (!empty($cName)) $data['cName'] = $cName;
        if (!empty($cDescription)) $data['cDescription'] = $cDescription;
        if (!empty($data)) $sp->update($data);

        // Set page attributes
        foreach($pageData as $handle=>$value){
            $sp->setAttribute($handle, $value);
        }

        return $sp;
    }
}