<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

$params	= $this->background->getParams();

$isNew = $this->background->id < 1;
?>
<script type="text/javascript">
	function js_Show(){
	    jQuery("#sbox-window, #sbox-overlay").show();
	}
</script>

<style type="text/css">
    label { float:left; clear:none; display:block; padding: 2px 1em 0 0; }
    #js-cpanel .ace-file-input {
        margin-bottom: 0;
    }
    .ace-file-input .icon-picture, .ace-file-input .icon-upload-alt {
        height: 24px;
    }
    #js-cpanel .ace-file-input label.selected .icon-picture {
        line-height: 25px !important;
    }

    .backgroundImage {
        max-width:  100px;
        max-height: 100px;
        margin-bottom:20px;
    }
</style>

<form name="adminForm" id="adminForm" action="index.php?option=com_community" method="POST" enctype="multipart/form-data">
<div class="row-fluid">
<div class="span12">
<table  width="100%" class="paramlist admintable" cellspacing="1">
    <tr>
        <td class="paramlist_key">
            <label for="title" class="title" title="<?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_TITLE_TIPS'); ?>">
                <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_TITLE_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_TITLE'); ?></span>
                <span class="required-sign"> *</span>
            </label>
        </td>
        <td class="paramlist_value">
            <input type="text" name="title" id="title" value="<?php echo $this->background->title; ?>" style="width: 200px;" />
        </td>
    </tr>
    <tr>
        <td class="paramlist_key">
            <label for="description" class="title" title="<?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_DESCRIPTION_TIPS'); ?>">
                <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_DESCRIPTION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_DESCRIPTION'); ?></span>
                <span class="required-sign"> *</span>
            </label>
        </td>
        <td class="paramlist_value">
            <input type="text" name="description" id="description" value="<?php echo $this->background->description; ?>" style="width: 200px;" />
        </td>
    </tr>
    <tr>
        <td class="paramlist_key">
            <label for="background_image"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_IMAGE_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_IMAGE');?></span></label>
        </td>
        <td class="paramlist_value">
            <?php if ($this->background->image) { ?>
                <img src="<?php echo $this->background->image; ?>" default-image="<?php echo $this->background->image; ?>" class="backgroundImage" /><br/>
            <?php } ?>

            <?php if ($this->background->custom || $isNew) { ?>
                <div class="ace-file-input">
                    <input type="file" name="background_image" id="background_image"/>
                </div>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td class="paramlist_key">
            <label class="control-label" for="scss-color-text" ><?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_TEXT_COLOR');?></label>
        </td>
        <td class="paramlist_value">
            <?php echo $this->renderField('scss-color-text');?>
        </td>
    </tr>
    <tr>
        <td class="paramlist_key">
            <label class="control-label" for="scss-color-placeholder" ><?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_PLACEHOLDER_COLOR');?></label>
        </td>
        <td class="paramlist_value">
            <?php echo $this->renderField('scss-color-placeholder');?>
        </td>
    </tr>
    <tr>
        <td class="paramlist_key">
            <label for="published"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_PUBLISH_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_ENABLE');?></span></label>
        </td>
        <td class="paramlist_value">
            <?php echo CHTMLInput::checkbox('published' ,'ace-switch ace-switch-5', null , $this->background->published); ?>
        </td>
    </tr>



</table>
<input type="hidden" name="view" value="backgrounds" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="backgroundid" value="<?php echo $this->background->id; ?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</div>

<div class="span12">
    <?php echo $this->loadTemplate('preview') ?>
</div>

</div>
</form>

<script src="<?php echo COMMUNITY_ASSETS_URL; ?>/js/jscolor/jscolor.js"></script>

<script>
    jQuery( document ).ready(function($)
    {   
        // Handle submitbutton event
        Joomla.submitbutton = function(action){
            if(action == 'reset') {
                $('a.reset').trigger('click');
                return true;
            }
            Joomla.submitform(action);
        }

        $('a.reset').on( 'click', function( e ) {
            var $reset = $( this ),
                id = $reset.attr('id').replace( /^reset-/, '' ),
                $field = $( '#' + id ),
                $deflt = $( '#default-' + id ),
                color;

            e.preventDefault();

            color = $deflt.val();
            if ( !$field[0].color.fromString( color || '' ) ) {
                $field.val( color ).css( 'background-color', '' );
            }

            $field.trigger('change')

            $reset.hide();
        });

        $('input.resettable').on( 'input change', function() {
            var $field = $( this ),
                id = $field.attr('id'),
                $deflt = $( '#default-' + id ),
                $reset = $( '#reset-' + id );

            if ( $field.val() === $deflt.val() ) {
                $reset.hide();
            } else {
                $reset.show();
            }
        });

        setTimeout(function() {
            $('a.remove').on('click', function() {
                var $bg = $('.backgroundImage'),
                    default_image = $bg.attr('default-image');

                $bg.attr('src', default_image);
                $('.color-container').css('background-image', 'url(\''+ default_image +'\'')
            })
        }, 100)
        
        Joomla.submitbutton = function(action)
        {
            var error = '';

            if ($('#title').val() == '') error = error + '\n<?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_ERROR_TITLE_EMPTY');?>';
            if ($('#description').val() == '') error = error + '\n<?php echo JText::_('COM_COMMUNITY_BACKGROUNDS_ERROR_DESCRIPTION_EMPTY');?>';

            if (['apply', 'save'].indexOf(action) >= 0 && !checkFiles()) {
                error = error + '\n<?php echo JText::_('COM_COMMUNITY_THEME_IMAGE_ERROR');?>';
            }

            if(!error) Joomla.submitform(action);
            if(error) alert(error);
        }

        function checkFiles()
        {
            var isValid = true;
            $('#adminForm input[type=file]').each(function() {
                if (this.value && !this.value.match(/\.(jpg|jpeg|png)$/i)) {
                    isValid = false;
                    return false;
                }
            });

            return isValid;
        }

        $('#background_image').ace_file_input({
            no_file:'No File ...',
            btn_choose:'Choose',
            btn_change:'Change'
        }).on('change', function(){
            // var files = $(this).data('ace_input_files');
            //or
            var files = $(this).ace_file_input('files');

            // var method = $(this).data('ace_input_method');
            //method will be either 'drop' or 'select'
            //or
            var method = $(this).ace_file_input('method');
        });

    });
</script>
