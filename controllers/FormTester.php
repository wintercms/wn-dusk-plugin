<?php

namespace Winter\Dusk\Controllers;

use App;
use Backend\Behaviors\FormController;
use Backend\Classes\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\View;

class FormTester extends Controller
{
    public string $formConfig;

    public $implement = [
        FormController::class,
    ];

    public function __construct()
    {
        // Get pass-through values
        $path = explode('/', FacadesRequest::path());
        $passthroughFilename = end($path);

        if (!file_exists(base_path('storage/dusk/form-tester/' . $passthroughFilename))) {
            return;
        }

        try {
            $config = unserialize(file_get_contents(base_path('storage/dusk/form-tester/' . $passthroughFilename)));
            if (!$config) {
                throw new \Exception('Invalid config');
            }
        } catch (\Throwable $ex) {
            return;
        }

        $this->formConfig = $config['formConfig'];

        // Prevent access to this controller unless we are running Dusk tests
        $this->middleware(function ($request, $response) {
            if (App::isProduction() || env('APP_ENV') !== 'dusk') {
                $response->setContent(View::make('backend::404'))->setStatusCode(404);
            }
        });

        if (App::isProduction() || env('APP_ENV') !== 'dusk') {
            return;
        }

        parent::__construct();
    }

    public function create(string $filename)
    {
        return $this->asExtension('FormController')->create('create');
    }
}
