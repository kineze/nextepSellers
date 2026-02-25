@extends('layouts.seller.app')

@section('content')
<seller-order-submit :products-url='@json(route("sellerProducts"))'></seller-order-submit>
@endsection
