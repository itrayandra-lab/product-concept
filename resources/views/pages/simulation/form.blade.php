@extends('layouts.guest')

@section('content')
    <section class="px-4 py-10 sm:px-8">
        <x-forms.simulation-form :lookups="$lookups" :initial-data="$prefill ?? []" />
    </section>
@endsection
