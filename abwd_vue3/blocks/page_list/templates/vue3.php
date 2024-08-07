<?php
defined('C5_EXECUTE') or die('Access Denied.');
$this->requireAsset('abwd-vue3-core');

$c = Page::getCurrentPage();

/** @var \Concrete\Core\Utility\Service\Text $th */
$th = Core::make('helper/text');
/** @var \Concrete\Core\Localization\Service\Date $dh */
$dh = Core::make('helper/date'); 

$props = array();
$props['block-id'] = $bID;

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
?>

<div class="vue3-block page-list">
    <page-list <?= $propStr; ?>></page-list>
</div>