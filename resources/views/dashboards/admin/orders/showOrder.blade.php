@extends('layouts.admin.app')

@section('content')
<admin-order-show :order-id="{{ (int) $orderId }}"></admin-order-show>
@endsection
