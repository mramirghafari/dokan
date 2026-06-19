<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UnitController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
    Route::post('Accounting/vouchers/merge', "AccountingController@mergeVouchers")->name('Accounting.vouchers.merge');
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

//logout
Route::get('/logout', function () {
    app(\App\Services\PanelMembershipService::class)->clearActivePanel();
    \Auth::logout();

    return redirect()->route('login');
});

Route::post('/sendSmsLogin', [\App\Http\Controllers\Auth\SMSController::class, 'index'])->name('sendSmsLogin');
Route::get('/otp', [\App\Http\Controllers\Auth\SMSController::class, 'otp'])->name('userOTP');
Route::post('/vilidation_code', [\App\Http\Controllers\Auth\SMSController::class, 'vilidation_code'])->name('vilidationCode');

Route::get('/portal/customer/{token}', "CustomerPortalController@show")->name('customer-portal.show');
Route::post('/portal/customer/{token}/requests', "CustomerPortalController@submitRequest")->name('customer-portal.requests.store');
Route::post('/portal/customer/{token}/payments', "CustomerPortalController@submitPayment")->name('customer-portal.payments.store');
Route::match(['GET', 'POST'], '/portal/customer/{token}/payments/{payment}/verify', "CustomerPortalController@verifyPayment")->name('customer-portal.payments.verify');
Route::post('/crm/integrations/voip/{connection}/webhook', "CrmIntegrationController@voipWebhook")->name('crm.integrations.voip.webhook');
Route::get('/bi/shared-report/{token}', "BiDashboardController@sharedReport")->name('bi.report-builder.shared');

