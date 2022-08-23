<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(AZRUL_SYSTEM_PATH.'/pc_includes/JSON.php');

class CMapping
{
    const GEOCODE_URL = 'https://maps.google.com/maps/api/geocode/json?';
    const STATICMAP_URL = 'https://maps.google.com/maps/api/staticmap?';

    /**
     * Restrive the data from cache table instead
     */
    private static function _getCachedAddressData($address)
    {
        $location   = JTable::getInstance('LocationCache', 'CTable');
        $location->load($address);
        return $location->data;
    }

    /**
     * Fetch google map data refere to
     * http://code.google.com/apis/maps/documentation/geocoding/#Geocoding
     */
    public static function getAddressData($address)
    {
        if (is_object($address)) {
            $address = implode(',', (array) $address);
        }

        $mapType = CFactory::getConfig()->get('maps_api', 'googlemap');
        $content = self::getAddressCache($address, $mapType);
        
        if (!$content) {
            if ($mapType == 'googlemap') {
                $url = CMapping::GEOCODE_URL . 'address='.urlencode($address).'&sensor=false'.'&key='.CFactory::getConfig()->get('googleapikey');
                $content = CRemoteHelper::getContent($url);
            } else {
                $url = 'https://nominatim.openstreetmap.org/search?q='.urlencode($address).'&format=json&limit=1';
                // Create a stream
                $opts = array('http'=>array('header'=>"User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0\r\n"));
                $context = stream_context_create($opts);
                $content = @file_get_contents($url, false, $context);
            }

            self::setAddressCache($address, $mapType, $content);
        }

        $status = null;

        if (!empty($content)) {
            $json = new Services_JSON();
            $status = $json->decode($content);
        }
        
        return $status;
    }

    protected static function setAddressCache($address, $mapType, $content)
    {
        $cachePath = JPATH_ROOT . '/media/com_community/address_cache/' . md5($address . $mapType) . '.json' ;
        JFile::write($cachePath, $content);
    }

    protected static function getAddressCache($address, $mapType)
    {
        $cachePath = JPATH_ROOT . '/media/com_community/address_cache/' . md5($address . $mapType) . '.json' ;
        if (JFile::exists($cachePath)) {
            return @file_get_contents($cachePath);
        } else {
            return '';
        }
    }

