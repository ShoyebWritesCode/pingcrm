<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\OrganizationsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\InvoicesController;
use Illuminate\Support\Facades\Route;

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

// Auth

Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login')
    ->middleware('guest');

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->name('login.store')
    ->middleware('guest');

Route::delete('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

// Dashboard

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

// Users

Route::get('users', [UsersController::class, 'index'])
    ->name('users')
    ->middleware('auth');

Route::get('users/create', [UsersController::class, 'create'])
    ->name('users.create')
    ->middleware('auth');

Route::post('users', [UsersController::class, 'store'])
    ->name('users.store')
    ->middleware('auth');

Route::get('users/{user}/edit', [UsersController::class, 'edit'])
    ->name('users.edit')
    ->middleware('auth');

Route::put('users/{user}', [UsersController::class, 'update'])
    ->name('users.update')
    ->middleware('auth');

Route::delete('users/{user}', [UsersController::class, 'destroy'])
    ->name('users.destroy')
    ->middleware('auth');

Route::put('users/{user}/restore', [UsersController::class, 'restore'])
    ->name('users.restore')
    ->middleware('auth');

// Organizations

Route::get('organizations', [OrganizationsController::class, 'index'])
    ->name('organizations')
    ->middleware('auth');

Route::get('organizations/create', [OrganizationsController::class, 'create'])
    ->name('organizations.create')
    ->middleware('auth');

Route::post('organizations', [OrganizationsController::class, 'store'])
    ->name('organizations.store')
    ->middleware('auth');

Route::get('organizations/{organization}/edit', [OrganizationsController::class, 'edit'])
    ->name('organizations.edit')
    ->middleware('auth');

Route::put('organizations/{organization}', [OrganizationsController::class, 'update'])
    ->name('organizations.update')
    ->middleware('auth');

Route::delete('organizations/{organization}', [OrganizationsController::class, 'destroy'])
    ->name('organizations.destroy')
    ->middleware('auth');

Route::put('organizations/{organization}/restore', [OrganizationsController::class, 'restore'])
    ->name('organizations.restore')
    ->middleware('auth');

Route::post('organizations/import-csv', [OrganizationsController::class, 'importCsv'])
    ->name('organizations.import-csv')
    ->middleware('auth');

// Contacts

Route::any('contacts/home', [ContactsController::class, 'index'])
    ->name('contacts')
    ->middleware('auth');

// Route::post('contacts/filter', [ContactsController::class, 'setFilter'])
//     ->name('contacts')
//     ->middleware('auth');

Route::get('contacts/create', [ContactsController::class, 'create'])
    ->name('contacts.create')
    ->middleware('auth');

Route::post('contacts', [ContactsController::class, 'store'])
    ->name('contacts.store')
    ->middleware('auth');

Route::get('contacts/{contact}/edit', [ContactsController::class, 'edit'])
    ->name('contacts.edit')
    ->middleware('auth');

Route::put('contacts/{contact}', [ContactsController::class, 'update'])
    ->name('contacts.update')
    ->middleware('auth');

Route::put('contacts/{contact}/custom-columns', [ContactsController::class, 'updateCustomColumns'])
    ->name('contacts.update-custom-columns')
    ->middleware('auth');

Route::delete('contacts/{columnId}/delete', [ContactsController::class, 'deleteCustomColumns'])
    ->name('contacts.delete-custom-columns')
    ->middleware('auth');

Route::delete('contacts/{contact}', [ContactsController::class, 'destroy'])
    ->name('contacts.destroy')
    ->middleware('auth');

Route::delete('contacts/delete/{ids}', [ContactsController::class, 'deleteSelected'])
    ->name('contacts.deleteSelected')
    ->middleware('auth');
Route::get('contacts/delall', [ContactsController::class, 'deleteAll'])
    ->name('contacts.deleteAll')
    ->middleware('auth');
Route::put('contacts/{contact}/restore', [ContactsController::class, 'restore'])
    ->name('contacts.restore')
    ->middleware('auth');


Route::post('contacts/import-csv', [ContactsController::class, 'importCsv'])
    ->name('contacts.import-csv')
    ->middleware('auth');

Route::post('contacts/add-column', [ContactsController::class, 'addColumn'])
    ->name('contacts.column')
    ->middleware('auth');

// Reports

Route::get('reports', [ReportsController::class, 'index'])
    ->name('reports')
    ->middleware('auth');

// Images

Route::get('/img/{path}', [ImagesController::class, 'show'])
    ->where('path', '.*')
    ->name('image');



// Products

Route::get('products', [ProductsController::class, 'index'])
    ->name('products')
    ->middleware('auth');
Route::get('products/create', [ProductsController::class, 'create'])
    ->name('products.create')
    ->middleware('auth');
Route::post('products', [ProductsController::class, 'store'])
    ->name('products.store')
    ->middleware('auth');
Route::get('products/{product}/edit', [ProductsController::class, 'edit'])
    ->name('products.edit')
    ->middleware('auth');

Route::put('products/{product}', [ProductsController::class, 'update'])
    ->name('products.update')
    ->middleware('auth');


// Invoices
Route::get('invoices', [InvoicesController::class, 'index'])
    ->name('invoices')
    ->middleware('auth');
Route::get('invoices/create', [InvoicesController::class, 'create'])
    ->name('invoices.create')
    ->middleware('auth');
Route::post('invoices', [InvoicesController::class, 'store'])
    ->name('invoices.store')
    ->middleware('auth');
Route::get('invoices/{invoice}/view', [InvoicesController::class, 'view'])
    ->name('invoices.view')
    ->middleware('auth');
Route::get('invoices/{invoice}/download', [InvoicesController::class, 'download'])
    ->name('invoices.download')
    ->middleware('auth');
Route::get('organizations/{organizationId}/contacts', [InvoicesController::class, 'getContacts'])
    ->name('organizations.contacts')
    ->middleware('auth');

//Column Visibility
Route::post('organizations/column', [OrganizationsController::class, 'saveColumnVisibility'])
    ->name('organizations.column')
    ->middleware('auth');
