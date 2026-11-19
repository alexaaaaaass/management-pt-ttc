<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Resources\AdminResource\Widgets\PemasukanChart;
use App\Filament\Admin\Resources\AdminResource\Widgets\PengeluaranChart;
use App\Filament\Admin\Resources\AdminResource\Widgets\ProductionMonitoringWidget;
use App\Filament\Admin\Widgets\ExecutivePerformanceDashboardWidget;
use App\Filament\Admin\Widgets\OrderOperationsOverview;
use App\Filament\Admin\Widgets\PiutangJatuhTempoWidget;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Filament\Admin\Widgets\StockMinimumAlertWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->path('admin')
            // GANTI NAMA HEADER
            ->brandName('Sistem ERP')

            // GANTI DENGAN LOGO PT
            ->brandLogo(asset('images/Logo PT Teknografi.png'))

            // ukuran logo
            ->brandLogoHeight('40px')
            ->sidebarCollapsibleOnDesktop()    
            ->font('Inter')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.hooks.accurate-theme')
            )

            // ✅ TAMBAHKAN INI
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Marketing')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Purchase')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Subcount')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Ppic')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Pra Cetak')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Printing')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Warehouse')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Finishing')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Dispatch')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Finance')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('HRD')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Report')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Manajemen Akses')
                    ->collapsed(),
            ])

            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                ExecutivePerformanceDashboardWidget::class,
                StatsOverview::class,
                OrderOperationsOverview::class,
                PemasukanChart::class,
                PengeluaranChart::class,
                PiutangJatuhTempoWidget::class,
                StockMinimumAlertWidget::class,
                ProductionMonitoringWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