Route::middleware(['auth', 'panel.active'])->group(function () {
    Route::get('/panel/select', [\App\Http\Controllers\Auth\PanelSelectionController::class, 'show'])->name('panel.select');
    Route::post('/panel/select', [\App\Http\Controllers\Auth\PanelSelectionController::class, 'store'])->name('panel.switch');

    //index
    Route::get('/', "HomeController@index")->name('index');
    Route::get('/dashboard/org-data/{id}', "HomeController@getOrgData")->name('getOrgData');

    //Change password
    Route::get('/profile', "UserController@profile")->name('profile.index');
    Route::post('/profile', "UserController@profileUpdate")->name('profile.update');

    //Notify Limit
    Route::post('/products/notify/{product}', "ProductController@notify")->name('product.notify');
    Route::post('/notifications/read-all', function () {
        \App\Models\Notifs::where('user_id', auth()->id())->where('status', 0)->update(['status' => 1]);

        return back();
    })->name('notifications.readAll');
    Route::post('/notifications/{notif}/read', function (\App\Models\Notifs $notif) {
        abort_if((int) $notif->user_id !== (int) auth()->id(), 403);
        $notif->update(['status' => 1]);

        return back();
    })->name('notifications.read');


    //Roles
    Route::resource('roles', RoleController::class)->except('create', 'show', 'destroy');
    //Permissions
    Route::resource('permissions', PermissionController::class)->except('create', 'show', 'destroy');
    //Brands
    Route::resource('brands', BrandController::class)->except('create', 'show', 'destroy');
    //Units
    Route::get('units/product', [UnitController::class, 'index'])->defaults('usage_scope', 'product')->name('units.product.index');
    Route::get('units/shipping', [UnitController::class, 'index'])->defaults('usage_scope', 'shipping')->name('units.shipping.index');
    Route::resource('units', UnitController::class)->except('create', 'show', 'destroy');
    Route::get('/units/getParent/{id}', "UnitController@parents")->name('units.parent');
    //Organizations
    Route::resource('organizations', OrganizationController::class)->except('create', 'show');
    //Cities
    Route::resource('cities', CityController::class)->except('create', 'show');
    //Regions
    Route::resource('regions', RegionController::class)->except('create', 'show');
    Route::get('region/{region_id}/areasList', "RegionController@areasList")->name('regions.areasList');
    Route::get('region/{region_id}/CustomersList', "RegionController@CustomersList")->name('regions.CustomersList');
    Route::get('region/{region_id}/activeCustomersList', "RegionController@activeCustomersList")->name('regions.activeCustomersList');
    Route::get('region/{region_id}/invoiceList', "RegionController@invoiceList")->name('regions.invoiceList');
    //Areas
    Route::resource('areas', AreaController::class)->except('create', 'show');
    Route::get('areas/{area_id}/customersList', "AreaController@customersList")->name('areas.customersList');
    Route::get('areas/{area_id}/activeCustomersList', "AreaController@activeCustomersList")->name('areas.activeCustomersList');
    Route::get('areas/{area_id}/invoiceList', "AreaController@invoiceList")->name('areas.invoiceList');
    Route::get('getAreasByRegion/{region_id}', "AreaController@getAreasByRegion")->name('regions.getAreasByRegion');
    //Stores
    Route::resource('stores', StoreController::class)->except('create', 'show', 'destroy');
    // Material Stores
    Route::resource('MaterialStores', MaterialStoreController::class)->except('create', 'show');
    // Material
    Route::resource('Materials', MaterialController::class);
    //Categories
    Route::resource('categories', CategoryController::class)->except('create', 'show', 'destroy');
    //Products
    Route::get('products/datatable', "ProductController@datatable")->name('products.datatable');
    Route::post('products/list-columns', "ProductController@saveListColumns")->name('products.list-columns.save');
    Route::get('products/data-import', "ProductDataImportController@index")->name('products.data-import.index');
    Route::get('products/data-import/template', "ProductDataImportController@template")->name('products.data-import.template');
    Route::post('products/data-import', "ProductDataImportController@import")->name('products.data-import.import');
    Route::get('products/data-import/status/{run}', "ProductDataImportController@importStatus")->name('products.data-import.status');
    Route::resource('products', ProductController::class)->except('show');
    Route::get('products/trashed', "ProductController@trashGet")->name('products.trashed.get');
    Route::delete('/products/trashed/{product}', "ProductController@trashPost")->name('products.trashed.post');
    Route::post('/products/restore/{product}', "ProductController@restore")->name('products.restore');
    Route::get('/products/getCategory/{id}', "ProductController@getCategory")->name('products.category.get');
    Route::get('/products/getprs/{id}', "ProductController@getprs")->name('products.getprs.get');
    Route::get('/products/getprInfo/{id}', "ProductController@getprInfo")->name('products.getprInfo');
    Route::get('/products/getStore/{id}', "ProductController@getStore")->name('products.store.get');
    Route::get('/products/neworder', "ProductController@neworder")->name('products.neworder');
    Route::get('/products/collection', "ProductController@collection")->name('products.collection');
    Route::get('/products/update-fees', "ProductController@updateFees")->name('products.updateFees');
    Route::post('/update_product_fees', "ProductController@update_product_fees")->name('products.update_product_fees');
    Route::get('productsByStore/{store?}', "ProductController@productsByStore")->name('products.productsByStore');

    //Customers
    Route::get('customers/datatable', "CustomersController@datatable")->name('customers.datatable');
    Route::post('customers/list-columns', "CustomersController@saveListColumns")->name('customers.list-columns.save');
    Route::get('customers/data-import', "CustomerDataImportController@index")->name('customers.data-import.index');
    Route::get('customers/data-import/template', "CustomerDataImportController@template")->name('customers.data-import.template');
    Route::post('customers/data-import', "CustomerDataImportController@import")->name('customers.data-import.import');
    Route::get('customers/data-import/status/{run}', "CustomerDataImportController@importStatus")->name('customers.data-import.status');
    Route::get('customers/{customer}/360', "CustomersController@profile360")->name('customers.360');
    Route::resource('customers', CustomersController::class);
    Route::any('customers_search', "CustomersController@search")->name('customers.search');
    Route::delete('customers/destory/{customer_id}', "CustomersController@destroy")->name('customers.destroy');
    Route::get('customers/{customer_id}/orders', "CustomersController@CustomerOrders")->name('customers.orders');
    Route::get('customersCreatedByMe', "CustomersController@createdByMe")->name('customers.createdByMe');
    Route::get('activeCustomers', "CustomersController@activeCustomers")->name('customers.activeCustomers');
    Route::get('customers/trashed', "CustomersController@trashGet")->name('customers.trashed.get');
    Route::delete('/customers/trashed/{product}', "CustomersController@trashPost")->name('customers.trashed.post');
    Route::post('/customers/restore/{product}', "CustomersController@restore")->name('customers.restore');
    Route::post('/update_customer_loc/{Customer}', "CustomersController@update_customer_loc")->name('update_customer_loc');
    Route::post('/update_customer_info_by_visitor/{Customer}', "CustomersController@update_customer_info_by_visitor")->name('update_customer_info_by_visitor');
    Route::get('/crm/dashboard', "CrmDashboardController@index")->name('crm.dashboard.index');
    Route::post('/crm/quick/followup', "CrmQuickActionController@storeFollowup")->name('crm.quick.followup');
    Route::post('/crm/quick/cards/{card}/note', "CrmQuickActionController@storeCardNote")->name('crm.quick.card-note');
    Route::get('/crm/leads', "CrmLeadController@index")->name('crm.leads.index');
    Route::get('/crm/leads/import/template', "CrmLeadController@importTemplate")->name('crm.leads.import.template');
    Route::post('/crm/leads/import', "CrmLeadController@import")->name('crm.leads.import');
    Route::get('/crm/leads/import/{run}', "CrmLeadController@importStatus")->name('crm.leads.import.status');
    Route::post('/crm/leads', "CrmLeadController@store")->name('crm.leads.store');
    Route::post('/crm/leads/{lead}/convert', "CrmLeadController@convert")->name('crm.leads.convert');
    Route::post('/crm/leads/{lead}/reject', "CrmLeadController@reject")->name('crm.leads.reject');
    Route::get('/crm/service-tickets', "CrmServiceTicketController@index")->name('crm.service-tickets.index');
    Route::post('/crm/service-tickets', "CrmServiceTicketController@store")->name('crm.service-tickets.store');
    Route::patch('/crm/service-tickets/{ticket}/status', "CrmServiceTicketController@updateStatus")->name('crm.service-tickets.status');
    Route::get('/crm/call-center', "CrmCallCenterController@index")->name('crm.call-center.index');
    Route::post('/crm/call-center', "CrmCallCenterController@store")->name('crm.call-center.store');
    Route::patch('/crm/call-center/{callLog}/outcome', "CrmCallCenterController@updateOutcome")->name('crm.call-center.outcome');
    Route::get('/crm/followups', "CrmFollowupController@index")->name('crm.followups.index');
    Route::post('/crm/followups', "CrmFollowupController@store")->name('crm.followups.store');
    Route::patch('/crm/followups/{followup}/status', "CrmFollowupController@updateStatus")->name('crm.followups.status');
    Route::get('/crm/campaigns', "CrmCampaignController@index")->name('crm.campaigns.index');
    Route::post('/crm/campaigns', "CrmCampaignController@store")->name('crm.campaigns.store');
    Route::post('/crm/campaigns/{campaign}/audience/sync', "CrmCampaignController@syncAudience")->name('crm.campaigns.audience.sync');
    Route::post('/crm/campaigns/{campaign}/activate', "CrmCampaignController@activateCampaign")->name('crm.campaigns.dispatch');
    Route::post('/crm/campaigns/{campaign}/result', "CrmCampaignController@recordResult")->name('crm.campaigns.result');
    Route::post('/crm/loyalty/transactions', "CrmCampaignController@storeLoyaltyTransaction")->name('crm.loyalty.transactions.store');
    Route::get('/crm/customer-portal', "CustomerPortalController@index")->name('crm.customer-portal.index');
    Route::post('/crm/customer-portal/accounts', "CustomerPortalController@storeAccess")->name('crm.customer-portal.accounts.store');
    Route::post('/crm/customer-portal/announcements', "CustomerPortalController@storeAnnouncement")->name('crm.customer-portal.announcements.store');
    Route::patch('/crm/customer-portal/requests/{portalRequest}', "CustomerPortalController@updateRequest")->name('crm.customer-portal.requests.update');
    Route::patch('/crm/customer-portal/payments/{payment}', "CustomerPortalController@updatePayment")->name('crm.customer-portal.payments.update');
    Route::get('/crm/public-api', "CrmPublicApiClientController@index")->name('crm.public-api.index');
    Route::post('/crm/public-api', "CrmPublicApiClientController@store")->name('crm.public-api.store');
    Route::patch('/crm/public-api/{client}/toggle', "CrmPublicApiClientController@toggle")->name('crm.public-api.toggle');
    Route::get('/crm/integrations', "CrmIntegrationController@index")->name('crm.integrations.index');
    Route::post('/crm/integrations', "CrmIntegrationController@store")->name('crm.integrations.store');
    Route::patch('/crm/integrations/{connection}/toggle', "CrmIntegrationController@toggle")->name('crm.integrations.toggle');
    Route::post('/crm/integrations/calendar/followup', "CrmIntegrationController@syncFollowup")->name('crm.integrations.calendar.sync-followup');
    Route::post('/crm/integrations/drive/link', "CrmIntegrationController@recordDriveLink")->name('crm.integrations.drive.link');
    Route::post('/crm/integrations/voip/click-to-call', "CrmIntegrationController@clickToCall")->name('crm.integrations.voip.click-to-call');
    Route::get('/crm/health', "CrmHealthController@index")->name('crm.health.index');
    Route::post('/crm/health/snapshot', "CrmHealthController@snapshot")->name('crm.health.snapshot');
    Route::get('/crm/workbench', "CrmWorkbenchController@index")->name('crm.workbench.index');
    Route::post('/crm/workbench/comments', "CrmWorkbenchController@storeComment")->name('crm.workbench.comments.store');
    Route::post('/crm/workbench/preferences', "CrmWorkbenchController@updatePreference")->name('crm.workbench.preferences.update');
    Route::patch('/crm/workbench/mentions/{mention}/read', "CrmWorkbenchController@readMention")->name('crm.workbench.mentions.read');
    Route::get('/crm/employee-performance', "CrmEmployeePerformanceController@index")->name('crm.employee-performance.index');
    Route::post('/crm/employee-performance/refresh', "CrmEmployeePerformanceController@refresh")->name('crm.employee-performance.refresh');
    Route::post('/crm/employee-performance/coaching', "CrmEmployeePerformanceController@storeCoaching")->name('crm.employee-performance.coaching.store');
    Route::patch('/crm/employee-performance/coaching/{plan}', "CrmEmployeePerformanceController@updateCoaching")->name('crm.employee-performance.coaching.status');
    Route::get('/crm/opportunities', "CrmOpportunityController@index")->name('crm.opportunities.index');
    Route::post('/crm/opportunities', "CrmOpportunityController@store")->name('crm.opportunities.store');
    Route::patch('/crm/opportunities/{opportunity}/stage', "CrmOpportunityController@updateStage")->name('crm.opportunities.stage');
    Route::get('/crm/sales-boards', "CrmSalesBoardController@index")->name('crm.sales-boards.index');
    Route::post('/crm/sales-boards', "CrmSalesBoardController@storeBoard")->name('crm.sales-boards.store');
    Route::post('/crm/sales-boards/lists', "CrmSalesBoardController@storeList")->name('crm.sales-boards.lists.store');
    Route::patch('/crm/sales-boards/lists/reorder', "CrmSalesBoardController@reorderLists")->name('crm.sales-boards.lists.reorder');
    Route::post('/crm/sales-boards/automation-rules', "CrmSalesBoardController@storeAutomationRule")->name('crm.sales-boards.automation-rules.store');
    Route::patch('/crm/sales-boards/automation-rules/{rule}/toggle', "CrmSalesBoardController@toggleAutomationRule")->name('crm.sales-boards.automation-rules.toggle');
    Route::post('/crm/sales-boards/cards', "CrmSalesBoardController@storeCard")->name('crm.sales-boards.cards.store');
    Route::post('/crm/sales-boards/customers', "CrmSalesBoardController@storeCustomers")->name('crm.sales-boards.customers.store');
    Route::get('/crm/sales-boards/cards/{card}', "CrmSalesBoardController@showCard")->name('crm.sales-boards.cards.show');
    Route::patch('/crm/sales-boards/cards/{card}', "CrmSalesBoardController@updateCard")->name('crm.sales-boards.cards.update');
    Route::post('/crm/sales-boards/cards/{card}/checklist', "CrmSalesBoardController@storeChecklistItem")->name('crm.sales-boards.cards.checklist.store');
    Route::patch('/crm/sales-boards/cards/{card}/checklist/{item}', "CrmSalesBoardController@updateChecklistItem")->name('crm.sales-boards.cards.checklist.update');
    Route::post('/crm/sales-boards/cards/{card}/comments', "CrmSalesBoardController@storeComment")->name('crm.sales-boards.cards.comments.store');
    Route::post('/crm/sales-boards/cards/{card}/attachments', "CrmSalesBoardController@storeAttachment")->name('crm.sales-boards.cards.attachments.store');
    Route::patch('/crm/sales-boards/cards/{card}/move', "CrmSalesBoardController@moveCard")->name('crm.sales-boards.cards.move');

    //Tasks
    Route::resource('tasks', TasksController::class);
    Route::get('getVisitorsByRegion/{region_id}', "TasksController@getVisitorsByRegion")->name('tasks.getVisitorsByRegion');
    Route::get('/active_tasks', "TasksController@active_list")->name('tasks.active_list');
    Route::get('/MyTaks/{task}', "TasksController@MyTask")->name('tasks.visitor_list');
    Route::get('/CustomerInfo/{Customer}/MyTaks/{task?}', "CustomersController@show")->name('tasks.CustomerInfo');
    Route::get('/MyTasks', "TasksController@MyTasks")->name('tasks.MyTasks');

    //Targets
    Route::resource('targets', TargetController::class);
    Route::get('/MyTargets', "TargetController@MyTargets")->name('targets.MyTargets');
    Route::get('targetsHistory', "TargetController@history")->name('targets.history');
    Route::get('/commissions', "CommissionController@index")->name('commissions.index');
    Route::post('/commissions/targets/{target}/calculate', "CommissionController@calculate")->name('commissions.calculate');
    //Histories
    //Route::get('/histories', "HistoryController@index"::class)->name('history.index');

    //Employees
    Route::resource('employees', EmployeeController::class)->except('create', 'show', 'destroy');
    Route::get('/employees/getUnit/{id}', "EmployeeController@getUnit")->name('employees.unit');
    Route::get('/employees/getChildUnit/{id}', "EmployeeController@getChildUnit")->name('employees.childUnit');
    //users
    Route::patch('users/{user}/update', "UserController@updateU")->name('users.update');
    Route::resource('users', UserController::class)->except('create', 'show', 'update');
    Route::any('/user/{user}/invoices', "UserController@userInvoiceList")->name('userInvoiceList');

    //Deliveries
    Route::resource('deliveries', DeliveryController::class)->except('show');
    Route::get('/deliveries/getEmployee/{id}', "DeliveryController@getEmployee")->name('deliveries.employees');
    Route::get('/deliveries/active_invoices', "DeliveryController@active_list")->name('deliveries.active_list');
    Route::get('/deliveries/compeleted_invoices', "DeliveryController@compeleted")->name('deliveries.compeleted');
    Route::get('/deliveries/Outgoing', "DeliveryController@Outgoing")->name('deliveries.Outgoing');
    Route::post('/deliveries/OutgoingFilter', "DeliveryController@Outgoing")->name('deliveries.OutgoingFilter');
    Route::get('/deliveries/preOrderOutput', "DeliveryController@preOrderOutput")->name('deliveries.preOrderOutput');
    Route::post('/deliveries/preOrderOutputFilter', "DeliveryController@preOrderOutput")->name('deliveries.preOrderOutputFilter');
    Route::get('/deliveries/Outgoing_by_items', "DeliveryController@Outgoing_by_items")->name('deliveries.Outgoing_by_items');
    Route::post('/deliveries/Outgoing_by_items_Filter', "DeliveryController@Outgoing_by_items")->name('deliveries.Outgoing_by_items_Filter');
    Route::get('/deliveries/dayOrders', "DeliveryController@dayOrders")->name('deliveries.dayOrders');
    Route::get('/distribution/advanced', "DistributionController@advanced")->name('distribution.advanced');
    Route::post('/distribution/visit-plans', "DistributionController@storeVisitPlan")->name('distribution.visitPlans.store');
    Route::post('/distribution/promotions', "DistributionController@storePromotion")->name('distribution.promotions.store');
    Route::get('/deliveries/addShipment', "DeliveryController@addShipment")->name('deliveries.addShipment');
    Route::get('/deliveries/shipments', "DeliveryController@shipments")->name('deliveries.shipments');
    Route::post('/deliveries/storeShipments', "DeliveryController@storeShipments")->name('deliveries.storeShipments');
    Route::get('/deliveries/EditShipment/{shipment}', "DeliveryController@EditShipment")->name('deliveries.EditShipment');
    Route::get('/deliveries/myShipment/{shipment}', "DeliveryController@myShipment")->name('deliveries.myShipment');
    Route::get('/deliveries/shipmentRoute/{factor}', "DeliveryController@shipmentRoute")->name('deliveries.shipmentRoute');

    //Procurement
    Route::get('/purchase-requisitions', "PurchaseRequisitionController@index")->name('purchase-requisitions.index');
    Route::get('/purchase-requisitions/create', "PurchaseRequisitionController@create")->name('purchase-requisitions.create');
    Route::post('/purchase-requisitions', "PurchaseRequisitionController@store")->name('purchase-requisitions.store');
    Route::post('/purchase-requisitions/from-reorder', "PurchaseRequisitionController@storeFromReorder")->name('purchase-requisitions.reorder.store');
    Route::get('/purchase-requisitions/{purchaseRequisition}', "PurchaseRequisitionController@show")->name('purchase-requisitions.show');
    Route::post('/purchase-requisitions/{purchaseRequisition}/quotations', "PurchaseRequisitionController@storeQuotation")->name('purchase-requisitions.quotations.store');
    Route::post('/purchase-requisitions/{purchaseRequisition}/quotations/{supplierQuotation}/select', "PurchaseRequisitionController@selectQuotation")->name('purchase-requisitions.quotations.select');
    Route::get('/purchase-orders', "PurchaseOrderController@index")->name('purchase-orders.index');
    Route::get('/purchase-orders/approvals', "PurchaseOrderController@approvals")->name('purchase-orders.approvals');
    Route::post('/purchase-orders/budgets', "PurchaseOrderController@storeBudget")->name('purchase-orders.budgets.store');
    Route::get('/purchase-orders/report', "PurchaseOrderController@report")->name('purchase-orders.report');
    Route::get('/purchase-orders/supplier-ledger', "PurchaseOrderController@supplierLedger")->name('purchase-orders.supplierLedger');
    Route::get('/purchase-orders/commitment-report', "PurchaseOrderController@commitmentReport")->name('purchase-orders.commitmentReport');
    Route::get('/purchase-orders/price-report', "PurchaseOrderController@priceReport")->name('purchase-orders.priceReport');
    Route::get('/purchase-orders/imports', "PurchaseOrderController@foreignImports")->name('purchase-orders.foreignImports');
    Route::post('/purchase-orders/imports', "PurchaseOrderController@storeForeignImport")->name('purchase-orders.foreignImports.store');
    Route::post('/purchase-orders/imports/{foreignPurchaseOrder}/status', "PurchaseOrderController@updateForeignImportStatus")->name('purchase-orders.foreignImports.status');
    Route::get('/purchase-service-invoices', "PurchaseOrderController@serviceInvoices")->name('purchase-service-invoices.index');
    Route::post('/purchase-service-invoices', "PurchaseOrderController@storeServiceInvoice")->name('purchase-service-invoices.store');
    Route::post('/purchase-service-invoices/{purchaseServiceInvoice}/cancel', "PurchaseOrderController@cancelServiceInvoice")->name('purchase-service-invoices.cancel');
    Route::get('/purchase-orders/direct-supply', "PurchaseOrderController@directSupply")->name('purchase-orders.directSupply');
    Route::post('/purchase-orders/direct-supply', "PurchaseOrderController@storeDirectSupply")->name('purchase-orders.directSupply.store');
    Route::get('/purchase-orders/create', "PurchaseOrderController@create")->name('purchase-orders.create');
    Route::post('/purchase-orders', "PurchaseOrderController@store")->name('purchase-orders.store');
    Route::post('/purchase-orders/{purchaseOrder}/request-approval', "PurchaseOrderController@requestApproval")->name('purchase-orders.requestApproval');
    Route::post('/purchase-orders/{purchaseOrder}/approval/approve', "PurchaseOrderController@approveApproval")->name('purchase-orders.approval.approve');
    Route::post('/purchase-orders/{purchaseOrder}/approval/reject', "PurchaseOrderController@rejectApproval")->name('purchase-orders.approval.reject');
    Route::post('/purchase-orders/{purchaseOrder}/approve', "PurchaseOrderController@approve")->name('purchase-orders.approve');
    Route::post('/purchase-orders/{purchaseOrder}/receive', "PurchaseOrderController@receive")->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchaseOrder}/invoice', "PurchaseOrderController@storePurchaseInvoice")->name('purchase-orders.invoice.store');
    Route::post('/purchase-orders/{purchaseOrder}/pay', "PurchaseOrderController@pay")->name('purchase-orders.pay');
    Route::post('/purchase-orders/{purchaseOrder}/returns', "PurchaseOrderController@returnItems")->name('purchase-orders.returns');

    //Tenants
    Route::resource('tenants', TenantsController::class);


    //Drivers
    Route::resource('freight', DriverController::class);
    Route::get('/freight/{Factor}', "DriverController@show")->name('customer_details');
    Route::post('/freight/update_by_driver/{Factor}', "DriverController@update_by_driver")->name('update_by_driver');

    //Accounts & Accounting
    Route::post('Account/import-standard', "AccountController@importStandard")->name('Account.importStandard');
    Route::resource('Account', AccountController::class)->except('show');
    Route::resource('Terminals', TerminalController::class)->except('show');
    Route::resource('Accounting', AccountingController::class)->except('show');
    Route::get('Accounting/vouchers', "AccountingController@vouchers")->name('Accounting.vouchers');
    Route::get('Accounting/vouchers/create', "AccountingController@createVoucher")->name('Accounting.vouchers.create');
    Route::get('Accounting/vouchers/opening', "AccountingController@createOpeningVoucher")->name('Accounting.vouchers.opening');
    Route::post('Accounting/vouchers/opening', "AccountingController@storeOpeningVoucher")->name('Accounting.vouchers.opening.store');
    Route::get('Accounting/voucher-templates', "AccountingController@voucherTemplates")->name('Accounting.voucherTemplates');
    Route::post('Accounting/voucher-templates/{voucherTemplate}/draft', "AccountingController@createVoucherFromTemplate")->name('Accounting.voucherTemplates.draft');
    Route::get('Accounting/vouchers/{voucher}/edit', "AccountingController@editVoucher")->name('Accounting.vouchers.edit');
    Route::post('Accounting/vouchers', "AccountingController@storeVoucher")->name('Accounting.vouchers.store');
    Route::put('Accounting/vouchers/{voucher}', "AccountingController@updateVoucher")->name('Accounting.vouchers.update');
    Route::post('Accounting/vouchers/merge', "AccountingController@mergeVouchers")->name('Accounting.vouchers.merge');
    Route::post('Accounting/vouchers/{voucher}/permanent', "AccountingController@makeVoucherPermanent")->name('Accounting.vouchers.permanent');
    Route::post('Accounting/vouchers/{voucher}/reverse', "AccountingController@reverseVoucher")->name('Accounting.vouchers.reverse');
    Route::post('Accounting/vouchers/{voucher}/cancel', "AccountingController@cancelVoucher")->name('Accounting.vouchers.cancel');
    Route::post('Accounting/vouchers/{voucher}/copy', "AccountingController@copyVoucher")->name('Accounting.vouchers.copy');
    Route::post('Accounting/vouchers/{voucher}/template', "AccountingController@storeVoucherTemplate")->name('Accounting.vouchers.template');
    Route::get('Accounting/legal-ledgers', "AccountingController@legalLedgers")->name('Accounting.legalLedgers');
    Route::get('Accounting/detailed-ledgers', "AccountingController@detailedLedgers")->name('Accounting.detailedLedgers');
    Route::get('Accounting/analytic-dimensions', "AccountingController@analyticDimensions")->name('Accounting.analyticDimensions');
    Route::get('Accounting/currency-balances', "AccountingController@currencyBalances")->name('Accounting.currencyBalances');
    Route::post('Accounting/currencies', "AccountingController@storeCurrency")->name('Accounting.currencies.store');
    Route::post('Accounting/exchange-rates', "AccountingController@storeExchangeRate")->name('Accounting.exchangeRates.store');
    Route::get('Accounting/financial-statements', "AccountingController@financialStatements")->name('Accounting.financialStatements');
    Route::get('Accounting/revenue-centers', "AccountingController@revenueCenters")->name('Accounting.revenueCenters');
    Route::post('Accounting/revenue-centers', "AccountingController@storeRevenueCenter")->name('Accounting.revenueCenters.store');
    Route::get('Accounting/incomes', "AccountingController@incomes")->name('Accounting.incomes');
    Route::post('Accounting/incomes/types', "AccountingController@storeIncomeType")->name('Accounting.incomes.types.store');
    Route::post('Accounting/incomes/{income}/attachments', "AccountingController@storeIncomeAttachment")->name('Accounting.incomes.attachments.store');
    Route::post('Accounting/incomes', "AccountingController@storeIncome")->name('Accounting.incomes.store');
    Route::get('Accounting/fiscal-closing', "AccountingController@fiscalClosing")->name('Accounting.fiscalClosing');
    Route::post('Accounting/fiscal-closing/{fiscalYear}/close', "AccountingController@closeFiscalYear")->name('Accounting.fiscalClosing.close');
    Route::get('Accounting/expenses', "AccountingController@expenses")->name('Accounting.expenses');
    Route::post('Accounting/expenses/cost-centers', "AccountingController@storeCostCenter")->name('Accounting.expenses.costCenters.store');
    Route::post('Accounting/expenses/types', "AccountingController@storeExpenseType")->name('Accounting.expenses.types.store');
    Route::post('Accounting/expenses/specialized', "AccountingController@storeSpecializedExpense")->name('Accounting.expenses.specialized.store');
    Route::post('Accounting/expenses/{expense}/approve', "AccountingController@approveSpecializedExpense")->name('Accounting.expenses.approve');
    Route::post('Accounting/expenses/{expense}/reject', "AccountingController@rejectSpecializedExpense")->name('Accounting.expenses.reject');
    Route::post('Accounting/expenses/{expense}/attachments', "AccountingController@storeExpenseAttachment")->name('Accounting.expenses.attachments.store');
    Route::post('Accounting/expenses', "AccountingController@storeExpense")->name('Accounting.expenses.store');
    Route::get('Accounting/company-assets', "AccountingController@companyAssets")->name('Accounting.companyAssets');
    Route::get('Accounting/company-assets/report', "AccountingController@companyAssetReport")->name('Accounting.companyAssets.report');
    Route::post('Accounting/company-assets', "AccountingController@storeCompanyAsset")->name('Accounting.companyAssets.store');
    Route::post('Accounting/company-assets/depreciation', "AccountingController@postCompanyAssetDepreciation")->name('Accounting.companyAssets.depreciation.post');
    Route::post('Accounting/company-assets/{companyAsset}/depreciation-policy', "AccountingController@storeCompanyAssetDepreciationPolicy")->name('Accounting.companyAssets.depreciationPolicy.store');
    Route::post('Accounting/company-assets/{companyAsset}/capital-addition', "AccountingController@postCompanyAssetCapitalAddition")->name('Accounting.companyAssets.capitalAddition.post');
    Route::post('Accounting/company-assets/{companyAsset}/disposal', "AccountingController@postCompanyAssetDisposal")->name('Accounting.companyAssets.disposal.post');
    Route::post('Accounting/company-assets/disposals/{disposal}/tax-invoice', "AccountingController@prepareCompanyAssetTaxInvoice")->name('Accounting.companyAssets.taxInvoice.prepare');
    Route::post('Accounting/company-assets/tax-invoices/{taxInvoice}/status', "AccountingController@updateCompanyAssetTaxInvoiceStatus")->name('Accounting.companyAssets.taxInvoice.status');
    Route::post('Accounting/company-assets/{companyAsset}/attachments', "AccountingController@storeCompanyAssetAttachment")->name('Accounting.companyAssets.attachments.store');
    Route::post('Accounting/company-assets/{companyAsset}/events', "AccountingController@storeCompanyAssetEvent")->name('Accounting.companyAssets.events.store');
    Route::get('Accounting/payroll', "AccountingController@payroll")->name('Accounting.payroll');
    Route::post('Accounting/payroll/contracts', "AccountingController@storePayrollContract")->name('Accounting.payroll.contracts.store');
    Route::post('Accounting/payroll/attendance', "AccountingController@storePayrollAttendance")->name('Accounting.payroll.attendance.store');
    Route::post('Accounting/payroll', "AccountingController@storePayrollRun")->name('Accounting.payroll.store');
    Route::post('Accounting/payroll/{payrollRun}/payments', "AccountingController@payPayrollRun")->name('Accounting.payroll.payments.store');
    Route::post('Accounting/payroll/{payrollRun}/cancel', "AccountingController@cancelPayrollRun")->name('Accounting.payroll.cancel');
    Route::get('contracting/projects', "ContractingController@index")->name('contracting.projects');
    Route::post('contracting/projects', "ContractingController@storeProject")->name('contracting.projects.store');
    Route::post('contracting/projects/{project}/progress-statements', "ContractingController@storeProgressStatement")->name('contracting.progressStatements.store');
    Route::post('contracting/projects/{project}/guarantees', "ContractingController@storeGuarantee")->name('contracting.guarantees.store');
    Route::post('contracting/projects/{project}/costs', "ContractingController@storeCostEntry")->name('contracting.costs.store');
    Route::get('Accounting/taxpayer', "TaxpayerComplianceController@index")->name('taxpayer.index');
    Route::post('Accounting/taxpayer/settings', "TaxpayerComplianceController@storeSetting")->name('taxpayer.settings.store');
    Route::post('Accounting/taxpayer/mappings', "TaxpayerComplianceController@storeMapping")->name('taxpayer.mappings.store');
    Route::post('Accounting/taxpayer/sales/{factor}', "TaxpayerComplianceController@prepareSales")->name('taxpayer.sales.prepare');
    Route::post('Accounting/taxpayer/contracting/{statement}', "TaxpayerComplianceController@prepareContracting")->name('taxpayer.contracting.prepare');
    Route::post('Accounting/taxpayer/assets/{assetTaxInvoice}', "TaxpayerComplianceController@prepareAsset")->name('taxpayer.assets.prepare');
    Route::post('Accounting/taxpayer/invoices/{taxpayerInvoice}/status', "TaxpayerComplianceController@updateStatus")->name('taxpayer.invoices.status');
    Route::get('sales/ecommerce', "EcommerceIntegrationController@index")->name('ecommerce.index');
    Route::post('sales/ecommerce/channels', "EcommerceIntegrationController@storeChannel")->name('ecommerce.channels.store');
    Route::post('sales/ecommerce/mappings', "EcommerceIntegrationController@storeMapping")->name('ecommerce.mappings.store');
    Route::post('sales/ecommerce/channels/{channel}/sample-order', "EcommerceIntegrationController@importSampleOrder")->name('ecommerce.orders.sample');
    Route::post('sales/ecommerce/orders/{orderMapping}/status', "EcommerceIntegrationController@updateOrderStatus")->name('ecommerce.orders.status');
    Route::get('Accounting/treasury', "AccountingController@treasury")->name('Accounting.treasury');
    Route::get('Accounting/treasury/create', "AccountingController@createTreasury")->name('Accounting.treasury.create');
    Route::post('Accounting/treasury', "AccountingController@storeTreasury")->name('Accounting.treasury.store');
    Route::get('Accounting/treasury/transfer', "AccountingController@createTreasuryTransfer")->name('Accounting.treasury.transfer.create');
    Route::post('Accounting/treasury/transfer', "AccountingController@storeTreasuryTransfer")->name('Accounting.treasury.transfer.store');
    Route::get('Accounting/treasury/bank-reconciliation', "AccountingController@bankReconciliation")->name('Accounting.treasury.bankReconciliation');
    Route::post('Accounting/treasury/bank-statements', "AccountingController@storeBankStatementLine")->name('Accounting.treasury.bankStatements.store');
    Route::post('Accounting/treasury/bank-statements/{line}/reconcile', "AccountingController@reconcileBankStatementLine")->name('Accounting.treasury.bankStatements.reconcile');
    Route::get('Accounting/treasury/liquidity', "AccountingController@liquidityReport")->name('Accounting.treasury.liquidity');
    Route::get('Accounting/treasury/cash-forecast', "AccountingController@treasuryCashForecast")->name('Accounting.treasury.cashForecast');
    Route::get('Accounting/treasury/petty-cash', "AccountingController@pettyCash")->name('Accounting.treasury.pettyCash');
    Route::post('Accounting/treasury/petty-cash/funds', "AccountingController@storePettyCashFund")->name('Accounting.treasury.pettyCash.funds.store');
    Route::post('Accounting/treasury/petty-cash/{fund}/charge', "AccountingController@chargePettyCash")->name('Accounting.treasury.pettyCash.charge');
    Route::post('Accounting/treasury/petty-cash/{fund}/expense', "AccountingController@spendPettyCash")->name('Accounting.treasury.pettyCash.expense');
    Route::post('Accounting/treasury/petty-cash/{fund}/settlement', "AccountingController@settlePettyCash")->name('Accounting.treasury.pettyCash.settlement');
    Route::get('Accounting/treasury/cheque-books', "AccountingController@chequeBooks")->name('Accounting.treasury.chequeBooks');
    Route::post('Accounting/treasury/cheque-books', "AccountingController@storeChequeBook")->name('Accounting.treasury.chequeBooks.store');
    Route::get('Accounting/treasury/cheques', "AccountingController@treasuryChequeReport")->name('Accounting.treasury.cheques');
    Route::get('Accounting/treasury/cheques/aging', "AccountingController@treasuryChequeAgingReport")->name('Accounting.treasury.cheques.aging');
    Route::post('Accounting/treasury/instruments/{instrument}/status', "AccountingController@updateTreasuryInstrumentStatus")->name('Accounting.treasury.instruments.status');
    Route::get('AccountingFund', "AccountingController@index")->name('Accounting.index');
    Route::any('AccountingReviews', "AccountingController@AccountingReviews")->name('Accounting.AccountingReviews');
    Route::any('Accounting/ProductsSales', "AccountingController@ProductsSales")->name('Accounting.ProductsSales');
    Route::get('Accounting/payed', "AccountingController@payed")->name('Accounting.payed');
    Route::get('Accounting/unpayed', "AccountingController@unpayed")->name('Accounting.unpayed');
    Route::get('Accounting/cashpay', "AccountingController@cashpay")->name('Accounting.cashpay');
    Route::get('Accounting/checkpay', "AccountingController@checkpay")->name('Accounting.checkpay');
    Route::get('Accounting/unknown', "AccountingController@unknown")->name('Accounting.unknown');
    Route::post('Accounting/PrFilter', "AccountingController@PrFilter")->name('Accounting.PrFilter');
    Route::post('Accounting/Filter', "AccountingController@Filter")->name('Accounting.Filter');

    //AccountingReviews
    Route::get('invoices/pishfactors/datatable', "InvoiceController@pishFactorsDatatable")->name('invoices.pishfactors.datatable');
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/details', "InvoiceController@detailList")->name('details.list');
    Route::post('invoices/actions', "InvoiceController@actions")->name('invoices.actions');

    //Details For Invoice
    Route::delete('/invoices/detail/{detail}', "InvoiceController@deleteDetail")->name('invoices.detail.delete');
    Route::get('/details', "InvoiceController@detailList")->name('invoices.detail.list');
    Route::get('/invoices/detail/{detail}/edit', "InvoiceController@editDetail")->name('invoices.detail.edit');
    Route::patch('/invoices/detail/{detail}/edit', "InvoiceController@updateDetail")->name('invoices.detail.update');
    Route::get('/invoices/detail/{invoice}/create', "InvoiceController@addDetailGet")->name('detail.add.get');
    Route::post('/invoices/detail/{invoice}/create', "InvoiceController@addDetailPost")->name('detail.add.post');

    Route::get('/all_invoices', "InvoiceController@all_invoices")->name('invoices.all_invoices');
    Route::get('/active_invoices', "InvoiceController@active_list")->name('invoices.active_list');
    Route::get('/denciled_invoices', "InvoiceController@denciled")->name('invoices.denciled');
    Route::get('/compeleted_invoices', "InvoiceController@compeleted")->name('invoices.compeleted');
    Route::get('/assigned_to_drivers', "DeliveryController@assigned_to_drivers")->name('invoices.assigned_to_drivers');
    Route::get('/factor_reporter', "InvoiceController@factor_reporter")->name('invoices.reporter');
    Route::get('/myInvoices', "InvoiceController@myInvoices")->name('invoices.myInvoices');
    Route::get('/factor_deposit/{$factor}', "InvoiceController@factor_deposit")->name('invoices.depost');
    //Suppliers
    Route::resource('suppliers', SupplierController::class);

    //Stocks
    Route::resource('stocks', StockController::class)->except('show');
    Route::resource('warehouse-locations', \App\Http\Controllers\WarehouseLocationController::class)->except(['create', 'show', 'destroy']);
    Route::get('/need-entity', "StockController@need_entity")->name('stocks.need_entity');
    Route::get('/stocks-entrance', "StockController@entrance")->name('stocks.entrance');
    Route::get('/stocks/{store}/products', "StockController@storeProducts")->name('stocks.storeProducts');
    Route::get('/stocks/{store}/product/{product}', "StockController@storeProductCartex")->name('stocks.storeProductCardex');
    Route::get('/stocks/inventory/balances', "StockController@inventoryBalances")->name('stocks.inventoryBalances');
    Route::get('/stocks/inventory/movements', "StockController@inventoryMovements")->name('stocks.inventoryMovements');
    Route::get('/stocks/inventory/traceability', "StockController@inventoryTraceability")->name('stocks.inventoryTraceability');
    Route::get('/stocks/inventory/reservations', "StockController@inventoryReservations")->name('stocks.inventoryReservations');
    Route::get('/stocks/inventory/valuation', "StockController@inventoryValuation")->name('stocks.inventoryValuation');
    Route::get('/stocks/inventory/reorder', "StockController@inventoryReorder")->name('stocks.inventoryReorder');
    Route::get('/stocks/inventory/slow-moving', "StockController@inventorySlowMoving")->name('stocks.inventorySlowMoving');
    Route::get('/stocks/inventory/adjustments', "StockController@inventoryAdjustments")->name('stocks.inventoryAdjustments');
    Route::get('/stocks/inventory/adjustments/create', "StockController@createInventoryAdjustment")->name('stocks.inventoryAdjustments.create');
    Route::post('/stocks/inventory/adjustments', "StockController@storeInventoryAdjustment")->name('stocks.inventoryAdjustments.store');
    Route::post('/stocks/inventory/adjustments/{adjustment}/approve', "StockController@approveInventoryAdjustment")->name('stocks.inventoryAdjustments.approve');
    Route::post('/stocks/inventory/adjustments/{adjustment}/cancel', "StockController@cancelInventoryAdjustment")->name('stocks.inventoryAdjustments.cancel');
    Route::get('/stocks/cartexStore', "StockController@store_cartex")->name('stocks.store_cartex');
    Route::get('/stocks/StoreProducts/{store}', "StockController@storeProducts")->name('stocks.StoreProductsCartex');
    Route::get('/stocks/cartex', "StockController@PrCartexList")->name('stocks.PrCartexList');
    Route::get('/stocks/cartex/{product}', "StockController@PrCartex")->name('stocks.PrCartex');
    Route::get('/stocks/getEmployee/{id}', "StockController@getEmployee")->name('stocks.employees');
    Route::get('/stocks/getCategory/{id}', "StockController@getCategory")->name('stocks.category.get');
    Route::get('/stocks/getStore/{id}', "StockController@getStore")->name('stocks.store.get');
    Route::get('/ProductionByExtraction', "StockController@ProductionByExtraction")->name('stocks.ProductionByExtraction');
    Route::post('/ProductionFormulas', "StockController@storeProductionFormula")->name('stocks.productionFormulas.store');
    Route::post('/ProductionFormulas/{productionFormula}/toggle', "StockController@toggleProductionFormula")->name('stocks.productionFormulas.toggle');
    Route::post('/ProductionByExtractionProcess', "StockController@ProductionByExtractionProcess")->name('stocks.ProductionByExtractionProcess');
    Route::post('/ProductionByFormulaProcess', "StockController@createProductionFromFormula")->name('stocks.ProductionByFormulaProcess');
    Route::post('/ProductionOrders/{productionOrder}/cancel', "StockController@cancelProductionOrder")->name('stocks.productionOrders.cancel');
    Route::get('/ProductStock/{product}', "StockController@ProductStock")->name('stocks.ProductStock');
    //

    // import stock
    Route::get('/import-stock', "StockController@import_stock")->name('stocks.import_stock');
    Route::get('/stocks/{store}/productsTransfer', "StockController@storeProductsForTransfer")->name('stocks.storeProductsForTransfer');

    // Stock Transfer
    Route::get('/stock-transfer', "StockController@StockTransfer")->name('stocks.entrance_transfer');

    //Receipts
    Route::resource('receipt', ReceiptController::class);
    Route::post('receipt/storeTransfer', "ReceiptController@storeTransfer")->name('receipt.storeTransfer');
    Route::get('/stocks/{store}/receipts', "ReceiptController@storeReceipts")->name('stocks.storeReceipts');
    Route::get('/stocks/{store}/receipts/{receipt}', "ReceiptController@storeReceiptShow")->name('stocks.storeReceiptShow');
    Route::post('/stocks/receipts/{receipt}/approve', "ReceiptController@approveReceipt")->name('stocks.receipts.approve');
    Route::post('/stocks/receipts/{receipt}/cancel', "ReceiptController@cancelReceipt")->name('stocks.receipts.cancel');
    Route::post('/stocks/receipts/{receipt}/returns', "ReceiptController@returnReceipt")->name('stocks.receipts.returns');
    Route::get('/deleteReceipt/{receipt}', "ReceiptController@deleteReceipt")->name('stocks.deleteReceipt');
    Route::post('/importReceiptAi', "ReceiptController@importReceiptAi")->name('stocks.importReceiptAi');


    // stock transfer

    //Abortions - کالاهای اسقاطی
    Route::resource('abortions', AbortionController::class)->except('show');

    //logs
    Route::get('/logs', "LogController@index")->name('logs.index');

    //Reports
    Route::get('/reports/deliveris', "ReportController@deliveries")->name('reports.deliveries.index');
    Route::get('/reports/management', "ReportController@management")->name('reports.management');
    Route::post('/reports/management/snapshot', "ReportController@managementSnapshot")->name('reports.management.snapshot');
    Route::post('/reports/management/templates', "ReportController@managementTemplateStore")->name('reports.management.templates.store');
    Route::post('/reports/management/schedules', "ReportController@managementScheduleStore")->name('reports.management.schedules.store');
    Route::get('/reports/management/export/{format}', "ReportController@managementExport")->name('reports.management.export');
    Route::get('/bi/dashboard', "BiDashboardController@index")->name('bi.dashboard.index');
    Route::get('/bi/executive', "BiDashboardController@executive")->name('bi.executive.index');
    Route::get('/bi/cfo', "BiDashboardController@cfo")->name('bi.cfo.index');
    Route::get('/bi/reconciliation', "BiDashboardController@reconciliation")->name('bi.reconciliation.index');
    Route::post('/bi/reconciliation/run', "BiDashboardController@runReconciliation")->name('bi.reconciliation.run');
    Route::post('/bi/reconciliation/backfill', "BiDashboardController@queueBackfill")->name('bi.reconciliation.backfill');
    Route::post('/bi/dashboard/refresh-crm', "BiDashboardController@refreshCrm")->name('bi.dashboard.refresh-crm');
    Route::post('/bi/dashboard/refresh-data-mart', "BiDashboardController@refreshDataMart")->name('bi.dashboard.refresh-data-mart');
    Route::get('/bi/report-builder', "BiDashboardController@reportBuilder")->name('bi.report-builder.index');
    Route::post('/bi/report-builder/templates', "BiDashboardController@storeTemplate")->name('bi.report-builder.templates.store');
    Route::post('/bi/report-builder/export', "BiDashboardController@queueExport")->name('bi.report-builder.export');
    Route::get('/bi/report-builder/exports/{exchangeRun}/download', "BiDashboardController@downloadExport")->name('bi.report-builder.exports.download');
    Route::post('/bi/report-builder/schedules', "BiDashboardController@storeSchedule")->name('bi.report-builder.schedules.store');
    Route::get('/bi/insights', "BiInsightController@index")->name('bi.insights.index');
    Route::post('/bi/insights/run', "BiInsightController@run")->name('bi.insights.run');
    Route::post('/bi/insights/rules', "BiInsightController@storeRule")->name('bi.insights.rules.store');
    Route::patch('/bi/insights/alerts/{alert}', "BiInsightController@updateAlert")->name('bi.insights.alerts.update');
    Route::get('/erp/scale-hardening', "ErpScaleHardeningController@index")->name('erp.scale-hardening.index');
    Route::post('/erp/scale-hardening/snapshot', "ErpScaleHardeningController@snapshot")->name('erp.scale-hardening.snapshot');
    Route::get('/erp/scale-hardening/lookup', "ErpScaleHardeningController@lookup")->name('erp.scale-hardening.lookup');

    //Transfers
    Route::resource('transfers', TransferController::class)->except('show');
    Route::post('transfers/approved/{transfer}', "TransferController@approved")->name('transfers.approved');
    Route::post('transfers/read/{transfer}', "TransferController@read")->name('transfers.read');
    Route::get('transfers/add-product/{transfer}', "TransferController@addProduct")->name('transfers.add.product');
    Route::patch('transfers/add-product/{transfer}', "TransferController@storeProduct")->name('transfers.store.product');
    Route::get('transfers/product/deny/{transfer}', "TransferController@denyTransfer")->name('transfers.deny');

    //Stocks
    Route::resource('repairs', RepairController::class)->except('show');
    Route::get('/repairs/getEmployee/{id}', "RepairController@getEmployee")->name('repairs.employees');
    Route::get('/repairs/getCategory/{id}', "RepairController@getCategory")->name('repairs.category.get');
    Route::get('/repairs/getStore/{id}', "RepairController@getStore")->name('repairs.store.get');

    //CHANGE INFO
    Route::get('/change-info', "HomeController@changeInfoGet")->name('profile.change.get');
    Route::post('/change-info', "HomeController@changeInfoPost")->name('profile.change.post');

    //CHANGE SETTINGS
    Route::get('/settings', "SettingController@index")->name('settings.index');
    Route::get('/settings/sales-scenario', "SettingController@salesScenario")->name('settings.salesScenario');
    Route::get('/settings/notifications', "SettingController@notifications")->name('settings.notifications');
    Route::get('/settings/dashboard-widgets', "SettingController@dashboardWidgets")->name('settings.dashboardWidgets');
    Route::post('/settings', "SettingController@update")->name('settings.update');
    Route::get('/setup-guide', "SetupGuideController@index")->name('setup-guide.index');

    Route::post('/panel/onboarding/welcome', "PanelOnboardingController@dismissWelcome")->name('panel.onboarding.welcome');
    Route::post('/panel/onboarding/tour', "PanelOnboardingController@completeTour")->name('panel.onboarding.tour');
    Route::post('/panel/onboarding/complete', "PanelOnboardingController@completeSetup")->name('panel.onboarding.complete');

    //WAREHOUSE REPORTSS
    Route::get('/reports/warehouse', "ReportController@warehouse")->name('warehouse.index');
    Route::get('/reports/warehouse/filter', "ReportController@warehouseFilter")->name('warehouse.filter');
});

