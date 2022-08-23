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

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     rss_date_parse
 * Purpose:  parse rss date into unix epoch
 * Input:    string: rss date
 *			 default_date:  default date if $rss_date is empty
 *
 * NOTE!!!  parse_w3cdtf provided by MagpieRSS's rss_utils.inc
 *          this file needs to be included somewhere in your script
 * -------------------------------------------------------------
 */

function smarty_modifier_rss_date_parse($rss_date, $default_date = null)
{
    if ($rss_date != '') {
        return parse_w3cdtf($rss_date);
    } elseif (isset($default_date) && $default_date != '') {
        return parse_w3cdtf($default_date);
    } else {
        return;
    }
}

?>
