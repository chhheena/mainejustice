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

declare(strict_types=1);

namespace voku\helper;

defined('_JEXEC') or die;

/**
 * {@inheritdoc}
 */
abstract class AbstractSimpleXmlDomNode extends \ArrayObject
{
    /** @noinspection MagicMethodsValidityInspection */

    /**
     * @param string $name
     *
     * @return array|int|null
     */
    public function __get($name)
    {
        // init
        $name = \strtolower($name);

        if ($name === 'length') {
            return $this->count();
        }

        if ($this->count() > 0) {
            $return = [];

            foreach ($this as $node) {
                if ($node instanceof SimpleXmlDomInterface) {
                    $return[] = $node->{$name};
                }
            }

            return $return;
        }

        if ($name === 'plaintext' || $name === 'outertext') {
            return [];
        }

        return null;
    }

    /**
     * @param string   $selector
     * @param int|null $idx
     *
     * @return SimpleXmlDomNodeInterface<SimpleXmlDomInterface>|SimpleXmlDomNodeInterface[]|null
     */
    public function __invoke($selector, $idx = null)
    {
        return $this->find($selector, $idx);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        // init
        $html = '';

        foreach ($this as $node) {
            $html .= $node->outertext;
        }

        return $html;
    }

    /**
     * @param string $selector
     * @param int|null   $idx
     *
     * @return SimpleXmlDomNodeInterface<SimpleXmlDomInterface>|SimpleXmlDomNodeInterface[]|null
     */
    abstract public function find(string $selector, $idx = null);
}