Route::post('/database/seeder', "InstallerController@index")->name('databaseSeeder');

Route::post('/add_factor_visitor', "InvoiceController@add_factor_visitor")->name('add_factor_visitor');
Route::get('/pishFactorInfo/{PishFactor}', "InvoiceController@pishFactorInfo")->name('pishFactorInfo');
Route::get('/pishFactorView/{PishFactor}', "InvoiceController@pishFactorInfo")->name('pishFactorView');
Route::post('/pishFactorUpdate/{PishFactor}', "InvoiceController@pishFactorUpdate")->name('pishFactorUpdate');
Route::get('/EditFactor/{PishFactor}', "InvoiceController@EditFactor")->name('EditFactor');
Route::post('/UpdateFactorItems/{PishFactor}', "InvoiceController@UpdateFactorItems")->name('UpdateFactorItems');
Route::post('/deletePF/{PishFactor}', "InvoiceController@DeleteFactor")->name('pishfactor.destroy');
Route::get('/waiting-orders', "InvoiceController@waiting_orders")->name('waiting_orders');
Route::get('/history-orders', "InvoiceController@history_orders")->name('history_orders');

// Depots
Route::resource('depot', DepotController::class);
Route::get('/history-orders', "InvoiceController@history_orders")->name('history_orders');

