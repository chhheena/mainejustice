<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

class RssFactoryBackendViewFeeds extends FactoryViewRss
{
    protected
        $get = array(
        'items',
        'pagination',
        'state',
        'listOrder',
        'listDirn',
        'saveOrder',
        'sortFields',
        'filters',
    ),
        $buttons = array(
        'add',
        'edit',
        'publish',
        'unpublish',
        'batch' => array('feeds_move', 'checkbox-partial'),
        array('refresh', 'feeds_list_refresh', 'loop', true),
        array('clearcache', 'feeds_list_clear_cache', 'purge', true),
        'delete',
    ),
        $html = array(
        'bootstrap.tooltip',
        'behavior.multiselect',
        'dropdown.init',
    );

    public function __construct(array $config = array())
    {
        parent::__construct($config);

        if (3 === (int)\Joomla\CMS\Version::MAJOR_VERSION) {
            $this->html[] = 'formbehavior.chosen/select';
        }
        else {
            $this->html[] = 'formbehavior.chosen/.pull-right select';
        }
    }
}
