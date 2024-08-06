<?php
defined('C5_EXECUTE') or die('Access Denied.');
$this->requireAsset('abwd-vue3-core');

$c = Page::getCurrentPage();

/** @var \Concrete\Core\Utility\Service\Text $th */
$th = Core::make('helper/text');
/** @var \Concrete\Core\Localization\Service\Date $dh */
$dh = Core::make('helper/date'); 

$props = array();

// Title/TitleFormat are for the overarching block
if (isset($pageListTitle) && $pageListTitle) {
    $props['title'] = h($pageListTitle);
    $props['title-format'] = $titleFormat;
}

if(isset($rssUrl) && $rssUrl) {
    $props['rss-url'] = $rssUrl;
}

// Include entry text if any of these 3 conditions are met
// Resolves to a boolean (true/false) based on conditional
$props['include-entry-text'] = (
    (isset($includeName) && $includeName) ||
    (isset($includeDescription) && $includeDescription) ||
    (isset($useButtonForLink) && $useButtonForLink)
);

$props['include-name'] = (isset($includeName) && $includeName);
$props['include-date'] = (isset($includeDate) && $includeDate);
$props['include-description'] = (isset($includeDescription) && $includeDescription);
$props['use-button-for-link'] = (isset($useButtonForLink) && $useButtonForLink);
$props['no-results-message'] = h($noResultsMessage);
$props['empty-block-message'] = t('Empty Page List Block.');
$props['display-empty-message'] = (is_object($c) && $c->isEditMode() && $controller->isBlockEmpty());
$props['show-pagination'] = $showPagination;
$props['button-link-text'] = h($buttonLinkText);


// BEGIN MAIN PAGE LIST PARSING
$pageProps = array();
foreach ($pages as $k=>$page) {

    // Initialize the page object with defaults
    $pageProps[$k] = array(
        'title' => h($page->getCollectionName()),
        'target' => h('_self'),
        'description' => h($page->getCollectionDescription()),
        'thumbnail' => false,
        'entry_classes' => 'ccm-block-page-list-page-entry',
        'date' => h($dh->formatDateTime($page->getCollectionDatePublic(), true))
    );

    // Get the page URL - if external link, set to open in a new tab
    if ($page->getCollectionPointerExternalLink() != '') {
        $pageProps[$k]['url'] = $page->getCollectionPointerExternalLink();
        if ($page->openCollectionPointerExternalLinkInNewWindow()) {
            $pageProps[$k]["target"] = h('_blank');
        }
    } else {
        $pageProps[$k]['url'] = h($page->getCollectionLink());
        $pageProps[$k]['target'] = h($page->getAttribute('nav_target'));
    }

    // If description is set to truncate after 'n' chars, limit it
    if($controller->truncateSummaries) $pageProps[$k]['description'] = h($th->wordSafeShortText($description, $controller->truncateChars));

    // Image creation if needed
    // Because of how CCMS image creation works (with image theme settings, thumbnail generation, etc.)
    // we generate the <img> tag here and pass it as a string into the props
    if ($displayThumbnail) {
        $thumbnail = $page->getAttribute('thumbnail');
        $img = Core::make('html/image', ['f' => $thumbnail, 'options' => array('usePictureTag'=>false)]);
        if(isset($img)) {
            $t = $img->getTag();
            if(!is_null($t)){
                $t->addClass('page-list-image');
                /** OUTPUT BUFFER - we need to do this to grab the generated <img> **/
                ob_start();
                echo $t;
                $tag = ob_get_clean();
                $pageProps[$k]['thumbnail'] = $tag;
                // Add special classes on the entry text for thumbnails
                if ($props['include-entry-text']) $pageProps[$k]['entry_classes'] = h($pageProps[$k]['entry_classes'].' ccm-block-page-list-page-entry-horizontal');
            }
        }
    }

    //Other useful page data...

    //$last_edited_by = $page->getVersionObject()->getVersionAuthorUserName();

    /* DISPLAY PAGE OWNER NAME
        * $page_owner = UserInfo::getByID($page->getCollectionUserID());
        * if (is_object($page_owner)) {
        *     echo $page_owner->getUserDisplayName();
        * }
        */

    /* CUSTOM ATTRIBUTE EXAMPLES:
        * $example_value = $page->getAttribute('example_attribute_handle', 'display');
        *
        * When you need the raw attribute value or object:
        * $example_value = $page->getAttribute('example_attribute_handle');
        */


}
// END MAIN PAGE LIST PARSING


// Build the string of props that will go on the component
$propStr = '';
foreach($props as $name=>$value){
    // Don't pass props that are boolean values and also false
    if($value === false) continue;

    // Pass props that are boolean value as just the prop name (no value)
    if($value === true){
        $propStr .= $name . ' ';
    } else {
        $propStr .= $name . '="' . $value . '" ';
    }
    
}
$propStr .= "pages='".json_encode($pageProps)."'";

?>

<div class="vue3-block page-list">
    <page-list <?= $propStr; ?>></page-list>
</div>