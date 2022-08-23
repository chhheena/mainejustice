<?php
/**
* @title		Minitek Wall
* @copyright   	Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license   	GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallLibBaseMasonryJavascript
{
	public function loadMasonryJavascript($masonry_params, $widgetID, $totalCount)
	{
		$document = JFactory::getDocument();

		$javascript = "
			;(function($) {
				$(function() {
		";

			$javascript .= $this->loadJavascriptVars($masonry_params, $widgetID, $totalCount);

			$javascript .= $this->initializeWall($masonry_params, $widgetID);

			if ($masonry_params['mas_hb'])
			{
				$javascript .= $this->initializeHoverBox($masonry_params['mas_hb_effect']);
			}

			$javascript .= $this->initializeFiltersSortings($widgetID);

			if ($masonry_params['mas_pagination'])
			{
				$javascript .= $this->initializePagination();
			}

			if ($masonry_params['mas_pagination'] == '1')
			{
				$javascript .= $this->initializeAppendPagination($widgetID);
			}
			else if ($masonry_params['mas_pagination'] == '2')
			{
				$javascript .= $this->initializeArrowsPagination($widgetID);
			}
			else if ($masonry_params['mas_pagination'] == '3')
			{
				$javascript .= $this->initializePagesPagination($widgetID);
			}
			else if ($masonry_params['mas_pagination'] == '4')
			{
				$javascript .= $this->initializeInfinitePagination($widgetID);
			}

		$javascript .= "
				})
			})(jQuery);	
		";

		$document->addCustomTag('<script type="text/javascript">'.$javascript.'</script>');
	}

	public function loadJavascriptVars($masonry_params, $widgetID, $totalCount)
	{
		$utilities = new MinitekWallLibUtilities;
		$source_id = $utilities->getSourceID($widgetID);

		$site_path = JURI::root();
		$pagination = $masonry_params['mas_pagination'];
		$startLimit = $masonry_params['mas_starting_limit'];
		$pageLimit = $masonry_params['mas_page_limit'];
		$globalLimit = $masonry_params['mas_global_limit'];
		$lang = JFactory::getLanguage()->getTag();
		$lang = explode('-', $lang);
		$lang = $lang[0];

		if ($startLimit > $globalLimit)
		{
			$startLimit = $globalLimit;
		}

		if ($totalCount < $startLimit)
		{
			$startLimit = $totalCount;
		}

		$lastPage = ceil(($totalCount - $startLimit) / $pageLimit);
		$gridType = $masonry_params['mas_grid'];

		if ($gridType === '98o')
		{
			$grid_type = 'columns';
		}
		else if ($gridType === '99v')
		{
			$grid_type = 'list';
		}
		else
		{
			$grid_type = 'masonry';
		}

		$hoverBox = $masonry_params['mas_hb'];
		$layoutMode = 'packery';

		if (array_key_exists('mas_layout_mode', $masonry_params)) 
		{
			$layoutMode = $masonry_params['mas_layout_mode'];
		}

		$wall_category_filters = $masonry_params['mas_category_filters'];
		$wall_tag_filters = $masonry_params['mas_tag_filters'];
		$wall_date_filters = $masonry_params['mas_date_filters'];
		$wall_sortings = 
			($masonry_params['mas_title_sorting']
			|| $masonry_params['mas_category_sorting']
			|| $masonry_params['mas_author_sorting']
			|| $masonry_params['mas_date_sorting']
			|| $masonry_params['mas_hits_sorting']
			|| $masonry_params['mas_sorting_direction'])
			? true : false;

		$filters_enabled = ($wall_category_filters || $wall_tag_filters || $wall_date_filters || $wall_sortings) ? 'true' : 'false';
		$filtersActive = (isset($masonry_params['mas_pag_keep_active']) && !$masonry_params['mas_pag_keep_active']) ? 'no' : 'yes';
		$filters_mode = isset($masonry_params['mas_filters_mode']) ? $masonry_params['mas_filters_mode'] : 'dynamic';
		$closeFilters = isset($masonry_params['mas_close_filters']) ? $masonry_params['mas_close_filters'] : 0;
		$equalHeight = isset($masonry_params['mas_force_equal_height']) ? $masonry_params['mas_force_equal_height'] : 0;
		$scrollToTop = isset($masonry_params['mas_pag_scroll_to_top']) ? $masonry_params['mas_pag_scroll_to_top'] : 0;

		$javascript = "
			// Global variables
			var site_path = '".$site_path."';
			var lang = '".$lang."';
			var pageLimit = '".$pageLimit."';
			var lastPage = '".$lastPage."';
			var endPage = parseInt(lastPage) + 2;
			var pagination = '".$pagination."';
			var filtersEnabled = '".$filters_enabled."';
			var filtersMode = '".$filters_mode."';
			var filtersActive = '".$filtersActive."';
			var closeFilters = ".$closeFilters.";
			var scrollToTop = ".$scrollToTop.";
			var _container = $('#mnwall_container_".$widgetID."');
			var gridType = parseInt('".$gridType."', 10);
			var grid_type = '".$grid_type."';
			var layoutMode = '".$layoutMode."';
			var hoverBox = '".$hoverBox."';
			var db_position_columns = '".$masonry_params['mas_db_position_columns']."';
			var equalHeight = '".$equalHeight."';
			var sortBy = _container.attr('data-order');
			var sortDirection = _container.attr('data-direction');
			sortDirection = (sortDirection == null) ? '' : sortDirection = sortDirection.toLowerCase();
			var sortAscending = sortDirection == 'asc' ? true : false;
			var source_id = '".$source_id."';

			if (sortBy == 'RAND()' || sortBy == 'rand' || sortBy == 'random' || source_id == 'rss')
			{
				sortBy = ['index'];
				sortAscending = true;
			}
			else
			{
				sortBy = [sortBy, 'id', 'title'];
			}			
		";

		return $javascript;
	}

	public function initializeWall($masonry_params, $widgetID)
	{
		$hiddenStyle = '';
		$visibleStyle = '';

		if (array_key_exists('mas_effects', $masonry_params))
		{
			$mas_effects = $masonry_params['mas_effects'];

			if (is_array($mas_effects))
			{
				if (in_array('fade', $mas_effects))
				{
					$hiddenStyle .= 'opacity: 0, ';
					$visibleStyle .= 'opacity: 1, ';
				}

				if (in_array('scale', $mas_effects))
				{
					$hiddenStyle .= 'transform: \'scale(0.001)\'';
					$visibleStyle .= 'transform: \'scale(1)\'';
				}
			}
			else
			{
				$hiddenStyle .= 'opacity: 0';
				$visibleStyle .= 'opacity: 1';
			}
		}
		else
		{
			$hiddenStyle .= 'opacity: 0';
			$visibleStyle .= 'opacity: 1';
		}

		$effect = "
			hiddenStyle: {
				".$hiddenStyle."
			},
			visibleStyle: {
				".$visibleStyle."
			}
		";

		$transitionDuration = 400;

		if (array_key_exists('mas_transition_duration', $masonry_params))
		{
			$transitionDuration = (int)$masonry_params['mas_transition_duration'];
		}

		$transitionStagger = 0;

		if (array_key_exists('mas_transition_stagger', $masonry_params))
		{
			$transitionStagger = (int)$masonry_params['mas_transition_stagger'];
		}

		$javascript = "
			// Create spinner
			var loader_opts = {
				lines: 9,
				length: 4,
				width: 3,
				radius: 3,
				corners: 1,
				rotate: 0,
				direction: 1,
				color: '#000',
				speed: 1,
				trail: 52,
				shadow: false,
				hwaccel: false,
				className: 'spinner',
				zIndex: 2e9,
				top: '50%',
				left: '50%'
			};

			$('#mnwall_loader_".$widgetID."').append(new Spinner(loader_opts).spin().el).show();

			var transitionDuration = ".$transitionDuration.";
			var transitionStagger = ".$transitionStagger.";

			// Initialize wall
			var _wall = $('#mnwall_iso_container_".$widgetID."').imagesLoaded(function()
			{
				// Instantiate isotope
				_wall.isotope({
					// General
					itemSelector: '.mnwall-item',
					layoutMode: layoutMode,
					// Vertical list
					vertical: {
						horizontalAlignment: 0
					},
					initLayout: false,
					stagger: transitionStagger,
					transitionDuration: transitionDuration,
					".$effect."
				});
			});

			// Initiate layout
			$('.mnwall_container').show();

			_wall.isotope({
				getSortData: {
					ordering: '[data-ordering] parseInt',
					fordering: '[data-fordering] parseInt',
					hits: '[data-hits] parseInt',
					title: '[data-title]',
					id: '[data-id] parseInt',
					index: '[data-index] parseInt',
					alias: '[data-alias]',
					date: '[data-date]',
					modified: '[data-modified]',
					start: '[data-start]',
					finish: '[data-finish]',
					category: '[data-category]',
					author: '[data-author]',
					rating: '[data-rating] parseFloat',
					comments: '[data-comments] parseInt',
					sales: '[data-sales] parseInt',
					points: '[data-points] parseInt',
					friends: '[data-friends] parseInt',
					members: '[data-members] parseInt',
					confirmed: '[data-confirmed] parseInt',
					tickets: '[data-tickets] parseInt'
				},
				sortBy: sortBy,
				sortAscending: sortAscending
			});

			if (pagination == '4') 
			{
				if (_container.find('.mnwall_more_results').visible() && !_container.find('.more-results').hasClass('mnwall-loading'))
				{
					infiniteWall();
				}
			}

			_wall.one('arrangeComplete', function() 
			{
				fixEqualHeights('all');
				_container.css('opacity', 1);
				$('#mnwall_loader_".$widgetID."').hide();
			});

			// Handle resize
			var _temp;

			$(window).resize(function()
			{
				fixEqualHeights('all');
				clearTimeout(_temp);
    			_temp = setTimeout(doneBrowserResizing, 500);
			});

			function doneBrowserResizing()
			{
  				_wall.isotope();
			}

			function fixEqualHeights(items)
			{
				if (gridType == '98' && layoutMode == 'fitRows' && db_position_columns == 'below' && equalHeight > 0)
				{
					var max_height = 0;

					if (items == 'all')
					{
						_container.find('.mnwall-item-inner').each(function(index, item) 
						{
							var _this_item_inner = $(this);
							_this_item_inner.css('height', 'auto');

							if (_this_item_inner.outerHeight() > max_height)
							{
								max_height = _this_item_inner.outerHeight();
							}
						});
					}
					else
					{
						$(items).each(function(index, item) 
						{
							var _this_item_inner = $(this).find('.mnwall-item-inner');
							_this_item_inner.css('height', 'auto');

							if (_this_item_inner.outerHeight() > max_height)
							{
								max_height = _this_item_inner.outerHeight();
							}
						});
					}

					_container.find('.mnwall-item-inner').css('height', max_height + 'px');
					setTimeout(function(){ _wall.isotope(); }, 1);
				}
			}
		";

		return $javascript;
	}

	public function initializeHoverBox($hoverBoxEffect)
	{
		$javascript = "
			// Hover effects
			var hoverBoxEffect = '".$hoverBoxEffect."';

			// Hover box trigger
			if (hoverBox == '1') 
			{
				var triggerHoverBox = function triggerHoverBox() 
				{
					if (gridType == 99 || gridType == 98) 
					{
						// Hover effects
						_container.find('.mnwall-item-inner-cont')
						.mouseenter(function(e) 
						{
							if (hoverBoxEffect == 'no') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().addClass('hoverShow');
							}
							else if (hoverBoxEffect == '1') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().addClass('hoverFadeIn');
							}
							else if (hoverBoxEffect == '2') 
							{
								$(this).closest('.mnwall-item').find('.mnwall-cover').stop().addClass('perspective');
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-img-div').stop().addClass('flip flipY hoverFlipY');
							}
							else if (hoverBoxEffect == '3') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-cover').stop().addClass('perspective');
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-img-div').stop().addClass('flip flipX hoverFlipX');
							}
							else if (hoverBoxEffect == '4') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().addClass('slideInRight');
							}
							else if (hoverBoxEffect == '5') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().addClass('slideInLeft');
							}
							else if (hoverBoxEffect == '6') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().addClass('slideInTop');
							}
							else if (hoverBoxEffect == '7') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().addClass('slideInBottom');
							}
							else if (hoverBoxEffect == '8') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().addClass('mnwzoomIn');
							}
						}).mouseleave(function (e) 
						{
							if (hoverBoxEffect == 'no') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().removeClass('hoverShow');
							}
							else if (hoverBoxEffect == '1') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().removeClass('hoverFadeIn');
							}
							else if (hoverBoxEffect == '2') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-img-div').stop().removeClass('hoverFlipY');
							}
							else if (hoverBoxEffect == '3') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-img-div').stop().removeClass('hoverFlipX');
							}
							else if (hoverBoxEffect == '4') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().removeClass('slideInRight');
							}
							else if (hoverBoxEffect == '5') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().removeClass('slideInLeft');
							}
							else if (hoverBoxEffect == '6') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().removeClass('slideInTop');
							}
							else if (hoverBoxEffect == '7') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().removeClass('slideInBottom');
							}
							else if (hoverBoxEffect == '8') 
							{
								$(this).closest('.mnwall-item-outer-cont').find('.mnwall-hover-box').stop().removeClass('mnwzoomIn');
							}
						});
					}

					if (gridType != 98 && gridType != 99) 
					{
						// Hover effects
						_container.find('.mnwall-item')
						.mouseenter(function(e) 
						{
							if (hoverBoxEffect == 'no') 
							{
								$(this).find('.mnwall-hover-box').stop().addClass('hoverShow');
							}
							else if (hoverBoxEffect == '1') 
							{
								$(this).find('.mnwall-hover-box').stop().addClass('hoverFadeIn');
							}
							else if (hoverBoxEffect == '2') 
							{
								$(this).stop().addClass('perspective');
								$(this).find('.mnwall-item-outer-cont').stop().addClass('flip flipY hoverFlipY');
							}
							else if (hoverBoxEffect == '3') 
							{
								$(this).stop().addClass('perspective');
								$(this).find('.mnwall-item-outer-cont').stop().addClass('flip flipX hoverFlipX');
							}
							else if (hoverBoxEffect == '4') 
							{
								$(this).find('.mnwall-hover-box').stop().addClass('animated slideInRight');
							}
							else if (hoverBoxEffect == '5') 
							{
								$(this).find('.mnwall-hover-box').stop().addClass('animated slideInLeft');
							}
							else if (hoverBoxEffect == '6') 
							{
								$(this).find('.mnwall-hover-box').stop().addClass('animated slideInTop');
							}
							else if (hoverBoxEffect == '7') 
							{
								$(this).find('.mnwall-hover-box').stop().addClass('animated slideInBottom');
							}
							else if (hoverBoxEffect == '8') 
							{
								$(this).find('.mnwall-hover-box').stop().addClass('animated mnwzoomIn');
							}
						}).mouseleave(function (e) 
						{
							if (hoverBoxEffect == 'no') 
							{
								$(this).find('.mnwall-hover-box').stop().removeClass('hoverShow');
							}
							else if (hoverBoxEffect == '1') 
							{
								$(this).find('.mnwall-hover-box').stop().removeClass('hoverFadeIn');
							}
							else if (hoverBoxEffect == '2') 
							{
								$(this).find('.mnwall-item-outer-cont').stop().removeClass('hoverFlipY');
							}
							else if (hoverBoxEffect == '3') 
							{
								$(this).find('.mnwall-item-outer-cont').stop().removeClass('hoverFlipX');
							}
							else if (hoverBoxEffect == '4') 
							{
								$(this).find('.mnwall-hover-box').stop().removeClass('slideInRight');
							}
							else if (hoverBoxEffect == '5') 
							{
								$(this).find('.mnwall-hover-box').stop().removeClass('slideInLeft');
							}
							else if (hoverBoxEffect == '6') 
							{
								$(this).find('.mnwall-hover-box').stop().removeClass('slideInTop');
							}
							else if (hoverBoxEffect == '7') 
							{
								$(this).find('.mnwall-hover-box').stop().removeClass('slideInBottom');
							}
							else if (hoverBoxEffect == '8') 
							{
								$(this).find('.mnwall-hover-box').stop().removeClass('mnwzoomIn');
							}
						});
					}
				}

				triggerHoverBox();
			}
		";

		return $javascript;
	}

	public function initializeFiltersSortings($widgetID)
	{
		$javascript = "
			// Filters
			var filters = {};

			$('#mnwall_iso_filters_".$widgetID."').on('click', '.mnwall-filter', function(event)
			{
				event.preventDefault();

				// Show filter name in dropdown
				if ($(this).parents('.mnwall_iso_dropdown').length)
				{
					var data_filter_attr = $(this).attr('data-filter');

					if (typeof data_filter_attr !== typeof undefined && data_filter_attr !== false) 
					{
    					if (data_filter_attr.length)
						{
							var filter_text = $(this).text();
						}
						else
						{
							var filter_text = $(this).closest('.mnwall_iso_dropdown').find('.dropdown-label span').attr('data-label');
						}

						$(this).closest('.mnwall_iso_dropdown').find('.dropdown-label span span').text(filter_text);
					}
				}

				// Show reset button in pagination
				_container.find('.mnwall-reset-btn').css('display','inline-block');

				var \$this = $(this);

				// get group key
				var \$buttonGroup = \$this.parents('.button-group');
				var filterGroup = \$buttonGroup.attr('data-filter-group');

				// set filter for group
				filters[ filterGroup ] = \$this.attr('data-filter');

				// combine filters
				var filterValue = '';

				for (var prop in filters) 
				{
					filterValue += filters[ prop ];
				}

				// set filter for Isotope
				_wall.isotope({
					filter: filterValue
				});

				// Hide reset button in pagination
				if (filterValue == '')
				{
					_container.find('.mnwall-reset-btn').hide();
				}
			});

			// Change active class on filter buttons
			var active_Filters = function active_Filters() 
			{
				var \$activeFilters = _container.find('.button-group').each(function(i, buttonGroup) 
				{
					var \$buttonGroup = $(buttonGroup);

					\$buttonGroup.on('click', 'a', function(event) 
					{
						event.preventDefault();
						\$buttonGroup.find('.mnw_filter_active').removeClass('mnw_filter_active');
						$(this).addClass('mnw_filter_active');
					});
				});
			};

			active_Filters();

			// Dropdown filter list
			var dropdown_Filters = function dropdown_Filters() 
			{
				var \$dropdownFilters = _container.find('.mnwall_iso_filters .mnwall_iso_dropdown').each(function(i, dropdownGroup) 
				{
					var \$dropdownGroup = $(dropdownGroup);

					\$dropdownGroup.on('click', '.dropdown-label', function(event) 
					{
						event.preventDefault();

						if ($(this).closest('.mnwall_iso_dropdown').hasClass('expanded'))
						{
							var filter_open = true;
						}
						else
						{
							var filter_open = false;
						}

						$('.mnwall_iso_dropdown').removeClass('expanded');

						if (!filter_open)
						{
							$(this).closest('.mnwall_iso_dropdown').addClass('expanded');
						}
					});
				});

				$(document).mouseup(function (e)
				{
					_target = e.target;
					var \$dropdowncontainer = _container.find('.mnwall_iso_dropdown');
					var \$filtercontainer = _container.find('.button-group');
					var \$sortingcontainer = _container.find('.sorting-group');

					if (closeFilters === 0)
					{
						// Close dropdown when click outside
						if (\$filtercontainer.has(e.target).length === 0
							&& \$sortingcontainer.has(e.target).length === 0
							&& _target.closest('div')
							&& !_target.closest('div').classList.contains('dropdown-label')
							&& \$dropdowncontainer.has(e.target).length === 0)
						{
							\$dropdowncontainer.removeClass('expanded');
						}
					}
					else
					{
						// Close dropdown when click inside
						if ((\$filtercontainer.has(e.target).length === 0
							&& \$sortingcontainer.has(e.target).length === 0
							&& _target.closest('div')
							&& !_target.closest('div').classList.contains('dropdown-label')
							&& \$dropdowncontainer.has(e.target).length === 0)
							|| _target.classList.contains('mnwall-filter'))
						{
							\$dropdowncontainer.removeClass('expanded');
						}
					}
				});
			};

			dropdown_Filters();

			// Bind sort button click
			_container.find('.sorting-group-filters').on('click', '.mnwall-filter', function(event) 
			{
				event.preventDefault();

				// Show sorting name in dropdown
				if ($(this).closest('.mnwall_iso_dropdown'))
				{
					var sorting_text = $(this).text();
					$(this).closest('.mnwall_iso_dropdown').find('.dropdown-label span span').text(sorting_text);
				}
				
				var sortValue = $(this).attr('data-sort-value');

				// Add second ordering: id
				sortValue = [sortValue, 'id'];

				// set filter for Isotope
				_wall.isotope({
					sortBy: sortValue
				});
			});

			// Change active class on sorting filters
			_container.find('.sorting-group-filters').each(function(i, sortingGroup) 
			{
				var \$sortingGroup = $(sortingGroup);

				\$sortingGroup.on('click', '.mnwall-filter', function() 
				{
					\$sortingGroup.find('.mnw_filter_active').removeClass('mnw_filter_active');
					$(this).addClass('mnw_filter_active');
				});
			});

			// Bind sorting direction button click
			_container.find('.sorting-group-direction').on('click', '.mnwall-filter', function(event) 
			{
				event.preventDefault();

				// Show sorting name in dropdown
				if ($(this).closest('.mnwall_iso_dropdown'))
				{
					var sorting_text = $(this).text();
					$(this).closest('.mnwall_iso_dropdown').find('.dropdown-label span span').text(sorting_text);
				}

				var sortDirection = $(this).attr('data-sort-value');

				if (sortDirection == 'asc') 
				{
					sort_Direction = true;
				} 
				else 
				{
					sort_Direction = false;
				}

				// set direction
				_wall.isotope({
					sortAscending: sort_Direction
				});
			});

			// Change active class on sorting direction
			_container.find('.sorting-group-direction').each(function(i, sortingDirection) 
			{
				var \$sortingDirection = $(sortingDirection);

				\$sortingDirection.on('click', '.mnwall-filter', function() 
				{
					\$sortingDirection.find('.mnw_filter_active').removeClass('mnw_filter_active');
					$(this).addClass('mnw_filter_active');
				});
			});

			// Dropdown sorting list
			var dropdown_Sortings = function dropdown_Sortings() 
			{
				var \$dropdownSortings = _container.find('.mnwall_iso_sortings .mnwall_iso_dropdown').each(function(i, dropdownSorting) 
				{
					var \$dropdownSorting = $(dropdownSorting);

					\$dropdownSorting.on('click', '.dropdown-label', function(event) 
					{
						event.preventDefault();

						if ($(this).closest('.mnwall_iso_dropdown').hasClass('expanded'))
						{
							var sorting_open = true;
						}
						else
						{
							var sorting_open = false;
						}

						$('.mnwall_iso_dropdown').removeClass('expanded');

						if (!sorting_open)
						{
							$(this).closest('.mnwall_iso_dropdown').addClass('expanded');
						}
					});
				});
			};

			dropdown_Sortings();

			// Reset Filters and sortings
			function reset_filters()
			{
				var \$resetFilters = _container.find('.button-group').each(function(i, buttonGroup) 
				{
					var \$buttonGroup = $(buttonGroup);
					\$buttonGroup.find('.mnw_filter_active').removeClass('mnw_filter_active');
					\$buttonGroup.find('li:first-child a').addClass('mnw_filter_active');

					// Reset filters
					var filterGroup = \$buttonGroup.attr('data-filter-group');
					filters[ filterGroup ] = '';

					// Hide reset button in pagination
					_container.find('.mnwall-reset-btn').hide();
				});

				// Reset dropdown filters text
				_container.find('.mnwall_iso_dropdown').each(function(i, dropdownGroup) 
				{
					var filter_text = $(dropdownGroup).find('.dropdown-label span').attr('data-label');
					$(dropdownGroup).find('.dropdown-label span span').text(filter_text);
				});

				// Get first item in sortBy array
				var \$resetSortings = _container.find('.sorting-group-filters').each(function(i, sortingGroup) 
				{
					var \$sortingGroup = $(sortingGroup);
					\$sortingGroup.find('.mnw_filter_active').removeClass('mnw_filter_active');
					\$sortingGroup.find('li a[data-sort-value=\"'+sortBy[0]+'\"]').addClass('mnw_filter_active');
				});

				var \$resetSortingDirection = _container.find('.sorting-group-direction').each(function(i, sortingGroupDirection) 
				{
					var \$sortingGroupDirection = $(sortingGroupDirection);
					\$sortingGroupDirection.find('.mnw_filter_active').removeClass('mnw_filter_active');
					\$sortingGroupDirection.find('li a[data-sort-value=\"'+sortDirection+'\"]').addClass('mnw_filter_active');
				});

				// set filter for Isotope
				_wall.isotope({
					filter: '',
					sortBy: sortBy,
					sortAscending: sortAscending
				});		
			}

			$('#mnwall_reset_".$widgetID.", #mnwall_container_".$widgetID." .mnwall-reset-btn').on('click', '', function(event)
			{
				event.preventDefault();

				reset_filters();
			});
		";

		return $javascript;
	}

	public function initializePagination()
	{
		$javascript = "
			// Last page
			if (_container.find('.more-results.mnw-all').attr('data-page') == endPage) 
			{
				_container.find('.more-results.mnw-all').addClass('disabled');
				_container.find('.more-results.mnw-all span.more-results').hide();
				_container.find('.more-results.mnw-all span.no-results').show();
				_container.find('.more-results.mnw-all img').hide();
			}

			// Create spinner
			var opts = {
				lines: 9,
				length: 4,
				width: 3,
				radius: 3,
				corners: 1,
				rotate: 0,
				direction: 1,
				color: '#000',
				speed: 1,
				trail: 52,
				shadow: false,
				hwaccel: false,
				className: 'spinner',
				zIndex: 2e9,
				top: '50%',
				left: '50%'
			};

			_container.find('.mas_loader').append(new Spinner(opts).spin().el);
		";

		return $javascript;
	}

	public function initializeAppendPagination($widgetID)
	{
		$javascript = "

			// Load more (Append) pagination
			_container.find('.more-results.mnw-all').on('click', function(event)
			{
				event.preventDefault();

				if ($(this).hasClass('disabled') || $(this).hasClass('mnwall-loading')) 
				{
					return false;
				}

				// Find page
				var page = $(this).attr('data-page');
				page = parseInt(page);
				var new_page = page + 1;

				// Increment page in data-page
				$(this).attr('data-page', new_page);

				// Show loader
				_container.find('.more-results').addClass('mnwall-loading');
				_container.find('.more-results span.more-results').hide();
				_container.find('.mnwall_append_loader').show();

				// Ajax request
				$.ajax({
					type: 'POST',
					url: site_path+'index.php?option=com_minitekwall&task=masonry.getContent&widget_id=".$widgetID."&page=' + page + '&grid=' + grid_type + '&lang=' + lang,
					success: function(msg)
					{
						if (msg.length > 3)
						{
							var newItems = $(msg).appendTo(_wall);
							newItems.css({'visibility':'hidden','left':'-9999px','top':'-9999px'});

							imagesLoaded(_wall, function() 
							{
								// Reset filters
								if (filtersEnabled == 'true' && filtersActive == 'no')
								{
									reset_filters();
								}

								// Append items
								newItems.css({'visibility':'visible'});
								_wall.isotope('appended', newItems);
								_wall.isotope('updateSortData').isotope();
								fixEqualHeights('all');

								// Hover box trigger
								if (hoverBox == '1') 
								{
									triggerHoverBox();
								}

								if (filtersEnabled == 'true' && filtersMode == 'dynamic')
								{
									// Store active filters
									if (filtersActive == 'yes')
									{
										var _activeButtonCategory = _container.find('.button-group-category').find('.mnw_filter_active').attr('data-filter');
										var _activeButtonTag = _container.find('.button-group-tag').find('.mnw_filter_active').attr('data-filter');
										var _activeButtonLocation = _container.find('.button-group-location').find('.mnw_filter_active').attr('data-filter');
										var _activeButtonDate = _container.find('.button-group-date').find('.mnw_filter_active').attr('data-filter');
									}

									// Update filters
									$.ajax({
										type: 'POST',
										url: site_path+'index.php?option=com_minitekwall&task=masonry.getFilters&widget_id=".$widgetID."&page=' + page + '&pagination=' + pagination + '&lang=' + lang,
										success: function(msg)
										{
											if (msg.length > 3)
											{
												// Add new filters
												_container.find('.mnwall_iso_filters').html(msg);

												// Restore active filters
												if (filtersActive == 'yes')
												{
													_container.find('.button-group-category').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonCategory && _activeButtonCategory.length)
													{
														var cat_text = _container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').text();
														
														if (cat_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.cat-label span span').text(cat_text);
														}
													}

													_container.find('.button-group-tag').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonTag && _activeButtonTag.length)
													{
														var tag_text = _container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').text();
														
														if (tag_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.tag-label span span').text(tag_text);
														}
													}

													_container.find('.button-group-location').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonLocation && _activeButtonLocation.length)
													{
														var location_text = _container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').text();
														
														if (location_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.location-label span span').text(location_text);
														}
													}

													_container.find('.button-group-date').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonDate && _activeButtonDate.length)
													{
														var date_text = _container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').text();
														
														if (date_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.date-label span span').text(date_text);
														}
													}
												}
											}

											active_Filters();
											dropdown_Filters();
										}
									});
								}

								// Hide loader
								_container.find('.more-results').removeClass('mnwall-loading');
								_container.find('.more-results span.more-results').show();
								_container.find('.mnwall_append_loader').hide();
								_container.find('.more-results').blur();

								// Deduct remaining items number in button
								var _remaining = _container.find('.mnw-total-items').text();
								var remaining = parseInt(_remaining) - parseInt(pageLimit);
								_container.find('.mnw-total-items').html(remaining);

								// Last page
								if (_container.find('.more-results').attr('data-page') == endPage) 
								{
									_container.find('.more-results').addClass('disabled');
									_container.find('.mnw-total-items').html('0');
									_container.find('.more-results span.more-results').hide();
									_container.find('.more-results span.no-results').show();
									_container.find('.more-results img').hide();
								}
							});
						}
						else
						{
							_container.find('.more-results').addClass('disabled');
							_container.find('.more-results span.more-results').hide();
							_container.find('.more-results span.no-results').show();
							_container.find('.more-results img').hide();
						}
					}
				});
			});
		";

		return $javascript;
	}

	public function initializeInfinitePagination($widgetID)
	{
		$javascript = "
			_container.find('.more-results.mnw-all').bind('inview', function(event, isInView, visiblePartX, visiblePartY) 
			{
				if (isInView) 
				{
					// element is now visible in the viewport
					if (visiblePartY == 'top') 
					{} 
					else if (visiblePartY == 'bottom') 
					{} 
					else 
					{
						if (!_container.find('.more-results').hasClass('mnwall-loading'))
						{
							infiniteWall();
						}
					}
				}
			});

			// Infinite pagination
			function infiniteWall()
			{
				\$this = _container.find('.more-results.mnw-all');

				if (\$this.hasClass('disabled') || _container.find('.more-results').hasClass('mnwall-loading')) 
				{
					return false;
				}

				// Find page
				var page = \$this.attr('data-page');
				page = parseInt(page);
				var new_page = page + 1;

				// Check if there is a pending ajax request
				if (typeof ajax_request !== 'undefined') 
				{
					ajax_request.abort();
					_container.find('.more-results span.more-results').show();
					_container.find('.mnwall_append_loader').hide();
				}

				// Show loader
				_container.find('.more-results').addClass('mnwall-loading');
				_container.find('.more-results span.more-results').hide();
				_container.find('.mnwall_append_loader').show();

				// Increment page in data-page
				\$this.attr('data-page', new_page);

				// Ajax request
				var ajax_request = $.ajax({
					type: 'POST',
					url: site_path+'index.php?option=com_minitekwall&task=masonry.getContent&widget_id=".$widgetID."&page=' + page + '&grid=' + grid_type + '&lang=' + lang,
					success: function(msg)
					{
						if (msg.length > 3)
						{
							var newItems = $(msg).appendTo(_wall);
							newItems.css({'visibility':'hidden','left':'-9999px','top':'-9999px'});

							imagesLoaded(_wall, function() 
							{
								// Reset filters
								if (filtersEnabled == 'true' && filtersActive == 'no')
								{
									reset_filters();
								}

								// Append items
								newItems.css({'visibility':'visible'});
								_wall.isotope('appended', newItems);
								_wall.isotope('updateSortData').isotope();
								fixEqualHeights('all');

								// Hover box trigger
								if (hoverBox == '1') 
								{
									triggerHoverBox();
								}

								if (filtersEnabled == 'true' && filtersMode == 'dynamic')
								{
									// Store active filters
									if (filtersActive == 'yes')
									{
										var _activeButtonCategory = _container.find('.button-group-category').find('.mnw_filter_active').attr('data-filter');
										var _activeButtonTag = _container.find('.button-group-tag').find('.mnw_filter_active').attr('data-filter');
										var _activeButtonLocation = _container.find('.button-group-location').find('.mnw_filter_active').attr('data-filter');
										var _activeButtonDate = _container.find('.button-group-date').find('.mnw_filter_active').attr('data-filter');
									}

									// Update filters
									$.ajax({
										type: 'POST',
										url: site_path+'index.php?option=com_minitekwall&task=masonry.getFilters&widget_id=".$widgetID."&page=' + page + '&pagination=' + pagination + '&lang=' + lang,
										success: function(msg)
										{
											if (msg.length > 3)
											{
												// Add new filters
												_container.find('.mnwall_iso_filters').html(msg);

												// Restore active filters
												if (filtersActive == 'yes')
												{
													_container.find('.button-group-category').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonCategory && _activeButtonCategory.length)
													{
														var cat_text = _container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').text();
														
														if (cat_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.cat-label span span').text(cat_text);
														}
													}

													_container.find('.button-group-tag').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonTag && _activeButtonTag.length)
													{
														var tag_text = _container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').text();
														
														if (tag_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.tag-label span span').text(tag_text);
														}
													}

													_container.find('.button-group-location').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonLocation && _activeButtonLocation.length)
													{
														var location_text = _container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').text();
														
														if (location_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.location-label span span').text(location_text);
														}
													}

													_container.find('.button-group-date').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonDate && _activeButtonDate.length)
													{
														var date_text = _container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').text();
														
														if (date_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.date-label span span').text(date_text);
														}
													}
												}
											}

											active_Filters();
											dropdown_Filters();
										}
									});
								}

								// Hide loader
								_container.find('.more-results').removeClass('mnwall-loading');
								_container.find('.more-results span.more-results').show();
								_container.find('.mnwall_append_loader').hide();

								// Last page
								if (_container.find('.more-results').attr('data-page') == endPage) 
								{
									_container.find('.more-results').addClass('disabled');
									_container.find('.more-results span.more-results').hide();
									_container.find('.more-results span.no-results').show();
									_container.find('.more-results img').hide();
								}

								// Run function again until load more button is out of viewport
								if (_container.find('.mnwall_more_results').visible())
								{
									infiniteWall();
								}
							});
						}
						else
						{
							_container.find('.more-results').addClass('disabled');
							_container.find('.more-results span.more-results').hide();
							_container.find('.more-results span.no-results').show();
							_container.find('.more-results img').hide();
						}
					}
				});
			}
		";

		return $javascript;
	}

	public function initializeArrowsPagination($widgetID)
	{
		$javascript = "
			var _activeButtonCategory;
			var _activeButtonTag;
			var _activeButtonLocation;
			var _activeButtonDate;

			// Previous arrow pagination
			_container.find('.mnwall_arrow_prev').on('click', function(event)
			{
				event.preventDefault();

				var current = $(this);

				if ($(this).hasClass('disabled') || $(this).hasClass('mnwall-loading') || _container.find('.mnwall_arrow_next').hasClass('mnwall-loading')) 
				{
					return false;
				}

				// Find page
				var page = $(this).attr('data-page');
				page = parseInt(page);
				var new_page = page - 1;
				var next_page = page + 1;

				// Check if there is a pending ajax request
				if (typeof ajax_request !== 'undefined') 
				{
					ajax_request.abort();
					_container.find('.mnwall_arrow_next').removeClass('mnwall-loading');
					_container.find('.more-results').show();
					_container.find('.mnwall_arrow_loader').hide();
				}

				// Show loader
				$(this).addClass('mnwall-loading');
				current.find('.more-results').hide();
				current.find('.mnwall_arrow_loader').show();

				// Ajax request
				var ajax_request = $.ajax({
					type: 'POST',
					url: site_path+'index.php?option=com_minitekwall&task=masonry.getContent&widget_id=".$widgetID."&page=' + page + '&grid=' + grid_type + '&lang=' + lang,
					success: function(msg)
					{
						if (msg.length > 3)
						{
							// Decrease page in link id
							_container.find('.mnwall_arrow_prev').attr('data-page', new_page);
							_container.find('.mnwall_arrow_next').attr('data-page', next_page);

							// Reset filters
							if (filtersEnabled == 'true' && filtersActive == 'no')
							{
								reset_filters();
							}

							// Append items
							var elems = _wall.isotope('getItemElements');
							var newItems = $(msg).appendTo(_wall);
							newItems.css({'visibility':'hidden','left':'-9999px','top':'-9999px'});

							imagesLoaded(_wall, function() 
							{
								_wall.isotope('remove', elems);
								newItems.css({'visibility':'visible'});
								_wall.isotope('insert', newItems);
								_wall.isotope('updateSortData').isotope();
								fixEqualHeights(newItems);

								// Hover box trigger
								if (hoverBox == '1') 
								{
									triggerHoverBox();
								}

								if (filtersEnabled == 'true' && filtersMode == 'dynamic')
								{
									// Store active filters
									if (filtersActive == 'yes')
									{
										if (undefined !== _container.find('.button-group-category').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonCategory = _container.find('.button-group-category').find('.mnw_filter_active').attr('data-filter');
										}

										if (undefined !== _container.find('.button-group-tag').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonTag = _container.find('.button-group-tag').find('.mnw_filter_active').attr('data-filter');
										}

										if (undefined !== _container.find('.button-group-location').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonLocation = _container.find('.button-group-location').find('.mnw_filter_active').attr('data-filter');
										}

										if (undefined !== _container.find('.button-group-date').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonDate = _container.find('.button-group-date').find('.mnw_filter_active').attr('data-filter');
										}
									}

									// Update filters
									$.ajax({
										type: 'POST',
										url: site_path+'index.php?option=com_minitekwall&task=masonry.getFilters&widget_id=".$widgetID."&page=' + page + '&pagination=' + pagination + '&lang=' + lang,
										success: function(msg)
										{
											if (msg.length > 3)
											{
												// Add new filters
												_container.find('.mnwall_iso_filters').html(msg);

												// Restore active filters
												if (filtersActive == 'yes')
												{
													_container.find('.button-group-category').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonCategory && _activeButtonCategory.length)
													{
														var cat_text = _container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').text();
														
														if (cat_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.cat-label span span').text(cat_text);
														}
													}

													_container.find('.button-group-tag').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonTag && _activeButtonTag.length)
													{
														var tag_text = _container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').text();
														
														if (tag_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.tag-label span span').text(tag_text);
														}
													}

													_container.find('.button-group-location').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonLocation && _activeButtonLocation.length)
													{
														var location_text = _container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').text();
														
														if (location_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.location-label span span').text(location_text);
														}
													}

													_container.find('.button-group-date').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonDate && _activeButtonDate.length)
													{
														var date_text = _container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').text();
														
														if (date_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.date-label span span').text(date_text);
														}
													}
												}
											}

											active_Filters();
											dropdown_Filters();
										}
									});
								}

								// Hide loader
								_container.find('.mnwall_arrow_prev').removeClass('mnwall-loading');
								current.find('.more-results').show();
								current.find('.mnwall_arrow_loader').hide();

								// Enable next button
								_container.find('.mnwall_arrow_next').removeClass('disabled');

								// Disable previous button on 1st page
								if (new_page <= 0)
								{
									if (new_page < 0)
									{
										_container.find('.mnwall_arrow_prev').attr('data-page', 0);
										_container.find('.mnwall_arrow_next').attr('data-page', 2);
									}

									// Disable previous button
									_container.find('.mnwall_arrow_prev').addClass('disabled');
								}

								// Scroll to top
								if (scrollToTop > 0)
								{
									setTimeout(function () {
										_container[0].scrollIntoView(true);
									}, transitionDuration);
								}
							});
						}
						else
						{
							// Disable previous button / Hide loader
							_container.find('.mnwall_arrow_prev').addClass('disabled');
							_container.find('.mnwall_arrow_loader').hide();
						}
					}
				});
			});

			// Next arrow pagination
			_container.find('.mnwall_arrow_next').on('click', function(event)
			{
				event.preventDefault();

				var current = $(this);

				if ($(this).hasClass('disabled') || $(this).hasClass('mnwall-loading') || _container.find('.mnwall_arrow_prev').hasClass('mnwall-loading')) 
				{
					return false;
				}

				// Find page
				var page = $(this).attr('data-page');
				page = parseInt(page);
				var next_page = page + 1;
				var prev_page = page - 1;
				var end_page_next = next_page - 1;
				var end_page_prev = next_page - 3;

				// Check if there is a pending ajax request
				if (typeof ajax_request !== 'undefined') 
				{
					ajax_request.abort();
					_container.find('.mnwall_arrow_prev').removeClass('mnwall-loading');
					_container.find('.more-results').show();
					_container.find('.mnwall_arrow_loader').hide();
				}

				// Show loader
				$(this).addClass('mnwall-loading');
				current.find('.more-results').hide();
				current.find('.mnwall_arrow_loader').show();

				// Ajax request
				var ajax_request = $.ajax({
					type: 'POST',
					url: site_path+'index.php?option=com_minitekwall&task=masonry.getContent&widget_id=".$widgetID."&page=' + page + '&grid=' + grid_type + '&lang=' + lang,
					success: function(msg)
					{
						if (msg.length > 3)
						{
							// Increment page in link id
							_container.find('.mnwall_arrow_next').attr('data-page', next_page);
							_container.find('.mnwall_arrow_prev').attr('data-page', prev_page);

							// Reset filters
							if (filtersEnabled == 'true' && filtersActive == 'no')
							{
								reset_filters();
							}

							// Append items
							var elems = _wall.isotope('getItemElements');
							var newItems = $(msg).appendTo(_wall);
							newItems.css({'visibility':'hidden','left':'-9999px','top':'-9999px'});

							imagesLoaded(_wall, function() 
							{
								_wall.isotope('remove', elems);
								newItems.css({'visibility':'visible'});
								_wall.isotope('insert', newItems);
								_wall.isotope('updateSortData').isotope();
								fixEqualHeights(newItems);

								// Hover box trigger
								if (hoverBox == '1') 
								{
									triggerHoverBox();
								}

								if (filtersEnabled == 'true' && filtersMode == 'dynamic')
								{
									// Store active filters
									if (filtersActive == 'yes')
									{
										if (undefined !== _container.find('.button-group-category').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonCategory = _container.find('.button-group-category').find('.mnw_filter_active').attr('data-filter');
										}

										if (undefined !== _container.find('.button-group-tag').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonTag = _container.find('.button-group-tag').find('.mnw_filter_active').attr('data-filter');
										}

										if (undefined !== _container.find('.button-group-location').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonLocation = _container.find('.button-group-location').find('.mnw_filter_active').attr('data-filter');
										}

										if (undefined !== _container.find('.button-group-date').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonDate = _container.find('.button-group-date').find('.mnw_filter_active').attr('data-filter');
										}
									}

									// Update filters
									$.ajax({
										type: 'POST',
										url: site_path+'index.php?option=com_minitekwall&task=masonry.getFilters&widget_id=".$widgetID."&page=' + page + '&pagination=' + pagination + '&lang=' + lang,
										success: function(msg)
										{
											if (msg.length > 3)
											{
												// Add new filters
												_container.find('.mnwall_iso_filters').html(msg);

												// Restore active filters
												if (filtersActive == 'yes')
												{
													_container.find('.button-group-category').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonCategory && _activeButtonCategory.length)
													{
														var cat_text = _container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').text();
														
														if (cat_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.cat-label span span').text(cat_text);
														}
													}

													_container.find('.button-group-tag').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonTag && _activeButtonTag.length)
													{
														var tag_text = _container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').text();
														
														if (tag_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.tag-label span span').text(tag_text);
														}
													}

													_container.find('.button-group-location').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonLocation && _activeButtonLocation.length)
													{
														var location_text = _container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').text();
														
														if (location_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.location-label span span').text(location_text);
														}
													}

													_container.find('.button-group-date').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonDate && _activeButtonDate.length)
													{
														var date_text = _container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').text();
														
														if (date_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.date-label span span').text(date_text);
														}
													}
												}
											}

											active_Filters();
											dropdown_Filters();
										}
									});
								}

								// Hide loader
								_container.find('.mnwall_arrow_next').removeClass('mnwall-loading');
								current.find('.more-results').show();
								current.find('.mnwall_arrow_loader').hide();

								// Enable previous button
								_container.find('.mnwall_arrow_prev').removeClass('disabled');

								// Last page
								if (_container.find('.mnwall_arrow_next').attr('data-page') == endPage) 
								{
									_container.find('.mnwall_arrow_next').addClass('disabled');
								}

								// Scroll to top
								if (scrollToTop > 0)
								{
									setTimeout(function () {
										_container[0].scrollIntoView(true);
									}, transitionDuration);
								}
							});
						}
						else
						{
							// Disable next button / Hide loader
							_container.find('.mnwall_arrow_next').addClass('disabled');
							_container.find('.mnwall_arrow_loader').hide();
							_container.find('.mnwall_arrow_prev').attr('data-page', end_page_prev);
							_container.find('.mnwall_arrow_next').attr('data-page', end_page_next);
						}
					}
				});
			});
		";

		return $javascript;
	}

	public function initializePagesPagination($widgetID)
	{
		$javascript = "
			var _activeButtonCategory;
			var _activeButtonTag;
			var _activeButtonLocation;
			var _activeButtonDate;

			// Pages pagination
			_container.find('.mnwall_page').on('click', function(event)
			{
				if (_container.find('.mnwall_pages').hasClass('mnwall-loading')) 
				{
					return false;
				}

				var current = $(this);
				_container.find('.mnwall_page').removeClass('mnw_active');

				event.preventDefault();

				// Find page
				var page = $(this).attr('data-page');
				page = parseInt(page);

				// Check if there is a pending ajax request
				if (typeof ajax_request !== 'undefined') 
				{
					ajax_request.abort();
					_container.find('.page-number').show();
					_container.find('.mnwall_page_loader').hide();
				}

				// Show loader
				_container.find('.mnwall_pages').addClass('mnwall-loading');
				current.find('.page-number').hide();
				current.find('.mnwall_page_loader').show();

				// Ajax request
				var ajax_request = $.ajax({
					type: 'POST',
					url: site_path+'index.php?option=com_minitekwall&task=masonry.getContent&widget_id=".$widgetID."&page=' + page + '&grid=' + grid_type + '&lang=' + lang,
					success: function(msg)
					{
						if (msg.length > 3)
						{
							// Reset filters
							if (filtersEnabled == 'true' && filtersActive == 'no')
							{
								reset_filters();
							}

							// Append items
							var elems = _wall.isotope('getItemElements');
							var newItems = $(msg).appendTo(_wall);
							newItems.css({'visibility':'hidden','left':'-9999px','top':'-9999px'});

							imagesLoaded(_wall, function() 
							{
								_wall.isotope('remove', elems);
								newItems.css({'visibility':'visible'});
								_wall.isotope('insert', newItems);
								_wall.isotope('updateSortData').isotope();
								fixEqualHeights(newItems);

								// Hover box trigger
								if (hoverBox == '1') 
								{
									triggerHoverBox();
								}

								if (filtersEnabled == 'true' && filtersMode == 'dynamic')
								{
									// Store active filters
									if (filtersActive == 'yes')
									{
										if (undefined !== _container.find('.button-group-category').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonCategory = _container.find('.button-group-category').find('.mnw_filter_active').attr('data-filter');
										}

										if (undefined !== _container.find('.button-group-tag').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonTag = _container.find('.button-group-tag').find('.mnw_filter_active').attr('data-filter');
										}

										if (undefined !== _container.find('.button-group-location').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonLocation = _container.find('.button-group-location').find('.mnw_filter_active').attr('data-filter');
										}

										if (undefined !== _container.find('.button-group-date').find('.mnw_filter_active').attr('data-filter'))
										{
											_activeButtonDate = _container.find('.button-group-date').find('.mnw_filter_active').attr('data-filter');
										}
									}

									// Update filters
									$.ajax({
										type: 'POST',
										url: site_path+'index.php?option=com_minitekwall&task=masonry.getFilters&widget_id=".$widgetID."&page=' + page + '&pagination=' + pagination + '&lang=' + lang,
										success: function(msg)
										{
											if (msg.length > 3)
											{
												// Add new filters
												_container.find('.mnwall_iso_filters').html(msg);

												// Restore active filters
												if (filtersActive == 'yes')
												{
													_container.find('.button-group-category').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonCategory && _activeButtonCategory.length)
													{
														var cat_text = _container.find('.button-group-category').find('[data-filter=\'' + _activeButtonCategory + '\']').text();
														
														if (cat_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.cat-label span span').text(cat_text);
														}
													}

													_container.find('.button-group-tag').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonTag && _activeButtonTag.length)
													{
														var tag_text = _container.find('.button-group-tag').find('[data-filter=\'' + _activeButtonTag + '\']').text();
														
														if (tag_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.tag-label span span').text(tag_text);
														}
													}

													_container.find('.button-group-location').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonLocation && _activeButtonLocation.length)
													{
														var location_text = _container.find('.button-group-location').find('[data-filter=\'' + _activeButtonLocation + '\']').text();
														
														if (location_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.location-label span span').text(location_text);
														}
													}

													_container.find('.button-group-date').find('.mnw_filter_active').removeClass('mnw_filter_active');
													_container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').addClass('mnw_filter_active');
													
													if (undefined !== _activeButtonDate && _activeButtonDate.length)
													{
														var date_text = _container.find('.button-group-date').find('[data-filter=\'' + _activeButtonDate + '\']').text();
														
														if (date_text)
														{
															_container.find('.mnwall_iso_dropdown').find('.date-label span span').text(date_text);
														}
													}
												}
											}

											active_Filters();
											dropdown_Filters();
										}
									});
								}

								// Hide loader
								_container.find('.mnwall_pages').removeClass('mnwall-loading');
								current.find('.page-number').show();
								current.find('.mnwall_page_loader').hide();

								// Remove active class
								if (!$(current).hasClass('mnw_active')) 
								{
									$(current).addClass('mnw_active');
								}

								// Scroll to top
								if (scrollToTop > 0)
								{
									setTimeout(function () {
										_container[0].scrollIntoView(true);
									}, transitionDuration);
								}
							});
						}
						else
						{
							// Hide loader
							_container.find('.mnwall_pages').removeClass('mnwall-loading');
							_container.find('.mnwall_page_loader').hide();
						}
					}
				});
			});
		";

		return $javascript;
	}
}
