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

namespace Symfony\Component\CssSelector\XPath\Extension;

defined('_JEXEC') or die;

/**
 * XPath expression translator abstract extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
abstract class AbstractExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNodeTranslators(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCombinationTranslators(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionTranslators(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPseudoClassTranslators(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeMatchingTranslators(): array
    {
        return [];
    }
}
