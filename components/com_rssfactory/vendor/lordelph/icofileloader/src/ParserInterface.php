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

namespace Elphin\IcoFileLoader;

defined('_JEXEC') or die;

interface ParserInterface
{
    /**
     * Returns true if string is more likely to be binary ico data rather than a filename
     * @param string $data
     * @return boolean
     */
    public function isSupportedBinaryString($data);

    /**
     * @param string $data binary string containing an icon
     * @return Icon
     */
    public function parse($data);
}
