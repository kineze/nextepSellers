@extends('layouts.seller.app')

@section('content')
<seller-order-show :order-id="{{ $orderId }}"></seller-order-show>
@endsection
