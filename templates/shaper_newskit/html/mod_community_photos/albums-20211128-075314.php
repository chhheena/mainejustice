<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();

$config = CFactory::getConfig();
$document = JFactory::getDocument();
$document->addScriptDeclaration("joms_prev_comment_load = +'" . $config->get('prev_comment_load', 10) . "';");

?>


<?php if( $albums ) { ?>

    <?php for( $i = 0 ; $i < count( $albums ); $i++ ) {
        $row    =& $albums[$i];
    ?>

    <div class="joms-stream__header no-gap">
        <div class="joms-avatar--stream square">
    	     <img onclick="joms.api.photoOpen('<?php echo $row->id; ?>', ''); return false;" title="<?php echo JText::sprintf('MOD_COMMUNITY_PHOTOS_UPLOADED_BY' , $row->user->getDisplayName() );?>" src="<?php echo $row->getCoverThumbPath(); ?>" alt="<?php echo CStringHelper::escape( $row->user->getDisplayName() );?>" >
        </div>

    		<div class="joms-stream__meta">
                <a class="joms-gallery__title" 
                    <?php if ( $isAlbumModal ) { ?>
                        href="javascript:" onclick="joms.api.photoOpen('<?php echo $row->id; ?>', ''); return false;"
                    <?php } else {
                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($row->albumid);

                        $photoUrl = CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $row->albumid . '&photoid=' . $row->id . ( $album->groupid ? '&groupid=' . $album->groupid : '' ). ( $album->eventid ? '&eventid=' . $album->eventid : '' ));
                    ?>
                        href="<?php echo $row->getURI(); ?>"
                    <?php } ?>
                    >
                    <?php echo $row->name; ?>
                </a>

                <span class="joms-block joms-text--light"><?php echo JText::sprintf('MOD_COMMUNITY_PHOTOS_UPLOADED_BY' , $row->user->getDisplayName() );?></span>
    			<div class="joms-text--light">
                    <small>
                        <?php if (CStringHelper::isPlural($row->count)) {
                            echo JText::sprintf('MOD_COMMUNITY_PHOTOS_COUNTER', $row->count);
                        } else {
                            echo JText::sprintf('MOD_COMMUNITY_PHOTOS_COUNTER_SINGULAR', $row->count);
                        } ?>
                    </small>
    			</div>
    		</div>

        </div>
    <?php } ?>

<?php } else { ?>
   <div class="joms-blankslate"><?php echo JText::_('MOD_COMMUNITY_PHOTOS_NO_PHOTO_UPLOADED');?></div>
<?php } ?>
