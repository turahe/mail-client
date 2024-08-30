@extends('mailclient::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('mailclient.name') !!}</p>
@endsection
