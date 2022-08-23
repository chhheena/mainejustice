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

<?php  // echo var_dump($photos); ?>

<?php if( $photos ) { ?>
<ul class="joms-list--thumbnail">
    <?php for( $i = 0 ; $i < count( $photos ); $i++ ) {
        $row    =& $photos[$i];
        $row->user = CFactory::getUser($row->creator);
    ?>
    <li class="joms-list__item">
        <a
            <?php if ( $isAlbumModal ) { ?>
                href="javascript:" onclick="joms.api.photoOpen('<?php echo $row->albumid; ?>', '<?php echo $row->id; ?>');"
            <?php } else {
                $album = JTable::getInstance('Album', 'CTable');
                $album->load($row->albumid);

                $photoUrl = CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $row->albumid . '&photoid=' . $row->id . ( $album->groupid ? '&groupid=' . $album->groupid : '' ). ( $album->eventid ? '&eventid=' . $album->eventid : '' ));
            ?>
                href="<?php echo $photoUrl; ?>"
            <?php } ?>
            >
            <img title="<?php echo JText::sprintf('MOD_COMMUNITY_PHOTOS_UPLOADED_BY' , $row->user->getDisplayName() );?>" src="<?php echo $row->getThumbURI(); ?>" alt="<?php echo CStringHelper::escape( $row->user->getDisplayName() );?>" >
        </a>
    </li>
    <?php } ?>
</ul>
<?php } else { ?>
   <div class="joms-blankslate"><?php echo JText::_('MOD_COMMUNITY_PHOTOS_NO_PHOTO_UPLOADED');?></div>
<?php } ?>
