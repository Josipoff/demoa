<link rel="stylesheet" href="<?php echo $this->resourcesURL; ?>resources/mt-form.css">
<link rel="stylesheet" href="<?php echo $this->resourcesURL; ?>resources/mt-form-font.css">
<?php echo $this->getCSS(); ?>
<script type="text/javascript">
//<![CDATA[
var jQueryNoConflictLevel = <?php echo $this->jQueryNoConflictLevel(); ?>;
//]]>
</script>
<?php if($this->loadJQuery()) { ?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<?php } ?>
<script src="<?php echo $this->resourcesURL; ?>resources/jquery.highlight-4.js"></script>
<script src="<?php echo $this->resourcesURL; ?>resources/mt-form.js"></script>
<script src="<?php echo $this->resourcesURL; ?>resources/affix.js"></script>
<?php echo $this->getScripts(); ?>

<div class="mt-settings-form mt-tabs-left mt-border-r-4px">

<?php if($this->showPageTitle()) { ?>
<h1><?php echo $this->pageTitle; ?></h1>
<?php } ?>

<div class="mt-left-sidebar">

<ul class="mt-tabs mt-border-r-4px">
<?php
    $tabIndex = 0;
    foreach($this->profiles as $profileId => $profileTitle) {
?>
	<li class="mt-<?php echo $this->profileEnabled($profileId) ? 'on' : 'off'; ?>">
        <a id="<?php echo $profileId; ?>-tab" class="<?php echo $profileId == $this->activeTab ? 'mt-active' : ''; ?>" data-mt-tab="mt-tab-<?php echo $tabIndex; ?>" href="#">
            <?php echo $profileTitle; ?>
        </a>
    </li>
<?php
        $tabIndex++;
    }
?>
    <li class="mt-<?php echo $trial ? 'off' : 'on'; ?>">
        <a id="licenses-tab" class="<?php echo 'licenses' == $this->activeTab ? 'mt-active' : ''; ?>" data-mt-tab="mt-tab-<?php echo $tabIndex; ?>" href="#">Licenses</a>
    </li>
</ul>

<div class="mt-support-block">
	<span class="mt-icon-question"></span>
	Got an issue or question?<br/>
	<a target="_blank" class="mt-support-link" href="http://magictoolbox.com/contact/"><b>Get a support</b></a>
	<div class="mt-clearfix"></div>
	<p>
		<a href="http://magictoolbox.com" target="_blank">www.magictoolbox.com</a>
		<span>
		<a href="http://www.facebook.com/magictoolbox/" target="_blank" class="mt-icon-social-facebook"></a>
		<a href="https://plus.google.com/+Magictoolboxhello/" target="_blank" class="mt-icon-social-google-plus"></a>
		<a href="http://twitter.com/magictoolbox/" target="_blank" class="mt-icon-social-twitter"></a>
		<a href="https://www.youtube.com/user/magictoolbox" target="_blank" class="mt-icon-social-youtube"></a>
		</span>
	</p>
</div>

</div>

<form id="magictoolbox-settings-form" action="<?php echo $this->getFormAction(); ?>" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="magicscroll-active-tab" id="magicscroll-active-tab" value="<?php echo $this->activeTab; ?>" />
<input type="hidden" name="magicscroll-submit-action" id="magicscroll-submit-action" value="save" />
<?php $this->getInputsHTML(); ?>

<div class="mt-buttons">
    <input type="button" class="mt-button mt-border-r-4px" data-submit-action="save" value="Save settings"/>
    <input type="button" class="mt-button mt-border-r-4px" data-submit-action="reset" value="Reset to defaults"/>
    <?php echo $this->getAdditionalButtons(); ?>
</div>

