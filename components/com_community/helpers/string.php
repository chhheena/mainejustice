<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

use \Joomla\String\StringHelper;

class CStringHelper extends StringHelper
{

    /**
     * Tests a bunch of text and see if it contains html tags.
     *
     * @param	$text	String	A text value.
     * @return	$text	Boolean	True if the text contains html tags and false otherwise.
     * */
    static public function isHTML($text)
    {
        $pattern = '/\<p\>|\<br\>|\<br \/\>|\<b\>|\<div\>/i';
        preg_match($pattern, CStringHelper::strtolower($text), $matches);

        return empty($matches) ? false : true;
    }

    /**
     *  Auto-link the given string
     */
    static function autoLink($text)
    {
        /* subdomain must be taken into consideration too */
        $pattern = '~(
					  (
					   #(?<=([^[:punct:]]{1})|^)			# that must not start with a punctuation (to check not HTML)
					   	(https?://)|(www)[^-][a-zA-Z0-9-]*?[.]	# normal URL lookup
					   )
					   [^\s()<>]+						# characters that satisfy SEF url
					   (?:								# followed by
					   		\([\w\d]+\)					# common character
					   		|							# OR
					   		([^[:punct:]\s]|/)			# any non-punctuation character followed by space OR forward slash
					   )
					 )~x';
        $callback = function ($matches) use ($text) {
            $url = array_shift($matches);
            $url_parts = parse_url($url);

            $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
            $substr = substr($text, 0, 50);

            if ($substr !== $text) {
                $text = $substr . "&hellip;";
            }

            if (strpos($url, 'www') !== false && strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
                $url = 'http://' . $url;
            }

            $isNewTab = CFactory::getConfig()->get('newtab', false);
            $isInternal = !$isNewTab ? '' : 'target="_blank" ';
            return sprintf('<a rel="nofollow" ' . $isInternal . ' href="%s">%s</a>', $url, $text);
        };

        return preg_replace_callback($pattern, $callback, $text);
    }

    /**
     * Automatically converts new line to html break tag.
     *
     * @param	$text	String	A text value.
     * @return	$text	String	A formatted data which contains html break tags.
     * */
    static public function nl2br($text)
    {
        $text = CString::str_ireplace(array("\r\n", "\r", "\n"), "<br />", $text);
        return preg_replace("/(<br\s*\/?>\s*){3,}/", "<br /><br />", $text);
    }

    static public function isPlural($num)
    {
        return !CStringHelper::isSingular($num);
    }

    static public function isSingular($num)
    {
        $config = CFactory::getConfig();
        $singularnumbers = $config->get('singularnumber');
        $singularnumbers = explode(',', $singularnumbers);

        return in_array($num, $singularnumbers);
    }

    static public function escape($var, $function = 'htmlspecialchars')
    {
        $disabledFunctions = array('eval', 'exec', 'passthru', 'system', 'shell_exec');
        if (!in_array($function, $disabledFunctions)) {
            if (in_array($function, array('htmlspecialchars', 'htmlentities'))) {
                return call_user_func($function, $var, ENT_COMPAT, 'UTF-8');
            }
            return call_user_func($function, $var);
        }
    }

    /**
     * @deprecated
     */
    static public function clean($string)
    {
        jimport('joomla.filter.filterinput');
        $safeHtmlFilter = JFilterInput::getInstance();
        return $safeHtmlFilter->clean($string);
    }

    /**
     * @todo: this would fail if the username contains {} char
     */
    static public function replaceThumbnails($data)
    {
        // Replace matches for {user:thumbnail:ID} so that this can be fixed even if the caching is enabled.
        $html = preg_replace_callback('/\{user:thumbnail:(.*)\}/', array('CStringHelper', 'replaceThumbnail'), $data);

        return $html;
    }

    static public function replaceThumbnail($matches)
    {
        static $data = array();

        if (!isset($data[$matches[1]])) {
            $user = CFactory::getUser($matches[1]);
            $data[$matches[1]] = $user->getThumbAvatar();
        }

        return $data[$matches[1]];
    }

    /**
     * Truncate the given text and append with '...' if necessary
     * @param string $str			string to truncate
     * @param int	 $lenght		length of the final string
     * @deprecated in 2.8. Removed in 3.0
     */
    static public function truncate($value, $length, $wrapSuffix = '<span>...</span>', $excludeImg = true)
    {
        if ($excludeImg) {
            $value = preg_replace("/<img[^>]+\>/i", " ", $value);
        }

        if (CStringHelper::strlen($value) > $length) {
            return CStringHelper::substr($value, 0, $length) . ' ' . $wrapSuffix;
        }
        return $value;
    }

    /**
     * Trims text to a certain number of words.
     *
     *
     * @since 3.2
     *
     * @param string $text Text to trim.
     * @param int $num_words Number of words. Default 55.
     * @param string $more Optional. What to append if $text needs to be trimmed. Default '&hellip;'.
     * @return string Trimmed text.
     */
    static public function trim_words($text, $num_words = 25, $more = null)
    {
        if (null === $more)
            $more = '&hellip;';
        $original_text = $text;
        $text = strip_tags($text);
        /* translators: If your word count is based on single characters (East Asian characters),
          enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */

        $words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
        $sep = ' ';

        if (count($words_array) > $num_words) {
            array_pop($words_array);
            $text = implode($sep, $words_array);
            $text = $text . $more;
        } else {
            $text = implode($sep, $words_array);
        }
        return $text;
    }

    static public function getRandom($length = 11)
    {
        $map = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len = strlen($map);
        $stat = stat(__FILE__);
        $randomString = '';

        if (empty($stat) || !is_array($stat))
            $stat = array(php_uname());

        mt_srand(crc32(microtime() . implode('|', $stat)));
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $map[mt_rand(0, $len - 1)];
        }

