<?php

/**
 * @package     JchOptimize\Core\Css\Sprite\Handler
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
namespace JchOptimize\Core\Css\Sprite\Handler;

use JchOptimize\Core\Css\Sprite\HandlerInterface;
use Joomla\Registry\Registry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
abstract class AbstractHandler implements HandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var Registry
     */
    protected $params;
    /**
     * @var array
     */
    protected $options;
    /**
     * @var array
     */
    public $spriteFormats = [];
    public function __construct(Registry $params, array $options)
    {
        $this->params = $params;
        $this->options = $options;
    }
}
