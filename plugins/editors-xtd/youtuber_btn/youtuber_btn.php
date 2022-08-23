<?php
defined('_JEXEC') or die;

class PlgButtonyoutuber_btn extends JPlugin{
	
	protected $autoloadLanguage = true;

	public function onDisplay($name){
		$doc = JFactory::getDocument();
                
                $config = $this->getConfig();
                if((int)$config->get('uploading_enable', 0)!=1){
                    return null;
                }
                
		$js = '
			function mxYouTubeRBtnClick(editor){
				window.wzYoutube.init(window.wzYoutube.appID,function(_videoID){
					jInsertEditorText(\'[mx_youtuber type="video" id="\' + _videoID + \'" display="title,date,channel,description,meta"]\', editor);
					window.wzYoutube.close();
				});
			}
			';

		$doc->addScriptDeclaration($js);

		$button = new JObject;
		$button->modal = false;
		$button->class = 'btn mxYouTuberBtn';
		$button->onclick = 'mxYouTubeRBtnClick(\'' . $name . '\');return false;';
		$button->text = 'YouTubeR';
		$button->name = 'youtube';

		$button->link = '#';

		return $button;
	}
        
        private function getConfig(){
            $plugin = JPluginHelper::getPlugin('system', 'youtuber');
            return new JRegistry($plugin->params);
        }
}