<?php
$tabIndex = 0;
foreach($this->paramsMap as $profileId => $groups) {
    $params->setProfile($profileId);
?>
<div class="mt-tab-pane <?php echo $profileId == $this->activeTab ? 'mt-active' : ''; ?>" id="mt-tab-<?php echo $tabIndex; ?>">

	<div class="mt-tab-controls mt-border-r-4px">
		<div class="mt-table-row">
			<span><input type="text" class="mt-parameter-keyword" data-search-source="mt-tab-<?php echo $tabIndex; ?>" placeholder="Search for parameter..."/></span>
			<span><label><input type="checkbox" class="mt-show-hide-advanced" data-search-source="mt-tab-<?php echo $tabIndex; ?>"/>Show advanced options</label></span>
		</div>
	</div>
<?php
    foreach($groups as $groupTitle => $ids) {
        $groupId = preg_replace('/[^a-z0-9]/i', '', strtolower($groupTitle));
        echo '<fieldset class="mt-border-r-4px"><legend>'.$groupTitle.'</legend><div class="params-block" id="block-'.$groupId.'" >';

        if($profileId == $this->customSlideshowProfileId && $groupTitle == $this->customSlideshowGroupTitle) {
            echo '<div class="mt-form-item">';
            require(dirname(__FILE__).DIRECTORY_SEPARATOR.'magictoolbox.settings.slideshow.tpl.php');
            echo '</div>';
        }

        foreach($ids as $id => $required) {
            $value = $params->getValue($id);
            $type = $params->getType($id);
            $subType = $params->getSubType($id);
            $enabled = $required || $this->isEnabledParam($id, $profileId);
            $disabled = $enabled ? '' : ' disabled="disabled"';
?>
    <div class="mt-form-item<?php if($params->isAdvanced($id)) { echo ' mt-advanced'; } ?>">
        <div class="mt-param-name"><label for="<?php echo $profileId.'-'.$id; ?>"><?php echo $params->getLabel($id); ?></label></div>
        <div class="mt-param-holder <?php echo $type; ?>" data-default="<?php echo $params->getDefaultValue($id); ?>" data-type="<?php echo $type.(empty($subType) ? '' : '-'.$subType); ?>">
        <div class="mt-param-holder-inner">
<?php
            switch($type) {
                case 'array':
                    if($subType == 'radio') {
                        echo '<span>';
                        $firstChild = true;
                        foreach($params->getValues($id) as $index => $v) {
                            ?><input type="radio" value="<?php echo $v; ?>"<?php echo ($value == $v ? ' checked="checked"' : ''); ?> name="<?php echo $this->getName($profileId, $id); ?>" id="<?php echo $profileId.'-'.$id.'-'.$index; ?>"<?php echo $disabled; ?>/><?php
                            ?><label class="<?php if($v == 'No') { echo 'mt-no-radio'; }; if($firstChild) { echo ' mt-fchild'; $firstChild = false; } ?>" for="<?php echo $profileId.'-'.$id.'-'.$index; ?>"><span><?php echo $this->getValueForDisplay($v); ?></span></label><?php
                        }
                        echo '</span>';
                    } else if($subType == 'select') {
                        ?><select name="<?php echo $this->getName($profileId, $id); ?>" id="<?php echo $profileId.'-'.$id; ?>"<?php echo $disabled; ?>><?php
                        foreach($params->getValues($id) as $v) {
                            ?><option value="<?php echo $v; ?>"<?php echo ($value == $v ? ' selected="selected"' : ''); ?>><?php echo $v; ?></option><?php
                        }
                        ?></select><?php
                    }  else {
                        ?><input type="text" class="mt-input text" name="<?php echo $this->getName($profileId, $id); ?>" id="<?php echo $profileId.'-'.$id; ?>"<?php echo $disabled; ?> value="<?php echo $value; ?>" /><?php
                    }
                    break;
                case 'num':
                case 'text':
                default:
                    ?><input type="text" class="mt-input <?php echo $type; ?>" name="<?php echo $this->getName($profileId, $id); ?>" id="<?php echo $profileId.'-'.$id; ?>"<?php echo $disabled; ?> value="<?php echo $value; ?>" /><?php
            }

            if(!$required) {
                if($enabled) {
                    echo '&nbsp;&nbsp;<a href="#" class="mt-switch-option-link" data-name="'.$this->getName($profileId, $id).'" data-general-name="'.$this->getName($params->generalProfile, $id).'" onclick="return false;">use default option</a>';
                } else {
                    echo '&nbsp;&nbsp;<a href="#" class="mt-switch-option-link option-disabled" data-name="'.$this->getName($profileId, $id).'" data-general-name="'.$this->getName($params->generalProfile, $id).'" onclick="return false;">edit</a>';
                }
            }

            echo '</div>';//mt-param-holder-inner

            $hint = '';
            if($description = $params->getDescription($id)) {
                $hint = $description;
            }
            if($type != 'array' && $params->valuesExists($id, '', false)) {
                if($hint != '') $hint .= '<br />';
                $hint .= '#allowed values: '.implode(', ', $params->getValues($id));
            }
            if($hint != '') {
                echo '<span class="mt-help-block">'.$hint.'</span>';
            }

            echo '</div></div>';
        }
        echo '</div></fieldset>';
    }
    echo '</div>';
    $tabIndex++;
}
?>

