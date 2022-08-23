<?php
/**
 * @package         Articles Anywhere
 * @version         12.4.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Output;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;
use RegularLabs\Plugin\System\ArticlesAnywhere\Helpers\Pagination as PaginationHelper;
use RegularLabs\Plugin\System\ArticlesAnywhere\Params;

class Pagination
{
	/* @var Config */
	protected $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
		$this->params = $this->getParams();
	}

	public function render($position, $total)
	{


		return '';
	}

	private function getParams()
	{


		return (object) [
			'enable'         => false,
			'limit'          => 1,
			'total_limit'    => 1,
			'total_no_limit' => 1,
			'page'           => 1,
			'offset'         => 0,
			'offset_start'   => 0,
			'position'       => [],
			'show_results'   => false,
		];
	}

}
