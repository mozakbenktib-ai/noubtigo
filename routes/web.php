<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Modules\Auth\Controllers\RegisterController;
use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\PasswordResetController;
use App\Http\Controllers\PasswordChangeController;
use App\Modules\Companies\Controllers\CompanySettingsController;
use App\Modules\Customers\Controllers\CustomerController;

// Auth Routes (Public)

Route::middleware('guest')->group(function () {
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Google OAuth Routes
    Route::get('/auth/google', [\App\Modules\Auth\Controllers\GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Modules\Auth\Controllers\GoogleAuthController::class, 'callback']);
    Route::get('/register/google-complete', [RegisterController::class, 'showGoogleCompleteForm'])->name('register.google-complete');
    Route::post('/register/google-complete', [RegisterController::class, 'completeGoogleRegistration']);
});

// Forced Password Change Routes
Route::middleware(['auth'])->group(function () {
    Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/password/change', [PasswordChangeController::class, 'show'])->name('password.change.show');
    Route::post('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');
});


// Public Queue Display & Device Pairing
Route::get('/queue/display/{room:slug?}', [\App\Modules\Queue\Controllers\DisplayController::class, 'show'])->name('queue.display');
Route::get('/display/setup/{uid?}', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'setup'])->name('queue.display.setup');
Route::get('/display/device/{token}', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'show'])->name('queue.display.show');
Route::post('/display/authorize', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'authorizeDevice'])->name('queue.display.authorize');
Route::get('/tv', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'setup'])->name('queue.display.tv');
Route::post('/display/device/{token}/takeover', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'takeOverSession'])->name('queue.display.takeover');
Route::post('/display/device/{token}/release', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'releaseSession'])->name('queue.display.release');
Route::get('/track/status', [\App\Modules\Queue\Controllers\TrackerController::class, 'show'])->name('queue.track.status');
Route::get('/track/{token}', [\App\Modules\Queue\Controllers\TrackerController::class, 'hub'])->name('queue.track.hub');
Route::get('/track', function () {
    abort(404, 'A company tracking token is required.');
});
Route::post('/track', [\App\Modules\Queue\Controllers\TrackerController::class, 'verify'])->name('queue.track.verify');
Route::get('/track/{token}/ticket', [\App\Modules\Queue\Controllers\TrackerController::class, 'index'])->name('queue.track.landing');

// Customer Portal Routes
Route::prefix('customer')->name('customer.')->group(function () {
    Route::middleware(['guest:customer'])->group(function () {
        Route::get('/login', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'login']);
        Route::get('/register', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'register']);
    });

    Route::middleware(['auth:customer'])->group(function () {
        Route::get('/dashboard', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'index'])->name('dashboard');
        Route::match(['get', 'post'], '/logout', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'logout'])->name('logout');
        Route::post('/favorites/{company}', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'toggleFavorite'])->name('favorites.toggle');
        Route::delete('/favorites/{company}', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'removeFavorite'])->name('favorites.remove');
        Route::post('/sync-ticket', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'syncTicket'])->name('sync-ticket');
        Route::post('/add-by-code', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'addCompanyByCode'])->name('add-by-code');
        Route::get('/track-company/{company}', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'trackCompany'])->name('track-company');
        Route::get('/track-ticket/{ticket}', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'trackTicket'])->name('track-ticket');
        Route::post('/profile', [\App\Modules\Customers\Controllers\CustomerPortalController::class, 'updateProfile'])->name('profile.update');
    });
});