<div class="mt-tab-pane <?php echo 'licenses' == $this->activeTab ? 'mt-active' : ''; ?>" id="mt-tab-<?php echo $tabIndex; ?>" data-skip-showhide="1">

    <?php if(!empty($this->message)) { ?>
    <div class="mt-alert-message">
        <?php echo $this->message; ?>
    </div>
    <?php } ?>

	<fieldset class="mt-border-r-4px">
		<legend>Magic Scroll&trade;</legend>
            <?php $license = $this->getLicenseType('magicscroll'); ?>
			<p>License status: <b class="mt-<?php echo $license; ?>"><?php echo $license; ?></b><?php if($license == 'trial') { ?> (<a class="show-upgrade-instructions" href="#">upgrade</a>)<?php } ?>.</p>
			<ol class="mt-instructions">
                <li>Please purchase license <a target="_blank" href="https://www.magictoolbox.com/buy/magicscroll">here</a>.</li>
				<li>
				<p>Enter your license key (XXXXXXX) for:</p>
					<input type="text" class="form-control" name="magicscroll-license-key" id="magicscroll-license-key" placeholder="License key" value="" autocomplete="off" />
                    <input type="button" class="mt-button mt-border-r-4px mt-button-small" data-submit-action="license" value="Submit"/>
				</li>
			</ol>
	</fieldset>

    <?php if($this->core->type == 'standard') { ?>
	<fieldset class="mt-border-r-4px">
		<legend>Magic Scroll&trade;</legend>
        <?php $license = $this->getLicenseType('magicscroll'); ?>
			<p>License status: <b class="mt-<?php echo $license; ?>"><?php echo $license; ?></b><?php if($license == 'trial') { ?> (<a class="show-upgrade-instructions" href="#">upgrade</a>)<?php } ?>.</p>	
			<ol class="mt-instructions">
				<li>Please purchase license <a target="_blank" href="https://www.magictoolbox.com/buy/magicscroll">here</a>.</li>
				<li>
				<p>Enter your license key (XXXXXXX) for:</p>
					<input type="text" class="form-control" name="magicscroll-license-key" id="magicscroll-license-key" placeholder="License key" value="" autocomplete="off" />
                    <input type="button" class="mt-button mt-border-r-4px mt-button-small" data-submit-action="license" value="Submit"/>
				</li>
			</ol>
	</fieldset>
    <?php } ?>

</div>

<script type="text/javascript">
//<![CDATA[

var magictoolboxProfiles = ['<?php echo implode("', '", array_keys($this->profiles)); ?>'];

//]]>
</script>

</form>

</div>
