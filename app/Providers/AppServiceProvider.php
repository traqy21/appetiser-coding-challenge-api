<?php

namespace App\Providers;

use App\Models\Merchant;
use App\Models\MerchantUser;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductInventory;
use App\Repositories\MerchantRepository;
use App\Repositories\MerchantUserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductInventoryRepository;
use App\Repositories\ProductRepository;
use App\ThirdParty\Jwt\TokenAuthFacades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider {

    public function boot() {
        \date_default_timezone_set('Asia/Manila');

        //validators
        Validator::extend('uuid', function ($attribute, $value, $parameters, $validator) {
            return \Ramsey\Uuid\Uuid::isValid($value);
        });

        Validator::extend('validate_user_exist', function ($attribute, $value, $parameters, $validator) {
            $request = app(Request::class);
            $userRepository = app()->make(MerchantUserRepository::class);
            $merchant = Merchant::where('name', $request->name)->first();

            if($merchant == null){
                return true;
            }

            $user = $userRepository->getUserByUsername($request->user['username'], $merchant->uuid);

            if($user == null){
                return true;
            }

            return false;
        });


        Validator::extend('is_base64_pdf', function ($attribute, $value, $parameters, $validator) {

            if ($value != null && $value != '' && $value != 'null') {

                $image = base64_decode($value);
                $f = finfo_open();
                $result = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
                return str_contains($result, 'application/pdf');
            }

            return true;
        });

        Validator::extend('validAttributeAction', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, ['create', 'update']);
        });

        Validator::extend('validVariableAction', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, ['create', 'update']);
        });

        Validator::extend('validAttributeVariationAction', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, ['create', 'update']);
        });

        Validator::extend('ValidateSkuPerMerchant', function ($attribute, $value, $parameters, $validator) {
            $request = app(Request::class);
            $user = TokenAuthFacades::getUser($request, "merchant");
            $merchantUuid = $user->merchant_uuid;
            $repository = app()->make(ProductInventoryRepository::class);
            $inventory = $repository->getBySku($merchantUuid, $value);
            return ($inventory == null) ? true : false;
        });

        Validator::extend('ValidateSkuPerMerchantOnUpdate', function ($attribute, $value, $parameters, $validator) {
            $request = app(Request::class);
            $user = TokenAuthFacades::getUser($request, "merchant");
            $merchantUuid = $user->merchant_uuid;

            $productRepository = app()->make(ProductRepository::class);
            $productObj = $productRepository->find('uuid', $request->uuid);

            $repository = app()->make(ProductInventoryRepository::class);
            $inventory = $repository->getBySkuExceptUuuid($merchantUuid, $value, $productObj->simple_inventory->uuid);

            return ($inventory == null);
        });

        Validator::extend('ValidateSkuPerMerchantOnUpdateForVariable', function ($attribute, $value, $parameters, $validator) {
            $request = app(Request::class);
            $user = TokenAuthFacades::getUser($request, "merchant");
            $merchantUuid = $user->merchant_uuid;

            $parseAttribute = explode('.', $attribute);
            $index = $parseAttribute[1];
            $data = $validator->getData()['product_variables'][$index];
            $variable = $data['variable'];

            $action =  isset($data['action']) ? $data['action'] : null;
            $sku = isset($variable['sku']) ? $variable['sku'] : null;

            if ($action == 'create') {
                $item = ProductInventory::where('sku', $sku)
                        ->where('merchant_uuid', $merchantUuid)
                        ->first();
                return $item == null;
            }
            return true;

        });

        Validator::extend('ValidatePayment', function ($attribute, $value, $parameters, $validator) {
            $request = app(Request::class);
            $user = TokenAuthFacades::getUser($request, "merchant");
            $merchantUuid = $user->merchant_uuid;
            Log::Debug('payment', [$request->payment_amount]);

            $order = Order::where('uuid', $request->order_uuid)->first();
            Log::Debug('order', [$order->order_amount]);

            return ($request->payment_amount >= $order->order_amount);
        });

        Validator::extend('validateAvailableStock', function ($attribute, $value, $parameters, $validator) {
            $request = app(Request::class);
            $item = OrderItem::where('uuid', $request->uuid)->first();
            $itemInventory = ProductInventory::where('sku', $item->sku)->first();

            return ($value <= $itemInventory->stock);

        });

        Validator::extend('validateAvailableStockByOrder', function ($attribute, $value, $parameters, $validator) {
            $request = app(Request::class);

            $itemInventory = ProductInventory::where('sku', $request->sku)->first();

            if($itemInventory !== null){
                $order = Order::where('uuid', $request->order_uuid)->first();
                $item = OrderItem::where('order_id', $order->id)
                    ->where('sku', $request->sku)
                    ->first();
                if($item == null){
                    //check stock
                    return ($itemInventory->stock > 0);
                }
                return (($item->quantity + $value) <= $itemInventory->stock);
            }

            return false;
        });

        Validator::extend('is_order_pending', function ($attribute, $value, $parameters, $validator) {
             $order = Order::where('uuid', $value)->first();
             return ($order->status == Order::PENDING);
        });

        Validator::extend('is_item_exist', function ($attribute, $value, $parameters, $validator) {
            $order = Order::where('uuid', $value)->first();
             return ($order->items->count() > 0);
        });

        Validator::extend('validDiscountVariable', function ($attribute, $value, $parameters, $validator) {

            $parseAttribute = explode('.', $attribute);
            $index = $parseAttribute[1];
            $variable = $validator->getData()['product_variables'][$index]['variable'];

            $regularPrice =  isset($variable['regular_price']) ? (float)$variable['regular_price'] : 0;
            $discountType = isset($variable['discount_type']) ? $variable['discount_type'] : null;
            $onSale = isset($variable['on_sale']) ? (bool)$variable['on_sale'] : false;

            if ($value < 1 && $onSale == true) {
                return false;
            }

            if (($regularPrice == 0 || $discountType == null) && $onSale == true) {
                return false;
            }

            if (strtolower($discountType) == 'php' || strtolower($discountType) == 'value' ) {
                return ($regularPrice - (float)$value) >= 0 || $onSale == false;
            }

            $percentage = (float) $value / 100;
            return ($regularPrice - ($regularPrice * $percentage)) >= 0 || $onSale == false;
        });

        Validator::extend('validVariableSkuOnUpdate', function ($attribute, $value, $parameters, $validator) {

            $parseAttribute = explode('.', $attribute);
            $index = $parseAttribute[1];
            $data = $validator->getData()['product_variables'][$index];
            $variable = $data['variable'];

            $action =  isset($data['action']) ? $data['action'] : null;
            $sku = isset($variable['sku']) ? $variable['sku'] : null;

            if ($action == 'create') {
                $item = ProductInventory::where('sku', $sku)->first();
                return $item == null;
            }
            return true;
        });

        Validator::extend('validDiscount', function ($attribute, $value, $parameters, $validator) {

            $data = $validator->getData();

            $regularPrice = isset($data['regular_price']) ? (float)$data['regular_price'] : 0;
            $discountType = isset($data['discount_type']) ? $data['discount_type'] : null;
            $onSale = isset($data['on_sale']) ? (bool)$data['on_sale'] : false;

            if ($value < 1 && $onSale == true) {
                return false;
            }

            if (($regularPrice == 0 || $discountType == null) && $onSale == true) {
                return false;
            }

            if (strtolower($discountType) == 'value' || strtolower($discountType) == 'php' ) {
                return ($regularPrice - (float)$value) >= 0 || $onSale == false;
            }

            $percentage = (float) $value / 100;
            return ($regularPrice - ($regularPrice * $percentage)) >= 0 || $onSale == false;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('Illuminate\Contracts\Routing\ResponseFactory', function ($app) {
            return new \Illuminate\Routing\ResponseFactory(
                $app['Illuminate\Contracts\View\Factory'],
                $app['Illuminate\Routing\Redirector']
            );
        });
    }

}
