<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Core\Html;

\defined('_JCH_EXEC') or die('Restricted access');
use CodeAlfa\RegexTokenizer\Html;
use JchOptimize\Core\Html\Callbacks\AbstractCallback;
use JchOptimize\Core\Exception;
class Parser
{
    use Html;
    /** @var string        Regex criteria of search */
    protected $sCriteria = '';
    /** @var array         Array of regex of excludes in search */
    protected $aExcludes = array();
    /** @var array          Array of ElementObjects containing criteria for elements to search for */
    protected $aElementObjects = array();
    public function __construct()
    {
    }
    //language=RegExp
    public static function HTML_BODY_ELEMENT() : string
    {
        return self::HTML_HEAD_ELEMENT() . '\\K.*+$';
    }
    //language=RegExp
    public static function HTML_HEAD_ELEMENT() : string
    {
        $aExcludes = array(self::HTML_ELEMENT('script'), self::HTML_COMMENT());
        return '<head\\b' . self::parseHtml($aExcludes) . '</head\\b\\s*+>';
    }
    //language=RegExp
    protected static function parseHtml($excludes = [], $lazy = \true) : string
    {
        $excludes[] = self::HTML_ELEMENT_SELF_CLOSING();
        $excludes[] = '<';
        $excludes = '(?:' . \implode('|', $excludes) . ')?';
        $lazily = $lazy ? '?' : '';
        return '(?>[^<]*+' . $excludes . ')*' . $lazily . '[^<]*+';
    }
    //language=RegExp
    public static function HTML_END_HEAD_TAG() : string
    {
        $excludes = [self::HTML_ELEMENT('script'), self::HTML_COMMENT()];
        return self::parseHtml($excludes) . '\\K(?:</head\\s*+>|$)';
    }
    //language=RegExp
    public static function HTML_END_BODY_TAG() : string
    {
        return '.*\\K</body\\s*+>(?=(?>[^<]*+(?:' . self::HTML_COMMENT() . ')?)*?</html\\s*+>)';
    }
    public function addElementObject(\JchOptimize\Core\Html\ElementObject $oElementObject)
    {
        $this->aElementObjects[] = $oElementObject;
    }
    public function addExclude($sExclude)
    {
        $this->aExcludes[] = $sExclude;
    }
    /**
     * @throws Exception\PregErrorException
     */
    public function processMatchesWithCallback($html, AbstractCallback $callbackObject)
    {
        $regex = $this->getHtmlSearchRegex();
        $callbackObject->setRegex($regex);
        $sProcessedHtml = \preg_replace_callback('#' . $regex . '#six', array($callbackObject, 'processMatches'), $html);
        self::throwExceptionOnPregError('JchOptimize\\Core\\Exception\\PregErrorException');
        return $sProcessedHtml;
    }
    protected function getHtmlSearchRegex() : string
    {
        $this->setCriteria();
        //language=RegExp
        $regex = self::parseHtml($this->getExcludes()) . '\\K(?:' . $this->getCriteria() . '|$)';
        return $regex;
    }
    //language=RegExp
    protected function setCriteria($bBranchReset = \true)
    {
        $aCriteria = array();
        /** @var ElementObject $oElement */
        foreach ($this->aElementObjects as $oElement) {
            $sRegex = '<';
            $aNames = \implode('|', $oElement->getNamesArray());
            $sRegex .= '(' . $aNames . ')\\b\\s*+';
            $sRegex .= $this->compileCriteria($oElement);
            $aCaptureAttributes = $oElement->getCaptureAttributesArray();
            if (!empty($aCaptureAttributes)) {
                $mValueCriteria = $oElement->getValueCriteriaRegex();
                if (\is_string($mValueCriteria)) {
                    $aValueCriteria = array('.' => $mValueCriteria);
                } else {
                    $aValueCriteria = $mValueCriteria;
                }
                foreach ($aCaptureAttributes as $sCaptureAttribute) {
                    foreach ($aValueCriteria as $sRegexKey => $sValueCriteria) {
                        if ($sValueCriteria != '' && \preg_match('#' . $sRegexKey . '#i', $sCaptureAttribute)) {
                            //If criteria is specified for attribute it must match
                            $sRegex .= '(?=' . $this->parseAttributes() . '(' . self::HTML_ATTRIBUTE_CP($sCaptureAttribute, \true, \true, $sValueCriteria) . '))';
                        } else {
                            //If no criteria specified matching is optional
                            $sRegex .= '(?=(?:' . $this->parseAttributes() . '(' . self::HTML_ATTRIBUTE_CP($sCaptureAttribute, \true, \true) . '))?)';
                        }
                    }
                }
            }
            if (!empty($aCaptureOneOrBothAttributes = $oElement->getCaptureOneOrBothAttribuesArray())) {
                //Has to be either a string for both attributes or associative array of criteria for both attributes
                $mValueCriteria = $oElement->getValueCriteriaRegex();
                if (\is_string($mValueCriteria)) {
                    $aValueCriteria = [$aCaptureOneOrBothAttributes[0] => $mValueCriteria, $aCaptureOneOrBothAttributes[1] => $mValueCriteria];
                } else {
                    $aValueCriteria = $mValueCriteria;
                }
                $sRegex .= '(?(?=' . $this->parseAttributes() . '(' . self::HTML_ATTRIBUTE_CP($aCaptureOneOrBothAttributes[0], \true, \true, $aValueCriteria[$aCaptureOneOrBothAttributes[0]]) . '))' . '(?=' . $this->parseAttributes() . '(' . self::HTML_ATTRIBUTE_CP($aCaptureOneOrBothAttributes[1], \true, \true, $aValueCriteria[$aCaptureOneOrBothAttributes[1]]) . '))?|' . '(?=' . $this->parseAttributes() . '(' . self::HTML_ATTRIBUTE_CP($aCaptureOneOrBothAttributes[1], \true, \true, $aValueCriteria[$aCaptureOneOrBothAttributes[1]]) . ')))';
            }
            $sRegex .= $this->parseAttributes();
            $sRegex .= '/?>';
            if (!$oElement->bSelfClosing) {
                if ($oElement->bCaptureContent) {
                    $sRegex .= $oElement->getValueCriteriaRegex() . '(' . self::parseHtml() . ')';
                } else {
                    $sRegex .= self::parseHtml();
                }
                $sRegex .= '</(?:' . $aNames . ')\\s*+>';
            }
            $aCriteria[] = $sRegex;
        }
        $sCriteria = \implode('|', $aCriteria);
        if ($bBranchReset) {
            $this->sCriteria = '(?|' . $sCriteria . ')';
        } else {
            $this->sCriteria = $sCriteria;
        }
    }
    //language=RegExp
    protected function compileCriteria(\JchOptimize\Core\Html\ElementObject $oElement) : string
    {
        $sCriteria = '';
        $aAttrNegCriteria = $oElement->getNegAttrCriteriaArray();
        if (!empty($aAttrNegCriteria)) {
            foreach ($aAttrNegCriteria as $sAttrNegCriteria) {
                $sCriteria .= $this->processNegCriteria($sAttrNegCriteria);
            }
        }
        $aAttrPosCriteria = $oElement->getPosAttrCriteriaArray();
        if (!empty($aAttrPosCriteria)) {
            foreach ($aAttrPosCriteria as $sAttrPosCriteria) {
                $sCriteria .= $this->processPosCriteria($sAttrPosCriteria);
            }
        }
        if ($oElement->bNegateCriteria) {
            $sCriteria = '(?!' . $sCriteria . ')';
        }
        return $sCriteria;
    }
    //language=RegExp
    protected function processNegCriteria($sCriteria) : string
    {
        return '(?!' . $this->processCriteria($sCriteria) . ')';
    }
    protected function processCriteria($sCriteria) : string
    {
        return $this->parseAttributes() . '(?:' . \str_replace('==', '\\s*+=\\s*+', $sCriteria) . ')';
    }
    //language=RegExp
    protected function parseAttributes() : string
    {
        return self::parseAttributesStatic();
    }
    //language=RegExp
    protected function processPosCriteria($sCriteria) : string
    {
        return '(?=' . $this->processCriteria($sCriteria) . ')';
    }
    protected function getExcludes() : array
    {
        return $this->aExcludes;
    }
    protected function getCriteria() : string
    {
        return $this->sCriteria;
    }
    /**
     * @throws Exception\PregErrorException
     */
    public function findMatches($sHtml, $iFlags = \PREG_PATTERN_ORDER)
    {
        \preg_match_all('#' . $this->getHtmlSearchRegex() . '#six', $sHtml, $aMatches, $iFlags);
        self::throwExceptionOnPregError('JchOptimize\\Core\\Exception\\PregErrorException');
        //Last array will always be an empty string so let's remove that
        if ($iFlags == \PREG_PATTERN_ORDER) {
            return \array_map(function ($a) {
                return \array_slice($a, 0, -1);
            }, $aMatches);
        } elseif ($iFlags == \PREG_SET_ORDER) {
            \array_pop($aMatches);
            return $aMatches;
        } else {
            return $aMatches;
        }
    }
    public function getElementWithCriteria() : string
    {
        $this->setCriteria(\false);
        return $this->sCriteria;
    }
}