        return $randomString;
    }

    /**
     * Get emoticon
     * @param  [string] $str [Status message]
     * @return [string]      [Imoticon icon]
     */
    static public function getEmoticon($str)
    {
        
        if (!CFactory::getConfig()->get('statusemoticon')) {
            return $str;
        }

        $emoticons = self::getEmoticonData();
        // in order to replace >:) before :)
        $emoticons = array_reverse($emoticons);

        foreach ($emoticons as $key => $emotion) {
            $mockup = '<span class="joms-content-emo2 joms-emo2 joms-emo2-' . $key . '"></span>';
            $str = str_replace($emotion, $mockup, $str);
        }

        return $str;
    }

    static public function getEmoticonData()
    {
        return array(
            'smile' => array(':)', ':-)', ':smile:'),
            'grin' => array(':D', ':grin:', ':grinning:'),
            'beaming' => array('^^', ':beaming:'),
            'squinting' => array('xD', ':squinting:'),
            'star-struck' => array(':star:'),
            'heart' => array('&lt;3', ':heart:', '<3'),
            'love' => array(':love:'),
            'kiss' => array(':-*', ':kiss:'),
            'wink' => array(';)', ':wink:'),
            'tongue' => array(':p', ':P', ':tongue:'),
            'stongue' => array('xP', ':stongue:'),
            'sunglasses' => array('B)', 'B-)', ':cool:', ':sunglasses:'),
            'hug' => array(':hug:'),
            'money' => array('$-D', '$-)', '$-P', ':money:'),
            'poop' => array(':poop:'),
            'evil' => array('&gt;:)', ':evil:', '&gt;:D', '>:D'),
            'joy' => array(':joy:', ':lmao:'),
            'rofl' => array(':rofl:'),
            'sweat' => array('^^!', ':sweat:'),
            'confused' => array(':?', ':confused:'),
            'flushed' => array(':flushed:'),
            'hmm' => array('-_-', ':hmm:'),
            'neutral' => array(':|', ':neutral:'),
            'shock' => array(':o', ':O', ':shock:'),
            'sleep' => array(':sleep:'),
            'think' => array(':think:'),
            'sexy' => array(':sexy:'),
            'whut' => array(':whut:', ':what:'),
            'unamused' => array(':unamused:'),
            'zipper' => array(':zipper:'),
            'sad' => array(':(', ':sad:'),
            'tired' => array('x-(', 'x(', ':tired:'),
            'worried' => array(':-s', ':worried:'),
            'angry' => array('&gt;:(', ':angry:'),
            'pouting' => array(':pouting:'),
            'dizzy_face' => array(':dizzy:'),
            'fear' => array(':fear:'),
            'fearful' => array(':fearful:'),
            'cry' => array('T_T', 'T.T', ':cry:'),
            'ill' => array(':ill:'),
            'sneezing' => array(':sneezing:'),
            'cold' => array(':cold:'),
            'star-struck' => array(':star-struck:'),
            'hundred' =>    array( ':100:'),
            'numbers' =>
            array(
                ':1234:',
            ),
            'monkey_face' =>
            array(
                ':monkey_face:',
            ),
            'grinning' =>
            array(
                ':grinning:',
            ),
            'earth_africa' =>
            array(
                ':earth_africa:',
            ),
            'checkered_flag' =>
            array(
                ':checkered_flag:',
            ),
            'mute' =>
            array(
                ':mute:',
            ),
            'jack_o_lantern' =>
            array(
                ':jack_o_lantern:',
            ),
            'atm' =>
            array(
                ':atm:',
            ),
            'grapes' =>
            array(
                ':grapes:',
            ),
            'earth_americas' =>
            array(
                ':earth_americas:',
            ),
            'melon' =>
            array(
                ':melon:',
            ),
            'triangular_flag_on_post' =>
            array(
                ':triangular_flag_on_post:',
            ),
            'monkey' =>
            array(
                ':monkey:',
            ),
            'christmas_tree' =>
            array(
                ':christmas_tree:',
            ),
            'put_litter_in_its_place' =>
            array(
                ':put_litter_in_its_place:',
            ),
            'speaker' =>
            array(
                ':speaker:',
            ),
            'earth_asia' =>
            array(
                ':earth_asia:',
            ),
            'crossed_flags' =>
            array(
                ':crossed_flags:',
            ),
            'sound' =>
            array(
                ':sound:',
            ),
            'watermelon' =>
            array(
                ':watermelon:',
            ),
            'gorilla' =>
            array(
                ':gorilla:',
            ),
            'fireworks' =>
            array(
                ':fireworks:',
            ),
            'potable_water' =>
            array(
                ':potable_water:',
            ),
            'wheelchair' =>
            array(
                ':wheelchair:',
            ),
            'rolling_on_the_floor_laughing' =>
            array(
                ':rolling_on_the_floor_laughing:',
            ),
            'loud_sound' =>
            array(
                ':loud_sound:',
            ),
            'waving_black_flag' =>
            array(
                ':waving_black_flag:',
            ),
            'tangerine' =>
            array(
                ':tangerine:',
            ),
            'dog' =>
            array(
                ':dog:',
            ),
            'sparkler' =>
            array(
                ':sparkler:',
            ),
            'globe_with_meridians' =>
            array(
                ':globe_with_meridians:',
            ),
            'smiley' =>
            array(
                ':smiley:',
            ),
            'loudspeaker' =>
            array(
                ':loudspeaker:',
            ),
            'sparkles' =>
            array(
                ':sparkles:',
            ),
            'dog2' =>
            array(
                ':dog2:',
            ),
            'waving_white_flag' =>
            array(
                ':waving_white_flag:',
            ),
            'world_map' =>
            array(
                ':world_map:',
            ),
            'lemon' =>
            array(
                ':lemon:',
            ),
            'mens' =>
            array(
                ':mens:',
            ),
            'womens' =>
            array(
                ':womens:',
            ),
            'rainbow-flag' =>
            array(
                ':rainbow-flag:',
            ),
            'banana' =>
            array(
                ':banana:',
            ),
            'mega' =>
            array(
                ':mega:',
            ),
            'japan' =>
            array(
                ':japan:',
            ),
            'poodle' =>
            array(
                ':poodle:',
            ),
            'balloon' =>
            array(
                ':balloon:',
            ),
            'flag-ac' =>
            array(
                ':flag-ac:',
            ),
            'sweat_smile' =>
            array(
                ':sweat_smile:',
            ),
            'pineapple' =>
            array(
                ':pineapple:',
            ),
            'restroom' =>
            array(
                ':restroom:',
            ),
            'postal_horn' =>
            array(
                ':postal_horn:',
            ),
            'wolf' =>
            array(
                ':wolf:',
            ),
            'tada' =>
            array(
                ':tada:',
            ),
            'snow_capped_mountain' =>
            array(
                ':snow_capped_mountain:',
            ),
            'laughing' =>
            array(
                ':laughing:',
            ),
            'apple' =>
            array(
                ':apple:',
            ),
            'flag-ad' =>
            array(
                ':flag-ad:',
            ),
            'fox_face' =>
            array(
                ':fox_face:',
            ),
            'confetti_ball' =>
            array(
                ':confetti_ball:',
            ),
            'bell' =>
            array(
                ':bell:',
            ),
            'mountain' =>
            array(
                ':mountain:',
            ),
            'baby_symbol' =>
            array(
                ':baby_symbol:',
            ),
            'wc' =>
            array(
                ':wc:',
            ),
            'no_bell' =>
            array(
                ':no_bell:',
            ),
            'green_apple' =>
            array(
                ':green_apple:',
            ),
            'tanabata_tree' =>
            array(
                ':tanabata_tree:',
            ),
            'flag-ae' =>
            array(
                ':flag-ae:',
            ),
            'volcano' =>
            array(
                ':volcano:',
            ),
            'cat' =>
            array(
                ':cat:',
            ),
            'flag-af' =>
            array(
                ':flag-af:',
            ),
            'musical_score' =>
            array(
                ':musical_score:',
            ),
            'blush' =>
            array(
                ':blush:',
            ),
            'pear' =>
            array(
                ':pear:',
            ),
            'bamboo' =>
            array(
                ':bamboo:',
            ),
            'passport_control' =>
            array(
                ':passport_control:',
            ),
            'mount_fuji' =>
            array(
                ':mount_fuji:',
            ),
            'cat2' =>
            array(
                ':cat2:',
            ),
            'musical_note' =>
            array(
                ':musical_note:',
            ),
            'dolls' =>
            array(
                ':dolls:',
            ),
            'lion_face' =>
            array(
                ':lion_face:',
            ),
            'camping' =>
            array(
                ':camping:',
            ),
            'flag-ag' =>
            array(
                ':flag-ag:',
            ),
            'customs' =>
            array(
                ':customs:',
            ),
            'yum' =>
            array(
                ':yum:',
            ),
            'peach' =>
            array(
                ':peach:',
            ),
            'tiger' =>
            array(
                ':tiger:',
            ),
            'notes' =>
            array(
                ':notes:',
            ),
            'flags' =>
            array(
                ':flags:',
            ),
            'beach_with_umbrella' =>
            array(
                ':beach_with_umbrella:',
            ),
            'cherries' =>
            array(
                ':cherries:',
            ),
            'flag-ai' =>
            array(
                ':flag-ai:',
            ),
            'baggage_claim' =>
            array(
                ':baggage_claim:',
            ),
            'left_luggage' =>
            array(
                ':left_luggage:',
            ),
            'wind_chime' =>
            array(
                ':wind_chime:',
            ),
            'strawberry' =>
            array(
                ':strawberry:',
            ),
            'desert' =>
            array(
                ':desert:',
            ),
            'studio_microphone' =>
            array(
                ':studio_microphone:',
            ),
            'flag-al' =>
            array(
                ':flag-al:',
            ),
            'tiger2' =>
            array(
                ':tiger2:',
            ),
            'heart_eyes' =>
            array(
                ':heart_eyes:',
            ),
            'desert_island' =>
            array(
                ':desert_island:',
            ),
            'kiwifruit' =>
            array(
                ':kiwifruit:',
            ),
            'rice_scene' =>
            array(
                ':rice_scene:',
            ),
            'kissing_heart' =>
            array(
                ':kissing_heart:',
            ),
            'warning' =>
            array(
                ':warning:',
            ),
            'flag-am' =>
            array(
                ':flag-am:',
            ),
            'leopard' =>
            array(
                ':leopard:',
            ),
            'level_slider' =>
            array(
                ':level_slider:',
            ),
            'horse' =>
            array(
                ':horse:',
            ),
            'children_crossing' =>
            array(
                ':children_crossing:',
            ),
            'ribbon' =>
            array(
                ':ribbon:',
            ),
            'national_park' =>
            array(
                ':national_park:',
            ),
            'control_knobs' =>
            array(
                ':control_knobs:',
            ),
            'kissing' =>
            array(
                ':kissing:',
            ),
            'tomato' =>
            array(
                ':tomato:',
            ),
            'flag-ao' =>
            array(
                ':flag-ao:',
            ),
            'stadium' =>
            array(
                ':stadium:',
            ),
            'flag-aq' =>
            array(
                ':flag-aq:',
            ),
            'gift' =>
            array(
                ':gift:',
            ),
            'no_entry' =>
            array(
                ':no_entry:',
            ),
            'kissing_smiling_eyes' =>
            array(
                ':kissing_smiling_eyes:',
            ),
            'coconut' =>
            array(
                ':coconut:',
            ),
            'racehorse' =>
            array(
                ':racehorse:',
            ),
            'microphone' =>
            array(
                ':microphone:',
            ),
            'classical_building' =>
            array(
                ':classical_building:',
            ),
            'no_entry_sign' =>
            array(
                ':no_entry_sign:',
            ),
            'reminder_ribbon' =>
            array(
                ':reminder_ribbon:',
            ),
            'kissing_closed_eyes' =>
            array(
                ':kissing_closed_eyes:',
            ),
            'unicorn_face' =>
            array(
                ':unicorn_face:',
            ),
            'flag-ar' =>
            array(
                ':flag-ar:',
            ),
            'headphones' =>
            array(
                ':headphones:',
            ),
            'avocado' =>
            array(
                ':avocado:',
            ),
            'relaxed' =>
            array(
                ':relaxed:',
            ),
            'zebra_face' =>
            array(
                ':zebra_face:',
            ),
            'eggplant' =>
            array(
                ':eggplant:',
            ),
            'radio' =>
            array(
                ':radio:',
            ),
            'building_construction' =>
            array(
                ':building_construction:',
            ),
            'flag-as' =>
            array(
                ':flag-as:',
            ),
            'admission_tickets' =>
            array(
                ':admission_tickets:',
            ),
            'no_bicycles' =>
            array(
                ':no_bicycles:',
            ),
            'no_smoking' =>
            array(
                ':no_smoking:',
            ),
            'slightly_smiling_face' =>
            array(
                ':slightly_smiling_face:',
            ),
            'flag-at' =>
            array(
                ':flag-at:',
            ),
            'ticket' =>
            array(
                ':ticket:',
            ),
            'saxophone' =>
            array(
                ':saxophone:',
            ),
            'deer' =>
            array(
                ':deer:',
            ),
            'house_buildings' =>
            array(
                ':house_buildings:',
            ),
            'potato' =>
            array(
                ':potato:',
            ),
            'guitar' =>
            array(
                ':guitar:',
            ),
            'carrot' =>
            array(
                ':carrot:',
            ),
            'cityscape' =>
            array(
                ':cityscape:',
            ),
            'flag-au' =>
            array(
                ':flag-au:',
            ),
            'do_not_litter' =>
            array(
                ':do_not_litter:',
            ),
            'hugging_face' =>
            array(
                ':hugging_face:',
            ),
            'cow' =>
            array(
                ':cow:',
            ),
            'medal' =>
            array(
                ':medal:',
            ),
            'musical_keyboard' =>
            array(
                ':musical_keyboard:',
            ),
            'corn' =>
            array(
                ':corn:',
            ),
            'derelict_house_building' =>
            array(
                ':derelict_house_building:',
            ),
            'non-potable_water' =>
            array(
                ':non-potable_water:',
            ),
            'trophy' =>
            array(
                ':trophy:',
            ),
            'flag-aw' =>
            array(
                ':flag-aw:',
            ),
            'ox' =>
            array(
                ':ox:',
            ),
            'trumpet' =>
            array(
                ':trumpet:',
            ),
            'hot_pepper' =>
            array(
                ':hot_pepper:',
            ),
            'sports_medal' =>
            array(
                ':sports_medal:',
            ),
            'flag-ax' =>
            array(
                ':flag-ax:',
            ),
            'water_buffalo' =>
            array(
                ':water_buffalo:',
            ),
            'no_pedestrians' =>
            array(
                ':no_pedestrians:',
            ),
            'thinking_face' =>
            array(
                ':thinking_face:',
            ),
            'house' =>
            array(
                ':house:',
            ),
            'no_mobile_phones' =>
            array(
                ':no_mobile_phones:',
            ),
            'flag-az' =>
            array(
                ':flag-az:',
            ),
            'first_place_medal' =>
            array(
                ':first_place_medal:',
            ),
            'house_with_garden' =>
            array(
                ':house_with_garden:',
            ),
            'violin' =>
            array(
                ':violin:',
            ),
            'face_with_raised_eyebrow' =>
            array(
                ':face_with_raised_eyebrow:',
            ),
            'cucumber' =>
            array(
                ':cucumber:',
            ),
            'cow2' =>
            array(
                ':cow2:',
            ),
            'flag-ba' =>
            array(
                ':flag-ba:',
            ),
            'pig' =>
            array(
                ':pig:',
            ),
            'drum_with_drumsticks' =>
            array(
                ':drum_with_drumsticks:',
            ),
            'underage' =>
            array(
                ':underage:',
            ),
            'broccoli' =>
            array(
                ':broccoli:',
            ),
            'office' =>
            array(
                ':office:',
            ),
            'second_place_medal' =>
            array(
                ':second_place_medal:',
            ),
            'neutral_face' =>
            array(
                ':neutral_face:',
            ),
            'third_place_medal' =>
            array(
                ':third_place_medal:',
            ),
            'mushroom' =>
            array(
                ':mushroom:',
            ),
            'flag-bb' =>
            array(
                ':flag-bb:',
            ),
            'radioactive_sign' =>
            array(
                ':radioactive_sign:',
            ),
            'pig2' =>
            array(
                ':pig2:',
            ),
            'expressionless' =>
            array(
                ':expressionless:',
            ),
            'iphone' =>
            array(
                ':iphone:',
            ),
            'post_office' =>
            array(
                ':post_office:',
            ),
            'european_post_office' =>
            array(
                ':european_post_office:',
            ),
            'soccer' =>
            array(
                ':soccer:',
            ),
            'boar' =>
            array(
                ':boar:',
            ),
            'peanuts' =>
            array(
                ':peanuts:',
            ),
            'calling' =>
            array(
                ':calling:',
            ),
            'biohazard_sign' =>
            array(
                ':biohazard_sign:',
            ),
            'flag-bd' =>
            array(
                ':flag-bd:',
            ),
            'no_mouth' =>
            array(
                ':no_mouth:',
            ),
            'face_with_rolling_eyes' =>
            array(
                ':face_with_rolling_eyes:',
            ),
            'phone' =>
            array(
                ':phone:',
            ),
            'pig_nose' =>
            array(
                ':pig_nose:',
            ),
            'chestnut' =>
            array(
                ':chestnut:',
            ),
            'arrow_up' =>
            array(
                ':arrow_up:',
            ),
            'hospital' =>
            array(
                ':hospital:',
            ),
            'flag-be' =>
            array(
                ':flag-be:',
            ),
            'baseball' =>
            array(
                ':baseball:',
            ),
            'smirk' =>
            array(
                ':smirk:',
            ),
            'arrow_upper_right' =>
            array(
                ':arrow_upper_right:',
            ),
            'flag-bf' =>
            array(
                ':flag-bf:',
            ),
            'basketball' =>
            array(
                ':basketball:',
            ),
            'ram' =>
            array(
                ':ram:',
            ),
            'bank' =>
            array(
                ':bank:',
            ),
            'bread' =>
            array(
                ':bread:',
            ),
            'telephone_receiver' =>
            array(
                ':telephone_receiver:',
            ),
            'croissant' =>
            array(
                ':croissant:',
            ),
            'pager' =>
            array(
                ':pager:',
            ),
            'sheep' =>
            array(
                ':sheep:',
            ),
            'arrow_right' =>
            array(
                ':arrow_right:',
            ),
            'persevere' =>
            array(
                ':persevere:',
            ),
            'flag-bg' =>
            array(
                ':flag-bg:',
            ),
            'volleyball' =>
            array(
                ':volleyball:',
            ),
            'hotel' =>
            array(
                ':hotel:',
            ),
            'arrow_lower_right' =>
            array(
                ':arrow_lower_right:',
            ),
            'goat' =>
            array(
                ':goat:',
            ),
            'flag-bh' =>
            array(
                ':flag-bh:',
            ),
            'love_hotel' =>
            array(
                ':love_hotel:',
            ),
            'disappointed_relieved' =>
            array(
                ':disappointed_relieved:',
            ),
            'baguette_bread' =>
            array(
                ':baguette_bread:',
            ),
            'football' =>
            array(
                ':football:',
            ),
            'fax' =>
            array(
                ':fax:',
            ),
            'convenience_store' =>
            array(
                ':convenience_store:',
            ),
            'dromedary_camel' =>
            array(
                ':dromedary_camel:',
            ),
            'arrow_down' =>
            array(
                ':arrow_down:',
            ),
            'battery' =>
            array(
                ':battery:',
            ),
            'rugby_football' =>
            array(
                ':rugby_football:',
            ),
            'pretzel' =>
            array(
                ':pretzel:',
            ),
            'open_mouth' =>
            array(
                ':open_mouth:',
            ),
            'flag-bi' =>
            array(
                ':flag-bi:',
            ),
            'flag-bj' =>
            array(
                ':flag-bj:',
            ),
            'pancakes' =>
            array(
                ':pancakes:',
            ),
            'school' =>
            array(
                ':school:',
            ),
            'tennis' =>
            array(
                ':tennis:',
            ),
            'zipper_mouth_face' =>
            array(
                ':zipper_mouth_face:',
            ),
            'camel' =>
            array(
                ':camel:',
            ),
            'arrow_lower_left' =>
            array(
                ':arrow_lower_left:',
            ),
            'electric_plug' =>
            array(
                ':electric_plug:',
            ),
            'cheese_wedge' =>
            array(
                ':cheese_wedge:',
            ),
            'hushed' =>
            array(
                ':hushed:',
            ),
            'computer' =>
            array(
                ':computer:',
            ),
            'giraffe_face' =>
            array(
                ':giraffe_face:',
            ),
            '8ball' =>
            array(
                ':8ball:',
            ),
            'flag-bl' =>
            array(
                ':flag-bl:',
            ),
            'arrow_left' =>
            array(
                ':arrow_left:',
            ),
            'department_store' =>
            array(
                ':department_store:',
            ),
            'meat_on_bone' =>
            array(
                ':meat_on_bone:',
            ),
            'arrow_upper_left' =>
            array(
                ':arrow_upper_left:',
            ),
            'flag-bm' =>
            array(
                ':flag-bm:',
            ),
            'sleepy' =>
            array(
                ':sleepy:',
            ),
            'bowling' =>
            array(
                ':bowling:',
            ),
            'factory' =>
            array(
                ':factory:',
            ),
            'desktop_computer' =>
            array(
                ':desktop_computer:',
            ),
            'elephant' =>
            array(
                ':elephant:',
            ),
            'rhinoceros' =>
            array(
                ':rhinoceros:',
            ),
            'arrow_up_down' =>
            array(
                ':arrow_up_down:',
            ),
            'cricket_bat_and_ball' =>
            array(
                ':cricket_bat_and_ball:',
            ),
            'printer' =>
            array(
                ':printer:',
            ),
            'poultry_leg' =>
            array(
                ':poultry_leg:',
            ),
            'tired_face' =>
            array(
                ':tired_face:',
            ),
            'japanese_castle' =>
            array(
                ':japanese_castle:',
            ),
            'flag-bn' =>
            array(
                ':flag-bn:',
            ),
            'field_hockey_stick_and_ball' =>
            array(
                ':field_hockey_stick_and_ball:',
            ),
            'sleeping' =>
            array(
                ':sleeping:',
            ),
            'left_right_arrow' =>
            array(
                ':left_right_arrow:',
            ),
            'keyboard' =>
            array(
                ':keyboard:',
            ),
            'european_castle' =>
            array(
                ':european_castle:',
            ),
            'mouse' =>
            array(
                ':mouse:',
            ),
            'flag-bo' =>
            array(
                ':flag-bo:',
            ),
            'cut_of_meat' =>
            array(
                ':cut_of_meat:',
            ),
            'ice_hockey_stick_and_puck' =>
            array(
                ':ice_hockey_stick_and_puck:',
            ),
            'mouse2' =>
            array(
                ':mouse2:',
            ),
            'three_button_mouse' =>
            array(
                ':three_button_mouse:',
            ),
            'leftwards_arrow_with_hook' =>
            array(
                ':leftwards_arrow_with_hook:',
            ),
            'bacon' =>
            array(
                ':bacon:',
            ),
            'relieved' =>
            array(
                ':relieved:',
            ),
            'flag-bq' =>
            array(
                ':flag-bq:',
            ),
            'wedding' =>
            array(
                ':wedding:',
            ),
            'tokyo_tower' =>
            array(
                ':tokyo_tower:',
            ),
            'arrow_right_hook' =>
            array(
                ':arrow_right_hook:',
            ),
            'hamburger' =>
            array(
                ':hamburger:',
            ),
            'stuck_out_tongue' =>
            array(
                ':stuck_out_tongue:',
            ),
            'trackball' =>
            array(
                ':trackball:',
            ),
            'flag-br' =>
            array(
                ':flag-br:',
            ),
            'rat' =>
            array(
                ':rat:',
            ),
            'table_tennis_paddle_and_ball' =>
            array(
                ':table_tennis_paddle_and_ball:',
            ),
            'minidisc' =>
            array(
                ':minidisc:',
            ),
            'stuck_out_tongue_winking_eye' =>
            array(
                ':stuck_out_tongue_winking_eye:',
            ),
            'fries' =>
            array(
                ':fries:',
            ),
            'badminton_racquet_and_shuttlecock' =>
            array(
                ':badminton_racquet_and_shuttlecock:',
            ),
            'statue_of_liberty' =>
            array(
                ':statue_of_liberty:',
            ),
            'flag-bs' =>
            array(
                ':flag-bs:',
            ),
            'arrow_heading_up' =>
            array(
                ':arrow_heading_up:',
            ),
            'hamster' =>
            array(
                ':hamster:',
            ),
            'stuck_out_tongue_closed_eyes' =>
            array(
                ':stuck_out_tongue_closed_eyes:',
            ),
            'pizza' =>
            array(
                ':pizza:',
            ),
            'boxing_glove' =>
            array(
                ':boxing_glove:',
            ),
            'floppy_disk' =>
            array(
                ':floppy_disk:',
            ),
            'arrow_heading_down' =>
            array(
                ':arrow_heading_down:',
            ),
            'flag-bt' =>
            array(
                ':flag-bt:',
            ),
            'rabbit' =>
            array(
                ':rabbit:',
            ),
            'church' =>
            array(
                ':church:',
            ),
            'drooling_face' =>
            array(
                ':drooling_face:',
            ),
            'flag-bv' =>
            array(
                ':flag-bv:',
            ),
            'mosque' =>
            array(
                ':mosque:',
            ),
            'rabbit2' =>
            array(
                ':rabbit2:',
            ),
            'hotdog' =>
            array(
                ':hotdog:',
            ),
            'martial_arts_uniform' =>
            array(
                ':martial_arts_uniform:',
            ),
            'arrows_clockwise' =>
            array(
                ':arrows_clockwise:',
            ),
            'cd' =>
            array(
                ':cd:',
            ),
            'arrows_counterclockwise' =>
            array(
                ':arrows_counterclockwise:',
            ),
            'sandwich' =>
            array(
                ':sandwich:',
            ),
            'chipmunk' =>
            array(
                ':chipmunk:',
            ),
            'synagogue' =>
            array(
                ':synagogue:',
            ),
            'goal_net' =>
            array(
                ':goal_net:',
            ),
            'flag-bw' =>
            array(
                ':flag-bw:',
            ),
            'dvd' =>
            array(
                ':dvd:',
            ),
            'hedgehog' =>
            array(
                ':hedgehog:',
            ),
            'dart' =>
            array(
                ':dart:',
            ),
            'taco' =>
            array(
                ':taco:',
            ),
            'back' =>
            array(
                ':back:',
            ),
            'flag-by' =>
            array(
                ':flag-by:',
            ),
            'shinto_shrine' =>
            array(
                ':shinto_shrine:',
            ),
            'movie_camera' =>
            array(
                ':movie_camera:',
            ),
            'burrito' =>
            array(
                ':burrito:',
            ),
            'flag-bz' =>
            array(
                ':flag-bz:',
            ),
            'pensive' =>
            array(
                ':pensive:',
            ),
            'kaaba' =>
            array(
                ':kaaba:',
            ),
            'film_frames' =>
            array(
                ':film_frames:',
            ),
            'bat' =>
            array(
                ':bat:',
            ),
            'golf' =>
            array(
                ':golf:',
            ),
            'end' =>
            array(
                ':end:',
            ),
            'film_projector' =>
            array(
                ':film_projector:',
            ),
            'bear' =>
            array(
                ':bear:',
            ),
            'ice_skate' =>
            array(
                ':ice_skate:',
            ),
            'fountain' =>

            array(
                ':fountain:',
            ),
            'flag-ca' =>
            array(
                ':flag-ca:',
            ),
            'on' =>
            array(
                ':on:',
            ),
            'stuffed_flatbread' =>
            array(
                ':stuffed_flatbread:',
            ),
            'soon' =>
            array(
                ':soon:',
            ),
            'upside_down_face' =>
            array(
                ':upside_down_face:',
            ),
            'fishing_pole_and_fish' =>
            array(
                ':fishing_pole_and_fish:',
            ),
            'tent' =>
            array(
                ':tent:',
            ),
            'clapper' =>
            array(
                ':clapper:',
            ),
            'egg' =>
            array(
                ':egg:',
            ),
            'flag-cc' =>
            array(
                ':flag-cc:',
            ),
            'koala' =>
            array(
                ':koala:',
            ),
            'foggy' =>
            array(
                ':foggy:',
            ),
            'tv' =>
            array(
                ':tv:',
            ),
            'panda_face' =>
            array(
                ':panda_face:',
            ),
            'fried_egg' =>
            array(
                ':fried_egg:',
            ),
            'top' =>
            array(
                ':top:',
            ),
            'flag-cd' =>
            array(
                ':flag-cd:',
            ),
            'money_mouth_face' =>
            array(
                ':money_mouth_face:',
            ),
            'running_shirt_with_sash' =>
            array(
                ':running_shirt_with_sash:',
            ),
            'astonished' =>
            array(
                ':astonished:',
            ),
            'feet' =>
            array(
                ':feet:',
            ),
            'camera' =>
            array(
                ':camera:',
            ),
            'flag-cf' =>
            array(
                ':flag-cf:',
            ),
            'place_of_worship' =>
            array(
                ':place_of_worship:',
            ),
            'night_with_stars' =>
            array(
                ':night_with_stars:',
            ),
            'ski' =>
            array(
                ':ski:',
            ),
            'shallow_pan_of_food' =>
            array(
                ':shallow_pan_of_food:',
            ),
            'camera_with_flash' =>
            array(
                ':camera_with_flash:',
            ),
            'sunrise_over_mountains' =>
            array(
                ':sunrise_over_mountains:',
            ),
            'turkey' =>
            array(
                ':turkey:',
            ),
            'white_frowning_face' =>
            array(
                ':white_frowning_face:',
            ),
            'flag-cg' =>
            array(
                ':flag-cg:',
            ),
            'stew' =>
            array(
                ':stew:',
            ),
            'sled' =>
            array(
                ':sled:',
            ),
            'atom_symbol' =>
            array(
                ':atom_symbol:',
            ),
            'curling_stone' =>
            array(
                ':curling_stone:',
            ),
            'slightly_frowning_face' =>
            array(
                ':slightly_frowning_face:',
            ),
            'sunrise' =>
            array(
                ':sunrise:',
            ),
            'om_symbol' =>
            array(
                ':om_symbol:',
            ),
            'chicken' =>
            array(
                ':chicken:',
            ),
            'bowl_with_spoon' =>
            array(
                ':bowl_with_spoon:',
            ),
            'flag-ch' =>
            array(
                ':flag-ch:',
            ),
            'video_camera' =>
            array(
                ':video_camera:',
            ),
            'video_game' =>
            array(
                ':video_game:',
            ),
            'rooster' =>
            array(
                ':rooster:',
            ),
            'vhs' =>
            array(
                ':vhs:',
            ),
            'city_sunset' =>
            array(
                ':city_sunset:',
            ),
            'confounded' =>
            array(
                ':confounded:',
            ),
            'green_salad' =>
            array(
                ':green_salad:',
            ),
            'star_of_david' =>
            array(
                ':star_of_david:',
            ),
            'flag-ci' =>
            array(
                ':flag-ci:',
            ),
            'popcorn' =>
            array(
                ':popcorn:',
            ),
            'city_sunrise' =>
            array(
                ':city_sunrise:',
            ),
            'disappointed' =>
            array(
                ':disappointed:',
            ),
            'mag' =>
            array(
                ':mag:',
            ),
            'hatching_chick' =>
            array(
                ':hatching_chick:',
            ),
            'joystick' =>
            array(
                ':joystick:',
            ),
            'wheel_of_dharma' =>
            array(
                ':wheel_of_dharma:',
            ),
            'flag-ck' =>
            array(
                ':flag-ck:',
            ),
            'canned_food' =>
            array(
                ':canned_food:',
            ),
            'baby_chick' =>
            array(
                ':baby_chick:',
            ),
            'flag-cl' =>
            array(
                ':flag-cl:',
            ),
            'game_die' =>
            array(
                ':game_die:',
            ),
            'mag_right' =>
            array(
                ':mag_right:',
            ),
            'yin_yang' =>
            array(
                ':yin_yang:',
            ),
            'bridge_at_night' =>
            array(
                ':bridge_at_night:',
            ),
            'spades' =>
            array(
                ':spades:',
            ),
            'hatched_chick' =>
            array(
                ':hatched_chick:',
            ),
            'flag-cm' =>
            array(
                ':flag-cm:',
            ),
            'latin_cross' =>
            array(
                ':latin_cross:',
            ),
            'triumph' =>
            array(
                ':triumph:',
            ),
            'hotsprings' =>
            array(
                ':hotsprings:',
            ),
            'bento' =>
            array(
                ':bento:',
            ),
            'microscope' =>
            array(
                ':microscope:',
            ),
            'bird' =>
            array(
                ':bird:',
            ),
            'cn' =>
            array(
                ':cn:',
            ),
            'telescope' =>
            array(
                ':telescope:',
            ),
            'rice_cracker' =>
            array(
                ':rice_cracker:',
            ),
            'hearts' =>
            array(
                ':hearts:',
            ),
            'orthodox_cross' =>
            array(
                ':orthodox_cross:',
            ),
            'milky_way' =>
            array(
                ':milky_way:',
            ),
            'rice_ball' =>
            array(
                ':rice_ball:',
            ),
            'satellite_antenna' =>
            array(
                ':satellite_antenna:',
            ),
            'flag-co' =>
            array(
                ':flag-co:',
            ),
            'carousel_horse' =>
            array(
                ':carousel_horse:',
            ),
            'sob' =>
            array(
                ':sob:',
            ),
            'diamonds' =>
            array(
                ':diamonds:',
            ),
            'star_and_crescent' =>
            array(
                ':star_and_crescent:',
            ),
            'penguin' =>
            array(
                ':penguin:',
            ),
            'dove_of_peace' =>
            array(
                ':dove_of_peace:',
            ),
            'flag-cp' =>
            array(
                ':flag-cp:',
            ),
            'ferris_wheel' =>
            array(
                ':ferris_wheel:',
            ),
            'clubs' =>
            array(
                ':clubs:',
            ),
            'peace_symbol' =>
            array(
                ':peace_symbol:',
            ),
            'candle' =>
            array(
                ':candle:',
            ),
            'frowning' =>
            array(
                ':frowning:',
            ),
            'rice' =>
            array(
                ':rice:',
            ),
            'flag-cr' =>
            array(
                ':flag-cr:',
            ),
            'roller_coaster' =>
            array(
                ':roller_coaster:',
            ),
            'menorah_with_nine_branches' =>
            array(
                ':menorah_with_nine_branches:',
            ),
            'black_joker' =>
            array(
                ':black_joker:',
            ),
            'eagle' =>
            array(
                ':eagle:',
            ),
            'curry' =>
            array(
                ':curry:',
            ),
            'bulb' =>
            array(
                ':bulb:',
            ),
            'anguished' =>
            array(
                ':anguished:',
            ),
            'flag-cu' =>
            array(
                ':flag-cu:',
            ),
            'barber' =>
            array(
                ':barber:',
            ),
            'duck' =>
            array(
                ':duck:',
            ),
            'six_pointed_star' =>
            array(
                ':six_pointed_star:',
            ),
            'ramen' =>
            array(
                ':ramen:',
            ),
            'flashlight' =>
            array(
                ':flashlight:',
            ),
            'mahjong' =>
            array(
                ':mahjong:',
            ),
            'aries' =>
            array(
                ':aries:',
            ),
            'spaghetti' =>
            array(
                ':spaghetti:',
            ),
            'circus_tent' =>
            array(
                ':circus_tent:',
            ),
            'izakaya_lantern' =>
            array(
                ':izakaya_lantern:',
            ),
            'flag-cv' =>
            array(
                ':flag-cv:',
            ),
            'weary' =>
            array(
                ':weary:',
            ),
            'flower_playing_cards' =>
            array(
                ':flower_playing_cards:',
            ),
            'owl' =>
            array(
                ':owl:',
            ),
            'performing_arts' =>
            array(
                ':performing_arts:',
            ),
            'frog' =>
            array(
                ':frog:',
            ),
            'flag-cw' =>
            array(
                ':flag-cw:',
            ),
            'notebook_with_decorative_cover' =>
            array(
                ':notebook_with_decorative_cover:',
            ),
            'exploding_head' =>
            array(
                ':exploding_head:',
            ),
            'taurus' =>
            array(
                ':taurus:',
            ),
            'sweet_potato' =>
            array(
                ':sweet_potato:',
            ),
            'closed_book' =>
            array(
                ':closed_book:',
            ),
            'gemini' =>
            array(
                ':gemini:',
            ),
            'frame_with_picture' =>
            array(
                ':frame_with_picture:',
            ),
            'flag-cx' =>
            array(
                ':flag-cx:',
            ),
            'grimacing' =>
            array(
                ':grimacing:',
            ),
            'crocodile' =>
            array(
                ':crocodile:',
            ),
            'oden' =>
            array(
                ':oden:',
            ),
            'flag-cy' =>
            array(
                ':flag-cy:',
            ),
            'book' =>
            array(
                ':book:',
            ),
            'turtle' =>
            array(
                ':turtle:',
            ),
            'art' =>
            array(
                ':art:',
            ),
            'sushi' =>
            array(
                ':sushi:',
            ),
            'cold_sweat' =>
            array(
                ':cold_sweat:',
            ),
            'cancer' =>
            array(
                ':cancer:',
            ),
            'fried_shrimp' =>
            array(
                ':fried_shrimp:',
            ),
            'slot_machine' =>
            array(
                ':slot_machine:',
            ),
            'scream' =>
            array(
                ':scream:',
            ),
            'green_book' =>
            array(
                ':green_book:',
            ),
            'leo' =>
            array(
                ':leo:',
            ),
            'flag-cz' =>
            array(
                ':flag-cz:',
            ),
            'lizard' =>
            array(
                ':lizard:',
            ),
            'virgo' =>
            array(
                ':virgo:',
            ),
            'steam_locomotive' =>
            array(
                ':steam_locomotive:',
            ),
            'de' =>
            array(
                ':de:',
            ),
            'blue_book' =>
            array(
                ':blue_book:',
            ),
            'snake' =>
            array(
                ':snake:',
            ),
            'fish_cake' =>
            array(
                ':fish_cake:',
            ),
            'railway_car' =>
            array(
                ':railway_car:',
            ),
            'dango' =>
            array(
                ':dango:',
            ),
            'orange_book' =>
            array(
                ':orange_book:',
            ),
            'libra' =>
            array(
                ':libra:',
            ),
            'dragon_face' =>
            array(
                ':dragon_face:',
            ),
            'flag-dg' =>
            array(
                ':flag-dg:',
            ),
            'zany_face' =>
            array(
                ':zany_face:',
            ),
            'books' =>
            array(
                ':books:',
            ),
            'dragon' =>
            array(
                ':dragon:',
            ),
            'flag-dj' =>
            array(
                ':flag-dj:',
            ),
            'dumpling' =>
            array(
                ':dumpling:',
            ),
            'scorpius' =>
            array(
                ':scorpius:',
            ),
            'bullettrain_side' =>
            array(
                ':bullettrain_side:',
            ),
            'bullettrain_front' =>
            array(
                ':bullettrain_front:',
            ),
            'notebook' =>
            array(
                ':notebook:',
            ),
            'fortune_cookie' =>
            array(
                ':fortune_cookie:',
            ),
            'sagittarius' =>
            array(
                ':sagittarius:',
            ),
            'sauropod' =>
            array(
                ':sauropod:',
            ),
            'flag-dk' =>
            array(
                ':flag-dk:',
            ),
            'rage' =>
            array(
                ':rage:',
            ),
            'ledger' =>
            array(
                ':ledger:',
            ),
            't-rex' =>
            array(
                ':t-rex:',
            ),
            'capricorn' =>
            array(
                ':capricorn:',
            ),
            'takeout_box' =>
            array(
                ':takeout_box:',
            ),
            'flag-dm' =>
            array(
                ':flag-dm:',
            ),
            'train2' =>
            array(
                ':train2:',
            ),
            'page_with_curl' =>
            array(
                ':page_with_curl:',
            ),
            'whale' =>
            array(
                ':whale:',
            ),
            'face_with_symbols_on_mouth' =>
            array(
                ':face_with_symbols_on_mouth:',
            ),
            'flag-do' =>
            array(
                ':flag-do:',
            ),
            'metro' =>
            array(
                ':metro:',
            ),
            'icecream' =>
            array(
                ':icecream:',
            ),
            'aquarius' =>
            array(
                ':aquarius:',
            ),
            'flag-dz' =>
            array(
                ':flag-dz:',
            ),
            'whale2' =>
            array(
                ':whale2:',
            ),
            'mask' =>
            array(
                ':mask:',
            ),
            'scroll' =>
            array(
                ':scroll:',
            ),
            'shaved_ice' =>
            array(
                ':shaved_ice:',
            ),
            'pisces' =>
            array(
                ':pisces:',
            ),
            'light_rail' =>
            array(
                ':light_rail:',
            ),
            'dolphin' =>
            array(
                ':dolphin:',
            ),
            'face_with_thermometer' =>
            array(
                ':face_with_thermometer:',
            ),
            'flag-ea' =>
            array(
                ':flag-ea:',
            ),
            'ophiuchus' =>
            array(
                ':ophiuchus:',
            ),
            'station' =>
            array(
                ':station:',
            ),
            'ice_cream' =>
            array(
                ':ice_cream:',
            ),
            'page_facing_up' =>
            array(
                ':page_facing_up:',
            ),
            'doughnut' =>
            array(
                ':doughnut:',
            ),
            'face_with_head_bandage' =>
            array(
                ':face_with_head_bandage:',
            ),
            'fish' =>
            array(
                ':fish:',
            ),
            'newspaper' =>
            array(
                ':newspaper:',
            ),
            'tram' =>
            array(
                ':tram:',
            ),
            'flag-ec' =>
            array(
                ':flag-ec:',
            ),
            'twisted_rightwards_arrows' =>
            array(
                ':twisted_rightwards_arrows:',
            ),
            'flag-ee' =>
            array(
                ':flag-ee:',
            ),
            'cookie' =>
            array(
                ':cookie:',
            ),
            'monorail' =>
            array(
                ':monorail:',
            ),
            'tropical_fish' =>
            array(
                ':tropical_fish:',
            ),
            'rolled_up_newspaper' =>
            array(
                ':rolled_up_newspaper:',
            ),
            'nauseated_face' =>
            array(
                ':nauseated_face:',
            ),
            'repeat' =>
            array(
                ':repeat:',
            ),
            'bookmark_tabs' =>
            array(
                ':bookmark_tabs:',
            ),
            'repeat_one' =>
            array(
                ':repeat_one:',
            ),
            'flag-eg' =>
            array(
                ':flag-eg:',
            ),
            'mountain_railway' =>
            array(
                ':mountain_railway:',
            ),
            'birthday' =>
            array(
                ':birthday:',
            ),
            'blowfish' =>
            array(
                ':blowfish:',
            ),
            'face_vomiting' =>
            array(
                ':face_vomiting:',
            ),
            'arrow_forward' =>
            array(
                ':arrow_forward:',
            ),
            'bookmark' =>
            array(
                ':bookmark:',
            ),
            'flag-eh' =>
            array(
                ':flag-eh:',
            ),
            'shark' =>
            array(
                ':shark:',
            ),
            'train' =>
            array(
                ':train:',
            ),
            'sneezing_face' =>
            array(
                ':sneezing_face:',
            ),
            'cake' =>
            array(
                ':cake:',
            ),
            'bus' =>
            array(
                ':bus:',
            ),
            'pie' =>
            array(
                ':pie:',
            ),
            'innocent' =>
            array(
                ':innocent:',
            ),
            'fast_forward' =>
            array(
                ':fast_forward:',
            ),
            'label' =>
            array(
                ':label:',
            ),
            'octopus' =>
            array(
                ':octopus:',
            ),
            'flag-er' =>
            array(
                ':flag-er:',
            ),
            'black_right_pointing_double_triangle_with_vertical_bar' =>
            array(
                ':black_right_pointing_double_triangle_with_vertical_bar:',
            ),
            'chocolate_bar' =>
            array(
                ':chocolate_bar:',
            ),
            'oncoming_bus' =>
            array(
                ':oncoming_bus:',
            ),
            'shell' =>
            array(
                ':shell:',
            ),
            'face_with_cowboy_hat' =>
            array(
                ':face_with_cowboy_hat:',
            ),
            'moneybag' =>
            array(
                ':moneybag:',
            ),
            'es' =>
            array(
                ':es:',
            ),
            'crab' =>
            array(
                ':crab:',
            ),
            'yen' =>
            array(
                ':yen:',
            ),
            'flag-et' =>
            array(
                ':flag-et:',
            ),
            'clown_face' =>
            array(
                ':clown_face:',
            ),
            'black_right_pointing_triangle_with_double_vertical_bar' =>
            array(
                ':black_right_pointing_triangle_with_double_vertical_bar:',
            ),
            'trolleybus' =>
            array(
                ':trolleybus:',
            ),
            'candy' =>
            array(
                ':candy:',
            ),
            'lying_face' =>
            array(
                ':lying_face:',
            ),
            'arrow_backward' =>
            array(
                ':arrow_backward:',
            ),
            'dollar' =>
            array(
                ':dollar:',
            ),
            'shrimp' =>
            array(
                ':shrimp:',
            ),
            'minibus' =>
            array(
                ':minibus:',
            ),
            'flag-eu' =>
            array(
                ':flag-eu:',
            ),
            'lollipop' =>
            array(
                ':lollipop:',
            ),
            'squid' =>
            array(
                ':squid:',
            ),
            'euro' =>
            array(
                ':euro:',
            ),
            'flag-fi' =>
            array(
                ':flag-fi:',
            ),
            'ambulance' =>
            array(
                ':ambulance:',
            ),
            'custard' =>
            array(
                ':custard:',
            ),
            'shushing_face' =>
            array(
                ':shushing_face:',
            ),
            'rewind' =>
            array(
                ':rewind:',
            ),
            'black_left_pointing_double_triangle_with_vertical_bar' =>
            array(
                ':black_left_pointing_double_triangle_with_vertical_bar:',
            ),
            'face_with_hand_over_mouth' =>
            array(
                ':face_with_hand_over_mouth:',
            ),
            'flag-fj' =>
            array(
                ':flag-fj:',
            ),
            'honey_pot' =>
            array(
                ':honey_pot:',
            ),
            'snail' =>
            array(
                ':snail:',
            ),
            'pound' =>
            array(
                ':pound:',
            ),
            'fire_engine' =>
            array(
                ':fire_engine:',
            ),
            'baby_bottle' =>
            array(
                ':baby_bottle:',
            ),
            'flag-fk' =>
            array(
                ':flag-fk:',
            ),
            'butterfly' =>
            array(
                ':butterfly:',
            ),
            'money_with_wings' =>
            array(
                ':money_with_wings:',
            ),
            'face_with_monocle' =>
            array(
                ':face_with_monocle:',
            ),
            'police_car' =>
            array(
                ':police_car:',
            ),
            'arrow_up_small' =>
            array(
                ':arrow_up_small:',
            ),
            'flag-fm' =>
            array(
                ':flag-fm:',
            ),
            'glass_of_milk' =>
            array(
                ':glass_of_milk:',
            ),
            'credit_card' =>
            array(
                ':credit_card:',
            ),
            'oncoming_police_car' =>
            array(
                ':oncoming_police_car:',
            ),
            'bug' =>
            array(
                ':bug:',
            ),
            'nerd_face' =>
            array(
                ':nerd_face:',
            ),
            'arrow_double_up' =>
            array(
                ':arrow_double_up:',
            ),
            'chart' =>
            array(
                ':chart:',
            ),
            'flag-fo' =>
            array(
                ':flag-fo:',
            ),
            'ant' =>
            array(
                ':ant:',
            ),
            'arrow_down_small' =>
            array(
                ':arrow_down_small:',
            ),
            'smiling_imp' =>
            array(
                ':smiling_imp:',
            ),
            'taxi' =>
            array(
                ':taxi:',
            ),
            'coffee' =>
            array(
                ':coffee:',
            ),
            'fr' =>
            array(
                ':fr:',
            ),
            'oncoming_taxi' =>
            array(
                ':oncoming_taxi:',
            ),
            'arrow_double_down' =>
            array(
                ':arrow_double_down:',
            ),
            'imp' =>
            array(
                ':imp:',
            ),
            'currency_exchange' =>
            array(
                ':currency_exchange:',
            ),
            'tea' =>
            array(
                ':tea:',
            ),
            'bee' =>
            array(
                ':bee:',
            ),
            'heavy_dollar_sign' =>
            array(
                ':heavy_dollar_sign:',
            ),
            'car' =>
            array(
                ':car:',
            ),
            'sake' =>
            array(
                ':sake:',
            ),
            'flag-ga' =>
            array(
                ':flag-ga:',
            ),
            'beetle' =>
            array(
                ':beetle:',
            ),
            'japanese_ogre' =>
            array(
                ':japanese_ogre:',
            ),
            'double_vertical_bar' =>
            array(
                ':double_vertical_bar:',
            ),
            'champagne' =>
            array(
                ':champagne:',
            ),
            'japanese_goblin' =>
            array(
                ':japanese_goblin:',
            ),
            'black_square_for_stop' =>
            array(
                ':black_square_for_stop:',
            ),
            'oncoming_automobile' =>
            array(
                ':oncoming_automobile:',
            ),
            'email' =>
            array(
                ':email:',
            ),
            'cricket' =>
            array(
                ':cricket:',
            ),
            'gb' =>
            array(
                ':gb:',
            ),
            'black_circle_for_record' =>
            array(
                ':black_circle_for_record:',
            ),
            'flag-gd' =>
            array(
                ':flag-gd:',
            ),
            'spider' =>
            array(
                ':spider:',
            ),
            'blue_car' =>
            array(
                ':blue_car:',
            ),
            'skull' =>
            array(
                ':skull:',
            ),
            'e-mail' =>
            array(
                ':e-mail:',
            ),
            'wine_glass' =>
            array(
                ':wine_glass:',
            ),
            'spider_web' =>
            array(
                ':spider_web:',
            ),
            'cocktail' =>
            array(
                ':cocktail:',
            ),
            'skull_and_crossbones' =>
            array(
                ':skull_and_crossbones:',
            ),
            'flag-ge' =>
            array(
                ':flag-ge:',
            ),
            'eject' =>
            array(
                ':eject:',
            ),
            'truck' =>
            array(
                ':truck:',
            ),
            'incoming_envelope' =>
            array(
                ':incoming_envelope:',
            ),
            'tropical_drink' =>
            array(
                ':tropical_drink:',
            ),
            'scorpion' =>
            array(
                ':scorpion:',
            ),
            'cinema' =>
            array(
                ':cinema:',
            ),
            'articulated_lorry' =>
            array(
                ':articulated_lorry:',
            ),
            'envelope_with_arrow' =>
            array(
                ':envelope_with_arrow:',
            ),
            'ghost' =>
            array(
                ':ghost:',
            ),
            'flag-gf' =>
            array(
                ':flag-gf:',
            ),
            'bouquet' =>
            array(
                ':bouquet:',
            ),
            'tractor' =>
            array(
                ':tractor:',
            ),
            'beer' =>
            array(
                ':beer:',
            ),
            'outbox_tray' =>
            array(
                ':outbox_tray:',
            ),
            'low_brightness' =>
            array(
                ':low_brightness:',
            ),
            'alien' =>
            array(
                ':alien:',
            ),
            'flag-gg' =>
            array(
                ':flag-gg:',
            ),
            'cherry_blossom' =>
            array(
                ':cherry_blossom:',
            ),
            'inbox_tray' =>
            array(
                ':inbox_tray:',
            ),
            'flag-gh' =>
            array(
                ':flag-gh:',
            ),
            'bike' =>
            array(
                ':bike:',
            ),
            'space_invader' =>
            array(
                ':space_invader:',
            ),
            'beers' =>
            array(
                ':beers:',
            ),
            'high_brightness' =>
            array(
                ':high_brightness:',
            ),
            'package' =>
            array(
                ':package:',
            ),
            'scooter' =>
            array(
                ':scooter:',
            ),
            'white_flower' =>
            array(
                ':white_flower:',
            ),
            'clinking_glasses' =>
            array(
                ':clinking_glasses:',
            ),
            'robot_face' =>
            array(
                ':robot_face:',
            ),
            'signal_strength' =>
            array(
                ':signal_strength:',
            ),
            'flag-gi' =>
            array(
                ':flag-gi:',
            ),
            'flag-gl' =>
            array(
                ':flag-gl:',
            ),
            'motor_scooter' =>
            array(
                ':motor_scooter:',
            ),
            'mailbox' =>
            array(
                ':mailbox:',
            ),
            'vibration_mode' =>
            array(
                ':vibration_mode:',
            ),
            'hankey' =>
            array(
                ':hankey:',
            ),
            'rosette' =>
            array(
                ':rosette:',
            ),
            'tumbler_glass' =>
            array(
                ':tumbler_glass:',
            ),
            'cup_with_straw' =>
            array(
                ':cup_with_straw:',
            ),
            'flag-gm' =>
            array(
                ':flag-gm:',
            ),
            'mailbox_closed' =>
            array(
                ':mailbox_closed:',
            ),
            'mobile_phone_off' =>
            array(
                ':mobile_phone_off:',
            ),
            'busstop' =>
            array(
                ':busstop:',
            ),
            'smiley_cat' =>
            array(
                ':smiley_cat:',
            ),
            'rose' =>
            array(
                ':rose:',
            ),
            'motorway' =>
            array(
                ':motorway:',
            ),
            'smile_cat' =>
            array(
                ':smile_cat:',
            ),
            'flag-gn' =>
            array(
                ':flag-gn:',
            ),
            'wilted_flower' =>
            array(
                ':wilted_flower:',
            ),
            'mailbox_with_mail' =>
            array(
                ':mailbox_with_mail:',
            ),
            'chopsticks' =>
            array(
                ':chopsticks:',
            ),
            'female_sign' =>
            array(
                ':female_sign:',
            ),
            'mailbox_with_no_mail' =>
            array(
                ':mailbox_with_no_mail:',
            ),
            'knife_fork_plate' =>
            array(
                ':knife_fork_plate:',
            ),
            'hibiscus' =>
            array(
                ':hibiscus:',
            ),
            'flag-gp' =>
            array(
                ':flag-gp:',
            ),
            'railway_track' =>
            array(
                ':railway_track:',
            ),
            'male_sign' =>
            array(
                ':male_sign:',
            ),
            'joy_cat' =>
            array(
                ':joy_cat:',
            ),
            'fuelpump' =>
            array(
                ':fuelpump:',
            ),
            'sunflower' =>
            array(
                ':sunflower:',
            ),
            'postbox' =>
            array(
                ':postbox:',
            ),
            'flag-gq' =>
            array(
                ':flag-gq:',
            ),
            'heart_eyes_cat' =>
            array(
                ':heart_eyes_cat:',
            ),
            'fork_and_knife' =>
            array(
                ':fork_and_knife:',
            ),
            'medical_symbol' =>
            array(
                ':medical_symbol:',
            ),
            'recycle' =>
            array(
                ':recycle:',
            ),
            'spoon' =>
            array(
                ':spoon:',
            ),
            'blossom' =>
            array(
                ':blossom:',
            ),
            'rotating_light' =>
            array(
                ':rotating_light:',
            ),
            'smirk_cat' =>
            array(
                ':smirk_cat:',
            ),
            'ballot_box_with_ballot' =>
            array(
                ':ballot_box_with_ballot:',
            ),
            'flag-gr' =>
            array(
                ':flag-gr:',
            ),
            'kissing_cat' =>
            array(
                ':kissing_cat:',
            ),
            'pencil2' =>
            array(
                ':pencil2:',
            ),
            'traffic_light' =>
            array(
                ':traffic_light:',
            ),
            'fleur_de_lis' =>
            array(
                ':fleur_de_lis:',
            ),
            'tulip' =>
            array(
                ':tulip:',
            ),
            'hocho' =>
            array(
                ':hocho:',
            ),
            'flag-gs' =>
            array(
                ':flag-gs:',
            ),
            'seedling' =>
            array(
                ':seedling:',
            ),
            'amphora' =>
            array(
                ':amphora:',
            ),
            'scream_cat' =>
            array(
                ':scream_cat:',
            ),
            'vertical_traffic_light' =>
            array(
                ':vertical_traffic_light:',
            ),
            'black_nib' =>
            array(
                ':black_nib:',
            ),
            'flag-gt' =>
            array(
                ':flag-gt:',
            ),
            'trident' =>
            array(
                ':trident:',
            ),
            'flag-gu' =>
            array(
                ':flag-gu:',
            ),
            'name_badge' =>
            array(
                ':name_badge:',
            ),
            'construction' =>
            array(
                ':construction:',
            ),
            'lower_left_fountain_pen' =>
            array(
                ':lower_left_fountain_pen:',
            ),
            'evergreen_tree' =>
            array(
                ':evergreen_tree:',
            ),
            'crying_cat_face' =>
            array(
                ':crying_cat_face:',
            ),
            'flag-gw' =>
            array(
                ':flag-gw:',
            ),
            'lower_left_ballpoint_pen' =>
            array(
                ':lower_left_ballpoint_pen:',
            ),
            'pouting_cat' =>
            array(
                ':pouting_cat:',
            ),
            'deciduous_tree' =>
            array(
                ':deciduous_tree:',
            ),
            'octagonal_sign' =>
            array(
                ':octagonal_sign:',
            ),
            'beginner' =>
            array(
                ':beginner:',
            ),
            'flag-gy' =>
            array(
                ':flag-gy:',
            ),
            'lower_left_paintbrush' =>
            array(
                ':lower_left_paintbrush:',
            ),
            'o' =>
            array(
                ':o:',
            ),
            'palm_tree' =>
            array(
                ':palm_tree:',
            ),
            'anchor' =>
            array(
                ':anchor:',
            ),
            'see_no_evil' =>
            array(
                ':see_no_evil:',
            ),
            'boat' =>
            array(
                ':boat:',
            ),
            'white_check_mark' =>
            array(
                ':white_check_mark:',
            ),
            'flag-hk' =>
            array(
                ':flag-hk:',
            ),
            'lower_left_crayon' =>
            array(
                ':lower_left_crayon:',
            ),
            'hear_no_evil' =>
            array(
                ':hear_no_evil:',
            ),
            'cactus' =>
            array(
                ':cactus:',
            ),
            'ear_of_rice' =>
            array(
                ':ear_of_rice:',
            ),
            'speak_no_evil' =>
            array(
                ':speak_no_evil:',
            ),
            'flag-hm' =>
            array(
                ':flag-hm:',
            ),
            'ballot_box_with_check' =>
            array(
                ':ballot_box_with_check:',
            ),
            'canoe' =>
            array(
                ':canoe:',
            ),
            'memo' =>
            array(
                ':memo:',
            ),
            'herb' =>
            array(
                ':herb:',
            ),
            'flag-hn' =>
            array(
                ':flag-hn:',
            ),
            'heavy_check_mark' =>
            array(
                ':heavy_check_mark:',
            ),
            'briefcase' =>
            array(
                ':briefcase:',
            ),
            'speedboat' =>
            array(
                ':speedboat:',
            ),
            'baby' =>
            array(
                ':baby:',
            ),
            'heavy_multiplication_x' =>
            array(
                ':heavy_multiplication_x:',
            ),
            'child' =>
            array(
                ':child:',
            ),
            'shamrock' =>
            array(
                ':shamrock:',
            ),
            'passenger_ship' =>
            array(
                ':passenger_ship:',
            ),
            'flag-hr' =>
            array(
                ':flag-hr:',
            ),
            'file_folder' =>
            array(
                ':file_folder:',
            ),
            'x' =>
            array(
                ':x:',
            ),
            'four_leaf_clover' =>
            array(
                ':four_leaf_clover:',
            ),
            'open_file_folder' =>
            array(
                ':open_file_folder:',
            ),
            'boy' =>
            array(
                ':boy:',
            ),
            'ferry' =>
            array(
                ':ferry:',
            ),
            'flag-ht' =>
            array(
                ':flag-ht:',
            ),
            'girl' =>
            array(
                ':girl:',
            ),
            'negative_squared_cross_mark' =>
            array(
                ':negative_squared_cross_mark:',
            ),
            'flag-hu' =>
            array(
                ':flag-hu:',
            ),
            'card_index_dividers' =>
            array(
                ':card_index_dividers:',
            ),
            'maple_leaf' =>
            array(
                ':maple_leaf:',
            ),
            'motor_boat' =>
            array(
                ':motor_boat:',
            ),
            'flag-ic' =>
            array(
                ':flag-ic:',
            ),
            'fallen_leaf' =>
            array(
                ':fallen_leaf:',
            ),
            'adult' =>
            array(
                ':adult:',
            ),
            'ship' =>
            array(
                ':ship:',
            ),
            'heavy_plus_sign' =>
            array(
                ':heavy_plus_sign:',
            ),
            'date' =>
            array(
                ':date:',
            ),
            'man' =>
            array(
                ':man:',
            ),
            'flag-id' =>
            array(
                ':flag-id:',
            ),
            'leaves' =>
            array(
                ':leaves:',
            ),
            'heavy_minus_sign' =>
            array(
                ':heavy_minus_sign:',
            ),
            'calendar' =>
            array(
                ':calendar:',
            ),
            'airplane' =>
            array(
                ':airplane:',
            ),
            'spiral_note_pad' =>
            array(
                ':spiral_note_pad:',
            ),
            'heavy_division_sign' =>
            array(
                ':heavy_division_sign:',
            ),
            'small_airplane' =>
            array(
                ':small_airplane:',
            ),
            'woman' =>
            array(
                ':woman:',
            ),
            'flag-ie' =>
            array(
                ':flag-ie:',
            ),
            'curly_loop' =>
            array(
                ':curly_loop:',
            ),
            'flag-il' =>
            array(
                ':flag-il:',
            ),
            'airplane_departure' =>
            array(
                ':airplane_departure:',
            ),
            'spiral_calendar_pad' =>
            array(
                ':spiral_calendar_pad:',
            ),
            'older_adult' =>
            array(
                ':older_adult:',
            ),
            'airplane_arriving' =>
            array(
                ':airplane_arriving:',
            ),
            'card_index' =>
            array(
                ':card_index:',
            ),
            'loop' =>
            array(
                ':loop:',
            ),
            'older_man' =>
            array(
                ':older_man:',
            ),
            'flag-im' =>
            array(
                ':flag-im:',
            ),
            'flag-in' =>
            array(
                ':flag-in:',
            ),
            'chart_with_upwards_trend' =>
            array(
                ':chart_with_upwards_trend:',
            ),
            'part_alternation_mark' =>
            array(
                ':part_alternation_mark:',
            ),
            'seat' =>
            array(
                ':seat:',
            ),
            'older_woman' =>
            array(
                ':older_woman:',
            ),
            'eight_spoked_asterisk' =>
            array(
                ':eight_spoked_asterisk:',
            ),
            'chart_with_downwards_trend' =>
            array(
                ':chart_with_downwards_trend:',
            ),
            'flag-io' =>
            array(
                ':flag-io:',
            ),
            'male-doctor' =>
            array(
                ':male-doctor:',
            ),
            'helicopter' =>
            array(
                ':helicopter:',
            ),
            'female-doctor' =>
            array(
                ':female-doctor:',
            ),
            'suspension_railway' =>
            array(
                ':suspension_railway:',
            ),
            'bar_chart' =>
            array(
                ':bar_chart:',
            ),
            'flag-iq' =>
            array(
                ':flag-iq:',
            ),
            'eight_pointed_black_star' =>
            array(
                ':eight_pointed_black_star:',
            ),
            'mountain_cableway' =>
            array(
                ':mountain_cableway:',
            ),
            'male-student' =>
            array(
                ':male-student:',
            ),
            'clipboard' =>
            array(
                ':clipboard:',
            ),
            'flag-ir' =>
            array(
                ':flag-ir:',
            ),
            'sparkle' =>
            array(
                ':sparkle:',
            ),
            'female-student' =>
            array(
                ':female-student:',
            ),
            'pushpin' =>
            array(
                ':pushpin:',
            ),
            'aerial_tramway' =>
            array(
                ':aerial_tramway:',
            ),
            'flag-is' =>
            array(
                ':flag-is:',
            ),
            'bangbang' =>
            array(
                ':bangbang:',
            ),
            'interrobang' =>
            array(
                ':interrobang:',
            ),
            'satellite' =>
            array(
                ':satellite:',
            ),
            'it' =>
            array(
                ':it:',
            ),
            'male-teacher' =>
            array(
                ':male-teacher:',
            ),
            'round_pushpin' =>
            array(
                ':round_pushpin:',
            ),
            'flag-je' =>
            array(
                ':flag-je:',
            ),
            'question' =>
            array(
                ':question:',
            ),
            'rocket' =>
            array(
                ':rocket:',
            ),
            'female-teacher' =>
            array(
                ':female-teacher:',
            ),
            'paperclip' =>
            array(
                ':paperclip:',
            ),
            'linked_paperclips' =>
            array(
                ':linked_paperclips:',
            ),
            'flying_saucer' =>
            array(
                ':flying_saucer:',
            ),
            'male-judge' =>
            array(
                ':male-judge:',
            ),
            'grey_question' =>
            array(
                ':grey_question:',
            ),
            'flag-jm' =>
            array(
                ':flag-jm:',
            ),
            'bellhop_bell' =>
            array(
                ':bellhop_bell:',
            ),
            'straight_ruler' =>
            array(
                ':straight_ruler:',
            ),
            'flag-jo' =>
            array(
                ':flag-jo:',
            ),
            'female-judge' =>
            array(
                ':female-judge:',
            ),
            'grey_exclamation' =>
            array(
                ':grey_exclamation:',
            ),
            'door' =>
            array(
                ':door:',
            ),
            'male-farmer' =>
            array(
                ':male-farmer:',
            ),
            'jp' =>
            array(
                ':jp:',
            ),
            'triangular_ruler' =>
            array(
                ':triangular_ruler:',
            ),
            'exclamation' =>
            array(
                ':exclamation:',
            ),
            'bed' =>
            array(
                ':bed:',
            ),
            'female-farmer' =>
            array(
                ':female-farmer:',
            ),
            'scissors' =>
            array(
                ':scissors:',
            ),
            'wavy_dash' =>
            array(
                ':wavy_dash:',
            ),
            'flag-ke' =>
            array(
                ':flag-ke:',
            ),
            'flag-kg' =>
            array(
                ':flag-kg:',
            ),
            'couch_and_lamp' =>
            array(
                ':couch_and_lamp:',
            ),
            'male-cook' =>
            array(
                ':male-cook:',
            ),
            'card_file_box' =>
            array(
                ':card_file_box:',
            ),
            'file_cabinet' =>
            array(
                ':file_cabinet:',
            ),
            'flag-kh' =>
            array(
                ':flag-kh:',
            ),
            'female-cook' =>
            array(
                ':female-cook:',
            ),
            'toilet' =>
            array(
                ':toilet:',
            ),
            'wastebasket' =>
            array(
                ':wastebasket:',
            ),
            'flag-ki' =>
            array(
                ':flag-ki:',
            ),
            'shower' =>
            array(
                ':shower:',
            ),
            'male-mechanic' =>
            array(
                ':male-mechanic:',
            ),
            'tm' =>
            array(
                ':tm:',
            ),
            'hash' =>
            array(
                ':hash:',
            ),
            'flag-km' =>
            array(
                ':flag-km:',
            ),
            'bathtub' =>
            array(
                ':bathtub:',
            ),
            'female-mechanic' =>
            array(
                ':female-mechanic:',
            ),
            'lock' =>
            array(
                ':lock:',
            ),
            'male-factory-worker' =>
            array(
                ':male-factory-worker:',
            ),
            'flag-kn' =>
            array(
                ':flag-kn:',
            ),
            'hourglass' =>
            array(
                ':hourglass:',
            ),
            'keycap_star' =>
            array(
                ':keycap_star:',
            ),
            'unlock' =>
            array(
                ':unlock:',
            ),
            'flag-kp' =>
            array(
                ':flag-kp:',
            ),
            'female-factory-worker' =>
            array(
                ':female-factory-worker:',
            ),
            'zero' =>
            array(
                ':zero:',
            ),
            'lock_with_ink_pen' =>
            array(
                ':lock_with_ink_pen:',
            ),
            'hourglass_flowing_sand' =>
            array(
                ':hourglass_flowing_sand:',
            ),
            'one' =>
            array(
                ':one:',
            ),
            'kr' =>
            array(
                ':kr:',
            ),
            'watch' =>
            array(
                ':watch:',
            ),
            'male-office-worker' =>
            array(
                ':male-office-worker:',
            ),
            'closed_lock_with_key' =>
            array(
                ':closed_lock_with_key:',
            ),
            'female-office-worker' =>
            array(
                ':female-office-worker:',
            ),
            'two' =>
            array(
                ':two:',
            ),
            'alarm_clock' =>
            array(
                ':alarm_clock:',
            ),
            'key' =>
            array(
                ':key:',
            ),
            'flag-kw' =>
            array(
                ':flag-kw:',
            ),
            'stopwatch' =>
            array(
                ':stopwatch:',
            ),
            'male-scientist' =>
            array(
                ':male-scientist:',
            ),
            'three' =>
            array(
                ':three:',
            ),
            'flag-ky' =>
            array(
                ':flag-ky:',
            ),
            'old_key' =>
            array(
                ':old_key:',
            ),
            'flag-kz' =>
            array(
                ':flag-kz:',
            ),
            'hammer' =>
            array(
                ':hammer:',
            ),
            'female-scientist' =>
            array(
                ':female-scientist:',
            ),
            'timer_clock' =>
            array(
                ':timer_clock:',
            ),
            'four' =>
            array(
                ':four:',
            ),
            'male-technologist' =>
            array(
                ':male-technologist:',
            ),
            'mantelpiece_clock' =>
            array(
                ':mantelpiece_clock:',
            ),
            'five' =>
            array(
                ':five:',
            ),
            'flag-la' =>
            array(
                ':flag-la:',
            ),
            'pick' =>
            array(
                ':pick:',
            ),
            'flag-lb' =>
            array(
                ':flag-lb:',
            ),
            'clock12' =>
            array(
                ':clock12:',
            ),
            'hammer_and_pick' =>
            array(
                ':hammer_and_pick:',
            ),
            'six' =>
            array(
                ':six:',
            ),
            'female-technologist' =>
            array(
                ':female-technologist:',
            ),
            'hammer_and_wrench' =>
            array(
                ':hammer_and_wrench:',
            ),
            'flag-lc' =>
            array(
                ':flag-lc:',
            ),
            'clock1230' =>
            array(
                ':clock1230:',
            ),
            'seven' =>
            array(
                ':seven:',
            ),
            'male-singer' =>
            array(
                ':male-singer:',
            ),
            'eight' =>
            array(
                ':eight:',
            ),
            'flag-li' =>
            array(
                ':flag-li:',
            ),
            'dagger_knife' =>
            array(
                ':dagger_knife:',
            ),
            'clock1' =>
            array(
                ':clock1:',
            ),
            'female-singer' =>
            array(
                ':female-singer:',
            ),
            'male-artist' =>
            array(
                ':male-artist:',
            ),
            'crossed_swords' =>
            array(
                ':crossed_swords:',
            ),
            'nine' =>
            array(
                ':nine:',
            ),
            'flag-lk' =>
            array(
                ':flag-lk:',
            ),
            'clock130' =>
            array(
                ':clock130:',
            ),
            'clock2' =>
            array(

                ':clock2:',
            ),
            'gun' =>
            array(
                ':gun:',
            ),
            'keycap_ten' =>
            array(
                ':keycap_ten:',
            ),
            'female-artist' =>
            array(
                ':female-artist:',
            ),
            'flag-lr' =>
            array(
                ':flag-lr:',
            ),
            'clock230' =>
            array(
                ':clock230:',
            ),
            'bow_and_arrow' =>
            array(
                ':bow_and_arrow:',
            ),
            'male-pilot' =>
            array(
                ':male-pilot:',
            ),
            'flag-ls' =>
            array(
                ':flag-ls:',
            ),
            'flag-lt' =>
            array(
                ':flag-lt:',
            ),
            'capital_abcd' =>
            array(
                ':capital_abcd:',
            ),
            'female-pilot' =>
            array(
                ':female-pilot:',
            ),
            'clock3' =>
            array(
                ':clock3:',
            ),
            'shield' =>
            array(
                ':shield:',
            ),
            'male-astronaut' =>
            array(
                ':male-astronaut:',
            ),
            'abcd' =>
            array(
                ':abcd:',
            ),
            'clock330' =>
            array(
                ':clock330:',
            ),
            'flag-lu' =>
            array(
                ':flag-lu:',
            ),
            'wrench' =>
            array(
                ':wrench:',
            ),
            'nut_and_bolt' =>
            array(
                ':nut_and_bolt:',
            ),
            'clock4' =>
            array(
                ':clock4:',
            ),
            'female-astronaut' =>
            array(
                ':female-astronaut:',
            ),
            'flag-lv' =>
            array(
                ':flag-lv:',
            ),
            'gear' =>
            array(
                ':gear:',
            ),
            'male-firefighter' =>
            array(
                ':male-firefighter:',
            ),
            'flag-ly' =>
            array(
                ':flag-ly:',
            ),
            'symbols' =>
            array(
                ':symbols:',
            ),
            'clock430' =>
            array(
                ':clock430:',
            ),
            'flag-ma' =>
            array(
                ':flag-ma:',
            ),
            'compression' =>
            array(
                ':compression:',
            ),
            'female-firefighter' =>
            array(
                ':female-firefighter:',
            ),
            'abc' =>
            array(
                ':abc:',
            ),
            'clock5' =>
            array(
                ':clock5:',
            ),
            'clock530' =>
            array(
                ':clock530:',
            ),
            'a' =>
            array(
                ':a:',
            ),
            'alembic' =>
            array(
                ':alembic:',
            ),
            'flag-mc' =>
            array(
                ':flag-mc:',
            ),
            'cop' =>
            array(
                ':cop:',
            ),
            'scales' =>
            array(
                ':scales:',
            ),
            'clock6' =>
            array(
                ':clock6:',
            ),
            'flag-md' =>
            array(
                ':flag-md:',
            ),
            'ab' =>
            array(
                ':ab:',
            ),
            'male-police-officer' =>
            array(
                ':male-police-officer:',
            ),
            'link' =>
            array(
                ':link:',
            ),
            'flag-me' =>
            array(
                ':flag-me:',
            ),
            'clock630' =>
            array(
                ':clock630:',
            ),
            'b' =>
            array(
                ':b:',
            ),
            'female-police-officer' =>
            array(
                ':female-police-officer:',
            ),
            'clock7' =>
            array(
                ':clock7:',
            ),
            'cl' =>
            array(
                ':cl:',
            ),
            'sleuth_or_spy' =>
            array(
                ':sleuth_or_spy:',
            ),
            'flag-mf' =>
            array(
                ':flag-mf:',
            ),
            'chains' =>
            array(
                ':chains:',
            ),
            'syringe' =>
            array(
                ':syringe:',
            ),
            'male-detective' =>
            array(
                ':male-detective:',
            ),
            'cool' =>
            array(
                ':cool:',
            ),
            'clock730' =>
            array(
                ':clock730:',
            ),
            'flag-mg' =>
            array(
                ':flag-mg:',
            ),
            'free' =>
            array(
                ':free:',
            ),
            'flag-mh' =>
            array(
                ':flag-mh:',
            ),
            'clock8' =>
            array(
                ':clock8:',
            ),
            'pill' =>
            array(
                ':pill:',
            ),
            'female-detective' =>
            array(
                ':female-detective:',
            ),
            'clock830' =>
            array(
                ':clock830:',
            ),
            'guardsman' =>
            array(
                ':guardsman:',
            ),
            'information_source' =>
            array(
                ':information_source:',
            ),
            'flag-mk' =>
            array(
                ':flag-mk:',
            ),
            'smoking' =>
            array(
                ':smoking:',
            ),
            'id' =>
            array(
                ':id:',
            ),
            'clock9' =>
            array(
                ':clock9:',
            ),
            'flag-ml' =>
            array(
                ':flag-ml:',
            ),
            'coffin' =>
            array(
                ':coffin:',
            ),
            'male-guard' =>
            array(
                ':male-guard:',
            ),
            'm' =>
            array(
                ':m:',
            ),
            'funeral_urn' =>
            array(
                ':funeral_urn:',
            ),
            'female-guard' =>
            array(
                ':female-guard:',
            ),
            'flag-mm' =>
            array(
                ':flag-mm:',
            ),
            'clock930' =>
            array(
                ':clock930:',
            ),
            'moyai' =>
            array(
                ':moyai:',
            ),
            'new' =>
            array(
                ':new:',
            ),
            'flag-mn' =>
            array(
                ':flag-mn:',
            ),
            'construction_worker' =>
            array(
                ':construction_worker:',
            ),
            'clock10' =>
            array(
                ':clock10:',
            ),
            'clock1030' =>
            array(
                ':clock1030:',
            ),
            'ng' =>
            array(
                ':ng:',
            ),
            'male-construction-worker' =>
            array(
                ':male-construction-worker:',
            ),
            'flag-mo' =>
            array(
                ':flag-mo:',
            ),
            'oil_drum' =>
            array(
                ':oil_drum:',
            ),
            'o2' =>
            array(
                ':o2:',
            ),
            'female-construction-worker' =>
            array(
                ':female-construction-worker:',
            ),
            'clock11' =>
            array(
                ':clock11:',
            ),
            'crystal_ball' =>
            array(
                ':crystal_ball:',
            ),
            'flag-mp' =>
            array(
                ':flag-mp:',
            ),
            'flag-mq' =>
            array(
                ':flag-mq:',
            ),
            'prince' =>
            array(
                ':prince:',
            ),
            'ok' =>
            array(
                ':ok:',
            ),
            'clock1130' =>
            array(
                ':clock1130:',
            ),
            'shopping_trolley' =>
            array(
                ':shopping_trolley:',
            ),
            'flag-mr' =>
            array(
                ':flag-mr:',
            ),
            'princess' =>
            array(
                ':princess:',
            ),
            'new_moon' =>
            array(
                ':new_moon:',
            ),
            'parking' =>
            array(
                ':parking:',
            ),
            'sos' =>
            array(
                ':sos:',
            ),
            'man_with_turban' =>
            array(
                ':man_with_turban:',
            ),
            'flag-ms' =>
            array(
                ':flag-ms:',
            ),
            'waxing_crescent_moon' =>
            array(
                ':waxing_crescent_moon:',
            ),
            'up' =>
            array(
                ':up:',
            ),
            'first_quarter_moon' =>
            array(
                ':first_quarter_moon:',
            ),
            'flag-mt' =>
            array(
                ':flag-mt:',
            ),
            'man-wearing-turban' =>
            array(
                ':man-wearing-turban:',
            ),
            'moon' =>
            array(
                ':moon:',
            ),
            'woman-wearing-turban' =>
            array(
                ':woman-wearing-turban:',
            ),
            'vs' =>
            array(
                ':vs:',
            ),
            'flag-mu' =>
            array(
                ':flag-mu:',
            ),
            'man_with_gua_pi_mao' =>
            array(
                ':man_with_gua_pi_mao:',
            ),
            'koko' =>
            array(
                ':koko:',
            ),
            'full_moon' =>
            array(
                ':full_moon:',
            ),
            'flag-mv' =>
            array(
                ':flag-mv:',
            ),
            'person_with_headscarf' =>
            array(
                ':person_with_headscarf:',
            ),
            'waning_gibbous_moon' =>
            array(
                ':waning_gibbous_moon:',
            ),
            'sa' =>
            array(
                ':sa:',
            ),
            'flag-mw' =>
            array(
                ':flag-mw:',
            ),
            'last_quarter_moon' =>
            array(
                ':last_quarter_moon:',
            ),
            'u6708' =>
            array(
                ':u6708:',
            ),
            'bearded_person' =>
            array(
                ':bearded_person:',
            ),
            'flag-mx' =>
            array(
                ':flag-mx:',
            ),
            'u6709' =>
            array(
                ':u6709:',
            ),
            'person_with_blond_hair' =>
            array(
                ':person_with_blond_hair:',
            ),
            'waning_crescent_moon' =>
            array(
                ':waning_crescent_moon:',
            ),
            'flag-my' =>
            array(
                ':flag-my:',
            ),
            'u6307' =>
            array(
                ':u6307:',
            ),
            'blond-haired-man' =>
            array(
                ':blond-haired-man:',
            ),
            'crescent_moon' =>
            array(
                ':crescent_moon:',
            ),
            'flag-mz' =>
            array(
                ':flag-mz:',
            ),
            'new_moon_with_face' =>
            array(
                ':new_moon_with_face:',
            ),
            'flag-na' =>
            array(
                ':flag-na:',
            ),
            'blond-haired-woman' =>
            array(
                ':blond-haired-woman:',
            ),
            'ideograph_advantage' =>
            array(
                ':ideograph_advantage:',
            ),
            'first_quarter_moon_with_face' =>
            array(
                ':first_quarter_moon_with_face:',
            ),
            'man_in_tuxedo' =>
            array(
                ':man_in_tuxedo:',
            ),
            'flag-nc' =>
            array(
                ':flag-nc:',
            ),
            'u5272' =>
            array(
                ':u5272:',
            ),
            'flag-ne' =>
            array(
                ':flag-ne:',
            ),
            'last_quarter_moon_with_face' =>
            array(
                ':last_quarter_moon_with_face:',
            ),
            'u7121' =>
            array(
                ':u7121:',
            ),
            'bride_with_veil' =>
            array(
                ':bride_with_veil:',
            ),
            'u7981' =>
            array(
                ':u7981:',
            ),
            'pregnant_woman' =>
            array(
                ':pregnant_woman:',
            ),
            'thermometer' =>
            array(
                ':thermometer:',
            ),
            'flag-nf' =>
            array(
                ':flag-nf:',
            ),
            'sunny' =>
            array(
                ':sunny:',
            ),
            'accept' =>
            array(
                ':accept:',
            ),
            'flag-ng' =>
            array(
                ':flag-ng:',
            ),
            'breast-feeding' =>
            array(
                ':breast-feeding:',
            ),
            'full_moon_with_face' =>
            array(
                ':full_moon_with_face:',
            ),
            'flag-ni' =>
            array(
                ':flag-ni:',
            ),
            'u7533' =>
            array(
                ':u7533:',
            ),
            'angel' =>
            array(
                ':angel:',
            ),
            'sun_with_face' =>
            array(
                ':sun_with_face:',
            ),
            'santa' =>
            array(
                ':santa:',
            ),
            'u5408' =>
            array(
                ':u5408:',
            ),
            'flag-nl' =>
            array(
                ':flag-nl:',
            ),
            'mrs_claus' =>
            array(
                ':mrs_claus:',
            ),
            'u7a7a' =>
            array(
                ':u7a7a:',
            ),
            'star' =>
            array(
                ':star:',
            ),
            'flag-no' =>
            array(
                ':flag-no:',
            ),
            'mage' =>
            array(
                ':mage:',
            ),
            'star2' =>
            array(
                ':star2:',
            ),
            'flag-np' =>
            array(
                ':flag-np:',
            ),
            'congratulations' =>
            array(
                ':congratulations:',
            ),
            'flag-nr' =>
            array(
                ':flag-nr:',
            ),
            'stars' =>
            array(
                ':stars:',
            ),
            'female_mage' =>
            array(
                ':female_mage:',
            ),
            'secret' =>
            array(
                ':secret:',
            ),
            'flag-nu' =>
            array(
                ':flag-nu:',
            ),
            'u55b6' =>
            array(
                ':u55b6:',
            ),
            'male_mage' =>
            array(
                ':male_mage:',
            ),
            'cloud' =>
            array(
                ':cloud:',
            ),
            'flag-nz' =>
            array(
                ':flag-nz:',
            ),
            'partly_sunny' =>
            array(
                ':partly_sunny:',
            ),
            'fairy' =>
            array(
                ':fairy:',
            ),
            'u6e80' =>
            array(
                ':u6e80:',
            ),
            'black_small_square' =>
            array(
                ':black_small_square:',
            ),
            'thunder_cloud_and_rain' =>
            array(
                ':thunder_cloud_and_rain:',
            ),
            'female_fairy' =>
            array(
                ':female_fairy:',
            ),
            'flag-om' =>
            array(
                ':flag-om:',
            ),
            'white_small_square' =>
            array(
                ':white_small_square:',
            ),
            'flag-pa' =>
            array(
                ':flag-pa:',
            ),
            'mostly_sunny' =>
            array(
                ':mostly_sunny:',
            ),
            'male_fairy' =>
            array(
                ':male_fairy:',
            ),
            'barely_sunny' =>
            array(
                ':barely_sunny:',
            ),
            'white_medium_square' =>
            array(
                ':white_medium_square:',
            ),
            'flag-pe' =>
            array(
                ':flag-pe:',
            ),
            'vampire' =>
            array(
                ':vampire:',
            ),
            'female_vampire' =>
            array(
                ':female_vampire:',
            ),
            'partly_sunny_rain' =>
            array(
                ':partly_sunny_rain:',
            ),
            'flag-pf' =>
            array(
                ':flag-pf:',
            ),
            'black_medium_square' =>
            array(
                ':black_medium_square:',
            ),
            'white_medium_small_square' =>
            array(
                ':white_medium_small_square:',
            ),
            'rain_cloud' =>
            array(
                ':rain_cloud:',
            ),
            'flag-pg' =>
            array(
                ':flag-pg:',
            ),
            'male_vampire' =>
            array(
                ':male_vampire:',
            ),
            'flag-ph' =>
            array(
                ':flag-ph:',
            ),
            'merperson' =>
            array(
                ':merperson:',
            ),
            'black_medium_small_square' =>
            array(
                ':black_medium_small_square:',
            ),
            'snow_cloud' =>
            array(
                ':snow_cloud:',
            ),
            'lightning' =>
            array(
                ':lightning:',
            ),
            'black_large_square' =>
            array(
                ':black_large_square:',
            ),
            'mermaid' =>
            array(
                ':mermaid:',
            ),
            'flag-pk' =>
            array(
                ':flag-pk:',
            ),
            'merman' =>
            array(
                ':merman:',
            ),
            'white_large_square' =>
            array(
                ':white_large_square:',
            ),
            'tornado' =>
            array(
                ':tornado:',
            ),
            'flag-pl' =>
            array(
                ':flag-pl:',
            ),
            'elf' =>
            array(
                ':elf:',
            ),
            'fog' =>
            array(
                ':fog:',
            ),
            'large_orange_diamond' =>
            array(
                ':large_orange_diamond:',
            ),
            'flag-pm' =>
            array(
                ':flag-pm:',
            ),
            'flag-pn' =>
            array(
                ':flag-pn:',
            ),
            'wind_blowing_face' =>
            array(
                ':wind_blowing_face:',
            ),
            'female_elf' =>
            array(
                ':female_elf:',
            ),
            'large_blue_diamond' =>
            array(
                ':large_blue_diamond:',
            ),
            'male_elf' =>
            array(
                ':male_elf:',
            ),
            'small_orange_diamond' =>
            array(
                ':small_orange_diamond:',
            ),
            'flag-pr' =>
            array(
                ':flag-pr:',
            ),
            'cyclone' =>
            array(
                ':cyclone:',
            ),
            'rainbow' =>
            array(
                ':rainbow:',
            ),
            'small_blue_diamond' =>
            array(
                ':small_blue_diamond:',
            ),
            'genie' =>
            array(
                ':genie:',
            ),
            'flag-ps' =>
            array(
                ':flag-ps:',
            ),
            'small_red_triangle' =>
            array(
                ':small_red_triangle:',
            ),
            'closed_umbrella' =>
            array(
                ':closed_umbrella:',
            ),
            'female_genie' =>
            array(
                ':female_genie:',
            ),
            'flag-pt' =>
            array(
                ':flag-pt:',
            ),
            'flag-pw' =>
            array(
                ':flag-pw:',
            ),
            'small_red_triangle_down' =>
            array(
                ':small_red_triangle_down:',
            ),
            'umbrella' =>
            array(
                ':umbrella:',
            ),
            'male_genie' =>
            array(
                ':male_genie:',
            ),
            'zombie' =>
            array(
                ':zombie:',
            ),
            'flag-py' =>
            array(
                ':flag-py:',
            ),
            'diamond_shape_with_a_dot_inside' =>
            array(
                ':diamond_shape_with_a_dot_inside:',
            ),
            'umbrella_with_rain_drops' =>
            array(
                ':umbrella_with_rain_drops:',
            ),
            'radio_button' =>
            array(
                ':radio_button:',
            ),
            'female_zombie' =>
            array(
                ':female_zombie:',
            ),
            'flag-qa' =>
            array(
                ':flag-qa:',
            ),
            'umbrella_on_ground' =>
            array(
                ':umbrella_on_ground:',
            ),
            'black_square_button' =>
            array(
                ':black_square_button:',
            ),
            'zap' =>
            array(
                ':zap:',
            ),
            'male_zombie' =>
            array(
                ':male_zombie:',
            ),
            'flag-re' =>
            array(
                ':flag-re:',
            ),
            'flag-ro' =>
            array(
                ':flag-ro:',
            ),
            'snowflake' =>
            array(
                ':snowflake:',
            ),
            'white_square_button' =>
            array(
                ':white_square_button:',
            ),
            'person_frowning' =>
            array(
                ':person_frowning:',
            ),
            'flag-rs' =>
            array(
                ':flag-rs:',
            ),
            'man-frowning' =>
            array(
                ':man-frowning:',
            ),
            'white_circle' =>
            array(
                ':white_circle:',
            ),
            'snowman' =>
            array(
                ':snowman:',
            ),
            'snowman_without_snow' =>
            array(
                ':snowman_without_snow:',
            ),
            'ru' =>
            array(
                ':ru:',
            ),
            'black_circle' =>
            array(
                ':black_circle:',
            ),
            'woman-frowning' =>
            array(
                ':woman-frowning:',
            ),
            'flag-rw' =>
            array(
                ':flag-rw:',
            ),
            'comet' =>
            array(
                ':comet:',
            ),
            'person_with_pouting_face' =>
            array(
                ':person_with_pouting_face:',
            ),
            'red_circle' =>
            array(
                ':red_circle:',
            ),
            'large_blue_circle' =>
            array(
                ':large_blue_circle:',
            ),
            'man-pouting' =>
            array(
                ':man-pouting:',
            ),
            'flag-sa' =>
            array(
                ':flag-sa:',
            ),
            'fire' =>
            array(
                ':fire:',
            ),
            'woman-pouting' =>
            array(
                ':woman-pouting:',
            ),
            'flag-sb' =>
            array(
                ':flag-sb:',
            ),
            'droplet' =>
            array(
                ':droplet:',
            ),
            'no_good' =>
            array(
                ':no_good:',
            ),
            'flag-sc' =>
            array(
                ':flag-sc:',
            ),
            'ocean' =>
            array(
                ':ocean:',
            ),
            'man-gesturing-no' =>
            array(
                ':man-gesturing-no:',
            ),
            'flag-sd' =>
            array(
                ':flag-sd:',
            ),
            'woman-gesturing-no' =>
            array(
                ':woman-gesturing-no:',
            ),
            'flag-se' =>
            array(
                ':flag-se:',
            ),
            'flag-sg' =>
            array(
                ':flag-sg:',
            ),
            'ok_woman' =>
            array(
                ':ok_woman:',
            ),
            'flag-sh' =>
            array(
                ':flag-sh:',
            ),
            'man-gesturing-ok' =>
            array(
                ':man-gesturing-ok:',
            ),
            'flag-si' =>
            array(
                ':flag-si:',
            ),
            'woman-gesturing-ok' =>
            array(
                ':woman-gesturing-ok:',
            ),
            'information_desk_person' =>
            array(
                ':information_desk_person:',
            ),
            'flag-sj' =>
            array(
                ':flag-sj:',
            ),
            'man-tipping-hand' =>
            array(
                ':man-tipping-hand:',
            ),
            'flag-sk' =>
            array(
                ':flag-sk:',
            ),
            'flag-sl' =>
            array(
                ':flag-sl:',
            ),
            'woman-tipping-hand' =>
            array(
                ':woman-tipping-hand:',
            ),
            'flag-sm' =>
            array(
                ':flag-sm:',
            ),
            'raising_hand' =>
            array(
                ':raising_hand:',
            ),
            'flag-sn' =>
            array(
                ':flag-sn:',
            ),
            'man-raising-hand' =>
            array(
                ':man-raising-hand:',
            ),
            'flag-so' =>
            array(
                ':flag-so:',
            ),
            'woman-raising-hand' =>
            array(
                ':woman-raising-hand:',
            ),
            'flag-sr' =>
            array(
                ':flag-sr:',
            ),
            'bow' =>
            array(
                ':bow:',
            ),
            'man-bowing' =>
            array(
                ':man-bowing:',
            ),
            'flag-ss' =>
            array(
                ':flag-ss:',
            ),
            'woman-bowing' =>
            array(
                ':woman-bowing:',
            ),
            'flag-st' =>
            array(
                ':flag-st:',
            ),
            'face_palm' =>
            array(
                ':face_palm:',
            ),
            'flag-sv' =>
            array(
                ':flag-sv:',
            ),
            'man-facepalming' =>
            array(
                ':man-facepalming:',
            ),
            'flag-sx' =>
            array(
                ':flag-sx:',
            ),
            'flag-sy' =>
            array(
                ':flag-sy:',
            ),
            'woman-facepalming' =>
            array(
                ':woman-facepalming:',
            ),
            'shrug' =>
            array(
                ':shrug:',
            ),
            'flag-sz' =>
            array(
                ':flag-sz:',
            ),
            'flag-ta' =>
            array(
                ':flag-ta:',
            ),
            'man-shrugging' =>
            array(
                ':man-shrugging:',
            ),
            'woman-shrugging' =>
            array(
                ':woman-shrugging:',
            ),
            'flag-tc' =>
            array(
                ':flag-tc:',
            ),
            'massage' =>
            array(
                ':massage:',
            ),
            'flag-td' =>
            array(
                ':flag-td:',
            ),
            'man-getting-massage' =>
            array(
                ':man-getting-massage:',
            ),
            'flag-tf' =>
            array(
                ':flag-tf:',
            ),
            'woman-getting-massage' =>
            array(
                ':woman-getting-massage:',
            ),
            'flag-tg' =>
            array(
                ':flag-tg:',
            ),
            'haircut' =>
            array(
                ':haircut:',
            ),
            'flag-th' =>
            array(
                ':flag-th:',
            ),
            'man-getting-haircut' =>
            array(
                ':man-getting-haircut:',
            ),
            'flag-tj' =>
            array(
                ':flag-tj:',
            ),
            'flag-tk' =>
            array(
                ':flag-tk:',
            ),
            'woman-getting-haircut' =>
            array(
                ':woman-getting-haircut:',
            ),
            'walking' =>
            array(
                ':walking:',
            ),
            'flag-tl' =>
            array(
                ':flag-tl:',
            ),
            'man-walking' =>
            array(
                ':man-walking:',
            ),
            'flag-tm' =>
            array(
                ':flag-tm:',
            ),
            'woman-walking' =>
            array(
                ':woman-walking:',
            ),
            'flag-tn' =>
            array(
                ':flag-tn:',
            ),
            'runner' =>
            array(
                ':runner:',
            ),
            'flag-to' =>
            array(
                ':flag-to:',
            ),
            'man-running' =>
            array(
                ':man-running:',
            ),
            'flag-tr' =>
            array(
                ':flag-tr:',
            ),
            'flag-tt' =>
            array(
                ':flag-tt:',
            ),
            'woman-running' =>
            array(
                ':woman-running:',
            ),
            'flag-tv' =>
            array(
                ':flag-tv:',
            ),
            'dancer' =>
            array(
                ':dancer:',
            ),
            'flag-tw' =>
            array(
                ':flag-tw:',
            ),
            'man_dancing' =>
            array(
                ':man_dancing:',
            ),
            'dancers' =>
            array(
                ':dancers:',
            ),
            'flag-tz' =>
            array(
                ':flag-tz:',
            ),
            'flag-ua' =>
            array(
                ':flag-ua:',
            ),
            'man-with-bunny-ears-partying' =>
            array(
                ':man-with-bunny-ears-partying:',
            ),
            'woman-with-bunny-ears-partying' =>
            array(
                ':woman-with-bunny-ears-partying:',
            ),
            'flag-ug' =>
            array(
                ':flag-ug:',
            ),
            'flag-um' =>
            array(
                ':flag-um:',
            ),
            'person_in_steamy_room' =>
            array(
                ':person_in_steamy_room:',
            ),
            'woman_in_steamy_room' =>
            array(
                ':woman_in_steamy_room:',
            ),
            'flag-un' =>
            array(
                ':flag-un:',
            ),
            'us' =>
            array(
                ':us:',
            ),
            'man_in_steamy_room' =>
            array(
                ':man_in_steamy_room:',
            ),
            'person_climbing' =>
            array(
                ':person_climbing:',
            ),
            'flag-uy' =>
            array(
                ':flag-uy:',
            ),
            'woman_climbing' =>
            array(
                ':woman_climbing:',
            ),
            'flag-uz' =>
            array(
                ':flag-uz:',
            ),
            'man_climbing' =>
            array(
                ':man_climbing:',
            ),
            'flag-va' =>
            array(
                ':flag-va:',
            ),
            'person_in_lotus_position' =>
            array(
                ':person_in_lotus_position:',
            ),
            'flag-vc' =>
            array(
                ':flag-vc:',
            ),
            'flag-ve' =>
            array(
                ':flag-ve:',
            ),
            'woman_in_lotus_position' =>
            array(
                ':woman_in_lotus_position:',
            ),
            'man_in_lotus_position' =>
            array(
                ':man_in_lotus_position:',
            ),
            'flag-vg' =>
            array(
                ':flag-vg:',
            ),
            'flag-vi' =>
            array(
                ':flag-vi:',
            ),
            'bath' =>
            array(
                ':bath:',
            ),
            'sleeping_accommodation' =>
            array(
                ':sleeping_accommodation:',
            ),
            'flag-vn' =>
            array(
                ':flag-vn:',
            ),
            'man_in_business_suit_levitating' =>
            array(
                ':man_in_business_suit_levitating:',
            ),
            'flag-vu' =>
            array(
                ':flag-vu:',
            ),
            'flag-wf' =>
            array(
                ':flag-wf:',
            ),
            'speaking_head_in_silhouette' =>
            array(
                ':speaking_head_in_silhouette:',
            ),
            'bust_in_silhouette' =>
            array(
                ':bust_in_silhouette:',
            ),
            'flag-ws' =>
            array(
                ':flag-ws:',
            ),
            'busts_in_silhouette' =>
            array(
                ':busts_in_silhouette:',
            ),
            'flag-xk' =>
            array(
                ':flag-xk:',
            ),
            'fencer' =>
            array(
                ':fencer:',
            ),
            'flag-ye' =>
            array(
                ':flag-ye:',
            ),
            'flag-yt' =>
            array(
                ':flag-yt:',
            ),
            'horse_racing' =>
            array(
                ':horse_racing:',
            ),
            'flag-za' =>
            array(
                ':flag-za:',
            ),
            'skier' =>
            array(
                ':skier:',
            ),
            'flag-zm' =>
            array(
                ':flag-zm:',
            ),
            'snowboarder' =>
            array(
                ':snowboarder:',
            ),
            'golfer' =>
            array(
                ':golfer:',
            ),
            'flag-zw' =>
            array(
                ':flag-zw:',
            ),
            'man-golfing' =>
            array(
                ':man-golfing:',
            ),
            'flag-england' =>
            array(
                ':flag-england:',
            ),
            'woman-golfing' =>
            array(
                ':woman-golfing:',
            ),
            'flag-scotland' =>
            array(
                ':flag-scotland:',
            ),
            'flag-wales' =>
            array(
                ':flag-wales:',
            ),
            'surfer' =>
            array(
                ':surfer:',
            ),
            'man-surfing' =>
            array(
                ':man-surfing:',
            ),
            'woman-surfing' =>
            array(
                ':woman-surfing:',
            ),
            'rowboat' =>
            array(
                ':rowboat:',
            ),
            'man-rowing-boat' =>
            array(
                ':man-rowing-boat:',
            ),
            'woman-rowing-boat' =>
            array(
                ':woman-rowing-boat:',
            ),
            'swimmer' =>
            array(
                ':swimmer:',
            ),
            'man-swimming' =>
            array(
                ':man-swimming:',
            ),
            'woman-swimming' =>
            array(
                ':woman-swimming:',
            ),
            'person_with_ball' =>
            array(
                ':person_with_ball:',
            ),
            'man-bouncing-ball' =>
            array(
                ':man-bouncing-ball:',
            ),
            'woman-bouncing-ball' =>
            array(
                ':woman-bouncing-ball:',
            ),
            'weight_lifter' =>
            array(
                ':weight_lifter:',
            ),
            'man-lifting-weights' =>
            array(
                ':man-lifting-weights:',
            ),
            'woman-lifting-weights' =>
            array(
                ':woman-lifting-weights:',
            ),
            'bicyclist' =>
            array(
                ':bicyclist:',
            ),
            'man-biking' =>
            array(
                ':man-biking:',
            ),
            'woman-biking' =>
            array(
                ':woman-biking:',
            ),
            'mountain_bicyclist' =>
            array(
                ':mountain_bicyclist:',
            ),
            'man-mountain-biking' =>
            array(
                ':man-mountain-biking:',
            ),
            'woman-mountain-biking' =>
            array(
                ':woman-mountain-biking:',
            ),
            'racing_car' =>
            array(
                ':racing_car:',
            ),
            'racing_motorcycle' =>
            array(
                ':racing_motorcycle:',
            ),
            'person_doing_cartwheel' =>
            array(
                ':person_doing_cartwheel:',
            ),
            'man-cartwheeling' =>
            array(
                ':man-cartwheeling:',
            ),
            'woman-cartwheeling' =>
            array(
                ':woman-cartwheeling:',
            ),
            'wrestlers' =>
            array(
                ':wrestlers:',
            ),
            'man-wrestling' =>
            array(
                ':man-wrestling:',
            ),
            'woman-wrestling' =>
            array(
                ':woman-wrestling:',
            ),
            'water_polo' =>
            array(
                ':water_polo:',
            ),
            'man-playing-water-polo' =>
            array(
                ':man-playing-water-polo:',
            ),
            'woman-playing-water-polo' =>
            array(
                ':woman-playing-water-polo:',
            ),
            'handball' =>
            array(
                ':handball:',
            ),
            'man-playing-handball' =>
            array(
                ':man-playing-handball:',
            ),
            'woman-playing-handball' =>
            array(
                ':woman-playing-handball:',
            ),
            'juggling' =>
            array(
                ':juggling:',
            ),
            'man-juggling' =>
            array(
                ':man-juggling:',
            ),
            'woman-juggling' =>
            array(
                ':woman-juggling:',
            ),
            'couple' =>
            array(
                ':couple:',
            ),
            'two_men_holding_hands' =>
            array(
                ':two_men_holding_hands:',
            ),
            'two_women_holding_hands' =>
            array(
                ':two_women_holding_hands:',
            ),
            'couplekiss' =>
            array(
                ':couplekiss:',
            ),
            'woman-kiss-man' =>
            array(
                ':woman-kiss-man:',
            ),
            'man-kiss-man' =>
            array(
                ':man-kiss-man:',
            ),
            'woman-kiss-woman' =>
            array(
                ':woman-kiss-woman:',
            ),
            'couple_with_heart' =>
            array(
                ':couple_with_heart:',
            ),
            'woman-heart-man' =>
            array(
                ':woman-heart-man:',
            ),
            'man-heart-man' =>
            array(
                ':man-heart-man:',
            ),
            'woman-heart-woman' =>
            array(
                ':woman-heart-woman:',
            ),
            'family' =>
            array(
                ':family:',
            ),
            'man-woman-boy' =>
            array(
                ':man-woman-boy:',
            ),
            'man-woman-girl' =>
            array(
                ':man-woman-girl:',
            ),
            'man-woman-girl-boy' =>
            array(
                ':man-woman-girl-boy:',
            ),
            'man-woman-boy-boy' =>
            array(
                ':man-woman-boy-boy:',
            ),
            'man-woman-girl-girl' =>
            array(
                ':man-woman-girl-girl:',
            ),
            'man-man-boy' =>
            array(
                ':man-man-boy:',
            ),
            'man-man-girl' =>
            array(
                ':man-man-girl:',
            ),
            'man-man-girl-boy' =>
            array(
                ':man-man-girl-boy:',
            ),
            'man-man-boy-boy' =>
            array(
                ':man-man-boy-boy:',
            ),
            'man-man-girl-girl' =>
            array(
                ':man-man-girl-girl:',
            ),
            'woman-woman-boy' =>
            array(
                ':woman-woman-boy:',
            ),
            'woman-woman-girl' =>
            array(
                ':woman-woman-girl:',
            ),
            'woman-woman-girl-boy' =>
            array(
                ':woman-woman-girl-boy:',
            ),
            'woman-woman-boy-boy' =>
            array(
                ':woman-woman-boy-boy:',
            ),
            'woman-woman-girl-girl' =>
            array(
                ':woman-woman-girl-girl:',
            ),
            'man-boy' =>
            array(
                ':man-boy:',
            ),
            'man-boy-boy' =>
            array(
                ':man-boy-boy:',
            ),
            'man-girl' =>
            array(
                ':man-girl:',
            ),
            'man-girl-boy' =>
            array(
                ':man-girl-boy:',
            ),
            'man-girl-girl' =>
            array(
                ':man-girl-girl:',
            ),
            'woman-boy' =>
            array(
                ':woman-boy:',
            ),
            'woman-boy-boy' =>
            array(
                ':woman-boy-boy:',
            ),
            'woman-girl' =>
            array(
                ':woman-girl:',
            ),
            'woman-girl-boy' =>
            array(
                ':woman-girl-boy:',
            ),
            'woman-girl-girl' =>
            array(
                ':woman-girl-girl:',
            ),
            'selfie' =>
            array(
                ':selfie:',
            ),
            'muscle' =>
            array(
                ':muscle:',
            ),
            'point_left' =>
            array(
                ':point_left:',
            ),
            'point_right' =>
            array(
                ':point_right:',
            ),
            'point_up' =>
            array(
                ':point_up:',
            ),
            'point_up_2' =>
            array(
                ':point_up_2:',
            ),
            'middle_finger' =>
            array(
                ':middle_finger:',
            ),
            'point_down' =>
            array(
                ':point_down:',
            ),
            'v' =>
            array(
                ':v:',
            ),
            'crossed_fingers' =>
            array(
                ':crossed_fingers:',
            ),
            'spock-hand' =>
            array(
                ':spock-hand:',
            ),
            'the_horns' =>
            array(
                ':the_horns:',
            ),
            'call_me_hand' =>
            array(
                ':call_me_hand:',
            ),
            'raised_hand_with_fingers_splayed' =>
            array(
                ':raised_hand_with_fingers_splayed:',
            ),
            'hand' =>
            array(
                ':hand:',
            ),
            'ok_hand' =>
            array(
                ':ok_hand:',
            ),
            'plus1' =>
            array(
                ':+1:',
            ),
            '-1' =>
            array(
                ':-1:',
            ),
            'fist' =>
            array(
                ':fist:',
            ),
            'facepunch' =>
            array(
                ':facepunch:',
            ),
            'left-facing_fist' =>
            array(
                ':left-facing_fist:',
            ),
            'right-facing_fist' =>
            array(
                ':right-facing_fist:',
            ),
            'raised_back_of_hand' =>
            array(
                ':raised_back_of_hand:',
            ),
            'wave' =>
            array(
                ':wave:',
            ),
            'i_love_you_hand_sign' =>
            array(
                ':i_love_you_hand_sign:',
            ),
            'writing_hand' =>
            array(
                ':writing_hand:',
            ),
            'clap' =>
            array(
                ':clap:',
            ),
            'open_hands' =>
            array(
                ':open_hands:',
            ),
            'raised_hands' =>
            array(
                ':raised_hands:',
            ),
            'palms_up_together' =>
            array(
                ':palms_up_together:',
            ),
            'pray' =>
            array(
                ':pray:',
            ),
            'handshake' =>
            array(
                ':handshake:',
            ),
            'nail_care' =>
            array(
                ':nail_care:',
            ),
            'ear' =>
            array(
                ':ear:',
            ),
            'nose' =>
            array(
                ':nose:',
            ),
            'footprints' =>
            array(
                ':footprints:',
            ),
            'eyes' =>
            array(
                ':eyes:',
            ),
            'eye' =>
            array(
                ':eye:',
            ),
            'brain' =>
            array(
                ':brain:',
            ),
            'lips' =>
            array(
                ':lips:',
            ),
            'cupid' =>
            array(
                ':cupid:',
            ),
            'heartbeat' =>
            array(
                ':heartbeat:',
            ),
            'broken_heart' =>
            array(
                ':broken_heart:',
            ),
            'two_hearts' =>
            array(
                ':two_hearts:',
            ),
            'sparkling_heart' =>
            array(
                ':sparkling_heart:',
            ),
            'heartpulse' =>
            array(
                ':heartpulse:',
            ),
            'blue_heart' =>
            array(
                ':blue_heart:',
            ),
            'green_heart' =>
            array(
                ':green_heart:',
            ),
            'yellow_heart' =>
            array(
                ':yellow_heart:',
            ),
            'orange_heart' =>
            array(
                ':orange_heart:',
            ),
            'purple_heart' =>
            array(
                ':purple_heart:',
            ),
            'black_heart' =>
            array(
                ':black_heart:',
            ),
            'gift_heart' =>
            array(
                ':gift_heart:',
            ),
            'revolving_hearts' =>
            array(
                ':revolving_hearts:',
            ),
            'heart_decoration' =>
            array(
                ':heart_decoration:',
            ),
            'heavy_heart_exclamation_mark_ornament' =>
            array(
                ':heavy_heart_exclamation_mark_ornament:',
            ),
            'love_letter' =>
            array(
                ':love_letter:',
            ),
            'zzz' =>
            array(
                ':zzz:',
            ),
            'anger' =>
            array(
                ':anger:',
            ),
            'bomb' =>
            array(
                ':bomb:',
            ),
            'boom' =>
            array(
                ':boom:',
            ),
            'sweat_drops' =>
            array(
                ':sweat_drops:',
            ),
            'dash' =>
            array(
                ':dash:',
            ),
            'dizzy' =>
            array(
                ':dizzy:',
            ),
            'speech_balloon' =>
            array(
                ':speech_balloon:',
            ),
            'left_speech_bubble' =>
            array(
                ':left_speech_bubble:',
            ),
            'right_anger_bubble' =>
            array(
                ':right_anger_bubble:',
            ),
            'thought_balloon' =>
            array(
                ':thought_balloon:',
            ),
            'hole' =>
            array(
                ':hole:',
            ),
            'eyeglasses' =>
            array(
                ':eyeglasses:',
            ),
            'dark_sunglasses' =>
            array(
                ':dark_sunglasses:',
            ),
            'necktie' =>
            array(
                ':necktie:',
            ),
            'shirt' =>
            array(
                ':shirt:',
            ),
            'jeans' =>
            array(
                ':jeans:',
            ),
            'scarf' =>
            array(
                ':scarf:',
            ),
            'gloves' =>
            array(
                ':gloves:',
            ),
            'coat' =>
            array(
                ':coat:',
            ),
            'socks' =>
            array(
                ':socks:',
            ),
            'dress' =>
            array(
                ':dress:',
            ),
            'kimono' =>
            array(
                ':kimono:',
            ),
            'bikini' =>
            array(
                ':bikini:',
            ),
            'womans_clothes' =>
            array(
                ':womans_clothes:',
            ),
            'purse' =>
            array(
                ':purse:',
            ),
            'handbag' =>
            array(
                ':handbag:',
            ),
            'pouch' =>
            array(
                ':pouch:',
            ),
            'shopping_bags' =>
            array(
                ':shopping_bags:',
            ),
            'school_satchel' =>
            array(
                ':school_satchel:',
            ),
            'mans_shoe' =>
            array(
                ':mans_shoe:',
            ),
            'athletic_shoe' =>
            array(
                ':athletic_shoe:',
            ),
            'high_heel' =>
            array(
                ':high_heel:',
            ),
            'sandal' =>
            array(
                ':sandal:',
            ),
            'boot' =>
            array(
                ':boot:',
            ),
            'crown' =>
            array(
                ':crown:',
            ),
            'womans_hat' =>
            array(
                ':womans_hat:',
            ),
            'tophat' =>
            array(
                ':tophat:',
            ),
            'mortar_board' =>
            array(
                ':mortar_board:',
            ),
            'billed_cap' =>
            array(
                ':billed_cap:',
            ),
            'helmet_with_white_cross' =>
            array(
                ':helmet_with_white_cross:',
            ),
            'prayer_beads' =>
            array(
                ':prayer_beads:',
            ),
            'lipstick' =>
            array(
                ':lipstick:',
            ),
            'ring' =>
            array(
                ':ring:',
            ),
            'gem' =>
            array(
                ':gem:',
            ),
        );
    }

    static public function getReactionData()
    {
        $like = new stdClass;
        $like->id = 1;
        $like->name = 'like';
        $like->text = JText::_('COM_COMMUNITY_REACTION_LIKE');

        $love = new stdClass;
        $love->id = 2;
        $love->name = 'love';
        $love->text = JText::_('COM_COMMUNITY_REACTION_LOVE');

        $haha = new stdClass;
        $haha->id = 3;
        $haha->name = 'haha';
        $haha->text = JText::_('COM_COMMUNITY_REACTION_HAHA');

        $wow = new stdClass;
        $wow->id = 4;
        $wow->name = 'wow';
        $wow->text = JText::_('COM_COMMUNITY_REACTION_WOW');

        $sad = new stdClass;
        $sad->id = 5;
        $sad->name = 'sad';
        $sad->text = JText::_('COM_COMMUNITY_REACTION_SAD');

        $angry = new stdClass;
        $angry->id = 6;
        $angry->name = 'angry';
        $angry->text = JText::_('COM_COMMUNITY_REACTION_ANGRY');

        return array($like, $love, $haha, $wow, $sad, $angry);
    }

    /*
     * Attaches mood emoticon
     */
    static public function getMood($str, $mood = null)
    {
        require_once(JPATH_ROOT . '/components/com_community/models/moods.php');

        static $moodCollection = array();

        $moodsModel = new CommunityModelMoods();
        $mood = $moodsModel->getMoodString($mood);

        if (empty($mood)) {
            return $str;
        } else if (!empty($str)) {
            return $str . ' - ' . $mood;
        } else {
            return $mood;
        }

        if (!empty($str)) {
            if ($mood != null && isset($moodCollection[$str][$mood])) {
                return $moodCollection[$str][$mood];
            } elseif (isset($moodCollection[$str])) {
                if (is_array($moodCollection[$str])) {
                    $moodCollection[$str] = $moodCollection[$str][key($moodCollection[$str])];
                }
                return $moodCollection[$str];
            }
        }

        $moodsModel = new CommunityModelMoods();
        $mood = $moodsModel->getMoodString($mood);

        if (empty($str)) {
            return $mood;
        }

        if ($mood !== '') {
            $mood = ' - ' . $mood;
        }

        if ($mood != null) {
            $moodCollection[$str][$mood] = $str . $mood;
            return $moodCollection[$str][$mood];
        } else {
            $moodCollection[$str] = $str . $mood;
            return $moodCollection[$str];
        }
    }

    static public function converttagtolink($str)
    {
        $parsedMessage = preg_replace('/(^|[^a-z0-9_])#([^\s[:punct:]]+)/i', '$1<a href="' . CRoute::_('index.php?option=com_community&view=frontpage&filter=hashtag&value=$2') . '"><strong>#$2</strong></a>', $str);
        return $parsedMessage;
    }

    /**
     * Auto make links from input text
     * @param string $text
     * @return string
     */
    public static function formatLinks($text)
    {
        $regex = "( )"; /* Force to have space at begining */
        $regex .= "((https?|ftp)\:\/\/)?"; // SCHEME
        $regex .= "([A-Za-z0-9+!*(),;?&=\$_.-]+(\:[A-Za-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
        $regex .= "([A-Za-z0-9-.]*)\.([A-Za-z]{2,4})"; // Host or IP
        $regex .= "(\:[0-9]{2,5})?"; // Port
        $regex .= "(\/([A-Za-z0-9+\$_-]\.?)+)*\/?"; // Path
        $regex .= "(\?[A-Za-z+&\$_.-][A-Za-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
        /* Find all url */
        $regex .= "(#[A-Za-z_.-][A-Za-z0-9+\$_.-]*)?"; // Anchor

        if (preg_match_all("/$regex/", $text, $matches)) {
            foreach ($matches[0] as $match) {
                /* Find and adding protocol if needed */
                if (strpos($match, 'http://') !== false || strpos($match, 'https://') !== false) {
                    $url = $match;
                } else {
                    $url = JUri::getInstance()->getScheme() . '://' . $match;
                }
                $url = trim($url);
                /* Link to open new tab if it's not internal link */
                if (JUri::isInternal($url)) {
                    $text = str_replace($match, '<a href="' . $url . '">' . $match . '</a>', $text);
                } else {
                    $text = str_replace($match, '<a href="' . $url . '" target="_blank" rel="nofollow" >' . $match . '</a>', $text);
                }
            }
        }
        return $text;
    }

    /**
     * Used to compare two string with ascii support.
     * @param $a
     * @param $b
     * @return int
     */
    public static function compareAscii($a, $b)
    {
        $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a);
        $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b);
        return strcmp($at, $bt);
    }

    /**
     * remove tag syntax from text
     * @param $text
     * @return string
     */
    public static function removeTagSyntax($text = '')
    {
        preg_match_all("/@\[\[\d+:\w+:[\w\s]+\]\]/", $text, $tagged);
        if (count($tagged[0]) > 0) {
            foreach ($tagged[0] as $user) {
                if (!empty($user)) {
                    $text = str_replace($user, preg_replace("/(\d)+:(\w)+:/", "", preg_replace("/[@\[\]]/", "", $user)), $text);
                }
            }
        }
        return $text;
    }

    public static function ratingStar($rating = 0, $total = 0, $link = false)
    {
        $html = '';

        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $html .= '<span class="star-ratings-css active"></span>';
            } else {
                $html .= '<span class="star-ratings-css"></span>';
            }
        }

        if ($total) {
            $html .= ' (' . $total . ')';
        }

        if ($link) {
            $html = '<a href="' . CRoute::_('index.php?option=com_community&view=pages&task=viewreviews&pageid=' . $link) . '">' . $html . '</a>';
        }

        return $html;
    }
}
