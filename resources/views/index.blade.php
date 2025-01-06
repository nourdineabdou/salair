@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Integration salaire</h1>
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
                    <th>Nom</th>
                    <th>Type fichier</th>
                    <th>NB colones</th>
                    <th>Nom fichier Sortie</th>
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
@endsection