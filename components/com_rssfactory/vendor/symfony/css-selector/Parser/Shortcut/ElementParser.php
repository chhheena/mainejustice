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

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Parser\Shortcut;

defined('_JEXEC') or die;

use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\SelectorNode;
use Symfony\Component\CssSelector\Parser\ParserInterface;

/**
 * CSS selector element parser shortcut.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class ElementParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $source): array
    {
        // Matches an optional namespace, required element or `*`
        // $source = 'testns|testel';
        // $matches = array (size=3)
        //     0 => string 'testns|testel' (length=13)
        //     1 => string 'testns' (length=6)
        //     2 => string 'testel' (length=6)
        if (preg_match('/^(?:([a-z]++)\|)?([\w-]++|\*)$/i', trim($source), $matches)) {
            return [new SelectorNode(new ElementNode($matches[1] ?: null, $matches[2]))];
        }

        return [];
    }
}
