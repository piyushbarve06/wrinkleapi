<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Auth\AuthController;
use App\Http\Controllers\v1\Auth\RegisterController;
use App\Http\Controllers\v1\Profile\ProfileController;
use App\Http\Controllers\v1\Auth\LogoutController;
use App\Http\Controllers\v1\PagesController;
use App\Http\Controllers\v1\SettingsController;
use App\Http\Controllers\v1\PaymentsController;
use App\Http\Controllers\v1\StoresController;
use App\Http\Controllers\v1\PaytmPayController;
use App\Http\Controllers\v1\CitiesController;
use App\Http\Controllers\v1\CategoriesController;
use App\Http\Controllers\v1\SubCategoriesController;
use App\Http\Controllers\v1\BlogsController;
use App\Http\Controllers\v1\ContactsController;
use App\Http\Controllers\v1\ReferralController;
use App\Http\Controllers\v1\ReferralCodesController;
use App\Http\Controllers\v1\OtpController;
use App\Http\Controllers\v1\FavouriteController;
use App\Http\Controllers\v1\ServicesController;
use App\Http\Controllers\v1\OffersController;
use App\Http\Controllers\v1\TimeslotController;
use App\Http\Controllers\v1\AddressController;
use App\Http\Controllers\v1\OrdersController;
use App\Http\Controllers\v1\ChatRoomsController;
use App\Http\Controllers\v1\ConversionsController;
use App\Http\Controllers\v1\ComplaintsController;
use App\Http\Controllers\v1\RatingsController;
use App\Http\Controllers\v1\RegisterRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return [
        'app' => 'Washing API By Initappz',
        'version' => '1.0.0',
    ];
});

