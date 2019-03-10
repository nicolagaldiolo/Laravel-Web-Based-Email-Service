<?php

namespace App\Providers;

use App\Library\Imap\GmailConnection;
use Illuminate\Support\ServiceProvider;

class ImapServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // includiamo una semplice quantità di logica per registrare una clousure da eseguire ogni volta che qualcosa nell'applicazione richiede un'istanza della classe identificata come 'Imap\Connection\GMail'.
        $this->app->singleton('Imap\Connection\GMail', function($app){
           return new GmailConnection(
               config('imap.gmail.options'),
               config('imap.gmail.retries'),
               config('imap.gmail.params')
           );
        });

        // possiamo chiamare nella nostra app una nuova istanza gmail cosi: $gmailConnection = \App::make('Imap\Connection\Gmail');

    }
}