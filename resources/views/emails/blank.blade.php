@extends('emails.layout')

@section('body')
    <h2>{{ $title }}</h2>
    {!! $body !!}
@endsection
