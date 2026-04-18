@extends('footbar.utama')

@section('title', 'Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@section('content')

<div class="profile-container">

    <!-- HEADER -->
    <div class="profile-header">
        <h2>👤 My Profile</h2>
    </div>

    <!-- FOTO -->
    <div class="profile-img-wrapper">
        <img src="https://i.pravatar.cc/100" class="profile-img">
    </div>

    <!-- BODY -->
    <div class="profile-body">
        <h3>Nama Kamu</h3>
        <p class="role">Web Developer</p>

        <div class="info">
            <div>📧 Email: kamu@email.com</div>
            <div>📱 Telepon: 0812xxxx</div>
        </div>
    </div>

</div>

@endsection