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

interface RendererInterface
{
    /**
     * Render an icon image. Options can be supplied which may be specific to the renderer, but
     * will likely include
     * 'w' = resize to desired width if image has different dimension. If omitted, source size is used
     * 'h' = desired height, must be supplied if 'w' is supplied
     * 'background' = #RRGGBB hex for background colour. If omitted, background is transparent
     *
     * @param IconImage $img
     * @param array|null $opts array of name/value pairs specific to the renderer
     * @return mixed|null rendered result, depending on renderer
     */
    public function render(IconImage $img, array $opts = null);
}
