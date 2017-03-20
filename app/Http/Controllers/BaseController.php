<?php

namespace App\Http\Controllers;

use Auth;
use View;
use Request;
use App\Models\Mship\Account;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BaseController extends \Illuminate\Routing\Controller
{
    use DispatchesJobs, ValidatesRequests, AuthorizesRequests;

    protected $account;
    protected $pageTitle;
    protected $pageSubTitle;
    protected $breadcrumb;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $this->account = Auth::user();
                $this->account->load('roles', 'roles.permissions');

                // Do we need to do some debugging on this user?
                if ($this->account->debug) {
                    \Debugbar::enable();
                }

                // if last login recorded is older than 45 minutes, record the new timestamp
                if ($this->account->last_login < \Carbon\Carbon::now()
                        ->subMinutes(45)
                        ->toDateTimeString()
                ) {
                    $this->account->last_login = \Carbon\Carbon::now();
                    // if the ip has changed, record this too
                    if ($this->account->last_login_ip != array_get($_SERVER, 'REMOTE_ADDR', '127.0.0.1')) {
                        $this->account->last_login_ip = array_get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
                    }
                    $this->account->save();
                }
            } else {
                $this->account = new Account();
            }

            return $next($request);
        });
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
            if ($this->isModuleRequest()) {
                return $this->getModuleRequest()->get('name');
            }

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
        if ($this->pageSubTitle == null) {
            if ($this->isModuleRequest()) {
                return $this->getControllerRequest();
            }

            return;
        }

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
        $this->addModuleBreadcrumb();
        $this->addControllerBreadcrumbs();
    }

    protected function addModuleBreadcrumb()
    {
        if ($this->isModuleRequest()) {
            $this->addBreadcrumb($this->getModuleRequest()->get('name'), $this->getModuleRequest()->get('slug'), true);
        }
    }

    protected function addControllerBreadcrumbs()
    {
        $this->addBreadcrumb(ucfirst($this->getControllerRequest()), $this->getControllerRequest(), true);
    }

    /**
     * Determine if this request is for a module, rather than the core code.
     *
     * @return bool True if this request is for a module.
     */
    protected function isModuleRequest()
    {
        return strcasecmp($this->getRequestClassAsArray(false)[1], 'modules') == 0;
    }

    /**
     * Get the information about the module use for this request.
     *
     * @return \Caffeinated\Modules\Collection
     */
    protected function getModuleRequest()
    {
        $requestClass = $this->getRequestClassAsArray(false);

        return \Module::where('slug', strtolower($requestClass[2]));
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

        // Remove app/modules/.../Http/Controllers/... from the class path.
        if ($this->isModuleRequest()) {
            return array_slice($requestClass, 6);
        }

        // Remove App/ From the class path.
        return array_slice($requestClass, 4);
    }
}