// Protected Dashboard Routes
Route::middleware(['auth', 'subscription.valid'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('todos')->name('todos.')->group(function () {
        Route::post('/', [\App\Modules\Todos\Controllers\TodoController::class, 'store'])->name('store');
        Route::patch('/{todo}', [\App\Modules\Todos\Controllers\TodoController::class, 'update'])->name('update');
        Route::patch('/{todo}/toggle', [\App\Modules\Todos\Controllers\TodoController::class, 'toggle'])->name('toggle');
        Route::delete('/{todo}', [\App\Modules\Todos\Controllers\TodoController::class, 'destroy'])->name('destroy');
    });

    Route::get('/global-search', [\App\Http\Controllers\GlobalSearchController::class, 'search'])->name('global.search');

    // Simple Queue Dashboard (Direct Access)
    Route::middleware(['permission:queue.view'])->group(function () {
        Route::get('/simple', [\App\Modules\Queue\Controllers\SimpleQueueController::class, 'index'])->name('queue.simple.index');
        Route::post('/simple/store', [\App\Modules\Queue\Controllers\SimpleQueueController::class, 'store'])->middleware('permission:queue.create')->name('queue.simple.store');
        Route::post('/simple/next', [\App\Modules\Queue\Controllers\SimpleQueueController::class, 'callNext'])->middleware('permission:queue.call')->name('queue.simple.next');
        Route::patch('/simple/{ticket}/pass', [\App\Modules\Queue\Controllers\SimpleQueueController::class, 'pass'])->middleware('permission:queue.edit')->name('queue.simple.pass');
        Route::patch('/simple/{ticket}/done', [\App\Modules\Queue\Controllers\SimpleQueueController::class, 'done'])->middleware('permission:queue.edit')->name('queue.simple.done');
        Route::get('/simple/stats', [\App\Modules\Queue\Controllers\SimpleQueueController::class, 'stats'])->name('queue.simple.stats');
    });

    // User Management & RBAC
    Route::prefix('rbac')->name('rbac.')->group(function () {
        // Master Control Panel (System Admin only)
        Route::middleware(['system_admin'])->group(function () {
            Route::get('/master', [\App\Modules\RBAC\Controllers\MasterController::class, 'index'])->name('master.index');
            Route::get('/master/users', [\App\Modules\RBAC\Controllers\MasterController::class, 'users'])->name('master.users');
            Route::patch('/master/users/{user}/permissions', [\App\Modules\RBAC\Controllers\MasterController::class, 'updateUserPermissions'])->name('master.update-user-permissions');

            Route::post('/master/permissions', [\App\Modules\RBAC\Controllers\MasterController::class, 'storePermission'])->name('master.store-permission');
            Route::post('/master/module-permissions', [\App\Modules\RBAC\Controllers\MasterController::class, 'storeModulePermissions'])->name('master.store-module-permissions');
            Route::patch('/master/permissions/{permission}', [\App\Modules\RBAC\Controllers\MasterController::class, 'updatePermission'])->name('master.update-permission');
            Route::post('/master/roles', [\App\Modules\RBAC\Controllers\MasterController::class, 'storeRole'])->name('master.store-role');
            Route::patch('/master/roles/{role}', [\App\Modules\RBAC\Controllers\MasterController::class, 'updateRole'])->name('master.update-role');
            Route::post('/master/roles/{role}/clone', [\App\Modules\RBAC\Controllers\MasterController::class, 'cloneRole'])->name('master.clone-role');
            Route::delete('/master/roles/{role}', [\App\Modules\RBAC\Controllers\MasterController::class, 'destroyRole'])->name('master.destroy-role');
            Route::post('/master/roles/{role}/sync', [\App\Modules\RBAC\Controllers\MasterController::class, 'syncRolePermissions'])->name('master.sync-role-permissions');
            Route::get('/master/roles/{role}/permissions', [\App\Modules\RBAC\Controllers\MasterController::class, 'getRolePermissions'])->name('master.role-permissions');

            // App Plans CRUD
            Route::post('/master/plans', [\App\Modules\RBAC\Controllers\MasterController::class, 'storePlan'])->name('master.store-plan');
            Route::patch('/master/plans/{plan}', [\App\Modules\RBAC\Controllers\MasterController::class, 'updatePlan'])->name('master.update-plan');
            Route::delete('/master/plans/{plan}', [\App\Modules\RBAC\Controllers\MasterController::class, 'destroyPlan'])->name('master.destroy-plan');

            // Company Master Management
            Route::patch('/master/companies/{company}', [\App\Modules\RBAC\Controllers\MasterController::class, 'updateCompany'])->name('master.update-company');
        });

        // Company User Management
        Route::middleware(['permission:users.view'])->group(function () {
            Route::get('/users', [\App\Modules\RBAC\Controllers\UsersController::class, 'index'])->name('users.index');
            Route::post('/users', [\App\Modules\RBAC\Controllers\UsersController::class, 'store'])->middleware('permission:users.create')->name('users.store');
            Route::patch('/users/{user}', [\App\Modules\RBAC\Controllers\UsersController::class, 'update'])->middleware('permission:users.edit')->name('users.update');
            Route::post('/users/{user}/regenerate-password', [\App\Modules\RBAC\Controllers\UsersController::class, 'regeneratePassword'])->middleware('permission:users.edit')->name('users.regenerate-password');
            Route::get('/users/{user}', [\App\Modules\RBAC\Controllers\UsersController::class, 'show'])->middleware('permission:users.view')->name('users.show');
            Route::delete('/users/{user}', [\App\Modules\RBAC\Controllers\UsersController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
            Route::patch('/users/{user}/roles', [\App\Modules\RBAC\Controllers\UsersController::class, 'updateRoles'])->middleware('permission:users.manage-roles')->name('users.update-roles');
            Route::patch('/users/{user}/status', [\App\Modules\RBAC\Controllers\UsersController::class, 'toggleStatus'])->middleware('permission:users.edit')->name('users.toggle-status');
        });

        Route::middleware(['permission:permission.manage'])->group(function () {
            Route::get('/roles-matrix', [\App\Modules\RBAC\Controllers\RolesController::class, 'matrix'])->name('roles.matrix');
            Route::patch('/roles-matrix', [\App\Modules\RBAC\Controllers\RolesController::class, 'updateMatrix'])->name('roles.update-matrix');
        });
    });

    // Queue API & Web routes
    Route::prefix('queue')->name('queue.')->group(function () {
        Route::middleware(['permission:queue.view'])->group(function () {
            Route::get('/', [\App\Modules\Queue\Controllers\TicketController::class, 'index'])->name('index');
            Route::post('/store', [\App\Modules\Queue\Controllers\TicketController::class, 'store'])->middleware('permission:queue.create')->name('store');
            Route::post('/call', [\App\Modules\Queue\Controllers\TicketController::class, 'callNext'])->middleware('permission:queue.call')->name('call');
            Route::patch('/{ticket}/status', [\App\Modules\Queue\Controllers\TicketController::class, 'updateStatus'])->middleware('permission:queue.edit')->name('update-status');
            Route::patch('/{ticket}/room', [\App\Modules\Queue\Controllers\TicketController::class, 'changeRoom'])->middleware('permission:queue.edit')->name('change-room');
            Route::post('/{ticket}/hold', [\App\Modules\Queue\Controllers\TicketController::class, 'hold'])->middleware('permission:ticket_hold')->name('hold');
            Route::post('/{ticket}/resume', [\App\Modules\Queue\Controllers\TicketController::class, 'resume'])->middleware('permission:ticket_resume')->name('resume');
            Route::post('/{ticket}/cancel-hold', [\App\Modules\Queue\Controllers\TicketController::class, 'cancelHold'])->middleware('permission:ticket_hold')->name('cancel-hold');
            Route::post('/{ticket}/cancel', [\App\Modules\Queue\Controllers\TicketController::class, 'cancel'])->middleware('permission:queue.delete')->name('cancel');

            // Advanced-only: drag & drop reorder
            Route::middleware('queue_mode:advanced')->group(function () {
                Route::post('/reorder', [\App\Modules\Queue\Controllers\TicketController::class, 'reorder'])->middleware('permission:queue.edit')->name('reorder');
            });
        });
    });

    // Tickets History & Details
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::middleware(['permission:queue.history'])->group(function () {
            Route::get('/', [\App\Modules\Queue\Controllers\TicketHistoryController::class, 'index'])->name('index');
            Route::get('/{ticket}', [\App\Modules\Queue\Controllers\TicketHistoryController::class, 'show'])->name('show');
        });
    });

    // Activity Logs (Admin only)
    Route::middleware(['permission:activity_logs.view'])->group(function () {
        Route::get('/activity-logs', [\App\Modules\Queue\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // Displays management (Standalone Module)
    Route::prefix('displays')->name('displays.')->group(function () {
        Route::middleware(['permission:displays.view'])->group(function () {
            Route::get('/', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'index'])->name('index');
            Route::post('/', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'store'])->middleware('permission:displays.create')->name('store');
            Route::patch('/{device}', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'update'])->middleware('permission:displays.create')->name('update');
            Route::delete('/{device}', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'destroy'])->middleware('permission:displays.delete')->name('destroy');
            Route::post('/{device}/reset-session', [\App\Modules\Displays\Controllers\DisplayDeviceController::class, 'resetSession'])->middleware('permission:displays.create')->name('reset_session');

            // Display Digital Signage Content Management
            Route::prefix('contents')->name('contents.')->middleware(['permission:display_content.view'])->group(function () {
                Route::get('/', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'index'])->name('index');
                Route::get('/create', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'create'])->middleware('permission:display_content.create')->name('create');
                Route::post('/', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'store'])->middleware('permission:display_content.create')->name('store');
                Route::get('/{content}/edit', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'edit'])->middleware('permission:display_content.edit')->name('edit');
                Route::put('/{content}', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'update'])->middleware('permission:display_content.edit')->name('update');
                Route::delete('/{content}', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'destroy'])->middleware('permission:display_content.delete')->name('destroy');
                Route::post('/{content}/toggle', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'toggle'])->middleware('permission:display_content.edit')->name('toggle');
                Route::post('/{content}/duplicate', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'duplicate'])->middleware('permission:display_content.create')->name('duplicate');
                Route::post('/reorder', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'reorder'])->middleware('permission:display_content.edit')->name('reorder');
                Route::get('/{content}/preview', [\App\Modules\Displays\Controllers\DisplayContentController::class, 'preview'])->name('preview');
            });
        });
    });

    // Services API & Web routes
    Route::prefix('services')->name('services.')->group(function () {
        Route::middleware(['permission:services.view'])->group(function () {
            Route::get('/', [\App\Modules\Services\Controllers\ServiceController::class, 'index'])->name('index');
            Route::post('/', [\App\Modules\Services\Controllers\ServiceController::class, 'store'])->middleware('permission:services.create')->name('store');
            Route::put('/{service}', [\App\Modules\Services\Controllers\ServiceController::class, 'update'])->middleware('permission:services.edit')->name('update');
            Route::delete('/{service}', [\App\Modules\Services\Controllers\ServiceController::class, 'destroy'])->middleware('permission:services.delete')->name('destroy');
        });
    });

    // Rooms API & Web routes
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::middleware(['permission:rooms.view'])->group(function () {
            Route::get('/', [\App\Modules\Rooms\Controllers\RoomController::class, 'index'])->name('index');
            Route::post('/', [\App\Modules\Rooms\Controllers\RoomController::class, 'store'])->middleware('permission:rooms.create')->name('store');
            Route::put('/{room}', [\App\Modules\Rooms\Controllers\RoomController::class, 'update'])->middleware('permission:rooms.edit')->name('update');
            Route::delete('/{room}', [\App\Modules\Rooms\Controllers\RoomController::class, 'destroy'])->middleware('permission:rooms.delete')->name('destroy');
        });
    });

    // Customers API & Web routes (Part of Users/Staff permission or its own?)
    // Let's assume its own 'customers' module for consistency if needed, but for now let's keep it simple or use users.view
 Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/ajax-search', [CustomerController::class, 'ajaxSearch'])
        ->name('ajax-search');

    Route::middleware(['permission:customers.view'])->group(function () {
        Route::get('/', [CustomerController::class, 'index'])
            ->name('index');

        Route::post('/', [CustomerController::class, 'store'])
            ->middleware('permission:customers.create')
            ->name('store');

        Route::put('/{customer}', [CustomerController::class, 'update'])
            ->middleware('permission:customers.edit')
            ->name('update');

        Route::get('/{customer}', [CustomerController::class, 'show'])
            ->name('show');

        Route::delete('/{customer}', [CustomerController::class, 'destroy'])
            ->middleware('permission:customers.delete')
            ->name('destroy');

        Route::patch('/{customer}/vip', [CustomerController::class, 'toggleVip'])
            ->middleware('permission:customers.edit')
            ->name('vip');
    });
});

    // ── Appointments ──────────────────────────────────────────────────────────
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::middleware(['permission:appointments.view'])->group(function () {
            Route::get('/', [\App\Modules\Appointments\Controllers\AppointmentController::class, 'index'])->name('index');
            Route::get('/events', [\App\Modules\Appointments\Controllers\AppointmentController::class, 'events'])->name('events');
            Route::get('/slots-available', [\App\Modules\Appointments\Controllers\AppointmentController::class, 'slots'])->name('slots');
            Route::post('/', [\App\Modules\Appointments\Controllers\AppointmentController::class, 'store'])->middleware('permission:appointments.create')->name('store');
            Route::put('/{appointment}', [\App\Modules\Appointments\Controllers\AppointmentController::class, 'update'])->middleware('permission:appointments.edit')->name('update');
            Route::patch('/{appointment}/checkin', [\App\Modules\Appointments\Controllers\AppointmentController::class, 'checkIn'])->middleware('permission:appointments.edit')->name('checkin');
            Route::patch('/{appointment}/cancel', [\App\Modules\Appointments\Controllers\AppointmentController::class, 'cancel'])->middleware('permission:appointments.edit')->name('cancel');
            Route::delete('/{appointment}', [\App\Modules\Appointments\Controllers\AppointmentController::class, 'destroy'])->middleware('permission:appointments.delete')->name('destroy');
        });
    });

    // ── Appointment Slots (templates) ─────────────────────────────────────────
    Route::prefix('appointment-slots')->name('appointment-slots.')->group(function () {
        Route::middleware(['permission:appointment_slots.view'])->group(function () {
            Route::get('/', [\App\Modules\Appointments\Controllers\AppointmentSlotController::class, 'index'])->name('index');
            Route::post('/', [\App\Modules\Appointments\Controllers\AppointmentSlotController::class, 'store'])->middleware('permission:appointment_slots.create')->name('store');
            Route::put('/{slot}', [\App\Modules\Appointments\Controllers\AppointmentSlotController::class, 'update'])->middleware('permission:appointment_slots.edit')->name('update');
            Route::delete('/{slot}', [\App\Modules\Appointments\Controllers\AppointmentSlotController::class, 'destroy'])->middleware('permission:appointment_slots.delete')->name('destroy');
        });
    });

    // Company Settings
    Route::get('/settings/company', [\App\Modules\Companies\Controllers\CompanySettingsController::class, 'index'])->middleware('permission:settings.view')->name('settings.company');
    Route::post('/settings/company', [\App\Modules\Companies\Controllers\CompanySettingsController::class, 'update'])->middleware('permission:settings.edit')->name('settings.company.update');
    Route::get('/settings/company/print-qr', [\App\Modules\Companies\Controllers\CompanySettingsController::class, 'printQr'])->middleware('permission:settings.view')->name('settings.company.print-qr');

    // Analytics
    Route::middleware(['permission:analytics.view'])->prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [\App\Modules\Reports\Controllers\AnalyticsController::class, 'index'])->name('index');
        Route::get('/staff', [\App\Modules\Reports\Controllers\AnalyticsController::class, 'staffDetail'])->name('staff');
        Route::get('/services', [\App\Modules\Reports\Controllers\AnalyticsController::class, 'servicesDetail'])->name('services');
        Route::get('/wait-times', [\App\Modules\Reports\Controllers\AnalyticsController::class, 'waitTimesDetail'])->name('wait-times');
        Route::get('/export', [\App\Modules\Reports\Controllers\AnalyticsController::class, 'exportCsv'])->name('export');
    });

    // WhatsApp Chat
    Route::prefix('chat')->name('whatsapp.chat.')->group(function () {
        Route::middleware(['permission:whatsapp.view'])->group(function () {
            Route::get('/', [\App\Modules\WhatsApp\Controllers\ChatController::class, 'index'])->name('index');
            Route::get('/{phone}', [\App\Modules\WhatsApp\Controllers\ChatController::class, 'show'])->name('show');
            Route::get('/{phone}/messages', [\App\Modules\WhatsApp\Controllers\ChatController::class, 'fetchMessages'])->name('messages');
            Route::post('/send', [\App\Modules\WhatsApp\Controllers\ChatController::class, 'send'])->middleware('permission:whatsapp.send')->name('send');
        });
    });
});

