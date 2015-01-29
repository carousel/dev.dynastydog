<?php

namespace Intervention\Validation;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('intervention/validation');

        // registering intervention validator extension
        $this->app['validator']->resolver(function($translator, $data, $rules, $messages) {

            // set the package validation error messages
            $messages['iban'] = $translator->get('validation::validation.iban');
            $messages['bic'] = $translator->get('validation::validation.bic');
            $messages['hexcolor'] = $translator->get('validation::validation.hexcolor');
            $messages['creditcard'] = $translator->get('validation::validation.creditcard');
            $messages['isbn'] = $translator->get('validation::validation.isbn');
            $messages['isodate'] = $translator->get('validation::validation.isodate');
            $messages['username'] = $translator->get('validation::validation.username');
            $messages['htmlclean'] = $translator->get('validation::validation.htmlclean');
            $messages['password'] = $translator->get('validation::validation.password');

            return new ValidatorExtension($translator, $data, $rules, $messages);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        # code...
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('validator');
    }

}
