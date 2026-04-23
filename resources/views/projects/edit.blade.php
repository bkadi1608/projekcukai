@extends('layouts.app')

@section('title', 'Edit Project - Aplikasi Cukai')
@section('topbar-title', 'Project')
@section('page-title', 'Edit Project Permohonan')
@section('page-subtitle', 'Perbarui data dasar project')

@section('content')
    <form method="POST" action="{{ route('projects.update', $project->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('projects.partials.form', [
            'project' => $project,
            'submitLabel' => 'Update Project',
            'submitClass' => 'btn-warning',
        ])
    </form>
@endsection