    /**
     * Given a string address, we will try to validate and see if google think
     * it is allright
     */
    public static function validateAddress($address)
    {
        $content = CMapping::_getCachedAddressData($address);
        $isValid = false;

        if (!empty($content)) {
            $json = new Services_JSON();
            $status = $json->decode($content);

            // @todo: catch $content error, google map  or openstreetmap
            if (isset($status->status)) {
                $isValid = $status->status == 'OK';
            } elseif (isset($status[0]->display_name)) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    /**
     * Return the code that will generate static map
     * if width is '0', then it will draw at 100% width
     */
    public static function drawStaticMap($address, $width, $height)
    {
        $elementid = 'map-'. md5($address);
        $elementid = substr($elementid, 0, 16);

        $data = CMapping::getAddressData($address);
        $html = '';

        if (CFactory::getConfig()->get('maps_api', '') == 'googlemap') {
            $url = CMapping::STATICMAP_URL . 'center=';
            $url .= urlencode($address).'&zoom=14&maptype=roadmap&sensor=false'.'&key='.CFactory::getConfig()->get('googleapikey');

            if ($data->status == 'OK') {
                $lat = $data->results[0]->geometry->location->lat;
                $long = $data->results[0]->geometry->location->lng;
                $url .= '&markers=color:blue|'.$lat.','.$long;
            }

            if (!empty($width)) {
                // No need for fancy javascript if the exact size is known
                $html = '<img src="' . $url .'&size='.  $width .'x'. $height.'" id="'.$elementid.'"/>';
            } else {
                // If we don't know the height, need some fancy javascript to calculate the width
                // and fetch the image via javascript
                $html  = '<div style="width:100%;height:'.$height.'px" id="'.$elementid.'-wrap">
							<img src="" id="'.$elementid.'"/>
						  </div>';
                $html .= '<script type="text/javascript">'
                        .'joms.jQuery(document).ready( function() {';

                if ($width != 0) {
                    $html .= 'var width 	= \'' . $width . '\';';
                } else {
                    $html .= 'var width 	= joms.jQuery(\'#'.$elementid.'-wrap\').width();';
                }


                $html .= 'var height 	=' . $height . ';'
                        .'var url		= \'' . $url .'&size=\' + width + \'x'.$height.'\';'
                        .'joms.jQuery(\'#'.$elementid.'\').attr(\'src\', url);'
                        .'});'
                        .'</script>';
            }
        } else {
            if ($data) {
                if (isset($data[0]->lat) && $data[0]->lon) {
                    $lat  = $data[0]->lat;
                    $long = $data[0]->lon;

                    $html = '<iframe width="'.$width.'" height="'.$height.'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.openstreetmap.org/export/embed.html?bbox='.$data[0]->boundingbox[2].'%2C'.$data[0]->boundingbox[0].'%2C'.$data[0]->boundingbox[3].'%2C'.$data[0]->boundingbox[1].'&amp;layer=mapnik&amp;marker='.$lat.'%2C'.$long.'"></iframe>';
                }
            }
        }

        return $html;
    }

    public static function drawOSMMap($address, $width, $height)
    {
        $elementid = 'map-'. md5($address);
        $elementid = substr($elementid, 0, 16);

        $data = CMapping::getAddressData($address);
        $html = '';

        if (CFactory::getConfig()->get('maps_api', '') == 'openstreetmap') {
            if ($data) {
                if (isset($data[0]->lat) && $data[0]->lon) {
                    $lat  = $data[0]->lat;
                    $long = $data[0]->lon;

                    $html = '<iframe width="'.$width.'" height="'.$height.'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.openstreetmap.org/export/embed.html?bbox='.$data[0]->boundingbox[2].'%2C'.$data[0]->boundingbox[0].'%2C'.$data[0]->boundingbox[3].'%2C'.$data[0]->boundingbox[1].'&amp;layer=mapnik&amp;marker='.$lat.'%2C'.$long.'"></iframe>';
                }
            }
        }

        return $html;
    }

    /**
     * Draw the triple zoomer map
     * @param type $address
     * @param type $width
     * @param type $height
     */
    public static function drawZoomableMap($address, $width, $height, $longitude = '', $latitude = '')
    {

        $tmpl = new CTemplate();
        $tmpl   ->set('address', $address)
                ->set('width', $width)
                ->set('height', $height);

        // set the actual long and lang if exists
        if ($longitude != '' && $longitude != '255.000000') {
            $tmpl   ->set('longitude', $longitude)
                    ->set('latitude', $latitude);
        }

        return $tmpl->fetch('map.zoom');
    }

    /**
     * Draw google map on the target element
     * It will add marker at the address
     */
    public static function drawMap($targetId, $address, $showMarker = true, $title = '', $info = '', $loadMap = false)
    {
        // need to attach the google map js once. It doesn't really need
        // to be attached in the <head> section

        $html = '';
        if ($loadMap) {
            $html = '<script type="text/javascript" src="maps.google.com/maps/api/js?sensor=false"></script>';
        }
        //CFactory::load( 'helpers' , 'string' );

        $html  .= '<script type="text/javascript">'
                .'joms.jQuery(document).ready( function() {'
                .'joms.maps.initialize(\''.$targetId.'\', \''. addslashes($address) .'\', \''. addslashes($title).'\', \''.$info.'\');'
                .'});'
                .'</script>';
        return $html;
    }

    /**
     * Add marker point to the given address
     */
    public function addMarker($targetId, $address, $title = '', $info = '')
    {
        $html = '';
        $data = CMapping::getAddressData($address);
        
        if ($data) {
            if (CFactory::getConfig()->get('maps_api', '') == 'googlemap') {
                if ($data->status == 'OK') {
                    $lat = $data->results[0]->geometry->location->lat;
                    $lng = $data->results[0]->geometry->location->lng;

                    $html  = '<script type="text/javascript">'
                    .'joms.jQuery(document).ready( function() {'
                    .'joms.maps.addMarker(\''.$targetId.'\', '.$lat.', '.$lng.', \''.$title.'\', \''.$info.'\');'
                    .'});'
                    .'</script>';
                }
            } else {
                if (isset($data[0]->lat) && $data[0]->lon) {
                    $lat = $data[0]->lat;
                    $lng = $data[0]->lon;

                    $html  = '<script type="text/javascript">'
                    .'joms.jQuery(document).ready( function() {'
                    .'joms.maps.addMarker(\''.$targetId.'\', '.$lat.', '.$lng.', \''.$title.'\', \''.$info.'\');'
                    .'});'
                    .'</script>';
                }
            }
        }
        return $html;
    }
    
    /**
     * Get the Formated address from google
     */
    public static function getFormatedAdd($address)
    {
        $data = CMapping::getAddressData($address);
        
        if (CFactory::getConfig()->get('maps_api', '') == 'googlemap') {
            if (isset($data->results[0])) {
                return $data->results[0]->formatted_address;
            } else {
                return null;
            }
        } else {
            if (isset($data[0]->display_name)) {
                return $data[0]->display_name;
            } else {
                return null;
            }
        }
    }
}
