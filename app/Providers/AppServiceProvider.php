<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);

        if (!app()->runningUnitTests()) {
            try {
                if (Schema::hasTable('settings')) {
                    $mailHost = Setting::get('mail_host');
                    if (!empty($mailHost)) {
                        $mailer = Setting::get('mail_mailer') ?: 'smtp';
                        $encryption = Setting::get('mail_encryption');

                        Config::set('mail.default', $mailer);
                        Config::set('mail.mailers.smtp.host', $mailHost);
                        Config::set('mail.mailers.smtp.port', (int) (Setting::get('mail_port') ?: 587));
                        Config::set('mail.mailers.smtp.username', Setting::get('mail_username'));
                        Config::set('mail.mailers.smtp.password', Setting::get('mail_password'));
                        if (!empty($encryption) && $encryption !== 'null') {
                            Config::set('mail.mailers.smtp.scheme', $encryption === 'ssl' ? 'smtps' : 'smtp');
                        }
                    }

                    $fromAddress = Setting::get('mail_from_address');
                    if (!empty($fromAddress)) {
                        Config::set('mail.from.address', $fromAddress);
                    }

                    $fromName = Setting::get('mail_from_name') ?: Setting::get('site_name');
                    if (!empty($fromName)) {
                        Config::set('mail.from.name', $fromName);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if database is not yet migrated
            }
        }
    }
}