// WhatsApp Webhook (Public)
Route::match(['get', 'post'], '/webhook/whatsapp', [\App\Modules\WhatsApp\Controllers\WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/webhook/whatsapp', [\App\Modules\WhatsApp\Controllers\WhatsAppWebhookController::class, 'handle'])->name('whatsapp.webhook.handle');

Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/offline', function () {
    return view('errors.offline');
})->name('offline');

Route::get('/', function () {
    return view('landing');
})->name('landing');

// ── System Admin: Payments, Subscriptions, Invoices ─────────────────────────
Route::middleware(['auth', 'system_admin'])->prefix('admin')->name('admin.')->group(function () {

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'index'])->name('index');
        Route::post('/{payment}/approve', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'approve'])->name('approve');
        Route::post('/{payment}/reject', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'reject'])->name('reject');
        Route::post('/{payment}/refund', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'refund'])->name('refund');
        Route::get('/{payment}/receipt', [\App\Modules\Payments\Controllers\AdminPaymentController::class, 'downloadReceipt'])->name('receipt');
    });

    // Subscriptions
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [\App\Modules\Payments\Controllers\AdminSubscriptionController::class, 'index'])->name('index');
        Route::post('/{subscription}/activate', [\App\Modules\Payments\Controllers\AdminSubscriptionController::class, 'activate'])->name('activate');
        Route::post('/{subscription}/suspend', [\App\Modules\Payments\Controllers\AdminSubscriptionController::class, 'suspend'])->name('suspend');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [\App\Modules\Payments\Controllers\AdminInvoiceController::class, 'index'])->name('index');
        Route::get('/{invoice}', [\App\Modules\Payments\Controllers\AdminInvoiceController::class, 'show'])->name('show');
    });

    // Coupons
    Route::post('/coupons/bulk-actions', [\App\Modules\Coupons\Controllers\AdminCouponController::class, 'bulkActions'])->name('coupons.bulk-actions');
    Route::get('/coupons/export-csv', [\App\Modules\Coupons\Controllers\AdminCouponController::class, 'exportCsv'])->name('coupons.export-csv');
    Route::get('/coupons/export-pdf', [\App\Modules\Coupons\Controllers\AdminCouponController::class, 'exportReportPdf'])->name('coupons.export-pdf');
    Route::get('/coupons/generate-code', [\App\Modules\Coupons\Controllers\AdminCouponController::class, 'generateCodeAjax'])->name('coupons.generate-code');
    Route::post('/coupons/{coupon}/duplicate', [\App\Modules\Coupons\Controllers\AdminCouponController::class, 'duplicate'])->name('coupons.duplicate');
    Route::resource('coupons', \App\Modules\Coupons\Controllers\AdminCouponController::class);
});