// Factor Setting
Route::resource('FactorManager', FactorSettingController::class);


Route::get('/update_costomers_user', function () {

    $userID = auth()->user()->id;
    $Logs = DB::table('logs')->where('user_id', $userID)->get();
    foreach ($Logs as $log) {
        if (str_starts_with($log->description, "یک مشتری ایجاد شد-")) {

            $infos = explode("-", $log->description);
            echo $infos[1] . "<br />";
        }
    }

    //dd($Logs);
    //$Customers = DB::table('customers')->where('role_id', $role->id)->pluck('store_id');

});


Route::get('/update_recive_times', function () {

    $userID = auth()->user()->id;
    $Logs = DB::table('pishfactors')->get();
    foreach ($Logs as $log) {
        if ($log->recive_date != null) {
            $jalali = explode("/", $log->recive_date);
            $miladi = Verta::jalaliToGregorian($jalali[0], $jalali[1], $jalali[2]);
            $ym = $miladi[0];
            if (strlen($miladi[1]) == 1) {
                $mm = "0" . $miladi[1];
            } else {
                $mm = $miladi[1];
            };
            if (strlen($miladi[2]) == 1) {
                $dm = "0" . $miladi[2];
            } else {
                $dm = $miladi[2];
            };
            echo $log->recive_date . " - $ym-$mm-$dm <br />";

            DB::table('pishfactors')->where('id', $log->id)->update(array(
                'recive_date_en' => "$ym-$mm-$dm 00:00:00",
            ));
        }
    }

    //dd($Logs);
    //$Customers = DB::table('customers')->where('role_id', $role->id)->pluck('store_id');

});

