@extends('layouts.admin.app')

@section('content')

<div class="w-full p-3 mx-auto">
    @can('View Reports')
        <dashboard></dashboard>
    @endcan

</div>

@endsection
