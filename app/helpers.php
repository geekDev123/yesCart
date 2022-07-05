<?php

use App\Models\User;
use App\Models\Product;
use App\Models\PackageInfo;
use App\Http\Resources\ProductResource;

// Get user info by user_id
if(!function_exists('getUserMetaInfoById')){
    function getUserMetaInfoById($id){ 
        $getUserinfo = User::find($id);
        if($getUserinfo != null){
            return $getUserinfo;
        }
        return null;
    }
}

// Get product info by user_id
if(!function_exists('getProductById')){
    function getProductById($id){ 
        $getProductInfo = Product::find($id);
        if($getProductInfo != null){
            return $getProductInfo;
        }
        return null;
    }
}

// Get product info by product ids
if(!function_exists('getPackageInfoById')){
    function getPackageInfoById($id){
        $getPackageInfo = PackageInfo::find($id);
        if($getPackageInfo != null){
            return $getPackageInfo;
        }
        return null;
    }
}

// Get product info by product ids
if(!function_exists('getVendorInfoByProductId')){
    function getVendorInfoByProductId($id){
        $getVendorInfo = Product::find($id)->user;
       
        if($getVendorInfo != null){
            return $getVendorInfo;
        }
        return null;
    }
}

