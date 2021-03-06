<?php

namespace contact\Controller;

use contact\Factory\ContactInfo\Map as Factory;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Map extends \phpws2\Http\Controller
{

    public function get(\Canopy\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Canopy\Response($view);
        return $response;
    }

    protected function getHtmlView($data, \Canopy\Request $request)
    {
        $content = \contact\Factory\ContactInfo::form($request, 'map');
        $view = new \phpws2\View\HtmlView(\PHPWS_ControlPanel::display($content));
        return $view;
    }

    protected function getJsonView($data, \Canopy\Request $request)
    {
        $command = $request->shiftCommand();
        switch ($command) {
            case 'locationString':
                return $this->locationString();
                break;

            case 'getGoogleLink':
                return $this->getGoogleLink($request);
                break;

            case 'saveThumbnail':
                return $this->saveThumbnail($request);
                break;

            case 'clearThumbnail':
                Factory::clearThumbnail();
                $json['success'] = 1;
                $response = new \phpws2\View\JsonView($json);
                return $response;
                break;
        }
    }

    private function getGoogleLink(\Canopy\Request $request)
    {
        $latitude = $request->getVar('latitude');
        $longitude = $request->getVar('longitude');
        $json['url'] = Factory::getImageUrl($latitude, $longitude);
        $response = new \phpws2\View\JsonView($json);
        return $response;
    }

    private function locationString()
    {
        $json = array();
        $contact_info = \contact\Factory\ContactInfo::load();
        $physical_address = $contact_info->getPhysicalAddress();

        try {
            $json['address'] = Factory::getGoogleSearchString($physical_address);
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $response = new \phpws2\View\JsonView($json);
        return $response;
    }

    private function saveThumbnail(\Canopy\Request $request)
    {
        $latitude = $request->getVar('latitude');
        $longitude = $request->getVar('longitude');

        Factory::createMapThumbnail($latitude, $longitude);

        $json['result'] = 'true';
        $response = new \phpws2\View\JsonView($json);
        return $response;
    }

}
