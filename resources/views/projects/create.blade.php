@extends('layouts.app')

@section('title', 'Tambah Project - Aplikasi Cukai')
@section('topbar-title', 'Project')
@section('page-title', 'Tambah Project Permohonan')
@section('page-subtitle', 'Buat wadah project untuk proses dokumen dan mail merge')

@section('content')
    <form method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data">
        @csrf

        @include('projects.partials.form', [
            'project' => null,
            'submitLabel' => 'Simpan Project',
            'submitClass' => 'btn-success',
        ])
    </form>
@endsection
