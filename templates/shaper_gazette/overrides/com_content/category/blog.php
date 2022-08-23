<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$app = Factory::getApplication();

$this->category->text = $this->category->description;
$app->triggerEvent('onContentPrepare', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$this->category->description = $this->category->text;

$results = $app->triggerEvent('onContentAfterTitle', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayTitle = trim(implode("\n", $results));

$results = $app->triggerEvent('onContentBeforeDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$beforeDisplayContent = trim(implode("\n", $results));

$results = $app->triggerEvent('onContentAfterDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayContent = trim(implode("\n", $results));

$plugin = JPluginHelper::getPlugin('content', 'jw_allvideos');
                        
// Check if plugin is enabled
if ($plugin)
{
    // Get plugin params
$pluginParams = new JRegistry($plugin->params);

$param1 = $pluginParams->get('vfolder');

}
?>
<div class="avPlayerWrapper avVideo">
    <div class="avPlayerContainer text-center">
        <div id="" class="avPlayerBlock"><video class="avPlayer" style="width:600px;height:450px;" src="<?php echo JURI::base().$param1; ?>/a-nation-in-decline.mp4" preload="metadata" controls="" controlslist=""></video></div>
            <div class="avDownloadLink">
                <a target="_blank" href="<?php echo JURI::base(); ?>/<?php echo $param1; ?>/a-nation-in-decline.mp4" download="">Download</a>
            </div>
        </div>
    </div>
</div>
