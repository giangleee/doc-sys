<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\MailTemplate;
use App\Models\ServiceUser;
use App\Models\User;
use App\Policies\DocumentPolicy;
use App\Policies\MailTemplatePolicy;
use App\Policies\ServiceUserPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
        MailTemplate::class => MailTemplatePolicy::class,
        ServiceUser::class => ServiceUserPolicy::class,
        Document::class => DocumentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
