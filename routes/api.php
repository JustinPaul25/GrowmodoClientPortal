<?php

use App\Models\CompanyType;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\EmployeeCount;
use App\Models\SubscriptionTalent;
use App\Models\SubscriptionPlanType;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ChoiceController;
use App\Http\Controllers\Api\OptionController;
use App\Http\Controllers\Api\SignUpController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\TaskTypeController;
use App\Http\Controllers\Api\Files\FileController;
use App\Http\Controllers\Api\CompanyTypeController;
use App\Http\Controllers\Api\ProjectTypeController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\Files\BrandsController;
use App\Http\Controllers\Api\Files\FolderController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\BrandCategoryController;
use App\Http\Controllers\Api\ThirdPartyRequestSender;
use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\OrganizationUserController;
use App\Http\Controllers\Api\Requests\TaskFormController;
use App\Http\Controllers\Api\Files\OrganizationsController;
use App\Http\Controllers\Api\Requests\ProjectFormController;
use App\Http\Controllers\Api\SocialMediaPlatformsController;
use App\Http\Controllers\Api\Admin\DynamicQuestionController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\Admin\SignupController as AdminSignupController;
use App\Http\Controllers\Api\GoogleDrive\FileController as GoogleFileController;
use App\Http\Controllers\Api\Admin\SubscriptionController as AdminSubscriptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(
    [
        'middleware' => ['json.response'],
        //'throttle:20', 'cors'],
        'as' => 'api.',
    ],
    function () {
        // Auth
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/login-token', [AuthController::class, 'loginViaToken'])->name('login.token');
        Route::get('/me', [AuthController::class, 'me'])->name('me')->middleware(['auth:api']);
        Route::get('/user', [AuthController::class, 'me'])->name('user.me')->middleware(['auth:api']);
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware(['auth:api']);
        Route::post('/update-password', [AuthController::class, 'updateMyPassword'])->name('update-password')->middleware(['auth:api']);

        // Miscs
        Route::get('/miscs/choices', [ChoiceController::class, 'index'])->name('miscs.choices')->middleware([]);
        Route::get('/miscs/roles', [ChoiceController::class, 'roles'])->name('miscs.roles'); //->middleware(['auth:api']);
        Route::get('/miscs/roles-invite-user', [ChoiceController::class, 'rolesInviteUser'])->name('miscs.roles-invite-user');

        Route::get('/miscs/task-dirs', [TaskTypeController::class, 'index'])->name('miscs.task-dirs'); //->middleware(['auth:api']);
        Route::get('/miscs/task-dirs/categories', [TaskTypeController::class, 'categories'])->name('miscs.task-dirs.categories'); //->middleware(['auth:api']);
        Route::get('/miscs/task-dirs/{taskType}/questions', [TaskTypeController::class, 'questions'])
            ->name('miscs.task-dirs.questions');
        Route::get('/miscs/task/quick-request', [TaskTypeController::class, 'quickRequest'])->name('miscs.task.quick-request');

        Route::get('/miscs/project-dirs', [ProjectTypeController::class, 'index'])->name('miscs.project-dirs'); //->middleware(['auth:api']);
        Route::get('/miscs/project-dirs/categories', [ProjectTypeController::class, 'categories'])->name('miscs.project-dirs.categories'); //->middleware(['auth:api']);
        Route::get('/miscs/project-dirs/{projectType}/questions', [ProjectTypeController::class, 'questions'])
            ->name('miscs.project-dirs.questions');


        Route::get('/admin/project-dirs/categories/{id}', [ProjectTypeController::class, 'categoriesAdmin'])->name('admin.project-dirs.category');
        Route::get('/admin/project-dirs/platforms/{id}', [ProjectTypeController::class, 'platformsAdmin'])->name('admin.project-dirs.platform');
        Route::get('/admin/dynamic-questions', [DynamicQuestionController::class, 'list'])->name('admin.dynamic-questions.list');

        Route::get('/admin/task-dirs/categories/{id}', [TaskTypeController::class, 'categoriesAdmin'])->name('admin.project-dirs.category');
        Route::get('/admin/task-dirs/platforms/{id}', [TaskTypeController::class, 'platformsAdmin'])->name('admin.project-dirs.platform');

        Route::get('/dynamic-question/{dynamicQuestion}', [DynamicQuestionController::class, 'show'])->name('dynamic-question.show')->middleware(['auth:api']);

        Route::get('/miscs/social-platforms', [SocialMediaPlatformsController::class, 'index'])->name('miscs.social-platforms'); //->middleware(['auth:api']);
        Route::get('/miscs/platforms', [PlatformController::class, 'index'])->name('miscs.platforms'); //->middleware(['auth:api']);

        Route::get('/miscs/subscriptions/plans', [SubscriptionController::class, 'plans'])->name('miscs.subscriptions.plans');
        // Route::get('/miscs/subscriptions/plan-types', [SubscriptionController::class, 'planTypes'])->name('miscs.subscriptions.plan-types');
        // Route::get('/miscs/subscriptions/talents', [SubscriptionController::class, 'talents'])->name('miscs.subscriptions.talents');

        Route::get('/miscs/options', [OptionController::class, 'index'])->name('miscs.options')->middleware(['auth:api']);
        Route::get('/miscs/external-links', [OptionController::class, 'index'])->name('miscs.external-links'); //->middleware(['auth:api']);
        Route::get('/miscs/testimonials', [TestimonialController::class, 'index'])->name('miscs.testimonials'); //->middleware(['auth:api']);

        Route::get('/miscs/brand-categories', [BrandCategoryController::class, 'index'])->name('miscs.brand-categories'); //->middleware(['auth:api']);

        Route::get('/miscs/employee-counts', function () {
            return JsonResponse::make(EmployeeCount::all());
        })->name('miscs.employee-counts'); //->middleware(['auth:api']);

        Route::get('/miscs/company-types', [CompanyTypeController::class, 'index'])->name('miscs.company-types'); //->middleware(['auth:api']);

        // Subscriptions
        Route::get('/miscs/subscriptions/billing-types', function () {
            return JsonResponse::make(SubscriptionPlanType::all());
        })->name('miscs.subscriptions.billing-types');
        Route::get('/miscs/subscriptions/talents', function (Request $request) {
            return JsonResponse::make(SubscriptionTalent::all());
        })->name('miscs.subscriptions.talents');

        Route::post('/signup', [SignUpController::class, 'step1'])->name('signup.step1');
        Route::post('/signup/verify-email', [SignUpController::class, 'verifyEmail'])->name('signup.verify-email');
        Route::post('/signup/resend-email-verify', [SignUpController::class, 'resendEmailVerify'])
            ->middleware('throttle:1,1')->name('signup.resend-email-verify');
        Route::post('/signup/step2', [SignUpController::class, 'step2'])->name('signup.step2')->middleware(['auth:api']);
        Route::post('signup/step3', [SignUpController::class, 'step3'])->name('signup.step3')->middleware(['auth:api']);
        Route::post('/accept-invitation', [SignUpController::class, 'invitationEmailVerify'])->name('signup.invitation-email-verify');

        Route::get('organization/{id}/users', [OrganizationUserController::class, 'orgusers'])->name('organization.users.orgusers')->middleware(['auth:api']);
    }
);

