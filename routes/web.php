<?php

use App\Http\Livewire\Admin\Users;
use App\Http\Livewire\Admin\Projects;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Admin\Platforms;
use App\Http\Livewire\Admin\TaskTypes;
use App\Http\Livewire\Admin\Organizations;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::post('webhooks/stripe', [WebhookController::class, 'handleWebhook']);

require __DIR__.'/auth.php';


// Route::middleware(['role:superadmin'])->prefix('admin')->group(function() {
//     Route::get('users', Users::class)->name('admin.users');

//     Route::get('organizations', Organizations::class)->name('admin.organizations');
//     Route::get('organizations/{organization_id}/projects', Projects::class)->name('admin.projects');

//     Route::get('task-types', TaskTypes::class)->name('admin.task_types');
//     Route::get('platforms', Platforms::class)->name('admin.platforms');
// });
