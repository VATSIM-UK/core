<?php

namespace App\Http\Controllers\Site;

class HomePageController extends \App\Http\Controllers\BaseController
{
    public function __invoke()
    {
        return view('site.home')
            ->with('nextEvent', $this->nextEvent());
    }

    private function nextEvent()
    {
        $html = file_get_contents('https://cts.vatsim.uk/extras/next_event.php');

        return $this->getHTMLByID('next', $html);
    }

    public function getHTMLByID($id, $html)
    {
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $node = $dom->getElementById($id);
        if ($node) {
            return $dom->saveXML($node);
        }

        return false;
    }
}