Route::group(
    [
        'middleware' => ['auth:api', 'status.checker'], //'throttle:20', 'cors'],
        'as' => 'api.',
    ],
    function () {
        // Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/organization/{organization}/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/organization/{organization}/with-archived/brands', [BrandController::class, 'indexWithArchived'])->name('brands.index.with-archived');
        Route::get('/organization/{organization}/archived/brands', [BrandController::class, 'indexArchived'])->name('brands.index.archived');
        Route::get('/organization/{organization}/brands/{brand}', [BrandController::class, 'show'])->name('brands.show');
        Route::put('/organization/{organization}/brands/{brand}', [BrandController::class, 'update'])->name('organization.brands.update.old');

        Route::post('/organization/{organization}/brands', [BrandController::class, 'store'])->name('organization.brands.store');
        Route::delete('/organization/{organization}/brands/{id}', [BrandController::class, 'destroy'])->name('organization.brands.delete');
        Route::put('/organization/{organization}/brands/{brand}/archive', [BrandController::class, 'archive'])->name('organization.brands.archive');
        Route::put('/organization/{organization}/brands/{id}/restore', [BrandController::class, 'restore'])->name('organization.brands.restore');

        Route::post('/organization/{organization}/brands/{brand}/upload', [BrandController::class, 'upload'])->name('organization.brands.upload');
        Route::put('/organization/{organization}/brands/{brand}/update', [BrandController::class, 'update'])->name('organization.brands.update');
        Route::put('/organization/{organization}/brands/{brand}/googlefonts', [BrandController::class, 'googlefonts'])->name('organization.brands.googlefonts');
        Route::put('/organization/{organization}/brands/{brand}/color', [BrandController::class, 'color'])->name('organization.brands.color');
        Route::put('/organization/{organization}/brands/{brand}/social-account', [BrandController::class, 'socialAccounts'])->name('organization.brands.social-account');
        Route::put('/organization/{organization}/brands/{brand}/avatar', [BrandController::class, 'updateAvatar'])->name('organization.brands.avatar');
        Route::delete('/organization/{organization}/brands/{brand}/file/{upload}', [BrandController::class, 'deleteFile'])->name('organization.brands.delete-file');

        Route::post('/brands', [BrandController::class, 'step1'])->name('brands.step1');
        Route::post('/brands/{brand}/step2', [BrandController::class, 'step2'])->name('brands.step2');
        Route::post('/brands/{brand}/step3', [BrandController::class, 'step3'])->name('brands.step3');
        Route::get('/brands', [BrandController::class, 'index'])->name('brands');

        // Route::post('/mirror/asana', [ThirdPartyRequestSender::class, 'asana'])->name('mirror.asana');
        // Route::post('/mirror/apideck', [ThirdPartyRequestSender::class, 'apideck'])->name('mirror.apideck');
        Route::get('/mirror/googlefonts', [ThirdPartyRequestSender::class, 'googlefonts'])->name('mirror.googlefonts');

        // Profile, Account
        Route::post('/account/edit', [AccountController::class, 'editAccount'])->name('account.edit');
        Route::post('/company/edit', [OrganizationController::class, 'update'])->name('company.edit');

        //
        Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');

        // Organizations
        Route::get('organization', [OrganizationController::class, 'index'])->name('organization.index');
        Route::get('organization/{id}/show', [OrganizationController::class, 'show'])->name('organization.show');

        // Organization Users
        Route::get('organization/users/{id}', [OrganizationUserController::class, 'show'])->name('organization.users.show');
        Route::get('organization/users', [OrganizationUserController::class, 'index'])->name('organization.users.index');
        Route::post('organization/users', [OrganizationUserController::class, 'store'])->name('organization.users.store');
        Route::post('organization/users/resend-invitation', [OrganizationUserController::class, 'resendInvitation'])->name('organization.users.resend-invitation')
            ->middleware('throttle:1,1');
        Route::post('organization/users/{id}', [OrganizationUserController::class, 'update'])->name('organization.users.update');
        Route::delete('organization/users/{id}', [OrganizationUserController::class, 'delete'])->name('organization.users.delete');

        // Route::get('onboarding', [OrganizationUserController::class, 'onboardingShow'])->name('organization.users.onboarding.show');
        Route::post('onboarding', [OrganizationUserController::class, 'onboardingStore'])->name('organization.users.onboarding.store');
        Route::post('offboarding', [OrganizationUserController::class, 'offboardingStore'])->name('organization.users.offboarding.store');

        // List Files and Directories
        Route::get('/files/organizations/{organization}', [OrganizationsController::class, 'index'])->name('files.organizations');
        // Route::get('/files/organizations/{organization}/brands/{brand}', [BrandsController::class, 'index'])->name('files.organizations.brands');
        // Route::get('/files/organizations/{organization}/projects/{project}', [ProjectsController::class, 'index'])->name('files.organizations.projects');
        // Route::get('/files/organizations/{organization}/tasks/{task}', [TasksController::class, 'index'])->name('files.organizations.tasks');

        // Tasks
        Route::get('/organizations/{organization}/tasks', [TaskFormController::class, 'index'])->name('organizations.tasks.index');
        Route::post('/organizations/{organization}/tasks', [TaskFormController::class, 'store'])->name('organizations.tasks.store');
        Route::get('/organizations/{organization}/tasks/{task}', [TaskFormController::class, 'show'])->name('organizations.tasks.show');
        Route::put('/organizations/{organization}/tasks/{task}', [TaskFormController::class, 'update'])->name('organizations.tasks.update');

        // Projects
        Route::get('/organizations/{organization}/projects', [ProjectFormController::class, 'index'])->name('organizations.projects.index');
        Route::post('/organizations/{organization}/projects', [ProjectFormController::class, 'store'])->name('organizations.projects.store');
        Route::get('/organizations/{organization}/projects/{project}', [ProjectFormController::class, 'show'])->name('organizations.projects.show');
        Route::put('/organizations/{organization}/projects/{project}', [ProjectFormController::class, 'update'])->name('organizations.projects.update');


        // Pusher Auth
        Route::post('/pusher/auth', function (Request $request) {
            $current_user = $request->user();
            if (empty($current_user->id))
                return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

            $pusher = new Pusher\Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'encrypted' => true,
                    'useTLS' => true,
                ]
            );

            $channelName = $request->channel_name;
            $socketId = $request->socket_id;
            // $auth = $pusher->socket_auth($channelName, $socketId);
            $auth = $pusher->authorizeChannel($channelName, $socketId);

            return json_decode($auth);
        });
    }
);

