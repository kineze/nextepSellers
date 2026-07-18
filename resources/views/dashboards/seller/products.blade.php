@extends('layouts.seller.app')

@section('content')
<seller-product-catalog
  :initial-catalog='@json($catalog)'
  products-url='{{ route('sellerProducts') }}'
></seller-product-catalog>
@endsection
