@extends('includes.main')
@section('content')
    <div class="breadcrumbs">
        <div class="col-sm-4">
            <div class="page-header float-left">
                <div class="page-title">
                    <h1>Dashboard</h1>
                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="page-header float-right">
                <div class="page-title">
                    <ol class="breadcrumb text-right">
                        <li class="active">User Full Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content mt-3">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">User Details</strong>
                        </div>
                        <div class="card-body">
                            <div class="row m-2 jumbotron justify-content-around p-1">
                                <div class="col-lg-5 col-md-5 col-sm-12 p-3" style="font-size: larger;">
                                    <strong>Name:</strong> {{ $user->name }}
                                </div>
                                <div class="col-lg-5 col-md-5 col-sm-12 p-3" style="font-size: larger;">
                                    <strong>Email:</strong> {{ $user->email }}
                                </div>
                                <div class="col-lg-5 col-md-5 col-sm-12 p-3" style="font-size: larger;">
                                    <strong>Phone:</strong> {{ $user->phone }}
                                </div>
                                <div class="col-lg-5 col-md-5 col-sm-12 p-3" style="font-size: larger;">
                                    <strong>Date of Birth:</strong> {{ $user->dob }}
                                </div>
                                <div class="col-lg-5 col-md-5 col-sm-12 p-3" style="font-size: larger;">
                                    <strong>State:</strong> {{ $user->state }}
                                </div>
                                <div class="col-lg-5 col-md-5 col-sm-12 p-3" style="font-size: larger;">
                                    <strong>City:</strong> {{ $user->city }}
                                </div>
                                <div class="col-lg-5 col-md-5 col-sm-12 p-3" style="font-size: larger;">
                                    <strong>Qualification:</strong> {{ $user->qualification }}
                                </div>
                                <div class="col-lg-5 col-md-5 col-sm-12 p-3" style="font-size: larger;">
                                    <strong>Aadhar Card:</strong>
                                    <div class="d-flex justify-content-around">
                                        <img src="{{ asset($user->aadhar_photo_front) }}" alt="Aadhar Photo"
                                            style="height: 150px;width: 150px;">
                                        <img src="{{ asset($user->aadhar_photo_back) }}" alt="Aadhar Photo"
                                            style="height: 150px;width: 150px;">
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 p-3 pl-5" style="font-size: larger;">
                                    <strong>Profile Photo:</strong><img src="{{ asset($user->profile_photo) }}"
                                        alt="Profile Photo" style="height: 150px;width: 150px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
