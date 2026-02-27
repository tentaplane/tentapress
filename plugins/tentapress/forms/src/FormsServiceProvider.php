<?php

declare(strict_types=1);

namespace TentaPress\Forms;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\Forms\Destinations\DestinationRegistry;
use TentaPress\Forms\Destinations\KitDestination;
use TentaPress\Forms\Destinations\MailchimpDestination;
use TentaPress\Forms\Destinations\TentaFormsDestination;
use TentaPress\Forms\Discovery\FormsBlockKit;
use TentaPress\Forms\Console\MigrateNewsletterBlocksCommand;
use TentaPress\Forms\Services\FormConfigNormalizer;
use TentaPress\Forms\Services\FormPayloadSigner;
use TentaPress\Forms\Services\FormSubmissionService;
use TentaPress\Forms\Services\SpamGuard;

final class FormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FormsBlockKit::class);
        $this->app->singleton(FormConfigNormalizer::class);
        $this->app->singleton(FormPayloadSigner::class);
        $this->app->singleton(SpamGuard::class);
        $this->app->singleton(MailchimpDestination::class);
        $this->app->singleton(TentaFormsDestination::class);
        $this->app->singleton(KitDestination::class);
        $this->app->singleton(DestinationRegistry::class, function ($app): DestinationRegistry {
            $registry = new DestinationRegistry();
            $registry->register($app->make(MailchimpDestination::class));
            $registry->register($app->make(TentaFormsDestination::class));
            $registry->register($app->make(KitDestination::class));

            return $registry;
        });
        $this->app->singleton(FormSubmissionService::class);
    }

    public function boot(): void
    {
        RateLimiter::for('tp.forms.submit', function (Request $request): Limit {
            $formKey = strtolower(trim((string) $request->route('formKey', 'form')));
            $key = $request->ip().'|'.$formKey;

            return Limit::perMinute(20)->by($key);
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-blocks');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateNewsletterBlocksCommand::class,
            ]);
        }

        if ($this->app->bound(BlockRegistry::class)) {
            $registry = $this->app->make(BlockRegistry::class);

            if ($registry instanceof BlockRegistry) {
                $this->app->make(FormsBlockKit::class)->register($registry);
            }
        }
    }
}
