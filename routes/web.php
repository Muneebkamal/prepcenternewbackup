<?php

use App\Http\Controllers\DailyInputController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PrepOrderController;
use App\Http\Controllers\ShippingPlanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/clear-cache', function () {
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');

    return "All caches cleared successfully!";
});
Auth::routes();

Route::middleware(['auth'])->group(function () {

    // Route::get('/', function () {
    //     return view('dashboard');
    // });
    Route::get('/', [DailyInputController::class, 'dashboard'])->name('dashboard');

    // Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::resource('employees', EmployeeController::class);

    // user employee merge
    Route::get('/user-emloyee', [EmployeeController::class, 'employeeMerge']);

    // products data
    Route::get('/products-data/merge', [EmployeeController::class, 'productsData']);

    // daily Input data
    Route::get('/daily/input/merge', [EmployeeController::class, 'dailyInputMerge']);

    // daily Input Detail data
    Route::get('/daily-input-details/merge', [EmployeeController::class, 'dailyInputDetailMerge']);
    Route::post('/update-rate', [EmployeeController::class, 'emplpyeeRate']);

    Route::resource('products', ProductsController::class);
    Route::get('/print-label/{fnsk?}', [ProductsController::class,'printLabel']);
    Route::get('/label/{fnsku}/download', [ProductsController::class, 'downloadLabel']);
    Route::get('/getItemsWps', [ProductsController::class,'getItemsWps']);
    Route::post('/product-update-image/{id}', [ProductsController::class,'updateImage'])->name('product.update.image');
    Route::resource('daily-input', DailyInputController::class);
    Route::get('daily-input-detail/{id?}', [DailyInputController::class,'edit2'])->name('daily-input.edit2');
    Route::get('/daily-inputs-current-month', [DailyInputController::class, 'fetchDailyInputs']);
    Route::post('/daily-input-detail', [DailyInputController::class, 'detailStore'])->name('daily.input.detail');
    Route::post('/daily-input-detail-edit{id}', [DailyInputController::class, 'detailEdit'])->name('daily.input.detail.edit');
    Route::post('/daily-input/fnssku', [DailyInputController::class, 'checkFnsku'])->name('daily.input.fnsku');
    Route::post('/daily-input-detail-delete{id}', [DailyInputController::class, 'delete'])->name('daily.input.detail.delete');
    Route::post('/update-daily-input-date', [DailyInputController::class, 'updateDate'])->name('daily.input.updateDate');
    Route::get('/edit-daily-input-qty/{id?}', [DailyInputController::class, 'editDetailQty'])->name('daily.input.editDetailQty');

    Route::get('/import/products', [ProductsController::class, 'importProducts'])->name('import.products');
    Route::post('/upload/products', [ProductsController::class, 'upload'])->name('csv.upload');
    Route::post('/saveimp/products', [ProductsController::class, 'saveColumns'])->name('csv.saveColumns');
    Route::get('products-nenw', [ProductsController::class, 'getProducts'])->name('products.data');
    Route::get('products-merge', [ProductsController::class, 'getProductsMerge'])->name('products.data.merge');

    Route::get('/delete-duplicate', [ProductsController::class, 'deleteDuplicate'])->name('delete-duplicate');
    
    Route::get('/import/table', [ProductsController::class, 'importTable'])->name('import.table');

    Route::post('/import/csv', [ProductsController::class, 'uploadCSV'])->name('import.csv');
    Route::post('/import/walmart', [ProductsController::class, 'uploadWalmart'])->name('import.walmart');
    Route::get('/merge-products', [ProductsController::class, 'mergeMenu'])->name('merge.product.menu');

    Route::get('/report-by-employee', [DailyInputController::class, 'reportByEmployee'])->name('report.by.employee');
    Route::post('/employee-search', [DailyInputController::class, 'reportByEmployee'])->name('employee.search');

    Route::get('/report-by-time', [DailyInputController::class, 'reportByTime'])->name('report.by.time');
    Route::post('/time-search', [DailyInputController::class, 'reportByTime'])->name('time.search');

    Route::get('/monthly-summary', [DailyInputController::class, 'monthlySummary'])->name('monthly.summary');
    Route::post('/summary-search', [DailyInputController::class, 'monthlySummary'])->name('summary.search');
    Route::get('/monthly-product-report', [ReportController::class, 'monthlyProductReport'])->name('monthly.product.report');
    Route::post('/monthly-product-report-search', [ReportController::class, 'monthlyProductReport'])->name('monthly.product.report.search');

    Route::get('/system-setting', [DailyInputController::class, 'systemSetting'])->name('system.setting');
    Route::post('/system-setting-add', [DailyInputController::class, 'systemSetting'])->name('system.setting.add');
    Route::post('/department-add', [DailyInputController::class, 'depAdd'])->name('department.add');
    Route::get('/get-daily-input-data', [DailyInputController::class, 'getDailyInputData'])->name('getDailyInputData');
    Route::get('/fetch-items', [DailyInputController::class, 'fetchItems'])->name('fetch.items');

    Route::post('/temp-products/merge', [ProductsController::class, 'tempProductMerge'])->name('temp.products.merge');
    
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('expenses/store', [ExpenseController::class, 'store'])->name('expneses.store');
    Route::get('list-category', [ExpenseController::class, 'categoriesList'])->name('categories.list');
    Route::get('categories', [ExpenseController::class, 'catIndex'])->name('categories.index');
    Route::post('categories/store', [ExpenseController::class, 'catStore'])->name('categories.store');
    Route::delete('categories/destroy/{id?}', [ExpenseController::class, 'catDestroy'])->name('categories.destroy');
    Route::get('handleCron', [ExpenseController::class, 'handleCron'])->name('categories.handleCron');
    Route::post('update-options', [SettingsController::class, 'updateOptions'])->name('settings.updateOptions');
    Route::post('delete-option', [SettingsController::class, 'deleteOption'])->name('settings.delete-option');
    Route::post('/edit-option', [SettingsController::class, 'editOption']);
    Route::post('/options/add', [SettingsController::class, 'addOption']);
    Route::post('/options/edit', [SettingsController::class, 'editOption']);
    Route::post('/options/delete', [SettingsController::class, 'deleteOption']);
    Route::post('/options/update', [SettingsController::class, 'updateOption'])->name('options.update');
    Route::get('/getAllProduct', [ProductsController::class, 'getImageProductImages'])->name('getAllProduct');
    Route::get('fetch-options-url', [SettingsController::class, 'fetchOptions'])->name('options.fetch-options-url');
    Route::get('/get-lables', [SettingsController::class, 'getLables'])->name('options.getLables');
    Route::get('/prep-orders', [PrepOrderController::class, 'index'])->name('prep-orders.index');
    Route::post('/prep-orders', [PrepOrderController::class, 'store'])->name('prep-orders.store');
    Route::delete('/prep-orders/{id?}', [PrepOrderController::class, 'destroy'])->name('prep-orders.destroy');
    Route::post('/prep-orders/data', [PrepOrderController::class, 'getData'])->name('prep-orders.data');
    Route::get('/prep-orders/edit/{id?}', [PrepOrderController::class, 'editData'])->name('prep-orders.editData');
    // Route::post('/prep-order-detail', [DailyInputController::class, 'prepOrderStore'])->name('prepOrderStore.detail');

    Route::get('/prep-orders-create', [PrepOrderController::class, 'createOrder'])->name('prep-orders.create');
    Route::post('/prep-orders/create-order', [PrepOrderController::class, 'createOrderStore'])->name('prep-orders.create.order');
    Route::delete('/prep-detail/{id}', [PrepOrderController::class, 'PrepDetailDel'])->name('prep-detail.destroy');
    Route::post('/prep-orders/edit-order', [PrepOrderController::class, 'editOrderStore'])->name('prep-orders.edit.order');
    Route::post('/prep-orders/edit-order', [PrepOrderController::class, 'editOrderStore'])->name('prep-orders.edit.order');
    Route::post('/update-qty', [PrepOrderController::class, 'updateQty'])->name('update.qty');
    Route::post('/done-daily-input', [PrepOrderController::class, 'dailyInputDone'])->name('daily.input.done');
    Route::post('/move-copy-item', [PrepOrderController::class, 'copyMoveItem'])->name('copyMoveItem');
    Route::post('/prep-orders/update-qty', [PrepOrderController::class, 'updateQtyNew'])->name('prep-orders.update-qty');
    Route::post('/save-new-product', [PrepOrderController::class, 'saveNewProduct'])->name('prep-orders.saveNewProduct');
    Route::post('/packing-template/save', [ProductsController::class, 'savePackingTemplate'])->name('packing-template.store');
    Route::post('/upload-image', [ProductsController::class, 'updateImage'])->name('product.upload.image');
    Route::get('/packing-templates/{product}', [ProductsController::class, 'getTemplates']);
    Route::get('/packing-template/{id}', [ProductsController::class, 'showTemplate']);
    Route::put('/packing-template/{id}', [ProductsController::class, 'updateTemplate']);
    Route::delete('/packing-template/{id}', [ProductsController::class, 'destroyTemplate']);
    Route::get('/update-product-id', [DailyInputController::class, 'updatePRoductId'])->name('updatePRoductId');
    Route::post('/check-temp-fnsku', [ProductsController::class, 'checkTempFnsku'])->name('check-temp-fnsku');
    Route::get('/delteDuplaicateProduct', [ProductsController::class, 'delteDuplaicateProduct'])->name('delteDuplaicateProduct');
    Route::post('/update-prep-order-name', [PrepOrderController::class, 'updateName'])->name('updateName');
    //shipping plan
    Route::resource('/shipping-plans',ShippingPlanController::class);
    Route::post('/save-shipping-item',[ShippingPlanController::class,'saveItem']);
    Route::get('/get-shipping-plan-items/{custom_id?}' ,[ShippingPlanController::class,'getShippingItems']);
    Route::delete('/shipping-plans/{id}', [ShippingPlanController::class, 'destroy'])->name('shipping-plans.destroy');
    Route::delete('/shipping-plans/{id}/delete-product', [ShippingPlanController::class, 'deleteProduct'])
    ->name('shipping-plans.delete-product');
    Route::get('/shipping-plans-all', [ShippingPlanController::class, 'getAllShipPlans'])
    ->name('get.shipping-plan');
    Route::post('/shipping-plans/move-item', [ShippingPlanController::class, 'moveITem'])
    ->name('moveITem.shipping-plan');
    Route::post('/shipping-plan/{id}/update-cost', [ShippingPlanController::class, 'updateCost'])
    ->name('shippingplan.updateCost');
    Route::post('/shipping-plan/{id}/update-field', [ShippingPlanController::class, 'updateField']);
    Route::post('/save-shipping-plan-data', [ShippingPlanController::class, 'saveShippingPlanData']);
    Route::post('/update-shipping-plan-name/{id?}', [ShippingPlanController::class, 'updatePlanName'])->name('shipping.update.name');



});