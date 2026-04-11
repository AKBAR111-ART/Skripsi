@extends('footbar.utama')

@section('title', 'Halaman Profile')

@section('content')
    <div class="profile-container">

    <!-- HEADER -->
    <div class="profile-header">
        <h1>My Profile</h1>
    </div>

    <!-- FOTO -->
    <img src="https://via.placeholder.com/100" class="profile-img" alt="Profile">

    <!-- BODY -->
    <div class="profile-body">
        <h2>Nama Kamu</h2>
        <p>Web Developer | Student | Freelancer</p>

        <div class="info">
            <div><b>Email:</b> kamu@email.com</div>
            <div><b>Telepon:</b> 0812xxxxxxx</div>
            <div><b>Alamat:</b> Jawa Timur, Indonesia</div>
        </div>

        <a href="#" class="btn">Edit Profile</a>
    </div>

</div>
 <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .profile-container {
            max-width: 400px;
            margin: 50px auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }

        .profile-header {
            background: linear-gradient(135deg, #4e73df, #224abe);
            padding: 30px;
            color: white;
        }

        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
            margin-top: -50px;
            background: white;
        }

        .profile-body {
            padding: 20px;
        }

        .profile-body h2 {
            margin: 10px 0 5px;
        }

        .profile-body p {
            color: #777;
            font-size: 14px;
        }

        .info {
            text-align: left;
            margin-top: 20px;
        }

        .info div {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #4e73df;
            color: white;
            border-radius: 8px;
            text-decoration: none;
        }

        .btn:hover {
            background: #2e59d9;
        }
    </style>
@endsection