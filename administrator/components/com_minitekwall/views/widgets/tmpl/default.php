<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$params = (isset($this->state->params)) ? $this->state->params : new JObject;
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
?>

<form action="<?php echo JRoute::_('index.php?option=com_minitekwall&view=widgets'); ?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>

		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>

			<table class="table table-striped" id="articleList">

				<thead>
					<tr>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>

						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>

						<th style="min-width:100px" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_MINITEKWALL_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>

						<th class="center">
							<?php echo JHtml::_('searchtools.sort', 'COM_MINITEKWALL_HEADING_TYPE', 'a.type_id', $listDirn, $listOrder); ?>
						</th>

						<th class="center">
							<?php echo JHtml::_('searchtools.sort', 'COM_MINITEKWALL_HEADING_SOURCE', 'a.source_id', $listDirn, $listOrder); ?>
						</th>

						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>

				<?php foreach ($this->items as $i => $item) :

					$ordering   = ($listOrder == 'ordering');
					$canEdit    = $user->authorise('core.edit',       'com_minitekwall');
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
					$canChange  = $user->authorise('core.edit.state', 'com_minitekwall') && $canCheckin;
					?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php //echo $item->catid; ?>">

						<td class="center hidden-phone">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>

						<td class="center">
							<div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'widgets.', $canChange); ?>
								<?php
								// Create dropdown items
								$action = $archived ? 'unarchive' : 'archive';
								JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'widgets');

								$action = $trashed ? 'untrash' : 'trash';
								JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'widgets');

								// Render dropdown list
								echo JHtml::_('actionsdropdown.render', $this->escape($item->name));
								?>
							</div>
						</td>

						<td class="nowrap has-context">
							<div class="pull-left">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'widgets.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_minitekwall&task=widget.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->name); ?>
									</a>
								<?php else : ?>
									<?php echo $this->escape($item->name); ?>
								<?php endif; ?>
								<?php if ($item->description) { ?>
								<div>
									<span class="small break-word">
										<?php echo $this->escape($item->description); ?>
									</span>
								</div>
								<?php } ?>
							</div>
						</td>

						<td class="center">
							<span class="label label-info">
								<?php echo $this->escape($item->type_id); ?>
							</span>
						</td>

						<td class="center">
							<span class="label label-warning">
								<?php echo $this->escape($item->source_id); ?>
							</span>
						</td>

						<td class="hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>

					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
