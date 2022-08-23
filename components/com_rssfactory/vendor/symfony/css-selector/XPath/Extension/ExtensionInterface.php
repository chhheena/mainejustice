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
 * XPath expression translator extension interface.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
interface ExtensionInterface
{
    /**
     * Returns node translators.
     *
     * These callables will receive the node as first argument and the translator as second argument.
     *
     * @return callable[]
     */
    public function getNodeTranslators(): array;

    /**
     * Returns combination translators.
     *
     * @return callable[]
     */
    public function getCombinationTranslators(): array;

    /**
     * Returns function translators.
     *
     * @return callable[]
     */
    public function getFunctionTranslators(): array;

    /**
     * Returns pseudo-class translators.
     *
     * @return callable[]
     */
    public function getPseudoClassTranslators(): array;

    /**
     * Returns attribute operation translators.
     *
     * @return callable[]
     */
    public function getAttributeMatchingTranslators(): array;

    /**
     * Returns extension name.
     */
    public function getName(): string;
}