// ── Help Center & User Documentation System ──────────────────────────────
Route::middleware(['auth', 'subscription.valid'])->prefix('help')->name('help.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HelpCenterController::class, 'index'])->name('index');
    Route::get('/api/contextual', [\App\Http\Controllers\HelpCenterController::class, 'contextual'])->name('api.contextual');
    Route::get('/api/search', [\App\Http\Controllers\HelpCenterController::class, 'search'])->name('api.search');
    Route::post('/onboarding/complete', [\App\Http\Controllers\HelpCenterController::class, 'completeOnboarding'])->name('onboarding.complete');
    Route::get('/{category}/{slug}', [\App\Http\Controllers\HelpCenterController::class, 'show'])->name('show');
});

// ── Company Admin: Billing & Subscription Management ─────────────────────────
Route::middleware(['auth'])->prefix('billing')->name('billing.')->group(function () {
    Route::middleware(['permission:billing.view'])->group(function () {
        Route::get('/', [\App\Modules\Payments\Controllers\CompanyBillingController::class, 'index'])->name('index');
    });
    Route::middleware(['permission:billing.edit'])->group(function () {
        Route::post('/subscribe', [\App\Modules\Payments\Controllers\CompanyBillingController::class, 'subscribe'])->name('subscribe');
        Route::post('/receipt', [\App\Modules\Payments\Controllers\CompanyBillingController::class, 'uploadReceipt'])->name('upload_receipt');
    });
    Route::get('/invoices/{invoice}', [\App\Modules\Payments\Controllers\CompanyBillingController::class, 'showInvoice'])->name('invoice.show');
    Route::post('/coupon/validate', [\App\Modules\Coupons\Controllers\CompanyCouponController::class, 'validateCoupon'])->name('coupon.validate');
});

// ── Experimental Sandbox: Packages & Add-ons Prototype ───────────────────────
// Completely isolated from production billing/subscriptions; zero database modifications.
Route::middleware(['auth'])->prefix('experimental')->name('experimental.')->group(function () {
    Route::get('/packages', [\App\Modules\Subscriptions\Controllers\PackageExperimentController::class, 'index'])->name('packages.index');
});