Route::middleware(['auth:api', 'role:organization_admin|organization_billing', 'status.checker'])->group(function () {
    Route::post('/change-plan/maintenance', [SubscriptionController::class, 'maintenance'])->name('change-plan.maintenance');
    Route::post('/change-plan/pause', [SubscriptionController::class, 'pause'])->name('change-plan.pause');
    Route::post('/change-plan/resume', [SubscriptionController::class, 'resume'])->name('change-plan.resume');
    Route::post('/change-plan/request', [SubscriptionController::class, 'request'])->name('change-plan.request');
});

Route::middleware(['auth:api', 'role:organization_admin', 'status.checker'])->group(function () {
    Route::post('organization', [OrganizationController::class, 'store'])->name('organization.store');
    Route::post('organization/{id}/edit', [OrganizationController::class, 'update'])->name('organization.update');
    Route::delete('organization/{id}', [OrganizationController::class, 'delete'])->name('organization.delete');
});

Route::middleware(['auth:api', 'status.checker'])->group(function () {
    Route::get('/file/{upload}', [FileController::class, 'file'])->name('file');
    Route::put('/file/{upload}/update-name', [FileController::class, 'updateName'])->name('file.update-name');
    Route::post('/file/temp', [FileController::class, 'uploadTemp'])->name('file.store.temp');

    Route::post('/file/organizations/{organization}', [FileController::class, 'store'])->name('file.organization.store');
    Route::delete('/file/{file}/organizations/{organization}', [FileController::class, 'destroy'])->name('file.organization.destroy');
    Route::put('/file/{file}/organizations/{organization}/transfer', [FileController::class, 'transfer'])->name('file.organization.transfer');
    Route::put('/file/{file}/organizations/{organization}/rename', [FileController::class, 'rename'])->name('file.organization.rename');
    Route::get('/file/{file}/organizations/{organization}/link', [FileController::class, 'link'])->name('file.organization.link');

    Route::post('/folder/organizations/{organization}', [FolderController::class, 'store'])->name('folder.store.organization');
    Route::delete('/folder/{folder}/organizations/{organization}', [FolderController::class, 'destroy'])->name('folder.delete.organization');
    Route::put('/folder/{folder}/organizations/{organization}/transfer', [FolderController::class, 'transfer'])->name('folder.organization.transfer');
    Route::put('/folder/{folder}/organizations/{organization}/rename', [FolderController::class, 'rename'])->name('folder.organization.rename');
    Route::get('/folder/{folder}/organizations/{organization}/child', [FolderController::class, 'child'])->name('folder.organization.child');
    Route::get('/folder/{folder}/organizations/{organization}/files', [FolderController::class, 'files'])->name('folder.organization.files');
});

