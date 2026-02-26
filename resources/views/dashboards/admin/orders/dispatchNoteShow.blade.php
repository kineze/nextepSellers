@extends('layouts.admin.app')

@section('content')
<admin-dispatch-note-show :note-id="{{ (int) $dispatchNoteId }}"></admin-dispatch-note-show>
@endsection
