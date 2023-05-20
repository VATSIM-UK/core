<?php

namespace App\Http\Controllers;

use App\Models\Mship\Account;
use Auth;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Cache;
use Session;
use View;

class BaseController extends \Illuminate\Routing\Controller
{
    use DispatchesJobs, ValidatesRequests, RedirectsUsers;
    use AuthorizesRequests {
        authorize as protected doAuthorize;
    }

    protected $account;

    protected $pageTitle;

    protected $pageSubTitle;

    protected $breadcrumb;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() || Auth::guard('web')->check()) {
                $this->account = Auth::user();
                $this->account->load('roles', 'roles.permissions');
            } else {
                $this->account = new Account();
            }

            return $next($request);
        });
    }

    public function redirectTo()
    {
        if (Session::has('url.intended')) {
            return Session::pull('url.intended');
        }

        return $this->redirectTo;
    }

    /**
     * Authorize a given action for the current user.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        try {
            return $this->doAuthorize($ability, $arguments);
        } catch (AuthorizationException $e) {
            if (Session::has('authorization.error')) {
                $class = get_class($e);

                // throw the same exception with the reason for authorization failure
                throw new $class(Session::get('authorization.error'), $e->getCode(), $e->getPrevious());
            } else {
                throw $e;
            }
        }
    }

    protected function viewMake($view)
    {
        $view = View::make($view);

        $view->with('_account', $this->account);

        $this->buildBreadcrumb('Home', '/');

        $view->with('_breadcrumb', $this->breadcrumb);
        $view->with('_bannerUrl', self::generateBannerUrl());

        $view->with('_pageTitle', $this->getTitle());
        $view->with('_pageSubTitle', $this->getSubTitle());

        return $view;
    }

    public function setTitle($title)
    {
        $this->pageTitle = $title;
    }

    public function getTitle()
    {
        if ($this->pageTitle == null) {
            return $this->breadcrumb->first()->get('name');
        }

        return $this->pageTitle;
    }

    public function setSubTitle($title)
    {
        $this->pageSubTitle = $title;
    }

    public function getSubTitle()
    {
        return $this->pageSubTitle;
    }

    /**
     * Generate CORE banner from time of day.
     */
    public static function generateBannerUrl()
    {
        $key = 'CORE_BANNER_URL';

        if ($url = Cache::get($key)) {
            return $url;
        }

        // Work out time of day
        $time = Carbon::now();

        switch ($time) {
            case $time->hour < 7:
                $time = 'night';
                break;
            case $time->hour < 9:
                $time = 'morning';
                break;
            case $time->hour < 17:
                $time = 'day';
                break;
            case $time->hour < 21:
                $time = 'evening';
                break;
            default:
                $time = 'night';
        }

        $dir = public_path('images/banner/'.$time);
        $images = array_diff(scandir($dir), ['.', '..']);
        if (count($images) == 0) {
            return asset('images/banner/fallback.jpg');
        }
        $url = asset("images/banner/$time/".$images[array_rand($images)]);
        Cache::put($key, $url, 60 * 60);

        return $url;
    }

    protected function setupLayout()
    {
        if (! is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

    /**
     * Add a new element to the breadcrumb to be shown on this page.
     *
     * @param  string  $name  The text to display on the page.
     * @param  string  $uri  The URI the text should link to.
     * @param  bool  $linkToPrevious  Set to TRUE if the breadcrumb is a parent of the previous one.
     * @param  bool  $first  Set to TRUE if the breadcrumb should be first.
     */
    protected function addBreadcrumb(string $name, $uri = null, $linkToPrevious = false, $first = false)
    {
        if ($this->breadcrumb == null) {
            $this->breadcrumb = collect();
        }

        if ($linkToPrevious) {
            $uri = $this->breadcrumb->last()->get('uri').'/'.$uri;
        }

        $element = collect(['name' => $name, 'uri' => $uri]);

        if ($first) {
            $this->breadcrumb->prepend($element);

            return;
        }

        $this->breadcrumb->push($element);
    }

    protected function buildBreadcrumb($startName, $startUri)
    {
        $this->addBreadcrumb($startName, $startUri, false, true);
    }

    protected function getControllerRequest()
    {
        $requestClass = $this->getRequestClassAsArray(true);

        return $requestClass[0];
    }

    protected function getRequestClassAsArray($clean = true)
    {
        $requestClass = explode('\\', get_called_class());

        // Return the dirty path.
        if (! $clean) {
            return $requestClass;
        }

        // Remove App/ From the class path.
        return array_slice($requestClass, 4);
    }
}
