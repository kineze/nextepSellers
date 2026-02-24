@extends('layouts.seller.app')

@section('content')
<seller-product-show
  :product-id='@json((int) $productId)'
  :product-data-url='@json(route("sellerProducts.data", $productId))'
  :products-url='@json(route("sellerProducts"))'
  :order-url='@json(route("sellerOrders"))'
></seller-product-show>
@endsection