Route::get('/update_factor_items', function () {

    $userID = auth()->user()->id;
    $Logs = DB::table('pish_factor_items')->get();
    foreach ($Logs as $log) {

        $Product = DB::table('products')->where('id', $log->pr_id)->first();
        DB::table('pish_factor_items')->where('id', $log->id)->update(array(
            'price' => "$Product->price",
        ));
    }
});

Route::get('/add_first_depots', function () {

    $userID = auth()->user()->id;
    $Logs = DB::table('products')->get();
    foreach ($Logs as $log) {

        $InsertDepot = DB::table('depots')
            ->insert([
                'pr_id' => $log->id,
                'price' => $log->price,
                'entity' => $log->entity,
                'orderLimit' => $log->orderLimit,
                'discount' => $log->discount,
                'tax' => $log->tax,
                'fee_masraf' => $log->tax
            ]);



        $Selecte_Depot = DB::table('depots')->where('pr_id', $log->id)->first();


        DB::table('products')->where('id', $log->id)->update(array(
            'depot_id' => "$Selecte_Depot->id",
        ));
    }
});


Route::get('/update_area_region_city', function () {

    $userID = auth()->user()->id;
    $Logs = DB::table('pishfactors')->get();
    foreach ($Logs as $log) {
        $Customer = DB::table('customers')->where('id', $log->customer_id)->first();
        if ($Customer && $Customer->area != null) {
            $Area = DB::table('areas')->where('id', $Customer->area)->first();
            $Region = DB::table('regions')->where('id', $Area->region_id)->first();
            $City = DB::table('regions')->where('id', $Region->city_id)->first();
            DB::table('pishfactors')->where('id', $log->id)->update(array(
                'area_id' => $Customer ? $Customer->area : 0,
                'region_id' => $Area ? $Area->region_id : 0,
                'city_id' => $Region ? $Region->city_id : 0,
            ));
        }
    }

    //dd($Logs);
    //$Customers = DB::table('customers')->where('role_id', $role->id)->pluck('store_id');

});
Route::get('/update_factor_fullprices', function () {

    $userID = auth()->user()->id;
    $Logs = DB::table('pishfactors')->get();


    foreach ($Logs as $log) {
        $Items = DB::table('pish_factor_items')->where('pishfactor_id', $log->id)->get();
        $allpacks = 0;
        $allitems = 0;
        $allitems_full = 0;
        $item_fees = 0;
        $all_item_fees = 0;
        $all_item_tax = 0;
        $all_discounts  = 0;
        $all_pats = 0;
        $factor_price = 0;

        foreach ($Items as $item) {
            $pr = DB::table('products')->where('id', $item->pr_id)->first();
            $allpacks += intval($item->pack);
            $allitems += intval($item->tedad);
            $items = intval($pr->pack_items) * intval($item->pack) + intval($item->tedad);
            $allitems_full += intval($items);
            $item_fees += intval($item->price);
            $fee_price = intval($items) * intval($item->price);
            $all_item_fees += $fee_price;
            $disprice = (intval($items) * intval($item->price)) * intval($item->discount) / 100;
            $pat = intval($fee_price) - intval($disprice);
            $all_discounts += $disprice;
            $all_pats += $pat;
            $taxprice = intval(($pat * $pr->tax) / 100);
            $all_item_tax += $taxprice;
            $fullp = intval($pat) + intval($taxprice);
            $factor_price += $fullp;
        }



        echo "<p style='direction: rtl'> فاکتور شماره: " . $log->id . " /// پس از تخفیف: " . $all_pats . " /// قیمت کل: " . $factor_price . " </p><hr />";

        DB::table('pishfactors')->where('id', $log->id)->update(array(
            'fullPrice' => str_replace(",", "", $factor_price),
            'pat_price' => str_replace(",", "", $all_pats),
        ));
    }

    //dd($Logs);
    //$Customers = DB::table('customers')->where('role_id', $role->id)->pluck('store_id');

});
Route::get('/update_customer_organ', function () {

    $userID = auth()->user()->id;
    $Logs = DB::table('customers')->get();
    foreach ($Logs as $log) {
        if ($log->organization_id == null) {
            $Area = DB::table('areas')->where('id', $log->area)->first();
            $Region = DB::table('regions')->where('id', $Area->region_id)->first();
            DB::table('customers')->where('id', $log->id)->update(array(
                'organization_id' => $Region->organization_id,
            ));
        }
    }

    //dd($Logs);
    //$Customers = DB::table('customers')->where('role_id', $role->id)->pluck('store_id');

});

Route::get('/check_customer_creator', function () {

    $userID = auth()->user()->id;
    $Logs = DB::table('logs')->get();
    foreach ($Logs as $log) {
        if (str_starts_with($log->description, 'یک مشتری')) {
            $LogArray = explode('-', $log->description);
            $CustomerName = $LogArray[1];
            DB::table('customers')->where('name', $CustomerName)->update(array(
                'created_by' => $log->user_id,
            ));
        }
    }

    //dd($Logs);
    //$Customers = DB::table('customers')->where('role_id', $role->id)->pluck('store_id');

});
