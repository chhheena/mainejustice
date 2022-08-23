<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

define('MAGPIE_DIR', '../');
require_once(MAGPIE_DIR . 'rss_fetch.inc');

$url = $_GET['url'];

if ($url) {
    $rss = fetch_rss($url);
    echo "Channel: " . $rss->channel['title'] . "<p>";
    echo "<ul>";
    foreach ($rss->items as $item) {
        $href = $item['link'];
        $title = $item['title'];
        echo "<li><a href=$href>$title</a></li>";
    }
    echo "</ul>";
}
?>

<form>
    RSS URL: <input type="text" size="30" name="url" value="<?php echo $url ?>"><br/>
    <input type="submit" value="Parse RSS">
</form>

<p>
<h2>Security Note:</h2>
This is a simple <b>example</b> script.  If this was a
<b>real</b> script we probably wouldn't allow  strangers to submit random URLs, and we certainly wouldn't simply echo anything passed in the URL.  Additionally its a bad idea to leave this example script lying around.
</p>
