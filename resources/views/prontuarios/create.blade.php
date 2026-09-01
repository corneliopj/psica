@extends('layouts.app')

@section('content')
<div class="container p-6">
    <h1 class="text-2xl mb-4">Novo Prontuário</h1>
    <form action="{{ route('prontuarios.store') }}" method="POST">@csrf
        @include('prontuarios._form')
    </form>
</div>
@endsection
