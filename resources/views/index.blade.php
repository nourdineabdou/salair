@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Integration de salaire ABM</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active">Liste des entites </li>
        </ol>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Liste
        </div>
        <div class="card-body">
        <table  class="table">
            <thead>
                <tr>
                    <th>Institution</th>
                    <th>Type fichier</th>
                    <th>NB colones</th>
                    <th>Nom de fichier de Sortie</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tfoot>
                @foreach(\App\Models\ReglesSalaire::all() as $regl)
                    <tr>
                        <td>{{$regl->nom}}</td>
                        <td>Excel</td>
                        @php
                           $l = explode(',',$regl->ordre);
                        @endphp
                        <td>{{ count($l) }}</td>
                        <td>{{$regl->name_file}}</td>
                        <td>@include('ajax.action' , ["id"=>$regl->id])</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-list me-1"></i>
                Date : {{ \Carbon\Carbon::now()->format('Y-m-d')}}
        </div>
        <div class="card-body">
            <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Institution</th>
                    <th scope="col">Heure_validation </th>
                    <th scope="col">Nbres_Lignes</th>
                    <th scope="col">Montant_Toatl</th>
                    <th scope="col">Nom_User</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Fichier_validé</th>
                </tr>
            </thead>
            <tbody id="tbody-salut">
                @foreach( $histo_this_days as $histo )
                    <tr>
                        <th scope="col">{{$histo->reglesSalaire->nom}}</th>
                        <th scope="col">{{$histo->created_at->format('H:i:s')}}</th>
                        <th scope="col">{{$histo->nbres_lignes}}</th>
                        <th scope="col">{{$histo->montant_Total}}</th>
                        <th scope="col">{{$histo->user->name}}</th>
                        <th scope="col">{{'Traité'}}</th>
                        <th scope="col"><a href="#" target="_blank"  ><i class="fa fa-file-excel-o"></i></a></th>
                    </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </div>
@endsection