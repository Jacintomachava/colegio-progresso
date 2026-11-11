<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ReclamacaoElogioPedido;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       /* view()->composer('layouts.app', function ($view) {

            $reclamacoes = ReclamacaoElogioPedido::latest()->take(10)->get(); // Últimas 5
            $totalReclamacao = ReclamacaoElogioPedido::count(); // Total

            //Mandar Para View
            $view->with([
                'reclamacoes' => $reclamacoes,
                'totalReclamacao' => $totalReclamacao,
            ]);
        });*/
    }
}
