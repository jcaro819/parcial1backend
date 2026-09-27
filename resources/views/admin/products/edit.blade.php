@extends('layouts.app')

@section('title', 'Editar '.$product->name)

@section('content')
<h1 class="h3 mb-3">Editar: {{ $product->name }}</h1>

<form method="POST" action="{{ route('admin.products.update', $product) }}" class="card card-body shadow-sm">
    @method('PUT')
    @include('admin.products._form')
</form>
@endsection