Route::prefix('/v1')->group(function () {
    Route::group(['namespace' => 'Auth'], function () {
        Route::post('auth/login', [AuthController::class, 'login']);
        Route::post('auth/loginWithPhonePassword', [AuthController::class, 'loginWithPhonePassword']);
        Route::post('auth/store_login', [AuthController::class, 'store_login']);
        Route::get('users/get_admin', [ProfileController::class, 'get_admin']);
        Route::post('otp/verifyPhone', [OtpController::class, 'verifyPhone']);
        Route::post('auth/loginWithMobileOtp', [AuthController::class, 'loginWithMobileOtp']);
        Route::post('auth/verifyPhoneForFirebase', [AuthController::class, 'verifyPhoneForFirebase']);
        Route::post('auth/create_admin_account', [RegisterController::class, 'create_admin_account']);
        Route::post('uploadImage', [ProfileController::class, 'uploadImage']);
        Route::post('auth/verifyEmailForReset', [AuthController::class, 'verifyEmailForReset']);
    });

    // Main Admin Routes
    Route::group(['middleware' => ['admin_auth', 'jwt.auth']], function () {
        Route::post('auth/admin_logout', [LogoutController::class, 'logout']);
        Route::get('users/admins', [AuthController::class, 'admins']);
        Route::post('profile/update', [AuthController::class, 'update']);
        Route::post('users/adminNewAdmin', [RegisterController::class, 'adminNewAdmin']);
        Route::post('drivers/create', [RegisterController::class, 'create_driver_account']);
        Route::post('drivers/update', [ProfileController::class, 'update']);
        Route::get('drivers/getAll', [ProfileController::class, 'getAllDriver']);
        Route::post('drivers/destroy', [ProfileController::class, 'destroy_driver']);

        Route::get('users/getAll', [ProfileController::class, 'getAll']);

        Route::post('auth/createFreelancerAccount', [RegisterController::class, 'createFreelancerAccount']);
        Route::post('freelancer/create', [StoresController::class, 'save']);
        Route::get('freelancer/getList', [StoresController::class, 'getList']);
        Route::post('freelancer/getById', [StoresController::class, 'getByUID']);
        Route::post('freelancer/updateInfo', [StoresController::class, 'updateInfo']);
        Route::post('freelancer/update', [StoresController::class, 'update']);

        // Cities Routes
        Route::get('cities/getAll', [CitiesController::class, 'getAll']);
        Route::post('cities/create', [CitiesController::class, 'save']);
        Route::post('cities/update', [CitiesController::class, 'update']);
        Route::post('cities/destroy', [CitiesController::class, 'delete']);
        Route::post('cities/getById', [CitiesController::class, 'getById']);

        // Settings Routes
        Route::post('settings/save', [SettingsController::class, 'save']);
        Route::get('settings/getById', [SettingsController::class, 'getById']);
        Route::get('settings/getAll', [SettingsController::class, 'getAll']);
        Route::get('settings/getSettingsForOwner', [SettingsController::class, 'getSettingsForOwner']);
        Route::post('settings/update', [SettingsController::class, 'update']);
        Route::post('settings/delete', [SettingsController::class, 'delete']);

        // Admin Routes For Payments
        Route::post('payments/paytmRefund', [PaytmPayController::class, 'refundUserRequest']);
        Route::post('payments/paytmRefund', [PaytmPayController::class, 'refundUserRequest']);
        Route::post('payments/getById', [PaymentsController::class, 'getById']);
        Route::post('payments/getPaymentInfo', [PaymentsController::class, 'getPaymentInfo']);
        Route::get('payments/getAll', [PaymentsController::class, 'getAll']);
        Route::post('payments/update', [PaymentsController::class, 'update']);
        Route::post('payments/delete', [PaymentsController::class, 'delete']);
        Route::post('payments/refundFlutterwave', [PaymentsController::class, 'refundFlutterwave']);
        Route::post('payments/payPalRefund', [PaymentsController::class, 'payPalRefund']);
        Route::post('payments/refundPayStack', [PaymentsController::class, 'refundPayStack']);
        Route::post('payments/razorPayRefund', [PaymentsController::class, 'razorPayRefund']);
        Route::post('payments/refundStripePayments', [PaymentsController::class, 'refundStripePayments']);
        Route::post('payments/stripeRefundPaymentIntent', [PaymentsController::class, 'stripeRefundPaymentIntent']);
        Route::post('payments/instaMOJORefund', [PaymentsController::class, 'instaMOJORefund']);

        // Pages Routes
        Route::post('pages/getById', [PagesController::class, 'getById']);
        Route::get('pages/getAll', [PagesController::class, 'getAllPages']);
        Route::post('pages/update', [PagesController::class, 'update']);

        // Categories Routes
        Route::get('categories/getAll', [CategoriesController::class, 'getAll']);
        Route::post('categories/create', [CategoriesController::class, 'save']);
        Route::post('categories/update', [CategoriesController::class, 'update']);
        Route::post('categories/destroy', [CategoriesController::class, 'delete']);
        Route::post('categories/getById', [CategoriesController::class, 'getById']);

        // SubCategories Routes
        Route::get('sub_categories/getAll', [SubCategoriesController::class, 'getAll']);
        Route::post('sub_categories/create', [SubCategoriesController::class, 'save']);
        Route::post('sub_categories/update', [SubCategoriesController::class, 'update']);
        Route::post('sub_categories/destroy', [SubCategoriesController::class, 'delete']);
        Route::post('sub_categories/getById', [SubCategoriesController::class, 'getById']);

        // Blogs Routes
        Route::get('blogs/getAll', [BlogsController::class, 'getAll']);
        Route::post('blogs/create', [BlogsController::class, 'save']);
        Route::post('blogs/update', [BlogsController::class, 'update']);
        Route::post('blogs/destroy', [BlogsController::class, 'delete']);
        Route::post('blogs/getById', [BlogsController::class, 'getById']);

        Route::get('contacts/getAll', [ContactsController::class, 'getAll']);
        Route::post('contacts/update', [ContactsController::class, 'update']);
        Route::post('mails/replyContactForm', [ContactsController::class, 'replyContactForm']);

        Route::get('referral/getAll', [ReferralController::class, 'getAll']);
        Route::post('referral/save', [ReferralController::class, 'save']);
        Route::post('referral/update', [ReferralController::class, 'update']);

        // Offers Routes //
        Route::get('offers/getAll', [OffersController::class, 'getAll']);
        Route::get('offers/getStores', [OffersController::class, 'getStores']);
        Route::post('offers/create', [OffersController::class, 'save']);
        Route::post('offers/update', [OffersController::class, 'update']);
        Route::post('offers/destroy', [OffersController::class, 'delete']);
        Route::post('offers/getById', [OffersController::class, 'getById']);
        Route::post('offers/updateStatus', [OffersController::class, 'updateStatus']);

        // Complaints Routes
        Route::get('complaints/getAll', [ComplaintsController::class, 'getAll']);
        Route::post('complaints/update', [ComplaintsController::class, 'update']);
        Route::post('complaints/replyContactForm', [ComplaintsController::class, 'replyContactForm']);

        Route::post('orders/getStatsOfStore', [OrdersController::class, 'getStoreStatsDataWithDates']);
        Route::get('orders/getAll', [OrdersController::class, 'getAll']);
        Route::post('orders/getByIdAdmin', [OrdersController::class, 'getOrderDetails']);
        Route::post('orders/updateStatusAdmin', [OrdersController::class, 'update']);

        Route::post('users/userInfoAdmin', [ProfileController::class, 'getInfo']);
        Route::get('home/getAdminDashboard', [OrdersController::class, 'getAdminDashboard']);
        Route::get('services/getAllServices', [ServicesController::class, 'getAllServices']);
        Route::post('services/updateStatus', [ServicesController::class, 'update']);

        Route::get('freelancer_request/getAll', [RegisterRequestController::class, 'getAll']);
        Route::post('freelancer_request/destroy', [RegisterRequestController::class, 'delete']);

        Route::post('sendNoficationGlobal', [ProfileController::class, 'sendNoficationGlobal']);
        Route::post('notification/sendToAllUsers', [ProfileController::class, 'sendToAllUsers']);
        Route::post('notification/sendToUsers', [ProfileController::class, 'sendToUsers']);
        Route::post('notification/sendToStores', [ProfileController::class, 'sendToStores']);
        Route::post('notification/sendToDrivers', [ProfileController::class, 'sendToDrivers']);

        Route::post('users/sendMailToUsers', [ProfileController::class, 'sendMailToUsers']);
        Route::post('users/sendMailToAll', [ProfileController::class, 'sendMailToAll']);
        Route::post('users/sendMailToStores', [ProfileController::class, 'sendMailToStores']);
        Route::post('users/sendMailToDrivers', [ProfileController::class, 'sendMailToDrivers']);

    });

    // User Routes
    Route::group(['middleware' => ['jwt', 'jwt.auth']], function () {
        Route::post('auth/logout', [LogoutController::class, 'logout']);

        Route::post('referralcode/getMyCode', [ReferralCodesController::class, 'getMyCode']);
        Route::post('referral/redeemReferral', [ReferralController::class, 'redeemReferral']);
        Route::post('profile/getMyWallet', [ProfileController::class, 'getMyWallet']);
        Route::post('profile/getMyWalletBalance', [ProfileController::class, 'getMyWalletBalance']);
        Route::post('profile/getProfile', [ProfileController::class, 'getProfile']);
        Route::post('profile/update', [ProfileController::class, 'updateProfile']);

        Route::post('drivers/nearMeDrivers', [ProfileController::class, 'nearMeDrivers']);

        // Payments Routes For Users
        Route::post('payments/createStripeToken', [PaymentsController::class, 'createStripeToken']);
        Route::post('payments/createCustomer', [PaymentsController::class, 'createCustomer']);
        Route::post('payments/getStripeCards', [PaymentsController::class, 'getStripeCards']);
        Route::post('payments/addStripeCards', [PaymentsController::class, 'addStripeCards']);
        Route::post('payments/createStripePayments', [PaymentsController::class, 'createStripePayments']);
        Route::get('getPayPalKey', [PaymentsController::class, 'getPayPalKey']);
        Route::get('getFlutterwaveKey', [PaymentsController::class, 'getFlutterwaveKey']);
        Route::get('getPaystackKey', [PaymentsController::class, 'getPaystackKey']);
        Route::get('getRazorPayKey', [PaymentsController::class, 'getRazorPayKey']);
        Route::get('payments/getPayments', [PaymentsController::class, 'getPayments']);

        Route::post('referral/redeemReferral', [ReferralController::class, 'redeemReferral']);
        Route::post('referralcode/getMyCode', [ReferralCodesController::class, 'getMyCode']);

        // Favourites Routes
        Route::get('favourite/getAll', [FavouriteController::class, 'getAll']);
        Route::post('favourite/create', [FavouriteController::class, 'save']);
        Route::post('favourite/update', [FavouriteController::class, 'update']);
        Route::post('favourite/destroy', [FavouriteController::class, 'delete']);
        Route::post('favourite/getById', [FavouriteController::class, 'getById']);
        Route::post('favourite/deleteLikes', [FavouriteController::class, 'deleteLikes']);

        Route::get('categories/getActive', [CategoriesController::class, 'getActive']);
        Route::post('categories/storesList', [CategoriesController::class, 'storesList']);

        // Timeslots Routes
        Route::get('timeslots/getAll', [TimeslotController::class, 'getAll']);
        Route::post('timeslots/create', [TimeslotController::class, 'save']);
        Route::post('timeslots/update', [TimeslotController::class, 'update']);
        Route::post('timeslots/destroy', [TimeslotController::class, 'delete']);
        Route::post('timeslots/getById', [TimeslotController::class, 'getById']);
        Route::post('timeslots/getByUid', [TimeslotController::class, 'getByUid']);
        Route::post('timeslots/getSlotsByForBookings', [TimeslotController::class, 'getSlotsByForBookings']);

        // services Routes
        Route::get('services/getAll', [ServicesController::class, 'getAll']);
        Route::post('services/create', [ServicesController::class, 'save']);
        Route::post('services/update', [ServicesController::class, 'update']);
        Route::post('services/destroy', [ServicesController::class, 'delete']);
        Route::post('services/getById', [ServicesController::class, 'getById']);
        Route::post('services/getListItems', [ServicesController::class, 'getListItems']);

        Route::post('sub_categories/getByCateID', [SubCategoriesController::class, 'getByCateID']);
        Route::post('freelancer/getStoreInfo', [StoresController::class, 'getStoreInfo']);
        Route::post('freelancer/getMyProfile', [StoresController::class, 'getMyProfile']);
        Route::post('freelancer/updateMyProfile', [StoresController::class, 'updateMyProfile']);

        // address Routes
        Route::post('address/save', [AddressController::class, 'save']);
        Route::post('address/getById', [AddressController::class, 'getById']);
        Route::post('address/getByUID', [AddressController::class, 'getByUID']);
        Route::get('address/getAll', [AddressController::class, 'getAll']);
        Route::post('address/update', [AddressController::class, 'update']);
        Route::post('address/delete', [AddressController::class, 'delete']);

        //order Routes
        Route::post('orders/create', [OrdersController::class, 'save']);
        Route::post('orders/update', [OrdersController::class, 'update']);
        Route::post('orders/destroy', [OrdersController::class, 'delete']);
        Route::post('orders/getById', [OrdersController::class, 'getById']);

        Route::post('orders/getMyOrders', [OrdersController::class, 'getMyOrders']);
        Route::post('orders/getStoreOrders', [OrdersController::class, 'getStoreOrders']);
        Route::post('orders/getDriverOrders', [OrdersController::class, 'getDriverOrders']);
        Route::post('orders/getOrderDetails', [OrdersController::class, 'getOrderDetails']);

        Route::post('orders/getStoreOrderDetails', [OrdersController::class, 'getStoreOrderDetails']);
        Route::post('orders/getDriverOrderDetails', [OrdersController::class, 'getDriverOrderDetails']);

        Route::post('chats/getChatRooms', [ChatRoomsController::class, 'getChatRooms']);
        Route::post('chats/createChatRooms', [ChatRoomsController::class, 'createChatRooms']);
        Route::post('chats/getChatListBUid', [ChatRoomsController::class, 'getChatListBUid']);
        Route::post('chats/getById', [ConversionsController::class, 'getById']);
        Route::post('chats/sendMessage', [ConversionsController::class, 'save']);

        Route::post('orders/getStats', [OrdersController::class, 'getStats']);
        Route::post('orders/getMonthsStats', [OrdersController::class, 'getMonthsStats']);
        Route::post('orders/getAllStats', [OrdersController::class, 'getAllStats']);

        Route::post('complaints/registerNewComplaints', [ComplaintsController::class, 'save']);

        Route::post('ratings/getByStoreId', [RatingsController::class, 'getByStoreId']);
        Route::post('ratings/saveStoreRatings', [RatingsController::class, 'saveStoreRatings']);

        Route::post('ratings/getByProductId', [RatingsController::class, 'getByProductId']);
        Route::post('ratings/saveServiceRating', [RatingsController::class, 'saveServiceRating']);

        Route::post('ratings/getByDriverId', [RatingsController::class, 'getByDriverId']);
        Route::post('ratings/saveDriversRatings', [RatingsController::class, 'saveDriversRatings']);
        Route::post('ratings/getDriversReviews', [RatingsController::class, 'getDriversReviews']);
        Route::post('ratings/getStoreReviews', [RatingsController::class, 'getStoreReviews']);

        Route::post('ratings/getSaveStoreReview', [RatingsController::class, 'getSaveStoreReview']);

        Route::post('password/updateUserPasswordWithEmail', [AuthController::class, 'updateUserPasswordWithEmail']);

        Route::post('notification/sendNotification', [ProfileController::class, 'sendNotification']);

    });
    // public routes
    Route::get('orders/getOrderInvoice', [OrdersController::class, 'getOrderInvoice']);
    Route::post('categories/storeCategories', [CategoriesController::class, 'storeCategories']);
    Route::post('sub_categories/userCategories', [SubCategoriesController::class, 'userCategories']);
    Route::post('services/getStoreService', [ServicesController::class, 'getStoreService']);

    Route::get('settings/getDefault', [SettingsController::class, 'getDefault']);

    Route::post('pages/getContent', [PagesController::class, 'getById']);
    // Payments Routes For User Public
    Route::get('payNow', [PaytmPayController::class, 'payNow']);
    Route::get('payNowWeb', [PaytmPayController::class, 'payNowWeb']);
    Route::post('paytm-callback', [PaytmPayController::class, 'paytmCallback']);
    Route::post('paytm-webCallback', [PaytmPayController::class, 'webCallback']);
    Route::get('refundUserRequest', [PaytmPayController::class, 'refundUserRequest']);

    Route::get('success_payments', [PaymentsController::class, 'success_payments']);
    Route::get('failed_payments', [PaymentsController::class, 'failed_payments']);
    Route::get('instaMOJOWebSuccess', [PaymentsController::class, 'instaMOJOWebSuccess']);
    Route::get('payments/payPalPay', [PaymentsController::class, 'payPalPay']);
    Route::get('payments/razorPay', [PaymentsController::class, 'razorPay']);
    Route::get('payments/VerifyRazorPurchase', [PaymentsController::class, 'VerifyRazorPurchase']);
    Route::post('payments/capureRazorPay', [PaymentsController::class, 'capureRazorPay']);
    Route::post('payments/instamojoPay', [PaymentsController::class, 'instamojoPay']);
    Route::get('payments/flutterwavePay', [PaymentsController::class, 'flutterwavePay']);
    Route::get('payments/paystackPay', [PaymentsController::class, 'paystackPay']);
    Route::get('payments/payKunPay', [PaymentsController::class, 'payKunPay']);
    Route::get('payments/stripeAppCheckout', [PaymentsController::class, 'stripeAppCheckout']);
    Route::post('payments/stripeWebCheckout', [PaymentsController::class, 'stripeWebCheckout']);
    Route::get('stripe_processing_payment', [PaymentsController::class, 'stripeProcessPayment']);
    Route::get('stripe_web_processing_payment', [PaymentsController::class, 'stripeWebProcessPayment']);

    Route::get('blogs/getTop', [BlogsController::class, 'getTop']);
    Route::get('blogs/getPublic', [BlogsController::class, 'getPublic']);
    Route::post('blogs/getDetails', [BlogsController::class, 'getById']);

    Route::post('contacts/create', [ContactsController::class, 'save']);
    Route::post('sendMailToAdmin', [ContactsController::class, 'sendMailToAdmin']);

    Route::post('freelancer/getStoresList', [StoresController::class, 'getStoresList']);
    Route::get('freelancer/searchResult', [StoresController::class, 'searchResult']);
    Route::post('sendVerificationOnMail', [AuthController::class, 'sendVerificationOnMail']);
    Route::post('verifyPhoneSignup', [AuthController::class, 'verifyPhoneSignup']);
    Route::post('otp/verifyOTP', [OtpController::class, 'verifyOTP']);
    Route::get('auth/firebaseauth', [AuthController::class, 'firebaseauth']);
    Route::get('success_verified', [AuthController::class, 'success_verified']);
    Route::post('auth/verifyPhoneForFirebaseRegistrations', [AuthController::class, 'verifyPhoneForFirebaseRegistrations']);
    Route::post('auth/create_account', [RegisterController::class, 'user_register']);

    Route::get('offers/getActive', [OffersController::class, 'getActive']);

    Route::post('get_store/getStoreReviews', [RatingsController::class, 'getStoreReviews']);
    Route::post('otp/verifyOTPReset', [OtpController::class, 'verifyOTPReset']);
    Route::get('getActiveCategories/getActive', [CategoriesController::class, 'getActive']);
    Route::get('cities/getActive', [CitiesController::class, 'getActive']);
    Route::post('user/verifyEmailRegister', [RegisterController::class, 'verifyEmailRegister']);
    Route::post('user/sendRegisterEmail', [AuthController::class, 'sendRegisterEmail']);
    Route::post('user/verifyMobileRegister', [RegisterController::class, 'verifyMobileRegister']);
    Route::post('auth/sendRegisterMobile', [AuthController::class, 'sendRegisterMobile']);
    Route::post('user/sendVerifyOTPMobile', [AuthController::class, 'sendVerifyOTPMobile']);

    Route::post('user/sendMyRequest', [RegisterRequestController::class, 'save']);

});
