<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>

<h5><i class="joms-icon-users"></i> <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);?>"><?php echo $page->name; ?></a></h5>
<p><?php echo JHTML::_('string.truncate',strip_tags($page->description) , $config->getInt('streamcontentlength'));?></p>