Route::middleware(['auth:api', 'role:superadmin'])->group(function () {
    // Super Admin | Users
    Route::post('/admin/register', [UserController::class, 'register'])->name('admin.register');
    Route::post('/admin/register-user', [UserController::class, 'registerUser'])->name('admin.register-user');
    Route::post('/admin/update-user/{id}', [UserController::class, 'updateUser'])->name('admin.update-user');
    Route::delete('/admin/user/{user}/delete', [AdminUserController::class, 'destroy'])->name('admin.user.delete');
    Route::put('/admin/signup/{user}/confirmation', [AdminSignupController::class, 'confirmation'])->name('admin.signup.confirmation');
    Route::get('/admin/user/{user}/status', [UserController::class, 'status'])->name('admin.user.status');

    // Super Admin | Organizations
    Route::post('/admin/organization', [OrganizationController::class, 'storeAdmin'])->name('admin.organization.store');
    Route::post('/admin/organization/{id}', [OrganizationController::class, 'updateAdmin'])->name('admin.organization.update');
    Route::delete('/admin/organization/{id}', [OrganizationController::class, 'deleteAdmin'])->name('admin.organization.delete');

    // Super Admin | Task Dir
    Route::post('/admin/task-dirs', [TaskTypeController::class, 'storeAdmin'])->name('admin.task-dirs.store');
    Route::post('/admin/task-dirs/{id}', [TaskTypeController::class, 'updateAdmin'])->name('admin.task-dirs.update');
    Route::delete('/admin/task-dirs/{id}', [TaskTypeController::class, 'deleteAdmin'])->name('admin.task-dirs.delete');


    // Super Admin | Project Dir
    Route::post('/admin/project-dirs', [ProjectTypeController::class, 'storeAdmin'])->name('admin.project-dirs.store');
    Route::post('/admin/project-dirs/{id}', [ProjectTypeController::class, 'updateAdmin'])->name('admin.project-dirs.update');
    Route::delete('/admin/project-dirs/{id}', [ProjectTypeController::class, 'deleteAdmin'])->name('admin.project-dirs.delete');
    Route::post('/admin/project-dirs/platforms/{id}', [ProjectTypeController::class, 'platformsAdmin'])->name('admin.project-dirs.platform');

    // Super Admin | Brand Cateogries
    Route::post('/admin/brand-cats', [BrandCategoryController::class, 'storeAdmin'])->name('admin.brand-cats.store');
    Route::post('/admin/brand-cats/{id}', [BrandCategoryController::class, 'updateAdmin'])->name('admin.brand-cats.update');
    Route::delete('/admin/brand-cats/{id}', [BrandCategoryController::class, 'deleteAdmin'])->name('admin.brand-cats.delete');

    Route::post('/admin/project-dirs', [ProjectTypeController::class, 'store'])->name('admin.project-dirs.store');

    Route::post('/admin/dynamic-questions', [DynamicQuestionController::class, 'store'])->name('admin.dynamic-questions.store');
    Route::put('/admin/dynamic-questions/{dynamicQuestion}', [DynamicQuestionController::class, 'update'])->name('admin.dynamic-questions.update');
    Route::delete('/admin/dynamic-questions/{dynamicQuestion}', [DynamicQuestionController::class, 'destroy'])->name('admin.dynamic-questions.destroy');

    // Super Admin | Subscription
    Route::post('/admin/subscription/approve-request', [AdminSubscriptionController::class, 'approveRequest'])->name('admin.subscription.approve-request');
    Route::post('/admin/subscription/cancel', [AdminSubscriptionController::class, 'cancel'])->name('admin.subscription.cancel');
    Route::post('/admin/subscription/resume', [AdminSubscriptionController::class, 'resume'])->name('admin.subscription.resume');
    Route::post('/admin/subscription/maintenance', [AdminSubscriptionController::class, 'maintenance'])->name('admin.subscription.maintenance');

    // Super Admin | Brands
    Route::post('/admin/organization/{organization}/brands', [AdminBrandController::class, 'store'])->name('admin.organization.brands.store');
    Route::post('/admin/organization/{organization}/brands/{brand}/upload', [AdminBrandController::class, 'upload'])->name('admin.organization.brands.upload');
    Route::put('/admin/organization/{organization}/brands/{brand}/update', [AdminBrandController::class, 'update'])->name('admin.organization.brands.update');
    Route::put('/admin/organization/{organization}/brands/{brand}/color', [AdminBrandController::class, 'color'])->name('admin.organization.brands.color');
    Route::put('/admin/organization/{organization}/brands/{brand}/social-account', [AdminBrandController::class, 'socialAccounts'])->name('admin.organization.brands.social-account');
    Route::delete('/admin/organization/{organization}/brands/{brand}/delete', [AdminBrandController::class, 'destroy'])->name('admin.organization.brands.delete');

    Route::get('/admin/new-customers', [CustomerController::class, 'newCustomer'])->name('admin.new-customer');
});


// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::group(function() {
//     Route::apiResource('tasks', TaskController::class);
// })->middleware('auth:api');

// Route::group(function() {
// Route::apiResource('tasks', TaskController::class);

// })->middleware('auth:api');


Route::middleware(['auth:api'])->group(function () {
    // Route::apiResource('organizations', OrganizationController::class);
    // Route::apiResource('organizations.tasks', TaskController::class);

    // Route::prefix('organization')->group(function() {

    // });

});


// Route::post('test-upload', function (Request $request) {
//     $file = Storage::put('avatars/1', $request->file);

//     return $file;
// });

// Route::group(['prefix' => 'google-drive'], function () {
//     Route::get('/organization/{organization}/folder/{folder}/files', [GoogleFileController::class, 'files'])->name('google-drive.get-files');
//     Route::post('/organization/{organization}/folder/{folder}/upload', [GoogleFileController::class, 'upload'])->name('google.upload-files');
//     Route::put('/organization/{organization}/file/{file}/rename', [GoogleFileController::class, 'rename'])->name('google.file.rename');
//     Route::put('/organization/{organization}/file/{file}/transfer', [GoogleFileController::class, 'transfer'])->name('google.file.transfer');
//     Route::delete('/organization/{organization}/file/{file}', [GoogleFileController::class, 'delete'])->name('google.file.delete');
//     Route::post('/organization/{organization}/file/{file}/share', [GoogleFileController::class, 'share'])->name('google.file.share');
//     Route::post('/organization/{organization}/file/{file}/remove-access', [GoogleFileController::class, 'removeAccess'])->name('google.file.remove-access');

//     //folder
//     Route::post('/organization/{organization}/create-folder', [FolderController::class, 'createFolder'])->name('organization.folder.create');
//     Route::put('/organization/{organization}/folder/{folder}/rename', [FolderController::class, 'renameFolder'])->name('organization.folder.rename');
//     Route::put('/organization/{organization}/folder/{folder}/transfer', [FolderController::class, 'transferFolder'])->name('organization.folder.transfer');
//     Route::delete('/organization/{organization}/folder/{folder}', [FolderController::class, 'deleteFolder'])->name('organization.folder.delete');
//     Route::post('/organization/{organization}/folder/{folder}/share', [FolderController::class, 'shareFolder'])->name('organization.folder.share');
//     Route::post('/organization/{organization}/folder/{folder}/remove-access', [FolderController::class, 'removeAccess'])->name('organization.folder.remove-access');
// });
