<?php
	class core {

		var $pattern;
		var $language;

		public function __construct() {
			//Set LCMS language
			$this->language = $this->loadLanguage(@$_GET['lang']);
			//Load LCMS definitions
			require_once('inc/engine/defines.php');
			//Load LCMS modules
			$this->loadModules('pages');
			//Load LCMS content
			echo $this->pattern;
		}
		
		//Load Language
		private function loadLanguage($get) {
			if(!isset($get) || empty($get) || !file_exists('inc/lang/'.$get)) {
				return $this->getSettings('site_lang');
			} else {
				$dir = explode('/',current(glob('inc/lang/'.$get.'_*')));
				return end($dir);
			}
		} //End loadLanguage();

		//Load Modules
		private function loadModules($startModule) {
			global $lang, $db, $core;
			$core = $this;

			//Load starting module
			require_once(MODULES.$startModule.'/'.$startModule.'.site.php');
			//Load other modules
			$query = $db->select('modules');
		    foreach ($query as $module)
			{
				if(file_exists(MODULES.$module['dir'].'/'.$module['dir'].'.site.php') && $module['dir']!=$startModule) {
			    	require_once(MODULES.$module['dir'].'/'.$module['dir'].'.site.php');
				}
			}
		} //End loadModules();
		
		//Load Pattern
		public function loadPattern($tpl) {
			$theme = $this->getSettings('theme');
			$tpl = THEMES.$theme.'/'.$tpl;
			if(file_exists($tpl)) {
				$pattern = file_get_contents($tpl);
				$strpos = strpos($pattern, '{{lcms.footer}}');
				if($strpos===false) $pattern = 'LCMS footer is missing';
				return $pattern;
			} else echo "Template file not found!";
		} //End loadPattern();
		
		//Get settings from DB
		public function getSettings($what) {
			global $db;

			$query = $db->select('settings',array('field'=>$what));
			$result = $query[0];
			return $result['value'];
		} //End getSettings();

		//Replace
		public function replace($what, $replacement) {
			if(strpos($this->pattern, $what) !== false) {
				$this->pattern = str_replace($what, $replacement, $this->pattern); return TRUE;
			} else return FALSE;
		} //End replace();
		
		//Add something inside HTML <$tag $extra>
		public function append($replacement, $tag, $extra = NULL) {
			if($extra) $extra = addslashes(' '.$extra);
			$this->pattern = preg_replace('/(<'.$tag.$extra.'>)(.*?)(<\/'.$tag.'>)/s', '$1$2'.$replacement."\n".'$3', $this->pattern);
		} //End append();

	}
?>