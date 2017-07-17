<?php

namespace App\Http\Controllers;

use App\Models\Mship\Account;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
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
            if (Auth::check()) {
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

    protected function setupLayout()
    {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

    /**
     * Add a new element to the breadcrumb to be shown on this page.
     *
     * @param      $name The text to display on the page.
     * @param      $uri  The URI the text should link to.
     * @param bool $linkToPrevious Set to TRUE if the breadcrumb is a parent of the previous one.
     */
    protected function addBreadcrumb($name, $uri = null, $linkToPrevious = false)
    {
        if ($this->breadcrumb == null) {
            $this->breadcrumb = collect();
        }

        if ($linkToPrevious) {
            $uri = $this->breadcrumb->last()->get('uri').'/'.$uri;
        }

        $element = collect(['name' => $name, 'uri' => $uri]);

        $this->breadcrumb->push($element);
    }

    protected function buildBreadcrumb($startName, $startUri)
    {
        $this->addBreadcrumb($startName, $startUri);
        $this->addControllerBreadcrumbs();
    }

    protected function addControllerBreadcrumbs()
    {
        $this->addBreadcrumb(ucfirst($this->getControllerRequest()), $this->getControllerRequest(), true);
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
        if (!$clean) {
            return $requestClass;
        }

        // Remove App/ From the class path.
        return array_slice($requestClass, 4);
    }
}
