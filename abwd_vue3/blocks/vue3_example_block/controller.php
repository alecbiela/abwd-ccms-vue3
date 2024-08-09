<?php
/**
 * Example block controller for a Vue 3 block
 * You don't need much in here, especially if using
 * API routes to grab the content
 * 
 */
namespace Concrete\Package\AbwdVue3\Block\Vue3ExampleBlock;

use Concrete\Core\Block\BlockController;

defined('C5_EXECUTE') or die('Access Denied');

class Controller extends BlockController
{

    public function getBlockTypeDescription()
    {
        return t("An example block made for testing purposes or as a boilerplate for a new block.");
    }

    public function getBlockTypeName()
    {
        return t("Vue 3 Example Block");
    }

    /**
     * This function runs when a block of this type is loaded on the page and a view is
     * generated. It can be used for passing props out to view.php that should be loaded
     * in the component.
     * 
     * $this->requireAsset('abwd-vue3-core') is required to load Vue.js and components.
     * However, it intelligently only loads the assets once, so you can put it in as 
     * many blocks as you create and it will only ever load the JS one time in the final view.
     */
    public function view()
    {
        $this->requireAsset('abwd-vue3-core');

        // Use $this->set('propname', $this->propData) to pass props. 
        $this->set('bID', $this->bID);
    }
}