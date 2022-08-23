<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\View;

\defined('_JEXEC') or die;
use JchOptimize\Helper\OptimizeImage;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Toolbar\ToolbarHelper as JToolbarHelper;
use Joomla\CMS\Uri\Uri as JUri;
use _JchOptimizeVendor\Joomla\View\HtmlView;
class OptimizeImagesHtml extends HtmlView
{
    public function loadResources()
    {
        $document = Factory::getDocument();
        $options = ['version' => JCH_VERSION];
        $document->addStyleSheet(JUri::root(\true) . '/media/com_jchoptimize/core/css/admin.css', $options);
        $document->addStyleSheet(JUri::root(\true) . '/media/com_jchoptimize/css/admin-joomla.css', $options);
        $document->addStyleSheet(JUri::root(\true) . '/media/com_jchoptimize/filetree/jquery.filetree.css', $options);
        $document->addStyleSheet(JUri::root(\true) . '/media/com_jchoptimize/bootstrap/css/bootstrap5-cssgrid.css', $options);
        HTMLHelper::_('jquery.framework');
        $document->addScript(JUri::root(\true) . '/media/com_jchoptimize/filetree/jquery.filetree.js', $options);
        $document->addScript(JUri::root(\true) . '/media/com_jchoptimize/js/platform-joomla.js', $options);
        $ajax_filetree = JRoute::_('index.php?option=com_jchoptimize&view=Ajax&task=filetree', \false);
        $script = <<<JS
\t\t
jQuery(document).ready( function() {
\tjQuery("#file-tree-container").fileTree({
\t\troot: "",
\t\tscript: "{$ajax_filetree}",
\t\texpandSpeed: 100,
\t\tcollapseSpeed: 100,
\t\tmultiFolder: false
\t}, function(file) {});
});
JS;
        $document->addScriptDeclaration($script);
        if (JCH_PRO) {
            $data = $this->getData();
            OptimizeImage::loadResources($data['apiParams']);
        }
        $this->removeData('apiParams');
        $options = ['trigger' => 'hover focus', 'placement' => 'right', 'html' => \true];
        HTMLHelper::_('bootstrap.popover', '.hasPopover', $options);
    }
    public function loadToolBar()
    {
        JToolbarHelper::title(Text::_(JCH_PRO ? 'COM_JCHOPTIMIZE_PRO' : 'COM_JCHOPTIMIZE'), 'dashboard');
        JToolbarHelper::preferences('com_jchoptimize');
    }
}
