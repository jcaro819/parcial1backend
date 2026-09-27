@extends('layouts.app')

@section('title', 'Nuevo producto')

@section('content')
<h1 class="h3 mb-3">Nuevo producto</h1>

<form method="POST" action="{{ route('admin.products.store') }}" class="card card-body shadow-sm">
    @include('admin.products._form')
</form>
@endsection
