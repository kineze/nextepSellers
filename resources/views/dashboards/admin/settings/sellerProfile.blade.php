@extends('layouts.admin.app')

@section('content')

<seller-profile :seller-id='@json($sellerId)'></seller-profile>

@endsection
