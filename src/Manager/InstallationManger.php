<?php

namespace Shipu\WebInstaller\Manager;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shipu\WebInstaller\Concerns\InstallationContract;
use Shipu\WebInstaller\Utilities\DatabaseConnection;

class InstallationManger implements InstallationContract
{
    public function run($data): bool
    {
        try {
            Artisan::call('migrate:fresh', [
                '--force' => true,
            ]);

            $user = config('installer.user_model');

            $user::create([
                'name'       => array_get($data, 'applications.admin.name'),
                'email'      => array_get($data, 'applications.admin.email'),
                'password'   => array_get($data, 'applications.admin.password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Artisan::call('db:seed', [
                '--force' => true,
            ]);

            file_put_contents(storage_path('installed'), 'installed');

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function redirect(): Application|Redirector|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        if(class_exists(Filament::class)) {
            return redirect()->intended(Filament::getUrl());
        }

        return redirect(config('installer.redirect_url')());
    }

    public function dehydrate(): void
    {
        Log::info("installation dehydrate...");
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }
}